<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Sale;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->canAccessKitchen()) {
                abort(403, 'You do not have permission to access the kitchen.');
            }
            return $next($request);
        });
    }

    /**
     * Get Food category IDs (including subcategories)
     */
    private function getFoodCategoryIds(): array
    {
        $foodCategory = Category::where('name', 'Food')->first();
        $foodCategoryIds = [];
        if ($foodCategory) {
            $foodCategoryIds[] = $foodCategory->id;
            // Add all subcategories of Food
            $foodCategoryIds = array_merge($foodCategoryIds, $foodCategory->children()->pluck('id')->toArray());
        }
        return $foodCategoryIds;
    }

    /**
     * Filter sale items to only show food items
     */
    private function filterFoodItems($sales, array $foodCategoryIds)
    {
        if (empty($foodCategoryIds)) {
            // If no food category found, return empty collection
            return collect([]);
        }
        
        return $sales->map(function($sale) use ($foodCategoryIds) {
            // Reload items relationship if needed
            if (!$sale->relationLoaded('items')) {
                $sale->load('items.product');
            }
            
            // Filter items to only show food items
            $sale->setRelation('items', $sale->items->filter(function($item) use ($foodCategoryIds) {
                if (!$item->product) {
                    return false;
                }
                // Reload product with category if needed
                if (!$item->product->relationLoaded('category')) {
                    $item->product->load('category');
                }
                // Check if product's category is in food categories
                return $item->product->category_id && in_array($item->product->category_id, $foodCategoryIds);
            })->values());
            
            return $sale;
        })->filter(function($sale) {
            // Only keep orders that have food items
            return $sale->items && $sale->items->count() > 0;
        })->values();
    }

    /**
     * Display the kitchen dashboard with pending orders
     */
    public function index()
    {
        $user = auth()->user();
        $isKitchenUser = $user->isKitchen();
        $foodCategoryIds = $this->getFoodCategoryIds();
        
        // If kitchen user, show only their orders (orders they're preparing or have prepared)
        // Otherwise (manager/supervisor), show all orders
        if ($isKitchenUser) {
            // Pending orders: show all (including pending status sales with kitchen_status = 'pending')
            $pendingOrders = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer'])
                ->where(function($q) {
                    $q->where(function($q2) {
                        // Completed sales with pending kitchen status
                        $q2->where('status', 'completed')
                           ->where('kitchen_status', 'pending');
                    })->orWhere(function($q2) {
                        // Pending sales (cart items) with kitchen_status = 'pending'
                        $q2->where('status', 'pending')
                           ->where('kitchen_status', 'pending');
                    });
                })
                ->today()
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Preparing orders: show only ones this kitchen user is preparing
            $preparingOrders = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer'])
                ->where(function($q) {
                    $q->where('status', 'completed')
                      ->orWhere('status', 'pending');
                })
                ->where('kitchen_status', 'preparing')
                ->where('prepared_by', $user->id)
                ->today()
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Filter to only show food items
            $pendingOrders = $this->filterFoodItems($pendingOrders, $foodCategoryIds);
            $preparingOrders = $this->filterFoodItems($preparingOrders, $foodCategoryIds);
            
            // Combine pending and preparing
            $pendingOrders = $pendingOrders->merge($preparingOrders)->sortByDesc('created_at');
            
            // Ready orders: show only ones this kitchen user prepared
            $readyOrders = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'preparedBy'])
                ->kitchenReady()
                ->where('prepared_by', $user->id)
                ->today()
                ->orderBy('kitchen_ready_at', 'desc')
                ->take(10)
                ->get();
            
            $readyOrders = $this->filterFoodItems($readyOrders, $foodCategoryIds);

            $servedOrders = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'preparedBy'])
                ->where('kitchen_status', 'served')
                ->where('prepared_by', $user->id)
                ->today()
                ->orderByDesc('updated_at')
                ->take(20)
                ->get();
            $servedOrders = $this->filterFoodItems($servedOrders, $foodCategoryIds);
            
            // Stats for this kitchen user only (include both completed and pending sales)
            $stats = [
                'pending' => Sale::where(function($q) {
                    $q->where(function($q2) {
                        $q2->where('status', 'completed')->where('kitchen_status', 'pending');
                    })->orWhere(function($q2) {
                        $q2->where('status', 'pending')->where('kitchen_status', 'pending');
                    });
                })->today()->count(),
                'preparing' => Sale::where(function($q) {
                    $q->where('status', 'completed')->orWhere('status', 'pending');
                })->today()->where('kitchen_status', 'preparing')->where('prepared_by', $user->id)->count(),
                'ready' => Sale::today()->where('kitchen_status', 'ready')->where('prepared_by', $user->id)->count(),
                'served' => Sale::today()->where('kitchen_status', 'served')->where('prepared_by', $user->id)->count(),
            ];
        } else {
            // Managers/supervisors see all pending + preparing tickets
            // (sale status can be completed OR pending/cart)
            $pendingOrders = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer'])
                ->whereIn('status', ['completed', 'pending'])
                ->whereIn('kitchen_status', ['pending', 'preparing'])
                ->today()
                ->orderBy('created_at', 'desc')
                ->get();

            $readyOrders = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'preparedBy'])
                ->kitchenReady()
                ->today()
                ->orderBy('kitchen_ready_at', 'desc')
                ->take(10)
                ->get();
            
            // Filter to only show food items
            $pendingOrders = $this->filterFoodItems($pendingOrders, $foodCategoryIds);
            $readyOrders = $this->filterFoodItems($readyOrders, $foodCategoryIds);

            $stats = [
                'pending' => Sale::where(function($q) {
                    $q->where(function($q2) {
                        $q2->where('status', 'completed')->where('kitchen_status', 'pending');
                    })->orWhere(function($q2) {
                        $q2->where('status', 'pending')->where('kitchen_status', 'pending');
                    });
                })->today()->count(),
                'preparing' => Sale::where(function($q) {
                    $q->where('status', 'completed')->orWhere('status', 'pending');
                })->today()->where('kitchen_status', 'preparing')->count(),
                'ready' => Sale::today()->where('kitchen_status', 'ready')->count(),
                'served' => Sale::today()->where('kitchen_status', 'served')->count(),
            ];
        }

        return view('admin.kitchen.index', compact('pendingOrders', 'readyOrders', 'stats'));
    }

    /**
     * Mark order as preparing
     */
    public function preparing(Sale $sale)
    {
        if (!auth()->user()->canControlKitchenOrders()) {
            return response()->json(['error' => 'Only kitchen staff can start preparing orders.'], 403);
        }

        // Allow both completed and pending orders to be prepared
        if (!in_array($sale->status, ['completed', 'pending'])) {
            return response()->json(['error' => 'Invalid order status.'], 422);
        }

        // Assign order to current kitchen user
        $sale->markAsPreparing(auth()->id());

        AuditLog::log('kitchen_preparing', "Started preparing order #{$sale->invoice_number}", $sale);

        return response()->json([
            'success' => true,
            'message' => 'Order marked as preparing.',
        ]);
    }

    /**
     * Mark order as ready
     */
    public function ready(Sale $sale)
    {
        if (!auth()->user()->canControlKitchenOrders()) {
            return response()->json(['error' => 'Only kitchen staff can mark orders as ready.'], 403);
        }

        // Allow both completed and pending orders to be marked as ready
        if (!in_array($sale->status, ['completed', 'pending'])) {
            return response()->json(['error' => 'Invalid order status.'], 422);
        }

        $sale->markAsReady(auth()->id());

        AuditLog::log('kitchen_ready', "Marked order #{$sale->invoice_number} as ready", $sale);

        return response()->json([
            'success' => true,
            'message' => 'Order marked as ready!',
        ]);
    }

    /**
     * Mark order as served
     */
    public function served(Sale $sale)
    {
        if ($sale->kitchen_status !== 'ready') {
            return response()->json(['error' => 'Order must be ready before marking as served.'], 422);
        }

        $sale->markAsServed();

        AuditLog::log('kitchen_served', "Marked order #{$sale->invoice_number} as served", $sale);

        return response()->json([
            'success' => true,
            'message' => 'Order marked as served.',
        ]);
    }

    /**
     * Show print view for an order
     */
    public function print(Sale $sale)
    {
        $foodCategoryIds = $this->getFoodCategoryIds();
        $sale->load(['items.product:id,preparation_time,category_id', 'user', 'customer']);
        
        // Filter items to only show food items
        $sale->setRelation('items', $sale->items->filter(function($item) use ($foodCategoryIds) {
            if (!$item->product) {
                return false;
            }
            return $item->product->category_id && in_array($item->product->category_id, $foodCategoryIds);
        })->values());
        
        return view('admin.kitchen.print', compact('sale'));
    }

    /**
     * Display page to print all kitchen orders
     */
    public function printAll()
    {
        $user = auth()->user();
        $isKitchenUser = $user->isKitchen();
        
        // Get all kitchen orders for today (including both completed and pending status)
        $query = Sale::with(['items.product:id,preparation_time', 'user', 'customer', 'table', 'tableGuest'])
            ->where(function($q) {
                $q->where('status', 'completed')
                  ->orWhere('status', 'pending');
            })
            ->whereIn('kitchen_status', ['pending', 'preparing', 'ready'])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc');
        
        // If kitchen user, show only their orders
        if ($isKitchenUser) {
            $query->where(function($q) use ($user) {
                $q->where('kitchen_status', 'pending') // All pending orders
                  ->orWhere('prepared_by', $user->id); // Or orders they prepared
            });
        }
        
        $orders = $query->get();
        
        // Group orders by status
        $ordersByStatus = [
            'pending' => $orders->where('kitchen_status', 'pending'),
            'preparing' => $orders->where('kitchen_status', 'preparing'),
            'ready' => $orders->where('kitchen_status', 'ready'),
        ];
        
        return view('admin.kitchen.print-all', compact('orders', 'ordersByStatus', 'isKitchenUser'));
    }

    /**
     * Get live orders for AJAX updates
     */
    public function liveOrders()
    {
        $user = auth()->user();
        $isKitchenUser = $user->isKitchen();
        $foodCategoryIds = $this->getFoodCategoryIds();
        
        // If kitchen user, show only their orders
        if ($isKitchenUser) {
            // Pending orders: show all (including pending status sales with kitchen_status = 'pending')
            $pendingOrders = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'table', 'tableGuest'])
                ->where(function($q) {
                    $q->where(function($q2) {
                        // Completed sales with pending kitchen status
                        $q2->where('status', 'completed')
                           ->where('kitchen_status', 'pending');
                    })->orWhere(function($q2) {
                        // Pending sales (cart items) with kitchen_status = 'pending'
                        $q2->where('status', 'pending')
                           ->where('kitchen_status', 'pending');
                    });
                })
                ->today()
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Preparing orders: show only ones this kitchen user is preparing
            $preparingOrders = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'table', 'tableGuest'])
                ->where(function($q) {
                    $q->where('status', 'completed')
                      ->orWhere('status', 'pending');
                })
                ->where('kitchen_status', 'preparing')
                ->where('prepared_by', $user->id)
                ->today()
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Filter to only show food items
            $pendingOrders = $this->filterFoodItems($pendingOrders, $foodCategoryIds);
            $preparingOrders = $this->filterFoodItems($preparingOrders, $foodCategoryIds);
            
            // Combine pending and preparing
            $pendingOrders = $pendingOrders->merge($preparingOrders)->sortByDesc('created_at');
            
            // Ready orders: show only ones this kitchen user prepared
            $readyOrders = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'preparedBy', 'table', 'tableGuest'])
                ->kitchenReady()
                ->where('prepared_by', $user->id)
                ->today()
                ->orderBy('kitchen_ready_at', 'desc')
                ->take(10)
                ->get();
            
            $readyOrders = $this->filterFoodItems($readyOrders, $foodCategoryIds);
            
            // Stats for this kitchen user only (include both completed and pending sales)
            $stats = [
                'pending' => Sale::where(function($q) {
                    $q->where(function($q2) {
                        $q2->where('status', 'completed')->where('kitchen_status', 'pending');
                    })->orWhere(function($q2) {
                        $q2->where('status', 'pending')->where('kitchen_status', 'pending');
                    });
                })->today()->count(),
                'preparing' => Sale::where(function($q) {
                    $q->where('status', 'completed')->orWhere('status', 'pending');
                })->today()->where('kitchen_status', 'preparing')->where('prepared_by', $user->id)->count(),
                'ready' => Sale::today()->where('kitchen_status', 'ready')->where('prepared_by', $user->id)->count(),
                'served' => Sale::today()->where('kitchen_status', 'served')->where('prepared_by', $user->id)->count(),
            ];

            $servedOrders = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'preparedBy', 'table', 'tableGuest'])
                ->where('kitchen_status', 'served')
                ->where('prepared_by', $user->id)
                ->today()
                ->orderByDesc('updated_at')
                ->take(20)
                ->get();
            $servedOrders = $this->filterFoodItems($servedOrders, $foodCategoryIds);
        } else {
            // Managers/supervisors see all pending + preparing tickets
            // (sale status can be completed OR pending/cart)
            $pendingOrders = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'table', 'tableGuest'])
                ->whereIn('status', ['completed', 'pending'])
                ->whereIn('kitchen_status', ['pending', 'preparing'])
                ->today()
                ->orderBy('created_at', 'desc')
                ->get();

            $readyOrders = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'preparedBy', 'table', 'tableGuest'])
                ->kitchenReady()
                ->today()
                ->orderBy('kitchen_ready_at', 'desc')
                ->take(20)
                ->get();
            
            // Filter to only show food items
            $pendingOrders = $this->filterFoodItems($pendingOrders, $foodCategoryIds);
            $readyOrders = $this->filterFoodItems($readyOrders, $foodCategoryIds);

            $stats = [
                'pending' => Sale::where(function($q) {
                    $q->where(function($q2) {
                        $q2->where('status', 'completed')->where('kitchen_status', 'pending');
                    })->orWhere(function($q2) {
                        $q2->where('status', 'pending')->where('kitchen_status', 'pending');
                    });
                })->today()->count(),
                'preparing' => Sale::where(function($q) {
                    $q->where('status', 'completed')->orWhere('status', 'pending');
                })->today()->where('kitchen_status', 'preparing')->count(),
                'ready' => Sale::today()->where('kitchen_status', 'ready')->count(),
                'served' => Sale::today()->where('kitchen_status', 'served')->count(),
            ];

            $servedOrders = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'preparedBy', 'table', 'tableGuest'])
                ->where('kitchen_status', 'served')
                ->today()
                ->orderByDesc('updated_at')
                ->take(20)
                ->get();
            $servedOrders = $this->filterFoodItems($servedOrders, $foodCategoryIds);
        }

        $mapOrder = function ($sale) {
            return [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'created_at' => $sale->created_at->toDateTimeString(),
                'kitchen_ready_at' => $sale->kitchen_ready_at ? $sale->kitchen_ready_at->toDateTimeString() : null,
                'kitchen_status' => $sale->kitchen_status,
                'notes' => $sale->notes,
                'customer' => $sale->customer ? ['name' => $sale->customer->name] : null,
                'user' => $sale->user ? ['name' => $sale->user->name] : null,
                'preparedBy' => $sale->preparedBy ? ['name' => $sale->preparedBy->name] : null,
                'table' => $sale->table ? [
                    'number' => $sale->table->number ?? $sale->table->name ?? $sale->table->table_number ?? null,
                ] : null,
                'table_guest' => $sale->tableGuest ? ['guest_name' => $sale->tableGuest->guest_name] : null,
                'items' => $sale->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price ?? null,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'preparation_time' => $item->product->preparation_time,
                        ] : null,
                    ];
                }),
            ];
        };

        return response()->json([
            'pendingOrders' => $pendingOrders->map($mapOrder)->values(),
            'readyOrders' => $readyOrders->map($mapOrder)->values(),
            'servedOrders' => $servedOrders->map($mapOrder)->values(),
            'stats' => $stats,
        ]);
    }

    /**
     * Kitchen report for kitchen staff and managers
     */
    public function report(Request $request)
    {
        $user = auth()->user();
        $isKitchenUser = $user->isKitchen();
        $foodCategoryIds = $this->getFoodCategoryIds();
        $date = $request->filled('date')
            ? \Carbon\Carbon::parse($request->date)->startOfDay()
            : now()->startOfDay();

        $query = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'preparedBy'])
            ->whereIn('status', ['completed', 'pending'])
            ->whereNotNull('kitchen_status')
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc');

        if ($isKitchenUser) {
            $query->where(function ($q) use ($user) {
                $q->where('kitchen_status', 'pending')
                  ->orWhere('prepared_by', $user->id);
            });
        }

        $orders = $this->filterFoodItems($query->get(), $foodCategoryIds);

        $stats = [
            'pending' => $orders->where('kitchen_status', 'pending')->count(),
            'preparing' => $orders->where('kitchen_status', 'preparing')->count(),
            'ready' => $orders->where('kitchen_status', 'ready')->count(),
            'served' => $orders->where('kitchen_status', 'served')->count(),
            'total' => $orders->count(),
            'items' => $orders->sum(fn ($order) => $order->items->sum('quantity')),
        ];

        return view('admin.kitchen.report', compact('orders', 'stats', 'date', 'isKitchenUser'));
    }
}





