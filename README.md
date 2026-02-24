# 🏪 الدكان (El Dokan) - E-Commerce Platform

## 📋 نظرة عامة

منصة تجارة إلكترونية متكاملة تشمل:
- 🛒 التسوق الإلكتروني
- 👨‍⚕️ حجز الاستشارات الطبية
- 📋 إدارة الوصفات الطبية
- 🚚 نظام التوصيل
- 💰 نظام مالي متقدم
- 🗺️ نظام خرائط GPS
- 💬 نظام الدعم الفني

## 🚀 البدء السريع

### المتطلبات
- PHP >= 8.1
- MySQL >= 5.7
- Composer
- Node.js & NPM (اختياري)

### التثبيت

```bash
# 1. استنساخ المشروع
git clone <repository-url>
cd Eldokan

# 2. تثبيت Dependencies
composer install

# 3. إعداد البيئة
cp .env.example .env
php artisan key:generate

# 4. إعداد قاعدة البيانات في .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eldokan
DB_USERNAME=root
DB_PASSWORD=

# 5. تشغيل Migrations
php artisan migrate

# 6. تشغيل Seeders
php artisan db:seed

# 7. إنشاء Storage Link
php artisan storage:link

# 8. تشغيل الخادم
php artisan serve
```

## 📦 المكتبات المثبتة

- **Laravel Framework** ^10.10
- **Laravel Sanctum** ^3.2 - Authentication
- **Intervention Image** ^2.7 - Image Processing
- **Simple QR Code** ^4.2 - QR Code Generation
- **Pusher** ^7.2 - Real-time Notifications
- **Laravel DomPDF** ^2.0 - PDF Reports
- **Spatie Permission** ^5.10 - Roles & Permissions
- **League GeoTools** ^0.5.0 - GPS & Maps

## 🗄️ قاعدة البيانات

### الجداول الرئيسية:
- `users` - المستخدمين
- `shops` - المتاجر
- `products` - المنتجات
- `orders` - الطلبات
- `doctors` - الأطباء
- `bookings` - الحجوزات
- `doctor_prescriptions` - وصفات الطبيب
- `medical_centers` - المراكز الطبية
- `representatives` - الممثلين
- `support_tickets` - تذاكر الدعم
- `doctor_wallets` - محافظ الأطباء
- `coupons` - الكوبونات

## 🔐 المصادقة

### تسجيل فوري (بدون تحقق):
```http
POST /api/auth/register
Content-Type: application/json

{
  "username": "user123",
  "phone": "01000000000",
  "email": "user@example.com",
  "password": "password123"
}
```

### تسجيل الدخول:
```http
POST /api/auth/login
Content-Type: application/json

{
  "phone": "01000000000",
  "password": "password123"
}
```

### تسجيل الدخول من رابط (مع كلمة مرور افتراضية):
```http
POST /api/auth/login-with-link
Content-Type: application/json

{
  "phone": "01000000000",
  "password": "123456"
}
```

## 📱 API Endpoints الرئيسية

### الصفحة الرئيسية
- `GET /api/home` - الصفحة الرئيسية العامة

### الطبيب
- `GET /api/doctor/dashboard` - لوحة التحكم
- `POST /api/doctor/prescriptions` - إنشاء وصفة
- `POST /api/doctor/prescriptions/{id}/share` - مشاركة وصفة
- `GET /api/doctor/wallet` - المحفظة
- `GET /api/doctor/medical-centers` - العيادات

### الخرائط
- `GET /api/maps/medical-centers` - المراكز الطبية
- `GET /api/maps/calculate-distance` - حساب المسافة

### الدعم الفني
- `POST /api/support/tickets` - إنشاء تذكرة
- `POST /api/support/tickets/{id}/messages` - إرسال رسالة

### Admin
- `GET /api/admin/reports/orders` - تقارير الطلبات
- `GET /api/admin/reports/financial` - التقارير المالية
- `GET /api/admin/doctors/{id}/wallet` - محفظة الطبيب

## 👤 بيانات الدخول الافتراضية

بعد تشغيل `php artisan db:seed`:

**Admin:**
- Phone: `01000000000`
- Password: `password`

**Doctor:**
- Phone: `01000000000` (من Doctor seeder)
- Password: `123456` (يجب تغييرها)

## 🌐 الحقول متعددة اللغات

جميع الجداول الرئيسية تحتوي على حقول عربية وإنجليزية:
- `name_ar`, `name_en`
- `description_ar`, `description_en`
- `address_ar`, `address_en`

## 🔒 الأمان

- ✅ Laravel Sanctum Authentication
- ✅ Role-based Access Control
- ✅ Permission-based Access Control
- ✅ Admin Middleware
- ✅ Doctor Middleware
- ✅ Representative Middleware
- ✅ Account Status Verification

## 📊 التقارير

### Admin Reports:
- تقارير الطلبات (CSV, PDF)
- التقارير المالية (CSV, PDF)
- تقارير المستخدمين (CSV, PDF)
- تقارير المنتجات (CSV, PDF)
- Dashboard Report

### Doctor Reports:
- تقارير الوصفات (CSV)
- تقارير المنتجات (CSV)
- تقارير المرضى (CSV)

## 🎫 الكوبونات

- أنواع: `percentage`, `fixed`, `free_shipping`
- ربط بالمنتجات والأقسام
- حدود الاستخدام
- تواريخ الصلاحية

## 💰 النظام المالي

- محفظة المستخدم
- محفظة الطبيب
- محفظة Admin
- العمولات التلقائية
- تتبع المعاملات

## 🗺️ نظام الخرائط

- تحديد إحداثيات العيادات
- حساب المسافات
- فلترة حسب المسافة
- عرض على الخريطة

## 📝 الوصفات الطبية

- إنشاء وصفات
- مشاركة من خلال رابط
- QR Code
- طباعة (بدون أسعار)
- وصفات ثابتة (Templates)
- ملاحظات لكل دواء

## 🛠️ التطوير

```bash
# تشغيل Tests
php artisan test

# Code Style
./vendor/bin/pint

# Clear Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 📄 الترخيص

MIT License

## 👥 المساهمون

- Backend Development Team

## 📞 الدعم

للحصول على الدعم، افتح تذكرة في قسم Support في التطبيق.

---

**تم التطوير بـ ❤️ باستخدام Laravel**
