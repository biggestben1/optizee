<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\PurchaseRequisition;
use Illuminate\Http\Request;

class PurchaseRequisitionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin && !auth()->user()->isManager() && !auth()->user()->isAccountant()) {
                abort(403, 'You do not have permission to access purchase requisitions.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = PurchaseRequisition::with(['requestedBy', 'approvedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('pr_date', $request->date);
        }

        $prs = $query->latest('pr_date')->paginate(20);

        return view('admin.purchase-requisitions.index', compact('prs'));
    }

    public function create()
    {
        $categories = Category::active()->with(['products' => fn($q) => $q->active()])->get();
        return view('admin.purchase-requisitions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pr_date' => 'required|date',
            'description' => 'required|string|max:500',
            'items_description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ]);

        $pr = PurchaseRequisition::create([
            'pr_date' => $validated['pr_date'],
            'requested_by' => auth()->id(),
            'description' => $validated['description'],
            'items_description' => $validated['items_description'],
            'amount' => $validated['amount'],
            'notes' => $validated['notes'],
            'status' => 'pending',
        ]);

        AuditLog::log('pr_created', "Created Purchase Requisition {$pr->pr_number} for ₦{$pr->amount}", $pr);

        return redirect()->route('admin.purchase-requisitions.index')
            ->with('success', 'Purchase Requisition created successfully.');
    }

    public function show(PurchaseRequisition $purchaseRequisition)
    {
        $purchaseRequisition->load(['requestedBy', 'approvedBy']);
        return view('admin.purchase-requisitions.show', compact('purchaseRequisition'));
    }

    public function edit(PurchaseRequisition $purchaseRequisition)
    {
        if ($purchaseRequisition->status !== 'pending') {
            return redirect()->route('admin.purchase-requisitions.show', $purchaseRequisition)
                ->with('error', 'Only pending PRs can be edited.');
        }

        return view('admin.purchase-requisitions.edit', compact('purchaseRequisition'));
    }

    public function update(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        if ($purchaseRequisition->status !== 'pending') {
            return redirect()->route('admin.purchase-requisitions.show', $purchaseRequisition)
                ->with('error', 'Only pending PRs can be edited.');
        }

        $validated = $request->validate([
            'pr_date' => 'required|date',
            'description' => 'required|string|max:500',
            'items_description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldValues = $purchaseRequisition->toArray();
        $purchaseRequisition->update($validated);

        AuditLog::log('pr_updated', "Updated Purchase Requisition {$purchaseRequisition->pr_number}", $purchaseRequisition, $oldValues, $purchaseRequisition->toArray());

        return redirect()->route('admin.purchase-requisitions.show', $purchaseRequisition)
            ->with('success', 'Purchase Requisition updated successfully.');
    }

    public function approve(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        if ($purchaseRequisition->status !== 'pending') {
            return back()->with('error', 'Only pending PRs can be approved.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $purchaseRequisition->approve(auth()->id(), $validated['notes'] ?? null);

        AuditLog::log('pr_approved', "Approved Purchase Requisition {$purchaseRequisition->pr_number}", $purchaseRequisition);

        return back()->with('success', 'Purchase Requisition approved successfully.');
    }

    public function reject(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        if ($purchaseRequisition->status !== 'pending') {
            return back()->with('error', 'Only pending PRs can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $purchaseRequisition->reject(auth()->id(), $validated['rejection_reason']);

        AuditLog::log('pr_rejected', "Rejected Purchase Requisition {$purchaseRequisition->pr_number}: {$validated['rejection_reason']}", $purchaseRequisition);

        return back()->with('success', 'Purchase Requisition rejected.');
    }

    public function complete(PurchaseRequisition $purchaseRequisition)
    {
        if ($purchaseRequisition->status !== 'approved') {
            return back()->with('error', 'Only approved PRs can be marked as completed.');
        }

        $purchaseRequisition->update(['status' => 'completed']);

        AuditLog::log('pr_completed', "Marked Purchase Requisition {$purchaseRequisition->pr_number} as completed", $purchaseRequisition);

        return back()->with('success', 'Purchase Requisition marked as completed.');
    }
}
