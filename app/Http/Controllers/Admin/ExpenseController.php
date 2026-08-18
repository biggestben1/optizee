<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin && !auth()->user()->isManager() && !auth()->user()->isAccountant()) {
                abort(403, 'You do not have permission to access expenses.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = Expense::with('user');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }

        // Default to current month if no dates provided
        if (!$request->filled('start_date') && !$request->filled('end_date')) {
            $query->whereMonth('expense_date', now()->month)
                  ->whereYear('expense_date', now()->year);
        }

        $expenses = $query->latest('expense_date')->paginate(20);

        // Calculate totals
        $totalAmount = $query->sum('amount');
        $totalByCategory = Expense::selectRaw('category, SUM(amount) as total')
            ->where(function($q) use ($request) {
                if ($request->filled('start_date')) {
                    $q->whereDate('expense_date', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $q->whereDate('expense_date', '<=', $request->end_date);
                }
                if (!$request->filled('start_date') && !$request->filled('end_date')) {
                    $q->whereMonth('expense_date', now()->month)
                      ->whereYear('expense_date', now()->year);
                }
            })
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category');

        $categories = Expense::getCategories();

        return view('admin.expenses.index', compact('expenses', 'totalAmount', 'totalByCategory', 'categories'));
    }

    public function create()
    {
        $categories = Expense::getCategories();
        return view('admin.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Remove commas from amount before validation
        $request->merge([
            'amount' => str_replace(',', '', $request->amount ?? '')
        ]);

        $validated = $request->validate([
            'expense_date' => 'required|date',
            'category' => 'required|string|in:' . implode(',', array_keys(Expense::getCategories())),
            'description' => 'required|string|max:500',
            'items_description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,transfer,pos',
            'vendor' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'receipt_number' => 'nullable|string|max:100',
            'is_direct_cost' => 'boolean',
        ]);

        // Amount is already cleaned (commas removed)
        $amount = $validated['amount'];
        
        $expense = Expense::create([
            'expense_date' => $validated['expense_date'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'items_description' => $validated['items_description'] ?? null,
            'amount' => $amount,
            'is_direct_cost' => $request->has('is_direct_cost'),
            'payment_method' => $validated['payment_method'],
            'vendor' => $validated['vendor'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'receipt_number' => $validated['receipt_number'] ?? null,
            'user_id' => auth()->id(),
        ]);

        AuditLog::log('expense_created', "Created expense {$expense->expense_number} for ₦{$expense->amount} - {$expense->category}", $expense);

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    public function show(Expense $expense)
    {
        $expense->load('user');
        return view('admin.expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $categories = Expense::getCategories();
        return view('admin.expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        // Remove commas from amount before validation
        $request->merge([
            'amount' => str_replace(',', '', $request->amount ?? '')
        ]);

        $validated = $request->validate([
            'expense_date' => 'required|date',
            'category' => 'required|string|in:' . implode(',', array_keys(Expense::getCategories())),
            'description' => 'required|string|max:500',
            'items_description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,transfer,pos',
            'vendor' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'receipt_number' => 'nullable|string|max:100',
            'is_direct_cost' => 'boolean',
        ]);

        // Amount is already cleaned (commas removed)
        
        $oldValues = $expense->toArray();
        $validated['is_direct_cost'] = $request->has('is_direct_cost');
        $expense->update($validated);

        AuditLog::log('expense_updated', "Updated expense {$expense->expense_number}", $expense, $oldValues, $expense->toArray());

        return redirect()->route('admin.expenses.show', $expense)
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expenseNumber = $expense->expense_number;
        $expense->delete();

        AuditLog::log('expense_deleted', "Deleted expense {$expenseNumber}", $expense);

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}
