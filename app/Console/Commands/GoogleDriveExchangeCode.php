<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GoogleDriveExchangeCode extends Command
{
    protected $signature = 'google-drive:exchange-code {code : Authorization code from Google} {--redirect-uri= : Override GOOGLE_DRIVE_REDIRECT_URI used for the auth URL}';

    protected $description = 'Exchange a Google OAuth authorization code for a refresh token.';

    public function handle(): int
    {
        $clientId = config('services.google_drive.client_id');
        $clientSecret = config('services.google_drive.client_secret');
        $redirectUri = $this->option('redirect-uri') ?: config('services.google_drive.redirect_uri');

        if (!$clientId || !$clientSecret) {
            $this->error('Set GOOGLE_DRIVE_CLIENT_ID and GOOGLE_DRIVE_CLIENT_SECRET in .env first.');
            return self::FAILURE;
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $this->argument('code'),
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->successful()) {
            $this->error('Google OAuth code exchange failed:');
            $this->line($response->body());
            return self::FAILURE;
        }

        $refreshToken = $response->json('refresh_token');

        if (!$refreshToken) {
            $this->warn('Google did not return a refresh token. Re-run google-drive:auth-url and make sure prompt=consent is present, or revoke app access and try again.');
            return self::FAILURE;
        }

        $this->line('Add this to .env:');
        $this->newLine();
        $this->line('GOOGLE_DRIVE_REFRESH_TOKEN=' . $refreshToken);

        return self::SUCCESS;
    }
}
