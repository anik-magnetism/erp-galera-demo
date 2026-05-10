<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $cacheKey = 'dashboard_stats_v1';
        $stats = Cache::remember($cacheKey, 60, function () {
            return [
                'total_orders' => DB::table('orders')->count(),
                'total_invoices' => DB::table('invoices')->count(),
                'total_revenue' => (float) DB::table('invoices')->sum('amount'),
                'stock_movement_count' => DB::table('stock_movements')->count(),
            ];
        });

        // add response timing metrics (simple example)
        $start = microtime(true);
        DB::table('orders')->select('id')->limit(1)->get();
        $readMs = round((microtime(true) - $start) * 1000, 2);
        $stats['sample_read_ms'] = $readMs;

        return response()->json($stats);
    }
}
