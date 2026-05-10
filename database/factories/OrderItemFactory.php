<?php

namespace Database\Factories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => null,
            'product_id' => null,
            'quantity' => fake()->numberBetween(1, 10),
            'unit_price' => fake()->randomFloat(2, 1, 1000),
        ];
    }
}
