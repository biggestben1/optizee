@extends('layouts.admin')

@section('title', 'Bank Settings')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Bank Settings</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Bank Details</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.bank-settings.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Bank Name</label>
                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $bank_name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Account Name</label>
                <input type="text" name="account_name" class="form-control" value="{{ old('account_name', $account_name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Account Number</label>
                <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $account_number) }}" required>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fe fe-save me-2"></i>Save Settings
            </button>
        </form>
    </div>
</div>
@endsection

