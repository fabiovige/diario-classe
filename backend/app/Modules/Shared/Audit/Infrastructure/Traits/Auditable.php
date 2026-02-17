<?php

namespace App\Modules\Shared\Audit\Infrastructure\Traits;

use App\Modules\Shared\Audit\Domain\Entities\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            self::logAudit($model, 'created', null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
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

        static::deleted(function (Model $model) {
            self::logAudit($model, 'deleted', $model->getAttributes(), null);
        });
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    protected static function logAudit(Model $model, string $action, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $model->getMorphClass(),
            'entity_id' => (string) $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
