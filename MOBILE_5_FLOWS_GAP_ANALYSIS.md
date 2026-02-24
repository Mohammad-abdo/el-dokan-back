# تحليل الفجوات: الـ 5 فلو (Figma) مقابل الباك اند

> **مرجع التصميم:** Figma - Ad-Dukkan-App (End User, Sales Rep, Driver, Doctor, Vendor, Company)  
> **تاريخ التحليل:** 2025-02-09

---

## ملخص تنفيذي

| الفلو | الحالة | التغطية التقريبية | ملاحظة |
|-------|--------|---------------------|--------|
| **1. End User (عميل)** | ✅ مغطى جيداً | ~95% | معظم الشاشات تعمل بالـ API الحالي |
| **2. Sales Representative (مندوب مبيعات)** | ⚠️ فجوات | ~60% | يحتاج عملاء، مواعيد، تقارير حسب التاريخ |
| **3. Driver (مندوب توصيل)** | ❌ فجوات كبيرة | ~35% | يحتاج طلبات متاحة، قبول، عرض سعر، استلام، تأكيد تسليم + QR |
| **4. Doctor (طبيب)** | ✅ مغطى جيداً | ~90% | داشبورد، وصفات، حجوزات، محادثات، تقارير موجودة |
| **5. Vendor/Shop (بائع/متجر)** | ✅ مغطى | ~85% | داشبورد، منتجات، طلبات، عملاء موجودة |

**الخلاصة:** الباك اند يخدم فلو **عميل** و **طبيب** و **بائع** بشكل جيد. فلو **المندوب (مبيعات)** يحتاج توسعات معتدلة. فلو **السائق** يحتاج إضافات كبيرة (طلبات متاحة، قبول، استلام، تأكيد تسليم مع QR).

---

## الفلو 1: End User (عميل) – تطبيق Ad-Dukkan للعميل

### الشاشات من Figma وما يخدمها من Endpoints

| الشاشة / الوظيفة | Endpoints المطلوبة | موجود في الباك اند؟ |
|------------------|---------------------|----------------------|
| Splash / اختيار اللغة | إعدادات أو تخزين محلي | ⚠️ اختياري: `GET /settings` |
| تسجيل / دخول / OTP | register, login, verify-otp, resend-otp | ✅ كلها موجودة |
| الرئيسية (بانر، فئات، منتجات) | home, sliders, categories, products | ✅ |
| تصفح فئة / منتجات / تفاصيل منتج | categories, products, products/{id}, products/filter | ✅ |
| سلة، إضافة، تحديث، حذف | cart, cart/add, cart/{id}, cart (DELETE) | ✅ |
| عنوان التوصيل (قائمة، إضافة، تعديل) | addresses (GET, POST, PUT, DELETE) | ✅ |
| الدفع، طرق الدفع، كوبون | payments/process, payments/methods, payments/apply-discount | ✅ |
| إنشاء طلب | POST /orders | ✅ |
| تتبع الطلب، خريطة، سائق | orders/{id}/track, deliveries/{id}/track, driver, map | ✅ |
| تأكيد استلام (عميل) | POST /deliveries/{order_id}/confirm | ✅ |
| إشعارات | notifications, unread-count, read, read-all | ✅ |
| الملف الشخصي، تعديل، عناوين، محفظة | user/profile, user/wallet, addresses | ✅ |
| المفضلة، التقييمات | favourites, ratings | ✅ |
| كوبونات | coupons/validate | ✅ |
| رفع وصفة، حجوزات أطباء | prescriptions/upload, doctors, bookings | ✅ |
| حجوزاتي، إلغاء، تقييم | bookings, bookings/{id}/cancel, bookings/{id}/rate | ✅ |
| الدعم الفني | support, support/tickets | ✅ |
| الإعدادات | settings | ✅ |

**نتيجة الفلو 1:** ✅ **مغطى** — لا يلزم endpoints جديدة للعميل من التصميم الحالي.

---

## الفلو 2: Sales Representative (مندوب المبيعات) – Sales flow

### الشاشات من Figma وما يخدمها من Endpoints

| الشاشة / الوظيفة | Endpoints المطلوبة | موجود في الباك اند؟ |
|------------------|---------------------|----------------------|
| بحث عن عميل / قائمة العملاء الحاليين | GET عملاء (بحث + قائمة) | ❌ لا يوجد `/representative/clients` |
| تحديد موعد (اسم عميل، وقت، تاريخ) | إنشاء موعد/زيارة | ⚠️ موجود كـ **زيارات** `POST /representative/visits` (visit_date, visit_time) |
| قائمة المواعيد / التقويم | قائمة زيارات/مواعيد | ⚠️ `GET /representative/visits` يغطي القائمة بدون تقويم مخصص |
| التواصل / قائمة خدمات-منتجات (طلب عرض سعر) | منتجات أو عروض أسعار | ⚠️ `GET /representative/products` للمنتجات؛ لا يوجد "طلب عرض سعر" كـ endpoint |
| مراجعة الطلب، تأكيد/إلغاء طلب | طلبات المندوب، تحديث حالة | ❌ لا يوجد طلبات خاصة بالمندوب أو ربط طلب بمندوب |
| قائمة العملاء | GET عملاء | ❌ لا يوجد `/representative/clients` |
| قائمة الطلبات (حالة: موافقة، انتظار، ملغى) | GET طلبات المندوب مع فلتر حالة | ❌ لا يوجد `/representative/orders` |
| تفاصيل الطلب، إلغاء الطلب | GET/PUT طلب معين | ❌ |
| التقارير (نوع التقرير، من تاريخ، إلى تاريخ، توليد) | GET تقارير بمعايير | ❌ لا يوجد `/representative/reports?type=&from=&to=` |
| الداشبورد | داشبورد المندوب | ✅ `GET /representative/dashboard` |
| المنتجات (عرض للمندوب) | منتجات | ✅ `GET /representative/products` |
| الزيارات (إنشاء، قائمة، تفاصيل) | زيارات | ✅ `representative/visits` (CRUD) |

**نتيجة الفلو 2:** ⚠️ **فجوات:**  
- مطلوب: **عملاء المندوب** (قائمة + بحث)، **طلبات المندوب** (قائمة + تفاصيل + إلغاء)، **تقارير** (نوع + من/إلى تاريخ).  
- يمكن ربط "تحديد موعد" بالزيارات الحالية بعد توحيد المسميات في الواجهة (موعد = زيارة).

---

## الفلو 3: Driver (مندوب التوصيل) – Service Provider - Driver

### الشاشات من Figma وما يخدمها من Endpoints

| الشاشة / الوظيفة | Endpoints المطلوبة | موجود في الباك اند؟ |
|------------------|---------------------|----------------------|
| الصفحة الرئيسية: عنوان الحالي، طلبات متاحة في المنطقة | موقع السائق + GET طلبات متاحة (بدون سائق أو معرّفة منطقة) | ❌ لا يوجد `GET /driver/available-orders` أو حسب موقع |
| تفاصيل طلب متاح (عنوان استلام، عنوان تسليم، مبلغ التوصيل) | GET تفاصيل طلب للتوصيل (للسائق) | ❌ الطلبات الحالية مرتبطة بعميل؛ لا endpoint "طلب متاح للتوصيل" |
| عرض سعر التوصيل، تأكيد (قبول الطلب) | POST عرض سعر + قبول الطلب من السائق | ❌ لا يوجد `POST /driver/orders/{id}/offer` أو `accept` |
| تم قبول طلبك بنجاح → ابدأ الآن | تحديث حالة التوصيل "بدأ" | ❌ لا يوجد `POST /driver/deliveries/{id}/start` |
| خريطة التوجه إلى المتجر | موقع المتجر + مسار | ⚠️ يمكن استخدام تفاصيل الطلب عند وجودها؛ لا endpoint مخصص "موقع متجر الطلب" للسائق |
| تم استلام الطلب (من المتجر) | تأكيد استلام من المتجر (pickup) | ❌ لا يوجد `POST /driver/deliveries/{id}/pickup` |
| خريطة التوجه إلى العميل | عنوان العميل + مسار | ⚠️ نفس التعليق أعلاه |
| تأكيد التسليم (مع مسح QR) | تأكيد تسليم + التحقق من QR | ❌ `POST /deliveries/{order_id}/confirm` للعميل فقط؛ لا تأكيد من السائق ولا QR |
| تم تأكيد توصيل الطلب بنجاح | تحديث حالة "تم التوصيل" من طرف السائق | ❌ |
| الداشبورد، قائمة التوصيلات، الأرباح | داشبورد السائق وتوصيلاته | ✅ `GET /driver/dashboard`, `GET /driver/deliveries` |

**نتيجة الفلو 3:** ❌ **فجوات كبيرة:**  
- مطلوب: **طلبات متاحة** (حسب الموقع/المنطقة)، **تفاصيل طلب للتوصيل**، **عرض سعر توصيل**، **قبول طلب**، **بدء التوصيل**، **تأكيد استلام من المتجر**، **تأكيد تسليم (مع QR)** من طرف السائق.

---

## الفلو 4: Doctor (طبيب) – Doctor flow

### الشاشات من Figma وما يخدمها من Endpoints

| الشاشة / الوظيفة | Endpoints المطلوبة | موجود في الباك اند؟ |
|------------------|---------------------|----------------------|
| تسجيل الطبيب، مراجعة الحساب، التحقق | register (نفس المستخدم) + حالة مراجعة | ⚠️ يمكن استخدام نفس auth؛ اختياري: GET حالة المستخدم/الطبيب (تحت المراجعة) |
| الداشبورد (دخل، طلبات مباشرة، مرضى) | داشبورد طبيب | ✅ `GET /doctor/dashboard` |
| إحصائيات (دخل، طلبات) | إحصائيات بفترات | ⚠️ جزئياً في dashboard؛ يمكن توسيع تقارير |
| الإشعارات / النشاطات | إشعارات | ✅ استخدام `GET /notifications` أو توسيع doctor |
| إنشاء وصفة (مريض، دواء، جرعة، تعليمات) | إنشاء وصفة طبيب | ✅ `POST /doctor/prescriptions` |
| وصفة مع QR ومشاركة | تفاصيل وصفة، QR، مشاركة | ✅ `GET /doctor/prescriptions/{id}`, share, print |
| إضافة بند للوصفة | تحديث وصفة / بنود | ✅ عبر apiResource prescriptions |
| المحادثات وقائمة الدردشة | محادثات الطبيب مع المرضى | ✅ `GET /doctor/chat/conversations`, `GET /doctor/chat/{patient}/conversation`, `POST .../send` |
| تفاصيل مريض (وصفات، مراجعات) | مريض + وصفاته وتاريخه | ✅ `GET /doctor/patients` ووصفات مرتبطة |
| السجل (حالات، طلبات، مراجعات) | سجل بفلاتر | ✅ `GET /doctor/reports/prescriptions`, products, patients |
| تفاصيل وصلة/سجل | تفاصيل عنصر من السجل | ✅ عبر prescriptions أو bookings |
| تأكيد الطلبات الطبية | تأكيد طلب/وصفة | ⚠️ يمكن عبر تحديث حالة الوصفة إن وُجدت حالات |
| الملف الشخصي، تعديل، إعدادات | profile, update | ✅ استخدام user/profile أو توسيع doctor |

**نتيجة الفلو 4:** ✅ **مغطى جيداً** — الفلو كامل تقريباً؛ تحسينات اختيارية (حالة "تحت المراجعة"، تقارير بفترات).

---

## الفلو 5: Vendor / Shop (بائع / متجر) – إدارة المنتجات والطلبات

### الشاشات من Figma وما يخدمها من Endpoints

| الشاشة / الوظيفة | Endpoints المطلوبة | موجود في الباك اند؟ |
|------------------|---------------------|----------------------|
| داشبورد المتجر | داشبورد | ✅ `GET /shop/dashboard` |
| قائمة المنتجات، إضافة، تعديل، حذف | منتجات المتجر | ✅ `GET/POST/PUT/DELETE /shop/products` |
| قائمة الطلبات، تفاصيل، تحديث حالة | طلبات المتجر | ✅ `GET /shop/orders`, `GET /shop/orders/{id}`, `PUT /shop/orders/{id}/status` |
| العملاء (من الطلبات) | عملاء | ✅ `GET /shop/customers` (معاد توجيهه لطلبات) |
| الإيرادات / مالي | إيرادات ومالية | ✅ ضمن `GET /shop/dashboard` أو revenue/financial |

**نتيجة الفلو 5:** ✅ **مغطى** — لا يلزم endpoints جديدة للفلو الحالي.

---

## التوصيات: Endpoints جديدة أو تعديلات مقترحة

### أولوية عالية (ضرورية لفلو السائق والمندوب)

1. **Driver – طلبات متاحة**
   - `GET /api/driver/available-orders?latitude=&longitude=&radius=`
   - يرجع طلبات لها توصيل (delivery) بدون سائق أو قابلة للتعيين.

2. **Driver – تفاصيل طلب للتوصيل**
   - `GET /api/driver/orders/{order_id}/delivery-details`
   - عناوين استلام/تسليم، مبلغ التوصيل، بيانات الطلب (بدون بيانات عميل حساسة أكثر من اللازم).

3. **Driver – عرض سعر توصيل وقبول الطلب**
   - `POST /api/driver/orders/{order_id}/offer` body: `{ "delivery_price": number }`
   - أو `POST /api/driver/orders/{order_id}/accept` مع عرض السعر.
   - يربط الطلب/التوصيل بالسائق ويحدّث الحالة.

4. **Driver – تأكيد استلام من المتجر**
   - `POST /api/driver/deliveries/{delivery_id}/pickup`
   - تحديث حالة التوصيل إلى "picked_up" أو ما يعادلها.

5. **Driver – تأكيد التسليم (مع QR اختياري)**
   - `POST /api/driver/deliveries/{delivery_id}/confirm-delivery`
   - body اختياري: `{ "qr_code": "..." }` للتحقق.
   - يحدّث حالة التوصيل والطلب إلى "delivered".

6. **Representative – عملاء**
   - `GET /api/representative/clients?search=`
   - قائمة عملاء مرتبطين بالمندوب (من زيارات، متاجر، أو جدول عملاء إن وُجد).

7. **Representative – طلبات المندوب**
   - `GET /api/representative/orders?status=`
   - `GET /api/representative/orders/{id}`
   - `PUT /api/representative/orders/{id}/cancel` (حسب الصلاحيات).

8. **Representative – تقارير**
   - `GET /api/representative/reports?type=visits|orders|sales&from_date=&to_date=`
   - يرجع بيانات جاهزة للتقرير حسب النوع والفترة.

### أولوية متوسطة (تحسينات)

9. **Doctor – حالة الحساب (تحت المراجعة)**
   - في `GET /auth/me` أو `GET /doctor/profile`: إرجاع `account_status: pending_approval | approved` للطبيب.

10. **عميل – QR للطلب بعد الإنشاء**
    - `GET /api/orders/{id}/qr-code` لاستخدامه في الاستلام أو التحقق من التوصيل.

11. **Driver – تحديث موقع السائق**
    - `PUT /api/driver/location` body: `{ "latitude", "longitude" }` لتحديث الموقع في الوقت الفعلي (للتتبع والخريطة).

---

## ملخص Endpoints حسب الفلو (موجود حالياً)

- **عميل:** كل الـ endpoints تحت `auth:sanctum` (auth, user, addresses, cart, orders, prescriptions, reminders, doctors, bookings, delivery, payment, notifications, messages, favourites, settings, ratings, coupons, support, maps) — **كلها موجودة.**
- **طبيب:** `GET/POST /doctor/dashboard`, bookings, patients, prescriptions (CRUD + share, print), wallet, medical-centers, reports (prescriptions, products, patients), chat (conversations, conversation, send) — **موجودة.**
- **متجر/بائع:** `GET /shop/dashboard`, products (CRUD), orders (list, show, status), customers — **موجودة.**
- **سائق:** `GET /driver/dashboard`, `GET /driver/deliveries` — **موجودة**؛ **ناقص:** available-orders, delivery-details, offer/accept, pickup, confirm-delivery (مع QR).
- **مندوب مبيعات:** `GET /representative/dashboard`, products, visits (CRUD) — **موجودة**؛ **ناقص:** clients, orders, reports.

---

## الخطوة التالية

1. تنفيذ الـ endpoints ذات الأولوية العالية (السائق + المندوب) في الباك اند.
2. تحديث توثيق الـ API للموبايل (مرجع الـ 5 فلو) بعد التنفيذ.
3. إضافة الـ endpoints الجديدة لـ `mobile_endpoints.json` و `MOBILE_DEVELOPER_API_REFERENCE.md`.

---

**آخر تحديث:** 2025-02-09
