<!doctype html>
<html lang="en" dir="ltr">

<head>
    @php
        $brandLogoPath = public_path('logo.jpg');
        $brandLogoVer = is_file($brandLogoPath) ? (string) filemtime($brandLogoPath) : '1';
    @endphp

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Optizee Hotel and Suites - Admin Login">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="Login - Optizee Hotel and Suites">
    <meta property="og:description" content="Optizee Hotel and Suites - Admin Login">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

    <link rel="preload" href="/sash/assets/css/style.css" as="style">
    <link rel="preload" href="/sash/assets/js/jquery.min.js" as="script">

    <!-- FAVICON -->
    <link rel="icon" type="image/jpeg" href="{{ asset('logo.jpg') }}?v={{ $brandLogoVer }}" />
    <link rel="shortcut icon" type="image/jpeg" href="{{ asset('logo.jpg') }}?v={{ $brandLogoVer }}" />

    <!-- TITLE -->
    <title>Login - Optizee Hotel and Suites</title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="/sash/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

    <!-- STYLE CSS -->
    <link href="/sash/assets/css/style.css" rel="stylesheet" />
    <link href="/sash/assets/css/dark-style.css" rel="stylesheet" />
    <link href="/sash/assets/css/transparent-style.css" rel="stylesheet">
    <link href="/sash/assets/css/skin-modes.css" rel="stylesheet" />

    <!--- FONT-ICONS CSS -->
    <link href="/sash/assets/css/icons.css" rel="stylesheet" />

    <!-- COLOR SKIN CSS -->
    <link id="theme" rel="stylesheet" type="text/css" media="all" href="/sash/assets/colors/color1.css" />

</head>

<body class="app sidebar-mini ltr">

    <!-- BACKGROUND-IMAGE -->
    <div class="login-img">

        <!-- PAGE -->
        <div class="page">
            <div class="">

                <!-- CONTAINER OPEN -->
                <div class="col col-login mx-auto mt-7">
                    <div class="text-center">
                        <img src="{{ asset('logo.jpg') }}?v={{ $brandLogoVer }}" class="header-brand-img" alt="Optizee Hotel and Suites" fetchpriority="high" decoding="async" style="height: 160px; width: auto; object-fit: contain;">
                    </div>
                </div>

                <div class="container-login100">
                    <div class="wrap-login100 p-6">
                        <form class="login100-form validate-form" method="POST" action="{{ route('login') }}">
                            @csrf
                            <span class="login100-form-title pb-5">
                                Admin Login
                            </span>
                            
                            @if ($errors->any())
                                <div class="alert alert-danger mb-4">
                                    @foreach ($errors->all() as $error)
                                        <p class="mb-0">{{ $error }}</p>
                                    @endforeach
                                </div>
                            @endif

                            <div class="wrap-input100 validate-input input-group">
                                <a href="javascript:void(0)" class="input-group-text bg-white text-muted">
                                    <i class="zmdi zmdi-account text-muted" aria-hidden="true"></i>
                                </a>
                                <input class="input100 border-start-0 form-control ms-0" type="text" name="login" placeholder="Email, Username, or 4-Digit Code" value="{{ old('login') }}" required autofocus>
                            </div>
                            <small class="text-muted d-block mb-3">You can login with your email, username, or 4-digit code</small>
                            <div class="wrap-input100 validate-input input-group" id="Password-toggle">
                                <a href="javascript:void(0)" class="input-group-text bg-white text-muted">
                                    <i class="zmdi zmdi-eye text-muted" aria-hidden="true"></i>
                                </a>
                                <input class="input100 border-start-0 form-control ms-0" type="password" name="password" placeholder="Password" required>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-4">
                                <label class="custom-control custom-checkbox mb-0">
                                    <input type="checkbox" class="custom-control-input" name="remember" id="remember">
                                    <span class="custom-control-label">Remember me</span>
                                </label>
                            </div>
                            <div class="container-login100-form-btn">
                                <button type="submit" class="login100-form-btn btn-primary">
                                    Login
                                </button>
                            </div>
                            
                            <!-- Quick Login Options -->
                            <div class="text-center mt-4 pt-4 border-top">
                                <p class="text-muted mb-3">Quick Login Options</p>
                                
                                <!-- Code Only Login -->
                                <div class="mb-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm w-100" id="quick-login-btn" onclick="showQuickLogin()">
                                        <i class="zmdi zmdi-flash me-2"></i>Quick Login (Code Only)
                                    </button>
                                    <small class="text-muted d-block mt-1">For when you're in a hurry</small>
                                </div>
                                
                            </div>
                        </form>
                    </div>
                </div>
                <!-- CONTAINER CLOSED -->
                
                <!-- Quick Login Modal (Outside main form) -->
                <div class="modal fade" id="quickLoginModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="zmdi zmdi-flash me-2"></i>Quick Login</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('login.quick') }}" id="quick-login-form">
                                @csrf
                                <div class="modal-body">
                                    <p class="text-muted mb-3">Enter your 4-digit code to login quickly (no password required)</p>
                                    <div class="mb-3">
                                        <label class="form-label">4-Digit Code</label>
                                        <input type="text" class="form-control form-control-lg text-center" 
                                               name="login_code" id="quick-login-code" 
                                               maxlength="4" pattern="[0-9]{4}" 
                                               placeholder="0000" required autofocus
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)"
                                               onkeypress="if(event.key === 'Enter') { event.preventDefault(); document.getElementById('quick-login-form').submit(); }">
                                    </div>
                                    @if ($errors->has('login_code'))
                                        <div class="alert alert-danger">
                                            {{ $errors->first('login_code') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" id="quick-login-submit">
                                        <i class="zmdi zmdi-sign-in me-2"></i>Quick Login
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End PAGE -->

    </div>
    <!-- BACKGROUND-IMAGE CLOSED -->

    <!-- JQUERY JS -->
    <script src="/sash/assets/js/jquery.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <script src="/sash/assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="/sash/assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <!-- SHOW PASSWORD JS -->
    <script src="/sash/assets/js/show-password.min.js"></script>

    <!-- Perfect SCROLLBAR JS-->
    <script src="/sash/assets/plugins/p-scroll/perfect-scrollbar.js"></script>

    <!-- Color Theme js -->
    <script src="/sash/assets/js/themeColors.js"></script>

    <!-- CUSTOM JS -->
    <script src="/sash/assets/js/custom.js"></script>

    <script>
        // Quick Login Modal
        function showQuickLogin() {
            const modalElement = document.getElementById('quickLoginModal');
            if (!modalElement) {
                console.error('Quick login modal not found');
                return;
            }
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            
            // Focus on input when modal is shown
            modalElement.addEventListener('shown.bs.modal', function() {
                const codeInput = document.getElementById('quick-login-code');
                if (codeInput) {
                    codeInput.focus();
                    codeInput.select();
                }
            }, { once: true });
        }
        
        // Handle quick login form submission
        document.addEventListener('DOMContentLoaded', function() {
            const quickLoginForm = document.getElementById('quick-login-form');
            if (quickLoginForm) {
                quickLoginForm.addEventListener('submit', function(e) {
                    console.log('Quick login form submit event triggered');
                    const codeInput = document.getElementById('quick-login-code');
                    const submitBtn = document.getElementById('quick-login-submit');
                    
                    if (!codeInput || !codeInput.value) {
                        e.preventDefault();
                        alert('Please enter your 4-digit code');
                        if (codeInput) codeInput.focus();
                        return false;
                    }
                    
                    if (codeInput.value.length !== 4) {
                        e.preventDefault();
                        alert('Please enter a 4-digit code');
                        codeInput.focus();
                        return false;
                    }
                    
                    // Validate code is numeric
                    if (!/^\d{4}$/.test(codeInput.value)) {
                        e.preventDefault();
                        alert('Code must be exactly 4 digits (0-9)');
                        codeInput.focus();
                        return false;
                    }
                    
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="zmdi zmdi-refresh zmdi-hc-spin me-2"></i>Logging in...';
                    }
                    
                    // Don't prevent default - allow form to submit normally
                    console.log('Form submitting to:', quickLoginForm.action);
                    console.log('Code value:', codeInput.value);
                });
            } else {
                console.error('Quick login form not found');
            }
        });


        // Auto-focus on quick login code input when modal opens
        document.addEventListener('DOMContentLoaded', function() {
            const quickLoginModal = document.getElementById('quickLoginModal');
            if (quickLoginModal) {
                quickLoginModal.addEventListener('shown.bs.modal', function() {
                    const codeInput = document.getElementById('quick-login-code');
                    if (codeInput) {
                        codeInput.focus();
                        codeInput.select();
                    }
                });
            }
            
        });
    </script>

</body>

</html>
