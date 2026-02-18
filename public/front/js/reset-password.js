$(document).ready(function () {
    $("#forgotPassword").validate({
        rules: {
            new_password: {
                required: true,
                minlength: 8,
                remote: {
                    url: "/api/forgot/check-password",
                    type: "post",
                    headers: {
                        Accept: "application/json",
                    },
                    data: {
                        current_password: function () {
                            return $("#new_password").val();
                        },
                        email: function () {
                            return $("#email_id").val();
                        },
                    },
                },
            },
            confirm_password: {
                required: true,
                equalTo: "#new_password",
            },
        },
        messages: {
            new_password: {
                required: "The password field is required.",
                minlength: "The password must be at least 8 characters.",
                remote: "New password cannot be the same as the current password.",
            },
            confirm_password: {
                required: "The confirm password field is required.",
                equalTo: "The confirm password must match the new password.",
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
        submitHandler: function (form) {
            var formData = new FormData(form);

            $.ajax({
                url: "/user-update-password",
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".forgot_btn").attr("disabled", true);
                    $(".forgot_btn").html(
                        "<div class='spinner-border text-light' role='status'></div>"
                    );
                },
            })
            .done((response) => {
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid");
                $(".forgot_btn").removeAttr("disabled");
                if (response.code === 200) {
                    toastr.success(response.message);
                    setTimeout(() => {
                        window.location.href = "/";
                    }, 300);
                }
            })
            .fail((error) => {
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid");
                $(".forgot_btn").removeAttr("disabled");

                if (error.status == 422) {
                    $.each(error.responseJSON.errors, function (key, val) {
                        $("#" + key).addClass("is-invalid");
                        $("#" + key + "_error").text(val[0]);
                    });
                    $("#error_login_message").text(
                        error.responseJSON.error ||
                            "An unexpected error occurred."
                    );
                } else {
                    $("#error_login_message").text(
                        error.responseJSON.error ||
                            "An unexpected error occurred."
                    );
                }
            });
        },
    });
});