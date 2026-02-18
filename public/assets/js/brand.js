/* global $, loadTranslationFile, document, FormData, showToast, _l */
(async () => {
    "use strict";

    try {
        await loadTranslationFile("admin", "general_settings,common");
    } catch (error) {
        console.warn('Translation file not loaded, using fallbacks');
    }

    let table;

    const translations = {
        'admin.general_settings.edit_brand': 'Edit Brand',
        'admin.general_settings.add_brand': 'Add Brand',
        'admin.general_settings.title_required': 'Title is required',
        'admin.general_settings.title_minlength': 'Title must be at least 2 characters',
        'admin.general_settings.title_maxlength': 'Title must not exceed 100 characters',
        'admin.common.save_changes': 'Save Changes',
        'admin.common.create_new': 'Create New',
        'admin.common.edit': 'Edit',
        'admin.common.delete': 'Delete',
        'admin.common.saving': 'Saving'
    };

    function t(key) {
        return (typeof _l !== 'undefined' && _l(key)) || translations[key] || key;
    }

    $(document).ready(function () {
        initTable();
        initEvents();
        initFormValidation();
    });

    function initTable() {
        showTableLoader();

        table = $("#brandsTable").DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            responsive: true,
            order: [[2, 'desc']],
            ajax: {
                url: "/admin/brands/list",
                type: "GET",
                data: d => {
                    d._token = $('meta[name="csrf-token"]').attr("content");
                },
                beforeSend: showTableLoader,
                complete: hideTableLoader,
                error: function (xhr, error, thrown) {
                    console.error('DataTable AJAX error:', error, thrown);
                    hideTableLoader();
                    showToast("error", "Failed to load brands data");
                }
            },
            columns: [
                {
                    data: "image",
                    name: "image",
                    orderable: false,
                    searchable: false,
                    render: renderImageCell
                },
                {
                    data: "title",
                    name: "title",
                    render: data => data || 'N/A'
                },
                {
                    data: "status",
                    name: "status",
                    render: function (data) {
                        const isActive = data === 1 || data === 'active';
                        const badgeClass = isActive ? 'badge-soft-success' : 'badge-soft-danger';
                        const label = isActive ? 'Active' : 'Inactive';
                        return `<span class="badge ${badgeClass} d-inline-flex align-items-center">${label}</span>`;
                    }
                },
                {
                    data: "id",
                    name: "actions",
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `
                            <a href="#" class="editBrand" data-id="${data}" title="${t('admin.common.edit')}">
                                <i class="ti ti-pencil m-2 fs-20 text-dark"></i>
                            </a>
                            <a href="#" class="deleteBrand" data-id="${data}" data-title="${row.title || 'this brand'}" title="${t('admin.common.delete')}">
                                <i class="ti ti-trash m-2 fs-20 text-dark"></i>
                            </a>
                        `;
                    }
                }
            ],
            language: {
                processing: "Loading brands...",
                emptyTable: "No brands found",
                zeroRecords: "No matching brands found"
            }
        });
    }

    function initEvents() {
        $(document).on('click', '.add-new-brand', function () {
            resetBrandForm();
            $("#add_brand_modal .modal-title").text(t("admin.general_settings.add_brand"));
            $(".submitbtn").text(t("admin.common.create_new"));
            $("#add_brand_modal").modal("show");
        });

        $(document).on("click", ".editBrand", function (e) {
            e.preventDefault();
            const id = $(this).data("id");
            if (!id) return showToast("error", "Invalid brand ID");

            $.ajax({
                type: "GET",
                url: `/admin/brands/edit/${id}`,
                success: function (resp) {
                    if (resp.code === 200 && resp.data) {
                        const brandData = resp.data;
                        resetBrandForm();

                        $("#brand_id").val(brandData.id);
                        $("#brand_title").val(brandData.title);

                        if (brandData.image) {
                            displayImagePreview(brandData.image);
                        }

                        $("#add_brand_modal .modal-title").text(t("admin.general_settings.edit_brand"));
                        $(".submitbtn").text(t("admin.common.save_changes"));
                        $("#add_brand_modal").modal("show");
                    } else {
                        showToast("error", resp.message || "Brand not found");
                    }
                },
                error: function (error) {
                    const msg = error.responseJSON?.message || "Failed to load brand";
                    showToast("error", msg);
                }
            });
        });

        $(document).on("click", ".deleteBrand", function (e) {
            e.preventDefault();
            const id = $(this).data("id");
            const title = $(this).data("title") || "this brand";
            $("#delete-modal input[name='delete_id']").val(id);
            $("#delete-modal .modal-body p").text(`Are you sure you want to delete "${title}"?`);
            $("#delete-modal").modal("show");
        });

        $(document).on("click", "#confirmDelete", function () {
            const id = $("#delete-modal input[name='delete_id']").val();
            if (!id) return showToast("error", "No brand selected");

            $.ajax({
                type: "POST",
                url: `/admin/brands/delete`,
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                    id: id
                },
                beforeSend: function () {
                    $("#confirmDelete").attr("disabled", true).html(`<span class="spinner-border spinner-border-sm me-2"></span>Deleting...`);
                },
                complete: function () {
                    $("#confirmDelete").attr("disabled", false).text("Delete");
                },
                success: function (resp) {
                    if (resp.code === 200) {
                        showToast("success", resp.message);
                        $("#delete-modal").modal("hide");
                        table.ajax.reload();
                    } else {
                        showToast("error", resp.message);
                    }
                },
                error: function (error) {
                    const message = error.responseJSON?.message || "Delete failed";
                    showToast("error", message);
                }
            });
        });

        $(document).on('change', '#brand_image', function () {
            const file = this.files[0];
            if (file && file.size > 5 * 1024 * 1024) {
                showToast("error", "File size must be under 5MB");
                this.value = '';
                return;
            }

            if (file) {
                const reader = new FileReader();
                reader.onload = e => showImagePreview(e.target.result, "Preview");
                reader.readAsDataURL(file);
            }
        });
    }

    function initFormValidation() {
        if (typeof $.fn.validate === 'undefined') {
            $("#addBrandForm").on('submit', function (e) {
                e.preventDefault();
                handleFormSubmit(this);
            });
            return;
        }

        $("#addBrandForm").validate({
            rules: {
                title: {
                    required: true,
                    minlength: 2,
                    maxlength: 100,
                },
                image: {
                    accept: "image/*"
                }
            },
            messages: {
                title: {
                    required: t("admin.general_settings.title_required"),
                    minlength: t("admin.general_settings.title_minlength"),
                    maxlength: t("admin.general_settings.title_maxlength"),
                },
                image: {
                    accept: "Only image files allowed"
                }
            },
            errorPlacement: (error, element) => {
                $("#" + element.attr("id") + "_error").text(error.text());
            },
            highlight: el => $(el).addClass("is-invalid"),
            unhighlight: el => {
                $(el).removeClass("is-invalid");
                $("#" + el.id + "_error").text("");
            },
            submitHandler: handleFormSubmit
        });
    }

    function handleFormSubmit(form) {
        const formData = new FormData(form);
        const id = $("#brand_id").val();
        const url = id ? "/admin/brands/update" : "/admin/brands/store";
        if (id) formData.append("id", id);

        $.ajax({
            type: "POST",
            url,
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: () => {
                $(".submitbtn").attr("disabled", true).html(`<span class="spinner-border spinner-border-sm me-2"></span>${t("admin.common.saving")}...`);
            },
            complete: () => {
                $(".submitbtn").attr("disabled", false).text(id ? t("admin.common.save_changes") : t("admin.common.create_new"));
            },
            success: function (resp) {
                if (resp.code === 200) {
                    showToast("success", resp.message);
                    $("#add_brand_modal").modal("hide");
                    table.ajax.reload();
                } else {
                    showToast("error", resp.message);
                }
            },
            error: function (error) {
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid");

                if (error.responseJSON?.code === 422) {
                    $.each(error.responseJSON.message, function (key, val) {
                        $("#" + key).addClass("is-invalid");
                        $("#" + key + "_error").text(Array.isArray(val) ? val[0] : val);
                    });
                } else {
                    showToast("error", error.responseJSON?.message || "Submission failed");
                }
            }
        });
    }

    function resetBrandForm() {
        $("#addBrandForm")[0].reset();
        $("#brand_id").val("");
        $(".error-text").text("");
        $(".form-control").removeClass("is-invalid");
        $("#image_preview").remove();
    }

    function renderImageCell(imageUrl) {
        if (!imageUrl) imageUrl = "/assets/img/default-image.png";
        else if (!imageUrl.startsWith("http")) imageUrl = `/storage/${imageUrl}`;
        return `<img src="${imageUrl}" class="img-thumbnail" style="width: 50px; height: 50px;" onerror="this.src='/assets/img/default-image.png'">`;
    }

    function displayImagePreview(imageUrl) {
        if (!imageUrl) return;
        if (!imageUrl.startsWith("http")) imageUrl = `/storage/${imageUrl}`;
        showImagePreview(imageUrl, "Current Image");
    }

    function showImagePreview(src, label) {
        $("#image_preview").remove();
        const html = `
            <div id="image_preview" class="mb-3">
                <label>${label}</label>
                <div>
                    <img src="${src}" class="img-thumbnail" style="width: 100px; height: 100px;" onerror="this.src='/assets/img/default-image.png'">
                </div>
            </div>
        `;
        $("#brand_image").closest('.mb-3').before(html);
    }

    function showTableLoader() {
        $(".table-loader").show();
    }

    function hideTableLoader() {
        $(".table-loader").hide();
    }

    function showToast(type, message) {
        if (typeof window.showToast === 'function') {
            window.showToast(type, message);
        } else {
            console.log(`${type.toUpperCase()}: ${message}`); // fallback
        }
    }

    window.brandModule = {
        table,
        refreshTable: () => table?.ajax.reload(),
        resetForm: resetBrandForm
    };

})();
