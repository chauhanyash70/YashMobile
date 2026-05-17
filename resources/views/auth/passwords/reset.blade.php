@extends('layouts.credentials-app')
@section('title', 'Reset Password')

@section('content')
	<div class="text-center">
		<h4 class="mt-3 mb-1 fw-semibold text-muted fs-18">{{ __('Reset Password') }}</h4>
	</div>
	<form class="my-2" method="POST" action="{{ route('password.reset') }}" id="reset-password-form">
		@csrf
        <input type="hidden" name="token" value="{{ $token }}">
		<div class="form-group mb-2">
			<label class="form-label" for="email">{{ __('Email Address') }}</label>
			<input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
				placeholder="Enter Email" value="{{ old('email', $email) }}" readonly>
			@error('email')
				<span class="invalid-feedback" role="alert">
					<strong>{{ $message }}</strong>
				</span>
			@enderror
		</div>

		<div class="form-group mb-2">
			<label class="form-label" for="password">{{ __('Password') }}</label>
			<input type="password" class="form-control password-field @error('password') is-invalid @enderror" 
					name="password" placeholder="Enter Password">
			@error('password')
				<span class="invalid-feedback" role="alert">
					<strong>{{ $message }}</strong>
				</span>
			@enderror
		</div>

        <div class="form-group">
			<label class="form-label" for="password-confirm">{{ __('Confirm Password') }}</label>
			<div class="input-group">
				<input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation"
				id="password-confirm" placeholder="Enter Password">
				<button class="btn btn-outline-primary toggle-password" type="button">
					<i class="fas fa-eye"></i>
				</button>
			</div>
			@error('password_confirmation')
				<span class="invalid-feedback" role="alert">
					<strong>{{ $message }}</strong>
				</span>
			@enderror
		</div>

		<div class="form-group row mt-3">
			@if (Route::has('login'))
				<div class="col-sm-12 text-end">
					<a href="{{ route('login') }}" class="text-muted font-13">
						{{ __('Back to Login') }}</a>
				</div>
			@endif
		</div>

		<div class="form-group mb-0 row">
			<div class="col-12">
				<div class="d-grid mt-3">
					<button class="btn btn-primary" type="submit">{{ __('Reset Password') }}</button>
				</div>
			</div>
		</div>
	</form>
@endsection

@section('pageScripts')
	<script src="{{ asset('vendor-assets/js/pages/forgotPassword/reset.js') }}"></script>
@endsection
