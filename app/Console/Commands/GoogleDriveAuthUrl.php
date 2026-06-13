<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GoogleDriveAuthUrl extends Command
{
    protected $signature = 'google-drive:auth-url {--redirect-uri= : Override GOOGLE_DRIVE_REDIRECT_URI for this URL}';

    protected $description = 'Generate a Google Drive OAuth consent URL for user-quota uploads.';

    public function handle(): int
    {
        $clientId = config('services.google_drive.client_id');
        $redirectUri = $this->option('redirect-uri') ?: config('services.google_drive.redirect_uri');

        if (!$clientId) {
            $this->error('Set GOOGLE_DRIVE_CLIENT_ID in .env first.');
            return self::FAILURE;
        }

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/drive',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ], '', '&', PHP_QUERY_RFC3986);

        $this->line('Open this URL in your browser:');
        $this->newLine();
        $this->line('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
        $this->newLine();
        $this->line('After approval, copy the code= value from the redirected URL.');

        return self::SUCCESS;
    }
}
