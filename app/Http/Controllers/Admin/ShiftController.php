<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Shift;
use App\Models\StaffAssignment;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $query = Shift::with(['user.role', 'user.currentAssignments.section', 'closedBy']);

        if ($request->filled('date')) {
            $query->whereDate('opened_at', $request->date);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $shifts = $query->latest('opened_at')->paginate(15);

        // Get current staff on duty (those with open shifts)
        $staffOnDuty = Shift::with(['user.role', 'user.currentAssignments.section'])
            ->open()
            ->get()
            ->map(function ($shift) {
                $assignments = $shift->user->currentAssignments;
                return [
                    'shift' => $shift,
                    'user' => $shift->user,
                    'sections' => $assignments->pluck('section')->unique('id'),
                    'assignments' => $assignments,
                ];
            });

        // Get all current assignments for today grouped by section
        $currentAssignments = StaffAssignment::with(['user.role', 'section'])
            ->current()
            ->get()
            ->groupBy('section_id');

        return view('admin.shifts.index', compact('shifts', 'staffOnDuty', 'currentAssignments'));
    }

    public function open(Request $request)
    {
        // Check if user already has open shift
        if (auth()->user()->hasOpenShift()) {
            return back()->with('error', 'You already have an open shift.');
        }

        $validated = $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $shift = Shift::create([
            'user_id' => auth()->id(),
            'opening_cash' => $validated['opening_cash'],
            'opened_at' => now(),
            'notes' => $validated['notes'],
            'status' => 'open',
        ]);

        AuditLog::log('shift_opened', "Opened shift with {$validated['opening_cash']} opening cash", $shift);

        return redirect()->route('admin.pos.index')
            ->with('success', 'Shift opened successfully.');
    }

    public function close(Request $request, Shift $shift)
    {
        // Verify shift belongs to current user or user is supervisor/manager
        if ($shift->user_id !== auth()->id() && !auth()->user()->canVoidSales()) {
            return back()->with('error', 'You can only close your own shift.');
        }

        if ($shift->status === 'closed') {
            return back()->with('error', 'This shift is already closed.');
        }

        $validated = $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $shift->close($validated['closing_cash'], auth()->id(), $validated['notes']);

        AuditLog::log('shift_closed', "Closed shift with {$validated['closing_cash']} closing cash", $shift);

        return redirect()->route('admin.shifts.index')
            ->with('success', 'Shift closed successfully.');
    }

    public function show(Shift $shift)
    {
        $shift->load(['user', 'closedBy', 'sales.items', 'customerPayments.customer']);

        $summary = [
            'total_sales' => $shift->sales()->completed()->sum('total'),
            'total_transactions' => $shift->sales()->completed()->count(),
            'cash_sales' => $shift->sales()->completed()->where('payment_method', 'cash')->sum('total'),
            'transfer_sales' => $shift->sales()->completed()->where('payment_method', 'transfer')->sum('total'),
            'pos_sales' => $shift->sales()->completed()->where('payment_method', 'pos')->sum('total'),
            'credit_sales' => $shift->sales()->completed()->where('is_credit_sale', true)->sum('total'),
            'voided_sales' => $shift->sales()->voided()->sum('total'),
            'customer_payments' => $shift->customerPayments()->sum('amount'),
        ];

        return view('admin.shifts.show', compact('shift', 'summary'));
    }

    public function current()
    {
        $shift = auth()->user()->getOpenShift();

        if (!$shift) {
            return redirect()->route('admin.shifts.create')
                ->with('info', 'Please open a shift to continue.');
        }

        return $this->show($shift);
    }

    public function create()
    {
        if (auth()->user()->hasOpenShift()) {
            return redirect()->route('admin.shifts.current');
        }

        return view('admin.shifts.create');
    }
}


