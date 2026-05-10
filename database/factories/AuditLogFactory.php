<?php

namespace Database\Factories;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'auditable_type' => 'App\\Models\\Order',
            'auditable_id' => fake()->numberBetween(1, 1000),
            'user_id' => null,
            'event' => fake()->randomElement(['created', 'updated', 'deleted']),
            'old_values' => null,
            'new_values' => null,
        ];
    }
}
