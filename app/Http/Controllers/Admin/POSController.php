<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Room;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Models\Table;
use App\Models\TableGuest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function __construct()
    {
        // Allow admins, managers, supervisors, and cashiers to access POS
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            
            if (!$user) {
                return redirect('/login');
            }
            
            // Allow admins
            if ($user->is_admin) {
                return $next($request);
            }
            
            // Allow cashiers, managers, supervisors
            if ($user->isCashier() || $user->isManager() || $user->isSupervisor()) {
                return $next($request);
            }
            
            // Deny access for other roles
            abort(403, 'You do not have permission to access the POS system.');
        });
    }

    public function index(Request $request)
    {
        $categories = Category::active()->with(['products' => fn($q) => $q->active()->where('stock_quantity', '>', 0)])->get();
        $customers = Customer::active()->get();
        $shift = auth()->user()->getOpenShift(); // Optional - for display only
        
        // Get tables and selected table/guest if provided - sorted naturally (Table 1, 2, 3... 10)
        $tables = Table::active()
            ->get()
            ->sortBy(function($table) {
                // Extract numeric part for natural sorting
                preg_match('/(\d+)/', $table->number, $matches);
                $numericPart = isset($matches[1]) ? (int)$matches[1] : 999999;
                // Return array: [numeric_part, full_string] for proper sorting
                return [$numericPart, $table->number];
            })
            ->values();
        $selectedTable = $request->filled('table_id') ? Table::with('activeGuests')->find($request->table_id) : null;
        $selectedGuest = $request->filled('guest_id') ? TableGuest::find($request->guest_id) : null;
        // NOTE: Do not auto-select a table.
        // Many workflows (walk-in or credit customer sales) should work without table/guest selection.
        // Tables/guests are only required when the user explicitly chooses a table.
        
        // Identify kitchen/food categories (categories with food-related keywords in name)
        $kitchenKeywords = ['food', 'kitchen', 'meal', 'dish', 'main', 'appetizer', 'starter', 'soup', 'salad', 'dessert', 'breakfast', 'lunch', 'dinner', 'snack'];
        $kitchenCategoryIds = $categories->filter(function($category) use ($kitchenKeywords) {
            $name = strtolower($category->name);
            return collect($kitchenKeywords)->contains(function($keyword) use ($name) {
                return str_contains($name, $keyword);
            });
        })->pluck('id')->toArray();
        
        // Get ready kitchen orders (orders with food items that are ready to serve)
        $readyKitchenOrders = Sale::with(['items.product', 'table', 'tableGuest', 'customer'])
            ->completed()
            ->kitchenReady()
            ->today()
            ->orderBy('kitchen_ready_at', 'desc')
            ->take(20)
            ->get();

        return view('admin.pos.index', compact('categories', 'customers', 'shift', 'tables', 'selectedTable', 'selectedGuest', 'kitchenCategoryIds', 'readyKitchenOrders'));
    }

    public function getProducts(Request $request)
    {
        $query = Product::active()->where('stock_quantity', '>', 0);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
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
            'vat_rate' => 'nullable|numeric|min:0|max:1',
            'notes' => 'nullable|string',
        ]);

        // Get shift if available (optional)
        $shift = auth()->user()->getOpenShift();

        // Verify stock availability
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            if ($product->stock_quantity < $item['quantity']) {
                return response()->json(['error' => "Insufficient stock for {$product->name}."], 422);
            }
        }

        // Calculate totals
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $itemTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0);
            $subtotal += $itemTotal;
        }

        $discount = $validated['discount'] ?? 0;
        $vatRate = $validated['vat_rate'] ?? 0; 
        $vatAmount = ($subtotal - $discount) * $vatRate;
        $total = ($subtotal - $discount) + $vatAmount;
        $isCredit = $validated['payment_method'] === 'credit';
        $amountPaid = $isCredit ? 0 : ($validated['amount_paid'] ?? 0);
        
        // Check if order contains food items (kitchen items)
        // Get kitchen category IDs
        $kitchenKeywords = ['food', 'kitchen', 'meal', 'dish', 'main', 'appetizer', 'starter', 'soup', 'salad', 'dessert', 'breakfast', 'lunch', 'dinner', 'snack'];
        $kitchenCategoryIds = Category::active()
            ->where(function($q) use ($kitchenKeywords) {
                foreach ($kitchenKeywords as $keyword) {
                    $q->orWhere('name', 'like', "%{$keyword}%");
                }
            })
            ->pluck('id')
            ->toArray();
        
        // Check if any item is from a kitchen category
        $hasKitchenItems = false;
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            if ($product && in_array($product->category_id, $kitchenCategoryIds)) {
                $hasKitchenItems = true;
                break;
            }
        }
        
        // Set kitchen_status: 'pending' if has food items, null otherwise
        $kitchenStatus = $hasKitchenItems ? 'pending' : null;
        
        // Validate amount paid for non-credit sales
        if (!$isCredit && ($amountPaid <= 0 || $amountPaid < $total)) {
            return response()->json(['error' => 'Amount paid must be greater than or equal to the total amount.'], 422);
        }

        // If credit sale, verify customer can afford it
        if ($isCredit) {
            if (empty($validated['customer_id'])) {
                return response()->json(['error' => 'Customer required for credit sale.'], 422);
            }

            $customer = Customer::find($validated['customer_id']);
            if (!$customer->canPurchaseOnCredit($total)) {
                return response()->json(['error' => 'Customer credit limit exceeded.'], 422);
            }
        }

        try {
            DB::beginTransaction();

            // Check if there's a pending sale for this guest and convert it to completed
            $pendingSale = null;
            if ($validated['table_id'] || $validated['table_guest_id']) {
                $pendingSale = Sale::where('table_id', $validated['table_id'] ?? null)
                    ->where('table_guest_id', $validated['table_guest_id'] ?? null)
                    ->where('status', 'pending')
                    ->where('user_id', auth()->id())
                    ->first();
            }

            if ($pendingSale) {
                // Update pending sale to completed
                $pendingSale->update([
                    'customer_id' => $validated['customer_id'],
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $vatAmount,
                    'total' => $total,
                    'amount_paid' => $isCredit ? 0 : $amountPaid,
                    'change' => $isCredit ? 0 : max(0, $amountPaid - $total),
                    'payment_method' => $validated['payment_method'],
                    'is_credit_sale' => $isCredit,
                    'notes' => $validated['notes'],
                    'status' => 'completed',
                    'kitchen_status' => $kitchenStatus,
                ]);

                // Delete old items and create new ones (in case items changed)
                $pendingSale->items()->delete();
                $sale = $pendingSale;
            } else {
                // Create new sale
                $sale = Sale::create([
                    'user_id' => auth()->id(),
                    'shift_id' => $shift?->id,
                    'customer_id' => $validated['customer_id'],
                    'table_id' => $validated['table_id'] ?? null,
                    'table_guest_id' => $validated['table_guest_id'] ?? null,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $vatAmount,
                    'total' => $total,
                    'amount_paid' => $isCredit ? 0 : $amountPaid,
                    'change' => $isCredit ? 0 : max(0, $amountPaid - $total),
                    'payment_method' => $validated['payment_method'],
                    'is_credit_sale' => $isCredit,
                    'notes' => $validated['notes'],
                    'kitchen_status' => $kitchenStatus,
                ]);
            }

            // Create sale items and update stock
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
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

                // Update stock
                $stockBefore = $product->stock_quantity;
                $product->decrement('stock_quantity', $item['quantity']);

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'sale_id' => $sale->id,
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->stock_quantity,
                    'reason' => "Sale #{$sale->invoice_number}",
                ]);
            }

            // If credit sale, add to customer balance
            if ($isCredit) {
                $customer = Customer::find($validated['customer_id']);
                $customer->addToBalance($total);
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

            AuditLog::log('sale_created', "Created sale #{$sale->invoice_number} for {$total}", $sale);

            $sale->load('items.product', 'customer', 'user');

            return response()->json([
                'success' => true,
                'sale' => $sale,
                'message' => 'Sale completed successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to process sale: ' . $e->getMessage()], 500);
        }
    }

    public function show(Sale $sale)
    {
        $sale->load('items.product', 'customer', 'user');
        return view('admin.pos.receipt', compact('sale'));
    }

    public function void(Request $request, Sale $sale)
    {
        if (!auth()->user()->canVoidSales()) {
            return response()->json(['error' => 'You are not authorized to void sales.'], 403);
        }

        if ($sale->status === 'voided') {
            return response()->json(['error' => 'This sale is already voided.'], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $sale->void(auth()->id(), $validated['reason']);

        AuditLog::log('sale_voided', "Voided sale #{$sale->invoice_number}: {$validated['reason']}", $sale);

        return response()->json([
            'success' => true,
            'message' => 'Sale voided successfully.',
        ]);
    }

    public function history(Request $request)
    {
        $query = Sale::with(['user', 'customer', 'items']);

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            $query->today();
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->latest()->paginate(20);

        return view('admin.pos.history', compact('sales'));
    }

    /**
     * Save pending order (auto-save when switching guests)
     */
    public function savePending(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'table_id' => 'nullable|exists:tables,id',
            'table_guest_id' => 'nullable',
            'customer_id' => 'nullable|exists:customers,id',
            'discount' => 'nullable|numeric|min:0',
        ]);

        if (!empty($validated['table_guest_id']) && !TableGuest::find($validated['table_guest_id'])) {
            return response()->json([
                'success' => true,
                'message' => 'Guest already removed.',
            ]);
        }

        $shift = auth()->user()->getOpenShift();

        // Calculate totals
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $itemTotal = ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0);
            $subtotal += $itemTotal;
        }

        $discount = $validated['discount'] ?? 0;
        $vatRate = $request->vat_rate ?? 0;
        $vatAmount = ($subtotal - $discount) * $vatRate;
        $total = ($subtotal - $discount) + $vatAmount;
        
        // Check if order contains food items (kitchen items)
        $kitchenKeywords = ['food', 'kitchen', 'meal', 'dish', 'main', 'appetizer', 'starter', 'soup', 'salad', 'dessert', 'breakfast', 'lunch', 'dinner', 'snack'];
        $kitchenCategoryIds = Category::active()
            ->where(function($q) use ($kitchenKeywords) {
                foreach ($kitchenKeywords as $keyword) {
                    $q->orWhere('name', 'like', "%{$keyword}%");
                }
            })
            ->pluck('id')
            ->toArray();
        
        // Check if any item is from a kitchen category
        $hasKitchenItems = false;
        foreach ($validated['items'] as $item) {
            $product = Product::find($item['product_id']);
            if ($product && in_array($product->category_id, $kitchenCategoryIds)) {
                $hasKitchenItems = true;
                break;
            }
        }
        
        // Set kitchen_status: 'pending' if has food items, null otherwise
        $kitchenStatus = $hasKitchenItems ? 'pending' : null;

        try {
            DB::beginTransaction();

            // Check if there's an existing pending sale for this guest
            $existingPending = Sale::where('table_id', $validated['table_id'] ?? null)
                ->where('table_guest_id', $validated['table_guest_id'] ?? null)
                ->where('status', 'pending')
                ->where('user_id', auth()->id())
                ->first();

            if ($existingPending) {
                // Update existing pending sale
                $existingPending->update([
                    'customer_id' => $validated['customer_id'] ?? null,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $vatAmount,
                    'total' => $total,
                    'kitchen_status' => $kitchenStatus,
                ]);

                // Delete old items
                $existingPending->items()->delete();

                $sale = $existingPending;
            } else {
                // Create new pending sale
                $sale = Sale::create([
                    'user_id' => auth()->id(),
                    'shift_id' => $shift?->id,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'table_id' => $validated['table_id'] ?? null,
                    'table_guest_id' => $validated['table_guest_id'] ?? null,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $vatAmount,
                    'total' => $total,
                    'amount_paid' => 0,
                    'change' => 0,
                    'payment_method' => 'cash',
                    'status' => 'pending',
                    'is_credit_sale' => false,
                    'kitchen_status' => $kitchenStatus,
                ]);
            }

            // Create sale items (but don't update stock yet - only when completed)
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
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
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'message' => 'Order saved successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to save order: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get pending order(s) for a guest or table
     */
    public function getPending(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'nullable',
            'table_guest_id' => 'nullable',
            'sale_id' => 'nullable',
        ]);

        // If sale_id is provided, get that specific order
        if ($validated['sale_id'] ?? null) {
            $sale = Sale::where('id', $validated['sale_id'])
                ->with(['items', 'tableGuest', 'customer', 'table'])
                ->first();

            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.',
                    'orders' => [],
                ]);
            }

            // Check if it's pending or completed
            if ($sale->status === 'pending') {
                return response()->json([
                    'success' => true,
                    'sale' => [
                        'id' => $sale->id,
                        'invoice_number' => $sale->invoice_number,
                        'table_id' => $sale->table_id,
                        'table_guest_id' => $sale->table_guest_id,
                        'customer_id' => $sale->customer_id,
                        'customer_name' => $sale->customer?->name,
                        'guest_name' => $sale->tableGuest?->guest_name,
                        'discount' => $sale->discount,
                        'subtotal' => $sale->subtotal,
                        'tax' => $sale->tax,
                        'total' => $sale->total,
                        'status' => $sale->status,
                        'created_at' => $sale->created_at,
                    ],
                    'items' => $sale->items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'name' => $item->product_name,
                            'unit_price' => $item->unit_price,
                            'quantity' => $item->quantity,
                            'discount' => $item->discount,
                        ];
                    }),
                ]);
            } else {
                // Sale exists but is completed - still return it for viewing
                return response()->json([
                    'success' => true,
                    'sale' => [
                        'id' => $sale->id,
                        'invoice_number' => $sale->invoice_number,
                        'table_id' => $sale->table_id,
                        'table_guest_id' => $sale->table_guest_id,
                        'customer_id' => $sale->customer_id,
                        'customer_name' => $sale->customer?->name,
                        'guest_name' => $sale->tableGuest?->guest_name,
                        'discount' => $sale->discount,
                        'subtotal' => $sale->subtotal,
                        'tax' => $sale->tax,
                        'total' => $sale->total,
                        'status' => $sale->status,
                        'created_at' => $sale->created_at,
                    ],
                    'items' => $sale->items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'name' => $item->product_name,
                            'unit_price' => $item->unit_price,
                            'quantity' => $item->quantity,
                            'discount' => $item->discount,
                        ];
                    }),
                    'message' => 'This order is already completed. You can view it but cannot edit.',
                ]);
            }
        }

        // If table_id is provided without guest_id, get all pending orders for that table
        if ($validated['table_id'] ?? null && !($validated['table_guest_id'] ?? null)) {
            $pendingSales = Sale::where('table_id', $validated['table_id'])
                ->where('status', 'pending')
                ->where('user_id', auth()->id())
                ->where(function ($query) {
                    $query->whereNull('table_guest_id')
                        ->orWhereHas('tableGuest', function ($guestQuery) {
                            $guestQuery->where('is_active', true);
                        });
                })
                ->with(['tableGuest', 'customer', 'items'])
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'orders' => $pendingSales->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'invoice_number' => $sale->invoice_number,
                        'table_guest_id' => $sale->table_guest_id,
                        'guest_name' => $sale->tableGuest?->guest_name,
                        'customer_name' => $sale->customer?->name,
                        'total' => $sale->total,
                        'items_count' => $sale->items->count(),
                        'created_at' => $sale->created_at,
                    ];
                }),
            ]);
        }

        // Get specific guest's pending order
        $pendingSale = Sale::where('table_id', $validated['table_id'] ?? null)
            ->where('table_guest_id', $validated['table_guest_id'] ?? null)
            ->where('status', 'pending')
            ->where('user_id', auth()->id())
            ->with(['items.product', 'tableGuest', 'customer'])
            ->first();

        if ($pendingSale) {
            return response()->json([
                'success' => true,
                'sale' => [
                    'id' => $pendingSale->id,
                    'invoice_number' => $pendingSale->invoice_number,
                    'customer_id' => $pendingSale->customer_id,
                    'customer_name' => $pendingSale->customer?->name,
                    'guest_name' => $pendingSale->tableGuest?->guest_name,
                    'discount' => $pendingSale->discount,
                    'subtotal' => $pendingSale->subtotal,
                    'tax' => $pendingSale->tax,
                    'total' => $pendingSale->total,
                    'created_at' => $pendingSale->created_at,
                ],
                'items' => $pendingSale->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'name' => $item->product_name,
                        'unit_price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'discount' => $item->discount,
                    ];
                }),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No pending order found.',
        ]);
    }

    /**
     * Clear all pending orders
     */
    public function clearPending(Request $request)
    {
        $count = Sale::where('status', 'pending')
            ->where('user_id', auth()->id())
            ->count();

        Sale::where('status', 'pending')
            ->where('user_id', auth()->id())
            ->delete();

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => 'Pending orders cleared successfully.',
        ]);
    }

    /**
     * Delete a single pending order
     */
    public function deletePending($sale)
    {
        $sale = Sale::find($sale);
        if (!$sale) {
            return response()->json([
                'success' => true,
                'message' => 'Pending order already removed.',
            ]);
        }

        $user = auth()->user();

        $canDelete = $sale->user_id === $user->id
            || $user->is_admin
            || $user->isManager()
            || $user->isSupervisor();

        if (!$canDelete) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this pending order.',
            ], 403);
        }

        if ($sale->status !== 'pending') {
            return response()->json([
                'success' => true,
                'message' => 'Order already completed.',
            ]);
        }

        $sale->items()->delete();
        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pending order deleted successfully.',
        ]);
    }

    /**
     * Supervisor Dashboard - View all pending orders in real-time
     */
    public function supervisorDashboard()
    {
        return view('admin.pos.supervisor');
    }

    /**
     * Get all pending orders for supervisor dashboard (all users)
     * Shows: pending sales + completed sales with pending/preparing kitchen status
     */
    public function getAllPendingOrders(Request $request)
    {
        try {
            // Get all active orders:
            // 1. Sales with status='pending' (cart items not yet completed)
            // 2. Completed sales with kitchen_status='pending' or 'preparing' (orders being prepared)
            $pendingSales = Sale::where(function($query) {
                    $query->where('status', 'pending')
                        ->orWhere(function($q) {
                            $q->where('status', 'completed')
                              ->whereIn('kitchen_status', ['pending', 'preparing']);
                        });
                })
                ->with(['user', 'table', 'tableGuest', 'customer', 'items'])
                ->latest()
                ->get();

            $orders = $pendingSales->map(function ($sale) {
                $statusBadge = '';
                if ($sale->status === 'pending') {
                    $statusBadge = '<span class="badge bg-warning">Pending Payment</span>';
                } elseif ($sale->kitchen_status === 'preparing') {
                    $statusBadge = '<span class="badge bg-info">Preparing</span>';
                } elseif ($sale->kitchen_status === 'pending') {
                    $statusBadge = '<span class="badge bg-primary">Kitchen Pending</span>';
                }

                return [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'waiter_name' => $sale->user->name ?? 'Unknown',
                    'waiter_id' => $sale->user_id,
                    'table_id' => $sale->table_id,
                    'table_number' => $sale->table?->number ?? 'N/A',
                    'table_name' => $sale->table?->name ?? 'N/A',
                    'guest_id' => $sale->table_guest_id,
                    'guest_name' => $sale->tableGuest?->guest_name ?? 'All Guests',
                    'customer_name' => $sale->customer?->name,
                    'subtotal' => (float) $sale->subtotal,
                    'discount' => (float) $sale->discount,
                    'tax' => (float) $sale->tax,
                    'total' => (float) $sale->total,
                    'status' => $sale->status,
                    'kitchen_status' => $sale->kitchen_status,
                    'status_badge' => $statusBadge,
                    'items_count' => $sale->items->count(),
                    'items' => $sale->items->map(function ($item) {
                        return [
                            'name' => $item->product_name,
                            'quantity' => (int) $item->quantity,
                            'unit_price' => (float) $item->unit_price,
                            'total' => (float) (($item->unit_price * $item->quantity) - ($item->discount ?? 0)),
                        ];
                    })->toArray(),
                    'created_at' => $sale->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $sale->created_at->diffForHumans(),
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'orders' => $orders,
                'count' => count($orders),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching pending orders: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading orders: ' . $e->getMessage(),
                'orders' => [],
            ], 500);
        }
    }

    public function getRooms(Request $request)
    {
        try {
            $categoryId = $request->input('category_id');
            $roomId = $request->input('room_id');
            $query = Room::active();

            if ($roomId) {
                // If room_id is provided, return just that room
                $query->where('id', $roomId);
            } elseif ($categoryId) {
                $query->where('room_category_id', $categoryId);
            }

            $rooms = $query->with('category')
                ->orderBy('room_number')
                ->get()
                ->map(function($room) {
                    return [
                        'id' => $room->id,
                        'room_number' => $room->room_number,
                        'room_name' => $room->getDisplayName(),
                        'price_per_night' => $room->getEffectivePrice(),
                        'hourly_rate' => $room->category->hourly_rate ?? ($room->getEffectivePrice() / 24),
                        'status' => $room->status,
                        'is_available' => $room->isAvailable(),
                        'is_suite_sub_room' => $room->is_suite_sub_room,
                        'category' => [
                            'id' => $room->category->id,
                            'name' => $room->category->name,
                            'is_suite' => $room->category->is_suite,
                            'full_suite_price' => $room->category->full_suite_price,
                            'hourly_rate' => $room->category->hourly_rate,
                        ],
                    ];
                });

            return response()->json([
                'success' => true,
                'rooms' => $rooms,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading rooms: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function checkRoomAvailability(Request $request)
    {
        try {
            $roomId = $request->input('room_id');
            $checkIn = $request->input('check_in_date');
            $checkOut = $request->input('check_out_date');

            if (!$roomId || !$checkIn || !$checkOut) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required parameters',
                ], 400);
            }

            $room = Room::findOrFail($roomId);
            
            // Use RoomController's checkAvailability logic
            $controller = new \App\Http\Controllers\Admin\RoomController();
            return $controller->checkAvailability($room, $request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking availability: ' . $e->getMessage(),
            ], 500);
        }
    }
}



