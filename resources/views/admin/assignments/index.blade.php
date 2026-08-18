@extends('layouts.admin')

@section('title', 'Staff Assignments')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Staff Assignments</li>
@endsection

@section('actions')
<a href="{{ route('admin.assignments.create') }}" class="btn btn-primary">
    <i class="fe fe-user-plus me-1"></i> New Assignment
</a>
@endsection

@section('content')
<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Section</label>
                <select name="section_id" class="form-select">
                    <option value="">All Sections</option>
                    @foreach($sections as $section)
                    <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                        {{ $section->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Staff Member</label>
                <select name="user_id" class="form-select">
                    <option value="">All Staff</option>
                    @foreach($staff as $member)
                    <option value="{{ $member->id }}" {{ request('user_id') == $member->id ? 'selected' : '' }}>
                        {{ $member->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Shift Type</label>
                <select name="shift_type" class="form-select">
                    <option value="">All Shifts</option>
                    <option value="morning" {{ request('shift_type') == 'morning' ? 'selected' : '' }}>Morning</option>
                    <option value="afternoon" {{ request('shift_type') == 'afternoon' ? 'selected' : '' }}>Afternoon</option>
                    <option value="evening" {{ request('shift_type') == 'evening' ? 'selected' : '' }}>Evening</option>
                    <option value="night" {{ request('shift_type') == 'night' ? 'selected' : '' }}>Night</option>
                    <option value="full_day" {{ request('shift_type') == 'full_day' ? 'selected' : '' }}>Full Day</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="custom-switch">
                    <input type="checkbox" name="active_only" class="custom-switch-input" value="1" 
                           {{ request('active_only', true) ? 'checked' : '' }}>
                    <span class="custom-switch-indicator"></span>
                    <span class="custom-switch-description">Active Only</span>
                </label>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fe fe-filter me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Assignments Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Staff Assignments</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Role</th>
                        <th>Section</th>
                        <th>Shift</th>
                        <th>Time</th>
                        <th>Working Days</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                    <tr>
                        <td>
                            <strong>{{ $assignment->user->name }}</strong>
                            <br><small class="text-muted">{{ $assignment->user->email }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $assignment->user->role?->display_name }}</span>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $assignment->section->code }}</span>
                            {{ $assignment->section->name }}
                        </td>
                        <td>
                            <span class="badge bg-info-transparent text-info">{{ $assignment->getShiftLabel() }}</span>
                        </td>
                        <td>{{ $assignment->getShiftTime() }}</td>
                        <td>{{ $assignment->getWorkingDaysFormatted() }}</td>
                        <td>
                            {{ $assignment->start_date->format('M d, Y') }}
                            @if($assignment->end_date)
                            <br><small class="text-muted">to {{ $assignment->end_date->format('M d, Y') }}</small>
                            @else
                            <br><small class="text-success">Ongoing</small>
                            @endif
                        </td>
                        <td>
                            @if($assignment->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-danger">Ended</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.assignments.edit', $assignment) }}" 
                                   class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>
                                @if($assignment->is_active)
                                <form action="{{ route('admin.assignments.end', $assignment) }}" 
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('End this assignment?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="End Assignment">
                                        <i class="fe fe-x-circle"></i>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.assignments.destroy', $assignment) }}" 
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this assignment permanently?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fe fe-trash-2"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fe fe-calendar" style="font-size: 32px;"></i>
                            <p class="mt-2 mb-0">No staff assignments found</p>
                            <a href="{{ route('admin.assignments.create') }}" class="btn btn-primary mt-3">
                                <i class="fe fe-plus me-1"></i> Create Assignment
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $assignments->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection










