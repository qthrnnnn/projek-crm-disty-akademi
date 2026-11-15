<?php

use App\Http\Controllers\ApiActionController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LeadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public endpoints
Route::get('/leads/template', [LeadController::class, 'downloadTemplate']);
Route::get('/leads/template-excel', [LeadController::class, 'downloadTemplateExcel']);
Route::post('/login', [ApiController::class, 'login'])->name('api.auth.login');
Route::post('/auth/login', [ApiController::class, 'login']);
Route::get('/csrf-token', fn () => response()->json(['token' => csrf_token()]));

// Protected endpoints (JWT Authenticated)
Route::middleware('jwt')->group(function () {
    Route::get('/user', [ApiController::class, 'user'])->name('api.auth.me');
    Route::get('/auth/me', [ApiController::class, 'user']);
    Route::match(['post', 'put', 'patch'], '/user/bank-account', [ApiController::class, 'updateBankAccount']);
    Route::post('/logout', [ApiController::class, 'logout'])->name('api.auth.logout');
    Route::post('/auth/logout', [ApiController::class, 'logout']);

    Route::get('/dashboard', [ApiActionController::class, 'dashboard'])->name('api.dashboard.index');

    // Leads actions
    Route::post('/leads', fn (Request $request) => app(ApiController::class)->store($request, 'leads'))->name('api.leads.store');
    Route::get('/leads/{id}', fn ($id) => app(ApiController::class)->show('leads', (int) $id))->name('api.leads.show');
    Route::post('/leads/{lead}/claim', [ApiActionController::class, 'claim']);
    Route::patch('/leads/{lead}/status', [ApiActionController::class, 'status'])->name('api.leads.update-status');
    Route::post('/leads/{lead}/activities', [ApiActionController::class, 'activity']);
    Route::post('/leads/import', [ApiActionController::class, 'importLeads']);
    Route::post('/lead-imports', [ApiActionController::class, 'importLeads']);
    Route::post('/leads/{lead}/remind-sales', [ApiActionController::class, 'createReminder'])
        ->middleware('admin')
        ->name('leads.remind-sales');

    // Products & Customers
    Route::post('/products', fn (Request $request) => app(ApiController::class)->store($request, 'products'))->name('api.products.store');
    Route::post('/customers', fn (Request $request) => app(ApiController::class)->store($request, 'customers'))->name('api.customers.store');
    Route::post('/customers/import', [ApiActionController::class, 'importCustomers'])
        ->middleware('admin');
    Route::get('/customers/export', [CustomerController::class, 'exportCsv'])
        ->middleware('admin');
    Route::get('/customers/template', [CustomerController::class, 'downloadTemplate'])
        ->middleware('admin');

    // Follow-ups & Reminders
    Route::post('/follow-ups', fn (Request $request) => app(ApiController::class)->store($request, 'follow-ups'))->name('follow-ups.store');
    Route::patch('/follow-ups/{followUp}/complete', [ApiActionController::class, 'completeFollowUp']);
    Route::patch('/follow-ups/{followUp}/cancel', [ApiActionController::class, 'cancelFollowUp']);
    Route::get('/reminders', fn (Request $request) => app(ApiController::class)->index($request, 'reminders'))->name('reminders.index');
    Route::post('/reminders/mark-all-read', [ApiActionController::class, 'readAllReminders'])->name('reminders.mark-all-read');
    Route::patch('/reminders/{reminder}/read', [ApiActionController::class, 'readReminder']);
    Route::patch('/reminders/{reminder}/complete', [ApiActionController::class, 'completeReminder'])->name('reminders.complete');

    // Payments & Commissions
    Route::post('/payments', [ApiActionController::class, 'storePayment']);
    Route::patch('/payments/{payment}/verify', [ApiActionController::class, 'verifyPayment'])
        ->middleware('admin');
    Route::patch('/commissions/{commission}/approve', [ApiActionController::class, 'approveCommission'])
        ->middleware('admin');
    Route::patch('/commissions/{commission}/pay', [ApiActionController::class, 'payCommission'])
        ->middleware('admin')
        ->name('commissions.pay');

    Route::get('/activities', [ApiController::class, 'activities']);

    // Generic REST routes for all CRM resources
    Route::get('/{resource}', [ApiController::class, 'index'])
        ->where('resource', '[a-z-]+');
    Route::post('/{resource}', [ApiController::class, 'store'])
        ->where('resource', '[a-z-]+');
    Route::get('/{resource}/{id}', [ApiController::class, 'show'])
        ->where(['resource' => '[a-z-]+', 'id' => '[0-9]+']);
    Route::match(['put', 'patch'], '/{resource}/{id}', [ApiController::class, 'update'])
        ->where(['resource' => '[a-z-]+', 'id' => '[0-9]+']);
    Route::delete('/{resource}/{id}', [ApiController::class, 'destroy'])
        ->where(['resource' => '[a-z-]+', 'id' => '[0-9]+']);
});
