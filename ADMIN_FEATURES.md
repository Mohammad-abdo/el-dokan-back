# ميزات لوحة التحكم - Admin Panel Features

## ✅ الميزات المضافة

### 1. نظام Sliders/Banners
- ✅ إضافة/تعديل/حذف Banners
- ✅ ربط Banners بنظام تتبع الطلبات
- ✅ ربط Banners بالمنتجات، المتاجر، الأطباء، الحجوزات
- ✅ تحديد تاريخ البداية والنهاية
- ✅ ترتيب Banners

**API Endpoints:**
- `GET /api/sliders` - عرض Banners (Public)
- `GET /api/admin/sliders` - قائمة Banners (Admin)
- `POST /api/admin/sliders` - إضافة Banner
- `PUT /api/admin/sliders/{id}` - تعديل Banner
- `DELETE /api/admin/sliders/{id}` - حذف Banner

### 2. نظام الأدوار والصلاحيات (Roles & Permissions)
- ✅ نظام Roles كامل
- ✅ نظام Permissions كامل
- ✅ ربط المستخدمين بالأدوار
- ✅ ربط الأدوار بالصلاحيات
- ✅ Middleware للتحقق من الصلاحيات

**الأدوار المتوفرة:**
- Admin (مدير النظام)
- يمكن إضافة أدوار أخرى حسب الحاجة

**الصلاحيات المتوفرة:**
- إدارة المستخدمين
- إدارة المنتجات
- إدارة الطلبات
- إدارة الأطباء
- إدارة النظام المالي
- إدارة التقييمات
- إدارة Banners
- إدارة الأقسام

### 3. النظام المالي المتقدم
- ✅ Financial Transactions
- ✅ Shop Financials (إيرادات المتاجر)
- ✅ Application Statistics (إحصائيات التطبيق)
- ✅ حساب العمولات تلقائياً
- ✅ تتبع الإيرادات والمصروفات
- ✅ Dashboard مالي

**API Endpoints:**
- `GET /api/admin/financial/dashboard` - لوحة التحكم المالية
- `GET /api/admin/financial/transactions` - المعاملات المالية
- `GET /api/admin/financial/shops` - الوضع المالي للمتاجر
- `GET /api/admin/financial/statistics` - إحصائيات التطبيق

**الميزات:**
- تتبع الإيرادات (Income)
- تتبع المصروفات (Expenses)
- تتبع العمولات (Commission)
- حساب صافي الربح
- إحصائيات يومية/أسبوعية/شهرية/سنوية

### 4. نظام التقييمات (Ratings)
- ✅ تقييم المنتجات
- ✅ تقييم الأطباء
- ✅ تقييم المتاجر
- ✅ تقييم السائقين
- ✅ موافقة/رفض التقييمات من قبل المدير
- ✅ حساب متوسط التقييمات تلقائياً

**API Endpoints:**
- `POST /api/ratings` - إضافة تقييم (User)
- `GET /api/ratings` - عرض التقييمات (User)
- `GET /api/admin/ratings` - قائمة التقييمات (Admin)
- `POST /api/admin/ratings/{id}/approve` - الموافقة على التقييم
- `POST /api/admin/ratings/{id}/reject` - رفض التقييم
- `DELETE /api/admin/ratings/{id}` - حذف التقييم

### 5. إدارة الأطباء المتقدمة
- ✅ عرض جميع الأطباء
- ✅ إضافة/تعديل/حذف أطباء
- ✅ تعليق الأطباء (Suspend)
- ✅ تفعيل الأطباء (Activate)
- ✅ عرض التقييمات لكل طبيب
- ✅ عرض إحصائيات الحجوزات

**API Endpoints:**
- `GET /api/admin/doctors` - قائمة الأطباء
- `GET /api/admin/doctors/{id}` - تفاصيل الطبيب مع التقييمات
- `POST /api/admin/doctors` - إضافة طبيب
- `PUT /api/admin/doctors/{id}` - تعديل طبيب
- `POST /api/admin/doctors/{id}/suspend` - تعليق طبيب
- `POST /api/admin/doctors/{id}/activate` - تفعيل طبيب
- `DELETE /api/admin/doctors/{id}` - حذف طبيب

### 6. نظام الأقسام والتصنيفات
- ✅ أقسام رئيسية
- ✅ أقسام فرعية (Subcategories)
- ✅ ربط المنتجات بالأقسام
- ✅ إدارة كاملة للأقسام

**API Endpoints:**
- `GET /api/categories` - عرض الأقسام (Public)
- `GET /api/categories/{id}` - تفاصيل القسم (Public)
- `GET /api/admin/categories` - قائمة الأقسام (Admin)
- `POST /api/admin/categories` - إضافة قسم
- `PUT /api/admin/categories/{id}` - تعديل قسم
- `DELETE /api/admin/categories/{id}` - حذف قسم

### 7. إحصائيات التطبيق
- ✅ إحصائيات المستخدمين
- ✅ إحصائيات الطلبات
- ✅ إحصائيات الحجوزات
- ✅ إحصائيات الإيرادات
- ✅ إحصائيات المنتجات والمتاجر والأطباء

## 🔐 الأمان

- ✅ Admin Middleware للتحقق من صلاحيات المدير
- ✅ Role-based Access Control
- ✅ Permission-based Access Control

## 📊 البيانات المالية

### Shop Financials
كل متجر له:
- `total_revenue` - إجمالي الإيرادات
- `total_commission` - إجمالي العمولات
- `pending_balance` - الرصيد المعلق
- `available_balance` - الرصيد المتاح
- `commission_rate` - نسبة العمولة

### Financial Transactions
كل معاملة تحتوي على:
- نوع المعاملة (income, expense, commission, refund, withdrawal)
- الفئة (order, booking, commission, etc.)
- المبلغ
- الحالة (pending, completed, failed, cancelled)
- تاريخ المعاملة

## 📈 Dashboard المالي

يعرض:
- الإيرادات (Income)
- المصروفات (Expenses)
- العمولات (Commission)
- صافي الربح (Net Profit)
- عدد الطلبات
- عدد الحجوزات

يمكن تصفية البيانات حسب:
- اليوم (day)
- الأسبوع (week)
- الشهر (month)
- السنة (year)

## 🎯 الخطوات التالية

1. تشغيل Migrations:
```bash
php artisan migrate
```

2. تشغيل Seeders:
```bash
php artisan db:seed
```

3. إنشاء مستخدم Admin:
```php
$user = User::create([...]);
$adminRole = Role::where('name', 'admin')->first();
$user->roles()->attach($adminRole);
```

4. استخدام API:
- جميع الـ Admin Endpoints محمية بـ `auth:sanctum` و `admin` middleware
- يجب أن يكون المستخدم لديه دور `admin`

