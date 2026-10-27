<?php

use App\Models\Currency;
use App\Models\Product;
use App\Services\PriceConverter;

function productPricedIn(float $price, float $exchangeRate): Product
{
    $product = new Product(['price' => $price]);
    $product->setRelation('currency', new Currency(['exchange_rate' => $exchangeRate]));

    return $product;
}

test('converts a base currency price using the target exchange rate', function () {
    $product = productPricedIn(100, 1);
    $target = new Currency(['exchange_rate' => 3800]);

    expect((new PriceConverter)->convert($product, $target))->toBe(380000.0);
});

test('converts through the base currency when the product is not in the base currency', function () {
    $product = productPricedIn(100, 0.85);
    $target = new Currency(['exchange_rate' => 3800]);

    expect((new PriceConverter)->convert($product, $target))->toBe(447058.82);
});

test('returns the same price when converting to the product currency', function () {
    $product = productPricedIn(42.5, 0.85);
    $target = new Currency(['exchange_rate' => 0.85]);

    expect((new PriceConverter)->convert($product, $target))->toBe(42.5);
});
