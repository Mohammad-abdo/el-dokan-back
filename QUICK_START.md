# ⚡ البدء السريع - El Dokan

## ✅ تم إنشاء ملف .env

تم إنشاء ملف `.env` بنجاح!

## ⚠️ ملاحظة مهمة:

المشروع الحالي يحتوي على **الكود فقط** ولكن ليس Laravel مثبت بالكامل. تحتاج إلى:

### الخيار 1: تثبيت Laravel في مجلد جديد

```bash
# 1. إنشاء مشروع Laravel جديد
composer create-project laravel/laravel eldokan_new

# 2. نسخ الكود الحالي إلى المشروع الجديد
# - نسخ مجلد app/
# - نسخ مجلد database/
# - نسخ مجلد routes/
# - نسخ مجلد config/
# - نسخ composer.json (أو دمج dependencies)

# 3. تثبيت Dependencies
cd eldokan_new
composer install

# 4. نسخ ملف .env
# (تم إنشاؤه بالفعل)

# 5. توليد APP_KEY
php artisan key:generate

# 6. تشغيل Migrations
php artisan migrate

# 7. تشغيل Seeders
php artisan db:seed

# 8. تشغيل المشروع
php artisan serve
```

### الخيار 2: إضافة ملفات Laravel المفقودة

إذا كنت تريد استخدام المجلد الحالي، تحتاج إلى إضافة:

1. ملف `artisan` (Laravel CLI)
2. مجلد `bootstrap/` 
3. مجلد `public/`
4. مجلد `resources/`
5. مجلد `storage/`
6. مجلد `tests/`
7. ملفات أخرى من Laravel

## 📝 محتوى ملف .env الحالي:

```env
APP_NAME="El Dokan"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eldokan
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
```

## 🎯 الخطوات الموصى بها:

1. **إنشاء مشروع Laravel جديد:**
   ```bash
   composer create-project laravel/laravel eldokan_complete
   ```

2. **نسخ الكود الحالي:**
   - نسخ جميع الملفات من `app/` إلى `eldokan_complete/app/`
   - نسخ جميع الملفات من `database/` إلى `eldokan_complete/database/`
   - نسخ `routes/api.php` إلى `eldokan_complete/routes/api.php`
   - نسخ `config/otp.php` إلى `eldokan_complete/config/otp.php`

3. **تثبيت Dependencies:**
   ```bash
   cd eldokan_complete
   composer require barryvdh/laravel-dompdf spatie/laravel-permission league/geotools
   ```

4. **إعداد قاعدة البيانات:**
   - تعديل `.env` مع معلومات قاعدة البيانات
   - إنشاء قاعدة البيانات: `CREATE DATABASE eldokan;`

5. **تشغيل المشروع:**
   ```bash
   php artisan key:generate
   php artisan migrate
   php artisan db:seed
   php artisan storage:link
   php artisan serve
   ```

## ✅ المشروع جاهز!

بعد إكمال الخطوات أعلاه، سيعمل المشروع على:
**http://localhost:8000**

---

**ملاحظة:** جميع الكود جاهز ومكتمل. فقط تحتاج إلى تثبيت Laravel Framework.


