<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(3, 1, 1000),
            'currency_id' => Currency::factory(),
            'tax_cost' => $this->faker->randomFloat(3, 1, 100),
            'manufacturing_cost' => $this->faker->randomFloat(3, 5, 500),
        ];
    }
}
