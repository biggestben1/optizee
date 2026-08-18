@extends('layouts.admin')

@section('title', 'Install App')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fe fe-download me-2"></i>Install Optizee Hotel and Suites App</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fe fe-chrome me-2 text-primary"></i>Chrome / Edge (Desktop)</h5>
                            <ol>
                                <li>Look for the <strong>install icon (➕)</strong> in the address bar</li>
                                <li>Click it and select <strong>"Install"</strong></li>
                                <li>OR click the <strong>menu (⋮)</strong> → <strong>"Install Optizee Hotel and Suites"</strong></li>
                            </ol>
                            <p class="text-muted small">
                                <strong>Note:</strong> The install option may appear after visiting the site a few times.
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fe fe-smartphone me-2 text-success"></i>Android (Chrome)</h5>
                            <ol>
                                <li>Tap the <strong>menu (⋮)</strong> in the top right</li>
                                <li>Look for <strong>"Install app"</strong> or <strong>"Add to Home screen"</strong></li>
                                <li>Tap it and confirm</li>
                            </ol>
                            <p class="text-muted small">
                                OR look for a pop-up banner at the bottom and tap <strong>"Install"</strong>
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fe fe-apple me-2 text-secondary"></i>iOS (Safari)</h5>
                            <ol>
                                <li>Tap the <strong>Share button</strong> (square with arrow) at the bottom</li>
                                <li>Scroll down and tap <strong>"Add to Home Screen"</strong></li>
                                <li>Tap <strong>"Add"</strong> to confirm</li>
                            </ol>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fe fe-info me-2 text-info"></i>Why isn't it showing?</h5>
                            <ul class="small">
                                <li>Visit the site a few more times</li>
                                <li>Make sure you're using a modern browser</li>
                                <li>Check that the site is accessible</li>
                                <li>Try clearing browser cache and reloading</li>
                            </ul>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                            <i class="fe fe-arrow-left me-2"></i>Back to Dashboard
                        </a>
                        <button onclick="window.location.reload()" class="btn btn-outline-primary">
                            <i class="fe fe-refresh-cw me-2"></i>Refresh Page
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection






