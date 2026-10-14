<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RefundController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user->is_admin && !$user->isManager() && !$user->isAccountant()) {
                abort(403, 'You do not have permission to manage refunds.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : now();

        $query = Refund::with('user')
            ->whereBetween('refund_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }

        $refunds = $query->latest('refund_date')->latest('id')->get();

        $byArea = collect(Refund::AREAS)->map(fn($label, $key) => [
            'label' => $label,
            'total' => $refunds->where('area', $key)->sum('amount'),
            'count' => $refunds->where('area', $key)->count(),
        ]);

        $byMethod = collect(Refund::PAYMENT_METHODS)->map(fn($label, $key) => $refunds->where('payment_method', $key)->sum('amount'));

        return view('admin.refunds.index', compact('refunds', 'byArea', 'byMethod', 'startDate', 'endDate'));
    }

    public function create(Request $request)
    {
        return view('admin.refunds.create', [
            'areas' => Refund::AREAS,
            'methods' => Refund::PAYMENT_METHODS,
            'selectedArea' => $request->query('area'),
            'reference' => $request->query('reference'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'refund_date' => 'required|date',
            'area' => ['required', Rule::in(array_keys(Refund::AREAS))],
            'reference' => 'nullable|string|max:100',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:30',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => ['required', Rule::in(array_keys(Refund::PAYMENT_METHODS))],
            'reason' => 'required|string|max:1000',
        ]);

        $refund = Refund::create($validated + ['user_id' => auth()->id()]);

        AuditLog::log('refund_created', "Refund {$refund->refund_number} ({$refund->area_label}) of ₦{$refund->amount}", $refund);

        return redirect()->route('admin.refunds.index')
            ->with('success', "Refund {$refund->refund_number} recorded.");
    }

    public function destroy(Refund $refund)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Only administrators can delete refunds.');
        }

        AuditLog::log('refund_deleted', "Deleted refund {$refund->refund_number} of ₦{$refund->amount}", $refund);
        $refund->delete();

        return back()->with('success', 'Refund deleted.');
    }
}
