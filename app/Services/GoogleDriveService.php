<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GoogleDriveService
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const DRIVE_URL = 'https://www.googleapis.com/drive/v3/files';
    private const UPLOAD_URL = 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&supportsAllDrives=true';

    public function configured(): bool
    {
        try {
            return (bool) ($this->folderId() && ($this->oauthConfigured() || $this->credentials()));
        } catch (Throwable $e) {
            Log::warning('Google Drive backup configuration is invalid: ' . $e->getMessage());
            return false;
        }
    }

    public function upload(string $absolutePath, string $fileName): ?array
    {
        if (!$this->configured()) {
            Log::warning('Google Drive backup skipped: credentials or folder ID missing.');
            return null;
        }

        $this->assertFolderWritable();

        $metadata = [
            'name' => $fileName,
            'parents' => [$this->folderId()],
        ];

        $boundary = 'backup_' . bin2hex(random_bytes(12));
        $body = "--{$boundary}\r\n"
            . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
            . json_encode($metadata, JSON_THROW_ON_ERROR) . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: application/zip\r\n\r\n"
            . file_get_contents($absolutePath) . "\r\n"
            . "--{$boundary}--";

        $response = Http::withToken($this->accessToken())
            ->withHeaders(['Content-Type' => "multipart/related; boundary={$boundary}"])
            ->timeout(180)
            ->send('POST', self::UPLOAD_URL, ['body' => $body]);

        if (!$response->successful()) {
            throw new RuntimeException('Google Drive upload failed: ' . $this->friendlyError($response->body()));
        }

        return $response->json();
    }

    public function listBackups(): array
    {
        if (!$this->configured()) {
            return [];
        }

        try {
            $query = sprintf(
                "'%s' in parents and trashed = false and name contains 'hru_ats_'",
                addslashes($this->folderId())
            );

            $response = Http::withToken($this->accessToken())
                ->get(self::DRIVE_URL, [
                    'q' => $query,
                    'fields' => 'files(id,name,size,createdTime,modifiedTime)',
                    'orderBy' => 'createdTime desc',
                    'pageSize' => 100,
                    'supportsAllDrives' => true,
                    'includeItemsFromAllDrives' => true,
                ]);

            if (!$response->successful()) {
                Log::warning('Google Drive backup list failed: ' . $response->body());
                return [];
            }

            return $response->json('files', []);
        } catch (Throwable $e) {
            Log::warning('Google Drive backup list failed: ' . $e->getMessage());
            return [];
        }
    }

    public function delete(string $fileId): bool
    {
        if (!$this->configured()) {
            return false;
        }

        $response = Http::withToken($this->accessToken())
            ->delete(self::DRIVE_URL . '/' . rawurlencode($fileId), [
                'supportsAllDrives' => true,
            ]);

        return $response->successful() || $response->status() === 404;
    }

    public function download(string $fileId, string $destinationPath): void
    {
        if (!$this->configured()) {
            throw new RuntimeException('Google Drive credentials or folder ID are not configured.');
        }

        File::ensureDirectoryExists(dirname($destinationPath), 0750, true);

        $response = Http::withToken($this->accessToken())
            ->timeout(180)
            ->sink($destinationPath)
            ->get(self::DRIVE_URL . '/' . rawurlencode($fileId), [
                'alt' => 'media',
                'supportsAllDrives' => true,
            ]);

        if (!$response->successful()) {
            if (is_file($destinationPath)) {
                unlink($destinationPath);
            }

            throw new RuntimeException('Google Drive download failed: ' . $this->friendlyError($response->body()));
        }
    }

    public function deleteOldBackups(int $days = 90): int
    {
        $deleted = 0;
        $cutoff = now()->subDays($days);

        foreach ($this->listBackups() as $file) {
            $createdAt = isset($file['createdTime']) ? Carbon::parse($file['createdTime']) : null;
            if ($createdAt && $createdAt->lt($cutoff) && $this->delete($file['id'])) {
                $deleted++;
            }
        }

        return $deleted;
    }

    public function deleteOldBackupsByPolicy(): array
    {
        $deleted = [
            'standard' => 0,
            'monthly' => 0,
        ];

        $standardCutoff = now()->subDays(90);
        $monthlyCutoff = now()->subYear();

        foreach ($this->listBackups() as $file) {
            $createdAt = isset($file['createdTime']) ? Carbon::parse($file['createdTime']) : null;
            if (!$createdAt || !isset($file['id'])) {
                continue;
            }

            $name = $file['name'] ?? '';
            $isMonthly = str_starts_with($name, 'hru_ats_monthly_');
            $cutoff = $isMonthly ? $monthlyCutoff : $standardCutoff;

            if ($createdAt->lt($cutoff) && $this->delete($file['id'])) {
                $deleted[$isMonthly ? 'monthly' : 'standard']++;
            }
        }

        return $deleted;
    }

    private function accessToken(): string
    {
        if ($this->oauthConfigured()) {
            return $this->oauthAccessToken();
        }

        $credentials = $this->credentials();
        if (!$credentials) {
            throw new RuntimeException('Google Drive credentials are not configured.');
        }

        $now = time();
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $claims = $this->base64UrlEncode(json_encode([
            'iss' => $credentials['client_email'] ?? null,
            'scope' => 'https://www.googleapis.com/auth/drive',
            'aud' => self::TOKEN_URL,
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_THROW_ON_ERROR));

        $signatureInput = "{$header}.{$claims}";
        $privateKey = $credentials['private_key'] ?? null;

        if (!$privateKey || !openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Unable to sign Google Drive service account token.');
        }

        $jwt = $signatureInput . '.' . $this->base64UrlEncode($signature);

        $response = Http::asForm()->post(self::TOKEN_URL, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('Google Drive token request failed: ' . $response->body());
        }

        return (string) $response->json('access_token');
    }

    private function oauthAccessToken(): string
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => config('services.google_drive.client_id'),
            'client_secret' => config('services.google_drive.client_secret'),
            'refresh_token' => config('services.google_drive.refresh_token'),
            'grant_type' => 'refresh_token',
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('Google Drive OAuth token request failed: ' . $response->body());
        }

        return (string) $response->json('access_token');
    }

    private function credentials(): ?array
    {
        $raw = config('services.google_drive.credentials');
        $path = config('services.google_drive.credentials_path');

        if ($raw) {
            return json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
        }

        if ($path && is_file($path)) {
            return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        }

        return null;
    }

    private function folderId(): ?string
    {
        $folderId = trim((string) config('services.google_drive.folder_id'));

        if ($folderId === '' || $folderId === '.') {
            return null;
        }

        return $folderId;
    }

    private function oauthConfigured(): bool
    {
        return filled(config('services.google_drive.client_id'))
            && filled(config('services.google_drive.client_secret'))
            && filled(config('services.google_drive.refresh_token'));
    }

    private function assertFolderWritable(): void
    {
        $response = Http::withToken($this->accessToken())
            ->get(self::DRIVE_URL . '/' . rawurlencode($this->folderId()), [
                'fields' => 'id,name,mimeType,capabilities(canAddChildren,canEdit)',
                'supportsAllDrives' => true,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Google Drive folder cannot be accessed: ' . $this->friendlyError($response->body()));
        }

        $folder = $response->json();

        if (($folder['mimeType'] ?? null) !== 'application/vnd.google-apps.folder') {
            throw new RuntimeException('Google Drive folder ID does not point to a folder.');
        }

        if (!($folder['capabilities']['canAddChildren'] ?? false)) {
            throw new RuntimeException('Google Drive folder is not writable by the configured service account.');
        }
    }

    private function friendlyError(string $body): string
    {
        $error = json_decode($body, true);
        $message = $error['error']['message'] ?? $body;
        $reason = $error['error']['errors'][0]['reason'] ?? null;

        if ($reason === 'storageQuotaExceeded' && str_contains($message, 'Service Accounts do not have storage quota')) {
            return 'Google rejected the upload because service accounts do not have Drive storage quota. Use a Google Shared Drive folder, or set GOOGLE_DRIVE_CLIENT_ID, GOOGLE_DRIVE_CLIENT_SECRET, and GOOGLE_DRIVE_REFRESH_TOKEN to upload with OAuth user credentials.';
        }

        if ($reason === 'notFound') {
            return 'Google Drive folder or file was not found. Verify GOOGLE_DRIVE_FOLDER_ID and share the folder with the service account email.';
        }

        if ($reason === 'insufficientParentPermissions') {
            return 'The service account can see the folder but cannot add files. Give it Editor, Content manager, or Manager permission.';
        }

        return $message;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
