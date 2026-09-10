<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Sale;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function live(Request $request)
    {
        $this->ensureKitchenAccess();

        $user = $request->user();
        $isKitchenUser = $user->isKitchen();
        $foodCategoryIds = $this->getFoodCategoryIds();

        if ($isKitchenUser) {
            $pendingOrders = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'table', 'tableGuest'])
                ->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('status', 'completed')->where('kitchen_status', 'pending');
                    })->orWhere(function ($q2) {
                        $q2->where('status', 'pending')->where('kitchen_status', 'pending');
                    });
                })
                ->today()
                ->orderBy('created_at', 'desc')
                ->get();

            $preparingOrders = Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'table', 'tableGuest'])
                ->whereIn('status', ['completed', 'pending'])
                ->where('kitchen_status', 'preparing')
                ->where('prepared_by', $user->id)
                ->today()
                ->orderBy('created_at', 'desc')
                ->get();

            $pendingOrders = $this->filterFoodItems($pendingOrders, $foodCategoryIds)
                ->merge($this->filterFoodItems($preparingOrders, $foodCategoryIds))
                ->sortByDesc('created_at')
                ->values();

            $readyOrders = $this->filterFoodItems(
                Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'preparedBy', 'table', 'tableGuest'])
                    ->kitchenReady()
                    ->where('prepared_by', $user->id)
                    ->today()
                    ->orderBy('kitchen_ready_at', 'desc')
                    ->take(20)
                    ->get(),
                $foodCategoryIds
            );

            $stats = [
                'pending' => Sale::where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('status', 'completed')->where('kitchen_status', 'pending');
                    })->orWhere(function ($q2) {
                        $q2->where('status', 'pending')->where('kitchen_status', 'pending');
                    });
                })->today()->count(),
                'preparing' => Sale::whereIn('status', ['completed', 'pending'])
                    ->today()->where('kitchen_status', 'preparing')->where('prepared_by', $user->id)->count(),
                'ready' => Sale::today()->where('kitchen_status', 'ready')->where('prepared_by', $user->id)->count(),
                'served' => Sale::today()->where('kitchen_status', 'served')->where('prepared_by', $user->id)->count(),
            ];
        } else {
            $pendingOrders = $this->filterFoodItems(
                Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'table', 'tableGuest'])
                    ->whereIn('status', ['completed', 'pending'])
                    ->whereIn('kitchen_status', ['pending', 'preparing'])
                    ->today()
                    ->orderBy('created_at', 'desc')
                    ->get(),
                $foodCategoryIds
            );

            $readyOrders = $this->filterFoodItems(
                Sale::with(['items.product:id,preparation_time,category_id', 'user', 'customer', 'preparedBy', 'table', 'tableGuest'])
                    ->kitchenReady()
                    ->today()
                    ->orderBy('kitchen_ready_at', 'desc')
                    ->take(20)
                    ->get(),
                $foodCategoryIds
            );

            $stats = [
                'pending' => Sale::where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('status', 'completed')->where('kitchen_status', 'pending');
                    })->orWhere(function ($q2) {
                        $q2->where('status', 'pending')->where('kitchen_status', 'pending');
                    });
                })->today()->count(),
                'preparing' => Sale::whereIn('status', ['completed', 'pending'])->today()->where('kitchen_status', 'preparing')->count(),
                'ready' => Sale::today()->where('kitchen_status', 'ready')->count(),
                'served' => Sale::today()->where('kitchen_status', 'served')->count(),
            ];
        }

        $mapOrder = function ($sale) {
            return [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'created_at' => $sale->created_at?->toDateTimeString(),
                'kitchen_ready_at' => $sale->kitchen_ready_at?->toDateTimeString(),
                'kitchen_status' => $sale->kitchen_status,
                'notes' => $sale->notes,
                'customer' => $sale->customer ? ['name' => $sale->customer->name] : null,
                'user' => $sale->user ? ['name' => $sale->user->name] : null,
                'prepared_by' => $sale->preparedBy ? ['name' => $sale->preparedBy->name] : null,
                'table' => $sale->table ? ['number' => $sale->table->number ?? null] : null,
                'table_guest' => $sale->tableGuest ? ['guest_name' => $sale->tableGuest->guest_name] : null,
                'items' => $sale->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => $item->unit_price !== null ? (float) $item->unit_price : null,
                ])->values(),
            ];
        };

        return response()->json([
            'pending_orders' => $pendingOrders->map($mapOrder)->values(),
            'ready_orders' => $readyOrders->map($mapOrder)->values(),
            'stats' => $stats,
            'can_control' => $user->canControlKitchenOrders(),
        ]);
    }

    public function preparing(Sale $sale)
    {
        $this->ensureKitchenAccess();

        if (!auth()->user()->canControlKitchenOrders()) {
            return response()->json(['message' => 'Only kitchen staff can start preparing orders.'], 403);
        }

        if (!in_array($sale->status, ['completed', 'pending'], true)) {
            return response()->json(['message' => 'Invalid order status.'], 422);
        }

        $sale->markAsPreparing(auth()->id());
        AuditLog::log('kitchen_preparing', "Started preparing order #{$sale->invoice_number}", $sale);

        return response()->json(['success' => true, 'message' => 'Order marked as preparing.']);
    }

    public function ready(Sale $sale)
    {
        $this->ensureKitchenAccess();

        if (!auth()->user()->canControlKitchenOrders()) {
            return response()->json(['message' => 'Only kitchen staff can mark orders as ready.'], 403);
        }

        if (!in_array($sale->status, ['completed', 'pending'], true)) {
            return response()->json(['message' => 'Invalid order status.'], 422);
        }

        $sale->markAsReady(auth()->id());
        AuditLog::log('kitchen_ready', "Marked order #{$sale->invoice_number} as ready", $sale);

        return response()->json(['success' => true, 'message' => 'Order marked as ready!']);
    }

    public function served(Sale $sale)
    {
        $this->ensureKitchenAccess();

        if ($sale->kitchen_status !== 'ready') {
            return response()->json(['message' => 'Order must be ready before marking as served.'], 422);
        }

        $sale->markAsServed();
        AuditLog::log('kitchen_served', "Marked order #{$sale->invoice_number} as served", $sale);

        return response()->json(['success' => true, 'message' => 'Order marked as served.']);
    }

    /**
     * Match PosController kitchen keywords so flagged sales actually appear on the board.
     * Also includes children of matched categories (e.g. Food → Soups).
     */
    protected function getFoodCategoryIds(): array
    {
        $kitchenKeywords = [
            'food', 'kitchen', 'meal', 'dish', 'main', 'appetizer', 'starter',
            'soup', 'salad', 'dessert', 'breakfast', 'lunch', 'dinner', 'snack',
        ];

        $categories = Category::active()
            ->where(function ($q) use ($kitchenKeywords) {
                foreach ($kitchenKeywords as $keyword) {
                    $q->orWhere('name', 'like', "%{$keyword}%");
                }
            })
            ->get();

        $ids = $categories->pluck('id')->all();

        foreach ($categories as $category) {
            $ids = array_merge($ids, $category->children()->pluck('id')->all());
        }

        return array_values(array_unique($ids));
    }

    protected function filterFoodItems($sales, array $foodCategoryIds)
    {
        // If no kitchen categories are configured, still show kitchen-flagged sales
        // rather than an empty board.
        if (empty($foodCategoryIds)) {
            return collect($sales)->values();
        }

        return $sales->map(function ($sale) use ($foodCategoryIds) {
            if (!$sale->relationLoaded('items')) {
                $sale->load('items.product');
            }

            $sale->setRelation('items', $sale->items->filter(function ($item) use ($foodCategoryIds) {
                if (!$item->product) {
                    return false;
                }
                if (!$item->product->relationLoaded('category')) {
                    $item->product->load('category');
                }
                return $item->product->category_id && in_array($item->product->category_id, $foodCategoryIds, true);
            })->values());

            return $sale;
        })->filter(fn ($sale) => $sale->items && $sale->items->count() > 0)->values();
    }

    protected function ensureKitchenAccess(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->is_admin && !$user->canAccessKitchen())) {
            abort(403, 'You do not have permission to access the kitchen.');
        }
    }
}
