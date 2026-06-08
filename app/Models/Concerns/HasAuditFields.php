<?php

namespace App\Models\Concerns;

trait HasAuditFields
{
    public static function bootHasAuditFields(): void
    {
        static::creating(function ($model): void {
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model): void {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
