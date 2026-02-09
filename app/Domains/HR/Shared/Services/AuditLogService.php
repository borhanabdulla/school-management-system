<?php

namespace App\Domains\HR\Shared\Services;

use App\Domains\Shared\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * تسجيل إنشاء سجل جديد
     */
    public function logCreated(Model $model, ?string $reason = null): AuditLog
    {
        return $this->log($model, 'created', null, $model->toArray(), $reason);
    }

    /**
     * تسجيل تعديل سجل
     * @param Model $model - النموذج بعد التعديل
     * @param array $oldValues - القيم القديمة
     */
    public function logUpdated(Model $model, array $oldValues, ?string $reason = null): AuditLog
    {
        return $this->log($model, 'updated', $oldValues, $model->toArray(), $reason);
    }

    /**
     * تسجيل حذف سجل
     */
    public function logDeleted(Model $model, ?string $reason = null): AuditLog
    {
        return $this->log($model, 'deleted', $model->toArray(), null, $reason);
    }

    /**
     * التسجيل الأساسي
     */
    private function log(Model $model, string $action, ?array $oldValues, ?array $newValues, ?string $reason): AuditLog
    {
        return AuditLog::create([
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => Auth::id(),
            'reason' => $reason,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * جلب سجل التدقيق لنموذج معين
     */
    public function getLogsFor(Model $model, int $limit = 50)
    {
        return AuditLog::forModel(get_class($model), $model->getKey())
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * جلب سجل التدقيق لجدول معين
     */
    public function getLogsForTable(string $modelClass, int $limit = 100)
    {
        return AuditLog::forModel($modelClass)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * جلب آخر التغييرات لمستخدم معين
     */
    public function getLogsByUser(int $userId, int $limit = 50)
    {
        return AuditLog::byUser($userId)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
