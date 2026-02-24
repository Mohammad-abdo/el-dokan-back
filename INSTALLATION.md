# دليل التثبيت - Eldokan Backend

## المتطلبات الأساسية

- PHP >= 8.1
- MySQL >= 5.7 أو MariaDB >= 10.3
- Composer
- Laravel 10

## خطوات التثبيت

### 1. تثبيت Composer Dependencies

```bash
composer install
```

### 2. إعداد ملف البيئة

```bash
cp .env.example .env
```

قم بتعديل ملف `.env` وإضافة معلومات قاعدة البيانات:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eldokan
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. توليد مفتاح التطبيق

```bash
php artisan key:generate
```

### 4. تشغيل Migrations

```bash
php artisan migrate
```

### 5. إنشاء رابط التخزين

```bash
php artisan storage:link
```

### 6. تشغيل الخادم

```bash
php artisan serve
```

الآن API متاح على: `http://localhost:8000`

## إعدادات إضافية

### إعداد OTP

في ملف `.env`:

```env
OTP_EXPIRY_MINUTES=1
OTP_LENGTH=6
```

### إعداد رفع الملفات

```env
MAX_FILE_SIZE=5120
MAX_PRESCRIPTION_IMAGES=20
ALLOWED_IMAGE_TYPES=png,jpg,jpeg
```

### إعداد Payment Gateway

```env
PAYMENT_GATEWAY=stripe
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
```

### إعداد Google Maps

```env
GOOGLE_MAPS_API_KEY=your_google_maps_key
```

## اختبار API

يمكنك استخدام Postman أو أي أداة أخرى لاختبار API:

1. تسجيل مستخدم جديد:
```
POST http://localhost:8000/api/auth/register
{
    "username": "testuser",
    "phone": "01000000000",
    "email": "test@example.com"
}
```

2. تسجيل الدخول:
```
POST http://localhost:8000/api/auth/login
{
    "phone": "01000000000"
}
```

## ملاحظات مهمة

- تأكد من أن مجلد `storage` قابل للكتابة
- تأكد من أن مجلد `bootstrap/cache` قابل للكتابة
- في بيئة الإنتاج، قم بتعطيل `APP_DEBUG=false`

