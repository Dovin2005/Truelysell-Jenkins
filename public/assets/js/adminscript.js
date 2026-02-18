var pageValue = $("body").data("page");

var frontendValue = $("body").data("frontend");
const languageId = $("#language-settings").data("language-id");

$(document).ready(function () {
    $(".copy-admin-details").on("click", function (event) {
        event.preventDefault(); // Prevent default anchor behavior

        const email = $(this).data("email");
        const password = $(this).data("password");

        $('#adminLoginForm input[name="email"]').val(email);
        $('#adminLoginForm input[name="password"]').val(password);
    });
});

$(document).ready(function () {
    let lastResponse = ""; // To store the last ChatGPT response

    // Open modal when the "Search by ChatGPT" link is clicked
    $("#openChatModal").click(function () {
        $("#chatModal").modal("show");
    });

    $("#sendMessage").click(function () {
        var userMessage = $("#userMessage").val().trim();

        if (userMessage) {
            // Display user's message
            $("#chat-box").append(
                "<div><strong>You:</strong> " + userMessage + "</div>"
            );
            $("#userMessage").val("");

            // Display loading text
            var loadingId = "loading-" + new Date().getTime();
            $("#chat-box").append(
                '<div id="' +
                    loadingId +
                    '"><div class="skeleton chat-skeleton label-loader mb-2"></div> <div class="skeleton chat2-skeleton label-loader"></div></div>'
            );

            // Send message to server
            $.ajax({
                url: "/api/chatgpt",
                method: "POST",
                data: {
                    message: userMessage,
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    if (response.code === 200 && response.data) {
                        $("#" + loadingId).html(
                            '<strong>ChatGPT:</strong> <span class="chat-response">' +
                                response.data +
                                "</span>"
                        );
                    } else {
                        $("#" + loadingId).html(
                            "<strong>ChatGPT:</strong> Sorry, no content available."
                        );
                    }
                    $("#chat-container").scrollTop(
                        $("#chat-container")[0].scrollHeight
                    );
                },
                error: function (xhr, status, error) {
                    let errorMessage = "An unexpected error occurred.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    $("#" + loadingId).html(
                        "<strong>ChatGPT:</strong> " + errorMessage
                    );
                    $("#chat-container").scrollTop(
                        $("#chat-container")[0].scrollHeight
                    );
                },
            });
        }
    });

    // Copy latest ChatGPT response
    $("#copyChatResponse").click(function () {
        var lastResponse = $("#chat-box .chat-response").last().text();

        if (lastResponse) {
            navigator.clipboard
                .writeText(lastResponse)
                .then(() => {
                    alert("Response copied to clipboard!");
                })
                .catch((err) => {
                    console.error("Failed to copy text: ", err);
                });
        } else {
            alert("No ChatGPT response available to copy.");
        }
    });

    // Clear chat box and reset last response when modal is closed
    $("#chatModal").on("hidden.bs.modal", function () {
        $("#chat-box").empty();
        $("#userMessage").val("");
        lastResponse = "";
    });
});

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
    timeOut: "4000",
    extendedTimeOut: "10000",
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut",
};

function initTooltip() {
    $('[data-tooltip="tooltip"]').tooltip({
        trigger: "hover",
    });
}

if (pageValue === "login" || pageValue === "adminlogin") {
    $("#adminLoginForm").on("submit", function (event) {
        event.preventDefault();

        const email = $("#email").val().trim();
        const password = $("#password").val().trim();
        const rememberMe = $("#rememberMe").is(":checked");
        const errorMessageContainer = $("#error-message");

        errorMessageContainer.empty();
        $("#email").siblings(".error-message").remove();
        $("#password").siblings(".error-message").remove();

        let hasError = false;
        if (!email) {
            $("#email").after(
                '<div class="error-message" style="color: red;">Email is required.</div>'
            );
            hasError = true;
        }

        if (!password) {
            $("#password").after(
                '<div class="error-message" style="color: red;">Password is required.</div>'
            );
            hasError = true;
        }

        if (hasError) {
            return;
        }

        $.ajax({
            url: "/admin/login-process",
            type: "POST",
            contentType: "application/json",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: JSON.stringify({
                email: email,
                password: password,
                remember: rememberMe,
            }),
            beforeSend: function () {
                $("#signInBtn").attr("disabled", true);
                $("#signInBtn").html(
                    '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                );
            },
            success: function (response) {
                $("#signInBtn").removeAttr("disabled").html("Sign In");
                localStorage.setItem("admin_token", response.token);
                localStorage.setItem("user_id", response.user_id);
                window.location.href = "/admin/dashboard";
            },
            error: function (xhr) {
                $("#signInBtn").removeAttr("disabled").html("Sign In");
                errorMessageContainer.empty();
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    if (Array.isArray(xhr.responseJSON.message)) {
                        xhr.responseJSON.message.forEach(function (error) {
                            errorMessageContainer.append(
                                "<div>Login failed due to incorrect email or password. Please try again</div>"
                            );
                        });
                    } else if (xhr.responseJSON.code == 403) {
                        errorMessageContainer.append(xhr.responseJSON.message);
                    } else {
                        errorMessageContainer.append(
                            "<div>Login failed due to incorrect email or password. Please try again</div>"
                        );
                    }
                } else {
                    errorMessageContainer.text(
                        "An error occurred. Please try again."
                    );
                }
            },
        });
    });

    $(document).ready(function () {
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
                $("#" + element.id)
                    .siblings("span")
                    .addClass("me-3");
            },
            unhighlight: function (element) {
                $(element).removeClass("is-invalid").addClass("is-valid");
                $("#" + element.id)
                    .siblings("span")
                    .addClass("me-3");
            },
            submitHandler: function (form) {
                form.submit();
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
                            toastr.success(response.message || "Password Updated Successfully.");
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
            $("#adminLoginForm")[0].reset();

            // Clear validation classes and messages
            $("#adminLoginForm .form-control").removeClass("is-invalid is-valid");
            $("#adminLoginForm .invalid-feedback").remove();
            $(".error-message").text("");
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
                    const userName = data.name;
                    const otp = data.otp;
                    const otpExpireTime = parseInt(
                        data.otp_expire_time.split(" ")[0]
                    );
                    const phoneNumber = data.phone_number;

                    showLoader();

                    if (type === "email") {
                        hideLoader();
                        otpEmailModal.show();
                        startTimer(otpExpireTime);
                    } else if (type === "sms") {
                        hideLoader();
                        otpSmsModal.show();
                        startSmsTimer(otpExpireTime);
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

                    const userName = data.name;
                    const otp = data.otp;
                    const otpExpireTime = parseInt(
                        data.otp_expire_time.split(" ")[0]
                    );
                    const phoneNumber = data.phone_number;

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
}

if (pageValue === "admin.dashboard") {
    // Request permission and retrieve the token on page load
    document.addEventListener("DOMContentLoaded", requestPermissionAndGetToken);

    applyBookingStatusStyles();
    function applyBookingStatusStyles() {
        $(".booking-status").each(function () {
            const status = $(this).data("status");
            let statusClass = "";
            let statusText = "";

            switch (status) {
                case 1:
                    statusClass = "badge badge-primary ms-2";
                    statusText = "Open";
                    break;
                case 2:
                    statusClass = "badge badge-soft-info ms-2";
                    statusText = "In progress";
                    break;
                case 3:
                    statusClass = "badge badge-soft-danger ms-2";
                    statusText = "Provider Cancelled";
                    break;
                case 4:
                    statusClass = "badge badge-soft-warning ms-2";
                    statusText = "Refund Initiated";
                    break;
                case 5:
                    statusClass = "badge badge-soft-success ms-2";
                    statusText = "Completed";
                    break;
                case 6:
                    statusClass = "badge badge-soft-success ms-2";
                    statusText = "Order Completed";
                    break;
                case 7:
                    statusClass = "badge badge-soft-success ms-2";
                    statusText = "Refund Completed";
                    break;
                case 8:
                    statusClass = "badge badge-soft-danger ms-2";
                    statusText = "Customer Cancelled";
                    break;

                default:
                    statusClass = "status-unknown";
                    statusText = "Unknown";
            }

            $(this).addClass(statusClass).text(statusText);
        });
    }
}

if (pageValue != "login") {
    $(document).on("click", "#logout-button", function (event) {
        event.preventDefault();
        $.ajax({
            url: "/admin/logout",
            type: "get",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    toastr.success(response.message);
                    localStorage.removeItem("admin_token");
                    sessionStorage.removeItem("admin_token");
                    localStorage.removeItem("user_id");
                    sessionStorage.removeItem("user_id");
                    window.location.href = "/admin/login";
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                toastr.error("An error occurred while logging out.");
            },
        });
    });
}

if (pageValue === "settings.email-settings") {
    $(document).ready(function () {
        getemailsettingsdata();
    });
    function getemailsettingsdata() {
        $.ajax({
            url: "/api/settings/getsettingsdata",
            type: "POST",
            data: { type: 1 },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code == 200) {
                    var settingsdata = response.data.settings;
                    $.each(settingsdata, function (index, item) {
                        if (
                            settingsdata[index].key == "phpmail_status" &&
                            settingsdata[index].value == "1"
                        ) {
                            $("#phpmail").prop("checked", true);
                        } else if (
                            settingsdata[index].key == "smtp_status" &&
                            settingsdata[index].value == "1"
                        ) {
                            $("#smtp").prop("checked", true);
                        } else if (
                            settingsdata[index].key == "sendgrid_status" &&
                            settingsdata[index].value == "1"
                        ) {
                            $("#sendgrid").prop("checked", true);
                        }
                    });
                } else {
                    toastr.error(response.message);
                }
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
            },
            error: function (error) {
                toastr.error("An error occurred while get the Email Settings.");
            },
        });
    }
    //add
    $(document).ready(function () {
        $("#phpemailform").on("submit", function (event) {
            event.preventDefault();
            let formData = {
                phpmail_from_name: $('input[name="name"]').val(),
                phpmail_from_email: $('input[name="from_email"]').val(),
                phpmail_password: $('input[name="password"]').val(),
                type: $("#type1").val(),
                phpmail_status: $("#statusToggle").is(":checked") ? 1 : 0,
            };

            $.ajax({
                url: "/api/settings/communication/storesettings",
                type: "POST",
                data: formData,
                dataType: "json",
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code == "200") {
                        toastr.success(response.message);
                        $("#connect_php").modal("hide");
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (error) {
                    toastr.error(
                        "An error occurred while adding the Email Settings."
                    );
                },
            });
        });
        $("#smtpform").on("submit", function (event) {
            event.preventDefault();
            let isValid = true;

            // Clear previous error messages
            $(".error-text").text("");

            // Email validation
            const email = $("#smtp_from_email").val().trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email) {
                if (languageId === 2) {
                    loadJsonFile("email_required", function (langtst) {
                        $("#smtp_from_email_error").text(langtst);
                    });
                } else {
                    $("#smtp_from_email_error").text("Email is required.");
                }
                isValid = false;
            } else if (!emailPattern.test(email)) {
                if (languageId === 2) {
                    loadJsonFile("email_format", function (langtst) {
                        $("#smtp_from_email_error").text(langtst);
                    });
                } else {
                    $("#smtp_from_email_error").text(
                        "Enter a valid email address."
                    );
                }
                isValid = false;
            }
            const emailname = $("#smtp_from_name").val().trim();
            if (!emailname) {
                $("#smtp_from_name_error").show();
                if (languageId === 2) {
                    loadJsonFile("From_Name_required", function (langtst) {
                        $("#smtp_from_name_error").text(langtst);
                    });
                } else {
                    $("#smtp_from_name_error").text("From Name is required.");
                }
                isValid = false;
            }

            // Port validation
            const port = $("#port").val().trim();
            const portPattern = /^[0-9]+$/;
            if (!port) {
                $("#port_error").show();
                if (languageId === 2) {
                    loadJsonFile("Port_required", function (langtst) {
                        $("#port_error").text(langtst);
                    });
                } else {
                    $("#port_error").text("Port is required.");
                }
                isValid = false;
            } else if (!portPattern.test(port) || port < 1 || port > 65535) {
                if (languageId === 2) {
                    loadJsonFile("valid_port_number", function (langtst) {
                        $("#port_error").text(langtst);
                    });
                } else {
                    $("#port_error").text(
                        "Enter a valid port number (1-65535)."
                    );
                }
                isValid = false;
            }

            // Host validation
            const host = $("#host").val().trim();
            if (!host) {
                $("#host_error").show();
                if (languageId === 2) {
                    loadJsonFile("Host is required", function (langtst) {
                        $("#host_error").text(langtst);
                    });
                } else {
                    $("#host_error").text("Host is required.");
                }
                isValid = false;
            }

            // Password validation
            const password = $("#smtp_password").val().trim();
            if (!password) {
                $("#smtp_password_error").show();
                if (languageId === 2) {
                    loadJsonFile("password_required", function (langtst) {
                        $("#smtp_password_error").text(langtst);
                    });
                } else {
                    $("#smtp_password_error").text("Password is required.");
                }
                isValid = false;
            } else if (password.length < 6) {
                $("#smtp_password_error").show();
                if (languageId === 2) {
                    loadJsonFile("Password_characters", function (langtst) {
                        $("#smtp_password_error").text(langtst);
                    });
                } else {
                    $("#smtp_password_error").text(
                        "Password must be at least 6 characters long."
                    );
                }
                isValid = false;
            }
            if (isValid) {
                let formData = {
                    smtp_from_email: $("#smtp_from_email").val(),
                    smtp_password: $("#smtp_password").val(),
                    smtp_from_name: $("#smtp_from_name").val(),
                    host: $('input[name="host"]').val(),
                    port: $('input[name="port"]').val(),
                    type: $("#type2").val(),
                    smtp_status: $("#statusToggle").is(":checked") ? 1 : 0,
                };

                $.ajax({
                    url: "/api/settings/communication/storesettings",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    headers: {
                        Authorization:
                            "Bearer " + localStorage.getItem("admin_token"),
                        Accept: "application/json",
                    },
                    beforeSend: function () {
                        $(".smtp_btn")
                            .attr("disabled", true)
                            .html(
                                '<div class="spinner-border text-light" role="status"></div>'
                            );
                    },
                })
                    .done((response) => {
                        $(".smtp_btn")
                            .removeAttr("disabled")
                            .html($(".smtp_btn").data("save"));
                        if (response.code == "200") {
                            if (languageId === 2) {
                                loadJsonFile(
                                    response.message,
                                    function (langtst) {
                                        toastr.success(langtst);
                                    }
                                );
                            } else {
                                toastr.success(response.message);
                            }
                            $("#connect_smtp").modal("hide");
                        } else {
                            toastr.error(response.message);
                        }
                    })
                    .fail((error) => {
                        $(".smtp_btn")
                            .removeAttr("disabled")
                            .html($(".smtp_btn").data("save"));
                        if (error.status === 422) {
                            $.each(error.responseJSON, function (key, val) {
                                $("#" + key).addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                                $("#" + key + "_error").show();
                            });
                        } else {
                            toastr.error(
                                "An error occurred while adding the Smtp Settings."
                            );
                        }
                    });
            }
        });
        $("#sendgridform").on("submit", function (event) {
            event.preventDefault();
            let isValid = true;

            // Clear previous error messages
            $(".error-text").text("");

            // Email validation
            const email = $("#sendgrid_from_email").val().trim();
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email) {
                if (languageId === 2) {
                    loadJsonFile("email_required", function (langtst) {
                        $("#sendgrid_from_email_error").text(langtst);
                    });
                } else {
                    $("#sendgrid_from_email_error").text("Email is required.");
                }
                isValid = false;
            } else if (!emailPattern.test(email)) {
                if (languageId === 2) {
                    loadJsonFile("email_format", function (langtst) {
                        $("#sendgrid_from_email_error").text(langtst);
                    });
                } else {
                    $("#sendgrid_from_email_error").text(
                        "Enter a valid email address."
                    );
                }
                isValid = false;
            }
            const sendgrid = $("#sendgrid_key").val().trim();
            if (!sendgrid) {
                $("#sendgrid_key_error").show();
                if (languageId === 2) {
                    loadJsonFile("Sendgrid_Key_required", function (langtst) {
                        $("#sendgrid_key_error").text(langtst);
                    });
                } else {
                    $("#sendgrid_key_error").text("Sendgrid Key is required.");
                }
                isValid = false;
            }
            if (isValid == true) {
                let formData = {
                    sendgrid_from_email: $("#sendgrid_from_email").val(),
                    sendgrid_key: $("#sendgrid_key").val(),
                    type: $("#type3").val(),
                };

                $.ajax({
                    url: "/api/settings/communication/storesettings",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    headers: {
                        Authorization:
                            "Bearer " + localStorage.getItem("admin_token"),
                        Accept: "application/json",
                    },
                    beforeSend: function () {
                        $(".sendgrid_btn")
                            .attr("disabled", true)
                            .html(
                                '<div class="spinner-border text-light" role="status"></div>'
                            );
                    },
                })
                    .done((response) => {
                        $(".sendgrid_btn")
                            .removeAttr("disabled")
                            .html($(".sendgrid_btn").data("save"));
                        if (response.code == "200") {
                            if (languageId === 2) {
                                loadJsonFile(
                                    response.message,
                                    function (langtst) {
                                        toastr.success(langtst);
                                    }
                                );
                            } else {
                                toastr.success(response.message);
                            }
                            $("#connect_sendgrid").modal("hide");
                        } else {
                            toastr.error(response.message);
                        }
                    })
                    .fail((error) => {
                        $(".sendgrid_btn")
                            .removeAttr("disabled")
                            .html($(".sendgrid_btn").data("save"));
                        if (error.status === 422) {
                            $.each(error.responseJSON, function (key, val) {
                                $("#" + key).addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                                $("#" + key + "_error").show();
                            });
                        } else {
                            toastr.error(
                                "An error occurred while adding the Sendgrid Settings."
                            );
                        }
                    });
            }
        });
    });
    $(document).on("click", ".integrate", function (e) {
        e.preventDefault();
        var Id = $(this).data("id");
        var type = $(this).data("type");
        $.ajax({
            url: "/api/settings/getemailsettings",
            type: "POST",
            data: {
                id: Id,
                type: type,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code == 200) {
                    var settingsdata = response.data;
                    $(settingsdata).each(function (index, val) {
                        if (val.type == "phpmail") {
                            $("#" + val.key).val(val.value);
                            $("#status_toggle").prop(
                                "checked",
                                val.phpmail_status == 1
                            );
                        } else if (val.type == "smtp") {
                            $("#" + val.key).val(val.value);
                            $("#status_toggle").prop(
                                "checked",
                                val.smtp_status == 1
                            );
                        }
                        if (val.type == "sendgrid") {
                            $("#" + val.key).val(val.value);
                        }
                    });
                } else {
                    toastr.error("Error: " + response.message);
                }
            },
            error: function (xhr) {
                toastr.error("Failed to set mail settings. Please try again.");
            },
        });
    });
    //Make Default
    $(document).ready(function () {
        $(document).on("click", ".make_default", function (e) {
            e.preventDefault();
            var type = $(this).data("type");
            var checkbox = $(this);
            var isChecked = checkbox.is(":checked");
            var toggleText = $("#" + type + "toggletext");
            $.ajax({
                url: "/api/settings/sms/storesmsstatus",
                type: "POST",
                data: {
                    type: type,
                    status: isChecked ? 1 : 0,
                },
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function (response) {
                    if (response.code === 200) {
                        getemailsettingsdata();
                        if (
                            response.data[0].value === "1" &&
                            response.data[0].type === type
                        ) {
                            $("#" + type).prop("checked", true);
                            if (type == "phpmail") {
                                $("#smtp,#sendgrid").prop("checked", false);
                            } else if (type == "smtp") {
                                $("#phpmail,#sendgrid").prop("checked", false);
                            } else if (type == "sendgrid") {
                                $("#phpmail,#smtp").prop("checked", false);
                            }
                        } else {
                            checkbox.prop("checked", false);
                        }
                        if (languageId === 2) {
                            loadJsonFile(response.message, function (langtst) {
                                toastr.success(langtst);
                            });
                        } else {
                            toastr.success(response.message);
                        }
                    } else {
                        toastr.error("Error: " + response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error(
                        "Failed to set default Mail. Please try again."
                    );
                },
            });
        });
    });
}

if (pageValue === "settings.email-templates") {
    $(document).ready(function () {
        template_table();
        $(".otherdiv").hide();
        // Initialize Summernote editor
        $("#summernote").summernote();
        $("#add_template").on("show.bs.modal", function () {
            $("#templateform")[0].reset(); // Reset the form data
            $("#templatetype").select2("val", [""]);
            $("#notification_type").select2("val", [""]);
            $("#summernote").summernote("code", "");
            $("#template_id").val("");
            $(".modal-title").text($(".modal-title").data("add"));
        });

        $(".placeholder_value").on("click", function () {
            var selectedContent = $(this).data("value");
            var templateType = $("#templatetype").val();

            if (templateType == 1) {
                var summernoteEditor = $("#summernote");
                summernoteEditor.summernote("focus");
                summernoteEditor.summernote("editor.restoreRange");
                summernoteEditor.summernote(
                    "editor.insertText",
                    selectedContent
                );
                summernoteEditor.summernote("editor.saveRange");
            } else {
                var otherContent = $("#othercontent");
                var cursorPos = otherContent.prop("selectionStart");
                var textBefore = otherContent.val().substring(0, cursorPos);
                var textAfter = otherContent.val().substring(cursorPos);
                otherContent.val(textBefore + selectedContent + textAfter);
            }
        });

        // Handle search input
        $("#searchLanguage").on("input", function () {
            template_table(1); // Reset to the first page on new search
        });

        $("#templatetype").on("change", function () {
            var tempval = $(this).val();
            if (tempval == "1") {
                $(".subjectfield").show();
                $(".maildiv").show();
                $(".otherdiv").hide();
            } else {
                $(".subjectfield").hide();
                $(".maildiv").hide();
                $(".otherdiv").show();
            }
        });
    });
    function template_table(page) {
        $.ajax({
            url: "/api/settings/gettemplatelist",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
                sort_by: "id",
                page: page,
                search: $("#searchLanguage").val(),
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code == "200") {
                    if ($.fn.dataTable.isDataTable("#TemplateTable")) {
                        $("#TemplateTable").DataTable().destroy();
                    }
                    templatesTable(response.data, response.meta);
                } else {
                    toastr.error(response.message);
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
                        toastr.error(
                            "An error occurred while fetching templates."
                        );
                    }
                } else {
                    toastr.error("An error occurred while fetching templates.");
                }
                toastr.error("Error fetching templates:", error);
            },
        });
    }

    function templatesTable(templates, meta) {
        let tableBody = "";

        if (templates.length > 0) {
            templates.forEach((template) => {
                tableBody += `
                    <tr>
                        <td>${
                            template.type == "1"
                                ? "Email"
                                : template.type == "2"
                                ? "SMS"
                                : "Notifications"
                        }</td>
                        <td>${template.notification_type_name}</td>
                         <td>${template.title}</td>
                        <td>
                            <span class="badge ${
                                template.status == "1"
                                    ? "badge-soft-success"
                                    : "badge-soft-danger"
                            } d-inline-flex align-items-center">
                                <i class="ti ti-circle-filled fs-5 me-1"></i>${
                                    template.status == "1"
                                        ? "Active"
                                        : "Inactive"
                                }
                            </span>
                        </td>
                        ${
                            $("#has_permission").data("visible") == 1
                                ? `<td>
                                ${
                                    $("#has_permission").data("delete") == 1
                                        ? `<a class="edit_data icon-only" href="#"
                                            data-bs-toggle="modal"
                                            data-bs-target="#add_template"
                                            data-id="${template.id}"
                                            data-type="${template.type}"
                                            data-notificationtype="${template.notification_type}"
                                            data-title="${template.title}"
                                            data-subject="${template.subject}"
                                            data-status="${template.status}">
                                            <i class="ti ti-pencil fs-20"></i>
                                        </a>`
                                        : ""
                                }
                                ${
                                    $("#has_permission").data("delete") == 1
                                        ? `<a class="icon-only delete_template" href="#" data-bs-toggle="modal" data-bs-target="#delete-modal" data-id="${template.id}">
                                            <i class="ti ti-trash m-3 fs-20"></i>
                                        </a>`
                                        : ""
                                }
                        </td>`
                                : ""
                        }
                    </tr>
                `;
            });
        } else {
            tableBody = `
            <tr>
                <td colspan="6" class="text-center">No Data Found</td>
            </tr>`;
        }

        $("#TemplateTable tbody").html(tableBody);

        $("#loader-table").addClass("d-none");
        $(".label-loader, .input-loader").hide();
        $("#TemplateTable, .real-label, .real-input").removeClass("d-none");

        if (!$.fn.dataTable.isDataTable("#TemplateTable")) {
            $("#TemplateTable").DataTable({
                ordering: true,
                language: datatableLang,
            });
        }
    }

    //add
    $(document).ready(function () {
        $("#templateform").on("submit", function (event) {
            event.preventDefault();
            // validation
            isValid = true;
            const apikey = $("#templatetype").val();
            if (!apikey) {
                $("#type_error").show();
                if (languageId === 2) {
                    loadJsonFile("Template_Type_required", function (langtst) {
                        $("#type_error").text(langtst);
                    });
                } else {
                    $("#type_error").text("Template Type is required.");
                }
                isValid = false;
            }
            const secretkey = $("#notification_type").val().trim();
            if (!secretkey) {
                $("#notification_type_error").show();
                if (languageId === 2) {
                    loadJsonFile(
                        "Notification_Type_required",
                        function (langtst) {
                            $("#notification_type_error").text(langtst);
                        }
                    );
                } else {
                    $("#notification_type_error").text(
                        "Notification Type is required."
                    );
                }
                isValid = false;
            }
            const senderid = $("#title").val().trim();
            if (!senderid) {
                $("#title_error").show();
                if (languageId === 2) {
                    loadJsonFile("title_required", function (langtst) {
                        $("#title_error").text(langtst);
                    });
                } else {
                    $("#title_error").text("Title is required.");
                }
                isValid = false;
            }
            if (apikey == 1) {
                const senderid = $("#subject").val().trim();
                if (!senderid) {
                    $("#subject_error").show();
                    if (languageId === 2) {
                        loadJsonFile("Subject_required", function (langtst) {
                            $("#subject_error").text(langtst);
                        });
                    } else {
                        $("#subject_error").text("Subject is required.");
                    }
                    isValid = false;
                }
            }
            if (isValid == true) {
                var summernoteContent = $("#summernote").summernote("code");
                // Set the content to the target field
                $(".content").val(summernoteContent);
                let formData = {
                    id: $('input[name="id"]').val(),
                    type: $("#templatetype").val(),
                    notification_type: $("#notification_type").val(),
                    title: $('input[name="title"]').val(),
                    subject: $('input[name="subject"]').val(),
                    content: summernoteContent,
                    othercontent: $("#othercontent").val(),
                    status: $("#status").is(":checked") ? 1 : 0,
                    user_id: localStorage.getItem("user_id"),
                };
                $.ajax({
                    url: "/api/settings/templates/store",
                    type: "POST",
                    data: formData,
                    headers: {
                        Authorization:
                            "Bearer " + localStorage.getItem("admin_token"),
                        Accept: "application/json",
                    },
                    beforeSend: function () {
                        $(".add_template_btn")
                            .attr("disabled", true)
                            .html(
                                '<div class="spinner-border text-light" role="status"></div>'
                            );
                    },
                })
                    .done((response) => {
                        $(".add_template_btn")
                            .removeAttr("disabled")
                            .html($(".add_template_btn").data("save"));

                        if (response.success) {
                            if (languageId === 2) {
                                loadJsonFile(
                                    response.message,
                                    function (langtst) {
                                        toastr.success(langtst);
                                    }
                                );
                            } else {
                                toastr.success(response.message);
                            }
                            $("#add_template").modal("hide");
                            template_table();
                        } else {
                            toastr.error(response.message);
                        }
                    })
                    .fail((error) => {
                        $(".add_template_btn")
                            .removeAttr("disabled")
                            .html($(".add_template_btn").data("save"));

                        if (error.status === 422) {
                            $.each(
                                error.responseJSON.errors,
                                function (key, val) {
                                    $("#" + key).addClass("is-invalid");
                                    $("#" + key + "_error").text(val[0]);
                                }
                            );
                        } else {
                            toastr.error(
                                "An error occurred while adding the template."
                            );
                        }
                    });
            }
        });

        $("#notification_type,#templatetype").on("change", function () {
            // Remove the 'is-invalid' class to update field styling
            $(this).removeClass("is-invalid");

            // Hide the associated error message
            $(this).siblings(".error-text").hide();
        });
    });

    $(document).on("click", ".edit_data", function (e) {
        e.preventDefault();

        var templateId = $(this).data("id");
        var type = $(this).data("type");
        var notificationtype = $(this).data("notificationtype");
        var title = $(this).data("title");
        var subject = $(this).data("subject");
        var status = $(this).data("status");
        if (type == "1") {
            $(".subjectfield").show();
        } else {
            $(".subjectfield").hide();
        }
        $("#template_id").val(templateId);
        $("#templatetype").val(type).trigger("change").select2();
        $("#notification_type")
            .val(notificationtype)
            .trigger("change")
            .select2();
        $("#title").val(title);
        $("#subject").val(subject);
        $("#status_toggle").prop("checked", status == 1);
        $(".modal-title").text($(".modal-title").data("edit"));
        $.ajax({
            url: "/api/settings/edittemplate",
            type: "POST",
            data: { id: templateId },
            dataType: "json",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code == "200") {
                    var content = response.data[0].content;
                    $("#content").val(content);
                    if (type != 1) {
                        $("#othercontent").val(content);
                    }
                    $("#summernote").summernote("code", content);
                } else {
                    toastr.error(response.message);
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
                        toastr.error(
                            "An error occurred while edit the template."
                        );
                    }
                } else {
                    toastr.error("An error occurred while edit the template.");
                }
                toastr.error("Error edit template:", error);
            },
        });
    });

    $(document).on("click", ".delete_template", function (e) {
        e.preventDefault();

        var templateId = $(this).data("id");
        $("#confirmDelete").data("id", templateId);
    });

    $(document).on("click", "#confirmDelete", function (e) {
        e.preventDefault();

        var templateId = $(this).data("id");
        $.ajax({
            url: "/api/settings/templates/deletetemplate",
            type: "POST",
            data: {
                id: templateId,
            },
            dataType: "json",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    if (languageId === 2) {
                        loadJsonFile(response.message, function (langtst) {
                            toastr.success(langtst);
                        });
                    } else {
                        toastr.success(response.message);
                    }
                    $("#delete-modal").modal("hide");
                    template_table(); // Refresh the templateId table
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                toastr.error(
                    "An error occurred while trying to delete the template."
                );
            },
        });
    });
}

if (pageValue === "settings.sms-settings") {
    $(document).ready(function () {
        loadSMSSetting();
    });

    function loadSMSSetting() {
        $.ajax({
            url: "/api/settings/getsettingsdata",
            type: "POST",
            data: { type: 2 },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    const requiredKeys = [
                        "nexmo_api_key",
                        "nexmo_secret_key",
                        "nexmo_sender_id",
                        "nexmo_status",
                        "twofactor_api_key",
                        "twofactor_secret_key",
                        "twofactor_sender_id",
                        "twofactor_status",
                        "twilio_api_key",
                        "twilio_secret_key",
                        "twilio_sender_id",
                        "twilio_status",
                    ];

                    const filteredSettings = response.data.settings.filter(
                        (setting) => requiredKeys.includes(setting.key)
                    );

                    filteredSettings.forEach((setting) => {
                        if (setting.key === "nexmo_status") {
                            if (setting.value === "1") {
                                $("#nexmo").prop("checked", true);
                            } else {
                                $("#nexmo").prop("checked", false);
                            }
                        } else {
                            $("#" + setting.key).val(setting.value);
                        }
                    });

                    filteredSettings.forEach((setting) => {
                        if (setting.key === "twofactor_status") {
                            if (setting.value === "1") {
                                $("#twofactor").prop("checked", true);
                            } else {
                                $("#twofactor").prop("checked", false);
                            }
                        } else {
                            $("#" + setting.key).val(setting.value);
                        }
                    });
                    filteredSettings.forEach((setting) => {
                        if (setting.key === "twilio_status") {
                            if (setting.value === "1") {
                                $("#twilio").prop("checked", true);
                            } else {
                                $("#twilio").prop("checked", false);
                            }
                        } else {
                            $("#" + setting.key).val(setting.value);
                        }
                    });
                }
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
            },
            error: function (xhr) {
                if (xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            },
        });
    }

    $(document).ready(function () {
        $(document).on("click", ".make_default", function (e) {
            e.preventDefault();
            var type = $(this).data("type");
            var checkbox = $(this);
            var isChecked = checkbox.is(":checked");
            var toggleText = $("#" + type + "toggletext");
            $.ajax({
                url: "/api/settings/sms/storesmsstatus",
                type: "POST",
                data: {
                    type: type,
                    status: isChecked ? 1 : 0,
                },
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function (response) {
                    loadSMSSetting();
                    if (response.code === 200) {
                        if (
                            response.data[0].value === "1" &&
                            response.data[0].type === type
                        ) {
                            $("#" + type).prop("checked", true);
                            if (type == "nexmo") {
                                $("#twofactor,#twilio").prop("checked", false);
                            } else if (type == "twofactor") {
                                $("#nexmo,#twilio").prop("checked", false);
                            } else if (type == "twilio") {
                                $("#nexmo,#twofactor").prop("checked", false);
                            }
                        } else {
                            checkbox.prop("checked", false);
                        }
                        if (languageId === 2) {
                            loadJsonFile(response.message, function (langtst) {
                                toastr.success(langtst);
                            });
                        } else {
                            toastr.success(response.message);
                        }
                    } else {
                        toastr.error("Error: " + response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error(
                        "Failed to set default sms. Please try again."
                    );
                },
            });
        });
        $("#addNexmoForm").submit(function (event) {
            event.preventDefault();
            // validation
            isValid = true;
            const apikey = $("#nexmo_api_key").val().trim();
            if (!apikey) {
                $("#nexmo_api_key_error").show();
                if (languageId === 2) {
                    loadJsonFile("ApiKey_required", function (langtst) {
                        $("#nexmo_api_key_error").text(langtst);
                    });
                } else {
                    $("#nexmo_api_key_error").text("Api Key is required.");
                }
                isValid = false;
            }
            const secretkey = $("#nexmo_secret_key").val().trim();
            if (!secretkey) {
                $("#nexmo_secret_key_error").show();
                if (languageId === 2) {
                    loadJsonFile("SecretKey_required", function (langtst) {
                        $("#nexmo_secret_key_error").text(langtst);
                    });
                } else {
                    $("#nexmo_secret_key_error").text(
                        "Secret Key is required."
                    );
                }
                isValid = false;
            }
            const senderid = $("#nexmo_sender_id").val().trim();
            if (!senderid) {
                $("#nexmo_sender_id_error").show();
                if (languageId === 2) {
                    loadJsonFile("SenderID_required", function (langtst) {
                        $("#nexmo_sender_id_error").text(langtst);
                    });
                } else {
                    $("#nexmo_sender_id_error").text("Sender ID is required.");
                }
                isValid = false;
            }

            if (isValid == true) {
                var formData = new FormData(this);
                $.ajax({
                    url: "/api/settings/communication/storesettings",
                    method: "POST",
                    data: formData,
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    cache: false,
                    headers: {
                        Authorization:
                            "Bearer " + localStorage.getItem("admin_token"),
                        Accept: "application/json",
                    },
                    beforeSend: function () {
                        $(".nexmo_btn").attr("disabled", true);
                        $(".nexmo_btn").html(
                            '<div class="spinner-border text-light" role="status"></div>'
                        );
                    },
                })
                    .done((response, statusText, xhr) => {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $(".nexmo_btn").removeAttr("disabled");
                        $(".nexmo_btn").html($(".nexmo_btn").data("save"));
                        if (response.code === 200) {
                            if (languageId === 2) {
                                loadJsonFile(
                                    response.message,
                                    function (langtst) {
                                        toastr.success(langtst);
                                    }
                                );
                            } else {
                                toastr.success(response.message);
                            }
                            $("#connect_nexmo").modal("hide");
                        } else {
                            toastr.error(response.message);
                        }
                    })
                    .fail((error) => {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $(".nexmo_btn").removeAttr("disabled");
                        $(".nexmo_btn").html($(".nexmo_btn").data("save"));

                        if (error.status == 422) {
                            $.each(error.responseJSON, function (key, val) {
                                $("#" + key).addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                                $("#" + key + "_error").show();
                            });
                        } else {
                            toastr.error(responseJSON.message);
                        }
                    });
            }
        });

        $("#addfactorForm").submit(function (event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: "/api/settings/communication/storesettings",
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                beforeSend: function () {
                    $(".nexmo_btn").attr("disabled", true);
                    $(".nexmo_btn").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
                .done((response, statusText, xhr) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".nexmo_btn").removeAttr("disabled");
                    $(".nexmo_btn").html("Save");
                    if (response.code === 200) {
                        toastr.success(response.message);
                        $("#connect_factor").modal("hide");
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".nexmo_btn").removeAttr("disabled");
                    $(".nexmo_btn").html("Save");

                    if (error.status == 422) {
                        $.each(error.responseJSON, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(responseJSON.message);
                    }
                });
        });

        $("#addtwilioForm").submit(function (event) {
            event.preventDefault();
            // validation
            isValid = true;
            const apikey = $("#twilio_api_key").val().trim();
            if (!apikey) {
                $("#twilio_api_key_error").show();
                if (languageId === 2) {
                    loadJsonFile("ApiKey_required", function (langtst) {
                        $("#twilio_api_key_error").text(langtst);
                    });
                } else {
                    $("#twilio_api_key_error").text("Api Key is required.");
                }
                isValid = false;
            }
            const secretkey = $("#twilio_secret_key").val().trim();
            if (!secretkey) {
                $("#twilio_secret_key_error").show();
                if (languageId === 2) {
                    loadJsonFile("SecretKey_required", function (langtst) {
                        $("#twilio_secret_key_error").text(langtst);
                    });
                } else {
                    $("#twilio_secret_key_error").text(
                        "Secret Key is required."
                    );
                }
                isValid = false;
            }
            const senderid = $("#twilio_sender_id").val().trim();
            if (!senderid) {
                $("#twilio_sender_id_error").show();
                if (languageId === 2) {
                    loadJsonFile("SenderID_required", function (langtst) {
                        $("#twilio_sender_id_error").text(langtst);
                    });
                } else {
                    $("#twilio_sender_id_error").text("Sender ID is required.");
                }
                isValid = false;
            }

            if (isValid == true) {
                var formData = new FormData(this);

                $.ajax({
                    url: "/api/settings/communication/storesettings",
                    method: "POST",
                    data: formData,
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    cache: false,
                    headers: {
                        Authorization:
                            "Bearer " + localStorage.getItem("admin_token"),
                        Accept: "application/json",
                    },
                    beforeSend: function () {
                        $(".twilio_btn").attr("disabled", true);
                        $(".twilio_btn").html(
                            '<div class="spinner-border text-light" role="status"></div>'
                        );
                    },
                })
                    .done((response, statusText, xhr) => {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $(".twilio_btn").removeAttr("disabled");
                        $(".twilio_btn").html($(".twilio_btn").data("save"));
                        if (response.code === 200) {
                            if (languageId === 2) {
                                loadJsonFile(
                                    response.message,
                                    function (langtst) {
                                        toastr.success(langtst);
                                    }
                                );
                            } else {
                                toastr.success(response.message);
                            }
                            $("#connect_twilio").modal("hide");
                        } else {
                            toastr.error(response.message);
                        }
                    })
                    .fail((error) => {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $(".twilio_btn").removeAttr("disabled");
                        $(".twilio_btn").html($(".twilio_btn").data("save"));

                        if (error.status == 422) {
                            $.each(error.responseJSON, function (key, val) {
                                $("#" + key).addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                                $("#" + key + "_error").show();
                            });
                        } else {
                            toastr.error(responseJSON.message);
                        }
                    });
            }
        });
    });
}

if (pageValue === "settings.notification-settings") {
    $(document).ready(function () {
        getnotificationsettingsdata();
        $(".savesettings").click(function (e) {
            e.preventDefault();

            let formData = {
                type: $('input[name="type"]').val(),
            };
            $('input[type="checkbox"]:checked').each(function () {
                formData[$(this).attr("name")] = $(this).val(); // Add checked checkboxes to formData
            });
            $.ajax({
                url: "/api/settings/communication/storesettings",
                method: "POST",
                data: formData,
                dataType: "json",
                cache: false,
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                beforeSend: function () {
                    $(".nexmo_btn").attr("disabled", true);
                    $(".nexmo_btn").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
                .done((response, statusText, xhr) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".nexmo_btn").removeAttr("disabled");
                    $(".nexmo_btn").html("submit");
                    if (response.code === 200) {
                        if (languageId === 2) {
                            loadJsonFile(response.message, function (langtst) {
                                toastr.success(langtst);
                            });
                        } else {
                            toastr.success(response.message);
                        }
                        $("#connect_nexmo").modal("hide");
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".nexmo_btn").removeAttr("disabled");
                    $(".nexmo_btn").html("submit");

                    if (error.status == 422) {
                        $.each(error.responseJSON, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(responseJSON.message);
                    }
                });
        });
    });
    function getnotificationsettingsdata() {
        $.ajax({
            url: "/api/settings/getsettingsdata",
            type: "POST",
            data: { type: 3 },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code == 200) {
                    var settingsdata = response.data.settings;
                    $.each(settingsdata, function (index, item) {
                        if (
                            settingsdata[index].key == "emailNotifications" &&
                            settingsdata[index].value == "1"
                        ) {
                            $("#emailNotifications").prop("checked", true);
                        } else if (
                            settingsdata[index].key == "pushNotifications" &&
                            settingsdata[index].value == "1"
                        ) {
                            $("#pushNotifications").prop("checked", true);
                        } else if (
                            settingsdata[index].key == "smsNotifications" &&
                            settingsdata[index].value == "1"
                        ) {
                            $("#smsNotifications").prop("checked", true);
                        }
                    });
                } else {
                    toastr.error(response.message);
                }
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
            },
            error: function (error) {
                toastr.error(
                    "An error occurred while get the Notification Settings."
                );
            },
        });
    }

    $("#configurationForm").validate({
        rules: {
            project_id: {
                required: true,
            },
            client_email: {
                email: true,
                required: true,
            },
            private_key: {
                required: true,
            },
        },
        messages: {
            project_id: {
                required: $("#project_id_error").data("required"),
            },
            client_email: {
                email: $("#client_email_error").data("required"),
                required: $("#client_email_error").data("email_format"),
            },
            private_key: {
                required: $("#private_key_error").data("required"),
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
            $.ajax({
                url: "/api/settings/communication/storesettings",
                type: "POST",
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                data: {
                    type: "fcm",
                    project_id: $("#project_id").val(),
                    client_email: $("#client_email").val(),
                    private_key: $("#private_key").val(),
                },
                beforeSend: function () {
                    $("#configurationSaveBtn")
                        .attr("disabled", true)
                        .html(
                            '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                        );
                },
                success: function (response) {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid is-valid");
                    $("#configurationSaveBtn")
                        .removeAttr("disabled")
                        .html($("#configurationSaveBtn").data("save"));
                    $("#del-account").modal("hide");
                    if (response.code === 200) {
                        $("#configuration_modal").modal("hide");
                        if (languageId === 2) {
                            loadJsonFile(response.message, function (langtst) {
                                toastr.success(langtst);
                            });
                        } else {
                            toastr.success(response.message);
                        }
                        getFcmCongfiguration();
                    }
                },
                error: function (error) {
                    $(".error-text").text("");
                    $("#configurationSaveBtn")
                        .removeAttr("disabled")
                        .html($("#configurationSaveBtn").data("save"));
                    $(".form-control").removeClass("is-invalid is-valid");
                    if (error.responseJSON.code === 422) {
                        $.each(error.responseJSON.errors, function (key, val) {
                            $("#" + key + "_del").addClass("is-invalid");
                            $("#" + key + "_del_error").text(val[0]);
                        });
                    } else {
                        toastr.error(error.responseJSON.message);
                    }
                },
            });
        },
    });

    function getFcmCongfiguration() {
        $.ajax({
            url: "/api/settings/getsettingsdata",
            type: "POST",
            data: { type: 3 },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code == 200) {
                    var settingsdata = response.data.settings;
                    $.each(settingsdata, function (index, item) {
                        if (settingsdata[index].key == "project_id") {
                            $("#project_id").val(settingsdata[index].value);
                        } else if (settingsdata[index].key == "client_email") {
                            $("#client_email").val(settingsdata[index].value);
                        } else if (settingsdata[index].key == "private_key") {
                            $("#private_key").val(settingsdata[index].value);
                        }
                    });
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (error) {
                toastr.error(
                    "An error occurred while get the Notification Settings."
                );
            },
        });
    }

    $(document).on("click", "#viewFcm", function () {
        $(".error-text").text("");
        $(".form-control").removeClass("is-invalid is-valid");
        getFcmCongfiguration();
    });
}

if (pageValue === "admin.testimonials") {
    let langCode = $("body").data("lang");

    function listTestimonial() {
        $.ajax({
            url: "/api/admin/testimonial-list",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
                sort_by: "id",
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    let testimonials = response.data;
                    let tableBody = "";

                    if (testimonials.length === 0) {
                        $("#testimonialsTable").DataTable().destroy();
                        tableBody += `
                            <tr>
                                <td colspan="6" class="text-center">${$(
                                    "#testimonialsTable"
                                ).data("empty")}</td>
                            </tr>`;
                    } else {
                        testimonials.forEach((testimonial, index) => {
                            tableBody += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="#" class="avatar avatar-md"><img src="${
                                                testimonial.client_image
                                            }" class="img-fluid rounded-circle" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="#">${
                                                    testimonial.client_name
                                                }</a>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>${testimonial.position}</td>
                                    <td class="text-wrap text-break w-75" style="text-align: justify;">${
                                        testimonial.description.length > 100
                                            ? testimonial.description.substring(
                                                  0,
                                                  100
                                              ) + "..."
                                            : testimonial.description
                                    }</td>
                                    ${
                                        $("#has_permission").data("edit") == 1
                                            ? `<td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input testimonial_status" ${
                                                testimonial.status == 1
                                                    ? "checked"
                                                    : ""
                                            } type="checkbox" role="switch" id="switch-sm" data-id="${
                                                  testimonial.id
                                              }">
                                        </div>
                                    </td>`
                                            : ""
                                    }
                                    ${
                                        $("#has_permission").data("visible") ==
                                        1
                                            ? `<td>
                                        <li style="list-style: none;">
                                            ${
                                                $("#has_permission").data(
                                                    "edit"
                                                ) == 1
                                                    ? `<a class="edit_testimonial_btn"
                                                href="#"
                                                data-bs-toggle="modal"
                                                data-bs-target="#add_testimonial_modal"
                                                data-id="${testimonial.id}"
                                                data-client_name="${
                                                    testimonial.client_name
                                                }"
                                                data-client_image="${
                                                    testimonial.client_image
                                                }"
                                                data-position="${
                                                    testimonial.position
                                                }"
                                                data-description="${
                                                    testimonial.description
                                                }"
                                                data-status="${
                                                    testimonial.status
                                                }">
                                                <i class="ti ti-pencil fs-20" data-tooltip="tooltip" title="${$(
                                                    ".testimonial_modal_title"
                                                ).data("edit")}"></i>
                                            </a>`
                                                    : ""
                                            }
                                            ${
                                                $("#has_permission").data(
                                                    "delete"
                                                ) == 1
                                                    ? `<a class="delete delete_testimonial_btn" href="#" data-bs-toggle="modal" data-bs-target="#delete_testimonial_modal" data-del-id="${
                                                          testimonial.id
                                                      }">
                                                <i class="ti ti-trash m-3 fs-20" data-tooltip="tooltip" title="${$(
                                                    ".testimonial_modal_title"
                                                ).data("delete")}"></i>
                                            </a>`
                                                    : ""
                                            }
                                        </li>
                                    </td>`
                                            : ""
                                    }
                                </tr>
                            `;
                        });
                    }

                    $("#testimonialsTable tbody").html(tableBody);
                    initTooltip();
                    $("#loader-table").addClass("d-none");
                    $(".label-loader, .input-loader").hide();
                    $(
                        "#testimonialsTable, .real-label, .real-input"
                    ).removeClass("d-none");

                    if (
                        testimonials.length != 0 &&
                        !$.fn.DataTable.isDataTable("#testimonialsTable")
                    ) {
                        $("#testimonialsTable").DataTable({
                            ordering: true,
                            language: datatableLang,
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

    $(document).on("click", ".edit_testimonial_btn", function (e) {
        e.preventDefault();
        $(".testimonial_modal_title").html(
            $(".testimonial_modal_title").data("edit_title")
        );
        $("#id").val("id");
        $("#method").val("update");
        $(".error-text").text("");
        $(".form-control").removeClass("is-invalid is-valid");

        var id = $(this).data("id");
        var client_name = $(this).data("client_name");
        var client_image = $(this).data("client_image");
        var position = $(this).data("position");
        var status = $(this).data("status");
        var description = $(this).data("description");

        $("#id").val(id);
        $("#client_name").val(client_name);
        $("#clientImagePreview").show();
        $("#clientImagePreview").attr("src", client_image);
        $(".upload_icon").hide();
        $("#status").prop("checked", status == 1);
        $("#position").val(position);
        $("#description").val(description);
    });

    $(document).ready(function () {
        listTestimonial();

        $("#client_image").on("change", function (event) {
            if ($(this).val() !== "") {
                $(this).valid();
            }
            let reader = new FileReader();
            reader.onload = function (e) {
                $("#clientImagePreview").attr("src", e.target.result).show();
                $(".upload_icon").hide();
            };
            reader.readAsDataURL(event.target.files[0]);
        });

        $(document).on("click", ".delete_testimonial_btn", function () {
            var id = $(this).data("del-id");
            $("#delete_id").val(id);
        });

        $("#add_testimonial_btn").on("click", function () {
            $(".testimonial_modal_title").html(
                $(".testimonial_modal_title").data("add_title")
            );
            $("#method").val("add");
            $("#clientImagePreview").hide();
            $(".upload_icon").show();
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid is-valid");
            $("#testimonialForm").trigger("reset");
        });

        $(document).on("click", ".delete_testimonial_confirm", function (e) {
            e.preventDefault();

            var delId = $("#delete_id").val();
            $.ajax({
                url: "/api/admin/delete-testimonial",
                type: "POST",
                data: {
                    id: delId,
                    language_code: langCode,
                },
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                        $("#delete_testimonial_modal").modal("hide");
                        listTestimonial();
                    }
                    $("#testimonialsTable").DataTable().destroy();
                },
                error: function (xhr, status, error) {
                    toastr.error(xhr.responseJSON.message);
                },
            });
        });

        $("#testimonialForm").validate({
            rules: {
                client_name: {
                    required: true,
                    maxlength: 100,
                },
                position: {
                    required: true,
                    maxlength: 100,
                },
                client_image: {
                    required: function () {
                        return (
                            $("#testimonialForm input[name='method']").val() ===
                            "add"
                        );
                    },
                    extension: "jpeg|jpg|png|webp",
                    filesize: 2048,
                },
                description: {
                    required: true,
                    maxword: 300,
                },
            },
            messages: {
                client_name: {
                    required: $("#client_name_error").data("required"),
                    maxlength: $("#client_name_error").data("max"),
                },
                position: {
                    required: $("#position_error").data("required"),
                    maxlength: $("#position_error").data("max"),
                },
                client_image: {
                    required: $("#client_image_error").data("required"),
                    extension: $("#client_image_error").data("extension"),
                    filesize: $("#client_image_error").data("filesize"),
                },
                description: {
                    required: $("#description_error").data("required"),
                    maxword: $("#description_error").data("max"),
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
                var formData = new FormData(form);
                formData.append("status", $("#status").is(":checked") ? 1 : 0);
                formData.append("language_code", langCode);

                $.ajax({
                    url: "/api/admin/save-testimonial",
                    type: "POST",
                    data: formData,
                    enctype: "multipart/form-data",
                    contentType: false,
                    processData: false,
                    cache: false,
                    headers: {
                        Authorization:
                            "Bearer " + localStorage.getItem("admin_token"),
                        Accept: "application/json",
                    },
                    beforeSend: function () {
                        $(".save_testimonial_btn")
                            .attr("disabled", true)
                            .html(
                                '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                            );
                    },
                })
                    .done(function (response) {
                        if ($.fn.DataTable.isDataTable("#testimonialsTable")) {
                            $("#testimonialsTable").DataTable().destroy();
                        }
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".save_testimonial_btn")
                            .removeAttr("disabled")
                            .html($(".save_testimonial_btn").data("save"));
                        if (response.code === 200) {
                            toastr.success(response.message);
                            $("#add_testimonial_modal").modal("hide");
                            listTestimonial();
                        }
                    })
                    .fail(function (error) {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".save_testimonial_btn")
                            .removeAttr("disabled")
                            .html($(".save_testimonial_btn").data("save"));
                        if (error.status == 422) {
                            $.each(
                                error.responseJSON.errors,
                                function (key, val) {
                                    $("#" + key).addClass("is-invalid");
                                    $("#" + key + "_error").text(val[0]);
                                }
                            );
                        }
                    });
            },
        });

        $.validator.addMethod(
            "maxword",
            function (value, element, param) {
                return str_word_count(value) <= param;
            },
            "Description should contain no more than 300 words."
        );

        $.validator.addMethod(
            "filesize",
            function (value, element, param) {
                if (element.files.length === 0) return true;
                return element.files[0].size <= param * 1024;
            },
            "File size must be less than {0} KB."
        );

        function str_word_count(str) {
            return str.trim().split(/\s+/).length;
        }

        $(document).on("change", ".testimonial_status", function () {
            let id = $(this).data("id");
            let status = $(this).is(":checked") ? 1 : 0;

            var data = {
                id: id,
                status: status,
                language_code: langCode,
            };

            $.ajax({
                url: "/api/admin/change-status-testimonial",
                type: "POST",
                data: data,
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                        listTestimonial();
                    }
                },
                error: function (error) {
                    toastr.error(error.responseJSON.message);
                },
            });
        });
    });
}

if (pageValue === "admin.subscriber-list") {
    function listSubscriber() {
        $.ajax({
            url: "/api/admin/newsletter/list-subscriber",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    let subscribers = response.data;
                    let tableBody = "";

                    if (subscribers.length === 0) {
                        $("#subscriberTable").DataTable().destroy();
                        tableBody += `
                            <tr>
                                <td colspan="5" class="text-center">${$(
                                    "#subscriberTable"
                                ).data("empty")}</td>
                            </tr>`;
                    } else {
                        subscribers.forEach((subscriber, index) => {
                            var checkedVal =
                                subscriber.status == 1 ? "checked" : "";
                            var subscriberStatus =
                                subscriber.status == 1 ? "" : "disabled";
                            tableBody += `
                                <tr>
                                    ${
                                        $("#has_permission").data("create") == 1
                                            ? `<td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input select-checkbox" ${subscriberStatus} type="checkbox">
                                        </div>
                                    </td>`
                                            : ""
                                    }
                                    <td>${subscriber.email}</td>
                                    ${
                                        $("#has_permission").data("edit") == 1
                                            ? `<td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input subscriber_status" ${checkedVal} type="checkbox" role="switch" id="switch-sm" data-subscriber-id = ${subscriber.id}>
                                        </div>
                                    </td>`
                                            : ""
                                    }
                                    ${
                                        $("#has_permission").data("visible") ==
                                        1
                                            ? `<td>
                                        <li style="list-style: none;">
                                            ${
                                                $("#has_permission").data(
                                                    "delete"
                                                ) == 1
                                                    ? `<a class="delete delete_subscriber_btn" href="#" data-bs-toggle="modal" data-bs-target="#delete_subscriber_modal" data-del-id="${
                                                          subscriber.id
                                                      }">
                                                <i class="ti ti-trash m-3 fs-20" data-tooltip="tooltip" title="${$(
                                                    "#subscriberTable"
                                                ).data("delete")}"></i>
                                            </a>`
                                                    : ""
                                            }
                                        </li>
                                    </td>`
                                            : ""
                                    }
                                </tr>
                            `;
                        });
                    }

                    $("#subscriberTable tbody").html(tableBody);
                    initTooltip();

                    $("#loader-table").addClass("d-none");
                    $(".label-loader, .input-loader").hide();
                    $("#subscriberTable, .real-label, .real-input").removeClass(
                        "d-none"
                    );

                    if (
                        subscribers.length != 0 &&
                        !$.fn.DataTable.isDataTable("#subscriberTable")
                    ) {
                        $("#subscriberTable").DataTable({
                            ordering: true,
                            language: datatableLang,
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

    $("#selected_email_btn").on("click", function () {
        let selectedEmails = [];

        $("#subscriberTable tbody .select-checkbox:checked").each(function () {
            let row = $(this).closest("tr");
            let email = row.find("td:nth-child(2)").text();
            let status = row.find(".subscriber_status");

            if (status.prop("checked")) {
                selectedEmails.push(email);
            }
        });

        if (selectedEmails.length > 0) {
            sendNewsletter(selectedEmails);
        } else {
            toastr.error($("#subscriberTable").data("empty_select"));
        }
    });

    function sendNewsletter(selectedEmails) {
        $.ajax({
            url: "/api/admin/get-newsletter-template",
            type: "POST",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200 && response.data != null) {
                    var subject = response.data.subject;
                    var content = response.data.content;

                    $.ajax({
                        url: "/api/mail/sendmail",
                        type: "POST",
                        data: {
                            subject: subject,
                            content: content,
                            to_email: selectedEmails,
                        },
                        beforeSend: function () {
                            $("#selected_email_btn").attr("disabled", true);
                            $("#selected_email_btn").html(
                                `<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>  ${$(
                                    "#subscriberTable"
                                ).data("sending")}..`
                            );
                        },
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                        },
                        success: function (response) {
                            if (response.code === 200) {
                                $("#selected_email_btn")
                                    .removeAttr("disabled")
                                    .html(
                                        `<i class="fas fa-envelope"></i> ${$(
                                            "#subscriberTable"
                                        ).data("send_email")}`
                                    );
                                toastr.success(response.message);
                            }
                        },
                        error: function (xhr, status, error) {
                            $("#selected_email_btn")
                                .removeAttr("disabled")
                                .html(
                                    `<i class="fas fa-envelope"></i> ${$(
                                        "#subscriberTable"
                                    ).data("send_email")}`
                                );
                            toastr.error(
                                "An error occurred while sending email."
                            );
                        },
                    });
                } else {
                    toastr.error("An error occurred while sending email.");
                }
            },
            error: function (xhr, status, error) {
                toastr.error("An error occurred while sending email.");
            },
        });
    }

    $(document).ready(function () {
        listSubscriber();

        $(document).on("click", ".delete_subscriber_btn", function () {
            var id = $(this).data("del-id");
            $("#delete_id").val(id);
        });

        $(document).on("click", ".delete_subscriber_confirm", function (e) {
            e.preventDefault();
            var delId = $("#delete_id").val();

            $.ajax({
                url: "/api/admin/newsletter/delete-subscriber",
                type: "POST",
                data: {
                    id: delId,
                    language_code: $("body").data("lang"),
                },
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    $("#subscriberTable").DataTable().destroy();
                    if (response.code === 200) {
                        toastr.success(response.message);
                        $("#delete_subscriber_modal").modal("hide");
                        listSubscriber();
                    }
                },
                error: function (xhr, status, error) {
                    toastr.error(
                        "An error occurred while deleting subscriber."
                    );
                },
            });
        });

        $(document).on("change", ".subscriber_status", function () {
            let id = $(this).data("subscriber-id");
            let status = $(this).is(":checked") ? 1 : 0;

            var data = {
                id: id,
                status: status,
                language_code: $("body").data("lang"),
            };

            $.ajax({
                url: "/api/admin/newsletter/change-subscriber-status",
                type: "POST",
                data: data,
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if ($.fn.DataTable.isDataTable("#subscriberTable")) {
                        $("#subscriberTable").DataTable().destroy();
                    }
                    if (response.code === 200) {
                        toastr.success(response.message);
                        listSubscriber();
                    }
                },
                error: function () {
                    toastr.error("An error occurred while updating status");
                },
            });
        });

        $("#select_all_subscriber").on("click", function () {
            let isChecked = $(this).is(":checked");

            $("#subscriberTable tbody")
                .find(".select-checkbox:not(:disabled)")
                .prop("checked", isChecked);
        });
    });
}

//faq-setting
if (pageValue === "admin.faq") {
    var langCode = $("body").data("lang");

    // function faq_table(langCode = '') {
    //     fetchData(langCode);
    // }

    function fetchFaq(langCode = "") {
        $.ajax({
            url: "/api/faq/faq-detail",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
                sort_by: "id",
                language_code: langCode,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    populateFaqTable(response.data, response.meta);
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
                } else {
                    toastr.error("An error occurred while fetching.");
                }
            },
        });
    }

    function populateFaqTable(Faq) {
        let tableBody = "";

        if (Faq.length > 0) {
            Faq.forEach((Faq, index) => {
                tableBody += `
                    <tr>
                            <td>${index + 1}</td>
                            <td>${
                                Faq.question.length > 30
                                    ? Faq.question.substring(0, 30) + "..."
                                    : Faq.question
                            }</td>
                            <td>${
                                Faq.answer.length > 100
                                    ? Faq.answer.substring(0, 50) + "..."
                                    : Faq.answer
                            }</td>
                            <td>
                                <span class="badge ${
                                    Faq.status == "1"
                                        ? "badge-soft-success"
                                        : "badge-soft-danger"
                                } d-inline-flex align-items-center">
                                    <i class="ti ti-circle-filled fs-5 me-1"></i>${
                                        Faq.status == "1"
                                            ? "Active"
                                            : "Inactive"
                                    }
                                </span>
                            </td>
                            ${
                                $("#has_permission").data("visible") == 1
                                    ? `<td>
                                <li style="list-style: none;">
                                    ${
                                        $("#has_permission").data("edit") == 1
                                            ? `<a class="edit_faq_data"
                                    href="#"
                                    data-bs-toggle="modal"
                                    data-bs-target="#edit_faq"
                                    data-id="${Faq.id}"
                                    data-question="${Faq.question}"
                                    data-answer="${Faq.answer}"
                                    data-status="${Faq.status}">
                                    <i class="ti ti-pencil fs-20"></i>
                                    </a>`
                                            : ""
                                    }
                                    ${
                                        $("#has_permission").data("delete") == 1
                                            ? `<a class="delete" href="#" data-bs-toggle="modal" data-bs-target="#delete-modal" data-id="${Faq.id}">
                                        <i class="ti ti-trash m-3 fs-20"></i>
                                    </a>`
                                            : ""
                                    }
                                </li>
                            </td>`
                                    : ""
                            }
                        </tr>
                    `;
            });
        } else {
            $("#faq_datatable").DataTable().destroy();
            tableBody = `
                    <tr>
                        <td colspan="5" class="text-center">${$(
                            "#faq_datatable"
                        ).data("empty")}</td>
                    </tr>
                `;
        }

        $("#faq_datatable tbody").html(tableBody);

        $("#loader-table").addClass("d-none");
        $(".label-loader, .input-loader").hide();
        $("#faq_datatable, .real-label, .real-input").removeClass("d-none");

        if (Faq.length != 0 && !$.fn.dataTable.isDataTable("#faq_datatable")) {
            $("#faq_datatable").DataTable({
                ordering: true,
                language: datatableLang,
            });
        }
    }

    $(document).on("click", ".edit_faq_data", function (e) {
        e.preventDefault();

        var faqId = $(this).data("id");
        var question = $(this).data("question");
        var answer = $(this).data("answer");
        var status = $(this).data("status");

        $("#edit_id").val(faqId);
        $("#edit_question").val(question);
        $("#edit_answer").val(answer);
        $("#edit_status").prop("checked", status == 1);
    });

    $(document).on("click", '.delete[data-bs-toggle="modal"]', function (e) {
        e.preventDefault();
        var faqId = $(this).data("id");
        $("#confirmDeleteFaq").data("id", faqId);
    });

    $(document).on("click", "#confirmDeleteFaq", function (e) {
        e.preventDefault();

        var faqId = $(this).data("id");
        $.ajax({
            url: "/api/faq/delete",
            type: "POST",
            data: {
                id: faqId,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $("#confirmDeleteFaq").attr("disabled", true);
                $("#confirmDeleteFaq").html(
                    '<div class="spinner-border text-light" role="status"></div>'
                );
            },

            success: function (response) {
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid");
                $("#confirmDeleteFaq").removeAttr("disabled");
                $("#confirmDeleteFaq").html("Delete");
                if (response.success) {
                    toastr.success(response.message);
                    fetchFaq(langCode);
                    $("#delete-modal").modal("hide");
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                toastr.error("An error occurred while trying to delete.");
            },
        });
    });

    $(document).ready(function () {
        fetchFaq(langCode);
    });

    $(document).ready(function () {
        // Add jQuery validation to the form
        $("#addFAQForm").validate({
            rules: {
                question: {
                    required: true,
                    minlength: 5, // Minimum length for question
                },
                answer: {
                    required: true,
                    minlength: 10, // Minimum length for answer
                },
            },
            messages: {
                question: {
                    required: $("#question_error").data("required"),
                    minlength: $("#question_error").data("min"),
                },
                answer: {
                    required: $("#answer_error").data("required"),
                    minlength: $("#answer_error").data("min"),
                },
            },
            errorElement: "span",
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback");
                element.closest(".mb-3").append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass("is-invalid").addClass("is-valid");
            },
            submitHandler: function (form) {
                // Prevent default form submission
                event.preventDefault();

                var formData = new FormData(form);
                formData.append("status", $("#status").is(":checked") ? 1 : 0);
                formData.append("language_id", $("#language_id").val());

                $.ajax({
                    url: "/api/faq/save",
                    method: "POST",
                    data: formData,
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        $(".add_faq_btn").attr("disabled", true);
                        $(".add_faq_btn").html(
                            '<div class="spinner-border text-light" role="status"></div>'
                        );
                    },
                })
                    .done((response, statusText, xhr) => {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $(".add_faq_btn").removeAttr("disabled");
                        $(".add_faq_btn").html(
                            $(".add_faq_btn").data("save_text")
                        );
                        if (response.code === 200) {
                            toastr.success(response.message);
                            fetchFaq(langCode);
                            $("#add_faq").modal("hide");
                        } else {
                            toastr.error(response.message);
                        }
                    })
                    .fail((error) => {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $(".add_faq_btn").removeAttr("disabled");
                        $(".add_faq_btn").html(
                            $(".add_faq_btn").data("save_text")
                        );

                        if (error.status == 422) {
                            $.each(error.responseJSON, function (key, val) {
                                $("#" + key).addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                            });
                        } else {
                            toastr.error(
                                error.responseJSON.message,
                                "bg-danger"
                            );
                        }
                    });
            },
        });
    });

    $(document).ready(function () {
        // Add jQuery validation to the form
        $("#editFAQForm").validate({
            rules: {
                edit_question: {
                    required: true,
                    minlength: 5, // Minimum length for question
                },
                edit_answer: {
                    required: true,
                    minlength: 10, // Minimum length for answer
                },
            },
            messages: {
                edit_question: {
                    required: "The question field is required.",
                    minlength:
                        "The question must be at least 5 characters long.",
                },
                edit_answer: {
                    required: "The answer field is required.",
                    minlength:
                        "The answer must be at least 10 characters long.",
                },
            },
            errorElement: "span",
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback");
                element.closest(".mb-3").append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass("is-invalid").addClass("is-valid");
            },
            submitHandler: function (form) {
                // Prevent default form submission
                event.preventDefault();

                var formData = new FormData(form);
                formData.append(
                    "status",
                    $("#edit_status").is(":checked") ? 1 : 0
                );

                $.ajax({
                    url: "/api/faq/update",
                    method: "POST",
                    data: formData,
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    cache: false,
                    headers: {
                        Authorization:
                            "Bearer " + localStorage.getItem("admin_token"),
                        Accept: "application/json",
                    },
                    beforeSend: function () {
                        $(".edit_faq_btn").attr("disabled", true);
                        $(".edit_faq_btn").html(
                            '<div class="spinner-border text-light" role="status"></div>'
                        );
                    },
                })
                    .done((response, statusText, xhr) => {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $(".edit_faq_btn").removeAttr("disabled");
                        $(".edit_faq_btn").html(
                            $(".edit_faq_btn").data("update-text")
                        );
                        if (response.code === 200) {
                            toastr.success(response.message);
                            fetchFaq(langCode);
                            $("#edit_faq").modal("hide");
                        } else {
                            toastr.error(response.message);
                        }
                    })
                    .fail((error) => {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $(".edit_faq_btn").removeAttr("disabled");
                        $(".edit_faq_btn").html(
                            $(".edit_faq_btn").data("update-text")
                        );

                        if (error.status == 422) {
                            $.each(error.responseJSON, function (key, val) {
                                $("#" + key).addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                            });
                        } else {
                            toastr.error(
                                error.responseJSON.message,
                                "bg-danger"
                            );
                        }
                    });
            },
        });
    });

    $(document).ready(function () {
        $("#add_faq").on("hidden.bs.modal", function () {
            $("#addFAQForm")[0].reset();

            $(".form-control")
                .removeClass("is-invalid")
                .removeClass("is-valid");
            $(".invalid-feedback").text("");
        });
    });

    $(document).ready(function () {
        $("#edit_faq").on("hidden.bs.modal", function () {
            $("#editFAQForm")[0].reset();

            $(".form-control")
                .removeClass("is-invalid")
                .removeClass("is-valid");
            $(".invalid-feedback").text("");

            languageTranslate("", langCode);
        });
    });

    $("#language_id").on("change", function () {
        var langId = $(this).val();
        var id = $("#edit_id").val();

        languageTranslate(langId);
        getFaq(id, langId);
    });

    function getFaq(id, lang_id = "", langCode = "") {
        $.ajax({
            url: "/api/admin/get-faq",
            type: "POST",
            data: {
                id: id,
                language_id: lang_id,
                language_code: langCode,
            },
            dataType: "json",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    let faq = response.data;

                    if (faq) {
                        $("#edit_question").val(faq.question);
                        $("#edit_answer").val(faq.answer);
                    } else {
                        $("#edit_question").val("");
                        $("#edit_answer").val("");
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

    function languageTranslate(lang_id = "", langCode = "") {
        $.ajax({
            url: "/api/translate",
            type: "POST",
            dataType: "json",
            data: {
                language_id: lang_id,
                language_code: langCode,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                const trans = response.translated_values;

                $(".form-control")
                    .removeClass("is-invalid")
                    .removeClass("is-valid");
                $(".invalid-feedback").text("");

                if (response.code === 200 && Object.keys(trans).length > 0) {
                    $('label[for="edit_question"]').html(
                        `${trans.question}<span class="text-danger"> *</span>`
                    );
                    $('label[for="edit_answer"]').html(
                        `${trans.answer}<span class="text-danger"> *</span>`
                    );

                    $("#edit_question").attr(
                        "placeholder",
                        trans.enter_question
                    );
                    $("#edit_answer").attr("placeholder", trans.enter_answer);
                    $("#status_text").text(trans.Status);
                    $(".cancelbtn").text(trans.Cancel);
                    $("#edit_btn_faq").text(trans.Save);
                    $(".lang_title").text(trans.available_translations);
                    $(".edit_modal_title").text(trans.edit_faq);
                    $(".edit_faq_btn").data("update-text", trans.Save);

                    $("#editFAQForm").validate().settings.messages = {
                        edit_question: {
                            required: trans.question_required,
                            minlength: trans.question_minlength,
                        },
                        edit_answer: {
                            required: trans.answer_required,
                            minlength: trans.answer_minlength,
                        },
                    };
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }
}

if (pageValue === "admin.blog-category") {
    var category_name_required = "Category name is required.";
    var category_name_max = "Category name cannot exceed 100 characters.";
    var category_name_exists = "Category name already exists.";
    var slug_required = "Slug is required.";
    var slug_max = "Slug cannot exceed 100 characters.";
    var slug_exists = "Slug already exists.";
    var lg_category_add_title = "Add Category";
    var lg_category_edit_title = "Edit Category";
    var lg_save = "Save";

    function listBlogCategory(langCode = "") {
        $.ajax({
            url: "/api/admin/blogs/list-category",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
                language_code: langCode,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === "200") {
                    let categories = response.data;
                    let tableBody = "";

                    if (categories.length === 0) {
                        $("#blogCategoryTable").DataTable().destroy();
                        tableBody += `
                            <tr>
                                <td colspan="5" class="text-center">${$(
                                    "#blogCategoryTable"
                                ).data("empty")}</td>
                            </tr>`;
                    } else {
                        categories.forEach((category, index) => {
                            let checkedVal =
                                category.status == 1 ? "checked" : "";
                            tableBody += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${category.name}</td>
                                    ${
                                        $("#has_permission").data("edit") == 1
                                            ? `<td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input category_status" ${checkedVal} type="checkbox" role="switch" id="switch-sm" data-category-id = ${category.id}>
                                        </div>
                                    </td>`
                                            : ""
                                    }
                                    ${
                                        $("#has_permission").data("visible") ==
                                        1
                                            ? `<td>
                                            <li style="list-style: none;">
                                                ${
                                                    $("#has_permission").data(
                                                        "edit"
                                                    ) == 1
                                                        ? `<a class="edit_category_btn"
                                                        href="#"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#blog_category_modal"
                                                        data-id="${category.id}"
                                                        data-category_name="${
                                                            category.name
                                                        }"
                                                        data-slug="${
                                                            category.slug
                                                        }">
                                                        <i class="ti ti-pencil fs-20" data-tooltip="tooltip" title="${$(
                                                            "#blogCategoryTable"
                                                        ).data("edit")}"></i>
                                                    </a>`
                                                        : ""
                                                }
                                                ${
                                                    $("#has_permission").data(
                                                        "delete"
                                                    ) == 1
                                                        ? `<a class="delete delete_category_btn" href="#" data-bs-toggle="modal" data-bs-target="#delete_blog_category_modal" data-del-id="${
                                                              category.id
                                                          }">
                                                        <i class="ti ti-trash m-3 fs-20" data-tooltip="tooltip" title="${$(
                                                            "#blogCategoryTable"
                                                        ).data("delete")}"></i>
                                                    </a>`
                                                        : ""
                                                }
                                            </li>
                                        </td>`
                                            : ""
                                    }
                                </tr>
                            `;
                        });
                    }

                    $("#blogCategoryTable tbody").html(tableBody);
                    initTooltip();

                    $("#loader-table").addClass("d-none");
                    $(".label-loader, .input-loader").hide();
                    $(
                        "#blogCategoryTable, .real-label, .real-input"
                    ).removeClass("d-none");

                    if (
                        categories.length != 0 &&
                        !$.fn.DataTable.isDataTable("#blogCategoryTable")
                    ) {
                        $("#blogCategoryTable").DataTable({
                            ordering: true,
                            language: datatableLang,
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
                } else {
                    toastr.error("An error occurred while fetching.");
                }
            },
        });
    }

    $(document).ready(function () {
        var langCode = $("body").data("lang");

        listBlogCategory(langCode);

        $("#add_category_btn").on("click", function () {
            $(".category_modal_title").html(lg_category_add_title);
            $("#method").val("add");
            $("#id").val("");
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid is-valid");
            $("#blogCategoryForm").trigger("reset");
            $("#translate_container").addClass("d-none");

            var langCode = $("body").data("lang");
            languageTranslate("", langCode);
        });

        $("#blogCategoryForm").validate({
            rules: {
                category_name: {
                    required: true,
                    maxlength: 100,
                    remote: {
                        url: "/api/admin/blogs/check-unique-category-name",
                        type: "post",
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                        },
                        data: {
                            category_name: function () {
                                return $("#category_name").val();
                            },
                            id: function () {
                                return $(
                                    '#blogCategoryForm input[name="id"]'
                                ).val();
                            },
                            language_id: function () {
                                return $("#language_id").val();
                            },
                            method: function () {
                                return $(
                                    '#blogCategoryForm input[name="method"]'
                                ).val();
                            },
                        },
                    },
                },
                slug: {
                    required: true,
                    maxlength: 100,
                    remote: {
                        url: "/api/admin/blogs/check-unique-category-slug",
                        type: "post",
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                        },
                        data: {
                            slug: function () {
                                return $("#slug").val();
                            },
                            id: function () {
                                return $(
                                    '#blogCategoryForm input[name="id"]'
                                ).val();
                            },
                            method: function () {
                                return $(
                                    '#blogCategoryForm input[name="method"]'
                                ).val(); // Pass 'add' or 'update'
                            },
                            language_id: function () {
                                return $("#language_id").val();
                            },
                        },
                    },
                },
            },
            messages: {
                category_name: {
                    required: category_name_required,
                    maxlength: category_name_max,
                    remote: category_name_exists,
                },
                slug: {
                    required: slug_required,
                    maxlength: slug_max,
                    remote: slug_exists,
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
                submitFormAjax(form);
            },
        });

        function submitFormAjax(form) {
            var formData = new FormData(form);

            $.ajax({
                url: "/api/admin/blogs/save-category",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                cache: false,
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                beforeSend: function () {
                    $(".save_category_btn")
                        .attr("disabled", true)
                        .html(
                            '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                        );
                },
            })
                .done(function (response) {
                    if ($.fn.DataTable.isDataTable("#blogCategoryTable")) {
                        $("#blogCategoryTable").DataTable().destroy();
                    }
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid is-valid");
                    $(".save_category_btn")
                        .removeAttr("disabled")
                        .html(lg_save);
                    if (response.code === 200) {
                        toastr.success(response.message);
                        $("#blog_category_modal").modal("hide");
                        var langCode = $("body").data("lang");
                        listBlogCategory(langCode);
                    }
                })
                .fail(function (error) {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid is-valid");
                    $(".save_category_btn")
                        .removeAttr("disabled")
                        .html(lg_save);
                    if (error.status == 422) {
                        $.each(error.responseJSON.errors, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    }
                });
        }

        $(document).on("click", ".edit_category_btn", function () {
            $("#blogCategoryForm").trigger("reset");
            $(".category_modal_title").html(lg_category_edit_title);
            $("#method").val("update");
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid is-valid");

            var id = $(this).data("id");
            $("#id").val(id);
            var category_name = $(this).data("category_name");
            var slug = $(this).data("slug");

            $("#translate_container").removeClass("d-none");
            var langId = $("#language_id").val();

            languageTranslate(langId);
            getCategory(id, langId);
        });

        $(document).on("click", ".delete_category_btn", function () {
            var id = $(this).data("del-id");
            $("#delete_id").val(id);
        });

        $(document).on("click", ".delete_category_confirm", function (e) {
            e.preventDefault();
            var delId = $("#delete_id").val();

            $.ajax({
                url: "/api/admin/blogs/delete-category",
                type: "POST",
                data: {
                    id: delId,
                    language_code: langCode,
                },
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    $("#blogCategoryTable").DataTable().destroy();
                    if (response.code === 200) {
                        toastr.success(response.message);
                        $("#delete_blog_category_modal").modal("hide");
                        listBlogCategory(langCode);
                    }
                },
                error: function (xhr, status, error) {
                    toastr.error(
                        "An error occurred while deleting blog category."
                    );
                },
            });
        });

        $(document).on("change", ".category_status", function () {
            let id = $(this).data("category-id");
            let status = $(this).is(":checked") ? 1 : 0;

            var data = {
                id: id,
                status: status,
                language_code: langCode,
            };

            $.ajax({
                url: "/api/admin/blogs/change-category-status",
                type: "POST",
                data: data,
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                        listBlogCategory(langCode);
                    }
                },
                error: function (error) {
                    toastr.error("An error occurred while updating status");
                },
            });
        });
    });

    $("#language_id").on("change", function () {
        var langId = $(this).val();
        var id = $("#id").val();

        languageTranslate(langId);
        getCategory(id, langId);
    });

    function getCategory(id, lang_id = "", langCode = "") {
        $.ajax({
            url: "/api/admin/blogs/get-blog-category",
            type: "POST",
            data: {
                id: id,
                language_id: lang_id,
                language_code: langCode,
            },
            dataType: "json",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    let category = response.data;

                    if (category) {
                        $("#category_name").val(category.name);
                        $("#slug").val(category.slug);
                    } else {
                        $("#category_name").val("");
                        $("#slug").val("");
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

    function languageTranslate(lang_id = "", langCode = "") {
        $.ajax({
            url: "/api/translate",
            type: "POST",
            dataType: "json",
            data: {
                language_id: lang_id,
                language_code: langCode,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                const trans = response.translated_values;

                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid is-valid");

                if (response.code === 200 && Object.keys(trans).length > 0) {
                    $('label[for="category_name"]').html(
                        `${trans.category_name}<span class="text-danger"> *</span>`
                    );
                    $('label[for="slug"]').html(
                        `${trans.Slug}<span class="text-danger"> *</span>`
                    );

                    $("#category_name").attr(
                        "placeholder",
                        trans.enter_category_name
                    );
                    $("#slug").attr("placeholder", trans.enter_slug);
                    $(".cancelbtn").text(trans.Cancel);
                    $(".save_category_btn").text(trans.Save);
                    $(".lang_title").text(trans.available_translations);
                    lg_save = trans.Save;
                    lg_category_add_title = trans.add_category;
                    lg_category_edit_title = trans.edit_category;

                    if ($("#method").val() == "update") {
                        $(".category_modal_title").text(trans.edit_category);
                    } else {
                        $(".category_modal_title").text(trans.add_category);
                    }

                    $("#blogCategoryForm").validate().settings.messages = {
                        category_name: {
                            required: trans.category_name_required,
                            maxlength: trans.category_name_max,
                            remote: trans.category_name_exists,
                        },
                        slug: {
                            required: trans.slug_required,
                            maxlength: trans.slug_max,
                            remote: trans.slug_exists,
                        },
                    };
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }
}

if (pageValue === "admin.blog-post") {
    var categoryValue = "";
    var lg_post_add_title = "Add Post";
    var lg_post_edit_title = "Edit Post";
    var lg_save = "Save";

    function listPosts(langCode = "") {
        $.ajax({
            url: "/api/admin/blogs/list-post",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
                language_code: langCode,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === "200") {
                    let posts = response.data;
                    let tableBody = "";
                    if (posts.length === 0) {
                        $("#blogPostTable").DataTable().destroy();
                        tableBody += `
                            <tr>
                                <td colspan="6" class="text-center">${$(
                                    "#blogPostTable"
                                ).data("empty")}</td>
                            </tr>`;
                    } else {
                        posts.forEach((post, index) => {
                            let checkedVal = post.status == 1 ? "checked" : "";
                            tableBody += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${post.title}</td>
                                    <td>${post.category.name}</td>
                                    <td>
                                        <span class="badge ${
                                            post.popular == "1"
                                                ? "badge-soft-success"
                                                : "badge-soft-danger"
                                        } d-inline-flex align-items-center">
                                            <i class="ti ti-circle-filled fs-5 me-1"></i>${
                                                post.popular == "1"
                                                    ? $("#blogPostTable").data(
                                                          "yes"
                                                      )
                                                    : $("#blogPostTable").data(
                                                          "no"
                                                      )
                                            }
                                        </span>
                                    </td>
                                    ${
                                        $("#has_permission").data("edit") == 1
                                            ? `<td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input post_status" ${checkedVal} type="checkbox" role="switch" id="switch-sm" data-post-id = ${post.id}>
                                            </div>
                                        </td>`
                                            : ""
                                    }
                                    ${
                                        $("#has_permission").data("visible") ==
                                        1
                                            ? `<td>
                                            <li style="list-style: none;">
                                            ${
                                                $("#has_permission").data(
                                                    "edit"
                                                ) == 1
                                                    ? `<a class="edit_post_btn"
                                                    href="#"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#blog_post_modal"
                                                    data-id="${post.id}">
                                                    <i class="ti ti-pencil fs-20" data-tooltip="tooltip" title="${$(
                                                        "#blogPostTable"
                                                    ).data("edit")}"></i>
                                                </a>`
                                                    : ""
                                            }
                                            ${
                                                $("#has_permission").data(
                                                    "delete"
                                                ) == 1
                                                    ? `<a class="delete delete_post_btn" href="#" data-bs-toggle="modal" data-bs-target="#delete_blog_post_modal" data-del-id="${
                                                          post.id
                                                      }">
                                                    <i class="ti ti-trash m-3 fs-20" data-tooltip="tooltip" title="${$(
                                                        "#blogPostTable"
                                                    ).data("delete")}"></i>
                                                </a>`
                                                    : ""
                                            }
                                            </li>
                                        </td>`
                                            : ""
                                    }
                                </tr>
                            `;
                        });
                    }

                    $("#blogPostTable tbody").html(tableBody);
                    initTooltip();

                    $("#loader-table").addClass("d-none");
                    $(".label-loader, .input-loader").hide();
                    $("#blogPostTable, .real-label, .real-input").removeClass(
                        "d-none"
                    );

                    if (
                        posts.length != 0 &&
                        !$.fn.DataTable.isDataTable("#blogPostTable")
                    ) {
                        $("#blogPostTable").DataTable({
                            ordering: true,
                            language: datatableLang,
                        });
                    }
                }
            },
            error: function (error) {
                if (error.responseJSON.code === 500) {
                    toastr.error("An error occurred while fetching.");
                }
            },
        });
    }

    function getBlogCategory(lang_id = "", langCode = "") {
        $.ajax({
            url: "/api/admin/blogs/get-category",
            type: "POST",
            data: {
                language_id: lang_id,
                language_code: langCode,
            },
            dataType: "json",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === "200") {
                    let categories = response.data;
                    let categoryDropdown = $("#category");
                    categoryDropdown.find("option:not(:first)").remove();

                    categories.forEach((category) => {
                        categoryDropdown.append(
                            `<option value="${category.id}">${category.name}</option>`
                        );
                    });
                    categoryDropdown.val(categoryValue).trigger("change");
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

    function initSummernote() {
        const defaultOptions = {
            height: 300,
            minHeight: 300,
            maxHeight: 500,
            focus: true,
            toolbar: [
                ["style", ["style"]],
                ["font", ["bold", "italic", "underline", "clear"]],
                ["fontname", ["fontname"]],
                ["color", ["color"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["table", ["table"]],
                ["insert", ["link", "picture", "video"]],
                ["view", ["fullscreen", "codeview", "help"]],
            ],
        };
        $(".summernote-editor").summernote(defaultOptions);
    }

    function getPost(id, langId = null, langCode = "") {
        $.ajax({
            url: "/api/admin/blogs/get-post",
            type: "POST",
            dataType: "json",
            data: {
                id: id,
                language_id: langId,
                language_code: langCode,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === "200") {
                    let post = response.data;

                    if (post) {
                        $("#imagePreview").attr("src", post.image);
                        $("#title").val(post.title);
                        $("#slug").val(post.slug);
                        $("#description").summernote("code", post.description);

                        $("#tags").tagsinput("removeAll");
                        if (post.tags) {
                            let normalizedTags = post.tags
                                .replace(/،/g, ",")
                                .replace(/\s*,\s*/g, ",")
                                .trim();
                            let tagsArray = normalizedTags.split(",");
                            tagsArray.forEach((tag) => {
                                if (tag.trim()) {
                                    $("#tags").tagsinput("add", tag.trim());
                                }
                            });
                        }

                        $("#seo_title").val(post.seo_title);
                        $("#seo_description").val(post.seo_description);
                        $("#status").prop("checked", post.status == 1);
                        $("#popular").prop("checked", post.popular == 1);
                        $("#category").val(post.category).trigger("change");
                        categoryValue = post.category;
                    } else {
                        $("#title").val("");
                        $("#slug").val("");
                        $("#description").summernote("code", "");
                        $("#imagePreview").hide();
                        $(".upload_icon").show();
                        $("#tags").tagsinput("removeAll");
                        $("#seo_title").val("");
                        $("#seo_description").val("");
                        $("#status").prop("checked", true);
                        $("#popular").prop("checked", false);
                        categoryValue = "";
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

    $(document).ready(function () {
        var langCode = $("body").data("lang");

        initSummernote();
        listPosts(langCode);
        getBlogCategory("", langCode);

        $("#add_post_btn").on("click", function () {
            $("#blogPostForm").trigger("reset");
            $(".post_modal_title").html(lg_post_add_title);
            $("#method").val("add");
            $("#id").val("");
            $("#imagePreview").hide();
            $(".upload_icon").show();
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid is-valid");
            $(".form-select").removeClass("is-invalid is-valid");
            $("#blogPostForm").trigger("reset");
            $("#description").summernote("code", "");
            $("#tags").tagsinput("removeAll");
            $("#translate_container").addClass("d-none");

            languageTranslate("", langCode);

            categoryValue = "";
            $("#category").find("option:not(:first)").remove();
            getBlogCategory("", langCode);
        });

        $("#image").on("change", function (event) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $("#imagePreview").attr("src", e.target.result).show();
                $(".upload_icon").hide();
            };
            reader.readAsDataURL(event.target.files[0]);
        });

        $("#blogPostForm").validate({
            rules: {
                title: {
                    required: true,
                    maxlength: 100,
                    remote: {
                        url: "/api/admin/blogs/check-unique-post-title",
                        type: "post",
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                        },
                        data: {
                            title: function () {
                                return $("#title").val();
                            },
                            id: function () {
                                return $(
                                    '#blogPostForm input[name="id"]'
                                ).val();
                            },
                            language_id: function () {
                                return $("#language_id").val();
                            },
                            parent_id: function () {
                                let method = $(
                                    '#blogPostForm input[name="method"]'
                                ).val();
                                return method === "add" ? 0 : $("#id").val();
                            },
                            method: function () {
                                return $(
                                    '#blogPostForm input[name="method"]'
                                ).val(); // Pass 'add' or 'update'
                            },
                        },
                    },
                },
                slug: {
                    required: true,
                    maxlength: 100,
                    remote: {
                        url: "/api/admin/blogs/check-unique-post-slug",
                        type: "post",
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                        },
                        data: {
                            slug: function () {
                                return $("#slug").val();
                            },
                            id: function () {
                                return $(
                                    '#blogPostForm input[name="id"]'
                                ).val();
                            },
                            language_id: function () {
                                return $("#language_id").val();
                            },
                            parent_id: function () {
                                let method = $(
                                    '#blogPostForm input[name="method"]'
                                ).val();
                                return method === "add" ? 0 : $("#id").val();
                            },
                            method: function () {
                                return $(
                                    '#blogPostForm input[name="method"]'
                                ).val();
                            },
                        },
                    },
                },
                image: {
                    required: function () {
                        return (
                            $("#blogPostForm")
                                .find('input[name="method"]')
                                .val() === "add"
                        );
                    },
                    extension: "jpeg|jpg|png|webp",
                    filesize: 2048,
                },
                category: {
                    required: true,
                },
            },
            messages: {
                title: {
                    required: "Title is required.",
                    remote: "Title already exists.",
                },
                slug: {
                    required: "Slug is required.",
                    remote: "Slug already exists.",
                },
                image: {
                    required: "Image is required.",
                    extension:
                        "Only JPEG, JPG, PNG and WEBP formats are allowed.",
                    filesize: "Image size must be less than 2MB.",
                },
                category: {
                    required: "Category is required.",
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
                $(element).removeClass("is-invalid");
                $(element).addClass("is-valid");
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
                var formData = new FormData(form);
                formData.append("status", $("#status").is(":checked") ? 1 : 0);
                formData.append(
                    "popular",
                    $("#popular").is(":checked") ? 1 : 0
                );
                formData.append("user_id", localStorage.getItem("user_id"));

                $.ajax({
                    url: "/api/admin/blogs/save-post",
                    type: "POST",
                    data: formData,
                    enctype: "multipart/form-data",
                    contentType: false,
                    processData: false,
                    cache: false,
                    headers: {
                        Authorization:
                            "Bearer " + localStorage.getItem("admin_token"),
                        Accept: "application/json",
                    },
                    beforeSend: function () {
                        $(".save_post_btn").attr("disabled", true);
                        $(".save_post_btn").html(
                            '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                        );
                    },
                })
                    .done((response, statusText, xhr) => {
                        if ($.fn.DataTable.isDataTable("#blogPostTable")) {
                            $("#blogPostTable").DataTable().destroy();
                        }
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".form-select").removeClass("is-invalid is-valid");
                        $(".save_post_btn").removeAttr("disabled");
                        $(".save_post_btn").html(
                            $(".save_post_btn").data("save")
                        );
                        if (response.code === 200) {
                            toastr.success(response.message);
                            $("#blog_post_modal").modal("hide");
                            var code = $("body").data("lang");
                            listPosts(code);
                        }
                    })
                    .fail((error) => {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".form-select").removeClass("is-invalid is-valid");
                        $(".save_post_btn").removeAttr("disabled");
                        $(".save_post_btn").html(
                            $(".save_post_btn").data("save")
                        );
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
                    });
            },
        });

        $.validator.addMethod(
            "maxword",
            function (value, element, param) {
                return str_word_count(value) <= param;
            },
            "Description should contain no more than 300 words."
        );

        $.validator.addMethod(
            "filesize",
            function (value, element, param) {
                if (element.files.length === 0) return true;
                return element.files[0].size <= param * 1024;
            },
            "File size must be less than {0} KB."
        );

        function str_word_count(str) {
            return str.trim().split(/\s+/).length;
        }

        $(document).on("click", ".edit_post_btn", function () {
            $("#blogPostForm").trigger("reset");
            $(".post_modal_title").html(lg_post_edit_title);
            $("#method").val("update");
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid is-valid");
            $(".form-select").removeClass("is-invalid is-valid");
            $(".upload_icon").hide();
            $("#imagePreview").show();
            $("#translate_container").removeClass("d-none");

            var id = $(this).data("id");
            $("#id").val(id);
            var langId = $("#language_id").val();

            languageTranslate("", langCode);
            getPost(id, langId);
            getBlogCategory("", langCode);
        });

        $(document).on("click", ".delete_post_btn", function () {
            var id = $(this).data("del-id");
            $("#delete_id").val(id);
        });

        $(document).on("click", ".delete_post_confirm", function (e) {
            e.preventDefault();
            var delId = $("#delete_id").val();

            $.ajax({
                url: "/api/admin/blogs/delete-post",
                type: "POST",
                data: {
                    id: delId,
                    language_code: langCode,
                },
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    $("#blogPostTable").DataTable().destroy();
                    if (response.code === 200) {
                        toastr.success(response.message);
                        $("#delete_blog_post_modal").modal("hide");
                        listPosts(langCode);
                    }
                },
                error: function (xhr, status, error) {
                    toastr.error("An error occurred while deleting blog post.");
                },
            });
        });

        $(document).on("change", ".post_status", function () {
            let id = $(this).data("post-id");
            let status = $(this).is(":checked") ? 1 : 0;

            var data = {
                id: id,
                status: status,
                language_code: langCode,
            };

            $.ajax({
                url: "/api/admin/blogs/change-post-status",
                type: "POST",
                data: data,
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                        listPosts(langCode);
                    }
                },
                error: function (error) {
                    toastr.error("An error occurred while updating status");
                },
            });
        });
    });

    $("#language_id").on("change", function () {
        var langId = $(this).val();
        var id = $("#id").val();

        getPost(id, langId);
        languageTranslate(langId);
        getBlogCategory(langId);
    });

    function languageTranslate(lang_id, langCode = "") {
        $.ajax({
            url: "/api/translate",
            type: "POST",
            dataType: "json",
            data: {
                language_id: lang_id,
                language_code: langCode,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                const trans = response.translated_values;
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid is-valid");
                $(".form-select").removeClass("is-invalid is-valid");

                if (response.code === 200 && Object.keys(trans).length > 0) {
                    $("#imageNote").text(trans.image_size_note);
                    $('label[for="image"]').html(
                        `${trans.image}<span class="text-danger"> *</span>`
                    );
                    $('label[for="title"]').html(
                        `${trans.Title}<span class="text-danger"> *</span>`
                    );
                    $('label[for="slug"]').html(
                        `${trans.Slug}<span class="text-danger"> *</span>`
                    );
                    $('label[for="category"]').html(
                        `${trans.Category}<span class="text-danger"> *</span>`
                    );
                    $('label[for="description"]').html(
                        `${trans.description}<span class="text-danger"> *</span>`
                    );
                    $('label[for="tags"]').html(`${trans.tags}`);
                    $('label[for="seo_title"]').html(`${trans.seo_title}`);
                    $('label[for="seo_description"]').html(
                        `${trans.seo_description}`
                    );

                    $("#category")
                        .find("option:first")
                        .text(trans.select_category);

                    $("#title").attr("placeholder", trans.enter_title);
                    $("#slug").attr("placeholder", trans.enter_slug);
                    $("#tags")
                        .tagsinput("input")
                        .attr("placeholder", trans.enter_tag);
                    $("#seo_title").attr("placeholder", trans.enter_seo_title);
                    $("#status_text").text(trans.Status);
                    $("#popular_text").text(trans.popular_text);
                    $("#upload_text").text(trans.upload);
                    $(".cancelbtn").text(trans.Cancel);
                    $(".save_post_btn").text(trans.Save);
                    $(".lang_title").text(trans.available_translations);
                    $(".save_post_btn").data("save", trans.Save);

                    if ($("#method").val() == "update") {
                        $(".post_modal_title").text(trans.edit_post);
                    } else {
                        $(".post_modal_title").text(trans.add_post);
                    }

                    $("#blogPostForm").validate().settings.messages = {
                        title: {
                            required: trans.title_required,
                            remote: trans.title_exists,
                        },
                        slug: {
                            required: trans.slug_required,
                            remote: trans.slug_exists,
                        },
                        image: {
                            required: trans.image_required,
                            extension: trans.client_image_extension,
                            filesize: trans.image_filesize,
                        },
                        category: {
                            required: trans.category_required,
                        },
                    };
                }
            },
            error: function (error) {
                $("#translate_btn").removeAttr("disabled").html("Translate");
                toastr.error(error.responseJSON.message);
            },
        });
    }
}

if (pageValue === "admin.roles-permissions") {
    $(document).ready(function () {
        listRoles();
    });

    function listRoles() {
        $.ajax({
            url: "/api/role/list",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "asc",
                user_id: localStorage.getItem("user_id"),
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === "200") {
                    let roles = response.data;
                    let tableBody = "";
                    if (roles.length === 0) {
                        $("#roleTable").DataTable().destroy();
                        tableBody += `
                            <tr>
                                <td colspan="4" class="text-center">${$(
                                    "#roleTable"
                                ).data("empty")}</td>
                            </tr>`;
                    } else {
                        roles.forEach((role, index) => {
                            let checkedVal = role.status == 1 ? "checked" : "";
                            tableBody += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${role.role_name}</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input role_status" ${checkedVal} type="checkbox" role="switch" id="switch-sm" data-id = ${
                                role.id
                            }>
                                        </div>
                                    </td>
                                    ${
                                        $("#has_permission").data("visible") ==
                                        1
                                            ? `<td>
                                        <div class="d-flex align-items-center">
                                            ${
                                                $("#has_permission").data(
                                                    "edit"
                                                ) == 1
                                                    ? `<a href="#"
                                                    class="edit_role_btn"
                                                    data-bs-toggle="modal" data-bs-target="#role_modal"
                                                    data-id="${role.id}"
                                                    data-role_name="${
                                                        role.role_name
                                                    }">
                                                    <i class="ti ti-pencil m-3 fs-20" data-tooltip="tooltip" title="${$(
                                                        "#roleTable"
                                                    ).data("edit")}"></i>
                                                </a>`
                                                    : ""
                                            }
                                            ${
                                                $("#has_permission").data(
                                                    "edit"
                                                ) == 1
                                                    ? `<a href="#"
                                                    class="permission_btn"
                                                    data-bs-toggle="modal" data-bs-target="#permission_modal" data-id="${
                                                        role.id
                                                    }">
                                                    <i class="ti ti-shield m-3 fs-20" data-tooltip="tooltip" title="${$(
                                                        "#roleTable"
                                                    ).data("permission")}"></i>
                                                </a>`
                                                    : ""
                                            }
                                            ${
                                                $("#has_permission").data(
                                                    "delete"
                                                ) == 1
                                                    ? `<a href="#"
                                                    class="delete_role_btn"
                                                    data-bs-toggle="modal" data-bs-target="#delete_role_modal" data-id="${
                                                        role.id
                                                    }">
                                                    <i class="ti ti-trash m-3 fs-20" data-tooltip="tooltip" title="${$(
                                                        "#roleTable"
                                                    ).data("delete")}"></i>
                                                </a>`
                                                    : ""
                                            }
                                        </div>
                                    </td>`
                                            : ""
                                    }
                                </tr>
                            `;
                        });
                    }

                    $("#roleTable tbody").html(tableBody);
                    initTooltip();

                    $("#loader-table").addClass("d-none");
                    $(".label-loader, .input-loader").hide();
                    $("#roleTable, .real-label, .real-input").removeClass(
                        "d-none"
                    );

                    if (
                        roles.length != 0 &&
                        !$.fn.DataTable.isDataTable("#roleTable")
                    ) {
                        $("#roleTable").DataTable({
                            ordering: true,
                            language: datatableLang,
                        });
                    }
                }
            },
            error: function (error) {
                if (error.responseJSON.code === 500) {
                    toastr.error(error.responseJSON.message);
                }
            },
        });
    }

    $(document).on("click", "#add_role_btn", function () {
        $(".role_modal_title").html($(".role_modal_title").data("add_text"));
        $("#method").val("add");
        $("#id").val("");
        $(".error-text").text("");
        $(".form-control").removeClass("is-invalid is-valid");
        $("#roleForm")[0].reset();
    });

    $(document).ready(function () {
        $("#roleForm").validate({
            rules: {
                role_name: {
                    required: true,
                    maxlength: 150,
                    remote: {
                        url: "/api/role/check-unique",
                        type: "post",
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                "content"
                            ),
                        },
                        data: {
                            role_name: function () {
                                return $("#role_name").val();
                            },
                            id: function () {
                                return $('#roleForm input[name="id"]').val();
                            },
                            user_id: function () {
                                return $("#user_id").val();
                            },
                        },
                    },
                },
            },
            messages: {
                role_name: {
                    required: $("#role_name_error").data("required"),
                    maxlength: $("#role_name_error").data("max"),
                    remote: $("#role_name_error").data("exists"),
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
                var formData = new FormData(form);
                formData.append("created_by", localStorage.getItem("user_id"));

                $.ajax({
                    url: "/api/role/save",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    cache: false,
                    headers: {
                        Authorization:
                            "Bearer " + localStorage.getItem("admin_token"),
                        Accept: "application/json",
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    beforeSend: function () {
                        $(".role_save_btn").attr("disabled", true);
                        $(".role_save_btn").html(
                            '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                        );
                    },
                })
                    .done((response) => {
                        if ($.fn.DataTable.isDataTable("#roleTable")) {
                            $("#roleTable").DataTable().destroy();
                        }
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".role_save_btn")
                            .removeAttr("disabled")
                            .html($(".role_save_btn").data("save"));
                        if (response.code === 200) {
                            toastr.success(response.message);
                            $("#role_modal").modal("hide");
                            listRoles();
                        }
                    })
                    .fail((error) => {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".role_save_btn")
                            .removeAttr("disabled")
                            .html($(".role_save_btn").data("save"));
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
                    });
            },
        });
    });

    $(document).on("click", ".edit_role_btn", function () {
        $(".role_modal_title").html($(".role_modal_title").data("edit_text"));
        $("#method").val("update");
        $(".error-text").text("");
        $(".form-control").removeClass("is-invalid is-valid");

        var id = $(this).data("id");
        var role_name = $(this).data("role_name");

        $("#id").val(id);
        $("#role_name").val(role_name);
    });

    $(document).on("click", ".delete_role_btn", function () {
        var id = $(this).data("id");
        $(".delete_role_confirm").data("id", id);
    });

    $(document).on("click", ".delete_role_confirm", function (e) {
        e.preventDefault();
        var id = $(this).data("id");

        $.ajax({
            url: "/api/role/delete",
            type: "POST",
            data: {
                id: id,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#roleTable").DataTable().destroy();
                if (response.code === 200) {
                    toastr.success(response.message);
                    $("#delete_role_modal").modal("hide");
                    listRoles();
                }
            },
            error: function (error) {
                if (error.responseJSON.code === 500) {
                    toastr.error(error.responseJSON.message);
                } else {
                    toastr.error("An error occurred while deleting role.");
                }
            },
        });
    });

    $(document).on("change", ".role_status", function () {
        let id = $(this).data("id");
        let status = $(this).is(":checked") ? 1 : 0;

        var data = {
            id: id,
            status: status,
        };

        $.ajax({
            url: "/api/role/change-status",
            type: "POST",
            data: data,
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    toastr.success(response.message);
                    listRoles();
                }
            },
            error: function (error) {
                if (error.responseJSON.code === 500) {
                    toastr.error(error.responseJSON.message);
                } else {
                    toastr.error("An error occurred while fetching.");
                }
            },
        });
    });

    $(document).on("click", ".permission_btn", function () {
        var id = $(this).data("id");
        $("#savePermissions").data("role_id", id);
        if ($.fn.DataTable.isDataTable("#permissionTable")) {
            $("#permissionTable").DataTable().destroy();
        }

        $.ajax({
            url: "/api/permission/list",
            type: "POST",
            dataType: "json",
            data: {
                id: id,
                order_by: "asc",
                user_id: localStorage.getItem("user_id"),
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === "200") {
                    let permissions = response.data;
                    let tableBody = "";

                    if (permissions.length === 0) {
                        if ($.fn.DataTable.isDataTable("#permissionTable")) {
                            $("#permissionTable").DataTable().destroy();
                        }
                        tableBody += `
                            <tr>
                                <td colspan="7" class="text-center">${$(
                                    "#permissionTable"
                                ).data("empty")}</td>
                            </tr>`;
                    } else {
                        permissions.forEach((permission, index) => {
                            let create =
                                permission.create == 1 ? "checked" : "";
                            let view = permission.view == 1 ? "checked" : "";
                            let edit = permission.edit == 1 ? "checked" : "";
                            let Delete =
                                permission.delete == 1 ? "checked" : "";
                            let allowAll =
                                permission.allow_all == 1 ? "checked" : "";

                            tableBody += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td data-id="${permission.id}">${
                                permission.module
                            }</td>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox" class="perm-create" ${create}>
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox" class="perm-view" ${view}>
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox" class="perm-edit" ${edit}>
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox" class="perm-delete" ${Delete}>
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox" class="perm-allow-all" ${allowAll}>
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                </tr>
                            `;
                        });
                    }

                    $("#permissionTable tbody").html(tableBody);
                    $("#loader-table2").addClass("d-none");
                    $(".label-loader, .input-loader").hide();
                    $("#permissionTable, .real-label, .real-input").removeClass(
                        "d-none"
                    );

                    if (
                        permissions.length != 0 &&
                        !$.fn.DataTable.isDataTable("#permissionTable")
                    ) {
                        $("#permissionTable").DataTable({
                            ordering: true,
                            language: datatableLang,
                        });
                    }

                    $(document).on("change", ".perm-allow-all", function () {
                        let row = $(this).closest("tr");
                        let isChecked = $(this).is(":checked");

                        row.find(
                            ".perm-create, .perm-view, .perm-edit, .perm-delete"
                        ).prop("checked", isChecked);
                    });

                    $(document).on(
                        "change",
                        ".perm-create, .perm-view, .perm-edit, .perm-delete",
                        function () {
                            let row = $(this).closest("tr");
                            let allChecked =
                                row.find(
                                    ".perm-create, .perm-view, .perm-edit, .perm-delete"
                                ).length ===
                                row.find(
                                    ".perm-create:checked, .perm-view:checked, .perm-edit:checked, .perm-delete:checked"
                                ).length;

                            row.find(".perm-allow-all").prop(
                                "checked",
                                allChecked
                            );
                        }
                    );
                }
            },
            error: function (error) {
                if (error.responseJSON.code === 500) {
                    toastr.error(error.responseJSON.message);
                }
            },
        });
    });

    $("#savePermissions").on("click", function (e) {
        e.preventDefault();

        let roleId = $(this).data("role_id");
        let table = $("#permissionTable").DataTable();
        let formData = new FormData();

        formData.append("role_id", roleId);

        table.rows().every(function (rowIdx, tableLoop, rowLoop) {
            let row = $(this.node());
            let index = rowIdx;

            formData.append(
                `permissions[${index}][id]`,
                row.find("td:eq(1)").data("id")
            );
            formData.append(
                `permissions[${index}][module]`,
                row.find("td:eq(1)").text().trim()
            );
            formData.append(
                `permissions[${index}][create]`,
                row.find(".perm-create").is(":checked") ? 1 : 0
            );
            formData.append(
                `permissions[${index}][view]`,
                row.find(".perm-view").is(":checked") ? 1 : 0
            );
            formData.append(
                `permissions[${index}][edit]`,
                row.find(".perm-edit").is(":checked") ? 1 : 0
            );
            formData.append(
                `permissions[${index}][delete]`,
                row.find(".perm-delete").is(":checked") ? 1 : 0
            );
            formData.append(
                `permissions[${index}][allow_all]`,
                row.find(".perm-allow-all").is(":checked") ? 1 : 0
            );
        });

        $.ajax({
            url: "/api/permission/update",
            type: "POST",
            dataType: "json",
            processData: false,
            contentType: false,
            data: formData,
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $("#savePermissions").attr("disabled", true);
                $("#savePermissions").html(
                    '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                );
            },
            success: function (response) {
                if ($.fn.DataTable.isDataTable("#permissionTable")) {
                    $("#permissionTable").DataTable().destroy();
                }
                $("#savePermissions").removeAttr("disabled");
                $("#savePermissions").html($("#savePermissions").data("save"));
                if (response.code === 200) {
                    toastr.success(response.message);
                    $("#permission_modal").modal("hide");
                }
            },
            error: function (error) {
                $("#savePermissions").removeAttr("disabled");
                $("#savePermissions").html($("#savePermissions").data("save"));
                if (error.code === 500) {
                    toastr.error(error.message);
                } else {
                    toastr.error(
                        "An error occurred while updating permission."
                    );
                }
            },
        });
    });
}

//validation hide
$(document).ready(function () {
    $(".validate-input").on("input change", function () {
        $(this).removeClass("is-invalid").next(".error-text").hide();
    });
});

//Page-Sections
if (pageValue === "admin.page-section") {
    function section_table() {
        fetchSection(1);
    }

    function fetchSection(page) {
        $.ajax({
            url: "/api/page-builder/section-list",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "asc",
                sort_by: "id",
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === "200") {
                    const backgroundImage = response.data[0].background_image;
                    if (backgroundImage) {
                        $("#background_img").attr("src", backgroundImage);
                    } else {
                        $("#background_img")
                            .attr("src", "")
                            .attr("alt", "No image available");
                    }

                    const thumbnailImage = response.data[0].thumbnail_image;
                    if (thumbnailImage) {
                        $("#thumbnail_img").attr("src", thumbnailImage);
                    } else {
                        $("#thumbnail_img")
                            .attr("src", "")
                            .attr("alt", "No thumbnail available");
                    }
                    $("#datatable_section").DataTable().destroy();
                    populateSectionTable(response.data, response.meta);
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
                } else {
                    toastr.error("An error occurred while fetching.");
                }
            },
        });
    }

    function populateSectionTable(Section, meta) {
        let tableBody = "";

        if (Section.length > 0) {
            Section.forEach((Section, index) => {
                tableBody += `
                    <tr>
                            <td>${index + 1}</td>
                            <td>${Section.name}</td>
                            <td>
                                <span class="badge ${
                                    Section.status == "1"
                                        ? "badge-soft-success"
                                        : "badge-soft-danger"
                                } d-inline-flex align-items-center">
                                    <i class="ti ti-circle-filled fs-5 me-1"></i>${
                                        Section.status == "1"
                                            ? "Active"
                                            : "Inactive"
                                    }
                                </span>
                            </td>
                            ${
                                $("#has_permission").data("visible") == 1
                                    ? `<td>
                                <li style="list-style: none;">
                                    ${
                                        $("#has_permission").data("edit") ==
                                            1 &&
                                        Section.id != 17 &&
                                        Section.id != 18 &&
                                        Section.id != 19 &&
                                        Section.id != 20
                                            ? `<a class="section_data"
                                        href="#"
                                        data-bs-toggle="modal"
                                        data-bs-target="#add_banner_sec"
                                        data-id="${Section.id}"
                                        data-name="${Section.name}"
                                        data-title="${Section.title}"
                                        data-label="${Section.label}"
                                        data-show_search="${Section.show_search}"
                                        data-show_location="${Section.show_location}"
                                        data-popular_search="${Section.popular_search}"
                                        data-provider_count="${Section.provider_count}"
                                        data-services_count="${Section.services_count}"
                                        data-review_count="${Section.review_count}"
                                        data-background_image="${Section.background_image}"
                                        data-thumbnail_image="${Section.thumbnail_image}"
                                        data-category="${Section.category}"
                                        data-feature_category="${Section.feature_category}"
                                        data-popular_category="${Section.popular_category}"
                                        data-service="${Section.service}"
                                        data-feature_service="${Section.feature_service}"
                                        data-popular_service="${Section.popular_service}"
                                        data-product="${Section.product}"
                                        data-feature_product="${Section.feature_product}"
                                        data-popular_product="${Section.popular_product}"
                                        data-faq="${Section.faq}"
                                        data-service_package="${Section.service_package}"
                                        data-about_as="${Section.about_as}"
                                        data-testimonial="${Section.testimonial}"
                                        data-how_it_work="${Section.how_it_work}"
                                        data-blog="${Section.blog}">
                                        <i class="ti ti-pencil fs-20"></i>
                                    </a>`
                                            : ""
                                    }
                                    ${
                                        $("#has_permission").data("delete") == 1
                                            ? `<a class="delete" href="#" data-bs-toggle="modal" data-bs-target="#delete-modal" data-id="${Section.id}">
                                            <i class="ti ti-trash m-3 fs-20"></i>
                                        </a>`
                                            : ""
                                    }
                                </li>
                            </td>`
                                    : ""
                            }
                        </tr>
                    `;
            });
        } else {
            tableBody = `
                    <tr>
                        <td colspan="4" class="text-center">No Section found</td>
                    </tr>
                `;
        }

        $("#datatable_section tbody").html(tableBody);
        $("#loader-table").addClass("d-none");
        $(".label-loader, .input-loader").hide();
        $("#datatable_section, .real-label, .real-input").removeClass("d-none");

        if (
            Section.length != 0 &&
            !$.fn.DataTable.isDataTable("#datatable_section")
        ) {
            $("#datatable_section").DataTable({
                ordering: true,
                language: datatableLang,
            });
        }
    }

    $(document).on("click", ".section_data", function (e) {
        e.preventDefault();

        var ID = $(this).data("id");

        $(
            "#section_id_1, #section_id_2, #section_id_3, #section_id_4, #section_id_5, #section_id_6, #section_id_7, #section_id_8, #section_id_9, #section_id_10, #section_id_11, #section_id_12, #section_id_13, #section_id_14, #section_id_15, #section_id_16"
        ).hide();

        if (ID == 1) {
            $("#section_id_1").show();
            $("#section_id").val($(this).data("id"));
            $("#title").val($(this).data("title"));
            $("#label").val($(this).data("label"));
            $("#show_search").prop("checked", $(this).data("show_search") == 1);
            $("#show_location").prop(
                "checked",
                $(this).data("show_location") == 1
            );
            $("#popular_search").prop(
                "checked",
                $(this).data("popular_search") == 1
            );
            $("#provider_count").prop(
                "checked",
                $(this).data("provider_count") == 1
            );
            $("#services_count").prop(
                "checked",
                $(this).data("services_count") == 1
            );
            $("#review_count").prop(
                "checked",
                $(this).data("review_count") == 1
            );
        } else if (ID == 2) {
            $("#section_id_2").show();
            $("#section_id").val($(this).data("id"));
            $("#category").val($(this).data("category"));
        } else if (ID == 3) {
            $("#section_id_3").show();
            $("#section_id").val($(this).data("id"));
            $("#feature_category").val($(this).data("feature_category"));
        } else if (ID == 4) {
            $("#section_id_4").show();
            $("#section_id").val($(this).data("id"));
            $("#popular_category").val($(this).data("popular_category"));
        } else if (ID == 5) {
            $("#section_id_5").show();
            $("#section_id").val($(this).data("id"));
            $("#service").val($(this).data("service"));
        } else if (ID == 6) {
            $("#section_id_6").show();
            $("#section_id").val($(this).data("id"));
            $("#feature_service").val($(this).data("feature_service"));
        } else if (ID == 7) {
            $("#section_id_7").show();
            $("#section_id").val($(this).data("id"));
            $("#popular_service").val($(this).data("popular_service"));
        } else if (ID == 8) {
            $("#section_id_8").show();
            $("#section_id").val($(this).data("id"));
            $("#product").val($(this).data("product"));
        } else if (ID == 9) {
            $("#section_id_9").show();
            $("#section_id").val($(this).data("id"));
            $("#feature_product").val($(this).data("feature_product"));
        } else if (ID == 10) {
            $("#section_id_10").show();
            $("#section_id").val($(this).data("id"));
            $("#popular_product").val($(this).data("popular_product"));
        } else if (ID == 11) {
            $("#section_id_11").show();
            $("#section_id").val($(this).data("id"));
            $("#faq").val($(this).data("faq"));
        } else if (ID == 12) {
            $("#section_id_12").show();
            $("#section_id").val($(this).data("id"));
            $("#service_package").val($(this).data("service_package"));
        } else if (ID == 13) {
            $("#section_id_13").show();
            $("#section_id").val($(this).data("id"));
            $("#about_as").val($(this).data("about_as"));
        } else if (ID == 14) {
            $("#section_id_14").show();
            $("#section_id").val($(this).data("id"));
            $("#testimonial").val($(this).data("testimonial"));
        } else if (ID == 15) {
            $("#section_id_15").show();
            $("#section_id").val($(this).data("id"));
            $("#how_it_work").val($(this).data("how_it_work"));
        } else if (ID == 16) {
            $("#section_id_16").show();
            $("#section_id").val($(this).data("id"));
            $("#blog").val($(this).data("blog"));
        }
    });

    $(document).ready(function () {
        section_table();
    });

    $(document).ready(function () {
        $("#addBannerOneForm").submit(function (event) {
            event.preventDefault();

            var formData = new FormData(this);
            formData.append(
                "show_search",
                $("#show_search").is(":checked") ? 1 : 0
            );
            formData.append(
                "show_location",
                $("#show_location").is(":checked") ? 1 : 0
            );
            formData.append(
                "popular_search",
                $("#popular_search").is(":checked") ? 1 : 0
            );
            formData.append(
                "provider_count",
                $("#provider_count").is(":checked") ? 1 : 0
            );
            formData.append(
                "services_count",
                $("#services_count").is(":checked") ? 1 : 0
            );
            formData.append(
                "review_count",
                $("#review_count").is(":checked") ? 1 : 0
            );

            $.ajax({
                url: "/api/page-builder/section-store",
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".banner_one").attr("disabled", true);
                    $(".banner_one").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
                .done((response, statusText, xhr) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".banner_one").removeAttr("disabled");
                    $(".banner_one").html("update");
                    if (response.code === 200) {
                        $("#datatable_section").DataTable().destroy();
                        toastr.success(response.message);
                        section_table();
                        $("#add_banner_sec").modal("hide");
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".banner_one").removeAttr("disabled");
                    $(".banner_one").html("update");

                    if (error.status == 422) {
                        $.each(error.responseJSON, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(responseJSON.message);
                    }
                });
        });
    });

    $(document).on("click", '.delete[data-bs-toggle="modal"]', function (e) {
        e.preventDefault();
        var secId = $(this).data("id");
        $("#confirmDeleteFaq").data("id", secId);
    });

    $(document).on("click", "#confirmDeleteFaq", function (e) {
        e.preventDefault();

        var secId = $(this).data("id");
        $.ajax({
            url: "/api/page-builder/delete",
            type: "POST",
            data: {
                id: secId,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $("#confirmDeleteFaq").attr("disabled", true);
                $("#confirmDeleteFaq").html(
                    '<div class="spinner-border text-light" role="status"></div>'
                );
            },

            success: function (response) {
                if (response.success) {
                    toastr.success(response.message);
                    section_table();
                    $("#delete-modal").modal("hide");
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                toastr.error("An error occurred while trying to delete.");
            },
        });
    });

    document
        .getElementById("background_image")
        .addEventListener("change", function (event) {
            previewImage(event, "background_img");
        });

    document
        .getElementById("thumbnail_image")
        .addEventListener("change", function (event) {
            previewImage(event, "thumbnail_img");
        });

    function previewImage(event, imgId) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById(imgId).src = e.target.result;
            };
            reader.readAsDataURL(file); // Convert image to a base64 URL
        } else {
            // Reset the image preview if no file is selected
            document.getElementById(imgId).src = "";
        }
    }
}

//Pages-list
if (pageValue === "admin.page-builder") {
    function page_table() {
        fetchPage(1);
    }

    function fetchPage(page) {
        var langCode = $("body").data("lang");
        let currentLang = langCode;
        $.ajax({
            url: "/api/page-builder/page-builder-list",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "asc",
                sort_by: "id",
                language_code: currentLang,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === "200") {
                    populatePageTable(response.data, response.meta);
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
                } else {
                    toastr.error("An error occurred while fetching.");
                }
            },
        });
    }

    function populatePageTable(Page, meta) {
        let tableBody = "";

        if (Page.length > 0) {
            Page.forEach((Page, index) => {
                tableBody += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${Page.page_title}</td>
                        <td>${Page.slug}</td>
                        <td>
                            <span class="badge ${
                                Page.status == "1"
                                    ? "badge-soft-success"
                                    : "badge-soft-danger"
                            } d-inline-flex align-items-center">
                                <i class="ti ti-circle-filled fs-5 me-1"></i>${
                                    Page.status == "1" ? "Active" : "Inactive"
                                }
                            </span>
                        </td>
                        ${
                            $("#has_permission").data("visible") == 1
                                ? `<td>
                            <ul style="list-style: none; padding: 0; margin: 0; display: flex; gap: 10px;">
                                ${
                                    $("#has_permission").data("edit") == 1
                                        ? `<li>
                                    <a href="/admin/content/edit/page-builder/${Page.encrypted_id}">
                                        <i class="ti ti-pencil fs-20"></i>
                                    </a>
                                </li>`
                                        : ""
                                }

                                <li>
                                    <a class="delete" href="#" data-bs-toggle="modal" data-bs-target="#delete-modal" onclick="setDeleteId('${
                                        Page.id
                                    }')">
                                        <i class="ti ti-trash fs-20 text-danger"></i>
                                    </a>
                                </li>
                            </ul>
                        </td>`
                                : ""
                        }
                    </tr>
                `;
            });
        } else {
            tableBody = `
                    <tr>
                        <td colspan="5" class="text-center">No Section found</td>
                    </tr>
                `;
        }

        $("#datatable_page tbody").html(tableBody);
        $("#loader-table").addClass("d-none");
        $(".label-loader, .input-loader").hide();
        $("#datatable_page, .real-label, .real-input").removeClass("d-none");

        if (
            Page.length != 0 &&
            !$.fn.DataTable.isDataTable("#datatable_page")
        ) {
            $("#datatable_page").DataTable({
                ordering: true,
                language: datatableLang,
            });
        }
    }

    function setDeleteId(id) {
        $("#deletePageId").val(id);
    }

    $("#confirmDeleteDelete").click(function () {
        var pageId = $("#deletePageId").val();

        $.ajax({
            url: "/delete-page",
            type: "POST",
            data: {
                id: pageId,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.success) {
                    toastr.success("Page deleted successfully!");

                    // Close the modal
                    $("#delete-modal").modal("hide");
                    page_table();
                } else {
                    toastr.error("Failed to delete page.");
                }
            },
            error: function () {
                toastr.error("An error occurred.");
            },
        });
    });

    $(document).ready(function () {
        page_table();
    });
}

//Add Page Builder
if (pageValue === "admin.add_page_builder") {
    $(document).ready(function () {
        fetchSection(1);
    });

    function fetchSection(page) {
        $.ajax({
            url: "/api/page-builder/section-list",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "asc",
                sort_by: "id",
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === "200") {
                    var data = response.data;
                    var sectionHtml = "";
                    var count = 0;

                    // Open the first row
                    sectionHtml += '<div class="row">';

                    // Statically add a card with key "Banner One" and value "[banner]"
                    sectionHtml += `
                        <div class="col-md-6">
                            <div class="card p-2 draggable-card" draggable="true" data-value="[banner]">
                                <div class="card-body p-0">
                                    <p class="fs-12">Banner One</p>
                                </div>
                            </div>
                        </div>
                    `;

                    // Generate cards dynamically based on the response data
                    $.each(response.data, function (index, section) {
                        if (section.name === "Banner One") return;
                        $.each(section, function (key, value) {
                            if (
                                key !== "id" &&
                                key !== "name" &&
                                key !== "status"
                            ) {
                                // Add a new row only after every 2 cards
                                if (count % 16 === 0 && count !== 0) {
                                    sectionHtml += '</div><div class="row">';
                                }

                                // Add a card
                                sectionHtml += `
                                    <div class="col-md-6">
                                        <div class="card p-2 draggable-card" draggable="true" data-value="${value}">
                                            <div class="card-body p-0">
                                                <p class="fs-12">${section.name}</p>
                                            </div>
                                        </div>
                                    </div>
                                `;

                                count++;
                            }
                        });
                    });

                    // Close the last row if needed
                    sectionHtml += "</div>";

                    // Update the card container
                    $("#cardContainer").html(sectionHtml);
                }
                $(".card-loader").hide();
                $(".real-card").removeClass("d-none");
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
                } else {
                    toastr.error("An error occurred while fetching.");
                }
            },
        });
    }

    $(document).on("dragstart", ".draggable-card", function (event) {
        event.originalEvent.dataTransfer.setData(
            "text/plain",
            $(this).data("value")
        );
    });

    function initializeSummernote() {
        $(".summer").summernote({
            height: 150,
            width: "100%",
            toolbar: [
                ["style", ["style"]],
                [
                    "font",
                    [
                        "bold",
                        "italic",
                        "underline",
                        "strikethrough",
                        "superscript",
                        "subscript",
                        "clear",
                    ],
                ],
                ["fontname", ["fontname"]],
                ["fontsize", ["fontsize"]],
                ["color", ["color"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["height", ["height"]],
                ["table", ["table"]],
                ["insert", ["link", "picture", "video"]],
                ["view", ["fullscreen", "codeview", "help"]],
            ],
            callbacks: {
                onDrop: function (event) {
                    event.preventDefault();
                    var data =
                        event.originalEvent.dataTransfer.getData("text/plain");
                    if (data) {
                        $(this).summernote("pasteHTML", data);
                    }
                },
            },
        });
    }

    $(document).ready(function () {
        initializeSummernote();

        $("#addTextarea").on("click", function () {
            const uniqueId = `status_${Date.now()}`;

            const textareaTemplate = `

            <div class="textarea-item border border-1 border-light px-2 mb-2 mt-2">
                <div class="d-flex justify-content-end gap-4 m-1">
                    <div class="">
                        <div class="status-title">
                                <h5>${$(".textareasContainer").data(
                                    "status"
                                )}</h5>
                            </div>
                            <div class="status-toggle modal-status mt-1">
                                <input type="checkbox" name="page_status[]" id="${uniqueId}" value="1" class="check user8 page_status_checkbox" checked>
                                <label for="${uniqueId}" class="checktoggle"></label>
                            </div>
                    </div>
                    <a class="removeTextarea mt-2"><i class="ti ti-trash m-3 fs-24 fw-bold"></i></a>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">${$(
                                ".textareasContainer"
                            ).data("section_title")}</label>
                            <input type="text" class="form-control" id="section_title" name="section_title[]" placeholder="${$(
                                ".textareasContainer"
                            ).data("section_title_placeholder")}">
                            <span class="invalid-feedback" id="section_title_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">${$(
                                ".textareasContainer"
                            ).data("section_label")}</label>
                            <input type="text" class="form-control" id="section_label" name="section_label[]" placeholder="${$(
                                ".textareasContainer"
                            ).data("section_label_placeholder")}">
                            <span class="invalid-feedback" id="section_label_error"></span>
                        </div>
                    </div>
                </div>
                <textarea type="text" name="page_content[]" class="form-control page_content summer" id="summernote" placeholder="${$(
                    ".textareasContainer"
                ).data("enter_page_content")}"></textarea>
            </div>
        `;

            $(".textareasContainer").append(textareaTemplate);
            initializeSummernote();
        });

        $(".textareasContainer").on("click", ".removeTextarea", function () {
            $(this).closest(".textarea-item").remove();
        });
    });

    $(document).ready(function () {
        $("#addPageBuilderForm").submit(function (event) {
            event.preventDefault();

            var langCode = $("body").data("lang");
            let currentLang = langCode;
            var formData = new FormData(this);
            formData.delete("page_status[]");

            $(".page_status_checkbox").each(function () {
                let isChecked = $(this).is(":checked") ? 1 : 0;
                formData.append("page_status[]", isChecked);
            });
            formData.append("status", $("#status").is(":checked") ? 1 : 0);
            formData.append("currentLang", currentLang); // Adding currentLang to formData

            $.ajax({
                url: "/api/page-builder/page-builder-store",
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".btn_page").attr("disabled", true);
                    $(".btn_page").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
                .done((response, statusText, xhr) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".btn_page").removeAttr("disabled");
                    const updateText =
                        document.getElementById("add_btn_page").dataset
                            .updateText;
                    document.getElementById("add_btn_page").innerText =
                        updateText;
                    if (response.code === 200) {
                        $("#addPageBuilderForm")[0].reset();
                        $(".textareasContainer").html("");
                        toastr.success(
                            $("#add_btn_page").data("create-success")
                        );
                    } else {
                        toastr.error($("#add_btn_page").data("create-success"));
                    }
                    window.location.href = "/admin/content/page-builder";
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".btn_page").removeAttr("disabled");
                    const updateText =
                        document.getElementById("add_btn_page").dataset
                            .updateText;
                    document.getElementById("add_btn_page").innerText =
                        updateText;

                    if (error.status == 422) {
                        if (error.responseJSON.message) {
                            toastr.error(error.responseJSON.message);
                        } else {
                            $.each(error.responseJSON, function (key, val) {
                                $("#" + key).addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                            });
                        }
                    } else {
                        toastr.error(error.responseJSON.message);
                    }
                });
        });
    });
}

//Edit Home Page
if (pageValue === "admin.edit_page_builder") {
    $(document).ready(function () {
        $("#aboutUsSummernote").summernote({
            height: 500,
            placeholder: "Enter About Us",
        });

        $("#termsConditionsSummernote").summernote({
            height: 500,
            placeholder: "Enter Terms and Conditions",
        });

        $("#privacyPolicySummernote").summernote({
            height: 500,
            placeholder: "Enter Privacy Policy",
        });

        $("#contactUsSummernote").summernote({
            height: 500,
            placeholder: "Enter Cotntact Us",
        });
        let pageSlug = getQueryParam("slug");

        fetchPageDetails($("#language_id").val(), pageSlug, $("#id").val());
    });

    function setLanguageId() {
        const selectedLanguageId = document.getElementById("language_id").value;

        document.getElementById("language_id_input").value = selectedLanguageId;
    }

    function getQueryParam(param) {
        let urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    function fetchPageDetails(langId, slug, editId, editParentId) {
        $.ajax({
            url: `/api/page-builder/get-page-details`,
            type: "POST",
            data: {
                language_id: langId,
                id: editId,
                parent_id: editParentId,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
                if (response.code == 200 && response.data) {
                    var data = response.data;

                    $("#edit_slug").val(data.slug);
                    $("#page_title").val(data.page_title);
                    $("#language_id_input").val(data.language_id);
                    $("#slug").val(data.slug);
                    if (data.about_us && data.about_us.trim() !== "") {
                        $(".about_us").summernote("code", data.about_us);
                        $("#aboutUsContainer").show();
                    } else {
                        $("#aboutUsContainer").hide();
                    }
                    if (
                        data.terms_conditions &&
                        data.terms_conditions.trim() !== ""
                    ) {
                        $(".terms_conditions").summernote(
                            "code",
                            data.terms_conditions
                        );
                        $("#termsConditionsContainer").show();
                    } else {
                        $("#termsConditionsContainer").hide();
                    }
                    if (
                        data.privacy_policy &&
                        data.privacy_policy.trim() !== ""
                    ) {
                        $(".privacy_policy").summernote(
                            "code",
                            data.privacy_policy
                        );
                        $("#privacyPolicyContainer").show();
                    } else {
                        $("#privacyPolicyContainer").hide();
                    }
                    if (data.contact_us && data.contact_us.trim() !== "") {
                        $(".contact_us").summernote("code", data.contact_us);
                        $("#contactUsContainer").show();
                    } else {
                        $("#contactUsContainer").hide();
                    }
                    $("#seo_title").val(data.seo_title);
                    $("#tag").tagsinput("removeAll");
                    var tagsArray = data.seo_tag
                        ? data.seo_tag.split(",").map((tag) => tag.trim())
                        : [""];
                    tagsArray.forEach((tag) => $("#tag").tagsinput("add", tag));
                    $("#seo_description").val(data.seo_description);
                    $("#status").prop("checked", data.status === 1);

                    $(".textareasContainer").empty();

                    if (data.page_content && data.page_content.trim() !== "") {
                        var pageContentArray = JSON.parse(data.page_content);

                        let count = 1;

                        let summernoteId = "";

                        pageContentArray.forEach(function (section) {
                            const uniqueId = Date.now();
                            const textareaTemplate = `
                                <div class="textarea-item border border-1 border-li px-2 mb-2">
                                    <div class="d-flex justify-content-end gap-2 m-1">
                                        <div class="m-1 p-1">
                                            <div class="status-title">
                                                <h5 class="status">Status</h5>
                                            </div>
                                            <div class="status-toggle modal-status mt-1">
                                                <input type="checkbox" name="page_status[]" id="status_${uniqueId}" value="1" ${
                                section.status == 1 ? "checked" : ""
                            } class="check user8 page_status_checkbox">
                                                <label for="status_${uniqueId}" class="checktoggle"></label>
                                            </div>
                                        </div>
                                        <a class="removeTextarea mt-3 text-danger"><i class="ti ti-trash m-2 fw-bold fs-24"></i></a>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label section_title">Section Title</label>
                                                <input type="text" class="form-control enter_section_title" id="section_title" value="${
                                                    section.section_title
                                                }" name="section_title[]" placeholder="Enter Section Title">
                                                <span class="invalid-feedback" id="section_title_error"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label section_label">Section Label</label>
                                                <input type="text" class="form-control section_label_placeholder" id="section_label" value="${
                                                    section.section_label
                                                }" name="section_label[]" placeholder="Enter Section label">
                                                <span class="invalid-feedback" id="section_label_error"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <textarea type="text" name="page_content[]" class="form-control page_content summer" id="summernote_${count}"></textarea>
                                </div>
                            `;

                            $(".textareasContainer").append(textareaTemplate);
                            initializeSummernote();
                            summernoteId = `#summernote_${count++}`;
                            $(summernoteId).summernote(
                                "code",
                                section.section_content
                            );
                        });
                    }
                } else {
                    $("#editPageBuilderForm")[0].reset();
                    $(".textareasContainer").empty();
                    $('input[data-role="tagsinput"]').tagsinput("removeAll");
                    $(".form-control")
                        .removeClass("is-invalid")
                        .removeClass("is-valid");
                    $(".invalid-feedback").text("");
                    $("#page_title").val("");
                    $("#slug").val("");
                    $(
                        "#aboutUsSummernote, #termsConditionsSummernote, #privacyPolicySummernote, #contactUsSummernote"
                    ).each(function () {
                        if ($(this).next(".note-editor").length) {
                            $(this).summernote("code", "");
                        }
                    });
                    $("#seo_title").val("");
                    $("#seo_description").val("");
                }
            },

            error: function (error) {
                $(".textareasContainer").empty();
                $('input[data-role="tagsinput"]').tagsinput("removeAll");
                $(".form-control")
                    .removeClass("is-invalid")
                    .removeClass("is-valid");
                $(".invalid-feedback").text("");
                $("#page_title").val("");
                $("#slug").val("");
                $(
                    "#aboutUsSummernote, #termsConditionsSummernote, #privacyPolicySummernote, #contactUsSummernote"
                ).each(function () {
                    if ($(this).next(".note-editor").length) {
                        $(this).summernote("code", "");
                    }
                });
                $("#seo_title").val("");
                $("#seo_description").val("");
            },
        });
    }

    $("#language_id").on("change", function () {
        var langId = $(this).val();
        var slug = $("#edit_slug").val();
        var editId = $("#id").val();
        var editParentId = $("#parent_id").val();
        document.getElementById("language_id_input").value = langId;

        fetchPageDetails(langId, slug, editId, editParentId);
        languageTranslate(langId);
    });

    function languageTranslate(lang_id) {
        $.ajax({
            url: "/api/translate",
            type: "POST",
            dataType: "json",
            data: {
                language_id: lang_id,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                const trans = response.translated_values;
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid is-valid");

                if (response.code === 200 && Object.keys(trans).length > 0) {
                    $(".dashboard").text(trans.dashboard);
                    $(".lang_title").text(trans.available_translations);
                    $(".seo_tags_label").text(trans.seo_tags_label);
                    $(".seo_tags_placeholder").text(trans.seo_tags_placeholder);
                    $(".seo_title_label").text(trans.seo_title_label);
                    $(".seo_title_placeholder").text(
                        trans.seo_title_placeholder
                    );
                    $(".seo_description_label").text(
                        trans.seo_description_label
                    );
                    $(".seo_description_placeholder").text(
                        trans.seo_description_placeholder
                    );
                    $(".status_toggle_label").text(trans.status_toggle_label);
                    $(".status_toggle_description").text(
                        trans.status_toggle_description
                    );
                    $(".page_title_label").text(trans.page_title_label);
                    $(".page_title_placeholder").text(
                        trans.page_title_placeholder
                    );
                    $(".slug_label").text(trans.slug_label);
                    $(".slug_placeholder").text(trans.slug_placeholder);
                    $(".status").text(trans.status);
                    $(".section_title").text(trans.section_title);
                    $(".section_label").text(trans.section_label);
                    $(".section_label_placeholder").text(
                        trans.section_label_placeholder
                    );
                    $(".status").text(trans.status);
                    $(".about_us_label").text(trans.about_us_label);
                    $(".contact_us_label").text(trans.contact_us_label);
                    $(".privacy_policy_label").text(trans.privacy_policy_label);
                    $(".terms_conditions_label").text(
                        trans.terms_conditions_label
                    );
                    $(".add_section").text(trans.add_section);
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }

    $(document).ready(function () {
        fetchSection(1);
    });

    function fetchSection(page) {
        $.ajax({
            url: "/api/page-builder/section-list",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "asc",
                sort_by: "id",
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === "200") {
                    var data = response.data;
                    var sectionHtml = "";
                    var count = 0;

                    // Open the first row
                    sectionHtml += '<div class="row">';

                    // Statically add a card with key "Banner One" and value "[banner]"
                    sectionHtml += `
                        <div class="col-md-6">
                            <div class="card p-2 draggable-card" draggable="true" data-value="[banner]">
                                <div class="card-body p-0">
                                    <p class="fs-12">Banner One</p>
                                </div>
                            </div>
                        </div>
                    `;

                    // Generate cards dynamically based on the response data
                    $.each(response.data, function (index, section) {
                        if (section.name === "Banner One") return;
                        $.each(section, function (key, value) {
                            if (
                                key !== "id" &&
                                key !== "name" &&
                                key !== "status"
                            ) {
                                // Add a new row only after every 2 cards
                                if (count % 16 === 0 && count !== 0) {
                                    sectionHtml += '</div><div class="row">';
                                }

                                // Add a card
                                sectionHtml += `
                                    <div class="col-md-6">
                                        <div class="card p-2 draggable-card" draggable="true" data-value="${value}">
                                            <div class="card-body p-0">
                                                <p class="fs-12">${section.name}</p>
                                            </div>
                                        </div>
                                    </div>
                                `;

                                count++;
                            }
                        });
                    });

                    // Close the last row if needed
                    sectionHtml += "</div>";

                    // Update the card container
                    $("#cardContainer").html(sectionHtml);
                }

                $(".card-loader").hide();
                $(".real-card").removeClass("d-none");
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
                } else {
                    toastr.error("An error occurred while fetching.");
                }
            },
        });
    }

    $(document).on("dragstart", ".draggable-card", function (event) {
        event.originalEvent.dataTransfer.setData(
            "text/plain",
            $(this).data("value")
        );
    });

    function initializeSummernote() {
        $(".summer").summernote({
            height: 100,
            width: "100%",
            toolbar: [
                ["style", ["style"]],
                [
                    "font",
                    [
                        "bold",
                        "italic",
                        "underline",
                        "strikethrough",
                        "superscript",
                        "subscript",
                        "clear",
                    ],
                ],
                ["fontname", ["fontname"]],
                ["fontsize", ["fontsize"]],
                ["color", ["color"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["height", ["height"]],
                ["table", ["table"]],
                ["insert", ["link", "picture", "video"]],
                ["view", ["fullscreen", "codeview", "help"]],
            ],
            callbacks: {
                onDrop: function (event) {
                    event.preventDefault();
                    var data =
                        event.originalEvent.dataTransfer.getData("text/plain");
                    if (data) {
                        $(this).summernote("pasteHTML", data);
                    }
                },
            },
        });
    }

    $(document).ready(function () {
        initializeSummernote();

        $("#addTextarea").on("click", function () {
            const uniqueId = `status_${Date.now()}`;

            const textareaTemplate = `
            <div class="textarea-item border border-1 border-dark px-2 mb-2">
                <div class="d-flex justify-content-end gap-4 m-1">
                    <div class="">
                        <div class="status-title">
                                <h5>${$(".textareasContainer").data(
                                    "status"
                                )}</h5>
                            </div>
                            <div class="status-toggle modal-status mt-1">
                                <input type="checkbox" name="page_status[]" id="${uniqueId}" value="1" class="check user8 page_status_checkbox" checked>
                                <label for="${uniqueId}" class="checktoggle"></label>
                            </div>
                    </div>
                    <a class="removeTextarea mt-2"><i class="ti ti-trash m-3 fs-24 fw-bold"></i></a>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">${$(
                                ".textareasContainer"
                            ).data("section_title")}</label>
                            <input type="text" class="form-control" id="section_title" name="section_title[]" placeholder="${$(
                                ".textareasContainer"
                            ).data("section_title_placeholder")}">
                            <span class="invalid-feedback" id="section_title_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">${$(
                                ".textareasContainer"
                            ).data("section_label")}</label>
                            <input type="text" class="form-control" id="section_label" name="section_label[]" placeholder="${$(
                                ".textareasContainer"
                            ).data("section_label_placeholder")}">
                            <span class="invalid-feedback" id="section_label_error"></span>
                        </div>
                    </div>
                </div>
                <textarea type="text" name="page_content[]" class="form-control page_content summer" placeholder="${$(
                    ".textareasContainer"
                ).data("enter_page_content")}"></textarea>
            </div>
        `;

            $(".textareasContainer").append(textareaTemplate);
            initializeSummernote();
        });

        $(".textareasContainer").on("click", ".removeTextarea", function () {
            $(this).closest(".textarea-item").remove();
        });
    });

    $(document).ready(function () {
        $("#editPageBuilderForm").submit(function (event) {
            event.preventDefault();

            var formData = new FormData(this);
            formData.delete("page_status[]");

            $(".page_status_checkbox").each(function () {
                let isChecked = $(this).is(":checked") ? 1 : 0;
                formData.append("page_status[]", isChecked);
            });

            formData.append("status", $("#status").is(":checked") ? 1 : 0);
            formData.append("lang_id", $("#lang_id").val());
            formData.append("edit_slug", $("#edit_slug").val());

            $.ajax({
                url: "/api/page-builder/page-builder-update",
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".edit_btn_page").attr("disabled", true);
                    $(".edit_btn_page").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
                .done((response, statusText, xhr) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".edit_btn_page").removeAttr("disabled");
                    const updateText =
                        document.getElementById("edit_btn_page").dataset
                            .updateText;
                    document.getElementById("edit_btn_page").innerText =
                        updateText;
                    if (response.code === 200) {
                        toastr.success(
                            $("#edit_btn_page").data("update-success")
                        );
                    } else {
                        toastr.error(
                            $("#edit_btn_page").data("update-success")
                        );
                    }
                    window.location.href = "/admin/content/page-builder";
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".edit_btn_page").removeAttr("disabled");
                    const updateText =
                        document.getElementById("edit_btn_page").dataset
                            .updateText;
                    document.getElementById("edit_btn_page").innerText =
                        updateText;
                    if (error.status == 422) {
                        if (error.responseJSON.message) {
                            toastr.error(error.responseJSON.message);
                        } else {
                            $.each(error.responseJSON, function (key, val) {
                                $("#" + key).addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                            });
                        }
                    } else {
                        toastr.error(error.responseJSON.message);
                    }
                });
        });
    });
}

if (pageValue === "content.menu-builder") {
    $("#nestable")
        .nestable({
            maxDepth: 2,
        })
        .on("change", updateOutput);

    var menu_name_required = "Menu name is required.";
    var menu_name_exists = "Menu name already exists.";
    var url_required = "URL is required.";
    var url_exists = "URL already exists.";
    var menu_exists = "Menu already added.";

    $(document).on("click", ".add_built_menu", function () {
        var name = $(this).data("title");
        var url = $(this).data("url");
        var target = "_self";
        var page_id = $(this).data("page_id");
        $("#method").val("add");

        if (menuNameExists(name)) {
            toastr.error(menu_exists);
        } else {
            $("#empty_web_menus").hide();
            $("#save_all_menus").removeClass("d-none");
            addToMenu((menu_id = ""), name, url, target, page_id);
        }
    });

    $("#name").on("keyup", function () {
        var givenName = $(this).val();
        var method = $("#method").val();
        var currentName = currentEditName.text();
        validateName(givenName, method, currentName);
    });

    $("#url").on("keyup", function () {
        var givenURL = $(this).val();
        var method = $("#method").val();
        var currentURL = currentEditURL.val();
        validateURL(givenURL, method, currentURL);
    });

    function menuNameExists(givenName, method, currentName) {
        let namesArray = [];
        let name;
        $(".dd-item").each(function () {
            name = $(this).data("name");
            namesArray.push(name.toLowerCase());
        });

        if (method === "update" && currentName) {
            namesArray = namesArray.filter(
                (n) => n !== currentName.toLowerCase()
            );
        }

        return namesArray.includes(givenName.toLowerCase()) ? 1 : 0;
    }

    function menuURLExists(givenURL, method, currentURL) {
        let urlArray = [];
        let url;
        givenURL = givenURL.replace(/\s+/g, "-").toLowerCase();

        $(".dd-item").each(function () {
            url = $(this).data("url");
            if (url) {
                urlArray.push(url.replace(/\s+/g, "-").toLowerCase());
            }
        });

        if (method === "update" && currentURL) {
            currentURL = currentURL.replace(/\s+/g, "-").toLowerCase();
            urlArray = urlArray.filter((n) => n !== currentURL);
        }

        return urlArray.includes(givenURL) ? 1 : 0;
    }

    function validateName(givenName, method = "", currentName = "") {
        const name = $("#name").val().trim();
        if (!name) {
            $("#name_error").text(menu_name_required);
            $("#name").addClass("is-invalid").removeClass("is-valid");
            return false;
        } else if (menuNameExists(givenName, method, currentName)) {
            $("#name_error").text(menu_name_exists);
            $("#name").addClass("is-invalid").removeClass("is-valid");
            return false;
        } else {
            $("#name_error").text("");
            $("#name").removeClass("is-invalid").addClass("is-valid");
            return true;
        }
    }

    function validateURL(givenURL, method = "", currentURL = "") {
        const url = $("#url").val().trim();
        if (!url) {
            $("#url_error").text(url_required);
            $("#url").addClass("is-invalid").removeClass("is-valid");
            return false;
        } else if (menuURLExists(givenURL, method, currentURL)) {
            $("#url_error").text(url_exists);
            $("#url").addClass("is-invalid").removeClass("is-valid");
            return false;
        } else {
            $("#url_error").text("");
            $("#url").removeClass("is-invalid").addClass("is-valid");
            return true;
        }
    }

    $(document).ready(function () {
        var langCode = $("body").data("lang");
        languageTranslate($("#language_id").val());
        getBuiltMenus("", langCode);
        listWebsiteMenus("", langCode);
    });

    function getBuiltMenus(langId = "", langCode = "") {
        $.ajax({
            url: "/api/content/menu-builder/get-built-in-menus",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "asc",
                language_id: langId,
                language_code: langCode,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    var menus = response.data;

                    if (menus.length != 0) {
                        menus.forEach((menu, index) => {
                            $(".built_in_menus").append(`
                                <div class="card mb-2">
                                    <div class="card-header justify-content-between d-flex flex-wrap">
                                        <div class="d-flex w-100 align-items-center">
                                            <div class="me-auto">
                                                <h6 class="text-left">${
                                                    menu.page_title
                                                }</h6>
                                            </div>
                                            <div class="ms-auto">
                                                ${
                                                    $("#has_permission").data(
                                                        "create"
                                                    ) == 1
                                                        ? `<button class="btn btn-primary btn-sm add_built_menu" data-page_id="${menu.id}" data-title="${menu.page_title}" data-url="${menu.slug}">
                                                    <i class="ti ti-plus"></i>
                                                </button>`
                                                        : ""
                                                }
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `);
                        });
                    } else {
                        $(".built_in_menus").append(`
                            <div class="d-flex w-100 align-items-center justify-content-center">
                                ${$(".website_menus").data("empty")}
                            </div>
                        `);
                    }
                }

                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
                $(".card-loader").hide();
                $(".real-card").removeClass("d-none");
            },
            error: function (error) {
                if (error.responseJSON.code === 500) {
                    toastr.error(error.responseJSON.message);
                } else {
                    toastr.error("An error occurred while fetching.");
                }
            },
        });
    }

    var menuCount = 1;
    function getMenuData($element, processedItems = new Set()) {
        const items = [];

        $element.children(".dd-item").each(function () {
            const $item = $(this);
            const itemId = $item.data("id");

            if (processedItems.has(itemId)) return;

            processedItems.add(itemId);

            const itemData = {
                id: menuCount++,
                name: $item.data("name"),
                url: $item.data("url").replace(/\s+/g, "-").toLowerCase(),
                target: $item.data("target"),
                page_id: $item.data("page_id"),
                custom: $item.data("custom"),
                submenus: [],
            };

            const $subMenu = $item.children(".dd-list");
            if ($subMenu.length > 0) {
                itemData.submenus = getMenuData($subMenu, processedItems);
            }

            items.push(itemData);
        });

        return items;
    }

    $(document).on("click", "#save_all_menus", function () {
        menuCount = 1;
        const menuData = getMenuData($("#nestable .dd-list"));
        const jsonData = JSON.stringify(menuData);
        var id = $(this).data("id");
        var saveBtn = "save_all_menus";

        saveAllMenus(id, jsonData, saveBtn);
    });

    let primaryMenuId = "";
    function saveAllMenus(id, jsonData, saveBtn = "") {
        $.ajax({
            url: "/api/content/menu-builder/save-menus",
            type: "POST",
            dataType: "json",
            data: {
                id: primaryMenuId,
                language_id: $("#language_id").val(),
                menus: jsonData,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            beforeSend: function () {
                if (saveBtn == "save_all_menus") {
                    $("#save_all_menus").attr("disabled", true);
                    $("#save_all_menus").html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
                }
            },
            success: function (response) {
                $("#save_all_menus").removeAttr("disabled");
                $("#save_all_menus").html(lg_save);
                $(".dd-list").children().remove();
                if (response.code === 200) {
                    toastr.success(response.message);
                    $("#nestable").nestable("destroy").nestable();
                    var langId = $("#language_id").val();
                    listWebsiteMenus(langId);
                }
            },
            error: function (error) {
                $("#save_all_menus").removeAttr("disabled");
                $("#save_all_menus").html(lg_save);
                if (error.code === 500) {
                    toastr.error(error.message);
                } else {
                    toastr.error("An error occurred while saving.");
                }
            },
        });
    }

    function listWebsiteMenus(langId = "", langCode = "") {
        $.ajax({
            url: "/api/content/menu-builder/list-website-menus",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "asc",
                language_id: langId,
                language_code: langCode,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    $(".label-loader, .input-loader").hide();
                    $(".real-label, .real-input").removeClass("d-none");
                    $(".card-loader").hide();
                    $(".real-card").removeClass("d-none");

                    if (response.data != "") {
                        $("#save_all_menus")
                            .attr("data-id", response.data.id)
                            .removeClass("d-none");
                        primaryMenuId = response.data.id;
                        const menus = response.data.menus;
                        $("#empty_web_menus").hide();
                        renderMenus(menus);
                    } else {
                        primaryMenuId = "";
                        $("#save_all_menus").attr("data-id", "");
                        $("#save_all_menus").addClass("d-none");
                        $("#empty_web_menus").show();
                    }
                }
            },
            error: function (error) {
                if (error.responseJSON && error.responseJSON.code === 500) {
                    toastr.error(error.responseJSON.message);
                } else {
                    toastr.error("An error occurred while fetching.");
                }
            },
        });
    }

    function renderMenus(menuItems) {
        menuItems.forEach((item) => {
            addToMenu(
                item.id,
                item.name,
                item.url,
                item.target,
                item.page_id,
                item.custom
            );

            if (item.submenus && item.submenus.length > 0) {
                renderSubmenus(item.id, item.submenus);
            }
        });
    }

    function renderSubmenus(parentId, submenus) {
        submenus.forEach((submenu) => {
            const submenuHTML = `
                <li class="dd-item"
                    data-id="${submenu.id}"
                    data-name="${submenu.name}"
                    data-url="${submenu.url}"
                    data-target="${submenu.target}"
                    data-page_id="${submenu.page_id}"
                    data-custom="${submenu.custom}"
                    data-new="1"
                    data-deleted="0">
                    <div class="dd-handle">${submenu.name}</div>
                    ${
                        $("#has_permission").data("edit") == 1
                            ? `<span class="button-edit btn btn-info btn-sm pull-right" data-owner-id="${submenu.id}">
                        <i class="ti ti-pencil" aria-hidden="true"></i>
                    </span>`
                            : ""
                    }
                    ${
                        $("#has_permission").data("delete") == 1
                            ? `<span class="button-delete btn btn-danger btn-sm pull-right" data-owner-id="${submenu.id}">
                        <i class="ti ti-trash" aria-hidden="true"></i>
                    </span>`
                            : ""
                    }
                </li>
            `;

            let parentItem = $(`[data-id="${parentId}"]`);
            let nestedList = parentItem.children("ol.dd-list");
            if (!nestedList.length) {
                nestedList = $('<ol class="dd-list"></ol>');
                parentItem.append(nestedList);

                if (!parentItem.find(".dd-collapse-btn").length) {
                    parentItem.prepend(`
                        <button class="dd-collapse-btn" data-action="collapse"></button>
                        <button class="dd-collapse-btn" data-action="expand" style="display: none;"></button>
                    `);

                    parentItem
                        .find('[data-action="collapse"]')
                        .on("click", function () {
                            nestedList.hide();
                            $(this).hide();
                        });

                    parentItem
                        .find('[data-action="expand"]')
                        .on("click", function () {
                            nestedList.show();
                            $(this).hide();
                        });
                }
            }

            nestedList.append(submenuHTML);

            if (submenu.submenus && submenu.submenus.length > 0) {
                renderSubmenus(submenu.id, submenu.submenus);
            }
        });

        updateOutput($("#nestable").data("output", $("#json-output")));
        $("#menuBuilderForm").trigger("reset");

        $("#nestable .button-delete").on("click", deleteFromMenu);
        $("#nestable .button-edit").on("click", prepareEdit);
    }

    $("#language_id").on("change", function () {
        var langId = $(this).val();
        var id = $("#id").val();

        $(".built_in_menus").empty();

        languageTranslate(langId);
        $("#nestable").nestable("destroy").nestable();
        $(".dd-list").empty();
        listWebsiteMenus(langId);
        getBuiltMenus(langId);
    });

    function languageTranslate(lang_id) {
        $.ajax({
            url: "/api/translate",
            type: "POST",
            dataType: "json",
            data: {
                language_id: lang_id,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                const trans = response.translated_values;

                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid is-valid");

                if (response.code === 200 && trans) {
                    $('label[for="name"]').html(
                        `${trans.name}<span class="text-danger"> *</span>`
                    );
                    $('label[for="href"]').html(
                        `${trans.slug}<span class="text-danger"> *</span>`
                    );
                    $('label[for="target"]').html(
                        `${trans.target}<span class="text-danger"> *</span>`
                    );

                    $("#name").attr("placeholder", trans.enter_menu_name);
                    $("#url").attr("placeholder", trans.enter_slug);
                    $("#updateBtn").text(trans.Save);
                    $("#addNewBtn").text(trans.add);
                    $("#save_all_menus").text(trans.Save);
                    $(".lang_title").text(trans.available_translations);
                    lg_save = trans.Save;

                    $("#target").empty();
                    $("#target").append(`
                       <option value="_self">${trans.self}</option>
                        <option value="_blank">${trans.blank}</option>
                    `);

                    $(".website_menus").data("empty", trans.no_data_available);
                    $(".empty_info").text(trans.no_data_available);

                    $(".translate").each(function () {
                        var translateKey = $(this).data("translate");
                        if (trans.hasOwnProperty(translateKey)) {
                            var translatedText = trans[translateKey];
                            $(this).text(translatedText);
                        }
                    });

                    menu_name_required = trans.menu_name_required;
                    menu_name_exists = trans.menu_name_exists;
                    url_required = trans.slug_required;
                    url_exists = trans.slug_exists;
                    menu_exists = trans.menu_exists;
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }
}

if (pageValue === "admin.addproduct" || pageValue === "admin.addservice") {
    $("#generalTab a").on("click", function (e) {
        e.preventDefault();
        $(this).tab("show");
    });
    async function init() {
        await loadcategory();
    }

    init().catch((error) => {
        toastr.error("Error during initialization:", error);
    });
    function loadcountrysec(val) {
        $("#" + val).show();
    }
    function loadslotstime(val) {
        $("#" + val).show();
    }

    function loadslots(val) {
        $("#" + val).show();
        $("#mon_slot").show();
        $("#tue_slot").show();
        $("#wed_slot").show();
        $("#thu_slot").show();
        $("#fri_slot").show();
        $("#sat_slot").show();
        $("#sun_slot").show();
    }
    async function loadvariation() {
        const response = await $.ajax({
            url: "/api/products/variationlistall",
            type: "POST",
            data: {
                order_by: "asc",
                count_per_page: 10,
                sort_by: "",
                search: "",
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
        });

        if (response.code == 200) {
            if (Array.isArray(response.data)) {
                var currency_data = response.data;
                var currency_table_body = $("#variation_fied");
                var response_data;
                currency_table_body.empty();

                $.each(currency_data, (index, val) => {
                    response_data = `<div class="checkbox" ><label><input id="cc${val.id}" onclick="loadvarvalues(${val.id})" name="variation[]" type="checkbox" value="${val.id}" name="checkbox"> ${val.variation_name}</label></div><div id="var${val.id}" class="checkbox" style="padding-left: 15px;"></div>`;
                    currency_table_body.append(response_data);
                });
            }
        } else {
            toastr.error("Error fetching settings:", response.message);
        }
    }
    function loadsubcategory(val) {
        var formData = {
            id: val,
        };
        $.ajax({
            url: "/api/products/categorylist",
            type: "POST",
            data: formData,
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code == 200) {
                    var currency_data = response.data;
                    var currency_table_body = $("#Subcategory_fied");
                    var response_data;
                    currency_table_body.empty();
                    currency_table_body.append(
                        "<option value='' >Select</option>"
                    );

                    $.each(currency_data, (index, val) => {
                        response_data = `<option value="${val.id}">${val.name}</option>`;
                        currency_table_body.append(response_data);
                    });
                }
            },
            error: function (xhr) {
                alert("Failed to set default language. Please try again.");
            },
        });
    }
    async function loadcategory() {
        if (pageValue === "admin.addservice") {
            var souce = "service";
        }
        if (pageValue === "admin.addproduct") {
            var souce = "product";
        }
        const response = await $.ajax({
            url: "/api/products/categorylist",
            type: "POST",
            data: {
                order_by: "asc",
                count_per_page: 10,
                sort_by: "",
                source_type: souce,
                search: "",
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
        });

        if (response.code == 200) {
            if (Array.isArray(response.data)) {
                var currency_data = response.data;
                var currency_table_body = $("#category_fied");
                var response_data;
                currency_table_body.empty();
                currency_table_body.append("<option value=''>Select</option>");

                $.each(currency_data, (index, val) => {
                    response_data = `<option value="${val.id}">${val.name}</option>`;
                    currency_table_body.append(response_data);
                });
            }
        } else {
            toastr.error("Error fetching settings:", response.message);
        }
    }

    var currentLang = $("body").data("lang");

    const validationMessages = {
        en: {
            source_name: {
                required: "The name field is required.",
                minlength: "The name must be at least 5 characters.",
                maxlength: "The name cannot exceed 255 characters.",
            },
            source_code: {
                required: "The service code is required.",
                minlength: "The service code must be at least 3 characters.",
                maxlength: "The service code cannot exceed 100 characters.",
            },
            category_fied: {
                required: "Please select a category.",
            },
            Subcategory_fied: {
                required: "Please select a subcategory.",
            },
            source_desc: {
                required: "The description is required.",
                minlength: "The description must be at least 10 characters.",
            },
            price_type: {
                required: "Please select a price type.",
            },
            fixed_price: {
                required: "The price is required.",
                number: "The price must be a valid number.",
            },
            "source_country[]": {
                required: "Please select at least one country.",
            },
            "source_city[]": {
                required: "Please select at least one city.",
            },
            "service_name[]": {
                required: "The service name is required.",
                minlength: "The service name must be at least 3 characters.",
            },
            "service_price[]": {
                required: "The service price is required.",
                number: "The service price must be a valid number.",
            },
            "service_desc[]": {
                required: "The service description is required.",
                minlength:
                    "The service description must be at least 10 characters.",
            },
            seo_title: {
                required: "The SEO title is required.",
                maxlength: "The SEO title cannot exceed 100 characters.",
            },
            tags: {
                required: "Please enter tags.",
                minlength: "Tags must be at least 3 characters.",
            },
            content: {
                required: "The SEO description is required.",
                minlength:
                    "The SEO description must be at least 10 characters.",
            },
            "logo[]": {
                required: "The service image is required.",
                extension:
                    "Only JPG, JPEG, PNG, WEBP and SVG files are allowed.",
            },
        },
        ar: {
            source_name: {
                required: "حقل الاسم مطلوب.",
                minlength: "يجب أن يكون الاسم على الأقل 5 أحرف.",
                maxlength: "لا يمكن أن يتجاوز الاسم 255 حرفًا.",
            },
            source_code: {
                required: "حقل رمز الخدمة مطلوب.",
                minlength: "يجب أن يكون رمز الخدمة على الأقل 3 أحرف.",
                maxlength: "لا يمكن أن يتجاوز رمز الخدمة 100 حرف.",
            },
            category_fied: {
                required: "يرجى اختيار الفئة.",
            },
            Subcategory_fied: {
                required: "يرجى اختيار الفئة الفرعية.",
            },
            source_desc: {
                required: "الوصف مطلوب.",
                minlength: "يجب أن يكون الوصف على الأقل 10 أحرف.",
            },
            price_type: {
                required: "يرجى اختيار نوع السعر.",
            },
            fixed_price: {
                required: "السعر مطلوب.",
                number: "يجب أن يكون السعر رقمًا صالحًا.",
            },
            "source_country[]": {
                required: "يرجى اختيار بلد واحد على الأقل.",
            },
            "source_city[]": {
                required: "يرجى اختيار مدينة واحدة على الأقل.",
            },
            "service_name[]": {
                required: "اسم الخدمة مطلوب.",
                minlength: "يجب أن يكون اسم الخدمة على الأقل 3 أحرف.",
            },
            "service_price[]": {
                required: "سعر الخدمة مطلوب.",
                number: "يجب أن يكون سعر الخدمة رقمًا صالحًا.",
            },
            "service_desc[]": {
                required: "وصف الخدمة مطلوب.",
                minlength: "يجب أن يكون وصف الخدمة على الأقل 10 أحرف.",
            },
            seo_title: {
                required: "عنوان تحسين محركات البحث (SEO) مطلوب.",
                maxlength:
                    "لا يمكن أن يتجاوز عنوان تحسين محركات البحث (SEO) 100 حرف.",
            },
            tags: {
                required: "يرجى إدخال العلامات.",
                minlength: "يجب أن تكون العلامات على الأقل 3 أحرف.",
            },
            content: {
                required: "وصف تحسين محركات البحث (SEO) مطلوب.",
                minlength:
                    "يجب أن يكون وصف تحسين محركات البحث (SEO) على الأقل 10 أحرف.",
            },
            "logo[]": {
                required: "صورة الخدمة مطلوبة.",
                extension: "يُسمح فقط بملفات JPG و JPEG و PNG و WEBP و SVG.",
            },
        },
    };

    $(document).ready(function () {
        $("#adminAddService").validate({
            rules: {
                source_name: {
                    required: true,
                    minlength: 5,
                    maxlength: 255,
                },
                source_code: {
                    required: true,
                    minlength: 3,
                    maxlength: 100,
                },
                category_fied: {
                    required: true,
                },
                Subcategory_fied: {
                    required: true,
                },
                source_desc: {
                    required: true,
                    minlength: 10,
                },
                price_type: {
                    required: true,
                },
                fixed_price: {
                    required: true,
                    number: true,
                },
                country: {
                    required: true,
                },
                state: {
                    required: true,
                },
                city: {
                    required: true,
                },
                "service_name[]": {
                    required: true,
                    minlength: 3,
                },
                "service_price[]": {
                    required: true,
                    number: true,
                },
                "service_desc[]": {
                    required: true,
                    minlength: 10,
                },
                seo_title: {
                    required: true,
                    maxlength: 100,
                },
                tags: {
                    required: true,
                    minlength: 3,
                },
                content: {
                    required: true,
                    minlength: 10,
                },
                "logo[]": {
                    required: true,
                    extension: "jpg|jpeg|png|svg|webp",
                },
            },
            messages: validationMessages[currentLang],
            errorElement: "span",
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback");
                element.closest(".mb-3").append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass("is-invalid").addClass("is-valid");
            },
            submitHandler: function (form) {
                form.submit();
            },
        });
    });
}

if (pageValue === "admin.userlist" || pageValue === "admin.providerslist") {
    $(document).ready(function () {
        list_table();
        // Handle search input
        $("#searchLanguage").on("input", function () {
            list_table(1); // Reset to the first page on new search
        });
    });
    var type = "";
    if (pageValue === "admin.providerslist") {
        var type = 2;
    } else if (pageValue === "admin.userlist") {
        var type = 3;
    }
    function list_table(page) {
        $.ajax({
            url: "/api/getuserlist",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
                sort_by: "id",
                page: page,
                search: $("#searchLanguage").val(),
                type: type,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code == "200") {
                    listTable(response.data, response.meta);
                } else {
                    toastr.error(response.message);
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
                        if (languageId === 2) {
                            loadJsonFile(
                                "error_occurred_fetching_data",
                                function (langtst) {
                                    toastr.error(langtst);
                                }
                            );
                        } else {
                            toastr.error(
                                "An error occurred while fetching the data."
                            );
                        }
                    }
                } else {
                    if (languageId === 2) {
                        loadJsonFile(
                            "error_occurred_fetching_data",
                            function (langtst) {
                                toastr.error(langtst);
                            }
                        );
                    } else {
                        toastr.error(
                            "An error occurred while fetching the data."
                        );
                    }
                }
                toastr.error("Error fetching list:", error);
            },
        });
    }

    function listTable(list, meta) {
        let tableBody = "";
        if (list.length > 0) {
            list.forEach((data) => {
                let routeUrl = "/admin/provider/view/" + data.userid;
                if (type == "3") {
                    routeUrl = "/admin/user/view/" + data.userid;
                } else {
                    routeUrl = "/admin/provider/view/" + data.userid;
                }
                tableBody += `
                    <tr>
                        <td>${data.name}</td>
                        <td>${data.email}</td>
                        <td>${data.phone_number ?? ""}</td>`;
                if (type == 2) {
                    tableBody += `<td>${data.category_name ?? ""}</td>`;
                }
                tableBody += `     ${
                    $("#has_permission").data("edit") == 1
                        ? `<td>
                                <div class="status-toggle modal-status">
                                    <input type="checkbox" id="listactive-${
                                        data.userid
                                    }" class="check make_default" data-id="${
                              data.userid
                          }" ${data.status == 1 ? "checked" : ""} >
                                    <label for="listactive-${
                                        data.userid
                                    }" class="checktoggle"> </label>
                                </div>
                            </td>`
                        : ""
                }
                             ${
                                 $("#has_permission").data("visible") == 1
                                     ? `<td>  <a href="${routeUrl}"
                                    class="view-user"
                                    data-id="${data.userid}">
                                    <i class="ti ti-eye fs-20 m-3"></i></a>
                                    ${
                                        $("#has_permission").data("delete") == 1
                                            ? `  <a class="icon-only delete" href="#" data-bs-toggle="modal" data-bs-target="#delete-modal" data-id="${data.userid}">
                                            <i class="ti ti-trash m-3 fs-20"></i>
                                            </a>`
                                            : ""
                                    }

                                        </td>`
                                     : ""
                             }
                </tr>`;
            });
            $("#ListTable tbody").html(tableBody);
        } else {
            if (!list || list.length === 0) {
                $("#ListTable").DataTable().destroy();
                $("#ListTable").DataTable({
                    paging: false,
                    language: {
                        emptyTable: "No Data found",
                    },
                    // Other DataTable options
                });
            }
        }

        $("#loader-table").addClass("d-none");
        $(".label-loader, .input-loader").hide();
        $("#ListTable, .real-label, .real-input").removeClass("d-none");

        if (!$.fn.dataTable.isDataTable("#ListTable")) {
            if (
                $("#ListTable").length &&
                !$.fn.DataTable.isDataTable("#ListTable")
            ) {
                $("#ListTable").DataTable({
                    ordering: true,
                    paging: true,
                    pageLength: 10,
                    language: datatableLang,
                });
            }
        }
    }

    function setupPagination(meta) {
        let paginationHtml = "";
        for (let i = 1; i <= meta.last_page; i++) {
            paginationHtml += `<li class="page-item ${
                meta.current_page === i ? "active" : ""
            }"><a class="page-link" href="#">${i}</a></li>`;
        }
        -(
            // Handle click event for pagination
            $("#pagination").on("click", ".page-link", function (e) {
                e.preventDefault();
                const page = $(this).text();
            })
        );
    }
    $(document).on("click", ".make_default", function (e) {
        e.preventDefault();
        var Id = $(this).data("id");
        var checkbox = $(this);
        var isChecked = checkbox.is(":checked");
        $.ajax({
            url: "/api/people/get-status", // Replace with your API endpoint
            type: "GET",
            data: { id: Id, status: isChecked ? 1 : 0 },
            success: function (response) {
                const statusValue = response.data;
                // Assume the response contains a "status" field
                if (statusValue == 1) {
                    $("#listactive-" + Id).prop("checked", true); // Enable the toggle
                } else {
                    $("#listactive-" + Id).prop("checked", false); // Disable the toggle
                }
                let statusLabel = response.message;
                if (languageId === 2) {
                    loadJsonFile(statusLabel, function (langtst) {
                        toastr.success(langtst);
                    });
                } else {
                    toastr.success(statusLabel);
                }
            },
            error: function () {
                let statusLabel = "Error fetching status!";
                if (languageId === 2) {
                    loadJsonFile(statusLabel, function (langtst) {
                        toastr.success(langtst);
                    });
                } else {
                    toastr.error(statusLabel);
                }
            },
        });
    });

    $(document).on("click", '.delete[data-bs-toggle="modal"]', function (e) {
        e.preventDefault();
        var Id = $(this).data("id");
        $("#confirmDelete").data("id", Id);
    });

    $(document).on("click", "#confirmDelete", function (e) {
        e.preventDefault();
        var Id = $(this).data("id");
        $.ajax({
            url: "/api/admin/deleteuser",
            type: "POST",
            data: {
                id: Id,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message);
                    $("#delete-modal").modal("hide");
                    list_table(1); // Refresh the templateId table
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                toastr.error(
                    "An error occurred while trying to delete the data."
                );
            },
        });
    });
}

if (pageValue == "provider.viewdetails.page") {
    $(document).on("click", "#confirmVerifyBtn", function (e) {
        e.preventDefault();
        let providerId = $("#provider_id").val();

        $.ajax({
            url: "/admin/verify-provider",
            type: "POST",
            data: {
                id: providerId,
                language_code: $("body").data("lang"),
            },
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $("#confirmVerifyBtn")
                    .attr("disabled", true)
                    .html(
                        `<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span> ${$(
                            "#confirmVerifyBtn"
                        ).data("verifying")}`
                    );
            },
            complete: function () {
                $("#confirmVerifyBtn")
                    .attr("disabled", false)
                    .html($("#confirmVerifyBtn").data("yes_verify"));
            },
            success: function (response) {
                if (response.code == 200) {
                    toastr.success(response.message);
                    $("#verifyProviderModal").modal("hide");

                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function (xhr, status, error) {
                toastr.error(xhr.responseJSON.message);
            },
        });
    });
}

if (pageValue === "admin.profile") {
    $(".upload_icon").hide();
    $("#imagePreview").show();

    $(document).ready(function () {
        const selectedCountry = $("#country").data("country");
        const selectedState = $("#state").data("state");
        const selectedCity = $("#city").data("city");

        getCountries(selectedCountry, selectedState, selectedCity);

        $("#country").on("change", function () {
            const selectedCountry = $(this).val();
            clearDropdown($("#state"));
            clearDropdown($("#city"));
            if (selectedCountry) {
                getStates(selectedCountry);
            }
        });

        $("#state").on("change", function () {
            const selectedState = $(this).val();
            clearDropdown($("#city"));
            if (selectedState) {
                getCities(selectedState);
            }
        });

        $("#save_admin_profile").on("click", function (e) {
            e.preventDefault();
            $("#adminProfileForm").submit();
        });

        $("#phone_number").on("input", function () {
            $(this).val(
                $(this)
                    .val()
                    .replace(/[^0-9]/g, "")
            );
            if ($(this).val().length > 12) {
                $(this).val($(this).val().slice(0, 12));
            }
        });

        $("#postal_code").on("input", function () {
            if ($(this).val().length > 6) {
                $(this).val($(this).val().slice(0, 6));
            }
        });

        $("#adminProfileForm").validate({
            rules: {
                first_name: {
                    required: true,
                    maxlength: 100,
                    pattern: /^[a-zA-Z]+$/,
                },
                last_name: {
                    required: true,
                    maxlength: 100,
                    pattern: /^[a-zA-Z]+$/,
                },
                user_name: {
                    required: true,
                    maxlength: 100,
                    remote: {
                        url: "/api/user/check-unique",
                        type: "post",
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                        },
                        data: {
                            user_name: function () {
                                return $("#user_name").val();
                            },
                            id: function () {
                                return $("#id").val();
                            },
                        },
                    },
                },
                email: {
                    required: true,
                    email: true,
                    remote: {
                        url: "/api/user/check-unique",
                        type: "post",
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                        },
                        data: {
                            email: function () {
                                return $("#email").val();
                            },
                            id: function () {
                                return $("#id").val();
                            },
                        },
                    },
                },
                phone_number: {
                    required: true,
                    minlength: 10,
                    maxlength: 12,
                },
                address: {
                    required: true,
                    maxlength: 150,
                },
                country: {
                    required: true,
                },
                state: {
                    required: true,
                },
                city: {
                    required: true,
                },
                postal_code: {
                    required: true,
                    maxlength: 6,
                    pattern: /^[a-zA-Z0-9]*$/,
                },
                profile_image: {
                    extension: "jpeg|jpg|png",
                    filesize: 2048,
                },
            },
            messages: {
                first_name: {
                    required: $("#first_name_error").data("required"),
                    maxlength: $("#first_name_error").data("max"),
                    pattern: $("#first_name_error").data("alpha"),
                },
                last_name: {
                    required: $("#last_name_error").data("required"),
                    maxlength: $("#last_name_error").data("max"),
                    pattern: $("#last_name_error").data("alpha"),
                },
                user_name: {
                    required: $("#user_name_error").data("required"),
                    maxlength: $("#user_name_error").data("max"),
                    remote: $("#user_name_error").data("exists"),
                },
                email: {
                    required: $("#email_error").data("required"),
                    email: $("#email_error").data("email_format"),
                    remote: $("#email_error").data("exists"),
                },
                phone_number: {
                    required: $("#phone_number_error").data("required"),
                    digits: $("#phone_number_error").data("digits"),
                    minlength: $("#phone_number_error").data("between"),
                    maxlength: $("#phone_number_error").data("between"),
                },
                address: {
                    required: $("#address_error").data("required"),
                    maxlength: $("#address_error").data("max"),
                },
                country: {
                    required: $("#country_error").data("required"),
                },
                state: {
                    required: $("#state_error").data("required"),
                },
                city: {
                    required: $("#city_error").data("required"),
                },
                postal_code: {
                    required: $("#postal_code_error").data("required"),
                    maxlength: $("#postal_code_error").data("max"),
                    pattern: $("#postal_code_error").data("char_allowed"),
                },
                profile_image: {
                    extension: $("#profile_image_error").data("extension"),
                    filesize: $("#profile_image_error").data("size"),
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
                var url = "save-admin-details";
                var btnId = "#save_admin_profile";
                var data = new FormData(form);
                saveAdminDetails(data, url, btnId);
            },
        });

        $("#changePasswordForm").validate({
            rules: {
                current_password: {
                    required: true,
                    minlength: 8,
                    remote: {
                        url: "/api/admin/check-password",
                        type: "post",
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                        },
                        data: {
                            current_password: function () {
                                return $("#current_password").val();
                            },
                            id: function () {
                                return $("#id").val();
                            },
                        },
                    },
                },
                new_password: {
                    required: true,
                    minlength: 8,
                    notEqualTo: "#current_password",
                },
                confirm_password: {
                    required: true,
                    equalTo: "#new_password",
                },
            },
            messages: {
                current_password: {
                    required: $("#current_password_error").data("required"),
                    minlength: $("#current_password_error").data("min"),
                    remote: $("#current_password_error").data("incorrect"),
                },
                new_password: {
                    required: $("#new_password_error").data("required"),
                    minlength: $("#new_password_error").data("min"),
                    notEqualTo: $("#new_password_error").data("not_equal"),
                },
                confirm_password: {
                    required: $("#confirm_password_error").data("required"),
                    equalTo: $("#confirm_password_error").data("equal"),
                },
            },
            errorPlacement: function (error, element) {
                var errorId = element.attr("id") + "_error";
                $("#" + errorId).text(error.text());
            },
            highlight: function (element) {
                $(element).addClass("is-invalid").removeClass("is-valid");
                $("#" + element.id)
                    .siblings("span")
                    .addClass("me-3");
            },
            unhighlight: function (element) {
                $(element).removeClass("is-invalid").addClass("is-valid");
                var errorId = element.id + "_error";
                $("#" + errorId).text("");
                $("#" + element.id)
                    .siblings("span")
                    .addClass("me-3");
            },
            onkeyup: function (element) {
                $(element).valid();
            },
            onchange: function (element) {
                $(element).valid();
            },
            submitHandler: function (form) {
                var url = "admin/change-password";
                var btnId = "#change_password";
                var data = new FormData(form);
                data.append("id", $("#id").val());

                saveAdminDetails(data, url, btnId);
            },
        });
    });

    $.validator.addMethod(
        "filesize",
        function (value, element, param) {
            if (element.files.length === 0) return true;
            return element.files[0].size <= param * 1024;
        },
        "File size must be less than {0} KB."
    );

    $.validator.addMethod(
        "notEqualTo",
        function (value, element, param) {
            return value !== $(param).val();
        },
        "New password cannot be the same as the current password."
    );

    function clearDropdown(dropdown) {
        dropdown.empty().append(
            $("<option>", {
                value: "",
                text: "Select",
                disabled: true,
                selected: true,
            })
        );
    }

    function getCountries(
        selectedCountry = null,
        selectedState = null,
        selectedCity = null
    ) {
        $.getJSON("/countries.json", function (data) {
            const countrySelect = $("#country");
            clearDropdown(countrySelect);

            $.each(data.countries, function (index, country) {
                countrySelect.append(
                    $("<option>", {
                        value: country.id,
                        text: country.name,
                        selected: country.id == selectedCountry,
                    })
                );
            });

            if (selectedCountry) {
                getStates(selectedCountry, selectedState, selectedCity);
            }
        }).fail(function () {
            toastr.error("Error loading country data");
        });
    }

    function getStates(
        selectedCountry,
        selectedState = null,
        selectedCity = null
    ) {
        $.getJSON("/states.json", function (data) {
            const stateSelect = $("#state");
            clearDropdown(stateSelect);

            const states = data.states.filter(
                (state) => state.country_id == selectedCountry
            );
            if (states.length === 1) {
                // Automatically select the single state
                stateSelect.append(
                    $("<option>", {
                        value: states[0].id,
                        text: states[0].name,
                        selected: true,
                    })
                );
                getCities(states[0].id, selectedCity); // Automatically load cities
            } else {
                $.each(states, function (index, state) {
                    stateSelect.append(
                        $("<option>", {
                            value: state.id,
                            text: state.name,
                            selected: state.id == selectedState,
                        })
                    );
                });

                if (selectedState) {
                    getCities(selectedState, selectedCity);
                }
            }
        }).fail(function () {
            toastr.error("Error loading state data");
        });
    }

    function getCities(selectedState, selectedCity = null) {
        $.getJSON("/cities.json", function (data) {
            const citySelect = $("#city");
            clearDropdown(citySelect);

            const cities = data.cities.filter(
                (city) => city.state_id == selectedState
            );
            if (cities.length === 1) {
                // Automatically select the single city
                citySelect.append(
                    $("<option>", {
                        value: cities[0].id,
                        text: cities[0].name,
                        selected: true,
                    })
                );
            } else {
                $.each(cities, function (index, city) {
                    citySelect.append(
                        $("<option>", {
                            value: city.id,
                            text: city.name,
                            selected: city.id == selectedCity,
                        })
                    );
                });
            }
        }).fail(function () {
            toastr.error("Error loading city data");
        });
    }

    $("#profile_image").on("change", function (event) {
        if ($(this).val() !== "") {
            $(this).valid();
        }
        let reader = new FileReader();
        reader.onload = function (e) {
            $("#imagePreview").attr("src", e.target.result).show();
            $(".upload_icon").hide();
        };
        reader.readAsDataURL(event.target.files[0]);
    });

    function saveAdminDetails(data, url, btnId) {
        $.ajax({
            url: "/api/" + url,
            type: "POST",
            data: data,
            enctype: "multipart/form-data",
            contentType: false,
            processData: false,
            cache: false,
            beforeSend: function () {
                $(btnId)
                    .attr("disabled", true)
                    .html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
            },
            success: function (response) {
                $(".error-text").text("");
                $(btnId)
                    .removeAttr("disabled")
                    .html($("#save_admin_profile").data("save"));
                $(".form-control").removeClass("is-invalid is-valid");
                if (response.code === 200) {
                    toastr.success(
                        $("#save_admin_profile").data("save_success")
                    );
                    getAdminDetails();
                }
                if ((btnId = "#change_password")) {
                    $("#current_password").val("");
                    $("#new_password").val("");
                    $("#confirm_password").val("");
                    $(".pass-group").find("span").removeClass("me-3");
                }
            },
            error: function (error) {
                $(".error-text").text("");
                $(btnId)
                    .removeAttr("disabled")
                    .html($("#save_admin_profile").data("save"));
                $(".form-control").removeClass("is-invalid is-valid");
                if (error.responseJSON.code === 422) {
                    $.each(error.responseJSON.errors, function (key, val) {
                        $("#" + key).addClass("is-invalid");
                        $("#" + key + "_error").text(val[0]);
                    });
                } else {
                    toastr.error(error.responseJSON.message);
                }
            },
        });
    }

    function getAdminDetails() {
        $.ajax({
            url: "/api/get-admin-details",
            type: "POST",
            data: {
                id: localStorage.getItem("user_id"),
                isMobile: 1,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    const data = response.data;
                    if (
                        data.user_details &&
                        data.user_details.profile_image != null
                    ) {
                        $(".headerProfileImg").attr(
                            "src",
                            data.user_details.profile_image
                        );
                    }
                }
            },
            error: function (xhr, status, error) {
                toastr.error(xhr.responseText);
            },
        });
    }
}

if (pageValue === "admin.bookinglist") {
    $(document).ready(function () {
        list_table();
        // Handle search input
        $("#searchLanguage").on("input", function () {
            list_table(1, "all-booking"); // Reset to the first page on new search
        });
    });

    // Automatically load data for the active tab on page load
    list_table(1, "all-booking");
    $(".bookingtab button").on("click", function (e) {
        e.preventDefault();

        // Update active tab
        $(".bookingTabs button").removeClass("active");
        $(this).addClass("active");

        // Get the selected tab's data
        var tab = $(this).attr("data-tab_type");
        list_table(1, tab);
    });
    function list_table(page, param) {
        $.ajax({
            url: "/api/bookinglists",
            type: "POST",
            dataType: "json",
            data: {
                type: param,
                order_by: "desc",
                sort_by: "id",
                page: page,
                search: $("#searchLanguage").val(),
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code == "200") {
                    listTable(response.data, response.meta);
                } else {
                    toastr.error(response.message);
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
                        toastr.error("An error occurred while fetching list.");
                    }
                } else {
                    toastr.error("An error occurred while fetching list.");
                }
                toastr.error("Error fetching list:", error);
            },
        });
    }

    function listTable(list, meta) {
        let tableBody = "";
        if (list["bookingdata"].length > 0) {
            i = 0;
            list["bookingdata"].forEach((data) => {
                i++;
                tableBody += `
                    <tr>
                        <td>${data.order_id}</td>
                        <td>${data.bookingdate}</td>
                        <td>
                            ${
                                data?.product?.created_by?.user_details
                                    ?.first_name ?? ""
                            }
                            ${
                                data?.product?.created_by?.user_details
                                    ?.last_name ?? ""
                            }
                        </td>
                        <td>
                            ${data?.user?.user_details?.first_name ?? ""}
                            ${data?.user?.user_details?.last_name ?? ""}
                        </td>
                        <td>${data.source_name}</td>
                        <td>${list["currency"]}${data.serviceamount}</td>
                        <td><span class="booking-status  fs-14" data-status="${
                            data.booking_status
                        }">${data.booking_status_label}</spn></td>`;
                tableBody += `
                            <td>  <a href="#"
                        class="view-bookinglist" data-bs-toggle="modal" data-bs-target="#view-modal"
                        data-id="${data.id}" data-date="${data.bookingdate}"
                data-order-id="${data.order_id}"
                data-status="${data.booking_status_label}"  data-provider="${
                    data?.product?.created_by?.user_details?.first_name ?? ""
                }" data-amount="${list["currency"]}${
                    data.serviceamount
                }"  data-username="${
                    data?.user?.user_details?.first_name ?? ""
                }" data-product="${data.source_name}" data-address="${
                    data.user_city
                }">
                           <i class="ti ti-eye fs-20 m-3"></i>
                    </a>
                            </td>
                </tr>`;
            });
        } else {
            if (!list || list.length === 0) {
                $("#ListTable").DataTable().destroy();
                var nodata = "No Data found";
                if (languageId === 2) {
                    loadJsonFile("No Data found", function (langtst) {
                        var nodata = langtst;
                    });
                }
                $("#ListTable").DataTable({
                    ordering: true,
                    pageLength: 10,
                    language: datatableLang,
                    order: [[0, "desc"]],
                });
            }
        }
        if ($.fn.DataTable.isDataTable("#ListTable")) {
            $("#ListTable").DataTable().destroy(); // Destroy previous instance
        }
        $("#ListTable tbody").html(tableBody);
        applyBookingStatusStyles();

        $("#loader-table").addClass("d-none");
        $(".label-loader, .input-loader").hide();
        $("#ListTable, .real-label, .real-input").removeClass("d-none");

        $("#ListTable").DataTable({
            ordering: true,
            pageLength: 10,
            language: datatableLang,
            order: [[0, "desc"]],
        });
    }

    function formatDate(dateString) {
        const date = new Date(dateString); // Parse the date string
        const day = String(date.getDate()).padStart(2, "0"); // Get day with leading zero
        const month = String(date.getMonth() + 1).padStart(2, "0"); // Months are 0-indexed
        const year = date.getFullYear(); // Get full year
        return `${day}-${month}-${year}`; // Return formatted date
    }

    function applyBookingStatusStyles() {
        $(".booking-status").each(function () {
            const status = $(this).data("status");
            let statusClass = "";
            let statusText = "";

            switch (status) {
                case 1:
                    statusClass = "badge badge-primary-transparent ms-2";
                    statusText = "Open";
                    break;
                case 2:
                    statusClass = "badge badge-soft-info ms-2";
                    statusText = "In progress";
                    break;
                case 3:
                    statusClass = "badge badge-soft-danger ms-2";
                    statusText = "Provider Cancelled";
                    break;
                case 4:
                    statusClass = "badge badge-soft-warning ms-2";
                    statusText = "Refund Initiated";
                    break;
                case 5:
                    statusClass = "badge badge-soft-success ms-2";
                    statusText = "Completed";
                    break;
                case 6:
                    statusClass = "badge badge-soft-success ms-2";
                    statusText = "Order Completed";
                    break;
                case 7:
                    statusClass = "badge badge-soft-success ms-2";
                    statusText = "Refund Completed";
                    break;
                case 8:
                    statusClass = "badge badge-soft-danger ms-2";
                    statusText = "Customer Cancelled";
                    break;

                default:
                    statusClass = "status-unknown";
                    statusText = "Unknown";
            }

            $(this).addClass(statusClass).text(statusText);
        });
    }

    $(document).on("click", ".view-bookinglist", function (e) {
        e.preventDefault();
        const source_name = $(this).data("product");
        const date = $(this).data("date");
        const username = $(this).data("username");
        const provider = $(this).data("provider");
        const user_address = $(this).data("address");
        const amount = $(this).data("amount");
        const booking_status = $(this).data("status");
        const order_id = $(this).data("order-id");

        document.getElementById("order_id").innerText = order_id;
        document.getElementById("modalTitle").innerText = source_name;
        document.getElementById("modalDate").innerText = date;
        document.getElementById("user").innerText = username;
        document.getElementById("provider").innerText = provider;
        document.getElementById("location").innerText = user_address;
        document.getElementById("amount").innerText = amount;
        document.getElementById("status").innerText = booking_status;
    });
}

if (pageValue === "admin.footer-builder") {
    let lg_save = "Save";
    
    function initSummernote(placeholder = "") {
        var resolvedPlaceholder =
            placeholder === "" ? "Enter Footer Content" : placeholder;

        $(".custom-summernote").each(function () {
            if ($(this).next(".note-editor").length) {
                $(this).summernote("destroy");
            }
        });

        $(".custom-summernote").summernote({
            height: 200,
            width: "100%",
            toolbar: [
                ["style", ["style"]],
                [
                    "font",
                    [
                        "bold",
                        "italic",
                        "underline",
                        "strikethrough",
                        "superscript",
                        "subscript",
                        "clear",
                    ],
                ],
                ["fontname", ["fontname"]],
                ["fontsize", ["fontsize"]],
                ["color", ["color"]],
                ["para", ["ul", "ol", "paragraph"]],
                ["height", ["height"]],
                ["table", ["table"]],
                ["insert", ["link", "picture", "video"]],
                ["view", ["fullscreen", "codeview", "help"]],
            ],
            placeholder: resolvedPlaceholder,
        });
    }

    $(document).ready(function () {
        // initSummernote();

        var langCode = $("body").data("lang");

        listFooter("", langCode);
    });

    $("#footerForm").on("submit", function (e) {
        e.preventDefault();

        let formData = $(this).serialize();

        $.ajax({
            url: "/api/admin/save-footer-builder",
            type: "POST",
            data: formData,
            dataType: "json",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $("#save_footer")
                    .attr("disabled", true)
                    .html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
            },
            success: function (response) {
                $(".error-text").text("");
                $("#save_footer").removeAttr("disabled").html(lg_save);
                $(".form-control").removeClass("is-invalid is-valid");
                if (response.code === 200) {
                    toastr.success(response.message);
                }
            },
            error: function (error) {
                $(".error-text").text("");
                $("#save_footer").removeAttr("disabled").html(lg_save);
                $(".form-control").removeClass("is-invalid is-valid");
                if (error.responseJSON.code === 422) {
                    $.each(error.responseJSON.errors, function (key, val) {
                        $("#" + key).addClass("is-invalid");
                        $("#" + key + "_error").text(val[0]);
                    });
                } else {
                    toastr.error(error.responseJSON.message);
                }
            },
        });
    });

    function listFooter(langId = "", langCode = "") {
        $.ajax({
            url: "/api/admin/list-footer-builder",
            type: "POST",
            data: {
                language_id: langId,
                language_code: langCode,
            },
            dataType: "json",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                initSummernote();
                if (response.code === 200) {
                    if (response.data != null) {
                        const footers = response.data.footer_content;
                        footers.forEach((footer, index) => {
                            index = index + 1;
                            $("#section_title_" + index).val(footer.title);
                            $("#status_" + index).prop(
                                "checked",
                                footer.status == 1
                            );
                            $("#footer_content_" + index).summernote(
                                "code",
                                footer.footer_content
                            );
                        });
                        $("#status").prop("checked", response.data.status == 1);
                        $("#id").val(response.data.id);
                    } else {
                        $(".form-control").val("");
                        $("#footer_content_1").summernote("code", "");
                        $("#footer_content_2").summernote("code", "");
                        $("#footer_content_3").summernote("code", "");
                        $("#footer_content_4").summernote("code", "");
                        $("#footer_content_5").summernote("code", "");
                    }
                }
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
            },
            error: function (error) {
                initSummernote();
                toastr.error(error.responseJSON.message);
            },
        });
    }

    $("#language_id").on("change", function () {
        var langId = $(this).val();

        languageTranslate(langId);
        listFooter(langId);
    });

    function languageTranslate(lang_id) {
        $.ajax({
            url: "/api/translate",
            type: "POST",
            dataType: "json",
            data: {
                language_id: lang_id,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                const trans = response.translated_values;

                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid is-valid");

                if (response.code === 200 && Object.keys(trans).length > 0) {
                    var placeholder = trans.enter_footer_content;
                    initSummernote(placeholder);

                    $("#section_title_1").attr(
                        "placeholder",
                        trans.enter_section_title
                    );
                    $("#section_title_2").attr(
                        "placeholder",
                        trans.enter_section_title
                    );
                    $("#section_title_3").attr(
                        "placeholder",
                        trans.enter_section_title
                    );
                    $("#section_title_4").attr(
                        "placeholder",
                        trans.enter_section_title
                    );
                    $("#section_title_5").attr(
                        "placeholder",
                        trans.enter_section_title
                    );

                    $("#save_footer").text(trans.Save);

                    $(".lang_title").text(trans.available_translations);
                    lg_save = trans.Save;

                    $(".translate-key").each(function () {
                        var translateKey = $(this).data("translate");
                        if (trans.hasOwnProperty(translateKey)) {
                            var translatedText = trans[translateKey];
                            $(this).text(translatedText);
                        }
                    });
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }
}

//User Request Dispite List
if (pageValue === "admin.request.dispute") {
    function request_table() {
        fetchRequest(1);
    }

    function fetchRequest(dispute) {
        $.ajax({
            url: "/api/booking/request-dispute",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
                sort_by: "id",
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    populateRequest(response.data);
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
                } else {
                    toastr.error("An error occurred while fetching.");
                }
            },
        });
    }

    $(document).ready(function () {
        request_table();
    });

    function populateRequest(Dispute) {
        let tableBody = "";

        if (Dispute.length > 0) {
            Dispute.forEach((Dispute, index) => {
                tableBody += `
                    <tr>
                            <td>${index + 1}</td>
                            <td>${Dispute.order_id}</td>
                            <td>${Dispute.user.name}</td>
                            <td>${Dispute.provider.name}</td>
                            <td>${Dispute.product.source_name}</td>
                            <td>
                                <span class="badge ${
                                    Dispute.status == "1"
                                        ? "badge-soft-success"
                                        : "badge-soft-danger"
                                } d-inline-flex align-items-center">
                                    <i class="ti ti-circle-filled fs-5 me-1"></i>${
                                        Dispute.status == "1"
                                            ? "Active"
                                            : "Inactive"
                                    }
                                </span>
                            </td>
                            ${
                                $("#has_permission").data("visible") == 1
                                    ? `<td><li style="list-style: none;">
                                ${
                                    $("#has_permission").data("visible") == 1
                                        ? `<a class="edit_dispute_data"
                                               href="#"
                                               data-bs-toggle="modal"
                                               data-bs-target="#edit_dispute"
                                               data-id="${Dispute.id}"
                                               data-subject="${Dispute.subject}"
                                               data-content="${Dispute.content}"
                                               data-admin_reply="${Dispute.admin_reply}"
                                               data-status="${Dispute.status}">
                                               <i class="ti ti-pencil fs-20"></i>
                                            </a>`
                                        : ""
                                }
                                        </li>
                            </td>`
                                    : ""
                            }
                        </tr>
                    `;
            });
        } else {
            $("#datatable_dispute").DataTable().destroy();

            tableBody = `
                    <tr>
                        <td colspan="7" class="text-center">${$(
                            "#datatable_dispute"
                        ).data("empty")}</td>
                    </tr>
                `;
        }

        $("#datatable_dispute tbody").html(tableBody);
        $("#loader-table").addClass("d-none");
        $(".label-loader, .input-loader").hide();
        $("#datatable_dispute, .real-label, .real-input").removeClass("d-none");

        if (
            Dispute.length != 0 &&
            !$.fn.dataTable.isDataTable("#datatable_dispute")
        ) {
            $("#datatable_dispute").DataTable({
                ordering: true,
                language: datatableLang,
            });
        }
    }

    $(document).on("click", ".edit_dispute_data", function (e) {
        e.preventDefault();

        var Id = $(this).data("id");
        var subject = $(this).data("subject");
        var content = $(this).data("content");
        var admin_reply = $(this).data("admin_reply");
        var status = $(this).data("status");

        $("#edit_id").val(Id);
        $("#edit_subject").val(subject);
        $("#edit_content").val(content);
        $("#edit_reply").val(admin_reply);
        $("#edit_status").prop("checked", status == 1);
    });

    $(document).ready(function () {
        $("#editDisputeForm").submit(function (event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: "/api/booking/raise-dispute/update",
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                beforeSend: function () {
                    $(".edit_btn").attr("disabled", true);
                    $(".edit_btn").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
                .done((response, statusText, xhr) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".edit_btn").removeAttr("disabled");
                    $(".edit_btn").html($(".edit_btn").data("update"));
                    if (response.code === 200) {
                        toastr.success(response.message);
                        request_table();
                        $("#edit_dispute").modal("hide");
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".edit_btn").removeAttr("disabled");
                    $(".edit_btn").html($(".edit_btn").data("update"));

                    if (error.status == 422) {
                        $.each(error.responseJSON, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(responseJSON.message);
                    }
                });
        });
    });

    if (pageValue === "admin.dashboard") {
        document.addEventListener(
            "DOMContentLoaded",
            requestPermissionAndGetToken
        );
        $.ajax({
            url: "/api/gettotalbookingcount",
            type: "POST",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code == "200") {
                    var data = response.data;
                    if (data != "") {
                        $(".completecount").text(data.completed_count);
                        $(".cancelcount").text(data.cancelled_count);
                        $(".upcomingcount").text(data.upcoming_count);
                        $(".totalincome").text(
                            currency + data.overall_total_amount
                        );
                        $(".completeincome").text(
                            currency + data.completed_total_amount
                        );
                        $(".totaldue").text(currency + data.due_amount);
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                toastr.error("An error occurred while fetching the data.");
            },
        });
        $.ajax({
            url: "/api/getsubscription",
            type: "POST",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code == "200") {
                    var data = response.data;
                    if (data != "") {
                        $(".plantitle").text(data.package_title);
                        $(".planprice").text(data.price);
                        $(".duration").text("/ " + data.package_term);
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                toastr.error("An error occurred while fetching the data.");
            },
        });
        $.ajax({
            url: "/api/getlatestbookings",
            type: "POST",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code == "200") {
                    var data = response.data;
                    const bookdiv = $(".book-crd");
                    if (data != "") {
                        response.data.forEach((val) => {
                            bookdiv.append(`<div class="card">
                                        <div class="card-body"><div class="d-flex justify-content-between align-items-center flex-wrap row-gap-2">
                                            <div class="d-flex align-items-center">
                                                <a href="booking-details.html" class="avatar avatar-lg flex-shrink-0 me-2">
                                                    <img src="assets/img/services/service-63.jpg" class="rounded-circle" alt="Img">
                                                </a>
                                                <div>
                                                    <a href="booking-details.html" class="fw-medium">${val.product_name}</a>
                                                    <span class="d-block fs-12"><i class="ti ti-clock me-1"></i>${val.fromtime} - ${val.totime}</span>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <a href="booking-details.html" class="avatar avatar-sm me-2">
                                                    <img src="assets/img/user/user-01.jpg" class="rounded-circle" alt="user">
                                                </a>

                                            </div>
                                        </div> </div>
                                        </div>`);
                        });
                    } else {
                        html = `<div class="text-center">No Data Found </div>`;
                        bookdiv.append(html);
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                toastr.error("An error occurred while fetching the data.");
            },
        });
        $.ajax({
            url: "/api/getlatestreviews",
            type: "POST",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code == "200") {
                    var data = response.data;
                    const ratingdiv = $(".ratecard");
                    if (data != "") {
                        data.forEach((val) => {
                            var ratehtml = ` <div class=" border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center flex-wrap row-gap-2">
                                    <div class="d-flex">
                                        <a href="javascript:void(0);" class="avatar avatar-lg flex-shrink-0 me-2">
                                            <img src="/assets/img/profile-default.png" class="rounded-circle" alt="Img">
                                        </a>
                                        <div>
                                            <div class="fw-medium">${val.provider_name}</div>
                                            <div class="d-flex align-items-center">
                                                <p class="fs-12 mb-0 pe-2 border-end">For <span class="text-info">${val.product_name}</span></p>
                                                <span class="avatar avatar-sm mx-2">
                                                    <img src="assets/img/profile-default.png" class="img-fluid rounded-circle" alt="user">
                                                </span>
                                                <span class="fs-12">${val.username}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <span class="text-warning fs-10 me-1">`;
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= Math.floor(val.rating)) {
                                    ratehtml += `<i class="ti ti-star-filled filled"></i>`;
                                } else {
                                    ratehtml += `<i class="ti ti-star"></i>`;
                                }
                            }
                            ratehtml += `</span>
                                        <span class="fs-12">${val.rating}</span>
                                    </div>
                                </div>
                            </div>`;
                            ratingdiv.append(ratehtml);
                        });
                    } else {
                        html = `<div class="text-center">No Data Found </div>`;
                        ratingdiv.append(html);
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                toastr.error("An error occurred while fetching the data.");
            },
        });
        $.ajax({
            url: "/api/getlatestproductservice",
            type: "POST",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code == "200") {
                    var data = response.data;
                    const servicediv = $(".servicecard");
                    if (data != "") {
                        data.forEach((val) => {
                            var servicehtml = `<div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex">
                                        <a href="service-details.html" class="avatar avatar-lg me-2">
                                            <img src="assets/img/services/service-56.jpg" class="rounded-circle" alt="Img">
                                        </a>
                                        <div>
                                            <a href="service-details.html" class="fw-medium mb-0">${val.product_name}</a>
                                            <div class="fs-12 d-flex align-items-center gap-2">
                                                <span class="pe-2 border-end">${val.total_bookings} Bookings</span>`;
                            if (val.average_rating != "") {
                                servicehtml += `<span><i class="ti ti-star-filled text-warning me-1 me-1"></i>${val.average_rating}</span>`;
                            }
                            servicehtml += `  </div>
                                        </div>
                                    </div>
                                 </div>`;

                            servicediv.append(servicehtml);
                        });
                    } else {
                        servicehtml = `<div class="text-center">No Data Found </div>`;
                        servicediv.append(servicehtml);
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                toastr.error("An error occurred while fetching the data.");
            },
        });
    }
}

var currency = getcurrency();
if (pageValue === "admin.subscriptionlist") {
    $(document).ready(function () {
        list_table();
        // Handle search input
        $("#searchLanguage").on("input", function () {
            list_table(1); // Reset to the first page on new search
        });
    });

    function list_table(page) {
        $.ajax({
            url: "/api/getsubscriptionlist",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
                sort_by: "id",
                page: page,
                search: $("#searchLanguage").val(),
                type: type,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code == "200") {
                    listTable(response.data, response.meta);
                } else {
                    toastr.error(response.message);
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
                        toastr.error("An error occurred while fetching list.");
                    }
                } else {
                    toastr.error("An error occurred while fetching list.");
                }
            },
        });
    }

    function listTable(list, meta) {
        let tableBody = "";

        if (list.length > 0) {
            list.forEach((data, index) => {
                let paymentStatus = "";
                let paymentStatusClass = "badge-soft-danger";
                if (data.payment_status == 3) {
                    paymentStatus = "Pending Verification";
                    paymentStatusClass = "badge-soft-warning";
                } else if (data.payment_status == 2) {
                    paymentStatus = "Paid";
                    paymentStatusClass = "badge-soft-success";
                } else {
                    paymentStatus = "Un Paid";
                    paymentStatusClass = "badge-soft-danger";
                }

                tableBody += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${data.package_title}</td>
                        <td>${currency}${data.price}</td>
                        <td>${data.subscription_type ?? ""}</td>
                        <td>${data.description ?? ""}</td>
                        <td>${data.name ?? ""}</td>
                        <td>${data.trx_date ?? ""}</td>
                        <td>${data.end_date ?? ""}</td>
                        <td>
                            <span class="badge ${paymentStatusClass} d-inline-flex align-items-center">
                                <i class="ti ti-circle-filled fs-5 me-1"></i>${paymentStatus}
                            </span>
                        </td>
                        <td>
                            <span class="badge ${
                                data.status == 1
                                    ? "badge-soft-success"
                                    : "badge-soft-danger"
                            } d-inline-flex align-items-center">
                                <i class="ti ti-circle-filled fs-5 me-1"></i>${
                                    data.status == 1 ? "Active" : "Inactive"
                                }
                            </span>
                        </td>
                        <td>
                            <div class="text-center d-flex justify-content-center">
                                ${
                                    data.payment_proof
                                        ? `<a href="${data.payment_proof}" target="_blank" class="me-1">
                                        <i class="ti ti-eye fs-4"></i>
                                    </a>`
                                        : "-"
                                }

                                ${
                                    data.payment_type == 4 &&
                                    data.payment_status == 3
                                        ? `<button type="button" class="btn btn-sm btn-success activate-payment" data-bs-toggle="modal" data-bs-target="#verifyPaymentModal" data-id="${data.id}">
                                        <i class="ti ti-check"></i>
                                    </button>`
                                        : ""
                                }
                            </div>
                        </td>
                </tr>`;
            });
            $("#ListTable tbody").html(tableBody);
        } else {
            if (!list || list.length === 0) {
                $("#ListTable").DataTable().destroy();
                $("#ListTable").DataTable({
                    paging: false,
                    language: {
                        emptyTable: "No Data found",
                    },
                    // Other DataTable options
                });
            }
        }

        $("#loader-table").addClass("d-none");
        $(".label-loader, .input-loader").hide();
        $("#ListTable, .real-label, .real-input").removeClass("d-none");

        if (!$.fn.dataTable.isDataTable("#ListTable")) {
            if (languageId === 2) {
                if (
                    $("#ListTable").length &&
                    !$.fn.DataTable.isDataTable("#ListTable")
                ) {
                    $("#ListTable").DataTable({
                        ordering: true,
                        paging: true,
                        pageLength: 10,
                        language: datatableLang,
                    });
                }
            } else {
                $("#ListTable").DataTable({
                    ordering: true,
                });
            }
        }
    }

    $(document).on("click", ".activate-payment", function () {
        let trxId = $(this).data("id");
        $("#trx_id").val(trxId);
    });

    $(document).on("click", "#confirmVerifyBtn", function (e) {
        e.preventDefault();

        $.ajax({
            url: "/admin/verify-banktransfer-payment",
            type: "POST",
            data: {
                trx_id: $("#trx_id").val(),
                language_code: $("body").data("lang"),
            },
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $("#confirmVerifyBtn")
                    .attr("disabled", true)
                    .html(
                        `<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span> ${$(
                            "#confirmVerifyBtn"
                        ).data("verifying")}`
                    );
            },
            complete: function () {
                $("#confirmVerifyBtn")
                    .attr("disabled", false)
                    .html($("#confirmVerifyBtn").data("yes_verify"));
            },
            success: function (response) {
                if (response.code == 200) {
                    toastr.success(response.message);
                    $("#verifyPaymentModal").modal("hide");
                    list_table(1);
                }
            },
            error: function (xhr, status, error) {
                toastr.error(xhr.responseJSON.message);
            },
        });
    });

    function setupPagination(meta) {
        let paginationHtml = "";
        for (let i = 1; i <= meta.last_page; i++) {
            paginationHtml += `<li class="page-item ${
                meta.current_page === i ? "active" : ""
            }"><a class="page-link" href="#">${i}</a></li>`;
        }

        // Handle click event for pagination
        $("#pagination").on("click", ".page-link", function (e) {
            e.preventDefault();
            const page = $(this).text();
        });
    }

    $(document).on("click", ".view-user", function (e) {
        e.preventDefault();
        const userId = $(this).data("id");
        $.ajax({
            url: "/admin/viewuserdata",
            type: "POST",
            data: {
                id: userId,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                const routeUrl = "/admin/viewuser";
                window.location.href = routeUrl;
            },
        });
    });
}

if (pageValue === "admin.reviews") {
    $(document).ready(function () {
        getReviewList(1);
    });

    function getReviewList(page = 1) {
        const perPage = $("#entries_per_page").val();

        $.ajax({
            url: "/api/get-review-list",
            type: "POST",
            data: {
                per_page: perPage,
                page: page,
            },
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    let reviews = response.data.data;
                    let totalReviews = response.data.total;
                    let lastPage = response.data.last_page;
                    let currentPage = response.data.current_page;

                    $(".review_list_container").empty();

                    if (reviews.length > 0) {
                        reviews.forEach((review) => {
                            let filledStars = "";
                            for (let i = 1; i <= 5; i++) {
                                filledStars +=
                                    i <= review.rating
                                        ? '<span><i class="ti ti-star-filled text-warning"></i></span>'
                                        : '<span><i class="ti ti-star text-muted"></i></span>';
                            }

                            $(".review_list_container").append(`
                                <div class="col-xxl-12 col-lg-12">
                                    <div class="card shadow-none">
                                        <div class="card-body">
                                            <div class="d-md-flex align-items-center">
                                                <div class="review-widget d-sm-flex flex-fill">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <div class="d-flex">
                                                            <span class="review-img me-2">
                                                                <img src="${
                                                                    review.service_image
                                                                }" class="rounded img-fluid" alt="Service Image">
                                                            </span>
                                                            <div>
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                    <div class="d-flex align-items-center">
                                                                        <h6 class="fs-14 me-2">${
                                                                            review.service_name
                                                                        }</h6>
                                                                        ${filledStars}
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="avatar avatar-sm me-2">
                                                                        <img src="${
                                                                            review.profile_image
                                                                        }" class="rounded-circle " alt="Img">
                                                                    </span>
                                                                    <h6 class="fs-13 me-2">${
                                                                        review.full_name
                                                                    }</h6>
                                                                    <span class="fs-12">${
                                                                        review.review_date
                                                                    }</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                ${
                                                    $("#has_permission").data(
                                                        "delete"
                                                    ) == 1
                                                        ? `<div class="user-icon d-inline-flex">
                                                        <a href="#" class="delete_review_btn" data-id="${review.id}" data-bs-toggle="modal" data-bs-target="#del-review"><i class="ti ti-trash m-3 fs-20"></i></a>
                                                    </div>`
                                                        : ""
                                                }
                                            </div>
                                            <div>
                                                <p class="fs-14">${
                                                    review.review
                                                }</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `);
                        });

                        $("#paginate_container").removeClass("d-none");
                    } else {
                        $("#paginate_container").addClass("d-none");
                        $(".review_list_container").append(`
                            <div class="col-xxl-12 col-lg-12">
                                <div class="card shadow-none">
                                    <div class="card-body d-flex align-items-center justify-content-center">
                                        <span class="text-center text-black">${$(
                                            ".review_list_container"
                                        ).data("empty")}</span>
                                    </div>
                                </div>
                            </div>
                        `);
                    }

                    let paginationLinks = "";

                    paginationLinks += `
                        <li class="page-item ${
                            currentPage === 1 ? "disabled" : ""
                        }">
                            <a class="page-link" href="javascript:void(0);" onclick="getReviewList(${
                                currentPage - 1
                            });">${$(".review_list_container").data("prev")}</a>
                        </li>
                    `;

                    for (let i = 1; i <= lastPage; i++) {
                        paginationLinks += `
                            <li class="page-item ${
                                i === currentPage ? "active" : ""
                            }">
                                <a class="page-link" href="javascript:void(0);" onclick="getReviewList(${i});">${i}</a>
                            </li>
                        `;
                    }

                    paginationLinks += `
                        <li class="page-item ${
                            currentPage === lastPage ? "disabled" : ""
                        }">
                            <a class="page-link" href="javascript:void(0);" onclick="getReviewList(${
                                currentPage + 1
                            });">${$(".review_list_container").data("next")}</a>
                        </li>
                    `;

                    $("#pagination_links").html(paginationLinks);

                    $(".label-loader, .input-loader").hide();
                    $(".real-label, .real-input").removeClass("d-none");
                    $("#reviewLoader").addClass("d-none").empty();
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }

    $(document).on("click", ".delete_review_btn", function () {
        var id = $(this).data("id");
        $("#deleteReviewConfirm").data("id", id);
    });

    $(document).on("click", "#deleteReviewConfirm", function (e) {
        e.preventDefault();

        var id = $(this).data("id");

        $.ajax({
            url: "/api/delete-review",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                review_id: id,
            },
            beforeSend: function () {
                $("#deleteReviewConfirm")
                    .attr("disabled", true)
                    .html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
            },
            success: function (response) {
                $("#deleteReviewConfirm")
                    .removeAttr("disabled")
                    .html($("#deleteReviewConfirm").data("delete"));

                if (response.code === 200) {
                    $("#del-review").modal("hide");
                    toastr.success(response.message);
                    getReviewList(1);
                }
            },
            error: function (error) {
                $("#deleteReviewConfirm")
                    .removeAttr("disabled")
                    .html($("#deleteReviewConfirm").data("delete"));
                toastr.error(error.responseJSON.message);
            },
        });
    });
}

function getcurrency() {
    $.ajax({
        url: "/api/getdefaultcurrency", // Laravel route
        type: "POST",
        success: function (response) {
            currency = "$";
            if (response.code == 200) {
                if (response.data) {
                    currency = response.data.symbol;
                }
            }
            return currency;
        },
        error: function (xhr) {
            toastr.error("Error:", xhr.responseText);
        },
    });
}

if (pageValue === "editservice") {
    $("#service_price").on("input", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/g, "")
        );

        if ($(this).val().length > 5) {
            $(this).val($(this).val().slice(0, 5));
        }
    });

    $("#fixed_price").on("input", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/g, "")
        );

        if ($(this).val().length > 5) {
            $(this).val($(this).val().slice(0, 5));
        }
    });

    var currentLang = $("body").data("lang");

    const validationMessage = {
        en: {
            source_name: {
                required: "The name field is required.",
                minlength: "The name must be at least 5 characters.",
                maxlength: "The name cannot exceed 255 characters.",
            },
            source_code: {
                required: "The service code is required.",
                minlength: "The service code must be at least 3 characters.",
                maxlength: "The service code cannot exceed 100 characters.",
            },
            category: {
                required: "Please select a category.",
            },
            Subcategory_fied: {
                required: "Please select a subcategory.",
            },
            source_desc: {
                required: "The description is required.",
                minlength: "The description must be at least 10 characters.",
            },
            price_type: {
                required: "Please select a price type.",
            },
            fixed_price: {
                required: "The price is required.",
                number: "The price must be a valid number.",
            },
            "source_country[]": {
                required: "Please select at least one country.",
            },
            "source_city[]": {
                required: "Please select at least one city.",
            },
            "service_name[]": {
                required: "The service name is required.",
                minlength: "The service name must be at least 3 characters.",
            },
            "service_price[]": {
                required: "The service price is required.",
                number: "The service price must be a valid number.",
            },
            "service_desc[]": {
                required: "The service description is required.",
                minlength:
                    "The service description must be at least 10 characters.",
            },
            seo_title: {
                required: "The SEO title is required.",
                maxlength: "The SEO title cannot exceed 100 characters.",
            },
            tags: {
                required: "Please enter tags.",
                minlength: "Tags must be at least 3 characters.",
            },
            content: {
                required: "The SEO description is required.",
                minlength: "The SEO description must be at least 10 characters.",
            },
            "logo[]": {
                required: "The service image is required.",
                extension: "Only JPG, JPEG, PNG, WEBP, and SVG files are allowed.",
            },
        },
        ar: {
            source_name: {
                required: "حقل الاسم مطلوب.",
                minlength: "يجب أن يكون الاسم على الأقل 5 أحرف.",
                maxlength: "لا يمكن أن يتجاوز الاسم 255 حرفًا.",
            },
            source_code: {
                required: "حقل رمز الخدمة مطلوب.",
                minlength: "يجب أن يكون رمز الخدمة على الأقل 3 أحرف.",
                maxlength: "لا يمكن أن يتجاوز رمز الخدمة 100 حرف.",
            },
            category: {
                required: "يرجى اختيار الفئة.",
            },
            Subcategory_fied: {
                required: "يرجى اختيار الفئة الفرعية.",
            },
            source_desc: {
                required: "الوصف مطلوب.",
                minlength: "يجب أن يكون الوصف على الأقل 10 أحرف.",
            },
            price_type: {
                required: "يرجى اختيار نوع السعر.",
            },
            fixed_price: {
                required: "السعر مطلوب.",
                number: "يجب أن يكون السعر رقمًا صالحًا.",
            },
            "source_country[]": {
                required: "يرجى اختيار بلد واحد على الأقل.",
            },
            "source_city[]": {
                required: "يرجى اختيار مدينة واحدة على الأقل.",
            },
            "service_name[]": {
                required: "اسم الخدمة مطلوب.",
                minlength: "يجب أن يكون اسم الخدمة على الأقل 3 أحرف.",
            },
            "service_price[]": {
                required: "سعر الخدمة مطلوب.",
                number: "يجب أن يكون سعر الخدمة رقمًا صالحًا.",
            },
            "service_desc[]": {
                required: "وصف الخدمة مطلوب.",
                minlength: "يجب أن يكون وصف الخدمة على الأقل 10 أحرف.",
            },
            seo_title: {
                required: "عنوان تحسين محركات البحث (SEO) مطلوب.",
                maxlength:
                    "لا يمكن أن يتجاوز عنوان تحسين محركات البحث (SEO) 100 حرف.",
            },
            tags: {
                required: "يرجى إدخال العلامات.",
                minlength: "يجب أن تكون العلامات على الأقل 3 أحرف.",
            },
            content: {
                required: "وصف تحسين محركات البحث (SEO) مطلوب.",
                minlength:
                    "يجب أن يكون وصف تحسين محركات البحث (SEO) على الأقل 10 أحرف.",
            },
            "logo[]": {
                required: "صورة الخدمة مطلوبة.",
                extension: "يُسمح فقط بملفات JPG و JPEG و PNG و WEBP و SVG.",
            },
        },
    };

    $(document).ready(function () {
        $("#adminUpdateService").validate({
            rules: {
                source_name: {
                    required: true,
                    minlength: 5,
                    maxlength: 255,
                },
                source_code: {
                    required: true,
                    minlength: 3,
                    maxlength: 100,
                },
                category: {
                    required: true,
                },
                Subcategory_fied: {
                    required: true,
                },
                source_desc: {
                    required: true,
                    minlength: 10,
                },
                price_type: {
                    required: true,
                },
                fixed_price: {
                    required: true,
                    number: true,
                },
                country: {
                    required: false,
                },
                state: {
                    required: false,
                },
                city: {
                    required: false,
                },
                "service_name[]": {
                    minlength: 3,
                },
                "service_price[]": {
                    number: true,
                },
                "service_desc[]": {
                    minlength: 10,
                },
                seo_title: {
                    required: true,
                    maxlength: 100,
                },
                tags: {
                    required: true,
                    minlength: 3,
                },
                content: {
                    required: true,
                    minlength: 10,
                },
                "logo[]": {
                    required: false,
                    extension: "jpg|jpeg|png|svg|webp",
                },
            },
            messages: validationMessage[currentLang],
            errorElement: "span",
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback");
                element.closest(".mb-3").append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass("is-invalid").addClass("is-valid");
            },
            submitHandler: function (form) {
                // Allow form submission
                form.submit();
            },
        });
    });

    $(".categoryProviderSelect").on("change", function () {
        const categoryId = $(this).val();

        const subcategoriesDropdown = $(".subcategories");

        if (categoryId) {
            fetchSubcategories(categoryId);
        }
    });

    function fetchSubcategories(categoryId, selectedSubcategory = null) {
        $.ajax({
            url: "/get-subcategories",
            type: "POST",
            data: { category_id: categoryId },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                let subcategoriesHtml =
                    '<option value="">Select Sub Category</option>';
                data.forEach((subcategory) => {
                    subcategoriesHtml += `<option value="${subcategory.id}" ${
                        subcategory.id == selectedSubcategory ? "selected" : ""
                    }>${subcategory.name}</option>`;
                });
                $(".subcategories").html(subcategoriesHtml);
            },
            error: function (xhr) {
                const errorMessage =
                    xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : "Failed to fetch subcategories. Please try again.";
                toastr.error("Error:", errorMessage);
            },
        });
    }

    function removeImage(button, imagePath, imageId) {
        if (!confirm("Are you sure you want to delete this image?")) {
            return;
        }

        $.ajax({
            url: "/image-delete",
            type: "POST",
            data: {
                id: imageId,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    $(button).closest(".d-flex").remove();
                } else {
                    alert("Failed to delete the image. Please try again.");
                }
            },
            error: function (xhr) {
                alert("Something went wrong. Please try again.");
            },
        });
    }
}

if (pageValue == "admin.ticket" || pageValue == "staff.tickets") {
    function applyTicketStatusStyles() {
        $(".ticket-status").each(function (index) {
            const status = $(this).data("status");
            let statusClass = "";
            let statusText = "";

            $(this).removeClass(
                "badge ms-2 badge-primary-transparent badge-soft-info badge-soft-warning badge-soft-success status-unknown"
            );

            switch (status) {
                case 1:
                    statusText = "Open";
                    statusClass = "badge badge-primary-transparent ms-2";
                    break;
                case 2:
                    statusText = "Assigned";
                    statusClass = "badge badge-soft-info ms-2";
                    break;
                case 3:
                    statusText = "Inprogress";
                    statusClass = "badge badge-soft-warning ms-2";
                    break;
                case 4:
                    statusText = "Closed";
                    statusClass = "badge badge-soft-success ms-2";
                    break;
                default:
                    statusText = "Unknown";
                    statusClass = "status-unknown";
            }

            const $this = $(this);

            $this.addClass(statusClass);
        });
    }
    // applypriorityStatusStyles();

    function applypriorityStatusStyles() {
        $(".priority-status").each(function (index) {
            const status = $(this).data("status");
            let statusClass = "";
            let statusText = "";

            // Define status classes and texts
            switch (status) {
                case "High":
                    statusText = "High";
                    statusClass = "badge badge-danger";
                    break;
                case "Medium":
                    statusText = "Medium";
                    statusClass = "badge badge-orange";
                    break;
                case "Low":
                    statusText = "Low";
                    statusClass = "badge badge-warning";
                    break;
                default:
                    statusText = "Unknown";
                    statusClass = "status-unknown";
            }

            $(this).addClass(statusClass);
        });
    }
    function storeTicketId(ticketId) {
        // Send an AJAX request to store the ticket ID in the session
        fetch("/store-ticket-id", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({ ticket_id: ticketId }),
        })
            .then((response) => response.json())
            .then((data) => {
                var currentPath = window.location.pathname;
                // Redirect to the ticket details page
                if (currentPath == "/admin/tickets") {
                    window.location.href = "/admin/ticket-details";
                } else {
                    window.location.href = "/staff/ticketdetails";
                }
            })
            .catch((error) => {
                console.error("Error storing ticket ID:", error);
            });
    }

    $(document).ready(function () {
        $("#summernote").summernote();
        $(".assignid").on("click", function () {
            var id = $(this).data("id");
            $(".ticketid").val(id);
        });
        $("#assignticketform").on("submit", function (event) {
            event.preventDefault();
            var userid = $("#user_id").val();
            var username = $("#user_id option:selected").text();
            var id = $('input[name="ticket_id"]').val();
            if (userid != "") {
                let formData = {
                    id: id,
                    user_id: $("#user_id").val(),
                    auth_id: $(".auth_id").val(),
                    status: "2",
                    type: "assignticket",
                };
                $.ajax({
                    url: "/api/updateticketstatus",
                    type: "POST",
                    data: formData,
                    headers: {
                        Authorization:
                            "Bearer " + localStorage.getItem("admin_token"),
                        Accept: "application/json",
                    },
                    beforeSend: function () {
                        $("#assignticket")
                            .attr("disabled", true)
                            .html(
                                '<div class="spinner-border text-light" role="status"></div>'
                            );
                    },
                })
                    .done((response) => {
                        $("#assignticket")
                            .removeAttr("disabled")
                            .html($("#ticketList").data("assign"));

                        if (response.code == "200") {
                            if (languageId === 2) {
                                loadJsonFile(
                                    response.message,
                                    function (langtst) {
                                        toastr.success(langtst);
                                    }
                                );
                            } else {
                                toastr.success(response.message);
                            }
                            $("#assign_ticket").modal("hide");
                            var assigndata = $(".assigneddetails" + id);
                            var assignhtml = `<img src="${
                                response.data.assinee_profile_image
                            }" class="avatar avatar-xs rounded-circle me-2" alt="img">
                                                    ${$("#ticketList").data(
                                                        "assignto"
                                                    )} <span class="text-dark ms-1 assigneename">${username}</span>`;
                            assigndata.append(assignhtml);
                            $("#assignBtn" + id).addClass("d-none");
                            $(".ticketstatus" + id).html(
                                `<i class="ti ti-circle-filled fs-5 me-1"></i>${$(
                                    "#ticketList"
                                ).data("assigned")}`
                            );
                            $(".ticketstatus" + id).data("status", 2);
                            $(".updatedTime" + id).html(
                                `<i class="ti ti-calendar-bolt me-1"></i>${
                                    $("#ticketList").data("updated") +
                                    " " +
                                    response.data.ticket_updated_at
                                }`
                            );
                        } else {
                            toastr.error(response.message);
                        }
                        applyTicketStatusStyles();
                    })
                    .fail((error) => {
                        $("#assignticket")
                            .removeAttr("disabled")
                            .html($("#ticketList").data("assign"));

                        if (error.status === 422) {
                            $.each(
                                error.responseJSON.errors,
                                function (key, val) {
                                    $("#" + key).addClass("is-invalid");
                                    $("#" + key + "_error").text(val[0]);
                                }
                            );
                        } else {
                            toastr.error(
                                "An error occurred while adding the data."
                            );
                        }
                    });
            } else {
                toastr.info("Please Select Assignee");
            }
        });
    });
}

if (pageValue == "admin.ticketdetails" || pageValue == "staff.ticketdetails") {
    function applyTicketStatusStyles() {
        $(".ticket-status").each(function (index) {
            const status = $(this).data("status");
            let statusClass = "";
            let statusText = "";

            // Define status classes and texts
            switch (status) {
                case 1:
                    statusText = "Open";
                    statusClass = "badge badge-primary-transparent ms-2";
                    break;
                case 2:
                    statusText = "Assigned";
                    statusClass = "badge badge-soft-info ms-2";
                    break;
                case 3:
                    statusText = "Inprogress";
                    statusClass = "badge badge-soft-warning ms-2";
                    break;
                case 4:
                    statusText = "Closed";
                    statusClass = "badge badge-soft-success ms-2";
                    break;
                default:
                    statusText = "Unknown";
                    statusClass = "status-unknown";
            }

            $(this).addClass(statusClass);
        });
    }

    function applypriorityStatusStyles() {
        $(".priority-status").each(function (index) {
            const status = $(this).data("status");
            let statusClass = "";
            let statusText = "";

            // Define status classes and texts
            switch (status) {
                case "High":
                    statusText = "High";
                    statusClass = "badge badge-danger";
                    break;
                case "Medium":
                    statusText = "Medium";
                    statusClass = "badge badge-orange";
                    break;
                case "Low":
                    statusText = "Low";
                    statusClass = "badge badge-warning";
                    break;
                default:
                    statusText = "Unknown";
                    statusClass = "status-unknown";
            }

            const $this = $(this);

            $this.addClass(statusClass);
        });
    }
    $(document).ready(function () {
        $("#summernote").summernote();
        $("#replyform").on("submit", function (event) {
            event.preventDefault();
            var summernoteContent = $("#summernote").summernote("code");
            // Set the content to the target field
            $(".description").val(summernoteContent);
            if (summernoteContent != "") {
                let formData = {
                    ticket_id: $(".ticket_id").val(),
                    user_id: $(".user_id").val(),
                    description: summernoteContent,
                };
                $.ajax({
                    url: "/api/ticket/storehistory",
                    type: "POST",
                    data: formData,
                    headers: {
                        Authorization:
                            "Bearer " + localStorage.getItem("admin_token"),
                        Accept: "application/json",
                    },
                    beforeSend: function () {
                        $("#postreply")
                            .attr("disabled", true)
                            .html(
                                '<div class="spinner-border text-light" role="status"></div>'
                            );
                    },
                })
                    .done((response) => {
                        $("#postreply").removeAttr("disabled").html("Post");

                        if (response.code == "200") {
                            if (languageId === 2) {
                                loadJsonFile(
                                    response.message,
                                    function (langtst) {
                                        toastr.success(langtst);
                                    }
                                );
                            } else {
                                toastr.success(response.message);
                            }
                            $("#add_reply").modal("hide");
                            $("#summernote").summernote("code", "");
                            $(".description").val("");
                            let commentsSection =
                                document.getElementById("comments-section");
                            let newCommentDiv = document.createElement("div");
                            newCommentDiv.innerHTML = response.comments.trim();
                            commentsSection.prepend(
                                newCommentDiv.firstElementChild
                            );
                        } else {
                            toastr.error(response.message);
                        }
                    })
                    .fail((error) => {
                        $(".add_ticket_btn")
                            .removeAttr("disabled")
                            .html("Save");

                        if (error.status === 422) {
                            $.each(
                                error.responseJSON.errors,
                                function (key, val) {
                                    $("#" + key).addClass("is-invalid");
                                    $("#" + key + "_error").text(val[0]);
                                }
                            );
                        } else {
                            toastr.error(
                                "An error occurred while adding the data."
                            );
                        }
                    });
            } else {
                toastr.info("Please Select Assignee");
            }
        });
    });
    $(".updatestatus").click(function () {
        var oldstatus = $(this).data("status");
        var ticketid = $(this).data("ticket_id");
        var status = $(".status").val();
        var statusname = $(".status option:selected").text();
        var adminid = localStorage.getItem("user_id");
        var assignid = $("#assign_id").val();
        if (oldstatus != status) {
            let formData = {
                id: ticketid,
                status: status,
                auth_id: adminid,
                assign_id: assignid,
            };
            $.ajax({
                url: "/api/updateticketstatus",
                type: "POST",
                data: formData,
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                beforeSend: function () {
                    $(".updatestatus")
                        .attr("disabled", true)
                        .html(
                            '<div class="spinner-border text-light" role="status"></div>'
                        );
                },
            })
                .done((response) => {
                    $(".updatestatus")
                        .removeAttr("disabled")
                        .html($(".updatestatus").data("update_status_text"));
                    if (response.code == "200") {
                        var ticketStatusText = "-";
                        var ticketStatusClass = "";

                        $(".ticketstatus" + ticketid).removeClass(
                            "badge badge-primary-transparent badge-soft-info badge-soft-warning badge-soft-success status-unknown"
                        );
                        if (status == 1) {
                            ticketStatusText =
                                $(".ticket-details").data("open");
                            ticketStatusClass =
                                "badge badge-primary-transparent ms-2";
                        } else if (status == 2) {
                            ticketStatusText =
                                $(".ticket-details").data("assigned");
                            ticketStatusClass = "badge badge-soft-warning ms-2";
                        } else if (status == 3) {
                            ticketStatusText =
                                $(".ticket-details").data("inprogress");
                            ticketStatusClass = "badge badge-soft-info ms-2";
                        } else if (status == 4) {
                            ticketStatusText =
                                $(".ticket-details").data("closed");
                            ticketStatusClass = "badge badge-soft-success ms-2";
                        }

                        $(".ticketstatus" + ticketid).html(
                            `<i class="ti ti-circle-filled fs-5 me-1"></i>${ticketStatusText}`
                        );
                        $(".ticketstatus" + ticketid).addClass(
                            ticketStatusClass
                        );
                        $(".updatedTime" + ticketid).html(
                            `<i class="ti ti-calendar-bolt me-1"></i>${
                                $(".ticket-details").data("updated") +
                                " " +
                                response.data.ticket_updated_at
                            }`
                        );

                        if (status == 4) {
                            $("#ticketCloseDate").removeClass("d-none");
                        } else {
                            $("#ticketCloseDate").addClass("d-none");
                        }
                        if (languageId === 2) {
                            loadJsonFile(response.message, function (langtst) {
                                toastr.success(langtst);
                            });
                        } else {
                            toastr.success(response.message);
                        }
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail((error) => {
                    $(".updatestatus")
                        .removeAttr("disabled")
                        .html($(".updatestatus").data("update_status_text"));

                    if (error.status === 422) {
                        $.each(error.responseJSON.errors, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(
                            "An error occurred while adding the data."
                        );
                    }
                });
        } else {
            toastr.info("Please Change Ticket Status");
        }
    });
}

if (pageValue === "admin.staffs") {
    $(document).ready(function () {
        $(".selects").select2();
        getStaffList();
        getRoles();

        $("#staffForm").validate({
            rules: {
                first_name: {
                    required: true,
                    maxlength: 100,
                    pattern: /^[a-zA-Z]+$/,
                },
                last_name: {
                    required: true,
                    maxlength: 100,
                    pattern: /^[a-zA-Z]+$/,
                },
                user_name: {
                    required: true,
                    maxlength: 100,
                    remote: {
                        url: "/api/user/check-unique",
                        type: "post",
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                        },
                        data: {
                            user_name: function () {
                                return $("#user_name").val();
                            },
                            id: function () {
                                return $("#id").val();
                            },
                        },
                    },
                },
                email: {
                    required: true,
                    email: true,
                    remote: {
                        url: "/api/user/check-unique",
                        type: "post",
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                        },
                        data: {
                            email: function () {
                                return $("#email").val();
                            },
                            id: function () {
                                return $("#id").val();
                            },
                        },
                    },
                },
                phone_number: {
                    required: true,
                    minlength: 10,
                    maxlength: 12,
                },
                gender: {
                    required: true,
                },
                dob: {
                    required: false,
                },
                address: {
                    required: false,
                    maxlength: 150,
                },
                country: {
                    required: false,
                },
                state: {
                    required: false,
                },
                city: {
                    required: false,
                },
                postal_code: {
                    required: false,
                    maxlength: 6,
                    pattern: /^[a-zA-Z0-9]*$/,
                },
                profile_image: {
                    extension: "jpeg|jpg|png",
                    filesize: 2048,
                },
                role_id: {
                    required: true,
                },
            },
            messages: {
                first_name: {
                    required: $("#first_name_error").data("required"),
                    maxlength: $("#first_name_error").data("max"),
                    pattern: $("#first_name_error").data("alpha"),
                },
                last_name: {
                    required: $("#last_name_error").data("required"),
                    maxlength: $("#last_name_error").data("max"),
                    pattern: $("#last_name_error").data("alpha"),
                },
                user_name: {
                    required: $("#user_name_error").data("required"),
                    maxlength: $("#user_name_error").data("max"),
                    remote: $("#user_name_error").data("exists"),
                },
                email: {
                    required: $("#email_error").data("required"),
                    email: $("#email_error").data("format"),
                    remote: $("#email_error").data("exists"),
                },
                phone_number: {
                    required: $("#phone_number_error").data("required"),
                    minlength: $("#phone_number_error").data("between"),
                    maxlength: $("#phone_number_error").data("between"),
                },
                gender: {
                    required: $("#gender_error").data("required"),
                },
                dob: {
                    required: "Date of birth is required.",
                    date: "Please enter a valid date.",
                },
                address: {
                    required: $("#address_error").data("required"),
                    maxlength: $("#address_error").data("max"),
                },
                country: {
                    required: $("#country_error").data("required"),
                },
                state: {
                    required: $("#state_error").data("required"),
                },
                city: {
                    required: $("#city_error").data("required"),
                },
                postal_code: {
                    required: $("#postal_code_error").data("required"),
                    maxlength: $("#postal_code_error").data("max"),
                    pattern: $("#postal_code_error").data("char"),
                },
                profile_image: {
                    extension: $("#profile_image_error").data("extension"),
                    filesize: $("#profile_image_error").data("size"),
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
                    $(element)
                        .next(".select2-container")
                        .addClass("is-invalid")
                        .removeClass("is-valid");
                }
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element) {
                if ($(element).hasClass("select2-hidden-accessible")) {
                    $(element)
                        .next(".select2-container")
                        .removeClass("is-invalid")
                        .addClass("is-valid");
                }
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
                $("#parent_id").val(localStorage.getItem("user_id"));

                var formData = new FormData(form);
                formData.set("status", $("#status").is(":checked") ? 1 : 0);

                $.ajax({
                    url: "/api/save-admin-details",
                    type: "POST",
                    data: formData,
                    enctype: "multipart/form-data",
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        $("#staff_save_btn")
                            .attr("disabled", true)
                            .html(
                                '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                            );
                    },
                    success: function (response) {
                        if ($.fn.DataTable.isDataTable("#staffTable")) {
                            $("#staffTable").DataTable().destroy();
                        }
                        $(".error-text").text("");
                        $("#staff_save_btn")
                            .removeAttr("disabled")
                            .html($("#staff_save_btn").data("save"));
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".select2-container").removeClass(
                            "is-invalid is-valid"
                        );
                        if (response.code === 200) {
                            toastr.success(response.message);
                            $("#add_staff_modal").modal("hide");
                            getStaffList();
                        }
                    },
                    error: function (error) {
                        $(".error-text").text("");
                        $("#staff_save_btn")
                            .removeAttr("disabled")
                            .html($("#staff_save_btn").data("save"));
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".select2-container").removeClass(
                            "is-invalid is-valid"
                        );
                        if (error.responseJSON.code === 422) {
                            $.each(
                                error.responseJSON.errors,
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

        $("#editStaffForm").validate({
            rules: {
                first_name: {
                    required: true,
                    maxlength: 100,
                    pattern: /^[a-zA-Z]+$/,
                },
                last_name: {
                    required: true,
                    maxlength: 100,
                    pattern: /^[a-zA-Z]+$/,
                },
                user_name: {
                    required: true,
                    maxlength: 100,
                    remote: {
                        url: "/api/user/check-unique",
                        type: "post",
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                        },
                        data: {
                            user_name: function () {
                                return $("#edit_user_name").val();
                            },
                            id: function () {
                                return $("#id").val();
                            },
                        },
                    },
                },
                email: {
                    required: true,
                    email: true,
                    remote: {
                        url: "/api/user/check-unique",
                        type: "post",
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                        },
                        data: {
                            email: function () {
                                return $("#edit_email").val();
                            },
                            id: function () {
                                return $("#id").val();
                            },
                        },
                    },
                },
                phone_number: {
                    required: true,
                    minlength: 10,
                    maxlength: 12,
                },
                gender: {
                    required: true,
                },
                dob: {
                    required: false,
                },
                address: {
                    required: false,
                    maxlength: 150,
                },
                country: {
                    required: false,
                },
                state: {
                    required: false,
                },
                city: {
                    required: false,
                },
                postal_code: {
                    required: false,
                    maxlength: 6,
                    pattern: /^[a-zA-Z0-9]*$/,
                },
                profile_image: {
                    extension: "jpeg|jpg|png",
                    filesize: 2048,
                },
                role_id: {
                    required: true,
                },
            },
            messages: {
                first_name: {
                    required: $("#first_name_error").data("required"),
                    maxlength: $("#first_name_error").data("max"),
                    pattern: $("#first_name_error").data("alpha"),
                },
                last_name: {
                    required: $("#last_name_error").data("required"),
                    maxlength: $("#last_name_error").data("max"),
                    pattern: $("#last_name_error").data("alpha"),
                },
                user_name: {
                    required: $("#user_name_error").data("required"),
                    maxlength: $("#user_name_error").data("max"),
                    remote: $("#user_name_error").data("exists"),
                },
                email: {
                    required: $("#email_error").data("required"),
                    email: $("#email_error").data("format"),
                    remote: $("#email_error").data("exists"),
                },
                phone_number: {
                    required: $("#phone_number_error").data("required"),
                    minlength: $("#phone_number_error").data("between"),
                    maxlength: $("#phone_number_error").data("between"),
                },
                gender: {
                    required: $("#gender_error").data("required"),
                },
                dob: {
                    required: "Date of birth is required.",
                    date: "Please enter a valid date.",
                },
                address: {
                    required: $("#address_error").data("required"),
                    maxlength: $("#address_error").data("max"),
                },
                country: {
                    required: $("#country_error").data("required"),
                },
                state: {
                    required: $("#state_error").data("required"),
                },
                city: {
                    required: $("#city_error").data("required"),
                },
                postal_code: {
                    required: $("#postal_code_error").data("required"),
                    maxlength: $("#postal_code_error").data("max"),
                    pattern: $("#postal_code_error").data("char"),
                },
                profile_image: {
                    extension: $("#profile_image_error").data("extension"),
                    filesize: $("#profile_image_error").data("size"),
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
                    $(element)
                        .next(".select2-container")
                        .addClass("is-invalid")
                        .removeClass("is-valid");
                }
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element) {
                if ($(element).hasClass("select2-hidden-accessible")) {
                    $(element)
                        .next(".select2-container")
                        .removeClass("is-invalid")
                        .addClass("is-valid");
                }
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
                var formData = new FormData(form);
                formData.set(
                    "status",
                    $("#edit_status").is(":checked") ? 1 : 0
                );
                formData.append("parent_id", localStorage.getItem("user_id"));

                $.ajax({
                    url: "/api/save-admin-details",
                    type: "POST",
                    data: formData,
                    enctype: "multipart/form-data",
                    contentType: false,
                    processData: false,
                    cache: false,
                    headers: {
                        Authorization:
                            "Bearer " + localStorage.getItem("admin_token"),
                        Accept: "application/json",
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    beforeSend: function () {
                        $("#staff_edit_btn")
                            .attr("disabled", true)
                            .html(
                                '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                            );
                    },
                    success: function (response) {
                        if ($.fn.DataTable.isDataTable("#staffTable")) {
                            $("#staffTable").DataTable().destroy();
                        }
                        $(".error-text").text("");
                        $("#staff_edit_btn")
                            .removeAttr("disabled")
                            .html($("#staff_edit_btn").data("save"));
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".select2-container").removeClass(
                            "is-invalid is-valid"
                        );
                        if (response.code === 200) {
                            toastr.success(response.message);
                            $("#edit_staff_modal").modal("hide");
                            getStaffList();
                        }
                    },
                    error: function (error) {
                        $(".error-text").text("");
                        $("#staff_edit_btn")
                            .removeAttr("disabled")
                            .html($("#staff_edit_btn").data("save"));
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".select2-container").removeClass(
                            "is-invalid is-valid"
                        );
                        if (error.responseJSON.code === 422) {
                            $.each(
                                error.responseJSON.errors,
                                function (key, val) {
                                    $("#edit_" + key).addClass("is-invalid");
                                    $("#edit_" + key + "_error").text(val[0]);
                                }
                            );
                        } else {
                            toastr.error(error.responseJSON.message);
                        }
                    },
                });
            },
        });

        $.validator.addMethod(
            "filesize",
            function (value, element, param) {
                if (element.files.length === 0) return true;
                return element.files[0].size <= param * 1024;
            },
            "File size must be less than {0} KB."
        );
    });

    $("#phone_number").on("input", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/g, "")
        );
        if ($(this).val().length > 12) {
            $(this).val($(this).val().slice(0, 12));
        }
    });

    $("#postal_code").on("input", function () {
        if ($(this).val().length > 6) {
            $(this).val($(this).val().slice(0, 6));
        }
    });

    $("#edit_phone_number").on("input", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/g, "")
        );
        if ($(this).val().length > 12) {
            $(this).val($(this).val().slice(0, 12));
        }
    });

    $("#edit_postal_code").on("input", function () {
        if ($(this).val().length > 6) {
            $(this).val($(this).val().slice(0, 6));
        }
    });

    $("#profile_image").on("change", function (event) {
        if ($(this).val() !== "") {
            $(this).valid();
        }
        let reader = new FileReader();
        reader.onload = function (e) {
            $("#imagePreview").attr("src", e.target.result).show();
        };
        reader.readAsDataURL(event.target.files[0]);
    });

    $("#edit_profile_image").on("change", function (event) {
        if ($(this).val() !== "") {
            $(this).valid();
        }
        let reader = new FileReader();
        reader.onload = function (e) {
            $("#editImagePreview").attr("src", e.target.result).show();
        };
        reader.readAsDataURL(event.target.files[0]);
    });

    $("#gender").on("change", function () {
        $(this).valid();
    });
    $("#country").on("change", function () {
        $(this).valid();
    });
    $("#state").on("change", function () {
        $(this).valid();
    });
    $("#city").on("change", function () {
        $(this).valid();
    });

    $("#country").on("change", function () {
        const selectedCountry = $(this).val();
        clearDropdown($(".state"));
        clearDropdown($(".city"));
        if (selectedCountry != "") {
            getStates(selectedCountry);
        }
    });

    $("#state").on("change", function () {
        const selectedState = $(this).val();
        clearDropdown($(".city"));
        getCities(selectedState);
    });

    $("#edit_country").on("change", function () {
        const selectedCountry = $(this).val();
        clearDropdown($(".state"));
        clearDropdown($(".city"));
        if (selectedCountry != "") {
            getStates(selectedCountry);
        }
    });

    $("#edit_state").on("change", function () {
        const selectedState = $(this).val();
        clearDropdown($(".city"));
        getCities(selectedState);
    });

    function getStates(selectedCountry = "", selectedState = "") {
        $.ajax({
            url: "/get-states",
            type: "GET",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                country_id: selectedCountry,
            },
            dataType: "json",
            success: function (response) {
                if (response && response.data && response.data.length > 0) {
                    const stateDropdown = $(".state");
                    response.data.forEach((state) => {
                        stateDropdown.append(
                            `<option value="${state.id}" ${
                                selectedState == state.id ? "selected" : ""
                            }>${state.name}</option>`
                        );
                    });
                }
            },
            error: function (error) {
                $(".form-control").removeClass("is-invalid is-valid");
                if (error.responseJSON.code === 422) {
                    $.each(error.responseJSON.errors, function (key, val) {
                        $("#" + key + "_del").addClass("is-invalid");
                        $("#" + key + "_del_error").text(val[0]);
                    });
                } else {
                    toastr.error(error.responseJSON.message);
                }
            },
        });
    }

    function getCities(selectedState = "", selectedCity = "") {
        $.ajax({
            url: "/get-cities",
            type: "GET",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                state_id: selectedState,
            },
            dataType: "json",
            success: function (response) {
                if (response && response.data && response.data.length > 0) {
                    const stateDropdown = $(".city");
                    response.data.forEach((city) => {
                        stateDropdown.append(
                            `<option value="${city.id}" ${
                                selectedCity == city.id ? "selected" : ""
                            }>${city.name}</option>`
                        );
                    });
                }
            },
            error: function (error) {
                $(".form-control").removeClass("is-invalid is-valid");
                if (error.responseJSON.code === 422) {
                    $.each(error.responseJSON.errors, function (key, val) {
                        $("#" + key + "_del").addClass("is-invalid");
                        $("#" + key + "_del_error").text(val[0]);
                    });
                } else {
                    toastr.error(error.responseJSON.message);
                }
            },
        });
    }

    function clearDropdown(dropdown) {
        dropdown.empty().append(
            $("<option>", {
                value: "",
                text: "Select",
                disabled: true,
                selected: true,
            })
        );
    }

    $("#add_staff_btn").on("click", function () {
        const stateSelect = $(".state");
        clearDropdown(stateSelect);
        const citySelect = $(".city");
        clearDropdown(citySelect);
        $("#gender").val("").trigger("change");
        $("#country").val("").trigger("change");
        $("#role_id").val("").trigger("change");
        $("#imagePreview").attr("src", $("#imagePreview").data("image"));
        $(".form-control").removeClass("is-invalid is-valid");
        $(".select2-container").removeClass("is-invalid is-valid");
        $(".error-text").text("");
        $("#staffForm").trigger("reset");
        $("#id").val("");
    });

    function getRoles() {
        $.ajax({
            url: "/api/role/list",
            type: "POST",
            data: {
                user_id: localStorage.getItem("user_id"),
                status: 1,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            dataType: "json",
            success: function (response) {
                if (response && response.data && response.data.length > 0) {
                    response.data.forEach((role) => {
                        $(".role-list").append(
                            `<option value="${role.id}">${role.role_name}</option>`
                        );
                    });
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }

    function getStaffList() {
        var id = localStorage.getItem("user_id");
        $.ajax({
            url: "/api/admin/get-staff-list",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
                sort_by: "id",
                id: id,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    let staffs = response.data;
                    let tableBody = "";

                    if (staffs.length === 0) {
                        if ($.fn.DataTable.isDataTable("#staffTable")) {
                            $("#staffTable").DataTable().destroy();
                        }
                        tableBody += `
                            <tr>
                                <td colspan="7" class="text-center">${$(
                                    "#staffTable"
                                ).data("empty")}</td>
                            </tr>`;
                    } else {
                        staffs.forEach((staff, index) => {
                            tableBody += `
                               <tr>
                                <td>${index + 1}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar avatar-lg me-2">
                                            <img src="${
                                                staff.profile_image
                                            }" class="rounded-circle"
                                                alt="user">
                                        </a>
                                        <div>
                                            <h6 class="fs-14 fw-medium"><a href="#">${
                                                staff.first_name
                                            } ${staff.last_name}</a></h6>
                                            <span class="fs-12">${
                                                staff.email
                                            }</span>
                                        </div>
                                    </div>
                                </td>
                                <td>${staff.created_at}</td>
                                ${
                                    $("#has_permission").data("edit") == 1
                                        ? `<td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input admin_staff_status" ${
                                            staff.status == 1 ? "checked" : ""
                                        } type="checkbox" role="switch" id="switch-sm" data-id="${
                                              staff.id
                                          }">
                                    </div>
                                </td>`
                                        : ""
                                }
                                ${
                                    $("#has_permission").data("visible") == 1
                                        ? `<td>
                                    <li style="list-style: none;">
                                    ${
                                        $("#has_permission").data("edit") == 1
                                            ? `<a href="javascript:void(0);" class="edit_staff_btn" data-bs-toggle="modal" data-bs-target="#edit_staff_modal"
                                        data-id = "${staff.id}"
                                        data-user_name = "${staff.user_name}"
                                        data-first_name = "${staff.first_name}"
                                        data-last_name = "${staff.last_name}"
                                        data-email = "${staff.email}"
                                        data-phone_number = "${staff.phone_number}"
                                        data-profile_image = "${staff.profile_image}"
                                        data-gender = "${staff.gender}"
                                        data-dob = "${staff.dob}"
                                        data-address = "${staff.address}"
                                        data-country_id = "${staff.country_id}"
                                        data-state_id = "${staff.state_id}"
                                        data-city_id = "${staff.city_id}"
                                        data-postal_code = "${staff.postal_code}"
                                        data-bio = "${staff.bio}"
                                        data-role = "${staff.role_id}"
                                        data-status = "${staff.status}" >
                                        <i class="ti ti-pencil m-2 fs-20"></i></a>`
                                            : ""
                                    }
                                    ${
                                        $("#has_permission").data("delete") == 1
                                            ? `<a href="javascript:void(0);" class="delete_staff_btn" data-id="${staff.id}" data-bs-toggle="modal" data-bs-target="#del-staff">
                                        <i class="ti ti-trash m-2 fs-20"></i></a>`
                                            : ""
                                    }
                                    </li>
                                </td>`
                                        : ""
                                }
                            </tr>
                            `;
                        });
                    }

                    $("#staffTable tbody").html(tableBody);

                    $("#loader-table").addClass("d-none");
                    $(".label-loader, .input-loader").hide();
                    $("#staffTable, .real-label, .real-input").removeClass(
                        "d-none"
                    );

                    if (
                        staffs.length != 0 &&
                        !$.fn.DataTable.isDataTable("#staffTable")
                    ) {
                        $("#staffTable").DataTable({
                            ordering: true,
                            language: datatableLang,
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
                } else {
                    toastr.error("An error occurred while fetching.");
                }
            },
        });
    }

    let initialPhoneNumber = null;

    $(document).on("click", ".edit_staff_btn", function () {
        $(".form-control").removeClass("is-invalid is-valid");
        $(".select2-container").removeClass("is-invalid is-valid");
        $(".error-text").text("");
        $("#staffForm").trigger("reset");

        const id = $(this).data("id");
        const user_name = $(this).data("user_name");
        const first_name = $(this).data("first_name");
        const last_name = $(this).data("last_name");
        const email = $(this).data("email");
        const phone_number = $(this).data("phone_number");
        const profile_image = $(this).data("profile_image");
        const gender = $(this).data("gender");
        const dob = $(this).data("dob");
        const bio = $(this).data("bio");
        const address = $(this).data("address");
        const country_id = $(this).data("country_id");
        const state_id = $(this).data("state_id");
        const city_id = $(this).data("city_id");
        const postal_code = $(this).data("postal_code");
        const status = $(this).data("status");
        const role = $(this).data("role");

        $("#edit_country").val(country_id).trigger("change");
        getStates(country_id, state_id);
        getCities(state_id, city_id);

        $("#id").val(id);
        $("#edit_user_name").val(user_name);
        $("#edit_first_name").val(first_name);
        $("#edit_last_name").val(last_name);
        $("#edit_email").val(email);
        if (profile_image) {
            $("#editImagePreview").attr("src", profile_image);
        } else {
            $("#editImagePreview").attr(
                "src",
                $("#editImagePreview").data("image")
            );
        }
        $("#edit_gender").val(gender).trigger("change");
        $("#edit_address").val(address);
        $("#edit_dob").val(dob);
        $("#edit_postal_code").val(postal_code);
        $("#edit_bio").text(bio);
        $("#edit_status").prop("checked", status);
        $("#edit_role").val(role).trigger("change");

        const phoneNumber = phone_number.trim();
        const phoneInput = document.querySelector(".edit_staff_phone_number");
        const hiddenInput = document.querySelector("#edit_staff_phone_number");

        if ($(phoneInput).data("itiInstance")) {
            $(phoneInput).data("itiInstance").destroy();
        }
        const iti = intlTelInput(phoneInput, {
            utilsScript:
                window.location.origin +
                "/assets/plugins/intltelinput/js/utils.js",
            separateDialCode: true,
        });
        $(phoneInput).data("itiInstance", iti);

        if (phoneNumber) {
            iti.setNumber(phoneNumber);
            hiddenInput.value = iti.getNumber();
            initialPhoneNumber = phoneNumber;
        }

        phoneInput.addEventListener("countrychange", function () {
            const currentPhoneNumber = iti.getNumber();
            if (currentPhoneNumber !== initialPhoneNumber) {
                hiddenInput.value = currentPhoneNumber;
            }
        });

        if (!hiddenInput.value) {
            hiddenInput.value = initialPhoneNumber;
        }
    });

    $(document).on("click", ".delete_staff_btn", function () {
        var id = $(this).data("id");
        $("#confirm_staff_delete").data("id", id);
    });

    $(document).on("click", "#confirm_staff_delete", function (event) {
        event.preventDefault();

        var id = $(this).data("id");

        $.ajax({
            url: "/api/admin/delete-staff",
            type: "POST",
            data: {
                id: id,
            },
            dataType: "json",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    toastr.success(response.message);
                    $("#del-staff").modal("hide");
                    getStaffList();
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    });

    $(document).on("change", ".admin_staff_status", function () {
        let id = $(this).data("id");
        let status = $(this).is(":checked") ? 1 : 0;

        var data = {
            id: id,
            status: status,
        };

        $.ajax({
            url: "/api/admin/staff-status-change",
            type: "POST",
            data: data,
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    toastr.success(response.message);
                    getStaffList();
                }
            },
            error: function (error) {
                toastr.error("An error occurred while updating status");
            },
        });
    });
}

if (pageValue === "set-password") {
    $(document).ready(function () {
        $("#setPasswordForm").validate({
            rules: {
                new_password: {
                    required: true,
                    minlength: 8,
                    notEqualTo: "#current_password",
                },
                confirm_password: {
                    required: true,
                    equalTo: "#new_password",
                },
            },
            messages: {
                new_password: {
                    required: $("#new_password_error").data("required"),
                    minlength: $("#new_password_error").data("min"),
                    notEqualTo: $("#new_password_error").data("not_equal"),
                },
                confirm_password: {
                    required: $("#confirm_password_error").data("required"),
                    equalTo: $("#confirm_password_error").data("equal"),
                },
            },
            errorPlacement: function (error, element) {
                var errorId = element.attr("id") + "_error";
                $("#" + errorId).text(error.text());
            },
            highlight: function (element) {
                $(element).addClass("is-invalid").removeClass("is-valid");
                $("#" + element.id)
                    .siblings("span")
                    .addClass("me-3");
            },
            unhighlight: function (element) {
                $(element).removeClass("is-invalid").addClass("is-valid");
                var errorId = element.id + "_error";
                $("#" + errorId).text("");
                $("#" + element.id)
                    .siblings("span")
                    .addClass("me-3");
            },
            onkeyup: function (element) {
                $(element).valid();
            },
            onchange: function (element) {
                $(element).valid();
            },
            submitHandler: function (form) {
                var formData = new FormData(form);
                $.ajax({
                    url: "/update-password",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        Authorization:
                            "Bearer " + localStorage.getItem("admin_token"),
                        Accept: "application/json",
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    beforeSend: function () {
                        $("#set_password_btn")
                            .attr("disabled", true)
                            .html(
                                '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                            );
                    },
                    success: function (response) {
                        $(".error-text").text("");
                        $("#set_password_btn")
                            .removeAttr("disabled")
                            .html($("#set_password_btn").data("save"));
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".select2-container").removeClass(
                            "is-invalid is-valid"
                        );
                        if (response.code === 200) {
                            toastr.success(response.message);
                            window.location.href = response.redirectUrl;
                        }
                    },
                    error: function (error) {
                        $(".error-text").text("");
                        $("#set_password_btn")
                            .removeAttr("disabled")
                            .html($("#set_password_btn").data("save"));
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".select2-container").removeClass(
                            "is-invalid is-valid"
                        );
                        if (error.responseJSON.code === 422) {
                            $.each(
                                error.responseJSON.errors,
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
}

if (pageValue === "admin.advadisment") {
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
                    extension:
                        "Only image files (jpg, jpeg, png, gif) are allowed.",
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
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    beforeSend: function () {
                        $(".add_advertisement_btn")
                            .attr("disabled", true)
                            .html(
                                '<div class="spinner-border text-light" role="status"></div>'
                            );
                    },
                })
                    .done((response) => {
                        $(".form-control").removeClass("is-invalid");
                        $(".add_advertisement_btn")
                            .removeAttr("disabled")
                            .html(
                                $(".add_advertisement_btn").data("save_text")
                            );

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
                        $(".add_advertisement_btn")
                            .removeAttr("disabled")
                            .html(
                                $(".add_advertisement_btn").data("save_text")
                            );

                        if (error.status === 422) {
                            $.each(
                                error.responseJSON.errors,
                                function (key, messages) {
                                    $("#" + key).addClass("is-invalid");
                                    $("#" + key + "_error").text(messages[0]); // Show first error message
                                }
                            );
                        } else {
                            toastr.error(
                                error.responseJSON.message,
                                "bg-danger"
                            );
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
                    extension:
                        "Only image files (jpg, jpeg, png, gif) are allowed.",
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
                formData.append(
                    "edit_status",
                    $("#edit_status").is(":checked") ? 1 : 0
                );

                $.ajax({
                    url: "/admin/advertisement/edit",
                    method: "POST",
                    data: formData,
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    cache: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    beforeSend: function () {
                        $(".edit_advertisement_btn")
                            .attr("disabled", true)
                            .html(
                                '<div class="spinner-border text-light" role="status"></div>'
                            );
                    },
                })
                    .done((response) => {
                        $(".form-control").removeClass("is-invalid");
                        $(".edit_advertisement_btn")
                            .removeAttr("disabled")
                            .html(
                                $(".edit_advertisement_btn").data("update_text")
                            );

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
                        $(".edit_advertisement_btn")
                            .removeAttr("disabled")
                            .html(
                                $(".edit_advertisement_btn").data("update_text")
                            );

                        if (error.status === 422) {
                            $.each(
                                error.responseJSON.errors,
                                function (key, messages) {
                                    $("#" + key).addClass("is-invalid");
                                    $("#" + key + "_error").text(messages[0]);
                                }
                            );
                        } else {
                            toastr.error(
                                error.responseJSON.message,
                                "bg-danger"
                            );
                        }
                    });
            },
        });
    });

    $(document).ready(function () {
        function toggleAdFields() {
            if ($("#html_ad").is(":checked")) {
                $("#html_ad_container").show();
                $("#image_ad_container").hide();
            } else {
                $("#html_ad_container").hide();
                $("#image_ad_container").show();
            }
        }

        toggleAdFields();

        $('input[name="ad_type"]').on("change", function () {
            toggleAdFields();
        });
    });

    $(document).ready(function () {
        function toggleAdFields() {
            if ($("#edit_html_ad").is(":checked")) {
                $("#edit_html_ad_container").show();
                $("#edit_image_ad_container").hide();
            } else {
                $("#edit_html_ad_container").hide();
                $("#edit_image_ad_container").show();
            }
        }

        // Initial state
        toggleAdFields();

        // Event listeners for radio button change
        $('input[name="edit_ad_type"]').on("change", function () {
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
                $("#loader-table").hide();
                $(".label-loader, .input-loader").hide(); // Hide all skeleton loaders
                $(".real-label, .real-input").removeClass("d-none");
                if (response.code === 200) {
                    populateTable(response.data, response.meta);
                }
            },
            error: function (error) {
                $("#loader-table").hide();
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
                            ${
                                $("#has_permission").data("visible") == 1
                                    ? `<td>
                                <li style="list-style: none;">
                                    ${
                                        $("#has_permission").data("edit") == 1
                                            ? `<a class="edit_Data_data"
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
                                    data-body_data="${encodeURIComponent(
                                        Data.body_data
                                    )}"
                                    >
                                    <i class="ti ti-pencil fs-20"></i>
                                    </a>`
                                            : ""
                                    }
                                    ${
                                        $("#has_permission").data("delete") == 1
                                            ? `<a class="delete" href="#" data-bs-toggle="modal" data-bs-target="#delete-modal" data-id="${Data.id}">
                                        <i class="ti ti-trash m-3 fs-20"></i>
                                    </a>`
                                            : ""
                                    }
                                </li>
                            </td>`
                                    : ""
                            }
                        </tr>
                    `;
            });
        } else {
            $("#data_datatable").DataTable().destroy();
            tableBody = `
                    <tr>
                        <td colspan="6" class="text-center">No data found</td>
                    </tr>
                `;
        }

        $("#data_datatable tbody").html(tableBody);

        if (
            Data.length != 0 &&
            !$.fn.dataTable.isDataTable("#data_datatable")
        ) {
            $("#data_datatable").DataTable({
                ordering: true,
                language: datatableLang,
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
            $("#image-preview").html(
                `<img src="${image}" alt="${imageAlt}" style="max-width: 100%; height: 10rem; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">`
            );
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
                $("#image-preview")
                    .html(`<img src="${e.target.result}" alt="Selected Image"
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
                $("#add-image-preview")
                    .html(`<img src="${e.target.result}" alt="Selected Image"
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
            type: "POST",
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
            $(".form-control")
                .removeClass("is-invalid")
                .removeClass("is-valid");
            $(".invalid-feedback").text("");
        });
        $("#edit_advertisement").on("hidden.bs.modal", function () {
            $("#editAdvertisementForm")[0].reset();

            $(".form-control")
                .removeClass("is-invalid")
                .removeClass("is-valid");
            $(".invalid-feedback").text("");
        });
    });
}

if (pageValue === "admin.addons") {
    $(document).ready(function () {
        listAddonModules();
    });

    function listAddonModules() {
        $.ajax({
            url: "/api/admin/addon-module-list",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "asc",
                sort_by: "id",
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    let addons = response.data;
                    let tableBody = "";

                    if (addons.length === 0) {
                        $("#addonModuleTable").DataTable().destroy();
                        tableBody += `
                            <tr>
                                <td colspan="6" class="text-center">${$(
                                    "#addonModuleTable"
                                ).data("empty")}</td>
                            </tr>`;
                    } else {
                        addons.forEach((addon, index) => {
                            tableBody += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${addon.name} (${addon.version})</td>
                                    <td>
                                        <img src="${
                                            addon.module_image
                                        }" alt="Image" style="width: 80px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    </td>
                                    <td>$${addon.price}</td>
                                    ${
                                        $("#has_permission").data("edit") == 1
                                            ? `<td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input addon_status" ${
                                                addon.status == 1
                                                    ? "checked"
                                                    : ""
                                            } type="checkbox" role="switch" id="switch-sm" data-id="${
                                                  addon.id
                                              }">
                                        </div>
                                    </td>`
                                            : ""
                                    }
                                    <td>
                                       <div class="d-flex align-items-center">
                                            <a href="/clear">
                                                <i class="ti ti-refresh fs-20"></i>
                                            </a>
                                            ${
                                                addon.new_version == "yes"
                                                    ? `
                                                <a class="btn btn-primary btn-sm my-2 ms-2" href="${addon.purchase_link}" target="_blank">
                                                    New Addon
                                                </a>`
                                                    : ""
                                            }
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                    }

                    $("#addonModuleTable tbody").html(tableBody);
                    $("#loader-table").addClass("d-none");
                    $(".label-loader, .input-loader").hide();
                    $(
                        "#addonModuleTable, .real-label, .real-input"
                    ).removeClass("d-none");

                    if (
                        addons.length != 0 &&
                        !$.fn.DataTable.isDataTable("#addonModuleTable")
                    ) {
                        $("#addonModuleTable").DataTable({
                            ordering: true,
                            language: datatableLang,
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

    function listNewAddonModules() {
        $.ajax({
            url: "/api/new-addon-modules",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "asc",
                sort_by: "id",
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    let addons = response.data;
                    let tableBody = "";

                    if (addons.length === 0) {
                        $("#newAddonModuleTable").DataTable().destroy();
                        tableBody += `
                            <tr>
                                <td colspan="5" class="text-center">${$(
                                    "#newAddonModuleTable"
                                ).data("empty")}</td>
                            </tr>`;
                    } else {
                        addons.forEach((addon, index) => {
                            tableBody += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${addon.module_name} (${
                                addon.module_version
                            })</td>
                                    <td>
                                        <img src="${
                                            addon.module_image
                                        }" alt="Image" style="width: 80px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    </td>
                                    <td>$${addon.module_price}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            ${
                                                addon.status == 1
                                                    ? `<span class="badge ${
                                                          addon.status == 1
                                                              ? "badge-soft-success"
                                                              : "badge-soft-danger"
                                                      } d-inline-flex align-items-center">
                                                    <i class="ti ti-circle-filled fs-5 me-1"></i>${
                                                        addon.status == 1
                                                            ? "Activated"
                                                            : ""
                                                    }
                                                </span>`
                                                    : `<a class="btn btn-primary btn-sm me-2" href="${addon.purchase_link}" target="_blank">
                                                    Purchase </a>
                                                <a class="btn btn-primary btn-sm purchase_btn" href="" data-bs-toggle="modal" data-bs-target="#purchase_modal" data-module="${addon.module_name}" data-git_link="${addon.git_link}" data-version="${addon.module_version}" data-price="${addon.module_price}">
                                                    Activate </a>`
                                            }
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                    }

                    $("#newAddonModuleTable tbody").html(tableBody);
                    $("#loader-table").addClass("d-none");
                    $(".label-loader, .input-loader").hide();
                    $(
                        "#newAddonModuleTable, .real-label, .real-input"
                    ).removeClass("d-none");

                    if (
                        addons.length != 0 &&
                        !$.fn.DataTable.isDataTable("#newAddonModuleTable")
                    ) {
                        $("#newAddonModuleTable").DataTable({
                            ordering: true,
                            language: datatableLang,
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

    $(document).on("change", ".addon_status", function () {
        let id = $(this).data("id");
        let status = $(this).is(":checked") ? 1 : 0;

        var data = {
            id: id,
            status: status,
        };

        $.ajax({
            url: "/api/admin/change-addon-status",
            type: "POST",
            data: data,
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    toastr.success(response.message);
                    listAddonModules();
                    location.reload();
                }
            },
            error: function (error) {
                toastr.error("An error occurred while updating status");
            },
        });
    });

    $(document).on("click", "#installed_addon", function () {
        $("#newAddonModuleTable").addClass("d-none");
        $("#addonModuleTable").removeClass("d-none");
        if ($.fn.DataTable.isDataTable("#newAddonModuleTable")) {
            $("#newAddonModuleTable").DataTable().destroy();
        }
        if ($.fn.DataTable.isDataTable("#addonModuleTable")) {
            $("#addonModuleTable").DataTable().destroy();
        }
        $("#addonModuleTable tbody").empty();
        $("#loader-table").removeClass("d-none");
        $(".label-loader, .input-loader").show();
        $("#addonModuleTable, .real-label, .real-input").addClass("d-none");

        listAddonModules();
    });

    $(document).on("click", "#new_addon", function () {
        $("#addonModuleTable").addClass("d-none");
        $("#newAddonModuleTable").removeClass("d-none");
        if ($.fn.DataTable.isDataTable("#addonModuleTable")) {
            $("#addonModuleTable").DataTable().destroy();
        }
        if ($.fn.DataTable.isDataTable("#newAddonModuleTable")) {
            $("#newAddonModuleTable").DataTable().destroy();
        }
        $("#newAddonModuleTable tbody").empty();
        $("#loader-table").removeClass("d-none");
        $(".label-loader, .input-loader").show();
        $("#newAddonModuleTable, .real-label, .real-input").addClass("d-none");
        listNewAddonModules();
    });

    $(document).on("click", ".purchase_btn", function () {
        $("#module_name").val($(this).data("module"));
        $("#module_version").val($(this).data("version"));
        $("#module_price").val($(this).data("price"));
        $("#git_link").val($(this).data("git_link"));

        $("#purchase_modal").modal("show");
    });

    $(document).on("click", ".purchase_confirm_btn", function (event) {
        event.preventDefault();

        var formData = new FormData();
        formData.append("module_name", $("#module_name").val());
        formData.append("module_version", $("#module_version").val());
        formData.append("module_price", $("#module_price").val());
        formData.append("git_link", $("#git_link").val());
        formData.append("purchase_code", $("#purchase_key").val());

        $.ajax({
            url: "/api/purchase-module",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $(".purchase_confirm_btn")
                    .attr("disabled", true)
                    .html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
            },
            success: function (response) {
                $(".error-text").text("");
                $(".purchase_confirm_btn")
                    .removeAttr("disabled")
                    .html($(".purchase_confirm_btn").data("save"));
                $(".form-control").removeClass("is-invalid is-valid");
                $(".select2-container").removeClass("is-invalid is-valid");
                $("#purchase_modal").modal("hide");
                if (response.code === 200) {
                    toastr.success(response.message);
                    listNewAddonModules();
                    location.reload();
                }
            },
            error: function (error) {
                $(".error-text").text("");
                $(".purchase_confirm_btn")
                    .removeAttr("disabled")
                    .html($(".purchase_confirm_btn").data("save"));
                $(".form-control").removeClass("is-invalid is-valid");
                $(".select2-container").removeClass("is-invalid is-valid");
                if (error.responseJSON.code === 422) {
                    $.each(error.responseJSON.errors, function (key, val) {
                        $("#" + key).addClass("is-invalid");
                        $("#" + key + "_error").text(val[0]);
                    });
                } else {
                    toastr.error(error.responseJSON.message);
                }
            },
        });
    });
}

//how-it-work
if (pageValue === 'admin.how-it-work') {
    loadHowItWorkSettings();

    function loadHowItWorkSettings(langId = '') {
        $.ajax({
            url: '/api/admin/index-invoice-setting',
            type: 'POST',
            data: {
                'group_id': 14,
                language_id: langId
            },
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function (response) {
                if (response.code === 200) {

                    response.data.settings = response.data.settings.map(setting => {
                        if (setting.key.startsWith('how_it_work_content_')) {
                            const lastUnderscoreIndex = setting.key.lastIndexOf('_');
                            if (lastUnderscoreIndex !== -1) {
                                setting.key = setting.key.substring(0, lastUnderscoreIndex);
                            }
                        }
                        return setting;
                    });

                    const requiredKeys = ['how_it_work_content'];
                    const filteredSettings = response.data.settings.filter(setting => requiredKeys.includes(setting.key));

                    if (filteredSettings.length > 0) {

                        filteredSettings.forEach(setting => {
                            const element = $('#' + setting.key);

                            if (element.is('select')) {
                                element.val(setting.value).change();
                            } else if (element.is(':checkbox')) {
                                element.prop('checked', setting.value == 1);
                            } else if (element.is('textarea')) {
                                element.val(setting.value);
                            } else {
                                element.val(setting.value);
                            }
                        });

                        const maintenanceContentSetting = filteredSettings.find(setting => setting.key === 'how_it_work_content');
                        if (maintenanceContentSetting) {
                            $('#summernote').summernote('code', maintenanceContentSetting.value);
                        }
                    } else {
                        $('#summernote').summernote('code', '');
                    }

                }
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
            },
            error: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    }

    $('#how_it_work_setting_form').submit(function (event) {
        event.preventDefault();

        let formData = new FormData();
        formData.append('group_id', $('#group_id').val());
        formData.append('how_it_work_content', $('#summernote').summernote('code')); // Add maintenance content
        formData.append('language_id', $('#language_id').val());

        $.ajax({
            url: "/api/admin/update-otp-setting",
            method: "POST",
            data: formData,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            dataType: "json",
            contentType: false,
            processData: false,
            cache: false,
            beforeSend: function () {
                $('.how_it_work_update_btn').attr('disabled', true).html('<div class="spinner-border text-light" role="status"></div>');
            },
            success: function (response) {
                $('.how_it_work_update_btn').removeAttr('disabled').html('Save');
                if (response.code === 200) {
                    if (languageId === 2) {
                        loadJsonFile(response.message, function (langtst) {
                            toastr.success(langtst);
                        });
                    }else{
                        toastr.success(response.message);
                    }
                    var langId = $('#language_id').val();
                    loadHowItWorkSettings(langId);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (error) {
                $('.how_it_work_update_btn').removeAttr('disabled').html('Save');
                if (error.status == 422) {
                    $.each(error.responseJSON.errors, function (key, val) {
                        $("#" + key).addClass("is-invalid");
                        $("#" + key + "_error").text(val[0]);
                    });
                } else {
                    toastr.error(error.responseJSON.message || 'An error occurred while updating settings.');
                }
            }
        });
    });

    $('#language_id').on('change', function() {
        var langId = $(this).val();

        languageTranslate(langId);
        loadHowItWorkSettings(langId);
    });

    function languageTranslate(lang_id) {
        $.ajax({
            url: "/api/translate",
            type: "POST",
            dataType: "json",
            data: {
                language_id: lang_id,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                const trans = response.translated_values;
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid is-valid");

                if (response.code === 200 && Object.keys(trans).length > 0) {
                    $('.lang_title').text(trans.available_translations);
                }

            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }
}

function removeImageNew(fileName, button) {
    selectedFiles.delete(fileName);
    button.parentElement.remove();
    updateFileInput();
}

function updateFileInput() {
    let fileInput = document.getElementById("logo");
    let dataTransfer = new DataTransfer();

    selectedFiles.forEach((file) => {
        dataTransfer.items.add(file);
    });

    fileInput.files = dataTransfer.files;
}
