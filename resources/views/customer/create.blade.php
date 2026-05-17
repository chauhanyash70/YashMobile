@extends('layouts.app')
@section('title', 'Add Customer')
@section('header_title', $header_title ?? (isset($customer) ? 'Edit Customer' : 'Add Customer'))
@section('tagline', $tagline ?? (isset($customer) ? 'Update customer contact information and documents.' : 'Register a new customer in the system.'))


@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">{{ isset($customer) ? 'Edit Customer' : 'Add Customer' }}</div>
                    <div class="card-body">
                        <form
                            action="{{ isset($customer) ? route('customers.update', $customer->id) : route('customers.store') }}"
                            method="POST" enctype="multipart/form-data" id="customer-form">
                            @csrf
                            @if (isset($customer))
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <div class="d-flex align-items-center flex-column justify-content-center mb-3">
                                        <div class="position-relative">
                                            <!-- Profile Image Preview -->
                                            <img id="profilePreview"
                                                src="{{ isset($customer) ? $customer->profile_url : asset('assets/images/user-blank.jpg') }}"
                                                alt="" height="150" width="150"
                                                class="rounded-circle border border-3 border-light shadow-sm"
                                                style="cursor: pointer; object-fit: cover;">

                                            <!-- Hidden File Input -->
                                            <input type="file" id="profileImageInput" name="profile_image"
                                                accept="image/*" class="d-none">

                                            <!-- Camera Icon (Clickable) -->
                                            <span id="uploadTrigger"
                                                class="thumb-md justify-content-center d-flex align-items-center bg-primary text-white rounded-circle position-absolute end-0 bottom-0 border border-3 border-card-bg"
                                                style="cursor: pointer; width: 40px; height: 40px;">
                                                <i class="fas fa-camera"></i>
                                            </span>
                                        </div>
                                        <p class="mt-2 text-muted small">Click to upload profile photo</p>
                                        @error('profile_image')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                id="name" name="name"
                                                value="{{ old('name', $customer->name ?? '') }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                                id="phone" name="phone"
                                                value="{{ old('phone', $customer->phone ?? '') }}">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                id="email" name="email"
                                                value="{{ old('email', $customer->email ?? '') }}">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>



                                        <div class="col-md-6 mb-3">
                                            <label for="address" class="form-label">Address</label>
                                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2">{{ old('address', $customer->address ?? '') }}</textarea>
                                            @error('address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3">Customer Documents</h5>
                                    <div id="drop-area-document">
                                        <i class="fas fa-cloud-upload-alt fa-3x mb-2 text-muted"></i>
                                        <p>Drag & Drop files here or click to select</p>
                                        <input type="file" id="fileElem-document" multiple
                                            accept="image/*,.pdf,.doc,.docx" class="d-none">
                                        <input type="file" name="customer_document[]" id="formImages-document" multiple
                                            class="d-none">
                                    </div>
                                    <div id="gallery-document" class="d-flex flex-wrap">
                                        @if (isset($customer) && $customer->documents)
                                            @foreach ($customer->documents as $doc)
                                                <div class="img-wrapper">
                                                    @if (Str::endsWith($doc, ['.jpg', '.jpeg', '.png', '.gif', '.webp']))
                                                        <a href="{{ asset('storage/' . $doc) }}" class="lightbox">
                                                            <img src="{{ asset('storage/' . $doc) }}" alt="">
                                                        </a>
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center bg-light border rounded"
                                                            style="width: 100px; height: 100px;">
                                                            <i class="fas fa-file fa-2x text-primary"></i>
                                                        </div>
                                                    @endif
                                                    {{-- <button type="button" class="img-remove" data-path="{{ $doc }}">×</button> --}}
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 text-end">
                                <a href="{{ route('customers.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit"
                                    class="btn btn-primary px-4">{{ isset($customer) ? 'Update Customer' : 'Save Customer' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageScripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const profileImageInput = document.getElementById("profileImageInput");
            const profilePreview = document.getElementById("profilePreview");
            const uploadTrigger = document.getElementById("uploadTrigger");

            uploadTrigger.addEventListener("click", function() {
                profileImageInput.click();
            });

            profileImageInput.addEventListener("change", function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profilePreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Init multi-file uploader for documents
            if (typeof initImageUploader === 'function') {
                initImageUploader('drop-area-document', 'fileElem-document', 'gallery-document',
                    'formImages-document');
            }

            // Form validation
            $('#customer-form').validate({
                rules: {
                    name: "required",
                    phone: {
                        required: false,
                        minlength: 10
                    }
                },
                errorElement: 'div',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.mb-3').append(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
@endsection
