var datatableLang = {
    lengthMenu: $("#datatable_data").data("length_menu"),
    info: $("#datatable_data").data("info"),
    infoEmpty: $("#datatable_data").data("info_empty"),
    infoFiltered: $("#datatable_data").data("info_filter"),
    search: $("#datatable_data").data("search"),
    zeroRecords: $("#datatable_data").data("info_empty"),
    paginate: {
        first: $("#datatable_data").data("first"),
        last: $("#datatable_data").data("last"),
        next: $("#datatable_data").data("next"),
        previous: $("#datatable_data").data("prev"),
    },
};

let table;
let currentLangCode = $("body").data("lang");

$(document).ready(function () {
    initTable();

    $("#sitemapForm").validate({
        rules: {
            url: {
                required: true,
                pattern:
                    /^(https?:\/\/)?((localhost|(\d{1,3}\.){3}\d{1,3})|([a-zA-Z0-9.-]+\.[a-zA-Z]{2,}))(:\d+)?(\/.*)?$/,
                minlength: 3,
                maxlength: 200,
            },
        },
        messages: {
            url: {
                required: $("#url_error").data("required"),
                pattern: $("#url_error").data("valid_url"),
                minlength: $("#url_error").data("min"),
                maxlength: $("#url_error").data("min"),
            },
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
        onkeyup: function (element) {
            $(element).valid();
        },
        onchange: function (element) {
            $(element).valid();
        },
        submitHandler: function (form) {
            let _formData = new FormData(form);
            _formData.append("language_code", currentLangCode);
            $("#sitemapForm .submitbtn").html(
                '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
            );

            $.ajax({
                type: "POST",
                url: "/admin/setting/save-sitemap-url",
                data: _formData,
                processData: false,
                contentType: false,
                headers: {
                    Authorization: "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                beforeSend: function () {
                    $("#sitemapForm .submitbtn").attr("disabled", true).html(
                        `<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>`
                    );
                },
                complete: function () {
                    $("#sitemapForm .submitbtn")
                        .attr("disabled", false)
                        .html($("#sitemapForm .submitbtn").data("save_text"));
                },
                success: function (resp) {
                    if (resp.code === 200) {
                        toastr.success(resp.message);
                        $("#add_sitemap").modal("hide");
                    } else {
                        toastr.error(resp.message);
                    }
                    $("#sitemapForm")[0].reset();
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid is-valid");
                    initTable();
                },
                error: function (error) {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid is-valid");
                    if (error.responseJSON.code === 422) {
                        $.each(
                            error.responseJSON.message,
                            function (key, val) {
                                $("#" + key).addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                            }
                        );
                    } else {
                        toastr.error(error.responseJSON.message);
                    }
                },
            });
        },
    });
});

function initTable() {
    $(".loader-table").removeClass('d-none');
    $(".real-table").addClass("d-none");
    
    table = $("#sitemapTable").DataTable({
        processing: false,
        serverSide: true,
        destroy: true,
        ajax: {
            url: "/admin/setting/get-sitemap-urls",
            type: "POST",
            data: function (d) {
                d._token = $('meta[name="csrf-token"]').attr("content");
                d.keyword = $("#keyword").val();
            },
        },
        order: [["1", "desc"]],
        ordering: false,
        pageLength: 10,
        responsive: false,
        autoWidth: false,
        aoColumns: [
            {
                data: "url",
                render: function (data, type, row) {
                    return `<p class="text-gray-9 fw-semibold fs-14">${row.url}</p>`;
                },
            },
            {
                data: "sitemap_path",
                render: function (data, type, row) {
                    return `<p class="text-gray-9 fw-semibold fs-14"><a href="${row.filePath}" target="_blank" id="viewTemplate" data-id="${row.id}">${row.sitemap_path}</a></p>`;
                },
            },
            {
                data: null,
                render: function (data, type, row) {
                    return `${ $("#has_permission").data(
                                    "delete"
                                ) == 1
                                    ? `<a class="delete deleteSitemap" href="#" data-bs-toggle="modal" data-bs-target="#delete-modal" data-id="${
                                            row.id
                                        }">
                                    <i class="ti ti-trash m-3 fs-20"></i>
                                </a>`
                                    : ""
                            }`;
                },
            },
        ],
        language: datatableLang,
        initComplete: function () {
            $(".loader-table, .label-loader, .input-loader").addClass('d-none');
            $(".real-table, .real-label, .real-input").removeClass("d-none");
        }
    });
}

$(document).on("click", ".deleteSitemap", function () {
    let delete_id = $(this).data("id");
    $("#delete_id").val(delete_id);
});

$("#deleteForm").on("submit", function (e) {
    e.preventDefault();

    $.ajax({
        type: "POST",
        url: "/admin/setting/delete-sitemapurl",
        data: {
            'id': $("#delete_id").val(),
            'language_code': currentLangCode,
        },
        headers: {
            Authorization: "Bearer " + localStorage.getItem("admin_token"),
            Accept: "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            $("#delete-modal").modal("hide");
            initTable();
            toastr.success(response.message);
        },
        error: function (error) {
            toastr.error(error.responseJSON.message);
            $("#delete-modal").modal("hide");
        },
    });
});