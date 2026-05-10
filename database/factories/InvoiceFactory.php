<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'order_id' => null,
            'amount' => fake()->randomFloat(2, 5, 5000),
            'status' => fake()->randomElement(['unpaid', 'paid', 'partially_paid']),
        ];
    }
}
