<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class SeedAuditLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $total;
    public int $chunk;

    public function __construct(int $total = 1000000, int $chunk = 1000)
    {
        $this->total = $total;
        $this->chunk = $chunk;
    }

    public function handle()
    {
        $faker = Faker::create();
        $models = ['App\\Models\\Order', 'App\\Models\\Invoice', 'App\\Models\\Product'];

        $batches = (int) ceil($this->total / $this->chunk);
        for ($b = 0; $b < $batches; $b++) {
            $rows = [];
            for ($i = 0; $i < $this->chunk && ($b * $this->chunk + $i) < $this->total; $i++) {
                $rows[] = [
                    'auditable_type' => $faker->randomElement($models),
                    'auditable_id' => $faker->numberBetween(1, 500000),
                    'user_id' => null,
                    'event' => $faker->randomElement(['created','updated','deleted']),
                    'old_values' => null,
                    'new_values' => json_encode(['sample' => $faker->sentence()]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($rows)) {
                DB::table('audit_logs')->insert($rows);
            }
        }
    }
}
