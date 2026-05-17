<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="light" data-bs-theme="light">
<head>
	<meta charset="utf-8" />
	<title>{{config('app.name')}} - {{$title??''}}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
	<meta content="" name="author" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<!-- App favicon -->
	<link rel="shortcut icon" href="{{asset('assets/logo/yash-mobile-favicon.svg')}}">
	<link rel="stylesheet" href="{{asset('vendor-assets/libs/jsvectormap/css/jsvectormap.min.css')}}">
	<!-- App css -->
	<link href="{{asset('vendor-assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
	<link href="{{asset('vendor-assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
	<link href="{{ asset('assets/js/toastr/toastr.min.css') }}" rel="stylesheet" type="text/css" />
	<link href="{{asset('vendor-assets/css/app.min.css')}}" rel="stylesheet" type="text/css" />
	@yield('pageCss')
</head>
<body>
	<div class="container-xxl">
        <div class="row vh-100 d-flex justify-content-center">
            <div class="col-12 align-self-center">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 mx-auto">
                            <div class="card">
                                <div class="card-body p-0 bg-white auth-header-box rounded-top">
                                    <div class="text-center p-1">
                                        <a href="{{route('login')}}" class="logo logo-admin">
                                            <img src="{{asset('assets/logo/yash-mobile-logo.png')}}" height="80" alt="logo" class="auth-logo">
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
        document.addEventListener("DOMContentLoaded", function () {
			document.querySelectorAll(".toggle-password").forEach(button => {
				button.addEventListener("click", function () {
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
		});
    </script>
	@yield('pageScripts')
</body>
<!--end body-->
</html>
