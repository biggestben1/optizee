<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Table;
use App\Models\TableGuest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function catalog()
    {
        $this->ensurePosAccess();

        $categories = Category::active()
            ->with(['products' => fn ($q) => $q->active()->where('stock_quantity', '>', 0)->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'products' => $category->products->map(fn ($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'selling_price' => (float) $p->selling_price,
                        'stock_quantity' => (int) $p->stock_quantity,
                        'category_id' => $p->category_id,
                    ])->values(),
                ];
            })
            ->filter(fn ($c) => count($c['products']) > 0)
            ->values();

        return response()->json(['categories' => $categories]);
    }

    public function tables()
    {
        $this->ensurePosAccess();

        $tables = Table::active()->withCount('activeGuests')->get()->sortBy(function ($table) {
            preg_match('/(\d+)/', $table->number ?? '', $matches);
            return [(int) ($matches[1] ?? 999999), $table->number ?? ''];
        })->values()->map(fn ($t) => [
            'id' => $t->id,
            'number' => $t->number,
            'status' => $t->status ?? null,
            'guest_count' => (int) $t->active_guests_count,
        ]);

        return response()->json(['tables' => $tables]);
    }

    public function tableGuests(Table $table)
    {
        $this->ensurePosAccess();

        $guests = $table->activeGuests()->with('customer')->orderBy('id')->get()->map(fn ($guest) => [
            'id' => $guest->id,
            'guest_name' => $guest->guest_name,
            'customer' => $guest->customer ? [
                'id' => $guest->customer->id,
                'name' => $guest->customer->name,
            ] : null,
        ]);

        return response()->json([
            'table' => [
                'id' => $table->id,
                'number' => $table->number,
                'status' => $table->status,
            ],
            'guests' => $guests,
        ]);
    }

    public function addGuest(Request $request, Table $table)
    {
        $this->ensurePosAccess();

        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        if (!$table->isOccupied()) {
            try {
                $shift = auth()->user()?->getOpenShift();
                $table->occupy(auth()->id(), $shift?->id);
            } catch (\Throwable $e) {
                try {
                    $table->occupy(auth()->id(), null);
                } catch (\Throwable $ignored) {
                    // Guest can still be added even if occupy fails.
                }
            }
        }

        $guest = TableGuest::create([
            'table_id' => $table->id,
            'guest_name' => $validated['guest_name'],
            'customer_id' => $validated['customer_id'] ?? null,
            'created_by' => auth()->id(),
            'is_active' => true,
            'seated_at' => now(),
        ]);
        $guest->load('customer');

        AuditLog::log('guest_added', "Mobile: added guest {$guest->guest_name} to table {$table->number}", $table);

        return response()->json([
            'success' => true,
            'message' => 'Guest added successfully.',
            'guest' => [
                'id' => $guest->id,
                'guest_name' => $guest->guest_name,
                'customer' => $guest->customer ? [
                    'id' => $guest->customer->id,
                    'name' => $guest->customer->name,
                ] : null,
            ],
        ]);
    }

    public function customers()
    {
        $this->ensurePosAccess();

        $customers = Customer::active()->withCredit()->get()->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'phone' => $c->phone,
            'credit_enabled' => (bool) $c->credit_enabled,
            'available_credit' => (float) $c->getAvailableCredit(),
        ]);

        return response()->json(['customers' => $customers]);
    }

    public function readyOrders()
    {
        $this->ensurePosAccess();

        $orders = Sale::with(['items.product', 'table', 'tableGuest', 'customer'])
            ->completed()
            ->kitchenReady()
            ->today()
            ->orderBy('kitchen_ready_at', 'desc')
            ->take(30)
            ->get()
            ->map(fn ($sale) => $this->mapSale($sale));

        return response()->json(['orders' => $orders]);
    }

    public function checkout(Request $request)
    {
        $this->ensurePosAccess();

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'table_id' => 'nullable|exists:tables,id',
            'table_guest_id' => 'nullable|exists:table_guests,id',
            'payment_method' => 'required|in:cash,transfer,pos,credit,mixed',
            'amount_paid' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            if (!$product || $product->stock_quantity < $item['quantity']) {
                return response()->json([
                    'message' => 'Insufficient stock for ' . ($product->name ?? 'item') . '.',
                ], 422);
            }
        }

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0);
        }

        $discount = $validated['discount'] ?? 0;
        $total = $subtotal - $discount;
        $isCredit = $validated['payment_method'] === 'credit';
        $amountPaid = $isCredit ? 0 : ($validated['amount_paid'] ?? 0);

        if (!$isCredit && ($amountPaid <= 0 || $amountPaid < $total)) {
            return response()->json(['message' => 'Amount paid must cover the total.'], 422);
        }

        if ($isCredit) {
            if (empty($validated['customer_id'])) {
                return response()->json(['message' => 'Customer required for credit sale.'], 422);
            }
            $customer = Customer::find($validated['customer_id']);
            if (!$customer || !$customer->canPurchaseOnCredit($total)) {
                return response()->json(['message' => 'Customer credit limit exceeded.'], 422);
            }
        }

        $kitchenKeywords = ['food', 'kitchen', 'meal', 'dish', 'main', 'appetizer', 'starter', 'soup', 'salad', 'dessert', 'breakfast', 'lunch', 'dinner', 'snack'];
        $kitchenCategoryIds = Category::active()
            ->where(function ($q) use ($kitchenKeywords) {
                foreach ($kitchenKeywords as $keyword) {
                    $q->orWhere('name', 'like', "%{$keyword}%");
                }
            })
            ->pluck('id')
            ->toArray();

        $hasKitchenItems = false;
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            if ($product && in_array($product->category_id, $kitchenCategoryIds, true)) {
                $hasKitchenItems = true;
                break;
            }
        }

        try {
            DB::beginTransaction();

            $shift = auth()->user()->getOpenShift();

            $sale = Sale::create([
                'user_id' => auth()->id(),
                'shift_id' => $shift?->id,
                'customer_id' => $validated['customer_id'] ?? null,
                'table_id' => $validated['table_id'] ?? null,
                'table_guest_id' => $validated['table_guest_id'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => 0,
                'total' => $total,
                'amount_paid' => $amountPaid,
                'change' => $isCredit ? 0 : max(0, $amountPaid - $total),
                'payment_method' => $validated['payment_method'],
                'is_credit_sale' => $isCredit,
                'notes' => $validated['notes'] ?? null,
                'kitchen_status' => $hasKitchenItems ? 'pending' : null,
                'status' => 'completed',
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemDiscount = $item['discount'] ?? 0;
                $itemTotal = ($item['unit_price'] * $item['quantity']) - $itemDiscount;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $product->cost_price,
                    'discount' => $itemDiscount,
                    'total' => $itemTotal,
                ]);

                $stockBefore = $product->stock_quantity;
                $product->decrement('stock_quantity', $item['quantity']);

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'sale_id' => $sale->id,
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->fresh()->stock_quantity,
                    'reason' => "Sale #{$sale->invoice_number}",
                ]);
            }

            if ($isCredit) {
                Customer::find($validated['customer_id'])->addToBalance($total);
            }

            if (!empty($validated['table_guest_id'])) {
                $guest = TableGuest::find($validated['table_guest_id']);
                if ($guest) {
                    $guest->leave();
                    $table = $guest->table;
                    if ($table && $table->activeGuests()->count() === 0) {
                        $table->release();
                    }
                }
            }

            DB::commit();

            AuditLog::log('sale_created', "Mobile sale #{$sale->invoice_number} for {$total}", $sale);

            $sale->load('items.product', 'customer', 'user', 'table', 'tableGuest');

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully.',
                'sale' => $this->mapSale($sale),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Sale failed: ' . $e->getMessage()], 500);
        }
    }

    protected function mapSale(Sale $sale): array
    {
        return [
            'id' => $sale->id,
            'invoice_number' => $sale->invoice_number,
            'total' => (float) $sale->total,
            'amount_paid' => (float) $sale->amount_paid,
            'change' => (float) ($sale->change ?? 0),
            'payment_method' => $sale->payment_method,
            'kitchen_status' => $sale->kitchen_status,
            'created_at' => optional($sale->created_at)?->toDateTimeString(),
            'customer' => $sale->customer ? ['id' => $sale->customer->id, 'name' => $sale->customer->name] : null,
            'table' => $sale->table ? ['id' => $sale->table->id, 'number' => $sale->table->number] : null,
            'table_guest' => $sale->tableGuest ? ['guest_name' => $sale->tableGuest->guest_name] : null,
            'items' => $sale->items->map(fn ($item) => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total' => (float) $item->total,
            ])->values(),
        ];
    }

    protected function ensurePosAccess(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->is_admin && !$user->canAccessPOS())) {
            abort(403, 'You do not have permission to access POS.');
        }
    }
}
