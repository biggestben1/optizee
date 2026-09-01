<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Optizee Hotel and Suites - POS & Management System">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Dashboard') - Optizee Hotel and Suites">
    <meta property="og:description" content="Optizee Hotel and Suites - POS & Management System">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#5e72e4">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Optizee Hotel and Suites">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Optizee Hotel and Suites">

    <link rel="icon" type="image/jpeg" href="{{ asset('logo.jpg') }}?v={{ time() }}" />
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('logo.jpg') }}?v={{ time() }}" />
    <link rel="apple-touch-icon" href="{{ asset('logo.jpg') }}?v={{ time() }}">
    <link rel="manifest" href="/manifest.json">

    <title>@yield('title', 'Dashboard') - Optizee Hotel and Suites</title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="/sash/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

    <!-- STYLE CSS -->
    <link href="/sash/assets/css/style.css" rel="stylesheet" />
    <link href="/sash/assets/css/dark-style.css" rel="stylesheet" />
    <link href="/sash/assets/css/transparent-style.css" rel="stylesheet">
    <link href="/sash/assets/css/skin-modes.css" rel="stylesheet" />

    <!-- FONT-ICONS CSS -->
    <link href="/sash/assets/css/icons.css" rel="stylesheet" />

    <!-- COLOR SKIN CSS -->
    <link id="theme" rel="stylesheet" type="text/css" media="all" href="/sash/assets/colors/color1.css" />

    <!-- DATA TABLE CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet" />

    <!-- SELECT2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* Keep logo from overlapping sidebar menu items */
        .app-header .header-brand-img,
        .app-sidebar .side-header .header-brand-img {
            height: 110px !important;
            width: auto !important;
            max-width: 220px;
            object-fit: contain;
        }
        .app-sidebar .side-header {
            padding-top: 16px;
            padding-bottom: 16px;
        }
    </style>

    @stack('styles')
</head>

<body class="app sidebar-mini ltr light-mode">

    <!-- GLOBAL-LOADER removed -->

    <!-- PAGE -->
    <div class="page">
        <div class="page-main">

            <!-- APP-HEADER -->
            <div class="app-header header sticky">
                <div class="container-fluid main-container">
                    <div class="d-flex">
                        <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar" href="javascript:void(0)"></a>
                        <a class="logo-horizontal" href="{{ auth()->user()->isKitchen() ? route('admin.kitchen.index') : (auth()->user()->isReceptionist() ? route('admin.hotel-pos.index') : route('admin.dashboard')) }}">
                            <img src="{{ asset('logo.jpg') }}?v={{ time() }}" class="header-brand-img desktop-logo" alt="Optizee Hotel and Suites">
                            <img src="{{ asset('logo.jpg') }}?v={{ time() }}" class="header-brand-img light-logo1" alt="Optizee Hotel and Suites">
                        </a>
                        <div class="d-flex order-lg-2 ms-auto header-right-icons">
                            <div class="navbar navbar-collapse responsive-navbar p-0">
                                <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
                                    <div class="d-flex order-lg-2">
                                        <div class="d-flex country">
                                            <a class="nav-link icon theme-layout nav-link-bg layout-setting">
                                                <span class="dark-layout"><i class="fe fe-moon"></i></span>
                                                <span class="light-layout"><i class="fe fe-sun"></i></span>
                                            </a>
                                        </div>
                                        <div class="dropdown d-flex">
                                            <a class="nav-link icon full-screen-link nav-link-bg">
                                                <i class="fe fe-minimize fullscreen-button"></i>
                                            </a>
                                        </div>
                                        <!-- PWA Install Button -->
                                        <div class="dropdown d-flex align-items-center me-2" id="pwa-install-container">
                                            <a href="javascript:void(0)" id="pwa-install-button" class="nav-link icon nav-link-bg d-flex align-items-center justify-content-center" title="Install Optizee Hotel and Suites App" style="color: #5e72e4; cursor: pointer; width: 40px; height: 40px; border-radius: 8px; background: rgba(94, 114, 228, 0.1); transition: all 0.3s;" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Install App" onmouseover="this.style.background='rgba(94, 114, 228, 0.2)'; this.style.transform='scale(1.1)'" onmouseout="this.style.background='rgba(94, 114, 228, 0.1)'; this.style.transform='scale(1)'">
                                                <i class="fe fe-download" style="font-size: 18px; font-weight: bold;"></i>
                                            </a>
                                        </div>
                                        @php $currentShift = auth()->user()->getOpenShift(); @endphp
                                        @if(!auth()->user()->isKitchen() && !auth()->user()->isReceptionist())
                                        @if($currentShift)
                                        <div class="d-flex align-items-center me-3">
                                            <span class="badge bg-success">Shift Active</span>
                                        </div>
                                        @else
                                        <div class="d-flex align-items-center me-3">
                                            <span class="badge bg-warning">No Active Shift</span>
                                        </div>
                                        @endif
                                        @endif
                                        <div class="dropdown d-flex profile-1">
                                            <a href="javascript:void(0)" data-bs-toggle="dropdown" class="nav-link leading-none d-flex">
                                                <img src="/sash/assets/images/users/21.jpg" alt="profile-user" class="avatar profile-user brround cover-image">
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                                <div class="drop-heading">
                                                    <div class="text-center">
                                                        <h5 class="text-dark mb-0 fs-14 fw-semibold">{{ Auth::user()->name }}</h5>
                                                        <small class="text-muted">{{ Auth::user()->role?->display_name ?? 'Administrator' }}</small>
                                                    </div>
                                                </div>
                                                <div class="dropdown-divider m-0"></div>
                                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                                    <i class="dropdown-icon fe fe-user"></i> Profile
                                                </a>
                                                @if($currentShift && !auth()->user()->isKitchen() && !auth()->user()->isReceptionist())
                                                <a class="dropdown-item" href="{{ route('admin.shifts.current') }}">
                                                    <i class="dropdown-icon fe fe-clock"></i> Current Shift
                                                </a>
                                                @endif
                                                <form method="POST" action="{{ route('logout') }}">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="dropdown-icon fe fe-alert-circle"></i> Sign out
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /APP-HEADER -->

            <!-- APP-SIDEBAR -->
            <div class="sticky">
                <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
                <div class="app-sidebar">
                    <div class="side-header">
                        <a class="header-brand1" href="{{ auth()->user()->isKitchen() ? route('admin.kitchen.index') : (auth()->user()->isReceptionist() ? route('admin.hotel-pos.index') : route('admin.dashboard')) }}">
                            <img src="{{ asset('logo.jpg') }}?v={{ time() }}" class="header-brand-img desktop-logo" alt="Optizee Hotel and Suites">
                            <img src="{{ asset('logo.jpg') }}?v={{ time() }}" class="header-brand-img toggle-logo" alt="Optizee Hotel and Suites">
                            <img src="{{ asset('logo.jpg') }}?v={{ time() }}" class="header-brand-img light-logo" alt="Optizee Hotel and Suites">
                            <img src="{{ asset('logo.jpg') }}?v={{ time() }}" class="header-brand-img light-logo1" alt="Optizee Hotel and Suites">
                        </a>
                    </div>
                    <div class="main-sidemenu">
                        <div class="slide-left disabled" id="slide-left">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                                <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                            </svg>
                        </div>
                        <ul class="side-menu">
                            @if(auth()->user()->isKitchen())
                            <li class="sub-category">
                                <h3>Kitchen</h3>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.kitchen.*') && !request()->routeIs('admin.kitchen.report') ? 'active' : '' }}" href="{{ route('admin.kitchen.index') }}">
                                    <i class="side-menu__icon fe fe-coffee"></i>
                                    <span class="side-menu__label">Kitchen Display</span>
                                </a>
                            </li>
                            @if(\Illuminate\Support\Facades\Route::has('admin.kitchen.report'))
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.kitchen.report') ? 'active' : '' }}" href="{{ route('admin.kitchen.report') }}">
                                    <i class="side-menu__icon fe fe-bar-chart-2"></i>
                                    <span class="side-menu__label">Kitchen Report</span>
                                </a>
                            </li>
                            @endif
                            @elseif(auth()->user()->isReceptionist())
                            <li class="sub-category">
                                <h3>Front Desk</h3>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.hotel-pos.*') ? 'active' : '' }}" href="{{ route('admin.hotel-pos.index') }}">
                                    <i class="side-menu__icon fe fe-home"></i>
                                    <span class="side-menu__label">Hotel POS</span>
                                </a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.room-bookings.*') ? 'active' : '' }}" href="{{ route('admin.room-bookings.index') }}">
                                    <i class="side-menu__icon fe fe-calendar"></i>
                                    <span class="side-menu__label">Room Bookings</span>
                                </a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                                    <i class="side-menu__icon fe fe-bar-chart-2"></i>
                                    <span class="side-menu__label">Reports</span>
                                </a>
                            </li>
                            @else
                            <li class="sub-category">
                                <h3>Main</h3>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                    <i class="side-menu__icon fe fe-home"></i>
                                    <span class="side-menu__label">Dashboard</span>
                                </a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.pos.*') && !request()->routeIs('admin.pos.supervisor*') ? 'active' : '' }}" href="{{ route('admin.pos.index') }}">
                                    <i class="side-menu__icon fe fe-shopping-cart"></i>
                                    <span class="side-menu__label">POS / Sales</span>
                                </a>
                            </li>
                            @if(!auth()->user()->isCashier())
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.hotel-pos.*') ? 'active' : '' }}" href="{{ route('admin.hotel-pos.index') }}">
                                    <i class="side-menu__icon fe fe-home"></i>
                                    <span class="side-menu__label">Hotel POS</span>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->is_admin || (auth()->user()->role && (auth()->user()->isSupervisor() || auth()->user()->isManager())))
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.pos.supervisor*') ? 'active' : '' }}" href="{{ route('admin.pos.supervisor') }}">
                                    <i class="side-menu__icon fe fe-eye"></i>
                                    <span class="side-menu__label">Supervisor Dashboard</span>
                                </a>
                            </li>
                            @endif
                            @if(!auth()->user()->isCashier())
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.tables.*') ? 'active' : '' }}" href="{{ route('admin.tables.index') }}">
                                    <i class="side-menu__icon fe fe-grid"></i>
                                    <span class="side-menu__label">Tables</span>
                                </a>
                            </li>
                            @endif

                            @if(auth()->user()->is_admin || auth()->user()->isManager())
                            <li class="sub-category">
                                <h3>Accounting</h3>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">
                                    <i class="side-menu__icon fe fe-user"></i>
                                    <span class="side-menu__label">Customers</span>
                                </a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}" href="{{ route('admin.suppliers.index') }}">
                                    <i class="side-menu__icon fe fe-truck"></i>
                                    <span class="side-menu__label">Suppliers</span>
                                </a>
                            </li>
                            <li class="sub-category">
                                <h3>Inventory</h3>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                                    <i class="side-menu__icon fe fe-folder"></i>
                                    <span class="side-menu__label">Categories</span>
                                </a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                                    <i class="side-menu__icon fe fe-box"></i>
                                    <span class="side-menu__label">Products</span>
                                </a>
                            </li>
                            @endif

                            <li class="sub-category">
                                <h3>Operations</h3>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                                    <i class="side-menu__icon fe fe-bar-chart-2"></i>
                                    <span class="side-menu__label">Reports</span>
                                </a>
                            </li>
                            @if(auth()->user()->is_admin || auth()->user()->isManager())
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.purchase-requisitions.*') ? 'active' : '' }}" href="{{ route('admin.purchase-requisitions.index') }}">
                                    <i class="side-menu__icon fe fe-file-text"></i>
                                    <span class="side-menu__label">Purchase Requisitions</span>
                                </a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}" href="{{ route('admin.expenses.index') }}">
                                    <i class="side-menu__icon fe fe-dollar-sign"></i>
                                    <span class="side-menu__label">Expenses</span>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->is_admin)
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.owner-purchases.*') ? 'active' : '' }}" href="{{ route('admin.owner-purchases.index') }}">
                                    <i class="side-menu__icon fe fe-shopping-bag"></i>
                                    <span class="side-menu__label">Owner Purchases</span>
                                </a>
                            </li>
                            @endif

                            @if(auth()->user()->is_admin || auth()->user()->isManager())
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.printers.*') ? 'active' : '' }}" href="{{ route('admin.printers.index') }}">
                                    <i class="side-menu__icon fe fe-printer"></i>
                                    <span class="side-menu__label">Printer Settings</span>
                                </a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.bank-settings.*') ? 'active' : '' }}" href="{{ route('admin.bank-settings.edit') }}">
                                    <i class="side-menu__icon fe fe-credit-card"></i>
                                    <span class="side-menu__label">Bank Settings</span>
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->is_admin)
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.go-live.*') ? 'active' : '' }}" href="{{ route('admin.go-live.index') }}">
                                    <i class="side-menu__icon fe fe-zap"></i>
                                    <span class="side-menu__label">Ready to Go Live</span>
                                </a>
                            </li>
                            @endif

                            @if((auth()->user()->is_admin || auth()->user()->isManager() || auth()->user()->isSupervisor()) && !auth()->user()->isCashier())
                            <li class="sub-category">
                                <h3>Hotel Management</h3>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.room-categories.*') ? 'active' : '' }}" href="{{ route('admin.room-categories.index') }}">
                                    <i class="side-menu__icon fe fe-tag"></i>
                                    <span class="side-menu__label">Room Categories</span>
                                </a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}" href="{{ route('admin.rooms.index') }}">
                                    <i class="side-menu__icon fe fe-home"></i>
                                    <span class="side-menu__label">Rooms</span>
                                </a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.room-bookings.*') ? 'active' : '' }}" href="{{ route('admin.room-bookings.index') }}">
                                    <i class="side-menu__icon fe fe-calendar"></i>
                                    <span class="side-menu__label">Room Bookings</span>
                                </a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.sections.*') ? 'active' : '' }}" href="{{ route('admin.sections.index') }}">
                                    <i class="side-menu__icon fe fe-layers"></i>
                                    <span class="side-menu__label">Sections</span>
                                </a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.assignments.*') ? 'active' : '' }}" href="{{ route('admin.assignments.index') }}">
                                    <i class="side-menu__icon fe fe-user-check"></i>
                                    <span class="side-menu__label">Staff Assignments</span>
                                </a>
                            </li>
                            @endif

                            @if(auth()->user()->canAccessKitchen())
                            <li class="sub-category">
                                <h3>Kitchen</h3>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.kitchen.*') && !request()->routeIs('admin.kitchen.report') ? 'active' : '' }}" href="{{ route('admin.kitchen.index') }}">
                                    <i class="side-menu__icon fe fe-coffee"></i>
                                    <span class="side-menu__label">Kitchen Display</span>
                                </a>
                            </li>
                            @if(\Illuminate\Support\Facades\Route::has('admin.kitchen.report'))
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.kitchen.report') ? 'active' : '' }}" href="{{ route('admin.kitchen.report') }}">
                                    <i class="side-menu__icon fe fe-bar-chart-2"></i>
                                    <span class="side-menu__label">Kitchen Report</span>
                                </a>
                            </li>
                            @endif
                            @endif

                            @if((auth()->user()->is_admin || auth()->user()->isManager()) && !auth()->user()->isCashier())
                            <li class="sub-category">
                                <h3>Administration</h3>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                    <i class="side-menu__icon fe fe-user-plus"></i>
                                    <span class="side-menu__label">Staff Management</span>
                                </a>
                            </li>
                            @endif
                            @endif

                            <!-- Account Section - Always visible at bottom for mobile -->
                            <li class="sub-category">
                                <h3>Account</h3>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                                    <i class="side-menu__icon fe fe-user"></i>
                                    <span class="side-menu__label">Profile</span>
                                </a>
                            </li>
                            <li class="slide">
                                <form method="POST" action="{{ route('logout') }}" class="w-100 m-0">
                                    @csrf
                                    <button type="submit" class="side-menu__item w-100 text-start border-0 bg-transparent" style="color: inherit; cursor: pointer; padding: 0.75rem 1.5rem;">
                                        <i class="side-menu__icon fe fe-log-out" style="color: #dc3545;"></i>
                                        <span class="side-menu__label" style="color: #dc3545;">Sign Out</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                        <div class="slide-right" id="slide-right">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                                <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /APP-SIDEBAR -->

            <!-- APP-CONTENT -->
            <div class="main-content app-content mt-0">
                <div class="side-app">
                    <div class="main-container container-fluid">

                        <!-- PAGE-HEADER -->
                        <div class="page-header">
                            <div>
                                <h1 class="page-title">@yield('title', 'Dashboard')</h1>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ auth()->user()->isKitchen() ? route('admin.kitchen.index') : (auth()->user()->isReceptionist() ? route('admin.hotel-pos.index') : route('admin.dashboard')) }}">Home</a></li>
                                    @yield('breadcrumb')
                                </ol>
                            </div>
                            <div class="ms-auto pageheader-btn">
                                @yield('actions')
                            </div>
                        </div>
                        <!-- /PAGE-HEADER -->

                        <!-- Alerts -->
                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        @yield('content')

                    </div>
                </div>
            </div>
            <!-- /APP-CONTENT -->
        </div>

    </div>
    <!-- /PAGE -->

    <!-- BACK-TO-TOP -->
    <a href="#top" id="back-to-top"><i class="fa fa-angle-up"></i></a>

    <!-- JQUERY JS -->
    <script src="/sash/assets/js/jquery.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <script src="/sash/assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="/sash/assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <!-- SPARKLINE JS -->
    <script src="/sash/assets/js/jquery.sparkline.min.js"></script>

    <!-- SIDE-MENU JS -->
    <script src="/sash/assets/plugins/sidemenu/sidemenu.js"></script>

    <!-- Perfect SCROLLBAR JS-->
    <script src="/sash/assets/plugins/p-scroll/perfect-scrollbar.js"></script>
    <script src="/sash/assets/plugins/p-scroll/pscroll.js"></script>
    <script src="/sash/assets/plugins/p-scroll/pscroll-1.js"></script>

    <!-- STICKY JS -->
    <script src="/sash/assets/js/sticky.js"></script>

    <!-- Color Theme js -->
    <script src="/sash/assets/js/themeColors.js"></script>

    <!-- DATA TABLE JS -->
    <script src="/sash/assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="/sash/assets/plugins/datatable/js/dataTables.bootstrap5.js"></script>
    <script src="/sash/assets/plugins/datatable/dataTables.responsive.min.js"></script>
    <script src="/sash/assets/plugins/datatable/responsive.bootstrap5.min.js"></script>

    <!-- SELECT2 JS -->
    <script src="/sash/assets/plugins/select2/select2.full.min.js" onerror="console.warn('Select2 library not found, skipping...')"></script>

    <!-- CUSTOM JS -->
    <script src="/sash/assets/js/custom.js"></script>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();

        // Initialize Select2 only if elements exist and library is loaded
        $(document).ready(function() {
            if (typeof $.fn.select2 !== 'undefined' && $('.select2').length > 0) {
                $('.select2').select2();
            }
        });

        // CSRF Token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    @stack('scripts')

    <!-- PWA Service Worker Registration -->
    <script>
    // Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js', { scope: '/' })
                .then(function(registration) {
                    console.log('[PWA] ServiceWorker registration successful with scope: ', registration.scope);

                    // Check for updates
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                // New service worker available, notify user
                                console.log('[PWA] New version available! Refresh to update.');
                            }
                        });
                    });
                })
                .catch(function(err) {
                    console.log('[PWA] ServiceWorker registration failed: ', err);
                });
        });

        // Handle service worker updates
        let refreshing = false;
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            if (!refreshing) {
                refreshing = true;
                window.location.reload();
            }
        });
    }

    // Handle PWA install prompt
    let deferredPrompt;
    const installButton = document.getElementById('pwa-install-button');
    const installContainer = document.getElementById('pwa-install-container');

    // Initially check if already installed
    if (installButton && installContainer) {
        if (window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone === true) {
            installContainer.style.display = 'none';
        } else {
            // Button is visible by default, will be fully enabled when prompt is available
            installButton.style.opacity = '1';
            installButton.style.cursor = 'pointer';
            installButton.title = 'Install Optizee Hotel and Suites App (Click to install)';
        }
    }

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        console.log('[PWA] Install prompt available');

        // Enable install button
        if (installButton && installContainer) {
            installContainer.style.display = 'block';
            installButton.style.display = 'block';
            installButton.style.opacity = '1';
            installButton.style.cursor = 'pointer';
        }
    });

    // Handle install button click
    if (installButton) {
        installButton.addEventListener('click', async () => {
            if (!deferredPrompt) {
                // Check if already installed
                if (window.matchMedia('(display-mode: standalone)').matches ||
                    window.navigator.standalone === true) {
                    alert('App is already installed!');
                    return;
                }

                // Show detailed instructions for manual installation
                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
                const isAndroid = /Android/.test(navigator.userAgent);
                const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
                const isEdge = /Edg/.test(navigator.userAgent);

                let instructions = '';
                let title = 'Install Optizee Hotel and Suites';

                if (isIOS) {
                    instructions = 'To install on iOS:\n\n1. Tap the Share button (square with arrow) at the bottom\n2. Scroll down and tap "Add to Home Screen"\n3. Tap "Add" to confirm';
                } else if (isAndroid) {
                    instructions = 'To install on Android:\n\nMethod 1 - Menu:\n1. Tap the menu (⋮) in the top right\n2. Look for "Install app" or "Add to Home screen"\n3. Tap it and confirm\n\nMethod 2 - Banner:\n1. Look for a pop-up banner at the bottom\n2. Tap "Install"\n\nNote: If you don\'t see the option, visit the site a few more times.';
                } else if (isChrome || isEdge) {
                    instructions = 'To install in Chrome/Edge:\n\nIMPORTANT: The install icon may not appear in the address bar immediately.\n\nMethod 1 - Menu (Most Reliable):\n1. Click the menu (⋮) in the top right corner\n2. Look for "Install Optizee Hotel and Suites" or "Install app"\n3. Click it\n\nMethod 2 - Address Bar:\n1. Look for a small install icon (➕) in the address bar\n2. Click it and select "Install"\n\nMethod 3 - Wait for Auto-Prompt:\n1. Visit the site a few more times\n2. The browser will show the install option automatically\n\nWhy it might not show:\n- Need to visit the site multiple times\n- Browser needs to recognize it as installable\n- Try refreshing the page a few times';
                } else {
                    instructions = 'To install:\n\n1. Look for install option in your browser menu\n2. Or visit the site multiple times\n3. The browser will show the install option when ready';
                }

                // Show instructions
                alert(title + '\n\n' + instructions);
                return;
            }

            // Show the install prompt
            deferredPrompt.prompt();

            // Wait for the user to respond
            const { outcome } = await deferredPrompt.userChoice;

            if (outcome === 'accepted') {
                console.log('[PWA] User accepted the install prompt');
                if (typeof showNotification === 'function') {
                    showNotification('App installation started!', 'success');
                } else {
                    alert('Installation started!');
                }
            } else {
                console.log('[PWA] User dismissed the install prompt');
            }

            // Clear the deferred prompt
            deferredPrompt = null;

            // Disable the button
            if (installButton) {
                installButton.style.opacity = '0.5';
                installButton.style.cursor = 'not-allowed';
            }
        });
    }

    // Listen for app installed
    window.addEventListener('appinstalled', (evt) => {
        console.log('[PWA] App installed successfully');
        deferredPrompt = null;

        // Hide install button
        if (installButton && installContainer) {
            installButton.style.display = 'none';
            installContainer.style.display = 'none';
        }

        // Show success message
        if (typeof showNotification === 'function') {
            showNotification('App installed successfully!', 'success');
        } else {
            alert('App installed successfully!');
        }
    });

    // Check if app is already installed (hide button if so)
    if (window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true) {
        if (installButton && installContainer) {
            installButton.style.display = 'none';
            installContainer.style.display = 'none';
        }
    }

    // Check if app is running in standalone mode
    if (window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true) {
        console.log('[PWA] Running in standalone mode');
        document.body.classList.add('pwa-standalone');
    }

    // Initialize tooltip for install button when it becomes visible
    if (typeof $ !== 'undefined' && installButton) {
        const observer = new MutationObserver(function(mutations) {
            if (installButton.style.display !== 'none' && installButton.offsetParent !== null) {
                $(installButton).tooltip();
            }
        });
        observer.observe(installButton, { attributes: true, attributeFilter: ['style', 'class'] });
    }

    // Network status detection
    window.addEventListener('online', () => {
        console.log('[PWA] Online');
        // Ask service worker to replay any offline queued requests
        try {
            if (navigator.serviceWorker && navigator.serviceWorker.controller) {
                navigator.serviceWorker.controller.postMessage({ type: 'SYNC_QUEUE' });
            }
        } catch (e) {}
        if (typeof showNotification === 'function') {
            showNotification('Connection restored', 'success');
        }
    });

    window.addEventListener('offline', () => {
        console.log('[PWA] Offline');
        if (typeof showNotification === 'function') {
            showNotification('No internet connection. Some features may be limited.', 'warning');
        }
    });
    </script>

</body>

</html>


