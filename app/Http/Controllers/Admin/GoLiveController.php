<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\TableGuest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoLiveController extends Controller
{
    public const DEFAULT_BOOKING_CUTOFF = '2026-10-02';

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin) {
                abort(403, 'Only administrators can access go-live tools.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $cutoff = $request->input('cutoff', self::DEFAULT_BOOKING_CUTOFF);

        $bookingsQuery = RoomBooking::with(['room', 'customer'])->latest();
        if ($request->filled('booking_search')) {
            $search = $request->booking_search;
            $bookingsQuery->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                    ->orWhere('guest_name', 'like', "%{$search}%")
                    ->orWhere('guest_phone', 'like', "%{$search}%");
            });
        }

        return view('admin.go-live.index', [
            'counts' => $this->getCounts(),
            'cutoff' => $cutoff,
            'bookingsBeforeCutoff' => $this->bookingsUpTo($cutoff)->count(),
            'bookings' => $bookingsQuery->paginate(15, ['*'], 'bookings_page')->withQueryString(),
        ]);
    }

    public function clearOrders(Request $request)
    {
        return $this->performClear($request, ['orders']);
    }

    public function clearCustomers(Request $request)
    {
        return $this->performClear($request, ['customers']);
    }

    public function clearAll(Request $request)
    {
        return $this->performClear($request, ['orders', 'customers']);
    }

    public function cancelBooking(Request $request, RoomBooking $roomBooking)
    {
        if ($roomBooking->status === 'cancelled') {
            return back()->with('error', "Booking {$roomBooking->booking_number} is already cancelled.");
        }

        DB::transaction(function () use ($request, $roomBooking) {
            $roomBooking->update([
                'status' => 'cancelled',
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
                'cancellation_reason' => $request->input('reason') ?: 'Cancelled from Go Live',
            ]);

            if ($roomBooking->is_credit_booking && $roomBooking->customer) {
                $roomBooking->customer->reduceBalance($roomBooking->total);
            }

            $this->releaseRooms([$roomBooking->room_id]);
        });

        AuditLog::log('go_live_booking_cancelled', "Go Live: cancelled room booking {$roomBooking->booking_number}", $roomBooking);

        return back()->with('success', "Booking {$roomBooking->booking_number} cancelled. The room is free and it no longer counts in reports.");
    }

    public function deleteBooking(RoomBooking $roomBooking)
    {
        $number = $roomBooking->booking_number;
        $this->deleteBookings(RoomBooking::whereKey($roomBooking->id));

        AuditLog::log('go_live_booking_deleted', "Go Live: deleted room booking {$number}");

        return back()->with('success', "Booking {$number} deleted.");
    }

    public function deleteBookingsUpTo(Request $request)
    {
        $validated = $request->validate([
            'cutoff' => 'required|date',
        ]);

        $cutoff = Carbon::parse($validated['cutoff'])->toDateString();
        $deleted = $this->deleteBookings($this->bookingsUpTo($cutoff));

        AuditLog::log('go_live_bookings_deleted', "Go Live: deleted {$deleted} room booking(s) made on or before {$cutoff}");

        return redirect()->route('admin.go-live.index', ['cutoff' => $cutoff])
            ->with('success', "Deleted {$deleted} room booking(s) made on or before " . Carbon::parse($cutoff)->format('M d, Y') . '.');
    }

    public function deleteAllBookings()
    {
        $deleted = $this->deleteBookings(RoomBooking::query());

        AuditLog::log('go_live_bookings_deleted', "Go Live: deleted all {$deleted} room booking(s)");

        return redirect()->route('admin.go-live.index')->with('success', "Deleted all {$deleted} room booking(s).");
    }

    private function bookingsUpTo(string $cutoff)
    {
        return RoomBooking::whereDate('created_at', '<=', Carbon::parse($cutoff)->toDateString());
    }

    /**
     * Delete bookings, undo any credit they added to customer accounts, and free their rooms.
     */
    private function deleteBookings($query): int
    {
        return DB::transaction(function () use ($query) {
            $bookings = $query->with('customer')->get();

            foreach ($bookings as $booking) {
                if ($booking->status !== 'cancelled' && $booking->is_credit_booking && $booking->customer) {
                    $booking->customer->reduceBalance($booking->total);
                }
            }

            $roomIds = $bookings->pluck('room_id')->unique()->all();
            RoomBooking::whereIn('id', $bookings->pluck('id'))->delete();
            $this->releaseRooms($roomIds);

            return $bookings->count();
        });
    }

    private function releaseRooms(array $roomIds): void
    {
        Room::whereIn('id', array_filter($roomIds))
            ->whereIn('status', ['booked', 'checked-in'])
            ->whereDoesntHave('activeBookings')
            ->update(['status' => 'available']);
    }

    private function getCounts(): array
    {
        return [
            'sales' => Sale::count(),
            'sale_items' => SaleItem::count(),
            'table_guests' => TableGuest::count(),
            'customers' => Customer::count(),
            'customer_payments' => CustomerPayment::count(),
            'room_bookings' => RoomBooking::count(),
            'active_room_bookings' => RoomBooking::active()->count(),
        ];
    }

    private function performClear(Request $request, array $targets)
    {
        try {
            $deleted = DB::transaction(function () use ($targets) {
                $result = [
                    'sales' => 0,
                    'sale_items' => 0,
                    'table_guests' => 0,
                    'customers' => 0,
                    'customer_payments' => 0,
                ];

                if (in_array('orders', $targets, true)) {
                    $result['sale_items'] = SaleItem::count();
                    SaleItem::query()->delete();

                    $result['sales'] = Sale::count();
                    Sale::query()->delete();

                    $result['table_guests'] = TableGuest::count();
                    TableGuest::query()->delete();
                }

                if (in_array('customers', $targets, true)) {
                    if (Sale::count() > 0) {
                        throw new \RuntimeException('Clear all orders before clearing customers.');
                    }

                    $result['customer_payments'] = CustomerPayment::count();
                    CustomerPayment::query()->delete();

                    $result['customers'] = Customer::count();
                    Customer::query()->delete();
                }

                return $result;
            });

            $action = match (true) {
                count($targets) === 2 => 'go_live_clear_all',
                in_array('orders', $targets, true) => 'go_live_clear_orders',
                default => 'go_live_clear_customers',
            };

            AuditLog::log($action, 'Go-live data cleared: ' . json_encode($deleted));

            $message = $this->buildMessage($deleted, $targets);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'deleted' => $deleted,
                    'counts' => $this->getCounts(),
                ]);
            }

            return redirect()->route('admin.go-live.index')->with('success', $message);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->route('admin.go-live.index')->with('error', $e->getMessage());
        }
    }

    private function buildMessage(array $deleted, array $targets): string
    {
        $parts = [];

        if (in_array('orders', $targets, true)) {
            $parts[] = "{$deleted['sales']} sale(s)";
            $parts[] = "{$deleted['sale_items']} sale item(s)";
            $parts[] = "{$deleted['table_guests']} table guest(s)";
        }

        if (in_array('customers', $targets, true)) {
            $parts[] = "{$deleted['customers']} customer(s)";
            $parts[] = "{$deleted['customer_payments']} customer payment(s)";
        }

        return 'Successfully cleared ' . implode(', ', $parts) . '. The system is ready for live use.';
    }
}
