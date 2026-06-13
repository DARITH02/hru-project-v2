<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/language/{locale}', function (Request $request, string $locale) {
    abort_unless(array_key_exists($locale, config('app.supported_locales', [])), 404);

    $request->session()->put('locale', $locale);

    return back();
})->name('language.switch');

Route::get('/google-drive/oauth/callback', function (Request $request) {
    $code = $request->query('code');

    abort_unless(is_string($code) && $code !== '', 400, 'Missing Google OAuth code.');

    $command = "docker compose exec app php artisan google-drive:exchange-code '" . e($code) . "'";

    return response(
        '<!doctype html><html><head><meta charset="utf-8"><title>Google Drive OAuth Code</title>'
        . '<style>body{font-family:system-ui,sans-serif;max-width:900px;margin:60px auto;padding:0 24px;line-height:1.5;color:#111827}'
        . 'code,pre{background:#f3f4f6;border:1px solid #e5e7eb;border-radius:8px;padding:12px;display:block;overflow:auto}'
        . '</style></head><body><h1>Google Drive OAuth Code</h1>'
        . '<p>Run this command in your project terminal:</p><pre>' . $command . '</pre>'
        . '<p>After it prints <code>GOOGLE_DRIVE_REFRESH_TOKEN=...</code>, paste that value into <code>.env</code>.</p>'
        . '</body></html>'
    );
})->name('google-drive.oauth.callback');

require __DIR__ . '/web/auth.php';
require __DIR__ . '/web/student.php';
require __DIR__ . '/web/teacher.php';
require __DIR__ . '/web/admin.php';
