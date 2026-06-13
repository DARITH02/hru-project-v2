<?php

namespace Tests\Feature;

use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GoogleDriveServiceTest extends TestCase
{
    public function test_invalid_google_drive_credentials_are_not_configured(): void
    {
        Log::spy();

        config([
            'services.google_drive.credentials' => '{bad-json',
            'services.google_drive.credentials_base64' => null,
            'services.google_drive.credentials_path' => null,
            'services.google_drive.client_id' => null,
            'services.google_drive.client_secret' => null,
            'services.google_drive.refresh_token' => null,
            'services.google_drive.folder_id' => 'folder-123',
        ]);

        $this->assertFalse(app(GoogleDriveService::class)->configured());

        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $message) => str_contains($message, 'Google Drive backup configuration is invalid'));
    }

    public function test_upload_sends_backup_to_configured_google_drive_folder(): void
    {
        $credentials = $this->serviceAccountCredentials();
        $backupPath = tempnam(sys_get_temp_dir(), 'backup_');
        file_put_contents($backupPath, 'zip-content');

        config([
            'services.google_drive.credentials' => json_encode($credentials, JSON_THROW_ON_ERROR),
            'services.google_drive.credentials_base64' => null,
            'services.google_drive.credentials_path' => null,
            'services.google_drive.folder_id' => 'folder-123',
        ]);

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'token-123'], 200),
            'www.googleapis.com/drive/v3/files/folder-123*' => Http::response([
                'id' => 'folder-123',
                'name' => 'HRU-BACKUP',
                'mimeType' => 'application/vnd.google-apps.folder',
                'capabilities' => [
                    'canAddChildren' => true,
                    'canEdit' => true,
                ],
            ], 200),
            'www.googleapis.com/upload/drive/v3/files*' => Http::response([
                'id' => 'drive-file-123',
                'name' => 'hru_ats_backup_2026_06_13_12_00_00.zip',
            ], 200),
        ]);

        $response = app(GoogleDriveService::class)->upload($backupPath, 'hru_ats_backup_2026_06_13_12_00_00.zip');

        $this->assertSame('drive-file-123', $response['id']);
        Http::assertSent(fn ($request) => $request->url() === 'https://oauth2.googleapis.com/token');
        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://www.googleapis.com/upload/drive/v3/files')
            && str_contains($request->body(), '"parents":["folder-123"]')
            && str_contains($request->body(), 'hru_ats_backup_2026_06_13_12_00_00.zip'));

        unlink($backupPath);
    }

    public function test_google_storage_disk_can_put_file(): void
    {
        config([
            'services.google_drive.credentials' => json_encode($this->serviceAccountCredentials(), JSON_THROW_ON_ERROR),
            'services.google_drive.credentials_base64' => null,
            'services.google_drive.credentials_path' => null,
            'services.google_drive.folder_id' => 'folder-123',
        ]);

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'token-123'], 200),
            'www.googleapis.com/drive/v3/files/folder-123*' => Http::response([
                'id' => 'folder-123',
                'name' => 'HRU-BACKUP',
                'mimeType' => 'application/vnd.google-apps.folder',
                'capabilities' => [
                    'canAddChildren' => true,
                    'canEdit' => true,
                ],
            ], 200),
            'www.googleapis.com/upload/drive/v3/files*' => Http::response([
                'id' => 'drive-file-123',
                'name' => 'test.txt',
            ], 200),
        ]);

        $this->assertTrue(Storage::disk('google')->put('test.txt', 'hello'));

        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://www.googleapis.com/upload/drive/v3/files')
            && str_contains($request->body(), '"name":"test.txt"')
            && str_contains($request->body(), 'hello'));
    }

    public function test_oauth_credentials_can_upload_using_refresh_token(): void
    {
        $backupPath = tempnam(sys_get_temp_dir(), 'backup_');
        file_put_contents($backupPath, 'zip-content');

        config([
            'services.google_drive.credentials' => null,
            'services.google_drive.credentials_base64' => null,
            'services.google_drive.credentials_path' => null,
            'services.google_drive.client_id' => 'client-id',
            'services.google_drive.client_secret' => 'client-secret',
            'services.google_drive.refresh_token' => 'refresh-token',
            'services.google_drive.folder_id' => 'folder-123',
        ]);

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'oauth-token-123'], 200),
            'www.googleapis.com/drive/v3/files/folder-123*' => Http::response([
                'id' => 'folder-123',
                'name' => 'HRU-BACKUP',
                'mimeType' => 'application/vnd.google-apps.folder',
                'capabilities' => [
                    'canAddChildren' => true,
                    'canEdit' => true,
                ],
            ], 200),
            'www.googleapis.com/upload/drive/v3/files*' => Http::response([
                'id' => 'drive-file-123',
                'name' => 'oauth-backup.zip',
            ], 200),
        ]);

        $response = app(GoogleDriveService::class)->upload($backupPath, 'oauth-backup.zip');

        $this->assertSame('drive-file-123', $response['id']);
        Http::assertSent(fn ($request) => $request->url() === 'https://oauth2.googleapis.com/token'
            && $request['grant_type'] === 'refresh_token'
            && $request['refresh_token'] === 'refresh-token');

        unlink($backupPath);
    }

    public function test_download_saves_google_drive_file_to_destination(): void
    {
        $destinationPath = tempnam(sys_get_temp_dir(), 'drive_download_');
        unlink($destinationPath);

        config([
            'services.google_drive.credentials' => json_encode($this->serviceAccountCredentials(), JSON_THROW_ON_ERROR),
            'services.google_drive.credentials_base64' => null,
            'services.google_drive.credentials_path' => null,
            'services.google_drive.folder_id' => 'folder-123',
        ]);

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'token-123'], 200),
            'www.googleapis.com/drive/v3/files/drive-file-123*' => Http::response('zip-content', 200),
        ]);

        app(GoogleDriveService::class)->download('drive-file-123', $destinationPath);

        $this->assertSame('zip-content', file_get_contents($destinationPath));

        unlink($destinationPath);
    }

    public function test_credentials_can_be_loaded_from_raw_secret_file_path(): void
    {
        $credentialsPath = tempnam(sys_get_temp_dir(), 'drive_credentials_');
        file_put_contents($credentialsPath, json_encode($this->serviceAccountCredentials(), JSON_THROW_ON_ERROR));

        config([
            'services.google_drive.credentials' => $credentialsPath,
            'services.google_drive.credentials_base64' => null,
            'services.google_drive.credentials_path' => null,
            'services.google_drive.client_id' => null,
            'services.google_drive.client_secret' => null,
            'services.google_drive.refresh_token' => null,
            'services.google_drive.folder_id' => 'folder-123',
        ]);

        $this->assertTrue(app(GoogleDriveService::class)->configured());

        unlink($credentialsPath);
    }

    public function test_credentials_can_be_loaded_from_base64_json(): void
    {
        config([
            'services.google_drive.credentials' => null,
            'services.google_drive.credentials_base64' => base64_encode(json_encode($this->serviceAccountCredentials(), JSON_THROW_ON_ERROR)),
            'services.google_drive.credentials_path' => null,
            'services.google_drive.client_id' => null,
            'services.google_drive.client_secret' => null,
            'services.google_drive.refresh_token' => null,
            'services.google_drive.folder_id' => 'folder-123',
        ]);

        $this->assertTrue(app(GoogleDriveService::class)->configured());
    }

    private function serviceAccountCredentials(): array
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($key, $privateKey);

        return [
            'client_email' => 'backup@example.iam.gserviceaccount.com',
            'private_key' => $privateKey,
        ];
    }
}
