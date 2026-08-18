<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomCategory;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomCategoryController extends Controller
{
    public function index()
    {
        $categories = RoomCategory::withCount('rooms')
            ->orderBy('name')
            ->get();
        
        return view('admin.room-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.room-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:room_categories,name',
            'description' => 'nullable|string',
            'price_per_room' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'full_suite_price' => 'nullable|numeric|min:0',
            'total_rooms' => 'nullable|integer|min:0',
            'is_suite' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $validated['is_suite'] = $request->boolean('is_suite', false);
            $validated['is_active'] = $request->boolean('is_active', true);

            $category = RoomCategory::create($validated);

            AuditLog::log('room_category_created', "Room category created: {$category->name}", $category);

            DB::commit();

            return redirect()->route('admin.room-categories.index')
                ->with('success', 'Room category created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create room category: ' . $e->getMessage());
        }
    }

    public function edit(RoomCategory $roomCategory)
    {
        return view('admin.room-categories.edit', compact('roomCategory'));
    }

    public function update(Request $request, RoomCategory $roomCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:room_categories,name,' . $roomCategory->id,
            'description' => 'nullable|string',
            'price_per_room' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'full_suite_price' => 'nullable|numeric|min:0',
            'total_rooms' => 'nullable|integer|min:0',
            'is_suite' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $validated['is_suite'] = $request->boolean('is_suite', false);
            $validated['is_active'] = $request->boolean('is_active', true);

            $oldValues = $roomCategory->toArray();
            $roomCategory->update($validated);

            AuditLog::log('room_category_updated', "Room category updated: {$roomCategory->name}", $roomCategory, $oldValues, $roomCategory->toArray());

            DB::commit();

            return redirect()->route('admin.room-categories.index')
                ->with('success', 'Room category updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update room category: ' . $e->getMessage());
        }
    }

    public function destroy(RoomCategory $roomCategory)
    {
        try {
            // Check if category has rooms
            if ($roomCategory->rooms()->count() > 0) {
                return back()->with('error', 'Cannot delete category with existing rooms. Please delete or move rooms first.');
            }

            DB::beginTransaction();

            $categoryName = $roomCategory->name;
            $roomCategory->delete();

            AuditLog::log('room_category_deleted', "Room category deleted: {$categoryName}", null);

            DB::commit();

            return redirect()->route('admin.room-categories.index')
                ->with('success', 'Room category deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete room category: ' . $e->getMessage());
        }
    }
}
