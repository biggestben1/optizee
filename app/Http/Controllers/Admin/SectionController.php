<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function __construct()
    {
        // Only admins, managers, and supervisors can manage sections
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin && !auth()->user()->isManager() && !auth()->user()->isSupervisor()) {
                abort(403, 'You do not have permission to manage sections.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $sections = Section::withCount('activeAssignments')->latest()->get();
        return view('admin.sections.index', compact('sections'));
    }

    public function create()
    {
        return view('admin.sections.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:sections,code',
            'description' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $section = Section::create($validated);

        AuditLog::log('section_created', "Created section: {$section->name}", $section);

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section created successfully.');
    }

    public function show(Section $section)
    {
        $section->load(['activeAssignments.user.role', 'activeAssignments.assignedBy']);
        return view('admin.sections.show', compact('section'));
    }

    public function edit(Section $section)
    {
        return view('admin.sections.edit', compact('section'));
    }

    public function update(Request $request, Section $section)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:sections,code,' . $section->id,
            'description' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $oldValues = $section->toArray();
        $section->update($validated);

        AuditLog::log('section_updated', "Updated section: {$section->name}", $section, $oldValues, $section->toArray());

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section updated successfully.');
    }

    public function destroy(Section $section)
    {
        if ($section->activeAssignments()->count() > 0) {
            return back()->with('error', 'Cannot delete section with active staff assignments.');
        }

        AuditLog::log('section_deleted', "Deleted section: {$section->name}", $section);
        $section->delete();

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section deleted successfully.');
    }
}










