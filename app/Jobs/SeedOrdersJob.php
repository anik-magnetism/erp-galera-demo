<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class SeedOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $total;
    public int $chunk;

    public function __construct(int $total = 500000, int $chunk = 1000)
    {
        $this->total = $total;
        $this->chunk = $chunk;
    }

    public function handle()
    {
        $faker = Faker::create();
        $companyIds = DB::table('companies')->pluck('id')->toArray();
        $customerIds = DB::table('customers')->pluck('id')->toArray();
        $productIds = DB::table('products')->pluck('id')->toArray();
        $warehouseIds = DB::table('warehouses')->pluck('id')->toArray();

        $batches = (int) ceil($this->total / $this->chunk);

        for ($b = 0; $b < $batches; $b++) {
            $orders = [];
            $orderItems = [];

            for ($i = 0; $i < $this->chunk && ($b * $this->chunk + $i) < $this->total; $i++) {
                $companyId = $companyIds[array_rand($companyIds)];
                $customerId = $customerIds[array_rand($customerIds)];
                $warehouseId = $warehouseIds[array_rand($warehouseIds)];

                $orders[] = [
                    'company_id' => $companyId,
                    'customer_id' => $customerId,
                    'warehouse_id' => $warehouseId,
                    'total_amount' => 0, // will update after items
                    'status' => 'completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($orders)) {
                // insert orders and get their ids
                DB::table('orders')->insert($orders);
                // get last inserted ids for batch
                $lastIds = DB::table('orders')->orderByDesc('id')->limit(count($orders))->pluck('id')->toArray();

                foreach ($lastIds as $orderId) {
                    $itemsCount = $faker->numberBetween(1, 5);
                    $total = 0;
                    for ($k = 0; $k < $itemsCount; $k++) {
                        $productId = $productIds[array_rand($productIds)];
                        $unitPrice = $faker->randomFloat(2, 1, 500);
                        $qty = $faker->numberBetween(1, 10);
                        $orderItems[] = [
                            'order_id' => $orderId,
                            'product_id' => $productId,
                            'quantity' => $qty,
                            'unit_price' => $unitPrice,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $total += $unitPrice * $qty;
                    }
                    // update order total
                    DB::table('orders')->where('id', $orderId)->update(['total_amount' => $total]);
                }

                if (!empty($orderItems)) {
                    // insert in chunks to avoid huge single insert
                    foreach (array_chunk($orderItems, 1000) as $chunkItems) {
                        DB::table('order_items')->insert($chunkItems);
                    }
                }
            }
        }
    }
}
