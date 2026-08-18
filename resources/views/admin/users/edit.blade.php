@extends('layouts.admin')

@section('title', 'Edit Staff')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Staff</a></li>
<li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Staff - {{ $user->name }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label" for="name">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="role_id">Role <span class="text-danger">*</span></label>
                        <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required {{ $user->is_admin ? 'disabled' : '' }}>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }}
                            </option>
                            @endforeach
                        </select>
                        @if($user->is_admin)
                        <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                        <small class="text-muted">Admin users cannot change roles.</small>
                        @endif
                        @error('role_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">New Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                        <small class="text-muted">Leave blank to keep current password.</small>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">Confirm New Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="login_code">Login Code</label>
                        <div class="input-group">
                            <input type="text" class="form-control font-monospace @error('login_code') is-invalid @enderror" 
                                   id="login_code" name="login_code" 
                                   value="{{ old('login_code', $user->login_code) }}" 
                                   maxlength="4" pattern="[0-9]{4}" 
                                   placeholder="0000"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)">
                            <button type="button" class="btn btn-outline-secondary" onclick="generateCode()" title="Generate Random Code">
                                <i class="fe fe-refresh-cw"></i> Generate
                            </button>
                        </div>
                        <small class="text-muted">4-digit code for quick login (leave blank to remove). Users can login with this code without password.</small>
                        @error('login_code')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="custom-switch">
                            <input type="checkbox" name="is_active" class="custom-switch-input" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description">Active</span>
                        </label>
                    </div>
                    <div class="d-flex">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function generateCode() {
    // Generate random 4-digit code
    const code = Math.floor(1000 + Math.random() * 9000).toString();
    document.getElementById('login_code').value = code;
}
</script>
@endpush
@endsection











