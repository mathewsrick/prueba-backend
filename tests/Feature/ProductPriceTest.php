<?php

use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create());

    $this->euro = Currency::factory()->create(['exchange_rate' => 0.85]);
    $this->peso = Currency::factory()->create(['exchange_rate' => 3800]);
    $this->product = Product::factory()->for($this->euro)->create(['price' => 100]);
});

test('lists the prices of a product', function () {
    ProductPrice::factory()->for($this->product)->for($this->peso)->create();

    $this->getJson("/api/products/{$this->product->id}/prices")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.currency.id', $this->peso->id);
});

test('stores a price sent by the client', function () {
    $this->postJson("/api/products/{$this->product->id}/prices", [
        'currency_id' => $this->peso->id,
        'price' => 500000,
    ])->assertCreated();

    expect((float) $this->product->prices()->first()->price)->toBe(500000.0);
});

test('calculates the price from exchange rates when it is not sent', function () {
    $this->postJson("/api/products/{$this->product->id}/prices", [
        'currency_id' => $this->peso->id,
    ])->assertCreated();

    expect((float) $this->product->prices()->first()->price)->toBe(447058.82);
});

test('rejects a second price in the same currency', function () {
    ProductPrice::factory()->for($this->product)->for($this->peso)->create();

    $this->postJson("/api/products/{$this->product->id}/prices", [
        'currency_id' => $this->peso->id,
        'price' => 10,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['currency_id' => 'already has a price']);
});

test('rejects an unknown currency', function () {
    $this->postJson("/api/products/{$this->product->id}/prices", [
        'currency_id' => 999,
        'price' => 10,
    ])->assertUnprocessable()->assertJsonValidationErrors('currency_id');
});

test('returns 404 for prices of a missing product', function () {
    $this->getJson('/api/products/99999/prices')->assertNotFound();
});
