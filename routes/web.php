<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

Route::inertia('/login', 'Spa')->name('login');

// Named routes for SPA views to satisfy redirect()->route(...) calls across Laravel controllers
$spaNamedRoutes = [
    'dashboard' => '/dashboard',
    'payments.index' => '/payments',
    'payments.create' => '/payments/create',
    'payments.show' => '/payments/{payment}',
    'commissions.index' => '/commissions',
    'customers.index' => '/customers',
    'customers.create' => '/customers/create',
    'customers.show' => '/customers/{customer}',
    'follow-ups.index' => '/follow-ups',
    'follow-ups.create' => '/follow-ups/create',
    'follow-ups.show' => '/follow-ups/{followUp}',
    'institutions.index' => '/institutions',
    'leads.index' => '/leads',
    'leads.create' => '/leads/create',
    'leads.show' => '/leads/{lead}',
    'lead-imports.index' => '/lead-imports',
    'products.index' => '/products',
    'registrations.index' => '/registrations',
    'registrations.create' => '/registrations/create',
    'trainers.index' => '/trainers',
    'training-schedules.index' => '/training-schedules',
    'users.index' => '/users',
];

Route::inertia('/', 'Spa')->name('home');

Route::get('/leads/template', [\App\Http\Controllers\LeadController::class, 'downloadTemplate']);
Route::get('/leads/template-excel', [\App\Http\Controllers\LeadController::class, 'downloadTemplateExcel']);

foreach ($spaNamedRoutes as $name => $uri) {
    Route::inertia($uri, 'Spa')->name($name);
}

/* Fallback agar bukti transfer pada disk public tetap bisa dibuka
   meskipun symlink public/storage belum dibuat setelah folder proyek dipindahkan. */
Route::get('/storage/payments/{filename}', function (string $filename) {
    if (str_contains($filename, '..')) {
        abort(404);
    }

    $path = 'payments/'.$filename;
    $disk = Storage::disk('public');
    if (! $disk->exists($path)) {
        abort(404);
    }

    return $disk->response($path);
})->where('filename', '[A-Za-z0-9._-]+');

/* Alamat yang tidak dikenal (mis. /admin/dashboard diketik langsung oleh Klien) dialihkan ke halaman awal SPA.
   Hanya untuk navigasi browser. Permintaan API atau JSON tetap mendapat 404 JSON sesuai kontrak. */
Route::fallback(function (Request $request) {
    if ($request->is('api/*') || $request->expectsJson()) {
        throw new NotFoundHttpException;
    }

    return Inertia::render('Spa');
});

