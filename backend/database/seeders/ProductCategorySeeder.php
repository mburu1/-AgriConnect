<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'       => 'Vegetables',
                'slug'       => 'vegetables',
                'description'=> 'Fresh farm vegetables',
                'is_featured'=> true,
                'children'   => ['Tomatoes', 'Kales (Sukuma Wiki)', 'Spinach', 'Cabbages', 'Onions', 'Carrots', 'Capsicum'],
            ],
            [
                'name'       => 'Fruits',
                'slug'       => 'fruits',
                'description'=> 'Fresh tropical and exotic fruits',
                'is_featured'=> true,
                'children'   => ['Mangoes', 'Avocados', 'Bananas', 'Pineapples', 'Passion Fruits', 'Watermelons'],
            ],
            [
                'name'       => 'Cereals & Grains',
                'slug'       => 'cereals-grains',
                'description'=> 'Staple grains and cereals',
                'is_featured'=> true,
                'children'   => ['Maize', 'Wheat', 'Rice', 'Millet', 'Sorghum', 'Barley'],
            ],
            [
                'name'       => 'Legumes & Pulses',
                'slug'       => 'legumes-pulses',
                'description'=> 'Beans, lentils, and pulses',
                'children'   => ['Beans', 'Lentils', 'Green Grams', 'Pigeon Peas', 'Soybeans'],
            ],
            [
                'name'       => 'Tubers & Roots',
                'slug'       => 'tubers-roots',
                'description'=> 'Potatoes, cassava, and root vegetables',
                'children'   => ['Irish Potatoes', 'Sweet Potatoes', 'Cassava', 'Arrow Roots (Nduma)', 'Yams'],
            ],
            [
                'name'       => 'Dairy & Eggs',
                'slug'       => 'dairy-eggs',
                'description'=> 'Farm-fresh dairy and eggs',
                'is_featured'=> true,
                'children'   => ['Fresh Milk', 'Yoghurt', 'Eggs'],
            ],
            [
                'name'       => 'Poultry & Meat',
                'slug'       => 'poultry-meat',
                'description'=> 'Chicken, beef, and other farm meats',
                'children'   => ['Chicken', 'Guinea Fowl', 'Rabbit', 'Goat Meat'],
            ],
            [
                'name'       => 'Herbs & Spices',
                'slug'       => 'herbs-spices',
                'description'=> 'Fresh herbs and dried spices',
                'children'   => ['Dhania (Coriander)', 'Rosemary', 'Thyme', 'Garlic', 'Ginger'],
            ],
        ];

        foreach ($categories as $cat) {
            $parent = ProductCategory::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name'        => $cat['name'],
                    'description' => $cat['description'] ?? null,
                    'is_featured' => $cat['is_featured'] ?? false,
                    'is_active'   => true,
                ]
            );

            foreach ($cat['children'] ?? [] as $child) {
                $childSlug = \Illuminate\Support\Str::slug($child) . '-' . $parent->id;
                ProductCategory::firstOrCreate(
                    ['slug' => $childSlug],
                    [
                        'parent_id'  => $parent->id,
                        'name'       => $child,
                        'is_active'  => true,
                    ]
                );
            }
        }
    }
}
