<?php

namespace App\Modules\Shared\Audit\Infrastructure\Traits;

use App\Modules\Shared\Audit\Domain\Entities\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            self::logAudit($model, 'created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $original = $model->getOriginal();
            $changed = $model->getChanges();

            if (empty($changed)) {
                return;
            }

            $oldValues = array_intersect_key($original, $changed);
            unset($changed['updated_at'], $oldValues['updated_at']);

            if (empty($changed)) {
                return;
            }

            self::logAudit($model, 'updated', $oldValues, $changed);
        });

        static::deleted(function ($model) {
            self::logAudit($model, 'deleted', $model->getAttributes(), null);
        });
    }

    protected static function logAudit($model, string $action, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $model->getMorphClass(),
            'entity_id' => (string) $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
