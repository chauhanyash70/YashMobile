@extends('layouts.credentials-app')
@section('title', 'Login')

@section('content')
	<div class="text-center">
		<h4 class="mt-3 mb-1 fw-semibold text-muted fs-18">Sign in to continue to {{ config('app.name') }}.</h4>
	</div>

	<form class="my-2" method="POST" action="{{ route('login') }}" id="login-form">
		@csrf
		<!-- Shared Email Field -->
		<div class="form-group mb-2">
			<label class="form-label" for="email">{{ __('Email Address') }}</label>
			<div class="input-group">
				<span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
				<input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
					placeholder="Enter Email" value="{{ old('email') }}">
			</div>
			@error('email')
				<span class="invalid-feedback d-block" role="alert">
					<strong>{{ $message }}</strong>
				</span>
			@enderror
		</div>

		<!-- Credentials Section (Password + Remember switch + Login Button) -->
		<div class="credentials-section">
			<div class="form-group">
				<label class="form-label" for="password">{{ __('Password') }}</label>
				<div class="input-group">
					<span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
					<input type="password" class="form-control password-field @error('password') is-invalid @enderror" 
						id="password" name="password" placeholder="Enter Password">
					<button class="btn btn-outline-primary toggle-password" type="button">
						<i class="fas fa-eye"></i>
					</button>
				</div>
				@error('password')
					<span class="invalid-feedback d-block" role="alert">
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
						<button class="btn btn-primary py-2" id="btn-login" type="submit">
							<span class="spinner-border spinner-border-sm me-2" id="login-spinner" style="display: none;" role="status" aria-hidden="true"></span>
							<span id="login-btn-text">{{ __('Login') }} <i class="fas fa-sign-in-alt ms-1"></i></span>
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- OTP Verification Section (Initially Hidden) -->
		<div class="otp-section" style="display: none;">
			<div class="form-group mb-2 text-center">
				<label class="form-label d-block mb-3" for="otp_1"><i class="fas fa-shield-alt me-2 text-primary"></i>{{ __('Enter 4-Digit Verification Code') }}</label>
				
				<div class="d-flex justify-content-center gap-3 my-3">
					<input type="text" class="form-control text-center fw-bold fs-14 otp-digit" id="otp_1" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="off" style="width: 40px; height: 44px; border-radius: 8px; border: 1.5px solid var(--bs-border-color);">
					<input type="text" class="form-control text-center fw-bold fs-14 otp-digit" id="otp_2" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="off" style="width: 40px; height: 44px; border-radius: 8px; border: 1.5px solid var(--bs-border-color);">
					<input type="text" class="form-control text-center fw-bold fs-14 otp-digit" id="otp_3" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="off" style="width: 40px; height: 44px; border-radius: 8px; border: 1.5px solid var(--bs-border-color);">
					<input type="text" class="form-control text-center fw-bold fs-14 otp-digit" id="otp_4" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="off" style="width: 40px; height: 44px; border-radius: 8px; border: 1.5px solid var(--bs-border-color);">
				</div>
				<input type="hidden" id="otp" name="otp">
				
				<div class="d-flex justify-content-between align-items-center mt-2 px-1">
					<small class="text-muted timer-container">
						Didn't receive the OTP? <span id="cooldown-timer" class="fw-semibold text-primary hover:text-primary-hover"></span>
					</small>
					<button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold" id="btn-resend-otp" style="display: none;">
						<span class="spinner-border spinner-border-sm me-1" id="resend-spinner" style="display: none;" role="status" aria-hidden="true"></span>
						<i class="fas fa-redo me-1" id="resend-icon"></i>{{ __('Resend OTP') }}
					</button>
				</div>
			</div>

			<div class="form-group mb-0 row">
				<div class="col-12">
					<div class="d-grid mt-3">
						<button class="btn btn-primary py-2" id="btn-verify-otp" type="button">
							<span class="spinner-border spinner-border-sm me-2" id="verify-spinner" style="display: none;" role="status" aria-hidden="true"></span>
							<span id="verify-otp-text">{{ __('Verify & Login') }} <i class="fas fa-check-circle ms-1"></i></span>
						</button>
					</div>
				</div>
			</div>
		</div>
	</form>
@endsection

@section('pageScripts')
	<script src="{{ asset('vendor-assets/js/pages/login/login.js') }}"></script>
@endsection
