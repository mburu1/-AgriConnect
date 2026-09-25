<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Page;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        // Home banners
        $banners = [
            [
                'title'      => 'Farm Fresh to Your Doorstep',
                'subtitle'   => 'Connect directly with verified Kenyan farmers for the freshest produce',
                'image_url'  => '/images/banners/hero-1.jpg',
                'link_url'   => '/shop',
                'cta_text'   => 'Shop Now',
                'position'   => 'HOME_HERO',
                'sort_order' => 1,
                'is_active'  => true,
            ],
            [
                'title'      => 'Support Local Farmers',
                'subtitle'   => 'Every purchase empowers a Kenyan farming family',
                'image_url'  => '/images/banners/hero-2.jpg',
                'link_url'   => '/farmers',
                'cta_text'   => 'Meet Our Farmers',
                'position'   => 'HOME_HERO',
                'sort_order' => 2,
                'is_active'  => true,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::firstOrCreate(['title' => $banner['title']], $banner);
        }

        // Static Pages
        $pages = [
            [
                'title'            => 'About AgriConnect',
                'slug'             => 'about-us',
                'content'          => '<h1>About AgriConnect</h1><p>AgriConnect is Kenya\'s premier platform connecting farmers directly with consumers...</p>',
                'meta_title'       => 'About Us - AgriConnect',
                'meta_description' => 'Learn about AgriConnect\'s mission to empower Kenyan farmers and connect them with consumers.',
                'is_published'     => true,
            ],
            [
                'title'            => 'Privacy Policy',
                'slug'             => 'privacy-policy',
                'content'          => '<h1>Privacy Policy</h1><p>At AgriConnect, we are committed to protecting your personal data...</p>',
                'meta_title'       => 'Privacy Policy - AgriConnect',
                'meta_description' => 'AgriConnect privacy policy and data protection practices.',
                'is_published'     => true,
            ],
            [
                'title'            => 'Terms and Conditions',
                'slug'             => 'terms-and-conditions',
                'content'          => '<h1>Terms and Conditions</h1><p>By using AgriConnect, you agree to these terms...</p>',
                'meta_title'       => 'Terms & Conditions - AgriConnect',
                'meta_description' => 'AgriConnect terms of use and service agreement.',
                'is_published'     => true,
            ],
            [
                'title'            => 'Farmer\'s Guide',
                'slug'             => 'farmer-guide',
                'content'          => '<h1>Farmer\'s Guide</h1><p>Welcome to AgriConnect! Here\'s how to get started selling your produce...</p>',
                'meta_title'       => 'Farmer\'s Guide - AgriConnect',
                'meta_description' => 'A complete guide for farmers joining the AgriConnect platform.',
                'is_published'     => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(['slug' => $page['slug']], $page);
        }
    }
}
