@extends('layouts.admin')

@section('title', 'Cloud Footer')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Cloud Footer</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Footer Content</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.cloud-footer.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Body</label>
                <textarea name="footer_body" class="form-control" rows="6">{{ old('footer_body', $footer_body) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fe fe-save me-2"></i>Save
            </button>
        </form>
    </div>
</div>
@endsection

