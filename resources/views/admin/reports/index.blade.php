@extends('layouts.admin')

@section('title', 'Reports')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Reports</li>
@endsection

@section('content')
<div class="row">
    @if(!auth()->user()->isReceptionist())
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fe fe-calendar text-primary mb-3" style="font-size: 40px;"></i>
                <h4>Daily Report</h4>
                <p class="text-muted">View detailed daily sales and transactions</p>
                <a href="{{ route('admin.reports.daily') }}" class="btn btn-primary">View Report</a>
            </div>
        </div>
    </div>
    @endif
    @if(auth()->user()->canViewFinancialReports())
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fe fe-shopping-cart text-success mb-3" style="font-size: 40px;"></i>
                <h4>Sales Report</h4>
                <p class="text-muted">Comprehensive sales analysis with filters</p>
                <a href="{{ route('admin.reports.sales') }}" class="btn btn-success">View Report</a>
            </div>
        </div>
    </div>
    @endif
    @if(!auth()->user()->isCashier() && !auth()->user()->isReceptionist())
    @if(auth()->user()->canViewFinancialReports())
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fe fe-trending-up text-info mb-3" style="font-size: 40px;"></i>
                <h4>Profit or Loss</h4>
                <p class="text-muted">Revenue, costs, and profit margins</p>
                <a href="{{ route('admin.reports.profit') }}" class="btn btn-info">View Report</a>
            </div>
        </div>
    </div>
    @endif
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fe fe-box text-warning mb-3" style="font-size: 40px;"></i>
                <h4>Products Report</h4>
                <p class="text-muted">Top selling products and stock status</p>
                <a href="{{ route('admin.reports.products') }}" class="btn btn-warning">View Report</a>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fe fe-users text-danger mb-3" style="font-size: 40px;"></i>
                <h4>Customers Report</h4>
                <p class="text-muted">Customer balances and sales history</p>
                <a href="{{ route('admin.reports.customers') }}" class="btn btn-danger">View Report</a>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fe fe-truck text-secondary mb-3" style="font-size: 40px;"></i>
                <h4>Suppliers Report</h4>
                <p class="text-muted">Suppliers balances and purchase history</p>
                <a href="{{ route('admin.reports.suppliers') }}" class="btn btn-secondary">View Report</a>
            </div>
        </div>
    </div>
    @endif
    @if(!auth()->user()->isCashier())
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fe fe-home text-info mb-3" style="font-size: 40px;"></i>
                <h4>Hotel Bookings Report</h4>
                <p class="text-muted">Room bookings, revenue, and occupancy</p>
                <a href="{{ route('admin.reports.hotel-bookings') }}" class="btn btn-info">View Report</a>
            </div>
        </div>
    </div>
    @if(!auth()->user()->isReceptionist())
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fe fe-briefcase text-primary mb-3" style="font-size: 40px;"></i>
                <h4>Hotel Inventory</h4>
                <p class="text-muted">Valuation report for hotel consumables</p>
                <a href="{{ route('admin.hotel-inventory.report') }}" class="btn btn-primary">View Report</a>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>
@endsection
