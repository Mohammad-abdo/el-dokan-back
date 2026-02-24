# 📱 Eldokan Mobile API - Quick Reference Guide

## 🔗 Base URL
```
https://your-domain.com/api
```

## 🔑 Authentication Header
```
Authorization: Bearer {token}
```

---

## 📋 جميع الـ Endpoints في جدول واحد

### 🔐 Authentication (10 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/auth/register` | تسجيل مستخدم جديد | ❌ |
| POST | `/api/auth/login` | تسجيل الدخول | ❌ |
| POST | `/api/auth/social-login` | تسجيل دخول اجتماعي | ❌ |
| POST | `/api/auth/verify-otp` | التحقق من OTP | ❌ |
| POST | `/api/auth/resend-otp` | إعادة إرسال OTP | ❌ |
| GET | `/api/auth/guest-login` | تسجيل دخول كضيف | ❌ |
| GET | `/api/auth/me` | بيانات المستخدم الحالي | ✅ |
| POST | `/api/auth/logout` | تسجيل الخروج | ✅ |
| POST | `/api/auth/change-password` | تغيير كلمة المرور | ✅ |
| POST | `/api/auth/login-with-link` | تسجيل دخول برابط وصفة | ✅ |

---

### 🏠 Home & Public (3 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/home` | بيانات الصفحة الرئيسية | ❌ |
| GET | `/api/sliders` | قائمة السلايدرات | ❌ |

---

### 👤 User Profile & Wallet (4 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/user/profile` | ملف المستخدم | ✅ |
| PUT | `/api/user/profile` | تحديث الملف | ✅ |
| GET | `/api/user/wallet` | رصيد المحفظة | ✅ |
| POST | `/api/user/wallet/top-up` | شحن المحفظة | ✅ |

---

### 📍 Addresses (6 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/addresses` | قائمة العناوين | ✅ |
| POST | `/api/addresses` | إضافة عنوان | ✅ |
| GET | `/api/addresses/{id}` | تفاصيل عنوان | ✅ |
| PUT | `/api/addresses/{id}` | تحديث عنوان | ✅ |
| DELETE | `/api/addresses/{id}` | حذف عنوان | ✅ |
| PUT | `/api/addresses/{id}/set-default` | تعيين كافتراضي | ✅ |

---

### 🏪 Shops & Products (5 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/shops` | قائمة المتاجر | ❌ |
| GET | `/api/shops/{id}` | تفاصيل متجر | ❌ |
| GET | `/api/products` | قائمة المنتجات | ❌ |
| GET | `/api/products/{id}` | تفاصيل منتج | ❌ |
| POST | `/api/products/filter` | تصفية المنتجات | ❌ |

---

### 📂 Categories (3 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/categories` | قائمة الفئات | ❌ |
| GET | `/api/categories/{id}` | تفاصيل فئة | ❌ |
| GET | `/api/subcategories` | قائمة الفئات الفرعية | ❌ |

---

### 🛒 Cart (5 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/cart` | محتويات السلة | ✅ |
| POST | `/api/cart/add` | إضافة للسلة | ✅ |
| PUT | `/api/cart/{id}` | تحديث عنصر | ✅ |
| DELETE | `/api/cart/{id}` | حذف عنصر | ✅ |
| DELETE | `/api/cart` | مسح السلة | ✅ |

---

### 📦 Orders (6 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/orders` | قائمة الطلبات | ✅ |
| POST | `/api/orders` | إنشاء طلب | ✅ |
| GET | `/api/orders/{id}` | تفاصيل طلب | ✅ |
| GET | `/api/orders/{id}/track` | تتبع الطلب | ✅ |
| PUT | `/api/orders/{id}/cancel` | إلغاء طلب | ✅ |
| GET | `/api/orders/status/{status}` | طلبات حسب الحالة | ✅ |

---

### 💊 Prescriptions (5 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/prescriptions/upload` | رفع وصفة طبية | ✅ |
| GET | `/api/prescriptions` | قائمة الوصفات | ✅ |
| GET | `/api/prescriptions/{id}` | تفاصيل وصفة | ✅ |
| GET | `/api/prescriptions/status/{status}` | وصفات حسب الحالة | ✅ |
| GET | `/api/prescriptions/link/{link}` | عرض وصفة برابط | ❌ |

---

### ⏰ Medication Reminders (6 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/reminders` | قائمة التذكيرات | ✅ |
| POST | `/api/reminders` | إنشاء تذكير | ✅ |
| GET | `/api/reminders/{id}` | تفاصيل تذكير | ✅ |
| PUT | `/api/reminders/{id}` | تحديث تذكير | ✅ |
| DELETE | `/api/reminders/{id}` | حذف تذكير | ✅ |
| PUT | `/api/reminders/{id}/toggle` | تفعيل/تعطيل | ✅ |

---

### 👨‍⚕️ Doctors (3 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/doctors` | قائمة الأطباء | ❌ |
| GET | `/api/doctors/{id}` | تفاصيل طبيب | ❌ |
| GET | `/api/doctors/{id}/availability` | أوقات التوفر | ❌ |

---

### 📅 Bookings (7 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/bookings` | قائمة الحجوزات | ✅ |
| POST | `/api/bookings` | إنشاء حجز | ✅ |
| GET | `/api/bookings/{id}` | تفاصيل حجز | ✅ |
| PUT | `/api/bookings/{id}/cancel` | إلغاء حجز | ✅ |
| POST | `/api/bookings/{id}/rate` | تقييم حجز | ✅ |
| POST | `/api/bookings/{id}/complaint` | تقديم شكوى | ✅ |
| GET | `/api/bookings/status/{status}` | حجوزات حسب الحالة | ✅ |

---

### 🚚 Delivery Tracking (5 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/deliveries/{order_id}/track` | تتبع التوصيل | ✅ |
| GET | `/api/deliveries/{order_id}/driver` | معلومات السائق | ✅ |
| POST | `/api/deliveries/{order_id}/contact-driver` | التواصل مع السائق | ✅ |
| POST | `/api/deliveries/{order_id}/confirm` | تأكيد الاستلام | ✅ |
| GET | `/api/deliveries/{order_id}/map` | خريطة التوصيل | ✅ |

---

### 💳 Payment (4 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/payments/process` | معالجة الدفع | ✅ |
| GET | `/api/payments/methods` | طرق الدفع | ✅ |
| POST | `/api/payments/apply-discount` | تطبيق خصم | ✅ |
| GET | `/api/payments/history` | سجل المدفوعات | ✅ |

---

### 🔔 Notifications (4 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/notifications` | قائمة الإشعارات | ✅ |
| GET | `/api/notifications/unread-count` | عدد غير المقروءة | ✅ |
| PUT | `/api/notifications/{id}/read` | تعليم كمقروء | ✅ |
| PUT | `/api/notifications/read-all` | تعليم الكل كمقروء | ✅ |

---

### 💬 Messages & Chat (5 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/messages` | قائمة المحادثات | ✅ |
| GET | `/api/messages/{user_id}/conversation` | محادثة مع مستخدم | ✅ |
| POST | `/api/messages` | إرسال رسالة | ✅ |
| POST | `/api/messages/voice` | إرسال رسالة صوتية | ✅ |
| PUT | `/api/messages/{id}/read` | تعليم كمقروء | ✅ |

---

### ⭐ Favourites (4 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/favourites` | قائمة المفضلة | ✅ |
| POST | `/api/favourites` | إضافة للمفضلة | ✅ |
| DELETE | `/api/favourites/{product_id}` | حذف من المفضلة | ✅ |
| GET | `/api/favourites/{product_id}/check` | التحقق من المفضلة | ✅ |

---

### ⭐ Ratings (2 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/ratings` | إنشاء تقييم | ✅ |
| GET | `/api/ratings` | قائمة التقييمات | ✅ |

---

### 🎟️ Coupons (1 endpoint)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/coupons/validate` | التحقق من كود خصم | ✅ |

---

### ⚙️ Settings & Support (7 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/settings` | الإعدادات | ✅ |
| PUT | `/api/settings` | تحديث الإعدادات | ✅ |
| GET | `/api/support` | معلومات الدعم | ✅ |
| POST | `/api/support/tickets` | إنشاء تذكرة | ✅ |
| GET | `/api/support/tickets` | قائمة التذاكر | ✅ |
| GET | `/api/support/tickets/{id}` | تفاصيل تذكرة | ✅ |
| POST | `/api/support/tickets/{id}/messages` | إضافة رسالة | ✅ |

---

### 🗺️ Maps & Locations (3 endpoints)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/maps/medical-centers` | المراكز الطبية | ✅ |
| GET | `/api/maps/doctors/{doctor_id}/clinics` | عيادات طبيب | ✅ |
| GET | `/api/maps/calculate-distance` | حساب المسافة | ✅ |

---

## 📊 إحصائيات الـ Endpoints

- **إجمالي الـ Endpoints:** 100+
- **Endpoints عامة (بدون Auth):** 12
- **Endpoints محمية (مع Auth):** 90+
- **Endpoints للـ POST:** 30+
- **Endpoints للـ GET:** 50+
- **Endpoints للـ PUT:** 15+
- **Endpoints للـ DELETE:** 10+

---

## 🔄 Request/Response Examples

### Example 1: Register User
```http
POST /api/auth/register
Content-Type: application/json

{
  "username": "john_doe",
  "phone": "+201234567890",
  "email": "john@example.com",
  "password": "password123"
}
```

### Example 2: Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

### Example 3: Get Products (with Auth)
```http
GET /api/products?page=1&per_page=20&category_id=1
Authorization: Bearer 1|xxxxxxxxxxxx
```

### Example 4: Add to Cart
```http
POST /api/cart/add
Authorization: Bearer 1|xxxxxxxxxxxx
Content-Type: application/json

{
  "product_id": 1,
  "quantity": 2
}
```

### Example 5: Create Order
```http
POST /api/orders
Authorization: Bearer 1|xxxxxxxxxxxx
Content-Type: application/json

{
  "address_id": 1,
  "payment_method": "credit_card",
  "coupon_code": "DISCOUNT10",
  "notes": "Please deliver before 5 PM"
}
```

---

## 📱 Mobile App Integration Tips

### 1. Authentication Flow
```
1. Register → Get user_id
2. Verify OTP → Get token
3. Store token securely
4. Use token in all protected endpoints
```

### 2. Token Management
- Store token in secure storage (Keychain/Keystore)
- Refresh token before expiry
- Handle 401 errors (token expired)

### 3. Error Handling
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Error details"]
  }
}
```

### 4. Pagination
Always check `meta` object for pagination:
```json
{
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "last_page": 5
  }
}
```

### 5. Image Uploads
Use `multipart/form-data` for file uploads:
- Prescriptions
- Voice messages
- Profile pictures

### 6. Real-time Updates
- Use WebSocket for:
  - Order tracking
  - Messages
  - Notifications
  - Delivery updates

---

## 🚀 Quick Start Checklist

- [ ] Configure base URL
- [ ] Implement authentication flow
- [ ] Store token securely
- [ ] Handle token refresh
- [ ] Implement error handling
- [ ] Add loading states
- [ ] Handle pagination
- [ ] Implement image uploads
- [ ] Add offline support
- [ ] Test all endpoints

---

## 📞 Support

للحصول على المساعدة:
- Email: support@eldokan.com
- Phone: +201234567890
- Documentation: See `MOBILE_API_ENDPOINTS.md` for detailed docs

---

**Last Updated:** 2024-01-01  
**API Version:** 1.0.0

