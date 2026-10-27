<?php

use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create());
});

test('lists currencies', function () {
    Currency::factory(2)->create();

    $this->getJson('/api/currencies')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('creates a currency', function () {
    $this->postJson('/api/currencies', [
        'name' => 'Mexican Peso',
        'symbol' => 'MX$',
        'exchange_rate' => 18.5,
    ])->assertCreated()->assertJsonPath('data.name', 'Mexican Peso');
});

test('rejects a duplicated currency name', function () {
    Currency::factory()->create(['name' => 'Euro']);

    $this->postJson('/api/currencies', [
        'name' => 'Euro',
        'symbol' => '€',
        'exchange_rate' => 0.9,
    ])->assertUnprocessable()->assertJsonValidationErrors('name');
});

test('rejects a zero exchange rate on create and update', function () {
    $currency = Currency::factory()->create();

    $this->postJson('/api/currencies', ['name' => 'Zero', 'symbol' => 'Z', 'exchange_rate' => 0])
        ->assertUnprocessable()->assertJsonValidationErrors('exchange_rate');

    $this->patchJson("/api/currencies/{$currency->id}", ['exchange_rate' => 0])
        ->assertUnprocessable()->assertJsonValidationErrors('exchange_rate');
});

test('allows updating a currency keeping its own name', function () {
    $currency = Currency::factory()->create(['name' => 'Euro']);

    $this->putJson("/api/currencies/{$currency->id}", ['name' => 'Euro', 'exchange_rate' => 0.95])
        ->assertOk();
});

test('rejects renaming a currency to an existing name', function () {
    Currency::factory()->create(['name' => 'Euro']);
    $currency = Currency::factory()->create();

    $this->patchJson("/api/currencies/{$currency->id}", ['name' => 'Euro'])
        ->assertUnprocessable()->assertJsonValidationErrors('name');
});

test('does not delete a currency in use', function () {
    $currency = Currency::factory()->create();
    Product::factory()->for($currency)->create();

    $this->deleteJson("/api/currencies/{$currency->id}")->assertConflict();

    $this->assertDatabaseHas('currencies', ['id' => $currency->id]);
});

test('deletes an unused currency', function () {
    $currency = Currency::factory()->create();

    $this->deleteJson("/api/currencies/{$currency->id}")->assertNoContent();

    $this->assertDatabaseMissing('currencies', ['id' => $currency->id]);
});
