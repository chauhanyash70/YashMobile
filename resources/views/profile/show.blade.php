@extends('layouts.app')

@section('content')
	<div class="container-xxl">
		<div class="row justify-content-center">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<div class="d-flex align-items-center flex-row flex-wrap">
							<div class="position-relative me-3">
								<img src="{{ Auth::user()->profile_url }}" alt="" height="120"
									class="rounded-circle">
							</div>
							<div>
								<h5 class="fw-semibold fs-22 mb-1">{{ Auth::user()->name }}</h5>
								<p class="mb-0 text-muted fw-medium">{{ Auth::user()->email }}</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row justify-content-center">
			<div class="col-12">
				<ul class="nav nav-tabs mb-3">
					<li class="nav-item">
						<a class="nav-link active" data-bs-toggle="tab" href="#edit_profile">Edit Profile</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" data-bs-toggle="tab" href="#change_password">Change Password</a>
					</li>
				</ul>

				<div class="tab-content">
					<!-- Edit Profile Tab -->
					<div class="tab-pane active" id="edit_profile">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">Personal Information</h4>
							</div>
							<div class="card-body pt-0">
								<form action="{{ route('profile.update') }}" method="POST"
									enctype="multipart/form-data" id="profile-form">
									@csrf
									<div class="d-flex align-items-center flex-row flex-wrap justify-content-center mb-3">
										<div class="position-relative me-3">
											<!-- Profile Image Preview -->
											<img id="profilePreview" src="{{ Auth::user()->profile_url }}"
												alt="" height="120" class="rounded-circle"
												style="cursor: pointer;">

											<!-- Hidden File Input -->
											<input type="file" id="profileImageInput" name="profile" accept="image/*"
												class="d-none">

											<!-- Camera Icon (Clickable) -->
											<span id="uploadTrigger"
												class="thumb-md justify-content-center d-flex align-items-center bg-primary text-white rounded-circle position-absolute end-0 bottom-0 border border-3 border-card-bg"
												style="cursor: pointer;">
												<i class="fas fa-camera"></i>
											</span>
										</div>
										@error('profile')
											<span class="text-danger">{{ $message }}</span>
										@enderror
									</div>
									<div class="form-group mb-3">
										<label>Name</label>
										<input class="form-control @error('name') is-invalid @enderror" type="text"
											name="name" value="{{ old('name', Auth::user()->name) }}">
										@error('name')
											<span class="text-danger">{{ $message }}</span>
										@enderror
									</div>

									<div class="form-group mb-3">
										<label>Contact Mobile</label>
										<input type="text" name="mobile"
											class="form-control @error('mobile') is-invalid @enderror"
											value="{{ old('mobile', Auth::user()->mobile) }}">
										@error('mobile')
											<span class="text-danger">{{ $message }}</span>
										@enderror
									</div>

									<div class="form-group mb-3">
										<label>Email Address</label>
										<input type="email" name="email"
											class="form-control @error('email') is-invalid @enderror"
											value="{{ old('email', Auth::user()->email) }}">
										@error('email')
											<span class="text-danger">{{ $message }}</span>
										@enderror
									</div>

									<div class="form-group">
										<button type="submit" class="btn btn-primary">Submit</button>
										<button type="reset" class="btn btn-danger">Cancel</button>
									</div>
								</form>
							</div>
						</div>
					</div>

					<!-- Change Password Tab -->
					<div class="tab-pane" id="change_password">
						<div class="card">
							<div class="card-header">
								<h4 class="card-title">Change Password</h4>
							</div>
							<div class="card-body pt-0">
								<form action="{{ route('profile.change-password') }}" method="POST"
									id="change-password-form">
									@csrf
									<div class="form-group mb-3">
										<label>Current Password</label>
										<div class="input-group">
											<input type="password" name="current_password" id="current_password"
												class="form-control @error('current_password') is-invalid @enderror">
											<button class="btn btn-outline-primary toggle-password" type="button">
												<i class="fas fa-eye"></i>
											</button>
										</div>
										@error('current_password')
											<span class="text-danger">{{ $message }}</span>
										@enderror
									</div>

									<div class="form-group mb-3">
										<label>New Password</label>
										<div class="input-group">
											<input type="password" name="new_password" id="new_password"
												class="form-control @error('new_password') is-invalid @enderror">
											<button class="btn btn-outline-primary toggle-password" type="button">
												<i class="fas fa-eye"></i>
											</button>
										</div>
										@error('new_password')
											<span class="text-danger">{{ $message }}</span>
										@enderror
									</div>

									<div class="form-group mb-3">
										<label>Confirm Password</label>
										<input type="password" name="new_password_confirmation" class="form-control">
									</div>

									<div class="form-group">
										<button type="submit" class="btn btn-primary">Change Password</button>
										<button type="reset" class="btn btn-danger">Cancel</button>
									</div>
								</form>
							</div>
						</div>
					</div> <!-- End Change Password Tab -->
				</div>
			</div>
		</div>
	</div>
@endsection

@section('pageScripts')
	<script>
		var currentPasswordCheckUrl = "{{ route('profile.check-password') }}";
	</script>
	<script src="{{ asset('vendor-assets/js/pages/profile/profile.js') }}"></script>
@endsection
