<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Product;

class PriceConverter
{
    public function convert(Product $product, Currency $target): float
    {
        $product->loadMissing('currency');
        $priceInBase = $product->price / $product->currency->exchange_rate;

        return round($priceInBase * $target->exchange_rate, 2);
    }
}
