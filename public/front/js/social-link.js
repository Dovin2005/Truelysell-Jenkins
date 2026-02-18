$(document).ready(function() {
    // First, fetch the social links data when page loads
    fetchSocialLinks();

    // Handle form submission
    $('form').on('submit', function(e) {
        e.preventDefault();

        // Perform validation before submitting
        if (validateForm($(this))) {
            submitForm($(this));
        }
    });

    // Initialize toastr notifications
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000"
        };
    }
});

// New validation function
function validateForm(form) {
    let allEmpty = true;

    // Check each URL field
    form.find('input[type="url"]').each(function() {
        if ($(this).val().trim() !== '') {
            allEmpty = false;
            return false; // Break out of the loop early if we find a non-empty field
        }
    });

    if (allEmpty) {
        toastr.error('Please provide at least one social media URL');
        return false;
    }

    return true;
}

function fetchSocialLinks() {
    $.ajax({
        url: '/provider/get/social-links',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                updateFormWithSocialLinks(response.data);
            }
        },
        error: function(xhr) {
            console.error('Error fetching social links:', xhr.responseText);
        }
    });
}

function updateFormWithSocialLinks(socialLinks) {
    socialLinks.forEach(link => {
        // Find all rows in the form
        $('.row.align-items-center').each(function() {
            const $row = $(this);
            const platformName = $row.find('input[type="text"][disabled]').val().trim();
            const socialLinkPlatform = link.social_link ? link.social_link.platform_name : link.platform_name;

            // Check if platform names match (case insensitive)
            if (platformName.toLowerCase() === socialLinkPlatform.toLowerCase()) {
                // Update URL field
                $row.find('input[type="url"]').val(link.link);

                // Update status checkbox
                const $checkbox = $row.find('input[type="checkbox"]');
                $checkbox.prop('checked', link.status === 1 || link.status === true);

                // Update hidden ID field if exists or create it
                const $idField = $row.find('input[name*="[id]"]');
                if ($idField.length) {
                    $idField.val(link.id);
                } else {
                    // Extract the index from the name attribute
                    const nameAttr = $row.find('input[name*="[social_link_id]"]').attr('name');
                    const index = nameAttr.match(/profiles\[(\d+)\]/)[1];

                    // Add hidden ID field
                    $row.append(`<input type="hidden" name="profiles[${index}][id]" value="${link.id}">`);
                }
            }
        });
    });
}

function submitForm(form) {
    const submitBtn = form.find('button[type="submit"]');
    const originalBtnText = submitBtn.text();

    // Show loading state
    submitBtn.prop('disabled', true).html(`
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        ${submitBtn.data('loading-text') || 'Saving...'}
    `);

    // Get form data
    const formData = form.serialize();

    // Make AJAX request
    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            // Show success message
            toastr.success(response.message || 'Social profiles updated successfully');

            // Update the form with any changes from the server
            if (response.data && response.data.profiles) {
                updateFormWithSocialLinks(response.data.profiles);
            }
        },
        error: function(xhr) {
            let errorMessage = 'An error occurred while saving. Please try again.';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                // Handle validation errors
                const errors = xhr.responseJSON.errors;
                for (const field in errors) {
                    toastr.error(errors[field][0]);
                }
                return;
            }

            toastr.error(errorMessage);
        },
        complete: function() {
            // Restore button state
            submitBtn.prop('disabled', false).text(originalBtnText);
        }
    });
}