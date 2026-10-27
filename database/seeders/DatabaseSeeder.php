<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            CurrencySeeder::class,
        ]);

        Product::factory(10)->recycle(Currency::all())->create();
    }
}
