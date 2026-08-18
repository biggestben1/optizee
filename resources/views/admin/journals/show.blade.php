@extends('layouts.admin')

@section('title', 'Journal Entry Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.journals.index') }}">Journals</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ $journal->reference_number }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Journal Entry: {{ $journal->reference_number }}</h3>
                <a href="{{ route('admin.journals.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fe fe-arrow-left"></i> Back to List
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <p class="mb-1 text-muted text-uppercase small font-weight-bold">Entry Date</p>
                        <p class="h5">{{ $journal->entry_date->format('M d, Y') }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1 text-muted text-uppercase small font-weight-bold">Status</p>
                        <span class="badge bg-{{ $journal->status == 'posted' ? 'success' : 'secondary' }} h6">
                            {{ ucfirst($journal->status) }}
                        </span>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1 text-muted text-uppercase small font-weight-bold">Recorded By</p>
                        <p class="h5">{{ $journal->user->name }}</p>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <p class="mb-1 text-muted text-uppercase small font-weight-bold">Description / Narrative</p>
                        <p class="h5">{{ $journal->description }}</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>Account Type</th>
                                <th>Account Name</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($journal->items as $item)
                            <tr>
                                <td>{{ $item->account_type }}</td>
                                <td>
                                    @if($item->account_type === 'Supplier' && $item->account)
                                        <a href="{{ route('admin.suppliers.show', $item->account) }}">
                                            {{ $item->account->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">General Ledger</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold {{ $item->debit > 0 ? 'text-primary' : '' }}">
                                    {{ $item->debit > 0 ? '₦' . number_format($item->debit, 2) : '-' }}
                                </td>
                                <td class="text-end fw-bold {{ $item->credit > 0 ? 'text-danger' : '' }}">
                                    {{ $item->credit > 0 ? '₦' . number_format($item->credit, 2) : '-' }}
                                </td>
                                <td><small class="text-muted">{{ $item->description }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-info font-weight-bold h5">
                                <td colspan="2" class="text-end">TOTALS</td>
                                <td class="text-end">₦{{ number_format($journal->items->sum('debit'), 2) }}</td>
                                <td class="text-end">₦{{ number_format($journal->items->sum('credit'), 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
