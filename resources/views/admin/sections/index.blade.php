@extends('layouts.admin')

@section('title', 'Hotel Sections')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Sections</li>
@endsection

@section('actions')
<a href="{{ route('admin.sections.create') }}" class="btn btn-primary">
    <i class="fe fe-plus me-1"></i> Add Section
</a>
@endsection

@section('content')
<div class="row">
    @forelse($sections as $section)
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card {{ $section->is_active ? '' : 'border-danger' }}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <span class="badge bg-primary me-2">{{ $section->code }}</span>
                    {{ $section->name }}
                </h4>
                @if(!$section->is_active)
                <span class="badge bg-danger">Inactive</span>
                @endif
            </div>
            <div class="card-body">
                @if($section->description)
                <p class="text-muted mb-2">{{ $section->description }}</p>
                @endif
                
                @if($section->location)
                <p class="mb-2">
                    <i class="fe fe-map-pin text-muted me-1"></i>
                    {{ $section->location }}
                </p>
                @endif
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <span class="badge bg-info-transparent text-info">
                            <i class="fe fe-users me-1"></i>
                            {{ $section->active_assignments_count }} Staff Assigned
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.sections.show', $section) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fe fe-eye"></i> View
                    </a>
                    <a href="{{ route('admin.sections.edit', $section) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fe fe-edit"></i> Edit
                    </a>
                    <a href="{{ route('admin.assignments.create', ['section_id' => $section->id]) }}" class="btn btn-sm btn-outline-success">
                        <i class="fe fe-user-plus"></i> Assign Staff
                    </a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fe fe-layers text-muted" style="font-size: 48px;"></i>
                <h4 class="mt-3">No Sections Found</h4>
                <p class="text-muted">Create your first hotel section to get started.</p>
                <a href="{{ route('admin.sections.create') }}" class="btn btn-primary">
                    <i class="fe fe-plus me-1"></i> Create Section
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection










