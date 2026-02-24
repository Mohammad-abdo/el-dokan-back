# Postman Collections - Ad-Dukkan Mobile (6 Flows)

مجموعات Postman احترافية لكل فلو في تطبيق الموبايل.

## الملفات

| الملف | الفلو |
|-------|--------|
| `Flow_1_End_User.postman_collection.json` | عميل (End User): Auth، Home، Cart، Orders، Profile |
| `Flow_2_Sales_Representative.postman_collection.json` | مندوب مبيعات: Dashboard، Clients، Visits، Orders، Reports |
| `Flow_3_Driver.postman_collection.json` | سائق: Available Orders، Offer، Accept، Pickup، Confirm Delivery، Location |
| `Flow_4_Doctor.postman_collection.json` | طبيب: Dashboard، Bookings، Prescriptions، Wallet، Reports، Chat |
| `Flow_5_Vendor_Shop.postman_collection.json` | بائع/متجر: Dashboard، Products، Orders، Customers |
| `Flow_6_Company.postman_collection.json` | شركة: نفس endpoints المتجر تحت `/api/company/` |

## الاستخدام

1. استورد الملف (أو الملفات) في Postman: **Import** → اختر الـ JSON.
2. عيّن المتغيرات في الـ Collection:
   - `base_url`: عنوان الـ API (مثال: `https://your-domain.com`)
   - `token`: توكن الدخول بعد استدعاء Login (انسخه من الـ Response وضعّه في Token).
3. للطلبات المحمية تأكد أن الـ Header يحتوي على: `Authorization: Bearer {{token}}`.

## ملاحظة

- فلو العميل (Flow 1): نفّذ **Login** أولاً ثم انسخ الـ `token` من الـ response إلى متغير الـ collection.
- فلو الشركة (Flow 6) يستخدم نفس منطق المتجر؛ المستخدم يجب أن يكون مرتبطاً بمتجر (shop) في النظام.
