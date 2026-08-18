<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierSupply;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        if ($request->boolean('owing')) {
            $query->withOutstandingBalance();
        }

        $suppliers = $query->latest()->paginate(15);
        $totalDebt = Supplier::sum('balance_owed');

        return view('admin.suppliers.index', compact('suppliers', 'totalDebt'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:suppliers',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'products_supplied' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        $supplier = Supplier::create($validated);

        AuditLog::log('supplier_created', "Created supplier: {$supplier->name}", $supplier);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'supplies' => fn($q) => $q->latest()->take(10),
            'payments' => fn($q) => $q->latest()->take(10),
            'purchaseInvoices' => fn($q) => $q->latest()->take(5),
        ]);

        return view('admin.suppliers.show', compact('supplier'));
    }

    /**
     * Show all orders/supplies for a supplier
     */
    public function orders(Supplier $supplier)
    {
        $supplies = $supplier->supplies()
            ->with(['user', 'stockMovements.product'])
            ->latest()
            ->paginate(15);
        
        // Get purchase invoices for this supplier
        $purchaseInvoices = PurchaseInvoice::where('supplier_id', $supplier->id)
            ->latest()
            ->paginate(15, ['*'], 'invoices_page');
        
        return view('admin.suppliers.orders', compact('supplier', 'supplies', 'purchaseInvoices'));
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:suppliers,phone,' . $supplier->id,
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'products_supplied' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $oldValues = $supplier->toArray();
        $supplier->update($validated);

        AuditLog::log('supplier_updated', "Updated supplier: {$supplier->name}", $supplier, $oldValues, $supplier->toArray());

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function recordSupply(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'total_amount' => 'required|numeric|min:0.01',
            'amount_paid' => 'required|numeric|min:0',
            'payment_type' => 'required|in:cash,credit,partial',
            'items_description' => 'required|string',
            'supply_date' => 'required|date',
            'notes' => 'nullable|string',
            'products' => 'nullable|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $balance = $validated['total_amount'] - $validated['amount_paid'];

        $supply = SupplierSupply::create([
            'supplier_id' => $supplier->id,
            'user_id' => auth()->id(),
            'total_amount' => $validated['total_amount'],
            'amount_paid' => $validated['amount_paid'],
            'balance' => $balance,
            'payment_type' => $validated['payment_type'],
            'items_description' => $validated['items_description'],
            'supply_date' => $validated['supply_date'],
            'notes' => $validated['notes'],
        ]);

        // Add balance to supplier
        if ($balance > 0) {
            $supplier->addToBalance($balance);
        }

        // Add stock for products if provided
        if (!empty($validated['products'])) {
            foreach ($validated['products'] as $productData) {
                $product = Product::find($productData['id']);
                $stockBefore = $product->stock_quantity;
                $product->increment('stock_quantity', $productData['quantity']);

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'supplier_supply_id' => $supply->id,
                    'type' => 'in',
                    'quantity' => $productData['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->stock_quantity,
                    'unit_cost' => $productData['unit_cost'],
                    'reason' => "Supply from {$supplier->name}",
                ]);

                // Update product cost price
                $product->update(['cost_price' => $productData['unit_cost']]);
            }
        }

        AuditLog::log('supplier_supply', "Recorded supply from {$supplier->name}: {$validated['total_amount']}", $supply);

        return back()->with('success', 'Supply recorded successfully.');
    }

    public function makePayment(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $supplier->balance_owed,
            'payment_method' => 'required|in:cash,transfer,cheque',
            'notes' => 'nullable|string|max:500',
        ]);

        $balanceBefore = $supplier->balance_owed;
        $supplier->reduceBalance($validated['amount']);

        SupplierPayment::create([
            'supplier_id' => $supplier->id,
            'user_id' => auth()->id(),
            'amount' => $validated['amount'],
            'balance_before' => $balanceBefore,
            'balance_after' => $supplier->balance_owed,
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'],
        ]);

        AuditLog::log('supplier_payment', "Made payment of {$validated['amount']} to {$supplier->name}", $supplier);

        return back()->with('success', 'Payment made successfully.');
    }

    public function statement(Supplier $supplier)
    {
        $supplies = $supplier->supplies()->latest()->get();
        $payments = $supplier->payments()->with('user')->latest()->get();

        return view('admin.suppliers.statement', compact('supplier', 'supplies', 'payments'));
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->balance_owed > 0) {
            return back()->with('error', 'Cannot delete supplier with outstanding balance.');
        }

        if ($supplier->supplies()->count() > 0) {
            return back()->with('error', 'Cannot delete supplier with supply history.');
        }

        AuditLog::log('supplier_deleted', "Deleted supplier: {$supplier->name}", $supplier);
        $supplier->delete();

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Show delivery checklist for a supplier supply
     */
    public function deliveryChecklist(SupplierSupply $supply)
    {
        $supply->load(['supplier', 'stockMovements.product']);
        $products = Product::with('category')->orderBy('name')->get();
        
        // Parse items from description if no stock movements exist
        $orderedItems = [];
        if ($supply->stockMovements->isEmpty() && $supply->items_description) {
            // Try to extract items from description (basic parsing)
            $lines = explode("\n", $supply->items_description);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $orderedItems[] = ['description' => $line];
                }
            }
        }
        
        return view('admin.suppliers.delivery-checklist', compact('supply', 'products', 'orderedItems'));
    }

    /**
     * Verify delivery checklist
     */
    public function verifyDelivery(Request $request, SupplierSupply $supply)
    {
        $validated = $request->validate([
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items.*.checked|exists:products,id',
            'items.*.quantity_received' => 'required_with:items.*.checked|integer|min:0',
            'items.*.unit_cost' => 'required_with:items.*.checked|string',
            'items.*.checked' => 'boolean',
            'additional_items' => 'nullable|array',
            'additional_items.*.product_id' => 'required|exists:products,id',
            'additional_items.*.quantity' => 'required|integer|min:1',
            'additional_items.*.unit_cost' => 'required|string',
            'additional_items.*.description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Process checked items
        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                if (!empty($item['checked']) && !empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    if (!$product) continue;
                    
                    $quantityReceived = $item['quantity_received'] ?? 0;
                    
                    if ($quantityReceived > 0) {
                        $stockBefore = $product->stock_quantity;
                        $product->increment('stock_quantity', $quantityReceived);

                        // Remove commas from unit cost
                        $unitCost = str_replace(',', '', $item['unit_cost']);

                        // Check if stock movement already exists for this supply
                        $existingMovement = StockMovement::where('supplier_supply_id', $supply->id)
                            ->where('product_id', $product->id)
                            ->first();

                        if ($existingMovement) {
                            // Update existing movement
                            $existingMovement->update([
                                'quantity' => $quantityReceived,
                                'stock_after' => $product->stock_quantity,
                                'unit_cost' => $unitCost,
                            ]);
                        } else {
                            // Create new stock movement
                            StockMovement::create([
                                'product_id' => $product->id,
                                'user_id' => auth()->id(),
                                'supplier_supply_id' => $supply->id,
                                'type' => 'in',
                                'quantity' => $quantityReceived,
                                'stock_before' => $stockBefore,
                                'stock_after' => $product->stock_quantity,
                                'unit_cost' => $unitCost,
                                'reason' => "Supply from {$supply->supplier->name} - Verified",
                            ]);
                        }

                        // Update product cost price
                        $product->update(['cost_price' => $unitCost]);
                    }
                }
            }
        }

        // Process additional items (items not in original order)
        if (!empty($validated['additional_items'])) {
            foreach ($validated['additional_items'] as $additionalItem) {
                if (empty($additionalItem['product_id'])) continue;
                
                $product = Product::find($additionalItem['product_id']);
                if (!$product) continue;
                
                $stockBefore = $product->stock_quantity;
                $product->increment('stock_quantity', $additionalItem['quantity']);

                // Remove commas from unit cost
                $unitCost = str_replace(',', '', $additionalItem['unit_cost']);

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'supplier_supply_id' => $supply->id,
                    'type' => 'in',
                    'quantity' => $additionalItem['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->stock_quantity,
                    'unit_cost' => $unitCost,
                    'reason' => "Additional item from {$supply->supplier->name}: " . ($additionalItem['description'] ?? $product->name),
                ]);

                // Update product cost price
                $product->update(['cost_price' => $unitCost]);
            }
        }

        // Update supply notes if provided
        if (!empty($validated['notes'])) {
            $supply->update(['notes' => ($supply->notes ?? '') . "\n\nDelivery Verified: " . $validated['notes']]);
        }

        AuditLog::log('delivery_verified', "Verified delivery for supply {$supply->reference_number}", $supply);

        return redirect()->route('admin.suppliers.show', $supply->supplier)
            ->with('success', 'Delivery verified and stock updated successfully.');
    }
}











