<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'company_id' => null,
            'customer_id' => null,
            'warehouse_id' => null,
            'total_amount' => fake()->randomFloat(2, 10, 5000),
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'cancelled']),
        ];
    }
}
