<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\RoomCategory;
use App\Models\Customer;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoomBookingController extends Controller
{
    public function index()
    {
        $bookings = RoomBooking::with(['room.category', 'customer', 'user'])
            ->latest()
            ->paginate(20);
        
        return view('admin.room-bookings.index', compact('bookings'));
    }

    public function store(Request $request)
    {
        $customerId = $request->input('customer_id');
        if ($customerId === '' || $customerId === 'null' || $customerId === 'undefined' || $customerId === '0') {
            $request->merge(['customer_id' => null]);
        }

        $guestEmail = $request->input('guest_email');
        if ($guestEmail === '' || $guestEmail === 'null') {
            $request->merge(['guest_email' => null]);
        }

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'booking_type' => 'required|in:overnight,short_stay',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'hours_stayed' => 'nullable|integer|min:1',
            'hourly_rate' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'guest_name' => 'required|string|max:255',
            'guest_phone' => 'required|string|max:20',
            'guest_email' => 'nullable|email|max:255',
            'guest_id_upload' => 'nullable|image|max:5120',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'service_charge' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,transfer,pos,credit,mixed',
            'amount_paid' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'is_full_suite_booking' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $room = Room::findOrFail($validated['room_id']);
            $bookingType = $validated['booking_type'] ?? 'overnight';
            
            // Handle short-stay vs overnight bookings
            if ($bookingType === 'short_stay') {
                // Short-stay: use date + time
                $checkIn = Carbon::parse($validated['check_in_date'] . ' ' . ($validated['check_in_time'] ?? '00:00:00'));
                $checkOut = Carbon::parse($validated['check_out_date'] . ' ' . ($validated['check_out_time'] ?? '23:59:59'));
                $hours = $validated['hours_stayed'] ?? max(1, $checkIn->diffInHours($checkOut));
                $nights = 0; // No nights for short-stay
            } else {
                // Overnight: use dates only
                $checkIn = Carbon::parse($validated['check_in_date'])->startOfDay();
                $checkOut = Carbon::parse($validated['check_out_date'])->startOfDay();
                $nights = max(1, $checkIn->diffInDays($checkOut));
                $hours = 0;
            }

            // Validate suite booking rules
            if ($room->category->is_suite) {
                if ($request->input('is_full_suite_booking')) {
                    // Check if any sub-room is booked
                    $subRoomsBooked = RoomBooking::whereIn('room_id', $room->subRooms->pluck('id'))
                        ->whereIn('status', ['confirmed', 'checked-in'])
                        ->where(function($q) use ($checkIn, $checkOut) {
                            $q->whereBetween('check_in_date', [$checkIn, $checkOut])
                              ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                              ->orWhere(function($q2) use ($checkIn, $checkOut) {
                                  $q2->where('check_in_date', '<=', $checkIn)
                                     ->where('check_out_date', '>=', $checkOut);
                              });
                        })
                        ->exists();
                    
                    if ($subRoomsBooked) {
                        if ($request->expectsJson() || $request->ajax()) {
                            return response()->json(['success' => false, 'message' => 'Cannot book full suite: individual rooms are already booked.'], 400);
                        }
                        return back()->with('error', 'Cannot book full suite: individual rooms are already booked.');
                    }
                } else {
                    // Check if full suite is booked
                    $fullSuiteBooked = RoomBooking::where('room_id', $room->parent_suite_id ?? $room->id)
                        ->where('is_full_suite_booking', true)
                        ->whereIn('status', ['confirmed', 'checked-in'])
                        ->where(function($q) use ($checkIn, $checkOut) {
                            $q->whereBetween('check_in_date', [$checkIn, $checkOut])
                              ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                              ->orWhere(function($q2) use ($checkIn, $checkOut) {
                                  $q2->where('check_in_date', '<=', $checkIn)
                                     ->where('check_out_date', '>=', $checkOut);
                              });
                        })
                        ->exists();
                    
                    if ($fullSuiteBooked) {
                        if ($request->expectsJson() || $request->ajax()) {
                            return response()->json(['success' => false, 'message' => 'Cannot book individual room: full suite is already booked.'], 400);
                        }
                        return back()->with('error', 'Cannot book individual room: full suite is already booked.');
                    }
                }
            }

            // Check room availability
            $conflictingBookings = RoomBooking::where('room_id', $room->id)
                ->whereIn('status', ['confirmed', 'checked-in'])
                ->where(function($q) use ($checkIn, $checkOut) {
                    $q->whereBetween('check_in_date', [$checkIn, $checkOut])
                      ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                      ->orWhere(function($q2) use ($checkIn, $checkOut) {
                          $q2->where('check_in_date', '<=', $checkIn)
                             ->where('check_out_date', '>=', $checkOut);
                      });
                })
                ->exists();
            
            if ($conflictingBookings) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Room is not available for the selected dates.'], 400);
                }
                return back()->with('error', 'Room is not available for the selected dates.');
            }

            // Calculate pricing
            if ($bookingType === 'short_stay') {
                $sentHourly = $validated['hourly_rate'] ?? null;
                $hourlyRate = ($sentHourly !== null && (float) $sentHourly > 0)
                    ? (float) $sentHourly
                    : $room->getEffectiveHourlyRate();
                $subtotal = $hourlyRate * $hours;
                $pricePerNight = 0;
            } else {
                $pricePerNight = $room->getEffectivePrice();
                $subtotal = $pricePerNight * $nights;
                $hourlyRate = 0;
            }
            
            $discount = $validated['discount'] ?? 0;
            $tax = $validated['tax'] ?? 0;
            $serviceCharge = $validated['service_charge'] ?? 0;
            $total = $subtotal - $discount + $tax + $serviceCharge;
            $amountPaid = $validated['amount_paid'];
            $change = max(0, $amountPaid - $total);

            // Handle guest ID upload
            $guestIdPath = null;
            if ($request->hasFile('guest_id_upload')) {
                $file = $request->file('guest_id_upload');
                $guestIdPath = $file->store('room_bookings/guest_ids', 'public');
            }

            // Create booking
            $booking = RoomBooking::create([
                'booking_type' => $bookingType,
                'room_id' => $room->id,
                'customer_id' => $validated['customer_id'] ?? null,
                'guest_name' => $validated['guest_name'],
                'guest_phone' => $validated['guest_phone'],
                'guest_email' => $validated['guest_email'] ?? null,
                'guest_id_upload' => $guestIdPath,
                'user_id' => auth()->id(),
                'shift_id' => auth()->user()->getOpenShift()?->id,
                'check_in_date' => $checkIn->toDateString(),
                'check_in_time' => $bookingType === 'short_stay' ? $checkIn->toTimeString() : null,
                'check_out_date' => $checkOut->toDateString(),
                'check_out_time' => $bookingType === 'short_stay' ? $checkOut->toTimeString() : null,
                'number_of_nights' => $nights,
                'hours_stayed' => $hours,
                'room_price_per_night' => $pricePerNight,
                'hourly_rate' => $hourlyRate,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'service_charge' => $serviceCharge,
                'total' => $total,
                'amount_paid' => $amountPaid,
                'change' => $change,
                'payment_method' => $validated['payment_method'],
                'status' => 'confirmed',
                'is_credit_booking' => $validated['payment_method'] === 'credit',
                'is_full_suite_booking' => $request->input('is_full_suite_booking', false),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Update room status
            $room->update(['status' => 'booked']);

            // If credit booking, add to customer balance
            if ($booking->is_credit_booking && $booking->customer_id) {
                $customer = Customer::find($booking->customer_id);
                if ($customer) {
                    $customer->addToBalance($booking->total);
                }
            }

            AuditLog::log('room_booking_created', "Room booking created: {$room->room_number} for {$nights} nights", $booking);

            DB::commit();

            // Handle AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Room booking created successfully!',
                    'booking_id' => $booking->id,
                    'redirect' => route('admin.room-bookings.receipt', $booking),
                ]);
            }

            return redirect()->route('admin.room-bookings.receipt', $booking)
                ->with('success', 'Room booking created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            // Handle AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Room booking error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            
            // Handle AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create booking: ' . $e->getMessage(),
                    'error_details' => config('app.debug') ? $e->getMessage() : 'An error occurred. Please check the logs.',
                ], 500);
            }
            
            return back()->with('error', 'Failed to create booking: ' . $e->getMessage());
        }
    }

    public function checkIn(RoomBooking $roomBooking)
    {
        if ($roomBooking->status !== 'confirmed') {
            return back()->with('error', 'Only confirmed bookings can be checked in.');
        }

        $roomBooking->update(['status' => 'checked-in']);
        $roomBooking->room->update(['status' => 'checked-in']);

        AuditLog::log('room_check_in', "Room checked in: {$roomBooking->room->room_number}", $roomBooking);

        return back()->with('success', 'Guest checked in successfully.');
    }

    public function showCheckOut(RoomBooking $roomBooking)
    {
        // Allow checkout for any booking except already checked-out or cancelled
        if (in_array($roomBooking->status, ['checked-out', 'cancelled'])) {
            return back()->with('error', 'This booking cannot be checked out (already checked-out or cancelled).');
        }

        $roomBooking->load(['room.category', 'customer', 'user']);
        
        // Calculate actual nights stayed
        $checkInDate = \Carbon\Carbon::parse($roomBooking->check_in_date);
        $checkOutDate = now(); // Current date/time
        $actualNights = $checkInDate->diffInDays($checkOutDate);
        if ($actualNights < 1) {
            $actualNights = 1; // Minimum 1 night
        }

        return view('admin.room-bookings.checkout', compact('roomBooking', 'actualNights', 'checkOutDate'));
    }

    public function checkOut(RoomBooking $roomBooking, Request $request)
    {
        // Allow checkout for any booking except already checked-out or cancelled
        if (in_array($roomBooking->status, ['checked-out', 'cancelled'])) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'This booking cannot be checked out (already checked-out or cancelled).'], 400);
            }
            return back()->with('error', 'This booking cannot be checked out (already checked-out or cancelled).');
        }

        try {
            DB::beginTransaction();

            // Calculate actual nights stayed
            $checkInDate = \Carbon\Carbon::parse($roomBooking->check_in_date);
            $checkOutDate = now();
            $actualNights = $checkInDate->diffInDays($checkOutDate);
            if ($actualNights < 1) {
                $actualNights = 1; // Minimum 1 night
            }

            // Update booking status
            $roomBooking->update([
                'status' => 'checked-out',
                'check_out_date' => $checkOutDate, // Update to actual checkout date
            ]);

            // Update room status to available
            if ($roomBooking->room) {
                $roomBooking->room->update(['status' => 'available']);
            }

            AuditLog::log('room_check_out', "Room checked out: " . ($roomBooking->room ? $roomBooking->room->room_number : 'N/A'), $roomBooking);

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Guest checked out successfully.',
                    'redirect' => route('admin.room-bookings.receipt', $roomBooking),
                ]);
            }

            return redirect()->route('admin.room-bookings.receipt', $roomBooking)
                ->with('success', 'Guest checked out successfully. Room is now available.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to check out: ' . $e->getMessage(),
                ], 500);
            }
            
            return back()->with('error', 'Failed to check out: ' . $e->getMessage());
        }
    }

    public function cancel(RoomBooking $roomBooking, Request $request)
    {
        if (!in_array($roomBooking->status, ['pending', 'confirmed'])) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Only pending or confirmed bookings can be cancelled.'], 400);
            }
            return back()->with('error', 'Only pending or confirmed bookings can be cancelled.');
        }

        try {
            DB::beginTransaction();

            // Update booking status to cancelled
            // This ensures it won't be included in revenue reports but remains in history
            $roomBooking->update([
                'status' => 'cancelled',
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
                'cancellation_reason' => $request->input('reason'),
            ]);

            // If it was a credit booking, reverse the customer balance update
            if ($roomBooking->is_credit_booking && $roomBooking->customer) {
                $roomBooking->customer->reduceBalance($roomBooking->total);
            }

            // Free up the room for new bookings
            if ($roomBooking->room) {
                $roomBooking->room->update(['status' => 'available']);
            }

            AuditLog::log('room_booking_cancelled', "Room booking cancelled: " . ($roomBooking->room ? $roomBooking->room->room_number : 'N/A'), $roomBooking);

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking cancelled successfully. Room is now available and this booking will not affect daily reports.',
                ]);
            }

            return back()->with('success', 'Booking cancelled successfully. Room is now available and this booking will not affect daily reports.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel booking: ' . $e->getMessage(),
                ], 500);
            }
            
            return back()->with('error', 'Failed to cancel booking: ' . $e->getMessage());
        }
    }

    public function receipt(RoomBooking $roomBooking)
    {
        // Load relationships with null checks - use with() to prevent N+1 queries
        $roomBooking->load(['room.category', 'customer', 'user']);
        
        // If room was deleted, we need to handle it gracefully
        if (!$roomBooking->room) {
            \Log::warning("Room booking #{$roomBooking->id} has no associated room");
        }
        
        // If user was deleted, log it
        if (!$roomBooking->user) {
            \Log::warning("Room booking #{$roomBooking->id} has no associated user");
        }
        
        // Ensure dates are properly cast to Carbon instances
        if ($roomBooking->check_in_date) {
            $roomBooking->check_in_date = \Carbon\Carbon::parse($roomBooking->check_in_date);
        }
        if ($roomBooking->check_out_date) {
            $roomBooking->check_out_date = \Carbon\Carbon::parse($roomBooking->check_out_date);
        }
        if ($roomBooking->created_at) {
            $roomBooking->created_at = \Carbon\Carbon::parse($roomBooking->created_at);
        }
        
        return view('admin.room-bookings.receipt', compact('roomBooking'));
    }
}
