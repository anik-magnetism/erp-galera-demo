<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'company_id' => null,
            'sku' => strtoupper(fake()->bothify('???-#####')),
            'name' => fake()->word() . ' ' . fake()->randomElement(['Pro', 'Plus', 'Max', 'Mini']),
            'description' => fake()->sentence(10),
            'price' => fake()->randomFloat(2, 5, 1000),
            'stock' => fake()->numberBetween(0, 1000),
        ];
    }
}
