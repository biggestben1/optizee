@extends('layouts.admin')

@section('title', 'Profile')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Profile</li>
@endsection

@section('content')
<div class="row">
    <!-- Update Profile Information -->
    <div class="col-lg-6 col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Profile Information</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Update your account's profile information and email address.</p>
                
                <form method="post" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')

                    <div class="mb-3">
                        <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email', $user->email) }}" readonly>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Email cannot be changed</small>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        @if (session('status') === 'profile-updated')
                        <span class="text-success">
                            <i class="fe fe-check-circle me-1"></i> Saved.
                        </span>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Login Code & Password -->
    <div class="col-lg-6 col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Login Code & Password</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Change your 4-digit login code and/or password.</p>
                
                <form method="post" action="{{ route('profile.credentials') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="current_password">Current Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                               id="current_password" name="current_password" autocomplete="current-password" required>
                        @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="login_code">4-Digit Login Code</label>
                        <input type="text" class="form-control @error('login_code') is-invalid @enderror" 
                               id="login_code" name="login_code" value="{{ old('login_code', $user->login_code) }}" 
                               maxlength="4" pattern="[0-9]{4}" placeholder="0000" 
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)">
                        @error('login_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Leave empty to keep current code. Must be exactly 4 digits (0-9).</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="new_password">New Password</label>
                        <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                               id="new_password" name="new_password" autocomplete="new-password">
                        @error('new_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Leave empty to keep current password. Minimum 8 characters.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="new_password_confirmation">Confirm New Password</label>
                        <input type="password" class="form-control" 
                               id="new_password_confirmation" name="new_password_confirmation" autocomplete="new-password">
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary">Update</button>
                        @if (session('status') === 'credentials-updated')
                        <span class="text-success">
                            <i class="fe fe-check-circle me-1"></i> Updated successfully.
                        </span>
                        @endif
                        @if (session('error'))
                        <span class="text-danger">
                            <i class="fe fe-alert-circle me-1"></i> {{ session('error') }}
                        </span>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
