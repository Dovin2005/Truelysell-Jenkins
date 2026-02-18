$(document).ready(function () {
toastr.options = {
    closeButton: true,
    debug: false,
    newestOnTop: false,
    progressBar: true,
    positionClass: "toast-top-right",
    preventDuplicates: true,
    onclick: null,
    showDuration: "30000",
    hideDuration: "10000",
    timeOut: "50000",
    extendedTimeOut: "10000",
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut",
};
    fetchColorSettings();

    let defaultPrimaryColor = "#FD2692";
    let defaultSecondaryColor = "#0A67F2";
    let defaultPrimaryHoverColor = "#db0077";
    let defaultSecondaryHoverColor = "#20226f";
    
    $("#colorSettingForm").validate({
        rules: {
            primary_color: { required: true },
            secondary_color: { required: true },
            primary_hover_color: { required: true },
            secondary_hover_color: { required: true },
        },
        messages: {
            primary_color: { required: 'Primary color is required.' },
            secondary_color: { required: 'Secondary color is required.' },
            primary_hover_color: { required: 'Primary hover color is required.' },
            secondary_hover_color: { required: 'Secondary hover color is required.' },
        },
        errorPlacement: function (error, element) {
            const errorId = element.attr("id") + "_error";
            $("#" + errorId).text(error.text());
        },
        highlight: function (element) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element) {
            $(element).removeClass("is-invalid").addClass("is-valid");
            const errorId = element.id + "_error";
            $("#" + errorId).text("");
        },
        submitHandler: function (form) {
            const colorData = new FormData(form);

            $.ajax({
                type: "POST",
                url: "/admin/setting/colors/update",
                data: colorData,
                processData: false,
                contentType: false,
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function () {
                    $('.saveCustomSettings').attr('disabled', true).html(`
                        <span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span> Saving...
                    `);
                },
                complete: function () {
                    $('.saveCustomSettings').attr('disabled', false).html('Save Changes');
                },
                success: function (resp) {
                    if (resp.code === 200) {
                        toastr.success(resp.message);
                        fetchColorSettings();

                        // Optional: Apply dynamically without reload
                        document.documentElement.style.setProperty('--primary', $("#primary_color").val());
                        document.documentElement.style.setProperty('--secondary', $("#secondary_color").val());
                        document.documentElement.style.setProperty('--primary-hover', $("#primary_hover_color").val());
                        document.documentElement.style.setProperty('--secondary-hover', $("#secondary_hover_color").val());
                    }
                },
                error: function (xhr) {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid is-valid");

                    if (xhr.responseJSON && xhr.responseJSON.code === 422) {
                        $.each(xhr.responseJSON.errors, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                         toastr.error(xhr.responseJSON?.message || 'An error occurred while saving. Please try again.');
                    }
                }
            });
        }
    });

    function fetchColorSettings() {
        $.ajax({
            url: '/api/admin/index-invoice-setting',
            type: 'POST',
            data: { 'group_id': 16 },
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function (resp) {
                if (resp.code === 200) {
                    const settingsArray = resp.data.settings;

                    // Convert array to key-value object
                    const data = {};
                    settingsArray.forEach(item => {
                        data[item.key] = item.value;
                    });

                    $("#primary_color").val(data.primary_color ?? defaultPrimaryColor);
                    $("#secondary_color").val(data.secondary_color ?? defaultSecondaryColor);
                    $("#primary_hover_color").val(data.primary_hover_color ?? defaultPrimaryHoverColor);
                    $("#secondary_hover_color").val(data.secondary_hover_color ?? defaultSecondaryHoverColor);

                    // Optional: Apply dynamically
                    document.documentElement.style.setProperty('--primary', data.primary_color ?? defaultPrimaryColor);
                    document.documentElement.style.setProperty('--secondary', data.secondary_color ?? defaultSecondaryColor);
                    document.documentElement.style.setProperty('--primary-hover', data.primary_hover_color ?? defaultPrimaryHoverColor);
                    document.documentElement.style.setProperty('--secondary-hover', data.secondary_hover_color ?? defaultSecondaryHoverColor);
                }
            },
            error: function (error) {
                const errorMessage = error.responseJSON?.message || 'An error occurred while fetching settings';
                toastr.error(errorMessage);
            },
            complete: function () {
                $(".label-loader, .input-loader").hide();
                $('.real-label, .real-input').removeClass('d-none');
            }
        });
    }
});
