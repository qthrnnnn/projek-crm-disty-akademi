<?php

namespace App\Support;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class CrmResourceRules
{
    private const MONEY = 'numeric|between:0,9999999999999.99';

    public static function for(string $resource, ?Model $record = null): array
    {
        $required = $record ? 'filled' : 'required';
        $defaulted = $record ? 'filled' : 'nullable';
        $leadStatus = Rule::in(array_keys(Lead::STATUS_LABELS));

        return match ($resource) {
            'activities' => [
                'lead_id' => [$required, 'integer', Rule::exists('leads', 'id')->withoutTrashed()],
                'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->withoutTrashed()],
                'type' => $required.'|in:call,email,whatsapp,meeting,note,status_change,assigned',
                'subject' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'activity_at' => $required.'|date',
            ],
            'commissions' => [
                'user_id' => [$required, 'integer', Rule::exists('users', 'id')->withoutTrashed()],
                'lead_id' => [$required, 'integer', Rule::exists('leads', 'id')->withoutTrashed()],
                'payment_id' => 'nullable|integer|exists:payments,id',
                'deal_amount' => $required.'|'.self::MONEY,
                'commission_rate' => 'filled|numeric|between:0,100',
                'commission_amount' => 'filled|'.self::MONEY,
                'bank_name' => 'nullable|string|max:50',
                'bank_account_number' => 'nullable|string|max:50',
                'bank_account_name' => 'nullable|string|max:150',
                'status' => 'filled|in:pending,approved,paid,cancelled',
                'approved_at' => 'nullable|date',
                'paid_at' => 'nullable|date',
                'notes' => 'nullable|string',
            ],
            'customers' => [
                'customer_code' => [$defaulted, 'string', 'max:50', self::unique('customers', 'customer_code', $record)],
                'name' => $required.'|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'nik' => 'nullable|string|max:30',
                'birth_date' => 'nullable|date',
                'document_number' => 'nullable|string|max:100',
                'institution_id' => 'nullable|integer|exists:institutions,id',
                'lead_id' => ['nullable', 'integer', Rule::exists('leads', 'id')->withoutTrashed()],
                'company' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'province' => 'nullable|string|max:100',
                'status' => 'filled|in:active,inactive,prospect',
                'source' => 'filled|string|max:50',
                'notes' => 'nullable|string',
            ],
            'follow-ups' => [
                'lead_id' => [$required, 'integer', Rule::exists('leads', 'id')->withoutTrashed()],
                'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')->withoutTrashed()],
                'title' => $defaulted.'|string|max:255',
                'description' => 'nullable|string',
                'follow_up_at' => $defaulted.'|date',
                'method' => $defaulted.'|in:whatsapp,call,email,meeting',
                'priority' => $defaulted.'|in:low,normal,high,urgent',
                'status' => $defaulted.'|in:pending,completed,cancelled',
                'result_notes' => 'nullable|string',
                'completed_at' => 'nullable|date',
                'type' => 'nullable|in:whatsapp,call,email,meeting',
                'scheduled_at' => 'nullable|date',
                'notes' => 'nullable|string',
                ...($record ? [
                    'lead_status' => ['nullable', $leadStatus],
                    'rejection_reason_id' => 'nullable|integer|exists:rejection_reasons,id',
                    'feedback_type' => 'nullable|string|max:255',
                ] : []),
            ],
            'import-batches' => [
                'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->withoutTrashed()],
                'file_name' => $required.'|string|max:255',
                'file_path' => $required.'|string|max:255',
                'total_rows' => 'filled|integer|between:0,4294967295',
                'success_rows' => 'filled|integer|between:0,4294967295',
                'failed_rows' => 'filled|integer|between:0,4294967295',
                'status' => 'filled|in:pending,validating,validated,executing,completed,failed',
            ],
            'institutions' => [
                'name' => $required.'|string|max:255',
                'type' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:100',
                'province' => 'nullable|string|max:100',
                'industry' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
            ],
            'lead-sources', 'rejection-reasons' => [
                'name' => [$required, 'string', 'max:255', self::unique(str_replace('-', '_', $resource), 'name', $record)],
                'label' => $required.'|string|max:255',
                'description' => 'nullable|string',
                'is_active' => 'filled|boolean',
            ],
            'leads' => [
                'lead_code' => [$defaulted, 'string', 'max:50', self::unique('leads', 'lead_code', $record)],
                'name' => $required.'|string|max:255',
                'nik' => 'nullable|string|max:30',
                'birth_date' => 'nullable|date',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'whatsapp' => 'nullable|string|max:50',
                'company' => 'nullable|string|max:255',
                'institution' => 'nullable|string|max:255',
                'job_title' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'province' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'lead_source_id' => 'nullable|integer|exists:lead_sources,id',
                'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')->withoutTrashed()],
                'deal_price' => 'nullable|'.self::MONEY,
                'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')->withoutTrashed()],
                'assigned_at' => 'nullable|date',
                'status' => [$defaulted, $leadStatus],
                'rejection_reason_id' => 'nullable|integer|exists:rejection_reasons,id',
                'rejection_notes' => 'nullable|string',
                'lead_score' => 'filled|integer|between:0,4294967295',
                'priority' => 'filled|in:low,normal,high,urgent',
                'first_contacted_at' => 'nullable|date',
                'last_contacted_at' => 'nullable|date',
                'converted_at' => 'nullable|date',
                'lost_at' => 'nullable|date',
                'notes' => 'nullable|string',
            ],
            'payments' => [
                'lead_id' => [$record ? 'filled' : 'required_without:customer_id', 'nullable', 'integer', Rule::exists('leads', 'id')->withoutTrashed()],
                'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')->withoutTrashed()],
                'verified_by' => ['nullable', 'integer', Rule::exists('users', 'id')->withoutTrashed()],
                'amount' => 'filled|'.self::MONEY,
                'payment_method' => 'nullable|string|max:50',
                'bank_name' => 'nullable|string|max:100',
                'account_number' => 'nullable|string|max:100',
                'account_name' => 'nullable|string|max:255',
                'proof_file' => $record ? 'nullable|string|max:255' : 'nullable|file|max:5120',
                'payment_date' => 'nullable|date',
                'status' => 'filled|in:pending,valid,invalid,recheck',
                'verification_notes' => 'nullable|string',
                'verified_at' => 'nullable|date',
                'notes' => 'nullable|string',
                ...($record ? [] : [
                    'customer_id' => ['nullable', 'integer', Rule::exists('customers', 'id')->withoutTrashed()],
                    'sender_name' => 'nullable|string|max:255',
                    'proof' => 'nullable|file|max:5120',
                ]),
            ],
            'products' => [
                'name' => $required.'|string|max:255',
                'code' => [$required, 'string', 'max:50', self::unique('products', 'code', $record)],
                'description' => 'nullable|string',
                'category' => 'nullable|string|max:100',
                'product_type' => 'filled|in:training,certification,bundle',
                'price' => 'filled|'.self::MONEY,
                'duration_hours' => 'nullable|integer|between:0,4294967295',
                'is_active' => 'filled|boolean',
            ],
            'registrations' => [
                'registration_number' => [$defaulted, 'string', 'max:100', self::unique('registrations', 'registration_number', $record)],
                'customer_id' => [
                    $record ? 'filled' : 'required_without_all:lead_id,participant_name',
                    'nullable', 'integer', Rule::exists('customers', 'id')->withoutTrashed(),
                ],
                'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->withoutTrashed()],
                'product_id' => [
                    $record ? 'filled' : 'required_without:training_schedule_id',
                    'nullable', 'integer', Rule::exists('products', 'id')->withoutTrashed(),
                ],
                'training_schedule_id' => 'nullable|integer|exists:training_schedules,id',
                'training_date' => 'nullable|date',
                'status' => 'filled|in:registered,confirmed,attended,completed,cancelled',
                'amount' => $defaulted.'|'.self::MONEY,
                'notes' => 'nullable|string',
                ...($record ? [] : [
                    'lead_id' => ['nullable', 'integer', Rule::exists('leads', 'id')->withoutTrashed()],
                    'participant_mode' => 'nullable|string|max:30',
                    'participant_name' => 'required_if:participant_mode,manual|nullable|string|max:255',
                    'participant_phone' => 'nullable|string|max:50',
                    'participant_nik' => 'nullable|string|max:30',
                    'participant_birth_date' => 'nullable|date',
                    'participant_company' => 'nullable|string|max:255',
                ]),
            ],
            'reminders' => [
                'lead_id' => [$required, 'integer', Rule::exists('leads', 'id')->withoutTrashed()],
                'commission_id' => 'nullable|integer|exists:commissions,id',
                'admin_id' => [$required, 'integer', Rule::exists('users', 'id')->withoutTrashed()],
                'sales_id' => [$required, 'integer', Rule::exists('users', 'id')->withoutTrashed()],
                'type' => 'filled|in:follow_up,commission_paid',
                'message' => $required.'|string',
                'priority' => 'filled|in:low,normal,high,urgent',
                'is_read' => 'filled|boolean',
                'read_at' => 'nullable|date',
                'is_completed' => 'filled|boolean',
                'completed_at' => 'nullable|date',
                'sales_reply' => 'nullable|string',
            ],
            'trainers' => [
                'name' => $required.'|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'notes' => 'nullable|string',
                'is_active' => 'filled|boolean',
            ],
            'training-schedules' => [
                'product_id' => [$required, 'integer', Rule::exists('products', 'id')->withoutTrashed()],
                'trainer_id' => $required.'|integer|exists:trainers,id',
                'training_date' => $required.'|date',
                'start_time' => $required.'|date_format:H:i,H:i:s',
                'end_time' => $required.'|date_format:H:i,H:i:s',
                'location' => 'nullable|string|max:255',
                'capacity' => 'filled|integer|between:0,4294967295',
                'status' => 'filled|in:scheduled,ongoing,completed,cancelled',
                'notes' => 'nullable|string',
            ],
            'users' => [
                'name' => $required.'|string|max:255',
                'email' => [$required, 'email', 'max:255', self::unique('users', 'email', $record)],
                'password' => $required.'|string|min:6|max:72',
                'role' => 'filled|in:admin,manager,sales',
                'phone' => 'nullable|string|max:50',
                'is_active' => 'filled|boolean',
                'bank_name' => 'nullable|string|max:50',
                'bank_account_number' => 'nullable|string|max:50',
                'bank_account_name' => 'nullable|string|max:150',
            ],
        };
    }

    private static function unique(string $table, string $column, ?Model $record): Unique
    {
        $rule = Rule::unique($table, $column);

        return $record ? $rule->ignore($record) : $rule;
    }
}
