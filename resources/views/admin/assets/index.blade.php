@extends('layouts.admin')

@section('title', 'Company Assets')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Assets</li>
@endsection

@section('actions')
<a href="{{ route('admin.assets.create') }}" class="btn btn-primary">
    <i class="fe fe-plus me-2"></i> Record New Asset
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form action="" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" placeholder="Search name/serial..." value="{{ request('search') }}" style="max-width: 250px;">
                    <select name="category" class="form-select" style="max-width: 200px;">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    <a href="{{ route('admin.assets.index') }}" class="btn btn-outline-secondary">Reset</a>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap border-bottom">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Serial Number</th>
                                <th>Purchase Date</th>
                                <th class="text-end">Value</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assets as $asset)
                            <tr>
                                <td><strong>{{ $asset->name }}</strong></td>
                                <td>{{ $asset->category }}</td>
                                <td>{{ $asset->serial_number ?? 'N/A' }}</td>
                                <td>{{ $asset->purchase_date->format('M d, Y') }}</td>
                                <td class="text-end">₦{{ number_format($asset->value, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $asset->status == 'active' ? 'success' : ($asset->status == 'repair' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($asset->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.assets.show', $asset) }}" class="btn btn-sm btn-info">
                                        <i class="fe fe-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.assets.edit', $asset) }}" class="btn btn-sm btn-primary">
                                        <i class="fe fe-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No assets found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-info fw-bold">
                                <td colspan="4" class="text-end">TOTAL ASSET VALUE</td>
                                <td class="text-end">₦{{ number_format($assets->sum('value'), 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                {{ $assets->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
