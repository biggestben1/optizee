<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HotelInventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::whereHas('category', function($q) {
            $q->where('department', 'hotel');
        })->with('category');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->lowStock();
            } elseif ($request->stock_status === 'out') {
                $query->where('stock_quantity', 0);
            }
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $products = $query->latest()->paginate(15);
        $categories = Category::active()->where('department', 'hotel')->get();

        return view('admin.hotel-inventory.index', compact('products', 'categories'));
    }

    public function report(Request $request)
    {
        $query = Product::whereHas('category', function($q) {
            $q->where('department', 'hotel');
        })->with('category');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->lowStock();
            } elseif ($request->stock_status === 'out') {
                $query->where('stock_quantity', 0);
            }
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $products = $query->get();
        $categories = Category::active()->where('department', 'hotel')->get();

        $totalCostValue = $products->sum(function($product) {
            return $product->stock_quantity * $product->cost_price;
        });

        $totalSalesValue = $products->sum(function($product) {
            return $product->stock_quantity * $product->selling_price;
        });

        $projectedProfit = $totalSalesValue - $totalCostValue;

        return view('admin.hotel-inventory.report', compact('products', 'categories', 'totalCostValue', 'totalSalesValue', 'projectedProfit'));
    }
}
