<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\ApiActionController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [ApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [ApiController::class, 'user']);
    Route::post('/logout', [ApiController::class, 'logout']);
    Route::get('/dashboard', [ApiActionController::class, 'dashboard']);
    Route::post('/leads/{lead}/claim', [ApiActionController::class, 'claim']);
    Route::patch('/leads/{lead}/status', [ApiActionController::class, 'status']);
    Route::post('/leads/{lead}/activities', [ApiActionController::class, 'activity']);
    Route::post('/leads/import', [ApiActionController::class, 'importLeads'])
        ->middleware('admin');
    Route::get('/leads/template', [\App\Http\Controllers\LeadController::class, 'downloadTemplate']);
    Route::patch('/follow-ups/{followUp}/complete', [ApiActionController::class, 'completeFollowUp']);
    Route::patch('/follow-ups/{followUp}/cancel', [ApiActionController::class, 'cancelFollowUp']);
    Route::post('/payments', [ApiActionController::class, 'storePayment']);
    Route::patch('/payments/{payment}/verify', [ApiActionController::class, 'verifyPayment'])
        ->middleware('admin');
    Route::patch('/commissions/{commission}/approve', [ApiActionController::class, 'approveCommission'])
        ->middleware('admin');
    Route::patch('/commissions/{commission}/pay', [ApiActionController::class, 'payCommission'])
        ->middleware('admin');
    Route::post('/leads/{lead}/remind-sales', [ApiActionController::class, 'createReminder'])
        ->middleware('admin');
    Route::post('/reminders/mark-all-read', [ApiActionController::class, 'readAllReminders']);
    Route::patch('/reminders/{reminder}/read', [ApiActionController::class, 'readReminder']);
    Route::patch('/reminders/{reminder}/complete', [ApiActionController::class, 'completeReminder']);
    Route::get('/activities', [ApiController::class, 'activities']);
    Route::post('/customers/import', [ApiActionController::class, 'importCustomers'])
        ->middleware('admin');
    Route::get('/customers/export', [\App\Http\Controllers\CustomerController::class, 'exportCsv'])
        ->middleware('admin');
    Route::get('/customers/template', [\App\Http\Controllers\CustomerController::class, 'downloadTemplate'])
        ->middleware('admin');
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
