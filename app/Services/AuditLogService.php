<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public function logAuthEvent(string $event, array $context = []): void
    {
        $this->log('auth', $event, $context);
    }

    public function logUserAction(string $action, array $context = []): void
    {
        $this->log('user_action', $action, $context);
    }

    public function logAdminAction(string $action, array $context = []): void
    {
        $this->log('admin_action', $action, $context);
    }

    public function logFinancialEvent(string $event, array $context = []): void
    {
        $this->log('financial', $event, $context);
    }

    public function logSecurityEvent(string $event, array $context = []): void
    {
        $this->log('security', $event, array_merge([
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ], $context));
    }

    public function logFailedLogin(string $identifier, string $reason): void
    {
        $this->logSecurityEvent('failed_login', [
            'identifier' => $identifier,
            'reason' => $reason,
        ]);
    }

    public function logSuccessfulLogin(int $userId, string $method = 'password'): void
    {
        $this->logAuthEvent('login_success', [
            'user_id' => $userId,
            'method' => $method,
        ]);
    }

    public function logLogout(int $userId): void
    {
        $this->logAuthEvent('logout', [
            'user_id' => $userId,
        ]);
    }

    public function logPasswordChange(int $userId, bool $selfChange = true): void
    {
        $this->logAuthEvent('password_changed', [
            'user_id' => $userId,
            'self_change' => $selfChange,
        ]);
    }

    public function logDataAccess(string $resource, int $resourceId, string $action): void
    {
        $this->log('data_access', "{$resource}.{$action}", [
            'resource_type' => $resource,
            'resource_id' => $resourceId,
        ]);
    }

    public function logOrderCreated(int $orderId, int $userId, float $amount): void
    {
        $this->logFinancialEvent('order_created', [
            'order_id' => $orderId,
            'user_id' => $userId,
            'amount' => $amount,
        ]);
    }

    public function logPayment(string $event, array $context): void
    {
        $this->logFinancialEvent("payment.{$event}", $context);
    }

    protected function log(string $type, string $event, array $context = []): void
    {
        $userId = auth()->id();
        $action = "{$type}:{$event}";

        $base = [
            'user_id' => $userId,
            'user_type' => $userId ? get_class(auth()->user()) : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'request_id' => Request::header('X-Request-ID'),
        ];

        $merged = array_merge($base, $context);

        Log::channel('audit')->info($action, $merged);

        try {
            AuditLog::create([
                'user_id'    => $userId,
                'action'     => $action,
                'model_type' => $context['model_type'] ?? $context['resource_type'] ?? null,
                'model_id'   => $context['model_id'] ?? $context['resource_id'] ?? null,
                'old_values' => $context['old_values'] ?? null,
                'new_values' => $context['new_values'] ?? null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Do not break the request if audit DB write fails
        }
    }
}
