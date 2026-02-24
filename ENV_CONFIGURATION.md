# 📋 إعدادات ملف .env - El Dokan

## ✅ تم إكمال ملف .env بنجاح!

تم إضافة جميع الإعدادات المطلوبة للمشروع.

## 📝 الإعدادات المضافة:

### 1. إعدادات التطبيق الأساسية
- `APP_NAME="El Dokan"` - اسم التطبيق
- `APP_LOCALE=ar` - اللغة الافتراضية (عربي)
- `APP_FALLBACK_LOCALE=en` - اللغة الاحتياطية (إنجليزي)
- `APP_TIMEZONE=UTC` - المنطقة الزمنية

### 2. إعدادات OTP
- `OTP_EXPIRY_MINUTES=5` - مدة صلاحية OTP (5 دقائق)
- `OTP_LENGTH=6` - طول رمز OTP

### 3. إعدادات Payment Gateway
- `PAYMENT_GATEWAY_DEFAULT=credit_card` - بوابة الدفع الافتراضية
- `STRIPE_KEY=` - مفتاح Stripe (يضاف لاحقاً)
- `STRIPE_SECRET=` - سر Stripe (يضاف لاحقاً)

### 4. إعدادات Google Maps
- `GOOGLE_MAPS_API_KEY=` - مفتاح Google Maps API (يضاف لاحقاً)

### 5. إعدادات Pusher (للإشعارات الفورية)
- `PUSHER_APP_ID=`
- `PUSHER_APP_KEY=`
- `PUSHER_APP_SECRET=`
- `PUSHER_HOST=`
- `PUSHER_PORT=443`
- `PUSHER_SCHEME=https`

### 6. إعدادات رفع الملفات
- `MAX_FILE_SIZE=10240` - الحد الأقصى لحجم الملف (KB)
- `MAX_PRESCRIPTION_IMAGES=20` - الحد الأقصى لصور الوصفات
- `ALLOWED_IMAGE_TYPES=png,jpg,jpeg,webp` - أنواع الصور المسموحة
- `ALLOWED_DOCUMENT_TYPES=pdf,doc,docx` - أنواع المستندات المسموحة

### 7. إعدادات التطبيق
- `DEFAULT_USER_PASSWORD=123456` - كلمة المرور الافتراضية للمستخدمين الجدد
- `DOCTOR_COMMISSION_RATE=10.00` - نسبة عمولة الطبيب (%)
- `SHOP_COMMISSION_RATE=10.00` - نسبة عمولة المتجر (%)

### 8. إعدادات QR Code
- `QR_CODE_SIZE=300` - حجم QR Code
- `QR_CODE_MARGIN=2` - هامش QR Code

### 9. إعدادات Pagination
- `PAGINATION_PER_PAGE=20` - عدد العناصر في الصفحة (للمستخدمين)
- `ADMIN_PAGINATION_PER_PAGE=50` - عدد العناصر في الصفحة (للمدير)

### 10. إعدادات Cache
- `CACHE_DURATION=3600` - مدة الكاش (بالثواني)
- `CACHE_PREFIX=eldokan_` - بادئة الكاش

### 11. إعدادات Rate Limiting
- `RATE_LIMIT_PER_MINUTE=60` - الحد الأقصى للطلبات في الدقيقة
- `RATE_LIMIT_PER_HOUR=1000` - الحد الأقصى للطلبات في الساعة

### 12. إعدادات الأمان
- `SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1` - النطاقات المسموحة
- `SESSION_DOMAIN=null` - نطاق الجلسة

## 🔧 الإعدادات التي تحتاج إلى إضافة قيمها:

### قاعدة البيانات:
```env
DB_DATABASE=eldokan
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

### Google Maps API:
```env
GOOGLE_MAPS_API_KEY=your_google_maps_api_key
```

### Stripe Payment:
```env
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
```

### Pusher (للإشعارات):
```env
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_secret
```

## ✅ الملف جاهز للاستخدام!

جميع الإعدادات الأساسية موجودة. فقط قم بإضافة:
1. معلومات قاعدة البيانات
2. مفاتيح API الخارجية (Google Maps, Stripe, Pusher)

---

**ملاحظة:** في بيئة الإنتاج، قم بتعطيل `APP_DEBUG=false` وتغيير `APP_ENV=production`


