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
                                            <x-intl-tel-input name="phone" id="phone" value="{{ old('phone', $customer->phone ?? '') }}" class="{{ $errors->has('phone') ? 'is-invalid' : '' }}" />
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
                                    <div id="gallery-document" class="d-flex flex-wrap gap-2 mt-2">
                                          @if (isset($customer) && $customer->documents)
                                              @foreach ($customer->documents as $index => $doc)
                                                  @php
                                                      $extension = strtolower(pathinfo($doc, PATHINFO_EXTENSION));
                                                      $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                      $isPdf = $extension === 'pdf';
                                                  @endphp
                                                  <div class="img-wrapper border rounded overflow-hidden position-relative shadow-sm hover-scale" style="width: 100px; height: 100px;">
                                                      <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 m-1 delete-doc-btn d-flex align-items-center justify-content-center" 
                                                              style="z-index: 10; width: 22px; height: 22px; padding: 0; border: none; background-color: rgba(220, 53, 69, 0.9);" 
                                                              data-path="{{ $doc }}" title="Delete document">
                                                          <i class="fas fa-times" style="font-size: 10px;"></i>
                                                      </button>
                                                      @if ($isImage)
                                                          <a href="{{ asset('storage/' . $doc) }}" data-fancybox="customer-create-docs" data-caption="Document {{ $index + 1 }}">
                                                              <img src="{{ asset('storage/' . $doc) }}" alt="" class="w-100 h-100 object-fit-cover">
                                                          </a>
                                                      @elseif ($isPdf)
                                                          <a href="{{ asset('storage/' . $doc) }}" data-fancybox="customer-create-docs" data-type="pdf" data-caption="Document {{ $index + 1 }} (PDF)">
                                                              <div class="d-flex flex-column align-items-center justify-content-center bg-danger-subtle text-danger w-100 h-100">
                                                                  <i class="far fa-file-pdf fa-2x mb-1"></i>
                                                                  <span class="fs-10 text-uppercase fw-semibold">PDF</span>
                                                              </div>
                                                          </a>
                                                      @else
                                                          <a href="{{ asset('storage/' . $doc) }}" data-fancybox="customer-create-docs" data-type="iframe" data-caption="Document {{ $index + 1 }}">
                                                              <div class="d-flex flex-column align-items-center justify-content-center bg-secondary-subtle text-secondary w-100 h-100">
                                                                  <i class="fas fa-file fa-2x mb-1"></i>
                                                                  <span class="fs-10 text-uppercase fw-semibold">{{ $extension ?: 'FILE' }}</span>
                                                              </div>
                                                          </a>
                                                      @endif
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
    <!-- PDF Password Modal -->
    <div class="modal fade" id="pdfPasswordModal" tabindex="-1" aria-labelledby="pdfPasswordModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfPasswordModalLabel"><i class="fas fa-lock text-warning me-2"></i>PDF is Password Protected</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-2">This PDF document is encrypted. Please enter the password to decrypt and preview it.</p>
                    <div class="mb-1">
                        <label for="pdfPasswordInput" class="form-label">Password</label>
                        <input type="password" class="form-control" id="pdfPasswordInput" placeholder="Enter PDF password">
                        <div class="text-danger small mt-1 d-none" id="pdfPasswordError">Incorrect password. Please try again.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitPdfPasswordBtn">Decrypt & Preview</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image/PDF Crop Modal -->
    <div class="modal fade" id="cropperModal" tabindex="-1" aria-labelledby="cropperModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cropperModalLabel"><i class="fas fa-crop-alt text-primary me-2"></i>Crop Document / Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="img-container" style="max-height: 450px; overflow: hidden; display: flex; justify-content: center; align-items: center; background-color: #f8f9fa;">
                        <img id="cropperImage" src="" alt="Source Image" style="max-width: 100%; max-height: 450px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="cropSaveBtn">Crop & Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageCss')
    <!-- Fancybox 5 CSS -->
    <link rel="stylesheet" href="{{ asset('vendor-assets/libs/fancybox/fancybox.css') }}" type="text/css" />
@endsection

@section('pageScripts')
    <!-- Fancybox 5 JS -->
    <script src="{{ asset('vendor-assets/libs/fancybox/fancybox.umd.js') }}"></script>
    <!-- CropperJS JS -->
    <script src="{{ asset('vendor-assets/libs/cropperjs/cropper.min.js') }}"></script>
    <!-- PDF.js JS -->
    <script src="{{ asset('vendor-assets/libs/pdfjs/pdf.min.js') }}"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('vendor-assets/libs/pdfjs/pdf.worker.min.js') }}";
    </script>
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

            // Init custom multi-file uploader for documents supporting PDF password verification and Cropping
            initPdfImageUploader('drop-area-document', 'fileElem-document', 'gallery-document', 'formImages-document');

            function initPdfImageUploader(dropAreaId, fileInputId, galleryId, formInputId) {
                const dropArea = document.getElementById(dropAreaId);
                const fileElem = document.getElementById(fileInputId);
                const gallery = document.getElementById(galleryId);
                const formImagesInput = document.getElementById(formInputId);

                let filesArray = [];
                let cropper = null;
                let currentFile = null;
                let currentPdfDoc = null;

                // Bootstrap modals
                const passwordModal = new bootstrap.Modal(document.getElementById('pdfPasswordModal'));
                const cropperModal = new bootstrap.Modal(document.getElementById('cropperModal'));

                const passwordInput = document.getElementById('pdfPasswordInput');
                const passwordError = document.getElementById('pdfPasswordError');
                const submitPasswordBtn = document.getElementById('submitPdfPasswordBtn');
                const cropperImage = document.getElementById('cropperImage');
                const cropSaveBtn = document.getElementById('cropSaveBtn');

                function handleFiles(files) {
                    for (let file of files) {
                        processFile(file);
                    }
                }

                function processFile(file) {
                    currentFile = file;
                    if (file.type === 'application/pdf') {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const typedarray = new Uint8Array(e.target.result);
                            loadPdfData(typedarray);
                        };
                        reader.readAsArrayBuffer(file);
                    } else if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            openCropper(e.target.result, file.name);
                        };
                        reader.readAsDataURL(file);
                    } else {
                        toastr.error("Only PDF and Image files are supported.");
                    }
                }

                function loadPdfData(typedarray, password = '') {
                    // Create a copy to prevent the typedarray buffer from being detached on failure
                    const dataCopy = typedarray.slice(0);
                    
                    pdfjsLib.getDocument({ data: dataCopy, password: password }).promise.then(function(pdf) {
                        currentPdfDoc = pdf;
                        passwordModal.hide();
                        passwordInput.value = '';
                        passwordError.classList.add('d-none');
                        
                        renderPdfPage(1);
                    }).catch(function(error) {
                        if (error.name === 'PasswordException') {
                            if (password !== '') {
                                passwordError.innerText = "Incorrect password. Please try again.";
                                passwordError.classList.remove('d-none');
                            } else {
                                passwordError.classList.add('d-none');
                            }
                            passwordModal.show();
                            
                            submitPasswordBtn.onclick = function() {
                                const pwd = passwordInput.value;
                                if (pwd) {
                                    loadPdfData(typedarray, pwd);
                                } else {
                                    passwordError.innerText = "Please enter a password.";
                                    passwordError.classList.remove('d-none');
                                }
                            };
                        } else {
                            toastr.error("Error loading PDF: " + error.message);
                        }
                    });
                }

                function renderPdfPage(pageNum) {
                    currentPdfDoc.getPage(pageNum).then(function(page) {
                        const viewport = page.getViewport({ scale: 2.0 });
                        const canvas = document.createElement('canvas');
                        const context = canvas.getContext('2d');
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        const renderContext = {
                            canvasContext: context,
                            viewport: viewport
                        };

                        page.render(renderContext).promise.then(function() {
                            const dataUrl = canvas.toDataURL('image/jpeg');
                            openCropper(dataUrl, currentFile.name.replace(/\.[^/.]+$/, "") + ".jpg");
                        });
                    });
                }

                function openCropper(imageSrc, filename) {
                    cropperImage.src = imageSrc;
                    cropperModal.show();

                    if (cropper) {
                        cropper.destroy();
                    }

                    const modalEl = document.getElementById('cropperModal');
                    const onShown = function() {
                        cropper = new Cropper(cropperImage, {
                            aspectRatio: NaN,
                            viewMode: 1,
                            autoCropArea: 0.8,
                        });
                        modalEl.removeEventListener('shown.bs.modal', onShown);
                    };
                    modalEl.addEventListener('shown.bs.modal', onShown);

                    cropSaveBtn.onclick = function() {
                        if (cropper) {
                            const canvas = cropper.getCroppedCanvas({
                                maxWidth: 4096,
                                maxHeight: 4096,
                                fillColor: '#fff',
                                imageSmoothingEnabled: true,
                                imageSmoothingQuality: 'high',
                            });

                            canvas.toBlob(function(blob) {
                                const croppedFile = new File([blob], filename, { type: 'image/jpeg' });
                                filesArray.push(croppedFile);
                                appendToGallery(URL.createObjectURL(blob), croppedFile);
                                syncFormInput();
                                cropperModal.hide();
                            }, 'image/jpeg', 0.9);
                        }
                    };
                }

                function appendToGallery(src, file) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'img-wrapper border rounded overflow-hidden position-relative shadow-sm hover-scale';
                    wrapper.style.width = '100px';
                    wrapper.style.height = '100px';
                    wrapper.style.display = 'inline-block';
                    wrapper.style.margin = '5px';

                    const link = document.createElement('a');
                    link.href = src;
                    link.setAttribute('data-fancybox', 'customer-create-docs');
                    link.setAttribute('data-caption', file.name);

                    const img = document.createElement('img');
                    img.className = 'w-100 h-100 object-fit-cover';
                    img.src = src;
                    link.appendChild(img);

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 m-1 d-flex align-items-center justify-content-center';
                    removeBtn.style.zIndex = '10';
                    removeBtn.style.width = '22px';
                    removeBtn.style.height = '22px';
                    removeBtn.style.padding = '0';
                    removeBtn.style.border = 'none';
                    removeBtn.style.backgroundColor = 'rgba(220, 53, 69, 0.9)';
                    removeBtn.innerHTML = '<i class="fas fa-times" style="font-size: 10px; color: white;"></i>';

                    removeBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        e.preventDefault();
                        wrapper.remove();
                        filesArray = filesArray.filter(f => f !== file);
                        syncFormInput();
                    });

                    wrapper.appendChild(removeBtn);
                    wrapper.appendChild(link);
                    gallery.appendChild(wrapper);

                    if (typeof Fancybox !== 'undefined') {
                        Fancybox.unbind('[data-fancybox="customer-create-docs"]');
                        Fancybox.bind('[data-fancybox="customer-create-docs"]', {
                            Compact: false,
                            Idle: false,
                            Animated: true,
                            dragToClose: true,
                            Toolbar: {
                                display: {
                                    left: ["infobar"],
                                    middle: [],
                                    right: ["slideshow", "download", "thumbs", "close"],
                                },
                            },
                            Html: {
                                pdf: {
                                    type: "pdf"
                                }
                            }
                        });
                    }
                }

                function syncFormInput() {
                    const dataTransfer = new DataTransfer();
                    filesArray.forEach(file => dataTransfer.items.add(file));
                    formImagesInput.files = dataTransfer.files;
                }

                dropArea.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dropArea.classList.add('hover');
                });
                dropArea.addEventListener('dragleave', () => {
                    dropArea.classList.remove('hover');
                });
                dropArea.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropArea.classList.remove('hover');
                    handleFiles(e.dataTransfer.files);
                });
                dropArea.addEventListener('click', (e) => {
                    if (e.target === dropArea || dropArea.contains(e.target)) {
                        fileElem.click();
                    }
                });
                fileElem.addEventListener('change', (e) => {
                    handleFiles(e.target.files);
                });

                document.getElementById('pdfPasswordModal').addEventListener('hidden.bs.modal', function () {
                    passwordInput.value = '';
                    passwordError.classList.add('d-none');
                });
            }

            // Delete existing document button click handler
            document.querySelectorAll(".delete-doc-btn").forEach(function(btn) {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const path = this.getAttribute("data-path");
                    const wrapper = this.closest(".img-wrapper");
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You want to delete this document?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const hiddenInput = document.createElement("input");
                            hiddenInput.type = "hidden";
                            hiddenInput.name = "deleted_documents[]";
                            hiddenInput.value = path;
                            document.getElementById("customer-form").appendChild(hiddenInput);
                            wrapper.remove();
                        }
                    });
                });
            });

            // Bind Fancybox to documents
            if (typeof Fancybox !== 'undefined') {
                Fancybox.bind('[data-fancybox="customer-create-docs"]', {
                    Compact: false,
                    Idle: false,
                    Animated: true,
                    dragToClose: true,
                    Toolbar: {
                        display: {
                            left: ["infobar"],
                            middle: [],
                            right: ["slideshow", "download", "thumbs", "close"],
                        },
                    },
                    Html: {
                        pdf: {
                            type: "pdf"
                        }
                    }
                });
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
