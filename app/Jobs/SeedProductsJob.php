<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class SeedProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $total;
    public int $chunk;

    public function __construct(int $total = 10000, int $chunk = 1000)
    {
        $this->total = $total;
        $this->chunk = $chunk;
    }

    public function handle()
    {
        $faker = Faker::create();
        $companyIds = DB::table('companies')->pluck('id')->toArray();
        $batches = (int) ceil($this->total / $this->chunk);

        for ($b = 0; $b < $batches; $b++) {
            $rows = [];
            for ($i = 0; $i < $this->chunk && ($b * $this->chunk + $i) < $this->total; $i++) {
                $rows[] = [
                    'company_id' => $companyIds[array_rand($companyIds)],
                    'sku' => strtoupper($faker->bothify('???-#####')),
                    'name' => $faker->word() . ' ' . $faker->randomElement(['Pro','Plus','Max']),
                    'description' => $faker->sentence(8),
                    'price' => $faker->randomFloat(2, 1, 2000),
                    'stock' => $faker->numberBetween(0, 1000),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($rows)) {
                DB::table('products')->insert($rows);
            }
        }
    }
}
