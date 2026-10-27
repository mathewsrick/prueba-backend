<?php

use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create());
});

test('lists products paginated with their currency', function () {
    Product::factory(3)->create();

    $this->getJson('/api/products')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'name', 'price', 'currency' => ['id', 'name', 'symbol']]],
            'links',
            'meta',
        ]);
});

test('creates a product', function () {
    $currency = Currency::factory()->create();

    $this->postJson('/api/products', [
        'name' => 'Laptop',
        'description' => 'Gaming laptop',
        'price' => 1500,
        'tax_cost' => 150,
        'manufacturing_cost' => 900,
        'currency_id' => $currency->id,
    ])->assertCreated()
        ->assertJsonPath('data.name', 'Laptop')
        ->assertJsonPath('data.currency.id', $currency->id);

    $this->assertDatabaseHas('products', ['name' => 'Laptop']);
});

test('rejects an invalid product', function () {
    $this->postJson('/api/products', [
        'name' => '',
        'price' => -1,
        'currency_id' => 999,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'price', 'tax_cost', 'manufacturing_cost', 'currency_id']);
});

test('shows a product with its prices', function () {
    $product = Product::factory()->create();

    $this->getJson("/api/products/{$product->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $product->id)
        ->assertJsonStructure(['data' => ['currency', 'prices']]);
});

test('returns a clean 404 for a missing product', function () {
    $this->getJson('/api/products/99999')
        ->assertNotFound()
        ->assertExactJson(['message' => 'Resource not found.']);
});

test('partially updates a product', function () {
    $product = Product::factory()->create(['name' => 'Old name']);

    $this->patchJson("/api/products/{$product->id}", ['price' => 99])
        ->assertOk()
        ->assertJsonPath('data.name', 'Old name');

    expect((float) $product->fresh()->price)->toBe(99.0);
});

test('deletes a product', function () {
    $product = Product::factory()->create();

    $this->deleteJson("/api/products/{$product->id}")->assertNoContent();

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});
