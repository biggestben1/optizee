<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\HallOccasion;
use App\Models\RoomCategory;
use Illuminate\Http\Request;

class BookingPriceController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || !($user->is_admin || $user->isManager())) {
                abort(403, 'Only admins and managers can change booking prices.');
            }

            return $next($request);
        });
    }

    public function index()
    {
        $roomCategories = RoomCategory::orderBy('name')->get();
        $occasions = HallOccasion::orderBy('name')->get();

        return view('admin.booking-prices.index', compact('roomCategories', 'occasions'));
    }

    public function updateRooms(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.price_per_room' => 'nullable|numeric|min:0',
            'categories.*.hourly_rate' => 'nullable|numeric|min:0',
            'categories.*.online_price_per_room' => 'nullable|numeric|min:0',
            'categories.*.online_hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $changed = 0;
        foreach (RoomCategory::whereIn('id', array_keys($validated['categories']))->get() as $category) {
            $old = $category->only(['price_per_room', 'hourly_rate', 'online_price_per_room', 'online_hourly_rate']);
            $category->fill($validated['categories'][$category->id]);

            if ($category->isDirty()) {
                $category->save();
                $changed++;
                AuditLog::log('room_prices_updated', "Room prices updated for {$category->name}", $category, $old, $category->only(array_keys($old)));
            }
        }

        return redirect()->route('admin.booking-prices.index')
            ->with('success', $changed ? "Room prices updated for {$changed} categor" . ($changed === 1 ? 'y.' : 'ies.') : 'No room prices were changed.');
    }

    public function updateHalls(Request $request)
    {
        $validated = $request->validate([
            'occasions' => 'required|array',
            'occasions.*.price' => 'required|numeric|min:0',
            'occasions.*.is_active' => 'nullable|boolean',
        ]);

        $changed = 0;
        foreach (HallOccasion::whereIn('id', array_keys($validated['occasions']))->get() as $occasion) {
            $old = $occasion->only(['price', 'is_active']);
            $occasion->fill([
                'price' => $validated['occasions'][$occasion->id]['price'],
                'is_active' => (bool) ($validated['occasions'][$occasion->id]['is_active'] ?? false),
            ]);

            if ($occasion->isDirty()) {
                $occasion->save();
                $changed++;
                AuditLog::log('hall_occasion_updated', "Hall occasion {$occasion->name} price set to ₦{$occasion->price}", $occasion, $old, $occasion->only(array_keys($old)));
            }
        }

        return redirect()->route('admin.booking-prices.index')
            ->with('success', $changed ? "Hall prices updated for {$changed} occasion" . ($changed === 1 ? '.' : 's.') : 'No hall prices were changed.');
    }
}
