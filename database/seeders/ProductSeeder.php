<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['sku' => 'SKU-WM-1001', 'name' => 'Wireless Mouse', 'description' => 'Basic 2.4GHz wireless mouse.', 'price' => 15.99, 'stock' => 50],
            ['sku' => 'SKU-KB-1002', 'name' => 'Mechanical Keyboard', 'description' => 'Full-size keyboard with blue switches.', 'price' => 45.50, 'stock' => 30],
            ['sku' => 'SKU-HUB-1003', 'name' => 'USB-C Hub', 'description' => '6-in-1 hub with HDMI and card reader.', 'price' => 22.00, 'stock' => 40],
            ['sku' => 'SKU-LS-1004', 'name' => 'Laptop Stand', 'description' => 'Adjustable aluminium laptop stand.', 'price' => 35.00, 'stock' => 25],
            ['sku' => 'SKU-MON-1005', 'name' => '27" Monitor', 'description' => 'Full HD monitor with HDMI and VGA.', 'price' => 199.99, 'stock' => 15],
            ['sku' => 'SKU-CAM-1006', 'name' => 'HD Webcam', 'description' => '1080p webcam with built-in microphone.', 'price' => 29.99, 'stock' => 20],
            ['sku' => 'SKU-CH-1007', 'name' => 'Office Chair', 'description' => 'Ergonomic chair with lumbar support.', 'price' => 120.00, 'stock' => 10],
            ['sku' => 'SKU-LMP-1008', 'name' => 'Desk Lamp', 'description' => 'LED desk lamp with adjustable brightness.', 'price' => 18.50, 'stock' => 60],
        ];

        foreach ($products as $product) {
            Product::create($product + ['status' => 'active']);
        }
    }
}
