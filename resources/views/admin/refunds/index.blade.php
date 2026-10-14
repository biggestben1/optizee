@extends('layouts.admin')

@section('title', 'Refunds')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Refunds</li>
@endsection

@section('actions')
<a href="{{ route('admin.refunds.create') }}" class="btn btn-danger">
    <i class="fe fe-rotate-ccw me-2"></i>Record Refund
</a>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Area of Work</label>
                <select name="area" class="form-select">
                    <option value="">All Areas</option>
                    @foreach(\App\Models\Refund::AREAS as $key => $label)
                    <option value="{{ $key }}" @selected(request('area') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary me-2"><i class="fe fe-filter me-1"></i>Filter</button>
                <a href="{{ route('admin.refunds.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    @foreach($byArea as $area)
    <div class="col-lg col-md-4 col-6 mb-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <h4 class="text-danger mb-1">₦{{ number_format($area['total'], 2) }}</h4>
                <p class="text-muted mb-0">{{ $area['label'] }}</p>
                <small class="text-muted">{{ $area['count'] }} refund(s)</small>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-9 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-0">Refunds ({{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }})</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Refund #</th>
                                <th>Date</th>
                                <th>Area</th>
                                <th>Reference</th>
                                <th>Customer</th>
                                <th>Reason</th>
                                <th>Paid Back By</th>
                                <th class="text-end">Amount</th>
                                <th>Recorded By</th>
                                @if(auth()->user()->is_admin)<th></th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($refunds as $refund)
                            <tr>
                                <td><strong>{{ $refund->refund_number }}</strong></td>
                                <td>{{ $refund->refund_date->format('M d, Y') }}</td>
                                <td><span class="badge bg-info">{{ $refund->area_label }}</span></td>
                                <td>{{ $refund->reference ?: '-' }}</td>
                                <td>
                                    {{ $refund->customer_name ?: '-' }}
                                    @if($refund->customer_phone)<br><small class="text-muted">{{ $refund->customer_phone }}</small>@endif
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($refund->reason, 60) }}</td>
                                <td>{{ strtoupper($refund->payment_method) }}</td>
                                <td class="text-end text-danger fw-bold">₦{{ number_format($refund->amount, 2) }}</td>
                                <td>{{ $refund->user->name ?? 'N/A' }}</td>
                                @if(auth()->user()->is_admin)
                                <td>
                                    <form method="POST" action="{{ route('admin.refunds.destroy', $refund) }}" onsubmit="return confirm('Delete this refund record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fe fe-trash-2"></i></button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No refunds recorded for this period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($refunds->count())
                        <tfoot>
                            <tr class="bg-light">
                                <th colspan="7" class="text-end">Total:</th>
                                <th class="text-end text-danger">₦{{ number_format($refunds->sum('amount'), 2) }}</th>
                                <th colspan="{{ auth()->user()->is_admin ? 2 : 1 }}"></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-0">Paid Back By</h4>
            </div>
            <div class="card-body">
                @foreach(\App\Models\Refund::PAYMENT_METHODS as $key => $label)
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ $label }}</span>
                    <strong class="text-danger">₦{{ number_format($byMethod[$key], 2) }}</strong>
                </div>
                @endforeach
                <hr>
                <small class="text-muted">Cash refunds are deducted from cash at hand in the Cumulative Report.</small>
            </div>
        </div>
    </div>
</div>
@endsection
