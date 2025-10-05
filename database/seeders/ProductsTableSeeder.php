<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now()->toDateTimeString();

        $rows = [
            ['name' => 'Basic Laptop A', 'price' => 450.00, 'category' => 'electronics'],
            ['name' => 'Gaming Laptop Pro', 'price' => 1500.00, 'category' => 'electronics'],
            ['name' => 'Wireless Mouse', 'price' => 25.00, 'category' => 'electronics'],
            ['name' => 'Office Chair', 'price' => 120.00, 'category' => 'furniture'],
            ['name' => 'Coffee Table', 'price' => 80.00, 'category' => 'furniture'],
            ['name' => 'Running Shoes', 'price' => 60.00, 'category' => 'fashion'],
            ['name' => 'Designer Jacket', 'price' => 250.00, 'category' => 'fashion'],
            ['name' => 'Electric Kettle', 'price' => 35.00, 'category' => 'home_appliances'],
            ['name' => 'Smartphone X', 'price' => 700.00, 'category' => 'electronics'],
            ['name' => 'Budget Tablet', 'price' => 199.99, 'category' => 'electronics'],
        ];

        foreach ($rows as $r) {
            DB::table('products')->insert(array_merge($r, ['created_at' => $now, 'updated_at' => $now]));
        }
    }
}
