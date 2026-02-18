var pageValue = $("body").data("provider");
if (pageValue === 'provider.calendar') {
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize FullCalendar
        fetchData();
    });

    function fetchData() {
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            dayMaxEventRows: true,
            views: {
                dayGridMonth: {
                    dayMaxEventRows: 2 // Limit events for month view
                },
                timeGridWeek: {
                    dayMaxEventRows: 2 // Limit events for week view
                },
                timeGridDay: {
                    dayMaxEventRows: 2 // Limit events for day view
                }
            },
            dateClick: function (info) {
                const selectedDate = new Date(info.dateStr);
                const today = new Date();

                today.setHours(0, 0, 0, 0);

                if (selectedDate < today) {
                    alert("Please select a future date.");
                    return;
                }

                const day = String(selectedDate.getDate()).padStart(2, "0");
                const month = String(selectedDate.getMonth() + 1).padStart(2, "0");
                const year = selectedDate.getFullYear();
                const formattedDate = `${day}-${month}-${year}`;

                document.getElementById("selected-date-text").innerText = `Date: ${formattedDate}`;

                const viewType = info.view.type;

                if (viewType === "timeGridWeek" || viewType === "timeGridDay") {
                    const selectedTime = selectedDate.toTimeString().split(" ")[0];
                    document.getElementById("selected-time-text").innerText = `Time: ${selectedTime}`;
                } else {
                    document.getElementById("selected-time-text").innerText = "";
                }

                document.getElementById("selected-date").value = formattedDate;
                document.getElementById("selected-time").value =
                    viewType === "timeGridWeek" || viewType === "timeGridDay"
                        ? selectedDate.toTimeString().split(" ")[0]
                        : "";

                const providerAppointmentModal = new bootstrap.Offcanvas(
                    document.getElementById("providerAppointmentModal")
                );
                providerAppointmentModal.show();
            },
            events: function (fetchInfo, successCallback, failureCallback) {
                // Fetch both bookings and leads
                Promise.all([
                    // Fetch bookings
                    $.ajax({
                        url: '/api/providerbookings',
                        type: 'POST',
                        data: {
                            provider_id: $('body').data('authid')
                        },
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        }
                    }),
                    // Fetch leads
                    $.ajax({
                        url: '/api/list/leads',
                        type: 'POST',
                        dataType: 'json',
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        },
                        data: {
                            provider_id: $('body').data('authid')
                        }
                    })
                ]).then(function(responses) {

                    const bookings = responses[0];
                    const leadsResponse = responses[1];

                    // Process bookings
                    const bookingEvents = Array.isArray(bookings) ? bookings : [];

                    // Process leads - NEW IMPROVED VERSION
                    let leadEvents = [];
                    if (leadsResponse && leadsResponse.data.provider_forms_inputs && leadsResponse.data.provider_forms_inputs.data) {

                        leadEvents = leadsResponse.data.provider_forms_inputs.data.map(lead => {
                            try {
                                // Parse the date - handle both ISO format and your custom format
                                let eventDate;
                                if (lead.created_at.includes('T')) {
                                    eventDate = new Date(lead.created_at);
                                } else {
                                    // If date is in format "26/06/2025 09:04 AM"
                                    const [datePart, timePart] = lead.created_at.split(' ');
                                    const [day, month, year] = datePart.split('/');
                                    eventDate = new Date(`${year}-${month}-${day}T${timePart}`);
                                }

                                if (isNaN(eventDate.getTime())) {
                                    console.warn("Invalid date for lead:", lead.id, lead.created_at);
                                    return null;
                                }

                                return {
                                    id: `lead_${lead.id}`,
                                    title: `Lead #${lead.user_form_inputs_id}`,
                                    status: getStatusText(lead.status),
                                    start: eventDate,
                                    allDay: true,
                                    backgroundColor: getStatusColor(lead.status),
                                    extendedProps: {
                                        type: 'lead',
                                        user_lead_id: lead.user_form_inputs_id,
                                        quote: lead.quote,
                                        description: lead.description,
                                        status: getStatusText(lead.status),
                                        customer_profile_image: lead.customer_profile_image,
                                        user_form_input: lead.user_form_input,
                                        created_at: lead.created_at // Keep original for debugging
                                    }
                                };
                            } catch (error) {
                                console.error("Error processing lead:", lead, error);
                                return null;
                            }
                        }).filter(event => event !== null);
                    } else {
                        console.warn("No leads data found in response:", leadsResponse);
                    }

                    // Combine both event types
                    const allEvents = [...bookingEvents, ...leadEvents];

                    // Add test event if no real events exist
                    if (allEvents.length === 0) {
                        console.warn("No events found, adding test event");
                        allEvents.push({
                            id: 'test-event',
                            title: 'TEST EVENT (No Data)',
                            start: new Date(),
                            allDay: true,
                            backgroundColor: 'red'
                        });
                    }

                    successCallback(allEvents);
                }).catch(function(error) {
                    console.error("Error fetching events:", error);
                    toastr.error("Failed to load events. Please try again.");
                    failureCallback({ message: "Failed to load events" });
                });
            },
            moreLinkClick: 'popover',
            eventContent: function (info) {
                const event = info.event;
                const start = event.start;
                if (event.extendedProps.type === 'lead') {
                    // Lead event styling
                    const formattedDate = formatDate(start);
                    return {
                        html: `
                            <div style="background-color: ${event.backgroundColor};
                                 padding: 5px; border-radius: 4px; color: white;
                                 margin: 2px; font-size: 0.8em;">
                                <b>${event.title}</b><br/>
                                <b>${event.extendedProps.status}</b><br/>
                                <small>${formattedDate}</small>
                            </div>
                        `
                    };
                } else {
                    // Booking event styling
                    const year = start.getFullYear();
                    const month = String(start.getMonth() + 1).padStart(2, '0');
                    const day = String(start.getDate()).padStart(2, '0');
                    const fromtime = event.extendedProps.fromtime || "";
                    const totime = event.extendedProps.totime || "";
                    let startdate = `${year}-${month}-${day}`;

                    if (fromtime !== '') {
                        const timeRange = `${fromtime} - ${totime}`;
                        startdate = `${year}-${month}-${day} ${timeRange}`;
                    }

                    return {
                        html: `
                            <div style="background-color: ${event.backgroundColor}; padding: 5px; border-radius: 4px; color: white;">
                                <b>${event.title}</b><br/>
                                <small>${startdate}</small>
                            </div>
                        `
                    };
                }
            },
            eventClick: function (info) {
                info.jsEvent.preventDefault();

                // Get the modal elements
                const modal = document.getElementById('calendarModal');
                const modalTitle = document.getElementById('calendarModalLabel');
                const serviceTitle = document.getElementById('service-title');
                const appointmentDate = document.getElementById('appointment-date');
                const appointmentTime = document.getElementById('appointment-time');
                const appointmentTimeParent = appointmentTime.parentElement;
                const clientName = document.getElementById('client-name');
                const clientStaff = document.getElementById('client-staff');
                const clientLocation = document.getElementById('client-location');
                const totalAmount = document.getElementById('total');
                const appointmentStatus = document.getElementById('appointment-status');
                const clientPhone = document.getElementById('client-phone');
                const clientEmail = document.getElementById('client-email');
                const colorElement = document.getElementById('color');
                const cardFooter = document.querySelector('.card-footer');
                const avatarImage = document.querySelector(".img-fluid.avatar");
                const formInputsContainer = document.getElementById('form-inputs-container');
                const customLink = document.getElementById('custom-link');
                if (customLink) {
                    customLink.href = '#';
                    customLink.style.display = 'none';
                }

                // Reset modal state
                appointmentTimeParent.classList.add("d-none");
                if (cardFooter) cardFooter.style.display = 'none';
                if (formInputsContainer) formInputsContainer.innerHTML = '';

                if (info.event.extendedProps.type == 'lead') {

                    // LEAD CLICK HANDLER
                    const lead = info.event.extendedProps;
                    const eventDate = new Date(info.event.start);
                    const day = String(eventDate.getDate()).padStart(2, '0');
                    const month = String(eventDate.getMonth() + 1).padStart(2, '0');
                    const year = eventDate.getFullYear();
                    const formattedDate = `${day}-${month}-${year}`;

                    modalTitle.innerText = 'Lead Details';

                    // Populate lead info

                    appointmentDate.innerText = formattedDate;

                    // Set customer name
                    const customerName = lead.user_form_input?.user?.name || 'Unknown Customer';
                    clientName.innerText = customerName;

                    // Set service/category info
                    const categoryName = lead.user_form_input?.category?.name || 'No Category';
                    clientLocation.innerText = lead.location || '-';
                    serviceTitle.innerText = `${categoryName}`;
                    // Set basic info
                    totalAmount.innerText = lead.quote ? `$${lead.quote}` : 'Not quoted';
                    appointmentStatus.innerText = lead.status || '';

                    // Set contact info if available
                    const user = lead.user_form_input?.user || {};
                    clientEmail.innerText = user.email || '';
                    clientPhone.innerText = user.phone_number || '';

                    // Set color
                    if (colorElement) {
                        colorElement.style.backgroundColor = info.event.backgroundColor;
                    }

                    // Set customer profile image
                    if (avatarImage) {
                        avatarImage.src = lead.customer_profile_image || "/assets/img/profile-default.png";
                    }

                    // Hide unnecessary elements for leads
                    if (clientStaff) clientStaff.innerText = '-';
                    if (customLink) {
                        const leadId = lead.id || '';
                        customLink.href = `/provider/leadsinfo`;
                        customLink.style.display = 'inline-block';
                    }

                } else {
                    // BOOKING CLICK HANDLER (your original code)
                    const eventTitle = info.event.title;
                    const start = info.event.start;
                    const year = start.getFullYear();
                    const month = String(start.getMonth() + 1).padStart(2, '0');
                    const day = String(start.getDate()).padStart(2, '0');
                    const eventDate = `${day}-${month}-${year}`;

                    modalTitle.innerText = 'Booking Details';

                    const fromtime = info.event.extendedProps.fromtime || "";
                    const totime = info.event.extendedProps.totime || "";
                    if (fromtime !== '') {
                        const timeRange = `${fromtime} - ${totime}`;
                    }

                    const provider = info.event.extendedProps.provider;
                    const user = info.event.extendedProps.user;
                    const location = info.event.extendedProps.branch;
                    const amount = info.event.extendedProps.amount;
                    const status = info.event.extendedProps.status;
                    const statusNo = info.event.extendedProps.status_no;
                    const staffname = info.event.extendedProps.staffname;
                    const phone = info.event.extendedProps.phone;
                    const email = info.event.extendedProps.email;
                    const color = info.event.backgroundColor;
                    const id = info.event.id;
                    const slotTime = info.event.extendedProps.slot_time;
                    const userimage = info.event.extendedProps.userimage;

                    // Populate booking info
                    serviceTitle.innerText = eventTitle ?? '';
                    appointmentDate.innerText = eventDate;

                    if (slotTime && slotTime.trim() !== "") {
                        appointmentTime.innerText = slotTime;
                        appointmentTimeParent.classList.remove("d-none");
                    }

                    clientName.innerText = user;
                    if (clientStaff) clientStaff.innerText = staffname;
                    clientLocation.innerText = location;
                    totalAmount.innerText = amount ?? '';
                    appointmentStatus.innerText = status;
                    clientPhone.innerText = phone ?? '';
                    clientEmail.innerText = email;

                    if (colorElement) {
                        colorElement.style.backgroundColor = color;
                    }

                    const cancelButton = document.querySelector('.cancelbooking');
                    if (cancelButton) cancelButton.setAttribute('data-id', id);

                    if (statusNo == 1 || statusNo == 2) {
                        if (cardFooter) cardFooter.style.display = 'block';
                    }

                    if (avatarImage) {
                        const defaultimage = "/assets/img/profile-default.png";
                        const profileImage = userimage && userimage !== "N/A"
                            ? `${userimage}`
                            : defaultimage;
                        avatarImage.src = profileImage;
                    }
                }

                // Show modal
                var myModal = new bootstrap.Offcanvas(modal);
                myModal.show();
            }
        });

        calendar.render();
    }

    // Helper functions
    function getStatusText(status) {
        const statusMap = {
            1: 'New',
            2: 'Accepted',
            3: 'Rejected'
        };
        return statusMap[status] || 'Unknown';
    }

    function getStatusColor(status) {
        const colorMap = {
            1: '#3498db',
            2: '#2ecc71',
            3: '#e74c3c'
        };
        return colorMap[status] || '#95a5a6';
    }

    function getUserStatusText(status) {
        const userStatusMap = {
            1: 'Active',
            0: 'Inactive'
        };
        return userStatusMap[status] || 'Unknown';
    }

    function formatDate(date) {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}-${month}-${year}`;
    }

    $(document).on('click', '.cancelbooking', function(e) {
        var bookingId = $(this).data('id');
        $("#cancelbooking").attr('data-id',bookingId);
    });
    $(document).on('click', '#cancelbooking', function(e) {
        e.preventDefault();
        var type = $(this).data('type');
        var id = $(this).data('id');
        $.ajax({
            url: '/api/updatebookingstatus',
            type: 'POST',
            data: {
                id: id,status : type
            },
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                $("#cancelbooking")
                    .attr("disabled", true)
                    .html(
                        '<span class="spinner-border spinner-border-sm align-middle" role="status" aria-hidden="true"></span>'
                    );
            },
            complete: function () {
                $("#cancelbooking").attr("disabled", false).html($('#cancelbooking').data('yes_cancel'));
            },
            success: function(response) {
                fetchData();
                if (response.code=== 200) {
                    const calendarModal = document.getElementById('calendarModal');
                    if (calendarModal) {
                        const bootstrapOffcanvas = bootstrap.Offcanvas.getInstance(calendarModal);
                        if (bootstrapOffcanvas) {
                            bootstrapOffcanvas.hide(); // Hide the offcanvas
                        }
                    }
                    var msg=response.message;
                    if (languageId === 2) {
                        loadJsonFile(response.message, function (langtst) {
                            msg=langtst;
                            toastr.success(msg);
                        });
                    }else{
                        toastr.success(msg);
                    }
                    $('#cancel_appointment').modal('hide');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                toastr.error('An error occurred while trying to cancel booking.');
            }
        });
    });
}

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
    const langCode = $("body").data("lang");
    const jsonFilePath = '/lang/' + langCode + '.json';
    $.getJSON(jsonFilePath, function (data) {
        let lang = searchInJson(searchKey, data);
        callback(lang);
    });
}

function fetchProviderCustomer() {
    var userId = $("#user_id").val();

    if (userId) {
        // Show loader
        $("#customerDetails").html(`
            <div class="text-center my-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);

        $.ajax({
            url: "/get-customer-provider",
            type: "POST",
            data: {
                user_id: userId,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.error) {
                    toastr.error(response.error);
                    $("#customerDetails").html("");
                } else {
                    const customerCard = `
                    <div class="card shadow-sm">
                        <div class="d-flex align-items-center p-3">
                            <img
                                src="${response.userInfo.profile_image}"
                                alt="Profile"
                                class="rounded-circle me-3"
                                style="width: 48px; height: 48px; object-fit: cover;"
                            />
                            <div>
                                <h6 class="mb-0 fw-semibold">${response.userInfo.first_name} ${response.userInfo.last_name}</h6>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="mb-2 d-flex gap-1">
                                <label class="text-muted small mb-1">Username:</label>
                                <div>${response.userInfo.name}</div>
                            </div>
                            <div class="mb-2 d-flex gap-1">
                                <label class="text-muted small mb-1">Phone:</label>
                                <div>${response.userInfo.phone_number}</div>
                            </div>
                            <div class="mb-2 d-flex gap-1">
                                <label class="text-muted small mb-1">E-mail:</label>
                                <div>${response.userInfo.email}</div>
                            </div>
                        </div>
                    </div>
                `;
                    $("#customerDetails").html(customerCard);
                }
            },
            error: function () {
                toastr.error("Error fetching customer details.");
                $("#customerDetails").html(""); // Clear loader on error
            },
        });
    } else {
        $("#customerDetails").html(""); // Clear the section if no user is selected
    }
}

function fetchBranchStaff() {
    var branchId = $("#branch_id").val();

    if (branchId) {
        $("#branchDetails").html(`
            <div class="text-center my-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);

        $.ajax({
            url: "/get-branch",
            type: "POST",
            data: {
                branch_id: branchId,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.error) {
                    toastr.error(response.error);
                    $("#branchDetails").html("");
                    $("#staff_id").html('<option value="">Select Staff</option>');
                } else {
                    // Branch Details
                    const branchCard = `
                        <div class="card shadow-sm">
                            <div class="card-body pt-0">
                                <div class="mb-2 d-flex gap-1">
                                    <label class="text-muted small mb-1">Branch Name:</label>
                                    <div>${response.branch.name}</div>
                                </div>
                                <div class="mb-2 d-flex gap-1">
                                    <label class="text-muted small mb-1">Phone Number:</label>
                                    <div>${response.branch.phone_number}</div>
                                </div>
                                <div class="mb-2 d-flex gap-1">
                                    <label class="text-muted small mb-1">E-mail:</label>
                                    <div>${response.branch.email}</div>
                                </div>
                            </div>
                        </div>
                    `;
                    $("#branchDetails").html(branchCard);

                    // Populate Staff Select Dropdown
                    let staffOptions = '<option value="">Select Staff</option>';
                    response.staff.forEach((staff) => {
                        staffOptions += `<option value="${staff.id}">${staff.name}</option>`;
                    });
                    $("#staff_id").html(staffOptions);
                }
            },
            error: function () {
                toastr.error("Error fetching customer details.");
                $("#branchDetails").html(""); // Clear loader on error
            },
        });
    } else {
        $("#branchDetails").html(""); // Clear the section if no user is selected
    }
}

function fetchStaffService() {
    var staffId = $("#staff_id").val();

    if (!staffId) {
        $("#service_id").html('<option value="">Select Service</option>');
        $("#staffDetails").html(""); // Clear staff details
        return;
    }

    $("#staffDetails").html(`
        <div class="text-center my-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);

    $.ajax({
        url: "/fetch-staff-service", // Update this to match your actual route
        type: "POST",
        data: { staff_id: staffId },
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.error) {
                toastr.error(response.error);
                $("#service_id").html('<option value="">Select Service</option>');
                $("#staffDetails").html(""); // Clear staff details
            } else {
                // Display Staff Details
                const staffCard = `
                    <div class="card shadow-sm">
                        <div class="card-body pt-0">
                            <div class="mb-2 d-flex gap-1">
                                <label class="text-muted small mb-1">Name:</label>
                                <div>${response.staff.name}</div>
                            </div>
                            <div class="mb-2 d-flex gap-1">
                                <label class="text-muted small mb-1">Phone:</label>
                                <div>${response.staff.phone_number}</div>
                            </div>
                            <div class="mb-2 d-flex gap-1">
                                <label class="text-muted small mb-1">E-mail:</label>
                                <div>${response.staff.email}</div>
                            </div>
                        </div>
                    </div>
                `;
                $("#staffDetails").html(staffCard);
            }
        },
        error: function () {
            toastr.error("Error fetching staff details.");
            $("#staffDetails").html(""); // Clear staff details on error
        },
    });
}

function fetchServices() {
    var branchIds = [];
    $(".branch_id").each(function () {
        branchIds.push($(this).val()); // Push each branch_id to the array
    });
    var staffId = $("#auth_user_id").val();
    var serviceId = $("#service_id").val();
    var selectedDate = $("#selected-date").val();

    const loader = `
        <div class="d-flex justify-content-center align-items-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    $("#serviceInfo").html(loader);

    $.ajax({
        url: "/get-staff-slot",
        type: "POST",
        data: {
            branch_ids: branchIds,
            staff_id: staffId,
            service_id: serviceId,
            selected_date: selectedDate,
        },
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            // Clear the loader
            $("#slot-input").empty();
            $("#serviceInfo").empty();

            if ($.isEmptyObject(response.slot)) {
                $("#slot-input").append(
                    '<div class="col-12 fw-bold mt-3"><p>No slots available at this moment</p></div>'
                );
            } else if (response.slot_message) {
                $("#slot-input").append(
                    '<div class="col-12 fw-bold mt-3"><p>Please select a current or future date.</p></div>'
                );
            } else {
                $.each(response.slot, function (index, slot) {
                    var disabledClass =
                        slot.slot_status === "no" ? "disable" : "";
                    var slotHtml = `
                        <div class="col-lg-4 col-md-6">
                            <div class="time-item ${disabledClass}" id="time-item-${
                        slot.id
                    }" onclick="selectRadioSlot(${slot.id})">
                                <input type="radio" name="slot_id" id="slot_${
                                    slot.id
                                }" value="${
                        slot.id
                    }" class="slot-radio" hidden ${
                        disabledClass ? "disabled" : ""
                    }>
                                <h6 class="fs-12 fw-medium">${
                                    slot.source_values
                                }</h6>
                            </div>
                        </div>
                    `;
                    $("#slot-input").append(slotHtml);
                });
            }

            var sourceName = response.service_info.source_name;
            var sourceCode = response.service_info.source_code;
            var sourcePrice = response.service_info.source_price;
            var priceType = response.service_info.price_type;

            $("#total_amount").val(response.service_info.source_price);

            var cardContent = `
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <span class="fw-bold">${sourceName} (${sourceCode})</span>
                        </h5>
                        <p class="card-text">
                            <span class="fw-bold">${sourcePrice} / ${priceType}</span>
                        </p>
                    </div>
                </div>
            `;

            $("#serviceInfo").html(cardContent);
        },
        error: function (xhr) {
            $("#slot-input").empty();
        },
    });
}

function selectRadioSlot(slotId) {
    document.querySelectorAll(".slot-radio").forEach((radio) => {
        radio.checked = false;
    });

    document.getElementById("slot_" + slotId).checked = true;

    document.querySelectorAll(".time-item").forEach((item) => {
        item.classList.remove("selected");
    });

    document.getElementById("time-item-" + slotId).classList.add("selected");
}

$(document).ready(function () {

    $("#providerAppointmentModal").on("hidden.bs.offcanvas", function () {
        $("#providerCalenderBookingForm")[0].reset();

        $('#customerDetails').empty();
        $('#branchDetails').empty();
        $('#staffDetails').empty();
        $('#serviceInfo').empty();
        $('#slot-input').empty();
        $("#user_id").val("");
        $("#branch_id").val("");
        $("#staff_id").val("");
        $("#providerCalenderBookingForm .form-control").removeClass("is-invalid is-valid");
        $("#providerCalenderBookingForm .invalid-feedback").remove();
    });



    $("#providerCalenderBookingForm").validate({
        rules: {
            user_id: {
                required: true,
            },
            branch_id: {
                required: true,
            },
            staff_id: {
                required: true,
            },
            service_id: {
                required: true,
            },
        },
        messages: {
            user_id: {
                required: "Please select a customer required.",
            },
            branch_id: {
                required: "Please select a branch required.",
            },
            staff_id: {
                required: "Please select a staff required.",
            },
            service_id: {
                required: "Please select a service required.",
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
        }
    });

    $("#pay-btn-p").on("click", function (event) {
        event.preventDefault();

        if ($("#providerCalenderBookingForm").valid()) {
            let paymentFormData = $("#providerCalenderBookingForm").serializeArray();
            let finalFormData = new FormData();

            finalFormData.append(
                "_token",
                $('meta[name="csrf-token"]').attr("content")
            );

            paymentFormData.forEach(function (item) {
                finalFormData.append(item.name, item.value);
            });

            $.ajax({
                url: "/provider/calender/booking",
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
                    $(".pay-btn-p").attr("disabled", true);
                    $(".pay-btn-p").html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
                },
            })
                .done((response) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".pay-btn-p").removeAttr("disabled");
                    $(".pay-btn-p").html("Add Appointment");

                    if (response.code === 200) {
                        toastr.success(response.message);

                        const offcanvas = document.getElementById("providerAppointmentModal");
                        const bootstrapOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
                        bootstrapOffcanvas.hide();
                        fetchData();

                    }
                })
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".pay-btn-p").removeAttr("disabled");
                    $(".pay-btn-p").html("Add Appointment");

                    if (error.status == 422) {
                        $.each(error.responseJSON, function (key, val) {
                            $("#" + key).addClass("is-invalid");
                            $("#" + key + "_error").text(val[0]);
                        });
                    } else {
                        toastr.error(error.responseJSON.message);
                    }
                });
        } else {
            toastr.error("Please fill all required fields correctly.");
        }
    });
});
