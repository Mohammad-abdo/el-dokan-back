<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\MedicationReminderController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\SettingsController;
use Illuminate\Support\Facades\Route;

// API Root/Status endpoint
Route::get('/', function () {
    return response()->json([
        'message' => 'Eldokan API',
        'version' => '1.0.0',
        'status' => 'active',
        'endpoints' => [
            'auth' => '/api/auth',
            'home' => '/api/home',
            'shops' => '/api/shops',
            'products' => '/api/products',
            'categories' => '/api/categories',
            'doctors' => '/api/doctors',
        ]
    ]);
});

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register-doctor', [AuthController::class, 'registerDoctor']);
    Route::post('/register-company', [AuthController::class, 'registerCompany']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/social-login', [AuthController::class, 'socialLogin']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::get('/guest-login', [AuthController::class, 'guestLogin']);
});

// Public routes
Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);
Route::get('/shops', [ShopController::class, 'index']);
Route::get('/shops/{shop}', [ShopController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::post('/products/filter', [ProductController::class, 'filter']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/subcategories', [CategoryController::class, 'subcategories']);
Route::get('/sliders', [\App\Http\Controllers\Api\SliderController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // User
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::get('/wallet', [UserController::class, 'wallet']);
        Route::post('/wallet/top-up', [UserController::class, 'topUpWallet']);
    });

    // Addresses
    Route::apiResource('addresses', AddressController::class);
    Route::put('/addresses/{address}/set-default', [AddressController::class, 'setDefault']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/{cart}', [CartController::class, 'update']);
    Route::delete('/cart/{cart}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // Orders
    Route::apiResource('orders', OrderController::class);
    Route::get('/orders/{order}/track', [OrderController::class, 'track']);
    Route::put('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::get('/orders/status/{status}', [OrderController::class, 'index']);

    // Prescriptions
    Route::post('/prescriptions/upload', [PrescriptionController::class, 'upload']);
    Route::get('/prescriptions', [PrescriptionController::class, 'index']);
    Route::get('/prescriptions/{prescription}', [PrescriptionController::class, 'show']);
    Route::get('/prescriptions/status/{status}', [PrescriptionController::class, 'index']);

    // Medication Reminders
    Route::apiResource('reminders', MedicationReminderController::class);
    Route::put('/reminders/{reminder}/toggle', [MedicationReminderController::class, 'toggle']);

    // Doctors
    Route::get('/doctors', [DoctorController::class, 'index']);
    Route::get('/doctors/{doctor}', [DoctorController::class, 'show']);
    Route::get('/doctors/{doctor}/availability', [DoctorController::class, 'availability']);

    // Bookings
    Route::apiResource('bookings', BookingController::class);
    Route::put('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::post('/bookings/{booking}/rate', [BookingController::class, 'rate']);
    Route::post('/bookings/{booking}/complaint', [BookingController::class, 'complaint']);
    Route::get('/bookings/status/{status}', [BookingController::class, 'index']);

    // Delivery
    Route::get('/deliveries/{order}/track', [DeliveryController::class, 'track']);
    Route::get('/deliveries/{order}/driver', [DeliveryController::class, 'driver']);
    Route::post('/deliveries/{order}/contact-driver', [DeliveryController::class, 'contactDriver']);
    Route::post('/deliveries/{order}/confirm', [DeliveryController::class, 'confirm']);
    Route::get('/deliveries/{order}/map', [DeliveryController::class, 'map']);

    // Payment
    Route::post('/payments/process', [PaymentController::class, 'process']);
    Route::get('/payments/methods', [PaymentController::class, 'methods']);
    Route::post('/payments/apply-discount', [PaymentController::class, 'applyDiscount']);
    Route::get('/payments/history', [PaymentController::class, 'history']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'readAll']);

    // Messages
    Route::get('/messages', [MessageController::class, 'index']);
    Route::get('/messages/{user}/conversation', [MessageController::class, 'conversation']);
    Route::post('/messages', [MessageController::class, 'send']);
    Route::post('/messages/voice', [MessageController::class, 'sendVoice']);
    Route::put('/messages/{message}/read', [MessageController::class, 'markAsRead']);

    // Favourites
    Route::get('/favourites', [\App\Http\Controllers\Api\FavouriteController::class, 'index']);
    Route::post('/favourites', [\App\Http\Controllers\Api\FavouriteController::class, 'store']);
    Route::delete('/favourites/{product}', [\App\Http\Controllers\Api\FavouriteController::class, 'destroy']);
    Route::get('/favourites/{product}/check', [\App\Http\Controllers\Api\FavouriteController::class, 'check']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::put('/settings', [SettingsController::class, 'update']);
    Route::get('/support', [SettingsController::class, 'support']);

    // Ratings
    Route::post('/ratings', [\App\Http\Controllers\Api\RatingController::class, 'store']);
    Route::get('/ratings', [\App\Http\Controllers\Api\RatingController::class, 'index']);

    // Coupons
    Route::post('/coupons/validate', [\App\Http\Controllers\Api\CouponController::class, 'validateCoupon']);

    // Change Password
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // Support
    Route::apiResource('support/tickets', \App\Http\Controllers\Support\SupportTicketController::class);
    Route::post('/support/tickets/{ticket}/messages', [\App\Http\Controllers\Support\SupportTicketController::class, 'addMessage']);

    // Maps
    Route::get('/maps/medical-centers', [\App\Http\Controllers\Api\MapController::class, 'medicalCenters']);
    Route::get('/maps/doctors/{doctor}/clinics', [\App\Http\Controllers\Api\MapController::class, 'doctorClinics']);
    Route::get('/maps/calculate-distance', [\App\Http\Controllers\Api\MapController::class, 'calculateDistance']);

    // Prescription by link
    Route::get('/prescriptions/link/{link}', [\App\Http\Controllers\Doctor\DoctorPrescriptionController::class, 'viewByLink']);
    Route::post('/auth/login-with-link', [AuthController::class, 'loginWithLink']);
});

// Doctor Routes
Route::prefix('doctor')->middleware(['auth:sanctum', 'doctor'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Doctor\DoctorDashboardController::class, 'dashboard']);
    Route::get('/bookings', [\App\Http\Controllers\Doctor\DoctorDashboardController::class, 'bookings']);
    Route::get('/patients', [\App\Http\Controllers\Doctor\DoctorDashboardController::class, 'patients']);

    Route::get('/prescriptions/templates', [\App\Http\Controllers\Doctor\DoctorPrescriptionController::class, 'templates']);
    Route::apiResource('prescriptions', \App\Http\Controllers\Doctor\DoctorPrescriptionController::class);
    Route::post('/prescriptions/{prescription}/share', [\App\Http\Controllers\Doctor\DoctorPrescriptionController::class, 'share']);
    Route::get('/prescriptions/{prescription}/print', [\App\Http\Controllers\Doctor\DoctorPrescriptionController::class, 'print']);

    Route::get('/wallet', [\App\Http\Controllers\Doctor\DoctorWalletController::class, 'index']);
    Route::get('/wallet/transactions', [\App\Http\Controllers\Doctor\DoctorWalletController::class, 'transactions']);

    Route::get('/medical-centers', [\App\Http\Controllers\Doctor\DoctorMedicalCenterController::class, 'index']);
    Route::post('/medical-centers/add', [\App\Http\Controllers\Doctor\DoctorMedicalCenterController::class, 'add']);
    Route::delete('/medical-centers/{medicalCenter}', [\App\Http\Controllers\Doctor\DoctorMedicalCenterController::class, 'remove']);

    Route::get('/reports/prescriptions', [\App\Http\Controllers\Doctor\DoctorReportController::class, 'prescriptionsReport']);
    Route::get('/reports/products', [\App\Http\Controllers\Doctor\DoctorReportController::class, 'productsReport']);
    Route::get('/reports/patients', [\App\Http\Controllers\Doctor\DoctorReportController::class, 'patientsReport']);

    Route::get('/visits', [\App\Http\Controllers\Doctor\DoctorVisitController::class, 'index']);
    Route::post('/visits/{visit}/confirm', [\App\Http\Controllers\Doctor\DoctorVisitController::class, 'confirm']);

    Route::get('/selected-treatments', [\App\Http\Controllers\Doctor\DoctorSelectedTreatmentController::class, 'index']);
    Route::post('/selected-treatments', [\App\Http\Controllers\Doctor\DoctorSelectedTreatmentController::class, 'store']);
    Route::delete('/selected-treatments/{treatment}', [\App\Http\Controllers\Doctor\DoctorSelectedTreatmentController::class, 'destroy']);

    Route::get('/chat/conversations', [\App\Http\Controllers\Doctor\DoctorChatController::class, 'conversations']);
    Route::get('/chat/{patient}/conversation', [\App\Http\Controllers\Doctor\DoctorChatController::class, 'conversation']);
    Route::post('/chat/{patient}/send', [\App\Http\Controllers\Doctor\DoctorChatController::class, 'send']);
});

// Shop Routes
Route::prefix('shop')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Shop\ShopDashboardController::class, 'dashboard']);
    Route::get('/products', [\App\Http\Controllers\Shop\ShopProductController::class, 'index']);
    Route::post('/products', [\App\Http\Controllers\Shop\ShopProductController::class, 'store']);
    Route::get('/products/{id}', [\App\Http\Controllers\Shop\ShopProductController::class, 'show']);
    Route::put('/products/{id}', [\App\Http\Controllers\Shop\ShopProductController::class, 'update']);
    Route::delete('/products/{id}', [\App\Http\Controllers\Shop\ShopProductController::class, 'destroy']);
    Route::get('/orders', [\App\Http\Controllers\Shop\ShopOrderController::class, 'index']);
    Route::get('/orders/{id}', [\App\Http\Controllers\Shop\ShopOrderController::class, 'show']);
    Route::put('/orders/{id}/status', [\App\Http\Controllers\Shop\ShopOrderController::class, 'updateStatus']);
    Route::get('/customers', [\App\Http\Controllers\Shop\ShopOrderController::class, 'index']); // Can reuse orders to get customers
    Route::get('/revenue', [\App\Http\Controllers\Shop\ShopDashboardController::class, 'dashboard']); // Revenue is in dashboard
    Route::get('/financial', [\App\Http\Controllers\Shop\ShopDashboardController::class, 'dashboard']); // Financial data
    Route::get('/financial/transactions', [\App\Http\Controllers\Shop\ShopDashboardController::class, 'transactions']);
});

// Driver Routes
Route::prefix('driver')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Driver\DriverDashboardController::class, 'dashboard']);
    Route::get('/deliveries', [\App\Http\Controllers\Driver\DriverDeliveryController::class, 'index']);
    Route::get('/earnings', [\App\Http\Controllers\Driver\DriverDashboardController::class, 'dashboard']);
    Route::get('/earnings/transactions', [\App\Http\Controllers\Driver\DriverDashboardController::class, 'transactions']);
    Route::get('/available-orders', [\App\Http\Controllers\Driver\DriverOrderController::class, 'availableOrders']);
    Route::get('/orders/{orderId}/delivery-details', [\App\Http\Controllers\Driver\DriverOrderController::class, 'deliveryDetails']);
    Route::post('/orders/{orderId}/offer', [\App\Http\Controllers\Driver\DriverOrderController::class, 'offer']);
    Route::post('/orders/{orderId}/accept', [\App\Http\Controllers\Driver\DriverOrderController::class, 'accept']);
    Route::post('/deliveries/{deliveryId}/pickup', [\App\Http\Controllers\Driver\DriverOrderController::class, 'pickup']);
    Route::post('/deliveries/{deliveryId}/confirm-delivery', [\App\Http\Controllers\Driver\DriverOrderController::class, 'confirmDelivery']);
    Route::put('/location', [\App\Http\Controllers\Driver\DriverLocationController::class, 'update']);
});

// Representative Routes
Route::prefix('representative')->middleware(['auth:sanctum', 'representative'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Representative\RepresentativeDashboardController::class, 'dashboard']);
    Route::apiResource('products', \App\Http\Controllers\Representative\RepresentativeProductController::class);
    Route::apiResource('visits', \App\Http\Controllers\Representative\RepresentativeVisitController::class);
    Route::get('/earnings', [\App\Http\Controllers\Representative\RepresentativeDashboardController::class, 'dashboard']);
    Route::get('/earnings/transactions', [\App\Http\Controllers\Representative\RepresentativeDashboardController::class, 'transactions']);
    Route::get('/clients', [\App\Http\Controllers\Representative\RepresentativeClientController::class, 'index']);
    Route::get('/orders', [\App\Http\Controllers\Representative\RepresentativeOrderController::class, 'index']);
    Route::get('/orders/{id}', [\App\Http\Controllers\Representative\RepresentativeOrderController::class, 'show']);
    Route::put('/orders/{id}/cancel', [\App\Http\Controllers\Representative\RepresentativeOrderController::class, 'cancel']);
    Route::post('/company-orders', [\App\Http\Controllers\Representative\RepresentativeCompanyOrderController::class, 'store']);
    Route::get('/reports', [\App\Http\Controllers\Representative\RepresentativeReportController::class, 'index']);
});

// Company Routes — لوحة الشركة: منتجات الشركة، مبيعات  مندوبين المبيعات ، الخطة، المندوبون، الزيارات
Route::prefix('company')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Company\CompanyDashboardController::class, 'dashboard']);
    Route::get('/plan', [\App\Http\Controllers\Company\CompanyPlanController::class, 'show']);
    Route::get('/company-products', [\App\Http\Controllers\Company\CompanyProductController::class, 'index']);
    Route::post('/company-products', [\App\Http\Controllers\Company\CompanyProductController::class, 'store']);
    Route::get('/company-products/{id}', [\App\Http\Controllers\Company\CompanyProductController::class, 'show']);
    Route::put('/company-products/{id}', [\App\Http\Controllers\Company\CompanyProductController::class, 'update']);
    Route::delete('/company-products/{id}', [\App\Http\Controllers\Company\CompanyProductController::class, 'destroy']);
    Route::get('/company-orders', [\App\Http\Controllers\Company\CompanyOrderController::class, 'index']);
    Route::get('/company-orders/{id}', [\App\Http\Controllers\Company\CompanyOrderController::class, 'show']);
    Route::get('/representatives', [\App\Http\Controllers\Company\CompanyRepresentativeController::class, 'index']);
    Route::get('/visits', [\App\Http\Controllers\Company\CompanyVisitController::class, 'index']);
});

// Admin Routes
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // Notifications Management
    Route::apiResource('notifications', \App\Http\Controllers\Admin\AdminNotificationController::class);
    Route::post('/notifications/bulk', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'sendBulk']);
    Route::get('/notifications/statistics', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'statistics']);

    // Sliders
    Route::apiResource('sliders', \App\Http\Controllers\Admin\AdminSliderController::class);

    // Doctors Management
    Route::post('/doctors/reports/generate', [\App\Http\Controllers\Admin\AdminDoctorReportController::class, 'generate']);
    Route::apiResource('doctors', \App\Http\Controllers\Admin\AdminDoctorController::class);
    Route::get('/doctors/{doctor}/bookings', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'bookings']);
    Route::get('/doctors/{doctor}/prescriptions', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'prescriptions']);
    Route::post('/doctors/{doctor}/suspend', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'suspend']);
    Route::post('/doctors/{doctor}/activate', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'activate']);

    // Financial Management
    Route::get('/financial/dashboard', [\App\Http\Controllers\Admin\AdminFinancialController::class, 'dashboard']);
    Route::get('/financial/transactions', [\App\Http\Controllers\Admin\AdminFinancialController::class, 'transactions']);
    Route::get('/financial/shops', [\App\Http\Controllers\Admin\AdminFinancialController::class, 'shopFinancials']);
    Route::get('/financial/vendors', [\App\Http\Controllers\Admin\AdminFinancialController::class, 'vendorsFinancials']);
    Route::get('/financial/vendor-wallet', [\App\Http\Controllers\Admin\AdminFinancialController::class, 'vendorWallet']);
    Route::get('/financial/statistics', [\App\Http\Controllers\Admin\AdminFinancialController::class, 'statistics']);

    // Ratings Management
    Route::get('/ratings', [\App\Http\Controllers\Admin\AdminRatingController::class, 'index']);
    Route::post('/ratings/{rating}/approve', [\App\Http\Controllers\Admin\AdminRatingController::class, 'approve']);
    Route::post('/ratings/{rating}/reject', [\App\Http\Controllers\Admin\AdminRatingController::class, 'reject']);
    Route::delete('/ratings/{rating}', [\App\Http\Controllers\Admin\AdminRatingController::class, 'destroy']);

    // Categories Management
    Route::get('/categories/{category}/subcategories', [\App\Http\Controllers\Admin\AdminCategoryController::class, 'subcategories']);
    Route::apiResource('categories', \App\Http\Controllers\Admin\AdminCategoryController::class);

    // Users Management
    Route::apiResource('users', \App\Http\Controllers\Admin\AdminUserController::class);
    Route::post('/users/{user}/suspend', [\App\Http\Controllers\Admin\AdminUserController::class, 'suspend']);
    Route::post('/users/{user}/activate', [\App\Http\Controllers\Admin\AdminUserController::class, 'activate']);

    // Products Management
    Route::apiResource('products', \App\Http\Controllers\Admin\AdminProductController::class);
    Route::post('/products/{product}/toggle-status', [\App\Http\Controllers\Admin\AdminProductController::class, 'toggleStatus']);
    Route::get('/products/{product}/analytics', [\App\Http\Controllers\Admin\AdminProductController::class, 'analytics']);
    Route::get('/products/{product}/export', [\App\Http\Controllers\Admin\AdminProductController::class, 'export']);

    // Shops Management
    Route::apiResource('shops', \App\Http\Controllers\Admin\AdminShopController::class);
    Route::post('/shops/{shop}/approve', [\App\Http\Controllers\Admin\AdminShopController::class, 'approve']);
    Route::post('/shops/{shop}/reject', [\App\Http\Controllers\Admin\AdminShopController::class, 'reject']);
    Route::post('/shops/{shop}/suspend', [\App\Http\Controllers\Admin\AdminShopController::class, 'suspend']);
    Route::get('/shops/{shop}/wallet', [\App\Http\Controllers\Admin\AdminShopController::class, 'wallet']);
    Route::post('/shops/{shop}/wallet/adjust', [\App\Http\Controllers\Admin\AdminShopController::class, 'adjustWallet']);
    Route::get('/shops/{shop}/product-reports', [\App\Http\Controllers\Admin\AdminShopController::class, 'productReports']);
    Route::get('/shops/{shop}/representatives', [\App\Http\Controllers\Admin\AdminShopController::class, 'representatives']);
    Route::post('/shops/{shop}/representatives', [\App\Http\Controllers\Admin\AdminShopController::class, 'storeRepresentative']);
    Route::get('/shops/{shop}/orders-from-reps', [\App\Http\Controllers\Admin\AdminShopController::class, 'ordersFromReps']);
    Route::get('/shops/{shop}/visits', [\App\Http\Controllers\Admin\AdminShopController::class, 'visits']);
    Route::apiResource('shops.company-products', \App\Http\Controllers\Admin\AdminCompanyProductController::class)->parameters(['company-products' => 'company_product']);
    Route::post('/shops/{shop}/company-products/{company_product}', [\App\Http\Controllers\Admin\AdminCompanyProductController::class, 'update']);
    Route::get('/shops/{shop}/company-orders', [\App\Http\Controllers\Admin\AdminCompanyOrderController::class, 'index']);
    Route::post('/shops/{shop}/company-orders', [\App\Http\Controllers\Admin\AdminCompanyOrderController::class, 'store']);
    Route::get('/shops/{shop}/company-orders/{company_order}', [\App\Http\Controllers\Admin\AdminCompanyOrderController::class, 'show']);
    Route::put('/shops/{shop}/plan', [\App\Http\Controllers\Admin\AdminShopController::class, 'updatePlan']);
    Route::apiResource('shops.branches', \App\Http\Controllers\Admin\AdminShopBranchController::class)->except(['show'])->shallow(false);
    Route::apiResource('shops.documents', \App\Http\Controllers\Admin\AdminShopDocumentController::class)->except(['show'])->shallow(false);
    Route::apiResource('company-plans', \App\Http\Controllers\Admin\AdminCompanyPlanController::class);

    // Orders Management
    Route::get('/orders', [\App\Http\Controllers\Admin\AdminOrderController::class, 'index']);
    Route::get('/orders/available-for-delivery', [\App\Http\Controllers\Admin\AdminOrderController::class, 'availableForDelivery']);
    Route::get('/orders/{order}', [\App\Http\Controllers\Admin\AdminOrderController::class, 'show']);
    Route::put('/orders/{order}/status', [\App\Http\Controllers\Admin\AdminOrderController::class, 'updateStatus']);
    Route::delete('/orders/{order}', [\App\Http\Controllers\Admin\AdminOrderController::class, 'destroy']);

    // Coupons Management
    Route::apiResource('coupons', \App\Http\Controllers\Admin\AdminCouponController::class);

    // Reports
    Route::get('/reports/orders', [\App\Http\Controllers\Admin\AdminReportController::class, 'ordersReport']);
    Route::get('/reports/financial', [\App\Http\Controllers\Admin\AdminReportController::class, 'financialReport']);
    Route::get('/reports/users', [\App\Http\Controllers\Admin\AdminReportController::class, 'usersReport']);
    Route::get('/reports/products', [\App\Http\Controllers\Admin\AdminReportController::class, 'productsReport']);
    Route::get('/reports/dashboard', [\App\Http\Controllers\Admin\AdminReportController::class, 'dashboardReport']);
    Route::get('/reports/companies', [\App\Http\Controllers\Admin\AdminReportController::class, 'companiesReport']);
    Route::get('/reports/{type}/export', [\App\Http\Controllers\Admin\AdminReportController::class, 'export']);

    // Full Data Export (RAW DATA + PDF via Puppeteer)
    Route::get('/export', [\App\Http\Controllers\Admin\AdminExportController::class, 'data']);
    Route::get('/export/pdf', [\App\Http\Controllers\Admin\AdminExportController::class, 'exportPdf']);

    // Doctor Wallet Management
    Route::get('/doctors/{doctor}/wallet', [\App\Http\Controllers\Admin\AdminDoctorWalletController::class, 'show']);
    Route::get('/doctors/{doctor}/wallet/transactions', [\App\Http\Controllers\Admin\AdminDoctorWalletController::class, 'transactions']);
    Route::post('/doctors/{doctor}/wallet/transfer', [\App\Http\Controllers\Admin\AdminDoctorWalletController::class, 'transfer']);
    Route::put('/doctors/{doctor}/wallet/commission', [\App\Http\Controllers\Admin\AdminDoctorWalletController::class, 'setCommission']);

    // Maps: all entities with coordinates for admin map view
    Route::get('/maps/entities', [\App\Http\Controllers\Admin\AdminMapController::class, 'entities']);
    // Medical Centers Management
    Route::get('/maps/medical-centers', [\App\Http\Controllers\Admin\AdminMapController::class, 'medicalCenters']);

    // Roles & Permissions Management
    Route::apiResource('roles', \App\Http\Controllers\Admin\AdminRoleController::class);
    Route::get('/roles/{role}/permissions', [\App\Http\Controllers\Admin\AdminRoleController::class, 'permissions']);
    Route::get('/permissions', [\App\Http\Controllers\Admin\AdminRoleController::class, 'permissions']);

    // Settings Management
    Route::get('/settings', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'index']);
    Route::put('/settings', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'update']);
    Route::post('/settings/upload', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'uploadFile']);
    Route::post('/maps/medical-centers', [\App\Http\Controllers\Admin\AdminMapController::class, 'storeMedicalCenter']);
    Route::put('/maps/medical-centers/{medicalCenter}', [\App\Http\Controllers\Admin\AdminMapController::class, 'updateMedicalCenter']);
    Route::delete('/maps/medical-centers/{medicalCenter}', [\App\Http\Controllers\Admin\AdminMapController::class, 'destroyMedicalCenter']);

    // Representatives Management
    Route::apiResource('representatives', \App\Http\Controllers\Admin\AdminRepresentativeController::class);
    Route::post('/representatives/{representative}/approve', [\App\Http\Controllers\Admin\AdminRepresentativeController::class, 'approve']);
    Route::post('/representatives/{representative}/suspend', [\App\Http\Controllers\Admin\AdminRepresentativeController::class, 'suspend']);

    // Support Management
    Route::get('/support/tickets', [\App\Http\Controllers\Admin\AdminSupportController::class, 'index']);
    Route::get('/support/tickets/{ticket}', [\App\Http\Controllers\Admin\AdminSupportController::class, 'show']);
    Route::post('/support/tickets/{ticket}/assign', [\App\Http\Controllers\Admin\AdminSupportController::class, 'assign']);
    Route::put('/support/tickets/{ticket}/status', [\App\Http\Controllers\Admin\AdminSupportController::class, 'updateStatus']);

    // File Uploads Management
    Route::get('/file-uploads', [\App\Http\Controllers\Admin\AdminFileUploadController::class, 'index']);
    Route::post('/file-uploads', [\App\Http\Controllers\Admin\AdminFileUploadController::class, 'upload']);
    Route::delete('/file-uploads/{fileUpload}', [\App\Http\Controllers\Admin\AdminFileUploadController::class, 'destroy']);

    // Visits Management
    Route::get('/visits', [\App\Http\Controllers\Admin\AdminVisitController::class, 'index']);
    Route::get('/visits/{visit}', [\App\Http\Controllers\Admin\AdminVisitController::class, 'show']);
    Route::post('/visits/{visit}/approve', [\App\Http\Controllers\Admin\AdminVisitController::class, 'approve']);
    Route::post('/visits/{visit}/reject', [\App\Http\Controllers\Admin\AdminVisitController::class, 'reject']);
    Route::delete('/visits/{visit}', [\App\Http\Controllers\Admin\AdminVisitController::class, 'destroy']);

    // Drivers Management
    Route::apiResource('drivers', \App\Http\Controllers\Admin\AdminDriverController::class);
    Route::get('/drivers/{driver}/deliveries', [\App\Http\Controllers\Admin\AdminDriverController::class, 'deliveries']);

    // Deliveries Management
    Route::get('/deliveries', [\App\Http\Controllers\Admin\AdminDeliveryController::class, 'index']);
    Route::get('/deliveries/{id}', [\App\Http\Controllers\Admin\AdminDeliveryController::class, 'show']);
    Route::put('/deliveries/{delivery}/status', [\App\Http\Controllers\Admin\AdminDeliveryController::class, 'updateStatus']);
    Route::post('/deliveries/{delivery}/assign-driver', [\App\Http\Controllers\Admin\AdminDeliveryController::class, 'assignDriver']);
});