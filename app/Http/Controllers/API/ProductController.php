<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->get('q', '');
        $perPage = min(100, (int) $request->get('per_page', 25));
        $query = Product::query();
        if (!empty($q)) {
            $query->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%");
        }
        return $query->orderBy('name')->paginate($perPage);
    }
}
