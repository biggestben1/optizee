<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\HallBooking;
use App\Models\HallOccasion;
use App\Support\PaymentSplit;
use Illuminate\Http\Request;

class HallRentalController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->canAccessHotelPOS()) {
                abort(403, 'You do not have permission to access hall rentals.');
            }

            return $next($request);
        });

        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user->is_admin && !$user->isManager()) {
                abort(403, 'Only administrators and managers can change hall occasions and prices.');
            }

            return $next($request);
        })->only(['storeOccasion', 'updateOccasion', 'destroyOccasion']);
    }

    public function index(Request $request)
    {
        $query = HallBooking::with(['user', 'occasion']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('event_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('event_date', '<=', $request->end_date);
        }

        $bookings = $query->latest()->paginate(20)->withQueryString();

        $todayBookings = HallBooking::notCancelled()->whereDate('created_at', today())->get();
        $todaySummary = [
            'count' => $todayBookings->count(),
            'total' => $todayBookings->sum('total'),
            'cash' => $todayBookings->sum('cash_amount'),
            'transfer' => $todayBookings->sum('transfer_amount'),
            'pos' => $todayBookings->sum('pos_amount'),
        ];

        $upcoming = HallBooking::notCancelled()
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->take(10)
            ->get();

        $occasions = HallOccasion::orderBy('name')->get();

        return view('admin.hall-rentals.index', compact('bookings', 'todaySummary', 'upcoming', 'occasions'));
    }

    public function create()
    {
        $occasions = HallOccasion::active()->orderBy('name')->get();

        return view('admin.hall-rentals.create', compact('occasions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hall_occasion_id' => 'required|exists:hall_occasions,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'customer_email' => 'nullable|email|max:255',
            'event_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'expected_guests' => 'nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer,pos,mixed',
            'amount_paid' => 'nullable|numeric|min:0',
            'mixed_cash' => 'nullable|numeric|min:0',
            'mixed_transfer' => 'nullable|numeric|min:0',
            'mixed_pos' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $occasion = HallOccasion::findOrFail($validated['hall_occasion_id']);
        $price = (float) $validated['price'];
        $discount = (float) ($validated['discount'] ?? 0);
        $total = max(0, $price - $discount);

        $payment = PaymentSplit::resolve(
            $validated['payment_method'],
            $total,
            (float) ($validated['amount_paid'] ?? 0),
            [
                'cash' => $validated['mixed_cash'] ?? 0,
                'transfer' => $validated['mixed_transfer'] ?? 0,
                'pos' => $validated['mixed_pos'] ?? 0,
            ]
        );

        if ($payment['amount_paid'] < $total) {
            return back()->withInput()->with('error', 'Amount paid (₦' . number_format($payment['amount_paid'], 2) . ') is less than the total (₦' . number_format($total, 2) . ').');
        }

        $booking = HallBooking::create(array_merge([
            'hall_occasion_id' => $occasion->id,
            'occasion_name' => $occasion->name,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'] ?? null,
            'event_date' => $validated['event_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'expected_guests' => $validated['expected_guests'] ?? null,
            'price' => $price,
            'discount' => $discount,
            'total' => $total,
            'payment_method' => $validated['payment_method'],
            'status' => 'confirmed',
            'notes' => $validated['notes'] ?? null,
            'user_id' => auth()->id(),
        ], $payment));

        AuditLog::log('hall_booking_created', "Hall booking {$booking->booking_number} ({$booking->occasion_name}) for ₦{$total}", $booking);

        return redirect()->route('admin.hall-rentals.show', $booking)
            ->with('success', 'Hall booking created successfully.');
    }

    public function show(HallBooking $hallBooking)
    {
        $hallBooking->load(['user', 'occasion', 'cancelledBy']);

        return view('admin.hall-rentals.receipt', ['booking' => $hallBooking]);
    }

    public function cancel(Request $request, HallBooking $hallBooking)
    {
        if ($hallBooking->isCancelled()) {
            return back()->with('error', 'This hall booking is already cancelled.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $hallBooking->update([
            'status' => 'cancelled',
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['reason'],
        ]);

        AuditLog::log('hall_booking_cancelled', "Hall booking {$hallBooking->booking_number} cancelled: {$validated['reason']}", $hallBooking);

        return back()->with('success', 'Hall booking cancelled. It will no longer count in income reports. Record a refund if money was returned.');
    }

    public function storeOccasion(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:hall_occasions,name',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $occasion = HallOccasion::create($validated + ['is_active' => true]);

        AuditLog::log('hall_occasion_created', "Hall occasion {$occasion->name} added at ₦{$occasion->price}", $occasion);

        return back()->with('success', "Occasion \"{$occasion->name}\" added.");
    }

    public function updateOccasion(Request $request, HallOccasion $hallOccasion)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:hall_occasions,name,' . $hallOccasion->id,
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $old = $hallOccasion->toArray();
        $hallOccasion->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLog::log('hall_occasion_updated', "Hall occasion {$hallOccasion->name} updated", $hallOccasion, $old, $hallOccasion->toArray());

        return back()->with('success', "Occasion \"{$hallOccasion->name}\" updated.");
    }

    public function destroyOccasion(HallOccasion $hallOccasion)
    {
        if ($hallOccasion->bookings()->exists()) {
            $hallOccasion->update(['is_active' => false]);

            return back()->with('success', "\"{$hallOccasion->name}\" has bookings, so it was deactivated instead of deleted.");
        }

        AuditLog::log('hall_occasion_deleted', "Hall occasion {$hallOccasion->name} deleted", $hallOccasion);
        $hallOccasion->delete();

        return back()->with('success', 'Occasion deleted.');
    }
}
