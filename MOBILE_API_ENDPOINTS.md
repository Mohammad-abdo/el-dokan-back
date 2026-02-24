# 📱 Eldokan Mobile API Endpoints Documentation

## 🔐 Base URL
```
https://your-domain.com/api
```

## 🔑 Authentication
جميع الـ endpoints المحمية تتطلب إرسال token في الـ header:
```
Authorization: Bearer {token}
```

---

## 📋 جدول المحتويات

1. [Authentication & Authorization](#1-authentication--authorization)
2. [Home & Public Data](#2-home--public-data)
3. [User Profile & Wallet](#3-user-profile--wallet)
4. [Addresses Management](#4-addresses-management)
5. [Shops & Products](#5-shops--products)
6. [Categories](#6-categories)
7. [Cart Management](#7-cart-management)
8. [Orders](#8-orders)
9. [Prescriptions](#9-prescriptions)
10. [Medication Reminders](#10-medication-reminders)
11. [Doctors](#11-doctors)
12. [Bookings](#12-bookings)
13. [Delivery Tracking](#13-delivery-tracking)
14. [Payment](#14-payment)
15. [Notifications](#15-notifications)
16. [Messages & Chat](#16-messages--chat)
17. [Favourites](#17-favourites)
18. [Ratings](#18-ratings)
19. [Coupons](#19-coupons)
20. [Settings & Support](#20-settings--support)
21. [Maps & Locations](#21-maps--locations)

---

## 1. Authentication & Authorization

### 1.1 Register User
**Endpoint:** `POST /api/auth/register`

**Description:** تسجيل مستخدم جديد

**Request Body:**
```json
{
  "username": "string (required, unique)",
  "phone": "string (required, unique)",
  "email": "string (optional, email format)",
  "password": "string (optional, min:6)"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "User registered successfully. Please verify OTP.",
  "data": {
    "user_id": 1,
    "phone": "+201234567890"
  }
}
```

---

### 1.2 Login
**Endpoint:** `POST /api/auth/login`

**Description:** تسجيل الدخول بالبريد الإلكتروني وكلمة المرور

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "username": "john_doe",
      "email": "user@example.com",
      "phone": "+201234567890",
      "avatar_url": "https://...",
      "wallet_balance": 0.00,
      "language_preference": "ar",
      "status": "active",
      "role": "user",
      "roles": [...],
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    "token": "1|xxxxxxxxxxxx"
  }
}
```

---

### 1.3 Social Login
**Endpoint:** `POST /api/auth/social-login`

**Description:** تسجيل الدخول عبر Google أو Apple

**Request Body:**
```json
{
  "provider": "google|apple",
  "provider_id": "string (required)",
  "email": "string (optional)",
  "name": "string (optional)"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {...},
    "token": "1|xxxxxxxxxxxx"
  }
}
```

---

### 1.4 Verify OTP
**Endpoint:** `POST /api/auth/verify-otp`

**Description:** التحقق من رمز OTP

**Request Body:**
```json
{
  "phone": "+201234567890",
  "otp": "123456"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "OTP verified successfully",
  "data": {
    "user": {...},
    "token": "1|xxxxxxxxxxxx"
  }
}
```

---

### 1.5 Resend OTP
**Endpoint:** `POST /api/auth/resend-otp`

**Description:** إعادة إرسال رمز OTP

**Request Body:**
```json
{
  "phone": "+201234567890"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "OTP resent successfully"
}
```

---

### 1.6 Guest Login
**Endpoint:** `GET /api/auth/guest-login`

**Description:** تسجيل دخول كضيف

**Response (200):**
```json
{
  "success": true,
  "message": "Guest session created",
  "data": {
    "guest_token": "xxxxxxxxxxxx"
  }
}
```

---

### 1.7 Get Current User
**Endpoint:** `GET /api/auth/me`

**Description:** الحصول على بيانات المستخدم الحالي

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "username": "john_doe",
    "email": "user@example.com",
    "phone": "+201234567890",
    "avatar_url": "https://...",
    "wallet_balance": 100.50,
    "language_preference": "ar",
    "status": "active",
    "role": "user",
    "roles": [...],
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

### 1.8 Logout
**Endpoint:** `POST /api/auth/logout`

**Description:** تسجيل الخروج

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

### 1.9 Change Password
**Endpoint:** `POST /api/auth/change-password`

**Description:** تغيير كلمة المرور

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "current_password": "oldpassword123",
  "new_password": "newpassword123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Password changed successfully"
}
```

---

### 1.10 Login with Prescription Link
**Endpoint:** `POST /api/auth/login-with-link`

**Description:** تسجيل الدخول عبر رابط الوصفة الطبية

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "link": "prescription-link-hash",
  "phone": "+201234567890"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {...},
    "token": "1|xxxxxxxxxxxx"
  }
}
```

---

## 2. Home & Public Data

### 2.1 Get Home Data
**Endpoint:** `GET /api/home`

**Description:** الحصول على بيانات الصفحة الرئيسية (سلايدر، فئات، منتجات مميزة، متاجر شائعة)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "sliders": [
      {
        "id": 1,
        "title": "Slider Title",
        "image_url": "https://...",
        "link": "https://...",
        "is_active": true
      }
    ],
    "categories": [
      {
        "id": 1,
        "name": "Category Name",
        "image_url": "https://...",
        "children": [...]
      }
    ],
    "featured_products": [...],
    "popular_shops": [...]
  }
}
```

---

### 2.2 Get Sliders
**Endpoint:** `GET /api/sliders`

**Description:** الحصول على جميع السلايدرات النشطة

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Slider Title",
      "image_url": "https://...",
      "link": "https://...",
      "is_active": true,
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

---

## 3. User Profile & Wallet

### 3.1 Get User Profile
**Endpoint:** `GET /api/user/profile`

**Description:** الحصول على ملف المستخدم الشخصي

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "john_doe",
    "email": "user@example.com",
    "phone": "+201234567890",
    "avatar_url": "https://...",
    "wallet_balance": 100.50,
    "language_preference": "ar",
    "status": "active",
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

### 3.2 Update User Profile
**Endpoint:** `PUT /api/user/profile`

**Description:** تحديث ملف المستخدم الشخصي

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "username": "new_username",
  "email": "newemail@example.com",
  "phone": "+201234567890",
  "avatar_url": "https://...",
  "language_preference": "ar|en"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "user": {...}
  }
}
```

---

### 3.3 Get User Wallet
**Endpoint:** `GET /api/user/wallet`

**Description:** الحصول على رصيد المحفظة

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "balance": 100.50,
    "currency": "EGP",
    "transactions": [...]
  }
}
```

---

### 3.4 Top Up Wallet
**Endpoint:** `POST /api/user/wallet/top-up`

**Description:** شحن المحفظة

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "amount": 100.00,
  "payment_method": "credit_card|wallet|..."
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Wallet topped up successfully",
  "data": {
    "new_balance": 200.50,
    "transaction_id": 123
  }
}
```

---

## 4. Addresses Management

### 4.1 Get All Addresses
**Endpoint:** `GET /api/addresses`

**Description:** الحصول على جميع عناوين المستخدم

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "title": "Home",
      "address_line_1": "123 Main St",
      "address_line_2": "Apt 4B",
      "city": "Cairo",
      "state": "Cairo",
      "postal_code": "12345",
      "country": "Egypt",
      "latitude": 30.0444,
      "longitude": 31.2357,
      "is_default": true,
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

---

### 4.2 Create Address
**Endpoint:** `POST /api/addresses`

**Description:** إضافة عنوان جديد

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "title": "Home|Work|Other",
  "address_line_1": "123 Main St",
  "address_line_2": "Apt 4B (optional)",
  "city": "Cairo",
  "state": "Cairo",
  "postal_code": "12345",
  "country": "Egypt",
  "latitude": 30.0444,
  "longitude": 31.2357,
  "is_default": false
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Address created successfully",
  "data": {
    "address": {...}
  }
}
```

---

### 4.3 Get Single Address
**Endpoint:** `GET /api/addresses/{id}`

**Description:** الحصول على عنوان محدد

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "address": {...}
  }
}
```

---

### 4.4 Update Address
**Endpoint:** `PUT /api/addresses/{id}`

**Description:** تحديث عنوان

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "title": "Home",
  "address_line_1": "456 New St",
  "city": "Alexandria",
  "state": "Alexandria",
  "postal_code": "54321",
  "country": "Egypt",
  "latitude": 31.2001,
  "longitude": 29.9187,
  "is_default": true
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Address updated successfully",
  "data": {
    "address": {...}
  }
}
```

---

### 4.5 Delete Address
**Endpoint:** `DELETE /api/addresses/{id}`

**Description:** حذف عنوان

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Address deleted successfully"
}
```

---

### 4.6 Set Default Address
**Endpoint:** `PUT /api/addresses/{id}/set-default`

**Description:** تعيين عنوان كافتراضي

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Default address updated successfully",
  "data": {
    "address": {...}
  }
}
```

---

## 5. Shops & Products

### 5.1 Get All Shops
**Endpoint:** `GET /api/shops`

**Description:** الحصول على قائمة المتاجر

**Query Parameters:**
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة
- `search` (optional): البحث بالاسم
- `category_id` (optional): تصفية حسب الفئة
- `rating` (optional): تصفية حسب التقييم

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Shop Name",
      "description": "Shop Description",
      "logo_url": "https://...",
      "cover_image_url": "https://...",
      "rating": 4.5,
      "total_reviews": 120,
      "is_active": true,
      "address": "123 Main St, Cairo",
      "phone": "+201234567890",
      "email": "shop@example.com",
      "working_hours": {...},
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 50,
    "last_page": 4
  }
}
```

---

### 5.2 Get Single Shop
**Endpoint:** `GET /api/shops/{id}`

**Description:** الحصول على تفاصيل متجر محدد

**Response (200):**
```json
{
  "success": true,
  "data": {
    "shop": {
      "id": 1,
      "name": "Shop Name",
      "description": "Shop Description",
      "logo_url": "https://...",
      "cover_image_url": "https://...",
      "rating": 4.5,
      "total_reviews": 120,
      "is_active": true,
      "address": "123 Main St, Cairo",
      "phone": "+201234567890",
      "email": "shop@example.com",
      "working_hours": {...},
      "products": [...],
      "reviews": [...],
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 5.3 Get All Products
**Endpoint:** `GET /api/products`

**Description:** الحصول على قائمة المنتجات

**Query Parameters:**
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة
- `search` (optional): البحث بالاسم
- `category_id` (optional): تصفية حسب الفئة
- `shop_id` (optional): تصفية حسب المتجر
- `min_price` (optional): الحد الأدنى للسعر
- `max_price` (optional): الحد الأقصى للسعر
- `sort_by` (optional): `price_asc|price_desc|rating|newest`

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Product Name",
      "description": "Product Description",
      "sku": "PROD-001",
      "price": 99.99,
      "discount_price": 79.99,
      "stock_quantity": 100,
      "images": [
        "https://...",
        "https://..."
      ],
      "category_id": 1,
      "category": {...},
      "shop_id": 1,
      "shop": {...},
      "rating": 4.5,
      "total_reviews": 50,
      "is_active": true,
      "is_prescription_required": false,
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 200,
    "last_page": 10
  }
}
```

---

### 5.4 Get Single Product
**Endpoint:** `GET /api/products/{id}`

**Description:** الحصول على تفاصيل منتج محدد

**Response (200):**
```json
{
  "success": true,
  "data": {
    "product": {
      "id": 1,
      "name": "Product Name",
      "description": "Product Description",
      "sku": "PROD-001",
      "price": 99.99,
      "discount_price": 79.99,
      "stock_quantity": 100,
      "images": [...],
      "category_id": 1,
      "category": {...},
      "shop_id": 1,
      "shop": {...},
      "rating": 4.5,
      "total_reviews": 50,
      "reviews": [...],
      "related_products": [...],
      "is_active": true,
      "is_prescription_required": false,
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 5.5 Filter Products
**Endpoint:** `POST /api/products/filter`

**Description:** تصفية المنتجات بمعايير متقدمة

**Request Body:**
```json
{
  "category_ids": [1, 2, 3],
  "shop_ids": [1, 2],
  "min_price": 10.00,
  "max_price": 500.00,
  "rating": 4.0,
  "is_prescription_required": false,
  "in_stock": true,
  "sort_by": "price_asc|price_desc|rating|newest",
  "page": 1,
  "per_page": 20
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "products": [...],
    "meta": {...}
  }
}
```

---

## 6. Categories

### 6.1 Get All Categories
**Endpoint:** `GET /api/categories`

**Description:** الحصول على قائمة الفئات الرئيسية

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Category Name",
      "name_ar": "اسم الفئة",
      "description": "Category Description",
      "image_url": "https://...",
      "icon": "icon-name",
      "parent_id": null,
      "is_active": true,
      "children": [...],
      "products_count": 50,
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

---

### 6.2 Get Single Category
**Endpoint:** `GET /api/categories/{id}`

**Description:** الحصول على تفاصيل فئة محددة مع المنتجات

**Response (200):**
```json
{
  "success": true,
  "data": {
    "category": {
      "id": 1,
      "name": "Category Name",
      "name_ar": "اسم الفئة",
      "description": "Category Description",
      "image_url": "https://...",
      "icon": "icon-name",
      "parent_id": null,
      "is_active": true,
      "children": [...],
      "products": [...],
      "products_count": 50,
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 6.3 Get Subcategories
**Endpoint:** `GET /api/subcategories`

**Description:** الحصول على جميع الفئات الفرعية

**Query Parameters:**
- `parent_id` (optional): تصفية حسب الفئة الأب

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "name": "Subcategory Name",
      "parent_id": 1,
      "parent": {...},
      "products_count": 25,
      "is_active": true
    }
  ]
}
```

---

## 7. Cart Management

### 7.1 Get Cart
**Endpoint:** `GET /api/cart`

**Description:** الحصول على محتويات السلة

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "product": {
          "id": 1,
          "name": "Product Name",
          "price": 99.99,
          "discount_price": 79.99,
          "images": [...]
        },
        "quantity": 2,
        "unit_price": 79.99,
        "total_price": 159.98,
        "created_at": "2024-01-01T00:00:00.000000Z"
      }
    ],
    "subtotal": 159.98,
    "tax": 15.00,
    "shipping": 10.00,
    "discount": 0.00,
    "total": 184.98,
    "items_count": 2
  }
}
```

---

### 7.2 Add to Cart
**Endpoint:** `POST /api/cart/add`

**Description:** إضافة منتج إلى السلة

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "product_id": 1,
  "quantity": 2
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Product added to cart successfully",
  "data": {
    "cart_item": {
      "id": 1,
      "product_id": 1,
      "quantity": 2,
      "unit_price": 79.99,
      "total_price": 159.98
    },
    "cart": {
      "items_count": 2,
      "total": 184.98
    }
  }
}
```

---

### 7.3 Update Cart Item
**Endpoint:** `PUT /api/cart/{id}`

**Description:** تحديث كمية منتج في السلة

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "quantity": 3
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Cart item updated successfully",
  "data": {
    "cart_item": {...},
    "cart": {...}
  }
}
```

---

### 7.4 Remove from Cart
**Endpoint:** `DELETE /api/cart/{id}`

**Description:** حذف منتج من السلة

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Item removed from cart successfully",
  "data": {
    "cart": {...}
  }
}
```

---

### 7.5 Clear Cart
**Endpoint:** `DELETE /api/cart`

**Description:** مسح السلة بالكامل

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Cart cleared successfully"
}
```

---

## 8. Orders

### 8.1 Get All Orders
**Endpoint:** `GET /api/orders`

**Description:** الحصول على قائمة جميع الطلبات

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة
- `status` (optional): تصفية حسب الحالة

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "order_number": "ORD-2024-001",
      "user_id": 1,
      "status": "pending|confirmed|processing|shipped|delivered|cancelled",
      "subtotal": 159.98,
      "tax": 15.00,
      "shipping": 10.00,
      "discount": 10.00,
      "total": 174.98,
      "payment_status": "paid|pending|failed",
      "payment_method": "credit_card|wallet|cash_on_delivery",
      "shipping_address": {...},
      "items": [...],
      "delivery": {...},
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 25,
    "last_page": 2
  }
}
```

---

### 8.2 Create Order
**Endpoint:** `POST /api/orders`

**Description:** إنشاء طلب جديد

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "address_id": 1,
  "payment_method": "credit_card|wallet|cash_on_delivery",
  "coupon_code": "DISCOUNT10 (optional)",
  "notes": "Delivery instructions (optional)"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "order": {
      "id": 1,
      "order_number": "ORD-2024-001",
      "status": "pending",
      "total": 174.98,
      "payment_status": "pending",
      "items": [...],
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 8.3 Get Single Order
**Endpoint:** `GET /api/orders/{id}`

**Description:** الحصول على تفاصيل طلب محدد

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "order": {
      "id": 1,
      "order_number": "ORD-2024-001",
      "status": "confirmed",
      "subtotal": 159.98,
      "tax": 15.00,
      "shipping": 10.00,
      "discount": 10.00,
      "total": 174.98,
      "payment_status": "paid",
      "payment_method": "credit_card",
      "shipping_address": {...},
      "items": [
        {
          "id": 1,
          "product_id": 1,
          "product": {...},
          "quantity": 2,
          "unit_price": 79.99,
          "total_price": 159.98
        }
      ],
      "delivery": {...},
      "status_history": [...],
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 8.4 Track Order
**Endpoint:** `GET /api/orders/{id}/track`

**Description:** تتبع حالة الطلب

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "order": {
      "id": 1,
      "order_number": "ORD-2024-001",
      "status": "shipped",
      "current_status": "shipped",
      "status_history": [
        {
          "status": "pending",
          "timestamp": "2024-01-01T10:00:00.000000Z"
        },
        {
          "status": "confirmed",
          "timestamp": "2024-01-01T10:05:00.000000Z"
        },
        {
          "status": "shipped",
          "timestamp": "2024-01-01T12:00:00.000000Z"
        }
      ],
      "estimated_delivery": "2024-01-02T14:00:00.000000Z",
      "delivery": {
        "driver": {...},
        "current_location": {
          "latitude": 30.0444,
          "longitude": 31.2357
        }
      }
    }
  }
}
```

---

### 8.5 Cancel Order
**Endpoint:** `PUT /api/orders/{id}/cancel`

**Description:** إلغاء طلب

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "reason": "Changed my mind (optional)"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Order cancelled successfully",
  "data": {
    "order": {
      "id": 1,
      "status": "cancelled",
      "cancellation_reason": "Changed my mind"
    }
  }
}
```

---

### 8.6 Get Orders by Status
**Endpoint:** `GET /api/orders/status/{status}`

**Description:** الحصول على الطلبات حسب الحالة

**Headers:**
```
Authorization: Bearer {token}
```

**Path Parameters:**
- `status`: `pending|confirmed|processing|shipped|delivered|cancelled`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "orders": [...],
    "meta": {...}
  }
}
```

---

## 9. Prescriptions

### 9.1 Upload Prescription
**Endpoint:** `POST /api/prescriptions/upload`

**Description:** رفع وصفة طبية

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
image: (file, required)
notes: (string, optional)
```

**Response (201):**
```json
{
  "success": true,
  "message": "Prescription uploaded successfully",
  "data": {
    "prescription": {
      "id": 1,
      "user_id": 1,
      "image_url": "https://...",
      "status": "pending",
      "notes": "Please process this prescription",
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 9.2 Get All Prescriptions
**Endpoint:** `GET /api/prescriptions`

**Description:** الحصول على قائمة جميع الوصفات الطبية

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة
- `status` (optional): تصفية حسب الحالة

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "image_url": "https://...",
      "status": "approved|pending|rejected",
      "notes": "Prescription notes",
      "medications": [...],
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {...}
}
```

---

### 9.3 Get Single Prescription
**Endpoint:** `GET /api/prescriptions/{id}`

**Description:** الحصول على تفاصيل وصفة طبية محددة

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "prescription": {
      "id": 1,
      "user_id": 1,
      "image_url": "https://...",
      "status": "approved",
      "notes": "Prescription notes",
      "medications": [
        {
          "id": 1,
          "name": "Medication Name",
          "dosage": "500mg",
          "frequency": "Twice daily",
          "duration": "7 days"
        }
      ],
      "doctor": {...},
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 9.4 Get Prescriptions by Status
**Endpoint:** `GET /api/prescriptions/status/{status}`

**Description:** الحصول على الوصفات الطبية حسب الحالة

**Headers:**
```
Authorization: Bearer {token}
```

**Path Parameters:**
- `status`: `pending|approved|rejected`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "prescriptions": [...],
    "meta": {...}
  }
}
```

---

### 9.5 View Prescription by Link
**Endpoint:** `GET /api/prescriptions/link/{link}`

**Description:** عرض وصفة طبية عبر رابط مشاركة

**Response (200):**
```json
{
  "success": true,
  "data": {
    "prescription": {...}
  }
}
```

---

## 10. Medication Reminders

### 10.1 Get All Reminders
**Endpoint:** `GET /api/reminders`

**Description:** الحصول على قائمة جميع تذكيرات الأدوية

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "medication_name": "Aspirin",
      "dosage": "500mg",
      "frequency": "daily",
      "times": ["08:00", "20:00"],
      "start_date": "2024-01-01",
      "end_date": "2024-01-07",
      "is_active": true,
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

---

### 10.2 Create Reminder
**Endpoint:** `POST /api/reminders`

**Description:** إنشاء تذكير دواء جديد

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "medication_name": "Aspirin",
  "dosage": "500mg",
  "frequency": "daily|weekly|custom",
  "times": ["08:00", "20:00"],
  "start_date": "2024-01-01",
  "end_date": "2024-01-07",
  "notes": "Take with food (optional)"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Medication reminder created successfully",
  "data": {
    "reminder": {...}
  }
}
```

---

### 10.3 Get Single Reminder
**Endpoint:** `GET /api/reminders/{id}`

**Description:** الحصول على تفاصيل تذكير محدد

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "reminder": {...}
  }
}
```

---

### 10.4 Update Reminder
**Endpoint:** `PUT /api/reminders/{id}`

**Description:** تحديث تذكير دواء

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "medication_name": "Aspirin",
  "dosage": "500mg",
  "frequency": "daily",
  "times": ["09:00", "21:00"],
  "start_date": "2024-01-01",
  "end_date": "2024-01-10",
  "notes": "Updated notes"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Reminder updated successfully",
  "data": {
    "reminder": {...}
  }
}
```

---

### 10.5 Delete Reminder
**Endpoint:** `DELETE /api/reminders/{id}`

**Description:** حذف تذكير دواء

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Reminder deleted successfully"
}
```

---

### 10.6 Toggle Reminder
**Endpoint:** `PUT /api/reminders/{id}/toggle`

**Description:** تفعيل/تعطيل تذكير دواء

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Reminder toggled successfully",
  "data": {
    "reminder": {
      "id": 1,
      "is_active": false
    }
  }
}
```

---

## 11. Doctors

### 11.1 Get All Doctors
**Endpoint:** `GET /api/doctors`

**Description:** الحصول على قائمة الأطباء

**Query Parameters:**
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة
- `search` (optional): البحث بالاسم
- `specialization` (optional): تصفية حسب التخصص
- `rating` (optional): تصفية حسب التقييم

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Dr. John Doe",
      "specialization": "Cardiology",
      "qualifications": "MBBS, MD",
      "experience_years": 10,
      "avatar_url": "https://...",
      "rating": 4.8,
      "total_reviews": 150,
      "consultation_fee": 200.00,
      "is_available": true,
      "medical_centers": [...],
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {...}
}
```

---

### 11.2 Get Single Doctor
**Endpoint:** `GET /api/doctors/{id}`

**Description:** الحصول على تفاصيل طبيب محدد

**Response (200):**
```json
{
  "success": true,
  "data": {
    "doctor": {
      "id": 1,
      "name": "Dr. John Doe",
      "specialization": "Cardiology",
      "qualifications": "MBBS, MD",
      "experience_years": 10,
      "avatar_url": "https://...",
      "bio": "Doctor biography",
      "rating": 4.8,
      "total_reviews": 150,
      "consultation_fee": 200.00,
      "is_available": true,
      "medical_centers": [...],
      "reviews": [...],
      "availability": {...},
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 11.3 Get Doctor Availability
**Endpoint:** `GET /api/doctors/{id}/availability`

**Description:** الحصول على أوقات توفر الطبيب

**Query Parameters:**
- `date` (optional): التاريخ المطلوب (YYYY-MM-DD)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "doctor_id": 1,
    "date": "2024-01-15",
    "available_slots": [
      {
        "time": "09:00",
        "is_available": true
      },
      {
        "time": "10:00",
        "is_available": false
      },
      {
        "time": "11:00",
        "is_available": true
      }
    ],
    "working_hours": {
      "monday": {"start": "09:00", "end": "17:00"},
      "tuesday": {"start": "09:00", "end": "17:00"},
      ...
    }
  }
}
```

---

## 12. Bookings

### 12.1 Get All Bookings
**Endpoint:** `GET /api/bookings`

**Description:** الحصول على قائمة جميع الحجوزات

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة
- `status` (optional): تصفية حسب الحالة

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "doctor_id": 1,
      "doctor": {...},
      "medical_center_id": 1,
      "medical_center": {...},
      "appointment_date": "2024-01-15",
      "appointment_time": "10:00",
      "status": "confirmed|pending|cancelled|completed",
      "consultation_type": "in_person|online",
      "consultation_fee": 200.00,
      "payment_status": "paid|pending",
      "notes": "Patient notes",
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {...}
}
```

---

### 12.2 Create Booking
**Endpoint:** `POST /api/bookings`

**Description:** إنشاء حجز جديد

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "doctor_id": 1,
  "medical_center_id": 1,
  "appointment_date": "2024-01-15",
  "appointment_time": "10:00",
  "consultation_type": "in_person|online",
  "notes": "Patient notes (optional)"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "booking": {
      "id": 1,
      "doctor_id": 1,
      "appointment_date": "2024-01-15",
      "appointment_time": "10:00",
      "status": "pending",
      "consultation_fee": 200.00,
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 12.3 Get Single Booking
**Endpoint:** `GET /api/bookings/{id}`

**Description:** الحصول على تفاصيل حجز محدد

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "booking": {
      "id": 1,
      "user_id": 1,
      "doctor_id": 1,
      "doctor": {...},
      "medical_center_id": 1,
      "medical_center": {...},
      "appointment_date": "2024-01-15",
      "appointment_time": "10:00",
      "status": "confirmed",
      "consultation_type": "in_person",
      "consultation_fee": 200.00,
      "payment_status": "paid",
      "notes": "Patient notes",
      "prescription": {...},
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 12.4 Cancel Booking
**Endpoint:** `PUT /api/bookings/{id}/cancel`

**Description:** إلغاء حجز

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "reason": "Cancellation reason (optional)"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Booking cancelled successfully",
  "data": {
    "booking": {
      "id": 1,
      "status": "cancelled"
    }
  }
}
```

---

### 12.5 Rate Booking
**Endpoint:** `POST /api/bookings/{id}/rate`

**Description:** تقييم حجز/طبيب

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "rating": 5,
  "comment": "Great doctor, very professional"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Rating submitted successfully",
  "data": {
    "rating": {
      "id": 1,
      "booking_id": 1,
      "rating": 5,
      "comment": "Great doctor, very professional",
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 12.6 Submit Complaint
**Endpoint:** `POST /api/bookings/{id}/complaint`

**Description:** تقديم شكوى على حجز

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "subject": "Complaint Subject",
  "message": "Complaint details",
  "type": "service|doctor|other"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Complaint submitted successfully",
  "data": {
    "complaint": {
      "id": 1,
      "booking_id": 1,
      "subject": "Complaint Subject",
      "message": "Complaint details",
      "status": "pending"
    }
  }
}
```

---

### 12.7 Get Bookings by Status
**Endpoint:** `GET /api/bookings/status/{status}`

**Description:** الحصول على الحجوزات حسب الحالة

**Headers:**
```
Authorization: Bearer {token}
```

**Path Parameters:**
- `status`: `pending|confirmed|cancelled|completed`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "bookings": [...],
    "meta": {...}
  }
}
```

---

## 13. Delivery Tracking

### 13.1 Track Delivery
**Endpoint:** `GET /api/deliveries/{order_id}/track`

**Description:** تتبع حالة التوصيل

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "order_id": 1,
    "order_number": "ORD-2024-001",
    "status": "in_transit",
    "current_location": {
      "latitude": 30.0444,
      "longitude": 31.2357,
      "address": "Current location address"
    },
    "driver": {
      "id": 1,
      "name": "Driver Name",
      "phone": "+201234567890",
      "vehicle_type": "motorcycle|car",
      "vehicle_number": "ABC-123"
    },
    "estimated_arrival": "2024-01-02T14:00:00.000000Z",
    "tracking_history": [
      {
        "status": "confirmed",
        "timestamp": "2024-01-01T10:00:00.000000Z"
      },
      {
        "status": "picked_up",
        "timestamp": "2024-01-02T12:00:00.000000Z"
      },
      {
        "status": "in_transit",
        "timestamp": "2024-01-02T13:00:00.000000Z"
      }
    ]
  }
}
```

---

### 13.2 Get Driver Info
**Endpoint:** `GET /api/deliveries/{order_id}/driver`

**Description:** الحصول على معلومات السائق

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "driver": {
      "id": 1,
      "name": "Driver Name",
      "phone": "+201234567890",
      "avatar_url": "https://...",
      "rating": 4.5,
      "vehicle_type": "motorcycle",
      "vehicle_number": "ABC-123",
      "current_location": {
        "latitude": 30.0444,
        "longitude": 31.2357
      }
    }
  }
}
```

---

### 13.3 Contact Driver
**Endpoint:** `POST /api/deliveries/{order_id}/contact-driver`

**Description:** التواصل مع السائق

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Driver contact information",
  "data": {
    "driver_phone": "+201234567890",
    "can_call": true,
    "can_message": true
  }
}
```

---

### 13.4 Confirm Delivery
**Endpoint:** `POST /api/deliveries/{order_id}/confirm`

**Description:** تأكيد استلام الطلب

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Delivery confirmed successfully",
  "data": {
    "order": {
      "id": 1,
      "status": "delivered",
      "delivered_at": "2024-01-02T14:00:00.000000Z"
    }
  }
}
```

---

### 13.5 Get Delivery Map
**Endpoint:** `GET /api/deliveries/{order_id}/map`

**Description:** الحصول على خريطة التوصيل

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "order_id": 1,
    "pickup_location": {
      "latitude": 30.0444,
      "longitude": 31.2357,
      "address": "Shop address"
    },
    "delivery_location": {
      "latitude": 30.0626,
      "longitude": 31.2497,
      "address": "Delivery address"
    },
    "driver_location": {
      "latitude": 30.0500,
      "longitude": 31.2400,
      "address": "Current driver location"
    },
    "route": {
      "distance": "5.2 km",
      "estimated_time": "15 minutes"
    }
  }
}
```

---

## 14. Payment

### 14.1 Process Payment
**Endpoint:** `POST /api/payments/process`

**Description:** معالجة الدفع

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "order_id": 1,
  "payment_method": "credit_card|wallet|cash_on_delivery",
  "card_token": "card_token (if credit_card)",
  "amount": 174.98
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Payment processed successfully",
  "data": {
    "payment": {
      "id": 1,
      "order_id": 1,
      "amount": 174.98,
      "payment_method": "credit_card",
      "status": "completed",
      "transaction_id": "TXN-2024-001",
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 14.2 Get Payment Methods
**Endpoint:** `GET /api/payments/methods`

**Description:** الحصول على طرق الدفع المتاحة

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "methods": [
      {
        "id": "credit_card",
        "name": "Credit Card",
        "icon": "https://...",
        "is_available": true
      },
      {
        "id": "wallet",
        "name": "Wallet",
        "icon": "https://...",
        "is_available": true
      },
      {
        "id": "cash_on_delivery",
        "name": "Cash on Delivery",
        "icon": "https://...",
        "is_available": true
      }
    ]
  }
}
```

---

### 14.3 Apply Discount
**Endpoint:** `POST /api/payments/apply-discount`

**Description:** تطبيق كود خصم

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "coupon_code": "DISCOUNT10",
  "order_id": 1
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Discount applied successfully",
  "data": {
    "coupon": {
      "id": 1,
      "code": "DISCOUNT10",
      "discount_type": "percentage|fixed",
      "discount_value": 10,
      "discount_amount": 17.50
    },
    "order": {
      "id": 1,
      "subtotal": 159.98,
      "discount": 17.50,
      "total": 157.48
    }
  }
}
```

---

### 14.4 Get Payment History
**Endpoint:** `GET /api/payments/history`

**Description:** الحصول على سجل المدفوعات

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "order_id": 1,
      "order_number": "ORD-2024-001",
      "amount": 174.98,
      "payment_method": "credit_card",
      "status": "completed",
      "transaction_id": "TXN-2024-001",
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {...}
}
```

---

## 15. Notifications

### 15.1 Get All Notifications
**Endpoint:** `GET /api/notifications`

**Description:** الحصول على قائمة جميع الإشعارات

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة
- `type` (optional): تصفية حسب النوع
- `is_read` (optional): `true|false`

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "type": "order|booking|prescription|general",
      "title": "Notification Title",
      "message": "Notification message",
      "data": {
        "order_id": 1,
        "order_number": "ORD-2024-001"
      },
      "is_read": false,
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {...}
}
```

---

### 15.2 Get Unread Count
**Endpoint:** `GET /api/notifications/unread-count`

**Description:** الحصول على عدد الإشعارات غير المقروءة

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "unread_count": 5
  }
}
```

---

### 15.3 Mark Notification as Read
**Endpoint:** `PUT /api/notifications/{id}/read`

**Description:** تعليم إشعار كمقروء

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Notification marked as read",
  "data": {
    "notification": {
      "id": 1,
      "is_read": true
    }
  }
}
```

---

### 15.4 Mark All as Read
**Endpoint:** `PUT /api/notifications/read-all`

**Description:** تعليم جميع الإشعارات كمقروءة

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "All notifications marked as read",
  "data": {
    "updated_count": 5
  }
}
```

---

## 16. Messages & Chat

### 16.1 Get All Conversations
**Endpoint:** `GET /api/messages`

**Description:** الحصول على قائمة جميع المحادثات

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "user_id": 2,
      "user": {
        "id": 2,
        "name": "User Name",
        "avatar_url": "https://..."
      },
      "last_message": {
        "id": 10,
        "message": "Last message text",
        "created_at": "2024-01-01T12:00:00.000000Z"
      },
      "unread_count": 2,
      "updated_at": "2024-01-01T12:00:00.000000Z"
    }
  ]
}
```

---

### 16.2 Get Conversation
**Endpoint:** `GET /api/messages/{user_id}/conversation`

**Description:** الحصول على محادثة مع مستخدم محدد

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة

**Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 2,
      "name": "User Name",
      "avatar_url": "https://..."
    },
    "messages": [
      {
        "id": 1,
        "sender_id": 1,
        "receiver_id": 2,
        "message": "Message text",
        "message_type": "text|voice|image",
        "voice_url": "https://... (if voice)",
        "image_url": "https://... (if image)",
        "is_read": true,
        "created_at": "2024-01-01T10:00:00.000000Z"
      }
    ],
    "meta": {...}
  }
}
```

---

### 16.3 Send Message
**Endpoint:** `POST /api/messages`

**Description:** إرسال رسالة نصية

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "receiver_id": 2,
  "message": "Message text",
  "message_type": "text"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "message": {
      "id": 1,
      "sender_id": 1,
      "receiver_id": 2,
      "message": "Message text",
      "message_type": "text",
      "is_read": false,
      "created_at": "2024-01-01T10:00:00.000000Z"
    }
  }
}
```

---

### 16.4 Send Voice Message
**Endpoint:** `POST /api/messages/voice`

**Description:** إرسال رسالة صوتية

**Headers:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
receiver_id: 2
voice: (file, required)
duration: 30 (optional, in seconds)
```

**Response (201):**
```json
{
  "success": true,
  "message": "Voice message sent successfully",
  "data": {
    "message": {
      "id": 1,
      "sender_id": 1,
      "receiver_id": 2,
      "message_type": "voice",
      "voice_url": "https://...",
      "duration": 30,
      "is_read": false,
      "created_at": "2024-01-01T10:00:00.000000Z"
    }
  }
}
```

---

### 16.5 Mark Message as Read
**Endpoint:** `PUT /api/messages/{id}/read`

**Description:** تعليم رسالة كمقروءة

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Message marked as read",
  "data": {
    "message": {
      "id": 1,
      "is_read": true
    }
  }
}
```

---

## 17. Favourites

### 17.1 Get All Favourites
**Endpoint:** `GET /api/favourites`

**Description:** الحصول على قائمة المنتجات المفضلة

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "product": {
        "id": 1,
        "name": "Product Name",
        "price": 99.99,
        "discount_price": 79.99,
        "images": [...],
        "rating": 4.5
      },
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {...}
}
```

---

### 17.2 Add to Favourites
**Endpoint:** `POST /api/favourites`

**Description:** إضافة منتج إلى المفضلة

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "product_id": 1
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Product added to favourites",
  "data": {
    "favourite": {
      "id": 1,
      "product_id": 1,
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 17.3 Remove from Favourites
**Endpoint:** `DELETE /api/favourites/{product_id}`

**Description:** حذف منتج من المفضلة

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Product removed from favourites"
}
```

---

### 17.4 Check if Product is Favourite
**Endpoint:** `GET /api/favourites/{product_id}/check`

**Description:** التحقق من وجود منتج في المفضلة

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "is_favourite": true,
    "favourite_id": 1
  }
}
```

---

## 18. Ratings

### 18.1 Create Rating
**Endpoint:** `POST /api/ratings`

**Description:** إنشاء تقييم جديد

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "rateable_type": "product|shop|doctor",
  "rateable_id": 1,
  "rating": 5,
  "comment": "Great product!"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Rating created successfully",
  "data": {
    "rating": {
      "id": 1,
      "user_id": 1,
      "rateable_type": "product",
      "rateable_id": 1,
      "rating": 5,
      "comment": "Great product!",
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 18.2 Get Ratings
**Endpoint:** `GET /api/ratings`

**Description:** الحصول على قائمة التقييمات

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `rateable_type` (optional): `product|shop|doctor`
- `rateable_id` (optional): ID
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "user": {
        "id": 1,
        "name": "User Name",
        "avatar_url": "https://..."
      },
      "rateable_type": "product",
      "rateable_id": 1,
      "rating": 5,
      "comment": "Great product!",
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {...}
}
```

---

## 19. Coupons

### 19.1 Validate Coupon
**Endpoint:** `POST /api/coupons/validate`

**Description:** التحقق من صحة كود الخصم

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "coupon_code": "DISCOUNT10",
  "order_id": 1,
  "total_amount": 174.98
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Coupon is valid",
  "data": {
    "coupon": {
      "id": 1,
      "code": "DISCOUNT10",
      "discount_type": "percentage|fixed",
      "discount_value": 10,
      "discount_amount": 17.50,
      "min_purchase": 100.00,
      "max_discount": 50.00,
      "valid_from": "2024-01-01",
      "valid_until": "2024-12-31"
    },
    "discount_applied": 17.50,
    "new_total": 157.48
  }
}
```

---

## 20. Settings & Support

### 20.1 Get Settings
**Endpoint:** `GET /api/settings`

**Description:** الحصول على إعدادات المستخدم

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "language_preference": "ar|en",
    "notifications_enabled": true,
    "push_notifications": true,
    "email_notifications": true,
    "sms_notifications": false,
    "theme": "light|dark",
    "currency": "EGP"
  }
}
```

---

### 20.2 Update Settings
**Endpoint:** `PUT /api/settings`

**Description:** تحديث إعدادات المستخدم

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "language_preference": "ar|en",
  "notifications_enabled": true,
  "push_notifications": true,
  "email_notifications": true,
  "sms_notifications": false,
  "theme": "light|dark"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Settings updated successfully",
  "data": {
    "settings": {...}
  }
}
```

---

### 20.3 Get Support Info
**Endpoint:** `GET /api/support`

**Description:** الحصول على معلومات الدعم الفني

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "support_email": "support@eldokan.com",
    "support_phone": "+201234567890",
    "support_hours": "9 AM - 5 PM",
    "faq_url": "https://...",
    "help_center_url": "https://..."
  }
}
```

---

### 20.4 Create Support Ticket
**Endpoint:** `POST /api/support/tickets`

**Description:** إنشاء تذكرة دعم فني

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "subject": "Support Ticket Subject",
  "message": "Support ticket message",
  "type": "technical|billing|general",
  "priority": "low|medium|high|urgent"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Support ticket created successfully",
  "data": {
    "ticket": {
      "id": 1,
      "ticket_number": "TKT-2024-001",
      "subject": "Support Ticket Subject",
      "status": "open",
      "priority": "medium",
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 20.5 Get All Support Tickets
**Endpoint:** `GET /api/support/tickets`

**Description:** الحصول على قائمة تذاكر الدعم

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة
- `status` (optional): `open|closed|pending`

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "ticket_number": "TKT-2024-001",
      "subject": "Support Ticket Subject",
      "status": "open",
      "priority": "medium",
      "messages": [...],
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {...}
}
```

---

### 20.6 Get Single Support Ticket
**Endpoint:** `GET /api/support/tickets/{id}`

**Description:** الحصول على تفاصيل تذكرة دعم

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "ticket": {
      "id": 1,
      "ticket_number": "TKT-2024-001",
      "subject": "Support Ticket Subject",
      "message": "Support ticket message",
      "status": "open",
      "priority": "medium",
      "messages": [
        {
          "id": 1,
          "message": "Response message",
          "sender_type": "user|support",
          "created_at": "2024-01-01T00:00:00.000000Z"
        }
      ],
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

### 20.7 Add Message to Support Ticket
**Endpoint:** `POST /api/support/tickets/{id}/messages`

**Description:** إضافة رسالة إلى تذكرة الدعم

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "message": "Additional message text"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Message added successfully",
  "data": {
    "message": {
      "id": 2,
      "ticket_id": 1,
      "message": "Additional message text",
      "sender_type": "user",
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  }
}
```

---

## 21. Maps & Locations

### 21.1 Get Medical Centers
**Endpoint:** `GET /api/maps/medical-centers`

**Description:** الحصول على قائمة المراكز الطبية على الخريطة

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `latitude` (optional): خط العرض
- `longitude` (optional): خط الطول
- `radius` (optional): نصف القطر بالكيلومتر (default: 10)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Medical Center Name",
      "address": "123 Main St, Cairo",
      "latitude": 30.0444,
      "longitude": 31.2357,
      "phone": "+201234567890",
      "distance": "2.5 km",
      "doctors": [...]
    }
  ]
}
```

---

### 21.2 Get Doctor Clinics
**Endpoint:** `GET /api/maps/doctors/{doctor_id}/clinics`

**Description:** الحصول على عيادات طبيب محدد على الخريطة

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "doctor_id": 1,
    "doctor": {...},
    "clinics": [
      {
        "id": 1,
        "name": "Clinic Name",
        "address": "123 Main St, Cairo",
        "latitude": 30.0444,
        "longitude": 31.2357,
        "phone": "+201234567890",
        "working_hours": {...}
      }
    ]
  }
}
```

---

### 21.3 Calculate Distance
**Endpoint:** `GET /api/maps/calculate-distance`

**Description:** حساب المسافة بين موقعين

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `from_latitude` (required): خط عرض النقطة الأولى
- `from_longitude` (required): خط طول النقطة الأولى
- `to_latitude` (required): خط عرض النقطة الثانية
- `to_longitude` (required): خط طول النقطة الثانية

**Response (200):**
```json
{
  "success": true,
  "data": {
    "distance": {
      "kilometers": 5.2,
      "meters": 5200,
      "miles": 3.23
    },
    "duration": {
      "minutes": 15,
      "seconds": 900
    },
    "route": {
      "from": {
        "latitude": 30.0444,
        "longitude": 31.2357,
        "address": "Starting address"
      },
      "to": {
        "latitude": 30.0626,
        "longitude": 31.2497,
        "address": "Destination address"
      }
    }
  }
}
```

---

## 📝 ملاحظات مهمة

### Status Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

### Error Response Format
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Pagination Format
```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "https://...",
    "last": "https://...",
    "prev": null,
    "next": "https://..."
  }
}
```

### Date Format
جميع التواريخ في formato ISO 8601:
```
2024-01-01T00:00:00.000000Z
```

### Image URLs
جميع روابط الصور تكون كاملة:
```
https://your-domain.com/storage/images/...
```

---

## 🔄 Version
**API Version:** 1.0.0  
**Last Updated:** 2024-01-01

---

## 📞 Support
للحصول على المساعدة أو الإبلاغ عن مشاكل، يرجى التواصل مع فريق الدعم الفني.

