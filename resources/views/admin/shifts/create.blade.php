@extends('layouts.admin')

@section('title', 'Open Shift')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.shifts.index') }}">Shifts</a></li>
<li class="breadcrumb-item active" aria-current="page">Open New Shift</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Open New Shift</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.shifts.open') }}" method="POST">
                    @csrf
                    <div class="text-center mb-4">
                        <i class="fe fe-play-circle text-success" style="font-size: 60px;"></i>
                        <h4 class="mt-3">Ready to start your shift?</h4>
                        <p class="text-muted">Enter the cash amount in your drawer to begin.</p>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="opening_cash">Opening Cash <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">₦</span>
                            <input type="number" step="0.01" class="form-control @error('opening_cash') is-invalid @enderror" id="opening_cash" name="opening_cash" value="{{ old('opening_cash', 0) }}" required autofocus>
                        </div>
                        @error('opening_cash')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Count all cash in your drawer before starting.</small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="notes">Notes (Optional)</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fe fe-play-circle me-2"></i> Open Shift
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection











