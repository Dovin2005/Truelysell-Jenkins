var pageValue = $('body').data('page');

var frontendValue = $('body').data('frontend');
var languageId = $("#language-settings").data("language-id");

toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": true,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

function initTooltip() {
    $('[data-tooltip="tooltip"]').tooltip({
        trigger: 'hover',
    });
}

//leads
if (pageValue === 'admin.leads') {

    $(document).on('click', '.view-lead-detail', function(e) {
        e.preventDefault();

        const id = $(this).data('id');

        localStorage.setItem('leadId', id);


        window.location.href = '/admin/leadsinfo';
    });

    $(document).on('click', '.accept_btn, .reject_btn', function(e) {
        e.preventDefault();

        var leadId = $(this).data('id');
        var status = $(this).hasClass('accept_btn') ? 2 : 3;

        $.ajax({
            url: '/api/leads/admin/status',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                id: leadId,
                status: status,
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Status updated successfully!');
                } else {
                    toastr.error(response.message || 'Failed to update status.');
                }
            },
            error: function(xhr) {
                toastr.error('Failed to update status. Please try again.');
            }
        });
    });
    $('#sortSelect').change(function() {
        loadLeads(1);
    });
    $('#order_byselect').change(function() {
        loadLeads(1);
    });
    function setActiveTab(tab, status) {
        $('#activeStatusInput').val(status);
        $('.nav-link').removeClass('active');
        $(tab).addClass('active');
    }
    loadLeads();


    function loadLeads(page = 1, status = null) {
        const selectedSortBy = $('#sortSelect').val();
        const selectedOrderBy = $('#order_byselect').val();
        const activeStatus = $('#activeStatusInput').val();


        const payload = {
            order_by: selectedOrderBy,
            sort_by: selectedSortBy,
            search: '',
            page: page,
            per_page: 5,
            status: activeStatus,

        };

        $.ajax({
            url: '/api/leads/admin/list',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function(response) {
                $('#accordionExample').empty();
                if (response.meta && response.meta.counts) {
                    const counts = response.meta.counts;

                    $('#inbox-tab span').text(counts.all || 0);
                    $('#new-tab span').text(counts.new || 0);
                    $('#accept-tab span').text(counts.accept || 0);
                    $('#reject-tab span').text(counts.reject || 0);

                    $('#inbox-tab').prop('disabled', counts.all === 0);
                    $('#new-tab').prop('disabled', counts.new === 0);
                    $('#accept-tab').prop('disabled', counts.accept === 0);
                    $('#reject-tab').prop('disabled', counts.reject === 0);

                    $('#inbox-tab').toggleClass('disabled', counts.all === 0);
                    $('#new-tab').toggleClass('disabled', counts.new === 0);
                    $('#accept-tab').toggleClass('disabled', counts.accept === 0);
                    $('#reject-tab').toggleClass('disabled', counts.reject === 0);
                }

                if (response.data && response.data.data.length > 0) {
                    let showStatusDiv = false;

                    response.data.data.forEach((item) => {
                        if (item.status == '1') {
                            showStatusDiv = true;
                        }

                        const createdAt = new Date(item.created_at).toLocaleString('en-GB', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true,
                        });
                        if (languageId === 2) {
                            const statusLabel = getStatusLabel(item.status);
                            loadJsonFile(statusLabel, function (langtst) {
                                $(`.user_status[data-status="${item.status}"]`).text(langtst);
                            });
                        }
                        const cardHtml = `
                            <div class="card shadow-none mb-2">
                                <div class="card-body p-2">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="input-block todo-inbox-check d-flex align-items-center w-50">
                                                <span class="avatar p-1 me-2 bg-teal-transparent flex-shrink-0">
                                                    <i class="ti ti-user-edit text-info fs-20"></i>
                                                </span>
                                                <div class="strike-info">
                                                <h4 class="mb-1 fs-16">${
                                                        item.user.name
                                                            ? item.user.name.charAt(0).toUpperCase() + item.user.name.slice(1)
                                                            : 'N/A'
                                                    }</h4>
                                                    <p class="d-flex align-items-center"><i class="ti ti-calendar me-1"></i>${item.formatted_created_at}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <div class="strike-info mx-2">
                                                <span class="badge badge-soft-warning ms-1">${
                                                        item.category.name
                                                            ? item.category.name.charAt(0).toUpperCase() + item.category.name.slice(1)
                                                            : 'N/A'
                                                    }</span>
                                            </div>
                                                <div class="d-flex align-items-center flex-fill justify-content-end">
                                                     <div class="notes-card-body d-flex align-items-center user_status" data-status="${item.status}">
                                                        <p class="badge bg-outline-primary me-2 mb-0">
                                                            ${getStatusLabel(item.status)}
                                                        </p>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <a href="#" class="text-decoration-none me-3 view-lead-detail"
                                                        data-id="${item.id}"
                                                        data-name="${item.user.name || 'N/A'}"
                                                        data-status="${item.status || 'New'}"
                                                        data-details="Meet ${item.user.name || 'N/A'} to discuss project details"
                                                        data-created-at="${createdAt}"
                                                        data-category="${item.category.name || 'N/A'}"
                                                        data-form-inputs='${JSON.stringify(item.form_inputs || [])}'
                                                        >
                                                            <i data-feather="eye"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;

                        $('#accordionExample').append(cardHtml);
                    });

                    if (showStatusDiv) {
                        $('#status_div').show();
                    } else {
                        $('#status_div').hide();
                    }

                    const totalPages = response.data.last_page;
                    const currentPage = response.data.current_page;
                    const maxVisiblePages = 5;

                    let startPage = Math.max(currentPage - Math.floor(maxVisiblePages / 2), 1);
                    let endPage = startPage + maxVisiblePages - 1;

                    if (endPage > totalPages) {
                        endPage = totalPages;
                        startPage = Math.max(endPage - maxVisiblePages + 1, 1);
                    }

                    const paginationHtml = `
                        <nav>
                            <ul class="pagination">
                                ${response.data.prev_page_url ?
                                    `<li class="page-item"><a class="page-link" href="#" onclick="loadLeads(${currentPage - 1})">Previous</a></li>` : ''}

                                ${Array.from({ length: endPage - startPage + 1 }, (_, i) => {
                                    const pageNumber = startPage + i;
                                    return `
                                        <li class="page-item ${currentPage === pageNumber ? 'active' : ''}">
                                            <a class="page-link" href="#" onclick="loadLeads(${pageNumber})">${pageNumber}</a>
                                        </li>`;
                                }).join('')}

                                ${response.data.next_page_url ?
                                    `<li class="page-item"><a class="page-link" href="#" onclick="loadLeads(${currentPage + 1})">Next</a></li>` : ''}
                            </ul>
                        </nav>
                    `;

                    $('#pagination').html(paginationHtml);

                    feather.replace();

                    // Modal event binding
                    $('.view-lead-details').on('click', function () {
                        const id = $(this).data('id');
                        const name = $(this).data('name');
                        const status = $(this).data('status');
                        const details = $(this).data('details');
                        const createdAt = $(this).data('created-at');
                        const category = $(this).data('category');
                        let formInputs = $(this).data('form-inputs');

                        $('#view-note-units .accept_btn').data('id', id);
                        $('#view-note-units .reject_btn').data('id', id);
                        $('#view-note-units .modal-body h4').text(name);
                        $('#view-note-units .modal-body .status').text(`Status: ${status}`);
                        $('#view-note-units .modal-body .times').text(`Created At: ${createdAt}`);
                        $('#view-note-units .modal-body .category').text(`Category: ${category}`);

                        if (typeof formInputs === 'string') {
                            formInputs = JSON.parse(formInputs);
                        }

                        let formInputsHtml = '';
                        formInputs.forEach(input => {
                            formInputsHtml += `
                                <div class="col-md-6 border border-1 mt-2">
                                    <div>
                                         <p class="mt-1"><strong>${input.details.title || 'N/A'}:</strong></p>
                                         <h6 class="mb-1">${input.value || 'N/A'}</h6>
                                    </div>
                                </div>`;
                        });
                        $('#form-inputs').html(formInputsHtml);
                    });
                } else {
                    $('#sortSelect').closest('.form-sort').hide();
                    $('#order_byselect').closest('.form-sort').hide();
                    $("#accordionExample").append(`
                        <div class="d-flex justify-content-center align-items-center" style="height: 50vh;">
                            <p class="text-center fw-bold">No leads available</p>
                        </div>
                    `);
                }
                $('#leadsLoader').addClass('d-none');
                $(".label-loader, .input-loader").hide();
                $('#accordionExample, .real-label, .real-input').removeClass('d-none');
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    }


    function getStatusClass(status) {
        switch(status) {
            case 1: return 'bg-outline-primary';
            case 2: return 'bg-outline-warning';
            case 3: return 'bg-outline-danger';
            default: return 'bg-outline-secondary';
        }
    }
    function getStatusLabel(status) {
        switch(status) {
            case 1:
                return 'New';
            case 2:
                return 'Accepted';
            case 3:
                return 'Rejected';
            default:
                return 'Unknown';
        }
    }

}

//leadsinfo
if (pageValue === 'admin.leadsinfo') {
    const leadId = localStorage.getItem('leadId');

    $(document).on('click', '.provider_list', function(e) {
        e.preventDefault();
        providerList();
        function providerList(){
        $.ajax({
            url: '/api/leads/list',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                order_by: 'desc',
                id : leadId,
            },
            success: function(response) {
                if (response.data) {
                    $('#new-tab-content').empty();
                    $('#accepted-tab-content').empty();
                    $('#rejected-tab-content').empty();

                    let newDataFound = false;
                    let acceptedDataFound = false;
                    let rejectedDataFound = false;

                    response.data.user_form_inputs.data.forEach(function(item) {
                        item.provider_forms_inputs.forEach(function(formInput, index) {
                            var createdAt = new Date(item.created_at).toLocaleDateString();

                            let statusText = '';
                            let statusClass = '';
                            switch (formInput.status) {
                                case 1:
                                    statusText = 'New';
                                    statusClass = 'bg-outline-primary';
                                    break;
                                case 2:
                                    statusText = 'Accepted';
                                    statusClass = 'bg-outline-success';
                                    break;
                                case 3:
                                    statusText = 'Rejected';
                                    statusClass = 'bg-outline-danger';
                                    break;
                                default:
                                    statusText = 'Unknown';
                                    statusClass = 'bg-outline-secondary';
                            }
                            if (languageId === 2) {
                                loadJsonFile(statusText, function (langtst) {
                                $(`#provider_status_${index}`).text(langtst);
                                });
                            }

                            // Create the provider card HTML
                            var providerCard = `
                                <div class="card mt-2 mb-2">
                                    <div class="card-body p-3 pb-0">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                                            <div class="input-block todo-inbox-check d-flex align-items-center w-50 mb-3">
                                                <span class="avatar p-1 me-2 bg-teal-transparent flex-shrink-0">
                                                    <i class="ti ti-user-edit text-info fs-20"></i>
                                                </span>
                                                <div class="strike-info">
                                                    <h4 class="mb-1">${formInput.provider.name || 'N/A'}</h4>
                                                </div>
                                                <div class="strike-info mx-2">
                                                    <span class="badge badge-soft-warning ms-1">${item.category.name || 'N/A'}</span>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center flex-fill justify-content-between ms-4 mb-3">
                                                <div class="notes-card-body d-flex align-items-center">
                                                    <p id="provider_status_${index}" class="provider_status badge ${statusClass} mb-0">${statusText}</p>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0)" class="text-decoration-none me-3 view-quote-details">
                                                        <i class="ti ti-eye fs-25">view</i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="provider-quote-details p-3 mb-3 border rounded bg-light" style="display: none;">
                                            <div class="mb-2">
                                                <strong class="text-primary">Quote:</strong>
                                                <span>${formInput.quote || 'N/A'}</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong class="text-primary">Start Date:</strong>
                                                <span>${formInput.start_date || 'N/A'}</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong class="text-primary">Description:</strong>
                                                <span>${formInput.description || 'N/A'}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            switch (formInput.status) {
                                case 1:
                                    $('#new-tab-content').append(providerCard);
                                    newDataFound = true;
                                    break;
                                case 2:
                                    $('#accepted-tab-content').append(providerCard);
                                    acceptedDataFound = true;
                                    break;
                                case 3:
                                    $('#rejected-tab-content').append(providerCard);
                                    rejectedDataFound = true;
                                    break;
                                default:
                                    toastr.error("Unknown status", formInput.status);
                            }
                        });
                    });
                    if (languageId === 2) {
                        loadJsonFile('No providers found', function (langtst) {
                            $('.provider_list').text(langtst);
                        });
                    }
                    if (!newDataFound) {
                        $('#new-tab-content').append('<p class="text-center provider_list text-muted">No providers found</p>');
                    }
                    if (!acceptedDataFound) {
                        $('#accepted-tab-content').append('<p class="text-center provider_list text-muted">No providers found</p>');
                    }
                    if (!rejectedDataFound) {
                        $('#rejected-tab-content').append('<p class="text-center provider_list text-muted">No providers found</p>');
                    }

                    $('.view-quote-details').on('click', function() {
                        $(this).closest('.card').find('.provider-quote-details').slideToggle();
                    });
                }
            },
            error: function(xhr) {
                toastr.error('Failed to update status. Please try again.');
            }
        });
    }
    });

    $(document).on('click', '.accept_btn, .reject_btn', function(e) {
        e.preventDefault();

        var leadId = $(this).data('id');
        var status = $(this).hasClass('accept_btn') ? 2 : 3;

        $.ajax({
            url: '/api/leads/admin/status',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                id: leadId,
                status: status,
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Status updated successfully!');
                } else {
                    toastr.error(response.message || 'Failed to update status.');
                }
            },
            error: function(xhr) {
                toastr.error('Failed to update status. Please try again.');
            }
        });
    });

    const payload = {
        order_by: 'asc',
        sort_by: 'created_at',
        search: '',
        id : leadId
    };

    function capitalizeFirstLetter(string) {
        if (!string) return string;
        return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
    }
    $.ajax({
        url: '/api/leads/list',
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
            'Accept': 'application/json'
        },
        data: JSON.stringify(payload),
        contentType: 'application/json',
        success: function(response) {
            if (response.success &&response.data.user_form_inputs.data.length > 0) {
                const lead = response.data.user_form_inputs.data[0];

                $('.modal-title').text(`Lead ID: ${lead.id}`);
                $('.times').text(`${lead.formatted_created_at}`);

                let statusText = '';
                let statusClass = '';
                switch (lead.status) {
                    case 1:
                        statusText = 'New';
                        statusClass = 'bg-outline-primary';
                        break;
                    case 2:
                        statusText = 'Accept';
                        statusClass = 'bg-outline-success';
                        break;
                    case 3:
                        statusText = 'Reject';
                        statusClass = 'bg-outline-danger';
                        break;
                    default:
                        statusText = 'Unknown';
                        statusClass = 'bg-outline-secondary';
                }
                if (languageId === 2) {
                    loadJsonFile(statusText, function (langtst) {
                        $('.status').text(langtst);

                    });
                } else {
                    $('.status').text(statusText);
                }

                $('.status').text(statusText)
                            .removeClass('bg-outline-primary bg-outline-success bg-outline-danger bg-outline-secondary')
                            .addClass(statusClass);

                if (lead.status === 1) {
                    $('#status_div').show();
                    $('#accept_btn').data('id', lead.id);
                    $('#reject_btn').data('id', lead.id);
                } else {
                    $('#status_div').hide();
                }

                $('.category').text(
                    `Category: ${
                        lead.category.name
                            ? lead.category.name.charAt(0).toUpperCase() + lead.category.name.slice(1)
                            : 'N/A'
                    }`
                );
                $('.sub_category').text(
                    `Sub Category: ${
                        lead.sub_category?.name
                            ? lead.sub_category.name.charAt(0).toUpperCase() + lead.sub_category.name.slice(1)
                            : '-'
                    }`
                );
                if (languageId === 2) {
                    loadJsonFile('Category', function (langtst) {
                        $('.category').text(`${langtst}: ${capitalizeFirstLetter(lead.category.name)}`);
                    });

                    loadJsonFile('Sub Category', function (langtst) {
                        const subCategoryName = capitalizeFirstLetter(lead.sub_category?.name) ?? '-';
                        $('.sub_category').text(`${langtst}: ${subCategoryName}`);
                    });
                }
                $('.username').text(
                    `${
                        lead.user.name
                            ? lead.user.name.charAt(0).toUpperCase() + lead.user.name.slice(1)
                            : 'N/A'
                    }`
                );


                $('#form-inputs').empty();
                lead.form_inputs.forEach(input => {
                    $('#form-inputs').append(`
                        <div class="col-md-12">
                            <div class="tab-info mt-3 border border-1 p-2">
                                 ${input.id !== "sub_category_id" ? `<h5 class="mt-2">${input.details.title}:</h5>` : ""}

                                <!-- If the option is not null, do not display input.value directly -->
                                ${
                                    input.id !== "sub_category_id"
                                        ? input.details.option && input.details.option !== "null"
                                            ? (() => {
                                                  const options1 = JSON.parse(input.details.option);
                                                  const options = JSON.parse(options1);
                                                  const matchedOption = options.find(
                                                      (option) => option.value === input.value
                                                  );
                                                  return matchedOption
                                                      ? `<p>${matchedOption.key}</p>`
                                                      : `<p>${input.value}</p>`;
                                              })()
                                            : input.value.country &&
                                              input.value.state &&
                                              input.value.city
                                            ? `<p>${input.value.country}, ${input.value.state}, ${input.value.city}</p>`
                                            : input.value && input.value.includes('uploads/leads/')
                                            ? (() => {
                                                const fileExtension = input.value.split('.').pop().toLowerCase();
                                                const documentExtensions = ['pdf', 'doc', 'docx', 'txt'];

                                                if (documentExtensions.includes(fileExtension)) {
                                                    return `<a href="/storage/${input.value}" download class="btn btn-primary">
                                                                Download Document
                                                            </a>`;
                                                } else {
                                                    return `<img src="/storage/${input.value}" alt="Image Preview" class="img-leads" />`;
                                                }
                                            })()
                                            : input.value
                                            ? `<p>${input.value}</p>`
                                            : ""
                                    : ""
                                }

                            </div>
                        </div>
                    `);
                });
            } else {
                toastr.error("No lead data found.");
            }
        },
        error: function(xhr) {
            if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error("An error occurred while retrieving lead data.");
            }
        }
    });


}
