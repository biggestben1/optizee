@extends('layouts.admin')

@section('title', 'Booking Prices')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Booking Prices</li>
@endsection

@section('content')
@if($errors->any())
<div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="card">
    <div class="card-header bg-info-transparent">
        <h4 class="card-title mb-0 text-info"><i class="fe fe-home me-2"></i>Room Prices (Walk-in &amp; Online Booking)</h4>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">
            Online prices are charged when "Online Booking" is selected in Hotel POS. Leave an online price empty to charge the walk-in price.
            Rooms with their own custom price keep it for walk-in bookings.
        </p>
        <form method="POST" action="{{ route('admin.booking-prices.rooms') }}">
            @csrf
            @method('PUT')
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-3">
                    <thead class="table-light">
                        <tr>
                            <th>Room Category</th>
                            <th>Walk-in / Night (₦)</th>
                            <th>Walk-in / Hour (₦)</th>
                            <th class="text-success"><i class="fe fe-globe me-1"></i>Online / Night (₦)</th>
                            <th class="text-success"><i class="fe fe-globe me-1"></i>Online / Hour (₦)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roomCategories as $category)
                        <tr>
                            <td>
                                <strong>{{ $category->name }}</strong>
                                @unless($category->is_active)<span class="badge bg-secondary ms-1">Inactive</span>@endunless
                                @if($category->is_suite)<br><small class="text-muted">Full suite: ₦{{ number_format($category->full_suite_price, 2) }}</small>@endif
                            </td>
                            @foreach(['price_per_room', 'hourly_rate', 'online_price_per_room', 'online_hourly_rate'] as $field)
                            <td>
                                <input type="number" name="categories[{{ $category->id }}][{{ $field }}]" class="form-control"
                                       min="0" step="0.01" value="{{ old("categories.{$category->id}.{$field}", $category->$field) }}"
                                       placeholder="{{ str_starts_with($field, 'online') ? 'Same as walk-in' : '' }}">
                            </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">No room categories yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($roomCategories->isNotEmpty())
            <button type="submit" class="btn btn-primary"><i class="fe fe-save me-2"></i>Save Room Prices</button>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-warning-transparent d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0 text-warning"><i class="fe fe-star me-2"></i>Hall Rental Prices</h4>
        <a href="{{ route('admin.hall-rentals.index') }}#occasions" class="btn btn-sm btn-outline-warning">
            <i class="fe fe-plus me-1"></i>Add / Rename Occasions
        </a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.booking-prices.halls') }}">
            @csrf
            @method('PUT')
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-3">
                    <thead class="table-light">
                        <tr>
                            <th>Occasion</th>
                            <th>Price (₦)</th>
                            <th class="text-center">Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($occasions as $occasion)
                        <tr>
                            <td><strong>{{ $occasion->name }}</strong></td>
                            <td>
                                <input type="number" name="occasions[{{ $occasion->id }}][price]" class="form-control"
                                       min="0" step="0.01" value="{{ old("occasions.{$occasion->id}.price", $occasion->price) }}" required>
                            </td>
                            <td class="text-center">
                                <input type="hidden" name="occasions[{{ $occasion->id }}][is_active]" value="0">
                                <input type="checkbox" class="form-check-input" name="occasions[{{ $occasion->id }}][is_active]" value="1"
                                       @checked(old("occasions.{$occasion->id}.is_active", $occasion->is_active))>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">No hall occasions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($occasions->isNotEmpty())
            <button type="submit" class="btn btn-warning"><i class="fe fe-save me-2"></i>Save Hall Prices</button>
            @endif
        </form>
    </div>
</div>
@endsection
