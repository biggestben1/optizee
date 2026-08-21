<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Table;
use App\Models\TableGuest;
use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TableController extends Controller
{
    public function __construct()
    {
        // Cashiers, supervisors, managers, and admins can manage tables
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user->is_admin && !$user->isManager() && !$user->isSupervisor() && !$user->isCashier()) {
                abort(403, 'You do not have permission to manage tables.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        // Sort naturally (Table 1, Table 2, ..., Table 10, not Table 1, Table 10, Table 2)
        $tables = Table::active()
            ->with(['activeGuests', 'servedBy', 'currentShift'])
            ->get()
            ->sortBy(function($table) {
                // Extract numeric part for natural sorting
                preg_match('/(\d+)/', $table->number, $matches);
                $numericPart = isset($matches[1]) ? (int)$matches[1] : 999999;
                // Return array: [numeric_part, full_string] for proper sorting
                return [$numericPart, $table->number];
            })
            ->values();
        
        return view('admin.tables.index', compact('tables'));
    }

    public function create()
    {
        return view('admin.tables.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:20|unique:tables,number',
            'name' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1|max:50',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['is_active'] = true;
        $validated['status'] = 'available';
        $validated['capacity'] = $validated['capacity'] ?? 4;

        try {
            $table = Table::create($validated);

            AuditLog::log('table_created', "Created table: {$table->number}", $table);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Table created successfully.',
                    'table' => [
                        'id' => $table->id,
                        'number' => $table->number,
                        'name' => $table->name,
                    ],
                ]);
            }

            return redirect()->route('admin.tables.index')
                ->with('success', 'Table created successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'message' => 'Failed to create table.',
                ], 422);
            }
            
            return back()->with('error', 'Failed to create table: ' . $e->getMessage());
        }
    }

    public function show(Table $table)
    {
        $table->load(['activeGuests.customer', 'activeGuests.createdBy', 'sales.items', 'servedBy']);
        
        // Get all sales for this table
        $allSales = $table->sales()
            ->with(['items.product', 'user', 'tableGuest'])
            ->completed()
            ->latest()
            ->get();
        
        // Group sales by guest
        $guestBills = $table->activeGuests->map(function ($guest) {
            $sales = $guest->sales()->completed()->with('items.product')->get();
            return [
                'guest' => $guest,
                'sales' => $sales,
                'total' => $sales->sum('total'),
                'paid' => $sales->sum('amount_paid'),
                'unpaid' => $sales->sum('total') - $sales->sum('amount_paid'),
            ];
        });

        // Combined bill (sales without guest assignment)
        $combinedSales = $table->sales()
            ->whereNull('table_guest_id')
            ->completed()
            ->with(['items.product', 'user'])
            ->get();
        
        $combinedTotal = $combinedSales->sum('total');
        $combinedPaid = $combinedSales->sum('amount_paid');

        return view('admin.tables.show', compact('table', 'guestBills', 'combinedSales', 'combinedTotal', 'combinedPaid', 'allSales'));
    }

    public function edit(Table $table)
    {
        return view('admin.tables.edit', compact('table'));
    }

    public function update(Request $request, Table $table)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:20|unique:tables,number,' . $table->id,
            'name' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1|max:50',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $oldValues = $table->toArray();
        $table->update($validated);

        AuditLog::log('table_updated', "Updated table: {$table->number}", $table, $oldValues, $table->toArray());

        return redirect()->route('admin.tables.index')
            ->with('success', 'Table updated successfully.');
    }

    public function destroy(Table $table)
    {
        if ($table->isOccupied()) {
            return back()->with('error', 'Cannot delete an occupied table. Please release it first.');
        }

        AuditLog::log('table_deleted', "Deleted table: {$table->number}", $table);
        $table->delete();

        return redirect()->route('admin.tables.index')
            ->with('success', 'Table deleted successfully.');
    }

    /**
     * Occupy a table (seat guests)
     */
    public function occupy(Request $request, Table $table)
    {
        if (!$table->isAvailable()) {
            return back()->with('error', 'Table is not available.');
        }

        $validated = $request->validate([
            'guests' => 'required|array|min:1',
            'guests.*.name' => 'required|string|max:255',
            'guests.*.customer_id' => 'nullable|exists:customers,id',
        ]);

        $shift = auth()->user()->getOpenShift();

        $table->occupy(auth()->id(), $shift?->id);

        // Create guests
        foreach ($validated['guests'] as $guestData) {
            TableGuest::create([
                'table_id' => $table->id,
                'guest_name' => $guestData['name'],
                'customer_id' => $guestData['customer_id'] ?? null,
                'created_by' => auth()->id(),
            ]);
        }

        AuditLog::log('table_occupied', "Occupied table {$table->number} with " . count($validated['guests']) . " guests", $table);

        return redirect()->route('admin.tables.show', $table)
            ->with('success', 'Table occupied successfully.');
    }

    /**
     * Add guest to existing table
     */
    public function addGuest(Request $request, $table = null)
    {
        $tableId = $request->input('table_id') ?: $table;
        $table = $table instanceof Table ? $table : Table::find($tableId);
        $wantsJson = $request->expectsJson() || $request->wantsJson() || $request->ajax()
            || $request->header('X-Requested-With') === 'XMLHttpRequest'
            || $request->filled('table_id');

        if (!$table) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a table first.',
                ], 422);
            }
            return back()->with('error', 'Please select a table first.');
        }

        $customerId = $request->input('customer_id');
        if ($customerId === '' || $customerId === 'null' || $customerId === 'undefined') {
            $request->merge(['customer_id' => null]);
            $customerId = null;
        }

        try {
            $validated = $request->validate([
                'guest_name' => 'required|string|max:255',
                'customer_id' => 'nullable|exists:customers,id',
            ]);
        } catch (ValidationException $e) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => collect($e->errors())->flatten()->first() ?: 'Invalid guest details.',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        try {
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
                'customer_id' => $validated['customer_id'] ?? $customerId,
                'created_by' => auth()->id(),
                'is_active' => true,
                'seated_at' => now(),
            ]);
            $guest->load('customer');

            try {
                AuditLog::log('guest_added', "Added guest {$validated['guest_name']} to table {$table->number}", $table);
            } catch (\Throwable $ignored) {
            }

            if ($wantsJson) {
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
                        'pending_items_count' => 0,
                        'pending_total' => 0,
                    ],
                ]);
            }

            return back()->with('success', 'Guest added successfully.');
        } catch (\Throwable $e) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add guest: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Failed to add guest: ' . $e->getMessage());
        }
    }

    /**
     * Create guest without table
     */
    public function createGuest(Request $request)
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $guest = TableGuest::create([
            'table_id' => null,
            'guest_name' => $validated['guest_name'],
            'customer_id' => $validated['customer_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        AuditLog::log('guest_created', "Created guest {$validated['guest_name']} without table", $guest);

        return response()->json([
            'success' => true,
            'message' => 'Guest created successfully.',
            'guest' => [
                'id' => $guest->id,
                'guest_name' => $guest->guest_name,
            ],
        ]);
    }

    /**
     * Get all active guests (without table requirement)
     */
    public function getAllGuests(Request $request)
    {
        $guests = TableGuest::active()
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($guest) {
                return [
                    'id' => $guest->id,
                    'guest_name' => $guest->guest_name,
                    'customer' => $guest->customer ? [
                        'id' => $guest->customer->id,
                        'name' => $guest->customer->name,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'guests' => $guests,
        ]);
    }

    /**
     * Update guest (e.g., assign customer)
     */
    public function updateGuest(Request $request, $guest)
    {
        $guest = TableGuest::find($guest);
        if (!$guest) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Guest already removed.',
                ]);
            }
            return back()->with('info', 'Guest already removed.');
        }
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $data = ['customer_id' => $validated['customer_id'] ?? null];
        
        // If a customer is assigned, update guest_name to match the customer's name
        if ($validated['customer_id']) {
            $customer = Customer::find($validated['customer_id']);
            if ($customer) {
                $data['guest_name'] = $customer->name;
            }
        }

        $guest->update($data);

        AuditLog::log('guest_updated', "Updated guest {$guest->guest_name} customer assignment", $guest);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Guest updated successfully.',
                'guest' => $guest->load('customer'),
            ]);
        }

        return back()->with('success', 'Guest updated successfully.');
    }

    /**
     * Remove guest from table (or delete guest without table)
     */
    public function removeGuest($guest)
    {
        $guest = TableGuest::find($guest);
        if (!$guest) {
            if (request()->expectsJson() || request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Guest already removed.',
                ]);
            }
            return back()->with('success', 'Guest already removed.');
        }

        try {
            DB::beginTransaction();

            $tableInfo = $guest->table ? "table {$guest->table->number}" : "without table";
            $guestName = $guest->guest_name;

            $pendingSales = $guest->sales()->where('status', 'pending')->get();
            foreach ($pendingSales as $sale) {
                $sale->items()->delete();
                $sale->delete();
            }

            if ($guest->sales()->exists()) {
                $guest->leave();
                AuditLog::log('guest_left', "Guest {$guestName} left {$tableInfo}");
                $message = 'Guest removed from the list.';
            } else {
                $guest->delete();
                AuditLog::log('guest_removed', "Removed guest {$guestName} from {$tableInfo}");
                $message = 'Guest deleted successfully.';
            }

            DB::commit();

            if (request()->expectsJson() || request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete guest: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Release table (checkout all guests)
     */
    public function release(Table $table)
    {
        if (!$table->isOccupied()) {
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Table is not occupied.',
                ], 422);
            }
            return back()->with('error', 'Table is not occupied.');
        }

        // Mark all active guests as left (this also deletes their pending orders)
        $table->activeGuests->each(function ($guest) {
            $guest->leave();
        });

        $leftoverPending = Sale::where('table_id', $table->id)->where('status', 'pending')->get();
        foreach ($leftoverPending as $sale) {
            $sale->items()->delete();
            $sale->delete();
        }

        $table->release();

        AuditLog::log('table_released', "Released table {$table->number}", $table);

        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Table released successfully.',
            ]);
        }

        return redirect()->route('admin.tables.index')
            ->with('success', 'Table released successfully.');
    }

    /**
     * Get guests for a table (AJAX)
     */
    public function getGuests(Table $table)
    {
        try {
            $guests = $table->activeGuests()->with('customer')->orderBy('id')->get();
            $pendingByGuest = Sale::query()
                ->where('status', 'pending')
                ->whereIn('table_guest_id', $guests->pluck('id')->filter())
                ->withCount('items')
                ->get()
                ->keyBy('table_guest_id');

            return response()->json([
                'table' => [
                    'id' => $table->id,
                    'number' => $table->number,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'location' => $table->location,
                    'status' => $table->status,
                ],
                'guests' => $guests->map(function ($guest) use ($pendingByGuest) {
                    $pendingSale = $pendingByGuest->get($guest->id);
                    return [
                        'id' => $guest->id,
                        'guest_name' => $guest->guest_name,
                        'customer' => $guest->customer ? [
                            'id' => $guest->customer->id,
                            'name' => $guest->customer->name,
                        ] : null,
                        'pending_items_count' => $pendingSale->items_count ?? 0,
                        'pending_total' => $pendingSale->total ?? 0,
                    ];
                }),
            ]);
        } catch (\Throwable $e) {
            $guests = $table->activeGuests()->with('customer')->orderBy('id')->get();

            return response()->json([
                'table' => [
                    'id' => $table->id,
                    'number' => $table->number,
                    'name' => $table->name,
                    'capacity' => $table->capacity,
                    'location' => $table->location,
                    'status' => $table->status,
                ],
                'guests' => $guests->map(function ($guest) {
                    return [
                        'id' => $guest->id,
                        'guest_name' => $guest->guest_name,
                        'customer' => $guest->customer ? [
                            'id' => $guest->customer->id,
                            'name' => $guest->customer->name,
                        ] : null,
                        'pending_items_count' => 0,
                        'pending_total' => 0,
                    ];
                }),
            ]);
        }
    }

    /**
     * Split bill view
     */
    public function splitBill(Table $table)
    {
        $table->load(['activeGuests.customer', 'sales.items.product']);
        
        $guestBills = $table->activeGuests->map(function ($guest) {
            $sales = $guest->sales()->completed()->with('items.product')->get();
            return [
                'guest' => $guest,
                'sales' => $sales,
                'total' => $sales->sum('total'),
            ];
        });

        return view('admin.tables.split-bill', compact('table', 'guestBills'));
    }
}

