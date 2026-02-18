var pageValue = $("body").data("provider");
if (pageValue === "staff.calendar") {
    var staffid = $(".auth_id").val();
    document.addEventListener("DOMContentLoaded", function () {
        // Fetch user data
        fetchdata(staffid)
    });

    function fetchdata(staffid){
        fetch("/api/provider/getstafflist", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                date: new Date().toISOString().split("T")[0],
            }),
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json(); // Parse JSON
            })
            .then((users) => {
                // Validate that `users` is an array
                if (!Array.isArray(users)) {
                    throw new Error("The users response is not an array.");
                }

                // Map users to FullCalendar resources
                const userColumns = users.map((user) => {
                    if (!user.id || !user.name) {
                        console.warn(
                            "User data is missing required fields:",
                            user
                        );
                    }
                    return {
                        title: user.name || "Unnamed User", // Default name if missing
                        id: user.id || Math.random().toString(36).substr(2, 9), // Generate unique ID if missing
                    };
                });
                // Initialize FullCalendar
                const calendarEl = document.getElementById("calendar");
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: "dayGridMonth",
                    headerToolbar: {
                        left: "prev,next today",
                        center: "title",
                        right: "dayGridMonth,timeGridWeek,timeGridDay",
                    },
                    dateClick: function (info) {
                        const selectedDate = new Date(info.dateStr);
                        const today = new Date();

                        today.setHours(0, 0, 0, 0);

                        if (selectedDate < today) {
                            alert("Please select a future date.");
                            return;
                        }

                        // Format the selected date as DD-MM-YYYY
                        const day = String(selectedDate.getDate()).padStart(
                            2,
                            "0"
                        );
                        const month = String(
                            selectedDate.getMonth() + 1
                        ).padStart(2, "0"); // Month is 0-indexed
                        const year = selectedDate.getFullYear();
                        const formattedDate = `${day}-${month}-${year}`;

                        document.getElementById(
                            "selected-date-text"
                        ).innerText = `Date: ${formattedDate}`;

                        const viewType = info.view.type;

                        if (
                            viewType === "timeGridWeek" ||
                            viewType === "timeGridDay"
                        ) {
                            const selectedTime = selectedDate
                                .toTimeString()
                                .split(" ")[0]; // Format as HH:MM:SS
                            document.getElementById(
                                "selected-time-text"
                            ).innerText = `Time: ${selectedTime}`;
                        } else {
                            document.getElementById(
                                "selected-time-text"
                            ).innerText = "";
                        }

                        // Set the formatted date to the hidden input
                        document.getElementById("selected-date").value =
                            formattedDate;

                        // Set the selected time to the hidden time input if necessary
                        document.getElementById("selected-time").value =
                            viewType === "timeGridWeek" ||
                            viewType === "timeGridDay"
                                ? selectedDate.toTimeString().split(" ")[0]
                                : "";

                        const appointmentModal = new bootstrap.Offcanvas(
                            document.getElementById("appointmentModal")
                        );
                        appointmentModal.show();
                    },
                    events: function (
                        fetchInfo,
                        successCallback,
                        failureCallback
                    ) {
                        // Fetch events dynamically with provider_id
                        $.ajax({
                            url: "/api/getstaffBookings",
                            type: "GET",
                            data: { staffid: staffid },
                            success: function (response) {
                                successCallback(response);
                            },
                            error: function () {
                                toastr.error(
                                    "Failed to load events. Please try again."
                                );
                                failureCallback();
                            },
                        });
                    },
                    moreLinkClick: "popover",
                    eventContent: function (info) {
                        return {
                            html: `
                                <div style="background-color: ${
                                    info.event.backgroundColor
                                }; padding: 5px; border-radius: 4px; color: white;">
                                    <b>${info.event.title}</b><br/>
                                    <small>${info.event.start.toLocaleString()}</small>
                                </div>
                            `,
                        };
                    },
                    eventClick: function (info) {
                        // Prevent the default action
                        info.jsEvent.preventDefault();
                        // Display event details in the modal
                        const eventTitle = info.event.title;
                        const eventDate = info.event.start
                            .toISOString()
                            .split("T")[0];
                        const dateTime = info.event.start;
                        const eventtime = dateTime
                            .toISOString()
                            .split("T")[1]
                            .split(".")[0];
                        const provider = info.event.extendedProps.provider;
                        const user = info.event.extendedProps.user;
                        const location = info.event.extendedProps.location;
                        const amount = info.event.extendedProps.amount;
                        const status = info.event.extendedProps.status;
                        const statusNo = info.event.extendedProps.status_no;
                        const phone = info.event.extendedProps.phone;
                        const email = info.event.extendedProps.email;
                        const color = info.event.backgroundColor;
                        const id = info.event.id;
                        const slotTime = info.event.extendedProps.slot_time;
                        const userimage = info.event.extendedProps.userimage;
                        // Populate modal content
                        document.getElementById("service-name").innerText =
                            eventTitle ?? "";
                        // document.getElementById("product").innerText =
                        //     eventTitle ?? "";
                        document.getElementById("appointment-date").innerText =
                            formatDate(eventDate);
                        const appointmentDiv = document.getElementById("appointment-time").parentElement;
                        if (slotTime && slotTime.trim() !== "") {
                            document.getElementById("appointment-time").innerText = slotTime;
                            appointmentDiv.classList.remove("d-none"); // Show the div
                        } else {
                            appointmentDiv.classList.add("d-none"); // Hide the div if empty
                        }
                        document.getElementById("client-name").innerText = user;
                        // document.getElementById("provider").innerText =
                        //     provider;
                        // document.getElementById("client-location").innerText =
                        //     location;
                        document.getElementById("total").innerText =
                            amount ?? "";
                        document.getElementById(
                            "appointment-status"
                        ).innerText = status;
                        document.getElementById("client-phone").innerText =
                            phone ?? "";
                        document.getElementById("client-email").innerText =
                            email;
                        const element = document.getElementById("color"); // Select the element
                        if (element) {
                            element.style.backgroundColor = color; // Apply the color
                        }
                        const cancelButton =
                            document.querySelector(".cancelbooking");
                        cancelButton.setAttribute("data-id", id);
                        if (statusNo == 1 || statusNo == 2) {
                            const cardFooter =
                            document.querySelector(".card-footer");
                            // Hide the element
                            if (cardFooter) {
                                cardFooter.style.display = "block";
                            }
                        } else {
                            const cardFooter =
                                document.querySelector(".card-footer");
                            // Hide the element
                            if (cardFooter) {
                                cardFooter.style.display = "none";
                            }
                        }
                        const avatarImage =
                            document.querySelector(".img-fluid.avatar");
                        if (avatarImage) {
                            const defaultimage = "/assets/img/user-default.jpg";
                            const profileImage =
                                userimage && userimage !== "N/A"
                                    ? `${userimage}`
                                    : defaultimage;
                            avatarImage.src = profileImage; // Set new image path
                        }
                        // Show modal
                        var myModal = new bootstrap.Offcanvas(
                            document.getElementById("calendarModal")
                        );
                        myModal.show();
                    },
                    dayMaxEventRows: 2, // Maximum number of events to show per day
                });

                calendar.render();
            })
            .catch((error) => {
                console.error("Error fetching user data:", error);
            });
    }

    $(document).on("click", ".cancelbooking", function (e) {
        var bookingId = $(this).data("id");
        $("#cancelbooking").attr("data-id", bookingId);
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
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code === 200) {
                    fetchdata(staffid);
                    const calendarModal =
                        document.getElementById("calendarModal");
                    if (calendarModal) {
                        const bootstrapOffcanvas =
                            bootstrap.Offcanvas.getInstance(calendarModal);
                        if (bootstrapOffcanvas) {
                            bootstrapOffcanvas.hide(); // Hide the offcanvas
                        }
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
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                toastr.error(
                    "An error occurred while trying to cancel booking."
                );
            },
        });
    });
}

// Utility function to format dates
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
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

function fetchCustomer() {
    var userId = $("#user_id").val();
    var auth_user_id = $("#auth_user_id").val();

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
            url: "/get-customer",
            type: "POST",
            data: {
                user_id: userId,
                auth_user_id: auth_user_id,
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

                // Update other fields
                $("#first_name").val(response.staffInfo.first_name);
                $("#last_name").val(response.staffInfo.last_name);
                $("#user_email").val(response.staffInfo.email);
                $("#user_phone").val(response.staffInfo.phone_number);
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
            $("#serviceInfo").html("");
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
    $(".select2").select2();
    $("#appointmentModal").on("hidden.bs.offcanvas", function () {
        $("#payment-form")[0].reset();

        $('#customerDetails').empty();
        $('#serviceInfo').empty();
        $('#slot-input').empty();
        $("#payment-form .form-control").removeClass("is-invalid is-valid");
        $("#payment-form .invalid-feedback").remove();
    });

    
    $("#payment-form").validate({
        rules: {
            user_id: {
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

    $("#pay-btn").on("click", function (event) {
        event.preventDefault();

        if ($("#payment-form").valid()) {
            let paymentFormData = $("#payment-form").serializeArray();
            let finalFormData = new FormData();

            finalFormData.append(
                "_token",
                $('meta[name="csrf-token"]').attr("content")
            );

            paymentFormData.forEach(function (item) {
                finalFormData.append(item.name, item.value);
            });

            $.ajax({
                url: "/staff/payment",
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
            .done((response) => {
                $(".error-text").text("");
                $(".form-control").removeClass("is-invalid");
                $(".pay-btn").removeAttr("disabled");
                $(".pay-btn").html("Add Appointment");
            
                if (response.code === 200) {
                    toastr.success(response.message);
            
                    const offcanvas = document.getElementById("appointmentModal");
                    const bootstrapOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
                    bootstrapOffcanvas.hide();
            
                    fetchdata(staffid);

                }
            })            
                .fail((error) => {
                    $(".error-text").text("");
                    $(".form-control").removeClass("is-invalid");
                    $(".pay-btn").removeAttr("disabled");
                    $(".pay-btn").html("Add Appointment");

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