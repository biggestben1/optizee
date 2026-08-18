<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\OwnerPurchase;
use Illuminate\Http\Request;

class OwnerPurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin) {
                abort(403, 'Only the owner/admin can access owner purchases.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = OwnerPurchase::with('user');

        if ($request->filled('start_date')) {
            $query->whereDate('purchase_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('purchase_date', '<=', $request->end_date);
        }

        $purchases = $query->latest('purchase_date')->paginate(20);

        $totalAmount = OwnerPurchase::sum('amount');

        return view('admin.owner-purchases.index', compact('purchases', 'totalAmount'));
    }

    public function create()
    {
        $categories = Category::active()->with(['products' => fn($q) => $q->active()])->get();
        return view('admin.owner-purchases.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'description' => 'required|string|max:500',
            'items_description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'vendor' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,transfer,pos,credit',
            'notes' => 'nullable|string|max:1000',
        ]);

        $purchase = OwnerPurchase::create([
            'purchase_date' => $validated['purchase_date'],
            'user_id' => auth()->id(),
            'description' => $validated['description'],
            'items_description' => $validated['items_description'],
            'amount' => $validated['amount'],
            'vendor' => $validated['vendor'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'],
        ]);

        AuditLog::log('owner_purchase_created', "Owner purchase {$purchase->purchase_number} for ₦{$purchase->amount}", $purchase);

        return redirect()->route('admin.owner-purchases.index')
            ->with('success', 'Owner purchase recorded successfully.');
    }

    public function show(OwnerPurchase $ownerPurchase)
    {
        $ownerPurchase->load('user');
        return view('admin.owner-purchases.show', compact('ownerPurchase'));
    }

    public function edit(OwnerPurchase $ownerPurchase)
    {
        $categories = Category::active()->with(['products' => fn($q) => $q->active()])->get();
        return view('admin.owner-purchases.edit', compact('ownerPurchase', 'categories'));
    }

    public function update(Request $request, OwnerPurchase $ownerPurchase)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'description' => 'required|string|max:500',
            'items_description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'vendor' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,transfer,pos,credit',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldValues = $ownerPurchase->toArray();
        $ownerPurchase->update($validated);

        AuditLog::log('owner_purchase_updated', "Updated owner purchase {$ownerPurchase->purchase_number}", $ownerPurchase, $oldValues, $ownerPurchase->toArray());

        return redirect()->route('admin.owner-purchases.show', $ownerPurchase)
            ->with('success', 'Owner purchase updated successfully.');
    }

    public function destroy(OwnerPurchase $ownerPurchase)
    {
        AuditLog::log('owner_purchase_deleted', "Deleted owner purchase {$ownerPurchase->purchase_number}", $ownerPurchase);
        
        $ownerPurchase->delete();

        return redirect()->route('admin.owner-purchases.index')
            ->with('success', 'Owner purchase deleted successfully.');
    }
}
