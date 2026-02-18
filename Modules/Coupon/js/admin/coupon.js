(function ($) {
    "use strict";
if (pageValue === 'admin.coupon') {
    let langCode = $('body').data('lang');

    $(document).ready(function() {
        listCoupons();
    });

    $(document).on('click', '.valid_coupon', function() {
        if ($.fn.DataTable.isDataTable('#couponTable')) {
            $('#couponTable').DataTable().destroy();
        }
        $('#couponTable tbody').empty();
        var isValid = $(this).data('valid');
        $('#loader-table').removeClass('d-none');
        $(".label-loader, .input-loader").show();
        $('#couponTable, .real-label, .real-input').addClass('d-none');

        listCoupons(isValid);
    });

    function listCoupons(isValid = 1) {
        $.ajax({
            url: "/api/coupon/list",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
                user_id: localStorage.getItem('user_id'),
                is_valid: isValid
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {

                    let coupons = response.data;
                    let tableBody = "";

                    if (coupons.length === 0) {
                        $('#couponTable').DataTable().destroy();
                        tableBody += `
                            <tr>
                                <td colspan="6" class="text-center">${$('#couponTable').data('empty')}</td>
                            </tr>`;
                    } else {
                        coupons.forEach((coupon, index) => {
                            var couponSymbol = coupon.coupon_type == 'percentage' ? '%' : '$';

                            tableBody += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${coupon.code}</td>
                                    <td>${coupon.coupon_type}</td>
                                    <td>${coupon.coupon_value}</td>
                                    ${ $('#has_permission').data('edit') == 1 ?
                                    `<td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input coupon_status" ${coupon.status == 1 ? 'checked' : ''} type="checkbox" role="switch" id="switch-sm" data-id="${coupon.id}">
                                        </div>
                                    </td>` : ''
                                    }
                                    ${ $('#has_permission').data('visible') == 1 ?
                                    `<td>
                                        <li style="list-style: none;">
                                            ${ $('#has_permission').data('edit') == 1 ?
                                            `<a href="/admin/edit-coupon/${coupon.id}">
                                                <i class="ti ti-pencil fs-20"></i>
                                            </a>` : ''
                                            }
                                            ${ $('#has_permission').data('delete') == 1 ?
                                            `<a class="delete delete_coupon_btn" href="#" data-bs-toggle="modal" data-bs-target="#delete_coupon_modal" data-del-id="${coupon.id}">
                                                <i class="ti ti-trash m-3 fs-20"></i>
                                            </a>` : ''
                                            }
                                        </li>
                                    </td>` : ''
                                    }
                                </tr>
                            `;
                        });
                    }

                    $('#couponTable tbody').html(tableBody);

                    if ((coupons.length != 0) && !$.fn.DataTable.isDataTable('#couponTable')) {
                        $('#couponTable').DataTable({
                            ordering: true,
                            language: datatableLang
                        });
                    }
                    $('#loader-table').addClass('d-none');
                    $(".label-loader, .input-loader").hide();
                    $('#couponTable, .real-label, .real-input').removeClass('d-none');
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

    $(document).on('change', '.coupon_status', function () {
        let id = $(this).data('id');
        let status = $(this).is(':checked') ? 1 : 0;

        var data = {
            'id': id,
            'status': status,
            'language_code': langCode
        };

        $.ajax({
            url: '/api/coupon/change-status',
            type: 'POST',
            data: data,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json',
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    toastr.success(response.message);
                    listCoupons();
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            }
        });
    });

    $(document).on("click", ".delete_coupon_btn", function () {
        var id = $(this).data("del-id");
        $('.delete_coupon_confirm').data('id', id);
    });

    $(document).on('click', '.delete_coupon_confirm', function (e) {
        e.preventDefault();

        var delId = $('.delete_coupon_confirm').data('id');
        $.ajax({
            url: "/api/coupon/delete",
            type: 'POST',
            data: {
                id: delId,
                language_code: langCode
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    toastr.success(response.message);
                    $("#delete_coupon_modal").modal("hide");
                    listCoupons();
                }
            },
            error: function (xhr, status, error) {
                toastr.error(xhr.responseJSON.message);
            },
        });
    });

}

if (pageValue === 'admin.create-coupon' || pageValue === 'admin.edit-coupon') {

    $(document).ready(function () {
        $('.custom-select2').select2();
    });

    $(document).on('change', '#product_type', function() {
        var productType = $(this).val();

        $('#category_id').trigger('change').val('');
        $('#subcategory_id').trigger('change').val('');
        $('#product_id').trigger('change').val('');

        if (productType == 'category') {
            $('#category_field').removeClass('d-none');
            $('#subcategory_field').addClass('d-none');
            $('#product_field').addClass('d-none');
        } else if (productType == 'subcategory') {
            $('#category_field').addClass('d-none');
            $('#subcategory_field').removeClass('d-none');
            $('#product_field').addClass('d-none');
        } else if (productType == 'service') {
            $('#category_field').addClass('d-none');
            $('#subcategory_field').addClass('d-none');
            $('#product_field').removeClass('d-none');
        } else {
            $('#category_field').addClass('d-none');
            $('#subcategory_field').addClass('d-none');
            $('#product_field').addClass('d-none');
        }

    });

    $(document).on('change', '#coupon_type', function() {
        var couponType = $(this).val();

        if (couponType == 'percentage') {
            $('.coupon_type_symbol').text('%');
        } else {
            $('.coupon_type_symbol').text('$');
        }
    });

    $(document).on('change', '#quantity', function() {
        var quantity = $(this).val();

        if (quantity == 'unlimited') {
            $('#quantity_value_field').addClass('d-none');
        } else {
            $('#quantity_value_field').removeClass('d-none');
        }
    });

    $(document).ready(function () {
        $('#couponForm').validate({
            rules: {
                code: {
                    required: true,
                    remote: {
                        url: '/api/coupon/check-unique',
                        type: 'post',
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                            'Accept': 'application/json'
                        },
                        data: {
                            code: function() {
                                return $('#code').val();
                            },
                            id: function() {
                                return $('#id').val();
                            }
                        }
                    }
                },
                product_type: {
                    required: true,
                },
                'category_id[]': {
                    required: true,
                },
                'subcategory_id[]': {
                    required: true,
                },
                'product_id[]': {
                    required: true
                },
                coupon_type: {
                    required: true,
                },
                coupon_value: {
                    required: true,
                },
                quantity: {
                    required: true
                },
                quantity_value: {
                    required: true
                },
                start_date: {
                    required: true
                },
                end_date: {
                    required: true
                },
            },
            messages: {
                code: {
                    required: $('#code_error').data('required'),
                    remote: $('#code_error').data('exists')
                },
                product_type: {
                    required: $('#product_type_error').data('required'),
                },
                'category_id[]': {
                    required: $('#category_id_error').data('required'),
                },
                'subcategory_id[]': {
                    required: $('#subcategory_id_error').data('required'),
                },
                'product_id[]': {
                    required: $('#product_id_error').data('required')
                },
                coupon_type: {
                    required: $('#coupon_type_error').data('required'),
                },
                coupon_value: {
                    required: $('#coupon_value_error').data('required'),
                },
                quantity: {
                    required: $('#quantity_error').data('required')
                },
                quantity_value: {
                    required: $('#quantity_value_error').data('required')
                },
                start_date: {
                    required: $('#start_date_error').data('required')
                },
                end_date: {
                    required: $('#end_date_error').data('required')
                },
            },
            errorPlacement: function (error, element) {
                if (element.hasClass("select2-hidden-accessible")) {
                    var errorId = element.attr("id") + "_error";
                    $("#" + errorId).text(error.text());
                } else {
                    var errorId = element.attr("id") + "_error";
                    $("#" + errorId).text(error.text());
                }
            },
            highlight: function (element) {
                if ($(element).hasClass("select2-hidden-accessible")) {
                    $(element).next(".select2-container").addClass("is-invalid").removeClass('is-valid');
                }
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element) {
                if ($(element).hasClass("select2-hidden-accessible")) {
                    $(element).next(".select2-container").removeClass("is-invalid").addClass('is-valid');
                }
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
                $.ajax({
                    url: "/api/coupon/save",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    cache: false,
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function () {
                        $("#save_coupon_btn").attr("disabled", true).html(
                            '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                        );
                    },
                    success: function (response) {
                        $(".error-text").text("");
                        $("#save_coupon_btn").removeAttr("disabled").html($('#save_coupon_btn').data('save'));
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".select2-container").removeClass('is-invalid is-valid');
                        if (response.code === 200) {
                            toastr.success(response.message);
                            window.location.href = '/admin/coupons';
                        }

                    },
                    error: function (error) {
                        $(".error-text").text("");
                        $("#save_coupon_btn").removeAttr("disabled").html($('#save_coupon_btn').data('save'));
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".select2-container").removeClass('is-invalid is-valid');
                        if (error.responseJSON.code === 422) {
                            $.each(error.responseJSON.errors, function(key, val) {
                                $("#" + key).addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                            });
                        } else {
                            toastr.error(error.responseJSON.message);
                        }
                    }
                });

            }
        });
    });

    $('#product_type').on('change', function () {
        $(this).valid();
    });
    $('#category_id').on('change', function () {
        $(this).valid();
    });
    $('#subcategory_id').on('change', function () {
        $(this).valid();
    });
    $('#product_id').on('change', function () {
        $(this).valid();
    });
    $('#coupon_type').on('change', function () {
        $(this).valid();
    });
    $('#quantity').on('change', function () {
        $(this).valid();
    });

    $("#coupon_value").on("input", function () {
        $(this).val($(this).val().replace(/[^0-9]/g, ""));
    });
    $("#quantity_value").on("input", function () {
        $(this).val($(this).val().replace(/[^0-9]/g, ""));
    });

}

})(jQuery);
