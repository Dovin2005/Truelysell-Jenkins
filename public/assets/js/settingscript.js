var pageValue = $('body').data('page');

var frontendValue = $('body').data('frontend');
var languageId = $("#language-settings").data("language-id");

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

toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": true,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "4000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

function initTooltip() {
    $('[data-tooltip="tooltip"]').tooltip({
        trigger: 'hover',
    });
}

//general-settings
if (pageValue === 'admin.general-settings') {
    $('#generalTab a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    function validatePhoneNumber(input) {
        const minLength = 10;
        const maxLength = 12;
        const value = input.value;

        // Ensure the length doesn't exceed maxLength
        if (value.length > maxLength) {
            input.value = value.slice(0, maxLength);
        }

        // Show an error if the input isn't within the valid range
        const errorText = document.getElementById('phone_no_error');
        if (value.length >= minLength && value.length <= maxLength) {
            errorText.textContent = '';
        } else {
            errorText.textContent = 'Phone number must be between 10 and 12 digits.';
        }
    }

    function validateFaxNumber(input) {
        const minLength = 10;
        const maxLength = 12;
        const value = input.value;

        // Ensure the length doesn't exceed maxLength
        if (value.length > maxLength) {
            input.value = value.slice(0, maxLength);
        }

        // Show an error if the input isn't within the valid range
        const errorText = document.getElementById('fax_no_error');
        if (value.length >= minLength && value.length <= maxLength) {
            errorText.textContent = '';
        } else {
            errorText.textContent = 'Fax number must be between 10 and 12 digits.';
        }
    }

    $(document).ready(function() {
        $.getJSON('/timezone.json', function(data) {
            const timezoneSelect = $('#timezone');
            timezoneSelect.empty();
            $.each(data.timezones, function(index, timezone) {
                timezoneSelect.append($('<option>', {
                    value: timezone.value,
                    text: timezone.label
                }));
            });
            timezoneSelect.select2();
            const selectedTimezone = timezoneSelect.val();
            if (selectedTimezone) {
                timezoneSelect.trigger('change');
            }
        }).fail(function() {
            toastr.error('Error loading timezone data');
        });

        $('#country').on('change', function() {
            const selectedCountry = $(this).val();
            $.getJSON('/states.json', function(data) {
                const stateSelect = $('#state');
                stateSelect.empty();
                stateSelect.append($('<option>', {
                    value: '',
                    text: 'Select State',
                    disabled: true,
                    selected: true
                }));
                $.each(data.states, function(index, state) {
                    if (state.country_id === selectedCountry) {
                        stateSelect.append($('<option>', {
                            value: state.id,
                            text: state.name
                        }));
                    }
                });
                stateSelect.select2();
            }).fail(function() {
                toastr.error('Error loading state data');
            });
        });

        $('#state').on('change', function() {
            const selectedState = $(this).val();
            $.getJSON('/cities.json', function(data) {
                const citySelect = $('#city');
                citySelect.empty();
                citySelect.append($('<option>', {
                    value: '',
                    text: 'Select City',
                    disabled: true,
                    selected: true
                }));
                $.each(data.cities, function(index, city) {
                    if (city.state_id === selectedState) {
                        citySelect.append($('<option>', {
                            value: city.id,
                            text: city.name
                        }));
                    }
                });
                citySelect.select2({
                    placeholder: "Select City",

                });
            }).fail(function() {
                toastr.error('Error loading city data');
            });
        });

        // Define the loadCountries function
        async function loadCountries() {
            try {
                const data = await $.getJSON('/countries.json');
                const countrySelect = $('#country');
                countrySelect.empty();
                countrySelect.append($('<option>', {
                    value: '',
                    text: 'Select Country',
                    disabled: true,
                    selected: true
                }));

                $.each(data.countries, function(index, country) {
                    countrySelect.append($('<option>', {
                        value: country.id,
                        text: country.name
                    }));
                });

                countrySelect.select2({
                    placeholder: "Select Country",

                });
            } catch (error) {
                toastr.error('Error loading country data:', error);
            }
        }

        async function loadGeneralSettings() {
            const response = await $.ajax({
                url: '/api/admin/general-setting/list',
                type: 'POST',
                data: {'group_id': 1},
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                    'Accept': 'application/json'
                }
            });

            if (response.code === 200) {
                const requiredKeys = [
                    'app_name', 'site_email', 'site_address',
                    'preloader_status', 'timezone', 'live_mail_send', 'is_queable', 'postal_code', 'phone_no',
                    'company_name', 'country', 'state', 'city', 'save_single_vendor_status', 'sso_status','fax_no', 'website',
                    'provider_approval_status', 'service_approval_status'
                ];

                const filteredSettings = response.data.settings.filter(setting => requiredKeys.includes(setting.key));

                filteredSettings.forEach(setting => {
                    if (setting.key === 'is_queable') {
                        $('#' + setting.key).trigger('change');
                    } else if (setting.key === 'save_single_vendor_status') {
                        $("#save_single_vendor_status").val(setting.value).trigger('change');
                    } else if (setting.key === 'sso_status') {
                        $("#sso_status").val(setting.value).trigger('change');
                    } else if (setting.key == 'provider_approval_status' || setting.key == 'service_approval_status') {
                        $('#' + setting.key).prop('checked', setting.value == 1);
                    } else {
                        $('#' + setting.key).val(setting.value);
                    }

                });

                const timezoneSelect = $('#timezone');
                    const selectedTimezone = filteredSettings.find(setting => setting.key === 'timezone');
                    if (selectedTimezone) {
                        timezoneSelect.val(selectedTimezone.value).trigger('change');
                        timezoneSelect.select2(); // Ensure select2 is initialized after setting the value
                    }


                const countrySelect = $('#country');
                const selectedCountry = filteredSettings.find(setting => setting.key === 'country');
                const selectedState = filteredSettings.find(setting => setting.key === 'state');
                const selectedCity = filteredSettings.find(setting => setting.key === 'city');



                if (selectedCountry) {
                    countrySelect.val(selectedCountry.value).trigger('change');

                    // Directly call the loading of states after setting the country
                    loadStatesForSelectedCountry(selectedCountry.value).then(() => {
                        if (selectedState) {
                            $('#state').val(selectedState.value).trigger('change');

                            // Load cities after setting the state
                            loadCitiesForSelectedState(selectedState.value).then(() => {
                                if (selectedCity) {
                                    $('#city').val(selectedCity.value).trigger('change');
                                }
                            });
                        }
                    });
                }
            } else {
                toastr.error('Error fetching settings:', response.message);
            }
            
        }

        async function init() {
            await loadCountries();
            await loadGeneralSettings();
            $(".label-loader, .input-loader").hide();
            $('.real-label, .real-input').removeClass('d-none');
        }

        init().catch((error) => {
            toastr.error('Error during initialization:', error);
        });


        function loadStatesForSelectedCountry(countryId) {
            return new Promise((resolve, reject) => {
                $.getJSON('/states.json', function(data) {
                    const stateSelect = $('#state');
                    stateSelect.empty();
                    stateSelect.append($('<option>', {
                        value: '',
                        text: 'Select State',
                        disabled: true,
                        selected: true
                    }));

                    let statesLoaded = false; // Track if states are loaded

                    $.each(data.states, function(index, state) {
                        if (state.country_id === countryId) {
                            stateSelect.append($('<option>', {
                                value: state.id,
                                text: state.name
                            }));
                            statesLoaded = true; // Set to true if at least one state is added
                        }
                    });
                    stateSelect.select2();
                    resolve();
                }).fail(function() {
                    toastr.error('Error loading state data');
                    reject();
                });
            });
        }


        function loadCitiesForSelectedState(stateId) {
            return new Promise((resolve, reject) => {
                $.getJSON('/cities.json', function(data) {
                    const citySelect = $('#city');
                    citySelect.empty();
                    citySelect.append($('<option>', {
                        value: '',
                        text: 'Select City',
                        disabled: true,
                        selected: true
                    }));
                    $.each(data.cities, function(index, city) {
                        if (city.state_id === stateId) {
                            citySelect.append($('<option>', {
                                value: city.id,
                                text: city.name
                            }));
                        }
                    });
                    citySelect.select2({
                        placeholder: "Select City",

                    });
                    resolve();
                }).fail(function() {
                    toastr.error('Error loading city data');
                    reject();
                });
            });
        }


    });

   // Reusable validation function
    function validateField(field) {
        let isValid = true;
        let errorMessages = {
            'app_name': 'App Name is required.',
            'company_name': 'Company Name is required.',
            'phone_no': 'Phone Number is required and must be 10-12 digits.',
            'site_email': 'Valid Email is required.',
            'fax_no': 'Fax Number is required.',
            'website': 'Website is required and must be valid.',
            'timezone': 'Timezone is required.',
            'site_address': 'Address is required and must not exceed 150 characters.',
            'country': 'Country is required.',
            'state': 'State/Province is required.',
            'city': 'City is required.',
            'postal_code': 'Postal Code is required and must be 5-6 alphanumeric characters.',
        };

        let fieldName = field.attr('name');
        let value = field.val() ? field.val().trim() : '';

        $("#" + fieldName + "_error").text("");
        field.removeClass("is-invalid");

        if (errorMessages[fieldName] && value === '') {
            isValid = false;
            field.addClass("is-invalid");
            $("#" + fieldName + "_error").text(errorMessages[fieldName]);
            return isValid;
        }

        if (fieldName === 'phone_no' && !/^\d{10,12}$/.test(value)) {
            isValid = false;
            field.addClass("is-invalid");
            $("#" + fieldName + "_error").text('Phone Number must be 10-12 digits.');
        }

        if (fieldName === 'site_email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            isValid = false;
            field.addClass("is-invalid");
            $("#" + fieldName + "_error").text('Enter a valid email address.');
        }

        if (fieldName === 'website' && !/^(https?:\/\/)?(www\.)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/\S*)?$/.test(value)) {
            isValid = false;
            field.addClass("is-invalid");
            $("#" + fieldName + "_error").text('Enter a valid website URL (e.g., https://www.example.com).');
        }

        if (fieldName === 'site_address' && value.length > 150) {
            isValid = false;
            field.addClass("is-invalid");
            $("#" + fieldName + "_error").text('Address must not exceed 150 characters.');
        }

        if (fieldName === 'postal_code' && !/^[a-zA-Z0-9]{5,6}$/.test(value)) {
            isValid = false;
            field.addClass("is-invalid");
            $("#" + fieldName + "_error").text('Postal Code must be 5-6 alphanumeric characters.');
        }

        if (fieldName === 'country' || fieldName === 'state' || fieldName === 'city') {
            if (value === '') {
                isValid = false;
                field.addClass("is-invalid");
                $("#" + fieldName + "_error").text(errorMessages[fieldName]);
            }
        }

        return isValid;
    }

    $('#generalSettingForm').on('change', 'input, select', function() {
        validateField($(this));
    });

    $('#generalSettingForm').on('submit', function(e) {
        e.preventDefault();

        let isValid = true;

        // Validate all inputs and selects
        $('#generalSettingForm').find('input, select').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });

        // If the form is invalid, stop submission
        if (!isValid) {
            return;
        }

        let formData = new FormData(this);
        formData.set('service_approval_status', $('#service_approval_status').is(':checked') ? 1 : 0);
        formData.set('provider_approval_status', $('#provider_approval_status').is(':checked') ? 1 : 0);

        $.ajax({
            url: '/api/admin/update-general-setting',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json',
            },
            beforeSend: function() {
                $('.general_setting_btn').attr('disabled', true);
                $('.general_setting_btn').html('<div class="spinner-border spinner-border-sm text-light" role="status"></div>');
            }
        })
        .done((response) => {
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid");
            $('.general_setting_btn').removeAttr("disabled").html("Update");

            if (response.code === 200) {
                // toastr.success(response.message);
                if (languageId === 2) {
                    loadJsonFile(response.message, function (langtst) {
                        toastr.success(langtst);
                    });
                }else{
                    toastr.success(response.message);
                }
            } else {
                toastr.error(response.message);
            }
        })
        .fail((error) => {
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid");
            $('.general_setting_btn').removeAttr("disabled").html("Update");

            if (error.status === 422) {
                $.each(error.responseJSON.errors, function(key, val) {
                    $(`[name="${key}"]`).addClass("is-invalid");
                    $("#" + key + "_error").text(val[0]);
                });
            } else {
                toastr.error(error.responseJSON.message || 'Error updating general settings');
            }
        });
    });
}

//otp-settings
if (pageValue === 'admin.otp-settings') {

    loadOtpSettings();

    function loadOtpSettings() {
        $.ajax({
            url: '/api/admin/index-invoice-setting',
            type: 'POST',
            data: {'group_id': 9},
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.code === 200) {
                    const requiredKeys = ['otp_type', 'otp_digit_limit', 'otp_expire_time', 'register', 'login'];
                    const filteredSettings = response.data.settings.filter(setting => requiredKeys.includes(setting.key));

                    // Update values in Blade file
                    filteredSettings.forEach(setting => {
                        const element = $('#' + setting.key);
                        if (setting.key === 'otp_type') {
                            const selectedTypes = Array.isArray(setting.value) ? setting.value : setting.value.split(',');
                            $('#otp_type').val(selectedTypes).trigger('change');
                        } else if (setting.key === 'register' || setting.key === 'login') {
                            $('#' + setting.key).prop('checked', setting.value === '1');
                        } else if (element.is('select')) {
                            element.val(setting.value).change();
                        } else {
                            element.val(setting.value);
                        }
                    });
                }
                $(".label-loader, .input-loader").hide();
                $('.real-label, .real-input').removeClass('d-none');
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    }

    $('#otpsettingform').submit(function(event) {
        event.preventDefault();

        $(".error-text").text("");
        $(".form-control").removeClass("is-invalid");


        let isValid = true;


        if ($('#otp_type').val() === '') {
            $('#otp_type').addClass('is-invalid');
            $('#otp_type_error').text('OTP Type is required.');
            isValid = false;
        }

        if (!isValid) {
            return;
        }

        let formData = new FormData();
        formData.append('otp_type', $('#otp_type').val());
        formData.append('otp_digit_limit', $('#otp_digit_limit').val());
        formData.append('otp_expire_time', $('#otp_expire_time').val());
        formData.append('group_id', $('#group_id').val());
        formData.append('register', $('#register').is(':checked') ? 1 : 0);
        formData.append('login', $('#login').is(':checked') ? 1 : 0);

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
                $('.otp_save_btn').attr('disabled', true).html('<div class="spinner-border text-light" role="status"></div>');
            },
        }).done((response) => {
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid");
            $(".otp_save_btn").removeAttr("disabled");
            $(".otp_save_btn").html("Update");
            if (response.code === 200) {
                // toastr.success(response.message);
                if (languageId === 2) {
                    loadJsonFile(response.message, function (langtst) {
                        toastr.success(langtst);
                    });
                }else{
                    toastr.success(response.message);
                }
                loadOtpSettings();
            } else {
                toastr.error(response.message);
            }
        }).fail((error) => {
            $('.otp_save_btn').removeAttr('disabled').html('Update');
            if (error.status == 422) {
                $.each(error.responseJSON.errors, function (key, val) {
                    $("#" + key).addClass("is-invalid");
                    $("#" + key + "_error").text(val[0]);
                });
            } else {
                toastr.error(error.responseJSON.message, "bg-danger");
            }
        });
    });

}

if (pageValue === 'admin.dt-settings') {

    $('.select2').select2();
    loadLocalizationSettings();

    $('#booking_prefix').on('input', function() {
        let value = $(this).val();
        value = value.replace(/[^a-zA-Z]/g, '');
        value = value.substring(0, 10);
        $(this).val(value);
    });

    function loadLocalizationSettings() {
        $.ajax({
            url: '/api/admin/index-invoice-setting',
            type: 'POST',
            data: {'group_id': 31},
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.code === 200) {
                    const requiredKeys = ['date_format_view', 'time_format_view', 'timezone_format_view', 'booking_prefix'];
                    const filteredSettings = response.data.settings.filter(setting => requiredKeys.includes(setting.key));

                    // Update values in Blade file
                    filteredSettings.forEach(setting => {
                        const element = $('#' + setting.key);
                        if (element.is('select')) {
                            element.val(setting.value).change();
                        } else {
                            element.val(setting.value);
                        }
                    });
                }
                $(".label-loader, .input-loader").hide();
                $('.real-label, .real-input').removeClass('d-none');
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    }

    $('#dtsettingform').submit(function(event) {
        event.preventDefault();

        $(".error-text").text("");
        $(".form-control").removeClass("is-invalid");


        let isValid = true;


        if ($('#otp_type').val() === '') {
            $('#otp_type').addClass('is-invalid');
            $('#otp_type_error').text('OTP Type is required.');
            isValid = false;
        }

        if (!isValid) {
            return;
        }

        let formData = new FormData();
        formData.append('date_format_view', $('#date_format_view').val());
        formData.append('time_format_view', $('#time_format_view').val());
        formData.append('timezone_format_view', $('#timezone_format_view').val());
        formData.append('booking_prefix', $('#booking_prefix').val());
        formData.append('group_id', 31);

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
                $('.dt_save_btn').attr('disabled', true).html('<div class="spinner-border text-light" role="status"></div>');
            },
        }).done((response) => {
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid");
            $(".dt_save_btn").removeAttr("disabled");
            $(".dt_save_btn").html($('.dt_save_btn').data('update'));
            if (response.code === 200) {
                toastr.success(response.message);
                loadOtpSettings();
            } else {
                toastr.error(response.message);
            }
        }).fail((error) => {
            $('.dt_save_btn').removeAttr('disabled').html($('.dt_save_btn').data('update'));
            if (error.status == 422) {
                $.each(error.responseJSON.errors, function (key, val) {
                    $("#" + key).addClass("is-invalid");
                    $("#" + key + "_error").text(val[0]);
                });
            } else {
                toastr.error(error.responseJSON.message, "bg-danger");
            }
        });
    });

}

if (pageValue === 'admin.search-settings') {

    $('.select2').select2();
    loadOtpSettings();

    function loadOtpSettings() {
        $.ajax({
            url: '/api/admin/index-invoice-setting',
            type: 'POST',
            data: {'group_id': 32},
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.code === 200) {
                    const requiredKeys = ['milesradious', 'goglemapkey'];
                    const filteredSettings = response.data.settings.filter(setting => requiredKeys.includes(setting.key));

                    // Update values in Blade file
                    filteredSettings.forEach(setting => {
                        const element = $('#' + setting.key);
                        if (setting.key === 'milesradious') {
                            $('#milesradius').val(setting.value);
                        }  else if (setting.key === 'goglemapkey') {
                            $('#goe_key').val(setting.value);
                        } else {
                            element.val(setting.value);
                        }
                    });
                }
                $(".label-loader, .input-loader").hide();
                $('.real-label, .real-input').removeClass('d-none');
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    }

    $('#searchsettingform').submit(function(event) {
        event.preventDefault();

        $(".error-text").text("");
        $(".form-control").removeClass("is-invalid");


        let isValid = true;

        let formData = new FormData();
        formData.append('goe_key', $('#goe_key').val());
        formData.append('milesradius', $('#milesradius').val());
        formData.append('group_id', $('#group_id').val());

        $.ajax({
            url: "/api/admin/update-search-setting",
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
                $('.dt_save_btn').attr('disabled', true).html('<div class="spinner-border text-light" role="status"></div>');
            },
        }).done((response) => {
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid");
            $(".dt_save_btn").removeAttr("disabled");
            $(".dt_save_btn").html($('.dt_save_btn').data('update'));
            if (response.code === 200) {
                toastr.success(response.message);
                loadOtpSettings();
            } else {
                toastr.error(response.message);
            }
        }).fail((error) => {
            $('.dt_save_btn').removeAttr('disabled').html($('.dt_save_btn').data('update'));
            if (error.status == 422) {
                $.each(error.responseJSON.errors, function (key, val) {
                    $("#" + key).addClass("is-invalid");
                    $("#" + key + "_error").text(val[0]);
                });
            } else {
                toastr.error(error.responseJSON.message, "bg-danger");
            }
        });
    });

}

//cookies-settings
if (pageValue === 'admin.cookies-settings') {
    function loadCookiesSettings(langId = '') {
        $.ajax({
            url: '/api/admin/index-invoice-setting',
            type: 'POST',
            data: {
                'group_id': 10,
                language_id: langId
             },
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.code === 200) {
                    // Extracting the language code from the keys in the response
                    const languageCode = response.data.settings.length > 0 ? response.data.settings[0].key.split('_').pop() : '';

                    response.data.settings.forEach(setting => {
                        // Remove the language code suffix to get the original key
                        const baseKey = setting.key.replace(`_${languageCode}`, '');
                        const element = $('#' + baseKey);

                        if (element.is('select')) {
                            element.val(setting.value).change();
                        } else if (element.is(':checkbox')) {
                            element.prop('checked', setting.value == 1);
                        } else if (baseKey === 'cookies_content_text') {
                            $('#summernote').summernote('code', setting.value);
                        } else {
                            element.val(setting.value);
                        }
                    });
                }
                $(".label-loader, .input-loader").hide();
                $('.real-label, .real-input').removeClass('d-none');
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    }

    $(document).ready(function() {
        loadCookiesSettings($('#language_id').val());

        $('#language_id').on('change', function() {
            loadCookiesSettings($(this).val());
        });
    });

    function validateCookiesField(field) {
        let isValid = true;
        let errorMessages = {
            'group_id': 'Group ID is required.',
            'cookies_position': 'Cookies position is required.',
            'agree_button_text': 'Agree button text is required.',
            'decline_button_text': 'Decline button text is required.',
            'lin_for_cookies_page': 'Link for cookies page is required.',
        };

        let fieldName = field.attr('name');
        let value = field.val().trim();

        $("#" + fieldName + "_error").text("");
        field.removeClass("is-invalid");

        if (errorMessages[fieldName] && (value === '' || value === null)) {
            isValid = false;
            field.addClass("is-invalid");
            $("#" + fieldName + "_error").text(errorMessages[fieldName]);
        }

        if (fieldName === 'lin_for_cookies_page' && value.length > 0 && !/^https?:\/\/[^\s$.?#].[^\s]*$/.test(value)) {
            isValid = false;
            field.addClass("is-invalid");
            $("#" + fieldName + "_error").text('Please enter a valid URL.');
        }

        return isValid;
    }

    $('#cookies_setting_form').on('change', 'input, select', function() {
        validateCookiesField($(this));
    });

    $('#cookies_setting_form').submit(function(event) {
        event.preventDefault();

        let isValid = true;

        $('#cookies_setting_form').find('input, select').each(function() {
            if (!validateCookiesField($(this))) {
                isValid = false;
            }
        });

        let summernoteContent = $('#summernote').summernote('code').trim();
        if (!summernoteContent) {
            isValid = false;
            $('#summernote').addClass("is-invalid");
            $("#summernote_error").text("Cookies content is required.");
        } else {
            $('#summernote').removeClass("is-invalid");
            $("#summernote_error").text("");
        }

        if (!isValid) {
            return;
        }

        let formData = new FormData();
        formData.append('group_id', $('#group_id').val());
        formData.append('cookies_content_text', summernoteContent);
        formData.append('cookies_position', $('#cookies_position').val());
        formData.append('agree_button_text', $('#agree_button_text').val());
        formData.append('decline_button_text', $('#decline_button_text').val());
        formData.append('show_decline_button', $('#show_decline_button').is(':checked') ? 1 : 0);
        formData.append('lin_for_cookies_page', $('#lin_for_cookies_page').val());
        formData.append('language_id', $('#language_id').val());

        $.ajax({
            url: "/api/admin/update-cookies-info-setting",
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
            beforeSend: function() {
                $('.cookies_update_btn').attr('disabled', true).html('<div class="spinner-border text-light" role="status"></div>');
            },
        }).done((response) => {
            $('.cookies_update_btn').removeAttr('disabled').html('Save');
            if (response.code === 200) {
                // toastr.success(response.message);
                if (languageId === 2) {
                    loadJsonFile(response.message, function (langtst) {
                        toastr.success(langtst);
                    });
                }else{
                    toastr.success(response.message);
                }
            } else {
                toastr.error(response.message);
            }
        }).fail((error) => {
            $('.cookies_update_btn').removeAttr('disabled').html('Save');
            if (error.status == 422) {
                $.each(error.responseJSON.errors, function(key, val) {
                    $("#" + key).addClass("is-invalid");
                    $("#" + key + "_error").text(val[0]);
                });
            } else {
                toastr.error(error.responseJSON.message, "bg-danger");
            }
        });
    });

    $('#language_id').on('change', function() {
        var langId = $(this).val();

        languageTranslate(langId);
        loadCookiesSettings(langId);
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

                    $('.cookies_settings').text(trans.cookies_settings);
                    $('.dashboard').text(trans.dashboard);
                    $('.Settings').text(trans.Settings);
                    $('.save').text(trans.save);
                    $('.cookies_content').text(trans.cookies_content);
                    $('.enter_cookies_content').text(trans.enter_cookies_content);
                    $('.lang_title').text(trans.available_translations);
                    $('.cook_po').text(trans.cookies_position);
                    $('.po_right').text(trans.right);
                    $('.po_left').text(trans.left);
                    $('.po_center').text(trans.center);
                    $('.aggree_txt').text(trans.agree_button_text);
                    $('.decline_txt').text(trans.decline_button_text);
                    $('.show_text').text(trans.show_decline_button);
                    $('.lint_txt').text(trans.link_for_cookies_page);

                }

            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }
}

//copyright-settings
if (pageValue === 'admin.copyright-settings') {
    function loadCopyrightSettings(langId = '') {
        $.ajax({
            url: '/api/admin/index-invoice-setting',
            type: 'POST',
            data: {
                'group_id': 8,
                language_id: langId
             },
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.code === 200) {
                    const settings = response.data.settings;

                    if (settings && Object.keys(settings).length > 0) {
                        $('#summernote').summernote('code', settings.value);
                    } else {
                        $('#summernote').summernote('code', '');
                    }
                }
                $(".label-loader, .input-loader").hide();
                $('.real-label, .real-input').removeClass('d-none');
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error('An error occurred:', xhr); // Log other errors
                }
            }
        });
    }

    $(document).ready(function() {
        loadCopyrightSettings($('#language_id').val());

        $('#language_id').on('change', function() {
            loadCopyrightSettings($(this).val());
        });
    });



    function validateField(field) {
        let isValid = true;
        let fieldName = field.attr('name');
        let value = field.val().trim();

        // Clear previous errors
        $("#" + fieldName + "_error").text("");
        field.removeClass("is-invalid");

        // Field-specific validations
        if (fieldName === 'copyright' && (value === '' || value === null)) {
            isValid = false;
            field.addClass("is-invalid");
            $("#" + fieldName + "_error").text("Copyright field is required.");
        }

        return isValid;
    }

    // Validate on field change
    $('#copyright_setting_form').on('change', 'textarea, input', function () {
        validateField($(this));
    });

    // Form submit handler
    $('#copyright_setting_form').submit(function (event) {
        event.preventDefault();

        let isValid = true;

        // Validate all form fields
        $('#copyright_setting_form').find('textarea, input').each(function () {
            if (!validateField($(this))) {
                isValid = false;
            }
        });

        if (!isValid) {
            return;
        }

        // Prepare form data
        let formData = new FormData(this);
        formData.append('group_id', 8);

        $.ajax({
            url: "/api/admin/update-copyright-setting",
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
                $('.copyright_update_btn').attr('disabled', true).html('<div class="spinner-border text-light" role="status"></div>');
            },
        }).done((response) => {
            $('.copyright_update_btn').removeAttr('disabled').html('Update');
            if (response.code === 200) {
                // toastr.success(response.message);
                if (languageId === 2) {
                    loadJsonFile(response.message, function (langtst) {
                        toastr.success(langtst);
                    });
                }else{
                    toastr.success(response.message);
                }
            } else {
                toastr.error(response.message);
            }
        }).fail((error) => {
            $('.copyright_update_btn').removeAttr('disabled').html('Update');
            if (error.status === 422) {
                $.each(error.responseJSON.errors, function (key, val) {
                    $("." + key).addClass("is-invalid");
                    $("#" + key + "_error").text(val[0]);
                });
            } else {
                toastr.error(error.responseJSON.message || 'Error updating copyright settings');
            }
        });
    });

    $('#language_id').on('change', function() {
        var langId = $(this).val();

        languageTranslate(langId);
        loadCopyrightSettings(langId);
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

                    $('.dashboard').text(trans.dashboard);
                    $('.copyright_settings').text(trans.copyright_settings);
                    $('.Settings').text(trans.Settings);
                    $('.update').text(trans.update);
                    $('.Copyright').text(trans.Copyright);
                    $('.lang_title').text(trans.available_translations);

                }

            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }
}

if (pageValue === 'admin.maintenance-settings') {
    loadMaintenanceSettings();

    function loadMaintenanceSettings() {
        $.ajax({
            url: '/api/admin/index-invoice-setting',
            type: 'POST',
            data: { 'group_id': 11 },
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.code === 200) {
                    const requiredKeys = ['maintenance', 'maintenance_content']; // Include 'maintenance_content'
                    const filteredSettings = response.data.settings.filter(setting => requiredKeys.includes(setting.key));

                    filteredSettings.forEach(setting => {
                        const element = $('#' + setting.key);

                        if (element.is('select')) {
                            element.val(setting.value).change();  // Set value for select
                        } else if (element.is(':checkbox')) {
                            element.prop('checked', setting.value == 1);  // Set checkbox state
                        } else if (element.is('textarea')) {
                            element.val(setting.value);  // Set value for textarea
                        } else {
                            element.val(setting.value);  // Set value for input fields
                        }
                    });

                    // Load maintenance content into Summernote if applicable
                    const maintenanceContentSetting = filteredSettings.find(setting => setting.key === 'maintenance_content');
                    if (maintenanceContentSetting) {
                        $('#summernote').summernote('code', maintenanceContentSetting.value);
                    }

                }
                $(".label-loader, .input-loader").hide();
                $('.real-label, .real-input').removeClass('d-none');
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    }

    $('#maintenance_setting_form').submit(function(event) {
        event.preventDefault();

        let formData = new FormData();
        formData.append('group_id', $('#group_id').val());
        formData.append('maintenance', $('#maintenance').is(':checked') ? 1 : 0);
        formData.append('maintenance_content', $('#summernote').summernote('code')); // Add maintenance content

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
                $('.maintenance_update_btn').attr('disabled', true).html('<div class="spinner-border text-light" role="status"></div>');
            },
        }).done((response) => {
            $('.maintenance_update_btn').removeAttr('disabled').html('Save');
            if (response.code === 200) {
                // toastr.success(response.message);
                if (languageId === 2) {
                    loadJsonFile(response.message, function (langtst) {
                        toastr.success(langtst);
                    });
                }else{
                    toastr.success(response.message);
                }
                loadMaintenanceSettings();
            } else {
                toastr.error(response.message);
            }
        }).fail((error) => {
            $('.maintenance_update_btn').removeAttr('disabled').html('Save');
            if (error.status == 422) {
                $.each(error.responseJSON.errors, function (key, val) {
                    $("#" + key).addClass("is-invalid");
                    $("#" + key + "_error").text(val[0]);
                });
            } else {
                toastr.error(error.responseJSON.message, "bg-danger");
            }
        });
    });
}

//logo-settings
if (pageValue === 'admin.logo-settings'){
    $(document).ready(function() {
        loadLogoSettings();

        function loadLogoSettings() {
            $.ajax({
                url: '/api/admin/index-logo-setting',
                type: 'POST',
                data: {'group_id': 6},
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.code === 200) {
                        const settings = response.data.settings;

                        settings.forEach(setting => {
                            const imagePath = setting.value;
                            switch (setting.key) {
                                case 'site_logo':
                                    $('#logo-preview').attr('src', imagePath);
                                    break;
                                case 'site_favicon':
                                    $('#favicon-preview').attr('src', imagePath);
                                    break;
                                case 'site_mobile_icon':
                                    $('#mobile-icon-preview').attr('src', imagePath);
                                    break;
                                case 'site_icon':
                                    $('#icon-preview').attr('src', imagePath);
                                    break;
                                case 'site_dark_logo':
                                    $('#dark-logo-preview').attr('src', imagePath);
                                    break;
                            }
                        });
                    }
                    $(".label-loader, .input-loader").hide();
                    $('.real-label, .real-input').removeClass('d-none');
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                        toastr.error(xhr.responseJSON.message);
                    }
                }
            });
        }

        $('.image-sign').on('change', function(event) {
            var input = event.target;
            var previewId = $(input).data('preview');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#' + previewId).attr('src', e.target.result);
                };
                reader.readAsDataURL(input.files[0]);
            }
        });

        $('#logoSettingForm').submit(function(event) {
            event.preventDefault();


            $('#logo, #favicon, #icon, #dark_logo').removeClass('is-invalid');
            $('#logo_error, #favicon_error, #icon_error, #dark_logo_error').text('');

            const maxFileSize = 2048 * 1024;
            const validFileTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/jpg'];
            const files = [
                { id: 'logo', file: $('#logo')[0].files[0], name: 'Logo' },
                { id: 'favicon', file: $('#favicon')[0].files[0], name: 'Favicon' },
                { id: 'icon', file: $('#icon')[0].files[0], name: 'Icon' },
                { id: 'mobile_icon', file: $('#mobile_icon')[0].files[0], name: 'Mobile Icon' },
                { id: 'dark_logo', file: $('#dark_logo')[0].files[0], name: 'Dark Logo' },
            ];

            let isValid = true;
            let errorMessages = [];

            files.forEach(({ id, file, name }) => {
                if (file) {
                    if (file.size > maxFileSize) {
                        errorMessages.push(`${name}: File size must not exceed 2 MB.`);
                        isValid = false;
                    } else if (!validFileTypes.includes(file.type)) {
                        errorMessages.push(`${name}: Invalid file type. Only JPG, PNG, GIF, or SVG are allowed.`);
                        isValid = false;
                    }
                }
            });

            // If validation fails, show errors in Toastr and exit
            if (!isValid) {
                toastr.error(errorMessages.join('<br>'), 'Validation Error');
                return;
            }

            // Prepare FormData for submission
            let formData = new FormData();
            formData.append('group_id', 6);
            formData.append('logo', $('#logo')[0].files[0] ?? null);
            formData.append('favicon', $('#favicon')[0].files[0] ?? null);
            formData.append('icon', $('#icon')[0].files[0] ?? null);
            formData.append('mobile_icon', $('#mobile_icon')[0].files[0] ?? null);
            formData.append('dark_logo', $('#dark_logo')[0].files[0] ?? null);

            // Submit via AJAX
            $.ajax({
                url: "/api/admin/update-logo-setting",
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
                beforeSend: function() {
                    $('.general_setting_btn').attr('disabled', true).html('<div class="spinner-border text-light" role="status"></div>');
                },
            }).done((response) => {
                $('.general_setting_btn').removeAttr('disabled').html('Update');
                if (response.code === 200) {
                    // toastr.success(response.message);
                    if (languageId === 2) {
                        loadJsonFile(response.message, function (langtst) {
                            toastr.success(langtst);
                        });
                    }else{
                        toastr.success(response.message);
                    }
                    loadLogoSettings(); // Reload updated images
                    setTimeout(function () {
                        location.reload();
                    }, 500);
                } else {
                    toastr.error(response.message);
                }
            }).fail((error) => {
                $('.general_setting_btn').removeAttr('disabled').html('Update');
                toastr.error("Error updating settings", "Error");

                if (error.status === 422) {
                    $.each(error.responseJSON.errors, function(key, val) {
                        toastr.error(val[0], `${key.replace('_', ' ').toUpperCase()} Error`);
                    });
                } else {
                    toastr.error(error.responseJSON.message || "Unknown error occurred", "Error");
                }
            });
        });

    });
}

//bread-image-settings
if (pageValue === 'admin.bread-image-settings'){

    $(document).ready(function() {
        loadBreadImageSettings();
        function loadBreadImageSettings() {
            $.ajax({
                url: '/api/admin/index-bread-image-setting',
                type: 'POST',
                data: { 'group_id': 7},
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.code === 200) {
                        const settings = response.data.settings;

                        settings.forEach(setting => {
                            const imagePath = setting.value;
                            if (setting.key === 'bread_image') {
                                $('#bread-image-preview').attr('src', imagePath);
                            }
                        });
                    }
                    $(".label-loader, .input-loader").hide();
                    $('.real-label, .real-input').removeClass('d-none');
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                        toastr.error(xhr.responseJSON.message);
                    }
                }
            });
        }

        $('.image-sign').on('change', function(event) {
            var input = event.target;
            var previewId = 'bread-image-preview';
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#' + previewId).attr('src', e.target.result);
                };
                reader.readAsDataURL(input.files[0]);
            }
        });

        $('#breadImageForm').submit(function(event) {
            event.preventDefault();

            $('#bread_image').removeClass('is-invalid');
            $('#bread_image_error').text('');

            const maxFileSize = 2048 * 1024;
            const breadImage = $('#bread_image')[0].files[0];
            let isValid = true;
            let errorMessages = '';

            if (breadImage) {
                if (breadImage.size > maxFileSize) {
                    $('#bread_image').addClass('is-invalid');
                    $('#bread_image_error').text('File size must not exceed 2 MB.');
                    errorMessages += `Bread Image: File size must not exceed 2 MB.<br>`;
                    isValid = false;
                } else if (!['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'].includes(breadImage.type)) {
                    $('#bread_image').addClass('is-invalid');
                    $('#bread_image_error').text('Invalid file type. Only JPG, PNG, GIF, or SVG are allowed.');
                    errorMessages += `Bread Image: Invalid file type. Only JPG, PNG, GIF, or SVG are allowed.<br>`;
                    isValid = false;
                }
            }

            if (!isValid) {
                toastr.error(errorMessages, 'Validation Error');
                return;
            }

            let formData = new FormData();
            formData.append('group_id', 7);
            formData.append('bread_image', breadImage);

            $.ajax({
                url: "/api/admin/update-bread-image-setting",
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
                beforeSend: function() {
                    $('.general_setting_btn').attr('disabled', true).html('<div class="spinner-border text-light" role="status"></div>');
                },
            }).done((response) => {
                $('.general_setting_btn').removeAttr('disabled').html('Update');
                if (response.code === 200) {
                    // toastr.success(response.message);
                    if (languageId === 2) {
                        loadJsonFile(response.message, function (langtst) {
                            toastr.success(langtst);
                        });
                    }else{
                        toastr.success(response.message);
                    }
                    loadBreadImageSettings(); // Reload settings to update the preview
                } else {
                    toastr.error(response.message);
                }
            }).fail((error) => {
                $('.general_setting_btn').removeAttr('disabled').html('Update');
                toastr.error("Error updating settings", "Error");

                if (error.status === 422) {
                    $.each(error.responseJSON.errors, function(key, val) {
                        $("#" + key).addClass("is-invalid");
                        $("#" + key + "_error").text(val[0]);
                    });
                } else {
                    toastr.error(error.responseJSON.message, "Error");
                }
            });
        });
    });
}

if (pageValue === 'admin.preference') {
    loadLeadsSetting();
    function loadLeadsSetting() {
        $.ajax({
            url: '/api/admin/index-invoice-setting',
            type: 'POST',
            data: { 'group_id': 12 },
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function (response) {
                if (response.code === 200) {
                    const requiredKeys = ['leads_status', 'service_status', 'product_status', 'paymentmode_status'];
                    const filteredSettings = response.data.settings.filter(setting => requiredKeys.includes(setting.key));

                    filteredSettings.forEach(setting => {
                        const element = $('#' + setting.key);
                       if (setting.key === 'paymentmode_status') {
                            $('input[name="paymentmode_status"][value="' + setting.value + '"]')
                                .prop('checked', true);
                        } else {
                            const element = $('#' + setting.key);
                            if (element.is(':checkbox')) {
                                element.prop('checked', setting.value == 1);
                            } else {
                                element.val(setting.value);
                            }
                        }
                    });

                }
                $(".label-loader, .input-loader").hide();
                $('.real-label, .real-input').removeClass('d-none');
            },
            error: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    }

    $('#product_setting_form').submit(function (event) {
        event.preventDefault();

        let formData = new FormData();
        formData.append('group_id', $('#group_id').val());
        formData.append('leads_status', $('#leads_status').is(':checked') ? 1 : 0);
        formData.append('service_status', $('#service_status').is(':checked') ? 1 : 0);
        formData.append('product_status', $('#product_status').is(':checked') ? 1 : 0);
        formData.append(
            'paymentmode_status',
            $('input[name="paymentmode_status"]:checked').val() || 1
        );

        $.ajax({
            url: "/api/admin/update-preference-setting",
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
                $('.leads_setting_btn').attr('disabled', true).html('<div class="spinner-border text-light" role="status"></div>');
            },
        }).done((response) => {
            $('.leads_setting_btn').removeAttr('disabled').html('Save');
            if (response.code === 200) {
                // toastr.success(response.message);
                if (languageId === 2) {
                    loadJsonFile(response.message, function (langtst) {
                        toastr.success(langtst);
                    });
                } else {
                    toastr.success(response.message);
                }
                loadLeadsSetting();
            } else {
                toastr.error(response.message);
            }
        }).fail((error) => {
            $('.leads_setting_btn').removeAttr('disabled').html('Save');
            if (error.status == 422) {
                $.each(error.responseJSON.errors, function (key, val) {
                    $("#" + key).addClass("is-invalid");
                    $("#" + key + "_error").text(val[0]);
                });
            } else {
                toastr.error(error.responseJSON.message, "bg-danger");
            }
        });
    });

}

//apperance-settings
if (pageValue === 'settings.apperance-settings') {
    loadAppearanceSettings();

    function loadAppearanceSettings() {
        $.ajax({
            url: '/api/admin/index-invoice-setting',
            type: 'POST',
            data: { 'group_id': 15 }, // Group ID for appearance settings
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.code === 200) {
                    const requiredKeys = ['primary_color', 'secondary_color', 'button_color', 'button_hover_color'];
                    const filteredSettings = response.data.settings.filter(setting => requiredKeys.includes(setting.key));

                    filteredSettings.forEach(setting => {
                        const element = $('#' + setting.key);
                        if (element.is('input[type="color"]')) {
                            element.val(setting.value); // Set color input values
                        }
                    });
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    }

    $('#apperance_setting_form').submit(function(event) {
        event.preventDefault();

        let formData = new FormData();
        formData.append('group_id', $('#group_id').val());
        formData.append('primary_color', $('#primary_color').val());
        formData.append('secondary_color', $('#secondary_color').val());
        formData.append('button_color', $('#button_color').val());
        formData.append('button_hover_color', $('#button_hover_color').val());

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
                $('.apperance_setting_update_btn').attr('disabled', true).html('<div class="spinner-border text-light" role="status"></div>');
            },
        }).done((response) => {
            $('.apperance_setting_update_btn').removeAttr('disabled').html('Save');
            if (response.code === 200) {
                // toastr.success(response.message);
                if (languageId === 2) {
                    loadJsonFile(response.message, function (langtst) {
                        toastr.success(langtst);
                    });
                }else{
                    toastr.success(response.message);
                }
                loadAppearanceSettings(); // Reload appearance settings if needed
            } else {
                toastr.error(response.message);
            }
        }).fail((error) => {
            $('.apperance_setting_update_btn').removeAttr('disabled').html('Save');
            if (error.status == 422) {
                $.each(error.responseJSON.errors, function (key, val) {
                    $("#" + key).addClass("is-invalid");
                    $("#" + key + "_error").text(val[0]);
                });
            } else {
                toastr.error(error.responseJSON.message, "bg-danger");
            }
        });
    });
}

//custom-settings
if (pageValue === 'settings.custom-settings') {
    $(document).ready(function() {
        loadCustomSettings();
    });

    function loadCustomSettings() {
        $.ajax({
            url: '/api/admin/index-custom-setting',
            type: 'POST',
            data: { 'group_id': 16 },
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.code === 200) {
                    const requiredKeys = ['custom_setting_content', 'custom_setting_content1'];
                    const filteredSettings = response.data.settings.filter(setting => requiredKeys.includes(setting.key));

                    filteredSettings.forEach(setting => {
                        $('#' + setting.key).val(setting.value);
                    });
                }
                $(".label-loader, .input-loader").hide();
                $('.real-label, .real-input').removeClass('d-none');
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    }

    $('#custom_setting_form').submit(function(event) {
        event.preventDefault();

        let formData = new FormData();
        formData.append('group_id', $('#group_id').val());
        formData.append('custom_setting_content', $('#custom_setting_content').val());
        formData.append('custom_setting_content1', $('#custom_setting_content1').val());

        $.ajax({
            url: "/api/admin/update-custom-setting",
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
                $('.custom_setting_update_btn').attr('disabled', true).html('<div class="spinner-border text-light" role="status"></div>');
            },
        }).done((response) => {
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid");
            $(".custom_setting_update_btn").removeAttr("disabled");
            $(".custom_setting_update_btn").html("Save");
            if (response.code === 200) {
                // toastr.success(response.message);
                if (languageId === 2) {
                    loadJsonFile(response.message, function (langtst) {
                        toastr.success(langtst);
                    });
                }else{
                    toastr.success(response.message);
                }
                loadCustomSettings();
            } else {
                toastr.error(response.message);
            }
        }).fail((error) => {
            $('.custom_setting_update_btn').removeAttr('disabled').html('Save');
            if (error.status == 422) {
                $.each(error.responseJSON.errors, function (key, val) {
                    $("#" + key).addClass("is-invalid");
                    $("#" + key + "_error").text(val[0]);
                });
            } else {
                toastr.error(error.responseJSON.message, "bg-danger");
            }
        });
    });

}

//language-settings
if (pageValue === 'listkeywords' || pageValue === 'savelangword') {
    if (!$.fn.dataTable.isDataTable('#languagesTableList')) {
        $('#languagesTableList').DataTable({
            ordering: true,
            autoWidth: false
        });
    }
}

if (pageValue === 'admin.db-settings') {

    var langCode = $('body').data('lang');

    $(document).ready(function () {
        fetchBackups(1);
    });

    function fetchBackups(page) {
        $("#loader-table,.label-loader").show();
        $(".real-table, .real-label").addClass('d-none');
        $.ajax({
            url: '/api/admin/dbbacklist',
            type: 'POST',
            dataType: 'json',
            data: {
                order_by: 'desc',
                sort_by: 'id',
                page: page,
                search: $('#searchLanguage').val()
            },
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function (response) {
                if (response.code === '200') {
                    populateBackupTable(response.data, response.meta);
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
                        toastr.error('An error occurred while fetching languages.');
                    }
                } else {
                    toastr.error('An error occurred while fetching languages.');
                }
                toastr.error('Error fetching languages:', error);
            },
            complete:function(){
                $("#loader-table, .label-loader").hide();
                $(".real-table, .real-label").removeClass('d-none');
            }
        });
    }

    function populateBackupTable(languages, meta) {
        let tableBody = '';
        let isEnglish = '';

        if (languages.length > 0) {
            languages.forEach(language =>  {
                tableBody += `
                    <tr>
                        <td>${language.name}</td>
                        <td>${language.show_date} ${language.show_time}</td>

                        <td>
                        <li class="d-flex align-items-center" style="list-style: none;">
                        <a href="/download-backup/${language.id}"> <i class="ti ti-cloud-download fs-20 m-2"></i></a>

                        </li>
                        </td>
                    </tr>
                `;
            });
        } else {
            tableBody = `
                <tr>
                    <td colspan="5" class="text-center">No backups found</td>
                </tr>
            `;
        }

        $('#databaseTable tbody').html(tableBody);
        if ((languages.length != 0) && !$.fn.DataTable.isDataTable('#databaseTable')) {
            $('#databaseTable').DataTable({
                ordering: true,
                language: datatableLang
            });
        }
    }
}

if (pageValue === 'admin.language-settings') {

    var langCode = $('body').data('lang');

    $(document).ready(function () {
        fetchLanguages();
        
        $('#addLanguageForm').validate({
            rules: {
                translation_language_id: {
                    required: true,
                },
            },
            messages: {
                translation_language_id: {
                    required: $('#translation_language_id_error').data('required'),
                },
            },
            errorPlacement: function (error, element) {
                var errorId = element.attr("id");
                $("#" + errorId + "_error").text(error.text());
            },
            highlight: function (element) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element) {
                $(element).removeClass("is-invalid").addClass("is-valid");
            },
            onkeyup: function (element) {
                $(element).valid();
            },
            onchange: function (element) {
                $(element).valid();
            },
            submitHandler: function (form) {
                let formData = new FormData(form);

                $.ajax({
                    url: '/api/admin/languages/store',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                        'Accept': 'application/json'
                    },
                    beforeSend: function () {
                        $("#addLanguageBtn").attr("disabled", true).html(
                            '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                        );
                    },
                    success: function (response) {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $("#addLanguageBtn").removeAttr("disabled");
                        $('#addLanguageBtn').html($('#addLanguageBtn').data('save'));
                        if (response.success) {
                            toastr.success(response.message);
                            $('#add_language').modal('hide');
                            location.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (error) {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $("#addLanguageBtn").removeAttr("disabled");
                        $('#addLanguageBtn').html($('#addLanguageBtn').data('save'));
                        if (error.responseJSON.errors) {
                            $.each(error.responseJSON.errors, function (key, val) {
                                $("#" + key).addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                            });
                        } else {
                            toastr.error(error.responseJSON.message || 'An error occurred while adding the language.');
                        }
                    }
                });
            }
        });
    });

    function fetchLanguages() {
        $.ajax({
            url: '/api/admin/languages',
            type: 'POST',
            dataType: 'json',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function (response) {
                if (response.code == 200) {
                    populateLanguageTable(response.data, response.meta);
                }
            },
            error: function (error) {
                if (error.responseJSON.code == 500) {
                    toastr.error(error.responseJSON.message);
                } else {
                    toastr.error('An error occurred while fetching languages.');
                }
            }
        });
    }

    function populateLanguageTable(languages, meta) {
        let tableBody = '';
        let isEnglish = '';

        if (languages.length > 0) {
            languages.forEach(language =>  {
                tableBody += `
                    <tr>
                        <td>${language.name}</td>
                        <td>${language.code}</td>
                        ${ $('#has_permission').data('edit') == 1 ?
                        `<td>
                            <div class="form-check form-switch">
                                <input class="form-check-input status_change" data-id="${language.id}" data-status_type="rtl" type="checkbox" ${(language.direction == 'rtl') ? 'checked' : ''} role="switch">
                            </div>
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input status_change" data-id="${language.id}" data-status_type="default" type="checkbox" ${(language.is_default == '1') ? 'checked' : ''} ${(language.is_default == '1') ? 'disabled' : ''} role="switch">
                            </div>
                        </td>
                        <td>
                            <div class="form-check form-switch">
                                <input class="form-check-input status_change" data-id="${language.id}" data-status_type="status" type="checkbox" ${(language.status == '1') ? 'checked' : ''} ${(language.code == 'en') ? '' : ''} role="switch">
                            </div>
                        </td> ` : ''}
                         ${ $('#has_permission').data('visible') == 1 ?
                            `<td>
                                <li class="d-flex align-items-center">
                                    ${$('#has_permission').data('edit') == 1  ?
                                    `<a href="listwords/${language.id}"> 
                                        <i class="ti ti-plus fs-20 m-2"></i>
                                    </a> ` : ''}
                                    ${language.id != 1 && $('#has_permission').data('delete') == 1 ?
                                    `<a class="delete_language" href="#" data-bs-toggle="modal" data-bs-target="#delete-modal" data-id="${language.id}">
                                        <i class="ti ti-trash fs-20"></i>
                                    </a>` : ''}
                                </li>
                        </td>` : ''
                        }
                    </tr>
                `;
            });
        } else {
            tableBody = `
                <tr>
                    <td colspan="6" class="text-center">No languages found</td>
                </tr>
            `;
        }

        $('#languagesTable tbody').html(tableBody);

        $('#loader-table').addClass('d-none');
        $(".label-loader, .input-loader").hide();
        $('#languagesTable, .real-label, .real-input').removeClass('d-none');

        if (!$.fn.dataTable.isDataTable('#languagesTable')) {
            $('#languagesTable').DataTable({
                "ordering": true,
                "language": datatableLang
            });
        }
    }

    $(document).on('click', '.status_change', function(e) {
        e.preventDefault();

        let type = $(this).data('status_type');
        let selectedLanguageId = $(this).data('id');
        let status = $(this).is(':checked') ? 1 : 0;
        let isThis = $(this);

        $.ajax({
            url: '/admin/languages/set-default',
            type: 'POST',
            data: {
                id: selectedLanguageId,
                language_code: langCode,
                status: status,
                type: type
            },
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(function () {
                        location.reload();
                    }, 500);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    if (type == 'status') {
                        isThis.prop('checked', !status);
                    }
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error('Failed to set default language. Please try again.');
                }
            }
        });
    });

    $(document).on('click', '.delete_language', function(e) {
        e.preventDefault();
        var languageId = $(this).data('id');
        $('#confirmDelete').data('id', languageId);
    });

    $(document).on('click', '#confirmDelete', function(e) {
        e.preventDefault();

        var languageId = $(this).data('id');
        $.ajax({
            url: '/api/admin/languages/deleteLanguage',
            type: 'POST',
            data: {
                id: languageId,
                language_code: langCode
            },
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#delete-modal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                toastr.error('An error occurred while trying to delete the language.');
            }
        });
    });

}
//invoice-settings
if (pageValue === 'admin.invoice-settings') {
    $(document).ready(function() {

        $('#image_sign').on('change', function (event) {
            var input = event.target;
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#invoice_company_logo_image').attr('src', e.target.result); // Set the image source
                };

                reader.readAsDataURL(input.files[0]);
            }
        });

        loadGeneralSettings();

        function loadGeneralSettings() {
            $.ajax({
                url: '/api/admin/index-invoice-setting',
                type: 'POST',
                data: {'group_id': 2},
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.code === 200) {
                        const requiredKeys = ['invoice_prefix', 'invoice_starts', 'invoice_company_logo', 'invoice_company_name', 'invoice_header_terms', 'providerlogo', 'invoice_footer_terms'];

                        const filteredSettings = response.data.settings.filter(setting => requiredKeys.includes(setting.key));

                        filteredSettings.forEach(setting => {
                            if (setting.key === 'invoice_company_logo') {
                                const imagePath = setting.value;
                                if (imagePath == 'http:\/\/127.0.0.1:8000\/storage\/') {
                                    $('#invoice_company_logo_image').attr('src', "/assets/img/logo-small.svg");
                                } else {
                                    $('#invoice_company_logo_image').attr('src', imagePath);
                                }
                            } else if(setting.key === 'providerlogo') {
                                const checkbox = $('#providerlogo');
                                checkbox.prop('checked', setting.value == 1);
                            }
                             else {
                                $('#' + setting.key).val(setting.value);
                            }
                        });
                    }
                    $(".label-loader, .input-loader").hide();
                    $('.real-label, .real-input').removeClass('d-none');
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                        toastr.error(xhr.responseJSON.message);
                    }
                }
            });
        }

        $('#invoice_setting_form').submit(function(event) {
            event.preventDefault();

            // Validate form before submission
            if (!validateInvoiceForm()) {
                return; // Stop form submission if validation fails
            }

            let formData = new FormData();
            formData.append('invoice_logo', $('#image_sign')[0].files[0]); // Add image to formData

            // Add the other settings to formData
            formData.append('invoice_prefix', $('#invoice_prefix').val());
            formData.append('invoice_starts', $('#invoice_starts').val());
            formData.append('providerlogo', $('#providerlogo').is(':checked') ? 1 : 0);
            formData.append('group_id', $('#group_id').val());

            $.ajax({
                url: "/api/admin/update-invoice-setting",
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
                    $('.invoice_save_btn').attr('disabled', true);
                    $(".invoice_save_btn").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            }).done((response, statusText, xhr) => {
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid");
                $('.invoice_save_btn').removeAttr('disabled');
                $('.invoice_save_btn').html($('.invoice_save_btn').data('save'));
                if (response.code === 200) {
                    toastr.success(response.message);
                    // Reload the settings after successful update
                    loadGeneralSettings();
                } else {
                    toastr.error(response.message);
                }
            }).fail((error) => {
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid");
                $('.invoice_save_btn').removeAttr('disabled');
                $('.invoice_save_btn').html($('.invoice_save_btn').data('save'));

                if (error.status == 422) {
                    $.each(error.responseJSON.errors, function (key, val) {
                        $("#" + key).addClass("is-invalid");
                        $("#" + key + "_error").text(val[0]);
                    });
                } else {
                    toastr.error(error.responseJSON.message, "bg-danger");
                }
            });
        });

        function validateInvoiceForm() {
            let isValid = true;
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid");

            const maxFileSize = 5 * 1024 * 1024;
            const validFileTypes = ['image/jpeg', 'image/png', 'image/svg+xml'];

            const fileInput = $('#image_sign')[0].files[0];
            if (fileInput) {
                const fileSize = fileInput.size;
                const fileType = fileInput.type;

                if (fileSize > maxFileSize) {
                    $('#image_sign').addClass("is-invalid");
                    $('#image_sign_error').text($('#image_sign_error').data('image_size'));
                    isValid = false;
                } else if (!validFileTypes.includes(fileType)) {
                    $('#image_sign').addClass("is-invalid");
                    $('#image_sign_error').text($('#image_sign_error').data('image_format'));
                    isValid = false;
                }
            }

            if ($('#invoice_prefix').val().trim() === '') {
                $('#invoice_prefix').addClass("is-invalid");
                $('#invoice_prefix_error').text($('#invoice_prefix_error').data('empty'));
                isValid = false;
            }

            if ($('#invoice_starts').val().trim() === '') {
                $('#invoice_starts').addClass("is-invalid");
                $('#invoice_starts_error').text($('#invoice_starts_error').data('empty'));
                isValid = false;
            }

            return isValid;
        }

        $('#image_sign').on('change', function() {
            validateInvoiceForm();
        });

        $('#invoice_prefix').on('change', function() {
            validateInvoiceForm();
        });

        $('#invoice_starts').on('change', function() {
            validateInvoiceForm();
        });

        $('#invoice_company_name').on('change', function() {
            validateInvoiceForm();
        });

        $('#invoice_header_terms').on('change', function() {
            validateInvoiceForm();
        });

        $('#invoice_footer_terms').on('change', function() {
            validateInvoiceForm();
        });

    });
}

//invoice-template
if (pageValue === 'admin.invoice-template'){

    $(document).ready(function() {
        $('.summernote-add').summernote({
            height: 200
        });
        $('#summernote').summernote();

        loadInvoiceTemplates();
    });

    $('.add_placeholder_value').on('click', function() {
        var selectedContent = $(this).data('value');

        var summernoteEditor = $('.summernote-add');

        summernoteEditor.summernote('focus');
        summernoteEditor.summernote('editor.restoreRange');
        summernoteEditor.summernote('editor.insertText', selectedContent);
        summernoteEditor.summernote('editor.saveRange');
    });

    $('.placeholder_value').on('click', function() {
        var selectedContent = $(this).data('value');
        var summernoteEditor = $('#summernote');

        summernoteEditor.summernote('focus');
        summernoteEditor.summernote('editor.restoreRange');
        summernoteEditor.summernote('editor.insertText', selectedContent);
        summernoteEditor.summernote('editor.saveRange');
    });

    function loadInvoiceTemplates() {
        $.ajax({
            url: '/api/admin/index-invoice-template',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.code === 200) {
                    let tableBody = '';

                    if (response.data.length > 0) {
                        response.data.forEach(template => {
                            tableBody += `
                                <tr>
                                    <td>${template.invoice_title}</td>
                                    <td>${template.invoice_type}</td>
                                     ${ 
                                        $('#has_permission').data('visible') == 1 ?
                                    `<td>
                                        <div class="d-flex align-items-center">
                                          ${ 
                                                $('#has_permission').data('edit') == 1 ?
                                                `  <a href="#" class=" bg-white btn-icon me-2 edit-template"
                                               data-bs-toggle="modal" data-bs-target="#edit_email_template"
                                               data-id="${template.id}">
                                                <i class="ti ti-pencil fs-20"></i>
                                                </a>` : ''
                                            }
                                          
                                              ${ 
                                                $('#has_permission').data('delete') == 1 ?
                                                `<a href="#" class=" bg-white btn-icon delete_invoice_template"
                                               data-bs-toggle="modal" data-bs-target="#delete-modal"
                                               data-id="${template.id}">
                                                <i class="ti ti-trash fs-20"></i>
                                                </a>` : ''
                                            }
                                            
                                        </div>
                                    </td>` : ''
                                    }
                                </tr>

                                </tr>
                            `;
                        });
                    } else {
                        tableBody = `
                            <tr>
                                <td colspan="2" class="text-center">No invoice templates found</td>
                            </tr>
                        `;
                    }

                    $('#invoiceTemplatesTable tbody').html(tableBody);

                    $('#loader-table').addClass('d-none');
                    $(".label-loader, .input-loader").hide();
                    $('#invoiceTemplatesTable, .real-label, .real-input').removeClass('d-none');

                    if (!$.fn.dataTable.isDataTable('#invoiceTemplatesTable')) {
                        $('#invoiceTemplatesTable').DataTable({
                            "ordering": true,
                            "language": datatableLang
                        });
                    }
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    }

    //add
    $('#email_template_form').submit(function (event) {
        event.preventDefault();

        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.error-text').text('');

        // Client-side validation
        let invoiceTitle = $('#invoice_title').val().trim();
        let invoiceType = $('#invoice_type').val().trim();
        let templateContent = $('.summernote-add').summernote('code').trim();

        let isValid = true;

        if (!invoiceTitle) {
            $('#invoice_title').addClass('is-invalid');
            $('#invoice_title_error').text('Invoice title is required.');
            isValid = false;
        }

        if (!invoiceType) {
            $('#invoice_type').addClass('is-invalid');
            $('#invoice_type_error').text('Invoice type is required.');
            isValid = false;
        }

        if (!templateContent || templateContent === '<p><br></p>') { // Check for empty Summernote content
            $('.email_template_summernote').addClass('is-invalid');
            $('#template_content_error').text('Template content is required.');
            isValid = false;
        }

        // If validation fails, stop the form submission
        if (!isValid) {
            return;
        }

        // Prepare form data
        let formData = new FormData();
        formData.append('invoice_title', invoiceTitle);
        formData.append('invoice_type', invoiceType);
        formData.append('template_content', templateContent);

        // AJAX request
        $.ajax({
            url: "/api/admin/add-invoice-template",
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
                $('.email_template_save_btn').attr('disabled', true).html('<div class="spinner-border text-light" role="status"></div>');
            },
        }).done((response) => {
            $('.email_template_save_btn').removeAttr('disabled').html('Submit');
            if (response.code === 200) {
                toastr.success(response.message);
                $('#add_email_template').modal('hide'); // Close modal if needed
                loadInvoiceTemplates(); // Reload the list if needed
            } else {
                toastr.error(response.message);
            }
        }).fail((error) => {
            $('.email_template_save_btn').removeAttr('disabled').html('Submit');
            if (error.status == 422) {
                $.each(error.responseJSON.errors, function (key, val) {
                    $("#" + key).addClass("is-invalid");
                    $("#" + key + "_error").text(val[0]);
                });
            } else {
                toastr.error(error.responseJSON.message, "bg-danger");
            }
        });
    });

    $('#add_email_template').on('show.bs.modal', function () {
        $('.summernote-add').summernote('code', "");
        $('#email_template_form').trigger('reset');
        $('.error-text').text('');
        $('.form-control').removeClass('is-invalid is-valid');
    });

    //edit
    $(document).on('click', '.edit-template', function(e) {
        e.preventDefault();
        $('.error-text').text('');
        $('.form-control').removeClass('is-invalid is-valid');

        var templateId = $(this).data('id');

        $.ajax({
            url: '/api/admin/index-invoice-template',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            data: { id: templateId },
            success: function(response) {
                if (response.code === 200) {
                    const template = response.data;
                    $('#editTemplateForm input[name="template_id"]').val(template.id);
                    $('#edit_invoice_title').val(template.invoice_title);
                    $('#edit_invoice_type').val(template.invoice_type);
                    $('#summernote').summernote('code', template.template_content);

                    $('#edit_email_template').modal('show');
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    });

    $('#editTemplateForm').on('submit', function(e) {
        e.preventDefault();
        $('#edit_template_content').val($('#summernote').summernote('code'));

        var formData = $(this).serialize();

        $.ajax({
            url: '/api/admin/add-invoice-template',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            data: formData,
            success: function(response) {
                if (response.code === 200) {
                    toastr.success(response.message);
                    $('#edit_email_template').modal('hide');
                    loadInvoiceTemplates();
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON) {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    });
    //delete
    let templateIdToDelete;

    $(document).on('click', '.delete_invoice_template', function() {
        templateIdToDelete = $(this).data('id');
    });

    $('#deleteTemplateForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '/api/admin/destroy-invoice-template',
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            data: { id: templateIdToDelete },
            success: function(response) {
                if (response.code === 200) {
                    // Optionally, refresh the list of templates or remove the deleted template from the DOM
                    loadInvoiceTemplates();
                    $('#delete-modal').modal('hide'); // Hide the modal
                    toastr.success(response.message); // Show success message
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                } else if (xhr.responseJSON && xhr.responseJSON.code === 422) {
                    toastr.error(xhr.responseJSON.errors.id[0]); // Show specific validation error
                }
            }
        });
    });

    $(document).on('click', '.make_default', function(e) {
        e.preventDefault();

        var templateId = $(this).data('id');

        $.ajax({
            url: '/api/admin/invoice-template/set-default',
            type: 'POST',
            data: {
                id: templateId,
            },
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    loadInvoiceTemplates();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error('Failed to set default language. Please try again.');
            }
        });
    });


}

if (pageValue === 'admin.appointment-settings') {

    loadAppointmentSettings();

    function loadAppointmentSettings() {
        $.ajax({
            url: '/api/admin/index-invoice-setting',
            type: 'POST',
            data: {'group_id': 33},
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.code === 200) {
                    const requiredKeys = ['appointment_time_intervals', 'multiple_booking_same_time', 'min_booking_time', 'max_booking_time', 'cancel_time_before', 'reschedule_time_before'];
                    const filteredSettings = response.data.settings.filter(setting => requiredKeys.includes(setting.key));

                    filteredSettings.forEach(setting => {
                        const element = $('#' + setting.key);
                        if (element.is(':checkbox')) {
                            element.prop('checked', setting.value === '1');
                        } else {
                            element.val(setting.value);
                        }
                    });
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    }

    $('#appointmentForm').submit(function(event) {
        event.preventDefault();

        let formData = new FormData();

        formData.append('group_id', 33);
        formData.append('appointment_time_intervals', $('#appointment_time_intervals').is(':checked') ? 1 : 0);
        formData.append('multiple_booking_same_time', $('#multiple_booking_same_time').is(':checked') ? 1 : 0);
        formData.append('min_booking_time', $('#min_booking_time').is(':checked') ? 1 : 0);
        formData.append('max_booking_time', $('#max_booking_time').is(':checked') ? 1 : 0);
        formData.append('cancel_time_before', $('#cancel_time_before').is(':checked') ? 1 : 0);
        formData.append('reschedule_time_before', $('#reschedule_time_before').is(':checked') ? 1 : 0);

        $.ajax({
            url: "/api/admin/update-appointment-setting",
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
                $('.appointment_setting_btn').attr('disabled', true).html('<div class="spinner-border text-light" role="status"></div>');
            },
        }).done((response) => {
            $(".appointment_setting_btn").removeAttr('disabled').html($('.appointment_setting_btn').data('save'));
            if (response.code === 200) {
                toastr.success(response.message);
                loadAppointmentSettings();
            }
        }).fail((error) => {
            $('.appointment_setting_btn').removeAttr('disabled').html($('.appointment_setting_btn').data('save'));
            if (error.status == 422) {
                $.each(error.responseJSON.errors, function (key, val) {
                    $("#" + key).addClass("is-invalid");
                    $("#" + key + "_error").text(val[0]);
                });
            } else {
                toastr.error(error.responseJSON.message, "bg-danger");
            }
        });
    });

}

/* Currency settings */
if (pageValue === "admin.currency-settings") {
    $(document).ready(function () {
        loadCurrencies();
    });

    function loadCurrencies() {
        $.ajax({
            url: "/api/currencies/list",
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
            success: function (response) {
                if (response.code == 200) {
                    var currency_data = response.data;
                    var currency_table_body = $(".currency_list");
                    var response_data;
                    if (currency_data.length === 0) {
                        $("#currency_table").DataTable().destroy();
                        response_data += `
                            <tr>
                                <td colspan="6" class="text-center">${$(
                                    "#currency_table"
                                ).data("empty_info")}</td>
                            </tr>`;
                    } else {
                        $.each(currency_data, (index, val) => {
                            response_data += `
                                <tr>
                                    <td>${val.name}</td>
                                    <td>${val.code}</td>
                                    <td>${val.symbol}</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input currency_status" ${
                                                val.status == 1 ? "checked" : ""
                                            } type="checkbox"
                                                role="switch" id="switch-sm" data-id="${
                                                    val.id
                                                }">
                                        </div>
                                    </td>
                                     ${
                                         $("#has_permission").data("edit") == 1
                                             ? `<td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input currency_default" ${
                                                    val.is_default == 1
                                                        ? "checked disabled"
                                                        : ""
                                                } type="checkbox"
                                                    role="switch" id="switch-sm" data-id="${
                                                        val.id
                                                    }">
                                            </div>
                                        </td>`
                                             : ""
                                     }
                                      ${
                                          $("#has_permission").data(
                                              "visible"
                                          ) == 1
                                              ? `<td>
                                        <li style="list-style: none;">
                                            ${
                                                $("#has_permission").data(
                                                    "delete"
                                                ) == 1
                                                    ? `<a class="delete delete_currency_modal" href="#" data-bs-toggle="modal" data-bs-target="#currency_delete" data-id="${
                                                          val.id
                                                      }">
                                                <i class="ti ti-trash m-3 fs-20" data-tooltip="tooltip" title="${$(
                                                    ".currency_save_btn"
                                                ).data("delete")}"></i>
                                                </a>`
                                                    : ""
                                            }
                                        </li>
                                    </td>`
                                              : ""
                                      }
                                </tr>`;
                        });
                    }
                    currency_table_body.html(response_data);
                    initTooltip();

                    if (
                        currency_data.length != 0 &&
                        !$.fn.dataTable.isDataTable("#currency_table")
                    ) {
                        $("#currency_table").DataTable({
                            ordering: true,
                            language: datatableLang,
                        });
                    }
                }
                $("#loader-table").addClass("d-none");
                $(".label-loader, .input-loader").hide();
                $("#currency_table, .real-label, .real-input").removeClass(
                    "d-none"
                );
            },
            error: function (error) {
                if (error.responseJSON.code === 500) {
                    toastr.error("An error occurred while fetching.");
                }
            },
        });
    }

    $(document).on("change", ".currency_default", function (e) {
        e.preventDefault();

        var currencyId = $(this).attr("data-id");

        var formData = {
            id: currencyId,
        };

        $.ajax({
            url: "/api/currencies/set-default",
            type: "POST",
            data: formData,
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    loadCurrencies();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error(
                        "Failed to set default currency. Please try again."
                    );
                }
            },
        });
    });

    $(document).on("change", ".currency_status", function (e) {
        e.preventDefault();

        let currencyId = $(this).attr("data-id");
        let status = $(this).is(":checked") ? 1 : 0;

        let formData = {
            id: currencyId,
            status: status,
        };

        $.ajax({
            url: "/api/currencies/change-status",
            type: "POST",
            data: formData,
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    loadCurrencies();
                    toastr.success(response.message);
                }
            },
            error: function (xhr) {
                toastr.error(
                    xhr.responseJSON.message ||
                        "An error occured while changing currency status."
                );
            },
        });
    });

    $(document).on("click", ".delete_currency_modal", function (e) {
        e.preventDefault();
        var id = $(this).data("id");
        $(".currency_delete_btn").data("id", id);
    });

    $(document).on("click", ".currency_delete_btn", function (e) {
        e.preventDefault();
        var delete_id = $(this).data("id");

        $.ajax({
            url: "/api/currencies/delete",
            type: "POST",
            data: {
                id: delete_id,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#currency_table").DataTable().destroy();
                if (response.code === 200) {
                    loadCurrencies();
                    $("#currency_delete").modal("hide");
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                toastr.error(
                    xhr.responseJSON.message ||
                        "An error occurred while deleting currency."
                );
            },
        });
    });

    $(document).on("click", "#add_currency_btn", function (e) {
        $(".error-text").text("");
        $(".form-control").removeClass("is-invalid is-valid");
        $(".currency_modal_title").html(
            $(".currency_modal_title").data("add-title")
        );
        $("#save_currency_id").val("");
        $("#save_currency_status").prop("checked", false);
        $("#save_currency_default").prop("checked", false);
        $("#available_currency_id").val("").trigger("change");
        $("#save_currency").modal("show");
    });

    $("#currencyForm").validate({
        rules: {
            available_currency_id: {
                required: true,
            },
        },
        messages: {
            available_currency_id: {
                required: $("#available_currency_id_error").data("required"),
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
            var formData = {
                available_currency_id: $("#available_currency_id").val(),
                is_default: $("#save_currency_default").is(":checked") ? 1 : 0,
            };

            $.ajax({
                url: "/api/currencies/save",
                type: "POST",
                data: formData,
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                beforeSend: function () {
                    $(".currency_save_btn").attr("disabled", true);
                    $(".currency_save_btn").html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
                },
                success: function (response) {
                    if ($.fn.DataTable.isDataTable("#currency_table")) {
                        $("#currency_table").DataTable().destroy();
                    }
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid is-valid");
                    $(".currency_save_btn").removeAttr("disabled");
                    $(".currency_save_btn").html($(".currency_save_btn").data('update-text'));
                    if (response.code === 200) {
                        $("#save_currency").modal("hide");
                        loadCurrencies();
                        toastr.success(response.message);
                    }
                },
                error: function (error) {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid is-valid");
                    $(".currency_save_btn").removeAttr("disabled");
                    $(".currency_save_btn").html($(".currency_save_btn").data('update-text'));
                    var errors = error.responseJSON.message;
                    if (errors) {
                        $.each(errors, function (key, message) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(message[0]);
                        });
                    } else {
                        toastr.error(
                            "An error occurred while creating currency."
                        );
                    }
                },
            });
        },
    });
}

if (pageValue === "admin.commission") {
    $(document).ready(function () {
        adminCommissionList();
    });

    function adminCommissionList() {
        $.ajax({
            url: "/api/admin/general-setting/list",
            type: "POST",
            data: { group_id: 2 },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    const requiredKeys = [
                        "commission_type",
                        "commission_rate_percentage",
                        "commission_rate_fixed",
                    ];
                    const filteredSettings = response.data.settings.filter(
                        (setting) => requiredKeys.includes(setting.key)
                    );
                    var selectedType = "";

                    filteredSettings.forEach((setting) => {
                        if (setting.value == "percentage") {
                            $("#" + setting.key).val(setting.value);
                            selectedType = setting.value;
                        } else if (setting.value == "fixed") {
                            $("#" + setting.key).val(setting.value);
                            selectedType = setting.value;
                        }

                        if (setting.key == "commission_rate_" + selectedType) {
                            $("#commission_rate").val(setting.value);
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

    $(document).on("change", "#commission_type", function () {
        var selectedType = $(this).val();
        $.ajax({
            url: "/api/admin/general-setting/list",
            type: "POST",
            data: { group_id: 2 },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    const requiredKeys = [
                        "commission_rate_percentage",
                        "commission_rate_fixed",
                    ];
                    const filteredSettings = response.data.settings.filter(
                        (setting) => requiredKeys.includes(setting.key)
                    );

                    filteredSettings.forEach((setting) => {
                        if ("commission_rate_" + selectedType == setting.key) {
                            $("#commission_rate").val(setting.value);
                        }
                    });
                }
            },
            error: function (xhr) {
                if (xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            },
        });
    });

    $("#adminCommissionForm").on("submit", function (event) {
        event.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: "/api/admin/update-admin-commission",
            method: "POST",
            data: formData,
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            beforeSend: function () {
                $(".admin_commission_btn").attr("disabled", true);
                $(".admin_commission_btn").html(
                    '<div class="spinner-border text-light" role="status"></div>'
                );
            },
            success: function (response) {
                $(".admin_commission_btn").attr("disabled", false);
                $(".admin_commission_btn").html(
                    $(".admin_commission_btn").data("save_text")
                );
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid");
                $(".form-select").removeClass("is-invalid");

                if (response.code === 200) {
                    toastr.success(response.message);
                    adminCommissionList();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                $(".admin_commission_btn").removeAttr("disabled");
                $(".admin_commission_btn").html(
                    $(".admin_commission_btn").data("save_text")
                );
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.message, function (key, val) {
                        $("#" + key + "_error").text(val);
                        $("#" + key).addClass("is-invalid");
                    });
                } else {
                    toastr.error(xhr.responseJSON.message, "bg-danger");
                }
            },
        });
    });
}

if (pageValue === "admin.tax-options") {
    function taxOptionsList() {
        $.ajax({
            url: "/api/admin/general-setting/list",
            type: "POST",
            data: { group_id: 3 },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    $(".tax_option_list").empty();

                    let settings = response.data.settings;
                    if (settings.length === 0) {
                        $(".tax_option_list").append(`
                            <tr>
                                <td colspan="5" class="text-center">No data found</td>
                            </tr>
                        `);
                    } else {
                        for (let i = 0; i < settings.length; i += 3) {
                            let taxType = settings[i].value;
                            let taxRate = settings[i + 1].value;
                            let taxTypeKey = settings[i].key;
                            let taxRateKey = settings[i + 1].key;
                            let taxStatusKey = settings[i + 2].key;
                            let taxTypeId = settings[i].id;
                            let taxRateId = settings[i + 1].id;
                            let taxStatus = settings[i + 2].value;
                            let checkedVal = taxStatus == 1 ? "checked" : "";

                            const row = `
                                <tr>
                                    <td>${taxType}</td>
                                    <td>${taxRate}</td>
                                     ${
                                         $("#has_permission").data("edit") == 1
                                             ? `<td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input tax_status" ${checkedVal} type="checkbox" role="switch" id="switch-sm" data-tax-type-status="${taxStatusKey}">
                                            </div>
                                         </td>`
                                             : ""
                                     }
                                    ${
                                        $("#has_permission").data("visible") ==
                                        1
                                            ? `<td>
                                    ${
                                        $("#has_permission").data("edit") == 1
                                            ? `<a href="#" class="btn btn-outline-light bg-white btn-icon me-2 edit-tax-rate"
                                        data-bs-toggle="modal" data-bs-target="#add_tax_rate"
                                        data-tax-type="${taxType}" data-tax-rate="${taxRate}"
                                        data-tax-type-key="${taxTypeKey}" data-tax-rate-key="${taxRateKey}"
                                        data-tax-type-id="${taxTypeId}" data-tax-rate-id="${taxRateId}">
                                            <i class="ti ti-edit"></i>
                                        </a>`
                                            : ""
                                    }

                                    ${
                                        $("#has_permission").data("delete") == 1
                                            ? `<a href="#" class="btn btn-outline-light bg-white btn-icon me-2 delete-tax-rate"
                                        data-bs-toggle="modal" data-bs-target="#tax_delete_modal"
                                        data-del-tax-type-key="${taxTypeKey}" data-del-tax-rate-key="${taxRateKey}"
                                        data-del-tax-status="${taxStatusKey}">
                                            <i class="ti ti-trash"></i>
                                        </a>`
                                            : ""
                                    }
                                    </td>`
                                            : ""
                                    }
                                </tr>
                            `;
                            $(".tax_option_list").append(row);
                        }
                        // Attach event handler to edit buttons
                        $(".edit-tax-rate").on("click", function () {
                            const taxType = $(this).data("tax-type");
                            const taxRate = $(this).data("tax-rate");
                            const taxTypeKey = $(this).data("tax-type-key");
                            const taxRateKey = $(this).data("tax-rate-key");
                            const taxTypeId = $(this).data("tax-type-id");
                            const taxRateId = $(this).data("tax-rate-id");

                            $(".tax_modal_title").html(
                                $(".tax_modal_title").data("edit_tax_rate")
                            );
                            $("#method").val("update");
                            $("#tax_type").val(taxType);
                            $("#tax_rate").val(taxRate);
                            $("#tax_type_id").val(taxTypeId);
                            $("#tax_rate_id").val(taxRateId);

                            $(".error-text").text("");
                            $(".form-control").removeClass("is-invalid");
                        });

                        $(".delete-tax-rate").on("click", function () {
                            const taxType = $(this).data("del-tax-type-key");
                            const taxRate = $(this).data("del-tax-rate-key");
                            const taxStatus = $(this).data("del-tax-status");
                            $("#del_tax_type").val(taxType);
                            $("#del_tax_rate").val(taxRate);
                            $("#del_tax_status").val(taxStatus);
                        });

                        $(".tax_status").on("change", function () {
                            let taxType = $(this).data("tax-type-status");
                            let newStatus = $(this).is(":checked") ? 1 : 0;

                            var data = {
                                tax_type_staus: taxType,
                                status: newStatus,
                            };

                            $.ajax({
                                url: "/api/admin/tax-status-change",
                                type: "POST",
                                data: data,
                                headers: {
                                    Authorization:
                                        "Bearer " +
                                        localStorage.getItem("admin_token"),
                                    Accept: "application/json",
                                },
                                success: function (response) {
                                    if (response.code === 200) {
                                        toastr.success(response.message);
                                    }
                                },
                                error: function () {
                                    toastr.error(
                                        "An error occurred while updating status"
                                    );
                                },
                            });
                        });
                    }
                }
                $("#loader-table").addClass("d-none");
                $(".label-loader, .input-loader").hide();
                $("#tax_option_table, .real-label, .real-input").removeClass(
                    "d-none"
                );
            },
            error: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.code === 404) {
                    toastr.error(xhr.responseJSON.message);
                }
            },
        });
    }

    $(document).ready(function () {
        taxOptionsList();

        $("#add_tax_btn").on("click", function () {
            $(".tax_modal_title").html(
                $(".tax_modal_title").data("add_tax_rate")
            );
            $("#method").val("add");
            $("#addTaxRateForm").trigger("reset");
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid");
        });

        $("#addTaxRateForm").submit(function (event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: "/api/admin/save-tax-options",
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
                    $(".tax_options_btn").attr("disabled", true);
                    $(".tax_options_btn").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
                .done((response, statusText, xhr) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".tax_options_btn").removeAttr("disabled");
                    $(".tax_options_btn").html(
                        $(".tax_options_btn").data("save_text")
                    );
                    if (response.code === 200) {
                        toastr.success(response.message);
                        $("#add_tax_rate").modal("hide");
                        taxOptionsList();
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".tax_options_btn").removeAttr("disabled");
                    $(".tax_options_btn").html(
                        $(".tax_options_btn").data("save_text")
                    );
                    if (error.status == 422) {
                        $.each(error.responseJSON.errors, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(responseJSON.message);
                    }
                });
        });

        $(".delete_tax_option").on("click", function (e) {
            e.preventDefault();

            var formData = {
                tax_type: $("#del_tax_type").val(),
                tax_rate: $("#del_tax_rate").val(),
                tax_status: $("#del_tax_status").val(),
            };

            $.ajax({
                url: "/api/admin/delete-tax-options",
                type: "POST",
                data: formData,
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        taxOptionsList();
                        $("#tax_delete_modal").modal("hide");
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error(xhr);
                },
            });
        });
    });
}

/* Cedential settings */
if (pageValue === "admin.credential-settings") {
    $(document).ready(function () {
        loadCredentialSetting();
    });

    function loadCredentialSetting() {
        $.ajax({
            url: "/api/credential/list",
            type: "POST",
            data: { group_id: 4 },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    const requiredKeys = [
                        "chatgpt_status",
                        "chatgpt_api_key",
                        "recaptcha_api_key",
                        "recaptcha_secret_key",
                        "recaptcha_status",
                        "location_api_key",
                        "location_status",
                        "sso_client_id",
                        "sso_client_secret",
                        "sso_redirect_url",
                        "sso_status",
                        "razorpay_key",
                        "razorpay_secret",
                        "razorpay_mode",
                        "payu_merchant_key",
                        "payu_merchant_salt",
                        "payu_base_url",
                        "payu_mode",
                        "payu_status",
                        "cashfree_api_key",
                        "cashfree_api_secret",
                        "cashfree_mode",
                        "cashfree_status",
                        "authorizenet_api_login_id",
                        "authorizenet_transaction_key",
                        "authorizenet_env",
                        "authorizenet_status",
                        "paystack_status",
                        "paystack_public_key",
                        "paystack_secret_key",
                        "paystack_payment_url",
                        "paystack_callback_url",
                        "mercadopago_status",
                        "mercadopago_public_key",
                        "mercadopago_access_token",
                        "mercadopago_callback_url",
                        "location_unit",
                        "location_distance_value"
                    ];

                    const filteredSettings = response.data.settings.filter(
                        (setting) => requiredKeys.includes(setting.key)
                    );

                    filteredSettings.forEach((setting) => {
                        if (setting.key === "chatgpt_status") {
                            if (setting.value === "1") {
                                $("#chatgpt_status").prop("checked", true);
                            } else {
                                $("#chatgpt_status").prop("checked", false);
                            }
                        } else {
                            $("#" + setting.key).val(setting.value);
                        }
                    });

                    filteredSettings.forEach((setting) => {
                        if (setting.key === "location_status") {
                            if (setting.value === "1") {
                                $("#location_status").prop("checked", true);
                            } else {
                                $("#location_status").prop("checked", false);
                            }
                        } else if (setting.key == "location_unit") {
                            $("#location_unit").val(setting.value).trigger("change");
                        } else if (setting.key == "location_distance_value") {
                            $("#location_distance").val(setting.value);
                        } else {
                            $("#" + setting.key).val(setting.value);
                        }
                    });

                    filteredSettings.forEach((setting) => {
                        if (setting.key === "recaptcha_status") {
                            if (setting.value === "1") {
                                $("#recaptcha_status").prop("checked", true);
                            } else {
                                $("#recaptcha_status").prop("checked", false);
                            }
                        } else {
                            $("#" + setting.key).val(setting.value);
                        }
                    });

                    filteredSettings.forEach((setting) => {
                        if (setting.key === "sso_status") {
                            if (setting.value === "1") {
                                $("#sso_status").prop("checked", true);
                            } else {
                                $("#sso_status").prop("checked", false);
                            }
                        } else {
                            $("#" + setting.key).val(setting.value);
                        }
                    });

                    filteredSettings.forEach((setting) => {
                        if (setting.key === "payu_status") {
                            if (setting.value === "1") {
                                $("#payu_status_toggle").prop("checked", true);
                            } else {
                                $("#payu_status_toggle").prop("checked", false);
                            }
                        } else {
                            $("#" + setting.key).val(setting.value);
                        }
                    });

                    filteredSettings.forEach((setting) => {
                        if (setting.key === "cashfree_status") {
                            if (setting.value === "1") {
                                $("#cashfree_status_toggle").prop(
                                    "checked",
                                    true
                                );
                            } else {
                                $("#cashfree_status_toggle").prop(
                                    "checked",
                                    false
                                );
                            }
                        } else {
                            $("#" + setting.key).val(setting.value);
                        }
                    });

                    filteredSettings.forEach((setting) => {
                        if (setting.key === "authorizenet_status") {
                            if (setting.value === "1") {
                                $("#authorizenet_status_toggle").prop(
                                    "checked",
                                    true
                                );
                            } else {
                                $("#authorizenet_status_toggle").prop(
                                    "checked",
                                    false
                                );
                            }
                        } else {
                            $("#" + setting.key).val(setting.value);
                        }
                    });

                    filteredSettings.forEach((setting) => {
                        if (setting.key === "paystack_status") {
                            if (setting.value === "1") {
                                $("#paystack_status_toggle").prop(
                                    "checked",
                                    true
                                );
                            } else {
                                $("#paystack_status_toggle").prop(
                                    "checked",
                                    false
                                );
                            }
                        } else {
                            $("#" + setting.key).val(setting.value);
                        }
                    });

                    filteredSettings.forEach((setting) => {
                        if (setting.key === "mercadopago_status") {
                            if (setting.value === "1") {
                                $("#mercadopago_status_toggle").prop(
                                    "checked",
                                    true
                                );
                            } else {
                                $("#mercadopago_status_toggle").prop(
                                    "checked",
                                    false
                                );
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
        $("#chatgptForm").submit(function (event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: "/api/credential/save/chatgpt",
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".chatgpt_setting_btn").attr("disabled", true);
                    $(".chatgpt_setting_btn").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
                .done((response, statusText, xhr) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".chatgpt_setting_btn").removeAttr("disabled");
                    $(".chatgpt_setting_btn").html(
                        $(".sso_setting_btn").data("update")
                    );
                    if (response.code === 200) {
                        toastr.success(response.message);
                        loadCredentialSetting();
                        $("#google_analytics").modal("hide");
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".chatgpt_setting_btn").removeAttr("disabled");
                    $(".chatgpt_setting_btn").html(
                        $(".sso_setting_btn").data("update")
                    );

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

        $("#googleLocationApi").submit(function (event) {
            event.preventDefault();

            // Use FormData from the form
            var formData = new FormData(this);

            // Remove the manual append if the fields already exist in the form
            // OR only append if the field is not part of the form inputs
            // Example: if distance and unit are visible inputs, do NOT append them manually

            $.ajax({
                url: "/api/credential/save/location",
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".location_setting_btn").attr("disabled", true);
                    $(".location_setting_btn").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
            .done((response) => {
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid");
                $(".location_setting_btn").removeAttr("disabled");
                $(".location_setting_btn").html($(".location_setting_btn").data("update") || "Update");

                if (response.code === 200) {
                    toastr.success(response.message);
                    loadCredentialSetting();
                } else {
                    toastr.error(response.message);
                }
            })
            .fail((error) => {
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid");
                $(".location_setting_btn").removeAttr("disabled");
                $(".location_setting_btn").html($(".location_setting_btn").data("update") || "Update");

                if (error.status === 422) {
                    $.each(error.responseJSON.errors, function (key, val) {
                        $("#" + key).addClass("is-invalid");
                        $("#" + key + "_error").text(val[0]);
                    });
                } else {
                    toastr.error(error.responseJSON?.message || "An unexpected error occurred.");
                }
            });
        });

        $("#googlerecaptchaApi").submit(function (event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: "/api/credential/save/googlerecaptcha",
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".recaptcha_setting_btn").attr("disabled", true);
                    $(".recaptcha_setting_btn").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
                .done((response, statusText, xhr) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".recaptcha_setting_btn").removeAttr("disabled");
                    $(".recaptcha_setting_btn").html(
                        $(".sso_setting_btn").data("update")
                    );
                    if (response.code === 200) {
                        toastr.success(response.message);
                        loadCredentialSetting();
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".recaptcha_setting_btn").removeAttr("disabled");
                    $(".recaptcha_setting_btn").html(
                        $(".sso_setting_btn").data("update")
                    );

                    if (error.status == 422) {
                        $.each(error.responseJSON.errors, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(responseJSON.message);
                    }
                });
        });

        $("#chatgpt_status").on("change", function () {
            let chatgptStatus = $(this).is(":checked") ? 1 : 0;

            let formData = {
                chatgpt_status: chatgptStatus,
            };

            $.ajax({
                url: "/api/credential/status/chatgpt-status",
                type: "POST",
                data: formData,
                dataType: "json",
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error("Failed to update status.");
                    }
                },
                error: function (error) {
                    toastr.error(
                        "An error occurred while updating the status."
                    );
                },
            });
        });

        $("#recaptcha_status").on("change", function () {
            let recaptchaStatus = $(this).is(":checked") ? 1 : 0;

            let formData = {
                recaptcha_status: recaptchaStatus,
            };

            $.ajax({
                url: "/api/credential/status/recaptcha-status",
                type: "POST",
                data: formData,
                dataType: "json",
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error("Failed to update status.");
                    }
                },
                error: function (error) {
                    toastr.error(
                        "An error occurred while updating the status."
                    );
                },
            });
        });

        $("#location_status").on("change", function () {
            let locationStatus = $(this).is(":checked") ? 1 : 0;

            // Get selected unit and distance value
            let locationUnit = $("#location_unit").val() || ""; // Km or Miles
            let locationDistance = $("#location_distance").val() || "";

            let formData = {
                location_status: locationStatus,
                location_unit: locationUnit,
                location_distance: locationDistance,
            };

            $.ajax({
                url: "/api/credential/status/location-status",
                type: "POST",
                data: formData,
                dataType: "json",
                headers: {
                    Authorization: "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error("Failed to update status.");
                    }
                },
                error: function (error) {
                    toastr.error("An error occurred while updating the status.");
                },
            });
        });

        $("#ssoForm").submit(function (event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: "/api/credential/save/sso",
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".sso_setting_btn").attr("disabled", true);
                    $(".sso_setting_btn").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
                .done((response, statusText, xhr) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".sso_setting_btn").removeAttr("disabled");
                    $(".sso_setting_btn").html(
                        $(".sso_setting_btn").data("update")
                    );
                    if (response.code == 200) {
                        toastr.success(response.message);
                        loadCredentialSetting();
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".sso_setting_btn").removeAttr("disabled");
                    $(".sso_setting_btn").html(
                        $(".sso_setting_btn").data("update")
                    );

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

        $("#sso_status").on("change", function () {
            let sso_configure_status = $(this).is(":checked") ? 1 : 0;

            let formData = {
                sso_status: sso_configure_status,
            };

            $.ajax({
                url: "/api/credential/status/sso-configure-status",
                type: "POST",
                data: formData,
                dataType: "json",
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error("Failed to update captcha status.");
                    }
                },
                error: function (error) {
                    toastr.error(
                        "An error occurred while updating the captcha status."
                    );
                },
            });
        });
    });

    $(document).on('change', '#location_unit', function () {
        const distanceInput = $('#location_distance');
        const distanceLabel = $('#distance_label');

        if ($(this).val()) {
            distanceInput.show().addClass('real-input');
            distanceLabel.show();
        } else {
            distanceInput.hide().removeClass('real-input');
            distanceLabel.hide();
        }
    });
}

//Subscription-package
if (pageValue === "admin.subscription-package") {
    function toggleInputFields() {
        const packageTerm = document.getElementById("package_term").value;
        const dayInput = document.getElementById("day_input");
        const monthInput = document.getElementById("month_input");
        const showinput = document.getElementById("show_input");
        const packageDurationInput =
            document.getElementById("package_duration");
        const packagedurationerror = document.getElementById(
            "edit_package_duration_error"
        );

        if (packageTerm === "day") {
            showinput.style.display = "none";
            dayInput.style.display = "block";
            monthInput.style.display = "none";
            packageDurationInput.disabled = false;
            packageDurationInput.value = "";
        } else if (packageTerm === "month") {
            showinput.style.display = "none";
            monthInput.style.display = "block";
            dayInput.style.display = "none";
            packageDurationInput.disabled = false;
            packageDurationInput.value = "";
        } else {
            showinput.style.display = "block";
            dayInput.style.display = "none";
            monthInput.style.display = "none";
            packageDurationInput.disabled = true;
            packageDurationInput.value = "";
        }
    }

    $(document).ready(function () {
        $("#addSubscriptionForm").submit(function (event) {
            event.preventDefault();

            var formData = new FormData(this);
            formData.append("status", $("#status").is(":checked") ? 1 : 0);
            formData.set("featured", $("#featured").is(":checked") ? 1 : 0);
            formData.set("badge", $("#badge").is(":checked") ? 1 : 0);
            formData.set("stripe_recurring", $("#stripe_recurring").is(":checked") ? 1 : 0);
            formData.set("paypal_recurring", $("#paypal_recurring").is(":checked") ? 1 : 0);
            formData.set("promoted_service", $("#promoted_service").is(":checked") ? 1 : 0);
            formData.set("promoted_jobber", $("#promoted_jobber").is(":checked") ? 1 : 0);

            $.ajax({
                url: "/api/subscription-package/save",
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".subscription_package_btn").attr("disabled", true);
                    $(".subscription_package_btn").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
                .done((response, statusText, xhr) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".subscription_package_btn").removeAttr("disabled");
                    $(".subscription_package_btn").html("submit");
                    if (response.code === 200) {
                        toastr.success(response.message);
                        subscription_table();
                        $("#add_subscription_package").modal("hide");
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".subscription_package_btn").removeAttr("disabled");
                    $(".subscription_package_btn").html("submit");

                    if (error.status == 422) {
                        $.each(error.responseJSON, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(error.responseJSON.message, "bg-danger");
                    }
                });
        });
    });

    $(document).ready(function () {
        subscription_table();
    });

    $(document).on('change', '#subscription_type, #edit_subscription_type', function () {
        let value = $(this).val();
        if (value == 'regular') {
            $(".recurring_subscription").removeClass('d-none');
            $(".hide_subscription_data").removeClass('d-none');
            $(".promote_subscription").addClass('d-none');
        } else if (value == 'promote') {
            $(".recurring_subscription").addClass('d-none');
            $(".hide_subscription_data").addClass('d-none');
            $(".promote_subscription").removeClass('d-none');
        } else {
            $(".recurring_subscription").addClass('d-none');
            $(".promote_subscription").addClass('d-none');
            $(".hide_subscription_data").removeClass('d-none');
        }
    });

    function subscription_table() {
        fetchSubscription(1);
    }

    function fetchSubscription(page) {
        $.ajax({
            url: "/api/subscription-package/list",
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
                    populateSubscriptionTable(response.data, response.meta);
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

    function populateSubscriptionTable(Subscription, meta) {
        let tableBody = "";

        if (Subscription.length > 0) {
            Subscription.forEach((Subscription, index) => {
                tableBody += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${Subscription.package_title}</td>
                        <td>${Subscription.subscription_type ?? ""}</td>
                        <td>${Subscription.price}</td>
                        <td>${
                            Subscription.package_term.charAt(0).toUpperCase() +
                            Subscription.package_term.slice(1).toLowerCase()
                        }</td>
                        <td>${
                            Subscription.package_duration
                                ? Subscription.package_duration
                                : "-"
                        }</td>
                        <td>${Subscription.number_of_service}</td>
                        <td>
                            <span class="badge ${
                                Subscription.status == "1"
                                    ? "badge-soft-success"
                                    : "badge-soft-danger"
                            } d-inline-flex align-items-center">
                                <i class="ti ti-circle-filled fs-5 me-1"></i>${
                                    Subscription.status == "1"
                                        ? "Active"
                                        : "Inactive"
                                }
                            </span>
                        </td>
                         ${
                             $("#has_permission").data("visible") == 1
                                 ? `<td><li style="list-style: none;">
                                        ${
                                            $("#has_permission").data("edit") ==
                                            1
                                                ? `<a class="edit_sub_data"
                                           href="#"
                                           data-bs-toggle="modal"
                                           data-bs-target="#edit_subscription_package"
                                           data-id="${Subscription.id}"
                                           data-package_title="${Subscription.package_title}"
                                           data-price="${Subscription.price}"
                                           data-package_term="${Subscription.package_term}"
                                           data-package_duration="${Subscription.package_duration}"
                                           data-number_of_service="${Subscription.number_of_service}"
                                           data-number_of_feature_service="${Subscription.number_of_feature_service}"
                                           data-number_of_product="${Subscription.number_of_product}"
                                           data-number_of_service_order="${Subscription.number_of_service_order}"
                                            data-number_of_locations="${Subscription.number_of_locations}"
                                           data-number_of_staff="${Subscription.number_of_staff}"
                                            data-number_of_lead="${Subscription.number_of_lead}"
                                            data-subscription_type="${Subscription.subscription_type}"
                                           data-order_by="${Subscription.order_by}"
                                           data-description="${Subscription.description}"
                                           data-status="${Subscription.status}"
                                           data-featured="${Subscription.featured}"
                                           data-badge="${Subscription.badge}"
                                           data-promoted_service="${Subscription.promoted_service}"
                                           data-promoted_jobber="${Subscription.promoted_jobber}"
                                           data-stripe_recurring="${Subscription.stripe_recurring}"
                                           data-paypal_recurring="${Subscription.paypal_recurring}">
                                           <i class="ti ti-pencil fs-20"></i>
                                        </a>`
                                                : ""
                                        }

                                        ${
                                            $("#has_permission").data(
                                                "delete"
                                            ) == 1
                                                ? ` <a class="delete" href="#" data-bs-toggle="modal" data-bs-target="#delete-modal" data-id="${Subscription.id}">
                                            <i class="ti ti-trash m-3 fs-20"></i>
                                        </a>`
                                                : ""
                                        }

                                    </li></td>`
                                 : ""
                         }
                    </tr>
                `;
            });
        } else {
            tableBody = `
                <tr>
                    <td colspan="10" class="text-center">No Subscription found</td>
                </tr>
            `;
        }

        $("#subscription_datatable tbody").html(tableBody);

        $("#loader-table").addClass("d-none");
        $(".label-loader, .input-loader").hide();
        $("#subscription_datatable, .real-label, .real-input").removeClass(
            "d-none"
        );

        if (
            Subscription.length != 0 &&
            !$.fn.dataTable.isDataTable("#subscription_datatable")
        ) {
            $("#subscription_datatable").DataTable({
                ordering: true,
                language: datatableLang,
            });
        }
    }

    $(document).on("click", ".edit_sub_data", function (e) {
        e.preventDefault();

        var subId = $(this).data("id");
        var title = $(this).data("package_title");
        var price = $(this).data("price");
        var package_term = $(this).data("package_term");
        var package_duration = $(this).data("package_duration");
        var number_of_service = $(this).data("number_of_service");
        var number_of_locations = $(this).data("number_of_locations");
        var number_of_staff = $(this).data("number_of_staff");
        var subscription_type = $(this).data("subscription_type");
        var number_of_lead = $(this).data("number_of_lead");
        var number_of_feature_service = $(this).data(
            "number_of_feature_service"
        );
        var number_of_product = $(this).data("number_of_product");
        var number_of_service_order = $(this).data("number_of_service_order");
        var order_by = $(this).data("order_by");
        var description = $(this).data("description");
        var status = $(this).data("status");
        var featured = $(this).data('featured');
        var badge = $(this).data('badge');
        var stripe_recurring = $(this).data('stripe_recurring');
        var paypal_recurring = $(this).data('paypal_recurring');
        var promoted_service = $(this).data("promoted_service");
        var promoted_jobber = $(this).data("promoted_jobber");
        $("#edit_id").val(subId);
        $("#edit_package_title").val(title);
        $("#edit_price").val(price);
        $("#edit_package_term").val(package_term);
        $("#edit_package_duration").val(package_duration);
        $("#edit_number_of_service").val(number_of_service);
        $("#edit_number_of_feature_service").val(number_of_feature_service);
        $("#edit_number_of_product").val(number_of_product);
        $("#edit_number_of_locations").val(number_of_locations);
        $("#edit_number_of_staff").val(number_of_staff);
        $("#edit_number_of_lead").val(number_of_lead);
        $("#edit_subscription_type").val(subscription_type).trigger("change");
        $("#edit_number_of_service_order").val(number_of_service_order);
        $("#edit_order_by").val(order_by);
        $("#edit_description").val(description);
        $("#edit_status").prop("checked", status == 1);
        $("#edit_featured").prop("checked", featured == 1);
        $("#edit_badge").prop("checked", badge == 1);
        $("#edit_stripe_recurring").prop("checked", stripe_recurring == 1);
        $("#edit_paypal_recurring").prop("checked", paypal_recurring == 1);
        $("#edit_promoted_jobber").prop("checked", promoted_jobber == 1);
        $("#edit_promoted_service").prop("checked", promoted_service == 1);
        togglePackageDuration(package_term);
    });

    $("#edit_package_term").on("change", function () {
        var package_term = $(this).val();
        togglePackageDuration(package_term);
    });

    function togglePackageDuration(package_term) {
        const EditpackageDurationInput = document.getElementById(
            "edit_package_duration"
        );
        if (package_term === "day") {
            EditpackageDurationInput.disabled = false;
            $("#duration_label").text("Number Of Day");
        } else if (package_term === "month") {
            EditpackageDurationInput.disabled = false;
            $("#duration_label").text("Number Of Month");
        } else {
            $("#duration_label").text("Select Day/Month");
            EditpackageDurationInput.disabled = true;
            $("#edit_package_duration").val("");
        }
    }

    $(document).ready(function () {
        $("#editSubscriptionForm").submit(function (event) {
            event.preventDefault();

            var formData = new FormData(this);
            formData.append("status", $("#edit_status").is(":checked") ? 1 : 0);
            formData.set("featured", $("#edit_featured").is(":checked") ? 1 : 0);
            formData.set("badge", $("#edit_badge").is(":checked") ? 1 : 0);
            formData.set("edit_stripe_recurring", $("#edit_stripe_recurring").is(":checked") ? 1 : 0);
            formData.set("edit_paypal_recurring", $("#edit_stripe_recurring").is(":checked") ? 1 : 0);
            formData.set("promoted_jobber", $("#edit_promoted_jobber").is(":checked") ? 1 : 0);
            formData.set("promoted_service", $("#edit_promoted_service").is(":checked") ? 1 : 0);
            $.ajax({
                url: "/api/subscription-package/update",
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
                    $(".editPackageBtn").attr("disabled", true);
                    $(".editPackageBtn").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
                .done((response, statusText, xhr) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".editPackageBtn").removeAttr("disabled");
                    $(".editPackageBtn").html("update");
                    if (response.code === 200) {
                        toastr.success(response.message);
                        subscription_table();
                        $("#edit_subscription_package").modal("hide");
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".editPackageBtn").removeAttr("disabled");
                    $(".editPackageBtn").html("update");

                    if (error.status == 422) {
                        $.each(error.responseJSON, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(error.responseJSON.message, "bg-danger");
                    }
                });
        });
    });

    $(document).on("click", '.delete[data-bs-toggle="modal"]', function (e) {
        e.preventDefault();

        var subId = $(this).data("id");
        $("#confirmDelete").data("id", subId);
    });

    $(document).on("click", "#confirmDelete", function (e) {
        e.preventDefault();

        var subId = $(this).data("id");
        $.ajax({
            url: "/api/subscription-package/delete",
            type: "POST",
            data: {
                id: subId,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                toastr.success(response.message);
                subscription_table();
                $("#delete-modal").modal("hide");
            },
            error: function (xhr, status, error) {
                toastr.error("An error occurred while trying to delete.");
            },
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("add_subscription_package");
        const packageDurationInput =
            document.getElementById("package_duration");

            
        modal.addEventListener("hidden.bs.modal", function () {
            $("#subscription_type").val('regular').trigger('change');
            $(".error-text").text("");
            $(".form-control").removeClass("is-invalid");
            $(".editPackageBtn").removeAttr("disabled");
            $(".editPackageBtn").html("submit");
            document.getElementById("addSubscriptionForm").reset();
            document.getElementById("package_title_error").textContent = "";
            document.getElementById("price_error").textContent = "";
            document.getElementById("package_term_error").textContent = "";
            document.getElementById("package_duration_error").textContent = "";
            document.getElementById("number_of_service_error").textContent = "";
            document.getElementById(
                "number_of_feature_service_error"
            ).textContent = "";
            document.getElementById("number_of_product_error").textContent = "";
            document.getElementById("number_of_locations_error").textContent =
                "";
            document.getElementById("number_of_staff_error").textContent = "";
            document.getElementById("number_of_lead_error").textContent = "";
            document.getElementById("subscription_type_error").textContent = "";
            document.getElementById(
                "number_of_service_order_error"
            ).textContent = "";
            packageDurationInput.disabled = true;
            document.getElementById("status_error").textContent = "";
            document.getElementById("show_input").style.display = "block";
            document.getElementById("day_input").style.display = "none";
            document.getElementById("month_input").style.display = "none";
        });
    });
}

if (pageValue === "admin.payment-settings") {
    $("#generalTab a").on("click", function (e) {
        e.preventDefault();
        $(this).tab("show");
    });

    $(document).ready(function () {
        async function loadGeneralSettings() {
            const response = await $.ajax({
                url: "/api/admin/general-setting/list",
                type: "POST",
                data: { group_id: 13 },
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
            });

            if (response.code === 200) {
                const requiredKeys = [
                    "paypal_status",
                    "wallet_status",
                    "cod_status",
                    "paypal_id",
                    "paypal_secret",
                    "stripe_status",
                    "bank_status",
                    "paypal_live",
                    "stripe_secret",
                    "stripe_key",
                    "mollie_status",
                    "branch_code",
                    "account_number",
                    "bank_name",
                    "account_name",
                    "razorpay_key",
                    "razorpay_secret",
                    "razorpay_mode",
                    "razorpay_status",
                    "payu_status",
                    "payu_merchant_key",
                    "payu_merchant_salt",
                    "payu_base_url",
                    "payu_mode",
                    "cashfree_api_key",
                    "cashfree_api_secret",
                    "cashfree_mode",
                    "cashfree_status",
                    "authorizenet_status",
                    "authorizenet_api_login_id",
                    "authorizenet_transaction_key",
                    "authorizenet_env",
                    "paystack_status",
                    "paystack_public_key",
                    "paystack_secret_key",
                    "paystack_payment_url",
                    "paystack_callback_url",
                    "mercadopago_status",
                    "mercadopago_public_key",
                    "mercadopago_access_token",
                    "mercadopago_callback_url",
                    "stripe_webhook_secret",
                ];

                const filteredSettings = response.data.settings.filter(
                    (setting) => requiredKeys.includes(setting.key)
                );

                filteredSettings.forEach((setting) => {
                    $("#" + setting.key).val(setting.value);
                    if (
                        setting.key === "paypal_status" &&
                        setting.value === "1"
                    ) {
                        $("#paypal_status_show").prop("checked", true);
                    }
                    if (
                        setting.key === "wallet_status" &&
                        setting.value === "1"
                    ) {
                        $("#wallet_status_show").prop("checked", true);
                    }
                    if (
                        setting.key === "mollie_status" &&
                        setting.value === "1"
                    ) {
                        $("#moillie_status_show").prop("checked", true);
                    }
                    if (setting.key === "cod_status" && setting.value === "1") {
                        $("#cod_status_show").prop("checked", true);
                    }
                    if (
                        setting.key === "bank_status" &&
                        setting.value === "1"
                    ) {
                        $("#bank_status_show").prop("checked", true);
                    }
                    if (
                        setting.key === "razorpay_status" &&
                        setting.value === "1"
                    ) {
                        $("#razorpay_status").prop("checked", true);
                    }
                    if (
                        setting.key === "payu_status" &&
                        setting.value === "1"
                    ) {
                        $("#payu_status_toggle").prop("checked", true);
                    }
                    if (
                        setting.key === "cashfree_status" &&
                        setting.value === "1"
                    ) {
                        $("#cashfree_status_toggle").prop("checked", true);
                    }
                    if (
                        setting.key === "authorizenet_status" &&
                        setting.value === "1"
                    ) {
                        $("#authorizenet_status_toggle").prop("checked", true);
                    }
                    if (
                        setting.key === "paystack_status" &&
                        setting.value === "1"
                    ) {
                        $("#paystack_status_toggle").prop("checked", true);
                    }
                    if (
                        setting.key === "mercadopago_status" &&
                        setting.value === "1"
                    ) {
                        $("#mercadopago_status_toggle").prop("checked", true);
                    }
                    if (setting.key == "paypal_live" && setting.value == 1) {
                        $("#paypal_live").prop("checked", true);
                    }
                    if (
                        setting.key === "stripe_status" &&
                        setting.value === "1"
                    ) {
                        $("#stripe_status_show").prop("checked", true);
                    }
                });
            } else {
                toastr.error("Error fetching settings:", response.message);
            }

            $(".label-loader, .input-loader").hide();
            $(".real-label, .real-input").removeClass("d-none");
        }

        async function init() {
            await loadGeneralSettings();
        }
        init().catch((error) => {
            toastr.error("Error during initialization:", error);
        });

        $(document).on("click, change", "#paypal_status_show", function (e) {
            if ($("#paypal_status_show").prop("checked") == true) {
                var checkedstatus = 1;
            } else {
                var checkedstatus = 0;
            }

            $.ajax({
                url: "/api/admin/updatepaymentSettings",
                type: "POST",
                data: {
                    paypal_status: checkedstatus,
                    group_id: 13,
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
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error("An error occured while changing status");
                },
            });
        });

        $(document).on("click, change", "#wallet_status_show", function (e) {
            if ($("#wallet_status_show").prop("checked") == true) {
                var checkedstatus = 1;
            } else {
                var checkedstatus = 0;
            }

            $.ajax({
                url: "/api/admin/updatepaymentSettings",
                type: "POST",
                data: {
                    wallet_status: checkedstatus,
                    group_id: 13,
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
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error("An error occured while changing status");
                },
            });
        });

        $(document).on("click, change", "#cod_status_show", function (e) {
            if ($("#cod_status_show").prop("checked") == true) {
                var checkedstatus = 1;
            } else {
                var checkedstatus = 0;
            }

            $.ajax({
                url: "/api/admin/updatepaymentSettings",
                type: "POST",
                data: {
                    cod_status: checkedstatus,
                    group_id: 13,
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
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error("An error occured while changing status");
                },
            });
        });

        $(document).on("click, change", "#moillie_status_show", function (e) {
            if ($("#moillie_status_show").prop("checked") == true) {
                var checkedstatus = 1;
            } else {
                var checkedstatus = 0;
            }

            $.ajax({
                url: "/api/admin/updatepaymentSettings",
                type: "POST",
                data: {
                    moillie_status_show: checkedstatus,
                    group_id: 13,
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
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error("An error occured while changing status");
                },
            });
        });

        $(document).on("click, change", "#stripe_status_show", function (e) {
            if ($("#stripe_status_show").prop("checked") == true) {
                var checkedstatus = 1;
            } else {
                var checkedstatus = 0;
            }

            $.ajax({
                url: "/api/admin/updatepaymentSettings",
                type: "POST",
                data: {
                    stripe_status: checkedstatus,
                    group_id: 13,
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
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error("An error occured while changing status");
                },
            });
        });

        $(document).on("click, change", "#bank_status_show", function (e) {
            if ($("#bank_status_show").prop("checked") == true) {
                var checkedstatus = 1;
            } else {
                var checkedstatus = 0;
            }

            $.ajax({
                url: "/api/admin/updatepaymentSettings",
                type: "POST",
                data: {
                    bank_status: checkedstatus,
                    group_id: 13,
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
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error("An error occured while changing status");
                },
            });
        });

        $(document).on("click, change", "#razor_status_show", function (e) {
            if ($("#razor_status_show").prop("checked") == true) {
                var checkedstatus = 1;
            } else {
                var checkedstatus = 0;
            }

            $.ajax({
                url: "/api/admin/updatepaymentSettings",
                type: "POST",
                data: {
                    razorpay_status: checkedstatus,
                    group_id: 13,
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
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error("An error occured while changing status");
                },
            });
        });

        $("#RazorpaySettingForm").on("submit", function (e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.ajax({
                url: "/api/admin/updatepaymentSettings",
                type: "POST",
                data: formData,
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                beforeSend: function () {
                    $(".paypal_button").attr("disabled", true);
                    $(".paypal_button").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                    $("#connect_payment_razorpay").modal("hide");
                },
                success: function (response) {
                    $(".paypal_button").attr("disabled", false);
                    $(".paypal_button").html("Update");
                    $("#connect_payment_razorpay").modal("hide");

                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error("Error updating general settings");
                },
            });
        });

        $("#BankSettingForm").on("submit", function (e) {
            e.preventDefault();
            let formData = $(this).serialize();
            $.ajax({
                url: "/api/admin/updatepaymentSettings",
                type: "POST",
                data: formData,
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                beforeSend: function () {
                    $(".paypal_button").attr("disabled", true);
                    $(".paypal_button").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                    $("#connect_payment").modal("hide");
                },
                success: function (response) {
                    $(".paypal_button").attr("disabled", false);
                    $(".paypal_button").html("Update");
                    $("#connect_payment").modal("hide");

                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error("Error updating general settings");
                },
            });
        });

        $("#stiprSettingForm").on("submit", function (e) {
            e.preventDefault();
            let formData = $(this).serialize();
            $.ajax({
                url: "/api/admin/updatepaymentSettings",
                type: "POST",
                data: formData,
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                beforeSend: function () {
                    $(".paypal_button").attr("disabled", true);
                    $(".paypal_button").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                    $("#connect_payment_stripe").modal("hide");
                },
                success: function (response) {
                    $(".paypal_button").attr("disabled", false);
                    $(".paypal_button").html("Update");
                    $("#connect_payment_stripe").modal("hide");

                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error("Error updating general settings");
                },
            });
        });

        $("#PaypalSettingForm").on("submit", function (e) {
            e.preventDefault();

            let formArray = $(this).serializeArray();
            let foundPaypalLive = false;

            $.each(formArray, function (i, field) {
                if (field.name === "paypal_live") {
                    field.value = $("#paypal_live").is(":checked") ? 1 : 0;
                    foundPaypalLive = true;
                }
            });

            if (!foundPaypalLive) {
                formArray.push({
                    name: "paypal_live",
                    value: $("#paypal_live").is(":checked") ? 1 : 0,
                });
            }

            let formData = $.param(formArray);

            $.ajax({
                url: "/api/admin/updatepaymentSettings",
                type: "POST",
                data: formData,
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                beforeSend: function () {
                    $(".paypal_button").attr("disabled", true);
                    $(".paypal_button").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                    $("#connect_payment_paypal").modal("hide");
                },
                success: function (response) {
                    $(".paypal_button").attr("disabled", false);
                    $(".paypal_button").html("Update");
                    $("#connect_payment_paypal").modal("hide");
                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    toastr.error("Error updating general settings");
                },
            });
        });

        //RazerPay
        $("#razorpay_credentials_form").submit(function (event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: "/api/credential/save/razorpay", // Adjust to your backend route
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".razorpay_setting_btn")
                        .attr("disabled", true)
                        .html(
                            '<div class="spinner-border text-light" role="status"></div>'
                        );
                },
            })
                .done((response) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".razorpay_setting_btn")
                        .removeAttr("disabled")
                        .html($(".razorpay_setting_btn").data("update"));
                    if (response.code === 200) {
                        toastr.success(response.message);
                        $("#connect_payment_razorpay").modal("hide");
                    } else {
                        toastr.error(
                            response.message || "Something went wrong."
                        );
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".razorpay_setting_btn")
                        .removeAttr("disabled")
                        .html($(".razorpay_setting_btn").data("update"));

                    if (error.status === 422) {
                        $.each(error.responseJSON.errors, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(
                            error.responseJSON?.message ||
                                "An unexpected error occurred."
                        );
                    }
                });
        });

        $("#razorpay_status").on("change", function () {
            let razorpayStatus = $(this).is(":checked") ? 1 : 0;

            let formData = {
                razorpay_status: razorpayStatus,
            };

            $.ajax({
                url: "/api/credential/status/razorpay-status", // Adjust to your backend route
                type: "POST",
                data: formData,
                dataType: "json",
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error("Failed to update Razorpay status.");
                    }
                },
                error: function () {
                    toastr.error(
                        "An error occurred while updating Razorpay status."
                    );
                },
            });
        });

        //PayU
        $("#payu_credentials_form").submit(function (event) {
            event.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: "/api/credential/save/payu", // Your backend route for saving PayU credentials
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".payu_setting_btn")
                        .attr("disabled", true)
                        .html(
                            '<div class="spinner-border text-light" role="status"></div>'
                        );
                },
            })
                .done((response) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".payu_setting_btn")
                        .removeAttr("disabled")
                        .html($(".payu_setting_btn").data("update"));
                    if (response.code === 200) {
                        toastr.success(response.message);
                        $("#connect_payment_payu").modal("hide");
                    } else {
                        toastr.error(
                            response.message || "Something went wrong."
                        );
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".payu_setting_btn")
                        .removeAttr("disabled")
                        .html($(".payu_setting_btn").data("update"));

                    if (error.status === 422) {
                        $.each(error.responseJSON.errors, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(
                            error.responseJSON?.message ||
                                "An unexpected error occurred."
                        );
                    }
                });
        });

        $("#payu_status_toggle").on("change", function () {
            let payuStatus = $(this).is(":checked") ? 1 : 0;

            let formData = {
                payu_status: payuStatus,
            };

            $.ajax({
                url: "/api/credential/status/payu-status", // Your backend route for PayU status
                type: "POST",
                data: formData,
                dataType: "json",
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"), // Or your auth method
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error("Failed to update PayU status.");
                    }
                },
                error: function () {
                    toastr.error(
                        "An error occurred while updating PayU status."
                    );
                },
            });
        });

        //Cashfree
        $("#cashfree_credentials_form").submit(function (event) {
            event.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: "/api/credential/save/cashfree", // Backend route for saving Cashfree credentials
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".cashfree_setting_btn")
                        .attr("disabled", true)
                        .html(
                            '<div class="spinner-border text-light" role="status"></div>'
                        );
                },
            })
                .done((response) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".cashfree_setting_btn")
                        .removeAttr("disabled")
                        .html($(".cashfree_setting_btn").data("update"));
                    if (response.code === 200) {
                        toastr.success(response.message);
                        $("#connect_payment_cashfree").modal("hide");
                    } else {
                        toastr.error(
                            response.message || "Something went wrong."
                        );
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".cashfree_setting_btn")
                        .removeAttr("disabled")
                        .html($(".cashfree_setting_btn").data("update"));

                    if (error.status === 422) {
                        $.each(error.responseJSON.errors, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(
                            error.responseJSON?.message ||
                                "An unexpected error occurred."
                        );
                    }
                });
        });

        $("#cashfree_status_toggle").on("change", function () {
            let cashfreeStatus = $(this).is(":checked") ? 1 : 0;

            let formData = {
                cashfree_status: cashfreeStatus,
            };

            $.ajax({
                url: "/api/credential/status/cashfree-status", // Backend route for Cashfree status
                type: "POST",
                data: formData,
                dataType: "json",
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"), // Or your auth method
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error("Failed to update Cashfree status.");
                    }
                },
                error: function () {
                    toastr.error(
                        "An error occurred while updating Cashfree status."
                    );
                },
            });
        });

        //Authorize.Net
        $("#authorizenet_credentials_form").submit(function (event) {
            event.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: "/api/credential/save/authorizenet", // Backend route
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".authorizenet_setting_btn")
                        .attr("disabled", true)
                        .html(
                            '<div class="spinner-border text-light" role="status"></div>'
                        );
                },
            })
                .done((response) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".authorizenet_setting_btn")
                        .removeAttr("disabled")
                        .html($(".authorizenet_setting_btn").data("update"));
                    if (response.code === 200) {
                        toastr.success(response.message);
                        $("#connect_payment_authorizenet").modal("hide");
                    } else {
                        toastr.error(
                            response.message || "Something went wrong."
                        );
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".authorizenet_setting_btn")
                        .removeAttr("disabled")
                        .html($(".authorizenet_setting_btn").data("update"));

                    if (error.status === 422) {
                        $.each(error.responseJSON.errors, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(
                            error.responseJSON?.message ||
                                "An unexpected error occurred."
                        );
                    }
                });
        });

        $("#authorizenet_status_toggle").on("change", function () {
            let authorizenetStatus = $(this).is(":checked") ? 1 : 0;

            let formData = {
                authorizenet_status: authorizenetStatus,
            };

            $.ajax({
                url: "/api/credential/status/authorizenet-status", // Backend route for status
                type: "POST",
                data: formData,
                dataType: "json",
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error("Failed to update Authorize.Net status.");
                    }
                },
                error: function () {
                    toastr.error(
                        "An error occurred while updating Authorize.Net status."
                    );
                },
            });
        });

        //Paystack
        $("#paystack_credentials_form").submit(function (event) {
            event.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: "/api/credential/save/paystack", // Backend route
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".paystack_setting_btn")
                        .attr("disabled", true)
                        .html(
                            '<div class="spinner-border text-light" role="status"></div>'
                        );
                },
            })
                .done((response) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".paystack_setting_btn")
                        .removeAttr("disabled")
                        .html($(".paystack_setting_btn").data("update"));
                    if (response.code === 200) {
                        toastr.success(response.message);
                        $("#connect_payment_paystack").modal("hide");
                    } else {
                        toastr.error(
                            response.message || "Something went wrong."
                        );
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".paystack_setting_btn")
                        .removeAttr("disabled")
                        .html($(".paystack_setting_btn").data("update"));

                    if (error.status === 422) {
                        $.each(error.responseJSON.errors, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(
                            error.responseJSON?.message ||
                                "An unexpected error occurred."
                        );
                    }
                });
        });

        $("#paystack_status_toggle").on("change", function () {
            let paystackStatus = $(this).is(":checked") ? 1 : 0;

            let formData = {
                paystack_status: paystackStatus,
            };

            $.ajax({
                url: "/api/credential/status/paystack-status", // Backend route for status
                type: "POST",
                data: formData,
                dataType: "json",
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error("Failed to update Paystack status.");
                    }
                },
                error: function () {
                    toastr.error(
                        "An error occurred while updating Paystack status."
                    );
                },
            });
        });

        // MercadoPago
        $("#mercadopago_credentials_form").submit(function (event) {
            event.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: "/api/credential/save/mercadopago", // Backend route
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".mercadopago_setting_btn")
                        .attr("disabled", true)
                        .html(
                            '<div class="spinner-border text-light" role="status"></div>'
                        );
                },
            })
                .done((response) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".mercadopago_setting_btn")
                        .removeAttr("disabled")
                        .html($(".mercadopago_setting_btn").data("update"));
                    if (response.code === 200) {
                        toastr.success(response.message);
                        $("#connect_payment_mercadopago").modal("hide");
                    } else {
                        toastr.error(
                            response.message || "Something went wrong."
                        );
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".mercadopago_setting_btn")
                        .removeAttr("disabled")
                        .html($(".mercadopago_setting_btn").data("update"));

                    if (error.status === 422) {
                        $.each(error.responseJSON.errors, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(
                            error.responseJSON?.message ||
                                "An unexpected error occurred."
                        );
                    }
                });
        });

        $("#mercadopago_status_toggle").on("change", function () {
            let mercadopagoStatus = $(this).is(":checked") ? 1 : 0;

            let formData = {
                mercadopago_status: mercadopagoStatus,
            };

            $.ajax({
                url: "/api/credential/status/mercadopago-status", // Backend route for status
                type: "POST",
                data: formData,
                dataType: "json",
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error("Failed to update Mercado Pago status.");
                    }
                },
                error: function () {
                    toastr.error(
                        "An error occurred while updating Mercado Pago status."
                    );
                },
            });
        });
    });
}

//file-storage
if (pageValue === "admin.file-storage") {
    function toggleCheckbox(currentId, otherId) {
        const currentCheckbox = document.getElementById(currentId);
        const otherCheckbox = document.getElementById(otherId);

        if (currentCheckbox.checked) {
            otherCheckbox.checked = false;
        }
    }

    $(document).ready(function () {
        loadAwsSetting();
    });

    function loadAwsSetting() {
        $.ajax({
            url: "/api/file-storage/list",
            type: "POST",
            data: { group_id: 20 },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    const requiredKeys = [
                        "aws_access_key",
                        "aws_secret_access_key",
                        "aws_region",
                        "aws_bucket",
                        "aws_url",
                        "aws_status",
                        "local_status",
                    ];

                    const filteredSettings = response.data.settings.filter(
                        (setting) => requiredKeys.includes(setting.key)
                    );

                    filteredSettings.forEach((setting) => {
                        if (setting.key === "aws_status") {
                            if (setting.value === "1") {
                                $("#aws_status").prop("checked", true);
                            } else {
                                $("#aws_status").prop("checked", false);
                            }
                        } else {
                            $("#" + setting.key).val(setting.value);
                        }
                    });

                    filteredSettings.forEach((setting) => {
                        if (setting.key === "local_status") {
                            if (setting.value === "1") {
                                $("#local_status").prop("checked", true);
                            } else {
                                $("#local_status").prop("checked", false);
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
        $("#fileStorageForm").submit(function (event) {
            event.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                url: "/api/file-storage/save/aws",
                method: "POST",
                data: formData,
                dataType: "json",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $(".aws_setting_btn").attr("disabled", true);
                    $(".aws_setting_btn").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
                .done((response, statusText, xhr) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".aws_setting_btn").removeAttr("disabled");
                    const updateText =
                        document.getElementById("btn_file").dataset.updateText;
                    document.getElementById("btn_file").innerText = updateText;
                    if (response.code === 200) {
                        toastr.success(response.message);
                        loadCredentialSetting();
                        $("#add_google_captacha").modal("hide");
                    } else {
                        toastr.error(response.message);
                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".aws_setting_btn").removeAttr("disabled");
                    const updateText =
                        document.getElementById("btn_file").dataset.updateText;
                    document.getElementById("btn_file").innerText = updateText;

                    if (error.status == 422) {
                        $.each(error.responseJSON.errors, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(responseJSON.message);
                    }
                });
        });
    });

    $(document).ready(function () {
        $("#local_status").on("change", function () {
            let localStatus = $(this).is(":checked") ? 1 : 0;

            let formData = {
                local_status: localStatus,
            };

            $.ajax({
                url: "/api/file-storage/status/local",
                type: "POST",
                data: formData,
                dataType: "json",
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error("Failed to update captcha status.");
                    }
                },
                error: function (error) {
                    console.error("Error updating captcha status:", error);
                    toastr.error("An error occurred while updating.");
                },
            });
        });
    });

    $(document).ready(function () {
        $("#aws_status").on("change", function () {
            let awsStatus = $(this).is(":checked") ? 1 : 0;

            let formData = {
                aws_status: awsStatus,
            };

            $.ajax({
                url: "/api/file-storage/status/aws",
                type: "POST",
                data: formData,
                dataType: "json",
                headers: {
                    Authorization:
                        "Bearer " + localStorage.getItem("admin_token"),
                    Accept: "application/json",
                },
                success: function (response) {
                    if (response.code === 200) {
                        toastr.success(response.message);
                    } else {
                        toastr.error("Failed to update captcha status.");
                    }
                },
                error: function (error) {
                    console.error("Error updating captcha status:", error);
                    toastr.error("An error occurred while updating.");
                },
            });
        });
    });
}

if (pageValue === 'admin.clear-cache-settings') {
    $("#clear-cache").on("click", function () {
        $.ajax({
            type: "POST",
            url: "/admin/setting/clear-cache-data",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (resp) {
                toastr.success(resp.message);
                $("#clear_cache").modal('hide');
                setTimeout(function () {
                    location.reload();
                }, 500);
            },
            error: function (error) {
                toastr.success(
                    error.responseJSON.message || "Error while clearing cache"
                );
            },
        });
    });
}