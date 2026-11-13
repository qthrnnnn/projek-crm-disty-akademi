<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

Route::get('/', function () {
    return response()->file(public_path('index.html'));
})->name('home');

Route::get('/login', function () {
    return response()->file(public_path('index.html'));
})->name('login');

/* Alamat yang tidak dikenal (mis. /admin/dashboard diketik langsung oleh Klien) dialihkan ke halaman awal SPA.
   Hanya untuk navigasi browser. Permintaan API atau JSON tetap mendapat 404 JSON sesuai kontrak. */
Route::fallback(function (Request $request) {
    if ($request->is('api/*') || $request->expectsJson()) {
        throw new NotFoundHttpException;
    }

    return response()->file(public_path('index.html'));
});

require __DIR__.'/settings.php';
