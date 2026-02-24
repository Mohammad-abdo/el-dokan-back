<?php

namespace Database\Seeders;

use App\Models\CompanyOrder;
use App\Models\CompanyOrderItem;
use App\Models\CompanyPlan;
use App\Models\CompanyProduct;
use App\Models\Doctor;
use App\Models\Representative;
use App\Models\Role;
use App\Models\Shop;
use App\Models\ShopBranch;
use App\Models\ShopDocument;
use App\Models\ShopFinancial;
use App\Models\ShopWalletAdjustment;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed شركات كاملة: منتجات شركة، مندوبين، محفظة، مبيعات، زيارات، فروع، وثائق.
 * والربط مع المتاجر والأطباء (الزيارات والمبيعات).
 */
class CompaniesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Companies (شركات) with products, reps, wallet, sales, visits...');

        $companyRole = Role::where('name', 'company')->first();
        $repRole = Role::where('name', 'representative')->first();
        if (!$companyRole) {
            $this->command->warn('Company role not found. Run RolesAndPermissionsSeeder first.');
            return;
        }

        $shops = Shop::all();
        $doctors = Doctor::with('user')->get();
        if ($shops->isEmpty() || $doctors->isEmpty()) {
            $this->command->warn('Shops and Doctors required. Run CompleteDataSeeder first.');
        }

        $companiesData = [
            [
                'name' => 'شركة الأدوية المتحدة',
                'name_ar' => 'شركة الأدوية المتحدة',
                'category' => 'company',
                'address' => 'القاهرة، المنطقة الصناعية',
                'phone' => '0223456789',
            ],
            [
                'name' => 'شركة تراكيب الطبية',
                'name_ar' => 'شركة تراكيب الطبية',
                'category' => 'company',
                'address' => 'الجيزة، شارع الهرم',
                'phone' => '0223456790',
            ],
            [
                'name' => 'مؤسسة المنتجات الصحية',
                'name_ar' => 'مؤسسة المنتجات الصحية',
                'category' => 'company',
                'address' => 'الإسكندرية، سموحة',
                'phone' => '0323456791',
            ],
        ];

        foreach ($companiesData as $index => $companyData) {
            $user = User::firstOrCreate(
                ['email' => 'company' . ($index + 1) . '@eldokan.com'],
                [
                    'username' => 'company' . ($index + 1),
                    'phone' => $companyData['phone'],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'role' => 'company',
                    'wallet_balance' => 0,
                ]
            );
            if (!$user->hasRole('company')) {
                $user->assignRole('company');
            }
            $user->update(['role' => 'company']);

            $defaultPlan = CompanyPlan::where('slug', 'basic')->first();
            $shop = Shop::firstOrCreate(
                ['name' => $companyData['name']],
                [
                    'user_id' => $user->id,
                    'category' => $companyData['category'],
                    'address' => $companyData['address'],
                    'phone' => $companyData['phone'],
                    'latitude' => 30.0444 + (($index + 1) * 0.02),
                    'longitude' => 31.2357 + (($index + 1) * 0.02),
                    'is_active' => true,
                    'vendor_status' => 'approved',
                    'company_plan_id' => $defaultPlan?->id,
                    'image_url' => 'https://picsum.photos/seed/company-' . ($index + 1) . '/400/300',
                ]
            );
            if ($shop->user_id !== $user->id) {
                $shop->update(['user_id' => $user->id, 'vendor_status' => 'approved']);
            }

            // محفظة الشركة
            ShopFinancial::firstOrCreate(
                ['shop_id' => $shop->id],
                [
                    'total_revenue' => rand(50000, 200000),
                    'total_commission' => rand(5000, 20000),
                    'pending_balance' => rand(5000, 15000),
                    'available_balance' => rand(30000, 100000),
                    'commission_rate' => 10,
                    'profit_share_percentage' => 90,
                ]
            );

            // فروع
            for ($b = 1; $b <= 2; $b++) {
                ShopBranch::firstOrCreate(
                    ['shop_id' => $shop->id, 'name' => $companyData['name'] . ' - فرع ' . $b],
                    [
                        'address' => $companyData['address'] . ' فرع ' . $b,
                        'phone' => $companyData['phone'],
                        'is_active' => true,
                        'sort_order' => $b,
                    ]
                );
            }

            // وثائق الشركة
            ShopDocument::firstOrCreate(
                ['shop_id' => $shop->id, 'type' => 'license', 'reference_number' => 'LIC-' . $shop->id],
                [
                    'title' => 'رخصة مزاولة',
                    'title_ar' => 'رخصة مزاولة النشاط',
                    'issue_date' => now()->subYears(2),
                    'expires_at' => now()->addYears(1),
                    'is_verified' => true,
                ]
            );

            // منتجات الشركة (أدوية، تراكيب، أخرى) — مع صور ووصف حقيقيين
            $productNames = [
                ['name' => 'دواء باراسيتامول 500', 'type' => 'drug', 'price' => 25.50, 'desc' => 'مسكن وخافض للحرارة، 500 ملغ'],
                ['name' => 'دواء إيبوبروفين', 'type' => 'drug', 'price' => 35.00, 'desc' => 'مضاد التهاب لا ستيرويدي، مسكن'],
                ['name' => 'تركيب فيتامين د', 'type' => 'compound', 'price' => 120.00, 'desc' => 'تركيبة فيتامين د مع الكالسيوم للعظام'],
                ['name' => 'تركيب كالسيوم + زنك', 'type' => 'compound', 'price' => 85.00, 'desc' => 'مكمل معادن للصحة العامة'],
                ['name' => 'مكمل أوميجا 3', 'type' => 'other', 'price' => 150.00, 'desc' => 'زيت سمك أوميجا 3 للقلب والدماغ'],
                ['name' => 'مستحضر عناية بالبشرة', 'type' => 'other', 'price' => 95.00, 'desc' => 'كريم عناية يومي للبشرة'],
            ];
            foreach ($productNames as $i => $p) {
                $seed = 'cp-' . $shop->id . '-' . ($i + 1);
                CompanyProduct::firstOrCreate(
                    ['shop_id' => $shop->id, 'name' => $p['name'] . ' - ' . $companyData['name']],
                    [
                        'name_ar' => $p['name'],
                        'sku' => 'CP-' . $shop->id . '-' . ($i + 1),
                        'product_type' => $p['type'],
                        'unit_price' => $p['price'] + ($index * 5),
                        'stock_quantity' => rand(100, 500),
                        'unit' => ['حبة', 'علبة', 'زجاجة', 'كرتون'][$i % 4],
                        'description' => $p['desc'] ?? '',
                        'description_ar' => $p['desc'] ?? $p['name'],
                        'is_active' => true,
                        'sort_order' => $i + 1,
                        'images' => [
                            'https://picsum.photos/seed/' . $seed . '/400/300',
                            'https://picsum.photos/seed/' . $seed . '-2/400/300',
                        ],
                    ]
                );
            }

            // موظفون/مندوبون الشركة
            $repUsers = [];
            for ($r = 1; $r <= 3; $r++) {
                $repUser = User::firstOrCreate(
                    ['email' => "rep_{$shop->id}_{$r}@eldokan.com"],
                    [
                        'username' => "rep_{$shop->id}_{$r}",
                        'phone' => '01' . str_pad($shop->id * 100 + $r, 9, '0'),
                        'password' => Hash::make('password'),
                        'status' => 'active',
                        'role' => 'representative',
                        'wallet_balance' => 0,
                    ]
                );
                if ($repRole && !$repUser->hasRole('representative')) {
                    $repUser->assignRole('representative');
                }
                $repUser->update(['role' => 'representative']);
                $repUsers[] = $repUser;
            }

            $representatives = [];
            foreach ($repUsers as $ri => $ru) {
                $rep = Representative::firstOrCreate(
                    ['user_id' => $ru->id],
                    [
                        'shop_id' => $shop->id,
                        'employee_id' => 'EMP-' . $shop->id . '-' . str_pad($ri + 1, 3, '0', STR_PAD_LEFT),
                        'territory' => ['القاهرة', 'الجيزة', 'الإسكندرية', 'المنيا'][$ri % 4],
                        'status' => 'active',
                    ]
                );
                if ($rep->shop_id !== $shop->id) {
                    $rep->update(['shop_id' => $shop->id]);
                }
                $representatives[] = $rep;
            }

            // زيارات  مندوبين المبيعات  للمتاجر والأطباء (الربط مع فلو المتاجر والأطباء)
            foreach ($representatives as $rep) {
                $visitCount = rand(4, 10);
                for ($v = 1; $v <= $visitCount; $v++) {
                    $targetShop = $shops->where('id', '!=', $shop->id)->random();
                    $doctor = $doctors->random();
                    $visitDate = Carbon::now()->subDays(rand(0, 60));
                    Visit::create([
                        'representative_id' => $rep->id,
                        'shop_id' => $targetShop->id,
                        'doctor_id' => $doctor->id,
                        'visit_date' => $visitDate->toDateString(),
                        'visit_time' => ['09:00', '10:30', '14:00', '16:00'][array_rand(['09:00', '10:30', '14:00', '16:00'])],
                        'purpose' => 'زيارة ترويج منتجات شركة ' . $companyData['name'],
                        'notes' => 'زيارة رقم ' . $v,
                        'status' => ['pending', 'approved', 'completed'][array_rand(['pending', 'approved', 'completed'])],
                    ]);
                }
            }

            // مبيعات الشركة (طلبات من  مندوبين المبيعات  لمتاجر/أطباء) مرتبطة بزيارات
            $visits = Visit::whereIn('representative_id', collect($representatives)->pluck('id'))->where('status', 'completed')->get();
            $companyProducts = CompanyProduct::where('shop_id', $shop->id)->get();
            if ($companyProducts->isNotEmpty() && $visits->isNotEmpty()) {
                foreach ($visits->take(15) as $visit) {
                    $itemsCount = rand(1, 3);
                    $products = $companyProducts->random(min($itemsCount, $companyProducts->count()));
                    $total = 0;
                    $orderItems = [];
                    foreach ($products as $cp) {
                        $qty = rand(1, 5);
                        $price = $cp->unit_price;
                        $rowTotal = $qty * $price;
                        $total += $rowTotal;
                        $orderItems[] = ['company_product_id' => $cp->id, 'quantity' => $qty, 'unit_price' => $price, 'total_price' => $rowTotal];
                    }
                    $customerType = rand(0, 1) ? 'shop' : 'doctor';
                    $customerId = $customerType === 'shop' ? $visit->shop_id : $visit->doctor_id;
                    if (!$customerId) {
                        $customerId = $customerType === 'shop' ? $shops->random()->id : $doctors->random()->id;
                    }
                    $order = CompanyOrder::create([
                        'shop_id' => $shop->id,
                        'representative_id' => $visit->representative_id,
                        'visit_id' => $visit->id,
                        'customer_type' => $customerType,
                        'customer_id' => $customerId,
                        'total_amount' => $total,
                        'status' => ['pending', 'confirmed', 'delivered'][array_rand(['pending', 'confirmed', 'delivered'])],
                        'notes' => 'طلب من زيارة',
                        'ordered_at' => $visit->visit_date ? Carbon::parse($visit->visit_date) : now(),
                    ]);
                    foreach ($orderItems as $item) {
                        CompanyOrderItem::create([
                            'company_order_id' => $order->id,
                            'company_product_id' => $item['company_product_id'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'total_price' => $item['total_price'],
                        ]);
                    }
                }
            }

            // تعديل محفظة (عينة) للربط مع فلو المحفظة
            ShopWalletAdjustment::firstOrCreate(
                [
                    'shop_id' => $shop->id,
                    'amount' => 5000,
                    'type' => 'credit',
                    'description' => 'إيداع أولي - سييد',
                ],
                ['admin_user_id' => User::where('role', 'admin')->first()?->id]
            );
        }

        $this->command->info('Companies seeded. Companies have: products, representatives, wallet, sales, visits, branches, documents.');
    }
}
