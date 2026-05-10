<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'invoice_id' => null,
            'amount' => fake()->randomFloat(2, 1, 5000),
            'method' => fake()->randomElement(['card', 'bank', 'cash']),
        ];
    }
}
