@extends('layouts.admin')

@section('title', 'Customers Report')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
<li class="breadcrumb-item active" aria-current="page">Customers Report</li>
@endsection

@section('content')
<!-- Export Button -->
<div class="mb-4 text-end">
    <a href="{{ route('admin.reports.export.customers') }}" class="btn btn-success">
        <i class="fe fe-download me-1"></i> Download Excel
    </a>
</div>

<!-- Summary -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-danger-transparent">
            <div class="card-body text-center">
                <h3 class="text-danger mb-1">₦{{ number_format($totalDebt, 2) }}</h3>
                <p class="text-muted mb-0">Total Outstanding Balances</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning-transparent">
            <div class="card-body text-center">
                <h3 class="text-warning mb-1">{{ $customersWithDebt->count() }}</h3>
                <p class="text-muted mb-0">Customers with Balances</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info-transparent">
            <div class="card-body text-center">
                <h3 class="text-info mb-1">{{ $topCustomers->count() }}</h3>
                <p class="text-muted mb-0">Active Customers</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Customers with Debt -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header bg-danger-transparent">
                <h4 class="card-title mb-0 text-danger">
                    <i class="fe fe-alert-circle me-2"></i>
                    Customers with Outstanding Balances
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover datatable">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th class="text-end">Balance</th>
                                <th class="text-end">Credit Limit</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customersWithDebt as $customer)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.customers.show', $customer) }}">
                                        {{ $customer->name }}
                                    </a>
                                </td>
                                <td>{{ $customer->phone }}</td>
                                <td class="text-end text-danger">
                                    <strong>₦{{ number_format($customer->credit_balance, 2) }}</strong>
                                </td>
                                <td class="text-end">₦{{ number_format($customer->credit_limit, 2) }}</td>
                                <td>
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fe fe-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    <i class="fe fe-check-circle text-success"></i> No outstanding balances
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($customersWithDebt->count() > 0)
                        <tfoot>
                            <tr class="bg-light">
                                <th colspan="2">Total</th>
                                <th class="text-end text-danger">₦{{ number_format($totalDebt, 2) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header bg-success-transparent">
                <h4 class="card-title mb-0 text-success">
                    <i class="fe fe-award me-2"></i>
                    Top Customers by Sales History
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th class="text-end">Total Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCustomers as $index => $customer)
                            @if($customer->total_purchases > 0)
                            <tr>
                                <td>
                                    @if($index < 3)
                                    <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'danger') }}">
                                        {{ $index + 1 }}
                                    </span>
                                    @else
                                    {{ $index + 1 }}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.customers.show', $customer) }}">
                                        {{ $customer->name }}
                                    </a>
                                </td>
                                <td class="text-end text-success">
                                    <strong>₦{{ number_format($customer->total_purchases, 2) }}</strong>
                                </td>
                            </tr>
                            @endif
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No customer purchases recorded</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.datatable').DataTable({
        order: [[2, 'desc']],
        pageLength: 25
    });
});
</script>
@endpush

