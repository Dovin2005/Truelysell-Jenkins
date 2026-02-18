var frontendValue = $('body').data('frontend');
const leadLanguageId = $('#language-settings').data('language-id');
const leadStatus = $('#lead-settings').data('lead-status');

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
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
};

$(document).on("click", ".provider-details-link", function (e) {
    e.preventDefault();

    const providerId = $(this).data("provider-id");
    const userId = $('body').data("authid");

    if (!userId) {
        $("#login-modal").modal("show");
        return;
    }

    if (providerId) {
        localStorage.setItem("provider_detail_id", providerId);

        const providerDetailsUrl = $(this).attr("href");
        window.location.href = providerDetailsUrl;
    } else {
        console.error("Provider ID is not valid.");
    }
});

if (frontendValue === 'home') {

    $(document).ready(function () {
        $('#categoryDropdown').change(function () {
            $('#modal-body-content').html('');
            formData = {};
            currentSectionIndex = 0;
            $('#continue-btn').text('Continue');
        });
        let formData = {};
        let currentSectionIndex = 0;

        function updateBackButtonVisibility() {
            if (currentSectionIndex === 0) {
                $('#back-btn').hide();
            } else {
                $('#back-btn').show();
            }
        }

        updateBackButtonVisibility();

        $('#back-btn').click(function () {
            if (currentSectionIndex > 0) {
                const formFields = $('#modal-body-content').children('.mb-3');
                const currentField = $(formFields[currentSectionIndex]);

                saveCurrentFieldData(currentField);

                currentField.hide();
                currentSectionIndex--;
                $(formFields[currentSectionIndex]).show();

                updateBackButtonVisibility();
                $('#continue-btn').text('Continue');
            }
        });

        function saveCurrentFieldData(currentField) {
            const inputField = currentField.find('input, select, textarea');
            const questionId = inputField.data('question-id');
            let inputValue;

            if (inputField.length > 0) {
                if (inputField.attr('id')?.startsWith('country_')) {
                    // Handle location type
                    const inputId = inputField.attr('id').split('_')[1];
                    const selectedCountryName = $(`#country_${inputId} option:selected`).text();
                    const selectedStateName = $(`#state_${inputId} option:selected`).text();
                    const selectedCityName = $(`#city_${inputId} option:selected`).text();

                    if (selectedCountryName && selectedStateName && selectedCityName) {
                        formData[`${inputId}`] = {
                            country: selectedCountryName,
                            state: selectedStateName,
                            city: selectedCityName,
                        };
                    }
                } else if (inputField.is(':checkbox')) {
                    // Handle checkbox
                    const selectedOptions = [];
                    const otherFieldName = `${inputField.attr('name')}_other`;

                    inputField.each(function () {
                        if ($(this).is(':checked')) {
                            if ($(this).val() === 'other') {
                                const otherValue = currentField.find(`input[name="${otherFieldName}"]`).val();
                                selectedOptions.push(otherValue || 'Other');
                            } else {
                                selectedOptions.push($(this).val());
                            }
                        }
                    });

                    if (selectedOptions.length > 0 && questionId) {
                        formData[questionId] = selectedOptions;
                    }
                } else if (inputField.is(':radio')) {
                    // Handle radio button
                    const selectedRadio = inputField.filter(':checked').val();
                    if (selectedRadio === 'other') {
                        const otherValue = currentField.find(`input[name="${inputField.attr('name')}_other"]`).val();
                        inputValue = otherValue || 'Other';
                    } else {
                        inputValue = selectedRadio;
                    }

                    if (inputValue && questionId) {
                        formData[questionId] = inputValue;
                    }
                } else if (inputField.attr('type') === 'file') {
                    // Handle file input
                    const fileInput = inputField[0];
                    const file = fileInput.files[0];

                    if (file) {
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = function () {
                            const base64String = reader.result.split(',')[1]; // Extract Base64 content
                            if (questionId) {
                                formData[questionId] = base64String;
                            }
                        };
                    }
                } else {
                    // Handle other input types
                    inputValue = inputField.val();
                    if (inputValue && questionId) {
                        formData[questionId] = inputValue;
                    }
                }
            }
        }
        $('#searchForm').submit(function (e) {
            e.preventDefault();

            const categoryId = $('#categoryDropdown').val();
            const location = $('input[name="location"]').val();
            if (leadStatus === 0) {
                let selectedOption = $(this).find(":selected");
                let categorySlug = selectedOption.data("slug");
                window.location.href = `/services/${categorySlug}`;
            }
            if (!categoryId) {
                toastr.error('Please select a category before searching.');
                return;
            }
            if (leadStatus === 1) {
            $('#modal-body-content').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');

            $.ajax({
                url: '/api/leads/user/list',
                type: 'POST',
                data: { category_id: categoryId },
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.code === 200) {
                        const subCategories = response.data.sub_categories;
                        const inputs = response.data.form_inputs;
                        if (inputs.length === 0) {
                            getUserIdAndSubmitForm();
                            return;
                        }

                        let formHtml = '';

                        if (subCategories && subCategories.length > 0) {
                            formHtml += '<div class="mb-3">';
                            formHtml += '<label class="form-label">Choose Subcategory:</label>';
                            subCategories.forEach(function (subCategory) {
                                formHtml += `
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="subcategory" data-question-id="sub_category_id" id="subcategory_${subCategory.id}" value="${subCategory.id}">
                                        <label class="form-check-label" for="subcategory_${subCategory.id}">${subCategory.name}</label>
                                    </div>`;
                            });
                            formHtml += '</div>';
                        }

                        if (inputs && inputs.length > 0) {
                            inputs.forEach(function (input) {
                                let inputHtml = `<div class="mb-3" style="display: none;">
                                    <label class="form-label">${input.title}</label>`;
                                    switch (input.type) {
                                        case 'text_field':
                                            inputHtml += `<input type="text" class="form-control" name="${input.name}" placeholder="${input.placeholder || ''}"
                                                           data-question-id="${input.id}" required="${input.is_required == 1}">`;
                                            break;
                                        case 'number_field':
                                            inputHtml += `<input type="number" class="form-control" name="${input.name}" placeholder="${input.placeholder || ''}"
                                                           data-question-id="${input.id}" min="${input.min_value || ''}" max="${input.max_value || ''}"
                                                           step="${input.step_value || '1'}" required="${input.is_required == 1}">`;
                                            break;
                                        case 'textarea':
                                            inputHtml += `<textarea class="form-control" name="${input.name}" placeholder="${input.placeholder || ''}"
                                                           data-question-id="${input.id}" required="${input.is_required == 1}"></textarea>`;
                                            break;
                                        case 'select':
                                            const selectOptions = JSON.parse(input.options || '[]');
                                            inputHtml += `<select class="form-control" name="${input.name}" data-question-id="${input.id}" required="${input.is_required == 1}">`;
                                            selectOptions.forEach(option => {
                                                inputHtml += `<option value="${option.value}">${option.key}</option>`;
                                            });
                                            inputHtml += `</select>`;
                                            break;
                                        case 'checkbox':
                                            const checkboxOptions = JSON.parse(input.options || '[]');
                                            checkboxOptions.forEach((option, index) => {
                                                inputHtml += `<div class="form-check">
                                                                <input type="checkbox" class="form-check-input" name="${input.name}[]" value="${option.value}"
                                                                       data-question-id="${input.id}">
                                                                <label class="form-check-label">${option.key}</label>
                                                              </div>`;
                                                if (input.other_option == 1 && index === checkboxOptions.length - 1) {
                                                    inputHtml += `<div class="form-check mt-2">
                                                                    <input type="checkbox" class="form-check-input" name="${input.name}" value="other"
                                                                            data-question-id="${input.id}">
                                                                    <input type="text" class="form-control mt-1" name="${input.name}_other" placeholder="Others"
                                                                            data-question-id="${input.id}" >
                                                                  </div>`;

                                                }
                                            });
                                            break;
                                        case 'radio':
                                                const radioOptions = JSON.parse(input.options || '[]');
                                                radioOptions.forEach((option, index) => {
                                                    inputHtml += `<div class="form-check">
                                                                    <input type="radio" class="form-check-input" name="${input.name}" value="${option.value}"
                                                                           data-question-id="${input.id}">
                                                                    <label class="form-check-label">${option.key}</label>
                                                                  </div>`;
                                                    if (input.other_option == 1 && index === radioOptions.length - 1) {
                                                        inputHtml += `<div class="form-check mt-2">
                                                                        <input type="radio" class="form-check-input" name="${input.name}" value="other"
                                                                               data-question-id="${input.id}">
                                                                        <input type="text" class="form-control mt-1" name="${input.name}_other" placeholder="Others"
                                                                               data-question-id="${input.id}" >
                                                                      </div>`;
                                                    }
                                                });
                                            break;
                                        case 'datepicker':
                                            inputHtml += `<input type="date" class="form-control" name="${input.name}" data-question-id="${input.id}"
                                                           required="${input.is_required == 1}">`;
                                            break;
                                        case 'timepicker':
                                            inputHtml += `<input type="time" class="form-control" name="${input.name}" data-question-id="${input.id}"
                                                           required="${input.is_required == 1}">`;
                                            break;
                                        case 'file':
                                            inputHtml += `<input type="file" class="form-control" name="${input.name}" data-question-id="${input.id}"
                                                           required="${input.is_required == 1}">`;
                                            break;
                                        case 'location':
                                                inputHtml += `
                                                    <select class="form-control mb-2" name="${input.name}_country" id="country_${input.id}" required="${input.is_required == 1}">
                                                        <option value="">Select Country</option>
                                                    </select>
                                                    <select class="form-control mb-2" name="${input.name}_state" id="state_${input.id}" required="${input.is_required == 1}">
                                                        <option value="">Select State</option>
                                                    </select>
                                                    <select class="form-control" name="${input.name}_city" id="city_${input.id}" required="${input.is_required == 1}">
                                                        <option value="">Select City</option>
                                                    </select>`;

                                                // Fetch and populate country dropdown
                                                $.getJSON('/countries.json', function(data) {
                                                    const countryDropdown = $(`#country_${input.id}`);
                                                    $.each(data.countries, function(index, country) {
                                                        countryDropdown.append($('<option>', { value: country.id, text: country.name }));
                                                    });
                                                }).fail(function() {
                                                    console.error('Error loading country data');
                                                });

                                                // Handle country change
                                                $(document).off('change', `#country_${input.id}`).on('change', `#country_${input.id}`, function() {
                                                    const selectedCountry = $(this).val();
                                                    const stateDropdown = $(`#state_${input.id}`);
                                                    const cityDropdown = $(`#city_${input.id}`);

                                                    // Reset state and city dropdowns
                                                    stateDropdown.empty().append($('<option>', { value: '', text: 'Select State' }));
                                                    cityDropdown.empty().append($('<option>', { value: '', text: 'Select City' }));

                                                    if (selectedCountry) {
                                                        // Fetch states based on selected country
                                                        $.getJSON(`/states.json?country_id=${selectedCountry}`, function(data) {
                                                            if (data && Array.isArray(data.states) && data.states.length > 0) {
                                                                $.each(data.states, function(index, state) {
                                                                    if (state.country_id === selectedCountry) {
                                                                        stateDropdown.append($('<option>', { value: state.id, text: state.name }));
                                                                    }
                                                                });
                                                            } else {
                                                                stateDropdown.append($('<option>', { value: '', text: 'No states available', disabled: true }));
                                                            }
                                                        }).fail(function(xhr, status, error) {
                                                            console.error('Error loading state data:', error);
                                                            stateDropdown.append($('<option>', { value: '', text: 'Error loading states', disabled: true }));
                                                        });
                                                    }
                                                });

                                                $(document).off('change', `#state_${input.id}`).on('change', `#state_${input.id}`, function () {
                                                    const selectedState = $(this).val();
                                                    const cityDropdown = $(`#city_${input.id}`);

                                                    cityDropdown.empty().append($('<option>', { value: '', text: 'Select City' }));

                                                    if (selectedState) {
                                                        $.getJSON(`/cities.json?state_id=${selectedState}`, function (data) {
                                                            if (data && data.cities && data.cities.length > 0) {
                                                                $.each(data.cities, function (index, city) {
                                                                    if (city.state_id === selectedState) { // Ensure state_id matches
                                                                        cityDropdown.append($('<option>', { value: city.id, text: city.name }));
                                                                    }
                                                                });
                                                            } else {
                                                                cityDropdown.append($('<option>', { value: '', text: 'No cities available', disabled: true }));
                                                            }
                                                        }).fail(function () {
                                                            console.error('Error loading city data');
                                                        });
                                                    }
                                                });
                                                break;
                                        default:
                                            console.warn(`Unsupported input type: ${input.type}`);
                                            break;
                                    }

                                inputHtml += '</div>';
                                formHtml += inputHtml;
                            });
                        }

                        $('#modal-body-content').html(formHtml);
                        $('#modal-body-content .mb-3').first().show();
                        $('#add-offer').modal('show');
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function () {
                    toastr.error('An error occurred while loading form inputs.');
                },
                complete: function () {
                    $('#modal-body-content').find('.spinner-border').remove();
                }
            });
            }

            function getUserIdAndSubmitForm() {
                $.ajax({
                    url: '/api/get-session-user-id',
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                        'Accept': 'application/json',
                    },
                    success: function (response) {
                        if (response.user_id) {
                            const userId = response.user_id;
                            localStorage.setItem('user_id', userId);
                            submitFormData(userId);
                        } else {
                            registerNewUser();
                        }
                    },
                    error: function () {
                        toastr.error('Unable to fetch session data. Please try again.');
                    }
                });
            }

            function submitFormData(userId) {
                const categoryId = $('#categoryDropdown').val();
                const subCategoryId = $('input[name="subcategory"]:checked').val();

                // if (!subCategoryId) {
                //     toastr.error('Please select a subcategory before submitting.');
                //     return;
                // }

                const payload = {
                    user_id: userId,
                    category_id: categoryId,
                    sub_category_id: subCategoryId,
                    form_inputs: Object.keys(formData).map(key => ({
                        id: key,
                        value: formData[key]
                    }))
                };

                $.ajax({
                    url: '/api/leads/user/store',
                    type: 'POST',
                    data: JSON.stringify(payload),
                    contentType: 'application/json',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.code === 200) {
                            $('#add-offer').modal('hide');
                            $('#modal-body-content').html('');
                            $('#categoryDropdown').val(null);
                            formData = {};
                            currentSectionIndex = 0;
                            $('#continue-btn').text('Continue');
                            if (leadLanguageId === 2) {
                                loadJsonFile('Form submitted successfully', function (langtst) {
                                    toastr.success(langtst);
                                });
                            }else{
                                toastr.success('Form submitted successfully');
                            }
                            // toastr.success('Form submitted successfully.');

                            const categoryId = response.data.category_id;
                            const leadsId = response.data.id;
                            localStorage.setItem('selected_category_id', categoryId);
                            localStorage.setItem('selected_leads_id', leadsId);
                            // console.log(leadsId);

                            window.location.href = '/user/provider';
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('An error occurred while submitting the form.');
                    }
                });
            }
            function registerNewUser() {
                $('#register-modal').modal('show');
            }
        });

        $('#continue-btn').click(function () {
            const formFields = $('#modal-body-content').children('.mb-3');
            const currentField = $(formFields[currentSectionIndex]);

            const inputField = currentField.find('input, select, textarea');
            const questionId = inputField.data('question-id');
            let inputValue;
            let isValid = true;
            // Validate input
            if (inputField.length > 0) {
                if (inputField.attr('id')?.startsWith('country_')) {
                    // Location type validation
                    const inputId = inputField.attr('id').split('_')[1];
                    const selectedCountryId = $(`#country_${inputId}`).val();
                    const selectedStateId = $(`#state_${inputId}`).val();
                    const selectedCityId = $(`#city_${inputId}`).val();

                    const selectedCountryName = $(`#country_${inputId} option:selected`).text();
                    const selectedStateName = $(`#state_${inputId} option:selected`).text();
                    const selectedCityName = $(`#city_${inputId} option:selected`).text();

                    if (!selectedCountryId || selectedCountryId === 'Select Country') {
                        isValid = false;
                        toastr.error('Please select a valid country.');
                    } else if (!selectedStateId || selectedStateId === 'Select State') {
                        isValid = false;
                        toastr.error('Please select a valid state.');
                    } else if (!selectedCityId || selectedCityId === 'Select City') {
                        isValid = false;
                        toastr.error('Please select a valid city.');
                    }

                    if (isValid) {
                        formData[`${inputId}`] = {
                            country: selectedCountryName,
                            state: selectedStateName,
                            city: selectedCityName,
                        };
                    }
                } else if (inputField.is(':checkbox')) {
                    // Checkbox validation
                    const selectedOptions = [];
                    const otherFieldName = `${inputField.attr('name')}_other`;

                    inputField.each(function () {
                        if ($(this).is(':checked')) {
                            if ($(this).val() === 'other') {
                                const otherValue = currentField.find(`input[name="${otherFieldName}"]`).val();
                                selectedOptions.push(otherValue || 'Other');
                            } else {
                                selectedOptions.push($(this).val());
                            }
                        }
                    });

                    if (selectedOptions.length === 0) {
                        isValid = false;
                        toastr.error('Please select at least one option.');
                    } else if (questionId) {
                        formData[questionId] = selectedOptions;
                    }
                } else if (inputField.is(':radio')) {
                    // Radio button validation
                    const selectedRadio = inputField.filter(':checked').val();

                    if (!selectedRadio) {
                        isValid = false;
                        toastr.error('Please select an option.');
                    } else if (selectedRadio === 'other') {
                        const otherValue = currentField.find(`input[name="${inputField.attr('name')}_other"]`).val();
                        if (!otherValue) {
                            isValid = false;
                            toastr.error('Please enter a value for the "Other" option.');
                        } else {
                            inputValue = otherValue;
                        }
                    } else {
                        inputValue = selectedRadio;
                    }

                    if (isValid && questionId) {
                        formData[questionId] = inputValue;
                    }
                } else if (inputField.attr('type') === 'file') {
                    const fileInput = inputField[0];
                    const file = fileInput.files[0];

                    if (file) {
                        const allowedExtensions = ['png', 'jpeg', 'jpg', 'pdf', 'doc', 'docx'];
                        const allowedMimeTypes = [
                            'image/png',
                            'image/jpeg',
                            'application/pdf',
                            'application/msword', // DOC
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' // DOCX
                        ];

                        const fileExtension = file.name.split('.').pop().toLowerCase();
                        const fileType = file.type;

                        if (!allowedExtensions.includes(fileExtension) || !allowedMimeTypes.includes(fileType)) {
                            isValid = false;
                            toastr.error('Invalid file type. Only PNG, JPEG, JPG, PDF, and DOC are allowed.');
                        } else {
                            const reader = new FileReader();
                            reader.readAsDataURL(file);
                            reader.onload = function () {
                                const base64String = reader.result.split(',')[1]; // Extract Base64 content
                                if (questionId) {
                                    formData[questionId] = base64String;
                                }
                            };
                        }
                    } else {
                        isValid = false;
                        toastr.error('Please select a file.');
                    }
                } else {
                    inputValue = inputField.val();
                    if (!inputValue) {
                        isValid = false;
                        toastr.error('Please fill in this field.');
                    } else if (questionId) {
                        formData[questionId] = inputValue;
                    }
                }
            }

            // Proceed if input is valid
            if (isValid) {
                // Navigate to the next section
                if (currentSectionIndex < formFields.length - 1) {
                    currentField.hide();
                    currentSectionIndex++;
                    $(formFields[currentSectionIndex]).show();

                    // Update button text
                    if (currentSectionIndex === formFields.length - 1) {
                        $(this).text('Submit');
                    } else {
                        $(this).text('Continue');
                    }
                    $('#back-btn').show();
                } else {
                    // Handle form submission
                    let userId ;

                    if (!userId) {
                        $.ajax({
                            url: '/api/get-session-user-id',
                            type: 'GET',
                            success: function (response) {
                                if (response.user_id) {
                                    userId = response.user_id;
                                    localStorage.setItem('user_id', userId);
                                    submitFormData(userId);
                                } else {
                                    registerNewUser();
                                }
                            },
                            error: function () {
                                toastr.error('Unable to fetch session data. Please try again.');
                            },
                        });
                    } else {
                        submitFormData(userId);
                    }
                }
            } else {
                // Disable the button if invalid
                $(this).prop('disabled', true);
                setTimeout(() => {
                    $(this).prop('disabled', false);
                }, 2000); // Re-enable after 2 seconds
            }

            function registerNewUser() {
                $('#register-modal').modal('show');
            }

            $(document).ready(function () {
                $('#register-form').off('submit').on('submit', function (e) {
                    e.preventDefault();

                    const formData = {
                        first_name: $('#first_name').val(),
                        email: $('#email').val(),
                        password: $('#password').val(),
                        _token: $('input[name="_token"]').val()
                    };

                    $.ajax({
                        url: '/api/userregister',
                        type: 'POST',
                        data: formData,
                        success: function (response) {
                            if (response.code === 200) {
                                localStorage.setItem('user_id', response.user_id);
                                console.log(response.user_id);

                                submitFormData(response.user_id);
                                $('#register-modal').modal('hide');

                                toastr.success('Registration successful!');

                            } else {
                                toastr.error('Registration failed. Please try again.');
                            }
                        },
                        error: function (xhr) {
                            const response = xhr.responseJSON;
                            if (response && response.message) {
                                toastr.error(response.message);
                            } else {
                                toastr.error('An error occurred while registering the user.');
                            }
                        }
                    });
                });
            });

            function submitFormData(userId) {
                const categoryId = $('#categoryDropdown').val();
                const subCategoryId = $('input[name="subcategory"]:checked').val();

                // if (!subCategoryId) {
                //     toastr.error('Please select a subcategory before submitting.');
                //     return;
                // }

                const payload = {
                    user_id: userId,
                    category_id: categoryId,
                    sub_category_id: subCategoryId,
                    form_inputs: Object.keys(formData).map(key => ({
                        id: key,
                        value: formData[key]
                    }))
                };

                $.ajax({
                    url: '/api/leads/user/store',
                    type: 'POST',
                    data: JSON.stringify(payload),
                    contentType: 'application/json',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.code === 200) {
                            $('#add-offer').modal('hide');
                            $('#modal-body-content').html('');
                            $('#categoryDropdown').val(null);
                            formData = {};
                            currentSectionIndex = 0;
                            $('#continue-btn').text('Continue');
                            toastr.success('Form submitted successfully.');

                            const categoryId = response.data.category_id;
                            const leadsId = response.data.id;
                            localStorage.setItem('selected_category_id', categoryId);
                            localStorage.setItem('selected_leads_id', leadsId);
                            // console.log(leadsId);

                            window.location.href = '/user/provider';
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('An error occurred while submitting the form.');
                    }
                });
            }
        });

    });

}

//modal loader
$(function(){
    $('#openModalBtn').on('click', function(event) {
        $('#modal-loading').modal('show'),1000;
    })
  });


  function searchInJson(keyToSearch, jsonData) {
    keyToSearch = keyToSearch.toLowerCase();
    let result = '';

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
    const jsonFilePath = '/lang/ar.json';
    $.getJSON(jsonFilePath, function (data) {
        let lang = searchInJson(searchKey, data);
        callback(lang);
    }).fail(function () {
        alert('Failed to load JSON file.');
    });
}
