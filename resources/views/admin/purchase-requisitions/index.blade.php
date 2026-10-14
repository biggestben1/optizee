@extends('layouts.admin')

@php
    $departments = \App\Models\PurchaseRequisition::DEPARTMENTS;
    $pageTitle = $department ? $departments[$department] . ' Purchases' : 'Purchase Requisitions';
    $badgeColors = ['bar' => 'bg-primary', 'kitchen' => 'bg-warning', 'hotel' => 'bg-info', 'general' => 'bg-secondary'];
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
@endsection

@section('actions')
<a href="{{ route('admin.purchase-requisitions.create', array_filter(['department' => $department])) }}" class="btn btn-primary">
    <i class="fe fe-plus me-2"></i> New {{ $department ? $departments[$department] . ' ' : '' }}Purchase Request
</a>
@endsection

@section('content')
<div class="row mb-3">
    @foreach($departments as $key => $label)
    <div class="col-md-3 col-6 mb-3">
        <a href="{{ route('admin.purchase-requisitions.index', ['department' => $key]) }}" class="text-decoration-none">
            <div class="card h-100 {{ $department === $key ? 'border-primary shadow' : '' }}">
                <div class="card-body text-center">
                    <h4 class="mb-1">₦{{ number_format($departmentTotals[$key]->total ?? 0, 2) }}</h4>
                    <p class="text-muted mb-0">{{ $label }} &middot; {{ $departmentTotals[$key]->count ?? 0 }} this month</p>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link {{ $department === null ? 'active' : '' }}" href="{{ route('admin.purchase-requisitions.index') }}">All</a>
            </li>
            @foreach($departments as $key => $label)
            <li class="nav-item">
                <a class="nav-link {{ $department === $key ? 'active' : '' }}" href="{{ route('admin.purchase-requisitions.index', ['department' => $key]) }}">{{ $label }}</a>
            </li>
            @endforeach
        </ul>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            @if($department)
            <input type="hidden" name="department" value="{{ $department }}">
            @endif
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fe fe-filter me-1"></i> Filter
                </button>
                <a href="{{ route('admin.purchase-requisitions.index', array_filter(['department' => $department])) }}" class="btn btn-secondary">
                    <i class="fe fe-x me-1"></i> Clear
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>PR Number</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Requested By</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prs as $pr)
                    <tr>
                        <td><strong>{{ $pr->pr_number }}</strong></td>
                        <td><span class="badge {{ $badgeColors[$pr->department] ?? 'bg-secondary' }}">{{ $pr->department_label }}</span></td>
                        <td>{{ $pr->pr_date->format('M d, Y') }}</td>
                        <td>{{ $pr->requestedBy->name }}</td>
                        <td>{{ Str::limit($pr->description, 50) }}</td>
                        <td class="text-end">₦{{ number_format($pr->amount, 2) }}</td>
                        <td>
                            @if($pr->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                            @elseif($pr->status === 'approved')
                            <span class="badge bg-success">Approved</span>
                            @elseif($pr->status === 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                            @elseif($pr->status === 'completed')
                            <span class="badge bg-info">Completed</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.purchase-requisitions.show', $pr) }}" class="btn btn-sm btn-info">
                                <i class="fe fe-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No {{ $department ? strtolower($departments[$department]) . ' ' : '' }}purchase requisitions found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $prs->links() }}
        </div>
    </div>
</div>
@endsection
