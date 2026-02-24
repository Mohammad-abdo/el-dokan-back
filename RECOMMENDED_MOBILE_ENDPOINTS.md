# Endpoints مقترحة للموبايل (من تحليل الـ 5 فلو)

> مبنية على [MOBILE_5_FLOWS_GAP_ANALYSIS.md](./MOBILE_5_FLOWS_GAP_ANALYSIS.md).  
> تنفيذها يكمّل فلو **السائق** و **مندوب المبيعات** حسب تصميم Figma.

---

## أولوية عالية (Driver)

| Method | Endpoint | الوصف |
|--------|----------|--------|
| GET | `GET /api/driver/available-orders` | طلبات قابلة للتوصيل في منطقة السائق. Query: `latitude`, `longitude`, `radius` (اختياري). |
| GET | `GET /api/driver/orders/{order_id}/delivery-details` | تفاصيل طلب للتوصيل (عنوان استلام، عنوان تسليم، مبلغ التوصيل) للسائق. |
| POST | `POST /api/driver/orders/{order_id}/offer` | تقديم عرض سعر توصيل من السائق. Body: `{ "delivery_price": number }`. |
| POST | `POST /api/driver/orders/{order_id}/accept` | قبول الطلب من السائق (بعد الموافقة على العرض إن وُجدت). |
| POST | `POST /api/driver/deliveries/{delivery_id}/pickup` | تأكيد استلام الطلب من المتجر. |
| POST | `POST /api/driver/deliveries/{delivery_id}/confirm-delivery` | تأكيد تسليم الطلب للعميل. Body اختياري: `{ "qr_code": "..." }` للتحقق. |
| PUT | `PUT /api/driver/location` | تحديث موقع السائق. Body: `{ "latitude", "longitude" }`. |

---

## أولوية عالية (Representative)

| Method | Endpoint | الوصف |
|--------|----------|--------|
| GET | `GET /api/representative/clients` | قائمة عملاء المندوب. Query: `search` (اختياري). |
| GET | `GET /api/representative/orders` | طلبات مرتبطة بالمندوب. Query: `status` (اختياري). |
| GET | `GET /api/representative/orders/{id}` | تفاصيل طلب. |
| PUT | `PUT /api/representative/orders/{id}/cancel` | إلغاء طلب (حسب الصلاحيات). |
| GET | `GET /api/representative/reports` | تقارير. Query: `type=visits|orders|sales`, `from_date`, `to_date`. |

---

## أولوية متوسطة

| Method | Endpoint | الوصف |
|--------|----------|--------|
| GET | `GET /api/orders/{id}/qr-code` | الحصول على QR للطلب (للعميل/المتجر عند الاستلام). |
| GET | في `GET /api/auth/me` أو profile الطبيب | إرجاع `account_status` (مثل `pending_approval` / `approved`) لحساب الطبيب. |

---

بعد تنفيذ أي endpoint أضفه إلى:
- `routes/api.php`
- `MOBILE_DEVELOPER_API_REFERENCE.md`
- `mobile_endpoints.json` (إن وُجد)
