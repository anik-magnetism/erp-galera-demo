<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Jobs\SeedCustomersJob;
use App\Jobs\SeedProductsJob;
use App\Jobs\SeedOrdersJob;
use App\Jobs\SeedInvoicesJob;
use App\Jobs\SeedAuditLogsJob;
use App\Models\Company;
use App\Models\Warehouse;

class LargeDatasetSeeder extends Seeder
{
    public function run()
    {
        // small tables inserted synchronously
        if (DB::table('companies')->count() < 100) {
            $rows = [];
            for ($i = 0; $i < 100; $i++) {
                $rows[] = [
                    'name' => fake()->company(),
                    'email' => fake()->companyEmail(),
                    'phone' => fake()->phoneNumber(),
                    'address' => fake()->address(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('companies')->insert($rows);
        }

        // warehouses
        if (DB::table('warehouses')->count() < 20) {
            $companyIds = DB::table('companies')->pluck('id')->toArray();
            $rows = [];
            foreach ($companyIds as $companyId) {
                $rows[] = [
                    'company_id' => $companyId,
                    'name' => 'Main Warehouse',
                    'location' => 'HQ',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('warehouses')->insert($rows);
        }

        // Dispatch large jobs to queue
        dispatch(new SeedCustomersJob(50000, 1000));
        dispatch(new SeedProductsJob(10000, 1000));
        dispatch(new SeedOrdersJob(500000, 1000));
        dispatch(new SeedInvoicesJob(1000));
        dispatch(new SeedAuditLogsJob(1000000, 1000));
    }
}
