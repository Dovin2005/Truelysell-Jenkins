var pageValue = $('body').data('page');

if (pageValue === "admin.advertisement") {
    $(document).ready(function () {
        $("#addAdvertisementForm").validate({
            rules: {
                ad_name: {
                    required: true,
                    maxlength: 100,
                },
                body: {
                    required: true,
                },
                ad_image: {
                    required: true,
                    extension: "jpg|jpeg|png|svg",
                },
                ad_url: {
                    required: true,
                    url: true, 
                },
                ad_alt: {
                    required: true,
                },
                ad_position: {
                    required: true,
                },
                ad_selector: {
                    required: true,
                    maxlength: 300,
                },
                ad_custom: {
                    maxlength: 300,
                },
            },
            messages: {
                ad_name: {
                    required: "Ad name is required",
                    maxlength: "Maximum 100 characters allowed",
                },
                body: {
                    required: "Ad type is required",
                },
                ad_image: {
                    required: "Image field is required",
                    extension: "Only image files (jpg, jpeg, png, gif) are allowed."
                },
                ad_url: {
                    required: "Image URL field is required",
                    url: "Please enter a valid URL",
                },
                ad_alt: {
                    required: "Image ALT field is required",
                },
                ad_position: {
                    required: "Please select an ad position",
                },
                ad_selector: {
                    required: "CSS selector is required",
                    maxlength: "Maximum 300 characters allowed",
                },
                ad_custom: {
                    maxlength: "Maximum 300 characters allowed",
                },
            },
            errorElement: "span",
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback");
                element.closest(".mb-3").append(error);
            },
            highlight: function (element) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element) {
                $(element).removeClass("is-invalid").addClass("is-valid");
            },
            submitHandler: function (form, event) {
                event.preventDefault(); 
    
                var formData = new FormData(form);
                formData.append("status", $("#status").is(":checked") ? 1 : 0);
    
                $.ajax({
                    url: "/admin/advertisement/create",
                    method: "POST",
                    data: formData,
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    cache: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                    beforeSend: function () {
                        $(".add_advertisement_btn").attr("disabled", true).html(
                            '<div class="spinner-border text-light" role="status"></div>'
                        );
                    },
                })
                .done((response) => {
                    $(".form-control").removeClass("is-invalid");
                    $(".add_advertisement_btn").removeAttr("disabled").html($('.add_advertisement_btn').data('save_text'));
                    
                    if (response.code === 200) {
                        toastr.success(response.message);
                        fetchData();
                        $("#add_advertisement").modal("hide");
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail((error) => {
                    $(".form-control").removeClass("is-invalid");
                    $(".add_advertisement_btn").removeAttr("disabled").html($('.add_advertisement_btn').data('save_text'));
    
                    if (error.status === 422) {
                        $.each(error.responseJSON.errors, function (key, messages) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(messages[0]); // Show first error message
                        });
                    } else {
                        toastr.error(error.responseJSON.message, "bg-danger");
                    }
                });
            },
        });
    });

    $(document).ready(function () {
        $("#editAdvertisementForm").validate({
            rules: {
                edit_ad_name: {
                    required: true,
                    maxlength: 100,
                },
                edit_body: {
                    required: true,
                },
                edit_ad_image: {
                    required: false,
                    extension: "jpg|jpeg|png|svg",
                },
                edit_ad_url: {
                    required: true,
                    url: true, 
                },
                edit_ad_alt: {
                    required: true,
                },
                edit_ad_position: {
                    required: true,
                },
                edit_ad_selector: {
                    required: true,
                    maxlength: 300,
                },
                edit_ad_custom: {
                    maxlength: 300,
                },
            },
            messages: {
                edit_ad_name: {
                    required: "Ad name is required",
                    maxlength: "Maximum 100 characters allowed",
                },
                edit_body: {
                    required: "Ad type is required",
                },
                edit_ad_image: {
                    required: "Image field is required",
                    extension: "Only image files (jpg, jpeg, png, gif) are allowed."
                },
                edit_ad_url: {
                    required: "Image URL field is required",
                    url: "Please enter a valid URL",
                },
                edit_ad_alt: {
                    required: "Image ALT field is required",
                },
                edit_ad_position: {
                    required: "Please select an ad position",
                },
                edit_ad_selector: {
                    required: "CSS selector is required",
                    maxlength: "Maximum 300 characters allowed",
                },
                edit_ad_custom: {
                    maxlength: "Maximum 300 characters allowed",
                },
            },
            errorElement: "span",
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback");
                element.closest(".mb-3").append(error);
            },
            highlight: function (element) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element) {
                $(element).removeClass("is-invalid").addClass("is-valid");
            },
            submitHandler: function (form, event) {
                event.preventDefault(); 
    
                var formData = new FormData(form);
                formData.append("edit_status", $("#edit_status").is(":checked") ? 1 : 0);
    
                $.ajax({
                    url: "/admin/advertisement/edit",
                    method: "POST",
                    data: formData,
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    cache: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                    beforeSend: function () {
                        $(".edit_advertisement_btn").attr("disabled", true).html(
                            '<div class="spinner-border text-light" role="status"></div>'
                        );
                    },
                })
                .done((response) => {
                    $(".form-control").removeClass("is-invalid");
                    $(".edit_advertisement_btn").removeAttr("disabled").html($('.edit_advertisement_btn').data('update_text'));
                    
                    if (response.code === 200) {
                        toastr.success(response.message);
                        fetchData();
                        $("#edit_advertisement").modal("hide");
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail((error) => {
                    $(".form-control").removeClass("is-invalid");
                    $(".edit_advertisement_btn").removeAttr("disabled").html($('.edit_advertisement_btn').data('update_text'));
    
                    if (error.status === 422) {
                        $.each(error.responseJSON.errors, function (key, messages) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(messages[0]);
                        });
                    } else {
                        toastr.error(error.responseJSON.message, "bg-danger");
                    }
                });
            },
        });
    });

    $(document).ready(function () {
        function toggleAdFields() {
            if ($('#html_ad').is(':checked')) {
                $('#html_ad_container').show();
                $('#image_ad_container').hide();
            } else {
                $('#html_ad_container').hide();
                $('#image_ad_container').show();
            }
        }

        toggleAdFields();

        $('input[name="ad_type"]').on('change', function () {
            toggleAdFields();
        });
    });

    $(document).ready(function () {
        function toggleAdFields() {
            if ($('#edit_html_ad').is(':checked')) {
                $('#edit_html_ad_container').show();
                $('#edit_image_ad_container').hide();
            } else {
                $('#edit_html_ad_container').hide();
                $('#edit_image_ad_container').show();
            }
        }

        // Initial state
        toggleAdFields();

        // Event listeners for radio button change
        $('input[name="edit_ad_type"]').on('change', function () {
            toggleAdFields();
        });
    });
    
    $(document).ready(function () {
        fetchData();
    });

    function fetchData() {
        $.ajax({
            url: "/admin/advertisement/index",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
                sort_by: "id",
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $('#loader-table').hide();
                $(".label-loader, .input-loader").hide(); // Hide all skeleton loaders
                $(".real-label, .real-input").removeClass("d-none");
                if (response.code === 200) {
                    populateTable(response.data, response.meta);
                }
            },
            error: function (error) {
                $('#loader-table').hide();
                $(".label-loader, .input-loader").hide(); // Hide all skeleton loaders
                $(".real-label, .real-input").removeClass("d-none");
                if (error.status === 422) {
                    var errors = error.responseJSON.errors;
                    if (errors) {
                        $.each(errors, function (key, messages) {
                            toastr.error(messages[0]);
                        });
                    } else {
                        toastr.error("An error occurred while fetching.");
                    }
                } else {
                    toastr.error("An error occurred while fetching.");
                }
            },
        });
    }

    function populateTable(Data) {
        let tableBody = "";

        if (Data.length > 0) {
            Data.forEach((Data, index) => {
                tableBody += `
                    <tr>
                            <td>${index + 1}</td>
                            <td>${Data.name}</td>
                            <td>${Data.slug}</td>
                            <td>${Data.clicks}</td>
                            <td>
                                <span class="badge ${
                                    Data.status == "1"
                                        ? "badge-soft-success"
                                        : "badge-soft-danger"
                                } d-inline-flex align-items-center">
                                    <i class="ti ti-circle-filled fs-5 me-1"></i>${
                                        Data.status == "1"
                                            ? "Active"
                                            : "Inactive"
                                    }
                                </span>
                            </td>
                            ${ $('#has_permission').data('visible') == 1 ?
                            `<td>
                                <li style="list-style: none;">
                                    ${ $('#has_permission').data('edit') == 1 ?
                                    `<a class="edit_Data_data"
                                    href="#"
                                    data-bs-toggle="modal"
                                    data-bs-target="#edit_advertisement"
                                    data-id="${Data.id}"
                                    data-position="${Data.position}"
                                    data-selector="${Data.selector}"
                                    data-style="${Data.style}"
                                    data-name="${Data.name}"
                                    data-adType="${Data.adType}"
                                    data-image="${Data.image}"
                                    data-imageUrl="${Data.imageUrl}"
                                    data-imageAlt="${Data.imageAlt}"
                                    data-status="${Data.status}"
                                    data-body_data="${encodeURIComponent(Data.body_data)}"
                                    >
                                    <i class="ti ti-pencil fs-20"></i>
                                    </a>` : ''
                                    }
                                    ${ $('#has_permission').data('delete') == 1 ?
                                    `<a class="delete" href="#" data-bs-toggle="modal" data-bs-target="#delete-modal" data-id="${Data.id}">
                                        <i class="ti ti-trash m-3 fs-20"></i>
                                    </a>` : ''
                                    }
                                </li>
                            </td>` : ''
                            }
                        </tr>
                    `;
            });
        } else {
            $('#data_datatable').DataTable().destroy();
            tableBody = `
                    <tr>
                        <td colspan="6" class="text-center">No data found</td>
                    </tr>
                `;
        }

        $("#data_datatable tbody").html(tableBody);


        if ((Data.length != 0) && !$.fn.dataTable.isDataTable("#data_datatable")) {
            $("#data_datatable").DataTable({
                ordering: true,
                language: datatableLang
            });
        }
    }

    $(document).on("click", ".edit_Data_data", function (e) {
        e.preventDefault();
    
        var ID = $(this).data("id");
        var name = $(this).data("name");
        var bodyData = decodeURIComponent($(this).data("body_data"));
        var image = $(this).data("image");
        var imageUrl = $(this).data("imageurl");
        var imageAlt = $(this).data("imagealt");
        var adType = $(this).data("adtype");
        var status = $(this).data("status");
        var position = $(this).data("position");
        var selector = $(this).data("selector");
        var style = $(this).data("style");    

        $("#edit_id").val(ID);
        $("#edit_ad_name").val(name);
        $("#edit_body").val(bodyData ?? "");
        $("#edit_ad_url").val(imageUrl);
        $("#edit_image").attr("src", image); 
        $("#edit_ad_alt").val(imageAlt);
        $("#edit_ad_position").val(position);
        $("#edit_ad_selector").val(selector);
        $("#edit_ad_custom").val(style);
        $("#edit_status").prop("checked", status == 1);
    
        if (adType === "HTML") {
            $("#edit_html_ad").prop("checked", true);
            $('input[name="edit_ad_type"]').trigger("change");
        } else if (adType === "IMAGE") {
            $("#edit_image_ad").prop("checked", true);
            $('input[name="edit_ad_type"]').trigger("change");
        }

        if (image) {
            $("#image-preview").html(`<img src="${image}" alt="${imageAlt}" style="max-width: 100%; height: 10rem; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">`);
        } else {
            $("#image-preview").html("<p>No image available</p>");
        }
    
    });    
    
    $(document).on("change", "#edit_ad_image", function (e) {
        var file = e.target.files[0];

        $("#image-preview").html("");

        if (file) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $("#image-preview").html(`<img src="${e.target.result}" alt="Selected Image" 
                    style="max-width: 100%; height: 10rem; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">`);
            };

            reader.readAsDataURL(file);
        }
    });

    $(document).on("change", "#ad_image", function (e) {
        var file = e.target.files[0];

        $("#add-image-preview").html("");

        if (file) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $("#add-image-preview").html(`<img src="${e.target.result}" alt="Selected Image" 
                    style="max-width: 100%; height: 10rem; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">`);
            };

            reader.readAsDataURL(file);
        }
    });

    $(document).on("click", '.delete[data-bs-toggle="modal"]', function (e) {
        e.preventDefault();
        var AddId = $(this).data("id");
        $("#confirmDelete").data("id", AddId);
    });

    $(document).on("click", "#confirmDelete", function (e) {
        e.preventDefault();

        var AddId = $(this).data("id");
        $.ajax({
            url: "/admin/advertisement/delete",
            type: 'POST',
            data: {
                id: AddId,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $("#confirmDelete").attr("disabled", true);
                $("#confirmDelete").html(
                    '<div class="spinner-border text-light" role="status"></div>'
                );
            },

            success: function (response) {
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid");
                $("#confirmDelete").removeAttr("disabled");
                $("#confirmDelete").html("Delete");
                if (response.success) {
                    toastr.success(response.message);
                    fetchData();
                    $("#delete-modal").modal("hide");
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid");
                $("#confirmDelete").removeAttr("disabled");
                $("#confirmDelete").html("Delete");
                toastr.error("An error occurred while trying to delete.");
            },
        });
    });


    $(document).ready(function () {
        $("#add_advertisement").on("hidden.bs.modal", function () {
            $("#addAdvertisementForm")[0].reset();
    
            $("#add-image-preview").html("");
            $(".form-control").removeClass("is-invalid").removeClass("is-valid");
            $(".invalid-feedback").text("");
        });
        $("#edit_advertisement").on("hidden.bs.modal", function () {
            $("#editAdvertisementForm")[0].reset();
    
            $(".form-control").removeClass("is-invalid").removeClass("is-valid");
            $(".invalid-feedback").text("");
        });
    });

}