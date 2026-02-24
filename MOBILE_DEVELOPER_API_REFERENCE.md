# 📱 مرجع API للموبايل ديفيلوبر - Eldokan (Ad-Dukkan)

> **التصميم:** [Figma - Ad-Dukkan-App](https://www.figma.com/design/Jx9TrhjKWJqvULxETdZc8J/Ad-Dukkan-App?node-id=75-8518)  
> **Base URL:** `https://your-domain.com/api`  
> **Auth:** `Authorization: Bearer {token}`  
> **تحليل الـ 5 فلو:** انظر [MOBILE_5_FLOWS_GAP_ANALYSIS.md](./MOBILE_5_FLOWS_GAP_ANALYSIS.md)

---

## الـ 5 فلو وتوزيع الـ Endpoints

| الفلو | المستخدم | الـ Endpoints الأساسية |
|-------|----------|------------------------|
| **فلو 1** | عميل (End User) | القسم العام أدناه: auth, home, user, addresses, shops, products, cart, orders, delivery, payment, notifications, messages, favourites, settings, support, prescriptions, doctors, bookings |
| **فلو 2** | مندوب مبيعات (Sales Rep) | ` /api/representative/*` — dashboard, visits, products؛ ناقص: clients, orders, reports (انظر تقرير الفجوات) |
| **فلو 3** | سائق (Driver) | ` /api/driver/*` — dashboard, deliveries؛ ناقص: available-orders, accept, pickup, confirm-delivery (انظر تقرير الفجوات) |
| **فلو 4** | طبيب (Doctor) | ` /api/doctor/*` — dashboard, bookings, patients, prescriptions, wallet, reports, chat |
| **فلو 5** | بائع/متجر (Vendor/Shop) | ` /api/shop/*` — dashboard, products, orders, customers |
| **فلو 6** | شركة (Company) | ` /api/company/*` — نفس endpoints المتجر (dashboard, products, orders, customers, revenue, financial) |

---

## 🔐 Authentication (10)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| POST | `/api/auth/register` | تسجيل مستخدم جديد | ❌ |
| POST | `/api/auth/login` | تسجيل الدخول | ❌ |
| POST | `/api/auth/social-login` | دخول Google/Apple | ❌ |
| POST | `/api/auth/verify-otp` | التحقق من OTP | ❌ |
| POST | `/api/auth/resend-otp` | إعادة إرسال OTP | ❌ |
| GET | `/api/auth/guest-login` | دخول كضيف | ❌ |
| GET | `/api/auth/me` | بيانات المستخدم الحالي | ✅ |
| POST | `/api/auth/logout` | تسجيل الخروج | ✅ |
| POST | `/api/auth/change-password` | تغيير كلمة المرور | ✅ |
| POST | `/api/auth/login-with-link` | دخول برابط وصفة | ✅ |

---

## 🏠 Home & Public (2)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/home` | الصفحة الرئيسية (سلايدر، فئات، منتجات، متاجر) | ❌ |
| GET | `/api/sliders` | قائمة السلايدرات | ❌ |

---

## 👤 User & Wallet (4)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/user/profile` | ملف المستخدم | ✅ |
| PUT | `/api/user/profile` | تحديث الملف | ✅ |
| GET | `/api/user/wallet` | رصيد المحفظة | ✅ |
| POST | `/api/user/wallet/top-up` | شحن المحفظة | ✅ |

---

## 📍 Addresses (6)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/addresses` | قائمة العناوين | ✅ |
| POST | `/api/addresses` | إضافة عنوان | ✅ |
| GET | `/api/addresses/{id}` | تفاصيل عنوان | ✅ |
| PUT | `/api/addresses/{id}` | تحديث عنوان | ✅ |
| DELETE | `/api/addresses/{id}` | حذف عنوان | ✅ |
| PUT | `/api/addresses/{id}/set-default` | تعيين كافتراضي | ✅ |

---

## 🏪 Shops & Products (5)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/shops` | قائمة المتاجر | ❌ |
| GET | `/api/shops/{id}` | تفاصيل متجر | ❌ |
| GET | `/api/products` | قائمة المنتجات | ❌ |
| GET | `/api/products/{id}` | تفاصيل منتج | ❌ |
| POST | `/api/products/filter` | تصفية منتجات | ❌ |

---

## 📂 Categories (3)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/categories` | قائمة الفئات | ❌ |
| GET | `/api/categories/{id}` | تفاصيل فئة | ❌ |
| GET | `/api/subcategories` | الفئات الفرعية | ❌ |

---

## 🛒 Cart (5)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/cart` | محتويات السلة | ✅ |
| POST | `/api/cart/add` | إضافة للسلة | ✅ |
| PUT | `/api/cart/{id}` | تحديث كمية | ✅ |
| DELETE | `/api/cart/{id}` | حذف عنصر | ✅ |
| DELETE | `/api/cart` | مسح السلة | ✅ |

---

## 📦 Orders (6)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/orders` | قائمة الطلبات | ✅ |
| POST | `/api/orders` | إنشاء طلب | ✅ |
| GET | `/api/orders/{id}` | تفاصيل طلب | ✅ |
| GET | `/api/orders/{id}/track` | تتبع الطلب | ✅ |
| PUT | `/api/orders/{id}/cancel` | إلغاء طلب | ✅ |
| GET | `/api/orders/status/{status}` | طلبات حسب الحالة | ✅ |

---

## 💊 Prescriptions (5)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| POST | `/api/prescriptions/upload` | رفع وصفة (multipart) | ✅ |
| GET | `/api/prescriptions` | قائمة الوصفات | ✅ |
| GET | `/api/prescriptions/{id}` | تفاصيل وصفة | ✅ |
| GET | `/api/prescriptions/status/{status}` | وصفات حسب الحالة | ✅ |
| GET | `/api/prescriptions/link/{link}` | عرض وصفة برابط | ❌ |

---

## ⏰ Medication Reminders (6)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/reminders` | قائمة التذكيرات | ✅ |
| POST | `/api/reminders` | إنشاء تذكير | ✅ |
| GET | `/api/reminders/{id}` | تفاصيل تذكير | ✅ |
| PUT | `/api/reminders/{id}` | تحديث تذكير | ✅ |
| DELETE | `/api/reminders/{id}` | حذف تذكير | ✅ |
| PUT | `/api/reminders/{id}/toggle` | تفعيل/تعطيل | ✅ |

---

## 👨‍⚕️ Doctors (3)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/doctors` | قائمة الأطباء | ❌ |
| GET | `/api/doctors/{id}` | تفاصيل طبيب | ❌ |
| GET | `/api/doctors/{id}/availability` | أوقات التوفر | ❌ |

---

## 📅 Bookings (7)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/bookings` | قائمة الحجوزات | ✅ |
| POST | `/api/bookings` | إنشاء حجز | ✅ |
| GET | `/api/bookings/{id}` | تفاصيل حجز | ✅ |
| PUT | `/api/bookings/{id}/cancel` | إلغاء حجز | ✅ |
| POST | `/api/bookings/{id}/rate` | تقييم حجز | ✅ |
| POST | `/api/bookings/{id}/complaint` | تقديم شكوى | ✅ |
| GET | `/api/bookings/status/{status}` | حجوزات حسب الحالة | ✅ |

---

## 🚚 Delivery (5)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/deliveries/{order_id}/track` | تتبع التوصيل | ✅ |
| GET | `/api/deliveries/{order_id}/driver` | معلومات السائق | ✅ |
| POST | `/api/deliveries/{order_id}/contact-driver` | التواصل مع السائق | ✅ |
| POST | `/api/deliveries/{order_id}/confirm` | تأكيد الاستلام | ✅ |
| GET | `/api/deliveries/{order_id}/map` | خريطة التوصيل | ✅ |

---

## 💳 Payment (4)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| POST | `/api/payments/process` | معالجة الدفع | ✅ |
| GET | `/api/payments/methods` | طرق الدفع | ✅ |
| POST | `/api/payments/apply-discount` | تطبيق كود خصم | ✅ |
| GET | `/api/payments/history` | سجل المدفوعات | ✅ |

---

## 🔔 Notifications (4)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/notifications` | قائمة الإشعارات | ✅ |
| GET | `/api/notifications/unread-count` | عدد غير المقروءة | ✅ |
| PUT | `/api/notifications/{id}/read` | تعليم كمقروء | ✅ |
| PUT | `/api/notifications/read-all` | تعليم الكل كمقروء | ✅ |

---

## 💬 Messages (5)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/messages` | قائمة المحادثات | ✅ |
| GET | `/api/messages/{user_id}/conversation` | محادثة مع مستخدم | ✅ |
| POST | `/api/messages` | إرسال رسالة | ✅ |
| POST | `/api/messages/voice` | إرسال رسالة صوتية (multipart) | ✅ |
| PUT | `/api/messages/{id}/read` | تعليم كمقروء | ✅ |

---

## ⭐ Favourites (4)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/favourites` | قائمة المفضلة | ✅ |
| POST | `/api/favourites` | إضافة للمفضلة | ✅ |
| DELETE | `/api/favourites/{product_id}` | حذف من المفضلة | ✅ |
| GET | `/api/favourites/{product_id}/check` | التحقق من المفضلة | ✅ |

---

## ⭐ Ratings (2)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| POST | `/api/ratings` | إنشاء تقييم | ✅ |
| GET | `/api/ratings` | قائمة التقييمات | ✅ |

---

## 🎟️ Coupons (1)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| POST | `/api/coupons/validate` | التحقق من كود خصم | ✅ |

---

## ⚙️ Settings & Support (7)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/settings` | الإعدادات | ✅ |
| PUT | `/api/settings` | تحديث الإعدادات | ✅ |
| GET | `/api/support` | معلومات الدعم | ✅ |
| POST | `/api/support/tickets` | إنشاء تذكرة | ✅ |
| GET | `/api/support/tickets` | قائمة التذاكر | ✅ |
| GET | `/api/support/tickets/{id}` | تفاصيل تذكرة | ✅ |
| POST | `/api/support/tickets/{id}/messages` | إضافة رسالة | ✅ |

---

## 🗺️ Maps & Locations (3)

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/maps/medical-centers` | المراكز الطبية | ✅ |
| GET | `/api/maps/doctors/{doctor_id}/clinics` | عيادات طبيب | ✅ |
| GET | `/api/maps/calculate-distance` | حساب المسافة | ✅ |

---

## 📊 ملخص Endpoints العميل (الفلو 1)

| النوع | العدد |
|------|--------|
| **إجمالي Endpoints العميل** | **100** |
| عامة (بدون Auth) | 12 |
| محمية (مع Auth) | 88 |
| GET | 52 |
| POST | 30 |
| PUT | 13 |
| DELETE | 5 |

---

## 🚚 فلو 3: Driver (سائق) – Endpoints

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/driver/dashboard` | إحصائيات السائق (توصيلات، أرباح) | ✅ |
| GET | `/api/driver/deliveries` | قائمة التوصيلات المعينة للسائق | ✅ |
| GET | `/api/driver/available-orders` | طلبات متاحة للتوصيل (اختياري: latitude, longitude, radius) | ✅ |
| GET | `/api/driver/orders/{orderId}/delivery-details` | تفاصيل الطلب للتوصيل | ✅ |
| POST | `/api/driver/orders/{orderId}/offer` | عرض سعر التوصيل (body: delivery_price) | ✅ |
| POST | `/api/driver/orders/{orderId}/accept` | قبول الطلب | ✅ |
| POST | `/api/driver/deliveries/{deliveryId}/pickup` | تأكيد استلام من المتجر | ✅ |
| POST | `/api/driver/deliveries/{deliveryId}/confirm-delivery` | تأكيد التسليم للعميل | ✅ |
| PUT | `/api/driver/location` | تحديث موقع السائق (body: latitude, longitude) | ✅ |

---

## 👨‍⚕️ فلو 4: Doctor (طبيب) – Endpoints

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/doctor/dashboard` | داشبورد الطبيب | ✅ doctor |
| GET | `/api/doctor/bookings` | حجوزات الطبيب | ✅ doctor |
| GET | `/api/doctor/patients` | قائمة المرضى | ✅ doctor |
| GET/POST/PUT/DELETE | `/api/doctor/prescriptions` | وصفات الطبيب | ✅ doctor |
| POST | `/api/doctor/prescriptions/{id}/share` | مشاركة وصفة | ✅ doctor |
| GET | `/api/doctor/prescriptions/{id}/print` | طباعة وصفة | ✅ doctor |
| GET | `/api/doctor/wallet` | محفظة الطبيب | ✅ doctor |
| GET | `/api/doctor/wallet/transactions` | معاملات المحفظة | ✅ doctor |
| GET/POST/DELETE | `/api/doctor/medical-centers` | المراكز الطبية | ✅ doctor |
| GET | `/api/doctor/reports/prescriptions` | تقرير الوصفات | ✅ doctor |
| GET | `/api/doctor/reports/products` | تقرير المنتجات | ✅ doctor |
| GET | `/api/doctor/reports/patients` | تقرير المرضى | ✅ doctor |
| GET | `/api/doctor/chat/conversations` | قائمة المحادثات | ✅ doctor |
| GET | `/api/doctor/chat/{patient}/conversation` | محادثة مع مريض | ✅ doctor |
| POST | `/api/doctor/chat/{patient}/send` | إرسال رسالة | ✅ doctor |

---

## 🏪 فلو 5: Vendor/Shop (بائع/متجر) – Endpoints

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/shop/dashboard` | داشبورد المتجر | ✅ |
| GET/POST | `/api/shop/products` | قائمة/إضافة منتجات | ✅ |
| GET/PUT/DELETE | `/api/shop/products/{id}` | تفاصيل/تعديل/حذف منتج | ✅ |
| GET | `/api/shop/orders` | طلبات المتجر | ✅ |
| GET | `/api/shop/orders/{id}` | تفاصيل طلب | ✅ |
| PUT | `/api/shop/orders/{id}/status` | تحديث حالة الطلب | ✅ |
| GET | `/api/shop/customers` | العملاء (من الطلبات) | ✅ |

---

## 🏢 فلو 6: Company (شركة) – Endpoints

نفس endpoints المتجر مع البادئة `/api/company/`: dashboard, products (CRUD), orders, orders/{id}, orders/{id}/status, customers, revenue, financial.

---

## 📋 فلو 2: Sales Representative (مندوب مبيعات) – Endpoints

| Method | Endpoint | وصف | Auth |
|--------|----------|-----|------|
| GET | `/api/representative/dashboard` | داشبورد المندوب | ✅ representative |
| GET | `/api/representative/clients` | قائمة العملاء (متاجر الزيارات)، query: search | ✅ representative |
| GET | `/api/representative/orders` | طلبات المتاجر المرتبطة بالمندوب، query: status | ✅ representative |
| GET | `/api/representative/orders/{id}` | تفاصيل طلب | ✅ representative |
| PUT | `/api/representative/orders/{id}/cancel` | إلغاء طلب | ✅ representative |
| GET | `/api/representative/reports` | تقارير، query: type=visits\|orders\|sales, from_date, to_date | ✅ representative |
| GET/POST/PUT/DELETE | `/api/representative/products` | منتجات (عرض للمندوب) | ✅ representative |
| GET/POST/PUT/DELETE | `/api/representative/visits` | زيارات/مواعيد | ✅ representative |

---

## 📎 ملفات إضافية للموبايل ديفيلوبر

| الملف | الوصف |
|-------|--------|
| **`MOBILE_5_FLOWS_GAP_ANALYSIS.md`** | تحليل الفجوات بين الـ 5 فلو (Figma) والباك اند + توصيات |
| **`postman/`** | **6 مجموعات Postman (واحدة لكل فلو):** Flow_1_End_User, Flow_2_Sales_Representative, Flow_3_Driver, Flow_4_Doctor, Flow_5_Vendor_Shop, Flow_6_Company |
| `MOBILE_API_ENDPOINTS.md` | توثيق مفصل مع Request/Response لكل endpoint |
| `MOBILE_API_ENDPOINTS_QUICK_REFERENCE.md` | مرجع سريع + نصائح تكامل |
| `mobile_endpoints.json` | كل الـ endpoints بصيغة JSON |
| `Eldokan_API_Collection.postman_collection.json` | مجموعة Postman للاختبار |

---

**آخر تحديث:** 2024-01-01 · **إصدار API:** 1.0.0
