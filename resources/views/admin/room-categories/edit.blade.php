@extends('layouts.admin')

@section('title', 'Edit Room Category')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.room-categories.index') }}">Room Categories</a></li>
<li class="breadcrumb-item active" aria-current="page">Edit Category</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 col-xl-6 mx-auto">
        <div class="card">
            <div class="card-header bg-warning-transparent">
                <h4 class="card-title mb-0">
                    <i class="fe fe-edit me-2"></i>Edit Room Category: {{ $roomCategory->name }}
                </h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.room-categories.update', $roomCategory) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $roomCategory->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="3">{{ old('description', $roomCategory->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_suite" value="1" id="is_suite" 
                                   {{ old('is_suite', $roomCategory->is_suite) ? 'checked' : '' }} onchange="toggleSuiteFields()">
                            <label class="form-check-label" for="is_suite">
                                This is a Suite (with multiple sub-rooms)
                            </label>
                        </div>
                    </div>

                    <div id="standard-fields" style="{{ $roomCategory->is_suite ? 'display: none;' : '' }}">
                        <div class="mb-3">
                            <label class="form-label">Price per Room (₦) <span class="text-danger">*</span></label>
                            <input type="number" name="price_per_room" class="form-control @error('price_per_room') is-invalid @enderror" 
                                   value="{{ old('price_per_room', $roomCategory->price_per_room) }}" step="0.01" min="0" 
                                   {{ !$roomCategory->is_suite ? 'required' : '' }}>
                            @error('price_per_room')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Price per night for standard rooms</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hourly Rate (₦) (Optional)</label>
                            <input type="number" name="hourly_rate" class="form-control @error('hourly_rate') is-invalid @enderror" 
                                   value="{{ old('hourly_rate', $roomCategory->hourly_rate) }}" step="0.01" min="0">
                            @error('hourly_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">For short-stay bookings (defaults to daily rate ÷ 24 if not set)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Total Rooms (Optional)</label>
                            <input type="number" name="total_rooms" class="form-control @error('total_rooms') is-invalid @enderror" 
                                   value="{{ old('total_rooms', $roomCategory->total_rooms) }}" min="0">
                            @error('total_rooms')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="suite-fields" style="{{ $roomCategory->is_suite ? '' : 'display: none;' }}">
                        <div class="mb-3">
                            <label class="form-label">Full Suite Price (₦) <span class="text-danger">*</span></label>
                            <input type="number" name="full_suite_price" class="form-control @error('full_suite_price') is-invalid @enderror" 
                                   value="{{ old('full_suite_price', $roomCategory->full_suite_price) }}" step="0.01" min="0"
                                   {{ $roomCategory->is_suite ? 'required' : '' }}>
                            @error('full_suite_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Price for booking the entire suite</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" 
                                   {{ old('is_active', $roomCategory->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active (Category is available for booking)
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <strong>Note:</strong> Changing prices will affect all future bookings. Existing bookings will keep their original prices.
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('admin.room-categories.index') }}" class="btn btn-secondary">
                            <i class="fe fe-x me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fe fe-save me-2"></i>Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>
</div>

@push('scripts')
<script>
function toggleSuiteFields() {
    const isSuite = document.getElementById('is_suite').checked;
    const standardFields = document.getElementById('standard-fields');
    const suiteFields = document.getElementById('suite-fields');
    
    if (isSuite) {
        standardFields.style.display = 'none';
        suiteFields.style.display = 'block';
        document.querySelector('[name="price_per_room"]').removeAttribute('required');
        document.querySelector('[name="full_suite_price"]').setAttribute('required', 'required');
    } else {
        standardFields.style.display = 'block';
        suiteFields.style.display = 'none';
        document.querySelector('[name="price_per_room"]').setAttribute('required', 'required');
        document.querySelector('[name="full_suite_price"]').removeAttribute('required');
    }
}
</script>
@endpush
@endsection




