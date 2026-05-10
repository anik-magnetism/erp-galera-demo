<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class SeedInvoicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $chunk;

    public function __construct(int $chunk = 1000)
    {
        $this->chunk = $chunk;
    }

    public function handle()
    {
        $faker = Faker::create();

        DB::table('orders')->orderBy('id')->chunk($this->chunk, function ($orders) use ($faker) {
            $invoices = [];
            $payments = [];

            foreach ($orders as $order) {
                $invoices[] = [
                    'order_id' => $order->id,
                    'amount' => $order->total_amount,
                    'status' => 'unpaid',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($invoices)) {
                DB::table('invoices')->insert($invoices);
                $inserted = DB::table('invoices')->orderByDesc('id')->limit(count($invoices))->get();

                foreach ($inserted as $inv) {
                    // randomly create a payment for some invoices
                    if ($faker->boolean(30)) {
                        $payments[] = [
                            'invoice_id' => $inv->id,
                            'amount' => $inv->amount,
                            'method' => $faker->randomElement(['card','bank','cash']),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                if (!empty($payments)) {
                    DB::table('payments')->insert($payments);
                }
            }
        });
    }
}
