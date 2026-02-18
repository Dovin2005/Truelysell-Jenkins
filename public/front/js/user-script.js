var pageValue = $("body").data("frontend");
var datatableLang = {
    lengthMenu: $("#datatable_data").data("length_menu"),
    info: $("#datatable_data").data("info"),
    infoEmpty: $("#datatable_data").data("info_empty"),
    infoFiltered: $("#datatable_data").data("info_filter"),
    search: $("#datatable_data").data("search"),
    zeroRecords: $("#datatable_data").data("zero_records"),
    paginate: {
        first: $("#datatable_data").data("first"),
        last: $("#datatable_data").data("last"),
        next: $("#datatable_data").data("next"),
        previous: $("#datatable_data").data("prev"),
    },
};
if ($("#advertisementOne").children().length > 0) {
    $(".advertisementValue").removeClass("d-none");
} else {
    $(".advertisementValue").addClass("d-none");
}

if ($("body").data('error')) {
    toastr.error($("body").data('error'));
}

const languageId = $("#language-settings").data("language-id");
let auth_id = $("body").data("authid");

document.getElementById("loginTogglePassword").addEventListener("click", function () {
	const passwordField = document.querySelector(".pasword-field");
	const toggleIcon = document.querySelector(".toggleIcon");

	const isPassword = passwordField.type === "password";

	passwordField.type = isPassword ? "text" : "password";

	toggleIcon.classList.toggle("fa-eye-slash");
	toggleIcon.classList.toggle("fa-eye");
});

$(document).ready(function () {
    if (auth_id) {
        notificationList(auth_id);
    }

    // Attach click event for mark all as read
    $(".markallread").on("click", function () {
        markAllRead(auth_id);
    });

    $(".language-select").on("click", function () {
        const languageId = $(this).data("id");
        const url = `/languagedefault/${languageId}`;

        $.ajax({
            url: url,
            method: "GET",
            success: function (response) {
                location.reload();
            },
            error: function (xhr, status, error) {
                toastr.error("An error occurred while changing language.");
            },
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const cookieConsentBanner = document.querySelector(".cookie-consent");
    const cookieConsentAgreeButton = document.querySelector(".cookie-consent__agree");
    const cookieConsentDeclineButton = document.querySelector(".cookie-consent__decline");

    // Hide banner if consent already given
    const cookieConsentValue = document.cookie
        .split("; ")
        .find((row) => row.startsWith("cookie_consent="))
        ?.split("=")[1];

    if (cookieConsentValue === "1" || cookieConsentValue === "0") {
        cookieConsentBanner.style.display = "none";
    }

    // Accept
    cookieConsentAgreeButton.addEventListener("click", function () {
        document.cookie = "cookie_consent=1; path=/; max-age=" + 60 * 60 * 24 * 30;
        cookieConsentBanner.style.display = "none";
    });

    if (cookieConsentDeclineButton) {
        cookieConsentDeclineButton.addEventListener("click", function () {
            document.cookie = "cookie_consent=0; path=/; max-age=" + 60 * 60 * 24 * 30;
            if (cookieConsentBanner) {
                cookieConsentBanner.style.display = "none";
            }
        });
    }

});

// Show banner with delay
document.addEventListener("DOMContentLoaded", () => {
    const cookieConsent = document.querySelector(".cookie-consent");

    if (cookieConsent) {
        // Show banner after delay only if no decision has been made
        const cookieConsentValue = document.cookie
            .split("; ")
            .find((row) => row.startsWith("cookie_consent="))
            ?.split("=")[1];

        if (cookieConsentValue !== "1" && cookieConsentValue !== "0") {
            setTimeout(() => {
                cookieConsent.classList.add("visible");
                cookieConsent.classList.remove("hidden");
            }, 2000);
        }
    }
});

function initSelect2() {
    $(".select2").select2({});
}

function addfavour(id) {
    $.ajax({
        url: "api/user/addfavour",
        method: "POST",
        data: {
            id: id,
            user_id: $("body").data("authid"),
        },
        dataType: "json",
        success: function (response) {
            if (response.code === 200) {
            }
        },
        error: function (error) {},
    });
}

function showLoader() {
    const loader = document.getElementById("NewletterpageLoader");
    if (loader) {
        loader.style.display = "block";
    }
}

function hideLoader() {
    const loader = document.getElementById("NewletterpageLoader");
    if (loader) {
        loader.style.display = "none";
    }
}

$("#subscriberForm").validate({
    rules: {
        subscriber_email: {
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
                    subscriber_email: function () {
                        return $("#subscriber_email").val();
                    },
                    id: function () {
                        return $("#id").val();
                    },
                },
            },
        },
    },
    messages: {
        subscriber_email: {
            required: "Email is required.",
            email: "Please enter a valid email address.",
            remote: "This email is already subscribed.",
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
        $.ajax({
            url: "/api/admin/newsletter/save-subscriber",
            method: "POST",
            data: formData,
            dataType: "json",
            contentType: false,
            processData: false,
            cache: false,
            success: function (response) {
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid is-valid");

                if (response.code === 200) {
                    const email = $("#subscriber_email").val();
                    const subject = response.data.subject;
                    const content = response.data.content;

                    showLoader();
                    sendEmailNew(email, subject, content)
                        .then(() => {})
                        .catch((error) => {
                            console.error("Error sending email:", error);
                        });
                    $("#subscriber_email").val("");
                }
            },
            error: function (error) {
                $(".error-text").text("");
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
    },
});
function sendEmailNew(email, subject, content) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: "/api/mail/sendmail",
            type: "POST",
            dataType: "json",
            data: {
                to_email: email, // Recipient email
                subject: subject, // Email subject
                content: content, // Email content
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                hideLoader();
                $("#newsletter_success_modal").modal("show");
                resolve(response);
            },
            error: function (error) {
                toastr.error("Failed to send email.");
                reject(error);
                hideLoader();
            },
        });
    });
}
function addtocart(id, price) {
    const userId = id;
    const pprice = price;
    $.ajax({
        url: "/api/addtocart",
        type: "POST",
        dataType: "json",
        data: { id: userId, isMobile: 1, price: pprice },
    });
    toastr.success("Product Added Sucessfully");
}

function removefromcart(id) {
    var rec = "prodsec" + id;
    document.getElementById(rec).style.display = "none";

    const userId = id;
    $.ajax({
        url: "/api/removefromcart",
        type: "POST",
        dataType: "json",
        data: { id: userId, isMobile: 1 },
    });
    toastr.success("Product Added Sucessfully");
}

$(document).on("click", ".logoutUser", function () {
    localStorage.removeItem("user_id");
});

if (pageValue === "user.profile") {
    $(document).ready(function () {
        initSelect2();
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

        var langCode = $("body").data("lang");

        let currentLang = langCode;

        const validationMessages = {
            en: {
                first_name: {
                    required: "First name is required.",
                    maxlength: "First name cannot exceed 50 characters.",
                    pattern: "Only alphabets are allowed.",
                },
                last_name: {
                    required: "Last name is required.",
                    maxlength: "Last name cannot exceed 50 characters.",
                    pattern: "Only alphabets are allowed.",
                },
                user_name: {
                    required: "Username is required.",
                    maxlength: "Username cannot exceed 30 characters.",
                    remote: "Username already exists.",
                },
                email: {
                    required: "Email is required.",
                    email: "Please enter a valid email address.",
                    remote: "Email already exists.",
                },
                phone_number: {
                    required: "Phone number is required.",
                    digits: "Only digits are allowed.",
                    minlength: "Phone number must be between 10 and 12 digits.",
                    maxlength: "Phone number must be between 10 and 12 digits.",
                },
                gender: {
                    required: "Gender is required.",
                },
                dob: {
                    required: "Date of birth is required.",
                    date: "Please enter a valid date.",
                },
                address: {
                    required: "Address is required.",
                    maxlength: "Address cannot exceed 255 characters.",
                },
                country: {
                    required: "Country is required.",
                },
                state: {
                    required: "State is required.",
                },
                city: {
                    required: "City is required.",
                },
                postal_code: {
                    required: "Postal code is required.",
                    maxlength: "Postal code must be at most 6 characters.",
                    pattern:
                        "Postal code can only contain letters and numbers.",
                },
                currency_code: {
                    required: "Currency code is required.",
                },
                profile_image: {
                    extension: "Please upload a valid image file.",
                    filesize: "Image size must be less than 2 MB.",
                },
                language: {
                    required: "Language is required.",
                },
                company_image: {
                    extension: "Please upload a valid image file.",
                    filesize: "Image size must be less than 2 MB.",
                },
                company_name: {
                    required: "Company name is required.",
                    maxlength: "Company name cannot exceed 100 characters.",
                },
                company_website: {
                    required: "Company website is required.",
                    url: "Please enter a valid URL.",
                },
                company_address: {
                    required: "Company address is required.",
                    maxlength: "Company address cannot exceed 255 characters.",
                },
            },
            ar: {
                first_name: {
                    required: "الاسم الأول مطلوب.",
                    maxlength: "يجب ألا يتجاوز الاسم الأول 50 حرفًا.",
                    pattern: "يسمح بالأحرف فقط.",
                },
                last_name: {
                    required: "الاسم الأخير مطلوب.",
                    maxlength: "يجب ألا يتجاوز الاسم الأخير 50 حرفًا.",
                    pattern: "يسمح بالأحرف فقط.",
                },
                user_name: {
                    required: "اسم المستخدم مطلوب.",
                    maxlength: "يجب ألا يتجاوز اسم المستخدم 30 حرفًا.",
                    remote: "اسم المستخدم موجود بالفعل.",
                },
                email: {
                    required: "البريد الإلكتروني مطلوب.",
                    email: "يرجى إدخال عنوان بريد إلكتروني صالح.",
                    remote: "البريد الإلكتروني موجود بالفعل.",
                },
                phone_number: {
                    required: "رقم الهاتف مطلوب.",
                    digits: "يسمح بالأرقام فقط.",
                    minlength: "يجب أن يكون رقم الهاتف بين 10 و 12 رقمًا.",
                    maxlength: "يجب أن يكون رقم الهاتف بين 10 و 12 رقمًا.",
                },
                gender: {
                    required: "الجنس مطلوب.",
                },
                dob: {
                    required: "تاريخ الميلاد مطلوب.",
                    date: "يرجى إدخال تاريخ صالح.",
                },
                address: {
                    required: "العنوان مطلوب.",
                    maxlength: "يجب ألا يتجاوز العنوان 255 حرفًا.",
                },
                country: {
                    required: "الدولة مطلوبة.",
                },
                state: {
                    required: "الولاية مطلوبة.",
                },
                city: {
                    required: "المدينة مطلوبة.",
                },
                postal_code: {
                    required: "الرمز البريدي مطلوب.",
                    maxlength: "يجب ألا يتجاوز الرمز البريدي 6 أحرف.",
                    pattern: "يمكن أن يحتوي الرمز البريدي على أحرف وأرقام فقط.",
                },
                currency_code: {
                    required: "رمز العملة مطلوب.",
                },
                profile_image: {
                    extension: "يرجى تحميل ملف صورة صالح.",
                    filesize: "يجب أن يكون حجم الصورة أقل من 2 ميغابايت.",
                },
                language: {
                    required: "اللغة مطلوبة.",
                },
                company_image: {
                    extension: "يرجى تحميل ملف صورة صالح.",
                    filesize: "يجب أن يكون حجم الصورة أقل من 2 ميغابايت.",
                },
                company_name: {
                    required: "اسم الشركة مطلوب.",
                    maxlength: "يجب ألا يتجاوز اسم الشركة 100 حرف.",
                },
                company_website: {
                    required: "موقع الشركة مطلوب.",
                    url: "يرجى إدخال رابط صالح.",
                },
                company_address: {
                    required: "عنوان الشركة مطلوب.",
                    maxlength: "يجب ألا يتجاوز عنوان الشركة 255 حرفًا.",
                },
            },
        };

        $("#userProfileForm").validate({
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
                                return $("#user_email").val();
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
                    required: true,
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
                currency_code: {
                    required: true,
                },
                profile_image: {
                    extension: "jpeg|jpg|png",
                    filesize: 2048,
                },
                language: {
                    required: true,
                },
            },
            messages: validationMessages[currentLang],
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
                $.ajax({
                    url: "/api/save-profile-details",
                    type: "POST",
                    data: formData,
                    enctype: "multipart/form-data",
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        $("#saveProfile")
                            .attr("disabled", true)
                            .html(
                                '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                            );
                    },
                    success: function (response) {
                        $(".error-text").text("");
                        $("#saveProfile")
                            .removeAttr("disabled")
                            .html($("#saveProfile").data("save"));
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".select2-container").removeClass(
                            "is-invalid is-valid"
                        );
                        if (response.code === 200) {
                            getProfileDetails();
                            toastr.success(response.message);
                        }
                    },
                    error: function (error) {
                        $(".error-text").text("");
                        $("#saveProfile")
                            .removeAttr("disabled")
                            .html($("#saveProfile").data("save"));
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

        $.validator.addMethod(
            "filesize",
            function (value, element, param) {
                if (element.files.length === 0) return true;
                return element.files[0].size <= param * 1024;
            },
            "File size must be less than {0} KB."
        );
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
            console.error("Error loading country data");
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
                stateSelect.append(
                    $("<option>", {
                        value: states[0].id,
                        text: states[0].name,
                        selected: true,
                    })
                );
                getCities(states[0].id, selectedCity);
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
            console.error("Error loading state data");
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
            console.error("Error loading city data");
        });
    }

    $("#profileImageBtn").on("click", function () {
        $("#profile_image").trigger("click");
    });

    $("#profile_image").on("change", function (event) {
        if ($(this).val() !== "") {
            $(this).valid();
        }
        let reader = new FileReader();
        reader.onload = function (e) {
            $(".profileImagePreview").attr("src", e.target.result).show();
        };
        reader.readAsDataURL(event.target.files[0]);
    });

    async function getProfileDetails() {
        try {
            const userId = $("#id").val();
            const response = await $.ajax({
                url: "/api/get-profile-details",
                type: "POST",
                dataType: "json",
                data: { id: userId, isMobile: 1 },
            });

            if (response.code === 200) {
                var userData = response.data.user_details;
                if (userData.profile_image != null) {
                    $(".headerProfileImg").attr("src", userData.profile_image);
                }
                if (userData.first_name != null && userData.last_name != null) {
                    $(".headerName").text(
                        userData.first_name + " " + userData.last_name
                    );
                }
            }
        } catch (error) {
            console.error(
                "Error fetching user details:",
                error.responseJSON?.message || error.statusText
            );
        }
    }
}

if (pageValue === "user.leads") {
    $.ajax({
        url: "/api/get-session-user-id",
        type: "GET",
        success: function (response) {
            if (response.user_id) {
                userId = response.user_id;
                localStorage.setItem("user_id", userId);
            }
        },
        error: function () {
            toastr.error("Unable to fetch session data. Please try again.");
        },
    });
    const user_id = localStorage.getItem("user_id");

    $(document).on("click", ".view-lead-detail", function (e) {
        e.preventDefault();

        const id = $(this).data("id");

        localStorage.setItem("leadId", id);

        window.location.href = "/user/leadsinfo";
    });

    $(document).on("click", ".accept_btn, .reject_btn", function (e) {
        e.preventDefault();

        var leadId = $(this).data("id");
        var status = $(this).hasClass("accept_btn") ? 2 : 3;

        $.ajax({
            url: "/api/leads/admin/status",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                id: leadId,
                status: status,
            },
            success: function (response) {
                if (response.success) {
                    toastr.success(
                        response.message || "Status updated successfully!"
                    );
                } else {
                    toastr.error(
                        response.message || "Failed to update status."
                    );
                }
            },
            error: function (xhr) {
                toastr.error("Failed to update status. Please try again.");
            },
        });
    });
    $("#sortSelect").change(function () {
        loadLeads(1);
    });
    $("#order_byselect").change(function () {
        loadLeads(1);
    });

    loadLeads();
    $(document).ready(function () {
        if ($("#activeStatusInput").length === 0) {
            $("body").append(
                '<input type="hidden" id="activeStatusInput" value="" />'
            );
        }
    });

    function setActiveTab(tab, status) {
        $("#activeStatusInput").val(status); // Update the hidden input value
        $(".nav-link").removeClass("active"); // Remove active class from all tabs
        $(tab).addClass("active"); // Add active class to the clicked tab
    }

    // Function to load leads
    function loadLeads(page = 1) {
        const selectedSortBy = $("#sortSelect").val();
        const selectedOrderBy = $("#order_byselect").val();
        const activeStatus = $("#activeStatusInput").val(); // Retrieve activeStatus from hidden input

        const payload = {
            order_by: selectedOrderBy,
            sort_by: selectedSortBy,
            search: "",
            page: page,
            per_page: 5,
            user_id: user_id,
            status: activeStatus, // Use the retrieved `activeStatus`
        };

        $.ajax({
            url: "/api/leads/list",
            type: "POST",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            data: JSON.stringify(payload),
            contentType: "application/json",
            success: function (response) {
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
                $("#leadsLoader").hide();
                $("#accordionExample").empty();
                if (response.data.meta && response.data.meta.counts) {
                    const counts = response.data.meta.counts;

                    $("#inbox-tab span").text(counts.all || 0);
                    $("#new-tab span").text(counts.new || 0);
                    $("#accept-tab span").text(counts.accept || 0);
                    $("#reject-tab span").text(counts.reject || 0);

                    $("#inbox-tab").prop("disabled", counts.all === 0);
                    $("#new-tab").prop("disabled", counts.new === 0);
                    $("#accept-tab").prop("disabled", counts.accept === 0);
                    $("#reject-tab").prop("disabled", counts.reject === 0);

                    $("#inbox-tab").toggleClass("disabled", counts.all === 0);
                    $("#new-tab").toggleClass("disabled", counts.new === 0);
                    $("#accept-tab").toggleClass(
                        "disabled",
                        counts.accept === 0
                    );
                    $("#reject-tab").toggleClass(
                        "disabled",
                        counts.reject === 0
                    );
                }
                if (
                    response.data &&
                    response.data.user_form_inputs.data.length > 0
                ) {
                    let showStatusDiv = false;
                    const dateformatSetting = response.data.dateformatSetting;
                    response.data.user_form_inputs.data.forEach((item) => {
                        if (item.status == "1") {
                            showStatusDiv = true;
                        }

                        const providers = item.provider_forms_inputs.map(
                            (input) => {
                                const name = input.provider?.name || "";
                                return (
                                    name.charAt(0).toUpperCase() +
                                    name.slice(1).toLowerCase()
                                );
                            }
                        );

                        if (providers.length === 0) {
                            return;
                        }

                        const createdAt = new Date(item.created_at);

                        let providerDisplay = "";
                        if (providers.length > 2) {
                            providerDisplay = `
                                ${providers.slice(0, 2).join(", ")}
                                <a href="#" class="view-more-providers text-decoration-none" data-providers="${providers.join(
                                    ", "
                                )}">
                                    +${providers.length - 2} more
                                </a>`;
                        } else {
                            providerDisplay = providers.join(", ");
                        }

                        const statusLabel = getStatusLabel(item.status);

                        if (languageId === 2) {
                            loadJsonFile(statusLabel, function (langtst) {
                                $(
                                    `.user_status[data-status="${item.status}"]`
                                ).text(langtst);
                            });
                        }

                        const cardHtml = `
                            <div class="border bg-light-300 p-2 rounded mb-1">
                                <div>
                                    <div class="row align-items-center">
                                        <div class="col-md-7">
                                            <div class="input-block todo-inbox-check d-flex align-items-center w-100">
                                                <span class="avatar p-1 me-2 bg-light flex-shrink-0">
                                                    <i class="ti ti-user-edit text-info fs-20"></i>
                                                </span>
                                                <div class="strike-info">
                                                    <h6 class="mb-1 fs-16 custom-heading text-truncate">${providerDisplay}</h6>
                                                    <p class="d-flex align-items-center custom-paragraph mb-0 text-truncate text-nowrap">
                                                        <i class="ti ti-calendar me-1"></i>
                                                        <span class="text-truncate">${item.formatted_created_at}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="d-flex justify-content-end align-items-center flex-nowrap gap-2 w-100">
                                                <span class="badge badge-soft-warning category-badge flex-shrink-1 text-truncate">
                                                    ${item.category.name || "N/A"}
                                                </span>
                                                <p class="badge bg-outline-primary mb-0 flex-shrink-0">
                                                    ${getStatusLabel(item.status)}
                                                </p>
                                                <a href="#" class="text-decoration-none d-flex align-items-center flex-shrink-0 view-lead-detail"
                                                data-id="${item.id}"
                                                data-name="${item.user.name || "N/A"}"
                                                data-status="${item.status || "New"}"
                                                data-details="Meet ${item.user.name || "N/A"} to discuss project details"
                                                data-created-at="${createdAt}"
                                                data-category="${item.category.name || "N/A"}"
                                                data-form-inputs='${JSON.stringify(item.form_inputs || [])}'>
                                                    <i class="ti ti-eye fs-20"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;

                        $("#accordionExample").append(cardHtml);
                    });

                    if (showStatusDiv) {
                        $("#status_div").show();
                    } else {
                        $("#status_div").hide();
                    }

                    const totalPages = response.data.user_form_inputs.last_page;
                    const currentPage =
                        response.data.user_form_inputs.current_page;
                    const maxVisiblePages = 5;

                    let startPage = Math.max(
                        currentPage - Math.floor(maxVisiblePages / 2),
                        1
                    );
                    let endPage = startPage + maxVisiblePages - 1;

                    if (endPage > totalPages) {
                        endPage = totalPages;
                        startPage = Math.max(endPage - maxVisiblePages + 1, 1);
                    }
                    if (languageId === 2) {
                        loadJsonFile("Previous", function (langtst) {
                            $(".page_link").text(langtst);
                        });
                    }
                    if (languageId === 2) {
                        loadJsonFile("Next", function (langtst) {
                            $(".page_link").text(langtst);
                        });
                    }

                    const paginationHtml = `
                        <nav>
                            <ul class="pagination">
                                ${
                                    response.data.user_form_inputs.prev_page_url
                                        ? `<li class="page-item"><a class="page-link page_link" href="#" onclick="loadLeads(${
                                              currentPage - 1
                                          })">Previous</a></li>`
                                        : ""
                                }

                                ${Array.from(
                                    { length: endPage - startPage + 1 },
                                    (_, i) => {
                                        const pageNumber = startPage + i;
                                        return `
                                        <li class="page-item ${
                                            currentPage === pageNumber
                                                ? "active"
                                                : ""
                                        }">
                                            <a class="page-link " href="#" onclick="loadLeads(${pageNumber})">${pageNumber}</a>
                                        </li>`;
                                    }
                                ).join("")}

                                ${
                                    response.data.user_form_inputs.next_page_url
                                        ? `<li class="page-item"><a class="page-link page_link" href="#" onclick="loadLeads(${
                                              currentPage + 1
                                          })">Next</a></li>`
                                        : ""
                                }
                            </ul>
                        </nav>
                    `;

                    $("#pagination").html(paginationHtml);

                    $(".view-lead-details").on("click", function () {
                        const id = $(this).data("id");
                        const name = $(this).data("name");
                        const status = $(this).data("status");
                        const details = $(this).data("details");
                        const createdAt = $(this).data("created-at");
                        const category = $(this).data("category");
                        let formInputs = $(this).data("form-inputs");

                        $("#view-note-units .accept_btn").data("id", id);
                        $("#view-note-units .reject_btn").data("id", id);
                        $("#view-note-units .modal-body h4").text(name);
                        $("#view-note-units .modal-body .status").text(
                            `Status: ${status}`
                        );
                        $("#view-note-units .modal-body .times").text(
                            `Created At: ${createdAt}`
                        );
                        $("#view-note-units .modal-body .category").text(
                            `Category: ${category}`
                        );

                        if (typeof formInputs === "string") {
                            formInputs = JSON.parse(formInputs);
                        }

                        let formInputsHtml = "";
                        formInputs.forEach((input) => {
                            formInputsHtml += `
                                <div class="col-md-6 border border-1 mt-2">
                                    <div>
                                         <p class="mt-1"><strong>${
                                             input.details.title || "N/A"
                                         }:</strong></p>
                                         <h6 class="mb-1">${
                                             input.value || "N/A"
                                         }</h6>
                                    </div>
                                </div>`;
                        });
                        $("#form-inputs").html(formInputsHtml);
                    });
                } else {
                    $(document).ready(function () {
                        if (languageId === 2) {
                            loadJsonFile(
                                "No leads available",
                                function (langtst) {
                                    $(".no_leads").text(langtst);
                                }
                            );
                        }
                    });
                    $("#sortSelect").closest(".form-sort").hide();
                    $("#order_byselect").closest(".form-sort").hide();
                    $("#accordionExample").append(`
                        <div class="d-flex justify-content-center align-items-center noleads" style="height: 50vh;">
                            <p class="text-center no_leads fw-bold">No leads available</p>
                        </div>
                    `);
                }
            },
            error: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            },
        });
    }

    function getStatusClass(status) {
        switch (status) {
            case 1:
                return "bg-outline-primary";
            case 2:
                return "bg-outline-warning";
            case 3:
                return "bg-outline-danger";
            default:
                return "bg-outline-secondary";
        }
    }
    function getStatusLabel(status) {
        switch (status) {
            case 1:
                return "New";
            case 2:
                return "Accepted";
            case 3:
                return "Rejected";
            default:
                return "Unknown";
        }
    }
}

if (pageValue === "user.leadsinfo") {
    const leadId = localStorage.getItem("leadId");
    $(document).on("click", ".chattab", function () {
        const providerId = $(this).data("providerid");
        const providerName = $(this).data("providername");
        const authuserid = $(this).data("userid");
        let encrypted_user_id;

        window.location.href = "/user/chat/";
    });

    function setSessionValue(key, value, authid) {
        $.ajax({
            url: "/set-session",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                key: key,
                value: value,
                type: "userchat",
                authid: authid,
            },
            success: function (response) {
                if (response.success) {
                }
            },
            error: function (xhr) {
                console.error("Error setting session value:", xhr.responseText);
            },
        });
    }

    function capitalizeFirstLetter(string) {
        if (!string) return string;

        return string
            .split(" ")
            .map(
                (word) =>
                    word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
            )
            .join(" ");
    }

    $(document).on("click", ".provider_list", function (e) {
        e.preventDefault();
        providerList();
        function providerList() {
            $.ajax({
                url: "/api/leads/list",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                data: {
                    order_by: "desc",
                    id: leadId,
                },
                success: function (response) {
                    if (response.data) {
                        var symbol = response.data.currencySymbol;
                        var dataFormat = response.data.dateformatSetting;

                        $("#new-tab-content").empty();
                        $("#accepted-tab-content").empty();
                        $("#rejected-tab-content").empty();

                        response.data.user_form_inputs.data.forEach(function (
                            item
                        ) {
                            item.provider_forms_inputs.forEach(function (
                                formInput,
                                index
                            ) {
                                var createdAt = new Date(
                                    item.created_at
                                ).toLocaleDateString();

                                let statusText = "";
                                let statusClass = "";
                                switch (formInput.status) {
                                    case 1:
                                        statusText = "New";
                                        statusClass = "bg-outline-primary";
                                        break;
                                    case 2:
                                        statusText = "Accepted";
                                        statusClass = "bg-outline-success";
                                        break;
                                    case 3:
                                        statusText = "Rejected";
                                        statusClass = "bg-outline-danger";
                                        break;
                                    default:
                                        statusText = "Unknown";
                                        statusClass = "bg-outline-secondary";
                                }
                                if (languageId === 2) {
                                    loadJsonFile(
                                        statusText,
                                        function (langtst) {
                                            $(`#provider_status_${index}`).text(
                                                langtst
                                            );
                                        }
                                    );
                                }
                                if (languageId === 2) {
                                    loadJsonFile("Paid", function (langtst) {
                                        $(".paid_button").text(langtst);
                                    });
                                    loadJsonFile("Pay Now", function (langtst) {
                                        $(".pay_button").text(langtst);
                                    });
                                    loadJsonFile("Chat", function (langtst) {
                                        $(".chat-btn").text(langtst);
                                    });
                                    loadJsonFile("Accept", function (langtst) {
                                        $(".btn-accept").text(langtst);
                                    });
                                    loadJsonFile("Reject", function (langtst) {
                                        $(".btn-reject").text(langtst);
                                    });
                                    loadJsonFile("Quote", function (langtst) {
                                        $(".quote").text(langtst);
                                    });
                                    loadJsonFile(
                                        "Start Date",
                                        function (langtst) {
                                            $(".start_date").text(langtst);
                                        }
                                    );
                                    loadJsonFile(
                                        "Description",
                                        function (langtst) {
                                            $(".description").text(langtst);
                                        }
                                    );
                                    loadJsonFile(
                                        "Order Confirmed",
                                        function (langtst) {
                                            $(".order_confirmed").text(langtst);
                                        }
                                    );
                                }
                                var providerCard = `
                                <div class="card mt-2 mb-2">
                                    <div class="card-body p-3 pb-0">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                                            <div class="input-block todo-inbox-check d-flex align-items-center w-50 mb-3">
                                                <span class="avatar p-1 me-2 bg-teal-transparent flex-shrink-0">
                                                    <i class="ti ti-user-edit text-info fs-20"></i>
                                                </span>
                                               <div class="strike-info">
                                                    <h4 class="mb-1">${
                                                        capitalizeFirstLetter(
                                                            formInput.provider
                                                                .name
                                                        ) || "N/A"
                                                    }</h4>
                                                </div>
                                                <div class="strike-info mx-2">
                                                    <span class="badge badge-soft-warning ms-1">${
                                                        capitalizeFirstLetter(
                                                            item.category.name
                                                        ) || "N/A"
                                                    }</span>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center flex-fill justify-content-between ms-4 mb-3">
                                                <div class="notes-card-body d-flex align-items-center">
                                                ${
                                                    formInput.user_status === 2
                                                        ? `
                                                    <p id="" class="order_confirmed badge bg-outline-success mb-0">Order Confirmed</p>
                                                `
                                                        : `
                                                    <p id="provider_status_${index}" class="provider_status badge ${statusClass} mb-0">${statusText}</p>
                                                `
                                                }


                                                </div>
                                                 ${
                                                     formInput.quote
                                                         ? `<div class="d-flex align-items-center">
                                                    <a href="javascript:void(0)" class="text-decoration-none me-3 view-quote-details">
                                                        <i class="ti ti-eye fs-25">view</i>
                                                    </a>
                                                </div>`
                                                         : ""
                                                 }
                                            </div>
                                        </div>
                                        <div class="provider-quote-details p-3 mb-3 border rounded bg-light" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-2">
                                                        <strong class="text-primary quote ">Quote:</strong>
                                                        <span>${
                                                            symbol || ""
                                                        }</span><span>${
                                    formInput.quote || "N/A"
                                }</span>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong class="text-primary start_date">Start Date:</strong>
                                                        <span>
                                                        ${
                                                            formInput.start_date
                                                                ? new Date(
                                                                      formInput.start_date
                                                                  ).toLocaleDateString(
                                                                      "en-GB"
                                                                  )
                                                                : "N/A"
                                                        }
                                                        </span>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong class="text-primary description">Description:</strong>
                                                        <span>${
                                                            formInput.description ||
                                                            "N/A"
                                                        }</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    ${
                                                        formInput.status ===
                                                            2 &&
                                                        item.status === 1 &&
                                                        formInput.user_status !=
                                                            3 &&
                                                        formInput.quote
                                                            ? `<div class="mt-3 d-flex justify-content-end">
                                                            <button class="btn btn-success me-2 btn-accept" data-id="${item.id}" data-provider="${formInput.id}" data-provider_email="${formInput.provider.email}" data-user_name="${item.user.name}" data-user_id="${item.user.id}" data-category_name="${item.category.name}" data-quote_amount="${formInput.quote}">Accept</button>
                                                            <button class="btn btn-danger btn-reject" data-id="${item.id}" data-provider="${formInput.id}" data-provider_email="${formInput.provider.email}" data-user_name="${item.user.name}" data-user_id="${item.user.id}" data-category_name="${item.category.name}" data-quote_amount="${formInput.quote}">Reject</button>
                                                        </div>`
                                                            : ""
                                                    }
                                                    ${
                                                        formInput.status ===
                                                            2 &&
                                                        item.status === 2 &&
                                                        formInput.user_status ===
                                                            2 &&
                                                        formInput.quote &&
                                                        item.payment_success ===
                                                            1
                                                            ? `<div class="mt-3 d-flex flex-column align-items-end">
                                                            <a href="/user/chat/${
                                                                formInput.encrypted_provider_id
                                                            }" class="btn btn-primary mb-2 chat-btn chattab"
                                                                    data-providerid="${
                                                                        formInput
                                                                            .provider
                                                                            .id
                                                                    }"
                                                                    data-providername="${
                                                                        formInput
                                                                            .provider
                                                                            .name
                                                                    }"
                                                                    data-userid="${
                                                                        item.user_id
                                                                    }">
                                                                Chat
                                                            </a>
                                                            <span class="text-primary" >${
                                                                formInput
                                                                    .provider
                                                                    .email ||
                                                                "N/A"
                                                            }</span>
                                                            <span class="text-primary" >${
                                                                formInput
                                                                    .provider
                                                                    .phone_number ||
                                                                "N/A"
                                                            }</span>
                                                        </div>
                                                        `
                                                            : ""
                                                    }
                                                        ${
                                                            formInput.status ===
                                                                2 &&
                                                            item.status === 2 &&
                                                            formInput.user_status ===
                                                                2 &&
                                                            formInput.quote
                                                                ? item.payment_success ===
                                                                  1
                                                                    ? `<div class="mt-3 d-flex justify-content-end">
                                                                        <button type="button" class="btn btn-success paid_button mt-2" disabled>Paid</button>
                                                                   </div>`
                                                                    : `<div class="mt-3 d-flex justify-content-end">
                                                                        <button type="button" class="btn btn-primary pay_button mt-2 payment" data-refid="${item.id}" data-providerid="${formInput.provider.id}" data-amt="${formInput.quote}">Pay Now</button>
                                                                   </div>`
                                                                : ""
                                                        }
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                                switch (formInput.status) {
                                    case 1:
                                        $("#new-tab-content").append(
                                            providerCard
                                        );
                                        break;
                                    case 2:
                                        $("#accepted-tab-content").append(
                                            providerCard
                                        );
                                        break;
                                    case 3:
                                        $("#rejected-tab-content").append(
                                            providerCard
                                        );
                                        break;
                                    default:
                                        toastr.error(
                                            "Unknown status",
                                            formInput.status
                                        );
                                }
                            });
                        });
                        if (languageId === 2) {
                            loadJsonFile(
                                "No providers found",
                                function (langtst) {
                                    $(".provider_list").text(langtst);
                                }
                            );
                        }
                        [
                            "#new-tab-content",
                            "#accepted-tab-content",
                            "#rejected-tab-content",
                        ].forEach(function (tabId) {
                            if ($(tabId).children().length === 0) {
                                $(tabId).html(
                                    '<p class="text-muted provider_list text-center mt-3">No providers found</p>'
                                );
                            }
                        });

                        $(".view-quote-details").on("click", function () {
                            $(this)
                                .closest(".card")
                                .find(".provider-quote-details")
                                .slideToggle();
                        });

                        function sendEmail(email, emailData, userName) {
                            return new Promise((resolve, reject) => {
                                $.ajax({
                                    url: "/api/mail/sendmail",
                                    type: "POST",
                                    dataType: "json",
                                    data: {
                                        to_email: email,
                                        notification_type: 4,
                                        type: 1,
                                        user_name: userName,
                                        subject: emailData.subject,
                                        content: emailData.content,
                                    },
                                    headers: {
                                        Authorization:
                                            "Bearer " +
                                            localStorage.getItem("admin_token"),
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

                        $(".btn-accept").on("click", function () {
                            const button = $(this); // Store the clicked button
                            const leadId = button.data("id");
                            const providerId = button.data("provider");
                            const user_name = button.data("user_name");
                            const user_id = button.data("user_id");
                            const provider_email =
                                button.data("provider_email");
                            const category_name = button.data("category_name");
                            const quote_amount = button.data("quote_amount");

                            $.ajax({
                                url: "/api/leads/user/status",
                                type: "POST",
                                dataType: "json",
                                data: {
                                    id: leadId,
                                    provider_forms_input: providerId,
                                    user_name: user_name,
                                    user_id: user_id,
                                    provider_email: provider_email,
                                    category_name: category_name,
                                    quote_amount: quote_amount,
                                    status: 2,
                                },
                                headers: {
                                    Authorization:
                                        "Bearer " +
                                        localStorage.getItem("admin_token"),
                                    Accept: "application/json",
                                },
                                beforeSend: function () {
                                    button
                                        .attr("disabled", true)
                                        .html(
                                            '<div class="spinner-border spinner-border-sm text-light" role="status"></div>'
                                        );
                                },
                                success: function (response) {
                                    button
                                        .closest(".card")
                                        .find(".chat-btn")
                                        .show();
                                    if (response && response.message) {
                                        // toastr.success(response.message);
                                        if (languageId === 2) {
                                            loadJsonFile(
                                                response.message,
                                                function (langtst) {
                                                    toastr.success(
                                                        langtst,
                                                        "",
                                                        {
                                                            toastClass:
                                                                "toastprovider",
                                                        }
                                                    );
                                                }
                                            );
                                        } else {
                                            toastr.success(
                                                response.message,
                                                "",
                                                {
                                                    toastClass: "toastprovider",
                                                }
                                            );
                                        }
                                    } else {
                                        toastr.success(
                                            "Leads Accept successfully."
                                        ); // Fallback message
                                    }
                                    providerList();
                                    userList();
                                },
                                error: function (error) {
                                    toastr.error("Failed to accept the lead.");
                                    console.error(
                                        "Error:",
                                        error.responseJSON || error
                                    );
                                },
                                complete: function () {
                                    button
                                        .removeAttr("disabled")
                                        .html("Accept");
                                },
                            });
                        });

                        $(".btn-reject").on("click", function () {
                            const button = $(this); // Store the clicked button
                            const leadId = button.data("id");
                            const providerId = button.data("provider");
                            const user_name = button.data("user_name");
                            const user_id = button.data("user_id");
                            const provider_email =
                                button.data("provider_email");
                            const category_name = button.data("category_name");
                            const quote_amount = button.data("quote_amount");

                            $.ajax({
                                url: "/api/leads/user/status",
                                type: "POST",
                                data: {
                                    id: leadId,
                                    provider_forms_input: providerId,
                                    user_name: user_name,
                                    user_id: user_id,
                                    provider_email: provider_email,
                                    category_name: category_name,
                                    quote_amount: quote_amount,
                                    status: 3,
                                },
                                beforeSend: function () {
                                    button
                                        .attr("disabled", true)
                                        .html(
                                            '<div class="spinner-border spinner-border-sm text-light" role="status"></div>'
                                        );
                                },
                                success: function (response) {
                                    if (response && response.message) {
                                        // toastr.success(response.message);
                                        if (languageId === 2) {
                                            loadJsonFile(
                                                response.message,
                                                function (langtst) {
                                                    toastr.success(
                                                        langtst,
                                                        "",
                                                        {
                                                            toastClass:
                                                                "toastprovider",
                                                        }
                                                    );
                                                }
                                            );
                                        } else {
                                            toastr.success(
                                                response.message,
                                                "",
                                                {
                                                    toastClass: "toastprovider",
                                                }
                                            );
                                        }
                                    } else {
                                        toastr.success(
                                            "Leads Reject successfully."
                                        ); // Fallback message
                                    }
                                    userList();
                                    providerList();
                                },
                                error: function (error) {
                                    toastr.error("Failed to reject the lead.");
                                },
                                complete: function () {
                                    button
                                        .removeAttr("disabled")
                                        .html("Reject");
                                },
                            });
                        });
                    }
                },
                error: function (xhr) {
                    toastr.error("Failed to update status. Please try again.");
                },
            });
        }
    });
    /*payment*/
    let trxid;
    $(document).on("click", ".payment", function () {
        var amt = $(this).data("amt");
        var refid = $(this).data("refid");
        var providerid = $(this).data("providerid");
        $.ajax({
            url: "/api/storepayments",
            type: "POST",
            data: {
                amount: amt,
                refid: refid,
                user_id: $("body").data("authid"),
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                trxid = response.data;
                $(".trx_id").val(trxid);
                fetchpaymentmethod(amt, refid, trxid, providerid);
            },
        });
    });
    function fetchpaymentmethod(amt, refid, trxid, providerid) {
        $.ajax({
            url: "/api/getpaymentmethod",
            type: "POST",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response && response.length > 0) {
                    let csrfToken = document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content");
                    let html = `
                        <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="paymentModalLabel">Select Payment Method</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form  id="payment" enctype="multipart/form-data" name="paybook">
                                            <input type="hidden" name="_token" value="${csrfToken}">
                                            <input type="hidden" name="amount" class="amount" value="${amt}">
                                            <input type="hidden" name="refid" class="refid" value="${refid}">
                                            <input type="hidden" name="providerid" class="providerid" value="${providerid}">
                                            <input type="hidden" name="name" class="usrname" value="">
                                            <input type="hidden" name="trx_id" class="trx_id" value="${trxid}">
                                            <input type="hidden" name="type" class="type" value="">
                                            <input type="hidden" name="payment_type" class="paymenttype" value="">
                                            <div class="mb-3" id="paymentmethoddiv">
                                                <label class="form-check-label mb-2">Choose Payment Method:</label>
                    `;

                    response.forEach((data) => {
                        if (data.label != "banktransfer") {
                            html += `
                                <div class="form-check">
                                    <input class="form-check-input paymentmethod" type="radio" name="paymentMethod" id="${data.label}" value="${data.label}">
                                    <label class="form-check-label" for="${data.label}">${data.payment_type}</label>
                                </div>
                            `;
                        }
                    });

                    html += `
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100 mt-3" id="payNowButton">Pay Now</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    $("body").append(html);

                    $("#paymentModal").modal("show");

                    $("#paymentModal").on("hidden.bs.modal", function () {
                        $(this).remove();
                    });
                } else {
                    alert("No payment methods available");
                }
            },
            error: function () {
                alert("Error fetching payment methods");
            },
        });
    }
    $(document).on("submit", "#payment", function (event) {
        const selectedPaymentMethod = $(
            'input[name="paymentMethod"]:checked'
        ).val();
        var amount = parseInt($(".amount").val());
        var refid = $(".refid").val();
        var providerid = $(".providerid").val();
        var username = $(".username").text();
        var trxid = $(".trx_id").val();

        if (selectedPaymentMethod === "paypal") {
            event.preventDefault();
            $.ajax({
                url: "/processpayment",
                type: "POST",
                data: {
                    paymenttype: 1,
                    name: username,
                    service_amount: amount,
                    trx_id: trxid,
                    refid: refid,
                    providerid: providerid,
                    type: "user",
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
                    window.location.href = response;
                },
                error: function (xhr) {
                    toastr.error(
                        xhr.responseJSON.message ||
                            "Paypal is currently unavailable. Please choose another payment method."
                    );
                },
            });
        } else if (selectedPaymentMethod === "stripe") {
            const form = $("#payment");
            form.attr("method", "POST");
            form.attr("action", "/stripepayment");
            $(".type").val("user");
            $(".paymenttype").val(2);
            $(".usrname").val(username);
        } else if (selectedPaymentMethod === "mollie") {
            event.preventDefault();
            $.ajax({
                url: "/molliepayment",
                type: "POST",
                data: {
                    paymenttype: 3,
                    name: username,
                    service_amount: amount,
                    trx_id: trxid,
                    refid: refid,
                    providerid: providerid,
                    type: "user",
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
                    window.location.href = response.url;
                },
                error: function (xhr) {
                    if (xhr.responseJSON.code == 422) {
                        toastr.error(
                            xhr.responseJSON.message ||
                                "Mollie is currently unavailable. Please choose another payment method."
                        );
                    } else {
                        toastr.error("An error occurred. Please try again.");
                    }
                },
            });
        } else if (selectedPaymentMethod === "wallet") {
            event.preventDefault();
            $.ajax({
                url: "/walletPayment",
                type: "POST",
                data: {
                    paymenttype: 4,
                    name: username,
                    service_amount: amount,
                    trx_id: trxid,
                    refid: refid,
                    providerid: providerid,
                    type: "user",
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
                    window.location.href = response.url;
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        toastr.error(
                            xhr.responseJSON.message ||
                                "Insufficient wallet balance"
                        );
                    } else {
                        toastr.error("An error occurred. Please try again.");
                    }
                },
            });
        }
    });
    const payload = {
        order_by: "desc",
        sort_by: "created_at",
        search: "",
        id: leadId,
    };
    userList();
    function userList() {
        $.ajax({
            url: "/api/leads/list",
            type: "POST",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            data: JSON.stringify(payload),
            contentType: "application/json",
            success: function (response) {
                if (
                    response.success &&
                    response.data.user_form_inputs.data.length > 0
                ) {
                    const lead = response.data.user_form_inputs.data[0];

                    $(".modal-title").text(`Lead ID: ${lead.id}`);
                    $(".times").text(` ${lead.formatted_created_at}`);

                    // Update status logic
                    let statusText = "";
                    let statusClass = "";
                    switch (lead.status) {
                        case 1:
                            statusText = "New";
                            statusClass = "bg-outline-primary";
                            break;
                        case 2:
                            statusText = "Accepted";
                            statusClass = "bg-outline-success";
                            break;
                        case 3:
                            statusText = "Rejected";
                            statusClass = "bg-outline-danger";
                            break;
                        default:
                            statusText = "Unknown";
                            statusClass = "bg-outline-secondary";
                    }
                    if (languageId === 2) {
                        loadJsonFile(statusText, function (langtst) {
                            $(".status").text(langtst);
                        });
                    } else {
                        // If languageId is not 2, just display the statusText as is
                        $(".status").text(statusText);
                    }

                    $(".status")
                        .text(statusText)
                        .removeClass(
                            "bg-outline-primary bg-outline-success bg-outline-danger bg-outline-secondary"
                        )
                        .addClass(statusClass);

                    if (lead.status === 1) {
                        $("#status_div").show();
                        $("#accept_btn").data("id", lead.id);
                        $("#reject_btn").data("id", lead.id);
                    } else {
                        $("#status_div").hide();
                    }

                    $(".category").text(
                        `Category: ${capitalizeFirstLetter(lead.category.name)}`
                    );
                    $(".sub_category").text(
                        `Sub Category: ${
                            capitalizeFirstLetter(lead.sub_category?.name) ??
                            "-"
                        }`
                    );
                    if (languageId === 2) {
                        loadJsonFile("Category", function (langtst) {
                            $(".category").text(
                                `${langtst}: ${capitalizeFirstLetter(
                                    lead.category.name
                                )}`
                            );
                        });

                        loadJsonFile("Sub Category", function (langtst) {
                            const subCategoryName =
                                capitalizeFirstLetter(
                                    lead.sub_category?.name
                                ) ?? "-";
                            $(".sub_category").text(
                                `${langtst}: ${subCategoryName}`
                            );
                        });
                    }
                    $(".username").text(
                        `${capitalizeFirstLetter(lead.user.name)}`
                    );

                    $("#form-inputs").empty();

                    if (lead.form_inputs && lead.form_inputs.length > 0) {
                        lead.form_inputs.forEach((input) => {
                            $("#form-inputs").append(`
                                <div class="col-md-12">
                                    <div class="tab-info mt-3 border border-1 p-2">
                                        ${
                                            input.id !== "sub_category_id"
                                                ? `<h5 class="mt-2">${input.details.title}:</h5>`
                                                : ""
                                        }

                                        <!-- If the option is not null, do not display input.value directly -->
                                        ${
                                            input.id !== "sub_category_id"
                                                ? input.details.option &&
                                                  input.details.option !==
                                                      "null"
                                                    ? (() => {
                                                          const options1 =
                                                              JSON.parse(
                                                                  input.details
                                                                      .option
                                                              );
                                                          const options =
                                                              JSON.parse(
                                                                  options1
                                                              );
                                                          const matchedOption =
                                                              options.find(
                                                                  (option) =>
                                                                      option.value ===
                                                                      input.value
                                                              );
                                                          return matchedOption
                                                              ? `<p>${matchedOption.key}</p>`
                                                              : `<p>${input.value}</p>`;
                                                      })()
                                                    : input.value.country &&
                                                      input.value.state &&
                                                      input.value.city
                                                    ? `<p>${input.value.country}, ${input.value.state}, ${input.value.city}</p>`
                                                    : input.value &&
                                                      input.value.includes(
                                                          "uploads/leads/"
                                                      )
                                                    ? (() => {
                                                          const fileExtension =
                                                              input.value
                                                                  .split(".")
                                                                  .pop()
                                                                  .toLowerCase();
                                                          const documentExtensions =
                                                              [
                                                                  "pdf",
                                                                  "doc",
                                                                  "docx",
                                                                  "txt",
                                                              ];

                                                          if (
                                                              documentExtensions.includes(
                                                                  fileExtension
                                                              )
                                                          ) {
                                                              return `<a href="/storage/${input.value}" download class="btn btn-primary">
                                                                        Download Document
                                                                    </a>`;
                                                          } else {
                                                              return `<img src="/storage/${input.value}" alt="Image Preview" class="img-leads" />`;
                                                          }
                                                      })()
                                                    : input.value
                                                    ? `<p>${input.value}</p>`
                                                    : ""
                                                : ""
                                        }

                                    </div>
                                </div>
                            `);
                        });
                    } else {
                        if (languageId === 2) {
                            loadJsonFile("No data found", function (langtst) {
                                $(".no_data_request").text(langtst);
                            });
                        }
                        $("#form-inputs").append(`
                            <div class="col-md-12">
                                <div class="tab-info text-center mt-3">
                                    <p class="no_data_request">No data found</p>
                                </div>
                            </div>
                        `);
                    }
                } else {
                    toastr.error("No lead data found.");
                }
            },
            error: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error(
                        "An error occurred while retrieving lead data."
                    );
                }
            },
        });
    }
}

if (pageValue === "user.provider" || pageValue === "home") {
    $.ajax({
        url: "/api/get-session-user-id",
        type: "GET",
        success: function (response) {
            if (response.user_id) {
                userId = response.user_id;
                localStorage.setItem("user_id", userId);
            }
        },
        error: function () {
            toastr.error("Unable to fetch session data. Please try again.");
        },
    });
    const user_id = localStorage.getItem("user_id");

    const categoryId = localStorage.getItem("selected_category_id");
    const leadsId = localStorage.getItem("selected_leads_id");

    function capitalizeFirstLetter(str) {
        if (!str) return "";
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    }

    if (pageValue === "user.provider") {
        $.ajax({
            url: "/api/getuserlist",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                type: "2",
                listtype: "provider",
                category_id: categoryId,
            },
            success: function (response) {
                const user_id = localStorage.getItem("user_id"); // Get the user_id from local storage
    
                if (response.data && Array.isArray(response.data)) {
                    $("#providers-container").empty();
                    if (response.data.length === 0) {
                        $("#providers-container").append(
                            `<p class="text-center">${$(
                                "#providers-container"
                            ).data("empty_info")}</p>`
                        );
                    } else {
                        response.data.forEach(function (provider) {
                            const category =
                                provider.category_name || "No Category";
                            const averageRating = provider.average_rating || "0.0";
                            const totalRatings = provider.total_ratings || "0";
                            if (languageId === 2) {
                                loadJsonFile("Send Request", function (langtst) {
                                    $(".provider_send_request").text(langtst);
                                });
                            }
                            const isCurrentUser = provider.provider_id == user_id;
                            const providerCard = `
                                <div class="col-xl-3 col-md-6 provider-card" data-provider-id="${
                                    provider.provider_id
                                }" ${isCurrentUser ? 'style="display: none;"' : ""}>
                                    <div class="card">
                                       <div class="card-body">
                                            <div class="card-img card-provider-img card-img-hover mb-3 position-relative">
                                                <div class="form-check d-flex justify-content-end image-check">
                                                    <input class="form-check-input provider-checkbox" type="checkbox" value="${
                                                        provider.provider_id
                                                    }" ${
                                isCurrentUser ? "disabled" : ""
                            }>
                                                </div>
                                                <a href="/${provider.user_slug ?? ''}" class="provider-details-link" data-provider-id="${
                                                    provider.provider_id || ""
                                                }">
                                                    <img src="${
                                                        provider.profile_image
                                                            ? "/storage/profile/" +
                                                              provider.profile_image
                                                            : "/assets/img/profile-default.png"
                                                    }" alt="Img">
                                                </a>
                                            </div>
                                            <div>
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div>
                                                        <h5 class="d-flex align-items-center mb-1">
                                                            <a href="/${provider.user_slug ?? ''}" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;" class="provider-details-link" data-provider-id="${
                                                                provider.provider_id ||
                                                                ""
                                                            }">${
                                provider.provider_name
                            }</a>
                                                            <span class="text-success ms-2"><i class="fa fa-check-circle"></i></span>
                                                        </h5>
                                                        <span>${category}</span>
                                                    </div>
                                                </div>
                                                <div class="rating d-flex align-items-center justify-content-between">
                                                    <div class="rating-stars d-flex align-items-center">
                                                        <i class="fas fa-star ${
                                                            averageRating >= 1
                                                                ? "filled"
                                                                : ""
                                                        }"></i>
                                                        <i class="fas fa-star ${
                                                            averageRating >= 2
                                                                ? "filled"
                                                                : ""
                                                        }"></i>
                                                        <i class="fas fa-star ${
                                                            averageRating >= 3
                                                                ? "filled"
                                                                : ""
                                                        }"></i>
                                                        <i class="fas fa-star ${
                                                            averageRating >= 4
                                                                ? "filled"
                                                                : ""
                                                        }"></i>
                                                        <i class="fas fa-star ${
                                                            averageRating >= 5
                                                                ? "filled"
                                                                : ""
                                                        }"></i>
                                                        <span class="d-inline-block">(${totalRatings})</span>
                                                    </div>
                                                    <div class="request_leads">
                                                        <a href="javascript:void(0)"
                                                        class="btn btn-sm ${
                                                            isCurrentUser
                                                                ? "btn-success"
                                                                : "btn-primary"
                                                        } text-white provider_send_request request-leads-btn"
                                                        data-provider-id="${
                                                            provider.provider_id
                                                        }"
                                                        ${
                                                            isCurrentUser
                                                                ? "disabled"
                                                                : ""
                                                        }>
                                                        ${
                                                            isCurrentUser
                                                                ? "Request Sended"
                                                                : "Send Request"
                                                        }
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
    
                            $("#providers-container").append(providerCard);
                        });
                    }
    
                    // Add event listeners
                    const sendRequestButton = $(".submit-selected");
    
                    // Function to update the state of the "Send Request" button
                    function updateSendRequestButtonState() {
                        const anySelected =
                            $(".provider-checkbox:checked").length > 0;
                        sendRequestButton.prop("disabled", !anySelected);
                    }
    
                    // Enable or disable the button when checkboxes are clicked
                    $(".provider-checkbox, .select-all-checkbox").on(
                        "change",
                        updateSendRequestButtonState
                    );
    
                    // Initially disable the "Send Request" button
                    updateSendRequestButtonState();
    
                    $(".request-leads-btn").on("click", function () {
                        const providerId = $(this).data("provider-id");
                        const button = $(this);
                        button
                            .prop("disabled", true)
                            .text($("#providers-container").data("sending_text"));
    
                        // Call the function to update leads
                        updateLeads([providerId], user_id, button);
                    });
    
                    $(".select-all-checkbox").on("change", function () {
                        const isChecked = $(this).is(":checked");
                        $(".provider-checkbox").prop("checked", isChecked);
                        updateSendRequestButtonState();
                    });
    
                    $(".submit-selected").on("click", function () {
                        const selectedProviderIds = $(".provider-checkbox:checked")
                            .map(function () {
                                return $(this).val();
                            })
                            .get();
    
                        $(this)
                            .prop("disabled", true)
                            .text($("#providers-container").data("sending_text"));
    
                        if (selectedProviderIds.length > 0) {
                            updateLeads(selectedProviderIds, user_id);
                        } else {
                            toastr.warning("No providers selected.");
                        }
                    });
                } else {
                    toastr.error("No providers found.");
                }
            },
            error: function (xhr) {
                toastr.error("Failed to update status. Please try again.");
            },
        });
    }

    function updateLeads(selectedProviderIds, user_id, button = "") {
        $.ajax({
            url: "/api/update/leads",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                provider_id: selectedProviderIds,
                user_id: user_id,
                user_form_inputs_id: leadsId,
            },
            success: function (response) {
                // Show success modal
                $("#success_modal").modal("show");

                if (button) {
                    button
                        .prop("disabled", true)
                        .text($("#providers-container").data("requested_text"))
                        .removeClass("btn-primary")
                        .addClass("btn-success disabled");
                }

                $(".submit-selected")
                    .prop("disabled", true)
                    .text($(".submit-selected").data("send_request_text"));

                selectedProviderIds.forEach(function (id) {
                    $(".request-leads-btn[data-provider-id='" + id + "']")
                        .prop("disabled", true)
                        .text($("#providers-container").data("requested_text"))
                        .removeClass("btn-primary")
                        .addClass("btn-success disabled");
                });

                setTimeout(() => {
                    $("#success_modal").modal("hide");
                    window.location.href = "/user/leads";
                }, 1500);

                const providerEmails = response.provider_emails;
                const emailData = {
                    subject: response.email_template.email_subject,
                    content: response.email_template.email_content,
                };
                const userName = response.user_name;

                providerEmails.forEach((email) => {});
            },
            error: function (xhr) {
                if (button) {
                    button
                        .prop("disabled", true)
                        .text($(".submit-selected").data("send_request_text"));
                }

                $(".submit-selected")
                    .prop("disabled", false)
                    .text($(".submit-selected").data("send_request_text"));

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    Object.keys(xhr.responseJSON.errors).forEach((key) => {
                        xhr.responseJSON.errors[key].forEach((errorMessage) => {
                            toastr.error(errorMessage);
                        });
                    });
                } else {
                    toastr.error(
                        "Failed to send leads request. Please try again."
                    );
                }
            },
        });
    }

    function sendEmail(email, emailData, userName) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: "/api/mail/sendmail",
                type: "POST",
                dataType: "json",
                data: {
                    to_email: email,
                    notification_type: 4,
                    type: 1,
                    user_name: userName,
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
}

if (pageValue === "user.providerlist") {
    $.ajax({
        url: "/api/get-session-user-id",
        type: "GET",
        success: function (response) {
            if (response.user_id) {
                var userId = response.user_id;
                localStorage.setItem("user_id", userId);
            }
        },
        error: function () {
            toastr.error("Unable to fetch session data. Please try again.");
        },
    });

    function registerNewUser() {
        $("#login-modal").modal("show");
    }

    $(document).ready(function () {
        var formData = {
            type: "2",
            listtype: "provider",
        };
        getProviderList(formData);
        $(".filter_category").prop("checked", true);
        $("#location").select2();
    });

    $("#all_categories").on("change", function () {
        const isChecked = $(this).prop("checked");
        $(".filter_category").prop("checked", isChecked);
    });

    $("#resetFilter").on("click", function () {
        $("#keywords").val("");
        $("#all_categories").prop("checked", false);
        $(".filter_category").prop("checked", false);
        $(".rating_filter").prop("checked", false);
        $("#location").val("").trigger("change");

        var formData = {
            type: "2",
            listtype: "provider",
        };
        getProviderList(formData);
    });

    function getProviderList(formData) {
        $.ajax({
            url: "/api/getuserlist",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: formData,
            success: function (response) {

                $("#searchProviderBtn").removeAttr("disabled").html("Search");
                $("#providers-container").empty();

                if (Array.isArray(response.data) && response.data.length > 0) {

                    let currencySymbol = response.data.currency_symbol || "$";
                    let currentHostname = window.location.origin;

                    // Sort promoted providers first
                    response.data.sort((a, b) => (b.promotedJobber || 0) - (a.promotedJobber || 0));

                    response.data.forEach(provider => {

                        const averageRating = parseFloat(provider.average_rating) || 0;
                        const isPromoted = provider.promotedJobber == 1;

                        const providerCard = `
                        <div class="col-xl-4 col-md-6 position-relative provider-card ${isPromoted ? 'promoted-provider' : ''}">
                            ${ isPromoted ?
                                `<div class="recommended-badge-provider">${$("#providers-container").data('recommended_text') || "Featured"}</div>` : ""
                            }
                            <div class="card position-relative overflow-hidden">

                                <div class="card-body">

                                ${ provider.featured == 1 ?
                                    `<div class="feature-text"><span class="bg-danger">${$("#providers-container").data('featured_text') || "Featured"}</span></div>` : ""
                                }

                                    ${isPromoted ? `
                                        <div class="feature-text mb-2">
                                            <span class="bg-warning text-dark d-none ">Promoted Provider</span>
                                        </div>` : ``}

                                    <div class="card-img card-provider-img card-img-hover mb-3">
                                        <a href="/${provider.user_slug ?? ''}"
                                        class="provider-details-link"
                                        data-provider-id="${provider.provider_id || ''}">
                                            <img src="${
                                                provider.profile_image
                                                ? currentHostname + "/storage/profile/" + provider.profile_image
                                                : "/assets/img/profile-default.png"
                                            }"
                                            class="img-fluid"
                                            alt="Provider Image">
                                        </a>
                                    </div>

                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div>
                                                <h5 class="mb-1">
                                                    <a href="/${provider.user_slug ?? ''}"
                                                    class="provider-details-link"
                                                    data-provider-id="${provider.provider_id || ''}">
                                                        ${provider.provider_name || "Unknown Provider"}
                                                    </a>

                                                    ${provider.badge == 1
                                                        ? `<span class="text-success ms-2">
                                                            <i class="fa fa-check-circle"></i>
                                                        </span>`
                                                        : ``}
                                                </h5>
                                                <span>${provider.category_name || "No Category"}</span>
                                            </div>

                                            ${provider.hourly_rate
                                                ? `<p class="fs-18 fw-medium text-dark">
                                                    ${currencySymbol}${provider.hourly_rate}
                                                    <span class="fw-normal fs-13 text-default">/hr</span>
                                                </p>`
                                                : ``}
                                        </div>

                                        <div class="rating d-flex align-items-center justify-content-between">
                                            <div class="rating-stars">
                                                ${getStarRatingHtml(averageRating)}
                                            </div>

                                            ${window.showChatButton
                                                ? `<a href="/user/chat/${provider.encrypted_id || ''}"
                                                    class="btn btn-primary chat_now_btn">
                                                    <i class="feather-message-circle me-1"></i> Chat Now
                                                </a>`
                                                : ``}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;

                        $("#providers-container").append(providerCard);
                    });

                } else {
                    $("#providers-container").html(`
                        <div class="text-center">
                            <h4>No providers available</h4>
                        </div>
                    `);
                }
            },
            error: function () {
                $("#searchProviderBtn").removeAttr("disabled").html("Search");
                toastr.error("Failed to fetch providers.");
            }
        });
    }

    function getStarRatingHtml(averageRating) {
        averageRating = parseFloat(averageRating) || 0;

        let html = "";

        for (let i = 1; i <= 5; i++) {
            if (averageRating >= i) {
                html += `<i class="fas fa-star filled"></i>`;
            } else if (averageRating >= i - 0.5) {
                html += `<i class="fas fa-star-half-alt filled"></i>`;
            } else {
                html += `<i class="far fa-star text-warning"></i>`;
            }
        }

        html += `<span class="ms-2 d-inline-block">(${averageRating.toFixed(1)})</span>`;
        return html;
    }

    $("#searchFilterForm").on("submit", function (e) {
        e.preventDefault();

        let selectedCategories = [];
        $(".category-checkbox:checked").each(function () {
            selectedCategories.push($(this).val());
        });

        var keywords = $("#keywords").val();

        let selectedRatings = [];
        $(".rating_filter:checked").each(function () {
            selectedRatings.push($(this).val());
        });

        var location = $("#location").val();

        var formData = {
            type: "2",
            listtype: "provider",
            category_id: selectedCategories,
            keywords: keywords,
            ratings: selectedRatings,
            location: location,
        };

        $("#searchProviderBtn")
            .attr("disabled", true)
            .html(
                '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span> Searching..'
            );

        getProviderList(formData);
        var currentUrl = window.location.href.split("#")[0];
        window.location.href = currentUrl + "#top";
    });
}

if (pageValue === "user.bookinglist") {
    $(document).ready(function () {
        applyBookingStatusStyles();
        function applyBookingStatusStyles() {
            $(".booking-status").each(function (index) {
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
                        statusText = "Inprogress";
                        statusClass = "badge badge-soft-info ms-2";
                        break;
                    case 3:
                        statusText = "Provider Cancelled";
                        statusClass = "badge badge-soft-danger ms-2";
                        break;
                    case 4:
                        statusText = "Refund Initiated";
                        statusClass = "badge badge-soft-warning ms-2";
                        break;
                    case 5:
                        statusText = "Completed";
                        statusClass = "badge badge-soft-success ms-2";
                        break;
                    case 6:
                        statusText = "Order Completed";
                        statusClass = "badge badge-soft-success ms-2";
                        break;
                    case 7:
                        statusText = "Refund Completed";
                        statusClass = "badge badge-soft-success ms-2";
                        break;
                    case 8:
                        statusText = "Cancelled";
                        statusClass = "badge badge-soft-danger ms-2";
                        break;
                    default:
                        statusText = "Unknown";
                        statusClass = "status-unknown";
                }

                const $this = $(this);

                $this.addClass(statusClass);
            });
        }
        $(document).on("click", ".viewpaymentproof", function (e) {
            e.preventDefault(); // Prevent default anchor behavior

            var paymentProof = $(this).data("proof"); // Get the payment proof file
            if (paymentProof) {
                const fileExtension = paymentProof
                    .split(".")
                    .pop()
                    .toLowerCase();
                const fileUrl = `${window.location.origin}/storage/${paymentProof}`; // Construct the file URL

                if (["jpg", "jpeg", "png", "gif"].includes(fileExtension)) {
                    // Open image in a new tab
                    window.open(fileUrl, "_blank");
                } else {
                    // Trigger download for other file types
                    const link = document.createElement("a");
                    link.href = fileUrl;
                    link.download = paymentProof.split("/").pop(); // Extract file name for download
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link); // Clean up
                }
            } else {
                if (languageId === 2) {
                    loadJsonFile(
                        "No payment proof available.",
                        function (langtst) {
                            msg = langtst;
                            toastr.success(msg);
                        }
                    );
                } else {
                    toastr.success("No payment proof available.");
                }
            }
        });

        $(document).on("click", ".cancel", function (e) {
            e.preventDefault();
            var bookingId = $(this).data("id");
            $(".bookingid").attr("data-id", bookingId);
        });

        $(document).on("click", ".bookingid", function (e) {
            e.preventDefault();
            var type = $(this).data("type");
            var id = $(this).data("id");
            $.ajax({
                url: "/api/updatebookingstatus",
                type: "POST",
                data: {
                    id: id,
                    status: type,
                },
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                beforeSend: function () {
                    $(".cancelbooking").attr("disabled", true);
                    $(".cancelbooking").html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
                },
                complete: function () {
                    $(".cancelbooking")
                        .attr("disabled", false)
                        .html($(".cancelbooking").data("yes_cancel"));
                },
                success: function (response) {
                    if (response.code === 200) {
                        if (response.data["status_code"] == "4") {
                            $(".refunddiv" + id).hide();
                            $(".bookingstatus" + id).text(
                                response.data["status"]
                            );
                            applyBookingStatusStyles();
                        }
                        var msg = response.message;
                        if (languageId === 2) {
                            loadJsonFile(response.message, function (langtst) {
                                msg = langtst;
                                toastr.success(msg);
                            });
                        } else {
                            toastr.success(msg);
                        }
                        $("#cancel_appointment").modal("hide");
                        setTimeout(function () {
                            window.location.reload();
                        }, 500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                    toastr.error(
                        "An error occurred while trying to cancel the booking."
                    );
                },
            });
        });
        $(document).on("click", ".refund", function (e) {
            var bookingId = $(this).data("id");
            $(".refundprocess").data("id", bookingId);
        });
        $(document).on("click", ".refundprocess", function (e) {
            e.preventDefault();
            var type = $(this).data("type");
            var id = $(this).data("id");
            $.ajax({
                url: "/api/updatebookingstatus",
                type: "POST",
                data: {
                    id: id,
                    status: type,
                },
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                beforeSend: function () {
                    $(".refundprocess").attr("disabled", true);
                    $(".refundprocess").html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
                },
                complete: function () {
                    $(".refundprocess")
                        .attr("disabled", false)
                        .html($(".refundprocess").data("yes"));
                },
                success: function (response) {
                    if (response.code === 200) {
                        if (response.data["status_code"] == "4") {
                            $(".refunddiv" + id).hide();
                            $(".bookingstatus" + id).text(
                                response.data["status"]
                            );
                            applyBookingStatusStyles();
                        }
                        var msg = response.message;
                        if (languageId === 2) {
                            loadJsonFile(response.message, function (langtst) {
                                msg = langtst;
                                toastr.success(msg);
                            });
                        } else {
                            toastr.success(msg);
                        }

                        $("#refund").modal("hide");
                        setTimeout(function () {
                            window.location.reload();
                        }, 500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                    toastr.error("An error occurred while trying to refund.");
                },
            });
        });
        $(document).on("click", ".ordercomplete", function (e) {
            var bookingId = $(this).data("id");
            $(".ordercompleteprocess").data("id", bookingId);
        });
        $(document).on("click", ".ordercompleteprocess", function (e) {
            e.preventDefault();
            var type = $(this).data("type");
            var id = $(this).data("id");
            $.ajax({
                url: "/api/updatebookingstatus",
                type: "POST",
                data: {
                    id: id,
                    status: type,
                },
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                beforeSend: function () {
                    $(".ordercompleteprocess").attr("disabled", true);
                    $(".ordercompleteprocess").html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
                },
                complete: function () {
                    $(".ordercompleteprocess")
                        .attr("disabled", false)
                        .html($(".ordercompleteprocess").data("yes"));
                },
                success: function (response) {
                    if (response.code === 200) {
                        if (response.data["status_code"] == "6") {
                            $(".orderdiv" + id).hide();
                            $(".bookingstatus" + id).text(
                                response.data["status"]
                            );
                            applyBookingStatusStyles();
                        }
                        var msg = response.message;
                        if (languageId === 2) {
                            loadJsonFile(response.message, function (langtst) {
                                msg = langtst;
                                toastr.success(msg);
                            });
                        } else {
                            toastr.success(msg);
                        }
                        $("#orderclose").modal("hide");
                        setTimeout(function () {
                            window.location.reload();
                        }, 500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                    toastr.error(
                        "An error occurred while trying to close the booking."
                    );
                },
            });
        });
        var langCode = $("body").data("lang");

        let currentLang = langCode;

        $("#raiseDispute").validate({
            rules: {
                subject: {
                    required: true,
                },
                content: {
                    required: true,
                },
            },
            messages: {
                subject: {
                    required: $("#subject_error").data("required"),
                },
                content: {
                    required: $("#subject_error").data("required"),
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
                var formData = new FormData(form);

                $.ajax({
                    url: "/user/booking/dispute",
                    method: "POST",
                    data: formData,
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        $(".btn_dispute").attr("disabled", true);
                        $(".btn_dispute").html(
                            '<div class="spinner-border text-light" role="status"></div>'
                        );
                    },
                })
                    .done((response, statusText, xhr) => {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $(".btn_dispute").removeAttr("disabled");
                        $(".btn_dispute").html("Save");
                        if (response.code === 200) {
                            $("#reschedule").modal("hide");
                            $("#dispute_modal").modal("show");
                        }
                    })
                    .fail((error) => {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $(".btn_dispute").removeAttr("disabled");
                        $(".btn_dispute").html("Save");

                        if (error.status == 422) {
                            $.each(
                                error.responseJSON.errors,
                                function (key, val) {
                                    $("#" + key).addClass("is-invalid");
                                    $("#" + key + "_error").text(val[0]);
                                }
                            );
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

        $(document).ready(function () {
            $("#reschedule").on("hidden.bs.modal", function () {
                $("#raiseDispute")[0].reset();

                $(".form-control")
                    .removeClass("is-invalid")
                    .removeClass("is-valid");
                $(".invalid-feedback").text("");
            });
        });

        $(document).ready(function () {
            $(".raise-dispute-btn").on("click", function (e) {
                e.preventDefault(); // Prevent the default action

                var bookingId = $(this).data("booking-id"); // Retrieve booking ID

                // Step 1: Initial AJAX call to get dispute information
                $.ajax({
                    url: "/get-dispute-info", // Backend route for initial data
                    method: "POST", // POST request
                    data: {
                        booking_id: bookingId, // Send booking ID
                    },
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ), // CSRF token
                    },
                    success: function (response) {
                        if (response.exists) {
                            // Populate form fields with initial data
                            $("#booking_id").val(response.booking_id);
                            $("#product_id").val(response.product_id);
                            $("#provider_id").val(response.provider_id);

                            // Step 2: Fetch additional dispute details using Fetch API
                            const disputeDetailsPayload = {
                                booking_id: response.booking_id,
                                product_id: response.product_id,
                                provider_id: response.provider_id,
                            };

                            // Show loading message
                            document.getElementById(
                                "loadingMessage"
                            ).style.display = "block";

                            fetch("/dispute/details", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": document.querySelector(
                                        'meta[name="csrf-token"]'
                                    ).content,
                                },
                                body: JSON.stringify(disputeDetailsPayload),
                            })
                                .then((response) => response.json())
                                .then((data) => {
                                    // Hide loading message

                                    document.getElementById(
                                        "loadingMessage"
                                    ).style.display = "none";
                                    if (data.exists) {
                                        // Show admin reply section
                                        document.getElementById(
                                            "raiseDispute"
                                        ).style.display = "none";
                                        document.getElementById(
                                            "adminReplySection"
                                        ).style.display = "block";

                                        // Populate admin reply section
                                        document.getElementById(
                                            "admin_subject"
                                        ).value = data.subject || "";
                                        document.getElementById(
                                            "admin_content"
                                        ).value = data.content || "";
                                        document.getElementById(
                                            "admin_reply"
                                        ).value =
                                            data.admin_reply ||
                                            "Not replied yet";
                                    } else {
                                        // Show dispute raise form if no reply exists
                                        document.getElementById(
                                            "raiseDispute"
                                        ).style.display = "block";
                                        document.getElementById(
                                            "adminReplySection"
                                        ).style.display = "none";
                                    }
                                })
                                .catch((err) => {
                                    // Handle errors in Fetch API
                                    console.error(
                                        "Error fetching dispute details:",
                                        err
                                    );
                                    document.getElementById(
                                        "loadingMessage"
                                    ).style.display = "none";
                                });
                        } else {
                            alert("Booking or Product not found.");
                        }
                    },
                    error: function (xhr, status, error) {
                        // Handle AJAX request errors
                        alert("An error occurred. Please try again.");
                    },
                });
            });
        });
    });
    $(document).ready(function () {
        $('.booking_details_btn').on("click", function () {
            var bookingId = $(this).data("booking-details-id");

            $(
                "#service_name, #service_code, #service_amount, #total_amount, #booking_status, #payment_type, #payment_status, #booking_date, #slot_date, #slot_day, #slot_time, #branch_name, #branch_email, #branch_mobile, #branch_address, #buyer_name, #buyer_email, #buyer_phone, #buyer_city, #provider_name, #provider_email, #provider_mobile, #staff_name, #staff_email, #staff_mobile"
            ).text("");

            $(".staff_info").hide();
            $(".slot_info").hide();
            $(".branch_info").hide();
            $(".label-loader, .input-loader").show();
            $(".real-label, .real-input").addClass("d-none");
            // AJAX request
            $.ajax({
                url: "/get/booking/details",
                type: "POST",
                data: { id: bookingId },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function (response) {
                    $(".label-loader, .input-loader").hide();
                    $(".real-label, .real-input").removeClass("d-none");

                    let orderId = response.booking.order_id;
                    if (orderId) {
                        $('#order_id').text(`#${orderId}`);
                    }

                    let bookingData = response.booking;
                    let additionalServices = response.additional_services;
                    let currency = response.currency;
                    if (Array.isArray(additionalServices) && additionalServices.length > 0) {
                        let list = '<div class="d-flex flex-column gap-2">';
                        additionalServices.forEach(service => {
                            list += `
                            <label class="d-flex gap-2">
                                <strong class="w-25">${service.name}</strong>:
                                <span class="text-dark flex-grow-1">${currency}${service.price}</span>
                            </label>`;
                        });
                        list += "</div>";
                        $(".additional_service").removeClass('d-none');
                        $("#additional_service_list").html(list);
                    } else {
                        $(".additional_service").addClass('d-none');
                    }

                    $("#service_name").text(response.service.source_name);
                    $("#service_code").text(response.service.source_code);
                    $("#service_amount").text(
                        response.currency + response.booking.service_amount
                    );
                    $("#total_amount").text(
                        response.currency + response.booking.total_amount
                    );
                    $("#booking_status").text(response.status.booking_status);
                    $("#payment_type").text(response.status.payment_type);
                    $("#payment_status").text(response.status.payment_status);
                    $("#booking_date").text(response.formatted_booking_date);

                    if (
                        response.slot &&
                        (response.slot.source_values ||
                            response.slot.formatted_source_key)
                    ) {
                        $("#slot_date").text(response.formatted_booking_date);
                        $("#slot_day").text(response.slot.formatted_source_key);
                        $("#slot_time").text(response.slot.source_values);
                        $(".slot_info").show();
                    } else {
                        $(".slot_info").hide();
                    }

                    if (
                        response.branch &&
                        (response.branch.branch_name ||
                            response.branch.branch_email)
                    ) {
                        $("#branch_name").text(response.branch.branch_name);
                        $("#branch_email").text(response.branch.branch_email);
                        $("#branch_mobile").text(response.branch.branch_mobile);
                        $("#branch_address").text(
                            response.branch.branch_address
                        );
                        $(".branch_info").show();
                    } else {
                        $(".branch_info").hide();
                    }

                    $("#buyer_name").text(
                        response.booking.first_name +
                            " " +
                            response.booking.last_name
                    );
                    $("#buyer_email").text(response.booking.user_email);
                    $("#buyer_phone").text(response.booking.user_phone);
                    $("#buyer_city").text(response.booking.user_city);

                    $("#provider_name").text(
                        response.provider_details.first_name +
                            " " +
                            response.provider_details.last_name
                    );
                    $("#provider_email").text(response.provider.email);
                    $("#provider_mobile").text(response.provider.phone_number);

                    if (
                        response.staff_details &&
                        (response.staff_details.first_name ||
                            response.staff_details.last_name ||
                            response.staff.email)
                    ) {
                        $("#staff_name").text(
                            response.staff_details.first_name +
                                " " +
                                response.staff_details.last_name
                        );
                        $("#staff_email").text(response.staff.email);
                        $("#staff_mobile").text(response.staff.phone_number);
                        $(".staff_info").show();
                    } else {
                        $(".staff_info").hide();
                    }

                    if (bookingData.review) {
                        let rating = bookingData.review ? bookingData.review.rating : 0;
                        let starsHtml = "";

                        for (let i = 1; i <= 5; i++) {
                            if (i <= rating) {
                                starsHtml += `<i class="fas fa-star text-warning"></i>`;
                            } else {
                                starsHtml += `<i class="far fa-star"></i>`;
                            }
                        }

                        $("#review_rating").html(starsHtml);

                        $("#review_text").text(bookingData.review.review);
                        $("#review_date").text(bookingData.review.review_date);
                        if (bookingData.review.reply) {
                            $(".provider_reply_section").removeClass('d-none');
                            $("#reply_review").text(bookingData.review.reply.review);
                        } else {
                            $(".provider_reply_section").addClass('d-none');
                        }
                        $(".review-section").removeClass("d-none");
                    } else {
                        $(".review-section").addClass("d-none");
                    }

                    if (response.booking.notes) {
                        $("#notes").text(response.booking.notes);
                        $(".notes_info").removeClass("d-none");
                    } else {
                        $("#notes").text("");
                        $(".notes_info").addClass("d-none");
                    }
                },
                error: function (xhr) {
                    $(".label-loader, .input-loader").hide();
                    $(".real-label, .real-input").removeClass("d-none");
                    console.log("Error:", xhr);
                },
            });
        });
    });

    $(".add_review_btn").on("click", function (e) {
       $("#review_booking_id").val($(this).data("booking_id"));
       $("#review_product_id").val($(this).data("product_id"));
    });

    $("#addCommentsForm").submit(function (event) {
        event.preventDefault();

        var formData = {
            rating: $(".rating-select i.active").length,
            review: $("#review").val(),
            product_id: $("#review_product_id").val(),
            parent_id: 0,
            booking_id: $("#review_booking_id").val(),
            user_id: $("#user_id").val(),
        };

        addReview(formData);
    });

    function addReview(formData) {
        $.ajax({
            url: "/api/add-comments",
            type: "POST",
            data: formData,
            dataType: "json",
            cache: false,
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $("#save_comment_btn")
                    .attr("disabled", true)
                    .html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
            },
            success: function (response) {
                $(".error-text").text("");
                $("#save_comment_btn").removeAttr("disabled").html("Submit");
                $(".form-control").removeClass("is-invalid is-valid");

                if (response.code === 200) {
                    $("#add-review").modal("hide");
                    toastr.success(response.message);
                    $('#add_review_btn').hide();
                    setTimeout(function () {
                        location.reload();
                    }, 20);
                }
            },
            error: function (error) {
                $(".error-text").text("");
                $("#save_comment_btn").removeAttr("disabled").html("Submit");
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
}

if (pageValue === "user.dashboard") {
    $.ajax({
        url: "/api/get-session-user-id",
        type: "GET",
        success: function (response) {
            if (response.user_id) {
                userId = response.user_id;
                localStorage.setItem("user_id", userId);
                callUserDashboardApi(userId);
            }
        },
        error: function () {
            toastr.error("Unable to fetch session data. Please try again.");
        },
    });
    const user_id = localStorage.getItem("user_id");

    function callUserDashboardApi(userId) {
        function capitalizeFirstLetter(str) {
            if (!str) return "";
            return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
        }
        $.ajax({
            url: "/api/userdashboard",
            type: "POST",
            data: {
                user_id: userId,
            },
            success: function (response) {
                if (
                    response.success &&
                    response.data.total_bookings !== undefined
                ) {
                    $(".totalOrder").text(response.data.total_bookings);
                    $(".totalSpend").text(response.data.total_service_amount);
                    $(".symbol").text(response.data.currencySymbol);

                    var bookings = response.data.bookings;

                    var bookingTableBody = $(".recent_booking");
                    bookingTableBody.empty();

                    var transactionTableBody = $(".recentTranction");
                    transactionTableBody.empty();

                    if (bookings.length === 0) {
                        if (languageId === 2) {
                            loadJsonFile("No Data Found", function (langtst) {
                                $(".no_data").text(langtst);
                            });
                        }
                        bookingTableBody.append(`
                        <tr>
                            <td colspan="2" class="text-center no_data text-gray">No Data Found</td>
                        </tr>
                    `);
                        transactionTableBody.append(`
                        <tr>
                            <td colspan="2" class="text-center no_data text-gray">No Data Found</td>
                        </tr>
                    `);
                    } else {
                        bookings.forEach(function (booking) {
                            function capitalizeFirstLetter(str) {
                                if (!str) return "";
                                return (
                                    str.charAt(0).toUpperCase() +
                                    str.slice(1).toLowerCase()
                                );
                            }

                            function truncateText(text, maxLength) {
                                if (!text) return "";
                                return text.length > maxLength
                                    ? text.substring(0, maxLength) + "..."
                                    : text;
                            }

                            var formattedDate = new Date(
                                booking.created_at
                            ).toLocaleDateString("en-US", {
                                year: "numeric",
                                month: "short",
                                day: "numeric",
                            });

                            const defaultImage =
                                "/assets/img/profile-default.png";
                            const defaultImage1 =
                                "/front/img/default-placeholder-image.png";

                            let categoryImage =
                                booking.category_image &&
                                booking.category_image !== "N/A"
                                    ? `/storage/${booking.category_image}`
                                    : defaultImage;

                            let providerImage =
                                booking.creator_profile_image &&
                                booking.creator_profile_image !== "N/A"
                                    ? `/storage/profile/${booking.creator_profile_image}`
                                    : defaultImage;

                            let serviceImage =
                                booking.product_image &&
                                booking.product_image !== "N/A"
                                    ? `/storage/${booking.product_image}`
                                    : defaultImage1;

                            var bookingRowHtml = `
                            <tr>
                                <td>
                                    <div class="d-flex">
                                        <span class="avatar avatar-lg me-2">
                                            <img src="${serviceImage}" class="img-fluid" alt="img">
                                        </span>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="fs-14">${truncateText(
                                                    capitalizeFirstLetter(
                                                        booking.product_name
                                                    ),
                                                    15
                                                )}</h6>
                                                <span class="text-gray fs-12">
                                                    <i class="feather-calendar me-1"></i>
                                                    ${formattedDate}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <span class="avatar avatar-lg me-2">
                                            <img src="${providerImage}" class="rounded-circle img-fluid" alt="Img">
                                        </span>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="fs-14">${truncateText(
                                                    capitalizeFirstLetter(
                                                        booking.creator_name
                                                    ),
                                                    20
                                                )}</h6>
                                                <span class="text-gray fs-14">${truncateText(
                                                    booking.creator_email,
                                                    50
                                                )}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        `;
                            bookingTableBody.append(bookingRowHtml);

                            var transactionRowHtml = `
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="dash-icon-1 bg-gray d-flex justify-content-center align-items-center rounded-circle avatar avatar-lg me-2">
                                           <img src="${serviceImage}" class="rounded-circle img-fluid" alt="Img">
                                        </span>
                                        <div>
                                            <h6 class="fs-14">${truncateText(
                                                capitalizeFirstLetter(
                                                    booking.product_name
                                                ),
                                                15
                                            )}</h6>
                                            <span class="text-gray fs-12">
                                                <i class="feather-calendar"></i>
                                                ${formattedDate}
                                                <span class="ms-2">
                                                    <i class="feather-clock"></i>
                                                    ${new Date(
                                                        booking.created_at
                                                    ).toLocaleTimeString(
                                                        "en-US",
                                                        {
                                                            hour: "2-digit",
                                                            minute: "2-digit",
                                                        }
                                                    )}
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <h6>${response.data.currencySymbol}${
                                booking.total_amount
                            }</h6>
                                </td>
                            </tr>
                        `;
                            transactionTableBody.append(transactionRowHtml);
                        });

                        $(document).ready(function () {
                            if (languageId === 2) {
                                loadJsonFile("View_All", function (langtst) {
                                    $(".view-transaction-btn").text(langtst);
                                });
                            }
                        });

                        bookingTableBody.append(`
                        <tr>
                            <td colspan="2" class="text-center">
                                <a href="/user/bookinglist" id="view-transaction-btn" class="btn border d-block view-transaction-btn">View All</a>
                            </td>
                        </tr>
                    `);

                        transactionTableBody.append(`
                        <tr>
                            <td colspan="2" class="text-center">
                                <a href="/user/transaction" id="view-transaction-btn" class="btn border d-block view-transaction-btn">View All</a>
                            </td>
                        </tr>
                    `);
                    }
                }
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
            },
            error: function (error) {
                // $("#pageLoader").hide();
                toastr.error("Error fetching dashboard data:", error);
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
            },
        });
    }
}

function notificationList(auth_user_id) {
    $.ajax({
        url: "/api/notification/notificationlist",
        type: "POST",
        data: { type: "user", authid: auth_user_id },
        headers: {
            Authorization: "Bearer " + localStorage.getItem("admin_token"),
            Accept: "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.code == "200") {
                var data = response.data["notifications"];
                var authuser = response.data["auth_user"];
                var count = response.data["count"];
                const belldiv = $("#notification-data");

                let bell_count_div = $(".bellcount");
                if (count > 0) {
                    const html = `<span class="notification-dot position-absolute start-80 translate-middle p-1 bg-danger border border-light rounded-circle">
                    </span>`;
                    bell_count_div.html(html);
                } else {
                    bell_count_div.empty();
                }

                belldiv.empty();
                if (data != "") {
                    data.forEach((val) => {
                        let profileImage = "/assets/img/profile-default.png";
                        if (authuser == val.from_user_id || authuser == val.to_user_id) {
                            profileImage = val.from_profileimg;
                        } else {
                            profileImage = val.to_profileimg;
                        }
                        var bellhtml = `<div class="border-bottom mb-3 pb-3">
                                    <div>
                                        <div class="d-flex">
                                            <span class="avatar avatar-lg me-2 flex-shrink-0">
                                                        <img src="${profileImage}" alt="Profile" class="rounded-circle">
                                                    </span>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center">
                                                <p class="mb-1 w-100">`;
                        if (authuser == val.from_user_id) {
                            bellhtml += `${val.from_description}</p>`;
                        } else {
                            bellhtml += `${val.to_description} </p>`;
                        }
                        bellhtml += `   </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                        belldiv.append(bellhtml);
                    });
                } else {
                    const belldiv = $("#notification-data");
                    belldiv.empty();
                    let msg = $("#notification-data").data("empty_info");
                    $(".markallread").hide();
                    bellhtml = `<div class="text-center">` + msg + `</div><br>`;
                    $("#notification-data").html(bellhtml);
                }
            }
        },
    });
}

function markAllRead(auth_user_id) {
    $.ajax({
        url: "/api/notification/updatereadstatus",
        type: "POST",
        data: { type: "user", authid: auth_user_id },
        headers: {
            Authorization: "Bearer " + localStorage.getItem("admin_token"),
            Accept: "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.code == "200") {
                notificationList();
            }
        },
        error: function (xhr, status, error) {
            toastr.error("An error occurred while update data.");
        },
    });
}

$(".cancelnotify").on("click", function (e) {
    e.preventDefault(); // Prevent default link behavior
    $(".notification-dropdown").removeClass("show"); // Hide the dropdown
});

if (pageValue === "productdetail") {
    $(document).on("click", "#add_review_btn", function () {
        $(".rating-select").find(".active").removeClass("active");
        $(".form-control").removeClass("is-valid is-invalid");
        $("#review_error").text("");
        $("#review").val("");
    });

    $("#addCommentsForm").submit(function (event) {
        event.preventDefault();

        var formData = {
            rating: $(".rating-select i.active").length,
            review: $("#review").val(),
            product_id: $(".product_id").val(),
            parent_id: 0,
            user_id: $("#user_id").val(),
        };

        addReview(formData);
    });

    function addReview(formData) {
        $.ajax({
            url: "/api/add-comments",
            type: "POST",
            data: formData,
            dataType: "json",
            cache: false,
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $("#save_comment_btn")
                    .attr("disabled", true)
                    .html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
            },
            success: function (response) {
                $(".error-text").text("");
                $("#save_comment_btn").removeAttr("disabled").html("Submit");
                $(".form-control").removeClass("is-invalid is-valid");
                $(".review_item").find(".reply_text").remove();

                if (response.code === 200) {
                    $("#add-review").modal("hide");
                    toastr.success(response.message);
                    $(".list-reviews").empty();
                    $('#add_review_btn').hide();
                    listReviews();
                }
            },
            error: function (error) {
                $(".error-text").text("");
                $("#save_comment_btn").removeAttr("disabled").html("Submit");
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

    let skip_review = 0;
    let per_page = 7;

    $(document).ready(function () {
        listReviews((skip_review = 0), false);
    });

    function listReviews(skip_review = 0, isLoadMore = false) {
        $.ajax({
            url: "/api/list-comments",
            type: "POST",
            data: {
                product_id: $(".product_id").val(),
                per_page: per_page,
                skip: skip_review,
            },
            dataType: "json",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    const reviews = response.data.ratings;
                    const ratingCounts = response.data.rating_counts;

                    $("#avg_rating").text(ratingCounts.avg_rating);
                    $(".total_review_count").text(ratingCounts.total_count);
                    $("#total_review_count").text(
                        `Reviews (${ratingCounts.total_count})`
                    );
                    renderProgressBars(ratingCounts);

                    if (reviews.length > 0) {
                        if (!isLoadMore) {
                            $(".list-reviews").empty();
                        }
                        renderReviews(reviews);
                    } else {
                        $("#load_more_reviews").addClass("d-none");
                    }

                    if (reviews.length >= per_page) {
                        $("#load_more_reviews").removeClass("d-none");
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

    function renderProgressBars(ratings) {
        const container = $("#review_progress_container");
        container.empty();

        const stars = [5, 4, 3, 2, 1];
        stars.forEach((star) => {
            const starCount = ratings.star_count[`star${star}`] || 0;
            const percentage = (
                (starCount / ratings.total_count) *
                100
            ).toFixed(2);

            const progressBarHTML = `
                <div class="d-flex align-items-center mb-2">
                    <p class="me-2 text-nowrap mb-0">${star} Star Ratings</p>
                    <div class="progress w-100" role="progressbar" aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar bg-warning" style="width: ${percentage}%"></div>
                    </div>
                    <p class="progress-count ms-2">${starCount}</p>
                </div>
            `;
            container.append(progressBarHTML);
        });
    }

    function renderReviews(reviews) {
        reviews.forEach((review) => {
            var ratingBadgeCls = "bg-success";
            if (review.rating == 1) {
                ratingBadgeCls = "bg-danger";
            }
            let isAllow = $('#review_container').data('is_allow_reply');

            $(".list-reviews").append(`
                <div class="card review-item mb-3">
                    <div class="card-body p-3 review_item" id="reply_${review.id}">
                        <div class="review-info">
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="avatar avatar-lg me-2 flex-shrink-0">
                                        <img src="${review.profile_image}" class="rounded-circle" alt="img">
                                    </span>
                                    <div>
                                        <h6 class="fs-16 fw-medium">${review.name}</h6>
                                        <div class="d-flex align-items-center flex-wrap date-info">
                                            <p class="fs-14 mb-0">${review.review_date}</p>
                                        </div>
                                    </div>
                                </div>
                                <span class="badge ${ratingBadgeCls} d-inline-flex align-items-center mb-2">
                                    <i class="ti ti-star-filled me-1"></i>${review.rating}
                                </span>
                            </div>
                            <p class="mb-2">${review.review}</p>
                            ${ isAllow == 1 ?
                            `<div class="d-flex align-items-center justify-content-between flex-wrap like-info">
                                <div class="d-inline-flex align-items-center">
                                    <a href="javascript:void(0);" class="d-inline-flex align-items-center me-2 reply_review" data-id="${review.id}"><i class="fa fa-reply me-1"></i>Reply</a>
                                </div>
                            </div>` : ''}
                        </div>
                    </div>
                </div>

            `);

            if (review.replies.length > 0) {
                var replies = review.replies;
                renderReplies(review.id, replies);
            }
        });
    }

    function renderReplies(parentId, replyData) {
        replyData.forEach((reply) => {
            $("#reply_" + `${parentId}`).append(`
            <div class="review-info reply mt-2 mb-2 p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center mb-2">
                        <span class="avatar avatar-lg me-2 flex-shrink-0">
                            <img src="${reply.profile_image}" class="rounded-circle" alt="img">
                        </span>
                        <div>
                            <h6 class="fs-16 fw-medium">${reply.name}</h6>
                            <div class="d-flex align-items-center flex-wrap date-info">
                                <p class="fs-14 mb-0">${reply.review_date}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="mb-2">${reply.review}</p>
            </div>
        `);
        });
    }

    $(document).on("click", "#load_more_reviews", function () {
        skip_review = skip_review + per_page;
        listReviews(skip_review, true);
    });

    var replyId = 0;
    $(document).on("click", ".reply_review", function () {
        var id = $(this).data("id");

        if ($("#user_id").val() == "") {
            $("#login-modal").modal("show");
        } else if (!$('#review_container').data('is_allow_reply')) {
            $("#reply-not-allowed-modal").modal("show");
        } else if (replyId != id) {
            $(".review_item").find(".reply_text").remove();
            $("#reply_" + `${id}`).append(`
                <div class="review-info reply reply_text">
                    <div class="row">
                        <div class="col-md-10">
                            <textarea class="form-control" name="reply_review" id="reply_review" rows="1" placeholder="Enter reply message"></textarea>
                            <span class="error-text text-danger" id="review_error"></span>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary send_reply" data-parent_id="${id}">Send</button>
                        </div>
                    </div>
                </div>
            `);
            replyId = id;
        } else {
            $("#reply_" + `${id}`)
                .find(".reply_text")
                .remove();
            replyId = 0;
        }
    });

    $(document).on("click", ".send_reply", function () {
        var formData = {
            review: $("#reply_review").val(),
            product_id: $(".product_id").val(),
            parent_id: $(this).data("parent_id"),
            user_id: $("#user_id").val(),
        };

        addReview(formData);
    });

    document.addEventListener('DOMContentLoaded', function() {
    function hideReplyForReviewItems() {
        document.querySelectorAll('.review-item').forEach(function(item) {
            const hasReviewInfo = item.querySelector('.review-info.reply');
            const reply = item.querySelector('.reply_review');
            if (hasReviewInfo && reply && reply.parentElement) {
                reply.parentElement.style.setProperty('display', 'none', 'important');
            }
        });
    }

    // Initial check
    hideReplyForReviewItems();

    // Observe dynamically added reviews
    const observer = new MutationObserver(function(mutations) {
        for (let mutation of mutations) {
            mutation.addedNodes.forEach(node => {
                if (node.nodeType === 1) {
                    if (node.matches('.review-item')) {
                        const hasReviewInfo = node.querySelector('.review-info.reply');
                        const reply = node.querySelector('.reply_review');
                        if (hasReviewInfo && reply && reply.parentElement) {
                            reply.parentElement.style.setProperty('display', 'none', 'important');
                        }
                    }
                    // Also check nested review-items
                    node.querySelectorAll?.('.review-item').forEach(innerItem => {
                        const hasReviewInfo = innerItem.querySelector('.review-info.reply');
                        const reply = innerItem.querySelector('.reply_review');
                        if (hasReviewInfo && reply && reply.parentElement) {
                            reply.parentElement.style.setProperty('display', 'none', 'important');
                        }
                    });
                }
            });
        }
    });

    observer.observe(document.body, { childList: true, subtree: true });
});
}

if (pageValue === "user.transaction") {
    $(document).ready(function () {
        bookingTransactionList();
    });

    $("#uploadPaymentProof").on("click", function () {
        let file = $("#codFile")[0].files[0];
        if (!file) {
            toastr.error("Please upload a file.");
            return;
        }

        if (!currentBookingId) {
            toastr.error(
                "Booking ID is missing. Please refresh and try again."
            );
            return;
        }

        let formData = new FormData();
        formData.append("payment_proof", file);
        formData.append("booking_id", currentBookingId);

        $.ajax({
            url: "/api/upload-payment-proof",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            processData: false,
            contentType: false,
            data: formData,
            success: function (response) {
                if (response.success) {
                    toastr.success(
                        "Payment proof uploaded successfully. Status updated to Paid."
                    );
                    $("#veiw_transaction").modal("hide");
                } else {
                    toastr.error("Failed to upload payment proof.");
                }
            },
            error: function () {
                toastr.error("An error occurred. Please try again.");
            },
        });
    });

    document.addEventListener("DOMContentLoaded", () => {
        const codFileInput = document.getElementById("codFile");
        const filePreview = document.getElementById("filePreview");
        const uploadButton = document.getElementById("uploadPaymentProof");

        codFileInput.addEventListener("change", (event) => {
            const file = event.target.files[0];
            const allowedTypes = [
                "image/jpeg",
                "image/png",
                "image/gif",
                "application/pdf",
            ];
            const maxSize = 2 * 1024 * 1024; // 2MB

            filePreview.innerHTML = "";
            uploadButton.disabled = true;

            if (!file) {
                return;
            }

            if (!allowedTypes.includes(file.type)) {
                toastr.error(
                    'Invalid file type. Only images and PDFs are allowed.',
                    'Error'
                );
                codFileInput.value = ""; // Clear the input
                return;
            }

            if (file.size > maxSize) {
                toastr.error(
                    "File size exceeds the maximum limit of 2MB.",
                    "Error"
                );
                codFileInput.value = ""; // Clear the input
                return;
            }

            if (file.type.startsWith("image/")) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement("img");
                    img.src = e.target.result;
                    img.alt = "Payment Proof Preview";
                    img.style.maxWidth = "100%";
                    img.style.maxHeight = "200px";
                    filePreview.appendChild(img);
                };
                reader.readAsDataURL(file);
            } else if (file.type === "application/pdf") {
                const pdfPreview = document.createElement("p");
                pdfPreview.textContent =
                    "PDF file selected: " + file.name;
                filePreview.appendChild(pdfPreview);
            }

            uploadButton.disabled = false;
        });
    });

    function truncateText(text, maxLength = 10) {
        if (!text) return "";
        return text.length > maxLength
            ? text.substring(0, maxLength) + "..."
            : text;
    }

    function bookingTransactionList() {
        const user_tranaction_id = $("#user_id").val();

        $.ajax({
            url: "/api/transactionlist",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                order_by: "desc",
                sort_by: "booking_date",
                customer_id: user_tranaction_id,
            },
            success: function (response) {
                $("#loader-table").hide();
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
                if (
                    response.success &&
                    response.data &&
                    response.data.transactions
                ) {
                    let transactions = response.data.transactions;
                    let tableBody = "";

                    if (transactions.length === 0) {
                        $("#transactionList").DataTable().destroy();
                        tableBody += `
                            <tr>
                                <td colspan="9" class="text-center">${$(
                                    "#transactionList"
                                ).data("empty")}</td>
                            </tr>`;
                    } else {
                        transactions.forEach((transaction, index) => {
                            let formattedDate = transaction.date;

                            let statusClass = "";
                            switch (transaction.payment.status) {
                                case "Unpaid":
                                    statusClass = "text-warning";
                                    break;
                                case "Paid":
                                    statusClass = "text-success";
                                    break;
                                case "Refund":
                                    statusClass = "text-danger";
                                    break;
                                case "Inprogress":
                                    statusClass = "text-primary";
                                    break;
                                case "Completed":
                                    statusClass = "text-success";
                                    break;
                                default:
                                    statusClass = "text-secondary";
                                    break;
                            }

                            let providerImage = transaction.provider.image_url;
                            let serviceImage = transaction.service.service_image_url;

                            let currency = transaction.currencySymbol;
                            let paymentType =
                                transaction.payment?.type || "N/A";
                            let paymentStatus =
                                transaction.payment?.status || "N/A";

                            tableBody += `
                                <tr>
                                    <td>${transaction.order_id}</td>
                                    <td>
                                        <div class="d-flex align-items-center table-minset">
                                            <img src="${providerImage}" class="transactionimg me-3 rounded-circle" alt="Provider Image" style="width: 50px; height: 50px; object-fit: cover;">
                                            <div>
                                                <span class="fw-bold d-block">${truncateText(
                                                    transaction.provider.name
                                                )}</span>
                                                <small class="text-muted">${truncateText(
                                                    transaction.provider.email
                                                )}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center table-minset">
                                            <div class="table-imgname">
                                                <img src="${serviceImage}" class="transactionimg me-2" alt="Service Image">
                                                <span>${truncateText(
                                                    transaction.service.name
                                                )}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>${currency}${
                                transaction.amount.service_amount
                            }</td>
                                    <td>${currency}${
                                transaction.amount.tax
                            }</td>
                                    <td>${formattedDate}</td>
                                    <td>${paymentType}</td>
                                    <td <h6 class="badge-active ${statusClass}">${paymentStatus}</td>
                                    <td>
                                        <div class="table-actions d-flex">
                                            <a class="delete-table view-transaction" href="#!"
                                            data-booking-id="${transaction.id}"
                                            data-customer="${
                                                transaction.customer.name
                                            }"
                                            data-provider="${
                                                transaction.provider.name
                                            }"
                                            data-service="${
                                                transaction.service.name
                                            }"
                                            data-amount="${
                                                transaction.amount
                                                    .service_amount
                                            }"
                                            data-tax="${transaction.amount.tax}"
                                            data-date="${formattedDate}"
                                            data-payment-type="${paymentType}"
                                            data-payment-status="${paymentStatus}"
                                            data-payment-proof="${
                                                transaction.payment
                                                    .payment_proof
                                            }"
                                            data-transaction_id="${
                                                transaction.payment
                                                    .transaction_id
                                            }"
                                            data-status="${transaction.status}"
                                            data-currency="${transaction.currencySymbol}"
                                            data-additional_services='${JSON.stringify(transaction.additional_services)}'
                                            data-bank_transfer_proof="${transaction.payment?.bank_transfer_proof}">
                                                <i class="ti ti-eye fs-20 m-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    $("#transactionList tbody").html(tableBody);

                    $(document).on("click", ".view-transaction", function () {
                        let additionalServices = $(this).data("additional_services");
                        let currency = $(this).data("currency");
                        if (Array.isArray(additionalServices) && additionalServices.length > 0) {
                            let list = "<ul class='mb-0 ps-3'>";
                            additionalServices.forEach(service => {
                                list += `<li><bold>${service.name}</bold> - ${currency}${service.price}</li>`;
                            });
                            list += "</ul>";
                            $(".additional_service").removeClass('d-none');
                            $("#additional_service_list").html(list);
                        } else {
                            $(".additional_service").addClass('d-none');
                        }

                        let customer = $(this).data("customer");
                        let provider = $(this).data("provider");
                        let service = $(this).data("service");
                        let amount = $(this).data("amount");
                        let tax = $(this).data("tax");
                        let date = $(this).data("date");
                        let paymentType = $(this).data("payment-type");
                        let paymentStatus = $(this).data("payment-status");
                        let status = $(this).data("status");
                        let paymentProof = $(this).data("payment-proof"); // Payment proof path
                        let transactionId = $(this).data("transaction_id"); // Payment proof path
                        let bankTransferProof = $(this).data("bank_transfer_proof");

                        currentBookingId = $(this).data("booking-id");

                        $("#transactionCustomer").text(customer);
                        $("#transactionProvider").text(provider);
                        $("#transactionService").text(service);
                        $("#transactionAmount").text(currency + amount);
                        $("#transactionTax").text(currency + tax);
                        $("#transactionDate").text(date);
                        $("#transactionPaymentType").text(paymentType);
                        $("#transactionId").text(transactionId);
                        $("#transactionStatus").text(paymentStatus);

                        // Handle Payment Proof Preview
                        let filePreview = $("#filePreview");
                        filePreview.empty(); // Clear previous content

                        if (paymentProof) {
                            filePreview.removeClass("d-none"); // Show the preview section
                            const fileExtension = paymentProof
                                .split(".")
                                .pop()
                                .toLowerCase();
                            if (
                                ["jpg", "jpeg", "png", "gif"].includes(
                                    fileExtension
                                )
                            ) {
                                filePreview.html(
                                    `<img src="${window.location.origin}/storage/${paymentProof}" alt="Payment Proof" class="img-fluid rounded">`
                                );
                            } else if (fileExtension === "pdf") {
                                filePreview.html(`
                                    <a href="${window.location.origin}/storage/${paymentProof}" class="btn btn-primary" target="_blank">
                                        <i class="ti ti-download"></i> Download Payment Proof
                                    </a>
                                `);
                            } else {
                                filePreview.html(
                                    `<p class="text-danger">Unsupported file type</p>`
                                );
                            }
                        } else {
                            filePreview.addClass("d-none"); // Hide the preview section
                        }

                        if (paymentStatus !== "Paid") {
                            $("#codUploadSection").removeClass("d-none");
                        } else {
                            $("#codUploadSection").addClass("d-none");
                        }

                        if (bankTransferProof) {
                            filePreview.removeClass("d-none"); // Show the preview section
                            filePreview.html(`
                                <a href="${bankTransferProof}" class="btn btn-primary" target="_blank">
                                    <i class="ti ti-eye"></i> View Payment Proof
                                </a>
                            `);
                        } else {
                            filePreview.addClass("d-none"); // Hide the preview section
                        }

                        $("#veiw_transaction").modal("show");
                    });

                    if (
                        transactions.length != 0 &&
                        !$.fn.DataTable.isDataTable("#transactionList")
                    ) {
                        $("#transactionList").DataTable({
                            ordering: true,
                            language: datatableLang,
                            order: [[0, "desc"]],
                        });
                    }
                }
            },
            error: function () {
                toastr.error("Unable to fetch session data. Please try again.");
                $("#loader-table").hide();
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
            },
        });
    }

    $(document).on("click", "#leadsTransation", function () {
        $("#transactionList").addClass("d-none");
        $("#leadsTransactionTable").removeClass("d-none");
        if ($.fn.DataTable.isDataTable("#transactionList")) {
            $("#transactionList").DataTable().destroy();
        }
        if ($.fn.DataTable.isDataTable("#leadsTransactionTable")) {
            $("#leadsTransactionTable").DataTable().destroy();
        }
        $("#leadsTransactionTable tbody").empty();

        listLeadsTransaction();
    });

    $(document).on("click", "#bookingTransaction", function () {
        $("#leadsTransactionTable").addClass("d-none");
        $("#transactionList").removeClass("d-none");
        if ($.fn.DataTable.isDataTable("#leadsTransactionTable")) {
            $("#leadsTransactionTable").DataTable().destroy();
        }
        if ($.fn.DataTable.isDataTable("#transactionList")) {
            $("#transactionList").DataTable().destroy();
        }
        $("#transactionList tbody").empty();
        bookingTransactionList();
    });

    function listLeadsTransaction() {
        $.ajax({
            url: "/leads/transaction-list",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
                customer_id: $("#user_id").val(),
            },
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    let transactions = response.data;
                    let tableBody = "";

                    if (transactions.length === 0) {
                        $("#leadsTransactionTable").DataTable().destroy();
                        tableBody += `
                            <tr>
                                <td colspan="9" class="text-center">${$(
                                    "#leadsTransactionTable"
                                ).data("empty")}</td>
                            </tr>`;
                    } else {
                        transactions.forEach((transaction, index) => {
                            tableBody += `
                                <tr>
                            <td>${index + 1}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="${
                                        transaction.provider.profile_image
                                    }" class="transactionimg me-3 rounded-circle admin_provider_img" alt="Provider Image">
                                    <div>
                                        <span class="fw-bold d-block">${truncateText(
                                            transaction.provider.full_name
                                        )}</span>
                                        <small class="text-muted">${truncateText(
                                            transaction.provider.email
                                        )}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                ${transaction.category}
                            </td>
                            <td>${transaction.currency}${
                                transaction.payment.amount
                            }</td>
                            <td>${transaction.payment.date}</td>
                            <td class="text-center">${
                                transaction.payment.type
                            }</td>
                            <td>
                                <span class="badge ${
                                    transaction.payment.status == "Paid"
                                        ? "badge-soft-success"
                                        : "badge-soft-danger"
                                } d-flex align-items-center">
                                    <i class="ti ti-point-filled"></i> ${
                                        transaction.payment.status
                                    }
                                </span>
                            </td>
                            <td>
                                <div class="table-actions d-flex">
                                    <a class="view-leads-transaction" href="javascript:void(0);"
                                        data-customer="${
                                            transaction.customer.full_name
                                        }"
                                        data-provider="${
                                            transaction.provider.full_name
                                        }"
                                        data-category="${transaction.category}"
                                        data-amount="${
                                            transaction.payment.amount
                                        }"
                                        data-date="${transaction.payment.date}"
                                        data-payment_type="${
                                            transaction.payment.type
                                        }"
                                        data-payment_status="${
                                            transaction.payment.status
                                        }">
                                        <i class="ti ti-eye fs-20 m-2"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                            `;
                        });
                    }

                    $("#leadsTransactionTable tbody").html(tableBody);

                    if (
                        transactions.length != 0 &&
                        !$.fn.DataTable.isDataTable("#leadsTransactionTable")
                    ) {
                        $("#leadsTransactionTable").DataTable({
                            ordering: true,
                            language: datatableLang,
                            pageLength: 10,
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

    $(document).on("click", ".view-leads-transaction", function () {
        let customer = $(this).data("customer");
        let provider = $(this).data("provider");
        let category = $(this).data("category");
        let amount = $(this).data("amount");
        let date = $(this).data("date");
        let paymentType = $(this).data("payment_type");
        let paymentStatus = $(this).data("payment_status");

        $("#leadsTransactionCustomer").text(customer);
        $("#leadsTransactionProvider").text(provider);
        $("#leadsTransactionService").text(category);
        $("#leadsTransactionAmount").text(amount);
        $("#leadsTransactionDate").text(date);
        $("#leadsTransactionPaymentType").text(paymentType);
        $("#leadsTransactionPaymentStatus").text(paymentStatus);

        $("#veiw_leads_transaction_modal").modal("show");
    });
}

if (pageValue === "productlists" || pageValue === "productlistcategory") {
    var selectedSubcategory = "";

    $(window).on("pageshow", function () {
        $("#searchServiceBtn").attr("disabled", false).html("Search");
    });

    $("#filterForm").on("submit", function () {
        $("#searchServiceBtn")
            .attr("disabled", true)
            .html(
                '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span> Searching..'
            );
    });

    $(document).ready(function () {
        $("#location").select2();
        var queryParams = new URLSearchParams(window.location.search);
        var selectedCategories = queryParams.getAll("cate[]");

        var selectedKeyword = queryParams.getAll("keywords");
        if (selectedKeyword != "") {
            $("#keywords").val(selectedKeyword);
        }

        if (selectedCategories.length > 0) {
            $("#all_categories").prop("checked", false);

            $(".filter_category").each(function () {
                var categoryId = $(this).val();
                if (selectedCategories.includes(categoryId)) {
                    $(this).prop("checked", true);
                } else {
                    $(this).prop("checked", false);
                }
            });
        }

        if (pageValue == "productlistcategory") {
            var url = window.location.href;
            var lastSegment = url.substring(url.lastIndexOf("/") + 1);
            $(".filter_category").each(function () {
                let category = $(this).data("slug");
                if (category == lastSegment) {
                    $(this).prop("checked", true);
                }
            });
        }

        var sortPrice = queryParams.get("sortprice");
        if (sortPrice) {
            $("#sortprice").val(sortPrice);
        }

        var categoryId = getCategoryIds();
        listSubcategory(categoryId);

        selectedSubcategory = queryParams.get("subcategory");

        var selectedRatings = queryParams.getAll("rating[]");

        if (selectedRatings.length > 0) {
            $(".rating_filter").each(function () {
                var categoryId = $(this).val();
                if (selectedRatings.includes(categoryId)) {
                    $(this).prop("checked", true);
                }
            });
        }

        var selectedLocation = queryParams.get("location");
        if (selectedLocation != "") {
            $("#location").val(selectedLocation).trigger("change");
        }

        var selectedPrice = queryParams.get("range_price");

        var fromValue = 0;
        var toValue = 0;

        if (selectedPrice) {
            var rangeValues = selectedPrice.split(";");
            fromValue = parseInt(rangeValues[0]);
            toValue = parseInt(rangeValues[1]);
            $(".filter-range-amount span").text(
                "" + fromValue + " - " + toValue
            );
        }

        $("#range").ionRangeSlider({
            type: "double",
            min: 0,
            max: 500,
            from: fromValue,
            to: toValue,
            step: 1,
            prefix: "",
            onChange: function (data) {
                $(".filter-range-amount span").text(
                    " " + data.from + " - " + data.to
                );
            },
        });
    });

    $("#all_categories").on("change", function () {
        const isChecked = $(this).prop("checked");
        $(".filter_category").prop("checked", isChecked);
        var categoryId = getCategoryIds();
        listSubcategory(categoryId);
    });

    $(".filter_category").on("change", function () {
        if (!$(this).prop("checked")) {
            $("#all_categories").prop("checked", false);
        } else if (
            $(".filter_category:checked").length ===
            $(".filter_category").length
        ) {
            $("#all_categories").prop("checked", true);
        }
        var categoryId = getCategoryIds();
        listSubcategory(categoryId);
    });

    function getCategoryIds() {
        const checkedValues = $(".filter_category:checked")
            .map(function () {
                return $(this).val();
            })
            .get();
        return checkedValues;
    }

    function listSubcategory(categoryId) {
        $.ajax({
            url: "/api/get-subcategories",
            type: "POST",
            dataType: "json",
            data: {
                category_id: categoryId,
                language_id: $("body").data("language_id"),
            },
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#subcategory").find("option").not(":first").remove();
                if (response.length != 0) {
                    response.forEach((item) => {
                        $("#subcategory").append(
                            `<option value="${item.id}" ${
                                item.id == selectedSubcategory ? "selected" : ""
                            }>${item.name}</option>`
                        );
                    });
                }
            },
            error: function (error) {
                $("#subcategory").find("option").not(":first").remove();
                if (error.responseJSON && error.responseJSON.code === 500) {
                    toastr.error(error.responseJSON.message);
                }
            },
        });
    }
}

if (pageValue === "user.providerdetails") {
    const providerId = localStorage.getItem("provider_detail_id");
    $("#pageLoader1").show();
    $.ajax({
        url: "/api/provider/details",
        type: "POST",
        data: {
            provider_id: providerId,
        },
        success: function (response) {
            $("#pageLoader1").hide();
            if (response.code === 200) {
                const user = response.data.user;
                const category = user.user_details.category;

                if (response.data.mapHasError) {
                    $(".map-error").removeClass("d-none");
                    $(".map-iframe").addClass("d-none");
                } else {
                    $(".map-iframe iframe").attr('src', response.data.googleMapUrl);
                    $(".map-error").addClass("d-none");
                    $(".map-iframe").removeClass("d-none");
                }

                $(".provider_id").attr("href", `/services?provider=${user.id}`);

                const companyCard = `
                <div class="card shadow-none rounded">
                    <div class="card-body text-center px-2">
                        <span class="d-block mb-2">
                            <img src="/front/img/icons/branch-icon-01.svg" class="w-auto m-auto" alt="Company Image">
                        </span>
                        <h6 class="mb-2">${
                            user.user_details.company_name || "N/A"
                        }</h6>
                        <p class="d-flex align-items-center justify-content-center fs-14">
                            <i class="ti ti-map-pin me-1"></i>${
                                user.user_details.company_address ||
                                "Address Not Available"
                            }
                        </p>
                    </div>
                </div>
            `;

                $(".our-branches-slider").append(companyCard);

                $(".provider-pic img").attr(
                    "src",
                    user.user_details.profile_image
                        ? "/storage/profile/" + user.user_details.profile_image
                        : "/assets/img/profile-default.png"
                );

                $("h5 a").text(
                    `${user.user_details.first_name} ${
                        user.user_details.last_name || ""
                    }`
                );
                const maskedEmail = user.email.replace(
                    /^(.)(.*)(@.*)$/,
                    (match, p1, p2, p3) => {
                        return `${p1}xxx${p3}`;
                    }
                );

                const maskedPhoneNumber = user.phone_number
                    ? user.phone_number.replace(/.(?=.{4})/g, "x")
                    : "N/A";

                $(".provider-bio-info h6:contains('Email')")
                    .next()
                    .text(maskedEmail);
                $(".provider-bio-info h6:contains('Phone Number')")
                    .next()
                    .text(maskedPhoneNumber);
                $(".provider-bio-info h6:contains('Language Known')")
                    .next()
                    .text(user.user_details.language);
                $(".provider-bio-info h6:contains('Address')")
                    .next()
                    .text(user.user_details.address);

                const createdAt = new Date(user.created_at).toLocaleDateString(
                    "en-US",
                    {
                        year: "numeric",
                        month: "short",
                        day: "2-digit",
                    }
                );

                const address = `${user.user_details.address}`;
                $(".category_name").text(category.name);
                $(".date_format").text(`Member Since ${createdAt}`);

                $(".product_details").empty();

                let highestRatedProduct = null;
                let highestRating = 0;

                if (response.data.products.length === 0) {
                    $(".product_details").html(
                        '<p class="text-center text-muted">No products found.</p>'
                    );
                } else {
                    response.data.products.forEach(function (product) {
                        const averageRating = product.average_rating || 0;
                        const ratingCount = product.rating_count || 0;

                        let ratingStars = "";
                        for (let i = 0; i < 5; i++) {
                            if (i < Math.floor(averageRating)) {
                                ratingStars +=
                                    '<i class="fa fa-star filled"></i>';
                            } else if (i < Math.ceil(averageRating)) {
                                ratingStars +=
                                    '<i class="fa-solid fa-star-half-stroke filled"></i>';
                            } else {
                                ratingStars += '<i class="fa fa-star"></i>';
                            }
                        }
                        const defaultImage1 =
                            "/front/img/services/add-service-04.jpg";
                        let serviceImage =
                            product.image_url && product.image_url !== "N/A"
                                ? `/storage/${product.image_url}`
                                : defaultImage1;
                        const productCard = `
                            <div class="card">
                                <div class="card-body">
                                    <div class="img-sec w-100 mb-3">
                                        <a href="/servicedetail/${product.slug}" ><img src="${serviceImage}" class="img-fluid rounded" alt="img" style="width: 150px; height: auto;"></a>
                                    </div>
                                    <div>
                                        <h5 class="mb-2 text-truncate">
                                            <a href="/servicedetail/${product.slug}" >${product.source_name}</a>
                                        </h5>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <p class="fs-14 mb-0"><i class="ti ti-map-pin me-2"></i>${address}</p>
                                            <span class="rating text-gray fs-14">${ratingStars} ${averageRating} <span class="d-inline-block">(${ratingCount} reviews)</span></span>
                                        </div>
                                        <div>
                                            <span>Price</span>
                                            <h6 class="text-primary fs-16 mt-1">$${product.source_price}
                                                <small class="text-decoration-linethrow d-none fs-14 fw-normal text-gray">${product.source_price}</small>
                                                <span class="fs-14 fw-normal text-gray"></span>
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        $(".product_details").append(productCard);

                        if (averageRating > highestRating) {
                            highestRating = averageRating;
                            highestRatedProduct = product;
                        }
                    });
                }

                if (highestRatedProduct) {
                    const highestRatingStars = Array.from(
                        { length: 5 },
                        (_, i) => {
                            return i <
                                Math.floor(highestRatedProduct.average_rating)
                                ? '<i class="fa fa-star filled"></i>'
                                : i <
                                  Math.ceil(highestRatedProduct.average_rating)
                                ? '<i class="fa-solid fa-star-half-stroke filled"></i>'
                                : '<i class="fa fa-star"></i>';
                        }
                    ).join("");

                    $(".provider_rate").html(`
                        ${highestRatingStars} ${highestRatedProduct.average_rating} <span class="d-inline-block">(${highestRatedProduct.rating_count} reviews)</span>
                    `);
                }
            }
        },
        error: function () {
            $("#pageLoader1").hide();
            toastr.error("Unable to fetch provider details. Please try again.");
        },
    });
}
$(document).on("click", "#del_account_btn", function () {
    $(".error-text").text("");
    $(".form-control").removeClass("is-invalid is-valid");
    $("#password_del").val("");
});

$("#deleteAccountForm").validate({
    rules: {
        password: {
            required: true,
        },
    },
    messages: {
        password: {
            required: "Password is required.",
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
        $.ajax({
            url: "/user/delete-account",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                id: $("#deleteAccountBtn").data("id"),
                password: $("#password_del").val(),
                language_code: $("body").data("lang"),
            },
            dataType: "json",
            beforeSend: function () {
                $("#deleteAccountBtn")
                    .attr("disabled", true)
                    .html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
            },
            success: function (response) {
                $(".error-text").text("");
                $("#deleteAccountBtn")
                    .removeAttr("disabled")
                    .html($("#deleteAccountBtn").data("delete"));
                $(".form-control").removeClass("is-invalid is-valid");
                $("#del-account").modal("hide");
                if (response.code === 200) {
                    var homePageUrl = window.location.origin + "/";
                    window.location.href = homePageUrl;

                    toastr.success(response.message);
                }
            },
            error: function (error) {
                $(".error-text").text("");
                $("#deleteAccountBtn")
                    .removeAttr("disabled")
                    .html($("#deleteAccountBtn").data("delete"));
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

$("#deleteAccountForm").validate().settings.messages = {
    password: {
        required: $("#deleteAccountBtn").data("password_required"),
    },
};

if (pageValue === "blog-details") {
    $(".blog_menu").addClass("active");

    var userId = $("#user_id").val();
    setupValidation(userId);

    function setupValidation(userId) {
        let rules = {};
        let messages = {};

        if (userId == "") {
            rules = {
                name: {
                    required: true,
                    maxlength: 100,
                },
                email: {
                    required: true,
                    email: true,
                },
                comment: { required: true },
            };
            messages = {
                name: {
                    required: "Name is required.",
                    maxlength: "Name cannot be exceed 100 characters.",
                },
                email: {
                    required: "Email is required.",
                    email: "Please enter a valid email.",
                },
                comment: { required: "Comment is required." },
            };
        } else {
            rules = {
                comment: { required: true },
            };
            messages = {
                comment: { required: "Comment is required." },
            };
        }

        $("#blogCommentForm").validate({
            rules: rules,
            messages: messages,
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
                let formData = {};
                var user_id = $("#user_id").val();
                if (user_id == "") {
                    formData = {
                        user_id: user_id,
                        post_id: $("#post_id").val(),
                        name: $("#post_name").val(),
                        email: $("#post_email").val(),
                        comment: $("#comment").val(),
                    };
                } else {
                    formData = {
                        user_id: user_id,
                        post_id: $("#post_id").val(),
                        comment: $("#comment").val(),
                    };
                }
                saveComment(formData);
            },
        });
    }

    $("#blogCommentForm").validate().settings.messages = {
        name: {
            required: $("#blogCommentBtn").data("name_required"),
            maxlength: $("#blogCommentBtn").data("name_max"),
        },
        email: {
            required: $("#blogCommentBtn").data("email_required"),
            email: $("#blogCommentBtn").data("email_format"),
        },
        comment: { required: $("#blogCommentBtn").data("comment_required") },
    };

    function saveComment(formData) {
        $.ajax({
            url: "/api/blogs/add-comment",
            type: "POST",
            data: formData,
            beforeSend: function () {
                $("#blogCommentBtn")
                    .attr("disabled", true)
                    .html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
            },
            success: function (response) {
                $(".error-text").text("");
                $("#blogCommentBtn")
                    .removeAttr("disabled")
                    .html("Post Comment");
                $(".form-control").removeClass("is-invalid is-valid");
                if (response.code === 200) {
                    toastr.success(response.message);
                    $("#blogCommentForm").trigger("reset");
                    listBlogComments();
                }
            },
            error: function (error) {
                $(".error-text").text("");
                $("#blogCommentBtn")
                    .removeAttr("disabled")
                    .html("Post Comment");
                $(".form-control").removeClass("is-invalid is-valid");
                if (error.responseJSON.code === 422) {
                    $.each(error.responseJSON.errors, function (key, val) {
                        if (key != "comment") {
                            $("#" + "post" + key).addClass("is-invalid");
                            $("#" + "post" + key + "_error").text(val[0]);
                        } else {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        }
                    });
                } else {
                    toastr.error(error.responseJSON.message);
                }
            },
        });
    }

    function listBlogComments() {
        $.ajax({
            url: "/api/blogs/list-comments",
            type: "POST",
            data: {
                post_id: $("#post_id").val(),
            },
            dataType: "json",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    const comments = response.data;

                    $("#blog_comments_container").show();
                    $("#list_blog_comments").empty();

                    if (comments.length > 0) {
                        comments.forEach((comment) => {
                            $("#list_blog_comments").append(`
                                <li>
                                    <div class="review-box">
                                        <div class="card shadow-none">
                                            <div class="card-body">
                                                <div class="d-flex align-items-start justify-content-between mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <span class="avatar avatar-md flex-shrink-0 me-2"><img src="${comment.image}" class="img-fluid rounded-circle" alt="img"></span>
                                                        <div class="review-name">
                                                            <h6 class="fs-16 fw-medium mb-1">${comment.name}</h6>
                                                            <p class="fs-14">${comment.comment_date}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p>${comment.comment}</p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            `);
                        });
                    }
                }
            },
            error: function (error) {
                if (error.responseJSON && error.responseJSON.code === 500) {
                    toastr.error(error.responseJSON.message);
                }
            },
        });
    }
}

if (pageValue === "contact-us") {
    var langCode = $("body").data("lang");

    let currentLang = langCode;

    const validationMessages = {
        en: {
            name: {
                required: "Name is required.",
                maxlength: "Name cannot be exceed 100 characters.",
                pattern: "Name must contain only alphabets and spaces.",
            },
            email: {
                required: "Email is required.",
                email: "Please enter a valid email.",
            },
            phone_number: {
                required: "Phone number is required.",
                digits: "Phone number must be a number.",
                minlength: "Phone number must be between 10 to 12 digits.",
                maxlength: "Phone number must be between 10 to 12 digits.",
            },
            message: {
                required: "Message is required.",
            },
        },
        ar: {
            name: {
                required: "الاسم مطلوب.",
                maxlength: "لا يمكن أن يتجاوز الإسم 100 حرف.",
                pattern: "يجب أن يحتوي الاسم على الحروف الأبجدية فقط.",
            },
            email: {
                required: "البريد الإلكتروني مطلوب.",
                email: "الرجاء إدخال بريد إلكتروني صالح.",
            },
            phone_number: {
                required: "رقم الهاتف مطلوب.",
                digits: "رقم الهاتف يجب أن يكون رقماً.",
                minlength: "يجب أن يكون رقم الهاتف بين 10 إلى 12 رقمًا.",
                maxlength: "يجب أن يكون رقم الهاتف بين 10 إلى 12 رقمًا.",
            },
            message: {
                required: "الرسالة مطلوبة.",
            },
        },
    };

    $("#contactForm").validate({
        rules: {
            name: {
                required: true,
                maxlength: 100,
                pattern: /^[A-Za-z\s]+$/,
            },
            email: {
                required: true,
                email: true,
            },
            phone_number: {
                required: true,
                digits: true,
                minlength: 10,
                maxlength: 12,
            },
            message: {
                required: true,
            },
        },
        messages: validationMessages[currentLang],
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
            var formData = {
                name: $("#contact_name").val(),
                email: $("#contact_email").val(),
                phone_number: $("#contact_phone_number").val(),
                message: $("#message").val(),
            };
            $.ajax({
                url: "/api/save-contact-details",
                type: "POST",
                data: formData,
                dataType: "json",
                beforeSend: function () {
                    $("#contactSaveBtn")
                        .attr("disabled", true)
                        .html(
                            '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span> Sending...'
                        );
                },
                success: function (response) {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid is-valid");
                    $("#contactSaveBtn")
                        .removeAttr("disabled")
                        .html(
                            'Send Message<i class="feather-arrow-right-circle ms-2"></i>'
                        );
                    if (response.code === 200) {
                        toastr.success(response.message);
                        $("#contactForm").trigger("reset");
                    }
                },
                error: function (error) {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid is-valid");
                    $("#contactSaveBtn")
                        .removeAttr("disabled")
                        .html(
                            'Send Message<i class="feather-arrow-right-circle ms-2"></i>'
                        );
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
        },
    });
}
// Check if the image exists
function checkImageExists(imageUrl, callback) {
    const img = new Image();
    img.onload = () => callback(true);
    img.onerror = () => callback(false);
    img.src = imageUrl;
}

if (pageValue === "user.security") {
    $(document).ready(function () {
        var langCode = $("body").data("lang");

        let currentLang = langCode;

        const validationMessages = {
            en: {
                current_password: {
                    required: "Current password is required.",
                    minlength: "Password must be at least 8 characters long.",
                    remote: "Incorrect password.",
                },
                new_password: {
                    required: "New password is required.",
                    minlength: "Password must be at least 8 characters long.",
                    notEqualTo:
                        "New password cannot be the same as the current password.",
                },
                confirm_password: {
                    required: "Confirm password is required.",
                    equalTo:
                        "The confirmation password doesn't match the new password.",
                },
            },
            ar: {
                current_password: {
                    required: "كلمة المرور الحالية مطلوبة.",
                    minlength: "يجب أن تكون كلمة المرور 8 أحرف على الأقل.",
                    remote: "كلمة المرور غير صحيحة.",
                },
                new_password: {
                    required: "كلمة المرور الجديدة مطلوبة.",
                    minlength: "يجب أن تكون كلمة المرور 8 أحرف على الأقل.",
                    notEqualTo:
                        "لا يمكن أن تكون كلمة المرور الجديدة هي نفسها كلمة المرور الحالية.",
                },
                confirm_password: {
                    required: "تأكيد كلمة المرور مطلوب.",
                    equalTo:
                        "كلمة المرور التأكيدية لا تطابق كلمة المرور الجديدة.",
                },
            },
        };

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
            messages: validationMessages[currentLang],
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
                    $(btnId).removeAttr("disabled").html("Save Changes");
                    $(".form-control").removeClass("is-invalid is-valid");
                    if (response.code === 200) {
                        $("#change-password").modal("hide");
                        toastr.success(response.message);
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
                    $(btnId).removeAttr("disabled").html("Save Changes");
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
    });

    $(document).on("submit", ".delete-device-form", function (event) {
        event.preventDefault();

        var form = $(this);
        var formData = new FormData(this);
        var row = form.closest("tr"); // Get the row to remove

        formData.append("_token", $('meta[name="csrf-token"]').attr("content"));

        $.ajax({
            url: "/device/delete",
            method: "POST",
            data: formData,
            dataType: "json",
            contentType: false,
            processData: false,
            cache: false,
            success: function (response) {
                if (response.success) {
                    $("#device-management").modal("hide");
                    toastr.success(response.message);
                    row.remove();
                } else {
                    alert(response.message || "An error occurred.");
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert("Failed to delete the device. Please try again.");
            },
        });
    });
}

if (pageValue === "user.wallet") {
    $.ajax({
        url: "/api/get-session-user-id",
        type: "GET",
        success: function (response) {
            if (response.user_id) {
                userId = response.user_id;
                localStorage.setItem("user_id", userId);
                walletList(userId);
            }
        },
        error: function () {
            toastr.error("Unable to fetch session data. Please try again.");
        },
    });

    function walletList(userId) {
        $.ajax({
            url: "/api/walletHistory",
            type: "POST",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            data: {
                userId: userId,
            },
            success: function (response) {
                if (response.success) {
                    $("#loader-table").hide();
                    $(".label-loader, .input-loader").hide();
                    $(".real-label, .real-input").removeClass("d-none");
                    // Update wallet balance and currency
                    $(".wallet_balance .currency").text(response.Currency);
                    $(".wallet_balance .balance").text(
                        response.totalAmountBalance
                    );
                    $(".total_debit .currency").text(response.Currency);
                    $(".total_amount .currency").text(response.Currency);
                    $(".total_debit .totalAmountdebit").text(
                        response.totalAmountdebit
                    );
                    $(".total_amount .totalAmount").text(response.totalAmount);

                    let currency_symbol = response.Currency;
                    let tableBody = $("#walletHistoryTable tbody");
                    let tableRow = "";
                    let walletHistories = response.data;

                    if (walletHistories.length == 0) {
                        if ($.fn.DataTable.isDataTable("#walletHistoryTable")) {
                            $("#walletHistoryTable").DataTable().destroy();
                        }
                        tableRow += `
                            <tr>
                                <td colspan="8" class="text-center">${$(
                                    "#walletHistoryTable"
                                ).data("empty")}</td>
                            </tr>`;
                    } else {
                        response.data.forEach((record, index) => {
                            tableRow += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${currency_symbol}${record.amount}</td>
                                    <td>${record.payment_type}</td>
                                    <td>${record.status}</td>
                                    <td>${record.transaction_date}</td>
                                </tr>
                            `;
                        });
                    }

                    tableBody.html(tableRow);

                    if (
                        walletHistories.length != 0 &&
                        !$.fn.DataTable.isDataTable("#walletHistoryTable")
                    ) {
                        $("#walletHistoryTable").DataTable({
                            ordering: true,
                            language: datatableLang,
                        });
                    }
                }
            },
            error: function () {
                toastr.error("Error fetching wallet history");
            },
        });
    }

    /*payment*/
    $(document).on("click", ".add_wallet", function () {
        $.ajax({
            url: "/api/getpaymentmethod",
            type: "POST",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response && response.length > 0) {
                    let csrfToken = document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content");
                    let html = `
                        <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="paymentModalLabel">Add to Wallet</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="paymentForm" enctype="multipart/form-data">
                                            <input type="hidden" name="_token" value="${csrfToken}">
                                            <div class="mb-3">
                                                <label for="amount" class="form-label">Amount</label>
                                                <input type="number" name="amount" class="form-control amount" id="amount" placeholder="Enter amount" >
                                            </div>
                                            <div class="mb-3" id="paymentmethoddiv">
                                                <label class="form-label mb-2">Choose Payment Method:</label>
                    `;

                    response.forEach((data) => {
                        if (data.label != "wallet" && data.label != "banktransfer") {
                            html += `
                                <div class="form-check">
                                    <input class="form-check-input paymentmethod" type="radio" name="paymentMethod" id="${data.label}" value="${data.label}">
                                    <label class="form-check-label" for="${data.label}">${data.payment_type}</label>
                                </div>
                            `;
                        }
                    });

                    html += `
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100 mt-3" id="payNowButton">Pay Now</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    $("body").append(html);
                    $("#paymentModal").modal("show");

                    $("#paymentModal").on("hidden.bs.modal", function () {
                        $(this).remove();
                    });

                    $("#paymentForm").on("submit", function (e) {
                        e.preventDefault();

                        let amount = $(".amount").val();
                        let paymentMethod = $(
                            'input[name="paymentMethod"]:checked'
                        ).val();
                        let userId = localStorage.getItem("user_id");
                        if (!amount || amount < 50) {
                            toastr.error(
                                "Please enter a valid amount (minimum 50)"
                            );
                            return;
                        }
                        if (!paymentMethod) {
                            toastr.error("Please select a payment method");
                            return;
                        }
                        $.ajax({
                            url: "/api/addWalletAmount",
                            type: "POST",
                            data: {
                                amount: amount,
                                paymentMethod: paymentMethod,
                                userId: userId,
                                id: userId,
                            },
                            headers: {
                                Authorization:
                                    "Bearer " +
                                    localStorage.getItem("admin_token"),
                                "X-CSRF-TOKEN": $(
                                    'meta[name="csrf-token"]'
                                ).attr("content"),
                            },
                            success: function (response) {
                                $("#paymentModal").modal("hide");

                                let trxId = response.data.id;
                                if (response.data.payment_type === "paypal") {
                                    processPayment(
                                        "/processpayment",
                                        amount,
                                        "paypal",
                                        trxId,
                                        userId
                                    );
                                } else if (paymentMethod === "stripe") {
                                    let form = $("<form>", {
                                        action: "/stripepayment",
                                        method: "POST",
                                    }).append(
                                        $("<input>", {
                                            type: "hidden",
                                            name: "_token",
                                            value: csrfToken,
                                        }),
                                        $("<input>", {
                                            type: "hidden",
                                            name: "amount",
                                            value: amount,
                                        }),
                                        $("<input>", {
                                            type: "hidden",
                                            name: "trxId",
                                            value: trxId,
                                        }),
                                        $("<input>", {
                                            type: "hidden",
                                            name: "userId",
                                            value: userId,
                                        }),
                                        $("<input>", {
                                            type: "hidden",
                                            name: "type",
                                            value: "wallet",
                                        })
                                    );

                                    // Append the form to the body and submit it
                                    $("body").append(form);
                                    form.submit();
                                } else {
                                    toastr.error(
                                        "Please select a valid payment method"
                                    );
                                }
                            },
                            error: function () {
                                toastr.error("Error updating wallet");
                            },
                        });
                    });
                } else {
                    toastr.error("No payment methods available");
                }
            },
            error: function () {
                toastr.error("Error fetching payment methods");
            },
        });
    });

    function processPayment(url, amount, method, trxId, userId) {
        $.ajax({
            url: url,
            type: "POST",
            data: {
                paymenttype: method === "paypal" ? 1 : 2,
                service_amount: amount,
                trx_id: trxId,
                type: "wallet",
                name: "kddd",
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (method === "paypal") {
                    window.location.href = response;
                } else if (method === "stripe") {
                    window.location.href = response;
                }
            },
            error: function () {
                toastr.error(`Error processing ${method} payment`);
            },
        });
    }
}

function searchInJson(keyToSearch, jsonData) {
    keyToSearch = keyToSearch.toLowerCase();
    let result = "";

    $.each(jsonData, function (key, value) {
        if (key.toLowerCase().includes(keyToSearch)) {
            result = value;
        }
    });

    if (result) {
        return result;
    }
}

function loadJsonFile(searchKey, callback) {
    const jsonFilePath = "/lang/ar.json";
    $.getJSON(jsonFilePath, function (data) {
        let lang = searchInJson(searchKey, data);
        callback(lang);
    }).fail(function () {
        alert("Failed to load JSON file.");
    });
}
if (pageValue === "user.ticket") {
    applyTicketStatusStyles();
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
                    statusText = "Inprogress";
                    statusClass = "badge badge-soft-info ms-2";
                    break;
                case 3:
                    statusText = "Assigned";
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
    applypriorityStatusStyles();

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
                // Redirect to the ticket details page
                window.location.href = "/user/ticket-details";
            })
            .catch((error) => {
                console.error("Error storing ticket ID:", error);
            });
    }
    $(document).ready(function () {
        // Initialize Summernote editor
        $("#summernote").summernote({
            height: 250,
        });
        $("#add_ticket").on("show.bs.modal", function () {
            $("#summernote").summernote("code", "");
            $("#Ticketform")[0].reset(); // Reset the form data
        });
    });

    //add
    $(document).ready(function () {
        $("#subject").on("change", function () {
            $("#subject_error").hide();
        });
        $("#Ticketform").on("submit", function (event) {
            event.preventDefault();
            // validation
            isValid = true;
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
            var desc = $("#summernote").summernote("code");
            if (!desc) {
                $("#description_error").show();
                if (languageId === 2) {
                    loadJsonFile("description_required", function (langtst) {
                        $("#description_error").text(langtst);
                    });
                } else {
                    $("#description_error").text("Description is required.");
                }
                isValid = false;
            }
            if (isValid == true) {
                var summernoteContent = $("#summernote").summernote("code");
                // Set the content to the target field
                $(".description").val(summernoteContent);
                let formData = {
                    id: $('input[name="id"]').val(),
                    user_id: $('input[name="user_id"]').val(),
                    subject: $('input[name="subject"]').val(),
                    description: summernoteContent,
                    status: 1,
                    priority: $("#priority").val(),
                    user_type: "User",
                };
                $.ajax({
                    url: "/api/user/addticket",
                    type: "POST",
                    data: formData,
                    headers: {
                        Authorization:
                            "Bearer " + localStorage.getItem("admin_token"),
                        Accept: "application/json",
                    },
                    beforeSend: function () {
                        $(".add_ticket_btn")
                            .attr("disabled", true)
                            .html(
                                '<span class="spinner-border spinner-border-sm align-middle" role="status"></span>'
                            );
                    },
                })
                    .done((response) => {
                        $(".add_ticket_btn")
                            .removeAttr("disabled")
                            .html("Save");

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
                            $("#add_ticket").modal("hide");
                            // Create the new ticket card
                            const newTicketHtml = `
                        <div class="card">
                            <div class="">
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="avatar avatar-xxl ms-2 me-2">
                                            ${
                                                response.ticketdata
                                                    .profile_image
                                                    ? `<img src="${response.ticketdata.profile_image}" alt="User Profile Image" class="img-fluid rounded-circle profileImagePreview" style="width: 60px; height: 60px;">`
                                                    : response.ticketdata
                                                          .user_type === "User"
                                                    ? `<img src="/assets/img/user-default.jpg" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview" style="width: 60px; height: 60px;">`
                                                    : `<img src="/assets/img/profile-default.png" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview" style="width: 60px; height: 60px;">`
                                            }
                                        </span>
                                        <div class="mb-2">
                                            <div class="d-flex flex-wrap align-items-center mb-1">
                                                <h6 class="fw-semibold me-2 mb-0 text-truncate">
                                                    <a href="/user/ticket-details/${
                                                        response.ticketdata
                                                            .ticket_id
                                                    }" class="text-decoration-none text-dark">
                                                        ${
                                                            response.ticketdata
                                                                .subject
                                                                ? response.ticketdata.subject.substring(
                                                                      0,
                                                                      20
                                                                  ) + "..."
                                                                : "-"
                                                        }
                                                    </a>
                                                </h6>
                                                <span class="ticket-status ticketstatus${
                                                    response.ticketdata.id
                                                } d-flex align-items-center fs-10 ms-2" data-status="${
                                response.ticketdata.status
                            }">
                                                    <i class="ti ti-circle-filled me-1"></i>${
                                                        response.ticketdata
                                                            .ticket_status ??
                                                        "-"
                                                    }
                                                </span>
                                            </div>

                                            <div class="d-flex flex-wrap align-items-center">
                                                ${
                                                    response.ticketdata
                                                        .assignee_id
                                                        ? `<p class="d-flex align-items-center me-3 mb-1 assigneddetails${
                                                              response
                                                                  .ticketdata.id
                                                          }">
                                                                ${
                                                                    response
                                                                        .ticketdata
                                                                        .assign_profileimage
                                                                        ? `<img src="${response.ticketdata.assign_profileimage}" class="rounded-circle me-2" width="10" height="10" alt="img">`
                                                                        : `<img src="/assets/img/user-default.jpg" class="rounded-circle me-2" width="10" height="10" alt="img">`
                                                                }
                                                                <span class="text-dark">Assigned to <span class="fw-semibold ms-1 assigneename">${
                                                                    response
                                                                        .ticketdata
                                                                        .assignee_name ??
                                                                    "-"
                                                                }</span></span>
                                                        </p>`
                                                        : ""
                                                }
                                                <p class="d-flex align-items-center mb-1 me-2 fs-10">
                                                    <i class="ti ti-calendar-bolt me-1"></i>Updated ${
                                                        response.ticketdata
                                                            .updated_at_relative
                                                    }
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-end">
                                        <div class="d-flex flex-wrap align-items-center fs-10">
                                            <span class="fw-semibold text-muted me-2">Ticket ID:</span>
                                            <span class="badge bg-info text-light rounded-pill me-3">#${
                                                response.ticketdata.ticket_id ??
                                                "-"
                                            }</span>

                                            <span class="fw-semibold text-muted me-2">Priority:</span>
                                            <span class="priority-status d-inline-flex align-items-center me-4" data-status="${
                                                response.ticketdata.priority
                                            }">
                                                <i class="ti ti-circle-filled fs-6 me-1"></i>${
                                                    response.ticketdata
                                                        .priority ?? "-"
                                                }
                                            </span>
                                        </div>
                                        <a href="/user/ticket-details/${
                                            response.ticketdata.ticket_id
                                        }" class="fs-14 bg-primary px-2 py-1 text-light mt-1 fw-bold d-flex align-items-center me-4 rounded">
                                            <i class="ti ti-eye me-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>`;

                            // Append the new ticket card to the top of the ticket list
                            $("#ticket-list").prepend(newTicketHtml);
                            $(".ticket-list").hide();
                            applyTicketStatusStyles();
                            applypriorityStatusStyles();
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
            }
        });
    });
}
if (pageValue === "user.ticketdetails") {
    applyTicketStatusStyles();
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
                    statusText = "Inprogress";
                    statusClass = "badge badge-soft-info ms-2";
                    break;
                case 3:
                    statusText = "Assigned";
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
    applypriorityStatusStyles();

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
        $("#summernote").summernote({
            height: 250,
        });
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
        if (oldstatus != status) {
            let formData = {
                id: ticketid,
                status: status,
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
                        .html("Update Ticket Status");

                    if (response.code == "200") {
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
                        .html("Update Ticket Status");

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

if (pageValue === "dynamic.page") {
    $(".booking_service_btn").click(function (e) {
        e.preventDefault();
        const productSlug = $(this).data('product_slug');

        $.ajax({
            url: "/check-product-user",
            type: "GET",
            data: { product_slug: productSlug }, // Send product_slug in the payload
            success: function (response) {
                if (response.exists === "yes") {
                    window.location.href = `/user/booking/service-booking/${productSlug}`;
                } else {
                    window.location.href = `/user/booking/${productSlug}`;
                }
            },
        });
    });
}
