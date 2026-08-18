<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Section;
use App\Models\StaffAssignment;
use App\Models\User;
use Illuminate\Http\Request;

class StaffAssignmentController extends Controller
{
    public function __construct()
    {
        // Supervisors, managers, and admins can manage staff assignments
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin && !auth()->user()->isManager() && !auth()->user()->isSupervisor()) {
                abort(403, 'You do not have permission to manage staff assignments.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = StaffAssignment::with(['user.role', 'section', 'assignedBy']);

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('shift_type')) {
            $query->where('shift_type', $request->shift_type);
        }

        if ($request->boolean('active_only', true)) {
            $query->current();
        }

        $assignments = $query->latest()->paginate(20);
        $sections = Section::active()->get();
        
        // Get only Cashiers for filter dropdown
        $staff = User::where('is_active', true)
            ->whereHas('role', function ($query) {
                $query->where('name', 'cashier');
            })
            ->with('role')
            ->orderBy('name')
            ->get();

        return view('admin.assignments.index', compact('assignments', 'sections', 'staff'));
    }

    public function create(Request $request)
    {
        $sections = Section::active()->get();
        
        // Get only Cashiers for section assignment
        $staff = User::where('is_active', true)
            ->whereHas('role', function ($query) {
                $query->where('name', 'cashier');
            })
            ->with('role')
            ->orderBy('name')
            ->get();
        
        $shiftTypes = StaffAssignment::SHIFT_TYPES;
        $daysOfWeek = StaffAssignment::DAYS_OF_WEEK;
        
        $selectedSection = $request->filled('section_id') ? Section::find($request->section_id) : null;

        return view('admin.assignments.create', compact('sections', 'staff', 'shiftTypes', 'daysOfWeek', 'selectedSection'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'section_id' => 'required|exists:sections,id',
            'shift_type' => 'required|in:morning,afternoon,evening,night,full_day,custom',
            'shift_start' => 'required_if:shift_type,custom|nullable|date_format:H:i',
            'shift_end' => 'required_if:shift_type,custom|nullable|date_format:H:i',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'working_days' => 'nullable|array',
            'working_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check for existing active assignment in same section
        $existingAssignment = StaffAssignment::where('user_id', $validated['user_id'])
            ->where('section_id', $validated['section_id'])
            ->where('is_active', true)
            ->first();

        if ($existingAssignment) {
            return back()->with('error', 'This staff member already has an active assignment in this section.')
                ->withInput();
        }

        // Set shift times based on type
        if ($validated['shift_type'] !== 'custom') {
            $shiftInfo = StaffAssignment::SHIFT_TYPES[$validated['shift_type']];
            $validated['shift_start'] = $shiftInfo['start'];
            $validated['shift_end'] = $shiftInfo['end'];
        }

        $validated['assigned_by'] = auth()->id();
        $validated['is_active'] = true;

        $assignment = StaffAssignment::create($validated);
        $assignment->load('user', 'section');

        AuditLog::log('staff_assigned', "Assigned {$assignment->user->name} to {$assignment->section->name}", $assignment);

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Staff assigned to section successfully.');
    }

    public function show(StaffAssignment $assignment)
    {
        $assignment->load(['user.role', 'section', 'assignedBy']);
        return view('admin.assignments.show', compact('assignment'));
    }

    public function edit(StaffAssignment $assignment)
    {
        $sections = Section::active()->get();
        
        // Get only Cashiers for section assignment
        $staff = User::where('is_active', true)
            ->whereHas('role', function ($query) {
                $query->where('name', 'cashier');
            })
            ->with('role')
            ->orderBy('name')
            ->get();
            
        $shiftTypes = StaffAssignment::SHIFT_TYPES;
        $daysOfWeek = StaffAssignment::DAYS_OF_WEEK;

        return view('admin.assignments.edit', compact('assignment', 'sections', 'staff', 'shiftTypes', 'daysOfWeek'));
    }

    public function update(Request $request, StaffAssignment $assignment)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'section_id' => 'required|exists:sections,id',
            'shift_type' => 'required|in:morning,afternoon,evening,night,full_day,custom',
            'shift_start' => 'required_if:shift_type,custom|nullable|date_format:H:i',
            'shift_end' => 'required_if:shift_type,custom|nullable|date_format:H:i',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'working_days' => 'nullable|array',
            'working_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        // Check for existing active assignment if changing user or section
        if (($validated['user_id'] != $assignment->user_id || $validated['section_id'] != $assignment->section_id)) {
            $existingAssignment = StaffAssignment::where('user_id', $validated['user_id'])
                ->where('section_id', $validated['section_id'])
                ->where('is_active', true)
                ->where('id', '!=', $assignment->id)
                ->first();

            if ($existingAssignment) {
                return back()->with('error', 'This staff member already has an active assignment in this section.')
                    ->withInput();
            }
        }

        // Set shift times based on type
        if ($validated['shift_type'] !== 'custom') {
            $shiftInfo = StaffAssignment::SHIFT_TYPES[$validated['shift_type']];
            $validated['shift_start'] = $shiftInfo['start'];
            $validated['shift_end'] = $shiftInfo['end'];
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $oldValues = $assignment->toArray();
        $assignment->update($validated);
        $assignment->load('user', 'section');

        AuditLog::log('assignment_updated', "Updated assignment for {$assignment->user->name}", $assignment, $oldValues, $assignment->toArray());

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Assignment updated successfully.');
    }

    public function destroy(StaffAssignment $assignment)
    {
        $assignment->load('user', 'section');
        
        AuditLog::log('assignment_removed', "Removed {$assignment->user->name} from {$assignment->section->name}", $assignment);
        $assignment->delete();

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Assignment removed successfully.');
    }

    /**
     * End an assignment (set end date to today)
     */
    public function end(StaffAssignment $assignment)
    {
        $assignment->update([
            'end_date' => now(),
            'is_active' => false,
        ]);

        $assignment->load('user', 'section');
        AuditLog::log('assignment_ended', "Ended assignment for {$assignment->user->name} in {$assignment->section->name}", $assignment);

        return back()->with('success', 'Assignment ended successfully.');
    }
}

