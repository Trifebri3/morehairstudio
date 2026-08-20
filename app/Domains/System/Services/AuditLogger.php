<?php

namespace App\Domains\System\Services;

use App\Domains\System\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Record a critical system action.
     */
    public static function log(string $action, string $modelType = null, int $modelId = null, array $oldValues = null, array $newValues = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip()
        ]);
    }
}
