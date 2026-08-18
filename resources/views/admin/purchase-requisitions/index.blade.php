@extends('layouts.admin')

@section('title', 'Purchase Requisitions')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Purchase Requisitions</li>
@endsection

@section('actions')
<a href="{{ route('admin.purchase-requisitions.create') }}" class="btn btn-primary">
    <i class="fe fe-plus me-2"></i> Create PR
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Purchase Requisitions</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
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
                <a href="{{ route('admin.purchase-requisitions.index') }}" class="btn btn-secondary">
                    <i class="fe fe-x me-1"></i> Clear
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>PR Number</th>
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
                        <td colspan="7" class="text-center text-muted">No purchase requisitions found</td>
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
















