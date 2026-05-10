<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Large dataset seeder (dispatches queued jobs for heavy imports)
        if (class_exists(\Database\Seeders\LargeDatasetSeeder::class)) {
            $this->call(\Database\Seeders\LargeDatasetSeeder::class);
        }
    }
}
