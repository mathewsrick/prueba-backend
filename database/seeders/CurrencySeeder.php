<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            ['name' => 'US Dollar', 'symbol' => '$', 'exchange_rate' => 1.00],
            ['name' => 'Euro', 'symbol' => '€', 'exchange_rate' => 0.85],
            ['name' => 'Colombian Peso', 'symbol' => 'COP', 'exchange_rate' => 3800.00],
            ['name' => 'Japanese Yen', 'symbol' => '¥', 'exchange_rate' => 110.00],
            ['name' => 'Canadian Dollar', 'symbol' => 'C$', 'exchange_rate' => 1.25],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(['name' => $currency['name']], $currency);
        }
    }
}
