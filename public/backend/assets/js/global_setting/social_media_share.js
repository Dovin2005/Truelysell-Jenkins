/* global $, document, FormData, showToast */
(async () => {
    "use strict";

    // Fallback translation function if not defined
    window._l = window._l || function(key) {
        const translations = {
            "admin.common.create_new": "Create New",
            "admin.common.save_changes": "Save Changes",
            "admin.common.saving": "Saving",
            "admin.common.active": "Active",
            "admin.common.inactive": "Inactive",
            "admin.common.edit": "Edit",
            "admin.common.delete": "Delete",
            "admin.general_settings.platform_required": "Platform name is required",
            "admin.general_settings.platform_minlength": "Platform name must be at least 3 characters",
            "admin.general_settings.link_required": "URL is required",
            "admin.general_settings.enter_valid_url": "Please enter a valid URL",
            "admin.general_settings.icon_required": "Icon class is required",
            "admin.general_settings.icon_minlength": "Icon class must be at least 3 characters",
            "admin.general_settings.edit_social_media_share": "Edit Social Media Share"
        };
        return translations[key] || key;
    };

    // Fallback permission function if not defined
    window.hasPermission = window.hasPermission || function(permissions, module, action) {
        // Default to true if permission system not available
        return true;
    };

    // Fallback showToast if not defined
    window.showToast = window.showToast || function(type, message) {
        console.log(`${type.toUpperCase()}: ${message}`);
    };

    let table;
    let permissions = {};

    try {
        // Try to load permissions if available
        if (typeof loadUserPermissions === 'function') {
            permissions = await loadUserPermissions();
        }

        $(document).ready(function () {
            initTable();
            initEvents();
            initFormValidation();
        });

        function initEvents() {
            $(document).on("click", ".add-new", function () {
                $("#add_social_media_share").modal("show");
                $("#addSocialMediaShareForm")[0].reset();
                $("#id").val("");
                $(".form-control").removeClass("is-invalid is-valid");
                $(".error-text").text("");
                $(".submitbtn").text(_l("admin.common.create_new"));
            });
        }

        function initFormValidation() {
            $("#addSocialMediaShareForm").validate({
                rules: {
                    platform_name: {
                        required: true,
                        minlength: 3,
                        maxlength: 30,
                    },
                    url: {
                        required: true,
                        url: true,
                    },
                    icon: {
                        required: true,
                        minlength: 3,
                        maxlength: 30,
                    }
                },
                messages: {
                    platform_name: {
                        required: _l("admin.general_settings.platform_required"),
                        minlength: _l("admin.general_settings.platform_minlength"),
                    },
                    url: {
                        required: _l("admin.general_settings.link_required"),
                        url: _l("admin.general_settings.enter_valid_url"),
                    },
                    icon: {
                        required: _l("admin.general_settings.icon_required"),
                        minlength: _l("admin.general_settings.icon_minlength"),
                    }
                },
                errorPlacement: function (error, element) {
                    $("#" + element.attr("id") + "_error").text(error.text());
                },
                submitHandler: function (form) {
                    let formData = new FormData(form);
                    formData.set("status", $("#status").is(":checked") ? 1 : 0);
                    $.ajax({
                        type: "POST",
                        url: "/admin/store-social-media-share",
                        data: formData,
                        processData: false,
                        contentType: false,
                        beforeSend: function () {
                            $(".submitbtn").attr("disabled", true).html(
                                `<span class="spinner-border spinner-border-sm" role="status"></span> ${_l("admin.common.saving")}...`
                            );
                        },
                        complete: function () {
                            $(".submitbtn").attr("disabled", false).text(
                                $("#id").val() ? _l("admin.common.save_changes") : _l("admin.common.create_new")
                            );
                        },
                        success: function (res) {
                            showToast("success", res.message);
                            $("#add_social_media_share").modal("hide");
                            table.ajax.reload();
                        },
                        error: function (err) {
                            if (err.status === 422) {
                                $.each(err.responseJSON.errors, function (key, val) {
                                    $("#" + key).addClass("is-invalid");
                                    $("#" + key + "_error").text(val[0]);
                                });
                            } else {
                                showToast("error", err.responseJSON.message || "An error occurred.");
                            }
                        },
                    });
                }
            });
        }

        function initTable() {
            table = $("#socialMediaShareTable").DataTable({
                processing: true,
                serverSide: true,
                paging: false, // Disable pagination
                searching: false, // Enable search
                info: false, // Optionally remove info like "Showing 1 to 10 of X"
                ajax: {
                    url: "/admin/get-social-media-shares",
                    type: "POST",
                    data: { _token: $('meta[name="csrf-token"]').attr("content") },
                    beforeSend: function () {
                        $(".table-loader").removeClass("d-none");
                        $(".real-table").addClass("d-none");
                    },
                    complete: function () {
                        $(".table-loader").addClass("d-none");
                        $(".real-table").removeClass("d-none");
                    }
                },
                columns: [
                    { data: "platform_name" },
                    { data: "url" },
                    {
                        data: "icon",
                        render: function (icon) {
                            return icon ? `<span class="ms-2">${icon}</span>` : `<span class="text-muted">-</span>`;
                        }
                    },
                    {
                        data: "status",
                        render: function (data) {
                            const isActive = data == 1;
                            return `<span class="badge ${isActive ? 'bg-success' : 'bg-danger'}">
                                <i class="ti ti-point-filled me-1"></i>${_l(isActive ? "admin.common.active" : "admin.common.inactive")}
                            </span>`;
                        }
                    },
                    {
                        data: "id",
                        render: function (data, type, row) {
                            return `
                                <div class="dropdown">
                                    <button class="btn btn-icon btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end p-2">
                                        ${hasPermission(permissions, "website_settings", "edit") ? `
                                            <li>
                                                <button type="button" class="dropdown-item rounded-1 editSocialLink" data-id="${row.id}">
                                                    <i class="ti ti-edit me-1"></i>${_l("admin.common.edit")}
                                                </button>
                                            </li>` : ''}
                                        ${hasPermission(permissions, "website_settings", "delete") ? `
                                            <li>
                                                <button type="button" class="dropdown-item rounded-1 deleteSocialLink" data-id="${row.id}" data-bs-toggle="modal" data-bs-target="#delete_modal">
                                                    <i class="ti ti-trash me-1"></i>${_l("admin.common.delete")}
                                                </button>
                                            </li>` : ''}
                                    </ul>
                                </div>`;
                        },
                        visible: hasPermission(permissions, "website_settings", "edit") || hasPermission(permissions, "website_settings", "delete")
                    }
                ]
            });
        }

        $(document).on("click", ".editSocialLink", function () {
            const id = $(this).data("id");

            $.get(`/admin/get-social-media-share/${id}`, function (res) {
                if (res) {
                    $("#add_social_media_share #platform_name").val(res.platform_name);
                    $("#add_social_media_share #url").val(res.url);
                    $("#add_social_media_share #icon").val(res.icon);
                    $("#add_social_media_share #id").val(res.id);
                    $("#add_social_media_share #status").prop("checked", res.status == 1);

                    $("#add_social_media_share .modal-title").text(_l("admin.general_settings.edit_social_media_share"));
                    $("#add_social_media_share .submitbtn").text(_l("admin.common.save_changes"));

                    $(".form-control").removeClass("is-invalid is-valid");
                    $(".error-text").text("");
                    $("#statusDiv").removeClass("d-none")
                        .parent().removeClass("justify-content-end").addClass("justify-content-between");

                    $("#add_social_media_share").modal("show");
                } else {
                    showToast("error", res.message || "Failed to load data.");
                }
            }).fail(function (err) {
                showToast("error", err.responseJSON?.message || "Something went wrong.");
            });
        });

        // Change this in the delete button click handler
        $(document).on("click", ".deleteSocialLink", function(e) {
        e.preventDefault();
        const id = $(this).data("id");
        $("#delete_id").val(id);

        // Safely initialize and show the modal
        const modalEl = document.getElementById('delete_modal');
        if (!modalEl) {
            console.error("Delete modal element not found!");
            return;
        }

        // Using Bootstrap's native modal instance
        const deleteModal = new bootstrap.Modal(modalEl);
        deleteModal.show();
    });

    // Delete form submission handler - improved version
    $("#deleteForm").on("submit", function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');

        // Set loading state
        $submitBtn.prop('disabled', true)
            .html(`<span class="spinner-border spinner-border-sm"></span> ${_l("admin.common.deleting")}...`);

        $.ajax({
            type: "POST",
            url: "/admin/delete-social-media-share",
            data: $form.serialize(),
            success: function(res) {
                showToast("success", res.message);
                const modal = bootstrap.Modal.getInstance(document.getElementById('delete_modal'));
                if (modal) modal.hide();
                table.ajax.reload(null, false); // Reload without resetting paging
            },
            error: function(err) {
                showToast("error", err.responseJSON?.message || "An error occurred during deletion.");
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text(_l("admin.common.delete"));
            }
        });
    });
    } catch (error) {
        console.error("Initialization error:", error);
        showToast("error", "Failed to initialize social media share functionality");
    }
})();