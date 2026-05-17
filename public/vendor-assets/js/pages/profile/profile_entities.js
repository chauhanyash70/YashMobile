$(document).ready(function() {
    // Initialize Datatables when tabs/pills are clicked
    $(document).on('shown.bs.tab', 'a[data-bs-toggle="tab"], a[data-bs-toggle="pill"]', function (e) {
        var target = $(e.target).attr("href") || $(e.target).data("bs-target");
        if (target === '#manage_customers') {
            initCustomerTable();
        }
    });

    // Check if we are already on the customers tab
    if (window.location.hash === '#manage_customers') {
        initCustomerTable();
    }

    let customerTable = null;
    function initCustomerTable() {
        if ($.fn.DataTable.isDataTable('#customer-table')) {
            $('#customer-table').DataTable().columns.adjust().draw();
            return;
        }

        console.log("Initializing customer table...");
        customerTable = $('#customer-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: customerDataUrl,
                type: 'POST',
                data: function(d) {
                    d._token = csrfToken;
                },
                error: function(xhr, error, thrown) {
                    console.error("DataTable AJAX error:", xhr.responseText);
                }
            },
            columns: [
                { 
                    data: 'name',
                    render: function(data, type, row) {
                        return `
                            <div class="d-flex align-items-center">
                                <img src="${row.profile_url}" alt="" class="rounded-circle me-2" height="30" width="30" style="object-fit: cover;">
                                <span>${data}</span>
                            </div>
                        `;
                    }
                },
                { data: 'phone' },
                { data: 'address' },
                { 
                    data: 'actions',
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <div class="d-flex align-items-center">
                                <a href="${row.details_url}" class="btn btn-sm btn-info me-1"><i class="fas fa-eye"></i></a>
                                <a href="${row.edit_url}" class="btn btn-sm btn-primary me-1"><i class="fas fa-edit"></i></a>
                                <button class="btn btn-sm btn-danger" onclick="destroyFunction(this)" data-id="${row.id}" data-url="${row.delete_url}"><i class="fas fa-trash"></i></button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[0, 'asc']],
            language: {
                searchPlaceholder: "Search customers...",
                search: ""
            }
        });
    }
});
