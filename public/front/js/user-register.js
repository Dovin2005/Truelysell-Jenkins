document.addEventListener("DOMContentLoaded", function () {
    const otpEmailModal = new bootstrap.Modal(
        document.getElementById("otp-email-reg-modal"),
        { keyboard: false }
    );
    const otpSmsModal = new bootstrap.Modal(
        document.getElementById("otp-reg-phone-modal"),
        { keyboard: false }
    );
    const otpTimerDisplay = document.getElementById("otp-reg-timer");
    const otpSmsTimerDisplay = document.getElementById("otp-reg-sms-timer");
    const otpEmailMessage = document.querySelector("#otp-email-message");
    const successModel = document.querySelector("#register-modal");
    let timerInterval;
    let timerTime = 0;
    let userEmail = "";
    const otpModal = document.getElementById("otp-email-reg-modal");
    const otpsmsModal = document.getElementById("otp-reg-phone-modal");

    otpModal.addEventListener("hidden.bs.modal", function () {
        const errorMessage = document.getElementById("error_email_reg_message");
        if (errorMessage) {
            errorMessage.textContent = "";
        }
    });
    otpsmsModal.addEventListener("hidden.bs.modal", function () {
        const errorMessage = document.getElementById("error_reg_sms_message");
        if (errorMessage) {
            errorMessage.textContent = "";
        }
    });

    function resendOtp(type) {
        const payload = {
            login_type: "register",
            ...userRegisterData,
        };

        const url = "/register-otp-settings";
        showLoader();

        $.ajax({
            url: url,
            type: "POST",
            data: payload,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                const userName = data.email;
                const otp = data.otp ?? '';
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
                    // sendEmail(userName, emailData, "email", userName, otp)
                    //     .then(() => {
                            
                    //     })
                    //     .catch((error) => {
                    //         hideLoader();
                    //         $("#otp_error").modal("show");
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
                    //         hideLoader();
                    //         otpSmsModal.show();
                    //         startSmsTimer(otpExpireTime);
                    //     })
                    //     .catch((error) => {
                    //         hideLoader();
                    //         alert("Failed to send SMS OTP. Please try again.");
                    //     });
                } else {
                    hideLoader();
                }
            },
            error: function (xhr) {
                const errorMessage =
                    xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : "Failed to resend OTP. Please try again.";
                toastr.error(errorMessage);
            },
        });
    }

    $(".resendRegEmailOtp").on("click", function () {
        resendOtp("email");
    });

    $(".resendRegSMSOtp").on("click", function () {
        resendOtp("sms");
    });

    let emailTimerInterval, smsTimerInterval;
    let emailTimerTime, smsTimerTime;

    function startTimer(expireTime) {
        clearInterval(emailTimerInterval);
        emailTimerTime = expireTime * 60;

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
                otpTimerDisplay.textContent = "00:00";
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

    function resetTimer() {
        clearInterval(emailTimerInterval);
        otpTimerDisplay.textContent = "00:00";
    }

    function resetSmsTimer() {
        clearInterval(smsTimerInterval);
        otpSmsTimerDisplay.textContent = "00:00";
    }

    let userRegisterData = {};

    document
        .getElementById("togglePassword")
        .addEventListener("click", function () {
            const passwordField = document.getElementById("password");
            const toggleIcon = document.getElementById("usertoggleIcon");
            const isPassword = passwordField.type === "password";

            // Toggle the type attribute
            passwordField.type = isPassword ? "text" : "password";

            // Toggle the icon class
            toggleIcon.classList.toggle("fa-eye-slash");
            toggleIcon.classList.toggle("fa-eye");
        });
   

    $("#phone").on("input", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/g, "")
        );

        if ($(this).val().length > 12) {
            $(this).val($(this).val().slice(0, 12));
        }
    });
    const phoneInput = document.querySelector("#phone");
    const iti = intlTelInput(phoneInput, {
        utilsScript: "assets/plugins/intltelinput/js/utils.js", // load the utils script for number formatting
        separateDialCode: true,
    });

    var langCode = $('body').data('lang');


    let currentLang = langCode; 

    const validationMessages = {
        en: {
            first_name: {
                required: "The first name field is required.",
                minlength: "The first name must be at least 3 characters.",
                maxlength: "The first name must not exceed 100 characters.",
                pattern: "The first name must not contain special characters or numbers.",
            },
            last_name: {
                required: "The last name field is required.",
                minlength: "The last name must be at least 3 characters.",
                maxlength: "The last name must not exceed 100 characters.",
                pattern: "The last name must not contain special characters or numbers.",
            },
            email: {
                required: "The email field is required.",
                email: "Please enter a valid email address.",
                pattern: "Please enter a valid email address.",
                remote: "Given email already exists.",
            },
            password: {
                required: "The password field is required.",
                minlength: "The password must be at least 8 characters.",
                maxlength: "The password must be less than 100 characters.",
            },
            name: {
                required: "The name field is required.",
                minlength: "The name must be at least 3 characters.",
                maxlength: "The name must not exceed 100 characters.",
                remote: "Username already exists.",
            },
            terms_policy: {
                required: "Please approve the Terms and Conditions & Privacy Policy.",
            },
            phone_number: {
                required: "The phone number field is required.",
                minlength: "The phone number must be at least 10 characters.",
                maxlength: "The phone number must not exceed 12 characters.",
            },
        },
        ar: {
            first_name: {
                required: "حقل الاسم الأول مطلوب.",
                minlength: "يجب أن يتكون الاسم الأول من 3 أحرف على الأقل.",
                maxlength: "يجب ألا يتجاوز الاسم الأول 100 حرف.",
                pattern: "يجب ألا يحتوي الاسم الأول على رموز خاصة أو أرقام.",
            },
            last_name: {
                required: "حقل اسم العائلة مطلوب.",
                minlength: "يجب أن يتكون اسم العائلة من 3 أحرف على الأقل.",
                maxlength: "يجب ألا يتجاوز اسم العائلة 100 حرف.",
                pattern: "يجب ألا يحتوي اسم العائلة على رموز خاصة أو أرقام.",
            },
            email: {
                required: "حقل البريد الإلكتروني مطلوب.",
                email: "يرجى إدخال عنوان بريد إلكتروني صالح.",
                pattern: "يرجى إدخال عنوان بريد إلكتروني صالح.",
                remote: "البريد الإلكتروني موجود بالفعل.",
            },
            password: {
                required: "حقل كلمة المرور مطلوب.",
                minlength: "يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.",
                maxlength: "يجب ألا تتجاوز كلمة المرور 100 حرف.",
            },
            name: {
                required: "حقل الاسم مطلوب.",
                minlength: "يجب أن يتكون الاسم من 3 أحرف على الأقل.",
                maxlength: "يجب ألا يتجاوز الاسم 100 حرف.",
                remote: "اسم المستخدم موجود بالفعل.",
            },
            terms_policy: {
                required: "يرجى الموافقة على الشروط والأحكام وسياسة الخصوصية.",
            },
            phone_number: {
                required: "حقل رقم الهاتف مطلوب.",
                minlength: "يجب أن يتكون رقم الهاتف من 10 أحرف على الأقل.",
                maxlength: "يجب ألا يتجاوز رقم الهاتف 12 حرفًا.",
            },
        },
    };
    

    $("#userregister").validate({
        rules: {
            first_name: {
                required: true,
                minlength: 3,
                maxlength: 100,
                pattern: /^[a-zA-Z\s]+$/, // Only allows alphabets and spaces
            },
            last_name: {
                required: true,
                minlength: 3,
                maxlength: 100,
                pattern: /^[a-zA-Z\s]+$/, // Only allows alphabets and spaces
            },
            email: {
                required: true,
                email: true,
                pattern: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
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
                        email: function () {
                            return $("#email").val();
                        },
                        id: function () {
                            return $("#id").val();
                        },
                    },
                },
            },
            password: {
                required: true,
                minlength: 8,
                maxlength: 100,
            },
            terms_policy: {
                required: true,
            },
            name: {
                required: true,
                minlength: 3,
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
                            return $("#name").val();
                        },
                        id: function () {
                            return $("#id").val();
                        },
                    },
                },
            },
            phone_number: {
                required: true,
                number: true,
                minlength: 10,
                maxlength: 12,
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
            const fullPhoneNumber = iti.getNumber();

            var formData = new FormData(form);
            formData.set("phone_number", fullPhoneNumber);

            $.ajax({
                url: "/userregister",
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".register_btn").attr("disabled", true);
                    $(".register_btn").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
                .done((response, statusText, xhr) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".register_btn").removeAttr("disabled");
                    $(".register_btn").html("Sign Up");

                    if (response.code === 200) {
                        if (response.register_status === "0") {
                            $("#register-modal").modal("hide");
                            $("#reg_success_modal").modal("show");

                            setTimeout(function () {
                                location.reload();
                            }, 500);
                        } else if (response.register_status === "1") {
                            $("#register-modal").modal("hide");

                            userRegisterData = {
                                name: $("#name").val(),
                                phone_number: $("#phone").val(),
                                email: $("#email").val(),
                                password: $("#password").val(),
                                first_name: $("#first_name").val(),
                                last_name: $("#last_name").val(),
                            };

                            const userName = $("#email").val();
                            const otp = response.otp;
                            const otpExpireTime = parseInt(
                                response.otp_expire_time.split(" ")[0]
                            );
                            const phoneNumber = response.phone_number;

                            showLoader();

                            const otpDigitLimit = parseInt(
                                response.otp_digit_limit
                            );

                            const inputcontainerreg = $(".inputcontainerreg");
                            inputcontainerreg.empty();

                            let inputsHtml =
                                '<div class="d-flex align-items-center mb-3">';
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
                            inputcontainerreg.append(inputsHtml);

                            $(".inputcontainerreg").on("input", "input", function () {
                                const maxLength = $(this).attr("maxlength") || 1;
                                if (this.value.length >= maxLength) {
                                    const next = $(this).data("next");
                                    if (next) {
                                        $("#" + next).focus();
                                    }
                                }
                            });
                        
                            $(".inputcontainerreg").on("keydown", "input", function (e) {
                                if (e.key === "Backspace" && this.value === "") {
                                    const prev = $(this).data("previous");
                                    if (prev) {
                                        $("#" + prev).focus();
                                    }
                                }
                            });
                        
                            $(".inputcontainerreg").on("click", "input", function () {
                                $(this).select(); // Optional: Select the input text when clicked
                            });

                            const inputRegSMSContainer = $(
                                ".inputRegSMSContainer"
                            );
                            inputRegSMSContainer.empty(); // Clear any existing input fields

                            let inputsSMSHtml =
                                '<div class="d-flex align-items-center mb-3">';
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
                            inputRegSMSContainer.append(inputsSMSHtml);

                            $(".inputRegSMSContainer").on("input", "input", function () {
                                const maxLength = $(this).attr("maxlength") || 1;
                                if (this.value.length >= maxLength) {
                                    const next = $(this).data("next");
                                    if (next) {
                                        $("#" + next).focus();
                                    }
                                }
                            });
                        
                            $(".inputRegSMSContainer").on("keydown", "input", function (e) {
                                if (e.key === "Backspace" && this.value === "") {
                                    const prev = $(this).data("previous");
                                    if (prev) {
                                        $("#" + prev).focus();
                                    }
                                }
                            });
                        
                            $(".inputRegSMSContainer").on("click", "input", function () {
                                $(this).select(); // Optional: Select the input text when clicked
                            });

                            if (response.otp_type === "email") {
                                hideLoader();
                                const otpEmailMessage =
                                    document.getElementById(
                                        "otp-reg-email-message"
                                    );
                                if (otpEmailMessage) {
                                    otpEmailMessage.textContent = `OTP sent to your Email Address ${userName}`;
                                }
                                otpEmailModal.show();
                                startTimer(otpExpireTime);
                                // const emailData = {
                                //     subject: response.email_subject,
                                //     content: response.email_content,
                                // };
                                // sendEmail(
                                //     userName,
                                //     emailData,
                                //     "email",
                                //     userName,
                                //     otp
                                // )
                                //     .then(() => {
                                //     })
                                //     .catch((error) => {
                                //         hideLoader();
                                //         $("#otp_error").modal("show");
                                //     });
                            } else if (response.otp_type === "sms") {
                                hideLoader();
                                const otpSmsMessage =
                                    document.getElementById(
                                        "otp-reg-sms-message"
                                    );
                                if (otpSmsMessage) {
                                    otpSmsMessage.textContent = `OTP sent to your mobile number ending ******${phoneNumber.slice(
                                        -4
                                    )}`;
                                }
                                otpSmsModal.show();
                                startSmsTimer(otpExpireTime);
                                // const emailData = {
                                //     subject: response.email_subject,
                                //     content: response.email_content,
                                // };
                                // sendSms(
                                //     phoneNumber,
                                //     emailData,
                                //     "sms",
                                //     userName,
                                //     otp
                                // )
                                //     .then(() => {
                                        
                                //     })
                                //     .catch((error) => {
                                       
                                //         hideLoader();
                                //         $("#otp_error").modal("show");
                                //     });
                            } else {
                                hideLoader();
                            }
                        }
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".register_btn").removeAttr("disabled");
                    $(".register_btn").html("Sign Up");
                    if (error.status == 422) {
                        $.each(error.responseJSON, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(
                            error.responseJSON.message || "Unexpected error"
                        );
                    }
                });
        },
    });

    $("#verify-email-red-otp-btn").on("click", function () {
        const otpDigitLimit = $(".inputcontainerreg input").length;

        // Collect the entered OTP digits
        const otp = [];
        for (let i = 1; i <= otpDigitLimit; i++) {
            const digit = $(`#digit-${i}`).val();
            otp.push(digit);
        }
        const otpString = otp.join("");

        // Add userRegisterData to the payload
        const payload = {
            otp: otpString,
            login_type: "register",
            ...userRegisterData, // Include name, phone_number, email, password
        };

        $.ajax({
            url: "/verify-otp",
            type: "POST",
            data: payload,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $(".verify-email-reg-otp-btn").attr("disabled", true);
                $(".verify-email-reg-otp-btn").html(
                    '<div class="spinner-border text-light" role="status"></div>'
                );
            },
            success: function (response) {
                $("#otp-email-reg-modal").modal("hide");
                $("#reg_success_modal").modal("show");

                setTimeout(function () {
                    location.reload();
                }, 500);
            },
            error: function (xhr) {
                const errorMessage = xhr.responseJSON.error || "OTP Required";
                $("#error_email_reg_message").text(errorMessage);
            },
            complete: function () {
                // Reset the button and remove the spinner
                $(".verify-email-reg-otp-btn").attr("disabled", false);
                $(".verify-email-reg-otp-btn").html("Verify OTP");
            },
        });
    });

    $("#verify-reg-sms-otp-btn").on("click", function () {
        const otpDigitLimit = $(".inputRegSMSContainer input").length;

        const otp = [];
        for (let i = 1; i <= otpDigitLimit; i++) {
            const digit = $(`#digitsms-${i}`).val();
            otp.push(digit);
        }
        const otpString = otp.join("");

        const payload = {
            otp: otpString,
            login_type: "register",
            ...userRegisterData,
        };

        $.ajax({
            url: "/verify-otp",
            type: "POST",
            data: payload,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#otp-reg-phone-modal").modal("hide");

                $("#reg_success_modal").modal("show");

                setTimeout(function () {
                    location.reload();
                }, 500);
            },
            error: function (xhr) {
                const errorMessage = xhr.responseJSON.error || "OTP Required";
                $("#error_reg_sms_message").text(errorMessage);
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
                        const errors = error.responseJSON.errors;
                        if (errors) {
                        } else {
                            toastr.error(
                                "An error occurred while sending OTP."
                            );
                        }
                    } else {
                        toastr.error("An error occurred while sending OTP.");
                    }
                    reject(error);
                },
            });
        });
    }

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

    // if (loginForm && emailInput) {
    //     loginForm.addEventListener("submit", function (event) {
    //         userEmail = emailInput.value;
    //     });
    // }
});
