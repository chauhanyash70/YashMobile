@extends('layouts.app')
@section('title', 'My Profile')
@section('header_title', $header_title ?? 'My Profile')
@section('tagline', $tagline ?? 'Manage your account settings, security, and personal information.')

@section('pageCss')
    <link href="{{ asset('vendor-assets/libs/data-tables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .dynamic-mesh-gradient {
            background: radial-gradient(circle at 0% 0%, #4e73df 0%, rgba(78, 115, 223, 0) 50%),
                radial-gradient(circle at 100% 0%, #6f42c1 0%, rgba(111, 66, 193, 0) 50%),
                radial-gradient(circle at 100% 100%, #1cc88a 0%, rgba(28, 200, 138, 0) 50%),
                radial-gradient(circle at 0% 100%, #f6c23e 0%, rgba(246, 194, 62, 0) 50%),
                #4e73df;
            background-size: 200% 200%;
            animation: gradientMove 15s ease infinite;
            height: 180px;
            position: relative;
            overflow: hidden;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card overflow-hidden">
                    <div class="card-body p-0">
                        <div class="profile-cover-bg p-5 d-flex align-items-end dynamic-mesh-gradient">
                            <div class="position-absolute top-0 start-0 w-100 h-100 opacity-25"
                                style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');">
                            </div>
                        </div>
                        <div class="p-4 pt-0">
                            <div class="row align-items-end">
                                <div class="col-auto">
                                    <div class="mt-n5 position-relative">
                                        <img src="{{ Auth::user()->profile_url }}" alt="" height="120"
                                            width="120"
                                            class="rounded-circle border border-4 border-card-bg shadow-sm object-fit-cover">
                                        <span
                                            class="position-absolute bottom-0 end-0 bg-success rounded-circle p-1 border border-2 border-card-bg"
                                            title="Online"></span>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="p-2">
                                        <h3 class="fw-bold mb-1">{{ Auth::user()->name }}</h3>
                                        <p class="text-muted mb-0"><i
                                                class="iconoir-mail me-1"></i>{{ Auth::user()->email }}</p>
                                    </div>
                                </div>
                                <div class="col-md-auto text-end">
                                    <div class="d-flex gap-2 mb-2">
                                        {{-- <div class="text-center bg-light rounded-3 p-2 px-3 border">
                                            <h5 class="mb-0 fw-bold">{{ Auth::user()->customers()->count() }}</h5>
                                            <p class="text-muted mb-0 small">Customers</p>
                                        </div> --}}
                                        <div class="text-center bg-light rounded-3 p-2 px-3 border">
                                            <h5 class="mb-0 fw-bold">{{ Auth::user()->created_at->format('M Y') }}</h5>
                                            <p class="text-muted mb-0 small">Member Since</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <a class="nav-link active p-3 d-flex align-items-center border-bottom rounded-0"
                                id="v-pills-profile-tab" data-bs-toggle="pill" href="#edit_profile" role="tab">
                                <i class="iconoir-user-circle me-2 fs-18"></i>
                                <span>Edit Profile</span>
                                <i class="iconoir-nav-arrow-right ms-auto"></i>
                            </a>
                            <a class="nav-link p-3 d-flex align-items-center border-bottom rounded-0"
                                id="v-pills-password-tab" data-bs-toggle="pill" href="#change_password" role="tab">
                                <i class="iconoir-lock me-2 fs-18"></i>
                                <span>Change Password</span>
                                <i class="iconoir-nav-arrow-right ms-auto"></i>
                            </a>
                            {{-- <a class="nav-link p-3 d-flex align-items-center rounded-0" id="v-pills-customers-tab"
                                data-bs-toggle="pill" href="#manage_customers" role="tab">
                                <i class="iconoir-group me-2 fs-18"></i>
                                <span>My Customers</span>
                                <i class="iconoir-nav-arrow-right ms-auto"></i>
                            </a> --}}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="tab-content" id="v-pills-tabContent">
                    <!-- Edit Profile Tab -->
                    <div class="tab-pane fade show active" id="edit_profile" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-transparent border-bottom">
                                <h4 class="card-title mb-0">Personal Information</h4>
                                <p class="text-muted mb-0 small">Update your profile details and avatar.</p>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
                                    id="profile-form">
                                    @csrf
                                    <div class="text-center mb-4">
                                        <div class="position-relative d-inline-block">
                                            <img id="profilePreview" src="{{ Auth::user()->profile_url }}" alt=""
                                                height="120" width="120"
                                                class="rounded-circle border border-4 border-light shadow-sm object-fit-cover"
                                                style="cursor: pointer;">
                                            <input type="file" id="profileImageInput" name="profile_image"
                                                accept="image/*" class="d-none">
                                            <button type="button" id="uploadTrigger"
                                                class="btn btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 p-1 px-2 border border-2 border-white">
                                                <i class="fas fa-camera"></i>
                                            </button>
                                        </div>
                                        @error('profile_image')
                                            <div class="text-danger mt-2 small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Full Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="iconoir-user"></i></span>
                                                <input
                                                    class="form-control border-start-0 @error('name') is-invalid @enderror"
                                                    type="text" name="name"
                                                    value="{{ old('name', Auth::user()->name) }}"
                                                    placeholder="Enter your name">
                                            </div>
                                            @error('name')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Contact Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="iconoir-phone"></i></span>
                                                <input type="text" name="mobile"
                                                    class="form-control border-start-0 @error('mobile') is-invalid @enderror"
                                                    value="{{ old('mobile', Auth::user()->mobile) }}"
                                                    placeholder="Enter mobile number">
                                            </div>
                                            @error('mobile')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="iconoir-mail"></i></span>
                                                <input type="email" name="email"
                                                    class="form-control border-start-0 @error('email') is-invalid @enderror"
                                                    value="{{ old('email', Auth::user()->email) }}"
                                                    placeholder="Enter email address">
                                            </div>
                                            @error('email')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-2 border-top d-flex gap-2">
                                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                                        <button type="reset" class="btn btn-light px-4">Reset</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Change Password Tab -->
                    <div class="tab-pane fade" id="change_password" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-transparent border-bottom">
                                <h4 class="card-title mb-0">Security Settings</h4>
                                <p class="text-muted mb-0 small">Change your account password to keep it secure.</p>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('profile.change-password') }}" method="POST"
                                    id="change-password-form">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label fw-medium">Current Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i
                                                    class="iconoir-lock"></i></span>
                                            <input type="password" name="current_password" id="current_password"
                                                class="form-control border-start-0 border-end-0 @error('current_password') is-invalid @enderror"
                                                placeholder="Enter current password">
                                            <button class="btn btn-outline-light border-start-0 toggle-password"
                                                type="button" style="border: 1px solid #dee2e6;">
                                                <i class="fas fa-eye text-muted"></i>
                                            </button>
                                        </div>
                                        @error('current_password')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-medium">New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i
                                                    class="iconoir-key"></i></span>
                                            <input type="password" name="new_password" id="new_password"
                                                class="form-control border-start-0 border-end-0 @error('new_password') is-invalid @enderror"
                                                placeholder="Minimum 8 characters">
                                            <button class="btn btn-outline-light border-start-0 toggle-password"
                                                type="button" style="border: 1px solid #dee2e6;">
                                                <i class="fas fa-eye text-muted"></i>
                                            </button>
                                        </div>
                                        @error('new_password')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-medium">Confirm New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i
                                                    class="iconoir-check"></i></span>
                                            <input type="password" name="new_password_confirmation"
                                                class="form-control border-start-0" placeholder="Re-type new password">
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-2 border-top d-flex gap-2">
                                        <button type="submit" class="btn btn-primary px-4">Update Password</button>
                                        <button type="reset" class="btn btn-light px-4">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Manage Customers Tab -->
                    <div class="tab-pane fade" id="manage_customers" role="tabpanel">
                        <div class="card">
                            <div
                                class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0">My Customers</h4>
                                    <p class="text-muted mb-0 small">Manage your customer portfolio.</p>
                                </div>
                                <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm px-3">
                                    <i class="iconoir-plus me-1"></i>Add New
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" id="customer-table"
                                        style="width: 100%;">
                                        <thead class="bg-light-subtle">
                                            <tr>
                                                <th class="border-top-0">Customer</th>
                                                <th class="border-top-0">Phone</th>
                                                <th class="border-top-0">Address</th>
                                                <th class="border-top-0 text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageScripts')
    <script>
        var currentPasswordCheckUrl = "{{ route('profile.check-password') }}";
        var customerDataUrl = "{{ route('getCustomerData') }}";

        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.fw-bold.mb-0');
            counters.forEach(counter => {
                const target = parseInt(counter.innerText);
                if (!isNaN(target) && target > 0) {
                    let count = 0;
                    const duration = 1000; // 1 second
                    const startTime = performance.now();

                    const animate = (currentTime) => {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        counter.innerText = Math.floor(progress * target);

                        if (progress < 1) {
                            requestAnimationFrame(animate);
                        } else {
                            counter.innerText = target;
                        }
                    };
                    requestAnimationFrame(animate);
                }
            });
        });
    </script>
    <script src="{{ asset('vendor-assets/libs/data-tables/datatables.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/js/pages/profile/profile.js') }}"></script>
    <script src="{{ asset('vendor-assets/js/pages/profile/profile_entities.js') }}"></script>
@endsection
