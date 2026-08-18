@extends('layouts.admin')

@section('title', $section->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.sections.index') }}">Sections</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ $section->name }}</li>
@endsection

@section('actions')
<a href="{{ route('admin.assignments.create', ['section_id' => $section->id]) }}" class="btn btn-primary">
    <i class="fe fe-user-plus me-1"></i> Assign Staff
</a>
<a href="{{ route('admin.sections.edit', $section) }}" class="btn btn-secondary">
    <i class="fe fe-edit me-1"></i> Edit Section
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Section Details</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Code</label>
                    <p class="mb-0"><span class="badge bg-primary">{{ $section->code }}</span></p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Name</label>
                    <p class="mb-0 fw-bold">{{ $section->name }}</p>
                </div>
                @if($section->location)
                <div class="mb-3">
                    <label class="text-muted small">Location</label>
                    <p class="mb-0"><i class="fe fe-map-pin me-1"></i>{{ $section->location }}</p>
                </div>
                @endif
                @if($section->description)
                <div class="mb-3">
                    <label class="text-muted small">Description</label>
                    <p class="mb-0">{{ $section->description }}</p>
                </div>
                @endif
                <div class="mb-0">
                    <label class="text-muted small">Status</label>
                    <p class="mb-0">
                        @if($section->is_active)
                        <span class="badge bg-success">Active</span>
                        @else
                        <span class="badge bg-danger">Inactive</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Assigned Staff
                    <span class="badge bg-info ms-2">{{ $section->activeAssignments->count() }}</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Staff Member</th>
                                <th>Role</th>
                                <th>Shift</th>
                                <th>Working Days</th>
                                <th>Since</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($section->activeAssignments as $assignment)
                            <tr>
                                <td>
                                    <strong>{{ $assignment->user->name }}</strong>
                                    <br><small class="text-muted">{{ $assignment->user->email }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $assignment->user->role?->display_name }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info-transparent text-info">{{ $assignment->getShiftLabel() }}</span>
                                    <br><small>{{ $assignment->getShiftTime() }}</small>
                                </td>
                                <td>{{ $assignment->getWorkingDaysFormatted() }}</td>
                                <td>{{ $assignment->start_date->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.assignments.edit', $assignment) }}" 
                                           class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="fe fe-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.assignments.end', $assignment) }}" 
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('End this assignment?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="End Assignment">
                                                <i class="fe fe-x-circle"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fe fe-users" style="font-size: 24px;"></i>
                                    <p class="mt-2 mb-0">No staff assigned to this section</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection










