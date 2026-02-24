# Backend Development Requirements - Eldokan E-Commerce Platform

## Application Overview
Eldokan (Ad-Dukkan) is a comprehensive Arabic e-commerce platform specializing in pharmacy, beauty products, and medical services. The application supports multiple shop categories, prescription management, doctor consultations, and delivery tracking.

## Core Modules & Features

### 1. User Management & Authentication
- **User Registration/Login**
  - Username, phone number, email authentication
  - Social login (Google, Apple)
  - Guest user support
  - Phone verification with OTP (6-digit code, 45-second timer)
  - Account review/approval workflow for new registrations

- **User Profile**
  - Personal information (name, phone, email)
  - Profile avatar/image
  - Wallet balance (EGP currency)
  - Saved addresses management
  - Language preferences (Arabic, English, and more)

### 2. Shop & Product Management
- **Shop Categories**
  - Beauty shops (تجميل)
  - Pharmacies (صيدليات)
  - Clothing (ملابس)
  - Pizza shops (بيتزا)
  - Electronics (إلكترونيات)
  - Chocolate shops (شيكولاته)
  - Dairy shops (ألبان)
  - Industries (الصناعات)

- **Product Features**
  - Product details (name, description, price, images)
  - Discount system (percentage-based, e.g., 30% off)
  - Product ratings (0.0-5.0 star system)
  - Stock management
  - Product categories and sub-categories
  - Multiple product images with thumbnails

- **Product Filtering**
  - Filter by shop category
  - Filter by sub-categories
  - Clear/reset filter options
  - Apply filters functionality

### 3. Shopping Cart & Orders
- **Cart Management**
  - Add/remove products
  - Quantity adjustment (increment/decrement)
  - Cart item count display
  - Price calculation (unit price, total per item, cart total)
  - Product availability validation

- **Order Management**
  - Order creation with unique order numbers (format: #D2235)
  - Order status tracking:
    - Received (تم الاستلام)
    - Processing (قيد التجهيز)
    - On the way (في الطريق)
    - Delivered (تم التوصيل)
  - Order timeline with timestamps
  - Order cancellation (with time restrictions)
  - Order history with filters:
    - All (الكل)
    - Upcoming (القادمه)
    - In Progress (قيد التنفيذ)
    - Completed (أكتملت)
    - Cancelled (تم إلغائها)

### 4. Prescription Management
- **Prescription Upload**
  - Image upload (up to 20 images, 5MB each, PNG format, 1200x500 recommended)
  - Camera or gallery selection
  - Prescription image preview and deletion
  - Prescription confirmation workflow

- **Prescription Processing**
  - Prescription number generation (format: #102345-RX)
  - Pharmacy assignment
  - Pharmacist assignment
  - Status tracking:
    - Under Review (قيد المراجعه)
    - Dispensed (تم صرفها)
  - Medication list extraction/management
  - Prescription history with filters

- **Medication Details**
  - Medication name and dosage (e.g., "Concor 5 Mg")
  - Form (tablets, etc.)
  - Quantity (strips, units)
  - Duration (days)
  - Dosage instructions (after breakfast, once daily, etc.)
  - Price per medication

### 5. Medication Reminders
- **Reminder Configuration**
  - Per-medication reminder settings
  - Time selection (hours, minutes, AM/PM)
  - Frequency options:
    - Twice daily (مرتين في اليوم)
    - 3 times daily (3 مرات في اليوم)
    - Daily (يوميا)
    - Specific days (ايام محدده)
  - Duration options:
    - Week (اسبوع)
    - Two weeks (اسبوعين)
    - Three weeks (3 اسابيع)
    - Month (شهر)
    - Specific period (فتره محدده)
  - Active/inactive reminder toggle
  - Reminder deletion

### 6. Doctor Consultation & Booking
- **Doctor Profiles**
  - Doctor information (name, specialty, photo)
  - Consultation pricing (with discount support)
  - Rating system (0.0-5.0 stars)
  - Available days (e.g., Saturday-Thursday)
  - Available hours (e.g., 10 AM - 5 PM)
  - Location/address
  - Consultation duration (e.g., 20 minutes)

- **Booking Types**
  - Online consultation (استشاره اونلاين) - video call
  - In-clinic booking (حجز في العياده)

- **Booking Management**
  - Booking number generation (format: #D2235)
  - Date selection (calendar interface)
  - Time slot selection
  - Patient information capture
  - Booking status:
    - Upcoming (القادمه)
    - In Progress (قيد التنفيذ)
    - Completed (أكتملت)
    - Cancelled (تم إلغائها)
  - Cancellation policy (e.g., 2 days from booking date with countdown timer)
  - Doctor rating after completion
  - Complaint submission

### 7. Delivery & Tracking
- **Address Management**
  - Multiple saved addresses
  - Address details:
    - City (المدينه)
    - District/Neighborhood (الحي)
    - Detailed address (العنوان بالتفصيل)
  - Default address selection
  - Address editing and deletion
  - Location pin/map integration

- **Delivery Tracking**
  - Real-time order tracking
  - Delivery driver assignment
  - Driver information (name, photo, rating)
  - Driver contact (call, chat)
  - Store address and delivery address
  - Estimated arrival time (e.g., "Arrives in 10 minutes")
  - Map integration with route visualization
  - QR code generation for delivery confirmation
  - Delivery rating system

### 8. Payment System
- **Payment Methods**
  - Credit card (بطاقة ائتمان)
  - E-wallet (محفظه الكترونيه)
  - Cash on delivery (نقدا عند الاستلام)

- **Payment Processing**
  - Card details storage (masked display: 25** *** *** ***)
  - Discount/promo code system
  - Price breakdown:
    - Number of products
    - Subtotal
    - Discount amount
    - Delivery fee
    - Total amount
  - Payment confirmation
  - Transaction history

### 9. Notifications System
- **Notification Types**
  - New notifications (unread)
  - Previous notifications (read)
  - Notification categories (prescriptions, orders, etc.)
  - Actionable notifications (e.g., "Go to cart" links)

- **Notification Features**
  - Read/unread status
  - Notification timestamps
  - Notification icons/badges
  - Unread count display

### 10. Messaging System
- **Chat Features**
  - Doctor-patient messaging
  - Message history
  - Text messages
  - Voice messages (with waveform display and duration)
  - Timestamps
  - Read receipts
  - Chat list with unread indicators

### 11. Settings & Preferences
- **User Settings**
  - Language selection
  - Dose reminder preferences
  - Messages/notifications settings
  - Support contact (24/7)
  - App policy/privacy
  - Logout functionality

## Data Models Required

### User
- id, username, phone, email, password_hash, avatar_url, wallet_balance, language_preference, status (pending/active), created_at, updated_at

### Address
- id, user_id, title, city, district, detailed_address, latitude, longitude, is_default, created_at, updated_at

### Shop
- id, name, category, subcategories, address, phone, rating, image_url, created_at, updated_at

### Product
- id, shop_id, name, description, price, discount_percentage, images[], rating, stock_quantity, category, subcategory, created_at, updated_at

### Cart
- id, user_id, product_id, quantity, created_at, updated_at

### Order
- id, order_number, user_id, shop_id, status, total_amount, delivery_address_id, payment_method, payment_status, created_at, updated_at

### OrderItem
- id, order_id, product_id, quantity, unit_price, total_price, created_at

### OrderStatusHistory
- id, order_id, status, timestamp, description, created_at

### Prescription
- id, prescription_number, user_id, pharmacy_id, pharmacist_id, images[], status, created_at, updated_at

### PrescriptionMedication
- id, prescription_id, medication_name, dosage, form, quantity, duration, instructions, price, created_at

### MedicationReminder
- id, user_id, prescription_medication_id, medication_name, reminder_time, frequency, duration, is_active, created_at, updated_at

### Doctor
- id, name, specialty, photo_url, rating, consultation_price, discount_percentage, available_days, available_hours_start, available_hours_end, location, consultation_duration, created_at, updated_at

### Booking
- id, booking_number, user_id, doctor_id, booking_type, appointment_date, appointment_time, patient_name, status, total_amount, payment_method, payment_status, created_at, updated_at

### Delivery
- id, order_id, driver_id, store_address, delivery_address, estimated_arrival_time, status, qr_code, created_at, updated_at

### Driver
- id, name, photo_url, phone, rating, current_location_lat, current_location_lng, status, created_at, updated_at

### Notification
- id, user_id, type, title, description, action_url, is_read, created_at

### Message
- id, sender_id, receiver_id, content, message_type (text/voice), voice_duration, is_read, created_at

### Payment
- id, user_id, order_id, booking_id, payment_method, amount, discount_amount, delivery_fee, total_amount, status, transaction_id, card_last_four, created_at

## API Endpoints Required

### Authentication
- POST /api/auth/register
- POST /api/auth/login
- POST /api/auth/social-login
- POST /api/auth/verify-otp
- POST /api/auth/resend-otp
- POST /api/auth/logout
- GET /api/auth/guest-login

### User Management
- GET /api/user/profile
- PUT /api/user/profile
- GET /api/user/wallet
- POST /api/user/wallet/top-up

### Addresses
- GET /api/addresses
- POST /api/addresses
- PUT /api/addresses/{id}
- DELETE /api/addresses/{id}
- PUT /api/addresses/{id}/set-default

### Shops & Products
- GET /api/shops
- GET /api/shops/{id}
- GET /api/products
- GET /api/products/{id}
- GET /api/products/filter
- GET /api/categories
- GET /api/subcategories

### Cart
- GET /api/cart
- POST /api/cart/add
- PUT /api/cart/{id}
- DELETE /api/cart/{id}
- DELETE /api/cart/clear

### Orders
- POST /api/orders
- GET /api/orders
- GET /api/orders/{id}
- GET /api/orders/{id}/track
- PUT /api/orders/{id}/cancel
- GET /api/orders/status/{status}

### Prescriptions
- POST /api/prescriptions/upload
- GET /api/prescriptions
- GET /api/prescriptions/{id}
- PUT /api/prescriptions/{id}/status
- GET /api/prescriptions/status/{status}

### Medication Reminders
- GET /api/reminders
- POST /api/reminders
- PUT /api/reminders/{id}
- DELETE /api/reminders/{id}
- PUT /api/reminders/{id}/toggle

### Doctors
- GET /api/doctors
- GET /api/doctors/{id}
- GET /api/doctors/{id}/availability

### Bookings
- POST /api/bookings
- GET /api/bookings
- GET /api/bookings/{id}
- PUT /api/bookings/{id}/cancel
- POST /api/bookings/{id}/rate
- POST /api/bookings/{id}/complaint
- GET /api/bookings/status/{status}

### Delivery
- GET /api/deliveries/{order_id}/track
- GET /api/deliveries/{order_id}/driver
- POST /api/deliveries/{order_id}/contact-driver
- POST /api/deliveries/{order_id}/confirm
- GET /api/deliveries/{order_id}/map

### Payment
- POST /api/payments/process
- GET /api/payments/methods
- POST /api/payments/apply-discount
- GET /api/payments/history

### Notifications
- GET /api/notifications
- GET /api/notifications/unread-count
- PUT /api/notifications/{id}/read
- PUT /api/notifications/read-all

### Messages
- GET /api/messages
- GET /api/messages/{conversation_id}
- POST /api/messages
- POST /api/messages/voice
- PUT /api/messages/{id}/read

### Settings
- GET /api/settings
- PUT /api/settings
- GET /api/support

## Business Logic Requirements

1. **Order Number Generation**: Unique format (e.g., #D2235) with sequential numbering
2. **Prescription Number Generation**: Format #102345-RX with sequential numbering
3. **Booking Number Generation**: Same format as orders (#D2235)
4. **Price Calculations**: Support for discounts, delivery fees, and currency (EGP)
5. **Order Status Workflow**: Enforce proper status transitions (Received → Processing → On the Way → Delivered)
6. **Booking Cancellation**: Enforce time-based cancellation policies (e.g., 2 days from booking)
7. **Prescription Status Workflow**: Under Review → Dispensed
8. **Real-time Tracking**: Integration with mapping services for delivery tracking
9. **QR Code Generation**: For delivery confirmation
10. **OTP System**: 6-digit codes with 45-second expiration and resend functionality
11. **Rating System**: 0.0-5.0 scale for products, doctors, and drivers
12. **Notification Triggers**: Automatic notifications for order status changes, prescription updates, booking confirmations
13. **Wallet System**: Balance management for user credits
14. **Multi-language Support**: Arabic (RTL) and English (LTR) with extensibility

## Technical Requirements

1. **RESTful API Design**: Follow REST principles
2. **Authentication**: JWT tokens or session-based
3. **File Upload**: Support for images (prescriptions, products, avatars) with size limits
4. **Real-time Updates**: WebSocket or polling for order tracking and messages
5. **Push Notifications**: For mobile app integration
6. **Payment Gateway Integration**: Support for credit cards, e-wallets, and cash on delivery
7. **Map Integration**: Google Maps or similar for address and delivery tracking
8. **QR Code Generation**: For delivery confirmation
9. **Image Processing**: Thumbnail generation, compression
10. **Database**: Relational database with proper indexing for performance
11. **Caching**: For frequently accessed data (products, shops, categories)
12. **Error Handling**: Comprehensive error messages in Arabic and English
13. **Logging**: Audit trails for orders, payments, and critical operations
14. **Security**: Data encryption, secure payment processing, input validation

## Additional Considerations

- Support for both Arabic (RTL) and English (LTR) text
- Timezone handling for appointments and order timestamps
- Scalability for multiple shops and high order volumes
- Admin dashboard requirements (not shown in UI but likely needed)
- Analytics and reporting capabilities
- Backup and disaster recovery procedures

