@extends('layouts.admin')

@section('title', 'Record New Asset')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.assets.index') }}">Assets</a></li>
<li class="breadcrumb-item active" aria-current="page">New Asset</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <form action="{{ route('admin.assets.store') }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Asset Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Asset Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Dell Latitude Laptop, Sofa Set" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <input type="text" name="category" class="form-control" placeholder="e.g. Furniture, Electronics, Vehicle" required list="categoryList">
                            <datalist id="categoryList">
                                <option value="Furniture">
                                <option value="Electronics">
                                <option value="Vehicles">
                                <option value="Kitchen Equipment">
                                <option value="Building">
                                <option value="Land">
                            </datalist>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purchase Date <span class="text-danger">*</span></label>
                            <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purchase Value (₦) <span class="text-danger">*</span></label>
                            <input type="number" name="value" class="form-control" step="0.01" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Serial Number</label>
                            <input type="text" name="serial_number" class="form-control" placeholder="e.g. SN-123456">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Reception, Manager Office">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active / In Use</option>
                                <option value="repair">Under Repair</option>
                                <option value="disposed">Disposed / Sold</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description / Notes</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Additional details about the asset..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('admin.assets.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Asset</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
