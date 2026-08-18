<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

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
        $categories = Category::active()->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($validated);

        // Create initial stock movement if stock > 0
        if ($product->stock_quantity > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'in',
                'quantity' => $product->stock_quantity,
                'stock_before' => 0,
                'stock_after' => $product->stock_quantity,
                'unit_cost' => $product->cost_price,
                'reason' => 'Initial stock',
            ]);
        }

        AuditLog::log('product_created', "Created product: {$product->name}", $product);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load('category', 'stockMovements.user');
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::active()->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $oldValues = $product->toArray();
        $product->update($validated);

        AuditLog::log('product_updated', "Updated product: {$product->name}", $product, $oldValues, $product->toArray());

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function adjustStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out,adjustment,damaged,expired',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
        ]);

        $stockBefore = $product->stock_quantity;

        if (in_array($validated['type'], ['out', 'damaged', 'expired'])) {
            if ($validated['quantity'] > $product->stock_quantity) {
                return back()->with('error', 'Insufficient stock.');
            }
            $product->decrement('stock_quantity', $validated['quantity']);
        } else {
            $product->increment('stock_quantity', $validated['quantity']);
        }

        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'stock_before' => $stockBefore,
            'stock_after' => $product->stock_quantity,
            'unit_cost' => $product->cost_price,
            'reason' => $validated['reason'],
        ]);

        AuditLog::log('stock_adjusted', "Stock adjusted for {$product->name}: {$validated['type']} {$validated['quantity']}", $product);

        return back()->with('success', 'Stock adjusted successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->saleItems()->count() > 0) {
            return back()->with('error', 'Cannot delete product with sales history.');
        }

        AuditLog::log('product_deleted', "Deleted product: {$product->name}", $product);
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}











