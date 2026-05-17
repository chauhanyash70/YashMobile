$(document).ready(function() {
    // Brand Table Initialization
    const brandTable = $('#brandDatatable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: getBrandDataUrl,
            type: "POST",
            data: { _token: csrfToken }
        },
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'type' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 2,
                render: function(data, type, full, meta) {
                    let badgeClass = 'bg-primary';
                    if (data === 'device') badgeClass = 'bg-success';
                    if (data === 'accessory') badgeClass = 'bg-warning';
                    return `<span class="badge ${badgeClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                }
            },
            {
                width: "150px",
                targets: -1,
                title: "Actions",
                orderable: false,
                render: function(data, type, full, meta) {
                    return `
                        <div class="d-flex gap-2">
                            <a href="javascript:void(0);" onclick="openEditModal(${full.id}, '${full.name}', '${full.type}', '${full.slug}')" class="btn btn-sm btn-outline-info">
                                <i class="iconoir-edit-pencil fs-18"></i>
                            </a>
                            <a href="javascript:void(0);" onclick="deleteBrand(${full.id})" class="btn btn-sm btn-outline-danger">
                                <i class="iconoir-trash fs-18"></i>
                            </a>
                        </div>
                    `;
                },
            },
        ],
    });

    // Slug generation
    $('#name').on('input', function() {
        let name = $(this).val();
        let slug = name.trim().toLowerCase()
                      .replace(/[^a-z0-9\s-]/g, '')
                      .replace(/\s+/g, '-')
                      .replace(/-+/g, '-');
        $('#slug').val(slug);
    });

    // Form Validation
    const brandForm = $('#brandForm');
    if (brandForm.length) {
        brandForm.validate({
            rules: {
                name: {
                    required: true,
                    minlength: 2
                },
                type: {
                    required: true
                }
            },
            messages: {
                name: {
                    required: "Please enter brand name",
                    minlength: "Name must be at least 2 characters long"
                },
                type: {
                    required: "Please select brand type"
                }
            },
            errorElement: 'div',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                error.insertAfter(element);
            },
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function(form) {
                let $form = $(form);
                let url = $form.attr('action');
                let method = $form.find('input[name="_method"]').val() || 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: $form.serialize(),
                    success: function(res) {
                        $('#brandModal').modal('hide');
                        brandTable.ajax.reload();
                       toastr.success(res.message);
                    },
                    error: function(xhr) {
                        let errorMessage = 'Something went wrong!';
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join('\n');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        toastr.error(errorMessage);
                    }
                });
                return false;
            }
        });
    }

    // Modal helpers exposing to window for global access
    window.openCreateModal = function() {
        $('#brandModalLabel').text('Add Brand');
        $('#brandForm').attr('action', brandStoreUrl);
        $('#methodField').html('');
        $('#name').val('');
        $('#type').val('device');
        $('#slug').val('');
        $('#brandForm').validate().resetForm();
        $('.is-invalid').removeClass('is-invalid');
        const modal = new bootstrap.Modal(document.getElementById('brandModal'));
        modal.show();
    };

    window.openEditModal = function(id, name, type, slug) {
        $('#brandModalLabel').text('Edit Brand');
        $('#brandForm').attr('action', brandBaseUrl + "/" + id);
        $('#methodField').html('<input type="hidden" name="_method" value="PUT">');
        $('#name').val(name);
        $('#type').val(type);
        $('#slug').val(slug);
        $('#brandForm').validate().resetForm();
        $('.is-invalid').removeClass('is-invalid');
        const modal = new bootstrap.Modal(document.getElementById('brandModal'));
        modal.show();
    };

    window.deleteBrand = function(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: brandBaseUrl + '/' + id,
                    type: 'POST',
                    data: { 
                        _token: csrfToken,
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        brandTable.ajax.reload();
                        Swal.fire(
                            'Deleted!',
                            res.message || 'Brand has been deleted.',
                            'success'
                        );
                    },
                    error: function(xhr) {
                        let errorMessage = 'Error deleting brand!';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire(
                            'Error!',
                            errorMessage,
                            'error'
                        );
                    }
                });
            }
        });
    };
});
