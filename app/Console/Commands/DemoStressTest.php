<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Faker\Factory as Faker;

class DemoStressTest extends Command
{
    protected $signature = 'demo:stress-test {--count=10000} {--concurrency=50}';

    protected $description = 'Insert orders, run concurrent reads, and verify replication across Galera nodes.';

    public function handle()
    {
        $count = (int) $this->option('count');
        $chunk = 1000;
        $faker = Faker::create();

        $this->info("Inserting {$count} orders in chunks of {$chunk}...");
        $start = microtime(true);

        $companyIds = DB::table('companies')->pluck('id')->toArray();
        $customerIds = DB::table('customers')->pluck('id')->toArray();
        $warehouseIds = DB::table('warehouses')->pluck('id')->toArray();
        $productIds = DB::table('products')->pluck('id')->toArray();

        $batches = (int) ceil($count / $chunk);
        for ($b = 0; $b < $batches; $b++) {
            $orders = [];
            $items = [];
            for ($i = 0; $i < $chunk && ($b * $chunk + $i) < $count; $i++) {
                $companyId = $companyIds[array_rand($companyIds)];
                $customerId = $customerIds[array_rand($customerIds)];
                $warehouseId = $warehouseIds[array_rand($warehouseIds)];

                $orders[] = [
                    'company_id' => $companyId,
                    'customer_id' => $customerId,
                    'warehouse_id' => $warehouseId,
                    'total_amount' => 0,
                    'status' => 'completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('orders')->insert($orders);
            $lastIds = DB::table('orders')->orderByDesc('id')->limit(count($orders))->pluck('id')->toArray();

            foreach ($lastIds as $oid) {
                $itemsCount = $faker->numberBetween(1, 4);
                $total = 0;
                for ($k = 0; $k < $itemsCount; $k++) {
                    $pid = $productIds[array_rand($productIds)];
                    $price = $faker->randomFloat(2, 1, 500);
                    $qty = $faker->numberBetween(1, 5);
                    $items[] = [
                        'order_id' => $oid,
                        'product_id' => $pid,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $total += $price * $qty;
                }
                DB::table('orders')->where('id', $oid)->update(['total_amount' => $total]);
            }

            if (!empty($items)) {
                foreach (array_chunk($items, 1000) as $chunkItems) {
                    DB::table('order_items')->insert($chunkItems);
                }
            }
        }

        $elapsed = microtime(true) - $start;
        $this->info("Inserted {$count} orders in {$elapsed} seconds.");

        // Run concurrent reads against our API endpoint
        $concurrency = (int) $this->option('concurrency');
        $this->info("Running concurrent reads (concurrency={$concurrency}) against /api/orders?page=1...");

        $client = new Client(['base_uri' => config('app.url') ?: 'http://localhost:8000']);
        $requests = function ($total) use ($client) {
            for ($i = 0; $i < $total; $i++) {
                yield function() use ($client) {
                    return $client->getAsync('/api/orders?page=1');
                };
            }
        };

        $totalRequests = 200;
        $startReads = microtime(true);
        $pool = new Pool($client, $requests($totalRequests), [
            'concurrency' => $concurrency,
            'fulfilled' => function ($response, $index) {
                // noop
            },
            'rejected' => function ($reason, $index) {
                // noop
            },
        ]);
        $promise = $pool->promise();
        $promise->wait();
        $readElapsed = microtime(true) - $startReads;
        $this->info("Completed {$totalRequests} reads in {$readElapsed} seconds.");

        // Verify replication on galera2 and galera3
        $this->info('Verifying counts on galera2 and galera3...');

        $counts = [];
        foreach (['galera2' => 3306, 'galera3' => 3306] as $host => $port) {
            try {
                $conn = [
                    'driver' => 'mysql',
                    'host' => $host,
                    'port' => $port,
                    'database' => env('DB_DATABASE', 'erp_demo'),
                    'username' => env('DB_USERNAME', 'erp_user'),
                    'password' => env('DB_PASSWORD', 'erp_password'),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                ];

                config(['database.connections.temp_conn' => $conn]);
                $cnt = DB::connection('temp_conn')->table('orders')->count();
                $counts[$host] = $cnt;
            } catch (\Throwable $e) {
                $counts[$host] = 'error: ' . $e->getMessage();
            }
        }

        $this->info('Replication counts:');
        foreach ($counts as $host => $c) {
            $this->line(" - {$host}: {$c}");
        }

        $this->info('Stress test complete.');
        return 0;
    }
}
