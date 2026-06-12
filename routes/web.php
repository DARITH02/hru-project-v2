<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/language/{locale}', function (Request $request, string $locale) {
    abort_unless(array_key_exists($locale, config('app.supported_locales', [])), 404);

    $request->session()->put('locale', $locale);

    return back();
})->name('language.switch');

require __DIR__ . '/web/auth.php';
require __DIR__ . '/web/student.php';
require __DIR__ . '/web/teacher.php';
require __DIR__ . '/web/admin.php';
