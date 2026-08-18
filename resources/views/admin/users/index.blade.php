@extends('layouts.admin')

@section('title', 'Staff Management')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Staff</li>
@endsection

@section('actions')
<a href="{{ route('admin.users.create') }}" class="btn btn-primary">
    <i class="fe fe-plus me-2"></i> Add Staff
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form action="" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Search staff..." value="{{ request('search') }}" style="max-width: 250px;">
                    <select name="role" class="form-select" style="max-width: 200px;">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-secondary">Filter</button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Login Code</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    @if($user->is_admin)
                                    <span class="badge bg-danger ms-1">Admin</span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->role)
                                    <span class="badge bg-primary">{{ $user->role->display_name }}</span>
                                    @else
                                    <span class="badge bg-secondary">No Role</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->login_code)
                                    <span class="badge bg-info text-dark font-monospace">{{ $user->login_code }}</span>
                                    @else
                                    <span class="badge bg-secondary">No Code</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->is_active)
                                    <span class="badge bg-success">Active</span>
                                    @else
                                    <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if(!$user->is_admin || auth()->user()->is_admin)
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fe fe-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.users.generate-code', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Generate/Update login code for {{ $user->name }}?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-info" title="Generate Login Code">
                                                <i class="fe fe-key"></i>
                                            </button>
                                        </form>
                                        @if($user->id !== auth()->id() && !$user->is_admin)
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fe fe-trash-2"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $users->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection











