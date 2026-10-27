<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Starlink Min Kit', 'price' => '$280', 'description' => 'Minimum kit for Starlink connectivity', 'image' => 'https://via.placeholder.com/300?text=Starlink+Min+Kit'],
            ['name' => 'Starlink Standard Kit', 'price' => '$390', 'description' => 'Standard kit for Starlink connectivity', 'image' => 'https://via.placeholder.com/300?text=Starlink+Standard+Kit'],
            ['name' => 'Cudy AC1200 Outdoor Gigabit', 'price' => '$150', 'description' => 'Outdoor gigabit WiFi router with AC1200 speed', 'image' => 'https://via.placeholder.com/300?text=Cudy+AC1200+Outdoor'],
            ['name' => 'Cudy AC1200 Indoor Router', 'price' => '$50', 'description' => 'Indoor AC1200 WiFi router', 'image' => 'https://via.placeholder.com/300?text=Cudy+AC1200+Indoor'],
            ['name' => 'Tenda AC1200', 'price' => '$45', 'description' => 'Tenda AC1200 WiFi router', 'image' => 'https://via.placeholder.com/300?text=Tenda+AC1200'],
            ['name' => 'Tiplink Router', 'price' => '$45', 'description' => 'Tiplink wireless router', 'image' => 'https://via.placeholder.com/300?text=Tiplink+Router'],
            ['name' => 'AX3000 WiFi Router', 'price' => '$180', 'description' => 'High-speed AX3000 WiFi 6 router', 'image' => 'https://via.placeholder.com/300?text=AX3000'],
            ['name' => 'Rujie 6262', 'price' => '$250', 'description' => 'Rujie enterprise network equipment', 'image' => 'https://via.placeholder.com/300?text=Rujie+6262'],
            ['name' => 'Rujie RAP52', 'price' => '$150', 'description' => 'Rujie access point RAP52', 'image' => 'https://via.placeholder.com/300?text=Rujie+RAP52'],
            ['name' => 'AI Solar WiFi Camera Quad Lens', 'price' => '$100', 'description' => 'Solar powered WiFi camera with quad lens security', 'image' => 'https://via.placeholder.com/300?text=AI+Solar+Quad+Lens'],
            ['name' => 'AI Solar 4G Camera Triple Lens', 'price' => '$100', 'description' => '4G solar powered camera with triple lens', 'image' => 'https://via.placeholder.com/300?text=AI+Solar+4G+Triple'],
            ['name' => 'AI 4G Solar Camera', 'price' => '$95', 'description' => '4G solar powered security camera', 'image' => 'https://via.placeholder.com/300?text=AI+4G+Solar+Camera'],
            ['name' => 'Secret Camera', 'price' => '$40', 'description' => 'Compact hidden security camera', 'image' => 'https://via.placeholder.com/300?text=Secret+Camera'],
            ['name' => 'Starlink Car Magnetic Mounting', 'price' => '$75', 'description' => 'Magnetic mount for Starlink equipment in vehicles', 'image' => 'https://via.placeholder.com/300?text=Starlink+Car+Mount'],
            ['name' => 'Outdoor Ethernet Cable', 'price' => '$1.00/meter', 'description' => 'High-quality outdoor ethernet cable per meter', 'image' => 'https://via.placeholder.com/300?text=Ethernet+Cable'],
        ];

        foreach ($products as $product) {
            $product['slug'] = Str::slug($product['name']);
            Product::updateOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}
