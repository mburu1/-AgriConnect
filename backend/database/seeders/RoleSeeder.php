<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'ADMIN',           'display_name' => 'Administrator',      'description' => 'Full platform access.'],
            ['name' => 'FARMER',          'display_name' => 'Farmer',             'description' => 'Can list products and manage orders.'],
            ['name' => 'CUSTOMER',        'display_name' => 'Customer',           'description' => 'Can browse and purchase produce.'],
            ['name' => 'CONTENT_MANAGER', 'display_name' => 'Content Manager',    'description' => 'Manages CMS articles and pages.'],
            ['name' => 'SUPPORT_AGENT',   'display_name' => 'Support Agent',      'description' => 'Handles enquiries and user support.'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }

        // Core permissions
        $permissions = [
            // Products
            ['name' => 'products.view',     'display_name' => 'View Products',     'module' => 'Products'],
            ['name' => 'products.create',   'display_name' => 'Create Products',   'module' => 'Products'],
            ['name' => 'products.update',   'display_name' => 'Update Products',   'module' => 'Products'],
            ['name' => 'products.delete',   'display_name' => 'Delete Products',   'module' => 'Products'],
            ['name' => 'products.feature',  'display_name' => 'Feature Products',  'module' => 'Products'],
            // Orders
            ['name' => 'orders.view',       'display_name' => 'View Orders',       'module' => 'Orders'],
            ['name' => 'orders.manage',     'display_name' => 'Manage Orders',     'module' => 'Orders'],
            // Farmers
            ['name' => 'farmers.verify',    'display_name' => 'Verify Farmers',    'module' => 'Farmers'],
            ['name' => 'farmers.suspend',   'display_name' => 'Suspend Farmers',   'module' => 'Farmers'],
            // Users
            ['name' => 'users.manage',      'display_name' => 'Manage Users',      'module' => 'Users'],
            // CMS
            ['name' => 'cms.manage',        'display_name' => 'Manage CMS',        'module' => 'CMS'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], $perm);
        }
    }
}
