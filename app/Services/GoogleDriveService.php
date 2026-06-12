<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GoogleDriveService
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const DRIVE_URL = 'https://www.googleapis.com/drive/v3/files';
    private const UPLOAD_URL = 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart';

    public function configured(): bool
    {
        return (bool) ($this->folderId() && $this->credentials());
    }

    public function upload(string $absolutePath, string $fileName): ?array
    {
        if (!$this->configured()) {
            Log::warning('Google Drive backup skipped: credentials or folder ID missing.');
            return null;
        }

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
            throw new RuntimeException('Google Drive upload failed: ' . $response->body());
        }

        return $response->json();
    }

    public function listBackups(): array
    {
        if (!$this->configured()) {
            return [];
        }

        $query = sprintf(
            "'%s' in parents and trashed = false and name contains 'hru_ats_backup_'",
            addslashes($this->folderId())
        );

        $response = Http::withToken($this->accessToken())
            ->get(self::DRIVE_URL, [
                'q' => $query,
                'fields' => 'files(id,name,size,createdTime,modifiedTime)',
                'orderBy' => 'createdTime desc',
                'pageSize' => 100,
            ]);

        if (!$response->successful()) {
            Log::warning('Google Drive backup list failed: ' . $response->body());
            return [];
        }

        return $response->json('files', []);
    }

    public function delete(string $fileId): bool
    {
        if (!$this->configured()) {
            return false;
        }

        $response = Http::withToken($this->accessToken())
            ->delete(self::DRIVE_URL . '/' . rawurlencode($fileId));

        return $response->successful() || $response->status() === 404;
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

    private function accessToken(): string
    {
        $credentials = $this->credentials();
        if (!$credentials) {
            throw new RuntimeException('Google Drive credentials are not configured.');
        }

        $now = time();
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $claims = $this->base64UrlEncode(json_encode([
            'iss' => $credentials['client_email'] ?? null,
            'scope' => 'https://www.googleapis.com/auth/drive.file',
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
        return config('services.google_drive.folder_id');
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
