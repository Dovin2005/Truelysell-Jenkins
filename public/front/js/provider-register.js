$(document).ready(function () {
    $("#categorySelect").on("change", function () {
        $(this).valid();
        const categoryId = $(this).val();
        var langCode = $("body").data("lang");
        fetchSubcategories(categoryId, langCode);
    });
});

$(document).ready(function () {
    $("#provider").on("hidden.bs.modal", function () {
        // Reset the form fields
        $("#providerRegister")[0].reset();

        // Remove validation states
        $(".form-control").removeClass("is-invalid").removeClass("is-valid");
        $(".invalid-feedback").text("");

        // Show the first fieldset and hide others
        $(".wizard-fieldset fieldset").hide(); // Hide all fieldsets
        $(".first-field").show(); // Show the first fieldset
    });
});

document
    .getElementById("providerTogglePassword")
    .addEventListener("click", function () {
        const passwordField = document.getElementById("provider_password");
        const toggleIcon = document.getElementById("providertoggleIcon");

        const isPassword = passwordField.type === "password";

        passwordField.type = isPassword ? "text" : "password";

        toggleIcon.classList.toggle("fa-eye-slash");
        toggleIcon.classList.toggle("fa-eye");
    });

function fetchSubcategories(categoryId, lang_id) {
    const subcategoriesContainer = document.getElementById("subcategories");

    if (!categoryId) {
        subcategoriesContainer.innerHTML = "";
        return;
    }

    subcategoriesContainer.innerHTML = `
        <div class="loader">Loading, please wait...</div>
    `;

    $.ajax({
        url: "/api/get-register-subcategories",
        type: "POST",
        data: { category_id: categoryId, language_code: lang_id },
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (data) {
            let subcategoriesHtml = "";

            if (data.length > 0) {
                data.forEach((subcategory) => {
                    subcategoriesHtml += `
                        <div class="form-checkbox d-inline-flex align-items-center mb-2 me-3">
                            <input class="form-check-input ms-0 mt-0" 
                                name="subcategory_ids[]" 
                                type="checkbox" 
                                id="subcategory-${subcategory.id}" 
                                value="${subcategory.id}">
                            <label class="form-check-label ms-2" for="subcategory-${subcategory.id}">
                                ${subcategory.name}
                            </label>
                        </div>`;
                });
            } else {
                subcategoriesHtml = `
                    <div class="no-subcategories">No Subcategories Provided</div>
                `;
            }
            $("#subcategories").html(subcategoriesHtml);
        },
        error: function (xhr) {
            const errorMessage =
                xhr.responseJSON && xhr.responseJSON.error
                    ? xhr.responseJSON.error
                    : "Failed to fetch subcategories. Please try again.";
            subcategoriesContainer.innerHTML = `
                <div class="error-message">${errorMessage}</div>
            `;
        },
    });
}

const individualRadio = document.getElementById("individual");
const companyRadio = document.getElementById("company");
const companyDetails = document.getElementById("company_details");
let subServiceType = "individual";

individualRadio.addEventListener("change", function () {
    if (individualRadio.checked) {
        companyDetails.style.display = "none";
        subServiceType = 'individual';
        $("#company_name").val("");
        $("#company_website").val("");
    }
});

companyRadio.addEventListener("change", function () {
    if (companyRadio.checked) {
        companyDetails.style.display = "block";
        subServiceType = 'company';
        $("#company_name").val("");
        $("#company_website").val("");
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const otpEmailModal = new bootstrap.Modal(
        document.getElementById("otp-email-prov-reg-modal"),
        { keyboard: false }
    );
    const otpSmsModal = new bootstrap.Modal(
        document.getElementById("otp-pro-reg-phone-modal"),
        { keyboard: false }
    );
    const otpTimerDisplay = document.getElementById("otp-pro-timer");
    const otpSmsTimerDisplay = document.getElementById("otp-pro-reg-sms-timer");
    const otpEmailMessage = document.querySelector("#otp-email-message");
    const successModel = document.querySelector("#register-modal");
    let timerInterval;
    let timerTime = 0;
    let userEmail = "";
    const otpModal = document.getElementById("otp-email-prov-reg-modal");
    const otpsmsModal = document.getElementById("otp-pro-reg-phone-modal");

    otpModal.addEventListener("hidden.bs.modal", function () {
        const errorMessage = document.getElementById(
            "error_prov_email_reg_message"
        );
        if (errorMessage) {
            errorMessage.textContent = "";
        }
    });
    otpsmsModal.addEventListener("hidden.bs.modal", function () {
        const errorMessage = document.getElementById(
            "error_pro_reg_sms_message"
        );
        if (errorMessage) {
            errorMessage.textContent = "";
        }
    });

    function resendOtp(type) {
        const payload = {
            login_type: "register",
            ...userRegisterData,
        };

        const url = "/provider-register-otp-settings";

        $.ajax({
            url: url,
            type: "POST",
            data: payload,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                const otpExpireTime = parseInt(
                    data.otp_expire_time.split(" ")[0]
                );
                const phoneNumber = $("#provider_phone_number").val();

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
                const errorMessage =
                    xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : "Failed to resend OTP. Please try again.";
                toastr.error(errorMessage);
            },
        });
    }

    $(".resendProRegEmailOtp").on("click", function () {
        resendOtp("email");
    });

    $(".resendProRegSMSOtp").on("click", function () {
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

    function resetTimer() {
        clearInterval(emailTimerInterval);
        otpTimerDisplay.textContent = "00:00";
    }

    function resetSmsTimer() {
        clearInterval(smsTimerInterval);
        otpSmsTimerDisplay.textContent = "00:00";
    }

    let userRegisterData = {};

    $("#provider_phone_number").on("input", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/g, "")
        );

        if ($(this).val().length > 12) {
            $(this).val($(this).val().slice(0, 12));
        }
    });

    const phoneInput = document.querySelector("#provider_phone_number");
    const iti = intlTelInput(phoneInput, {
        utilsScript: "assets/plugins/intltelinput/js/utils.js", // load the utils script for number formatting
        separateDialCode: true,
    });

    $(document).ready(function () {
        var langCode = $("body").data("lang");

        let currentF1Lang = langCode;
        let currentF2Lang = langCode;

        const validationF1Messages = {
            en: {
                provider_first_name: {
                    required: "The name field is required.",
                    minlength: "The name must be at least 3 characters.",
                },
                provider_last_name: {
                    required: "The name field is required.",
                    minlength: "The name must be at least 3 characters.",
                },
                provider_name: {
                    required: "The name field is required.",
                    minlength: "The name must be at least 3 characters.",
                    remote: "Provider name already exist",
                },
                provider_email: {
                    required: "The email field is required.",
                    email: "Please enter a valid email address.",
                    pattern: "Please enter a valid email address.",
                    remote: "Given email already exist.",
                },
                provider_password: {
                    required: "The password field is required.",
                    minlength: "The password must be at least 8 characters.",
                },
                provider_phone_number: {
                    required: "The phone number field is required.",
                    minlength:
                        "The phone number must be at least 10 characters.",
                },
            },
            ar: {
                provider_first_name: {
                    required: "حقل الاسم مطلوب.",
                    minlength: "يجب أن يكون الاسم 3 أحرف على الأقل.",
                },
                provider_last_name: {
                    required: "حقل الاسم مطلوب.",
                    minlength: "يجب أن يكون الاسم 3 أحرف على الأقل.",
                },
                provider_name: {
                    required: "حقل الاسم مطلوب.",
                    minlength: "يجب أن يكون الاسم 3 أحرف على الأقل.",
                    remote: "اسم المزود موجود بالفعل.",
                },
                provider_email: {
                    required: "حقل البريد الإلكتروني مطلوب.",
                    email: "يرجى إدخال عنوان بريد إلكتروني صالح.",
                    pattern: "يرجى إدخال عنوان بريد إلكتروني صالح.",
                    remote: "البريد الإلكتروني المقدم موجود بالفعل.",
                },
                provider_password: {
                    required: "حقل كلمة المرور مطلوب.",
                    minlength: "يجب أن تكون كلمة المرور 8 أحرف على الأقل.",
                },
                provider_phone_number: {
                    required: "حقل رقم الهاتف مطلوب.",
                    minlength: "يجب أن يكون رقم الهاتف 10 أحرف على الأقل.",
                },
            },
        };

        const validationF2Messages = {
            en: {
                category_id: {
                    required: "The category field is required.",
                },
                company_name: {
                    required: "The company name field is required.",
                    minlength:
                        "The company name must be at least 3 characters.",
                    maxlength:
                        "The company name must not exceed 50 characters.",
                },
                company_website: {
                    required: "The company website field is required.",
                    minlength:
                        "The company website must be at least 3 characters.",
                    maxlength:
                        "The company website must not exceed 255 characters.",
                    url: "Please enter a valid URL.",
                },
                provider_terms_policy: {
                    required:
                        "Please approve the Terms and Conditions & Privacy Policy.",
                },
            },
            ar: {
                category_id: {
                    required: "حقل الفئة مطلوب.",
                },
                company_name: {
                    required: "حقل اسم الشركة مطلوب.",
                    minlength: "يجب أن يكون اسم الشركة 3 أحرف على الأقل.",
                    maxlength: "يجب ألا يتجاوز اسم الشركة 50 حرفًا.",
                },
                company_website: {
                    required: "حقل موقع الشركة مطلوب.",
                    minlength: "يجب أن يكون موقع الشركة 3 أحرف على الأقل.",
                    maxlength: "يجب ألا يتجاوز موقع الشركة 255 حرفًا.",
                    url: "يرجى إدخال عنوان URL صالح.",
                },
                provider_terms_policy: {
                    required:
                        "يرجى الموافقة على الشروط والأحكام وسياسة الخصوصية.",
                },
            },
        };

        $("#providerRegister").validate({
            rules: {
                provider_first_name: {
                    required: true,
                    minlength: 3,
                    maxlength: 50,
                },
                provider_last_name: {
                    required: true,
                    minlength: 3,
                    maxlength: 50,
                },
                provider_name: {
                    required: true,
                    minlength: 3,
                    maxlength: 50,
                    remote: {
                        url: "/api/user/check-unique",
                        type: "post",
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                        },
                        data: {
                            name: function () {
                                return $("#provider_name").val();
                            },
                            id: function () {
                                return $("#id").val();
                            },
                        },
                    },
                },
                provider_email: {
                    required: true,
                    email: true,
                    pattern: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
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
                                return $("#provider_email").val();
                            },
                            id: function () {
                                return $("#id").val();
                            },
                        },
                    },
                },
                provider_password: {
                    required: true,
                    minlength: 8,
                },
                provider_phone_number: {
                    required: true,
                    number: true,
                    minlength: 10,
                },
            },
            messages: validationF1Messages[currentF1Lang],
            errorElement: "span",
            errorPlacement: function (error, element) {
                if (element.attr("name") === "category_id") {
                    error.appendTo("#category_id_error"); // Make sure you have an element with this id for error message.
                } else {
                    error.addClass("invalid-feedback");
                    element.closest(".mb-3").append(error); // Default error placement
                }
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass("is-invalid").addClass("is-valid");
            },
        });

        $("#get_started_btn").on("click", function (event) {
            event.preventDefault();

            let infoFormData = $("#providerRegister").serializeArray();

            if ($("#providerRegister").valid()) {
                let formDataCollection = {};
                infoFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                $("#first-field").hide();
                $("#second-field").show();
            }
        });

        $("#companyInfo").validate({
            rules: {
                category_id: {
                    required: true,
                },
                company_name: {
                    required: true,
                    minlength: 3,
                    maxlength: 50,
                },
                company_website: {
                    required: true,
                    minlength: 3,
                    maxlength: 255,
                    url: true,
                },
                provider_terms_policy: {
                    required: true,
                },
            },
            messages: validationF2Messages[currentF2Lang],
            errorElement: "span",
            errorPlacement: function (error, element) {
                error.addClass("invalid-feedback");

                if (element.attr("name") === "provider_terms_policy") {
                    // Special case for provider_terms_policy
                    $("#provider_terms_policy_error").html(error);
                } else {
                    // Append error to the closest parent div
                    element.closest(".mb-4").append(error);
                }
            },
            highlight: function (element) {
                if ($(element).hasClass("select2-hidden-accessible")) {
                    $(element)
                        .next(".select2-container")
                        .addClass("is-invalid")
                        .removeClass("is-valid");
                } else {
                    $(element).addClass("is-invalid").removeClass("is-valid");
                }
            },
            unhighlight: function (element) {
                if ($(element).hasClass("select2-hidden-accessible")) {
                    $(element)
                        .next(".select2-container")
                        .removeClass("is-invalid")
                        .addClass("is-valid");
                } else {
                    $(element).removeClass("is-invalid").addClass("is-valid");
                }
            },
        });

        $("#provider_register_btn").on("click", function (event) {
            event.preventDefault();

            let companyInfo = $("#companyInfo").serializeArray();
            let infoFormData = $("#providerRegister").serializeArray();

            if ($("#companyInfo").valid()) {
                let formDataCollection = {};
                companyInfo.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                const all = 1;

                if (all == 1) {
                    let finalFormData = new FormData();

                    finalFormData.append(
                        "_token",
                        $('meta[name="csrf-token"]').attr("content")
                    );

                    [...infoFormData, ...companyInfo].forEach(function (item) {
                        finalFormData.append(item.name, item.value);
                    });

                    const fullPhoneNumber = iti.getNumber();
                    finalFormData.set("provider_phone_number", fullPhoneNumber);
                    finalFormData.append("sub_service_type", subServiceType);

                    $.ajax({
                        url: "/provider/register",
                        method: "POST",
                        data: finalFormData,
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
                            $(".provider_register_btn").attr("disabled", true);
                            $(".provider_register_btn").html(
                                '<div class="spinner-border text-light" role="status"></div>'
                            );
                        },
                    })
                        .done((response, statusText, xhr) => {
                            $(".error-text").text("");
                            $(".form-control").removeClass("is-invalid");
                            $(".provider_register_btn").removeAttr("disabled");
                            $(".provider_register_btn").html("Submit");

                            if (response.code === 200) {
                                if (response.register_status === "0") {
                                    $("#provider").modal("hide");

                                    if (response.provider_approval_status == 1) {
                                        $("#provider_approval_success_modal").modal("show");
                                    } else {
                                        $("#reg_success_modal").modal("show");

                                        setTimeout(function () {
                                            location.reload();
                                        }, 500);
                                    }

                                } else if (response.register_status === "1") {
                                    $("#provider").modal("hide");

                                    userRegisterData = {
                                        name: $("#provider_name").val(),
                                        phone_number: $("#provider_phone_number").val(),
                                        email: $("#provider_email").val(),
                                        password: $("#provider_password").val(),
                                        category_id: $("#categorySelect").val(),
                                        company_name: $("#company_name").val(),
                                        company_website: $("#company_website").val(),
                                        provider_first_name: $("#provider_first_name").val(),
                                        provider_last_name: $("#provider_last_name").val(),
                                        sub_service_type: subServiceType ?? 'individual'
                                    };

                                    const otpExpireTime = parseInt(
                                        response.otp_expire_time.split(" ")[0]
                                    );
                                    const phoneNumber = $("#provider_phone_number").val();

                                    showLoader();

                                    const otpDigitLimit = parseInt(
                                        response.otp_digit_limit
                                    );

                                    const inputProvideContainerreg = $(
                                        ".inputProvideContainerreg"
                                    );
                                    inputProvideContainerreg.empty();

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
                                    inputProvideContainerreg.append(inputsHtml);

                                    $(".inputProvideContainerreg").on(
                                        "input",
                                        "input",
                                        function () {
                                            const maxLength =
                                                $(this).attr("maxlength") || 1;
                                            if (
                                                this.value.length >= maxLength
                                            ) {
                                                const next =
                                                    $(this).data("next");
                                                if (next) {
                                                    $("#" + next).focus();
                                                }
                                            }
                                        }
                                    );

                                    $(".inputProvideContainerreg").on(
                                        "keydown",
                                        "input",
                                        function (e) {
                                            if (
                                                e.key === "Backspace" &&
                                                this.value === ""
                                            ) {
                                                const prev =
                                                    $(this).data("previous");
                                                if (prev) {
                                                    $("#" + prev).focus();
                                                }
                                            }
                                        }
                                    );

                                    $(".inputProvideContainerreg").on(
                                        "click",
                                        "input",
                                        function () {
                                            $(this).select();
                                        }
                                    );

                                    const inputProRegSMSContainer = $(
                                        ".inputProRegSMSContainer"
                                    );
                                    inputProRegSMSContainer.empty(); // Clear any existing input fields

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
                                    inputProRegSMSContainer.append(
                                        inputsSMSHtml
                                    );

                                    $(".inputProRegSMSContainer").on(
                                        "input",
                                        "input",
                                        function () {
                                            const maxLength =
                                                $(this).attr("maxlength") || 1;
                                            if (
                                                this.value.length >= maxLength
                                            ) {
                                                const next =
                                                    $(this).data("next");
                                                if (next) {
                                                    $("#" + next).focus();
                                                }
                                            }
                                        }
                                    );

                                    $(".inputProRegSMSContainer").on(
                                        "keydown",
                                        "input",
                                        function (e) {
                                            if (
                                                e.key === "Backspace" &&
                                                this.value === ""
                                            ) {
                                                const prev =
                                                    $(this).data("previous");
                                                if (prev) {
                                                    $("#" + prev).focus();
                                                }
                                            }
                                        }
                                    );

                                    $(".inputProRegSMSContainer").on(
                                        "click",
                                        "input",
                                        function () {
                                            $(this).select();
                                        }
                                    );

                                    if (response.otp_type === "email") {
                                        hideLoader();
                                        const otpEmailMessage =
                                            document.getElementById(
                                                "otp-prov-reg-email-message"
                                            );
                                        if (otpEmailMessage) {
                                            otpEmailMessage.textContent = `OTP sent to your Email Address ${$("#provider_email").val()}`;
                                        }
                                        otpEmailModal.show();
                                        startTimer(otpExpireTime);
                                    } else if (response.otp_type === "sms") {
                                        hideLoader();
                                        const otpSmsMessage =
                                            document.getElementById(
                                                "otp-prov-reg-sms-message"
                                            );
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
                            }
                        })

                        .fail((xhr, statusText, error) => {
                            $(".error-text").text(""); // Clear all previous error messages
                            $(".form-control").removeClass("is-invalid"); // Remove invalid classes
                            $(".provider_register_btn").removeAttr("disabled"); // Re-enable the button
                            $(".provider_register_btn").html("Submit");

                            if (xhr.status === 422) {
                                // Validation error
                                let response = JSON.parse(xhr.responseText);
                                toastr.error(
                                    response.message ||
                                        "An error occurred. Please try again."
                                );
                            } else {
                                toastr.error(xhr.responseJSON.message || "An unexpected error occurred.");
                            }
                        });
                }
            }
        });
    });

    $("#verify-email-prov-reg-otp-btn").on("click", function () {
        const otpDigitLimit = $(".inputProvideContainerreg input").length;

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
            login_type: "provider_register",
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
                $(".verify-email-prov-reg-otp-btn").attr("disabled", true);
                $(".verify-email-prov-reg-otp-btn").html(
                    '<div class="spinner-border text-light" role="status"></div>'
                );
            },
            success: function (response) {
                $("#otp-email-prov-reg-modal").modal("hide");

                if (response.provider_approval_status == 1) {
                    $("#provider_approval_success_modal").modal("show");
                } else {
                    $("#reg_success_modal").modal("show");

                    setTimeout(function () {
                        location.reload();
                    }, 500);
                }
            },
            error: function (xhr) {
                const errorMessage = xhr.responseJSON.error || "OTP Required";

                $("#error_prov_email_reg_message").text(errorMessage);
            },
            complete: function () {
                $(".verify-email-prov-reg-otp-btn").attr("disabled", false);
                $(".verify-email-prov-reg-otp-btn").html("Verify OTP");
            },
        });
    });

    $("#verify-pro-reg-sms-otp-btn").on("click", function () {
        const otpDigitLimit = $(".inputProRegSMSContainer input").length;

        const otp = [];
        for (let i = 1; i <= otpDigitLimit; i++) {
            const digit = $(`#digitsms-${i}`).val();
            otp.push(digit);
        }
        const otpString = otp.join("");

        const payload = {
            otp: otpString,
            login_type: "provider_register",
            ...userRegisterData,
        };

        $.ajax({
            url: "/verify-otp",
            type: "POST",
            data: payload,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $(".verify-pro-reg-sms-otp-btn").attr("disabled", true);
                $(".verify-pro-reg-sms-otp-btn").html(
                    '<div class="spinner-border text-light" role="status"></div>'
                );
            },
            success: function (response) {
                $("#otp-pro-reg-phone-modal").modal("hide");

                if (response.provider_approval_status == 1) {
                    $("#provider_approval_success_modal").modal("show");
                } else {
                    $("#reg_success_modal").modal("show");

                    setTimeout(function () {
                        location.reload();
                    }, 500);
                }
            },
            error: function (xhr) {
                const errorMessage = xhr.responseJSON.error || "OTP Required";
                $("#error_pro_reg_sms_message").text(errorMessage);
            },
            complete: function () {
                $(".verify-pro-reg-sms-otp-btn").attr("disabled", false);
                $(".verify-pro-reg-sms-otp-btn").html("Verify OTP");
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

document.addEventListener("DOMContentLoaded", function () {
    // Select the modal element
    const providerModal = document.getElementById("provider");

    // Attach the 'hidden.bs.modal' event listener
    providerModal.addEventListener("hidden.bs.modal", function () {
        // Reset the form inside the modal
        const form = providerModal.querySelector("#providerRegister");
        if (form) {
            form.reset(); // Reset all form inputs to their initial state
        }

        // Clear custom error messages if any
        providerModal
            .querySelectorAll(".invalid-feedback")
            .forEach(function (errorElement) {
                errorElement.textContent = ""; // Clear error messages
            });

        // Hide dynamically added elements (e.g., company details)
        const companyDetails = providerModal.querySelector("#company_details");
        if (companyDetails) {
            companyDetails.style.display = "none"; // Hide the company details section
        }

        // Reset dynamically populated subcategories
        const subcategories = providerModal.querySelector("#subcategories");
        if (subcategories) {
            subcategories.innerHTML = ""; // Clear subcategory checkboxes
        }

        // Reset dropdown selections
        const categorySelect = providerModal.querySelector("#categorySelect");
        if (categorySelect) {
            categorySelect.value = ""; // Set the default value
        }
    });
});
