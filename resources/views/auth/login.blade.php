@extends('layouts.credentials-app')

@section('content')
	<div class="text-center">
		<h4 class="mt-3 mb-1 fw-semibold text-muted fs-18">Sign in to continue to {{ config('app.name') }}.</h4>
	</div>
	<form class="my-2" method="POST" action="{{ route('login') }}" id="login-form">
		@csrf
		<div class="form-group mb-2">
			<label class="form-label" for="email">{{ __('Email Address') }}</label>
			<input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
				placeholder="Enter Email" value="{{ old('email') }}">
			@error('email')
				<span class="invalid-feedback" role="alert">
					<strong>{{ $message }}</strong>
				</span>
			@enderror
		</div>

		<div class="form-group">
			<label class="form-label" for="password">{{ __('Password') }}</label>
			<div class="input-group">
				<input type="password" class="form-control password-field @error('password') is-invalid @enderror" 
					name="password" placeholder="Enter Password">
				<button class="btn btn-outline-primary toggle-password" type="button">
					<i class="fas fa-eye"></i>
				</button>
			</div>
			@error('password')
				<span class="invalid-feedback" role="alert">
					<strong>{{ $message }}</strong>
				</span>
			@enderror
		</div>

		<div class="form-group row mt-3">
			<div class="col-sm-6">
				<div class="form-check form-switch form-switch-primary">
					<input class="form-check-input" name="remember" type="checkbox" id="remember"
						{{ old('remember') ? 'checked' : '' }}>
					<label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
				</div>
			</div>
			@if (Route::has('password.request'))
				<div class="col-sm-6 text-end">
					<a href="{{ route('password.request') }}" class="text-muted font-13"><i class="dripicons-lock"></i>
						{{ __('Forgot Your Password?') }}</a>
				</div>
			@endif

		</div>

		<div class="form-group mb-0 row">
			<div class="col-12">
				<div class="d-grid mt-3">
					<button class="btn btn-primary" type="submit">{{ __('Login') }} <i
							class="fas fa-sign-in-alt ms-1"></i></button>
				</div>
			</div>
		</div>
	</form>
@endsection

@section('pageScripts')
	<script src="{{ asset('vendor-assets/js/pages/login/login.js') }}"></script>
@endsection
