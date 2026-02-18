var pageValue = $("body").data("provider");
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

if ($("#permissionError").data("error") != "") {
    toastr.error($("#permissionError").data("error"));
}

if ($("body").data("error")) {
    toastr.error($("body").data("error"));
}

let languageId = $("#language-settings").data("language-id");
let auth_id = $("body").data("authid");

function updateAuthProviderId(callback) {
    $.ajax({
        url: "/api/get-session-user-id",
        type: "GET",
        success: function (response) {
            if (response.user_id) {
                const userId = response.user_id;
                localStorage.setItem("provider_id", userId);
                if (typeof callback === "function") {
                    callback(userId); // Pass the updated userId to the callback
                }
            }
        },
        error: function () {
            toastr.error("Unable to fetch session data. Please try again.");
        },
    });
}
updateAuthProviderId(function (updatedUserId) {
    // Update auth_provider_id globally
    const auth_provider_id = updatedUserId;
});

$(document).ready(function () {
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
                toastr.error("An error occurred: " + error);
            },
        });
    });

    notificationList(auth_id);

    // Attach click event for mark all as read
    $(".markallread").on("click", function () {
        markAllRead(auth_id);
    });
});

if (pageValue === "provider.leads") {
    $(document).on("click", ".view-lead-detail", function (e) {
        e.preventDefault();

        const id = $(this).data("id");
        const providerId = $(this).data("provider_id");

        localStorage.setItem("leadId", id);
        localStorage.setItem("providerId", providerId);

        window.location.href = "/provider/leadsinfo";
    });

    $(document).on("click", ".accept_btn, .reject_btn", function (e) {
        e.preventDefault();

        const button = $(this); // Store the clicked button
        const leadId = button.data("id");
        const status = button.hasClass("accept_btn") ? 2 : 3;

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
            beforeSend: function () {
                button.attr("disabled", true).html(`
                    <div class="spinner-border spinner-border-sm text-light" role="status"></div>
                `);
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
            complete: function () {
                button
                    .removeAttr("disabled")
                    .html(status === 2 ? "Accept" : "Reject");
            },
        });
    });

    $("#sortSelect").change(function () {
        loadLeads(1);
    });
    $("#order_byselect").change(function () {
        loadLeads(1);
    });
    function setActiveTab(tab, status) {
        $("#activeStatusInput").val(status); // Update the hidden input value
        $(".nav-link").removeClass("active"); // Remove active class from all tabs
        $(tab).addClass("active"); // Add active class to the clicked tab
    }

    $.ajax({
        url: "/api/get-session-user-id",
        type: "GET",
        success: function (response) {
            if (response.user_id) {
                const userId = response.user_id;
                localStorage.setItem("provider_id", userId);

                // Call loadLeads only if provider_id is set
                const provider_id = localStorage.getItem("provider_id");
                if (provider_id) {
                    loadLeads(); // Call the function with default parameters
                }
            }
        },
        error: function () {
            toastr.error("Unable to fetch session data. Please try again.");
        },
    });

    function loadLeads(page = 1, status = null) {
        const selectedSortBy = $("#sortSelect").val();
        const selectedOrderBy = $("#order_byselect").val();
        const activeStatus = $("#activeStatusInput").val();

        const provider_id = localStorage.getItem("provider_id");

        if (!provider_id) {
            return;
        }

        const payload = {
            order_by: selectedOrderBy,
            sort_by: selectedSortBy,
            search: "",
            page: page,
            provider_id: provider_id,
            per_page: 5,
            status: activeStatus,
        };

        $.ajax({
            url: "/api/list/leads",
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
                $("#accordionExample").empty();
                $("#leadsLoader").hide();
                if (response.data.meta && response.data.meta.counts) {
                    const counts = response.data.meta.counts;

                    const tabs = [
                        { id: "#inbox-tab", count: counts.all },
                        { id: "#new-tab", count: counts.new },
                        { id: "#accept-tab", count: counts.accept },
                        { id: "#reject-tab", count: counts.reject },
                    ];

                    tabs.forEach((tab) => {
                        $(`${tab.id} span`).text(tab.count || 0); // Update count
                        $(tab.id).prop("disabled", tab.count === 0); // Disable if count is 0
                        $(tab.id).toggleClass("disabled", tab.count === 0); // Add/remove 'disabled' class
                    });
                }

                if (
                    response.data &&
                    response.data.provider_forms_inputs.data.length > 0
                ) {
                    let showStatusDiv = false;

                    response.data.provider_forms_inputs.data.forEach((item) => {
                        if (item.status == "1") {
                            showStatusDiv = true;
                        }
                        const createdAt = new Date(
                            item.created_at
                        ).toLocaleString("en-GB", {
                            day: "2-digit",
                            month: "2-digit",
                            year: "numeric",
                            hour: "2-digit",
                            minute: "2-digit",
                            hour12: true,
                        });
                        if (languageId === 2) {
                            const statusLabel = getStatusLabel(item.status);
                            loadJsonFile(statusLabel, function (langtst) {
                                $(
                                    `.user_status[data-status="${item.status}"]`
                                ).text(langtst);
                            });
                        }
                        const cardHtml = `
                            <div class="card mb-2">
                                <div class="card-body p-2">
                                    <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="input-block todo-inbox-check d-flex align-items-center">
                                            <span class="avatar p-1 me-2 bg-light flex-shrink-0">
                                                <i class="ti ti-user-edit text-info fs-20"></i>
                                            </span>
                                            <div class="strike-info">
                                             <h4 class="mb-1 custom-heading fs-16">
                                                    ${
                                                        item.user_form_input
                                                            .user?.name
                                                            ? item.user_form_input.user.name
                                                                  .toLowerCase()
                                                                  .replace(
                                                                      /^\w/,
                                                                      (c) =>
                                                                          c.toUpperCase()
                                                                  )
                                                            : "N/A"
                                                    }
                                             </h4>
                                                <p class="d-flex align-items-center custom-paragraph">
                                                    <i class="ti ti-calendar me-1"></i>${
                                                        item.formatted_created_at
                                                    }
                                                </p> </div>

                                        </div>
                                        </div>
                                        <div class="col-md-6">
                                        <div class="d-flex align-items-center flex-fill justify-content-between">
                                        <div class="strike-info mx-2">
                                                <span class="badge badge-soft-warning ms-1">${
                                                    item.user_form_input
                                                        .category.name || "N/A"
                                                }</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                           <div class="notes-card-body d-flex align-items-center user_status" data-status="${
                                               item.status
                                           }">
                                                <p class="badge bg-outline-primary me-2 mb-0">
                                                    ${getStatusLabel(
                                                        item.status
                                                    )}
                                                </p>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="text-decoration-none me-3 view-lead-detail"
                                                   data-id="${
                                                       item.user_form_input.id
                                                   }"

                                                   data-provider_id="${item.id}"
                                                   data-created-at="${createdAt}"
                                                   data-form-inputs='${JSON.stringify(
                                                       item.user_form_input
                                                           .form_inputs || []
                                                   )}'>
                                                    <i  class="ti ti-eye fs-25">view</i>
                                                </a>
                                            </div>
                                            </div>
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

                    const totalPages =
                        response.data.provider_forms_inputs.last_page;
                    const currentPage =
                        response.data.provider_forms_inputs.current_page;
                    const maxVisiblePages = 5;

                    if (response.data.provider_forms_inputs.total === 0) {
                        $("#pagination").html("");
                    } else {
                        let startPage = Math.max(
                            currentPage - Math.floor(maxVisiblePages / 2),
                            1
                        );
                        let endPage = startPage + maxVisiblePages - 1;

                        if (endPage > totalPages) {
                            endPage = totalPages;
                            startPage = Math.max(
                                endPage - maxVisiblePages + 1,
                                1
                            );
                        }

                        const paginationHtml = `
                            <nav>
                                <ul class="pagination justify-content-center">
                                    ${
                                        response.data.provider_forms_inputs
                                            .prev_page_url
                                            ? `<li class="page-item">
                                                <a class="page-link" href="#" onclick="loadLeads(${
                                                    currentPage - 1
                                                }); return false;">Previous</a>
                                            </li>`
                                            : `<li class="page-item disabled">
                                                <a class="page-link">Previous</a>
                                            </li>`
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
                                                <a class="page-link" href="#" onclick="loadLeads(${pageNumber}); return false;">${pageNumber}</a>
                                            </li>`;
                                        }
                                    ).join("")}

                                    ${
                                        response.data.provider_forms_inputs
                                            .next_page_url
                                            ? `<li class="page-item">
                                                <a class="page-link" href="#" onclick="loadLeads(${
                                                    currentPage + 1
                                                }); return false;">Next</a>
                                            </li>`
                                            : `<li class="page-item disabled">
                                                <a class="page-link">Next</a>
                                            </li>`
                                    }
                                </ul>
                            </nav>
                        `;

                        // Append pagination HTML
                        $("#pagination").html(paginationHtml);
                    }

                    // Modal event binding
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
                            ` ${createdAt}`
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
                        <div class="d-flex justify-content-center align-items-center" style="height: 50vh;">
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

if (pageValue === "provider.leadsinfo") {
    const leadId = localStorage.getItem("leadId");
    const providerId = localStorage.getItem("providerId");

    document.getElementById("quote").addEventListener("input", function (e) {
        const input = e.target.value;
        if (input.length > 4) {
            e.target.value = input.slice(0, 4); // Limit to 4 characters
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        const startDateInput = document.getElementById("start_date");

        const today = new Date().toISOString().split("T")[0];

        startDateInput.min = today;
    });

    $("#providerQuoteForm").submit(function (event) {
        event.preventDefault();

        $("#quote, #start_date, #description").removeClass("is-invalid");
        $("#quote_error, #start_date_error, #description_error").text("");

        let formData = {
            quote: $("#quote").val(),
            start_date: $("#start_date").val(),
            description: $("#description").val(),
            provider_forms_inputs_id: providerId,
        };

        let isValid = true;
        let errorMessages = "";

        if (!formData.quote || isNaN(formData.quote)) {
            $("#quote").addClass("is-invalid");
            $("#quote_error").text("Please enter a valid quote amount.");
            isValid = false;
            errorMessages += "Quote: Please enter a valid amount.<br>";
        }

        if (!formData.start_date) {
            $("#start_date").addClass("is-invalid");
            $("#start_date_error").text("Please select a start date.");
            isValid = false;
            errorMessages += "Start Date: This field is required.<br>";
        }

        if (!formData.description) {
            $("#description").addClass("is-invalid");
            $("#description_error").text("Please provide a description.");
            isValid = false;
            errorMessages += "Description: This field is required.<br>";
        }

        if (!isValid) {
            toastr.error(errorMessages, "Validation Error");
            return;
        }

        $.ajax({
            url: "/api/provider/update-quote",
            method: "POST",
            data: JSON.stringify(formData),
            headers: {
                "Content-Type": "application/json",
                Authorization:
                    "Bearer " + localStorage.getItem("provider_token"),
            },
            beforeSend: function () {
                $('button[type="submit"]')
                    .attr("disabled", true)
                    .html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
            },
        })
            .done(function (response) {
                $('button[type="submit"]')
                    .removeAttr("disabled")
                    .html("Submit Quote");

                if (response.code === 200) {
                    // toastr.success(response.message, "Success");
                    if (languageId === 2) {
                        loadJsonFile(response.message, function (langtst) {
                            toastr.success(langtst);
                        });
                    } else {
                        toastr.success(response.message);
                    }
                    $("#providerQuoteForm")[0].reset();
                    providerList();
                } else {
                    toastr.error(response.message, "Error");
                }
            })
            .fail(function (error) {
                $('button[type="submit"]')
                    .removeAttr("disabled")
                    .html("Submit Quote");

                if (error.status === 422) {
                    $.each(error.responseJSON.errors, function (key, val) {
                        $("#" + key).addClass("is-invalid");
                        $("#" + key + "_error").text(val[0]);
                    });
                } else {
                    toastr.error(
                        error.responseJSON?.message ||
                            "An unexpected error occurred",
                        "Error"
                    );
                }
            });
    });

    $(document).on("click", ".accept_btn, .reject_btn", function (e) {
        e.preventDefault();

        const button = $(this); // Store the clicked button
        const siblingButton = button.siblings(".accept_btn, .reject_btn"); // Get the sibling button
        const leadId = button.data("id");
        const user_email = button.data("email");
        const category_name = button.data("category");
        const user_form_inputs_id = button.data("user_form_inputs_id");
        const provider_id = button.data("provider_id");
        const user_id = button.data("user_id");
        const status = button.hasClass("accept_btn") ? 2 : 3;

        $.ajax({
            url: "/api/leads/provider/status",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                id: leadId,
                status: status,
                user_email: user_email,
                category_name: category_name,
                user_form_inputs_id: user_form_inputs_id,
                provider_id: provider_id,
                user_id: user_id,
            },
            beforeSend: function () {
                button.attr("disabled", true).html(`
                    <div class="spinner-border spinner-border-sm text-light" role="status"></div>
                `);
                siblingButton.hide(); // Hide the sibling button
            },
            success: function (response) {
                if (response && response.message) {
                    if (languageId === 2) {
                        loadJsonFile(response.message, function (langtst) {
                            toastr.success(langtst, "", {
                                toastClass: "toastprovider",
                            });
                        });
                    } else {
                        toastr.success(response.message, "", {
                            toastClass: "toastprovider",
                        });
                    }
                } else {
                    toastr.success("Leads request sent successfully."); // Fallback message
                }
                providerList();

                const email = response.user_email;
                const emailData = {
                    subject: response.email_template.email_subject,
                    content: response.email_template.email_content,
                };
                const userName = response.user_name;

                sendEmail(email, emailData, userName)
                    .then((emailResponse) => {})
                    .catch((error) => {});
            },
            error: function (xhr) {
                toastr.error("Failed to update status. Please try again.");
            },
            complete: function () {
                button
                    .removeAttr("disabled")
                    .html(button.hasClass("accept_btn") ? "Accept" : "Reject");
                siblingButton.show();
            },
        });
    });

    function sendEmail(email, emailData, userName) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: "/api/mail/sendmail",
                type: "POST",
                dataType: "json",
                data: {
                    to_email: email,
                    notification_type: 5,
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

    const payload = {
        order_by: "asc",
        sort_by: "created_at",
        search: "",
        id: providerId,
    };
    providerList();

    function providerList() {
        $("#pageLoader").show();
        $.ajax({
            url: "/api/list/leads",
            type: "POST",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            data: JSON.stringify(payload),
            contentType: "application/json",
            success: function (response) {
                $("#pageLoader").hide();
                if (response.data.meta && response.data.meta.counts) {
                    $(".currency").text(
                        response.data.meta.counts.currencySymbol || $
                    );
                }
                if (
                    response.success &&
                    response.data.provider_forms_inputs.data.length > 0
                ) {
                    const lead = response.data.provider_forms_inputs.data[0];
                    const userFormInput = lead.user_form_input;

                    // Update modal title and timestamp
                    $(".modal-title").text(`Lead ID: ${lead.id}`);
                    $(".times").text(`${lead.formatted_created_at}`);
                    $(".quote").val(lead.quote);
                    $(".start_date").val(lead.start_date);
                    $(".description").val(lead.description);
                    // Update status display
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
                        $(".status").text(statusText);
                    }

                    $(".status")
                        .text(statusText)
                        .removeClass(
                            "bg-outline-primary bg-outline-success bg-outline-danger bg-outline-secondary"
                        )
                        .addClass(statusClass);

                    // Show or hide status action buttons
                    if (lead.status === 1) {
                        $("#status_div").show();
                        $("#accept_btn").data("id", lead.id);
                        $("#reject_btn").data("id", lead.id);

                        $("#accept_btn").data(
                            "email",
                            userFormInput.user.email
                        );
                        $("#reject_btn").data(
                            "email",
                            userFormInput.user.email
                        );

                        $("#accept_btn").data(
                            "category",
                            userFormInput.category.name
                        );
                        $("#reject_btn").data(
                            "category",
                            userFormInput.category.name
                        );

                        $("#accept_btn").data(
                            "user_form_inputs_id",
                            lead.user_form_inputs_id
                        );
                        $("#reject_btn").data(
                            "user_form_inputs_id",
                            lead.user_form_inputs_id
                        );
                        $("#accept_btn").data("user_id", userFormInput.user_id);
                        $("#reject_btn").data("user_id", userFormInput.user_id);

                        $("#accept_btn").data("provider_id", lead.provider_id);
                        $("#reject_btn").data("provider_id", lead.provider_id);
                    } else {
                        $("#status_div").hide();
                    }

                    // Render category and username
                    $(".category").text(
                        `Category: ${userFormInput.category.name
                            .charAt(0)
                            .toUpperCase()}${userFormInput.category.name.slice(
                            1
                        )}`
                    );

                    $(".sub_category").text(
                        `Sub Category: ${
                            userFormInput.sub_category?.name
                                ? userFormInput.sub_category.name
                                      .charAt(0)
                                      .toUpperCase() +
                                  userFormInput.sub_category.name.slice(1)
                                : "-"
                        }`
                    );

                    if (languageId === 2) {
                        loadJsonFile("Category", function (translatedCategory) {
                            $(".category").text(
                                `${translatedCategory}: ${userFormInput.category.name
                                    .charAt(0)
                                    .toUpperCase()}${userFormInput.category.name.slice(
                                    1
                                )}`
                            );
                        });

                        loadJsonFile(
                            "Sub Category",
                            function (translatedSubCategory) {
                                const subCategoryName = userFormInput
                                    .sub_category?.name
                                    ? userFormInput.sub_category.name
                                          .charAt(0)
                                          .toUpperCase() +
                                      userFormInput.sub_category.name.slice(1)
                                    : "-";
                                $(".sub_category").text(
                                    `${translatedSubCategory}: ${subCategoryName}`
                                );
                            }
                        );
                    }

                    $(".username").text(
                        `${userFormInput.user.name
                            .charAt(0)
                            .toUpperCase()}${userFormInput.user.name.slice(1)}`
                    );

                    if (userFormInput.status === 2) {
                        disableQuoteForm("User Accepted");
                    } else if (lead.user_status === 3) {
                        disableQuoteForm("User Rejected");
                    } else {
                        enableQuoteForm();
                    }
                    function disableQuoteForm(message) {
                        $(
                            "#providerQuoteForm input, #providerQuoteForm textarea, #providerQuoteForm button"
                        ).prop("disabled", true);
                        $("#providerQuoteForm").append(
                            `<div class="alert alert-info mt-3">${message}</div>`
                        );
                    }

                    function enableQuoteForm() {
                        $(
                            "#providerQuoteForm input, #providerQuoteForm textarea, #providerQuoteForm button"
                        ).prop("disabled", false);
                        $("#providerQuoteForm .alert").remove();
                    }

                    $("#form-inputs").empty();

                    if (
                        userFormInput.form_inputs_details &&
                        userFormInput.form_inputs_details.length > 0
                    ) {
                        userFormInput.form_inputs_details.forEach((input) => {
                            $("#form-inputs").append(`
                                <div class="col-md-12">
                                    <div class="tab-info mt-0 border border-1 p-2">
                                    ${
                                        input.id !== "sub_category_id"
                                            ? `<h5 class="mt-2">${input.details.title}:</h5>`
                                            : ""
                                    }

                                        <!-- Check and display appropriate value -->
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
                                                    : input.value?.country &&
                                                      input.value?.state &&
                                                      input.value?.city
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
                                <div class="tab-info mt-0 border border-1 p-2">
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
                $("#pageLoader").hide();
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

if (pageValue === "provider.profile") {
    async function getUserId() {
        try {
            const response = await $.ajax({
                url: "/api/get-session-user-id",
                type: "GET",
            });

            if (response.user_id) {
                const userId = response.user_id;
                localStorage.setItem("user_id", userId);
            }
        } catch (error) {}
    }

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
                const data = response.data;
                if (data.user_details.profile_image != null) {
                    $(".headerProfileImg").attr(
                        "src",
                        data.user_details.profile_image
                    );
                }

                $('.document-preview-container').empty();
                if (data.documents && data.documents.length > 0) {
                    data.documents.forEach(appendDocumentPreview);
                }
            }
        } catch (error) {}
    }

    function appendDocumentPreview(value, index) {
        $('.document-preview-container').append(
            `<div class="document-preview me-2">
                <a href="${value.document_url}" target="_blank" class="btn btn-sm btn-light me-0">
                    <i class="ti ti-file-text fs-40"></i>
                </a>
                <button type="button" class="btn btn-sm btn-light remove-document" data-id="${value.id}">
                    <i class="ti ti-trash"></i>
                </button>
            </div>`
        );
    }

    $(document).ready(async function () {
        await getUserId();
    });

    $(document).ready(function () {
        $(".select2").select2();
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

        $("#providerProfileForm").validate({
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
                company_image: {
                    extension: "jpg|jpeg|png",
                    filesize: 2048,
                },
                company_name: {
                    required: true,
                    maxlength: 100,
                },
                company_website: {
                    required: true,
                    url: true,
                },
                company_address: {
                    required: true,
                    maxlength: 150,
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
                        $("#save_provider")
                            .attr("disabled", true)
                            .html(
                                '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                            );
                    },
                    success: function (response) {
                        $(".error-text").text("");
                        $("#save_provider")
                            .removeAttr("disabled")
                            .html($("#save_provider").data("save"));
                        $(".form-control").removeClass("is-invalid is-valid");
                        $(".select2-container").removeClass(
                            "is-invalid is-valid"
                        );
                        if (response.code === 200) {
                            toastr.success(response.message);
                            $("#documents").val("");
                            getProfileDetails();
                        }
                    },
                    error: function (error) {
                        $(".error-text").text("");
                        $("#save_provider")
                            .removeAttr("disabled")
                            .html($("#save_provider").data("save"));
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
            "date",
            function (value, element) {
                return (
                    this.optional(element) || /^\d{2}-\d{2}-\d{4}$/.test(value)
                );
            },
            "Please enter a valid date in DD-MM-YYYY format."
        );

        $.validator.addMethod(
            "filesize",
            function (value, element, param) {
                if (element.files.length === 0) return true;
                return element.files[0].size <= param * 1024;
            },
            "File size must be less than {0} KB."
        );
    });

    let removedDocuments = [];
    $(document).on('click', '.remove-document', function () {
        let documentId = $(this).data('id');
        removedDocuments.push(documentId);
        $("#removed_documents").val(removedDocuments.join(","));
        $(this).closest('.document-preview').remove();
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

    $("#hourly_rate").on('input', function () {
        $(this).val($(this).val().replace(/[^0-9]/g, "").slice(0, 5));
    })

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
        }).fail(function () {});
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
        }).fail(function () {});
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
        }).fail(function () {});
    }

    $("#companyImageBtn").on("click", function () {
        $("#company_image").click();
    });

    $("#company_image").on("change", function (event) {
        if ($(this).val() !== "") {
            $(this).valid();
        }
        let reader = new FileReader();
        reader.onload = function (e) {
            $("#companyImagePreview").attr("src", e.target.result).show();
        };
        reader.readAsDataURL(event.target.files[0]);
    });

    $("#profileImageBtn").on("click", function () {
        $("#profile_image").click();
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
}

if (pageValue === "provider.bookinglist") {
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

                // Apply the class immediately
                $this.addClass(statusClass);
            });
        }
        $(document).on("click", ".accept", function (e) {
            var bookingId = $(this).data("id");
            $("#acceptbooking").attr("data-id", bookingId);
        });

        $(document).on("click", ".cancel", function (e) {
            var bookingId = $(this).data("id");
            $("#cancelbooking").attr("data-id", bookingId);
        });
        $(document).on("click", ".complete", function (e) {
            var bookingId = $(this).data("id");
            $("#completebooking").attr("data-id", bookingId);
        });
        $(document).on("click", "#acceptbooking", function (e) {
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
                    $("#acceptbooking")
                        .attr("disabled", true)
                        .html(
                            '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                        );
                },
                complete: function () {
                    $("#acceptbooking")
                        .attr("disabled", false)
                        .html($("#acceptbooking").data("yes_confirm"));
                },
                success: function (response) {
                    if (response.code === 200) {
                        if (response.data["status_code"] == "2") {
                            $(".completediv" + id).show();
                            $(".acceptdiv" + id).hide();
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
                        $("#accept").modal("hide");
                        setTimeout(function () {
                            window.location.reload();
                        }, 500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    let msg = "error_accept_book";
                    if (languageId === 2) {
                        loadJsonFile(msg, function (langtst) {
                            msg = langtst;
                            toastr.error(msg);
                        });
                    } else {
                        toastr.error(
                            "An error occurred while trying to accept the booking."
                        );
                    }
                },
            });
        });
        $(document).on("click", "#cancelbooking", function (e) {
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
                success: function (response) {
                    if (response.code === 200) {
                        if (response.data["status_code"] == "3") {
                            $(".completediv" + id).hide();
                            $(".acceptdiv" + id).hide();
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
                    toastr.error(
                        "An error occurred while trying to accept the booking."
                    );
                },
            });
        });
        $(document).on("click", "#completebooking", function (e) {
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
                    $("#completebooking")
                        .attr("disabled", true)
                        .html(
                            '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                        );
                },
                complete: function () {
                    $("#completebooking")
                        .attr("disabled", false)
                        .html($("#completebooking").data("yes_confirm"));
                },
                success: function (response) {
                    if (response.code === 200) {
                        if (response.data["status_code"] == "5") {
                            $(".completediv" + id).hide();
                            $(".acceptdiv" + id).hide();
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
                        $("#completed").modal("hide");
                        setTimeout(function () {
                            window.location.reload();
                        }, 500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    toastr.error(
                        "An error occurred while trying to complete the booking."
                    );
                },
            });
        });
        $(document).on("click", ".chattab", function () {
            const userId = $(this).data("userid"); // Get the user ID from the clicked list item
            const userName = $(this).data("user");
            const authuserid = $(this).data("authuserid");
            setSessionValue("chatuserid", userId, authuserid);
            setSessionValue("chatusername", userName, authuserid);
            window.location.href = "/provider/chat";
        });
    });

    $(".booking_details_btn").on("click", function () {
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
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");

                let orderId = response.booking.order_id;
                if (orderId) {
                    $("#order_id").text(`#${orderId}`);
                }

                let bookingData = response.booking;
                let additionalServices = response.additional_services;
                let currency = response.currency;
                if (
                    Array.isArray(additionalServices) &&
                    additionalServices.length > 0
                ) {
                    let list = '<div class="d-flex flex-column gap-2">';
                    additionalServices.forEach((service) => {
                        list += `
                        <label class="d-flex gap-2">
                            <strong class="w-25">${service.name}</strong>:
                            <span class="text-dark flex-grow-1">${currency}${service.price}</span>
                        </label>`;
                    });
                    list += "</div>";
                    $(".additional_service").removeClass("d-none");
                    $("#additional_service_list").html(list);
                } else {
                    $(".additional_service").addClass("d-none");
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
                    $("#branch_address").text(response.branch.branch_address);
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
                        $("#provider_reply").text(bookingData.review.reply.review);
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
            },
        });
    });

    $(".view_review_btn").on("click", function (e) {
        $("#review_booking_id").val($(this).data("booking_id"));
        $("#review_product_id").val($(this).data("product_id"));
        $("#review").val($(this).data("review")).attr('readonly', true);
        $("#review_id").val($(this).data("review_id"));

        let rating = $(this).data('rating');
        let starsHtml = "";
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                starsHtml += `<i class="fas fa-star text-warning"></i>`;
            } else {
                starsHtml += `<i class="far fa-star"></i>`;
            }
        }
        $(".review_rating_list").html(starsHtml);

        if ($(this).data("review_reply")) {
            $("#reply_review").val($(this).data("review_reply")).attr('readonly', true);
            $('#save_comment_btn').addClass('d-none');
        } else {
            $("#reply_review").val('').attr('readonly', false);
            $('#save_comment_btn').removeClass('d-none');
        }
    });

    $("#addCommentsForm").submit(function (event) {
        event.preventDefault();

        var formData = {
            review: $("#reply_review").val(),
            product_id: $("#review_product_id").val(),
            parent_id: $("#review_id").val(),
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

if (pageValue === "staff.bookinglist") {
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

                // Apply the class immediately
                $this.addClass(statusClass);
            });
        }
        $(document).on("click", ".accept", function (e) {
            var bookingId = $(this).data("id");
            $("#acceptbooking").attr("data-id", bookingId);
        });

        $(document).on("click", ".cancel", function (e) {
            var bookingId = $(this).data("id");
            $("#cancelbooking").attr("data-id", bookingId);
        });
        $(document).on("click", ".complete", function (e) {
            var bookingId = $(this).data("id");
            $("#completebooking").attr("data-id", bookingId);
        });
        $(document).on("click", "#acceptbooking", function (e) {
            e.preventDefault();
            var type = $(this).data("type");
            var id = $(this).data("id");
            $.ajax({
                url: "/api/updatebookingstatus",
                type: "POST",
                data: {
                    id: id,
                    status: type,
                    updated_by: "staff"
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
                    $("#acceptbooking")
                        .attr("disabled", true)
                        .html(
                            '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                        );
                },
                complete: function () {
                    $("#acceptbooking")
                        .attr("disabled", false)
                        .html($("#acceptbooking").data("yes_confirm"));
                },
                success: function (response) {
                    if (response.code === 200) {
                        if (response.data["status_code"] == "2") {
                            $(".completediv" + id).show();
                            $(".acceptdiv" + id).hide();
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
                        $("#accept").modal("hide");
                        setTimeout(function () {
                            window.location.reload();
                        }, 500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    let msg = "error_accept_book";
                    if (languageId === 2) {
                        loadJsonFile(msg, function (langtst) {
                            msg = langtst;
                            toastr.error(msg);
                        });
                    } else {
                        toastr.error(
                            "An error occurred while trying to accept the booking."
                        );
                    }
                },
            });
        });
        $(document).on("click", "#cancelbooking", function (e) {
            e.preventDefault();
            var type = $(this).data("type");
            var id = $(this).data("id");
            $.ajax({
                url: "/api/updatebookingstatus",
                type: "POST",
                data: {
                    id: id,
                    status: type,
                    updated_by: "staff"
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
                        if (response.data["status_code"] == "3") {
                            $(".completediv" + id).hide();
                            $(".acceptdiv" + id).hide();
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
                    toastr.error(
                        "An error occurred while trying to accept the booking."
                    );
                },
            });
        });
        $(document).on("click", "#completebooking", function (e) {
            e.preventDefault();
            var type = $(this).data("type");
            var id = $(this).data("id");
            $.ajax({
                url: "/api/updatebookingstatus",
                type: "POST",
                data: {
                    id: id,
                    status: type,
                    updated_by: "staff"
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
                    $("#completebooking")
                        .attr("disabled", true)
                        .html(
                            '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                        );
                },
                complete: function () {
                    $("#completebooking")
                        .attr("disabled", false)
                        .html($("#completebooking").data("yes_confirm"));
                },
                success: function (response) {
                    if (response.code === 200) {
                        if (response.data["status_code"] == "5") {
                            $(".completediv" + id).hide();
                            $(".acceptdiv" + id).hide();
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
                        $("#completed").modal("hide");
                        setTimeout(function () {
                            window.location.reload();
                        }, 500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    toastr.error(
                        "An error occurred while trying to complete the booking."
                    );
                },
            });
        });
        $(document).on("click", ".chattab", function () {
            const userId = $(this).data("userid"); // Get the user ID from the clicked list item
            const userName = $(this).data("user");
            const authuserid = $(this).data("authuserid");
            setSessionValue("chatuserid", userId, authuserid);
            setSessionValue("chatusername", userName, authuserid);
            window.location.href = "/provider/chat";
        });
    });

    $(".booking_details_btn").on("click", function () {
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
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");

                let orderId = response.booking.order_id;
                if (orderId) {
                    $("#order_id").text(`#${orderId}`);
                }

                let additionalServices = response.additional_services;
                let currency = response.currency;
                if (
                    Array.isArray(additionalServices) &&
                    additionalServices.length > 0
                ) {
                    let list = '<div class="d-flex flex-column gap-2">';
                    additionalServices.forEach((service) => {
                        list += `
                        <label class="d-flex gap-2">
                            <strong class="w-25">${service.name}</strong>:
                            <span class="text-dark flex-grow-1">${currency}${service.price}</span>
                        </label>`;
                    });
                    list += "</div>";
                    $(".additional_service").removeClass("d-none");
                    $("#additional_service_list").html(list);
                } else {
                    $(".additional_service").addClass("d-none");
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
                    $("#branch_address").text(response.branch.branch_address);
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
}

if (pageValue === "provider.staffs") {
    let cachedCountries = [];
    let cachedStates = [];
    let cachedCities = [];

    $(document).ready(function () {
        $(".selects").select2();

        loadLocationData();
        getStaffList();
        getBranch();
        getRoles();

        $("#country").on("change", function () {
            const selectedCountry = $(this).val();
            clearDropdown($(".state"));
            clearDropdown($(".city"));
            if (selectedCountry) {
                getStates(selectedCountry);
            }
        });

        $("#state").on("change", function () {
            const selectedState = $(this).val();
            clearDropdown($(".city"));
            if (selectedState) {
                getCities(selectedState);
            }
        });

        $("#edit_country").on("change", function () {
            const selectedCountry = $(this).val();
            clearDropdown($(".state"));
            clearDropdown($(".city"));
            if (selectedCountry) {
                getStates(selectedCountry);
            }
        });

        $("#edit_state").on("change", function () {
            const selectedState = $(this).val();
            clearDropdown($(".city"));
            if (selectedState) {
                getCities(selectedState);
            }
        });
    });

    async function loadLocationData() {
        try {
            const [countriesResponse, statesResponse, citiesResponse] =
                await Promise.all([
                    $.getJSON("/countries.json"),
                    $.getJSON("/states.json"),
                    $.getJSON("/cities.json"),
                ]);

            cachedCountries = countriesResponse.countries;
            cachedStates = statesResponse.states;
            cachedCities = citiesResponse.cities;

            getCountries();
        } catch (error) {}
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

    function getCountries(selectedCountry = null) {
        const countrySelect = $(".country");
        clearDropdown(countrySelect);

        $.each(cachedCountries, function (index, country) {
            countrySelect.append(
                $("<option>", {
                    value: country.id,
                    text: country.name,
                    selected: country.id == selectedCountry,
                })
            );
        });
        countrySelect.select2();
        if (selectedCountry) {
            getStates(selectedCountry);
        }
    }

    function getStates(selectedCountry, selectedState = null) {
        const stateSelect = $(".state");
        clearDropdown(stateSelect);

        const states = cachedStates.filter(
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
            getCities(states[0].id);
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
                getCities(selectedState);
            }
        }
    }

    function getCities(selectedState, selectedCity = null) {
        const citySelect = $(".city");
        clearDropdown(citySelect);

        const cities = cachedCities.filter(
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
    }

    $("#add_staff_btn").on("click", function () {
        const countrySelect = $(".country");
        clearDropdown(countrySelect);
        const stateSelect = $(".state");
        clearDropdown(stateSelect);
        const citySelect = $(".city");
        clearDropdown(citySelect);
        getCountries();
        $("#gender").val("").trigger("change");
        $("#category").val("").trigger("change");
        $("#role_id").val("").trigger("change");
        $(".subcategory-list").find("option:not(:first)").remove();
        $("#imagePreview").attr("src", $("#imagePreview").data("image"));
        $(".form-control").removeClass("is-invalid is-valid");
        $(".select2-container").removeClass("is-invalid is-valid");
        $(".error-text").text("");
        $("#staffForm").trigger("reset");
        $("#id").val("");
        $("#branch_id").val([]).trigger("change");

        $.ajax({
            url: "/provider/staff/check-limit",
            type: "POST",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $("#add_staff_btn")
                    .attr("disabled", true)
                    .html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
            },
            success: function (response) {
                $("#add_staff_btn")
                    .removeAttr("disabled")
                    .html(
                        '<i class="ti ti-circle-plus me-2"></i>' +
                            $("#add_staff_btn").data("add_text")
                    );
                if (response.code === 200) {
                    if (response.no_package === true) {
                        $("#no_sub").modal("show");
                    } else if (response.sub_count_end === true) {
                        $("#sub_count_end").modal("show");
                    } else if (response.sub_end === true) {
                        $("#sub_end").modal("show");
                    } else {
                        $("#add_staff_modal").modal("show");
                    }
                }
            },
            error: function (error) {
                $("#add_staff_btn")
                    .removeAttr("disabled")
                    .html(
                        '<i class="ti ti-circle-plus me-2"></i>' +
                            $("#add_staff_btn").data("add_text")
                    );
                if (response.code === 500) {
                    toastr.error(error.responseJSON.message);
                } else {
                    toastr.error("An error occured!");
                }
            },
        });
    });

    $(document).ready(function () {
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
                    maxlength: "First name cannot exceed 100 characters.",
                    pattern: "Only alphabets are allowed.",
                },
                last_name: {
                    required: "Last name is required.",
                    maxlength: "Last name cannot exceed 100 characters.",
                    pattern: "Only alphabets are allowed.",
                },
                user_name: {
                    required: "Username is required.",
                    maxlength: "Username cannot exceed 100 characters.",
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
                    maxlength: "Address cannot exceed 100 characters.",
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
                category: {
                    required: "Category is required.",
                },
                role_id: {
                    required: "Role is required.",
                },
            },
            ar: {
                first_name: {
                    required: "الاسم الأول مطلوب.",
                    maxlength: "يجب ألا يتجاوز الاسم الأول 100 حرفًا.",
                    pattern: "يسمح بالأحرف فقط.",
                },
                last_name: {
                    required: "الاسم الأخير مطلوب.",
                    maxlength: "يجب ألا يتجاوز الاسم الأخير 100 حرفًا.",
                    pattern: "يسمح بالأحرف فقط.",
                },
                user_name: {
                    required: "اسم المستخدم مطلوب.",
                    maxlength: "يجب ألا يتجاوز اسم المستخدم 100 حرفًا.",
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
                    maxlength: "يجب ألا يتجاوز العنوان 100 حرفًا.",
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
                category: {
                    required: "الفئة مطلوبة.",
                },
                role_id: {
                    required: "الدور مطلوب.",
                },
            },
        };

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
                category: {
                    required: true,
                },
                role_id: {
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
                            .html($("#staff_save_btn").data("save_text"));
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
                            .html($("#staff_save_btn").data("save_text"));
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
                },
                email: {
                    required: true,
                    email: true,
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
                category: {
                    required: true,
                },
                role_id: {
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
                            .html($("#staff_save_btn").data("save_text"));
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
                            .html($("#staff_save_btn").data("save_text"));
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
            "date",
            function (value, element) {
                return (
                    this.optional(element) || /^\d{2}-\d{2}-\d{4}$/.test(value)
                );
            },
            "Please enter a valid date in DD-MM-YYYY format."
        );

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
    $("#category").on("change", function () {
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

    $("#category").on("change", function () {
        var categoryId = $(this).val();
        if (categoryId) {
            listSubcategory(categoryId);
        }
    });

    $("#edit_category").on("change", function () {
        var categoryId = $(this).val();
        if (categoryId) {
            listSubcategory(categoryId);
        }
    });

    function getBranch() {
        $.ajax({
            url: "/provider/get-branch-list",
            type: "POST",
            data: {
                id: $("#parent_id").val(),
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            dataType: "json",
            success: function (response) {
                if (response && response.data && response.data.length > 0) {
                    const branchDropdown = $(".branch-list");
                    response.data.forEach((branch) => {
                        branchDropdown.append(
                            `<option value="${branch.id}">${branch.branch_name}</option>`
                        );
                    });
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }

    function getRoles() {
        $.ajax({
            url: "/role/list",
            type: "POST",
            data: {
                user_id: $("#parent_id").val(),
                status: 1,
            },
            headers: {
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

    function listSubcategory(categoryId, selectedSubcategory = "") {
        $.ajax({
            url: "/api/get-subcategories",
            type: "POST",
            dataType: "json",
            data: {
                category_id: categoryId,
                language_id: languageId,
            },
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $(".subcategory-list").find("option:not(:first)").remove();
                if (response.length != 0) {
                    response.forEach((item) => {
                        $(".subcategory-list").append(
                            `<option value="${item.id}" }>${item.name}</option>`
                        );
                    });
                }
            },
            error: function (error) {
                $(".subcategory-list").find("option:not(:first)").remove();
                if (error.responseJSON && error.responseJSON.code === 500) {
                    toastr.error(error.responseJSON.message);
                }
            },
        });
    }

    function getStaffList() {
        var id = $("#parent_id").val();
        $.ajax({
            url: "/api/provider/get-staff-list",
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
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge ${
                                            staff.status == "1"
                                                ? "badge-soft-success"
                                                : "badge-soft-danger"
                                        } d-flex align-items-center">
                                            <i class="ti ti-point-filled"></i>
                                            ${
                                                staff.status == "1"
                                                    ? "Active"
                                                    : "Inactive"
                                            }
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-icon d-inline-flex">
                                    ${
                                        $("#has_permission").data("edit") == 1
                                            ? `<a href="javascript:void(0);" class="me-2 edit_staff_btn" data-bs-toggle="modal" data-bs-target="#edit_staff_modal"
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
                                        data-category_id = "${staff.category_id}"
                                        data-subcategory_id = "${staff.subcategory_id}"
                                        data-branch_id = "${staff.branch_id}"
                                        data-role = "${staff.role_id}"
                                        data-status = "${staff.status}" >
                                        <i class="ti ti-edit"></i></a>`
                                            : ""
                                    }
                                    ${
                                        $("#has_permission").data("delete") == 1
                                            ? `<a href="javascript:void(0);" class="delete_staff_btn" data-id="${staff.id}" data-bs-toggle="modal" data-bs-target="#del-staff"><i
                                                class="ti ti-trash"></i></a>`
                                            : ""
                                    }
                                    </div>
                                </td>
                            </tr>
                            `;
                        });
                        $("#tabelSkeletonLoader").hide();
                        $("#loader-table").hide();
                        $(".label-loader, .input-loader").hide();
                        $(".real-label, .real-input").removeClass("d-none");
                    }

                    $("#staffTable tbody").html(tableBody);
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
                $("#tabelSkeletonLoader").hide();
                $("#loader-table").hide();
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
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
        const category_id = $(this).data("category_id");
        const subcategory_id = $(this).data("subcategory_id");
        const branch_id = $(this).data("branch_id");
        if (typeof branch_id === "string" && branch_id.includes(",")) {
            branchIdArray = branch_id.split(",");
            $("#edit_branch_id").val(branchIdArray).trigger("change");
        } else {
            branchIdArray = branch_id;
            $("#edit_branch_id").val(branchIdArray).trigger("change");
        }
        const role = $(this).data("role");
        const status = $(this).data("status");

        const countrySelect = $(".country");
        clearDropdown(countrySelect);
        const stateSelect = $(".state");
        clearDropdown(stateSelect);
        const citySelect = $(".city");
        clearDropdown(citySelect);

        async function loadData() {
            await loadLocationData();
            getCountries(country_id);
            getStates(country_id, state_id);
            getCities(state_id, city_id);
        }
        loadData();

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
        $("#edit_bio").text(bio);
        $("#edit_postal_code").val(postal_code);
        $("#edit_category").val(category_id).trigger("change");
        $("#edit_subcategory_id").val(subcategory_id).trigger("change");
        $("#edit_status").val(status).trigger("change");
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
            url: "/api/provider/delete-staff",
            type: "POST",
            data: {
                id: id,
            },
            dataType: "json",
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
}

if (pageValue === "provider.add.service") {
    let is_empty_branch = 0;

    $(document).ready(function () {
        $("#description").summernote({
            height: 300,
            callbacks: {
                onChange: function (contents) {
                    $("#description").val(contents).trigger("input"); // Update hidden textarea
                },
            },
        });
    });
    document.addEventListener("DOMContentLoaded", function () {
        var defaultLangId = $("#userLangId").val();

        languageTranslate(defaultLangId);
        fetchBranch();
    });

    $("#hours_select").on("input", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/g, "")
        );

        if ($(this).val().length > 12) {
            $(this).val($(this).val().slice(0, 12));
        }
    });

    let branchEmptyInfo =
        "No branches found at the moment. You can proceed further.";

    function languageTranslate(lang_id) {
        $("#serviceLoader").show();
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

                if (response.code === 200 && Object.keys(trans).length > 0) {
                    $(".form-control")
                        .removeClass("is-invalid")
                        .removeClass("is-valid");
                    $(".invalid-feedback").text("");

                    $("select option").each(function () {
                        var translateKey = $(this).data("translate");
                        if (trans.hasOwnProperty(translateKey)) {
                            $(this).text(trans[translateKey]);
                        }
                    });

                    if (
                        trans[
                            "No branches found at the moment. You can proceed further."
                        ]
                    ) {
                        branchEmptyInfo =
                            trans[
                                "No branches found at the moment. You can proceed further."
                            ];
                    }

                    // Loop through each element with the class 'translatable' and update text or attribute
                    $(".translatable").each(function () {
                        var translateKey = $(this).data("translate"); // Get the translation key from the data-translate attribute

                        if (trans.hasOwnProperty(translateKey)) {
                            var translatedText = trans[translateKey];

                            // If it's an input or textarea (with placeholder), update the placeholder
                            if ($(this).is("input, textarea")) {
                                $(this).attr("placeholder", translatedText);

                                // Check if it's a tagsinput field
                                if ($(this).data("role") === "tagsinput") {
                                    $(this).tagsinput("destroy"); // Destroy existing instance
                                    $(this).attr("placeholder", translatedText); // Update the placeholder
                                    $(this).tagsinput(); // Reinitialize the tagsinput
                                }
                            }
                            // If it's a span with invalid-feedback class, update the error message
                            else if ($(this).hasClass("invalid-feedback")) {
                                $(this).text(""); // Clear existing content
                                $(this).text(translatedText); // Set the translated error message
                            }
                            // Otherwise, update the text content
                            else {
                                $(this).text(translatedText);
                            }
                        }
                    });

                    // Translate labels and placeholders for form fields
                    $(".field-input, .form-label").each(function () {
                        var translateKey = $(this).data("translate"); // Get the translation key from the data-translate attribute

                        if (trans.hasOwnProperty(translateKey)) {
                            var translatedText = trans[translateKey];

                            // Update labels for form fields
                            if ($(this).hasClass("form-label")) {
                                $(this).html(translatedText); // Update label HTML
                            }

                            // Update placeholders for inputs
                            if ($(this).hasClass("field-input")) {
                                $(this).attr("placeholder", translatedText); // Update placeholder attribute
                            }
                        }
                    });

                    $("#service-form").validate().settings.messages = {
                        service_name: {
                            required: trans.service_name_required,
                            minlength: trans.service_name_minlength,
                            maxlength: trans.service_name_maxlength,
                            remote: trans.service_name_remote,
                        },
                        product_code: {
                            required: trans.product_code_required,
                            minlength: trans.product_code_minlength,
                            maxlength: trans.product_code_maxlength,
                        },
                        category: {
                            required: trans.category_required,
                        },
                        sub_category: {
                            required: trans.sub_category_required,
                        },
                        description: {
                            required: trans.description_required,
                            minlength: trans.description_minlength,
                            maxlength: trans.description_maxlength,
                        },
                        include: {
                            required: trans.include_required,
                            minlength: trans.include_minlength,
                            maxlength: trans.include_maxlength,
                        },
                        price_type: {
                            required: trans.price_type_required,
                        },
                        service_price: {
                            required: trans.service_price_required,
                            number: trans.service_price_number,
                            min: trans.service_price_min,
                        },
                        basic_service_price: {
                            required: trans.basic_service_price_required,
                            number: trans.basic_service_price_number,
                            min: trans.basic_service_price_min,
                        },
                        basic_price_description: {
                            required: trans.basic_price_description_required,
                            minlength: trans.basic_price_description_minlength,
                            maxlength: trans.basic_price_description_maxlength,
                        },
                    };

                    $("#location-form").validate().settings.messages = {
                        address: {
                            required: trans.address_required_service,
                            minlength: trans.address_minlength_service,
                            maxlength: trans.address_maxlength_service,
                        },
                        pincode: {
                            required: trans.pincode_required_service,
                            maxlength: trans.pincode_maxlength_service,
                        },
                        state: {
                            required: trans.state_required_service,
                        },
                        city: {
                            required: trans.city_required_service,
                        },
                        country: {
                            required: trans.country_required_service,
                        },
                    };

                    $("#image-form").validate().settings.messages = {
                        "service_images[]": {
                            required: trans.image_required_service,
                            extension: trans.image_required_extension,
                        },
                    };

                    $("#seo-form").validate().settings.messages = {
                        seo_title: {
                            required: trans.seo_title_required,
                        },
                        seo_tag: {
                            required: trans.seo_tag_required,
                        },
                        seo_description: {
                            required: trans.seo_dis_required,
                        },
                    };

                    $("#serviceLoader").hide();
                    $(".label-loader, .input-loader").hide();
                    $(".real-label, .real-input").removeClass("d-none");
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }

    function fetchBranch() {
        $.ajax({
            url: "/get-staff",
            type: "GET",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                const staffContainer = $("#staff-container");
                staffContainer.empty();

                if (data.error) {
                    staffContainer.append(`
                        <div class="alert alert-warning text-center">
                            ${branchEmptyInfo}
                        </div>
                    `);
                    is_empty_branch = 1;
                    $(".location_tab").addClass("d-none");
                    $("#second-field").hide();
                    return;
                }

                $(".location_tab").removeClass("d-none");

                data.branches.forEach((branch) => {
                    const branchImage = branch.branch.branch_image
                        ? `${branch.branch.branch_image}`
                        : "/front/img/default-placeholder-image.png";

                    let branchHtml = `
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center w-100">
                                        <img src="${branchImage}" alt="Branch Image" class="avatar avatar-rounded me-2">
                                        <div>
                                            <div class="fs-15 fw-semibold">${branch.branch.branch_name}</div>
                                            <p class="mb-0 text-muted fs-11">
                                                ${branch.branch.branch_address}, ${branch.branch.branch_city}, ${branch.branch.branch_state}, ${branch.branch.branch_country} - ${branch.branch.branch_zip}
                                            </p>
                                        </div>
                                        <div class="ms-auto">
                                            <input type="checkbox" name="branch_select[]" value="${branch.branch.id}" class="branch-checkbox">
                                        </div>
                                    </div>
                                </div>`;

                    // Only render staff if exists
                    if (branch.staff.length > 0) {
                        branchHtml += `<div class="card-body"><div class="staff-list">`;

                        branch.staff.forEach((staff) => {
                            const staffImage = staff.user.profile_image
                                ? staff.user.profile_image
                                : "/assets/img/profile-default.png";

                            branchHtml += `
                            <div class="d-flex align-items-center mb-3">
                                <img src="${staffImage}" alt="Staff Image" class="avatar avatar-sm me-2">
                                <div>
                                    <div class="fs-14 fw-semibold">${
                                        staff.user.first_name
                                    } ${staff.user.last_name}</div>
                                    <p class="mb-0 text-muted fs-12">${
                                        staff.user.mobile_number ||
                                        "No Mobile Number"
                                    }</p>
                                </div>
                                <div class="ms-auto">
                                    <input type="checkbox" name="staff_select[]" value="${
                                        staff.user.user_id
                                    }" class="staff-checkbox" data-branch-id="${
                                branch.branch.id
                            }" disabled>
                                </div>
                            </div>`;
                        });

                        branchHtml += `</div></div>`; // close staff-list and card-body
                    }

                    branchHtml += `</div></div></div>`; // close card, col, row

                    staffContainer.append(branchHtml);
                });

                addCheckboxLogic();
            },
            error: function (xhr) {
                const errorMessage =
                    xhr.responseJSON?.error ||
                    "Failed to fetch branches. Please try again.";
                alert(errorMessage);
            },
        });
    }

    // Checkbox logic for enabling/disabling staff based on branch selection
    function addCheckboxLogic() {
        // Branch checkbox toggling
        $(".branch-checkbox").on("change", function () {
            const branchId = $(this).val();
            const isChecked = $(this).is(":checked");
            $(`.staff-checkbox[data-branch-id="${branchId}"]`)
                .prop("disabled", !isChecked)
                .prop("checked", false);
        });

        // Staff checkbox toggling
        $(".staff-checkbox").on("change", function () {
            const branchId = $(this).data("branch-id");
            const isBranchChecked = $(
                `.branch-checkbox[value="${branchId}"]`
            ).is(":checked");

            if (!isBranchChecked) {
                // Prevent toggling staff checkbox if the branch is not selected
                alert("You must select the branch before selecting its staff.");
                $(this).prop("checked", false);
            }
        });
    }

    $(document).ready(function () {
        let lastResponse = "";

        $("#openChatModal").click(function () {
            $("#chatModal").modal("show");
        });

        $("#sendMessage").click(function () {
            var userMessage = $("#userMessage").val().trim();

            if (userMessage) {
                $("#chat-box").append(
                    "<div><strong>You:</strong> " + userMessage + "</div>"
                );
                $("#userMessage").val("");

                // Display loading text
                var loadingId = "loading-" + new Date().getTime();
                $("#chat-box").append(
                    '<div id="' +
                        loadingId +
                        '"><div class="skeleton chat-skeleton label-loader mb-2"></div> <div class="skeleton chat2-skeleton label-loader"></div> </div>'
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

    $("#service_price").on("input", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/g, "")
        );

        if ($(this).val().length > 12) {
            $(this).val($(this).val().slice(0, 12));
        }
    });

    $(document).on("input", "#add_price", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/g, '')
                .slice(0, 10)
        );
    });

    $(document).ready(function () {
        $(".categoryProviderSelect").on("change", function () {
            const categoryId = $(this).val();

            const subcategoriesDropdown = $(".subcategories");

            if (categoryId) {
                subcategoriesDropdown.prop("disabled", false); // Enable dropdown
                var langCode = $("body").data("lang");
                fetchSubcategories(categoryId, langCode);
            } else {
                subcategoriesDropdown.prop("disabled", true); // Disable dropdown
                subcategoriesDropdown.html(
                    '<option value="">Select Sub Category</option>'
                );
            }
        });

        $("#pincode").on("input", function () {
            if ($(this).val().length > 6) {
                $(this).val($(this).val().slice(0, 6));
            }
        });

        let selectedFiles = new DataTransfer();

        $("#service_images").on("change", function (event) {
            const files = event.target.files;
            const errorSpan = $(".extension_error");
            let allValid = true;
            const validFiles = [];

            for (let i = 0; i < files.length; i++) {
                if (files[i].type.startsWith("image/")) {
                    let isDuplicate = false;
                    for (let j = 0; j < selectedFiles.files.length; j++) {
                        if (
                            files[i].name === selectedFiles.files[j].name &&
                            files[i].lastModified ===
                                selectedFiles.files[j].lastModified
                        ) {
                            isDuplicate = true;
                            break;
                        }
                    }
                    if (!isDuplicate) {
                        validFiles.push(files[i]);
                    }
                } else {
                    allValid = false;
                }
            }

            if (allValid) {
                validFiles.forEach((file) => selectedFiles.items.add(file));
                this.files = selectedFiles.files;
                updateImagePreview(validFiles); // Pass only newly added valid files to update image preview
                errorSpan.text(""); // Clear any existing error message
            } else {
                errorSpan.text(
                    "Only image files are allowed. Please select valid images."
                );
                this.value = ""; // Reset the file input to clear any invalid files
                const previeContainer = $("#image_preview_container");
                previeContainer.empty();
            }
        });

        // Function to update image preview
        function updateImagePreview(files) {
            const previewContainer = $("#image_preview_container");

            files.forEach((file, index) => {
                if (file.type.startsWith("image/")) {
                    const reader = new FileReader();

                    reader.onload = function (e) {
                        const imageHTML = `
                            <div class="avatar avatar-gallery me-3" data-index="${index}">
                                <img src="${e.target.result}" alt="Img" style="width: 100px; height: 100px; object-fit: cover;">
                                <a href="javascript:void(0);" class="trash-top d-flex align-items-center justify-content-center" data-index="${index}">
                                    <i class="ti ti-trash"></i>
                                </a>
                            </div>`;
                        previewContainer.append(imageHTML);
                    };

                    reader.readAsDataURL(file);
                }
            });
        }

        // Handle delete image action
        $(document).on("click", ".trash-top", function () {
            const index = $(this).data("index");
            const fileList = Array.from(selectedFiles.files);

            // Remove the file from selectedFiles
            fileList.splice(index, 1);

            // Update selectedFiles
            const dataTransfer = new DataTransfer();
            fileList.forEach((file) => dataTransfer.items.add(file));
            selectedFiles = dataTransfer; // Update selectedFiles with the new DataTransfer instance

            // Update the file input's files property
            $("#service_images")[0].files = selectedFiles.files;

            // Remove image preview from the DOM
            $(this).closest(".avatar-gallery").remove();

            // Reindex the previews
            $("#image_preview_container .avatar-gallery").each(function (i) {
                $(this).attr("data-index", i);
                $(this).find(".trash-top").attr("data-index", i);
            });
        });

        $("#addSlotBtn").on("click", function () {
            $("#slotData").slideDown();
        });

        $("#closeSlotBtn").on("click", function () {
            $("#slotData").slideUp();
        });

        const $priceType = $("#price_type");
        const $hoursSection = $(".hours-section");
        const $minutesSection = $(".minutes-section");
        const $hoursSelect = $("#hours_select");
        const $minutesSelect = $("#minutes_select");

        $priceType.change(function () {
            const selectedValue = $(this).val();

            $hoursSection.hide();
            $minutesSection.hide();
            $hoursSelect.val("");
            $minutesSelect.val("");

            if (selectedValue === "hourly") {
                $hoursSelect.val(1);
                $hoursSection.hide();
            } else if (selectedValue === "minute") {
                $minutesSection.show();
            } else if (
                selectedValue === "fixed" ||
                "squre-metter" ||
                "squre-Feet"
            ) {
                $hoursSelect.val(1);
                $hoursSection.show();
            }
        });

        $(document).on("change", "input[name='day_checkbox[]']", function () {
            const checkbox = $(this);
            const container = checkbox.closest(".mb-4");
            const day = container.attr("id").replace("Data", "");

            if (checkbox.is(":checked")) {
                const initialTimeInput = `
                    <div class="d-flex gap-3 mt-2 additional-time">
                        <input type="time" class="form-control start_time" name="start_time[${day}][]" id="start_time">
                        <input type="time" class="form-control end_time" name="end_time[${day}][]" id="end_time" readonly>
                        <a class="p-1 rounded-0 remove-time-btn" style="margin-top: 5px;">
                            <i class="ti ti-trash me-2 fw-bold fs-4"></i>
                        </a>
                    </div>
                `;
                container.find("#slotinputs").show().append(initialTimeInput);
            }
        });

        $(document).on("click", ".add-time-btn", function () {
            const container = $(this).closest(".mb-4");
            const day = container.attr("id").replace("Data", "");
            const isChecked = container
                .find('input[type="checkbox"]')
                .is(":checked");

            if (!isChecked) {
                return;
            }

            // Append a new time input
            const newTimeInput = `
                <div class="d-flex gap-3 mt-2 additional-time">
                    <input type="time" class="form-control start_time" name="start_time[${day}][]" id="start_time">
                    <input type="time" class="form-control end_time" name="end_time[${day}][]" id="end_time" readonly>
                    <a class="p-1 rounded-0 remove-time-btn" style="margin-top: 5px;">
                        <i class="ti ti-trash me-2 fw-bold fs-4"></i>
                    </a>
                </div>
            `;
            container.find("#slotinputs").show().append(newTimeInput);
        });

        $(document).on("click", ".remove-time-btn", function () {
            $(this).closest(".additional-time").remove();
        });

        $(document).on("change", ".start_time", function () {
            const startTimeInput = $(this);
            const selectedHours = $("#hours_select").val(); // Assuming you have these selectors
            const selectedMinutes = $("#minutes_select").val(); // Assuming you have these selectors
            const startTime = startTimeInput.val();
            const timeSlotDiv = startTimeInput.closest(".d-flex");
            const endTimeInput = timeSlotDiv.find(".end_time");

            // --- Step 1: Calculate End Time (Your existing logic) ---
            if ((selectedHours || selectedMinutes) && startTime) {
                const [startHour, startMinute] = startTime
                    .split(":")
                    .map(Number);
                let endHour = startHour;
                let endMinute = startMinute;

                if (selectedHours) {
                    endHour += parseInt(selectedHours);
                }
                if (selectedMinutes) {
                    endMinute += parseInt(selectedMinutes);
                }

                endHour += Math.floor(endMinute / 60);
                endMinute = endMinute % 60;
                endHour = endHour % 24;

                const formattedEndTime = `${endHour
                    .toString()
                    .padStart(2, "0")}:${endMinute
                    .toString()
                    .padStart(2, "0")}`;
                endTimeInput.val(formattedEndTime);
            } else {
                endTimeInput.val("");
                return; // No start time or duration, so no validation needed
            }

            // --- Step 2: Validate for Overlapping Times (New Logic) ---
            const currentStartTime = startTimeInput.val();
            const currentEndTime = endTimeInput.val();
            const dayContainer = startTimeInput.closest(".mb-4");
            let isOverlapping = false;

            if (!currentStartTime || !currentEndTime) return;

            // Iterate over all time slots within the same day
            dayContainer.find(".additional-time").each(function () {
                const otherSlot = $(this);

                // Don't compare the input with itself
                if (otherSlot.is(timeSlotDiv)) {
                    return; // 'continue' in jQuery's .each()
                }

                const existingStartTime = otherSlot.find(".start_time").val();
                const existingEndTime = otherSlot.find(".end_time").val();

                if (existingStartTime && existingEndTime) {
                    // Check for overlap: (StartA < EndB) && (StartB < EndA)
                    if (
                        currentStartTime < existingEndTime &&
                        existingStartTime < currentEndTime
                    ) {
                        isOverlapping = true;
                        return false; // 'break' in jQuery's .each()
                    }
                }
            });

            // --- Step 3: Handle the Overlap ---
            if (isOverlapping) {
                const dayName = dayContainer.attr("id").replace("Data", "");
                toastr.error(
                    "The selected time has already been occupied. Please select a different time."
                );
                startTimeInput.val(""); // Clear the invalid start time
                endTimeInput.val(""); // Clear the calculated end time
                startTimeInput.focus(); // Set focus back to the input for correction
            }
        });

        $('input[name="day_checkbox[]"]').on("change", function () {
            const parentDiv = $(this).closest(".mb-4");
            const slotInputs = parentDiv.find("#slotinputs");

            if ($(this).is(":checked")) {
                slotInputs.show();
            } else {
                slotInputs.hide();
                slotInputs.find(".start_time, .end_time").val("");
            }
        });

        function fetchSubcategories(categoryId, langCode) {
            $.ajax({
                url: "/api/get-register-subcategories",
                type: "POST",
                data: { category_id: categoryId, language_code: langCode },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function (data) {
                    let subcategoriesHtml =
                        '<option value="">Select Sub Category</option>';
                    data.forEach((subcategory) => {
                        subcategoriesHtml += `<option value="${subcategory.id}">${subcategory.name}</option>`;
                    });
                    $(".subcategories").html(subcategoriesHtml);
                },
                error: function (xhr) {
                    const errorMessage =
                        xhr.responseJSON && xhr.responseJSON.error
                            ? xhr.responseJSON.error
                            : "Failed to fetch subcategories. Please try again.";
                },
            });
        }
    });

    $(document).ready(function () {
        $("#service-form").validate({
            ignore: ".note-editor *",
            rules: {
                service_name: {
                    required: true,
                    minlength: 3,
                    maxlength: 255,
                    remote: {
                        url: "/api/provider/service/check-unique",
                        type: "post",
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                        },
                        data: {
                            service_name: function () {
                                return $("#service_name").val();
                            },
                            language_id: function () {
                                return $("#userLangId").val();
                            },
                        },
                    },
                },
                product_code: {
                    required: true,
                    minlength: 3,
                    maxlength: 50,
                },
                category: {
                    required: true,
                },
                sub_category: {
                    required: true,
                },
                description: {
                    required: true,
                    minlength: 10,
                },
                include: {
                    required: true,
                    minlength: 3,
                    maxlength: 500,
                },
                price_type: {
                    required: true,
                },
                service_price: {
                    required: true,
                    number: true,
                    min: 1,
                },
                basic_service_price: {
                    required: true,
                    number: true,
                    min: 0,
                },
                basic_price_description: {
                    required: true,
                    minlength: 10,
                    maxlength: 500,
                },
            },
            messages: {
                service_name: {
                    required: "The service name field is required.",
                    minlength:
                        "The service name must be at least 3 characters.",
                    maxlength: "The service name cannot exceed 255 characters.",
                    remote: "Service name already exist.",
                },
                product_code: {
                    required: "The product code field is required.",
                    minlength:
                        "The product code must be at least 3 characters.",
                    maxlength: "The product code cannot exceed 50 characters.",
                },
                category: {
                    required: "The category field is required.",
                },
                sub_category: {
                    required: "The sub-category field is required.",
                },
                description: {
                    required: "The description field is required.",
                    minlength:
                        "The description must be at least 10 characters.",
                },
                include: {
                    include: "The include field is required.",
                    minlength: "The include must be at least 3 characters.",
                    maxlength: "The description cannot exceed 500 characters.",
                },
                price_type: {
                    required: "The price type field is required.",
                },
                service_price: {
                    required: "The price field is required.",
                    number: "The price must be a valid number.",
                    min: "The price cannot be negative.",
                },
                basic_service_price: {
                    required: "The price field is required.",
                    number: "The price must be a valid number.",
                    min: "The price cannot be negative.",
                },
                basic_price_description: {
                    required: "The description field is required.",
                    minlength:
                        "The description must be at least 20 characters.",
                    maxlength: "The description cannot exceed 500 characters.",
                },
            },
            errorElement: "span",
            errorPlacement: function (error, element) {
                error.addClass("text-danger"); // Apply Bootstrap's text-danger class
                if (element.attr("id") === "description") {
                    error.insertAfter(".note-editor"); // Show error below Summernote editor
                } else {
                    element.closest(".mb-3").append(error);
                }
            },
            highlight: function (element) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element) {
                $(element).removeClass("is-invalid").addClass("is-valid");
            },
        });

        $(".note-editable").on("keyup", function () {
            $("#description").valid();
        });

        $("#service_btn").on("click", function (event) {
            event.preventDefault();

            let serviceFormData = $("#service-form").serializeArray();

            if ($("#service-form").valid()) {
                let formDataCollection = {};
                serviceFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                if (is_empty_branch == 1) {
                    $("#first-field").hide();
                    $("#second-field").hide();
                    $("#third-field").show();
                    $("#progressbar li").eq(0).removeClass("active");
                    $("#progressbar li").eq(1).removeClass("active");
                    $("#progressbar li").eq(2).addClass("active");
                } else {
                    $("#first-field").hide();
                    $("#second-field").show();
                    $("#progressbar li").eq(0).removeClass("active");
                    $("#progressbar li").eq(1).addClass("active");
                }
            }
        });

        $("#location_prv").on("click", function () {
            $("#second-field").hide();
            $("#first-field").show();
            $("#progressbar li").eq(1).removeClass("active");
            $("#progressbar li").eq(0).addClass("active");
        });

        $("#location-form").validate({
            rules: {
                address: {
                    required: true,
                    minlength: 5,
                    maxlength: 255,
                },
                pincode: {
                    required: false,
                    maxlength: 6,
                },
                state: {
                    required: true,
                },
                city: {
                    required: true,
                },
                country: {
                    required: true,
                },
            },
            messages: {
                address: {
                    required: "The address field is required.",
                    minlength: "The address must be at least 5 characters.",
                    maxlength: "The address cannot exceed 255 characters.",
                },
                pincode: {
                    required: "The pincode field is optional.",
                    maxlength: "The pincode cannot exceed 6 digits.",
                },
                state: {
                    required: "The state field is required.",
                },
                city: {
                    required: "The city field is required.",
                },
                country: {
                    required: "The country field is required.",
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
        });

        $("#location_btn").on("click", function (event) {
            event.preventDefault();

            let locationFormData = $("#location-form").serializeArray();

            if ($("#location-form").valid()) {
                let formDataCollection = {};
                locationFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                $("#second-field").hide();
                $("#third-field").show();
                $("#progressbar li").eq(1).removeClass("active");
                $("#progressbar li").eq(2).addClass("active");
            }
        });

        $("#staff_prv").on("click", function () {
            $("#second-field").hide();
            $("#first-field").show();
            $("#progressbar li").eq(1).removeClass("active");
            $("#progressbar li").eq(0).addClass("active");
        });

        $("#staff_btn").on("click", function (event) {
            event.preventDefault();

            // Serialize form data
            let staffFormData = $("#staff-form").serializeArray();

            // Check if the form is valid
            if ($("#staff-form").valid()) {
                // Build the payload
                let payload = [];

                // Iterate over each selected branch
                $('input[name="branch_select[]"]:checked').each(function () {
                    const branchId = $(this).val(); // Get branch ID

                    // Find all selected staff under this branch
                    const staffIds = $(
                        `input[name="staff_select[]"][data-branch-id="${branchId}"]:checked`
                    )
                        .map(function () {
                            return $(this).val(); // Get staff ID
                        })
                        .get();

                    // Add the branch and staff to the payload
                    payload.push({
                        branch_id: branchId,
                        staff_ids: staffIds,
                    });
                });

                // Add the payload to the formDataCollection
                let formDataCollection = {};
                staffFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                // Set the structured payload in formDataCollection
                formDataCollection["payload"] = JSON.stringify(payload);

                // Hide the second field and show the third field
                $("#second-field").hide();
                $("#third-field").show();

                // Update progress bar
                $("#progressbar li").eq(1).removeClass("active");
                $("#progressbar li").eq(2).addClass("active");

                console.log(formDataCollection); // Debugging: View the structured data
            }
        });

        $("#image-form").validate({
            rules: {
                "service_images[]": {
                    required: true,
                    extension: "jpg|jpeg|png|svg|webp",
                },
                service_video: {
                    required: false, // Not mandatory
                    url: true, // Ensure it's a valid URL format
                },
            },
            messages: {
                "service_images[]": {
                    required: "The service image is required.",
                    extension:
                        "Only image files (jpg, jpeg, png, webp, gif) are allowed.",
                },
                service_video: {
                    url: "Please enter a valid URL for the video.", // Custom message for invalid URL
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
        });

        $("#image_btn").on("click", function (event) {
            event.preventDefault();
            if ($("#image-form").valid()) {
                $("#third-field").hide();

                $("#forth-field").show();

                $("#progressbar li").eq(2).removeClass("active");
                $("#progressbar li").eq(3).addClass("active");
            } else {
            }
        });

        $("#image_prv").on("click", function () {
            if (is_empty_branch == 1) {
                $("#second-field").hide();
                $("#third-field").hide();
                $("#first-field").show();
                $("#progressbar li").eq(2).removeClass("active");
                $("#progressbar li").eq(1).removeClass("active");
                $("#progressbar li").eq(0).addClass("active");
            } else {
                $("#third-field").hide();
                $("#second-field").show();
                $("#progressbar li").eq(2).removeClass("active");
                $("#progressbar li").eq(1).addClass("active");
            }
        });

        $("#seo_prv").on("click", function () {
            $("#forth-field").hide();
            $("#third-field").show();
            $("#progressbar li").eq(3).removeClass("active");
            $("#progressbar li").eq(2).addClass("active");
        });

        $("#seo-form").validate({
            rules: {
                seo_title: {
                    required: true,
                    maxlength: 255,
                },
                seo_tag: {
                    required: true,
                    maxlength: 255,
                },
                seo_description: {
                    required: true,
                    maxlength: 255,
                },
            },
            messages: {
                seo_title: {
                    required: "The SEO title field is required.",
                    maxlength: "The SEO title cannot exceed 255 characters.",
                },
                seo_tag: {
                    required: "The SEO tag field is required.",
                    maxlength: "The SEO tag cannot exceed 255 characters.",
                },
                seo_description: {
                    required: "The SEO description field is required.",
                    maxlength:
                        "The SEO description cannot exceed 255 characters.",
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
        });

        $("#seo_btn").on("click", function (event) {
            event.preventDefault();

            let serviceFormData = $("#service-form").serializeArray();
            let locationFormData = $("#location-form").serializeArray();
            let staffFormData = $("#staff-form").serializeArray();
            let imageFormData = $("#location-form").serializeArray();
            let seoFormData = $("#seo-form").serializeArray();

            const all = 1;

            if ($("#seo-form").valid()) {
                let finalFormData = new FormData();

                finalFormData.append(
                    "_token",
                    $('meta[name="csrf-token"]').attr("content")
                );

                [
                    ...serviceFormData,
                    ...locationFormData,
                    ...staffFormData,
                    ...imageFormData,
                    ...seoFormData,
                ].forEach(function (item) {
                    finalFormData.append(item.name, item.value);
                });

                let imageFiles = $("#service_images")[0].files;
                if (imageFiles.length > 0) {
                    for (let i = 0; i < imageFiles.length; i++) {
                        finalFormData.append("service_images[]", imageFiles[i]);
                    }
                }

                $("input[name='add_image[]']").each(function (index) {
                    if ($(this)[0].files.length > 0) {
                        finalFormData.append(
                            `add_image[${index}]`,
                            $(this)[0].files[0]
                        );
                    }
                });

                let serviceVideo = $("#service_video").val();
                if (serviceVideo) {
                    finalFormData.append("service_video", serviceVideo);
                }

                let payload = [];
                $('input[name="branch_select[]"]:checked').each(function () {
                    const branchId = $(this).val();

                    const staffIds = $(
                        `input[name="staff_select[]"][data-branch-id="${branchId}"]:checked`
                    )
                        .map(function () {
                            return $(this).val();
                        })
                        .get();

                    payload.push({
                        branch_id: branchId,
                        staff_ids: staffIds,
                    });
                });

                finalFormData.append(
                    "branch_staff_payload",
                    JSON.stringify(payload)
                );

                let dayCheckbox = [];
                let startTimeData = {};
                let endTimeData = {};

                $('input[name="day_checkbox[]"]:checked').each(function () {
                    const day = $(this).val();
                    dayCheckbox.push(day);

                    const dayKey = day.toLowerCase();

                    startTimeData[dayKey] = [];
                    endTimeData[dayKey] = [];

                    $(`input[name="start_time[${dayKey}][]"]`).each(function (
                        index
                    ) {
                        const startTime = $(this).val();
                        const endTime = $(`input[name="end_time[${dayKey}][]"]`)
                            .eq(index)
                            .val();

                        if (startTime && endTime) {
                            startTimeData[dayKey].push(startTime);
                            endTimeData[dayKey].push(endTime);
                        }
                    });
                });

                finalFormData.append(
                    "day_checkbox",
                    JSON.stringify(dayCheckbox)
                );
                finalFormData.append(
                    "start_time",
                    JSON.stringify(startTimeData)
                );
                finalFormData.append("end_time", JSON.stringify(endTimeData));

                $("#serviceLoader").show();

                $.ajax({
                    url: "/provider/service/save",
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
                        $(".add_btn").attr("disabled", true);
                        $(".add_btn").html(
                            '<span class="spinner-border text-light spinner-border-sm align-middle" role="status"></span>'
                        );
                    },
                })
                    .done((response, statusText, xhr) => {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $(".add_btn").removeAttr("disabled");
                        $(".add_btn").html("Submit");

                        if (response.code === 200) {
                            if (response.service_approval_status == 1) {
                                $("#provider_service_success_modal").modal(
                                    "show"
                                );
                                setTimeout(function () {
                                    window.location.href =
                                        response.redirect_url;
                                }, 2000);
                            } else {
                                toastr.success(response.message);
                                setTimeout(function () {
                                    window.location.href =
                                        response.redirect_url;
                                }, 10);
                            }

                            $(".form-control").removeClass("is-valid");

                            $(".is-invalid").removeClass("is-invalid");
                            $(".invalid-feedback").remove();
                        }
                    })
                    .fail((error) => {
                        $("#serviceLoader").hide();
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $(".add_btn").removeAttr("disabled");
                        $(".add_btn").html("submit");

                        if (error.status == 422) {
                            $.each(error.responseJSON, function (key, val) {
                                $("#" + key).addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                            });
                        } else {
                            toastr.error(error.responseJSON.message);
                        }
                    });
            }
        });
    });

    let cachedCountries = [];
    let cachedStates = [];
    let cachedCities = [];

    $(document).ready(function () {
        $(".selects").select2();
        $.when(
            $.getJSON("/countries.json"),
            $.getJSON("/states.json"),
            $.getJSON("/cities.json")
        )
            .done(function (countriesData, statesData, citiesData) {
                cachedCountries = countriesData[0].countries;
                cachedStates = statesData[0].states;
                cachedCities = citiesData[0].cities;

                getCountries();
            })
            .fail(function () {});

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

    function getCountries(selectedCountry = null) {
        const countrySelect = $("#country");
        clearDropdown(countrySelect);

        $.each(cachedCountries, function (index, country) {
            countrySelect.append(
                $("<option>", {
                    value: country.id,
                    text: country.name,
                    selected: country.id == selectedCountry,
                })
            );
        });

        if (selectedCountry) {
            getStates(selectedCountry);
        }
    }

    function getStates(selectedCountry, selectedState = null) {
        const stateSelect = $("#state");
        clearDropdown(stateSelect);

        const states = cachedStates.filter(
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
            getCities(states[0].id);
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
                getCities(selectedState);
            }
        }
    }

    function getCities(selectedState, selectedCity = null) {
        const citySelect = $("#city");
        clearDropdown(citySelect);

        const cities = cachedCities.filter(
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
        citySelect.find('option[value=""]').remove();
    }

    $(".add-extra").on("click", function () {
        const newServiceRow = `
            <div class="row mb-3 extra-service">
                <div class="col-xl-5">
                    <div class="d-flex align-items-center">
                        <div class="file-upload service-file-upload d-flex align-items-center justify-content-center flex-column me-4">
                            <div class="image-preview-wrapper">
                                <i class="ti ti-photo"></i>
                                <img src="" alt="Preview" class="img-preview d-none" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                            </div>
                            <input type="file" name="add_image[]" class="add_image" accept="image/*">
                        </div>
                        <div class="flex-fill">
                            <label class="form-label">${$(
                                "#appendaddservice"
                            ).data(
                                "name"
                            )} <span class="text-danger">*</span></label>
                            <input type="text" name="add_name[]" id="add_name" class="form-control" placeholder="${$(
                                "#appendaddservice"
                            ).data("service_name_placeholder")}">
                            <span class="invalid-feedback" id="add_name_error"></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3">
                    <div class="mb-3">
                        <label class="form-label">${$("#appendaddservice").data(
                            "price"
                        )} <span class="text-danger">*</span></label>
                        <input type="number" name="add_price[]" id="add_price" class="form-control" placeholder="${$(
                            "#appendaddservice"
                        ).data("pricing_placeholder")}">
                        <span class="invalid-feedback" id="add_price_error"></span>
                    </div>
                </div>
                <div class="col-xl-3">
                    <div class="mb-3">
                        <label class="form-label">${$("#appendaddservice").data(
                            "description"
                        )} <span class="text-danger">*</span></label>
                        <input type="text" name="add_duration[]" id="add_duration" class="form-control" placeholder="${$(
                            "#appendaddservice"
                        ).data("enter_description")}">
                        <span class="invalid-feedback" id="add_duration_error"></span>
                    </div>
                </div>
                <div class="col-xl-1 d-flex align-items-center">
                    <a href="javascript:void(0);" class="text-danger remove-extra"><i class="ti ti-trash fs-14"></i></a>
                </div>
            </div>
        `;
        $("#appendaddservice").append(newServiceRow);
    });

    $(document).on("change", ".add_image", function () {
        const fileInput = this;
        const preview = $(fileInput)
            .siblings(".image-preview-wrapper")
            .find(".img-preview");
        const icon = $(fileInput).siblings(".image-preview-wrapper").find("i");

        if (fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                preview.attr("src", e.target.result).removeClass("d-none");
                icon.addClass("d-none");
            };

            reader.readAsDataURL(fileInput.files[0]);
        } else {
            // Reset to icon if no file is selected
            preview.addClass("d-none").attr("src", "");
            icon.removeClass("d-none");
        }
    });

    $(document).on("click", ".remove-extra", function () {
        $(this).closest(".extra-service").remove();
    });

    $(document).ready(function () {
        $("#basic_btn").removeClass("btn-primary").addClass("btn-dark");
        $("#basic_container").show();

        $(".price-btn").on("click", function (e) {
            e.preventDefault();

            $(".price-btn").removeClass("btn-dark").addClass("btn-primary");
            $(this).removeClass("btn-primary").addClass("btn-dark");

            const selectedId = $(this).attr("id");
            $("#basic_container, #premium_container, #pro_container").hide();

            if (selectedId === "basic_btn") {
                $("#basic_container").slideDown();
            } else if (selectedId === "premium_btn") {
                $("#premium_container").slideDown();
            } else if (selectedId === "pro_btn") {
                $("#pro_container").slideDown();
            }
        });
    });
}

if (pageValue === "provider.service") {
    $(document).on("click", "#providerAddService", function (e) {
        e.preventDefault();
        const authId = $("#auth_id").val();
        $.ajax({
            url: "/provider/subscription/detail",
            type: "POST",
            data: {
                id: authId,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $(".add_service").attr("disabled", true);
                $(".add_service").html(
                    '<div class="spinner-border text-light" role="status"></div>'
                );
            },
        })
            .done((response, statusText, xhr) => {
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid");
                if (response.code === 200) {
                    if (response.no_package === true) {
                        $("#no_sub").modal("show");
                        $(".add_service").removeAttr("disabled");
                        $(".add_service").html("Add Service");
                    }
                    // Check for 'sub_end' in the response
                    else if (response.sub_count_end === true) {
                        $("#sub_count_end").modal("show");
                        $(".add_service").removeAttr("disabled");
                        $(".add_service").html("Add Service");
                    } else if (response.sub_end === true) {
                        $("#sub_end").modal("show");
                        $(".add_service").removeAttr("disabled");
                        $(".add_service").html("Add Service");
                    }
                    // Check for 'redirect_url' in the response
                    else if (response.redirect_url) {
                        setTimeout(function () {
                            window.location.href = response.redirect_url;
                        });
                    }
                    // Handle unexpected cases (optional)
                    else {
                        toastr.error("Unexpected response from server.");
                    }
                }
            })
            .fail((error) => {
                $(".error-text").text(""); // Clear all previous error messages
                $(".form-control").removeClass("is-invalid"); // Remove invalid classes
                $(".add_service").removeAttr("disabled"); // Re-enable the button
                $(".add_service").html("Add Service");

                if (error.status == 422) {
                    // Loop through validation errors and display them
                    $.each(error.responseJSON.errors, function (key, val) {
                        $("#" + key).addClass("is-invalid"); // Highlight invalid field
                        $("#" + key + "_error").text(val[0]); // Set error text
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
    });

    function page_table() {
        fetchProviderService(1);
    }

    function fetchProviderService(service) {
        $("#tabelSkeletonLoader").show();
        const authId = $("#auth_id").val();
        $.ajax({
            url: "/api/provider/service-list",
            type: "POST",
            dataType: "json",
            data: {
                auth_id: authId,
                order_by: "asc",
                sort_by: "id",
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === "200") {
                    populateProviderService(response.data, response.meta);
                }
                $("#tabelSkeletonLoader").hide();
                $("#loader-table").hide();
                $(".label-loader, .input-loader").hide(); // Hide all skeleton loaders
                $(".real-label, .real-input").removeClass("d-none");
            },
            error: function (error) {
                $("#tabelSkeletonLoader").hide();
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

 function populateProviderService(Service, meta) {
        let tableBody = "";

        if (Service.length > 0) {
            Service.forEach((Service, index) => {

                let promotedBadge = Service.service_promoted
                    ? `<span class="badge bg-warning text-dark ms-2">
                            Promoted
                    </span>`
                    : '';

                tableBody += `
                    <tr>
                        <td>${index + 1}</td>

                        <td>
                            ${Service.source_name}
                            ${promotedBadge}
                        </td>

                        <td>${Service.slug}</td>

                        <td>
                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input serviceId"
                                    ${Service.status == 1 ? "checked" : ""}
                                    type="checkbox"
                                    role="switch"
                                    data-id="${Service.id}">
                            </div>
                        </td>

                        <td>
                            <span class="badge ${
                                Service.verified_status == 1
                                    ? "badge-soft-success"
                                    : "badge-soft-danger"
                            }">
                                <i class="ti ti-point-filled"></i>
                                ${Service.verified_status == 1 ? "Verified" : "Not Verified"}
                            </span>
                        </td>

                        <td>
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item">
                                    <a href="javascript:void(0);" onclick="editService('${Service.slug}')">
                                        <i class="ti ti-pencil fs-20"></i>
                                    </a>
                                </li>

                                <li class="list-inline-item">
                                    <a class="delete" href="#"
                                    data-bs-toggle="modal"
                                    data-bs-target="#delete-modal"
                                    data-id="${Service.id}">
                                        <i class="ti ti-trash fs-20"></i>
                                    </a>
                                </li>

                                <li class="list-inline-item">
                                    <a class="promote btn btn-sm btn-primary"
                                    href="/provider/promote-service/${Service.id}">
                                        Promote
                                    </a>
                                </li>
                            </ul>
                        </td>
                    </tr>
                `;
            });
        } else {
            tableBody = `
                        <tr>
                            <td colspan="5" class="text-center">No Service found</td>
                        </tr>
                    `;
        }

        $("#datatable_service tbody").html(tableBody);
        if (
            Service.length != 0 &&
            !$.fn.DataTable.isDataTable("#datatable_service")
        ) {
            $("#datatable_service").DataTable({
                ordering: true,
                language: datatableLang,
            });
        }
    }

    function editService(slug) {
        window.open(`/provider/edit?slug=${slug}`, "_self");
    }

    $(document).on("change", ".serviceId", function (e) {
        e.preventDefault();

        var serviceId = $(this).attr("data-id");

        var formData = {
            id: serviceId,
        };

        $.ajax({
            url: "/api/provider/service/status",
            type: "POST",
            data: formData,
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    page_table();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                // alert('Failed to set default currency. Please try again.');
            },
        });
    });

    $(document).on("click", '.delete[data-bs-toggle="modal"]', function (e) {
        e.preventDefault();
        var deleteId = $(this).data("id");
        $("#confirmDelete").data("id", deleteId);
    });

    $(document).on("click", "#confirmDelete", function (e) {
        e.preventDefault();

        var deleteId = $(this).data("id");
        $.ajax({
            url: "/api/provider/service/delete",
            type: "POST",
            data: {
                id: deleteId,
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
                $("#serviceLoader").hide();
                if (response.success) {
                    toastr.success(response.message);
                    page_table();
                    $("#delete-modal").modal("hide");
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                $("#serviceLoader").hide();
                toastr.error("An error occurred while trying to delete.");
            },
        });
    });

    $(document).ready(function () {
        page_table();
    });
}

if (pageValue === "provider.edit.service") {
    let is_empty_branch = 0;

    $(document).ready(function () {
        $("#description").summernote({
            height: 300,
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

    $("#service_price").on("input", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/g, "")
        );

        if ($(this).val().length > 12) {
            $(this).val($(this).val().slice(0, 12));
        }
    });
    $("#hours_select").on("input", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/g, "")
        );

        if ($(this).val().length > 12) {
            $(this).val($(this).val().slice(0, 12));
        }
    });

    $(document).ready(function () {
        let selectedFiles = new DataTransfer(); // Used to manage the selected files

        $("#service_images").on("change", function (event) {
            const files = event.target.files;

            for (let i = 0; i < files.length; i++) {
                selectedFiles.items.add(files[i]);
            }

            this.files = selectedFiles.files;
        });
    });

    $("#language_id").on("change", function () {
        let selectedLanguageId = $(this).val();
        fetchSubcategories(selectedLanguageId); // Call your function with the new selected language_id
    });

    $(".categoryProviderSelect").on("change", function () {
        var categoryId = $(this).val();
        var selectedLanguageId = $("#language_id").val();

        fetchSubcategories(selectedLanguageId, categoryId);
    });

    let branchEmptyInfo =
        "No branches found at the moment. You can proceed further.";

    function fetchBranch(serviceBranchIds = [], serviceBranchStaffIds = []) {
        $.ajax({
            url: "/get-staff",
            type: "GET",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                const staffContainer = $("#staff-container");
                staffContainer.empty();

                if (data.error) {
                    staffContainer.append(`
                        <div class="alert alert-warning text-center branch-empty">
                        ${branchEmptyInfo}
                        </div>
                    `);
                    is_empty_branch = 1;
                    $(".location_tab").addClass("d-none");
                    $("#second-field").hide();
                    return;
                }
                $(".location_tab").removeClass("d-none");

                data.branches.forEach((branch) => {
                    const isChecked = serviceBranchIds.includes(
                        branch.branch.id
                    )
                        ? "checked"
                        : "";

                    // Build branch image URL
                    const branchImage = branch.branch.branch_image;

                    let branchHtml = `
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center w-100">
                                        <img src="${branchImage}" alt="Branch Image" class="avatar avatar-rounded me-2">
                                        <div>
                                            <div class="fs-15 fw-semibold">${branch.branch.branch_name}</div>
                                            <p class="mb-0 text-muted fs-11">
                                                ${branch.branch.branch_address}, ${branch.branch.branch_city}, ${branch.branch.branch_state}, ${branch.branch.branch_country} - ${branch.branch.branch_zip}
                                            </p>
                                        </div>
                                        <div class="ms-auto">
                                            <input type="checkbox" name="branch_select[]" value="${branch.branch.id}" class="branch-checkbox" ${isChecked}>
                                        </div>
                                    </div>
                                </div>`;

                    // Only add staff card body if staff exists
                    if (branch.staff.length > 0) {
                        branchHtml += `<div class="card-body"><div class="staff-list">`;

                        branch.staff.forEach((staff) => {
                            const isCheckedStaff =
                                serviceBranchStaffIds.includes(
                                    staff.user.user_id
                                )
                                    ? "checked"
                                    : "";
                            const staffImage = staff.user.profile_image;

                            branchHtml += `
                            <div class="d-flex align-items-center mb-3">
                                <img src="${staffImage}" alt="Staff Image" class="avatar avatar-sm me-2">
                                <div>
                                    <div class="fs-14 fw-semibold">${
                                        staff.user.first_name
                                    } ${staff.user.last_name}</div>
                                    <p class="mb-0 text-muted fs-12">${
                                        staff.user.mobile_number ||
                                        "No Mobile Number"
                                    }</p>
                                </div>
                                <div class="ms-auto">
                                    <input type="checkbox" name="staff_select[]" value="${
                                        staff.user.user_id
                                    }" class="staff-checkbox" data-branch-id="${
                                branch.branch.id
                            }" ${isCheckedStaff}>
                                </div>
                            </div>`;
                        });

                        branchHtml += `</div></div>`; // Close staff-list and card-body
                    }

                    branchHtml += `</div></div></div>`; // Close card, col, row

                    staffContainer.append(branchHtml);
                });

                addCheckboxLogic();
            },
            error: function (xhr) {
                const errorMessage =
                    xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : "Failed to fetch branches. Please try again.";
                alert(errorMessage);
            },
        });
    }

    // Checkbox logic for enabling/disabling staff based on branch selection
    function addCheckboxLogic() {
        // Branch checkbox toggling
        $(".branch-checkbox").on("change", function () {
            const branchId = $(this).val();
            const isChecked = $(this).is(":checked");
            $(`.staff-checkbox[data-branch-id="${branchId}"]`)
                .prop("disabled", !isChecked)
                .prop("checked", false);
        });

        // Staff checkbox toggling
        $(".staff-checkbox").on("change", function () {
            const branchId = $(this).data("branch-id");
            const isBranchChecked = $(
                `.branch-checkbox[value="${branchId}"]`
            ).is(":checked");

            if (!isBranchChecked) {
                // Prevent toggling staff checkbox if the branch is not selected
                alert("You must select the branch before selecting its staff.");
                $(this).prop("checked", false);
            }
        });
    }

    function fetchSubcategories(
        languageId,
        categoryId,
        selectedSubcategory = null
    ) {
        $.ajax({
            url: "/get-subcategories",
            type: "POST",
            data: { language_id: languageId, category_id: categoryId },
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
                $("#serviceLoader").hide();
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
            },
            error: function (xhr) {
                const errorMessage =
                    xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : "Failed to fetch subcategories. Please try again.";
            },
        });
    }

    $(document).ready(function () {
        let selectedLanguageId = $("#language_id").val();
        fetchCategories(selectedLanguageId);

        $("#language_id").on("change", function () {
            let selectedLanguageId = $(this).val();
            fetchCategories(selectedLanguageId);
        });
    });

    function fetchCategories(languageId, selectedCategory = null) {
        $.ajax({
            url: "/api/get-categories",
            type: "POST",
            data: { language_id: languageId },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                let categoryProviderSelect =
                    '<option value="">Select Sub Category</option>';
                data.forEach((category) => {
                    categoryProviderSelect += `<option value="${category.id}" ${
                        category.id == selectedCategory ? "selected" : ""
                    }>${category.name}</option>`;
                });
                $(".categoryProviderSelect").html(categoryProviderSelect);
            },
            error: function (xhr) {
                const errorMessage =
                    xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : "Failed to fetch subcategories. Please try again.";
            },
        });
    }

    function page_details(pageSlug) {
        if (pageSlug) {
            fetchPageDetails(pageSlug);
        } else {
            toastr.error("No page ID provided.");
        }
    }

    $(document).ready(function () {
        let pageSlug = getQueryParam("slug");
        page_details(pageSlug);
    });

    function getQueryParam(param) {
        let urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    let Data = {};

    function fetchPageDetailsDynamic() {
        // Get the slug dynamically from the input field
        const serviceSlug = document.getElementById("service_slug").value;

        if (serviceSlug) {
            fetchPageDetails(serviceSlug);
        } else {
            console.error("Service slug is not available.");
        }
    }

    $("#language_id").on("change", function () {
        var langId = $(this).val();
        languageTranslate(langId);
    });

    function changeKeyboardLayout() {
        var langId = $("#language_id").val();
        var selectedOption = $("#language_id option:selected");
        var languageCode = selectedOption.data("code"); // assuming 'code' holds the language code (e.g., 'ar' for Arabic)

        // Check if the selected language requires RTL
        if (languageCode === "ar" || languageCode === "he") {
            $("input, textarea").css("direction", "rtl");
        } else {
            $("input, textarea").css("direction", "ltr");
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        setLanguageId();
    });

    function setLanguageId() {
        const selectedLanguageId = document.getElementById("language_id").value;

        document.getElementById("language_id_input").value = selectedLanguageId;
    }

    document.addEventListener("DOMContentLoaded", function () {
        var defaultLangId = $("#userLangId").val();

        languageTranslate(defaultLangId);
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

                if (response.code === 200 && Object.keys(trans).length > 0) {
                    $(".form-control")
                        .removeClass("is-invalid")
                        .removeClass("is-valid");
                    $(".invalid-feedback").text("");

                    $("select option").each(function () {
                        var translateKey = $(this).data("translate");
                        if (trans.hasOwnProperty(translateKey)) {
                            $(this).text(trans[translateKey]);
                        }
                    });

                    if (
                        trans[
                            "No branches found at the moment. You can proceed further."
                        ]
                    ) {
                        branchEmptyInfo =
                            trans[
                                "No branches found at the moment. You can proceed further."
                            ];
                    }

                    $(".translatable").each(function () {
                        var translateKey = $(this).data("translate");

                        if (trans.hasOwnProperty(translateKey)) {
                            var translatedText = trans[translateKey];

                            if ($(this).is("input, textarea")) {
                                $(this).attr("placeholder", translatedText);

                                if ($(this).data("role") === "tagsinput") {
                                    $(this).tagsinput("destroy");
                                    $(this).attr("placeholder", translatedText);
                                    $(this).tagsinput();
                                }
                            } else if ($(this).hasClass("invalid-feedback")) {
                                $(this).text("");
                                $(this).text(translatedText);
                            } else {
                                $(this).text(translatedText);
                            }
                        }
                    });

                    $("#service-form").validate().settings.messages = {
                        service_name: {
                            required: trans.service_name_required,
                            minlength: trans.service_name_minlength,
                            maxlength: trans.service_name_maxlength,
                            remote: trans.service_name_remote,
                        },
                        product_code: {
                            required: trans.product_code_required,
                            minlength: trans.product_code_minlength,
                            maxlength: trans.product_code_maxlength,
                        },
                        category: {
                            required: trans.category_required,
                        },
                        sub_category: {
                            required: trans.sub_category_required,
                        },
                        description: {
                            required: trans.description_required,
                            minlength: trans.description_minlength,
                            maxlength: trans.description_maxlength,
                        },
                        include: {
                            required: trans.include_required,
                            minlength: trans.include_minlength,
                            maxlength: trans.include_maxlength,
                        },
                        price_type: {
                            required: trans.price_type_required,
                        },
                        service_price: {
                            required: trans.service_price_required,
                            number: trans.service_price_number,
                            min: trans.service_price_min,
                        },
                        basic_service_price: {
                            required: trans.basic_service_price_required,
                            number: trans.basic_service_price_number,
                            min: trans.basic_service_price_min,
                        },
                        basic_price_description: {
                            required: trans.basic_price_description_required,
                            minlength: trans.basic_price_description_minlength,
                            maxlength: trans.basic_price_description_maxlength,
                        },
                    };

                    $("#location-form").validate().settings.messages = {
                        address: {
                            required: trans.address_required_service,
                            minlength: trans.address_minlength_service,
                            maxlength: trans.address_maxlength_service,
                        },
                        pincode: {
                            required: trans.pincode_required_service,
                            maxlength: trans.pincode_maxlength_service,
                        },
                        state: {
                            required: trans.state_required_service,
                        },
                        city: {
                            required: trans.city_required_service,
                        },
                        country: {
                            required: trans.country_required_service,
                        },
                    };
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }

    function fetchPageDetails(slug) {
        $("#serviceLoader").show();
        const serviceId = $("#service_id").val(); // Get the service ID
        const languageId = $("#language_id").val(); // Get the selected language ID
        const servicesSlug = $("#service_slug").val(); // Get the selected language ID
        const parentId = $("#parent_id").val(); // Get the selected language ID
        $.ajax({
            url: `/api/provider/service-details/${slug}`,
            type: "POST",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            data: {
                service_id: serviceId,
                language_id: languageId,
                services_slug: servicesSlug,
                parent_id: parentId,
            },
            success: function (response) {
                if (response.code === 200) {
                    const data = response.data.product;
                    const metadata = response.data.meta;
                    const serviceBranches = response.data.service_branch;
                    const serviceBranchesStaffs = serviceBranches.flatMap(
                        (branch) => branch.staff_details
                    );

                    const serviceBranchIds = serviceBranches.map(
                        (branch) => branch.id
                    );
                    const serviceBranchStaffIds = serviceBranchesStaffs.map(
                        (staff) => staff.id
                    );
                    fetchBranch(serviceBranchIds, serviceBranchStaffIds);

                    Data = {
                        country: data.country,
                        state: data.state,
                        city: data.city,
                    };

                    // Populate basic product details
                    $("#id").val(data.id);
                    $("#service_id").val(data.id);
                    $("#service_slug").val(data.slug);
                    $("#parent_id").val(data.parent_id);
                    $("#category_id").val(data.source_category);
                    $("#service_name").val(data.source_name);
                    $("#product_code").val(data.source_code);
                    if (
                        data.source_description &&
                        data.source_description.trim() !== ""
                    ) {
                        $("#description").summernote(
                            "code",
                            data.source_description
                        );
                    }
                    $("#price_type").val(data.price_type);
                    if (data.price_type === "free") {
                        $("#free_price").prop("checked", true);
                        $("#price_type").val("free");
                    } else {
                        $("#free_price").prop("checked", false);
                    }
                    const priceTypeSelect = $("#price_type");
                    if (data.price_type === "free") {
                        if (
                            priceTypeSelect.find("option[value='free']")
                                .length === 0
                        ) {
                            priceTypeSelect.append(
                                '<option value="free">Free</option>'
                            );
                        }
                        priceTypeSelect.val("free");
                    } else {
                        priceTypeSelect.find("option[value='free']").remove();
                        priceTypeSelect.val(data.price_type);
                    }
                    $("#hours_select").val(data.duration);
                    $("#minutes_select").val(data.duration);
                    $("#service_price").val(data.source_price);
                    $("#seo_title").val(data.seo_title);
                    $("#seo_tag").tagsinput("removeAll");
                    var tagsArray = (data.tags || "")
                        .split(",")
                        .map((tag) => tag.trim());
                    tagsArray.forEach((tag) =>
                        $("#seo_tag").tagsinput("add", tag)
                    );
                    $("#include").tagsinput("removeAll");
                    var tagsArray = (data.include || "")
                        .split(",")
                        .map((include) => include.trim());
                    tagsArray.forEach((include) =>
                        $("#include").tagsinput("add", include)
                    );
                    $("#seo_description").val(data.seo_description);
                    $("#address").val(data.address);
                    $("#pincode").val(data.pincode);
                    $("#plan").val(data.plan);
                    $("#price_description").val(data.price_description);

                    $("#category").val(data.source_category);

                    metadata.forEach((meta) => {
                        const sourceKey = meta.source_key;
                        const sourceValue = meta.source_Values;

                        switch (sourceKey) {
                            case "basic_service_price":
                                $("#basic_service_price").val(sourceValue);
                                break;
                            case "basic_price_description":
                                $("#basic_price_description").val(sourceValue);
                                break;
                            case "premium_service_price":
                                $("#premium_service_price").val(sourceValue);
                                break;
                            case "premium_price_description":
                                $("#premium_price_description").val(
                                    sourceValue
                                );
                                break;
                            case "pro_service_price":
                                $("#pro_service_price").val(sourceValue);
                                break;
                            case "pro_price_description":
                                $("#pro_price_description").val(sourceValue);
                                break;
                            default:
                        }
                    });

                    fetchSubcategories(
                        data.language_id,
                        data.source_category,
                        data.source_subcategory
                    );

                    fetchCategories(data.language_id, data.source_category);

                    if (data.price_type === "hourly") {
                        $(".hours-section").hide();
                        $(".minutes-section").hide();
                    } else if (data.price_type === "minute") {
                        $(".minutes-section").show();
                        $(".hours-section").hide();
                    } else if (
                        data.price_type === "fixed" ||
                        "squre-metter" ||
                        "squre-Feet"
                    ) {
                        $(".hours-section").show();
                        $(".minutes-section").hide();
                    } else {
                        $(".hours-section").hide();
                        $(".minutes-section").hide();
                    }

                    $("#image_preview_container").empty();

                    metadata.forEach((meta, index) => {
                        if (meta.source_key === "product_image") {
                            const imageUrl = meta.source_Values;
                            const imageId = meta.id;
                            const previewBlock = `
                                <div class="avatar avatar-gallery me-3" data-index="${index}">
                                    <input type="hidden" name="${imageId}" value="${imageId}">
                                    <img src="${imageUrl}" alt="Img" style="width: 100px; height: 100px; object-fit: cover;">
                                    <a href="javascript:void(0);" class="trash-top d-flex align-items-center justify-content-center" data-index="${index}">
                                        <i class="ti ti-trash"></i>
                                    </a>
                                </div>
                            `;
                            $("#image_preview_container").append(previewBlock);
                        }

                        if (meta.source_key === "video_link") {
                            const videoUrl = meta.source_Values;

                            // Populate the existing video input field with the video URL
                            $("#service_video").val(videoUrl);
                        }
                    });

                    const daysOfWeek = [
                        "Monday",
                        "Tuesday",
                        "Wednesday",
                        "Thursday",
                        "Friday",
                        "Saturday",
                        "Sunday",
                    ];
                    daysOfWeek.forEach((day) => {
                        const dayKeyPrefix = day.toLowerCase() + "_slot";
                        const dayMeta = metadata.filter((meta) =>
                            meta.source_key
                                .toLowerCase()
                                .startsWith(dayKeyPrefix)
                        );

                        if (dayMeta.length > 0) {
                            $(`#${day.toLowerCase()}_checkbox`).prop(
                                "checked",
                                true
                            );
                            const slotContainer = $(
                                `#${day.toLowerCase()}Data #slotinputs`
                            );
                            slotContainer.show();
                            slotContainer.empty();

                            dayMeta.forEach((slot, index) => {
                                const [startTime, endTime] =
                                    slot.source_Values.split(" - ");
                                const inputId = slot.id;
                                const slotHTML = `
                                    <div class="d-flex gap-3 mt-2 additional-time">
                                        <input type="hidden" name="${inputId}" value="${inputId}">
                                        <input type="time" class="form-control start_time" name="start_time[${day.toLowerCase()}][${index}]" value="${startTime}" id="start_time_${index}">
                                        <input type="time" class="form-control end_time" name="end_time[${day.toLowerCase()}][${index}]" value="${endTime}" id="end_time_${index}" readonly>
                                        <a class="p-1 rounded-0 remove-time-btn" style="margin-top: 5px;">
                                            <i class="ti ti-trash me-2 fw-bold fs-4"></i>
                                        </a>
                                    </div>`;
                                slotContainer.append(slotHTML);
                            });
                        } else {
                            $(`#${day.toLowerCase()}Data #slotinputs`).hide();
                            $("#closeSlotBtn").trigger("click");
                        }
                    });

                    $("#appendaddservice").empty();

                    response.data.additional_services.forEach(function (
                        service
                    ) {
                        const serviceHtml = `
                            <div class="row mb-3 extra-service">
                                <input type="hidden" name="${service.id}" value="${service.id}">
                                <div class="col-xl-5">
                                    <div class="d-flex align-items-center">
                                        <div class="file-upload service-file-upload d-flex align-items-center justify-content-center flex-column me-4">
                                            <div class="image-preview-wrapper">
                                                <img src="${service.image}" alt="Preview" class="img-preview" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                                            </div>
                                            <input type="file" name="add_image[]" class="add_image" accept="image/*">
                                        </div>
                                        <div class="flex-fill">
                                            <label class="form-label translatable" data-translate="name">Name <span class="text-danger">*</span></label>
                                            <input type="text" name="add_name[]" id="add_name" class="form-control translatable" data-translate="name_placeholder" placeholder="Enter Name" value="${service.name}">
                                            <span class="invalid-feedback" id="add_name_error"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3">
                                    <div class="mb-3">
                                        <label class="form-label translatable" data-translate="price">Price <span class="text-danger">*</span></label>
                                        <input type="number" name="add_price[]" id="add_price" class="form-control translatable" data-translate="pricing_placeholder" placeholder="Enter Service Price" value="${Math.floor(service.price)}">
                                        <span class="invalid-feedback" id="add_price_error"></span>
                                    </div>
                                </div>
                                <div class="col-xl-3">
                                    <div class="mb-3">
                                        <label class="form-label translatable" data-translate="description">Description <span class="text-danger">*</span></label>
                                        <input type="text" name="add_duration[]" id="add_duration" class="form-control translatable" data-translate="description_placeholder" value="${service.duration}" placeholder="Enter Service description">
                                        <span class="invalid-feedback" id="add_duration_error"></span>
                                    </div>
                                </div>
                                <div class="col-xl-1 d-flex align-items-center">
                                    <a href="javascript:void(0);" class="text-danger remove-extra"><i class="ti ti-trash fs-14"></i></a>
                                </div>
                            </div>
                        `;

                        $("#appendaddservice").append(serviceHtml);
                    });
                    setTimeout(function () {
                        $("#serviceLoader").hide();
                    }, 1000);
                } else {
                    $("#serviceLoader").hide();
                    toastr.error("Failed to fetch page details.");
                }
            },

            error: function (error) {
                $("#serviceLoader").hide();
                if (error.status === 422) {
                    const languageIdValue = $("#language_id_input").val();

                    $("#service-form")[0].reset();
                    $("#location-form")[0].reset();
                    $("#image-form")[0].reset();
                    $("#seo-form")[0].reset();

                    $("#language_id_input").val(languageIdValue);

                    $("#appendaddservice").empty();

                    $("#image_preview_container").empty();

                    $('input[data-role="tagsinput"]').tagsinput("removeAll");

                    $(".select").val(null).trigger("change");

                    $("#include").val("");

                    $("#sub_category").val("");

                    $(".form-control")
                        .removeClass("is-invalid")
                        .removeClass("is-valid");
                    $(".invalid-feedback").text("");
                }
            },
        });
    }

    $(document).on("click", "#service_btn", function () {
        const error_status = false;

        const country_id = Data.country;
        const state_id = Data.state;
        const city_id = Data.city;

        getCountries(country_id);
        getStates(country_id, state_id);
        getCities(state_id, city_id);
    });

    $(document).on("click", ".remove-time-btn", function () {
        const slotElement = $(this).closest(".additional-time");
        const slotId = slotElement.find("input[type='hidden']").val(); // Get the slot ID

        $.ajax({
            url: `/api/provider/delete-slot/${slotId}`, // API endpoint for slot deletion
            type: "DELETE",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    slotElement.remove(); // Remove the slot from the UI
                }
            },
        });
    });

    $(document).on("click", ".trash-top", function () {
        const index = $(this).data("index");
        const imageId = $(`.avatar-gallery[data-index="${index}"] input`).val();

        $.ajax({
            url: `/api/provider/delete-service-image/${imageId}`,
            type: "DELETE",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    $(`.avatar-gallery[data-index="${index}"]`).remove();
                    // alert("Image deleted successfully.");
                } else {
                    alert("Failed to delete the image. Please try again.");
                }
            },
            error: function () {
                alert("An error occurred while deleting the image.");
            },
        });
    });

    $(document).on("click", ".remove-extra", function () {
        const serviceElement = $(this).closest(".extra-service");
        const serviceId = serviceElement.find("input[type='hidden']").val(); // Get the service ID

        if (!serviceId) {
            serviceElement.remove();
            return;
        }

        $.ajax({
            url: `/api/provider/delete-additional-services/${serviceId}`, // API endpoint for service deletion
            type: "DELETE",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"), // Use your token setup
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    serviceElement.remove(); // Remove the service from the UI
                }
            },
        });
    });

    //service submit and validation
    $(document).ready(function () {
        $("#service-form").validate({
            rules: {
                service_name: {
                    required: true,
                    minlength: 3,
                    maxlength: 255,
                    remote: {
                        url: "/api/provider/service/edit/check-unique",
                        type: "post",
                        headers: {
                            Authorization:
                                "Bearer " + localStorage.getItem("admin_token"),
                            Accept: "application/json",
                        },
                        data: {
                            edit_service_name: function () {
                                return $("#service_name").val();
                            },
                            id: function () {
                                return $("#id").val();
                            },
                            language_id: function () {
                                return $("#language_id").val();
                            },
                        },
                    },
                },
                product_code: {
                    required: true,
                    minlength: 3,
                    maxlength: 50,
                },
                category: {
                    required: true,
                },
                sub_category: {
                    required: true,
                },
                description: {
                    required: true,
                    minlength: 10,
                    maxlength: 1500,
                },
                include: {
                    required: true,
                    minlength: 3,
                    maxlength: 500,
                },
                price_type: {
                    required: true,
                },
                service_price: {
                    required: true,
                    number: true,
                    min: 1,
                },
                basic_service_price: {
                    required: true,
                    number: true,
                    min: 0,
                },
                basic_price_description: {
                    required: true,
                    minlength: 10,
                    maxlength: 500,
                },
            },
            messages: {
                service_name: {
                    required: "The service name field is required.",
                    minlength:
                        "The service name must be at least 3 characters.",
                    maxlength: "The service name cannot exceed 255 characters.",
                    remote: "Service name already exist.",
                },
                product_code: {
                    required: "The product code field is required.",
                    minlength:
                        "The product code must be at least 3 characters.",
                    maxlength: "The product code cannot exceed 50 characters.",
                },
                category: {
                    required: "The category field is required.",
                },
                sub_category: {
                    required: "The sub-category field is required.",
                },
                description: {
                    required: "The description field is required.",
                    minlength:
                        "The description must be at least 10 characters.",
                    maxlength: "The description cannot exceed 1500 characters.",
                },
                include: {
                    include: "The include field is required.",
                    minlength: "The include must be at least 3 characters.",
                    maxlength: "The description cannot exceed 500 characters.",
                },
                price_type: {
                    required: "The price type field is required.",
                },
                service_price: {
                    required: "The price field is required.",
                    number: "The price must be a valid number.",
                    min: "The price cannot be negative.",
                },
                basic_service_price: {
                    required: "The price field is required.",
                    number: "The price must be a valid number.",
                    min: "The price cannot be negative.",
                },
                basic_price_description: {
                    required: "The description field is required.",
                    minlength:
                        "The description must be at least 20 characters.",
                    maxlength: "The description cannot exceed 500 characters.",
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
        });

        $("#service_btn").on("click", function (event) {
            event.preventDefault();

            let serviceFormData = $("#service-form").serializeArray();

            if ($("#service-form").valid()) {
                let formDataCollection = {};
                serviceFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                if (is_empty_branch == 1) {
                    $("#first-field").hide();
                    $("#second-field").hide();
                    $("#third-field").show();
                    $("#progressbar li").eq(0).removeClass("active");
                    $("#progressbar li").eq(1).removeClass("active");
                    $("#progressbar li").eq(2).addClass("active");
                } else {
                    $("#first-field").hide();
                    $("#second-field").show();
                    $("#progressbar li").eq(0).removeClass("active");
                    $("#progressbar li").eq(1).addClass("active");
                }
            }
        });

        $("#location_prv").on("click", function () {
            $("#second-field").hide();
            $("#first-field").show();
            $("#progressbar li").eq(1).removeClass("active");
            $("#progressbar li").eq(0).addClass("active");
        });

        $("#location-form").validate({
            rules: {
                address: {
                    required: true,
                    minlength: 5,
                    maxlength: 255,
                },
                pincode: {
                    required: false,
                    maxlength: 6,
                },
                state: {
                    required: true,
                },
                city: {
                    required: true,
                },
                country: {
                    required: true,
                },
            },
            messages: {
                address: {
                    required: "The address field is required.",
                    minlength: "The address must be at least 5 characters.",
                    maxlength: "The address cannot exceed 255 characters.",
                },
                pincode: {
                    required: "The pincode field is optional.",
                    maxlength: "The pincode cannot exceed 6 digits.",
                },
                state: {
                    required: "The state field is required.",
                },
                city: {
                    required: "The city field is required.",
                },
                country: {
                    required: "The country field is required.",
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
        });

        $("#location_btn").on("click", function (event) {
            event.preventDefault();

            let locationFormData = $("#location-form").serializeArray();

            if ($("#location-form").valid()) {
                let formDataCollection = {};
                locationFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                $("#second-field").hide();
                $("#third-field").show();
                $("#progressbar li").eq(1).removeClass("active");
                $("#progressbar li").eq(2).addClass("active");
            }
        });

        $("#staff_prv").on("click", function () {
            $("#second-field").hide();
            $("#first-field").show();
            $("#progressbar li").eq(1).removeClass("active");
            $("#progressbar li").eq(0).addClass("active");
        });

        $("#staff_btn").on("click", function (event) {
            event.preventDefault();

            let staffFormData = $("#staff-form").serializeArray();

            if ($("#staff-form").valid()) {
                let formDataCollection = {};
                staffFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                $("#second-field").hide();
                $("#third-field").show();
                $("#progressbar li").eq(1).removeClass("active");
                $("#progressbar li").eq(2).addClass("active");
            }
        });

        $("#image_prv").on("click", function () {
            if (is_empty_branch == 1) {
                $("#second-field").hide();
                $("#third-field").hide();
                $("#first-field").show();
                $("#progressbar li").eq(2).removeClass("active");
                $("#progressbar li").eq(1).removeClass("active");
                $("#progressbar li").eq(0).addClass("active");
            } else {
                $("#third-field").hide();
                $("#second-field").show();
                $("#progressbar li").eq(2).removeClass("active");
                $("#progressbar li").eq(1).addClass("active");
            }
        });

        $("#image-form").validate({
            rules: {
                "service_images[]": {
                    required: false,
                    extension: "jpg|jpeg|png|svg|webp",
                },
                service_video: {
                    required: false, // Not required
                    url: true, // Must be a valid URL
                },
            },
            messages: {
                "service_images[]": {
                    required: "The service image is required.",
                    extension:
                        "Only JPG, JPEG, PNG, WEBP and SVG files are allowed.",
                },
                service_video: {
                    required: "The video URL is required.", // Customize if necessary
                    url: "Please enter a valid URL for the video.", // Message for invalid URL format
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
        });

        $("#image_btn").on("click", function (event) {
            event.preventDefault();

            if ($("#image-form").valid()) {
                $("#third-field").hide();
                $("#forth-field").show();

                $("#progressbar li").eq(2).removeClass("active");
                $("#progressbar li").eq(3).addClass("active");
            } else {
            }
        });

        $("#seo_prv").on("click", function () {
            $("#forth-field").hide();
            $("#third-field").show();
            $("#progressbar li").eq(3).removeClass("active");
            $("#progressbar li").eq(2).addClass("active");
        });

        $("#seo-form").validate({
            rules: {
                seo_title: {
                    required: true,
                    maxlength: 255,
                },
            },
            messages: {
                seo_title: {
                    required: "The SEO title field is required.",
                    maxlength: "The SEO title cannot exceed 255 characters.",
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
        });

        $("#seo_btn").on("click", function (event) {
            $("#serviceLoader").show();

            event.preventDefault();

            let serviceFormData = $("#service-form").serializeArray();
            let staffFormData = $("#staff-form").serializeArray();
            let locationFormData = $("#location-form").serializeArray();
            let imageFormData = $("#location-form").serializeArray();
            let seoFormData = $("#seo-form").serializeArray();

            const all = 1;

            if (all == 1) {
                let finalFormData = new FormData();

                finalFormData.append(
                    "_token",
                    $('meta[name="csrf-token"]').attr("content")
                );

                [
                    ...serviceFormData,
                    ...locationFormData,
                    ...staffFormData,
                    ...imageFormData,
                    ...seoFormData,
                ].forEach(function (item) {
                    finalFormData.append(item.name, item.value);
                });

                let imageFiles = $("#service_images")[0].files;
                if (imageFiles.length > 0) {
                    for (let i = 0; i < imageFiles.length; i++) {
                        finalFormData.append("service_images[]", imageFiles[i]); // Append each file
                    }
                }

                $("input[name='add_image[]']").each(function (index) {
                    if ($(this)[0].files.length > 0) {
                        finalFormData.append(
                            `add_image[${index}]`,
                            $(this)[0].files[0]
                        );
                    }
                });

                let serviceVideo = $("#service_video").val();
                if (serviceVideo) {
                    finalFormData.append("service_video", serviceVideo);
                }

                let serviceId = $("#service_id").val();
                if (serviceId) {
                    finalFormData.append("serviceId", serviceId);
                }

                let payload = [];
                $('input[name="branch_select[]"]:checked').each(function () {
                    const branchId = $(this).val();

                    const staffIds = $(
                        `input[name="staff_select[]"][data-branch-id="${branchId}"]:checked`
                    )
                        .map(function () {
                            return $(this).val();
                        })
                        .get();

                    payload.push({
                        branch_id: branchId,
                        staff_ids: staffIds,
                    });
                });

                finalFormData.append(
                    "branch_staff_payload",
                    JSON.stringify(payload)
                );

                $.ajax({
                    url: "/provider/service/update",
                    method: "POST",
                    data: finalFormData,
                    dataType: "json",
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        $(".add_btn").attr("disabled", true);
                        $(".add_btn").html(
                            '<span class="spinner-border text-light spinner-border-sm align-middle" role="status"></span>'
                        );
                    },
                })
                    .done((response, statusText, xhr) => {
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $(".add_btn").removeAttr("disabled");
                        $(".add_btn").html("Submit");

                        if (response.code === 200) {
                            toastr.success(response.message);
                            setTimeout(function () {
                                window.location.href = response.redirect_url;
                            }, 10);
                        }
                    })
                    .fail((error) => {
                        $("#serviceLoader").hide();
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $(".add_btn").removeAttr("disabled");
                        $(".add_btn").html("submit");

                        if (error.status == 422) {
                            $.each(error.responseJSON, function (key, val) {
                                $("#" + key).addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                            });
                        } else {
                            toastr.error(error.responseJSON.message);
                        }
                    });
            }
        });
    });

    $("#addSlotBtn").on("click", function () {
        $("#slotData").slideDown();
    });

    $("#closeSlotBtn").on("click", function () {
        $("#slotData").slideUp();
    });

    const $priceType = $("#price_type");
    const $hoursSection = $(".hours-section");
    const $minutesSection = $(".minutes-section");
    const $hoursSelect = $("#hours_select");
    const $minutesSelect = $("#minutes_select");

    $priceType.change(function () {
        const selectedValue = $(this).val();

        $hoursSection.hide();
        $minutesSection.hide();
        $hoursSelect.val("");
        $minutesSelect.val("");

        if (selectedValue === "hourly") {
            $hoursSelect.val(1);
            $hoursSection.hide();
            $minutesSection.hide();
        } else if (selectedValue === "minute") {
            $minutesSection.show();
        } else if (
            selectedValue === "fixed" ||
            "squre-metter" ||
            "squre-Feet"
        ) {
            $hoursSelect.val(1);
            $hoursSection.show();
            $minutesSection.hide();
        }
    });

    $(document).on("change", "input[name='day_checkbox[]']", function () {
        const checkbox = $(this);
        const container = checkbox.closest(".mb-4");
        const day = container.attr("id").replace("Data", "");

        if (checkbox.is(":checked")) {
            const initialTimeInput = `
                <div class="d-flex gap-3 mt-2 additional-time">
                    <input type="time" class="form-control start_time" name="start_time[${day}][]" id="start_time">
                    <input type="time" class="form-control end_time" name="end_time[${day}][]" id="end_time" readonly>
                    <a class="p-1 rounded-0 remove-time-btn" style="margin-top: 5px;">
                        <i class="ti ti-trash me-2 fw-bold fs-4"></i>
                    </a>
                </div>
            `;
            container.find("#slotinputs").show().append(initialTimeInput);
        }
    });

    $(document).on("click", ".add-time-btn", function () {
        const container = $(this).closest(".mb-4"); // Parent container for the day
        const day = container.attr("id").replace("Data", ""); // Extract the day
        const isChecked = container
            .find('input[type="checkbox"]')
            .is(":checked");

        if (!isChecked) {
            return;
        }

        const newTimeInput = `
            <div class="d-flex gap-3 mt-2 additional-time">
                <input type="time" class="form-control start_time" name="start_time[${day}][]" id="start_time">
                <input type="time" class="form-control end_time" name="end_time[${day}][]" id="end_time" readonly>
                <a class="p-1 rounded-0 remove-time-btn" style="margin-top: 5px;">
                    <i class="ti ti-trash me-2 fw-bold fs-4"></i>
                </a>
            </div>
        `;
        container.find("#slotinputs").append(newTimeInput);
    });

    $(document).on("click", ".remove-time-btn", function () {
        $(this).closest(".additional-time").remove();
    });

    $(document).on("change", ".start_time", function () {
        const startTimeInput = $(this);
        const selectedHours = $("#hours_select").val(); // Assuming you have these selectors
        const selectedMinutes = $("#minutes_select").val(); // Assuming you have these selectors
        const startTime = startTimeInput.val();
        const timeSlotDiv = startTimeInput.closest(".d-flex");
        const endTimeInput = timeSlotDiv.find(".end_time");

        // --- Step 1: Calculate End Time (Your existing logic) ---
        if ((selectedHours || selectedMinutes) && startTime) {
            const [startHour, startMinute] = startTime.split(":").map(Number);
            let endHour = startHour;
            let endMinute = startMinute;

            if (selectedHours) {
                endHour += parseInt(selectedHours);
            }
            if (selectedMinutes) {
                endMinute += parseInt(selectedMinutes);
            }

            endHour += Math.floor(endMinute / 60);
            endMinute = endMinute % 60;
            endHour = endHour % 24;

            const formattedEndTime = `${endHour
                .toString()
                .padStart(2, "0")}:${endMinute.toString().padStart(2, "0")}`;
            endTimeInput.val(formattedEndTime);
        } else {
            endTimeInput.val("");
            return; // No start time or duration, so no validation needed
        }

        // --- Step 2: Validate for Overlapping Times (New Logic) ---
        const currentStartTime = startTimeInput.val();
        const currentEndTime = endTimeInput.val();
        const dayContainer = startTimeInput.closest(".mb-4");
        let isOverlapping = false;

        if (!currentStartTime || !currentEndTime) return;

        // Iterate over all time slots within the same day
        dayContainer.find(".additional-time").each(function () {
            const otherSlot = $(this);

            // Don't compare the input with itself
            if (otherSlot.is(timeSlotDiv)) {
                return; // 'continue' in jQuery's .each()
            }

            const existingStartTime = otherSlot.find(".start_time").val();
            const existingEndTime = otherSlot.find(".end_time").val();

            if (existingStartTime && existingEndTime) {
                // Check for overlap: (StartA < EndB) && (StartB < EndA)
                if (
                    currentStartTime < existingEndTime &&
                    existingStartTime < currentEndTime
                ) {
                    isOverlapping = true;
                    return false; // 'break' in jQuery's .each()
                }
            }
        });

        // --- Step 3: Handle the Overlap ---
        if (isOverlapping) {
            const dayName = dayContainer.attr("id").replace("Data", "");
            toastr.error(
                "The selected time has already been occupied. Please select a different time."
            );
            startTimeInput.val(""); // Clear the invalid start time
            endTimeInput.val(""); // Clear the calculated end time
            startTimeInput.focus(); // Set focus back to the input for correction
        }
    });

    $('input[name="day_checkbox[]"]').on("change", function () {
        const parentDiv = $(this).closest(".mb-4");
        const slotInputs = parentDiv.find("#slotinputs");

        if ($(this).is(":checked")) {
            slotInputs.show();
        } else {
            slotInputs.hide();
            slotInputs.find(".start_time, .end_time").val("");
        }
    });

    $("#service_images").on("change", function (event) {
        const files = event.target.files;
        const previewContainer = $("#image_preview_container");

        // previewContainer.empty();

        Array.from(files).forEach((file, index) => {
            if (file.type.startsWith("image/")) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    const imageHTML = `
                        <div class="avatar avatar-gallery me-3" data-index="${index}">
                            <img src="${e.target.result}" alt="Img" style="width: 100px; height: 100px; object-fit: cover;">
                            <a href="javascript:void(0);" class="trash-top d-flex align-items-center justify-content-center" data-index="${index}">
                                <i class="ti ti-trash"></i>
                            </a>
                        </div>`;
                    previewContainer.append(imageHTML);
                };

                reader.readAsDataURL(file);
            }
        });
    });

    let cachedCountries = [];
    let cachedStates = [];
    let cachedCities = [];

    $(document).ready(function () {
        $(".selects").select2();
        $.when(
            $.getJSON("/countries.json"),
            $.getJSON("/states.json"),
            $.getJSON("/cities.json")
        )
            .done(function (countriesData, statesData, citiesData) {
                cachedCountries = countriesData[0].countries;
                cachedStates = statesData[0].states;
                cachedCities = citiesData[0].cities;

                getCountries();
            })
            .fail(function () {});

        // Event listeners
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

    function getCountries(selectedCountry = null) {
        const countrySelect = $("#country");
        clearDropdown(countrySelect);

        $.each(cachedCountries, function (index, country) {
            countrySelect.append(
                $("<option>", {
                    value: country.id,
                    text: country.name,
                    selected: country.id == selectedCountry,
                })
            );
        });

        if (selectedCountry) {
            getStates(selectedCountry);
        }
    }

    function getStates(selectedCountry, selectedState = null) {
        const stateSelect = $("#state");
        clearDropdown(stateSelect);

        const states = cachedStates.filter(
            (state) => state.country_id == selectedCountry
        );

        $.each(states, function (index, state) {
            stateSelect.append(
                $("<option>", {
                    value: state.id,
                    text: state.name,
                    selected: state.id == selectedState, // Preselect the option
                })
            );
        });

        if (selectedState) {
            getCities(selectedState); // Trigger fetching cities if state is preselected
        }
    }

    function getCities(selectedState, selectedCity = null) {
        const citySelect = $("#city");
        clearDropdown(citySelect);
        citySelect.val(null);

        const cities = cachedCities.filter(
            (city) => city.state_id == selectedState
        );

        const selectedCityArray = Array.isArray(selectedCity)
            ? selectedCity
            : selectedCity
            ? selectedCity.split(",")
            : [];

        $.each(cities, function (index, city) {
            citySelect.append(
                $("<option>", {
                    value: city.id,
                    text: city.name,
                    selected: selectedCityArray.includes(String(city.id)),
                })
            );
        });

        citySelect.find('option[value=""]').remove();
    }

    $(document).on("input", "#add_price", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/g, '')
                .slice(0, 10)
        );
    });

    $(".add-extra").on("click", function () {
        const newServiceRow = `
            <div class="row mb-3 extra-service">
                <div class="col-xl-5">
                    <div class="d-flex align-items-center">
                        <div class="file-upload service-file-upload d-flex align-items-center justify-content-center flex-column me-4">
                            <div class="image-preview-wrapper">
                                <i class="ti ti-photo"></i>
                                <img src="" alt="Preview" class="img-preview d-none" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                            </div>
                            <input type="file" name="add_image[]" class="add_image" accept="image/*">
                        </div>
                        <div class="flex-fill">
                            <label class="form-label">${$(
                                "#appendaddservice"
                            ).data(
                                "name"
                            )} <span class="text-danger">*</span></label>
                            <input type="text" name="add_name[]" id="add_name" class="form-control" placeholder="${$(
                                "#appendaddservice"
                            ).data("service_name_placeholder")}">
                            <span class="invalid-feedback" id="add_name_error"></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3">
                    <div class="mb-3">
                        <label class="form-label">${$("#appendaddservice").data(
                            "price"
                        )} <span class="text-danger">*</span></label>
                        <input type="number" name="add_price[]" id="add_price" class="form-control" placeholder="${$(
                            "#appendaddservice"
                        ).data("pricing_placeholder")}">
                        <span class="invalid-feedback" id="add_price_error"></span>
                    </div>
                </div>
                <div class="col-xl-3">
                    <div class="mb-3">
                        <label class="form-label">${$("#appendaddservice").data(
                            "description"
                        )} <span class="text-danger">*</span></label>
                        <input type="text" name="add_duration[]" id="add_duration" class="form-control" placeholder="${$(
                            "#appendaddservice"
                        ).data("enter_description")}">
                        <span class="invalid-feedback" id="add_duration_error"></span>
                    </div>
                </div>
                <div class="col-xl-1 d-flex align-items-center">
                    <a href="javascript:void(0);" class="text-danger remove-extra"><i class="ti ti-trash fs-14"></i></a>
                </div>
            </div>
        `;
        $("#appendaddservice").append(newServiceRow);
    });

    // Handle image preview and icon replacement
    $(document).on("change", ".add_image", function () {
        const fileInput = this;
        const preview = $(fileInput)
            .siblings(".image-preview-wrapper")
            .find(".img-preview");
        const icon = $(fileInput).siblings(".image-preview-wrapper").find("i");

        if (fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                preview.attr("src", e.target.result).removeClass("d-none");
                icon.addClass("d-none");
            };

            reader.readAsDataURL(fileInput.files[0]);
        } else {
            // Reset to icon if no file is selected
            preview.addClass("d-none").attr("src", "");
            icon.removeClass("d-none");
        }
    });

    // Remove extra service row
    $(document).on("click", ".remove-extra", function () {
        $(this).closest(".extra-service").remove();
    });

    $(document).ready(function () {
        $("#basic_btn").removeClass("btn-primary").addClass("btn-dark"); // Highlight Basic button
        $("#basic_container").show(); // Show Basic container

        $(".price-btn").on("click", function (e) {
            e.preventDefault();

            $(".invalid-feedback").text(""); // Clear validation error messages

            $(".price-btn").removeClass("btn-dark").addClass("btn-primary");
            $(this).removeClass("btn-primary").addClass("btn-dark");

            const selectedId = $(this).attr("id");
            $("#basic_container, #premium_container, #pro_container").hide();

            if (selectedId === "basic_btn") {
                $("#basic_container").slideDown();
            } else if (selectedId === "premium_btn") {
                $("#premium_container").slideDown();
            } else if (selectedId === "pro_btn") {
                $("#pro_container").slideDown();
            }
        });
    });
}

if (pageValue === "provider.subscription") {
    function subscriptiondetail(auth_provider_id, type) {
        $.ajax({
            url: "/api/subscription-package/subscription-detail",
            type: "POST",
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: { authid: auth_provider_id, subscriptiontype: type },
            success: function (response) {
                if (response.code == "200") {
                    if (response.data.length > 0) {
                        renderSubscriptionCards(response.data);
                    } else {
                        if (type == "topup") {
                            $("#subscriptionCards").removeClass("d-none").html(`
                                <div class="text-center pt-5">
                                ${$("#subscriptionCards").data("empty_topup")}
                                </div>
                            `);
                        } else {
                            $("#subscriptionCards").removeClass("d-none").html(`
                                <div class="text-center pt-5">
                                    ${$("#subscriptionCards").data(
                                        "empty_subscription"
                                    )}
                                </div>
                            `);
                        }
                    }
                }
            },
            error: function (xhr, status, error) {
                toastr.error("An error occurred while trying to get data.");
            },
        });
    }
    function renderSubscriptionCards(data) {
        const container = document.getElementById("subscriptionCards");
        container.classList.add("subscription-cards");
        container.innerHTML = ""; // Clear existing content
        data.forEach((plan) => {
            // Create card element
            const card = document.createElement("div");
            card.classList.add("card");
            card.classList.add("subscription-cards");
            card.id = "subscriptioncard";
            // Add card content
            var innerHTML = "";
            if (
                plan.subscribedstatus == 1 &&
                plan.subscription_type == "topup"
            ) {
                innerHTML += ` <div class="position-absolute top-0 end-0 p-2 text-end"><span class="badge badge-success">
                                        <i class="ti ti-point-filled"></i>Top-Up
                                    </span></div>`;
            }
            innerHTML += `<h2>${plan.package_title}</h2>
            <p class="price">${plan.currency}${plan.price}</p>
            <ul class="text-start">`;
            if (plan.package_duration) {
                innerHTML += `<li class="justify-content-start"><i class="fa fa-check"></i>${
                    plan.package_duration ?? "0"
                } ${plan.package_term ?? "0"} / Duration</li>`;
            }
            if (plan.number_of_service) {
                innerHTML += `<li class="justify-content-start"><i class="fa fa-check"></i>${
                    plan.number_of_service ?? "0"
                } / No of Service</li>`;
            }
            if (plan.number_of_locations) {
                innerHTML += `<li class="justify-content-start"><i class="fa fa-check"></i>${
                    plan.number_of_locations ?? "0"
                } / No of Branch Location</li>`;
            }
            if (plan.number_of_staff) {
                innerHTML += `<li class="justify-content-start"><i class="fa fa-check"></i>${
                    plan.number_of_staff ?? "0"
                } / No of Staff</li>`;
            }
            if (plan.description) {
                innerHTML += `<li class="justify-content-start"><i class="fa fa-check"></i>${plan.description}</li>`;
            }

            innerHTML += `</ul><div class="subscribebutton" id ="subscribebutton${plan.id}">`;
            if (plan.subscription_type == "regular") {
                if (plan.subscribedstatus == 0) {
                    innerHTML += `<button type="button" class="btn btn-primary subscribe w-100"  data-planid=${plan.id} data-plan=${plan.package_title} data-amt=${plan.price} data-type=${plan.subscription_type}> Subscribe </button>`;
                } else if (plan.subscribedstatus == 1) {
                    innerHTML += `<button type="button" class="btn btn-primary subscribed w-100" > Subscribed </button>`;
                } else {
                    innerHTML += `<button type="button" class="btn btn-warning w-100" > Pending Verification </button>`;
                }

                $("#subscription-warning").removeClass('d-none');
            } else {
                if (plan.topup_subscribedstatus == 0) {
                    innerHTML += `<button type="button" class="btn btn-primary subscribe w-100"  data-planid=${plan.id} data-plan=${plan.package_title} data-amt=${plan.price} data-type=${plan.subscription_type}> Top-up </button>`;
                } else if (plan.topup_subscribedstatus == 1) {
                    innerHTML += `<button type="button" class="btn btn-primary subscribed w-100">Topped Up</button>`;
                } else {
                    innerHTML += `<button type="button" class="btn btn-warning w-100" > Pending Verification </button>`;
                }

                $("#subscription-warning").addClass('d-none');
            }
            innerHTML += `</div>`;
            // Append card to container
            card.innerHTML = innerHTML;
            container.appendChild(card);
            $(".subscription-loader").hide();
            $(".label-loader, .input-loader").hide();
            $(".real-label, .real-input").removeClass("d-none");
        });
    }
    $(document).ready(function () {
        $(document).on("click", ".subscribe", function () {
            var planid = $(this).data("planid");
            var name = $(this).data("plan");
            var amt = $(this).data("amt");
            var subscribetype = $(this).data("type");
            $(".package_id").val(planid);
            $(".package_name").val(name);
            $(".package_amount").val(amt);
            if (amt != 0) {
                /*storepackagetransaction*/
                $.ajax({
                    url: "/api/storepackagetransaction",
                    type: "POST",
                    data: {
                        provider_id: $("body").data("authid"),
                        amount: amt,
                        package_id: planid,
                        subscribetype: subscribetype,
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
                        if (response.code == "200") {
                            trxid = response.data;
                            $(".trx_id").val(trxid);
                        }
                    },
                });

                $("#payment_proof").val("");
                $("#paymentModal").modal('show');
                
            } else {
                $.ajax({
                    url: "/api/storepackagetransaction",
                    type: "POST",
                    data: {
                        provider_id: $("body").data("authid"),
                        amount: amt,
                        package_id: planid,
                        type: "free",
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
                        showLoader();
                        var msg = "Package Subscribed Successfully";
                        if (response != "") {
                            if (languageId === 2) {
                                loadJsonFile(msg, function (langtst) {
                                    msg = langtst;
                                    toastr.success(msg);
                                });
                            } else {
                                toastr.success(msg);
                            }
                            loadTabData("regular");
                        }
                    },
                });
            }
        });

        $(document).on("click", ".paymentmethod", function () {
            let paymentmethod = $(this).val();
            if (paymentmethod == "banktransfer") {
                $(".bank-details").removeClass('d-none');
            } else {
                $(".bank-details").addClass('d-none');
            }
        });

        $(document).on("submit", "#payment", function (event) {
            const selectedPaymentMethod = $(
                'input[name="paymentMethod"]:checked'
            ).val();
            var name = $(".package_name").val();
            var serviceamount = parseInt($(".package_amount").val());
            var trxid = $(".trx_id").val();
            var package_id = $(".package_id").val();

            if (selectedPaymentMethod === "paypal") {
                event.preventDefault();
                $.ajax({
                    url: "/paypal/subscription/checkout",
                    type: "POST",
                    data: {
                        name: name,
                        service_amount: serviceamount,
                        trx_id: trxid,
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
                        toastr.error(
                            xhr.responseJSON.message ||
                                "Paypal is currently unavailable. Please choose another payment method."
                        );
                    },
                });
            } else if (selectedPaymentMethod === "stripe") {
                //create ajax request
                event.preventDefault();
                $.ajax({
                    url: "/provider/stripe/checkout",
                    type: "POST",
                    data: {
                        trx_id: trxid,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        window.location.href = response.url;
                    },
                    error: function (xhr) {
                        toastr.error(
                            xhr.responseJSON.message ||
                            "Stripe is currently unavailable. Please choose another payment method."
                        );
                    }

                });
            } else if (selectedPaymentMethod === "mollie") {
                event.preventDefault();
                $.ajax({
                    url: "/molliepayment",
                    type: "POST",
                    data: {
                        name: name,
                        service_amount: serviceamount,
                        trx_id: trxid,
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
                        toastr.error(
                            xhr.responseJSON.message ||
                                "Mollie is currently unavailable. Please choose another payment method."
                        );
                    },
                });
            } else if (selectedPaymentMethod === "razerpay") {
                event.preventDefault();
                $.ajax({
                    url: "/razorpay-sub-pay",
                    type: "POST",
                    data: {
                        name: name,
                        service_amount: serviceamount,
                        trx_id: trxid,
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
                        if (response.razorpay) {
                            $("#paymentModal").modal("hide");
                            const razor = response.razorpay;

                            const rzp = new Razorpay({
                                key: razor.key,
                                amount: razor.amount,
                                currency: razor.currency,
                                name: "Your Company",
                                description: "Booking Payment",
                                order_id: razor.order_id,
                                handler: function (razorpayResponse) {
                                    // Redirect directly with required params
                                    const form = document.createElement("form");
                                    form.method = "POST";
                                    form.action = "/razorpay-sub-success";

                                    const csrf = document.querySelector(
                                        'meta[name="csrf-token"]'
                                    ).content;

                                    // Add CSRF token
                                    const csrfInput =
                                        document.createElement("input");
                                    csrfInput.type = "hidden";
                                    csrfInput.name = "_token";
                                    csrfInput.value = csrf;
                                    form.appendChild(csrfInput);

                                    // Append Razorpay response values
                                    for (const key in razorpayResponse) {
                                        if (
                                            razorpayResponse.hasOwnProperty(key)
                                        ) {
                                            const input =
                                                document.createElement("input");
                                            input.type = "hidden";
                                            input.name = key;
                                            input.value = razorpayResponse[key];
                                            form.appendChild(input);
                                        }
                                    }

                                    // Append booking_id
                                    const bookingInput =
                                        document.createElement("input");
                                    bookingInput.type = "hidden";
                                    bookingInput.name = "booking_id";
                                    bookingInput.value = razor.booking_id;
                                    form.appendChild(bookingInput);

                                    document.body.appendChild(form);
                                    form.submit();
                                },
                            });

                            rzp.open();
                        }
                    },
                    error: function (xhr) {
                        toastr.error(
                            xhr.responseJSON.message ||
                                "Razerpay is currently unavailable. Please choose another payment method."
                        );
                    },
                });
            } else if (selectedPaymentMethod === "cashfree") {
                event.preventDefault();
                $.ajax({
                    url: "/cashfree-sub-pay",
                    type: "POST",
                    data: {
                        name: name,
                        service_amount: serviceamount,
                        trx_id: trxid,
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
                        if (
                            response &&
                            response.cashfree &&
                            response.cashfree.redirect_url
                        ) {
                            window.location.href =
                                response.cashfree.redirect_url;
                        }
                    },
                    error: function (xhr) {
                        toastr.error(
                            xhr.responseJSON.message ||
                                "Cashfree is currently unavailable. Please choose another payment method."
                        );
                    },
                });
            } else if (selectedPaymentMethod === "payu") {
                event.preventDefault();
                $.ajax({
                    url: "/payu-sub-pay",
                    type: "POST",
                    data: {
                        name: name,
                        service_amount: serviceamount,
                        trx_id: trxid,
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
                        //PayU
                        if (
                            response.payment == "payu" &&
                            response.status === "success"
                        ) {
                            // 1. Create a new form element dynamically
                            var form = document.createElement("form");
                            form.method = "POST";
                            form.action = response.action; // The PayU URL from our JSON response
                            form.style.display = "none"; // Hide the form from the user

                            // 2. Loop through the fields from our JSON response and create hidden inputs
                            for (var key in response.fields) {
                                if (response.fields.hasOwnProperty(key)) {
                                    var input = document.createElement("input");
                                    input.type = "hidden";
                                    input.name = key;
                                    input.value = response.fields[key];
                                    form.appendChild(input);
                                }
                            }

                            // 3. Append the form to the document's body
                            document.body.appendChild(form);

                            // 4. Submit the form to trigger the redirect to PayU
                            form.submit();

                            // 5. (Optional) Remove the form after submission
                            document.body.removeChild(form);
                        } else {
                        }
                    },
                    error: function (xhr) {
                        toastr.error(
                            xhr.responseJSON.message ||
                                "Payu is currently unavailable. Please choose another payment method."
                        );
                    },
                });
            } else if (selectedPaymentMethod === "paystack") {
                event.preventDefault();
                $.ajax({
                    url: "/paystack-sub-pay",
                    type: "POST",
                    data: {
                        name: name,
                        service_amount: serviceamount,
                        trx_id: trxid,
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
                        //paystack
                        if (
                            response &&
                            response.paystack &&
                            response.paystack.redirect_url
                        ) {
                            window.location.href =
                                response.paystack.redirect_url;
                        }
                    },
                    error: function (xhr) {
                        toastr.error(
                            xhr.responseJSON.message ||
                                "paystack is currently unavailable. Please choose another payment method."
                        );
                    },
                });
            } else if (selectedPaymentMethod === "authorizenet") {
                $("#paymentModalNet").modal("show");
                $("#paymentModal").modal("hide");
                return;
            } else if (selectedPaymentMethod == "banktransfer") {
                event.preventDefault();

                let formData = new FormData();
                formData.append('trx_id', trxid);

                let fileInput = $('#payment_proof')[0].files[0];
                if (fileInput) {
                    formData.append('payment_proof', fileInput);
                } else {
                    toastr.error($(".bank-details").data('payment_proof_required'));
                    return;
                }

                $.ajax({
                    url: "/payment/update-banktransfer",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function (response) {
                        $("#paymentModal").modal('hide');
                        toastr.success(response.message);
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function (error) {
                        if (error.status == 422) {
                            $.each(error.responseJSON.errors, function (key, val) {
                                toastr.error(val[0]);
                            });
                        } else {
                            toastr.error(error.responseJSON.message);
                        }
                    }
                });
            } else if (selectedPaymentMethod === "mercadopago") {
                event.preventDefault();
                $.ajax({
                    url: "/mercadopago-sub-pay",
                    type: "POST",
                    data: {
                        name: name,
                        service_amount: serviceamount,
                        trx_id: trxid,
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
                        //paystack
                        if (
                            response &&
                            response.mercadopago &&
                            response.mercadopago.redirect_url
                        ) {
                            window.location.href =
                                response.mercadopago.redirect_url;
                        }
                    },
                    error: function (xhr) {
                        toastr.error(
                            xhr.responseJSON.message ||
                                "Mercadopago is currently unavailable. Please choose another payment method."
                        );
                    },
                });
            }

            $("#submit-authnet-payment").on("click", function (e) {
                e.preventDefault(); // prevent form submission

                const cardHolderName = $("#cardHolderName").val().trim();
                const cardNumber = $("#cardNumber").val().replace(/\s+/g, "");
                const cardCvv = $("#cardCvv").val().trim();
                const cardExpiryMonth = $("#cardExpiryMonth").val();
                const cardExpiryYear = $("#cardExpiryYear").val();

                // Validate required fields
                if (
                    !cardHolderName ||
                    !cardNumber ||
                    !cardCvv ||
                    !cardExpiryMonth ||
                    !cardExpiryYear
                ) {
                    toastr.error(
                        "⚠️ Please fill all required fields before submitting."
                    );
                    return;
                }

                // Optional: Card format checks (if needed)
                if (
                    !/^\d{13,19}$/.test(cardNumber) ||
                    !/^\d{3,4}$/.test(cardCvv)
                ) {
                    toastr.error("⚠️ Please enter valid card details.");
                    return;
                }

                const all = 1;

                if (all === 1) {
                    let finalFormData = new FormData();

                    finalFormData.append(
                        "_token",
                        $('meta[name="csrf-token"]').attr("content")
                    );

                    finalFormData.append(
                        "card_holder_name",
                        $("#cardHolderName").val().trim()
                    );
                    finalFormData.append(
                        "card_number",
                        $("#cardNumber").val().replace(/\s+/g, "")
                    );
                    finalFormData.append(
                        "card_cvv",
                        $("#cardCvv").val().trim()
                    );
                    finalFormData.append(
                        "card_expiry_month",
                        $("#cardExpiryMonth").val()
                    );
                    finalFormData.append(
                        "card_expiry_year",
                        $("#cardExpiryYear").val()
                    );
                    finalFormData.append("name", $(".package_name").val());
                    finalFormData.append(
                        "serviceamount",
                        $(".package_amount").val()
                    );
                    finalFormData.append("trxid", $(".trx_id").val());

                    $.ajax({
                        url: "/authorize-sub-pay",
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
                            $(".pay-btn").attr("disabled", true);
                            $(".pay-btn").html(
                                '<div class="spinner-border text-light" role="status"></div>'
                            );
                        },
                    })
                        .done((response, statusText, xhr) => {
                            $(".error-text").text("");
                            $(".form-control").removeClass("is-invalid");
                            $(".pay-btn").removeAttr("disabled");
                            $(".pay-btn").html("Pay");
                            $("#serviceLoader").show();
                            $("#bookcomplete").text("100");

                            if (response.url) {
                                window.location.href = response.url;
                            }
                            $("#paymentModalNet").modal("hide");
                        })
                        .fail((error) => {
                            $(".error-text").text("");
                            $(".form-control").removeClass("is-invalid");
                            $(".pay-btn").removeAttr("disabled");
                            $(".pay-btn").html("Pay");
                            if (error.status === 422) {
                                if (error.responseJSON.errors) {
                                    $.each(
                                        error.responseJSON.errors,
                                        function (key, val) {
                                            $("#" + key).addClass("is-invalid");
                                            $("#" + key + "_error").text(
                                                val[0]
                                            );
                                        }
                                    );
                                } else if (error.responseJSON.message) {
                                    toastr.error(error.responseJSON.message);
                                }
                            } else {
                                toastr.error(error.responseJSON.message);
                            }
                        });
                }
            });
        });
    });
    // Function to load tab data
    function loadTabData(type) {
        const auth_provider_id = localStorage.getItem("provider_id");
        if (auth_provider_id) {
            subscriptiondetail(auth_provider_id, type);
        } else {
            toastr.error("Provider ID is not available.");
        }
        const tabs = document.querySelectorAll(".tab-link");
        tabs.forEach((tab) => tab.classList.remove("active", "btn-primary"));
        tabs.forEach((tab) => tab.classList.add("btn-secondary"));
        const activeTab = document.getElementById(
            type === "regular" ? "regularTab" : "topupTab"
        );
        activeTab.classList.add("active", "btn-primary");
        activeTab.classList.remove("btn-secondary");
    }

    // Load default tab on page load
    document.addEventListener("DOMContentLoaded", function () {
        loadTabData("regular"); // Load regular tab by default
    });

    // Attach event listeners to tabs
    document
        .getElementById("regularTab")
        .addEventListener("click", function () {
            loadTabData("regular");
        });

    document.getElementById("topupTab").addEventListener("click", function () {
        loadTabData("topup");
    });
}
if (pageValue === "provider.dashboard") {
    $("#message-loader").removeClass("hidden");
    $(".topup,.topupplantitle,.topupprice").hide();
    document.addEventListener("DOMContentLoaded", requestPermissionAndGetToken);
    $(".subscribedpack").hide();

    $.ajax({
        url: "/api/gettotalbookingcount",
        type: "POST",
        data: {
            provider_id: auth_id,
        },
        dataType: "json",
        headers: {
            Authorization: "Bearer " + localStorage.getItem("admin_token"),
            Accept: "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.code == "200") {
                var data = response.data["totalcount"];
                var currencyval = response.data["currency"];
                if (data !== null) {
                    $(".completecount").text(data.completed_count);
                    $(".cancelcount").text(data.cancelled_count);
                    $(".upcomingcount").text(data.upcoming_count);
                    $(".order_completed_count").text(
                        data.order_completed_count
                    );
                    $(".totalincome").text(currencyval + "0");
                    $(".completeincome").text(currencyval + "0");
                    $(".totaldue").text(currencyval + "0");
                    if (data.processed_amount) {
                        $(".totalincome").text(
                            currencyval + data.processed_amount
                        );
                    }
                    if (data.overall_total_amount) {
                        $(".completeincome").text(
                            currencyval + data.overall_total_amount
                        );
                    }
                    if (data.due_amount) {
                        $(".totaldue").text(currencyval + data.due_amount);
                    }
                }
            } else {
                toastr.error(response.message);
            }
        },
        error: function (xhr, status, error) {
            if (languageId === 2) {
                loadJsonFile(
                    "error_occurred_fetching_data",
                    function (langtst) {
                        toastr.error(langtst);
                    }
                );
            } else {
                toastr.error("An error occurred while fetching the data.");
            }
        },
    });

    $(".popularplan").hide();
    $.ajax({
        url: "/api/getsubscription",
        type: "POST",
        data: {
            provider_id: auth_id,
        },
        dataType: "json",
        headers: {
            Authorization: "Bearer " + localStorage.getItem("admin_token"),
            Accept: "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.code == "200") {
                var data = response.data["standardplan"];
                var currency = response.data["currency"];
                if (data != "" && data != null) {
                    $(".popularplan").show();
                    $(".plantitle").text(data.package_title);
                    $(".planprice").text(
                        data.price ? currency + data.price : ""
                    );
                } else {
                    $(".popularplan").hide();
                }
            } else {
                toastr.error(response.message);
            }
        },
        error: function (xhr, status, error) {
            if (languageId === 2) {
                loadJsonFile(
                    "error_occurred_fetching_data",
                    function (langtst) {
                        toastr.error(langtst);
                    }
                );
            } else {
                toastr.error("An error occurred while fetching the data.");
            }
        },
    });

    $.ajax({
        url: "/api/getsubscribedpack",
        type: "POST",
        data: {
            provider_id: auth_id,
        },
        dataType: "json",
        headers: {
            Authorization: "Bearer " + localStorage.getItem("admin_token"),
            Accept: "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.code == "200") {
                var data = response.data["standardplan"];
                var topup = response.data["topupplan"];
                var currency = response.data["currency"];
                if (data && data !== "") {
                    $(".nosubscribe,.subscribeplan").hide();
                    $(".subscribedpack").show();
                    $(".subscribedplantitle").text(data.package_title);
                    $(".description").text(data.description);
                    if (data.package_title != "Free") {
                        $(".subprice").text(currency + data.price);
                    }
                } else {
                    $(".nosubscribe").show();
                }
                if (topup && topup !== "") {
                    $(".topup,.topupplantitle,.topupprice").show();
                    $(".topupplantitle").text(topup.package_title);
                    $(".topupprice").text(currency + topup.price);
                }
                $(".label-loader, .input-loader").hide(); // Hide all skeleton loaders
                $(".real-label, .real-input").removeClass("d-none");
            } else {
                toastr.error(response.message);
            }
        },
        error: function (xhr, status, error) {
            if (languageId === 2) {
                loadJsonFile(
                    "error_occurred_fetching_data",
                    function (langtst) {
                        toastr.error(langtst);
                    }
                );
            } else {
                toastr.error("An error occurred while fetching the data.");
            }
        },
    });

    $.ajax({
        url: "/api/getlatestbookings",
        type: "POST",
        data: {
            provider_id: auth_id,
            language_id: languageId,
        },
        dataType: "json",
        headers: {
            Authorization: "Bearer " + localStorage.getItem("admin_token"),
            Accept: "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.code == "200") {
                var data = response.data;
                const bookdiv = $(".bookcard");
                if (data != "") {
                    response.data.forEach((val) => {
                        const defaultproductpath =
                            "/front/img/default-placeholder-image.png";
                        let productImage =
                            val.productimage && val.productimage !== "N/A"
                                ? `/storage/${val.productimage}`
                                : defaultproductpath;
                        const defaultprofileImage =
                            "/assets/img/profile-default.png";
                        let profileImagePath =
                            val.profile_image && val.profile_image !== "N/A"
                                ? `/storage/profile/${val.profile_image}`
                                : defaultprofileImage;
                        var bookhtml = `<div class="card book-crd">
                                    <div class="card-body"><div class="d-flex justify-content-between align-items-center flex-wrap row-gap-2">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-lg flex-shrink-0 me-2">
                                               <img  src="${productImage}" alt="Img">
                                            </div>
                                            <div>
                                                <div class="fw-medium text-black">${val.product_name}</div>`;

                        if (val.fromtime != "" && val.fromtime != null) {
                            bookhtml += ` <span class="d-block fs-12"><i class="ti ti-clock me-1"></i>${val.fromtime} - ${val.totime}</span>`;
                        }
                        bookhtml += `</div></div>
                                                    <div class="d-flex align-items-center">
                                                    <a href="#" class="avatar avatar-sm me-2">`;
                        bookhtml += `<img  src="${profileImagePath}" class="rounded-circle" alt="Img">`;

                        bookhtml += `</a></div>
                                    </div> </div>
                                    </div>`;
                        bookdiv.append(bookhtml);
                    });
                } else {
                    $(".bookview").hide();
                    var msg = "No Data Found";
                    if (languageId === 2) {
                        loadJsonFile("No Data Found", function (langtst) {
                            msg = langtst;
                            html = `<div class="text-center">` + msg + `</div>`;
                            $(".bookcard").html(html);
                        });
                    } else {
                        html = `<div class="text-center">` + msg + `</div>`;
                        $(".bookcard").html(html);
                    }
                }
            } else {
                toastr.error(response.message);
            }
        },
        error: function (xhr, status, error) {
            if (languageId === 2) {
                loadJsonFile(
                    "error_occurred_fetching_data",
                    function (langtst) {
                        toastr.error(langtst);
                    }
                );
            } else {
                toastr.error("An error occurred while fetching the data.");
            }
        },
    });

    $.ajax({
        url: "/api/getlatestreviews",
        type: "POST",
        data: {
            provider_id: auth_id,
            language_id: languageId,
        },
        dataType: "json",
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
                        const defaultImagePath =
                            "/assets/img/profile-default.png";
                        let userImage =
                            val.profile_image && val.profile_image !== "N/A"
                                ? `/storage/profile/${val.profile_image}`
                                : defaultImagePath;
                        let productImage = val.product_image;

                        var ratehtml = ` <div class=" border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap row-gap-2">
                                <div class="d-flex">
                                    <a href="javascript:void(0);" class="avatar avatar-lg flex-shrink-0 me-2">`;
                        ratehtml += `<img src="${productImage}" alt="Product Image">`;
                        ratehtml += `</a>
                                    <div>
                                          <p class="fs-12 mb-0 pe-2 border-end">For <span class="text-info">${val.product_name}</span></p>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm mx-2">`;
                        ratehtml += `<img src="${userImage}" alt="User Profile Image" class="img-fluid rounded-circle">`;
                        ratehtml += `    </span>
                                            <span class="fs-12">${val.username}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <span class="text-warning fs-10 me-1">`;
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= Math.floor(val.rating ?? 0)) {
                                ratehtml += `<i class="ti ti-star-filled filled"></i>`;
                            } else {
                                ratehtml += `<i class="ti ti-star"></i>`;
                            }
                        }
                        ratehtml += `</span>
                                    <span class="fs-12">${
                                        val.rating ?? 0
                                    }</span>
                                </div>
                            </div>
                        </div>`;
                        ratingdiv.append(ratehtml);
                    });
                } else {
                    var msg = "No Data Found";
                    if (languageId === 2) {
                        loadJsonFile("No Data Found", function (langtst) {
                            msg = langtst;
                            html = `<div class="text-center">` + msg + `</div>`;
                            $(".ratecard").html(html);
                        });
                    } else {
                        html = `<div class="text-center">` + msg + `</div>`;
                        $(".ratecard").html(html);
                    }
                }
            } else {
                toastr.error(response.message);
            }
        },
        error: function (xhr, status, error) {
            if (languageId === 2) {
                loadJsonFile(
                    "error_occurred_fetching_data",
                    function (langtst) {
                        toastr.error(langtst);
                    }
                );
            } else {
                toastr.error("An error occurred while fetching the data.");
            }
        },
    });

    $.ajax({
        url: "/api/getlatestproductservice",
        type: "POST",
        data: {
            provider_id: auth_id,
            language_id: languageId,
        },
        dataType: "json",
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
                        const defaultproductpath =
                            "/front/img/default-placeholder-image.png";
                        let productImage =
                            val.productimage && val.productimage !== "N/A"
                                ? `/storage/${val.productimage}`
                                : defaultproductpath;

                        var servicehtml = `<div class="card book-crd">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center flex-wrap row-gap-2">
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar avatar-lg flex-shrink-0 me-2">
                                            <img src="${productImage}" alt="Img">
                                        </a>
                                        <div>
                                            <a href="${
                                                val.product_slug
                                                    ? "/servicedetail/" +
                                                      val.product_slug
                                                    : "#"
                                            }" class="fw-medium">${
                            val.product_name
                        }</a>
                                            <div class="fs-12 d-flex align-items-center gap-2">`;

                        let booklabel = "Bookings";
                        if (languageId === 2) {
                            loadJsonFile(booklabel, function (langtst) {
                                servicehtml += `<span class="pe-2 border-end">${val.total_bookings} ${langtst}</span>`;
                            });
                        } else {
                            servicehtml += `<span class="pe-2 border-end">${val.total_bookings} Bookings</span>`;
                        }

                        if (val.average_rating != "") {
                            servicehtml += `<span><i class="ti ti-star-filled text-warning me-1"></i>${
                                val.average_rating ?? 0
                            }</span>`;
                        }

                        servicehtml += `</div></div></div>
                                </div>
                            </div>
                        </div>`;

                        servicediv.append(servicehtml);
                    });

                    $(".label-loader, .input-loader").hide(); // Hide all skeleton loaders
                    $(".real-label, .real-input").removeClass("d-none");
                } else {
                    $(".serviceview").hide();
                    var msg = "No Data Found";
                    if (languageId === 2) {
                        loadJsonFile("No Data Found", function (langtst) {
                            msg = langtst;
                            html = `<div class="text-center">${msg}</div>`;
                            $(".servicecard").html(html);
                        });
                    } else {
                        html = `<div class="text-center">${msg}</div>`;
                        $(".servicecard").html(html);
                    }
                }
            } else {
                toastr.error(response.message);
            }
        },
        error: function (xhr, status, error) {
            if (languageId === 2) {
                loadJsonFile(
                    "error_occurred_fetching_data",
                    function (langtst) {
                        toastr.error(langtst);
                    }
                );
            } else {
                toastr.error("An error occurred while fetching the data.");
            }
        },
    });

    setTimeout(function () {
        $("#message-loader").addClass("hidden");
    }, 1000);
}

if (pageValue === "staff.dashboard") {
    $("#message-loader").removeClass("hidden");

    $.ajax({
        url: "/api/staff/get-total-bookingcount",
        type: "POST",
        data: {
            provider_id: auth_id,
        },
        dataType: "json",
        headers: {
            Authorization: "Bearer " + localStorage.getItem("admin_token"),
            Accept: "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.code == "200") {
                var data = response.data["totalcount"];
                var currencyval = response.data["currency"];
                if (data !== null) {
                    $(".completecount").text(data.completed_count);
                    $(".cancelcount").text(data.cancelled_count);
                    $(".upcomingcount").text(data.upcoming_count);
                    $(".order_completed_count").text(
                        data.order_completed_count
                    );
                    $(".totalincome").text(currencyval + "0");
                    $(".completeincome").text(currencyval + "0");
                    $(".totaldue").text(currencyval + "0");
                    if (data.processed_amount) {
                        $(".totalincome").text(
                            currencyval + data.processed_amount
                        );
                    }
                    if (data.overall_total_amount) {
                        $(".completeincome").text(
                            currencyval + data.overall_total_amount
                        );
                    }
                    if (data.due_amount) {
                        $(".totaldue").text(currencyval + data.due_amount);
                    }
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
        url: "/api/staff/getlatestbookings",
        type: "POST",
        data: {
            provider_id: $("body").data("authid"),
            language_id: languageId,
        },
        headers: {
            Authorization: "Bearer " + localStorage.getItem("admin_token"),
            Accept: "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.code == "200") {
                var data = response.data;
                const bookdiv = $(".bookcard");
                if (data != "") {
                    response.data.forEach((val) => {
                        const defaultproductpath =
                            "/front/img/default-placeholder-image.png";
                        let productImage =
                            val.product_image_url ?? defaultproductpath;
                        const defaultprofileImage =
                            "/assets/img/profile-default.png";
                        let profileImagePath =
                            val.profile_image && val.profile_image !== "N/A"
                                ? `/storage/profile/${val.profile_image}`
                                : defaultprofileImage;
                        var bookhtml = `<div class="card book-crd">
                                    <div class="card-body"><div class="d-flex justify-content-between align-items-center flex-wrap row-gap-2">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-lg flex-shrink-0 me-2">
                                               <img  src="${productImage}" alt="Img">
                                            </div>
                                        <div>
                                        <div class="fw-medium text-black">${val.product_name}</div>`;

                        if (val.fromtime != "" && val.fromtime != null) {
                            bookhtml += ` <span class="d-block fs-12"><i class="ti ti-clock me-1"></i>${val.fromtime} - ${val.totime}</span>`;
                        }
                        bookhtml += `</div></div>
                                                    <div class="d-flex align-items-center">
                                                    <a href="#" class="avatar avatar-sm me-2">`;
                        bookhtml += `<img  src="${profileImagePath}" class="rounded-circle" alt="Img">`;

                        bookhtml += `</a></div>
                                    </div> </div>
                                    </div>`;
                        bookdiv.append(bookhtml);
                    });
                } else {
                    $(".bookview").hide();
                    var msg = "No Data Found";
                    if (languageId === 2) {
                        loadJsonFile("No Data Found", function (langtst) {
                            msg = langtst;
                            html = `<div class="text-center">` + msg + `</div>`;
                            $(".bookcard").html(html);
                        });
                    } else {
                        html = `<div class="text-center">` + msg + `</div>`;
                        $(".bookcard").html(html);
                    }
                }
            } else {
                toastr.error(response.message);
            }
        },
        complete: function() {
            $(".label-loader, .input-loader").hide();
            $(".real-label, .real-input").removeClass("d-none");
        },
        error: function (xhr, status, error) {
            if (languageId === 2) {
                loadJsonFile(
                    "error_occurred_fetching_data",
                    function (langtst) {
                        toastr.error(langtst);
                    }
                );
            } else {
                toastr.error("An error occurred while fetching the data.");
            }
        },
    });

    $.ajax({
        url: "/api/staff/getlatestreviews",
        type: "POST",
        data: {
            provider_id: $("body").data("authid"),
            language_id: languageId,
        },
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
                        const defaultImagePath =
                            "/assets/img/profile-default.png";
                        let userImage =
                            val.profile_image && val.profile_image !== "N/A"
                                ? `/storage/profile/${val.profile_image}`
                                : defaultImagePath;
                        const defaultproductpath =
                            "/front/img/default-placeholder-image.png";
                        let productImage =
                            val.productimage && val.productimage !== "N/A"
                                ? `/storage/${val.productimage}`
                                : defaultproductpath;

                        var ratehtml = ` <div class=" border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap row-gap-2">
                                <div class="d-flex">
                                    <a href="javascript:void(0);" class="avatar avatar-lg flex-shrink-0 me-2">`;
                        ratehtml += `<img src="${productImage}" alt="Product Image">`;
                        ratehtml += `</a>
                                    <div>
                                          <p class="fs-12 mb-0 pe-2 border-end">For <span class="text-info">${val.product_name}</span></p>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm mx-2">`;
                        ratehtml += `<img src="${userImage}" alt="User Profile Image" class="img-fluid rounded-circle">`;
                        ratehtml += `    </span>
                                            <span class="fs-12">${val.username}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <span class="text-warning fs-10 me-1">`;
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= Math.floor(val.rating ?? 0)) {
                                ratehtml += `<i class="ti ti-star-filled filled"></i>`;
                            } else {
                                ratehtml += `<i class="ti ti-star"></i>`;
                            }
                        }
                        ratehtml += `</span>
                                    <span class="fs-12">${
                                        val.rating ?? 0
                                    }</span>
                                </div>
                            </div>
                        </div>`;
                        ratingdiv.append(ratehtml);
                    });
                } else {
                    var msg = "No Data Found";
                    if (languageId === 2) {
                        loadJsonFile("No Data Found", function (langtst) {
                            msg = langtst;
                            html = `<div class="text-center">` + msg + `</div>`;
                            $(".ratecard").html(html);
                        });
                    } else {
                        html = `<div class="text-center">` + msg + `</div>`;
                        $(".ratecard").html(html);
                    }
                }
            } else {
                toastr.error(response.message);
            }
        },
        complete: function() {
            $(".label-loader, .input-loader").hide();
            $(".real-label, .real-input").removeClass("d-none");
        },
        error: function (xhr, status, error) {
            if (languageId === 2) {
                loadJsonFile(
                    "error_occurred_fetching_data",
                    function (langtst) {
                        toastr.error(langtst);
                    }
                );
            } else {
                toastr.error("An error occurred while fetching the data.");
            }
        },
    });

    $.ajax({
        url: "/api/staff/getlatestproductservice",
        type: "POST",
        data: {
            provider_id: $("body").data("authid"),
            language_id: languageId,
        },
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
                        const defaultproductpath =
                            "/front/img/default-placeholder-image.png";
                        let productImage =
                            val.productimage && val.productimage !== "N/A"
                                ? `/storage/${val.productimage}`
                                : defaultproductpath;
                        var servicehtml = `<div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex">
                                    <a href="#" class="avatar avatar-lg me-2">
                                        <img src=${productImage}  alt="Img">
                                    </a>
                                    <div>
                                        <a href="${
                                            val.product_slug
                                                ? "/servicedetail" +
                                                  val.product_slug
                                                : "#"
                                        }" class="fw-medium mb-0">${
                            val.product_name
                        }</a>
                                        <div class="fs-12 d-flex align-items-center gap-2">`;
                        let booklabel = "Bookings";
                        if (languageId === 2) {
                            loadJsonFile(booklabel, function (langtst) {
                                servicehtml += `<span class="pe-2 border-end">${val.total_bookings} ${langtst}</span>`;
                            });
                        } else {
                            servicehtml += `<span class="pe-2 border-end">${val.total_bookings} Bookings</span>`;
                        }

                        if (val.average_rating != "") {
                            servicehtml += `<span><i class="ti ti-star-filled text-warning me-1 me-1"></i>${
                                val.average_rating ?? 0
                            }</span>`;
                        }
                        servicehtml += `  </div>
                                    </div>
                                </div>
                             </div>`;
                        servicediv.append(servicehtml);
                    });
                } else {
                    $(".serviceview").hide();
                    var msg = "No Data Found";
                    if (languageId === 2) {
                        loadJsonFile("No Data Found", function (langtst) {
                            msg = langtst;
                            html = `<div class="text-center">` + msg + `</div>`;
                            $(".servicecard").html(html);
                        });
                    } else {
                        html = `<div class="text-center">` + msg + `</div>`;
                        $(".servicecard").html(html);
                    }
                }
            } else {
                toastr.error(response.message);
            }
        },
        error: function (xhr, status, error) {
            toastr.error(
                xhr.responseJSON.message ||
                    "An error occurred while fetching the data."
            );
        },
    });

    setTimeout(function () {
        $("#message-loader").addClass("hidden");
    }, 1000);
}

function notificationList(auth_provider_id) {
    $.ajax({
        url: "/api/notification/notificationlist",
        type: "POST",
        data: { type: "provider", authid: auth_provider_id },
        headers: {
            Accept: "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.code == "200") {
                var data = response.data["notifications"];
                var authuser = response.data["auth_user"];
                var count = response.data["count"];
                let belldiv = $("#notification-data");
                let bell_count_div = $(".bellcount");

                if (count > 0) {
                    const html = `<span class="notification-dot position-absolute start-80 translate-middle p-1 bg-danger border border-light rounded-circle">
                    </span>`;
                    bell_count_div.html(html);
                } else {
                    bell_count_div.empty();
                }

                if (data != "") {
                    data.forEach((val) => {
                        let profileImage = "/assets/img/profile-default.png";
                        if (
                            authuser == val.from_user_id ||
                            authuser == val.to_user_id
                        ) {
                            profileImage = val.from_profileimg;
                        } else {
                            profileImage = val.to_profileimg;
                        }
                        var bellhtml = `<div class="border-bottom mb-3 pb-3">
                                        <div class="d-flex">
                                            <span class="avatar avatar-lg me-2 flex-shrink-0">
                                                        <img src="${profileImage}" alt="Profile" class="rounded-circle">
                                                    </span>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center">
                                                <p class="mb-1 w-100">`;
                        if (authuser == val.from_user_id) {
                            if (val.from_description) {
                                bellhtml += `${val.from_description}</p>`;
                            }
                        } else {
                            if (val.to_description) {
                                bellhtml += `${val.to_description} </p>`;
                            }
                        }
                        bellhtml += `<span class="d-flex justify-content-end "> <i class="ti ti-point-filled text-primary"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                </div>`;
                        belldiv.append(bellhtml);
                    });
                } else {
                    belldiv.empty();
                    let msg = $("#notification-data").data("empty_info");
                    $(".markallread").hide();
                    bellhtml =
                        `<div class="text-center mb-3">` + msg + `</div>`;
                    $("#notification-data").html(bellhtml);
                }
            }
        },
    });
}

function markAllRead(auth_provider_id) {
    $.ajax({
        url: "/api/notification/updatereadstatus",
        type: "POST",
        data: { type: "provider", authid: auth_provider_id },
        headers: {
            Authorization: "Bearer " + localStorage.getItem("admin_token"),
            Accept: "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.code == "200") {
                notificationList(auth_provider_id);
            }
        },
        error: function (xhr, status, error) {
            if (languageId === 2) {
                loadJsonFile("error_occurred_update_data", function (langtst) {
                    toastr.error(langtst);
                });
            } else {
                toastr.error("An error occurred while update data.");
            }
        },
    });
}

$(".cancelnotify").on("click", function (e) {
    e.preventDefault(); // Prevent default link behavior
    $(".notification-dropdown").removeClass("show"); // Hide the dropdown
});

function setSessionValue(key, value, authid) {
    $.ajax({
        url: "/set-session", // Laravel route
        type: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
            key: key,
            value: value,
            type: "providerchat",
            authid: authid,
        },
        success: function (response) {
            if (response.success) {
            }
        },
        error: function (xhr) {},
    });
}

if (pageValue === "provider.transaction") {
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
        formData.append("booking_id", currentBookingId); // Add booking_id to the form data

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
                    bookingTransactionList();
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
                    '{{ __("Invalid file type. Only images and PDFs are allowed.") }}',
                    '{{ __("Error") }}'
                );
                codFileInput.value = ""; // Clear the input
                return;
            }

            if (file.size > maxSize) {
                toastr.error(
                    '{{ __("File size exceeds the maximum limit of 2MB.") }}',
                    '{{ __("Error") }}'
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
                    '{{ __("PDF file selected: ") }}' + file.name;
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
        const provider_id = $("#user_id").val();
        $.ajax({
            url: "/api/transactionlist",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                order_by: "desc",
                sort_by: "booking_date",
                provider_id: provider_id,
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
                        if ($.fn.DataTable.isDataTable("#transactionList")) {
                            $("#transactionList").DataTable().destroy();
                        }
                        tableBody += `
                            <tr>
                                <td colspan="11" class="text-center">${$(
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
                                case "In Progress":
                                    statusClass = "text-primary";
                                    break;
                                case "Completed":
                                    statusClass = "text-success";
                                    break;
                                default:
                                    statusClass = "text-secondary";
                                    break;
                            }

                            const defaultImage =
                                "/assets/img/profile-default.png";

                            let customerImage =
                                transaction.customer.image_url &&
                                transaction.customer.image_url !== ""
                                    ? `${transaction.customer.image_url}`
                                    : defaultImage;

                            let serviceImage =
                                transaction.service.service_image_url;

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
                                            <img src="${customerImage}" class="transactionimg me-3 rounded-circle" alt="Customer Image" style="width: 50px; height: 50px; object-fit: cover;">
                                            <div>
                                                <span class="fw-bold d-block">${truncateText(
                                                    transaction.customer.name
                                                )}</span>
                                                <small class="text-muted">${truncateText(
                                                    transaction.customer.email
                                                )}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                    <div class="d-flex align-items-center table-minset">
                                        <a href="javascript:void(0);" class="table-imgname d-block flex-shrink-0">
                                            <img src="${serviceImage}" class="transactionimg me-2" alt="Service Image">

                                        </a>
                                        <span>${truncateText(
                                            transaction.service.name
                                        )}</span>
                                        </div>
                                    </td>
                                    <td>${currency}${
                                transaction.amount.total_amount
                            }</td>
                                    <td>${currency}${
                                transaction.amount.commission
                            }</td>
                                    <td>${currency}${
                                transaction.amount.final_total
                            }</td>
                                    <td>${formattedDate}</td>
                                    <td>${paymentType}</td>
                                    <td <h6 class="badge-active ${statusClass}">${paymentStatus}</td>
                                    <td>
                                        <div class="table-actions d-flex">
                                            <a class="delete-table view-transaction" href="javascript:void(0);"
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
                                            data-payment-proof="${
                                                transaction.payment
                                                    .payment_proof
                                            }"
                                            data-transaction_id="${
                                                transaction.payment
                                                    .transaction_id
                                            }"
                                            data-currency="${
                                                transaction.currencySymbol
                                            }"
                                            data-additional_services='${JSON.stringify(
                                                transaction.additional_services
                                            )}'
                                            data-status="${paymentStatus}">
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
                        let additionalServices = $(this).data(
                            "additional_services"
                        );
                        let currency = $(this).data("currency");
                        if (
                            Array.isArray(additionalServices) &&
                            additionalServices.length > 0
                        ) {
                            let list = "<ul class='mb-0 ps-3'>";
                            additionalServices.forEach((service) => {
                                list += `<li><bold>${service.name}</bold> - ${currency}${service.price}</li>`;
                            });
                            list += "</ul>";
                            $(".additional_service").removeClass("d-none");
                            $("#additional_service_list").html(list);
                        } else {
                            $(".additional_service").addClass("d-none");
                        }

                        let customer = $(this).data("customer");
                        let provider = $(this).data("provider");
                        let service = $(this).data("service");
                        let amount = $(this).data("amount");
                        let tax = $(this).data("tax");
                        let date = $(this).data("date");
                        let paymentType = $(this).data("payment-type");
                        let transactionId = $(this).data("transaction_id"); // Payment proof path

                        let status = $(this).data("status");
                        currentBookingId = $(this).data("booking-id");
                        let paymentProof = $(this).data("payment-proof"); // Payment proof path
                        $("#transactionCustomer").text(customer);
                        $("#transactionProvider").text(provider);
                        $("#transactionService").text(service);
                        $("#transactionAmount").text(currency + amount);
                        $("#transactionTax").text(currency + tax);
                        $("#transactionDate").text(date);
                        $("#transactionPaymentType").text(paymentType);
                        $("#transactionId").text(transactionId);
                        $("#transactionStatus").text(status);

                        // Handle Payment Proof Preview
                        let filePreview = $("#filePreview");
                        filePreview.empty();

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

                        if (paymentType === "COD" && status !== "Paid") {
                            $("#codUploadSection").removeClass("d-none");
                        } else {
                            $("#codUploadSection").addClass("d-none");
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
                toastr.error(
                    "Error while retrieving transactions. Please try again."
                );
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
                provider_id: $("#user_id").val(),
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
                        if (
                            $.fn.DataTable.isDataTable("#leadsTransactionTable")
                        ) {
                            $("#leadsTransactionTable").DataTable().destroy();
                        }
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
                                        transaction.customer.profile_image
                                    }" class="transactionimg me-3 rounded-circle admin_provider_img" alt="Customer Image">
                                    <div>
                                        <span class="fw-bold d-block">${truncateText(
                                            transaction.customer.full_name
                                        )}</span>
                                        <small class="text-muted">${truncateText(
                                            transaction.customer.email
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

if (pageValue === "provider.payouts") {
    $(document).ready(function () {
        getProviderPayoutRequest();
        getPayoutDetails();
        getProviderBalnce();
    });

    $(".payout_type").on("change", function () {
        $(".form-control").removeClass("is-invalid is-valid");
        $(".error-text").text("");
        payoutType = $(this).val();

        if (payoutType == 1) {
            $("#payout_type").val(payoutType);
            $("#paypalContainer").show();
            $("#stripeContainer").hide();
            $("#bankContainer").hide();
            $("#id").val($("#paypalContainer").data("id"));
        } else if (payoutType == 2) {
            $("#payout_type").val(payoutType);
            $("#paypalContainer").hide();
            $("#stripeContainer").show();
            $("#bankContainer").hide();
            $("#id").val($("#stripeContainer").data("id"));
        } else if (payoutType == 4) {
            $("#payout_type").val(payoutType);
            $("#paypalContainer").hide();
            $("#stripeContainer").hide();
            $("#bankContainer").show();
            $("#id").val($("#bankContainer").data("id"));
        }

        $("#payoutForm").validate().destroy();
        setupValidation(payoutType);
    });

    $("#account_number").on("input", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/g, "")
        );
    });

    function setupValidation(payoutType) {
        let rules = {};
        let messages = {};

        if (payoutType == 1) {
            rules = {
                paypal_id: { required: true },
            };
            messages = {
                paypal_id: { required: $("#paypal_id_error").data("required") },
            };
        } else if (payoutType == 2) {
            rules = {
                stripe_id: { required: true },
            };
            messages = {
                stripe_id: { required: $("#stripe_id_error").data("required") },
            };
        } else if (payoutType == 4) {
            rules = {
                holder_name: { required: true },
                bank_name: { required: true },
                account_number: { required: true },
                ifsc: { required: true },
            };
            messages = {
                holder_name: {
                    required: $("#holder_name_error").data("required"),
                },
                bank_name: { required: $("#bank_name_error").data("required") },
                account_number: {
                    required: $("#account_number_error").data("required"),
                },
                ifsc: { required: $("#ifsc_error").data("required") },
            };
        }

        $("#payoutForm").validate({
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
                payoutType = $("#payout_type").val();
                if (payoutType == 1) {
                    formData = {
                        provider_id: $("#provider_id").val(),
                        payout_type: $("#payout_type").val(),
                        paypal_id: $("#paypal_id").val(),
                        id: $("#id").val(),
                    };
                } else if (payoutType == 2) {
                    formData = {
                        provider_id: $("#provider_id").val(),
                        payout_type: $("#payout_type").val(),
                        stripe_id: $("#stripe_id").val(),
                        id: $("#id").val(),
                    };
                } else if (payoutType == 4) {
                    formData = {
                        holder_name: $("#holder_name").val(),
                        bank_name: $("#bank_name").val(),
                        account_number: $("#account_number").val(),
                        ifsc: $("#ifsc").val(),
                        provider_id: $("#provider_id").val(),
                        payout_type: $("#payout_type").val(),
                        id: $("#id").val(),
                    };
                }
                savePayout(formData);
            },
        });
    }
    setupValidation($("#payout_type").val());

    $("#set_payout_btn").on("click", function () {
        $(".form-control").removeClass("is-invalid is-valid");
        $(".error-text").text("");
    });

    function getPayoutDetails() {
        $.ajax({
            url: "/api/get-payout-details",
            type: "POST",
            data: {
                provider_id: $("#provider_id").val(),
            },
            success: function (response) {
                if (response.data.length > 0) {
                    var payouts = response.data;

                    payouts.forEach((payout) => {
                        if (payout.payout_type == 1) {
                            $("#paypal_id").val(payout.payout_detail);
                            $("#paypalContainer").attr("data-id", payout.id);
                            if (payout.status == 1) {
                                $("#id").val(payout.id);
                                $("#payout_type").val(payout.payout_type);
                                $("#rolelink").prop("checked", true);
                                $("#paypalContainer").show();
                                $("#stripeContainer").hide();
                                $("#bankContainer").hide();
                                $("#payment_method").text("Paypal");
                            }
                        } else if (payout.payout_type == 2) {
                            $("#stripe_id").val(payout.payout_detail);
                            $("#stripeContainer").attr("data-id", payout.id);
                            if (payout.status == 1) {
                                $("#id").val(payout.id);
                                $("#payout_type").val(payout.payout_type);
                                $("#rolelink1").prop("checked", true);
                                $("#paypalContainer").hide();
                                $("#stripeContainer").show();
                                $("#bankContainer").hide();
                                $("#payment_method").text("Stripe");
                            }
                        } else if (payout.payout_type == 4) {
                            bankData = payout.payout_detail;
                            $("#holder_name").val(bankData.holder_name);
                            $("#bank_name").val(bankData.bank_name);
                            $("#account_number").val(bankData.account_number);
                            $("#ifsc").val(bankData.ifsc);
                            $("#bankContainer").attr("data-id", payout.id);
                            if (payout.status == 1) {
                                $("#id").val(payout.id);
                                $("#payout_type").val(payout.payout_type);
                                $("#rolelink2").prop("checked", true);
                                $("#paypalContainer").hide();
                                $("#stripeContainer").hide();
                                $("#bankContainer").show();
                                $("#payment_method").text("Bank Transfer");
                            }
                        }
                    });
                } else {
                    $("#rolelink").prop("checked", true);
                    $("#paypalContainer").show();
                    $("#stripeContainer").hide();
                    $("#bankContainer").hide();
                    $("#payout_type").val("1");
                    $("#payment_method").text("None");
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }

    function savePayout(formData) {
        $.ajax({
            url: "/api/save-payout-details",
            type: "POST",
            data: formData,
            beforeSend: function () {
                $("#payout_save_btn")
                    .attr("disabled", true)
                    .html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
            },
            success: function (response) {
                $(".error-text").text("");
                $("#payout_save_btn")
                    .removeAttr("disabled")
                    .html($("#payout_save_btn").data("save"));
                $(".form-control").removeClass("is-invalid is-valid");
                $("#set-payout").modal("hide");
                if (response.code === 200) {
                    toastr.success(response.message);
                    getPayoutDetails();
                }
            },
            error: function (error) {
                $(".error-text").text("");
                $("#payout_save_btn")
                    .removeAttr("disabled")
                    .html($("#payout_save_btn").data("save"));
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

    function getProviderPayoutHistory() {
        $.ajax({
            url: "/api/provider/get-payout-history",
            type: "POST",
            data: {
                provider_id: $("#provider_id").val(),
            },
            success: function (response) {
                if (response.code === 200) {
                    let payoutHistory = response.data;
                    let tableBody = "";

                    if (payoutHistory.length === 0) {
                        if ($.fn.DataTable.isDataTable("#payoutHistoryTable")) {
                            $("#payoutHistoryTable").DataTable().destroy();
                        }
                        tableBody += `
                            <tr>
                                <td colspan="8" class="text-center">${$(
                                    "#payoutHistoryTable"
                                ).data("empty")}</td>
                            </tr>`;
                    } else {
                        let currencySymbol = response.currency_symbol;
                        payoutHistory.forEach((payout, index) => {
                            tableBody += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${payout.created_at}</td>
                                    <td>${currencySymbol}${
                                payout.total_amount
                            }</td>
                                    <td>${currencySymbol}${
                                payout.processed_amount
                            }</td>
                                    <td>${
                                        payout.payment_method ?? "PayPal"
                                    }</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-success-100 d-flex align-items-center"><i
                                                    class="ti ti-point-filled"></i>Paid</span>
                                        </div>
                                    </td>
                                    <td>${payout.created_at}</td>
                                    <td>
                                        <div class="">
                                            <a href="${
                                                payout.payment_proof_path
                                            }" download="PaymentProof">
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                    }

                    $("#payoutHistoryTable tbody").html(tableBody);
                    if (
                        payoutHistory.length != 0 &&
                        !$.fn.DataTable.isDataTable("#payoutHistoryTable")
                    ) {
                        $("#payoutHistoryTable").DataTable({
                            ordering: true,
                        });
                    }
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }

    function getProviderPayoutRequest() {
        $.ajax({
            url: "/api/provider/get-payout-request",
            type: "POST",
            data: {
                provider_id: $("#provider_id").val(),
            },
            success: function (response) {
                if (response.code === 200) {
                    let payoutHistory = response.data;
                    let tableBody = "";

                    if (payoutHistory.length == 0) {
                        if ($.fn.DataTable.isDataTable("#payoutRequestTable")) {
                            $("#payoutRequestTable").DataTable().destroy();
                        }
                        tableBody += `
                            <tr>
                                <td colspan="8" class="text-center">${$(
                                    "#payoutRequestTable"
                                ).data("empty")}</td>
                            </tr>`;
                    } else {
                        let currencySymbol = response.currency_symbol;
                        payoutHistory.forEach((payout, index) => {
                            tableBody += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${payout.created_date}</td>
                                    <td class="text-center">${currencySymbol}${
                                payout.amount
                            }</td>
                                    <td>${
                                        payout.payment_method ?? "PayPal"
                                    }</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-skyblue d-flex align-items-center"><i
                                                    class="ti ti-point-filled"></i>Pending</span>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                    }

                    $("#payoutRequestTable tbody").html(tableBody);
                    if (
                        payoutHistory.length != 0 &&
                        !$.fn.DataTable.isDataTable("#payoutRequestTable")
                    ) {
                        $("#payoutRequestTable").DataTable({
                            ordering: true,
                        });
                    }
                }
                $("#loader-table").hide();
                $(".label-loader, .input-loader").hide(); // Hide all skeleton loaders
                $(".real-label, .real-input").removeClass("d-none");
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }

    $("#amount").on("input", function () {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/g, "")
        );
        if ($(this).val().length > 12) {
            $(this).val($(this).val().slice(0, 12));
        }
    });

    $("#send_request").on("click", function () {
        $(".form-control").removeClass("is-invalid is-valid");
        $(".error-text").text("");
        $("#amount").val("");
    });

    $("#requestForm").validate({
        rules: {
            amount: {
                required: true,
                number: true,
                min: 1,
            },
        },
        messages: {
            amount: {
                required: "Amount is required.",
                number: "Please enter a valid number.",
                min: "Minimum request amount is 1.00",
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
                url: "/api/provider/send-request-amount",
                type: "POST",
                data: {
                    provider_id: $("#provider_id").val(),
                    payment_id: $("#payout_type").val(),
                    amount: $("#amount").val(),
                },
                beforeSend: function () {
                    $("#requestSendBtn")
                        .attr("disabled", true)
                        .html(
                            '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                        );
                },
                success: function (response) {
                    $(".error-text").text("");
                    $("#requestSendBtn").removeAttr("disabled").html("Send");
                    $(".form-control").removeClass("is-invalid is-valid");
                    $("#request_amount").modal("hide");
                    if (response.code === 200) {
                        toastr.success(response.message);
                        getProviderBalnce();
                        getProviderPayoutRequest();
                    }
                },
                error: function (error) {
                    $(".error-text").text("");
                    $("#requestSendBtn").removeAttr("disabled").html("Send");
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

    function getProviderBalnce() {
        $.ajax({
            url: "/api/get-provider-balance",
            type: "POST",
            data: {
                provider_id: $("#provider_id").val(),
            },
            success: function (response) {
                if (response.code === 200) {
                    var provider = response.data;

                    $(".available_amount").text(
                        provider.currency_symbol + provider.available_amount
                    );
                    $("#last_payout").text(
                        provider.currency_symbol + provider.last_payout
                    );

                    if (provider.available_amount <= 0) {
                        $("#send_request").hide();
                    } else {
                        $("#send_request").show();
                    }
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    }

    $(document).on("click", "#payoutHistory", function () {
        $("#payoutRequestTable").addClass("d-none");
        $("#payoutHistoryTable").removeClass("d-none");
        if ($.fn.DataTable.isDataTable("#payoutRequestTable")) {
            $("#payoutRequestTable").DataTable().destroy();
        }
        if ($.fn.DataTable.isDataTable("#payoutHistoryTable")) {
            $("#payoutHistoryTable").DataTable().destroy();
        }
        $("#payoutHistoryTable tbody").empty();

        getProviderPayoutHistory();
    });

    $(document).on("click", "#payoutRequest", function () {
        $("#payoutHistoryTable").addClass("d-none");
        $("#payoutRequestTable").removeClass("d-none");
        if ($.fn.DataTable.isDataTable("#payoutHistoryTable")) {
            $("#payoutHistoryTable").DataTable().destroy();
        }
        if ($.fn.DataTable.isDataTable("#payoutRequestTable")) {
            $("#payoutRequestTable").DataTable().destroy();
        }
        $("#payoutRequestTable tbody").empty();
        getProviderPayoutRequest();
    });
}
if (
    pageValue === "providerpayment.success" ||
    pageValue === "provider.subscriptionsuccess"
) {
    const container = document.getElementById("subscriptionCards");
    container.innerHTML = "";
    const newContent = `<div id="paymentSuccess" class="d-flex justify-content-center align-items-center vh-80">
        <div class="card mx-auto subscribe-success-message text-center p-3 py-5">
            <h3>Payment Successful</h3>
            <p>Your payment was successful! Click "OK" to proceed to the dashboard.</p>
            <a href="/provider/dashboard" class="btn btn-primary w-100">OK</a>
        </div>
    </div>`;

    // Add the new content to the container
    container.innerHTML += newContent;
}

$(document).on("click", "#del_account_btn", function () {
    $(".error-text").text("");
    $(".form-control").removeClass("is-invalid is-valid");
    $("#password").val("");
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
                password: $("#password").val(),
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

$("#deleteAccountForm").validate().settings.messages = {
    password: {
        required: $("#deleteAccountBtn").data("password_required"),
    },
};

if (pageValue === "provider.subscriptionhistory") {
    $(document).ready(function () {
        list_table();
        // Handle search input
        $("#searchLanguage").on("input", function () {
            list_table(1); // Reset to the first page on new search
        });
    });
    function list_table(page) {
        $.ajax({
            url: "/getsubscriptionhistorylist",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
                sort_by: "id",
                page: page,
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
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
        if (list["historydata"].length > 0) {
            i = 0;
            list["historydata"].forEach((data) => {
                i++;
                tableBody += `
                    <tr>
                        <td>${i}</td>
                        <td>${data.trx_date}</td>
                         <td>${data?.subscription_type ?? ""}</td>
                        <td>${data?.package_title ?? ""}</td>
                        <td>${list["currency"]}${data?.price ?? ""}</td>
                       <td><span class="payment-status fs-14" data-status="${
                           data.payment_status
                       }">${data.paymentstatus}</spn></td>
                        <td><span class="active-status fs-14" data-status="${
                            data.status
                        }">${data.activestatus}</spn></td>`;
                tableBody += `
                            <td>  <a href="#"
                        class="view-history" data-bs-toggle="modal" data-bs-target="#view-modal"
                        data-id="${data.id}" data-type="${data.subscription_type}" data-title="${data.package_title}" data-date="${data.trx_date}" data-status="${data.paymentstatus}"  data-amount="${list["currency"]}${data.price}">
                           <i class="ti ti-eye fs-20 m-3"></i>
                    </a>
                            </td>
                </tr>`;
            });

            $("#loader-table").hide();
            $(".label-loader, .input-loader").hide(); // Hide all skeleton loaders
            $(".real-label, .real-input").removeClass("d-none");
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
            $("#loader-table").hide();
            $(".label-loader, .input-loader").hide(); // Hide all skeleton loaders
            $(".real-label, .real-input").removeClass("d-none");
        }
        if ($.fn.DataTable.isDataTable("#ListTable")) {
            $("#ListTable").DataTable().destroy(); // Destroy previous instance
        }
        $("#ListTable tbody").html(tableBody);
        applyBookingStatusStyles();
        $("#ListTable").DataTable({
            ordering: true,
            pageLength: 10,
        });
    }
    function setupPagination(meta) {
        let paginationHtml = "";
        for (let i = 1; i <= meta.last_page; i++) {
            paginationHtml += `<li class="page-item ${
                meta.current_page === i ? "active" : ""
            }"><a class="page-link" href="#">${i}</a></li>`;
        }
        $("#pagination").html(paginationHtml);
        $("#pagination").on("click", ".page-link", function (e) {
            e.preventDefault();
            const page = $(this).text();
            list_table(page);
        });
    }

    function applyBookingStatusStyles() {
        $(".payment-status").each(function () {
            const status = $(this).data("status");
            let statusClass = "";
            let statusText = "";

            switch (status) {
                case 1:
                    statusClass = "badge badge-soft-info ms-2";
                    statusText = "Open";
                    break;
                case 2:
                    statusClass = "badge badge-soft-success ms-2";
                    statusText = "Paid";
                    break;
                default:
                    statusClass = "status-unknown";
                    statusText = "Unknown";
            }

            $(this).addClass(statusClass).text(statusText);
        });
        $(".active-status").each(function () {
            const status = $(this).data("status");
            let statusClass = "";
            let statusText = "";

            switch (status) {
                case 1:
                    statusClass = "badge badge-soft-success ms-2";
                    statusText = "Active";
                    break;
                case 0:
                    statusClass = "badge badge-soft-danger ms-2";
                    statusText = "Inactive";
                    break;
                default:
                    statusClass = "status-unknown";
                    statusText = "Unknown";
            }

            $(this).addClass(statusClass).text(statusText);
        });
    }
    $(document).on("click", ".view-history", function (e) {
        e.preventDefault();
        const type = $(this).data("type");
        const name = $(this).data("title");
        const date = $(this).data("date");
        const amount = $(this).data("amount");
        const status = $(this).data("status");
        document.getElementById("modalType").innerText = type;
        document.getElementById("modalTitle").innerText = name;
        document.getElementById("modalDate").innerText = date;
        document.getElementById("amount").innerText = amount;
        document.getElementById("status").innerText = status;
    });
}
function checkImageExists(imageUrl, callback) {
    const img = new Image();
    img.onload = () => callback(true);
    img.onerror = () => callback(false);
    img.src = imageUrl;
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

if (pageValue === "provider.reviews") {
    $(document).ready(function () {
        getProviderReviews(1);
    });

    function getProviderReviews(page = 1) {
        const perPage = $("#entries_per_page").val();
        $.ajax({
            url: "/api/get-review-list",
            type: "POST",
            data: {
                user_id: $("#user_id").val(),
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
                                if (i <= review.rating) {
                                    filledStars +=
                                        '<span><i class="ti ti-star-filled text-warning"></i></span>';
                                } else {
                                    filledStars +=
                                        '<span><i class="ti ti-star text-muted"></i></span>';
                                }
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
                                                        "del_permission"
                                                    ) == 1
                                                        ? `<div class="user-icon d-inline-flex">
                                                        <a href="#" class="delete_review_btn" data-id="${review.id}" data-bs-toggle="modal" data-bs-target="#del-review"><i class="ti ti-trash"></i></a>
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
                        $(".review-loader").hide();
                        $(".label-loader, .input-loader").hide();
                        $(".real-label, .real-input").removeClass("d-none");
                    } else {
                        $("#paginate_container").addClass("d-none");
                        $(".review-loader").hide();
                        $(".label-loader, .input-loader").hide();
                        $(".real-label, .real-input").removeClass("d-none");
                        $(".review_list_container").append(`
                            <div class="col-xxl-12 col-lg-12">
                                <div class="card shadow-none">
                                    <div class="card-body d-flex align-items-center justify-content-center">
                                        <span class="text-center text-black">${$(
                                            ".review_list_container"
                                        ).data("empty_info")}</span>
                                    </div>
                                </div>
                            </div>
                        `);
                    }

                    const endRecord =
                        currentPage * perPage > totalReviews
                            ? totalReviews
                            : currentPage * perPage;
                    $("#pagination_info").text(
                        `${
                            (currentPage - 1) * perPage + 1
                        } - ${endRecord} of ${totalReviews}`
                    );

                    let paginationLinks = "";
                    for (let i = 1; i <= lastPage; i++) {
                        paginationLinks += `
                            <li class="page-item me-2">
                                <a class="page-link-1 ${
                                    i === currentPage ? "active" : ""
                                }" href="javascript:void(0);" onclick="getProviderReviews(${i});">${i}</a>
                            </li>
                        `;
                    }
                    $("#pagination_links").html(paginationLinks);
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
                    .html($("#deleteReviewConfirm").data("yes"));

                if (response.code === 200) {
                    $("#del-review").modal("hide");
                    toastr.success(response.message);
                    getProviderReviews(1);
                }
            },
            error: function (error) {
                $("#deleteReviewConfirm")
                    .removeAttr("disabled")
                    .html($("#deleteReviewConfirm").data("yes"));
                toastr.error(error.responseJSON.message);
            },
        });
    });
}

if (pageValue === "provider.security") {
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

        // Make sure the CSRF token is included in the FormData if it's missing
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

if (pageValue === "provider.addbranch") {
    $(".select2").select2();

    $(".addHolidayBtn").on("click", function () {
        $("#holidayContainer").append(
            `<div class="row extra-holiday">
                <div class="col-xl-4">
                    <div class="mb-3">
                        <div class=" input-icon position-relative">
                            <input type="date" class="form-control" name="holiday[]" placeholder="dd-mm-yyyy">
                        </div>
                    </div>
                </div>
                <div class="col-xl-1 d-flex align-items-center">
                    <a href="javascript:void(0);" class="text-danger remove-holiday"><i class="ti ti-trash fs-14"></i></a>
                </div>
            </div>`
        );
    });

    $(document).on("click", ".remove-holiday", function () {
        $(this).closest(".extra-holiday").remove();
    });

    $(document).ready(function () {
        $("#branchForm").validate({
            rules: {
                branch_name: {
                    required: true,
                    maxlength: 100,
                    remote: {
                        url: "/provider/branch/check-unique",
                        type: "post",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                "content"
                            ),
                            Accept: "application/json",
                        },
                        data: {
                            branch_name: function () {
                                return $("#branch_name").val();
                            },
                        },
                    },
                },
                phone_number: {
                    required: true,
                    minlength: 10,
                    maxlength: 12,
                },
                email: {
                    required: true,
                    email: true,
                },
                branch_address: {
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
                zip_code: {
                    required: true,
                    maxlength: 6,
                    pattern: /^[a-zA-Z0-9]*$/,
                },
                start_hour: {
                    required: true,
                },
                end_hour: {
                    required: true,
                },
                "working_day[]": {
                    required: true,
                },
                "holiday[]": {
                    required: false,
                },
                branch_image: {
                    required: true,
                    extension: "jpeg|jpg|png",
                    filesize: 2048,
                },
            },
            messages: {
                branch_name: {
                    required: $("#branch_name_error").data("required"),
                    maxlength: $("#branch_name_error").data("max"),
                    remote: $("#branch_name_error").data("exists"),
                },
                phone_number: {
                    required: $("#phone_number_error").data("required"),
                    minlength: $("#phone_number_error").data("between"),
                    maxlength: $("#phone_number_error").data("between"),
                },
                email: {
                    required: $("#email_error").data("required"),
                    email: $("#email_error").data("format"),
                },
                branch_address: {
                    required: $("#branch_address_error").data("required"),
                    maxlength: $("#branch_address_error").data("max"),
                },
                zip_code: {
                    required: $("#zip_code_error").data("required"),
                    maxlength: $("#zip_code_error").data("max"),
                    pattern: $("#zip_code_error").data("char"),
                },
                start_hour: {
                    required: $("#start_hour_error").data("required"),
                },
                end_hour: {
                    required: $("#end_hour_error").data("required"),
                },
                "working_day[]": {
                    required: $("#working_day_sunday_error").data("required"),
                },
                "holiday[]": {
                    required: "Holiday is required.",
                },
                state: {
                    required: $("#state_error").data("required"),
                },
                city: {
                    required: $("#city_error").data("required"),
                },
                country: {
                    required: $("#country_error").data("required"),
                },
                branch_image: {
                    required: $("#branch_image_error").data("required"),
                    extension: $("#branch_image_error").data("extension"),
                    filesize: $("#branch_image_error").data("filesize"),
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
        });

        $.validator.addMethod(
            "filesize",
            function (value, element, param) {
                if (element.files.length === 0) return true;
                return element.files[0].size <= param * 1024;
            },
            "File size must be less than {0} KB."
        );

        $("#staffsForm").validate({
            rules: {
                "staffs[]": {
                    required: false,
                },
            },
            messages: {
                "staffs[]": {
                    required: "Staff is required.",
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
        });
    });

    $("#branch_btn").on("click", function (event) {
        event.preventDefault();

        if ($("#branchForm").valid()) {
            $("#first-field").hide();
            $("#second-field").show();
            $("#progressbar li").eq(0).removeClass("active");
            $("#progressbar li").eq(1).addClass("active");
        }
    });

    $("#staff_prev").on("click", function () {
        $("#second-field").hide();
        $("#first-field").show();
        $("#progressbar li").eq(1).removeClass("active");
        $("#progressbar li").eq(0).addClass("active");
    });

    $("#staff_btn").on("click", function (event) {
        event.preventDefault();

        if ($("#staffsForm").valid()) {
            $("#first-field").hide();
            $("#second-field").show();
            $("#progressbar li").eq(0).removeClass("active");
            $("#progressbar li").eq(1).addClass("active");

            saveBranch();
        }
    });

    function saveBranch() {
        let serviceFormData = $("#branchForm").serializeArray();
        let locationFormData = $("#staffsForm").serializeArray();

        var formData = new FormData();
        [...serviceFormData, ...locationFormData].forEach(function (item) {
            formData.append(item.name, item.value);
        });

        formData.append("branch_image", $("#branch_image")[0].files[0]);

        $.ajax({
            url: "/provider/save-branch-details",
            type: "POST",
            data: formData,
            enctype: "multipart/form-data",
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $("#staff_btn")
                    .attr("disabled", true)
                    .html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
            },
            success: function (response) {
                $(".error-text").text("");
                $("#staff_btn")
                    .removeAttr("disabled")
                    .html($("#staff_btn").data("save"));
                $(".form-control").removeClass("is-invalid is-valid");
                $(".select2-container").removeClass("is-invalid is-valid");
                if (response.code === 200) {
                    toastr.success(response.message);
                    window.location.href = "/provider/branch";
                }
            },
            error: function (error) {
                $(".error-text").text("");
                $("#staff_btn")
                    .removeAttr("disabled")
                    .html($("#staff_btn").data("save"));
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
    }

    $("#branch_image").on("change", function (event) {
        if ($(this).val() !== "") {
            $(this).valid();
        }
        $("#branch_img_container").show();
        let reader = new FileReader();
        reader.onload = function (e) {
            $("#branch_img_preview").attr("src", e.target.result).show();
        };
        reader.readAsDataURL(event.target.files[0]);
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
    $("#staff").on("change", function () {
        $(this).valid();
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

    $("#zip_code").on("input", function () {
        if ($(this).val().length > 6) {
            $(this).val($(this).val().slice(0, 6));
        }
    });

    $("#country").on("change", function () {
        const selectedCountry = $(this).val();
        clearDropdown($("#state"));
        clearDropdown($("#city"));

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
                    const stateDropdown = $("#state");
                    response.data.forEach((state) => {
                        stateDropdown.append(
                            `<option value="${state.id}">${state.name}</option>`
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
    });

    $("#state").on("change", function () {
        const selectedState = $(this).val();
        clearDropdown($("#city"));
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
                    const stateDropdown = $("#city");
                    response.data.forEach((city) => {
                        stateDropdown.append(
                            `<option value="${city.id}">${city.name}</option>`
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
}

if (pageValue === "provider.branch") {
    $(document).ready(function () {
        getBranchList();
    });

    $(document).on("click", "#addBranchBtn", function () {
        $.ajax({
            url: "/provider/branch/check-limit",
            type: "POST",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $("#addBranchBtn")
                    .attr("disabled", true)
                    .html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
            },
            success: function (response) {
                $("#addBranchBtn")
                    .removeAttr("disabled")
                    .html($("#addBranchBtn").data("add_text"));
                if (response.code === 200) {
                    if (response.no_package === true) {
                        $("#no_sub").modal("show");
                    } else if (response.sub_count_end === true) {
                        $("#sub_count_end").modal("show");
                    } else if (response.sub_end === true) {
                        $("#sub_end").modal("show");
                    } else {
                        window.location.href = response.redirect_url;
                    }
                }
            },
            error: function (error) {
                $("#addBranchBtn")
                    .removeAttr("disabled")
                    .html($("#addBranchBtn").data("add_text"));
                if (response.code === 500) {
                    toastr.error(error.responseJSON.message);
                } else {
                    toastr.error("An error occured!");
                }
            },
        });
    });

    function getBranchList() {
        var id = $("#branchTable").data("id");

        $.ajax({
            url: "/provider/get-branch-list",
            type: "POST",
            dataType: "json",
            data: {
                id: id,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                Accept: "application/json",
            },
            success: function (response) {
                if (response.code === 200) {
                    let staffs = response.data;
                    let tableBody = "";

                    if (staffs.length === 0) {
                        if ($.fn.DataTable.isDataTable("#branchTable")) {
                            $("#branchTable").DataTable().destroy();
                        }
                        tableBody += `
                            <tr>
                                <td colspan="3" class="text-center">No branch available</td>
                            </tr>`;
                    } else {
                        staffs.forEach((staff, index) => {
                            tableBody += `
                               <tr>
                                <td class="text-center">${index + 1}</td>
                                <td>${staff.branch_name}</td>
                                <td>
                                    <div class="user-icon d-inline-flex">
                                    ${
                                        $("#has_permission").data("edit") == 1
                                            ? `<a href="/provider/edit-branch/${staff.id}" class="me-2 edit_staff_btn"><i class="ti ti-edit"></i> </a>`
                                            : ""
                                    }
                                    ${
                                        $("#has_permission").data("delete") == 1
                                            ? `<a href="javascript:void(0);" class="delete_branch_btn" data-id="${staff.id}" data-bs-toggle="modal" data-bs-target="#del_branch"><i
                                                class="ti ti-trash"></i></a>`
                                            : ""
                                    }
                                    </div>
                                </td>
                            </tr>
                            `;
                        });
                    }

                    $("#branchTable tbody").html(tableBody);
                    if (
                        staffs.length != 0 &&
                        !$.fn.DataTable.isDataTable("#branchTable")
                    ) {
                        $("#branchTable").DataTable({
                            ordering: true,
                            language: datatableLang,
                        });
                    }
                }
                $("#tabelSkeletonLoader").hide();
                $("#loader-table").hide();
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
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

    $(document).on("click", ".delete_branch_btn", function () {
        var id = $(this).data("id");
        $("#confirm_branch_delete").data("id", id);
    });

    $(document).on("click", "#confirm_branch_delete", function (event) {
        event.preventDefault();

        var id = $(this).data("id");

        $.ajax({
            url: "/provider/delete-branch",
            type: "POST",
            data: {
                id: id,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                Accept: "application/json",
            },
            dataType: "json",
            success: function (response) {
                if (response.code === 200) {
                    toastr.success(response.message);
                    $("#del_branch").modal("hide");
                    getBranchList();
                }
            },
            error: function (error) {
                toastr.error(error.responseJSON.message);
            },
        });
    });
}

if (pageValue === "provider.editbranch") {
    $(".select2").select2();
    let isDelteBranchImage = 0;

    $(".addHolidayBtn").on("click", function () {
        $("#holidayContainer").append(
            `<div class="row extra-holiday">
                <div class="col-xl-4">
                    <div class="mb-3">
                        <div class=" input-icon position-relative">
                            <input type="date" class="form-control" name="holiday[]" placeholder="dd-mm-yyyy">
                        </div>
                    </div>
                </div>
                <div class="col-xl-1 d-flex align-items-center">
                    <a href="javascript:void(0);" class="text-danger remove-holiday"><i class="ti ti-trash fs-14"></i></a>
                </div>
            </div>`
        );
    });

    $(document).on("click", ".remove-holiday", function () {
        $(this).closest(".extra-holiday").remove();
    });

    $(document).on("click", ".delete_branch_image", function () {
        isDelteBranchImage = 1;
        $("#branch_image").val("");
    });

    $("#branchForm").validate({
        rules: {
            branch_name: {
                required: true,
                maxlength: 100,
                remote: {
                    url: "/provider/branch/check-unique",
                    type: "post",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                        Accept: "application/json",
                    },
                    data: {
                        branch_name: function () {
                            return $("#branch_name").val();
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
            email: {
                required: true,
                email: true,
            },
            branch_address: {
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
            zip_code: {
                required: true,
                maxlength: 6,
                pattern: /^[a-zA-Z0-9]*$/,
            },
            start_hour: {
                required: true,
            },
            end_hour: {
                required: true,
            },
            "working_day[]": {
                required: true,
            },
            "holiday[]": {
                required: false,
            },
            branch_image: {
                extension: "jpeg|jpg|png",
                filesize: 2048,
            },
        },
        messages: {
            branch_name: {
                required: $("#branch_name_error").data("required"),
                maxlength: $("#branch_name_error").data("max"),
                remote: $("#branch_name_error").data("exists"),
            },
            phone_number: {
                required: $("#phone_number_error").data("required"),
                minlength: $("#phone_number_error").data("between"),
                maxlength: $("#phone_number_error").data("between"),
            },
            email: {
                required: $("#email_error").data("required"),
                email: $("#email_error").data("format"),
            },
            branch_address: {
                required: $("#branch_address_error").data("required"),
                maxlength: $("#branch_address_error").data("max"),
            },
            zip_code: {
                required: $("#zip_code_error").data("required"),
                maxlength: $("#zip_code_error").data("max"),
                pattern: $("#zip_code_error").data("char"),
            },
            start_hour: {
                required: $("#start_hour_error").data("required"),
            },
            end_hour: {
                required: $("#end_hour_error").data("required"),
            },
            "working_day[]": {
                required: $("#working_day_sunday_error").data("required"),
            },
            "holiday[]": {
                required: "Holiday is required.",
            },
            state: {
                required: $("#state_error").data("required"),
            },
            city: {
                required: $("#city_error").data("required"),
            },
            country: {
                required: $("#country_error").data("required"),
            },
            branch_image: {
                required: $("#branch_image_error").data("required"),
                extension: $("#branch_image_error").data("extension"),
                filesize: $("#branch_image_error").data("filesize"),
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
    });

    $.validator.addMethod(
        "filesize",
        function (value, element, param) {
            if (element.files.length === 0) return true;
            return element.files[0].size <= param * 1024;
        },
        "File size must be less than {0} KB."
    );

    $("#staffsForm").validate({
        rules: {
            "staffs[]": {
                required: false,
            },
        },
        messages: {
            "staffs[]": {
                required: "Staff is required.",
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
    });

    $("#branch_btn").on("click", function (event) {
        event.preventDefault();

        if ($("#branchForm").valid()) {
            $("#first-field").hide();
            $("#second-field").show();
            $("#progressbar li").eq(0).removeClass("active");
            $("#progressbar li").eq(1).addClass("active");
        }
    });

    $("#staff_prev").on("click", function () {
        $("#second-field").hide();
        $("#first-field").show();
        $("#progressbar li").eq(1).removeClass("active");
        $("#progressbar li").eq(0).addClass("active");
    });

    $("#staff_btn").on("click", function (event) {
        event.preventDefault();

        if ($("#staffsForm").valid()) {
            $("#first-field").hide();
            $("#second-field").show();
            $("#progressbar li").eq(0).removeClass("active");
            $("#progressbar li").eq(1).addClass("active");

            saveBranch();
        }
    });

    function saveBranch() {
        let serviceFormData = $("#branchForm").serializeArray();
        let locationFormData = $("#staffsForm").serializeArray();

        var formData = new FormData();
        [...serviceFormData, ...locationFormData].forEach(function (item) {
            formData.append(item.name, item.value);
        });

        var fileInput = $("#branch_image")[0].files[0];

        if (fileInput) {
            formData.append("branch_image", fileInput);
        }
        formData.append("is_delete_branch_image", isDelteBranchImage);

        $.ajax({
            url: "/provider/save-branch-details",
            type: "POST",
            data: formData,
            enctype: "multipart/form-data",
            contentType: false,
            processData: false,
            cache: false,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $("#staff_btn")
                    .attr("disabled", true)
                    .html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
            },
            success: function (response) {
                $(".error-text").text("");
                $("#staff_btn")
                    .removeAttr("disabled")
                    .html($("#staff_btn").data("save"));
                $(".form-control").removeClass("is-invalid is-valid");
                $(".select2-container").removeClass("is-invalid is-valid");
                if (response.code === 200) {
                    toastr.success(response.message);
                    window.location.href = "/provider/branch";
                }
            },
            error: function (error) {
                $(".error-text").text("");
                $("#staff_btn")
                    .removeAttr("disabled")
                    .html($("#staff_btn").data("save"));
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
    }

    $("#branch_image").on("change", function (event) {
        if ($(this).val() !== "") {
            $(this).valid();
        }
        $("#branch_img_container").show();
        $("#image_preview_container").removeClass("d-none");
        let reader = new FileReader();
        reader.onload = function (e) {
            $("#branch_img_preview").attr("src", e.target.result).show();
        };
        reader.readAsDataURL(event.target.files[0]);
        isDelteBranchImage = 0;
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
    $("#staff").on("change", function () {
        $(this).valid();
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

    $("#zip_code").on("input", function () {
        if ($(this).val().length > 6) {
            $(this).val($(this).val().slice(0, 6));
        }
    });

    $(document).ready(function () {
        var selectedCountry = $("#country").data("country");
        var selectedState = $("#state").data("state");
        getStates(selectedCountry);
        getCities(selectedState);
    });

    function getStates(selectedCountry = "") {
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
                    const stateDropdown = $("#state");
                    response.data.forEach((state) => {
                        stateDropdown.append(
                            `<option value="${state.id}" ${
                                state.id == $("#state").data("state")
                                    ? "selected"
                                    : ""
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

    function getCities(selectedState = "") {
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
                    const stateDropdown = $("#city");
                    response.data.forEach((city) => {
                        stateDropdown.append(
                            `<option value="${city.id}" ${
                                city.id == $("#city").data("city")
                                    ? "selected"
                                    : ""
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

    $("#country").on("change", function () {
        const selectedCountry = $(this).val();
        clearDropdown($("#state"));
        clearDropdown($("#city"));
        getStates(selectedCountry);
    });

    $("#state").on("change", function () {
        const selectedState = $(this).val();
        clearDropdown($("#city"));
        getCities(selectedState);
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
}

if (pageValue === "provider.roles-permissions") {
    $(document).ready(function () {
        listRoles();
    });

    function listRoles() {
        $.ajax({
            url: "/role/list",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "asc",
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
                                        <div class="form-check form-switch d-flex align-items-center">
                                            <input class="form-check-input role_status" ${checkedVal} type="checkbox" role="switch" id="switch-sm" data-id = ${
                                role.id
                            }>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                        ${
                                            $("#has_permission").data("edit") ==
                                            1
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
                                            $("#has_permission").data("edit") ==
                                            1
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
                                    </td>
                                </tr>
                            `;
                        });
                    }

                    $("#roleTable tbody").html(tableBody);

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
                $("#tabelSkeletonLoader").hide();
                $("#loader-table").hide();
                $(".label-loader, .input-loader").hide();
                $(".real-label, .real-input").removeClass("d-none");
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
                        url: "/role/check-unique",
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

                $.ajax({
                    url: "/role/save",
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
            url: "/role/delete",
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
            url: "/role/change-status",
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
            url: "/permission/list",
            type: "POST",
            dataType: "json",
            data: {
                id: id,
                order_by: "asc",
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
            url: "/permission/update",
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
if (pageValue === "provider.ticket" || pageValue === "staff.ticket") {
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
        // Initialize Summernote editor
        $("#summernote").summernote({
            height: 250,
        });
        $("#add_ticket").on("show.bs.modal", function () {
            $("#summernote").summernote("code", "");
            $("#Ticketform")[0].reset(); // Reset the form data
        });
    });
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
                if (currentPath == "/provider/ticket") {
                    window.location.href = "/provider/ticket-details";
                } else {
                    window.location.href = "/staff/ticket-details";
                }
            })
            .catch((error) => {
                console.error("Error storing ticket ID:", error);
            });
    }
    //add
    $(document).ready(function () {
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
                    user_type: "Provider",
                };
                $.ajax({
                    url: "/api/provider/addticket",
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
                            const newTicketHtml = `
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="avatar avatar-xxl me-2">
                                            ${
                                                response.ticketdata
                                                    .profile_image &&
                                                response.ticketdata
                                                    .profile_image !== ""
                                                    ? `<img src="${response.ticketdata.profile_image}" alt="User Profile Image" class="img-fluid rounded-circle profileImagePreview" style="width: 60px; height: 60px;">`
                                                    : response.ticketdata
                                                          .user_type === "User"
                                                    ? `<img src="/assets/img/user-default.jpg" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview" style="width: 60px; height: 60px;">`
                                                    : `<img src="/assets/img/profile-default.png" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview" style="width: 60px; height: 60px;">`
                                            }
                                        </span>
                                        
                                        <div class="mb-2">
                                            <div class="d-flex flex-wrap align-items-center mt-3">
                                                <h6 class="fw-semibold me-2 mb-0">
                                                    <a onclick="storeTicketId(${
                                                        response.ticketdata.id
                                                    })" class="text-decoration-none text-dark">
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
                                                } d-flex align-items-center ms-2 fs-10" data-status="${
                                response.ticketdata.status
                            }">
                                                    <i class="ti ti-circle-filled fs-6 me-1"></i>${
                                                        response.ticketdata
                                                            .ticket_status ??
                                                        "-"
                                                    }
                                                </span>
                                            </div>
                                            
                                            <div class="d-flex flex-wrap align-items-center mt-1">
                                                ${
                                                    response.ticketdata
                                                        .assignee_id
                                                        ? `
                                                <p class="d-flex align-items-center me-3 mb-1 assigneddetails${
                                                    response.ticketdata.id
                                                }">
                                                    <span class="fw-semibold text-muted me-2">Assigned to:</span>
                                                    <img src="${
                                                        response.ticketdata
                                                            .assign_profileimage &&
                                                        response.ticketdata
                                                            .assign_profileimage !==
                                                            ""
                                                            ? response
                                                                  .ticketdata
                                                                  .assign_profileimage
                                                            : "/assets/img/user-default.jpg"
                                                    }" class="rounded-circle me-2" width="24" height="24" alt="img">
                                                    <span class="text-dark fw-semibold ms-1 assigneename">${
                                                        response.ticketdata
                                                            .assignee_name ??
                                                        "-"
                                                    }</span>
                                                </p>`
                                                        : ""
                                                }
                                                
                                                <p class="d-flex align-items-center mb-1 me-2 fs-10">
                                                    <i class="ti ti-calendar-bolt me-1"></i>
                                                    <span class="fw-semibold text-muted me-1">Updated:</span> ${
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
                                        <div class="mb-3">
                                            <a href="${
                                                $("#ticket-list").data(
                                                    "user_type"
                                                ) == 2
                                                    ? `/provider/ticket-details/${response.ticketdata.ticket_id}`
                                                    : `/staff/ticket_details/${response.ticketdata.ticket_id}`
                                            }" class="fs-14 bg-primary px-2 py-1 text-light mt-1 fw-bold text-dark d-flex align-items-center me-4 rounded">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        </div>
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
if (
    pageValue === "provider.ticketdetails" ||
    pageValue === "staff.ticket_details"
) {
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
}
