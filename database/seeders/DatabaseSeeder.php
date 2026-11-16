<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed demo products for local development.
     * Admin accounts are created with `php artisan admin:create`.
     */
    public function run(): void
    {
        $demoProducts = [
            ['প্রিমিয়াম জিপার হুডি', 'Premium Zipper Hoodie', 850],
            ['হাই নেক সোয়েটার', 'High Neck Sweater', 1200],
            ['ক্লাসিক কটন টি-শার্ট', 'Classic Cotton T-Shirt', 450],
            ['রিল্যাক্সড ফিট জগার্স', 'Relaxed Fit Joggers', 950],
            ['উলের শীতের টুপি', 'Knitted Winter Beanie', 350],
            ['ডেনিম ট্রাকার জ্যাকেট', 'Denim Trucker Jacket', 2200],
            ['ক্যানভাস টোট ব্যাগ', 'Everyday Canvas Tote', 550],
            ['ফ্লিস লাইনড পুলওভার', 'Fleece Lined Pullover', 1450],
        ];

        foreach ($demoProducts as [$nameBn, $nameEn, $price]) {
            Product::factory()->create(['name_bn' => $nameBn, 'name_en' => $nameEn, 'price' => $price]);
        }
    }
}
