<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\User;
use App\Support\Jwt;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TestApiEndpointsCommand extends Command
{
    protected $signature = 'api:test';
    protected $description = 'Menjalankan pengujian otomatis ke seluruh rute REST API CRM dengan token JWT';

    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('   PENGUJIAN OTOMATIS SELURUH REST API - CRM DISTY AKADEMI');
        $this->info('================================================================');

        $admin = User::where('role', 'admin')->where('is_active', true)->first();
        if (! $admin) {
            $admin = User::create([
                'name' => 'Admin Tester',
                'email' => 'admin.tester@distyakademi.com',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]);
        }

        $sales = User::where('role', 'sales')->where('is_active', true)->first();
        if (! $sales) {
            $sales = User::create([
                'name' => 'Sales Tester',
                'email' => 'sales.tester@distyakademi.com',
                'password' => bcrypt('password123'),
                'role' => 'sales',
                'is_active' => true,
            ]);
        }

        $secret = config('jwt.rahasia', 'default-jwt-secret');
        $adminToken = Jwt::buat([
            'sub' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'admin',
        ], $secret, 3600);

        $salesToken = Jwt::buat([
            'sub' => $sales->id,
            'name' => $sales->name,
            'email' => $sales->email,
            'role' => 'sales',
        ], $secret, 3600);

        $lead = Lead::first();
        if (! $lead) {
            $lead = Lead::create([
                'lead_code' => 'LDA-TEST-001',
                'name' => 'Lead Tester API',
                'phone' => '081234567890',
                'status' => 'new',
                'assigned_to' => $sales->id,
            ]);
        }

        $endpoints = [
            // [Method, URI, Body, Token, ExpectedStatuses, Keterangan]
            ['GET', '/api/csrf-token', [], null, [200], 'CSRF Token Public'],
            ['GET', '/api/leads/template', [], null, [200], 'Download Template CSV Lead'],
            ['GET', '/api/leads/template-excel', [], null, [200], 'Download Template Excel Lead'],
            ['POST', '/api/login', ['email' => $admin->email, 'password' => 'password123'], null, [200, 422], 'Login Validasi Kredensial'],
            ['GET', '/api/user', [], $salesToken, [200], 'Profil User (Sales Token)'],
            ['GET', '/api/user', [], $adminToken, [200], 'Profil User (Admin Token)'],
            ['GET', '/api/user', [], null, [401], 'Cek Proteksi (Tanpa Token ditolak 401)'],
            ['GET', '/api/user', [], 'token.rusak.palsu', [401], 'Cek Proteksi (Token Palsu ditolak 401)'],
            ['POST', '/api/user/bank-account', ['bank_name' => 'BCA', 'bank_account_number' => '123456789', 'bank_account_name' => 'Tester'], $salesToken, [200], 'Update Rekening Bank Komisi'],
            ['GET', '/api/dashboard', [], $salesToken, [200], 'Dashboard Metrik Sales'],
            ['GET', '/api/dashboard', [], $adminToken, [200], 'Dashboard Metrik Admin'],
            ['GET', '/api/leads', [], $salesToken, [200], 'Daftar Leads (Pagination & Search)'],
            ['GET', "/api/leads/{$lead->id}", [], $salesToken, [200], 'Detail Lead Tertentu'],
            ['PATCH', "/api/leads/{$lead->id}/status", ['status' => 'contacted'], $salesToken, [200], 'Ubah Status Funnel Lead'],
            ['POST', "/api/leads/{$lead->id}/activities", ['type' => 'note', 'subject' => 'Catatan tes API', 'activity_at' => now()->toDateTimeString()], $salesToken, [200], 'Catat Log Aktivitas Lead'],
            ['POST', "/api/leads/{$lead->id}/claim", [], $salesToken, [200, 422], 'Klaim Lead oleh Sales'],
            ['GET', '/api/reminders', [], $salesToken, [200], 'Daftar Pengingat / Notifikasi Sales'],
            ['POST', '/api/reminders/mark-all-read', [], $salesToken, [200], 'Tandai Semua Pengingat Dibaca'],
            ['GET', '/api/customers', [], $salesToken, [200], 'Daftar Customer Terdaftar'],
            ['GET', '/api/customers/export', [], $adminToken, [200], 'Ekspor CSV Customer (Admin)'],
            ['GET', '/api/customers/template', [], $adminToken, [200], 'Unduh Template Customer (Admin)'],
            ['GET', '/api/activities', [], $salesToken, [200], 'Histori Audit Log Aktivitas'],
            ['POST', '/api/logout', [], $salesToken, [200], 'Logout Stateless'],
            // Dynamic Resources
            ['GET', '/api/products', [], $adminToken, [200], 'Resource Master: Products'],
            ['GET', '/api/institutions', [], $adminToken, [200], 'Resource Master: Institutions'],
            ['GET', '/api/trainers', [], $adminToken, [200], 'Resource Master: Trainers'],
            ['GET', '/api/training-schedules', [], $adminToken, [200], 'Resource Master: Training Schedules'],
            ['GET', '/api/lead-sources', [], $adminToken, [200], 'Resource Master: Lead Sources'],
            ['GET', '/api/rejection-reasons', [], $adminToken, [200], 'Resource Master: Rejection Reasons'],
            ['GET', '/api/registrations', [], $adminToken, [200], 'Resource Master: Registrations'],
            ['GET', '/api/commissions', [], $adminToken, [200], 'Resource Master: Commissions'],
            ['GET', '/api/payments', [], $adminToken, [200], 'Resource Master: Payments'],
            ['GET', '/api/users', [], $adminToken, [200], 'Resource Master: Users'],
        ];

        $tableRows = [];
        $passedCount = 0;
        $failedCount = 0;

        foreach ($endpoints as $item) {
            [$method, $uri, $body, $token, $expectedStatuses, $desc] = $item;

            // Reset auth state antar iterasi agar tidak bocor dari request sebelumnya
            auth()->forgetGuards();

            $headers = [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            ];

            if ($token) {
                $headers['HTTP_AUTHORIZATION'] = "Bearer {$token}";
            }

            $start = microtime(true);
            $request = Request::create(
                $uri,
                $method,
                [],
                [],
                [],
                $headers,
                $body ? json_encode($body) : null
            );

            $response = app()->handle($request);
            $durationMs = round((microtime(true) - $start) * 1000, 1);
            $statusCode = $response->getStatusCode();

            $isSuccess = in_array($statusCode, $expectedStatuses);
            if ($isSuccess) {
                $passedCount++;
                $statusBadge = "<info>[PASSED]</info> {$statusCode}";
            } else {
                $failedCount++;
                $statusBadge = "<error>[FAILED] {$statusCode}</error>";
            }

            $tableRows[] = [
                $method,
                $uri,
                $token ? 'Bearer JWT' : 'Public',
                $statusBadge,
                "{$durationMs} ms",
                $desc,
            ];
        }

        $this->table(
            ['Method', 'Endpoint URI', 'Auth', 'Status Code', 'Waktu', 'Keterangan'],
            $tableRows
        );

        $this->newLine();
        $this->info("Ringkasan Hasil Pengujian:");
        $this->info("- Total Endpoint Diuji : ".count($endpoints));
        $this->info("- Berhasil (PASSED)    : {$passedCount}");
        $this->line("- Gagal (FAILED)       : ".($failedCount > 0 ? "<error>{$failedCount}</error>" : "0"));
        $this->info("================================================================");

        return $failedCount === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
