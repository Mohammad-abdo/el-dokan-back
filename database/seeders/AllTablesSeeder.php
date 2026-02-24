<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\ApplicationStatistic;
use App\Models\Booking;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Delivery;
use App\Models\Doctor;
use App\Models\DoctorPrescription;
use App\Models\DoctorPrescriptionItem;
use App\Models\DoctorWallet;
use App\Models\DoctorWalletTransaction;
use App\Models\Driver;
use App\Models\FileUpload;
use App\Models\FinancialTransaction;
use App\Models\MedicalCenter;
use App\Models\MedicationReminder;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\OtpVerification;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionMedication;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\Rating;
use App\Models\Representative;
use App\Models\Shop;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AllTablesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding all tables...');

        // Get existing data
        $users = User::all();
        $products = Product::all();
        $shops = Shop::all();
        $doctors = Doctor::all();
        $coupons = Coupon::all();

        if ($users->isEmpty() || $products->isEmpty() || $shops->isEmpty() || $doctors->isEmpty()) {
            $this->command->warn('Please run CompleteDataSeeder first to create base data!');
            return;
        }

        // Seed Addresses
        $this->command->info('Seeding Addresses...');
        foreach ($users->take(10) as $user) {
            for ($i = 1; $i <= rand(1, 3); $i++) {
                Address::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'title' => "عنوان {$i}",
                        'detailed_address' => "شارع {$i}, حي {$i}, القاهرة",
                    ],
                    [
                        'city' => 'القاهرة',
                        'district' => 'حي ' . $i,
                        'is_default' => $i === 1,
                        'latitude' => 30.0444 + (rand(-100, 100) / 1000),
                        'longitude' => 31.2357 + (rand(-100, 100) / 1000),
                    ]
                );
            }
        }

        // Seed Carts
        $this->command->info('Seeding Carts...');
        foreach ($users->take(10) as $user) {
            for ($i = 1; $i <= rand(1, 5); $i++) {
                $product = $products->random();
                Cart::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'quantity' => rand(1, 5),
                    ]
                );
            }
        }

        // Seed Orders with Items and Status History
        $this->command->info('Seeding Orders...');
        $orderStatuses = ['received', 'processing', 'on_the_way', 'delivered', 'cancelled'];
        $addresses = Address::all();

        for ($i = 1; $i <= 20; $i++) {
            $user = $users->random();
            $shop = $shops->random();
            $address = $addresses->where('user_id', $user->id)->first();

            if (!$address) continue;

            // Calculate total first
            $shopProducts = $products->where('shop_id', $shop->id)->take(rand(1, 5));
            if ($shopProducts->isEmpty()) continue;

            $totalAmount = 0;
            $items = [];
            foreach ($shopProducts as $product) {
                $quantity = rand(1, 3);
                $price = $product->price * (1 - $product->discount_percentage / 100);
                $totalAmount += $price * $quantity;
                $items[] = ['product' => $product, 'quantity' => $quantity, 'price' => $price];
            }

            $discountAmount = rand(0, (int)($totalAmount * 0.2));
            $deliveryFee = rand(20, 50);

            $orderNumber = 'ORD-' . str_pad($i, 6, '0', STR_PAD_LEFT);
            $order = Order::firstOrCreate(
                ['order_number' => $orderNumber],
                [
                    'user_id' => $user->id,
                    'shop_id' => $shop->id,
                    'status' => $orderStatuses[array_rand($orderStatuses)],
                    'total_amount' => $totalAmount,
                    'discount_amount' => $discountAmount,
                    'delivery_fee' => $deliveryFee,
                    'delivery_address_id' => $address->id,
                    'payment_method' => ['credit_card', 'e_wallet', 'cash_on_delivery'][array_rand(['credit_card', 'e_wallet', 'cash_on_delivery'])],
                    'payment_status' => ['pending', 'paid', 'failed', 'refunded'][array_rand(['pending', 'paid', 'failed', 'refunded'])],
                    'created_at' => Carbon::now()->subDays(rand(0, 30)),
                ]
            );

            // Add order items
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                ]);
            }

            // Add status history (only if order was just created)
            if ($order->wasRecentlyCreated) {
                OrderStatusHistory::firstOrCreate([
                    'order_id' => $order->id,
                    'status' => $order->status,
                ], [
                    'description' => 'Order ' . $order->status,
                    'timestamp' => $order->created_at,
                ]);
            }
        }

        // Seed Payments
        $this->command->info('Seeding Payments...');
        $orders = Order::where('payment_status', 'paid')->get();
        foreach ($orders as $order) {
            Payment::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'user_id' => $order->user_id,
                    'amount' => $order->total_amount,
                    'payment_method' => $order->payment_method,
                    'status' => 'paid',
                    'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                    'total_amount' => $order->total_amount,
                    'discount_amount' => $order->discount_amount,
                    'delivery_fee' => $order->delivery_fee,
                ]
            );
        }

        // Seed Deliveries
        $this->command->info('Seeding Deliveries...');
        $drivers = Driver::all();
        if ($drivers->isEmpty()) {
            // Create drivers first
            for ($i = 1; $i <= 5; $i++) {
                Driver::firstOrCreate(
                    ['phone' => "0100000{$i}00"],
                    [
                        'name' => "سائق {$i}",
                        'status' => 'available',
                        'rating' => rand(40, 50) / 10,
                    ]
                );
            }
            $drivers = Driver::all();
        }

        $shippedOrders = Order::whereIn('status', ['on_the_way', 'delivered'])->get();
        foreach ($shippedOrders as $order) {
            $address = Address::where('user_id', $order->user_id)->first();
            if (!$address) continue;
            
            $shop = Shop::find($order->shop_id);
            Delivery::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'driver_id' => $drivers->random()->id,
                    'store_address' => $shop ? $shop->address : 'Store Address',
                    'delivery_address' => $address->detailed_address ?? $address->title,
                    'status' => $order->status === 'delivered' ? 'delivered' : 'in_transit',
                    'estimated_arrival_minutes' => rand(15, 60),
                ]
            );
        }

        // Seed Prescriptions
        $this->command->info('Seeding Prescriptions...');
        for ($i = 1; $i <= 15; $i++) {
            $user = $users->random();
            $pharmacy = $shops->random();
            $prescriptionNumber = 'PRES-' . str_pad($i, 6, '0', STR_PAD_LEFT);
            
            $prescription = Prescription::firstOrCreate(
                ['prescription_number' => $prescriptionNumber],
                [
                    'user_id' => $user->id,
                    'pharmacy_id' => $pharmacy->id,
                    'pharmacist_id' => null,
                    'images' => [
                        'https://picsum.photos/seed/pres-' . $i . '-1/600/800',
                        'https://picsum.photos/seed/pres-' . $i . '-2/600/800',
                    ],
                    'status' => ['under_review', 'dispensed', 'rejected'][array_rand(['under_review', 'dispensed', 'rejected'])],
                    'notes' => 'ملاحظات الوصفة الطبية ' . $i,
                    'created_at' => Carbon::now()->subDays(rand(0, 20)),
                ]
            );

            // Add prescription medications
            for ($j = 1; $j <= rand(2, 5); $j++) {
                PrescriptionMedication::create([
                    'prescription_id' => $prescription->id,
                    'medication_name' => "دواء {$j}",
                    'dosage' => rand(1, 3) . ' حبة',
                    'form' => 'tablets',
                    'quantity' => rand(10, 30),
                    'duration' => rand(5, 14),
                    'instructions' => 'تناول بعد الأكل - ' . rand(1, 3) . ' مرة يومياً',
                    'price' => rand(50, 200),
                ]);
            }
        }

        // Seed Medication Reminders
        $this->command->info('Seeding Medication Reminders...');
        $prescriptions = Prescription::where('status', 'dispensed')->get();
        foreach ($prescriptions as $prescription) {
            foreach ($prescription->medications as $medication) {
                MedicationReminder::create([
                    'user_id' => $prescription->user_id,
                    'prescription_medication_id' => $medication->id,
                    'medication_name' => $medication->medication_name,
                    'reminder_time' => ['08:00', '14:00', '20:00'][array_rand(['08:00', '14:00', '20:00'])],
                    'time_period' => ['am', 'pm'][array_rand(['am', 'pm'])],
                    'frequency' => ['daily', 'twice_daily', 'three_times_daily'][array_rand(['daily', 'twice_daily', 'three_times_daily'])],
                    'duration' => ['week', 'two_weeks', 'month'][array_rand(['week', 'two_weeks', 'month'])],
                    'is_active' => true,
                ]);
            }
        }

        // Seed Bookings
        $this->command->info('Seeding Bookings...');
        for ($i = 1; $i <= 20; $i++) {
            $user = $users->random();
            $doctor = $doctors->random();
            $appointmentDate = Carbon::now()->addDays(rand(1, 30));
            $bookingNumber = 'BOOK-' . str_pad($i, 6, '0', STR_PAD_LEFT);

            Booking::firstOrCreate(
                ['booking_number' => $bookingNumber],
                [
                    'user_id' => $user->id,
                    'doctor_id' => $doctor->id,
                    'booking_type' => ['online', 'in_clinic'][array_rand(['online', 'in_clinic'])],
                    'appointment_date' => $appointmentDate->format('Y-m-d'),
                    'appointment_time' => ['09:00', '11:00', '14:00', '16:00'][array_rand(['09:00', '11:00', '14:00', '16:00'])],
                    'patient_name' => $user->username,
                    'status' => ['upcoming', 'in_progress', 'completed', 'cancelled'][array_rand(['upcoming', 'in_progress', 'completed', 'cancelled'])],
                    'total_amount' => $doctor->consultation_price,
                    'payment_method' => ['credit_card', 'e_wallet', 'cash_on_delivery'][array_rand(['credit_card', 'e_wallet', 'cash_on_delivery'])],
                    'payment_status' => ['pending', 'paid', 'failed', 'refunded'][array_rand(['pending', 'paid', 'failed', 'refunded'])],
                    'created_at' => Carbon::now()->subDays(rand(0, 10)),
                ]
            );
        }

        // Seed Ratings
        $this->command->info('Seeding Ratings...');
        foreach ($products->take(30) as $product) {
            for ($i = 1; $i <= rand(1, 5); $i++) {
                Rating::firstOrCreate(
                    [
                        'user_id' => $users->random()->id,
                        'rateable_type' => Product::class,
                        'rateable_id' => $product->id,
                    ],
                    [
                        'rating' => rand(3, 5),
                        'comment' => 'تعليق على المنتج',
                    ]
                );
            }
        }

        foreach ($shops->take(5) as $shop) {
            for ($i = 1; $i <= rand(1, 3); $i++) {
                Rating::firstOrCreate(
                    [
                        'user_id' => $users->random()->id,
                        'rateable_type' => Shop::class,
                        'rateable_id' => $shop->id,
                    ],
                    [
                        'rating' => rand(3, 5),
                        'comment' => 'تعليق على المتجر',
                    ]
                );
            }
        }

        foreach ($doctors->take(5) as $doctor) {
            for ($i = 1; $i <= rand(1, 3); $i++) {
                Rating::firstOrCreate(
                    [
                        'user_id' => $users->random()->id,
                        'rateable_type' => Doctor::class,
                        'rateable_id' => $doctor->id,
                    ],
                    [
                        'rating' => rand(4, 5),
                        'comment' => 'تعليق على الطبيب',
                    ]
                );
            }
        }

        // Seed Notifications
        $this->command->info('Seeding Notifications...');
        $notificationTypes = ['order', 'booking', 'promotion', 'system'];
        foreach ($users->take(20) as $user) {
            for ($i = 1; $i <= rand(3, 10); $i++) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => $notificationTypes[array_rand($notificationTypes)],
                    'title' => 'إشعار ' . $i,
                    'description' => 'رسالة الإشعار ' . $i,
                    'is_read' => rand(0, 1),
                    'created_at' => Carbon::now()->subDays(rand(0, 7)),
                ]);
            }
        }

        // Seed Messages
        $this->command->info('Seeding Messages...');
        for ($i = 1; $i <= 30; $i++) {
            $sender = $users->random();
            $receiver = $users->where('id', '!=', $sender->id)->random();

            Message::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'content' => 'رسالة نصية ' . $i,
                'is_read' => rand(0, 1),
                'created_at' => Carbon::now()->subDays(rand(0, 5)),
            ]);
        }

        // Seed OTP Verifications
        $this->command->info('Seeding OTP Verifications...');
        foreach ($users->take(10) as $user) {
            OtpVerification::create([
                'phone' => $user->phone,
                'otp' => str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
                'is_verified' => true,
                'expires_at' => Carbon::now()->addMinutes(5),
                'created_at' => Carbon::now()->subDays(rand(0, 10)),
            ]);
        }

        // Seed Promo Codes
        $this->command->info('Seeding Promo Codes...');
        $promoCodes = [
            ['code' => 'NEWUSER20', 'type' => 'percentage', 'value' => 20.00, 'min_order_amount' => 100.00],
            ['code' => 'SUMMER50', 'type' => 'percentage', 'value' => 50.00, 'min_order_amount' => 200.00],
            ['code' => 'WEEKEND30', 'type' => 'percentage', 'value' => 30.00, 'min_order_amount' => 150.00],
            ['code' => 'FLAT100', 'type' => 'fixed', 'value' => 100.00, 'min_order_amount' => 500.00],
        ];

        foreach ($promoCodes as $promo) {
            PromoCode::firstOrCreate(
                ['code' => $promo['code']],
                array_merge($promo, [
                    'usage_limit' => rand(50, 200),
                    'is_active' => true,
                    'valid_from' => Carbon::now()->toDateString(),
                    'valid_until' => Carbon::now()->addMonths(3)->toDateString(),
                ])
            );
        }

        // Seed Coupon Usages
        $this->command->info('Seeding Coupon Usages...');
        $usedOrders = Order::where('discount_amount', '>', 0)->take(10)->get();
        foreach ($usedOrders as $order) {
            if ($coupons->isNotEmpty()) {
                CouponUsage::create([
                    'coupon_id' => $coupons->random()->id,
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'discount_amount' => $order->discount_amount,
                    'created_at' => $order->created_at,
                ]);
            }
        }

        // Seed Medical Centers
        $this->command->info('Seeding Medical Centers...');
        $medicalCenters = [
            ['name' => 'مركز طبي 1', 'name_en' => 'Medical Center 1', 'address' => 'القاهرة', 'latitude' => 30.0444, 'longitude' => 31.2357],
            ['name' => 'مركز طبي 2', 'name_en' => 'Medical Center 2', 'address' => 'الجيزة', 'latitude' => 30.0079, 'longitude' => 31.2109],
            ['name' => 'مركز طبي 3', 'name_en' => 'Medical Center 3', 'address' => 'الإسكندرية', 'latitude' => 31.2156, 'longitude' => 29.9553],
        ];

        $createdCenters = [];
        foreach ($medicalCenters as $center) {
            $mc = MedicalCenter::firstOrCreate(
                ['name' => $center['name']],
                $center
            );
            $createdCenters[] = $mc;
        }

        // Seed Doctor Medical Centers (using pivot table)
        foreach ($doctors as $doctor) {
            if ($createdCenters && !$doctor->medicalCenters()->exists()) {
                $center = $createdCenters[array_rand($createdCenters)];
                $doctor->medicalCenters()->attach($center->id, [
                    'is_primary' => rand(0, 1),
                ]);
            }
        }

        // Seed Doctor Prescriptions
        $this->command->info('Seeding Doctor Prescriptions...');
        for ($i = 1; $i <= 10; $i++) {
            $doctor = $doctors->random();
            $user = $users->random();
            $prescriptionNumber = 'DOC-' . str_pad($i, 6, '0', STR_PAD_LEFT);

            $doctorPrescription = DoctorPrescription::firstOrCreate(
                ['prescription_number' => $prescriptionNumber],
                [
                    'doctor_id' => $doctor->id,
                    'patient_id' => $user->id,
                    'prescription_name' => 'وصفة طبية ' . $i,
                    'patient_name' => $user->username,
                    'notes' => 'ملاحظات الطبيب',
                    'created_at' => Carbon::now()->subDays(rand(0, 15)),
                ]
            );

            // Add prescription items (only if prescription was just created)
            if ($doctorPrescription->wasRecentlyCreated) {
                for ($j = 1; $j <= rand(2, 4); $j++) {
                    DoctorPrescriptionItem::create([
                        'doctor_prescription_id' => $doctorPrescription->id,
                        'medication_name' => "دواء طبي {$j}",
                        'quantity' => rand(1, 10),
                        'notes' => 'جرعة: ' . rand(1, 3) . ' حبة، ' . rand(1, 3) . ' مرة يوميا لمدة ' . rand(5, 14) . ' يوم',
                        'order' => $j,
                    ]);
                }
            }
        }

        // Seed Doctor Wallets and Transactions
        $this->command->info('Seeding Doctor Wallets...');
        foreach ($doctors as $doctor) {
            $wallet = DoctorWallet::firstOrCreate(
                ['doctor_id' => $doctor->id],
                [
                    'balance' => rand(1000, 10000),
                    'pending_balance' => rand(0, 2000),
                ]
            );

            // Add wallet transactions
            for ($i = 1; $i <= rand(3, 8); $i++) {
                DoctorWalletTransaction::create([
                    'doctor_id' => $doctor->id,
                    'type' => ['booking_payment', 'commission', 'withdrawal', 'transfer', 'refund'][array_rand(['booking_payment', 'commission', 'withdrawal', 'transfer', 'refund'])],
                    'amount' => rand(100, 2000),
                    'description' => 'معاملة محفظة ' . $i,
                    'status' => 'completed',
                    'created_at' => Carbon::now()->subDays(rand(0, 20)),
                ]);
            }
        }

        // Seed Representatives
        $this->command->info('Seeding Representatives...');
        $repRole = \App\Models\Role::where('name', 'representative')->first();
        $repUsers = $users->where('id', '>', 10)->take(3);
        foreach ($repUsers as $user) {
            // Assign representative role
            if ($repRole && !$user->hasRole('representative')) {
                $user->assignRole('representative');
            }
            if ($user->role !== 'representative') {
                $user->update(['role' => 'representative']);
            }
            
            Representative::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_id' => 'EMP-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    'territory' => ['القاهرة', 'الجيزة', 'الإسكندرية'][array_rand(['القاهرة', 'الجيزة', 'الإسكندرية'])],
                    'status' => 'active',
                ]
            );
        }

        // Seed Visits
        $this->command->info('Seeding Visits...');
        $representatives = Representative::all();
        foreach ($representatives as $rep) {
            for ($i = 1; $i <= rand(3, 8); $i++) {
                Visit::create([
                    'representative_id' => $rep->id,
                    'shop_id' => $shops->random()->id,
                    'doctor_id' => $doctors->random()->id,
                    'visit_date' => Carbon::now()->subDays(rand(0, 30))->toDateString(),
                    'visit_time' => ['09:00', '11:00', '14:00', '16:00'][array_rand(['09:00', '11:00', '14:00', '16:00'])],
                    'purpose' => 'زيارة متابعة ' . $i,
                    'status' => ['pending', 'approved', 'rejected', 'completed'][array_rand(['pending', 'approved', 'rejected', 'completed'])],
                    'notes' => 'ملاحظات الزيارة ' . $i,
                ]);
            }
        }

        // Seed Support Tickets
        $this->command->info('Seeding Support Tickets...');
        for ($i = 1; $i <= 15; $i++) {
            $user = $users->random();
            $ticketNumber = 'TCK-' . str_pad($i, 6, '0', STR_PAD_LEFT);
            
            $ticket = SupportTicket::firstOrCreate(
                ['ticket_number' => $ticketNumber],
                [
                    'user_id' => $user->id,
                    'subject' => 'موضوع التذكرة ' . $i,
                    'description' => 'وصف المشكلة ' . $i,
                    'status' => ['open', 'in_progress', 'resolved', 'closed'][array_rand(['open', 'in_progress', 'resolved', 'closed'])],
                    'priority' => ['low', 'medium', 'high'][array_rand(['low', 'medium', 'high'])],
                    'created_at' => Carbon::now()->subDays(rand(0, 10)),
                ]
            );

            // Add support messages (only if ticket was just created)
            if ($ticket->wasRecentlyCreated) {
                for ($j = 1; $j <= rand(1, 3); $j++) {
                    SupportMessage::create([
                        'ticket_id' => $ticket->id,
                        'sender_id' => $j === 1 ? $user->id : $users->where('id', '!=', $user->id)->random()->id, // First message from user, rest from support
                        'message' => 'رسالة الدعم ' . $j,
                        'created_at' => $ticket->created_at->addMinutes($j * 10),
                    ]);
                }
            }
        }

        // Seed Financial Transactions
        $this->command->info('Seeding Financial Transactions...');
        for ($i = 1; $i <= 30; $i++) {
            $transactionNumber = 'TXN-' . str_pad($i, 6, '0', STR_PAD_LEFT);
            
            FinancialTransaction::firstOrCreate(
                ['transaction_number' => $transactionNumber],
                [
                    'type' => ['income', 'expense', 'refund', 'commission', 'withdrawal'][array_rand(['income', 'expense', 'refund', 'commission', 'withdrawal'])],
                    'category' => ['order', 'booking', 'commission', 'refund', 'withdrawal', 'other'][array_rand(['order', 'booking', 'commission', 'refund', 'withdrawal', 'other'])],
                    'amount' => rand(50, 2000),
                    'description' => 'معاملة مالية ' . $i,
                    'status' => ['pending', 'completed', 'failed', 'cancelled'][array_rand(['pending', 'completed', 'failed', 'cancelled'])],
                    'transaction_date' => Carbon::now()->subDays(rand(0, 20))->toDateString(),
                    'created_at' => Carbon::now()->subDays(rand(0, 20)),
                ]
            );
        }

        // Seed Application Statistics
        $this->command->info('Seeding Application Statistics...');
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i);
            ApplicationStatistic::firstOrCreate(
                ['date' => $date->toDateString()],
                [
                    'total_users' => User::whereDate('created_at', '<=', $date)->count(),
                    'active_users' => User::whereDate('created_at', '<=', $date)->count(),
                    'total_orders' => Order::whereDate('created_at', '<=', $date)->count(),
                    'completed_orders' => Order::where('status', 'delivered')->whereDate('created_at', '<=', $date)->count(),
                    'total_bookings' => Booking::whereDate('created_at', '<=', $date)->count(),
                    'completed_bookings' => Booking::where('status', 'completed')->whereDate('created_at', '<=', $date)->count(),
                    'total_revenue' => Order::whereDate('created_at', '<=', $date)->sum('total_amount'),
                    'total_commission' => Order::whereDate('created_at', '<=', $date)->sum('total_amount') * 0.1, // Assuming 10% commission
                    'total_products' => Product::count(),
                    'total_shops' => Shop::count(),
                    'total_doctors' => Doctor::count(),
                ]
            );
        }

        // Seed File Uploads
        $this->command->info('Seeding File Uploads...');
        foreach ($users->take(10) as $user) {
            for ($i = 1; $i <= rand(1, 3); $i++) {
                FileUpload::create([
                    'uploadable_type' => User::class,
                    'uploadable_id' => $user->id,
                    'file_type' => ['products', 'customers', 'visits', 'other'][array_rand(['products', 'customers', 'visits', 'other'])],
                    'file_name' => "file-{$i}.jpg",
                    'file_path' => "uploads/file-{$i}.jpg",
                    'file_size' => rand(1000, 5000000),
                    'mime_type' => 'image/jpeg',
                    'description' => 'ملف مرفوع ' . $i,
                ]);
            }
        }

        $this->command->info('All tables seeded successfully!');
    }
}
