<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Shop;
use App\Models\Category;
use App\Models\Product;
use App\Models\Doctor;
use App\Models\Coupon;
use App\Models\Slider;
use App\Models\ShopFinancial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompleteDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@eldokan.com'],
            [
                'username' => 'admin',
                'phone' => '01000000000',
                'password' => Hash::make('password'),
                'status' => 'active',
                'role' => 'admin',
                'wallet_balance' => 10000,
            ]
        );

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            if (!$admin->hasRole('admin')) {
                $admin->assignRole('admin');
            }
            // Ensure role field is set
            if ($admin->role !== 'admin') {
                $admin->update(['role' => 'admin']);
            }
        }

        // Create Test Users
        $userRole = Role::where('name', 'user')->first();
        for ($i = 1; $i <= 10; $i++) {
            $user = User::firstOrCreate(
                ['username' => "user{$i}"],
                [
                    'phone' => "0100000000{$i}",
                    'email' => "user{$i}@test.com",
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'role' => 'user',
                    'wallet_balance' => rand(0, 5000),
                ]
            );
            
            // Assign user role if not assigned
            if ($userRole && !$user->hasRole('user')) {
                $user->assignRole('user');
            }
            if ($user->role !== 'user') {
                $user->update(['role' => 'user']);
            }
        }

        // Create Categories (مع صور حقيقية)
        $categories = [
            ['name' => 'تجميل', 'name_ar' => 'تجميل', 'name_en' => 'Beauty', 'slug' => 'beauty', 'image_url' => 'https://picsum.photos/seed/cat-beauty/400/300'],
            ['name' => 'صيدليات', 'name_ar' => 'صيدليات', 'name_en' => 'Pharmacies', 'slug' => 'pharmacies', 'image_url' => 'https://picsum.photos/seed/cat-pharma/400/300'],
            ['name' => 'ملابس', 'name_ar' => 'ملابس', 'name_en' => 'Clothing', 'slug' => 'clothing', 'image_url' => 'https://picsum.photos/seed/cat-clothing/400/300'],
            ['name' => 'إلكترونيات', 'name_ar' => 'إلكترونيات', 'name_en' => 'Electronics', 'slug' => 'electronics', 'image_url' => 'https://picsum.photos/seed/cat-electronics/400/300'],
        ];

        $createdCategories = [];
        foreach ($categories as $cat) {
            $category = Category::firstOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
            $createdCategories[] = $category;

            // Create subcategories
            $subcategories = [
                ['name' => 'مستحضرات التجميل', 'name_ar' => 'مستحضرات التجميل', 'name_en' => 'Cosmetics', 'slug' => $category->slug . '-cosmetics', 'parent_id' => $category->id],
                ['name' => 'العناية بالبشرة', 'name_ar' => 'العناية بالبشرة', 'name_en' => 'Skincare', 'slug' => $category->slug . '-skincare', 'parent_id' => $category->id],
            ];

            foreach ($subcategories as $sub) {
                Category::firstOrCreate(
                    ['slug' => $sub['slug']],
                    $sub
                );
            }
        }

        // Create Shops (with coordinates for delivery tracking map)
        $shopCategories = ['تجميل', 'صيدليات', 'ملابس', 'إلكترونيات'];
        $cairoLat = 30.0444;
        $cairoLng = 31.2357;
        for ($i = 1; $i <= 5; $i++) {
            $shop = Shop::firstOrCreate(
                ['name' => "متجر {$i}"],
                [
                    'name_ar' => "متجر {$i}",
                    'name_en' => "Shop {$i}",
                    'category' => $shopCategories[array_rand($shopCategories)],
                    'address' => "عنوان المتجر {$i}",
                    'latitude' => $cairoLat + ($i * 0.01),
                    'longitude' => $cairoLng + ($i * 0.01),
                    'phone' => "0100000000{$i}",
                    'rating' => rand(30, 50) / 10,
                    'is_active' => true,
                    'image_url' => 'https://picsum.photos/seed/shop-' . $i . '/400/300',
                ]
            );
            if ($shop->latitude === null) {
                $shop->update([
                    'latitude' => $cairoLat + ($i * 0.01),
                    'longitude' => $cairoLng + ($i * 0.01),
                ]);
            }

            ShopFinancial::firstOrCreate(
                ['shop_id' => $shop->id],
                ['commission_rate' => 10.00]
            );
        }

        // Create Products
        $shops = Shop::all();
        $categories = Category::whereNull('parent_id')->get();
        $subcategories = Category::whereNotNull('parent_id')->get();

        $picsum = static function ($seed, $w = 400, $h = 300) {
            return "https://picsum.photos/seed/" . $seed . "/" . $w . "/" . $h;
        };
        foreach ($shops as $shop) {
            for ($i = 1; $i <= 10; $i++) {
                $slug = 'product-' . $shop->id . '-' . $i;
                Product::firstOrCreate(
                    [
                        'shop_id' => $shop->id,
                        'name' => "منتج {$i} - {$shop->name}",
                    ],
                    [
                        'name_ar' => "منتج {$i} - {$shop->name}",
                        'name_en' => "Product {$i} - {$shop->name}",
                        'description' => "وصف المنتج {$i}",
                        'description_ar' => "وصف المنتج {$i}",
                        'description_en' => "Product description {$i}",
                        'short_description' => "وصف قصير للمنتج {$i}",
                        'short_description_ar' => "وصف قصير للمنتج {$i}",
                        'short_description_en' => "Short description for product {$i}",
                        'price' => rand(50, 1000),
                        'discount_percentage' => rand(0, 50),
                        'stock_quantity' => rand(10, 100),
                        'category_id' => $categories->random()->id,
                        'subcategory_id' => $subcategories->random()->id ?? null,
                        'rating' => rand(30, 50) / 10,
                        'is_active' => true,
                        'slug' => $slug,
                        'images' => [
                            $picsum('prod-' . $shop->id . '-' . $i),
                            $picsum('prod-' . $shop->id . '-' . $i . '-2'),
                        ],
                    ]
                );
            }
        }

        // Create Doctor Users
        $doctorRole = Role::where('name', 'doctor')->first();
        $doctorUsers = [];
        for ($i = 1; $i <= 5; $i++) {
            $doctorUser = User::firstOrCreate(
                ['email' => "doctor{$i}@eldokan.com"],
                [
                    'username' => "doctor{$i}",
                    'phone' => "0110000000{$i}",
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'role' => 'doctor',
                    'wallet_balance' => rand(0, 5000),
                ]
            );

            if ($doctorRole && !$doctorUser->hasRole('doctor')) {
                $doctorUser->assignRole('doctor');
            }
            if ($doctorUser->role !== 'doctor') {
                $doctorUser->update(['role' => 'doctor']);
            }
            $doctorUsers[] = $doctorUser;
        }

        // Create Doctors linked to users
        $specialties = ['أمراض القلب', 'طب الأطفال', 'الجراحة', 'طب النساء', 'طب العيون'];
        $specialtiesAr = ['أمراض القلب', 'طب الأطفال', 'الجراحة', 'طب النساء', 'طب العيون'];
        $specialtiesEn = ['Cardiology', 'Pediatrics', 'Surgery', 'Gynecology', 'Ophthalmology'];
        
        for ($i = 0; $i < 5; $i++) {
            $specialtyIndex = array_rand($specialties);
            $doctor = Doctor::firstOrCreate(
                ['user_id' => $doctorUsers[$i]->id],
                [
                    'name' => "د. طبيب " . ($i + 1),
                    'name_ar' => "د. طبيب " . ($i + 1),
                    'name_en' => "Dr. Doctor " . ($i + 1),
                    'specialty' => $specialties[$specialtyIndex],
                    'specialty_ar' => $specialtiesAr[$specialtyIndex],
                    'specialty_en' => $specialtiesEn[$specialtyIndex],
                    'consultation_price' => rand(200, 500),
                    'discount_percentage' => rand(0, 30),
                    'available_days' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday'],
                    'available_hours_start' => '09:00',
                    'available_hours_end' => '17:00',
                    'location' => "عنوان العيادة " . ($i + 1),
                    'location_ar' => "عنوان العيادة " . ($i + 1),
                    'location_en' => "Clinic Address " . ($i + 1),
                    'consultation_duration' => 20,
                    'rating' => rand(40, 50) / 10,
                    'status' => 'active',
                    'is_active' => true,
                    'photo_url' => 'https://picsum.photos/seed/doctor-' . ($i + 1) . '/300/300',
                ]
            );
        }

        // Create Coupons
        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => 'كوبون ترحيبي',
                'type' => 'percentage',
                'value' => 10,
                'min_order_amount' => 100,
                'usage_limit' => 100,
                'is_active' => true,
            ],
            [
                'code' => 'SAVE50',
                'name' => 'خصم 50 جنيه',
                'type' => 'fixed',
                'value' => 50,
                'min_order_amount' => 200,
                'max_discount' => 50,
                'usage_limit' => 50,
                'is_active' => true,
            ],
            [
                'code' => 'FREESHIP',
                'name' => 'شحن مجاني',
                'type' => 'free_shipping',
                'value' => 0,
                'min_order_amount' => 500,
                'usage_limit' => 200,
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::firstOrCreate(
                ['code' => $coupon['code']],
                $coupon
            );
        }

        // Create Sliders (صور حقيقية من picsum.photos)
        for ($i = 1; $i <= 3; $i++) {
            $product = Product::inRandomOrder()->first();
            if ($product) {
                Slider::firstOrCreate(
                    ['title' => "Banner {$i}"],
                    [
                        'title_ar' => "بانر {$i}",
                        'title_en' => "Banner {$i}",
                        'description' => "وصف Banner {$i}",
                        'description_ar' => "وصف البانر {$i}",
                        'description_en' => "Banner description {$i}",
                        'image_url' => 'https://picsum.photos/seed/banner-' . $i . '/1200/400',
                        'link_type' => 'product',
                        'link_id' => $product->id,
                        'order' => $i,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('Complete data seeded successfully!');
    }
}