@extends('layouts.admin')

@section('title', 'Assign Staff')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.assignments.index') }}">Assignments</a></li>
<li class="breadcrumb-item active" aria-current="page">New Assignment</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assign Staff to Section</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.assignments.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="user_id">Cashier <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" 
                                    id="user_id" name="user_id" required>
                                <option value="">-- Select Cashier --</option>
                                @forelse($staff as $member)
                                <option value="{{ $member->id }}" {{ old('user_id') == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }}
                                </option>
                                @empty
                                <option value="" disabled>No cashiers available</option>
                                @endforelse
                            </select>
                            @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">{{ $staff->count() }} cashier(s) available</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="section_id">Section <span class="text-danger">*</span></label>
                            <select class="form-select @error('section_id') is-invalid @enderror" 
                                    id="section_id" name="section_id" required>
                                <option value="">Select Section</option>
                                @foreach($sections as $section)
                                <option value="{{ $section->id }}" 
                                    {{ (old('section_id') == $section->id || ($selectedSection && $selectedSection->id == $section->id)) ? 'selected' : '' }}>
                                    [{{ $section->code }}] {{ $section->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('section_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    <h5 class="mb-3"><i class="fe fe-clock me-2"></i>Shift Details</h5>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="shift_type">Shift Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('shift_type') is-invalid @enderror" 
                                    id="shift_type" name="shift_type" required onchange="toggleCustomTime()">
                                @foreach($shiftTypes as $key => $shift)
                                <option value="{{ $key }}" {{ old('shift_type', 'full_day') == $key ? 'selected' : '' }}>
                                    {{ $shift['label'] }} 
                                    @if($shift['start'])
                                    ({{ $shift['start'] }} - {{ $shift['end'] }})
                                    @endif
                                </option>
                                @endforeach
                            </select>
                            @error('shift_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3" id="custom-start-group" style="display: none;">
                            <label class="form-label" for="shift_start">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('shift_start') is-invalid @enderror" 
                                   id="shift_start" name="shift_start" value="{{ old('shift_start') }}">
                            @error('shift_start')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3" id="custom-end-group" style="display: none;">
                            <label class="form-label" for="shift_end">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('shift_end') is-invalid @enderror" 
                                   id="shift_end" name="shift_end" value="{{ old('shift_end') }}">
                            @error('shift_end')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="start_date">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                   id="start_date" name="start_date" 
                                   value="{{ old('start_date', date('Y-m-d')) }}" required>
                            @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="end_date">End Date <small class="text-muted">(Optional - leave empty for ongoing)</small></label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                   id="end_date" name="end_date" value="{{ old('end_date') }}">
                            @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Working Days</label>
                        <div class="row">
                            @foreach($daysOfWeek as $day)
                            <div class="col-auto">
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" 
                                           name="working_days[]" value="{{ $day }}"
                                           {{ in_array($day, old('working_days', [])) ? 'checked' : '' }}>
                                    <span class="custom-control-label">{{ ucfirst($day) }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Leave unchecked for all days</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="notes">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="2" 
                                  placeholder="Any special instructions or notes">{{ old('notes') }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex">
                        <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fe fe-check me-1"></i> Create Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-12">
        <div class="card bg-info-transparent">
            <div class="card-header">
                <h4 class="card-title text-info mb-0">
                    <i class="fe fe-info me-2"></i>Shift Types
                </h4>
            </div>
            <div class="card-body">
                @foreach($shiftTypes as $key => $shift)
                <div class="mb-2">
                    <strong>{{ $shift['label'] }}</strong>
                    @if($shift['start'])
                    <span class="text-muted">: {{ $shift['start'] }} - {{ $shift['end'] }}</span>
                    @else
                    <span class="text-muted">: Set custom times</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleCustomTime() {
    const shiftType = document.getElementById('shift_type').value;
    const startGroup = document.getElementById('custom-start-group');
    const endGroup = document.getElementById('custom-end-group');
    
    if (shiftType === 'custom') {
        startGroup.style.display = 'block';
        endGroup.style.display = 'block';
    } else {
        startGroup.style.display = 'none';
        endGroup.style.display = 'none';
    }
}

// Run on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleCustomTime();
});
</script>
@endpush

