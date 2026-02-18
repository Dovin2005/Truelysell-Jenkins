$(document).ready(function() {
    // Handle form submission
    $('form').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
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
                updateFormWithResponse(response);
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
    });

    // Function to update form with response data
    function updateFormWithResponse(response) {
        if (response.data && response.data.profiles) {
            response.data.profiles.forEach(profile => {
                // Find the corresponding row and update it
                const row = $(`input[name*="[id]"][value="${profile.id}"]`).closest('.row.align-items-center');

                if (row.length) {
                    // Update the URL input
                    row.find('input[type="url"]').val(profile.link);

                    // Update the status checkbox
                    const statusCheckbox = row.find('input[type="checkbox"]');
                    statusCheckbox.prop('checked', profile.status === 1 || profile.status === true);

                    // Update the selected option in dropdown
                    row.find('select').val(profile.social_link_id);
                }
            });
        }
    }

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