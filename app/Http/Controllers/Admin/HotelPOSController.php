<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\Customer;
use Illuminate\Http\Request;

class HotelPOSController extends Controller
{
    public function __construct()
    {
        // Allow admins, managers, supervisors, and cashiers to access Hotel POS
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            
            if (!$user) {
                return redirect('/login');
            }
            
            // Allow admins
            if ($user->is_admin) {
                return $next($request);
            }
            
            // Allow cashiers, managers, supervisors
            if ($user->isCashier() || $user->isManager() || $user->isSupervisor()) {
                return $next($request);
            }
            
            // Deny access for other roles
            abort(403, 'You do not have permission to access the Hotel POS system.');
        });
    }

    public function index(Request $request)
    {
        // Get room categories and rooms for hotel booking
        $roomCategories = RoomCategory::active()
            ->with(['activeRooms' => function($q) {
                $q->orderBy('room_number');
            }])
            ->get();

        $customers = Customer::active()->withCredit()->get();
        
        // Get selected room if provided
        $selectedRoom = $request->filled('room_id') ? Room::with('category')->find($request->room_id) : null;

        return view('admin.hotel-pos.index', compact('roomCategories', 'customers', 'selectedRoom'));
    }

    public function getRooms(Request $request)
    {
        try {
            $categoryId = $request->input('category_id');
            $roomId = $request->input('room_id');
            $query = Room::active();

            if ($roomId) {
                // If room_id is provided, return just that room
                $query->where('id', $roomId);
            } elseif ($categoryId) {
                $query->where('room_category_id', $categoryId);
            }

            $rooms = $query->with('category')
                ->orderBy('room_number')
                ->get()
                ->map(function($room) {
                    $category = $room->category;
                    $effectivePrice = $room->getEffectivePrice();
                    
                    return [
                        'id' => $room->id,
                        'room_number' => $room->room_number,
                        'room_name' => $room->getDisplayName(),
                        'price_per_night' => $effectivePrice,
                        'hourly_rate' => $room->getEffectiveHourlyRate(),
                        'status' => $room->status,
                        'is_available' => $room->isAvailable(),
                        'is_suite_sub_room' => $room->is_suite_sub_room,
                        'category' => $category ? [
                            'id' => $category->id,
                            'name' => $category->name,
                            'is_suite' => $category->is_suite ?? false,
                            'full_suite_price' => $category->full_suite_price ?? 0,
                            'hourly_rate' => $category->hourly_rate ?? 0,
                        ] : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'rooms' => $rooms,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading rooms: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error loading rooms: ' . $e->getMessage(),
            ], 500);
        }
    }
}
