/* global $, document, setTimeout, FormData, window, bootstrap, location, toastr, alert, clearInterval, setInterval, localStorage */

toastr.options = {
    debug: false,
    newestOnTop: false,
    progressBar: true,
    positionClass: "toast-top-right",
    preventDuplicates: true,
    onclick: null,
    showDuration: "30000",
    hideDuration: "10000",
    timeOut: "4000",
    extendedTimeOut: "10000",
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut",
};

document.addEventListener("DOMContentLoaded", function () {
    const loginModal = document.getElementById("login-modal");
    const forgotModal = document.getElementById("forgot-modal");
    const otpEmailModal = new bootstrap.Modal(
        document.getElementById("otp-email-modal"),
        { keyboard: false }
    );
    const otpSmsModal = new bootstrap.Modal(
        document.getElementById("otp-phone-modal"),
        { keyboard: false }
    );
    const otpTimerDisplay = document.getElementById("otp-timer");
    const otpSmsTimerDisplay = document.getElementById("otp-sms-timer");
    const otpModal = document.getElementById("otp-email-modal");
    const otpsmsModal = document.getElementById("otp-phone-modal");

    otpModal.addEventListener("hidden.bs.modal", function () {
        const errorMessage = document.getElementById("error_message");
        if (errorMessage) {
            errorMessage.textContent = "";
        }
    });
    otpsmsModal.addEventListener("hidden.bs.modal", function () {
        const errorMessage = document.getElementById("error_sms_message");
        if (errorMessage) {
            errorMessage.textContent = "";
        }
    });

    $(document).ready(function () {
        $(".copy-login-details").on("click", function (event) {
            event.preventDefault(); // Prevent default anchor behavior

            const email = $(this).data("email");
            const password = $(this).data("password");

            $("#userlogins input[name='email']").val(email);
            $("#userlogins input[name='password']").val(password);
        });
    });

    document
        .getElementById("loginTogglePassword")
        .addEventListener("click", function () {
            const passwordField = document.getElementById("login_password");
            const toggleIcon = document.getElementById("logintoggleIcon");

            const isPassword = passwordField.type === "password";

            passwordField.type = isPassword ? "text" : "password";

            toggleIcon.classList.toggle("fa-eye-slash");
            toggleIcon.classList.toggle("fa-eye");
        });

    $("#forgot_login").validate({
        rules: {
            forgot_email: {
                required: true,
                email: true,
            },
        },
        messages: {
            forgot_email: {
                required: "The email field is required.",
                email: "Please enter a valid email address.",
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
            form.submit();
        },
    });

    var langCode = $("body").data("lang");

    let currentLang = langCode;

    const validationMessages = {
        en: {
            email: {
                required: "The email field is required.",
                email: "Please enter a valid email address.",
                pattern: "Please enter a valid email address.",
            },
            password: {
                required: "The password field is required.",
                minlength: "The password must be at least 8 characters.",
            },
        },
        ar: {
            email: {
                required: "حقل البريد الإلكتروني مطلوب.",
                email: "يرجى إدخال عنوان بريد إلكتروني صالح.",
                pattern: "يرجى إدخال عنوان بريد إلكتروني صالح.",
            },
            password: {
                required: "حقل كلمة المرور مطلوب.",
                minlength: "يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.",
            },
        },
    };

    $("#userlogins").validate({
        rules: {
            email: {
                required: true,
                email: true, // Ensures the email format is valid
            },
            forgot_email: {
                required: true,
                email: true, // Ensures the email format is valid
            },
            password: {
                required: true,
                minlength: 8,
            },
        },
        messages: validationMessages[currentLang],
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
            // Form submission via AJAX
            var formData = new FormData(form);

            $.ajax({
                url: "/userlogins",
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".login_btn").attr("disabled", true);
                    $(".login_btn").html(
                        "<div class='spinner-border text-light' role='status'></div>"
                    );
                },
            })
                .done((response) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".login_btn").removeAttr("disabled");
                    $(".login_btn").html("Sign In");
                    if (response.code === 200) {
                        $("#login-modal").modal("hide");

                        if (response.provider_verified_status == 0) {
                            $("#provider_not_verified_modal").modal("show");
                        } else {
                            $("#success_modal").modal("show");
    
                            setTimeout(function () {
                                window.location.href = response.redirect_url;
                            }, 500);
                        }
                    }
                })
                .fail((error) => {
                    $(".error-text").text(""); // Clear all previous error messages
                    $(".form-control").removeClass("is-invalid"); // Remove invalid classes
                    $(".login_btn").removeAttr("disabled"); // Re-enable the button
                    $(".login_btn").html("Sign In");

                    if (error.status === 422) {
                        if (error.responseJSON.errors) {
                            $.each(
                                error.responseJSON.errors,
                                function (key, val) {
                                    $("#" + key).addClass("is-invalid");
                                    $("#" + key + "_error").text(val[0]);
                                }
                            );

                            // Also show the general message (optional)
                            $("#error_login_message").text(
                                error.responseJSON.message ||
                                    "Validation error occurred."
                            );
                        } else {
                            $("#error_login_message").text(
                                error.responseJSON.message ||
                                    "Validation failed."
                            );
                        }
                    } else {
                        $("#error_login_message").text(
                            error.responseJSON.message ||
                                "An unexpected error occurred."
                        );
                    }
                });
        },
    });

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
                        $("#reset-password").modal("hide");
                        $("#login-modal").modal("show");
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

    $("#forgot-modal").on("hide.bs.modal", function () {
        resetLoginForm();
    });

    // Reset login form function
    function resetLoginForm() {
        // Reset form inputs
        $("#userlogins")[0].reset();

        // Clear validation classes and messages
        $("#userlogins .form-control").removeClass("is-invalid is-valid");
        $("#userlogins .invalid-feedback").remove();
        $("#error_login_message").text("");
    }

    function resendOtp(type) {
        const email = $("[name='email']").val();
        const forgot_email = $("[name='forgot_email']").val();

        let username = "";

        if (email) {
            username = email;
        } else if (forgot_email) {
            username = forgot_email;
        }

        if (!username || !isValidEmail(username)) {
            toastr.error("Please provide a valid email address.");
            return;
        }

        const url = "/otp-settings"; // Replace with your actual endpoint
        const data = { email: username, type: type };

        showLoader();

        $.ajax({
            url: url,
            type: "POST",
            data: data,
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
            },
            success: function (data) {
                const otpExpireTime = parseInt(
                    data.otp_expire_time.split(" ")[0]
                );
                const phoneNumber = data.phone_number;

                showLoader();

                if (type === "email") {
                    hideLoader();
                    otpEmailModal.show();
                    startTimer(otpExpireTime);
                    // const emailData = {
                    //     subject: data.email_subject,
                    //     content: data.email_content,
                    // };
                    // sendEmail(username, emailData, "email", userName, otp)
                    //     .then(() => {
                            
                    //     })
                    //     .catch(() => {
                    //         hideLoader();
                    //         alert(
                    //             "Failed to send email OTP. Please try again."
                    //         );
                    //     });
                } else if (type === "sms") {
                    hideLoader();
                    otpSmsModal.show();
                    startSmsTimer(otpExpireTime);
                    // const emailData = {
                    //     subject: data.email_subject,
                    //     content: data.email_content,
                    // };
                    // sendSms(phoneNumber, emailData, "sms", userName, otp)
                    //     .then(() => {
                            
                    //     })
                    //     .catch(() => {
                    //         hideLoader();
                    //         $("#otp_error").modal("show");
                    //     });
                } else {
                    hideLoader();
                }
            },
            error: function (xhr) {
                hideLoader();
                const errorMessage =
                    xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : "Failed to resend OTP. Please try again.";
                toastr.error(errorMessage);
            },
        });
    }

    // Attach event listeners to the "Resend OTP" links
    $(".resendEmailOtp").on("click", function () {
        resendOtp("email");
    });

    $(".resendSMSOtp").on("click", function () {
        resendOtp("sms");
    });

    let emailTimerInterval, smsTimerInterval; // Separate interval variables
    let emailTimerTime, smsTimerTime; // Separate time variables

    function startTimer(expireTime) {
        clearInterval(emailTimerInterval); // Clear any existing timer
        emailTimerTime = expireTime * 60; // Convert minutes to seconds

        emailTimerInterval = setInterval(() => {
            let minutes = Math.floor(emailTimerTime / 60);
            let seconds = emailTimerTime % 60;
            otpTimerDisplay.textContent = `${String(minutes).padStart(
                2,
                "0"
            )}:${String(seconds).padStart(2, "0")}`;
            emailTimerTime--;

            if (emailTimerTime < 0) {
                clearInterval(emailTimerInterval);
                otpTimerDisplay.textContent = "00:00"; // Timer finished
            }
        }, 1000);
    }

    function startSmsTimer(expireSmsTime) {
        clearInterval(smsTimerInterval); // Clear any existing timer
        smsTimerTime = expireSmsTime * 60; // Convert minutes to seconds

        smsTimerInterval = setInterval(() => {
            let minutes = Math.floor(smsTimerTime / 60);
            let seconds = smsTimerTime % 60;
            otpSmsTimerDisplay.textContent = `${String(minutes).padStart(
                2,
                "0"
            )}:${String(seconds).padStart(2, "0")}`;
            smsTimerTime--;

            if (smsTimerTime < 0) {
                clearInterval(smsTimerInterval);
                otpSmsTimerDisplay.textContent = "00:00";
            }
        }, 1000);
    }

    $("#otp_signin").on("click", function () {
        const username = $("[name='email']").val();

        if (!username || !isValidEmail(username)) {
            toastr.error("Please provide a valid email address.");
            return;
        }

        showLoader();
        $.ajax({
            url: "/otp-settings",
            type: "POST",
            data: { email: username },
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
            },
            success: function (data) {
                const loginModalInstance =
                    bootstrap.Modal.getInstance(loginModal);
                if (loginModalInstance) {
                    loginModalInstance.hide();
                }
                if (data.provider_verified_status == 0) {
                    $("#provider_not_verified_modal").modal("show");
                    hideLoader();
                } else {

                const otpExpireTime = parseInt(
                    data.otp_expire_time.split(" ")[0]
                );
                const phoneNumber = $("#phone_number").val();

                showLoader();

                const otpDigitLimit = parseInt(data.otp_digit_limit);

                const inputContainer = $(".inputcontainer");
                inputContainer.empty();

                let inputsHtml = "<div class='d-flex align-items-center mb-3'>";

                for (let i = 1; i <= otpDigitLimit; i++) {
                    const nextId = `digit-${i + 1}`;
                    const prevId = `digit-${i - 1}`;
                    inputsHtml += `
                      <input type="text"
                      class="rounded w-100 py-sm-3 py-2 text-center fs-26 fw-bold me-3 digit-${i}" 
                      id="digit-${i}" 
                      name="digit-${i}" 
                      data-next="${nextId}" 
                      data-previous="${prevId}" 
                      maxlength="1">`;
                }

                inputsHtml += "</div>";
                inputContainer.append(inputsHtml);

                $(".inputcontainer").on("input", "input", function () {
                    const maxLength = $(this).attr("maxlength") || 1;
                    if (this.value.length >= maxLength) {
                        const next = $(this).data("next");
                        if (next) {
                            $("#" + next).focus();
                        }
                    }
                });

                $(".inputcontainer").on("keydown", "input", function (e) {
                    if (e.key === "Backspace" && this.value === "") {
                        const prev = $(this).data("previous");
                        if (prev) {
                            $("#" + prev).focus();
                        }
                    }
                });

                $(".inputcontainer").on("click", "input", function () {
                    $(this).select();
                });

                const inputSMSContainer = $(".inputSMSContainer");
                inputSMSContainer.empty();

                let inputsSMSHtml =
                    "<div class='d-flex align-items-center mb-3'>";
                for (let i = 1; i <= otpDigitLimit; i++) {
                    const nextId = `digitsms-${i + 1}`;
                    const prevId = `digitsms-${i - 1}`;
                    inputsSMSHtml += `
                        <input type="text" 
                               class="rounded w-100 py-sm-3 py-2 text-center fs-26 fw-bold me-3 digitsms-${i}" 
                               id="digitsms-${i}" 
                               name="digitsms-${i}" 
                               data-next="${nextId}" 
                               data-previous="${prevId}" 
                               maxlength="1">
                    `;
                }

                inputsSMSHtml += "</div>";
                inputSMSContainer.append(inputsSMSHtml);

                $(".inputSMSContainer").on("input", "input", function () {
                    const maxLength = $(this).attr("maxlength") || 1;
                    if (this.value.length >= maxLength) {
                        const next = $(this).data("next");
                        if (next) {
                            $("#" + next).focus();
                        }
                    }
                });

                $(".inputSMSContainer").on("keydown", "input", function (e) {
                    if (e.key === "Backspace" && this.value === "") {
                        const prev = $(this).data("previous");
                        if (prev) {
                            $("#" + prev).focus();
                        }
                    }
                });

                $(".inputSMSContainer").on("click", "input", function () {
                    $(this).select();
                });

                if (data.otp_type === "email") {
                    hideLoader();
                    const otpEmailMessage =
                        document.getElementById("otp-email-message");
                    if (otpEmailMessage) {
                        otpEmailMessage.textContent = `OTP sent to your Email Address ${username}`;
                    }
                    otpEmailModal.show();
                    startTimer(otpExpireTime);
                    // const emailData = {
                    //     subject: data.email_subject,
                    //     content: data.email_content,
                    // };
                    // sendEmail(username, emailData, "email", userName, otp)
                    //     .then(() => {
                            
                    //     })
                    //     .catch(() => {
                    //         hideLoader();
                    //         $("#otp_error").modal("show");
                    //     });
                } else if (data.otp_type === "sms") {
                    hideLoader();
                    const otpSmsMessage =
                        document.getElementById("otp-sms-message");
                    if (otpSmsMessage) {
                        otpSmsMessage.textContent = `OTP sent to your mobile number ending ******${phoneNumber.slice(
                            -4
                        )}`;
                    }
                    otpSmsModal.show();
                    startSmsTimer(otpExpireTime);
                    // const emailData = {
                    //     subject: data.email_subject,
                    //     content: data.email_content,
                    // };
                    // sendSms(phoneNumber, emailData, "sms", userName, otp)
                    //     .then(() => {
                            
                    //     })
                    //     .catch(() => {
                    //         hideLoader();
                    //         $("#otp_error").modal("show");
                    //     });
                } else {
                    hideLoader();
                }
                }
            },
            error: function (xhr) {
                hideLoader();
                $("#otp_error").modal("show");
            },
        });
    });

    $("#otp_signin_forgot").on("click", function () {
        const username = $("[name='forgot_email']").val();

        if (!username || !isValidEmail(username)) {
            toastr.error("Please provide a valid email address.");
            return;
        }

        $.ajax({
            url: "/otp-settings",
            type: "POST",
            data: { email: username, type: "forgot" },
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
            },
            success: function (data) {
                if (data.provider_verified_status == 0) {
                    $("#provider_not_verified_modal").modal("show");
                    hideLoader();
                    const loginModalInstance =
                    bootstrap.Modal.getInstance(forgotModal);
                    if (loginModalInstance) {
                        loginModalInstance.hide();
                    }
                } else {
                const loginModalInstance =
                    bootstrap.Modal.getInstance(forgotModal);
                if (loginModalInstance) {
                    loginModalInstance.hide();
                }

                const otpExpireTime = parseInt(
                    data.otp_expire_time.split(" ")[0]
                );
                const phoneNumber = data.phone_number || "";

                showLoader();

                const otpDigitLimit = parseInt(data.otp_digit_limit);

                const inputContainer = $(".inputcontainer");
                inputContainer.empty(); // Clear any existing input fields

                let inputsHtml = "<div class='d-flex align-items-center mb-3'>";
                for (let i = 1; i <= otpDigitLimit; i++) {
                    const nextId = `digit-${i + 1}`;
                    const prevId = `digit-${i - 1}`;
                    inputsHtml += `
                        <input type="text" 
                               class="rounded w-100 py-sm-3 py-2 text-center fs-26 fw-bold me-3 digit-${i}" 
                               id="digit-${i}" 
                               name="digit-${i}" 
                               data-next="${nextId}" 
                               data-previous="${prevId}" 
                               maxlength="1">
                    `;
                }

                inputsHtml += "</div>";
                inputContainer.append(inputsHtml);

                $(".inputcontainer").on("input", "input", function () {
                    const maxLength = $(this).attr("maxlength") || 1;
                    if (this.value.length >= maxLength) {
                        const next = $(this).data("next");
                        if (next) {
                            $("#" + next).focus();
                        }
                    }
                });

                $(".inputcontainer").on("keydown", "input", function (e) {
                    if (e.key === "Backspace" && this.value === "") {
                        const prev = $(this).data("previous");
                        if (prev) {
                            $("#" + prev).focus();
                        }
                    }
                });

                $(".inputcontainer").on("click", "input", function () {
                    $(this).select();
                });

                const inputSMSContainer = $(".inputSMSContainer");
                inputSMSContainer.empty(); // Clear any existing input fields

                let inputsSMSHtml =
                    "<div class='d-flex align-items-center mb-3'>";
                for (let i = 1; i <= otpDigitLimit; i++) {
                    const nextId = `digitsms-${i + 1}`;
                    const prevId = `digitsms-${i - 1}`;
                    inputsSMSHtml += `
                        <input type="text" 
                               class="rounded w-100 py-sm-3 py-2 text-center fs-26 fw-bold me-3 digitsms-${i}" 
                               id="digitsms-${i}" 
                               name="digitsms-${i}" 
                               data-next="${nextId}" 
                               data-previous="${prevId}" 
                               maxlength="1">
                    `;
                }

                inputsSMSHtml += "</div>";
                inputSMSContainer.append(inputsSMSHtml);

                if (data.otp_type === "email") {
                    hideLoader();
                    const otpEmailMessage =
                        document.getElementById("otp-email-message");
                    if (otpEmailMessage) {
                        otpEmailMessage.textContent = `OTP sent to your Email Address ${username}`;
                    }
                    otpEmailModal.show();
                    startTimer(otpExpireTime);
                    // const emailData = {
                    //     subject: data.email_subject,
                    //     content: data.email_content,
                    // };
                    // sendEmail(username, emailData, "email", userName, otp)
                    //     .then(() => {
                            
                    //     })
                    //     .catch(() => {
                    //         hideLoader();
                    //         $("#otp_error").modal("show");
                    //     });
                } else if (data.otp_type === "sms") {
                    hideLoader();
                    const otpSmsMessage =
                        document.getElementById("otp-sms-message");
                    if (otpSmsMessage) {
                        otpSmsMessage.textContent = `OTP sent to your mobile number ending ******${phoneNumber.slice(
                            -4
                        )}`;
                    }
                    otpSmsModal.show();
                    startSmsTimer(otpExpireTime);
                    // const emailData = {
                    //     subject: data.email_subject,
                    //     content: data.email_content,
                    // };
                    // sendSms(phoneNumber, emailData, "sms", userName, otp)
                    //     .then(() => {
                            
                    //     })
                    //     .catch(() => {
                    //         hideLoader();
                    //         $("#otp_error").modal("show");
                    //     });
                } else {
                    hideLoader();
                }
                }
            },
            error: function (xhr) {
                hideLoader();
                const errorMessage =
                    xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : "Failed to fetch OTP settings. Please try again.";
                toastr.error(errorMessage);
            },
        });
    });

    function isValidEmail(email) {
        const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return emailPattern.test(email);
    }

    function sendEmail(email, emailData, userName, otp) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: "/api/mail/sendmail",
                type: "POST",
                dataType: "json",
                data: {
                    otp_type: "email",
                    to_email: email,
                    notification_type: 2,
                    type: 1,
                    user_name: userName,
                    otp: otp,
                    subject: emailData.subject,
                    content: emailData.content,
                },
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    resolve(response);
                },
                error: function (error) {
                    reject(error);
                },
            });
        });
    }

    function sendSms(phoneNumber, emailData, userName, otp) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: "/api/sms/sendsms",
                type: "POST",
                dataType: "json",
                data: {
                    otp_type: "sms",
                    to_number: phoneNumber,
                    notification_type: 2,
                    type: 1,
                    user_name: userName,
                    otp: otp,
                    subject: emailData.subject,
                    content: emailData.content,
                },
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    resolve(response);
                },
                error: function (error) {
                    if (error.status === 422) {
                        toastr.error("An error occurred while sending OTP.");
                    } else {
                        toastr.error("An error occurred while sending OTP.");
                    }
                    reject(error);
                },
            });
        });
    }

    $("#verify-email-otp-btn").on("click", function () {
        const email = $("[name='email']").val();
        const otpDigitLimit = $(".inputcontainer input").length;
        const forgot_email = $("[name='forgot_email']").val();
        const login_type = "forgot_email";

        const otp = [];
        for (let i = 1; i <= otpDigitLimit; i++) {
            const digit = $(`#digit-${i}`).val();
            otp.push(digit);
        }
        const otpString = otp.join("");

        let requestData = {
            otp: otpString,
        };

        if (email) {
            requestData.email = email;
        } else if (forgot_email) {
            requestData.forgot_email = forgot_email;
            requestData.login_type = login_type;
        }

        $.ajax({
            url: "/verify-otp",
            type: "POST",
            data: requestData,
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
            },
            beforeSend: function () {
                $(".verify-email-otp-btn").attr("disabled", true);
                $(".verify-email-otp-btn").html(
                    "<div class='spinner-border text-light' role='status'></div>"
                );
            },
            success: function (response) {
                if (response.data === "done") {
                    $("#otp-email-modal").modal("hide");
                    $("#reset-password").modal("show");
                    let email = response.email;
                    $("#email_id").val(email);
                } else {
                    $("#otp-email-modal").modal("hide");
                    $("#success_modal").modal("show");
                    setTimeout(function () {
                        location.reload();
                    }, 500);
                }
            },
            error: function (xhr) {
                const errorMessage = xhr.responseJSON.error || "OTP Required";

                $("#error_message").text(errorMessage);
            },
            complete: function () {
                // Reset the button and remove the spinner
                $(".verify-email-otp-btn").attr("disabled", false);
                $(".verify-email-otp-btn").html("Verify OTP");
            },
        });
    });

    $("#verify-sms-otp-btn").on("click", function () {
        const email = $("[name='email']").val();
        const otpDigitLimit = $(".inputcontainer input").length;

        const otp = [];
        for (let i = 1; i <= otpDigitLimit; i++) {
            const digit = $(`#digitsms-${i}`).val();
            otp.push(digit);
        }
        const otpString = otp.join("");

        $.ajax({
            url: "/verify-otp",
            type: "POST",
            data: {
                email: email,
                otp: otpString,
            },
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
            },
            success: function () {
                // Hide the OTP modal
                $("#otp-phone-modal").modal("hide");

                // Show the success modal
                $("#success_modal").modal("show");

                // Reload the page after 1 second
                setTimeout(function () {
                    location.reload();
                }, 500);
            },
            error: function (xhr) {
                const errorMessage = xhr.responseJSON.error || "OTP Required";
                $("#error_sms_message").text(errorMessage); // Update the text content
            },
        });
    });

    function showLoader() {
        const loader = document.getElementById("pageLoader");
        if (loader) {
            loader.style.display = "block";
        }
    }

    function hideLoader() {
        const loader = document.getElementById("pageLoader");
        if (loader) {
            loader.style.display = "none";
        }
    }
});
