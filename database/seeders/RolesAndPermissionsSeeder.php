<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // Users
            ['name' => 'view_users', 'display_name' => 'View Users', 'group' => 'users'],
            ['name' => 'create_users', 'display_name' => 'Create Users', 'group' => 'users'],
            ['name' => 'edit_users', 'display_name' => 'Edit Users', 'group' => 'users'],
            ['name' => 'delete_users', 'display_name' => 'Delete Users', 'group' => 'users'],

            // Products
            ['name' => 'view_products', 'display_name' => 'View Products', 'group' => 'products'],
            ['name' => 'create_products', 'display_name' => 'Create Products', 'group' => 'products'],
            ['name' => 'edit_products', 'display_name' => 'Edit Products', 'group' => 'products'],
            ['name' => 'delete_products', 'display_name' => 'Delete Products', 'group' => 'products'],

            // Orders
            ['name' => 'view_orders', 'display_name' => 'View Orders', 'group' => 'orders'],
            ['name' => 'manage_orders', 'display_name' => 'Manage Orders', 'group' => 'orders'],

            // Doctors
            ['name' => 'view_doctors', 'display_name' => 'View Doctors', 'group' => 'doctors'],
            ['name' => 'create_doctors', 'display_name' => 'Create Doctors', 'group' => 'doctors'],
            ['name' => 'edit_doctors', 'display_name' => 'Edit Doctors', 'group' => 'doctors'],
            ['name' => 'suspend_doctors', 'display_name' => 'Suspend Doctors', 'group' => 'doctors'],
            ['name' => 'delete_doctors', 'display_name' => 'Delete Doctors', 'group' => 'doctors'],

            // Financial
            ['name' => 'view_financial', 'display_name' => 'View Financial', 'group' => 'financial'],
            ['name' => 'manage_financial', 'display_name' => 'Manage Financial', 'group' => 'financial'],

            // Ratings
            ['name' => 'view_ratings', 'display_name' => 'View Ratings', 'group' => 'ratings'],
            ['name' => 'approve_ratings', 'display_name' => 'Approve Ratings', 'group' => 'ratings'],
            ['name' => 'delete_ratings', 'display_name' => 'Delete Ratings', 'group' => 'ratings'],

            // Sliders
            ['name' => 'view_sliders', 'display_name' => 'View Sliders', 'group' => 'sliders'],
            ['name' => 'create_sliders', 'display_name' => 'Create Sliders', 'group' => 'sliders'],
            ['name' => 'edit_sliders', 'display_name' => 'Edit Sliders', 'group' => 'sliders'],
            ['name' => 'delete_sliders', 'display_name' => 'Delete Sliders', 'group' => 'sliders'],

            // Categories
            ['name' => 'view_categories', 'display_name' => 'View Categories', 'group' => 'categories'],
            ['name' => 'create_categories', 'display_name' => 'Create Categories', 'group' => 'categories'],
            ['name' => 'edit_categories', 'display_name' => 'Edit Categories', 'group' => 'categories'],
            ['name' => 'delete_categories', 'display_name' => 'Delete Categories', 'group' => 'categories'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Create Admin Role
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'Full system access',
            ]
        );

        // Assign all permissions to admin (sync to avoid duplicates)
        $adminRole->permissions()->syncWithoutDetaching(Permission::all()->pluck('id'));

        // Create Doctor Role
        $doctorRole = Role::firstOrCreate(
            ['name' => 'doctor'],
            [
                'display_name' => 'Doctor',
                'description' => 'Doctor access to manage patients, prescriptions, and bookings',
            ]
        );

        // Create Representative Role
        $representativeRole = Role::firstOrCreate(
            ['name' => 'representative'],
            [
                'display_name' => 'Representative',
                'description' => 'Representative access to manage products and visits',
            ]
        );

        // Create Shop Role (vendor / store owner)
        Role::firstOrCreate(
            ['name' => 'shop'],
            [
                'display_name' => 'Shop',
                'description' => 'Shop owner access to manage products and orders',
            ]
        );

        // Create Company Role (شركات أدوية/تراكيب/منتجات - مندوبين وزيارات ومبيعات)
        Role::firstOrCreate(
            ['name' => 'company'],
            [
                'display_name' => 'Company',
                'description' => 'Company access: products, representatives, visits, sales',
            ]
        );

        // Create Driver Role
        Role::firstOrCreate(
            ['name' => 'driver'],
            [
                'display_name' => 'Driver',
                'description' => 'Driver access for deliveries',
            ]
        );

        // Create User Role (regular user)
        $userRole = Role::firstOrCreate(
            ['name' => 'user'],
            [
                'display_name' => 'User',
                'description' => 'Regular user access',
            ]
        );
    }
}

