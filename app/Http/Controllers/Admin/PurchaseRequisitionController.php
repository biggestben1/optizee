<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\PurchaseRequisition;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $department = array_key_exists($request->department, PurchaseRequisition::DEPARTMENTS) ? $request->department : null;

        $query = PurchaseRequisition::with(['requestedBy', 'approvedBy']);

        if ($department) {
            $query->where('department', $department);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('pr_date', $request->date);
        }

        $prs = $query->latest('pr_date')->paginate(20)->withQueryString();

        $departmentTotals = PurchaseRequisition::where('status', '!=', 'rejected')
            ->whereMonth('pr_date', now()->month)
            ->whereYear('pr_date', now()->year)
            ->selectRaw('department, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('department')
            ->get()
            ->keyBy('department');

        return view('admin.purchase-requisitions.index', compact('prs', 'department', 'departmentTotals'));
    }

    public function create(Request $request)
    {
        $categories = Category::active()->with(['parent', 'products' => fn($q) => $q->active()])->get();
        $department = array_key_exists($request->department, PurchaseRequisition::DEPARTMENTS) ? $request->department : null;

        return view('admin.purchase-requisitions.create', compact('categories', 'department'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department' => ['required', Rule::in(array_keys(PurchaseRequisition::DEPARTMENTS))],
            'pr_date' => 'required|date',
            'description' => 'required|string|max:500',
            'items_description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'discount_received' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $pr = PurchaseRequisition::create([
            'discount_received' => $validated['discount_received'] ?? 0,
            'department' => $validated['department'],
            'pr_date' => $validated['pr_date'],
            'requested_by' => auth()->id(),
            'description' => $validated['description'],
            'items_description' => $validated['items_description'] ?? null,
            'amount' => $validated['amount'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        AuditLog::log('pr_created', "Created {$pr->department_label} Purchase Requisition {$pr->pr_number} for ₦{$pr->amount}", $pr);

        return redirect()->route('admin.purchase-requisitions.index', ['department' => $pr->department])
            ->with('success', "{$pr->department_label} purchase requisition created successfully.");
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
            'department' => ['required', Rule::in(array_keys(PurchaseRequisition::DEPARTMENTS))],
            'pr_date' => 'required|date',
            'description' => 'required|string|max:500',
            'items_description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'discount_received' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);
        $validated['discount_received'] = $validated['discount_received'] ?? 0;

        $oldValues = $purchaseRequisition->toArray();
        $purchaseRequisition->update($validated);

        AuditLog::log('pr_updated', "Updated Purchase Requisition {$purchaseRequisition->pr_number}", $purchaseRequisition, $oldValues, $purchaseRequisition->toArray());

        return redirect()->route('admin.purchase-requisitions.show', $purchaseRequisition)
            ->with('success', 'Purchase Requisition updated successfully.');
    }

    /**
     * Supplier discounts are often only known after purchase, so this works at any status except rejected.
     */
    public function updateDiscount(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        if ($purchaseRequisition->status === 'rejected') {
            return back()->with('error', 'Rejected PRs cannot have a discount recorded.');
        }

        $validated = $request->validate([
            'discount_received' => 'required|numeric|min:0',
        ]);

        $old = $purchaseRequisition->only('discount_received');
        $purchaseRequisition->update($validated);

        AuditLog::log('pr_discount_received', "Discount received of ₦{$purchaseRequisition->discount_received} on {$purchaseRequisition->pr_number}", $purchaseRequisition, $old, $purchaseRequisition->only('discount_received'));

        return back()->with('success', 'Discount received saved.');
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
