@extends('layouts.admin')

@section('title', 'Journal Entries')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Journals</li>
@endsection

@section('actions')
<a href="{{ route('admin.journals.create') }}" class="btn btn-primary">
    <i class="fe fe-plus me-2"></i> New Journal Entry
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form action="" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Search reference/desc..." value="{{ request('search') }}" style="max-width: 250px;">
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    <a href="{{ route('admin.journals.index') }}" class="btn btn-outline-secondary">Reset</a>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Description</th>
                                <th>Total Amount</th>
                                <th>Created By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $entry)
                            <tr>
                                <td>{{ $entry->entry_date->format('M d, Y') }}</td>
                                <td><strong>{{ $entry->reference_number }}</strong></td>
                                <td>{{ Str::limit($entry->description, 50) }}</td>
                                <td>₦{{ number_format($entry->items->sum('debit'), 2) }}</td>
                                <td>{{ $entry->user->name }}</td>
                                <td>
                                    <span class="badge bg-{{ $entry->status == 'posted' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($entry->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.journals.show', $entry) }}" class="btn btn-sm btn-info">
                                        <i class="fe fe-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No journal entries found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $entries->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
