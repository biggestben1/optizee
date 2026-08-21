@extends('layouts.admin')

@section('title', 'Kitchen Report')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.kitchen.index') }}">Kitchen</a></li>
<li class="breadcrumb-item active" aria-current="page">Report</li>
@endsection

@section('actions')
<a href="{{ route('admin.kitchen.index') }}" class="btn btn-outline-primary">
    <i class="fe fe-coffee me-1"></i> Kitchen Display
</a>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Select Date</label>
                <input type="date" name="date" class="form-control" value="{{ $date->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fe fe-filter me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-2">
        <div class="card bg-warning-transparent">
            <div class="card-body text-center">
                <h3 class="text-warning mb-1">{{ $stats['pending'] }}</h3>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info-transparent">
            <div class="card-body text-center">
                <h3 class="text-info mb-1">{{ $stats['preparing'] }}</h3>
                <p class="text-muted mb-0">Preparing</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-success-transparent">
            <div class="card-body text-center">
                <h3 class="text-success mb-1">{{ $stats['ready'] }}</h3>
                <p class="text-muted mb-0">Ready</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary-transparent">
            <div class="card-body text-center">
                <h3 class="text-secondary mb-1">{{ $stats['served'] }}</h3>
                <p class="text-muted mb-0">Served</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-primary-transparent">
            <div class="card-body text-center">
                <h3 class="text-primary mb-1">{{ $stats['total'] }}</h3>
                <p class="text-muted mb-0">Orders</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-danger-transparent">
            <div class="card-body text-center">
                <h3 class="text-danger mb-1">{{ $stats['items'] }}</h3>
                <p class="text-muted mb-0">Food Items</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h4 class="card-title mb-0">Kitchen Orders — {{ $date->format('M d, Y') }}</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Items</th>
                        <th>Prepared By</th>
                        <th>Ready At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>
                            <strong>{{ $order->invoice_number }}</strong>
                            @if($order->customer)
                            <br><small class="text-muted">{{ $order->customer->name }}</small>
                            @endif
                        </td>
                        <td>{{ $order->created_at->format('H:i') }}</td>
                        <td>
                            @if($order->kitchen_status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                            @elseif($order->kitchen_status === 'preparing')
                            <span class="badge bg-info">Preparing</span>
                            @elseif($order->kitchen_status === 'ready')
                            <span class="badge bg-success">Ready</span>
                            @elseif($order->kitchen_status === 'served')
                            <span class="badge bg-secondary">Served</span>
                            @else
                            <span class="badge bg-light text-dark">{{ ucfirst($order->kitchen_status) }}</span>
                            @endif
                        </td>
                        <td>
                            @foreach($order->items as $item)
                            <div>{{ $item->quantity }}× {{ $item->product_name }}</div>
                            @endforeach
                        </td>
                        <td>{{ $order->preparedBy->name ?? '—' }}</td>
                        <td>
                            {{ $order->kitchen_ready_at ? $order->kitchen_ready_at->format('H:i') : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No kitchen orders for this date.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
