# 🚀 تعليمات إعداد المشروع - El Dokan

## ✅ تم إنشاء ملف .env

تم إنشاء ملف `.env` بنجاح مع الإعدادات الأساسية.

## 📋 الخطوات التالية:

### 1. إعداد قاعدة البيانات

قم بتعديل ملف `.env` وإضافة معلومات قاعدة البيانات:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eldokan
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 2. إنشاء قاعدة البيانات

```sql
CREATE DATABASE eldokan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. تشغيل Migrations

```bash
php artisan migrate
```

### 4. تشغيل Seeders

```bash
php artisan db:seed
```

### 5. إنشاء Storage Link

```bash
php artisan storage:link
```

### 6. توليد APP_KEY

```bash
php artisan key:generate
```

### 7. تشغيل المشروع

```bash
php artisan serve
```

المشروع سيعمل على: `http://localhost:8000`

## 📝 ملاحظات:

- تأكد من أن PHP >= 8.1 مثبت
- تأكد من أن MySQL يعمل
- تأكد من أن جميع Dependencies مثبتة: `composer install`

## 🔐 بيانات الدخول الافتراضية:

بعد تشغيل Seeders:

**Admin:**
- Phone: `01000000000`
- Password: `password`

**Doctor:**
- Phone: `01000000000`
- Password: `123456`

## ✅ المشروع جاهز!

جميع الملفات والكود جاهز. فقط قم بإعداد قاعدة البيانات وتشغيل Migrations.


