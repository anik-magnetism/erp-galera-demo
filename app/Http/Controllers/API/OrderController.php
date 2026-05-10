<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(100, (int) $request->get('per_page', 25));
        return Order::with('items')->orderByDesc('created_at')->paginate($perPage);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'company_id' => 'required|exists:companies,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = DB::transaction(function () use ($data) {
            $orderId = DB::table('orders')->insertGetId([
                'company_id' => $data['company_id'],
                'customer_id' => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'total_amount' => 0,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $items = [];
            $total = 0;
            foreach ($data['items'] as $it) {
                $product = DB::table('products')->where('id', $it['product_id'])->first();
                $price = $product ? $product->price : 0;
                $qty = (int) $it['quantity'];
                $items[] = [
                    'order_id' => $orderId,
                    'product_id' => $it['product_id'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $total += $price * $qty;
            }

            if (!empty($items)) {
                DB::table('order_items')->insert($items);
            }

            DB::table('orders')->where('id', $orderId)->update(['total_amount' => $total, 'status' => 'processing']);

            return DB::table('orders')->where('id', $orderId)->first();
        });

        return response()->json($order, 201);
    }
}
