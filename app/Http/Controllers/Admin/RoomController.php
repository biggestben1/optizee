<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\RoomBooking;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function index()
    {
        $categories = RoomCategory::active()
            ->with(['rooms' => function($q) {
                $q->orderBy('room_number');
            }])
            ->get();
        
        return view('admin.rooms.index', compact('categories'));
    }

    public function getByCategory(RoomCategory $category)
    {
        $rooms = $category->activeRooms()
            ->orderBy('room_number')
            ->get()
            ->map(function($room) {
                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'room_name' => $room->getDisplayName(),
                    'price_per_night' => $room->getEffectivePrice(),
                    'hourly_rate' => $room->getEffectiveHourlyRate(),
                    'status' => $room->status,
                    'is_available' => $room->isAvailable(),
                    'is_suite_sub_room' => $room->is_suite_sub_room,
                ];
            });
        
        return response()->json($rooms);
    }

    public function checkAvailability(Room $room, Request $request)
    {
        $checkIn = $request->input('check_in_date');
        $checkOut = $request->input('check_out_date');
        
        // Check if room has active bookings for the date range
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
        
        $isAvailable = !$conflictingBookings && $room->isAvailable();
        
        // For suite: check if full suite is booked or if any sub-room is booked
        if ($room->category->is_suite && !$room->is_suite_sub_room) {
            // Check if full suite is booked
            $fullSuiteBooked = RoomBooking::where('room_id', $room->id)
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
                $isAvailable = false;
            }
        } elseif ($room->is_suite_sub_room) {
            // Check if full suite is booked (if so, sub-rooms cannot be booked)
            $parentSuite = $room->parentSuite;
            if ($parentSuite) {
                $fullSuiteBooked = RoomBooking::where('room_id', $parentSuite->id)
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
                    $isAvailable = false;
                }
            }
        }
        
        return response()->json([
            'available' => $isAvailable,
            'room' => [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'room_name' => $room->getDisplayName(),
                'price_per_night' => $room->getEffectivePrice(),
                'hourly_rate' => $room->getEffectiveHourlyRate(),
            ]
        ]);
    }

    public function create()
    {
        $categories = RoomCategory::active()->with('rooms')->orderBy('name')->get();
        return view('admin.rooms.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_category_id' => 'required|exists:room_categories,id',
            'room_number' => 'required|string|max:255|unique:rooms,room_number',
            'room_name' => 'nullable|string|max:255',
            'hourly_rate' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,booked,checked-in,checked-out,cleaning,maintenance',
            'parent_suite_id' => 'nullable|exists:rooms,id',
        ]);

        try {
            DB::beginTransaction();

            $validated['hourly_rate'] = $request->filled('hourly_rate') && (float) $request->input('hourly_rate') > 0
                ? (float) $request->input('hourly_rate')
                : null;

            $room = Room::create($validated);

            AuditLog::log('room_created', "Room created: {$room->room_number}", $room);

            DB::commit();

            return redirect()->route('admin.rooms.index')
                ->with('success', 'Room created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create room: ' . $e->getMessage());
        }
    }

    public function edit(Room $room)
    {
        $categories = RoomCategory::active()->orderBy('name')->get();
        $parentSuites = Room::where('room_category_id', $room->category->id)
            ->where('id', '!=', $room->id)
            ->whereNull('parent_suite_id')
            ->get();
        
        return view('admin.rooms.edit', compact('room', 'categories', 'parentSuites'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_category_id' => 'required|exists:room_categories,id',
            'room_number' => 'required|string|max:255|unique:rooms,room_number,' . $room->id,
            'room_name' => 'nullable|string|max:255',
            'hourly_rate' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,booked,checked-in,checked-out,cleaning,maintenance',
            'parent_suite_id' => 'nullable|exists:rooms,id',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $validated['hourly_rate'] = $request->filled('hourly_rate') && (float) $request->input('hourly_rate') > 0
                ? (float) $request->input('hourly_rate')
                : null;

            $room->update($validated);

            AuditLog::log('room_updated', "Room updated: {$room->room_number}", $room);

            DB::commit();

            return redirect()->route('admin.rooms.index')
                ->with('success', 'Room updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update room: ' . $e->getMessage());
        }
    }

    public function destroy(Room $room)
    {
        try {
            // Check if room has active bookings
            $activeBookings = RoomBooking::where('room_id', $room->id)
                ->whereIn('status', ['confirmed', 'checked-in'])
                ->exists();

            if ($activeBookings) {
                return back()->with('error', 'Cannot delete room with active bookings. Please check out or cancel bookings first.');
            }

            DB::beginTransaction();

            $roomNumber = $room->room_number;
            $room->delete();

            AuditLog::log('room_deleted', "Room deleted: {$roomNumber}", null);

            DB::commit();

            return redirect()->route('admin.rooms.index')
                ->with('success', 'Room deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete room: ' . $e->getMessage());
        }
    }
}
