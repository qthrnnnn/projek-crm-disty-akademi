<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ActivityAudit
{
    public const MODULES = [
        'Lead' => ['leads', 'Lead'],
        'Customer' => ['customers', 'Customer'],
        'Institution' => ['institutions', 'Instansi'],
        'Product' => ['products', 'Program pelatihan'],
        'Trainer' => ['trainers', 'Trainer'],
        'TrainingSchedule' => ['training-schedules', 'Jadwal pelatihan'],
        'Registration' => ['registrations', 'Pendaftaran peserta'],
        'Payment' => ['payments', 'Pembayaran'],
        'Commission' => ['commissions', 'Komisi'],
        'FollowUp' => ['follow-ups', 'Follow-up'],
        'SalesReminder' => ['reminders', 'Pengingat'],
        'User' => ['users', 'Pengguna'],
        'LeadSource' => ['lead-sources', 'Sumber lead'],
        'RejectionReason' => ['rejection-reasons', 'Alasan penolakan'],
        'ImportBatch' => ['import-batches', 'Impor data'],
        'ImportRow' => ['import-rows', 'Baris impor'],
    ];

    private const IGNORED = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'];
    private const PRIVATE_FIELDS = ['password', 'token', 'secret', 'bank_account_number', 'account_number', 'nik', 'document_number', 'data'];

    public function record(Model $model, string $event, array $before = []): void
    {
        // Migrations/seeding may run before the audit columns exist.
        if (! Schema::hasColumn('activities', 'changes')) {
            return;
        }

        [$module, $label] = self::MODULES[class_basename($model)];
        $after = $event === 'deleted' ? [] : $model->getAttributes();
        $changes = [];
        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $field) {
            if (in_array($field, self::IGNORED, true)) {
                continue;
            }
            $old = $before[$field] ?? null;
            $new = $after[$field] ?? null;
            if ($old === $new || ($old !== null && $new !== null && (string) $old === (string) $new)) {
                continue;
            }
            $private = in_array($field, self::PRIVATE_FIELDS, true) || preg_match('/password|token|secret/i', $field);
            $changes[$field] = [
                'before' => $private && $old !== null ? '[disembunyikan]' : $old,
                'after' => $private && $new !== null ? '[disembunyikan]' : $new,
            ];
        }
        if ($event === 'updated' && $changes === []) {
            return;
        }

        $attributes = $model->getAttributes();
        $leadId = $model instanceof Lead ? $model->getKey() : ($attributes['lead_id'] ?? null);
        if (! $leadId && isset($attributes['customer_id'])) {
            $leadId = Customer::withTrashed()->whereKey($attributes['customer_id'])->value('lead_id');
        }
        $lead = $leadId ? Lead::withTrashed()->find($leadId) : null;
        $name = $attributes['name'] ?? $attributes['title'] ?? $attributes['registration_number']
            ?? $attributes['payment_code'] ?? $attributes['file_name'] ?? $label.' #'.$model->getKey();
        $actor = auth()->user();
        $verb = ['created' => 'ditambahkan', 'updated' => 'diubah', 'deleted' => 'dihapus', 'restored' => 'dipulihkan'][$event];

        Activity::create([
            'lead_id' => $lead?->id,
            'lead_name' => $lead?->name ?? ($model instanceof Lead ? $model->name : null),
            'user_id' => $actor && User::withTrashed()->whereKey($actor->id)->exists() ? $actor->id : null,
            'actor_name' => $actor?->name ?? 'Sistem',
            'owner_id' => $lead?->assigned_to ?? $attributes['sales_id'] ?? $attributes['assigned_to'] ?? $attributes['user_id'] ?? null,
            'type' => $event,
            'subject' => $model instanceof Lead && $event === 'created' ? 'Lead baru ditambahkan' : $label.' '.$verb,
            'description' => $label.' "'.$name.'" '.$verb.'.',
            'entity_type' => $module,
            'entity_id' => $model->getKey(),
            'entity_name' => mb_substr($name, 0, 255),
            'changes' => $changes,
            'activity_at' => now(),
        ]);
    }

    public function updateQuery(Builder $query, array $values): int
    {
        return DB::transaction(function () use ($query, $values) {
            $records = (clone $query)->lockForUpdate()->get();
            if ($records->isEmpty()) {
                return 0;
            }
            // Retain the original predicates, including optimistic concurrency checks.
            $count = $query->whereKey($records->modelKeys())->update($values);
            foreach ($records as $record) {
                $before = $record->getAttributes();
                $record->refresh();
                $this->record($record, 'updated', $before);
            }

            return $count;
        });
    }

    public function deleteQuery(Builder $query): int
    {
        return DB::transaction(function () use ($query) {
            $records = (clone $query)->lockForUpdate()->get();
            foreach ($records as $record) {
                $record->delete();
            }

            return $records->count();
        });
    }
}
