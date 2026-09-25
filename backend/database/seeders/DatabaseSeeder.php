<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\County;
use App\Models\ProductCategory;
use App\Models\Banner;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CountySeeder::class,
            ProductCategorySeeder::class,
            CmsSeeder::class,
        ]);
    }
}
