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

    <style>
        body.app,
        .login-img {
            min-height: 100vh;
            background: radial-gradient(1200px 500px at 50% -10%, rgba(226, 160, 74, 0.28), transparent 55%),
                        linear-gradient(165deg, #1a1208 0%, #2a2118 48%, #3d2a14 100%) !important;
        }
        .login-img .page { background: transparent !important; }
        .col-login { max-width: 460px; }
        .optizee-logo-wrap {
            display: inline-flex;
            background: #fff;
            border-radius: 22px;
            padding: 10px 14px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.28);
            margin-bottom: 8px;
        }
        .optizee-logo-wrap img { height: 108px !important; }
        .container-login100 { align-items: flex-start !important; padding-top: 0 !important; }
        .wrap-login100 {
            width: 100%;
            max-width: 440px;
            margin: 0 auto 40px;
            padding: 28px 26px 22px !important;
            border-radius: 24px !important;
            border: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.28) !important;
        }
        .login100-form-title {
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #1a1208 !important;
            padding-bottom: 6px !important;
        }
        .login-subtitle {
            display: block;
            text-align: center;
            color: #8a7b6a;
            margin-bottom: 22px;
            font-size: 14px;
        }
        .wrap-input100 { margin-bottom: 12px; }
        .wrap-input100 .input-group-text {
            border-radius: 14px 0 0 14px !important;
            background: #f7f1e8 !important;
            border-color: #eadfce !important;
        }
        .wrap-input100 .input100 {
            height: 50px;
            border-radius: 0 14px 14px 0 !important;
            background: #fffdf9;
            border-color: #eadfce !important;
        }
        .wrap-input100 .input100:focus {
            box-shadow: 0 0 0 3px rgba(226, 160, 74, 0.25);
            border-color: #e2a04a !important;
        }
        .login100-form-btn {
            height: 50px !important;
            border: 0;
            border-radius: 14px !important;
            background: linear-gradient(180deg, #e2a04a, #c9842e) !important;
            color: #1a1208 !important;
            font-weight: 800;
            letter-spacing: 0.01em;
            transition: transform 0.12s ease, box-shadow 0.12s ease;
            box-shadow: 0 8px 18px rgba(201, 132, 46, 0.35);
        }
        .login100-form-btn:hover { transform: translateY(-1px); }
        .login100-form-btn:active { transform: scale(0.98); }
        #quick-login-btn {
            height: 46px;
            border-radius: 14px;
            border-color: #e2a04a;
            color: #8a5a12;
            font-weight: 700;
            background: #fff8ee;
        }
        #quick-login-btn:hover { background: #f6e4c4; color: #1a1208; }
        .quick-login-box { border-top: 1px solid #f0e6d8 !important; }
        #quickLoginModal .modal-content { border: 0; border-radius: 20px; }
        #quickLoginModal .modal-header { border-bottom-color: #f0e6d8; }
        #quick-login-code {
            letter-spacing: 0.4em;
            font-weight: 800;
            border-radius: 14px;
        }
        #quick-login-submit {
            background: #c9842e;
            border-color: #c9842e;
            color: #1a1208;
            font-weight: 800;
            border-radius: 12px;
        }
    </style>

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
                        <span class="optizee-logo-wrap">
                            <img src="{{ asset('logo.jpg') }}?v={{ $brandLogoVer }}" class="header-brand-img" alt="Optizee Hotel and Suites" fetchpriority="high" decoding="async" style="height: 108px; width: auto; object-fit: contain;">
                        </span>
                    </div>
                </div>

                <div class="container-login100">
                    <div class="wrap-login100 p-6">
                        <form class="login100-form validate-form" method="POST" action="{{ route('login') }}">
                            @csrf
                            <span class="login100-form-title">
                                Admin Login
                            </span>
                            <span class="login-subtitle">Sign in to Optizee Hotel and Suites</span>
                            
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
                            <div class="text-center mt-4 pt-4 border-top quick-login-box">
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
