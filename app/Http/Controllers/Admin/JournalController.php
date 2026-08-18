<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin && !auth()->user()->isManager() && !auth()->user()->isAccountant()) {
                abort(403, 'You do not have permission to access journal entries.');
            }
            return $next($request);
        });
    }
    public function index(Request $request)
    {
        $query = JournalEntry::with(['user', 'items']);

        if ($request->filled('search')) {
            $query->where('reference_number', 'like', "%{$request->search}%")
                ->orWhere('description', 'like', "%{$request->search}%");
        }

        $entries = $query->latest()->paginate(15);

        return view('admin.journals.index', compact('entries'));
    }

    public function create()
    {
        $suppliers = Supplier::active()->get();
        return view('admin.journals.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string',
            'items' => 'required|array|min:2',
            'items.*.account_type' => 'required|string',
            'items.*.account_id' => 'nullable|integer',
            'items.*.debit' => 'required|numeric|min:0',
            'items.*.credit' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ]);

        // Check if debits equal credits
        $totalDebit = collect($request->items)->sum('debit');
        $totalCredit = collect($request->items)->sum('credit');

        if (number_format($totalDebit, 2) !== number_format($totalCredit, 2)) {
            return back()->withInput()->with('error', 'Total debits must equal total credits.');
        }

        DB::beginTransaction();
        try {
            $entry = JournalEntry::create([
                'entry_date' => $validated['entry_date'],
                'description' => $validated['description'],
                'user_id' => auth()->id(),
                'status' => 'posted',
            ]);

            foreach ($validated['items'] as $itemData) {
                $item = $entry->items()->create($itemData);

                // Handle supplier balance if account_type is Supplier
                if ($itemData['account_type'] === 'Supplier' && !empty($itemData['account_id'])) {
                    $supplier = Supplier::find($itemData['account_id']);
                    if ($supplier) {
                        // For a supplier (Liability): 
                        // Credit increases balance (we owe more)
                        // Debit decreases balance (we owe less)
                        if ($itemData['credit'] > 0) {
                            $supplier->addToBalance($itemData['credit']);
                        }
                        if ($itemData['debit'] > 0) {
                            $supplier->reduceBalance($itemData['debit']);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.journals.index')->with('success', 'Journal entry posted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error posting journal entry: ' . $e->getMessage());
        }
    }

    public function show(JournalEntry $journal)
    {
        $journal->load(['user', 'items.account']);
        return view('admin.journals.show', compact('journal'));
    }
}
