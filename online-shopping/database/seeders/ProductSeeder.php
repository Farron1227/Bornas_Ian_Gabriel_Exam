<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@purplebug.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create test customer
        User::create([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan.delacruz@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        // Create categories
        $electronics = Category::create([
            'name' => 'Electronics',
            'description' => 'Electronic devices and gadgets',
        ]);

        $clothing = Category::create([
            'name' => 'Clothing',
            'description' => 'Fashion and apparel',
        ]);

        // Create products
        $products = [
            [
                'name' => 'Wireless Headphones',
                'description' => 'High-quality wireless headphones with noise cancellation',
                'price' => 1999.00,
                'stock' => 50,
                'category_id' => $electronics->id,
            ],
            [
                'name' => 'Smart Watch',
                'description' => 'Feature-rich smartwatch with fitness tracking',
                'price' => 4500.00,
                'stock' => 30,
                'category_id' => $electronics->id,
            ],
            [
                'name' => 'Bluetooth Speaker',
                'description' => 'Portable Bluetooth speaker with amazing sound',
                'price' => 1500.00,
                'stock' => 40,
                'category_id' => $electronics->id,
            ],
            [
                'name' => 'Cotton T-Shirt',
                'description' => 'Comfortable 100% cotton t-shirt',
                'price' => 350.00,
                'stock' => 100,
                'category_id' => $clothing->id,
            ],
            [
                'name' => 'Denim Jeans',
                'description' => 'Classic denim jeans for everyday wear',
                'price' => 1200.00,
                'stock' => 60,
                'category_id' => $clothing->id,
            ],
            [
                'name' => 'Summer Dress',
                'description' => 'Light and breezy summer dress',
                'price' => 899.00,
                'stock' => 45,
                'category_id' => $clothing->id,
            ],
            [
                'name' => 'USB-C Cable',
                'description' => 'Fast charging USB-C cable',
                'price' => 250.00,
                'stock' => 200,
                'category_id' => $electronics->id,
            ],
            [
                'name' => 'Phone Case',
                'description' => 'Protective phone case with elegant design',
                'price' => 399.00,
                'stock' => 150,
                'category_id' => $electronics->id,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
