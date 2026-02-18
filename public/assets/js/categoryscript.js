var pageValue = $('body').data('page');

var frontendValue = $('body').data('frontend');

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

var datatableLang = {
    "lengthMenu": $('#datatable_data').data('length_menu'),
    "info": $('#datatable_data').data('info'),
    "infoEmpty": $('#datatable_data').data('info_empty'),
    "infoFiltered": $('#datatable_data').data('info_filter'),
    "search": $('#datatable_data').data('search'),
    "zeroRecords": $('#datatable_data').data('zero_records'),
    "paginate": {
        "first": $('#datatable_data').data('first'),
        "last": $('#datatable_data').data('last'),
        "next": $('#datatable_data').data('next'),
        "previous": $('#datatable_data').data('prev'),
    }
}

function initTooltip() {
    $('[data-tooltip="tooltip"]').tooltip({
        trigger: 'hover',
    });
}

var langCode = $('body').data('lang');

//categories
if(pageValue == 'admin.servicecategories') {

    $(document).ready(function () {
        loadCategories();
    });

    $(document).on('change', '#language', function (e) {
        let languageId = $(this).val();
        loadCategories(languageId);
    });

    $('#slug, #category_name').on('input', function (e) {
        $(this).attr('maxlength', 100);
    })

    function loadCategories(languageId = '') {
        $.ajax({
            url: "/api/categories/list",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
                sort_by: "id",
                language_id: languageId ? languageId : $('#language').val()
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code == 200) {

                    let categories = response.data;
                    let tableBody = "";

                    if ($.fn.DataTable.isDataTable('#categories_table')) {
                        $('#categories_table').DataTable().destroy();
                    }

                    if (categories.length == 0) {
                        tableBody += `
                            <tr>
                                <td colspan="6" class="text-center">${$('#categories_table').data('empty')}</td>
                            </tr>`;
                    } else {
                        categories.forEach((val, index) => {
                            tableBody += `
                                <tr>
                                    <td>${ index + 1 }</td>
                                    <td>
                                        <div class="d-flex align-items-center flex-nowrap min-w-200px">
                                            <img src="${val.image}" class="rounded me-2 category-img" alt="image">
                                            <span class="text-break">${val.name.length > 20 ? val.name.substring(0, 20) + "..." : val.name}</span>
                                        </div>
                                    </td>
                                    <td>${val.slug}</td>
                                    <td>
                                        <span class="badge ${(val.status == 1)? 'badge-soft-success' : 'badge-soft-danger'} d-inline-flex align-items-center">
                                            <i class="ti ti-circle-filled fs-5 me-1"></i>${(val.status == 1)? 'Active' : 'In-active'}
                                        </span>
                                    </td>
                                    ${ $('#has_permission').data('edit') == 1 ?
                                    `<td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input category_featured" ${(val.featured == 1)? 'checked' : ''} type="checkbox"
                                                role="switch" id="switch-sm" data-id="${val.id}">
                                        </div>
                                    </td>` : ''
                                    }
                                    ${ $('#has_permission').data('visible') == 1 ?
                                    `<td>
                                        <li style="list-style: none;">
                                            ${ $('#has_permission').data('edit') == 1 ?
                                            `<a href="javascript:void(0);" class="edit_category_btn"
                                            data-id="${val.id}" data-parent-id="${val.parent_id}" data-name="${val.name}"
                                            data-description="${val.description}" data-slug="${val.slug}"
                                            data-status="${val.status}" data-language_id="${val.language_id}" data-featured="${val.featured}"
                                            data-image="${val.image}" data-icon="${val.icon}">
                                            <i class="ti ti-pencil fs-20"></i>
                                            </a>` : ''
                                            }
                                            ${ $('#has_permission').data('delete') == 1 ?
                                            `<a href="javascript:void(0);" class=" delete_category_modal" data-bs-toggle="modal" data-bs-target="#delete-modal"
                                            data-id="${val.id}">
                                                <i class="ti ti-trash fs-20 m-1"></i>
                                            </a>` : ''
                                            }
                                            ${val.parent_id === 0 && $('#has_permission').data('view') == 1 ? `
                                                <a href="javascript:void(0);" class="manage_forms_input" data-id="${val.id}">
                                                    <i class="ti ti-settings fs-20 m-1"></i>
                                                </a>
                                            ` : ''}
                                        </li>
                                    </td>` : ''
                                    }
                                </tr>`;
                        });
                    }

                    $('#categories_table tbody').html(tableBody);
                    $('#loader-table').addClass('d-none');
                    $(".label-loader, .input-loader").hide();
                    $('#categories_table, .real-label, .real-input').removeClass('d-none');

                    if ((categories.length != 0) && !$.fn.DataTable.isDataTable('#categories_table')) {
                        $('#categories_table').DataTable({
                            ordering: true,
                            language: datatableLang
                        });
                    }
                }
            },
            error: function (error) {
                if (error.status === 422) {
                    var errors = error.responseJSON.errors;
                    if (errors) {
                        $.each(errors, function (key, messages) {
                            toastr.error(messages[0]);
                        });
                    } else {
                        toastr.error("An error occurred while fetching.");
                    }
                }
            },
        });
    }

    $('#categoryForm').validate({
        rules: {
            language_id: {
                required: true,
            },
            category_name: {
                required: true,
            },
            slug: {
                required: true,
            },
            category_image: {
                required: function () {
                    return $("#id").val() == '';
                },
            },
            category_icon: {
                required: function () {
                    return $("#id").val() == '';
                },
            },
            description: {
                required: true,
            }
        },
        messages: {
            language_id: {
                required: $('#language_id_error').data('required'),
            },
            category_name: {
                required: $('#category_name_error').data('required'),
            },
            slug: {
                required: $('#slug_error').data('required'),
            },
            category_image: {
                required: $('#category_image_error').data('required'),
            },
            category_icon: {
                required: $('#category_icon_error').data('required'),
            },
            description: {
                required: $('#description_error').data('required'),
            }
        },
        errorPlacement: function (error, element) {
            var errorId = element.attr("id") + "_error";
            $("#" + errorId).text(error.text());
        },
        highlight: function (element) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element) {
            $(element).removeClass("is-invalid").addClass("is-valid");
            var errorId = element.id + "_error";
            $("#" + errorId).text("");
        },
        onkeyup: function(element) {
            $(element).valid();
        },
        onchange: function(element) {
            $(element).valid();
        },
        submitHandler: function (form) {
            var formData = new FormData(form);

            formData.append('source_type', 'service');
            formData.set("status", $("#status").is(":checked") ? 1 : 0);
            formData.set("featured", $("#featured").is(":checked") ? 1 : 0);
            formData.append('language_code', langCode);

            $.ajax({
                url: "/api/categories/save",
                type: "POST",
                data: formData,
                enctype: "multipart/form-data",
                contentType: false,
                processData: false,
                cache: false,
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                    'Accept': 'application/json'
                },
                beforeSend: function () {
                    $(".category_save_btn").attr("disabled", true).html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
                },
            })
            .done(function (response) {
                if ($.fn.DataTable.isDataTable('#categories_table')) {
                    $('#categories_table').DataTable().destroy();
                }
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid is-valid");
                $(".category_save_btn").removeAttr("disabled").html($('.category_save_btn').data('save'));
                if (response.code === 200) {
                    toastr.success(response.message);
                    $("#save_category_modal").modal('hide');
                    loadCategories();
                }
            })
            .fail(function (error) {
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid is-valid");
                $(".category_save_btn").removeAttr("disabled").html($('.category_save_btn').data('save'));
                if (error.status == 422) {
                    $.each(error.responseJSON.errors, function (key, val) {
                        $("#" + key).addClass("is-invalid");
                        $("#" + key + "_error").text(val[0]);
                    });
                }
            });
        }
    });

    $(document).on('click', '.manage_forms_input', function () {
        var categoryId = $(this).data('id');

        $.ajax({
            url: '/admin/set-category-id',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  // Ensure CSRF token is included
            },
            data: {
                category_id: categoryId
            },
            success: function(response) {
                if (response.success) {
                    // Redirect to the form categories page without the ID in the URL
                    window.location.href = "/admin/form-categories";
                }
            },
            error: function(xhr) {
                toastr.log('Error:', xhr.responseText);
            }
        });
    });

    $('#category_image').on('change', function (event) {
        var input = event.target;
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#category_image_view').attr('src', e.target.result);
            };
            $('#category_image_view').show();
            $('.upload_icon').addClass('d-none');

            reader.readAsDataURL(input.files[0]);
        }
    });

    $('#category_icon').on('change', function (event) {
        var input = event.target;
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#category_icon_view').attr('src', e.target.result);
            };
            $('#category_icon_view').show();
            $('.upload_icon_2').addClass('d-none');

            reader.readAsDataURL(input.files[0]);
        }
    });

    $(document).on('click', '#add_category_btn', function () {
        $('#id').val('');
        $('.category_modal_title').text($('.category_modal_title').data('add_category'));
        $('#categoryForm')[0].reset();
        $('.form-control, .form-select').removeClass('is-invalid is-valid');
        $('.error-text').text('');
        $('#language_id').val('');
        $('#category_image_view, #category_icon_view').hide();
        $('.upload_icon, .upload_icon_2').removeClass('d-none');
    });

    $(document).on('click', '.edit_category_btn', function () {
        $('#id').val($(this).data('id'));
        $('.category_modal_title').text($('.category_modal_title').data('edit_category'));
        $('.form-control, .form-select').removeClass('is-invalid is-valid');
        $('.error-text').text('');
        $('#language_id').val($(this).data('language_id'));
        $('#category_name').val($(this).data('name'));
        $('#slug').val($(this).data('slug'));
        $('#status').prop('checked', $(this).data('status') == 1 ? true : false);
        $('#featured').prop('checked', $(this).data('featured') == 1 ? true : false);
        $('#description').val($(this).data('description'));
        $('#category_image_view').attr('src', $(this).data('image'));
        $('#category_icon_view').attr('src', $(this).data('icon'));
        $('#category_image_view, #category_icon_view').show();
        $('.upload_icon, .upload_icon_2').addClass('d-none');

        $('#save_category_modal').modal('show');
    });

    $(document).on('click', '.delete_category_modal', function(e) {
        e.preventDefault();

        var categoryId = $(this).data('id');
        $('.category_delete_btn').data('id', categoryId);
    });

    $(document).on('click', '.category_delete_btn', function (e) {
        e.preventDefault();
        var categoryId = $(this).data('id');

        var formData = {
            'id' : categoryId,
            'language_code': langCode
        };

        $.ajax({
            url: '/api/categories/delete',
            type: 'POST',
            data : formData,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#delete-modal').modal('hide');
                if (response.code === 200) {
                    loadCategories();
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while deleting.');
            }
        });
    });

    $(document).on('change', '.category_featured', function () {
        let id = $(this).data('id');
        let featured = $(this).is(':checked') ? 1 : 0;
        $.ajax({
            url: '/api/categories/change-featured',
            type: 'POST',
            data : {
                id: id,
                featured: featured,
                language_code: langCode
            },
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.code == 200) {
                    loadCategories();
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while updating.');
            }
        });
    });
}

if(pageValue == 'admin.servicesubcategories') {

    $(document).ready(function () {
        loadSubCategories();
    });

    $(document).on('change', '#category', function (e) {
        let categoryId = $(this).val();
        loadSubCategories(categoryId);
    });

    $(document).on('change', '#language', function (e) {
        let languageId = $(this).val();
        loadSubCategories('', languageId);
        getCategories(languageId);
    });

    $('#slug, #subcategory_name').on('input', function (e) {
        $(this).attr('maxlength', 100);
    })

    function getCategories(languageId = '') {
        $.ajax({
            url: "/api/get-categories",
            type: "POST",
            dataType: "json",
            data: {
                language_id: languageId ? languageId : $('#language').val()
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                let categories = response;
                if (categories) {
                    let selectOptions = `<option value="">${$('#category_id').data('select')}</option>`;
                    categories.forEach(category => {
                        selectOptions += `<option value="${category.id}">${category.name}</option>`;
                    });
                    $('#category_id').html(selectOptions);

                    let selectOptions2 = `<option value="">${$('#category').data('select')}</option>`;
                    categories.forEach(category => {
                        selectOptions2 += `<option value="${category.id}">${category.name}</option>`;
                    });
                    $('#category').html(selectOptions2);
                }
            },
        });
    }

    function loadSubCategories(categoryId = '', languageId = '') {
        $.ajax({
            url: "/api/subcategories/list",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
                sort_by: "id",
                category_id: categoryId ? categoryId : $('#category').val(),
                language_id: languageId ? languageId : $('#language').val()
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code == 200) {

                    let categories = response.data;
                    let tableBody = "";

                    if ($.fn.DataTable.isDataTable('#categories_table')) {
                        $('#categories_table').DataTable().destroy();
                    }

                    if (categories.length == 0) {
                        tableBody += `
                            <tr>
                                <td colspan="7" class="text-center">${$('#categories_table').data('empty')}</td>
                            </tr>`;
                    } else {
                        categories.forEach((val, index) => {
                            tableBody += `
                                <tr>
                                    <td>${ index + 1 }</td>
                                    <td>
                                        <div class="d-flex align-items-center flex-nowrap min-w-200px">
                                            <img src="${val.image}" class="rounded me-2 category-img" alt="image">
                                            <span class="text-break">${val.name.length > 20 ? val.name.substring(0, 20) + "..." : val.name}</span>
                                        </div>
                                    </td>
                                    <td>${val.slug}</td>
                                    <td>${val.parent_category ? val.parent_category.name : '-'}</td>
                                    <td>
                                        <span class="badge ${(val.status == 1)? 'badge-soft-success' : 'badge-soft-danger'} d-inline-flex align-items-center">
                                            <i class="ti ti-circle-filled fs-5 me-1"></i>${(val.status == 1)? 'Active' : 'In-active'}
                                        </span>
                                    </td>
                                    ${ $('#has_permission').data('edit') == 1 ?
                                    `<td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input category_featured" ${(val.featured == 1)? 'checked' : ''} type="checkbox"
                                                role="switch" data-id="${val.id}">
                                        </div>
                                    </td>` : ''
                                    }
                                    ${ $('#has_permission').data('visible') == 1 ?
                                    `<td>
                                        <li style="list-style: none;">
                                            ${ $('#has_permission').data('edit') == 1 ?
                                            `<a href="javascript:void(0);" class="edit_category_btn"
                                            data-id="${val.id}" data-name="${val.name}"
                                            data-description="${val.description}" data-slug="${val.slug}"
                                            data-status="${val.status}" data-language_id="${val.language_id}" data-featured="${val.featured}"
                                            data-image="${val.image}" data-icon="${val.icon}" data-parent_category_id="${val.parent_category ? val.parent_category.id : ''}">
                                            <i class="ti ti-pencil fs-20"></i>
                                            </a>` : ''
                                            }
                                            ${ $('#has_permission').data('delete') == 1 ?
                                            `<a href="javascript:void(0);" class=" delete_category_modal" data-bs-toggle="modal" data-bs-target="#delete-modal"
                                            data-id="${val.id}">
                                                <i class="ti ti-trash fs-20 m-1"></i>
                                            </a>` : ''
                                            }
                                        </li>
                                    </td>` : ''
                                    }
                                </tr>`;
                        });
                    }

                    $('#categories_table tbody').html(tableBody);
                    $('#loader-table').addClass('d-none');
                    $(".label-loader, .input-loader").hide();
                    $('#categories_table, .real-label, .real-input').removeClass('d-none');

                    if ((categories.length != 0) && !$.fn.DataTable.isDataTable('#categories_table')) {
                        $('#categories_table').DataTable({
                            ordering: true,
                            language: datatableLang
                        });
                    }
                }
            },
            error: function (error) {
                if (error.status === 422) {
                    var errors = error.responseJSON.errors;
                    if (errors) {
                        $.each(errors, function (key, messages) {
                            toastr.error(messages[0]);
                        });
                    } else {
                        toastr.error("An error occurred while fetching.");
                    }
                }
            },
        });
    }

    $('#subcategoryForm').validate({
        rules: {
            category_id: {
                required: true,
            },
            subcategory_name: {
                required: true,
            },
            slug: {
                required: true,
            },
            category_image: {
                required: function () {
                    return $("#id").val() == '';
                },
            },
            category_icon: {
                required: function () {
                    return $("#id").val() == '';
                },
            },
            description: {
                required: true,
            }
        },
        messages: {
            category_id: {
                required: $('#category_id_error').data('required'),
            },
            subcategory_name: {
                required: $('#subcategory_name_error').data('required'),
            },
            slug: {
                required: $('#slug_error').data('required'),
            },
            category_image: {
                required: $('#category_image_error').data('required'),
            },
            category_icon: {
                required: $('#category_icon_error').data('required'),
            },
            description: {
                required: $('#description_error').data('required'),
            }
        },
        errorPlacement: function (error, element) {
            var errorId = element.attr("id") + "_error";
            $("#" + errorId).text(error.text());
        },
        highlight: function (element) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element) {
            $(element).removeClass("is-invalid").addClass("is-valid");
            var errorId = element.id + "_error";
            $("#" + errorId).text("");
        },
        onkeyup: function(element) {
            $(element).valid();
        },
        onchange: function(element) {
            $(element).valid();
        },
        submitHandler: function (form) {
            var formData = new FormData(form);

            formData.append('source_type', 'service');
            formData.set("status", $("#status").is(":checked") ? 1 : 0);
            formData.set("featured", $("#featured").is(":checked") ? 1 : 0);
            formData.append('language_code', langCode);
            formData.append('language_id', $('#language').val());

            $.ajax({
                url: "/api/subcategories/save",
                type: "POST",
                data: formData,
                enctype: "multipart/form-data",
                contentType: false,
                processData: false,
                cache: false,
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                    'Accept': 'application/json'
                },
                beforeSend: function () {
                    $(".category_save_btn").attr("disabled", true).html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
                },
            })
            .done(function (response) {
                if ($.fn.DataTable.isDataTable('#categories_table')) {
                    $('#categories_table').DataTable().destroy();
                }
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid is-valid");
                $(".category_save_btn").removeAttr("disabled").html($('.category_save_btn').data('save'));
                if (response.code === 200) {
                    toastr.success(response.message);
                    $("#save_category_modal").modal('hide');
                    loadSubCategories();
                }
            })
            .fail(function (error) {
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid is-valid");
                $(".category_save_btn").removeAttr("disabled").html($('.category_save_btn').data('save'));
                if (error.status == 422) {
                    $.each(error.responseJSON.errors, function (key, val) {
                        $("#" + key).addClass("is-invalid");
                        $("#" + key + "_error").text(val[0]);
                    });
                }
            });
        }
    });

    $('#category_image').on('change', function (event) {
        var input = event.target;
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#category_image_view').attr('src', e.target.result);
            };
            $('#category_image_view').show();
            $('.upload_icon').addClass('d-none');

            reader.readAsDataURL(input.files[0]);
        }
    });

    $('#category_icon').on('change', function (event) {
        var input = event.target;
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#category_icon_view').attr('src', e.target.result);
            };
            $('#category_icon_view').show();
            $('.upload_icon_2').addClass('d-none');

            reader.readAsDataURL(input.files[0]);
        }
    });

    $(document).on('click', '#add_category_btn', function () {
        $('#id').val('');
        $('.category_modal_title').text($('.category_modal_title').data('add_category'));
        $('#subcategoryForm')[0].reset();
        $('.form-control, .form-select').removeClass('is-invalid is-valid');
        $('.error-text').text('');
        $('#category_id').val('');
        $('#category_image_view, #category_icon_view').hide();
        $('.upload_icon, .upload_icon_2').removeClass('d-none');
    });

    $(document).on('click', '.edit_category_btn', function () {
        $('#id').val($(this).data('id'));
        $('.category_modal_title').text($('.category_modal_title').data('edit_category'));
        $('.form-control, .form-select').removeClass('is-invalid is-valid');
        $('.error-text').text('');
        $('#category_id').val($(this).data('parent_category_id'));
        $('#subcategory_name').val($(this).data('name'));
        $('#slug').val($(this).data('slug'));
        $('#status').prop('checked', $(this).data('status') == 1 ? true : false);
        $('#featured').prop('checked', $(this).data('featured') == 1 ? true : false);
        $('#description').val($(this).data('description'));
        $('#category_image_view').attr('src', $(this).data('image'));
        $('#category_icon_view').attr('src', $(this).data('icon'));
        $('#category_image_view, #category_icon_view').show();
        $('.upload_icon, .upload_icon_2').addClass('d-none');

        $('#save_category_modal').modal('show');
    });

    $(document).on('click', '.delete_category_modal', function(e) {
        e.preventDefault();

        var categoryId = $(this).data('id');
        $('.category_delete_btn').data('id', categoryId);
    });

    $(document).on('click', '.category_delete_btn', function (e) {
        e.preventDefault();
        var categoryId = $(this).data('id');

        var formData = {
            'id' : categoryId,
            'language_code': langCode
        };

        $.ajax({
            url: '/api/categories/delete',
            type: 'POST',
            data : formData,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#delete-modal').modal('hide');
                if (response.code === 200) {
                    loadSubCategories();
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while deleting.');
            }
        });
    });

    $(document).on('change', '.category_featured', function () {
        let id = $(this).data('id');
        let featured = $(this).is(':checked') ? 1 : 0;
        $.ajax({
            url: '/api/categories/change-featured',
            type: 'POST',
            data : {
                id: id,
                featured: featured,
                language_code: langCode
            },
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.code == 200) {
                    loadSubCategories();
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred while updating.');
            }
        });
    });
}

//form-categories
if(pageValue == 'admin.form-categories') {
    $(document).ready(function() {
        $('#addFormInputButton').on('click', function() {
            $('#addFormsInputForm')[0].reset();

            $('#placeholder_select_edit').val('');

            $('#required_status').prop('checked', false);

            $('#placeholder_div, #options-container, #file_size, #other_option').hide();

            $('#placeholder_select_edit_error').text('');
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid");
        });

        $('#placeholder_select_edit').change(function() {
            const selectedValue = $(this).val();
            const placeholderDiv = $('#placeholder_div');
            const optionsContainer = $('#options-container');
            const fileSize = $('#file_size');
            const otherOption = $('#other_option');

            placeholderDiv.toggle(selectedValue === "text_field" || selectedValue === "number_field" || selectedValue === "textarea" || selectedValue === "timepicker" || selectedValue === "datepicker");
            optionsContainer.toggle(selectedValue === "select" || selectedValue === "checkbox" || selectedValue === "radio");
            fileSize.toggle(selectedValue === "file");
            otherOption.toggle(selectedValue === "select" || selectedValue === "checkbox" || selectedValue === "radio");
        });

        $('#add-option-btn').click(function() {
            // Get the current number of options in the options container
            const optionCount = $('#options-container .option-item').length;

            // Limit the options to 10
            if (optionCount >= 10) {
                toastr.error("You can add a maximum of 10 options.");
                return; // Stop the function if the limit is reached
            }

            const optionDiv = $('<div class="option-item d-flex align-items-center mb-2"></div>');
            const optionName = $('<input type="text" class="form-control me-2 option-name" placeholder="Enter Option">');
            const optionValue = $('<input type="text" class="form-control me-2 option-value" placeholder="Enter Value">');
            const deleteBtn = $('<button type="button" class="btn"><i class="ti ti-trash-x me-2 btn-danger"></i></button>');

            deleteBtn.click(function() {
                optionDiv.remove();
            });

            optionDiv.append(optionName, optionValue, deleteBtn);
            $('#options-container').append(optionDiv);
        });

        $('#addFormsInputForm').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);

            formData.set('is_required', $('#required_status').is(':checked') ? 1 : 0);

            const selectedFieldType = $('#placeholder_select_edit').val();
            formData.set('input_type', selectedFieldType);

            if (selectedFieldType === "text_field" || selectedFieldType === "number_field" || selectedFieldType === "textarea" || selectedFieldType === "timepicker" || selectedFieldType === "datepicker") {
                formData.set('form_placeholder', $('#form_placeholder').val());
            }

            if (selectedFieldType === "file") {
                formData.set('file_size', $('#file_size_no').val());
            }

            if (selectedFieldType === "select" || selectedFieldType === "checkbox" || selectedFieldType === "radio") {
                const optionsArray = [];
                $('#options-container .option-item').each(function() {
                    const optionName = $(this).find('.option-name').val();
                    const optionValue = $(this).find('.option-value').val();
                    if (optionName && optionValue) {
                        optionsArray.push({ key: optionName, value: optionValue });
                    }
                });
                if (optionsArray.length > 0) {
                    formData.set('options', JSON.stringify(optionsArray));
                }
                formData.set('has_other_option', $('#status_toggle').is(':checked') ? 1 : 0);
            }

            $.ajax({
                url: '/api/categories/form-inputs',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done((response) => {
                if (response.code === '200') {
                    toastr.success(response.message);
                    $('#add_language').modal('hide');
                    $('#addFormsInputForm')[0].reset();
                    $('#required_status').prop('checked', false);
                    $('#placeholder_div, #options-container, #file_size, #other_option').hide();
                    loadFormInputList();
                } else {
                    toastr.error(response.message);
                }
            })
            .fail((xhr) => {
                const errorMessage = xhr.responseJSON?.message || 'An error occurred. Please try again.';

                if (xhr.status === 422) {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $.each(xhr.responseJSON.errors, function (key, val) {
                        $("#" + key).addClass("is-invalid");
                        $("#" + key + "_error").text(val[0]);
                    });
                } else {
                    toastr.error(errorMessage, "bg-danger");
                }
            })
        });

        let categoryId = $('#category_id').val();


        function loadFormInputList() {
            $.ajax({
                url: '/api/categories/form-inputs/list',
                type: 'POST',
                data: {
                    category_id: categoryId,
                },
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.code === 200) {
                        $('#draggable-left').empty();

                        response.data.forEach(function(input, index) {
                            var label = $('<label class="form-label col-4"></label>').text(input.label);
                            var inputField = null;

                            switch(input.type) {
                                case 'text_field':
                                    inputField = $('<input>', {
                                        type: 'text',
                                        class: 'form-control',
                                        name: input.name,
                                        placeholder: input.placeholder,
                                        required: input.is_required == 1,
                                        disabled: true
                                    });
                                    break;
                                case 'number_field':
                                    inputField = $('<input>', {
                                        type: 'number',
                                        class: 'form-control',
                                        name: input.name,
                                        placeholder: input.placeholder,
                                        required: input.is_required == 1,
                                        min: input.min_value || '',
                                        max: input.max_value || '',
                                        step: input.step_value || '1',
                                        disabled: true
                                    });
                                    break;
                                case 'textarea':
                                    inputField = $('<textarea>', {
                                        class: 'form-control',
                                        name: input.name,
                                        placeholder: input.placeholder,
                                        required: input.is_required == 1,
                                        disabled: true
                                    });
                                    break;
                                case 'select':
                                    inputField = $('<select>', {
                                        class: 'form-control',
                                        name: input.name,
                                        required: input.is_required == 1,
                                        disabled: true
                                    });
                                    try {
                                        let options = JSON.parse(input.options);
                                        if (typeof options === 'string') {
                                            options = JSON.parse(options);
                                        }
                                        if (Array.isArray(options)) {
                                            options.forEach(function(option) {
                                                var optionElement = $('<option>', {
                                                    value: option.value,
                                                    text: option.key
                                                });
                                                inputField.append(optionElement);
                                            });
                                        }
                                    } catch (e) {
                                        toastr.error('Error parsing options:', e);
                                    }
                                    break;
                                case 'checkbox':
                                    inputField = $('<div class="form-check disabled">');
                                    try {
                                        let options = JSON.parse(input.options);
                                        if (typeof options === 'string') {
                                            options = JSON.parse(options);
                                        }
                                        options.forEach(function(option) {
                                            var checkboxWrapper = $('<div class="form-check">');
                                            var checkbox = $('<input>', {
                                                type: 'checkbox',
                                                class: 'form-check-input',
                                                name: input.name + '[]',
                                                value: option.value
                                            });
                                            var checkboxLabel = $('<label class="form-check-label">').text(option.key);
                                            checkboxWrapper.append(checkbox).append(checkboxLabel);
                                            inputField.append(checkboxWrapper);
                                        });
                                    } catch (e) {
                                        toastr.error('Error parsing options:', e);
                                    }
                                    break;
                                case 'radio':
                                    inputField = $('<div class="form-check disabled">');
                                    try {
                                        let options = JSON.parse(input.options);
                                        if (typeof options === 'string') {
                                            options = JSON.parse(options);
                                        }
                                        options.forEach(function(option) {
                                            var radioWrapper = $('<div class="form-check">');
                                            var radio = $('<input>', {
                                                type: 'radio',
                                                class: 'form-check-input',
                                                name: input.name,
                                                value: option.value
                                            });
                                            var radioLabel = $('<label class="form-check-label">').text(option.key);
                                            radioWrapper.append(radio).append(radioLabel);
                                            inputField.append(radioWrapper);
                                        });
                                    } catch (e) {
                                        toastr.error('Error parsing options:', e);
                                    }
                                    break;
                                case 'datepicker':
                                    inputField = $('<input>', {
                                        type: 'date',
                                        class: 'form-control',
                                        name: input.name,
                                        required: input.is_required == 1,
                                        disabled: true
                                    });
                                    break;
                                case 'timepicker':
                                    inputField = $('<input>', {
                                        type: 'time',
                                        class: 'form-control',
                                        name: input.name,
                                        required: input.is_required == 1,
                                        disabled: true
                                    });
                                    break;
                                case 'file':
                                    inputField = $('<input>', {
                                        type: 'file',
                                        class: 'form-control',
                                        name: input.name,
                                        required: input.is_required == 1,
                                        disabled: true
                                    });
                                    break;
                                case 'location':
                                        var countryDropdown = $('<select>', {
                                            id: 'country',  // Assign an ID to access it in jQuery
                                            class: 'form-control mb-2',
                                            name: input.name + '_country',
                                            required: input.is_required == 1
                                        }).append($('<option>', { value: '', text: 'Select Country' }));

                                        var stateDropdown = $('<select>', {
                                            id: 'state',  // Assign an ID to access it in jQuery
                                            class: 'form-control mb-2',
                                            name: input.name + '_state',
                                            required: input.is_required == 1
                                        }).append($('<option>', { value: '', text: 'Select State', disabled: true, selected: true }));

                                        var cityDropdown = $('<select>', {
                                            id: 'city',  // Assign an ID to access it in jQuery
                                            class: 'form-control',
                                            name: input.name + '_city',
                                            required: input.is_required == 1
                                        }).append($('<option>', { value: '', text: 'Select City', disabled: true, selected: true }));

                                        inputField = $('<div>').append(countryDropdown).append(stateDropdown).append(cityDropdown);

                                        $.getJSON('/countries.json', function(data) {
                                            $.each(data.countries, function(index, country) {
                                                countryDropdown.append($('<option>', { value: country.id, text: country.name }));
                                            });
                                        }).fail(function() {
                                            toastr.error('Error loading country data');
                                        });

                                        countryDropdown.on('change', function() {
                                            const selectedCountry = $(this).val();

                                            $.getJSON('/states.json', function(data) {
                                                stateDropdown.empty();
                                                stateDropdown.append($('<option>', {
                                                    value: '',
                                                    text: 'Select State',
                                                    disabled: true,
                                                    selected: true
                                                }));

                                                $.each(data.states, function(index, state) {
                                                    if (state.country_id === selectedCountry) {
                                                        stateDropdown.append($('<option>', { value: state.id, text: state.name }));
                                                    }
                                                });
                                            }).fail(function() {
                                                toastr.error('Error loading state data');
                                            });
                                        });

                                        stateDropdown.on('change', function() {
                                            const selectedState = $(this).val();

                                            $.getJSON('/cities.json', function(data) {
                                                cityDropdown.empty();
                                                cityDropdown.append($('<option>', {
                                                    value: '',
                                                    text: 'Select City',
                                                    disabled: true,
                                                    selected: true
                                                }));

                                                $.each(data.cities, function(index, city) {
                                                    if (city.state_id === selectedState) {
                                                        cityDropdown.append($('<option>', { value: city.id, text: city.name }));
                                                    }
                                                });
                                            }).fail(function() {
                                                toastr.error('Error loading city data');
                                            });
                                        });

                                    break;

                            }


                            var editIcon = '';
                            var deleteIcon = '';

                            if ($('#has_permission').data('edit')) {
                                editIcon = $('<a>', {
                                    class: ' me-2 edit_data',
                                    href: '#',
                                    'data-bs-toggle': 'modal',
                                    'data-bs-target': '#edit_language',
                                    'data-id': input.id,
                                    'data-label': input.label,
                                    'data-name': input.name,
                                    'data-placeholder': input.placeholder,
                                    'data-required': input.is_required,
                                    'data-type': input.type,
                                    'data-direction': input.direction,
                                    'data-file_size': input.file_size,
                                    'data-options': JSON.stringify(input.options),
                                    'data-other-option': input.other_option
                                }).append($('<i>', { class: 'ti ti-pencil fs-20' }));
                            }

                            if ($('#has_permission').data('delete')) {
                                deleteIcon = $('<a>', {
                                    class: ' delete_data',
                                    href: '#',
                                    'data-bs-toggle': 'modal',
                                    'data-bs-target': '#delete-modal',
                                    'data-id': input.id
                                }).append($('<i>', { class: 'ti ti-trash-x fs-20 me-2' }));
                            }

                            var card = $('<div>', {
                                class: 'card p-3 mb-3 draggable-item',
                                'data-id': input.id,
                                'data-order': index + 1
                            });

                            var cardBody = $('<div>', {
                                class: 'row align-items-center'
                            });

                            cardBody.append($('<div class="col-md-5">').append(label));
                            cardBody.append($('<div class="col-md-5">').append(inputField));
                            cardBody.append($('<div class="col-md-2 text-end">').append(editIcon).append(deleteIcon));

                            card.append(cardBody);
                            $('#draggable-left').append(card);
                        });
                        $('#draggable-left').sortable({
                            placeholder: 'sortable-placeholder',
                            update: function(event, ui) {
                                let orderData = [];
                                $('#draggable-left .draggable-item').each(function(index, element) {
                                    orderData.push({
                                        id: $(element).data('id'),
                                        order: index + 1
                                    });
                                });
                                updateFormOrder(orderData);
                            }
                        }).disableSelection();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error("An unexpected error occurred.");
                    }
                }
            });
        }

        function updateFormOrder(orderData) {
            $.ajax({
                url: '/api/categories/form-inputs/order',
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: JSON.stringify({ order: orderData }),
                contentType: 'application/json',
                success: function(response) {
                    if (response.code === 200) {
                        toastr.success("Order updated successfully!");
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error("Failed to update the order.");
                }
            });
        }


        loadFormInputList();

        //delete
        $(document).on('click', '.delete_data', function(e) {
            e.preventDefault();

            var formInputId = $(this).data('id');
            $('#confirmDelete').data('id', formInputId);
        });


        $(document).on('click', '#confirmDelete', function(e) {
            e.preventDefault();

            var formInputId = $(this).data('id');

            $.ajax({
                url: '/api/categories/form-inputs/delete',
                type: 'POST',
                data: {
                    id: formInputId,
                },
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#delete-modal').modal('hide');
                        loadFormInputList();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('An error occurred while trying to delete the form input.');
                }
            });
        });


        //edit
        $(document).on('click', '.edit_data', function(e) {
            e.preventDefault();

            var languageId = $(this).data('id');
            var label = $(this).data('label');
            var name = $(this).data('name');
            var placeholder = $(this).data('placeholder');
            var direction = $(this).data('direction');
            var required = $(this).data('required');
            var type = $(this).data('type');
            var options = $(this).data('options');
            var filesize = $(this).data('file_size');
            var otherOption = $(this).data('other-option');

            if (typeof options === 'string') {
                options = JSON.parse(options);
            }

            $('#edit_language').data('id', languageId);
            $('#edit_language').data('type', type);
            $('#edit_form_label').val(label);
            $('#edit_form_description').val(name);
            $('#language_code').val(placeholder);
            $('#rtl_toggle').prop('checked', direction === 'RTL');
            $('#required_status_edit').prop('checked', required == 1);

            $('#edit_placeholder_div').hide();
            $('#edit_options-container').hide();
            $('#edit_other_option').hide();
            $('#edit_file_size').hide();

            if (type === 'text_field' || type === 'number_field' || type === 'textarea' || type === 'datepicker' || type === 'timepicker' ) {
                $('#edit_placeholder_div').show();
                $('#edit_form_placeholder').val(placeholder);
            } else if (type === 'file') {
                $('#edit_file_size').show();
                $('#edit_file_size_no').val(filesize);
            } else if (type === 'select' || type === 'radio' || type === 'checkbox') {
                $('#edit_options-container').show();
                $('#edit_options-container').empty();

                const addOptionBtn = $('<button type="button" id="add-option-btn" class="btn btn-primary mb-3">Add Option</button>');

                $('#edit_options-container').append(addOptionBtn);

                addOptionBtn.click(function() {
                    const optionCount = $('#edit_options-container .edit_option-item').length;

                    // Limit the options to 10
                    if (optionCount >= 10) {
                        toastr.error("You can add a maximum of 10 options.");
                        return; // Stop the function if the limit is reached
                    }
                    const optionDiv = $('<div class="mb-3 edit_option-item d-flex align-items-center"></div>');
                    const optionName = $('<input type="text" class="form-control me-2 edit_option-name" placeholder="Enter Option">');
                    const optionValue = $('<input type="text" class="form-control me-2 edit_option-value" placeholder="Enter Value">');
                    const deleteBtn = $('<button type="button" class="btn "><i class="ti ti-trash-x me-2  btn-danger"></i></button>');

                    deleteBtn.click(function() {
                        optionDiv.remove();
                    });

                    optionDiv.append(optionName, optionValue, deleteBtn);
                    $('#edit_options-container').append(optionDiv);
                });

                if (typeof options === 'string') {
                    options = JSON.parse(options);
                }
                if (typeof options === 'string') {
                    options = JSON.parse(options);
                }

                if (options && Array.isArray(options)) {
                    options.forEach(function(option) {
                        const optionDiv = $('<div id=" edit_option-item" class="mb-3 edit_option-item d-flex align-items-center"></div>');
                        const optionName = $('<input type="text" id="edit_option-name" class="form-control me-2 edit_option-name" value="' + option.value + '" placeholder="Enter Option">');
                        const optionValue = $('<input type="text" id="edit_option-value" class="form-control me-2 edit_option-value" value="' + option.key + '" placeholder="Enter Value">');
                        const deleteBtn = $('<button type="button" class="btn btn-danger">Delete</button>');

                        deleteBtn.click(function() {
                            optionDiv.remove();
                        });

                        optionDiv.append(optionName, optionValue, deleteBtn);
                        $('#edit_options-container').append(optionDiv);
                    });
                }


                if (type === 'radio' || type === 'checkbox' || type === 'select') {
                    $('#edit_other_option').show();
                    $('#rtl_toggle').prop('checked', otherOption === 1);
                }
            }



        });

        $('#editFormsInputForm').on('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);

            var formId = $('#edit_language').data('id');
            var inputType = $('#edit_language').data('type');
            formData.set('id', formId);
            formData.set('form_label', $('#edit_form_label').val());
            formData.set('form_placeholder', $('#edit_form_placeholder').val());

            formData.set('is_required', $('#required_status_edit').is(':checked') ? 1 : 0);

            const selectedFieldType =$('#edit_language').data('type');
            formData.set('input_type', inputType);

            if (selectedFieldType === "text_field" || selectedFieldType === "number_field" || selectedFieldType === "textarea" || selectedFieldType === "timepicker" || selectedFieldType === "datepicker") {
                formData.set('form_placeholder', $('#edit_form_placeholder').val());
            }

            if (selectedFieldType === "file") {
                formData.set('file_size', $('#edit_file_size_no').val());
            }

            if (selectedFieldType === "select" || selectedFieldType === "checkbox" || selectedFieldType === "radio") {
                const optionsArray = [];
                $('#edit_options-container .edit_option-item').each(function() {
                    const optionName = $(this).find('.edit_option-name').val();
                    const optionValue = $(this).find('.edit_option-value').val();
                    if (optionName && optionValue) {
                        optionsArray.push({ key: optionName, value: optionValue });
                    }
                });
                if (optionsArray.length > 0) {
                    formData.set('options', JSON.stringify(optionsArray));
                }
                formData.set('has_other_option', $('#rtl_toggle').is(':checked') ? 1 : 0);
            }

            $.ajax({
                url: '/api/categories/form-inputs',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done((response) => {
                if (response.code === '200') {
                    toastr.success(response.message);

                    $('#add_language, #edit_language').modal('hide');
                    $('#addFormsInputForm, #editFormsInputForm')[0].reset();
                    $('#required_status, #required_status_edit').prop('checked', false);

                    $('#edit_placeholder_div, #edit_options-container, #edit_file_size, #edit_other_option').hide();
                    $('#placeholder_div, #options-container, #file_size, #other_option').hide();

                    loadFormInputList();
                } else {
                    toastr.error(response.message);
                }
            })
            .fail((xhr) => {
                const errorMessage = xhr.responseJSON?.message || 'An error occurred. Please try again.';

                if (xhr.status === 422) {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");

                    $.each(xhr.responseJSON.errors, function (key, val) {
                        $("#" + key).addClass("is-invalid");
                        $("#" + key + "_error").text(val[0]);
                    });
                } else {
                    toastr.error(errorMessage, "bg-danger");
                }
            });

        });

    });

}