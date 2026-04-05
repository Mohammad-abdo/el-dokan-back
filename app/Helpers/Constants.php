<?php

namespace App\Helpers;

class Constants
{
    // User statuses
    public const USER_STATUS_PENDING = 'pending';
    public const USER_STATUS_ACTIVE = 'active';
    public const USER_STATUS_SUSPENDED = 'suspended';
    public const USER_STATUSES = [
        self::USER_STATUS_PENDING,
        self::USER_STATUS_ACTIVE,
        self::USER_STATUS_SUSPENDED,
    ];

    // Order statuses
    public const ORDER_STATUS_RECEIVED = 'received';
    public const ORDER_STATUS_PROCESSING = 'processing';
    public const ORDER_STATUS_ON_THE_WAY = 'on_the_way';
    public const ORDER_STATUS_DELIVERED = 'delivered';
    public const ORDER_STATUS_CANCELLED = 'cancelled';
    public const ORDER_STATUSES = [
        self::ORDER_STATUS_RECEIVED,
        self::ORDER_STATUS_PROCESSING,
        self::ORDER_STATUS_ON_THE_WAY,
        self::ORDER_STATUS_DELIVERED,
        self::ORDER_STATUS_CANCELLED,
    ];

    // Payment methods
    public const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';
    public const PAYMENT_METHOD_E_WALLET = 'e_wallet';
    public const PAYMENT_METHOD_CASH_ON_DELIVERY = 'cash_on_delivery';
    public const PAYMENT_METHODS = [
        self::PAYMENT_METHOD_CREDIT_CARD,
        self::PAYMENT_METHOD_E_WALLET,
        self::PAYMENT_METHOD_CASH_ON_DELIVERY,
    ];

    // Payment statuses
    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_FAILED = 'failed';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';
    public const PAYMENT_STATUSES = [
        self::PAYMENT_STATUS_PENDING,
        self::PAYMENT_STATUS_PAID,
        self::PAYMENT_STATUS_FAILED,
        self::PAYMENT_STATUS_REFUNDED,
    ];

    // Doctor statuses
    public const DOCTOR_STATUS_PENDING = 'pending';
    public const DOCTOR_STATUS_ACTIVE = 'active';
    public const DOCTOR_STATUS_INACTIVE = 'inactive';
    public const DOCTOR_STATUSES = [
        self::DOCTOR_STATUS_PENDING,
        self::DOCTOR_STATUS_ACTIVE,
        self::DOCTOR_STATUS_INACTIVE,
    ];

    // Shop statuses
    public const SHOP_STATUS_PENDING = 'pending';
    public const SHOP_STATUS_ACTIVE = 'active';
    public const SHOP_STATUS_INACTIVE = 'inactive';
    public const SHOP_STATUSES = [
        self::SHOP_STATUS_PENDING,
        self::SHOP_STATUS_ACTIVE,
        self::SHOP_STATUS_INACTIVE,
    ];

    // Vendor statuses
    public const VENDOR_STATUS_PENDING = 'pending';
    public const VENDOR_STATUS_APPROVED = 'approved';
    public const VENDOR_STATUS_REJECTED = 'rejected';
    public const VENDOR_STATUSES = [
        self::VENDOR_STATUS_PENDING,
        self::VENDOR_STATUS_APPROVED,
        self::VENDOR_STATUS_REJECTED,
    ];

    // Driver statuses
    public const DRIVER_STATUS_OFFLINE = 'offline';
    public const DRIVER_STATUS_ONLINE = 'online';
    public const DRIVER_STATUS_BUSY = 'busy';
    public const DRIVER_STATUSES = [
        self::DRIVER_STATUS_OFFLINE,
        self::DRIVER_STATUS_ONLINE,
        self::DRIVER_STATUS_BUSY,
    ];

    // Delivery statuses
    public const DELIVERY_STATUS_PENDING = 'pending';
    public const DELIVERY_STATUS_ASSIGNED = 'assigned';
    public const DELIVERY_STATUS_PICKED_UP = 'picked_up';
    public const DELIVERY_STATUS_ON_WAY = 'on_way';
    public const DELIVERY_STATUS_DELIVERED = 'delivered';
    public const DELIVERY_STATUS_CANCELLED = 'cancelled';
    public const DELIVERY_STATUSES = [
        self::DELIVERY_STATUS_PENDING,
        self::DELIVERY_STATUS_ASSIGNED,
        self::DELIVERY_STATUS_PICKED_UP,
        self::DELIVERY_STATUS_ON_WAY,
        self::DELIVERY_STATUS_DELIVERED,
        self::DELIVERY_STATUS_CANCELLED,
    ];

    // Booking statuses
    public const BOOKING_STATUS_PENDING = 'pending';
    public const BOOKING_STATUS_CONFIRMED = 'confirmed';
    public const BOOKING_STATUS_COMPLETED = 'completed';
    public const BOOKING_STATUS_CANCELLED = 'cancelled';
    public const BOOKING_STATUSES = [
        self::BOOKING_STATUS_PENDING,
        self::BOOKING_STATUS_CONFIRMED,
        self::BOOKING_STATUS_COMPLETED,
        self::BOOKING_STATUS_CANCELLED,
    ];

    // Product statuses
    public const PRODUCT_STATUS_ACTIVE = 'active';
    public const PRODUCT_STATUS_INACTIVE = 'inactive';
    public const PRODUCT_STATUSES = [
        self::PRODUCT_STATUS_ACTIVE,
        self::PRODUCT_STATUS_INACTIVE,
    ];

    // OTP types
    public const OTP_TYPE_VERIFICATION = 'verification';
    public const OTP_TYPE_PASSWORD_RESET = 'password_reset';
    public const OTP_TYPE_LOGIN = 'login';
    public const OTP_TYPES = [
        self::OTP_TYPE_VERIFICATION,
        self::OTP_TYPE_PASSWORD_RESET,
        self::OTP_TYPE_LOGIN,
    ];

    // Financial transaction types
    public const TRANSACTION_TYPE_DEPOSIT = 'deposit';
    public const TRANSACTION_TYPE_WITHDRAWAL = 'withdrawal';
    public const TRANSACTION_TYPE_ORDER = 'order';
    public const TRANSACTION_TYPE_REFUND = 'refund';
    public const TRANSACTION_TYPE_COMMISSION = 'commission';
    public const TRANSACTION_TYPE_TRANSFER = 'transfer';
    public const TRANSACTION_TYPES = [
        self::TRANSACTION_TYPE_DEPOSIT,
        self::TRANSACTION_TYPE_WITHDRAWAL,
        self::TRANSACTION_TYPE_ORDER,
        self::TRANSACTION_TYPE_REFUND,
        self::TRANSACTION_TYPE_COMMISSION,
        self::TRANSACTION_TYPE_TRANSFER,
    ];

    // User roles
    public const ROLE_ADMIN = 'admin';
    public const ROLE_DOCTOR = 'doctor';
    public const ROLE_SHOP = 'shop';
    public const ROLE_DRIVER = 'driver';
    public const ROLE_REPRESENTATIVE = 'representative';
    public const ROLE_USER = 'user';
    public const ROLE_COMPANY = 'company';
    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_DOCTOR,
        self::ROLE_SHOP,
        self::ROLE_DRIVER,
        self::ROLE_REPRESENTATIVE,
        self::ROLE_USER,
        self::ROLE_COMPANY,
    ];

    // Pagination
    public const DEFAULT_PAGINATION = 20;
    public const ADMIN_PAGINATION = 50;
    public const MAX_PAGINATION = 100;

    // File sizes (in KB)
    public const MAX_IMAGE_SIZE = 5120; // 5MB
    public const MAX_FILE_SIZE = 10240; // 10MB
    public const MAX_DOCUMENT_SIZE = 5120; // 5MB

    // Password requirements
    public const PASSWORD_MIN_LENGTH = 8;
    public const PASSWORD_MAX_LENGTH = 100;

    // Phone validation
    public const PHONE_MIN_LENGTH = 10;
    public const PHONE_MAX_LENGTH = 15;

    // Username validation
    public const USERNAME_MIN_LENGTH = 3;
    public const USERNAME_MAX_LENGTH = 50;

    // Cache keys
    public const CACHE_KEY_HOME_DATA = 'home_data';
    public const CACHE_KEY_CATEGORIES = 'categories';
    public const CACHE_KEY_SETTINGS = 'settings';
    public const CACHE_KEY_SLIDERS = 'sliders';

    // Cache durations (in seconds)
    public const CACHE_DURATION_SHORT = 300; // 5 minutes
    public const CACHE_DURATION_MEDIUM = 3600; // 1 hour
    public const CACHE_DURATION_LONG = 86400; // 24 hours
}
