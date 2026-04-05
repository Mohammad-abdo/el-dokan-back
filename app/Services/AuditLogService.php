<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an authentication event
     */
    public function logAuthEvent(string $event, array $context = []): void
    {
        $this->log('auth', $event, $context);
    }

    /**
     * Log a user action
     */
    public function logUserAction(string $action, array $context = []): void
    {
        $this->log('user_action', $action, $context);
    }

    /**
     * Log an admin action
     */
    public function logAdminAction(string $action, array $context = []): void
    {
        $this->log('admin_action', $action, $context);
    }

    /**
     * Log a financial transaction
     */
    public function logFinancialEvent(string $event, array $context = []): void
    {
        $this->log('financial', $event, $context);
    }

    /**
     * Log a security event
     */
    public function logSecurityEvent(string $event, array $context = []): void
    {
        $this->log('security', $event, array_merge([
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ], $context));
    }

    /**
     * Log a failed login attempt
     */
    public function logFailedLogin(string $email, string $reason): void
    {
        $this->logSecurityEvent('failed_login', [
            'email' => $email,
            'reason' => $reason,
        ]);
    }

    /**
     * Log a successful login
     */
    public function logSuccessfulLogin(int $userId, string $method = 'password'): void
    {
        $this->logAuthEvent('login_success', [
            'user_id' => $userId,
            'method' => $method,
        ]);
    }

    /**
     * Log a logout
     */
    public function logLogout(int $userId): void
    {
        $this->logAuthEvent('logout', [
            'user_id' => $userId,
        ]);
    }

    /**
     * Log a password change
     */
    public function logPasswordChange(int $userId, bool $selfChange = true): void
    {
        $this->logAuthEvent('password_changed', [
            'user_id' => $userId,
            'self_change' => $selfChange,
        ]);
    }

    /**
     * Log sensitive data access
     */
    public function logDataAccess(string $resource, int $resourceId, string $action): void
    {
        $this->log('data_access', "{$resource}.{$action}", [
            'resource_type' => $resource,
            'resource_id' => $resourceId,
        ]);
    }

    /**
     * Log order creation
     */
    public function logOrderCreated(int $orderId, int $userId, float $amount): void
    {
        $this->logFinancialEvent('order_created', [
            'order_id' => $orderId,
            'user_id' => $userId,
            'amount' => $amount,
        ]);
    }

    /**
     * Log payment event
     */
    public function logPayment(string $event, array $context): void
    {
        $this->logFinancialEvent("payment.{$event}", $context);
    }

    /**
     * Generic log method
     */
    protected function log(string $type, string $event, array $context = []): void
    {
        $userId = auth()->id();

        Log::channel('audit')->info("{$type}:{$event}", array_merge([
            'user_id' => $userId,
            'user_type' => $userId ? get_class(auth()->user()) : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'request_id' => Request::header('X-Request-ID'),
        ], $context));
    }
}
