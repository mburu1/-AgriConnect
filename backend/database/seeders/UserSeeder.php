<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Farmer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::firstOrCreate(
            ['email' => 'admin@agriconnect.co.ke'],
            [
                'name'     => 'AgriConnect Admin',
                'password' => Hash::make('Admin@1234'),
                'role'     => 'ADMIN',
                'status'   => 'ACTIVE',
            ]
        );

        // Demo Farmer
        $farmerUser = User::firstOrCreate(
            ['email' => 'demo.farmer@agriconnect.co.ke'],
            [
                'name'         => 'John Kamau',
                'phone_number' => '0712345678',
                'password'     => Hash::make('Farmer@1234'),
                'role'         => 'FARMER',
                'status'       => 'ACTIVE',
            ]
        );

        Farmer::firstOrCreate(
            ['user_id' => $farmerUser->id],
            [
                'farm_name'               => 'Kamau Fresh Farms',
                'slug'                    => 'kamau-fresh-farms',
                'bio'                     => 'Third-generation farmer in Kiambu growing fresh vegetables and tomatoes.',
                'verification_status'     => 'VERIFIED',
                'primary_specialization'  => 'Horticulture',
                'total_farm_size_acres'   => 5.5,
                'rating_average'          => 4.80,
                'is_featured'             => true,
                'is_active'               => true,
            ]
        );

        // Demo Customer
        User::firstOrCreate(
            ['email' => 'customer@agriconnect.co.ke'],
            [
                'name'         => 'Mary Njeri',
                'phone_number' => '0723456789',
                'password'     => Hash::make('Customer@1234'),
                'role'         => 'CUSTOMER',
                'status'       => 'ACTIVE',
            ]
        );
    }
}
