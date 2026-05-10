<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class SeedCustomersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $total;
    public int $chunk;

    public function __construct(int $total = 50000, int $chunk = 1000)
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
                    'first_name' => $faker->firstName(),
                    'last_name' => $faker->lastName(),
                    'email' => $faker->unique()->safeEmail(),
                    'phone' => $faker->phoneNumber(),
                    'address' => $faker->address(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($rows)) {
                DB::table('customers')->insert($rows);
            }
        }
    }
}
