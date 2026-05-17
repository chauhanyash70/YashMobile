<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="light" data-bs-theme="light">

<head>
    <script>
        /**
         * Theme Manager - Immediate Execution to prevent flash
         */
        (function() {
            function getCookie(name) {
                let value = "; " + document.cookie;
                let parts = value.split("; " + name + "=");
                if (parts.length === 2) return parts.pop().split(";").shift();
            }

            let theme = getCookie("theme") || "auto";

            if (theme === "auto") {
                const hour = new Date().getHours();
                theme = (hour >= 18 || hour < 6) ? "dark" : "light";
            }

            document.documentElement.setAttribute("data-bs-theme", theme);
        })();
    </script>

    <meta charset="utf-8" />
    <title>@yield('title', $title ?? 'Login') | {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/logo/yash-mobile-favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('vendor-assets/libs/jsvectormap/css/jsvectormap.min.css') }}">
    <!-- App css -->
    <link href="{{ asset('vendor-assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor-assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/js/toastr/toastr.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor-assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .nav-icon {
            color: #94a3b8;
            font-size: 22px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            background: rgba(148, 163, 184, 0.1);
        }

        .nav-icon:hover {
            background: rgba(148, 163, 184, 0.2);
            color: var(--bs-primary);
        }

        [data-bs-theme="dark"] .light-mode {
            display: block;
        }

        [data-bs-theme="dark"] .dark-mode {
            display: none;
        }

        [data-bs-theme="light"] .light-mode {
            display: none;
        }

        [data-bs-theme="light"] .dark-mode {
            display: block;
        }

        .theme-item.active {
            background-color: var(--bs-tertiary-bg);
            color: var(--bs-primary);
        }
    </style>
    @yield('pageCss')
</head>

<body>

    <div class="container-xxl">
        <div class="row vh-100 d-flex justify-content-center">
            <div class="col-12 align-self-center">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 mx-auto">
                            <div class="card position-relative">
                                <div class="position-absolute top-0 end-0 p-2" style="z-index: 10;">
                                    <div class="dropdown">
                                        <a class="nav-link dropdown-toggle arrow-none nav-icon"
                                            data-bs-toggle="dropdown" href="#" role="button"
                                            aria-haspopup="false" aria-expanded="false" id="light-dark-mode"
                                            style="width: 32px; height: 32px; font-size: 18px;">
                                            <i class="icofont-sun dark-mode"></i>
                                            <i class="icofont-moon light-mode"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="light-dark-mode">
                                            <a class="dropdown-item theme-item" href="javascript:void(0);"
                                                data-theme="light">
                                                <i class="icofont-sun me-2"></i>Light
                                            </a>
                                            <a class="dropdown-item theme-item" href="javascript:void(0);"
                                                data-theme="dark">
                                                <i class="icofont-moon me-2"></i>Dark
                                            </a>
                                            <a class="dropdown-item theme-item" href="javascript:void(0);"
                                                data-theme="auto">
                                                <i class="iconoir-system-restart me-2"></i>Auto
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-0 auth-header-box rounded-top">
                                    <div class="text-center p-1">
                                        <a href="{{ route('login') }}" class="logo">
                                            <span class="logo-lg">
                                                <img src="{{ asset('assets/logo/yash-mobile-logo-white.png') }}"
                                                    alt="logo-large" class="logo-light" width="200">
                                                <img src="{{ asset('assets/logo/yash-mobile-logo.png') }}"
                                                    alt="logo-large" class="logo-dark" width="200">
                                            </span>
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    @yield('content')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Javascript  -->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-validate/additional-methods.min.js') }}"></script>
    <script src="{{ asset('assets/js/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        var csrfToken = "{{ csrf_token() }}";
        toastr.options = {
            "closeButton": true,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-bottom-center",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "2000",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
        @foreach (['error', 'warning', 'success', 'info'] as $msg)
            @if (Session::has($msg))
                toastr.{{ $msg }}("{{ Session::get($msg) }}");
            @endif
        @endforeach
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".toggle-password").forEach(button => {
                button.addEventListener("click", function() {
                    const passwordField = this.previousElementSibling;
                    if (passwordField.type === "password") {
                        passwordField.type = "text";
                        this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                    } else {
                        passwordField.type = "password";
                        this.innerHTML = '<i class="fas fa-eye"></i>';
                    }
                });
            });

            // Theme Logic
            function setCookie(e, t, o) {
                let n = "";
                if (o) {
                    let e = new Date;
                    e.setTime(e.getTime() + 24 * o * 60 * 60 * 1e3), n = "; expires=" + e.toUTCString()
                }
                document.cookie = e + "=" + t + n + "; path=/"
            }

            function getCookie(e) {
                let t = e + "=",
                    o = document.cookie.split(";");
                for (let e = 0; e < o.length; e++) {
                    let n = o[e].trim();
                    if (0 == n.indexOf(t)) return n.substring(t.length, n.length)
                }
                return null
            }

            function applyTheme() {
                let e = getCookie("theme") || "auto";
                if ("auto" === e) {
                    const t = (new Date).getHours();
                    e = t >= 18 || t < 6 ? "dark" : "light"
                }
                document.documentElement.setAttribute("data-bs-theme", e);

                document.querySelectorAll(".theme-item").forEach((function(t) {
                    t.classList.remove("active"), t.getAttribute("data-theme") === (getCookie(
                            "theme") ||
                        "auto") && t.classList.add("active")
                }))
            }

            document.querySelectorAll(".theme-item").forEach((function(e) {
                e.addEventListener("click", (function() {
                    let e = this.getAttribute("data-theme");
                    setCookie("theme", e, 30), applyTheme()
                }))
            }));

            applyTheme();
            setInterval((function() {
                "auto" === (getCookie("theme") || "auto") && applyTheme()
            }), 6e4);
        });
    </script>
    @yield('pageScripts')
</body>
<!--end body-->

</html>
