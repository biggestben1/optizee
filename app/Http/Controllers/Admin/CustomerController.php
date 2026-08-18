<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Shift;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'credit') {
                $query->withCredit();
            } elseif ($request->status === 'owing') {
                $query->withOutstandingBalance();
            }
        }

        $customers = $query->latest()->paginate(15);
        $totalDebt = Customer::sum('credit_balance');

        return view('admin.customers.index', compact('customers', 'totalDebt'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        // Remove commas from credit_limit before validation
        if ($request->has('credit_limit') && !empty($request->credit_limit)) {
            $request->merge(['credit_limit' => str_replace(',', '', $request->credit_limit)]);
        } else {
            $request->merge(['credit_limit' => '0']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_enabled' => 'boolean',
        ]);

        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
        $validated['credit_enabled'] = $request->boolean('credit_enabled');
        $validated['created_by'] = auth()->id();

        $customer = Customer::create($validated);

        AuditLog::log('customer_created', "Created customer: {$customer->name}", $customer);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['sales' => fn($q) => $q->latest()->take(10), 'payments' => fn($q) => $q->latest()->take(10)]);
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        // Remove commas from credit_limit before validation
        if ($request->has('credit_limit') && !empty($request->credit_limit)) {
            $request->merge(['credit_limit' => str_replace(',', '', $request->credit_limit)]);
        } else {
            $request->merge(['credit_limit' => '0']);
        }

        // Support partial JSON updates from POS (credit activation modal)
        // The POS needs to toggle credit without re-submitting full customer profile.
        if ($request->expectsJson() && $request->boolean('is_ajax_pos')) {
            $validated = $request->validate([
                'credit_limit' => 'nullable|numeric|min:0',
                'credit_enabled' => 'nullable|boolean',
            ]);

            $oldValues = $customer->toArray();
            $customer->update([
                'credit_limit' => $validated['credit_limit'] ?? $customer->credit_limit,
                'credit_enabled' => $request->boolean('credit_enabled', $customer->credit_enabled),
            ]);

            AuditLog::log('customer_updated', "Updated customer credit: {$customer->name}", $customer, $oldValues, $customer->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Customer credit updated successfully.',
                'customer' => $customer,
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'credit_enabled' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['credit_limit'] = $validated['credit_limit'] ?? $customer->credit_limit;
        $validated['credit_enabled'] = $request->boolean('credit_enabled');
        $validated['is_active'] = $request->boolean('is_active', true);

        $oldValues = $customer->toArray();
        $customer->update($validated);

        AuditLog::log('customer_updated', "Updated customer: {$customer->name}", $customer, $oldValues, $customer->toArray());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully.',
                'customer' => $customer
            ]);
        }

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function receivePayment(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $customer->credit_balance,
            'payment_method' => 'required|in:cash,transfer,pos',
            'notes' => 'nullable|string|max:500',
        ]);

        $balanceBefore = $customer->credit_balance;
        $customer->reduceBalance($validated['amount']);

        // Get current open shift
        $shift = Shift::open()->where('user_id', auth()->id())->first();

        CustomerPayment::create([
            'customer_id' => $customer->id,
            'user_id' => auth()->id(),
            'shift_id' => $shift?->id,
            'amount' => $validated['amount'],
            'balance_before' => $balanceBefore,
            'balance_after' => $customer->credit_balance,
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'],
        ]);

        AuditLog::log('customer_payment', "Received payment of {$validated['amount']} from {$customer->name}", $customer);

        return back()->with('success', 'Payment received successfully.');
    }

    public function statement(Customer $customer)
    {
        $sales = $customer->sales()->with('items')->latest()->get();
        $payments = $customer->payments()->with('user')->latest()->get();

        return view('admin.customers.statement', compact('customer', 'sales', 'payments'));
    }

    public function destroy(Customer $customer)
    {
        if ($customer->credit_balance > 0) {
            return back()->with('error', 'Cannot delete customer with outstanding balance.');
        }

        if ($customer->sales()->count() > 0) {
            return back()->with('error', 'Cannot delete customer with sales history.');
        }

        AuditLog::log('customer_deleted', "Deleted customer: {$customer->name}", $customer);
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}


