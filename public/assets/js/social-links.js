/* global $, document, FormData */
(async () => {
    "use strict";

    window._l = window._l || function (key) {
        return key;
    };

    // Ensure toastr is properly initialized
    window.showToast = window.showToast || function (type, message) {
        // Using toastr if available, fallback to alert
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            alert(`[${type.toUpperCase()}] ${message}`);
        }
    };

    const permissions = window.permissions || [];
    let table;

    $(document).ready(function () {
        initTable();
        initEvents();
        initFormValidation();
    });

    function initEvents() {
        $(document).on('click', '.add-new', function () {
            $("#add_social_link .modal-title").text("Add Social Link");
            $("#add_social_link .submitbtn").text("Create New");

            const formElement = $("#addSocialLinkForm")[0];
            if (formElement) formElement.reset();

            $("#id").val("");
            $("#status").prop('checked', false);
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid is-valid");
            $("#add_social_link").modal("show");
        });

        $(document).on("click", ".editSocialLink", function () {
            let id = $(this).data("id");
            $.get(`/admin/get_social_link/${id}`, function (response) {
                if (response.code === 200) {
                    const d = response.data;
                    $("#platform_name").val(d.platform_name);
                    $("#link").val(d.link);
                    $("#icon").val(d.icon);
                    $("#id").val(d.id);
                    $("#status").prop('checked', d.status == 1);
                    $("#add_social_link .modal-title").text("Edit Social Link");
                    $("#add_social_link .submitbtn").text("Save Changes");
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid is-valid");
                    $("#add_social_link").modal("show");
                } else {
                    showToast("error", response.message);
                }
            }).fail(function (xhr) {
                showToast("error", xhr.responseJSON?.message || "Error fetching data.");
            });
        });

        $(document).on("click", ".deleteSocialLink", function () {
            $("#delete_id").val($(this).data("id"));
        });

        // Status toggle
        $(document).on('change', '.status-toggle', function () {
            const id = $(this).data('id');
            const currentStatus = $(this).is(':checked') ? 1 : 0;
            const toggle = $(this);

            $.ajax({
                url: '/admin/toggle-social-link-status',
                type: 'POST',
                data: {
                    id: id,
                    status: currentStatus,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.code === 200) {
                        showToast('success', response.message);
                    } else {
                        toggle.prop('checked', !currentStatus);
                        showToast('error', response.message);
                    }
                },
                error: function (xhr) {
                    toggle.prop('checked', !currentStatus);
                    showToast('error', xhr.responseJSON?.message || 'Error updating status.');
                }
            });
        });
    }

    function initFormValidation() {
        $("#addSocialLinkForm").validate({
            rules: {
                platform_name: { required: true, minlength: 3, maxlength: 30 },
                link: { required: true, url: true },
                icon: { required: true, minlength: 3, maxlength: 30 }
            },
            messages: {
                platform_name: {
                    required: "Platform name is required",
                    minlength: "Platform name must be at least 3 characters",
                    maxlength: "Platform name cannot exceed 30 characters",
                },
                link: {
                    required: "Link is required",
                    url: "Please enter a valid URL",
                },
                icon: {
                    required: "Icon is required",
                    minlength: "Icon must be at least 3 characters",
                    maxlength: "Icon cannot exceed 30 characters",
                }
            },
            errorPlacement: function (error, element) {
                $("#" + element.attr("id") + "_error").text(error.text());
            },
            highlight: function (element) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element) {
                $(element).removeClass("is-invalid").addClass("is-valid");
                $("#" + element.id + "_error").text("");
            },
            submitHandler: function (form) {
                let formData = new FormData(form);
                formData.append('status', $("#status").is(':checked') ? 1 : 0);

                $.ajax({
                    type: "POST",
                    url: "/admin/store-social-link",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr("content")
                    },
                    beforeSend: function () {
                        $(".submitbtn").attr("disabled", true).html(`
                            <span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span> Saving...
                        `);
                    },
                    complete: function () {
                        $(".submitbtn").attr("disabled", false).text(
                            $("#id").val() ? "Save Changes" : "Create New"
                        );
                    },
                    success: function (resp) {
                        if (resp.code === 200) {
                            showToast("success", resp.message);
                            $("#add_social_link").modal("hide");
                            table.ajax.reload();
                        }
                    },
                    error: function (error) {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid is-valid");
                        if (error.responseJSON?.code === 422) {
                            $.each(error.responseJSON.message, function (key, val) {
                                $("#" + key).addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                            });
                        } else {
                            showToast("error", error.responseJSON.message || "Error occurred.");
                        }
                    },
                });
            },
        });
    }

    function initTable() {
        table = $("#socialLinksTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/admin/get_social_links",
                type: "POST",
                data: function (d) {
                    d._token = $('meta[name="csrf-token"]').attr("content");
                    d.search = $("#search").val();
                },
                beforeSend: function () {
                    $(".table-loader").show();
                    $(".real-table, .table-footer").addClass("d-none");
                },
                complete: function () {
                    $(".table-loader").hide();
                    $(".real-table, .real-label, .real-input").removeClass("d-none");
                    if ($("#socialLinksTable").DataTable().rows().count() === 0) {
                        $(".table-footer").addClass("d-none");
                    } else {
                        $(".table-footer").removeClass("d-none");
                    }
                },
                error: function (xhr) {
                    console.error("AJAX Load Error:", xhr.responseText);
                    showToast("error", "Failed to load data. Check console for more info.");
                }
            },
            order: [["1", "desc"]],
            ordering: false,
            searching: false,
            pageLength: 10,
            lengthChange: false,
            columns: [
                { data: "platform_name", render: d => `<h6 class="fw-medium text-black">${d}</h6>` },
                { data: "link", render: d => `<p class="text-gray-9 fw-semibold fs-14">${d}</p>` },
                { data: "icon", render: d => `<p class="text-gray-9 fw-semibold fs-14">${d}</p>` },
                {
                    data: "status",
                    render: function (data) {
                        const badgeClass = data == 1 ? 'badge bg-success' : 'badge bg-danger';
                        const badgeText = data == 1 ? 'Active' : 'Inactive';
                        return `<span class="${badgeClass}">${badgeText}</span>`;
                    }
                },
                {
                    data: "id",
                    render: function (data) {
                        let actions = ``;
                        if (hasPermission(permissions, "website_settings", "edit")) {
                            actions += `<li><button class="dropdown-item editSocialLink" data-id="${data}"><i class="ti ti-edit me-1"></i>Edit</button></li>`;
                        }
                        if (hasPermission(permissions, "website_settings", "delete")) {
                            actions += `<li><button class="dropdown-item deleteSocialLink" data-id="${data}" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash me-1"></i>Delete</button></li>`;
                        }
                        return `<div class="dropdown">
                            <button class="btn btn-icon btn-sm" type="button" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-2">${actions}</ul>
                        </div>`;
                    },
                    visible: hasPermission(permissions, "website_settings", "edit") || hasPermission(permissions, "website_settings", "delete"),
                },
            ],
            drawCallback: function () {
                $(".dataTables_info, .dataTables_paginate").addClass("d-none");
                const tableWrapper = $(this).closest(".dataTables_wrapper");
                $(".table-footer").empty().append(
                    $('<div class="d-flex justify-content-between align-items-center w-100"></div>')
                        .append($('<div class="datatable-info"></div>').append(tableWrapper.find(".dataTables_info").clone(true)))
                        .append($('<div class="datatable-pagination"></div>').append(tableWrapper.find(".dataTables_paginate").clone(true)))
                );
                $(".table-footer").find(".dataTables_paginate").removeClass("d-none");
            },
            language: {
                emptyTable: "No data available in table",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                lengthMenu: "Show _MENU_ entries",
                zeroRecords: "No matching records found",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous",
                },
            },
        });
    }

    // Handle delete
    $("#deleteForm").on("submit", function (e) {
        e.preventDefault();
        $.post("/admin/delete-social-link", $(this).serialize(), function (response) {
            if (response.code === 200) {
                showToast("success", response.message);
            } else {
                showToast("error", response.message);
            }
            $("#delete_modal").modal("hide");
            table.ajax.reload();
        }).fail(function (xhr) {
            showToast("error", xhr.responseJSON?.message || "Delete failed.");
            $("#delete_modal").modal("hide");
        });
    });

    function hasPermission(permissions, module, action) {
        return true; // Adjust based on actual permission logic
    }

})();
