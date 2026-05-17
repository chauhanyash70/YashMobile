@extends('layouts.credentials-app')
@section('title', 'Forgot Password')

@section('content')
	<div class="text-center">
		<h4 class="mt-3 mb-1 fw-semibold text-muted fs-18">{{ __('Reset Password') }}</h4>
	</div>
	@if (session('status'))
		<div class="alert alert-success p-2" role="alert">
			{{ session('status') }}
		</div>
	@endif
	<form class="my-2" method="POST" action="{{ route('password.email') }}" id="send-email-form">
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

		<div class="form-group row mt-3">
			@if (Route::has('login')) 
				<div class="col-12 text-end">
					<a href="{{ route('login') }}" class="text-muted font-13"><i class="dripicons-lock"></i>
						{{ __('Back To Login') }}</a>
				</div>
			@endif
		</div>

		<div class="form-group mb-0 row">
			<div class="col-12">
				<div class="d-grid mt-3">
					<button class="btn btn-primary" type="submit">{{ __('Send Password Reset Link') }} <i
							class="far fa-envelope me-1"></i></button>
				</div>
			</div>
		</div>
	</form>
@endsection

@section('pageScripts')
	<script src="{{ asset('vendor-assets/js/pages/forgotPassword/email.js') }}"></script>
@endsection
