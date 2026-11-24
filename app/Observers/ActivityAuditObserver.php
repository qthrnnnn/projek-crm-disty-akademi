<?php

namespace App\Observers;

use App\Support\ActivityAudit;
use Illuminate\Database\Eloquent\Model;

class ActivityAuditObserver
{
    public function created(Model $model): void
    {
        app(ActivityAudit::class)->record($model, 'created');
    }

    public function updated(Model $model): void
    {
        app(ActivityAudit::class)->record($model, 'updated', $model->getRawOriginal());
    }

    public function deleted(Model $model): void
    {
        app(ActivityAudit::class)->record($model, 'deleted', $model->getAttributes());
    }

    public function restored(Model $model): void
    {
        app(ActivityAudit::class)->record($model, 'restored');
    }
}
