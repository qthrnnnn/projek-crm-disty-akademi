<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\SalesReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiActionController extends Controller
{
    public function claim(Lead $lead): JsonResponse
    {
        return $this->run(LeadController::class, 'claim', [$lead], $lead);
    }

    public function status(Request $request, Lead $lead): JsonResponse
    {
        return $this->run(LeadController::class, 'updateStatus', [$request, $lead], $lead);
    }

    public function activity(Request $request, Lead $lead): JsonResponse
    {
        return $this->run(ActivityController::class, 'store', [$request, $lead], $lead);
    }

    public function completeFollowUp(Request $request, FollowUp $followUp): JsonResponse
    {
        return $this->run(FollowUpController::class, 'complete', [$request, $followUp], $followUp);
    }

    public function cancelFollowUp(FollowUp $followUp): JsonResponse
    {
        return $this->run(FollowUpController::class, 'cancel', [$followUp], $followUp);
    }

    public function storePayment(Request $request): JsonResponse
    {
        return $this->run(PaymentController::class, 'store', [$request]);
    }

    public function verifyPayment(Request $request, Payment $payment): JsonResponse
    {
        return $this->run(PaymentController::class, 'verify', [$request, $payment], $payment);
    }

    public function approveCommission(Commission $commission): JsonResponse
    {
        return $this->run(CommissionController::class, 'approve', [$commission], $commission);
    }

    public function payCommission(Request $request, Commission $commission): JsonResponse
    {
        return $this->run(CommissionController::class, 'markPaid', [$request, $commission], $commission);
    }

    public function createReminder(Request $request, Lead $lead): JsonResponse
    {
        return $this->run(SalesReminderController::class, 'store', [$request, $lead]);
    }

    public function readReminder(SalesReminder $reminder): JsonResponse
    {
        return $this->run(SalesReminderController::class, 'markRead', [$reminder], $reminder);
    }

    public function completeReminder(Request $request, SalesReminder $reminder): JsonResponse
    {
        return $this->run(SalesReminderController::class, 'markCompleted', [$request, $reminder], $reminder);
    }

    public function readAllReminders(Request $request): JsonResponse
    {
        return $this->run(SalesReminderController::class, 'markAllRead', [$request]);
    }

    public function importLeads(Request $request): JsonResponse
    {
        return $this->run(LeadImportController::class, 'store', [$request]);
    }

    public function importCustomers(Request $request): JsonResponse
    {
        return $this->run(CustomerController::class, 'importCsv', [$request]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $response = app(DashboardController::class)->index($request);

        return response()->json([
            'success' => true,
            'data' => $response->getData(true),
        ]);
    }

    private function run(string $controller, string $method, array $arguments, ?object $resource = null): JsonResponse
    {
        $response = app($controller)->{$method}(...$arguments);

        if ($response instanceof JsonResponse) {
            return $response;
        }

        if ($response instanceof Response && ! $response instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json(['data' => $response->getContent()], $response->getStatusCode());
        }

        $message = session('success') ?: session('error') ?: 'Operasi berhasil.';

        return response()->json([
            'success' => ! session('error'),
            'message' => $message,
            'data' => $resource?->fresh(),
        ], session('error') ? 422 : 200);
    }
}
