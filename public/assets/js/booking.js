var pageValue = $("body").data("frontend");

function myreFunction(bgd) {
    const productSlug = bgd; // Use product_slug instead of product_id

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
}

let bookingVerifyUserId = null;

$(document).ready(function () {
    $("#bookServiceButton").click(function (e) {
        e.preventDefault();
        const productSlug = $("#product_slug").val(); // Use product_slug instead of product_id
        $(".book-btn").text("Please Wait...").prop("disabled", true);
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
            complete: function () {
                $(".book-btn").text("Book Service").prop("disabled", false);
            },
        });
    });

    $(".bookServicelistButton").click(function (e) {
        e.preventDefault();
        const productSlug = $("#product_slug").val(); // Use product_slug instead of product_id

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
});

if (
    pageValue === "user.booking.location.service_booking" ||
    pageValue === "user.booking.service_booking" ||
    pageValue === "payment.two"
) {
    document.addEventListener("DOMContentLoaded", function () {
        let today = new Date();
        let day = String(today.getDate()).padStart(2, "0");
        let month = String(today.getMonth() + 1).padStart(2, "0"); // Months are zero-indexed
        let year = today.getFullYear();
        let selectedDate = `${day}-${month}-${year}`;
        var serviceId = $("#service_id").val();
        $("#selected_date").val(selectedDate);
        fetchOnlySlot(serviceId, selectedDate);
    });

    // booking Datetimepicker
    if ($(".bookingDatepic").length > 0) {
        $(".bookingDatepic")
            .datetimepicker({
                format: "DD-MM-YYYY",
                keepOpen: true,
                inline: true,
                icons: {
                    up: "fas fa-angle-up",
                    down: "fas fa-angle-down",
                    next: "fas fa-angle-right",
                    previous: "fas fa-angle-left",
                },
                minDate: moment(), // Disables past dates
            })
            .on("dp.change", function (event) {
                var selectedDate = event.date.format("DD-MM-YYYY");
                $("#selected_date").val(selectedDate);

                var serviceId = $("#service_id").val();
                let branchId = $('input[name="branch_id"]:checked').val();
                let staffId = $('input[name="staff_id"]:checked').val();

                fetchSlot(branchId, staffId, serviceId, selectedDate);
            });
    }

    function fetchSlot(branchId, staffId, serviceId, selectedDate) {
        $("#slot-inut").empty();
        $(".slotLoader-skaliaton").append(
            '<div><div class="skeleton chat-skeleton label-loader mb-2 mt-2"></div> <div class="skeleton chat2-skeleton label-loader"></div> </div>'
        );

        $.ajax({
            url: "/get-slot",
            type: "POST",
            data: {
                branch_id: branchId,
                staff_id: staffId,
                service_id: serviceId,
                selected_date: selectedDate,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#slot-inut").empty();

                if ($.isEmptyObject(response.slot)) {
                    if (
                        response.formatted_slot_availability &&
                        response.formatted_slot_availability.length > 0
                    ) {
                        $("#slot-inut").append(
                            '<div class="col-12 fw-bold mt-3 text-center">' +
                                "<p>No slots available for the given date.</p>" +
                                '<p>Future availability date: <span class="availability-dates"></span></p>' +
                                "</div>"
                        );
                        $.each(
                            response.formatted_slot_availability,
                            function (index, availableDate) {
                                var dateHtml = `
                                <div class="col-lg-4 col-md-6 mt-2" id="slotDateHover">
                                    <div class="time-item" id="time-item-${index}" onclick="selectsRadioDate('${availableDate["value"]}')">
                                        <input type="radio" name="available_date" id="date_${index}" value="${availableDate["value"]}" class="slot-date-radio" hidden>
                                        <h6 class="fs-12 fw-medium">${availableDate["label"]}</h6>
                                    </div>
                                </div>
                            `;
                                $("#slot-inut").append(dateHtml);
                            }
                        );
                        $("#time_staus").val("1");
                    } else {
                        $("#time_staus").val("0");
                        $(".time-section").addClass("d-none");
                        $("#slot-inut").append(
                            '<div class="col-12 fw-bold mt-3"><p>No slots or future dates available at this moment</p></div>'
                        );
                    }
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
                        $("#slot-inut").append(slotHtml);
                    });
                    $("#time_staus").val("1");
                }
                $(".slotLoader").hide();
                $("#serviceLoader").hide();
                $(".label-loader, .input-loader").hide(); // Hide all skeleton loaders
                $(".real-label, .real-input").removeClass("d-none");
            },
        });
    }

    function fetchOnlySlot(serviceId, selectedDate) {
        $("#slot-input").empty();
        $(".slotLoader-skaliaton").append(
            '<div><div class="skeleton chat-skeleton label-loader mb-2 mt-2"></div> <div class="skeleton chat2-skeleton label-loader"></div> </div>'
        );

        $.ajax({
            url: "/get-slots",
            type: "POST",
            data: {
                service_id: serviceId,
                selected_date: selectedDate,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#slot-input").empty();

                if ($.isEmptyObject(response.slot)) {
                    if (
                        response.formatted_slot_availability &&
                        response.formatted_slot_availability.length > 0
                    ) {
                        $("#slot-input").append(
                            '<div class="col-12 fw-bold mt-3 text-center">' +
                                "<p>No slots available for the given date.</p>" +
                                '<p>Future availability date: <span class="availability-dates"></span></p>' +
                                "</div>"
                        );
                        $.each(
                            response.formatted_slot_availability,
                            function (index, availableDate) {
                                var dateHtml = `
                                <div class="col-lg-4 col-md-6 mt-2" id="slotDateHover">
                                    <div class="time-item" id="time-item-${index}" onclick="selectRadioDate('${availableDate["value"]}')">
                                        <input type="radio" name="available_date" id="date_${index}" value="${availableDate["value"]}" class="slot-date-radio" hidden>
                                        <h6 class="fs-12 fw-medium">${availableDate["label"]}</h6>
                                    </div>
                                </div>
                            `;
                                $("#slot-input").append(dateHtml);
                            }
                        );
                        $("#time_staus").val("1");
                    } else {
                        $("#time_staus").val("0");
                        $(".time-section").addClass("d-none");
                        $("#slot-input").append(
                            '<div class="col-12 fw-bold mt-3"><p>No slots or future dates available at this moment</p></div>'
                        );
                    }
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
                    $("#time_staus").val("1");
                }
                $(".slotLoader").hide();
                $("#serviceLoader").hide();
                $(".label-loader, .input-loader").hide(); // Hide all skeleton loaders
                $(".real-label, .real-input").removeClass("d-none");
                $(".location-loader").hide();
            },
        });
    }

    function selectRadioDate(date) {
        var serviceId = $("#service_id").val();
        $(".bookingDatepics")
            .data("DateTimePicker")
            .date(moment(date, "DD-MM-YYYY"));
        fetchOnlySlot(serviceId, date);
    }

    function selectsRadioDate(date) {
        var serviceId = $("#service_id").val();
        $(".bookingDatepic")
            .data("DateTimePicker")
            .date(moment(date, "DD-MM-YYYY"));
        fetchSlot(serviceId, date);
    }

    function selectRadio(branchId) {
        document.querySelectorAll(".branch-radio").forEach((radio) => {
            radio.checked = false;
        });

        var serviceId = $("#service_id").val();
        document.getElementById("branch_" + branchId).checked = true;

        fetchStaffs(branchId, serviceId);
    }

    function selectRadioStaff(staffId) {
        document.querySelectorAll(".staff-radio").forEach((radio) => {
            radio.checked = false;
        });

        document.getElementById("staff_" + staffId).checked = true;

        document.querySelectorAll(".staff-card").forEach((card) => {
            card.classList.remove("selected");
        });

        document
            .querySelector(`#staff_${staffId}`)
            .closest(".staff-card")
            .classList.add("selected");
    }

    function selectRadioSlot(slotId) {
        document.querySelectorAll(".slot-radio").forEach((radio) => {
            radio.checked = false;
        });

        document.getElementById("slot_" + slotId).checked = true;

        document.querySelectorAll(".time-item").forEach((item) => {
            item.classList.remove("selected");
        });

        document
            .getElementById("time-item-" + slotId)
            .classList.add("selected");

        var selectedDate = $("#selected_date").val();

        fetchSlotInfo(selectedDate, slotId);
    }

    function fetchSlotInfo(selectedDate, slotId = null) {
        $.ajax({
            url: "/get-slot-info", // Replace with your appropriate URL
            type: "POST",
            data: {
                slot_id: slotId,
                selected_date: selectedDate,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                if (data.slot && data.slot.source_Values) {
                    $(".slot_time").text(data.slot.source_Values);
                    $(".slot-section").show(); // Ensure the section is visible if there's a value
                } else {
                    $(".slot-section").hide(); // Hide the section if no value is found
                }

                $(".slot_day").text(data.selected_date);
                $(".slot_time_day").text(data.selected_date_slot);
                $(".final-time").text(data.selected_date_slot);
                $("#from_time").val(data.from_time);
                $("#to_time").val(data.to_time);
            },
        });
    }

    function selectRadioAddService(additionalServiceId) {
        const checkbox = document.getElementById(
            "additionalService_" + additionalServiceId
        );
        const card = checkbox.closest(".select-item");
        const button = card.querySelector(".btn-addon");
        const outputP = document.querySelector(".additional-service-output p");

        checkbox.checked = !checkbox.checked;

        if (checkbox.checked) {
            card.classList.add("active");
            button.classList.add("btn-success");
            button.classList.remove("btn-light");
            button.innerHTML = '<i class="feather-check-circle me-1"></i>Added';
        } else {
            card.classList.remove("active");
            button.classList.remove("btn-success");
            button.classList.add("btn-light");
            button.innerHTML = '<i class="feather-plus-circle me-1"></i>Add';
        }

        updateSelectedServices();
    }

    function updateSelectedServices() {
        const selectedServices = Array.from(
            document.querySelectorAll(".additionalService-radio:checked")
        ).map((checkbox) => {
            const card = checkbox.closest(".select-item");
            return card.querySelector("h6").textContent.trim();
        });

        const outputP = document.querySelector(".additional-service-output p");

        if (outputP) {
            outputP.textContent =
                selectedServices.length > 0 ? selectedServices.join(", ") : "";
        }
    }

    let overallStaffCount = 0;
    function fetchStaffs(branchId, serviceId) {
        $.ajax({
            url: "/get-branch-staff",
            type: "GET",
            data: { branch_id: branchId, service_id: serviceId },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                const staffCountFormatted = data.staff_count
                    .toString()
                    .padStart(2, "0");
                $("#staff-count").text("Total: " + staffCountFormatted);

                const staffContainer = $("#staff-container");
                staffContainer.empty();

                overallStaffCount = data.staffs.length;

                if (data.staffs.length === 0) {
                    staffContainer.append(`
                    <div class="col-12 text-center">
                        <p class="text-dark fw-bold mt-5">No staff available at this moment. You cannot proceed further.</p>
                    </div>
                `);
                    return;
                }

                data.staffs.forEach((staff) => {
                    const staffHtml = `
                    <div class="col-lg-4 col-md-6">
                        <div class="card staff-card mb-0" onclick="selectRadioStaff(${
                            staff.user.user_id
                        })">
                            <!-- Make the radio button hidden, but keep it functional -->
                            <input type="radio" name="staff_id" id="staff_${
                                staff.user.user_id
                            }" value="${
                        staff.user.user_id
                    }" class="staff-radio" hidden>
                            <div class="card-body p-3 text-center">
                                <span class="avatar avatar-lg mx-auto mb-2">
                                    <img src="${
                                        staff.user.profile_image
                                    }" alt="img">
                                </span>
                                <h6 class="mb-2 fw-medium">${
                                    staff.user.first_name
                                } ${staff.user.last_name}</h6>
                                <p class="mb-2">${staff.user.email}</p>
                                <div class="d-flex align-items-center justify-content-between border-top pt-2">
                                    <p class="mb-0 d-flex align-items-center">
                                        <i class="ti ti-circle-check-filled text-danger fs-5 me-1"></i>${
                                            staff.services_count ?? "0.0"
                                        } Services
                                    </p>
                                    <p class="mb-0 d-flex align-items-center">
                                        <i class="ti ti-star-filled text-warning me-1"></i>${
                                            staff.rating ?? "0.0"
                                        }
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                    staffContainer.append(staffHtml);
                });
            },

            error: function (xhr) {
                const errorMessage =
                    xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : "Failed to fetch staff details. Please try again.";
                toastr.error(errorMessage);
            },
        });
    }

    function fetchlocstaffSlot(branchId, staffId, addServiceIds = []) {
        $.ajax({
            url: "/get-branch-staff-info",
            type: "GET",
            data: {
                branch_id: branchId,
                staff_id: staffId,
                addService_ids: addServiceIds,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                let serviceNames = data.addService_info
                    .map(function (service) {
                        return `${
                            service.name
                        } - ${service.symbol}${parseFloat(service.price).toFixed(2)}`;
                    })
                    .join("<br>");

                if (!serviceNames || serviceNames.trim() === "") {
                    $(".add-section").hide(); // Hide the section if serviceNames is empty
                } else {
                    $(".service-names").html(serviceNames); // Use .html() to preserve <br>
                    $(".add-section").show(); // Show the section
                }

                $(".additional-service-output").text(serviceNames);

                $(".location-section .avatar img").attr(
                    "src",
                    data.branch_info.branch_image_url
                );
                $(".location-section .branch-name").text(
                    data.branch_info.branch_name
                );
                $(".location-section .branch-email").text(
                    data.branch_info.branch_email
                );
                $(".cart-location-name").text(data.branch_info.branch_name);

                if (
                    data.staff_info &&
                    data.staff_info.first_name &&
                    data.staff_info.last_name
                ) {
                    $(".staff-section .staff-name").text(
                        data.staff_info.first_name +
                            " " +
                            data.staff_info.last_name
                    );
                    $(".staff-section").show(); // Ensure the staff section is visible
                } else {
                    $(".staff-section").hide(); // Hide the staff section if no staff info is available
                }
                // Update the staff details
                $(".staff-section .avatar img").attr(
                    "src",
                    data.staff_info.profile_image_url
                );

                $(".cart-staff-name").text(
                    data.staff_info.first_name + " " + data.staff_info.last_name
                );

                $("#booking_first_name").val(data.user_info.first_name);
                $("#booking_last_name").val(data.user_info.last_name);
                $("#booking_email").val(data.user_info.email);
                $("#phone_number").val(data.user_info.phone_number);
                $("#address").val(data.user_info.address);
                $("#city").val(data.user_info.city);
                $("#state").val(data.user_info.state);
                $("#postal").val(data.user_info.postal_code);
            },
            error: function (xhr) {
                const errorMessage =
                    xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : "Failed to fetch details. Please try again.";
                toastr.error(errorMessage);
            },
        });
    }

    function fetchPayout(serviceId, addServiceIds = [], coupon = null) {
        $.ajax({
            url: "/get-payout",
            type: "POST",
            data: {
                service_id: serviceId,
                addService_ids: addServiceIds,
                coupon_id: coupon ? coupon.coupon_id : null,
                coupon_code: coupon ? coupon.coupon_code : null,
                coupon_type: coupon ? coupon.coupon_type : null,
                coupon_value: coupon ? coupon.coupon_value : null,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                $("#payout").empty();
                // Ensure numeric values are properly formatted
                let subTotal = parseFloat(data.sub_total).toFixed(2);
                let addServiceTotal = parseFloat(data.addService_total).toFixed(
                    2
                );
                let taxTotal = parseFloat(data.tax_total).toFixed(2);
                let totalAmount = parseFloat(data.total_amount).toFixed(2);
                let discountTotal = data.discount_total
                    ? parseFloat(data.discount_total).toFixed(2)
                    : "0.00";
                let currecy_details = data.currecy_details;

                $(".pay_amount").text(totalAmount);
                $(".sub_amount").val(subTotal);
                $(".final-sub").text(subTotal);
                $(".addService_amount").val(addServiceTotal);
                $(".tax_amount").val(taxTotal);
                $(".final-tax").text(taxTotal);
                $(".total_amount").val(totalAmount);
                $(".final-total").text(totalAmount);

                let payoutHtml = `
                <div class="total-wrap">
                    <div class="mb-2 d-flex align-items-center justify-content-between">
                        <h6 class="fw-medium">${$("#payout").data(
                            "sub_total"
                        )}</h6>
                        <p class="text-gray-9">${
                            currecy_details.symbol
                        }${subTotal}</p>
                    </div>
                    <div class="mb-2 d-flex align-items-center justify-content-between">
                        <h6 class="fw-medium">${$("#payout").data(
                            "additional_services_total"
                        )}</h6>
                        <p class="text-gray-9">${
                            currecy_details.symbol
                        }${addServiceTotal}</p>
                    </div>`;

                data.tax_used.forEach((tax) => {
                    let taxAmount = parseFloat(tax.tax_amount).toFixed(2);
                    payoutHtml += `
                    <div class="mb-2 d-flex align-items-center justify-content-between">
                        <h6 class="fw-medium">${$("#payout").data(
                            "tax"
                        )} <span class="text-default fw-normal">(${
                        tax.tax_type
                    } ${parseFloat(tax.tax_rate).toFixed(2)}%)</span></h6>
                        <p class="text-gray-9">${
                            currecy_details.symbol
                        }${taxAmount}</p>
                    </div>`;
                });

                if (
                    data.discount_details &&
                    typeof data.discount_details === "object" &&
                    !Array.isArray(data.discount_details)
                ) {
                    let discountType = data.discount_details.discount_type;
                    let discountValue = data.discount_details.discount_value;
                    let discountAmount = parseFloat(
                        data.discount_details.discount_amount
                    ).toFixed(2);

                    let discountText =
                        discountType === "percentage"
                            ? `${discountValue}% (-$${discountAmount})`
                            : `-$${discountAmount}`;

                    payoutHtml += `
                    <div class="mb-2 d-flex align-items-center justify-content-between text-danger">
                        <h6 class="fw-medium">Discount</h6>
                        <p class="text-gray-9">{discountText}</p>
                    </div>`;
                }

                if (
                    data.coupon_details &&
                    typeof data.coupon_details === "object" &&
                    !Array.isArray(data.coupon_details)
                ) {
                    let couponId = data.coupon_details.id;
                    let couponCode = data.coupon_details.coupon_code;
                    let couponType = data.coupon_details.coupon_type;
                    let couponValue = data.coupon_details.coupon_value;
                    let couponDiscountAmount =
                        data.coupon_details.coupon_discount_amount;

                    let couponText =
                        couponType === "percentage"
                            ? `${currecy_details.symbol}${couponDiscountAmount}`
                            : `${currecy_details.symbol}${couponDiscountAmount}`;

                    payoutHtml += `
                    <div class="mb-2 d-flex align-items-center justify-content-between text-success">
                        <input type="text" name="coupon_id" id="coupon_id" value="${couponId}" hidden>
                        <input type="text" name="coupon_value" id="coupon_value" value="${couponValue}" hidden>
                        <h6 class="fw-medium">Coupon (${couponCode}${
                        couponType == "percentage"
                            ? " " + couponValue + "%"
                            : ""
                    })</h6>
                        <p class="text-gray-9">${couponText}</p>
                    </div>`;

                    // Hide "ADD" button and show "Remove" button
                    $("#coupon_btn").addClass("d-none");
                    $("#coupon_remove_btn")
                        .removeClass("d-none")
                        .addClass("d-block");
                }

                payoutHtml += `
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="fs-14">${$("#payout").data("total")}</h6>
                        <h6 class="fs-14">${
                            currecy_details.symbol
                        }${totalAmount}</h6>
                    </div>
                </div>`;

                $("#payout").html(payoutHtml);

                if (data.wallet_availabe === "yes") {
                    $("#wallet-meg")
                        .html(
                            `
                            <div class="card wallet-card">
                                <div class="card-body">
                                    <h5 class="card-title">${$(
                                        "#wallet-meg"
                                    ).data("wallet_balance")}</h5>
                                    <p class="card-text">${$(
                                        "#wallet-meg"
                                    ).data(
                                        "your_wallet_balance_is_sufficient"
                                    )}.</p>
                                    <p class="card-text">${$(
                                        "#wallet-meg"
                                    ).data("available_balance")}: <strong>${
                                currecy_details.symbol
                            }${data.wallet_amount}</strong></p>
                                </div>
                            </div>
                        `
                        )
                        .css("color", "green");

                    $("#wallet_status").prop("disabled", false);
                } else {
                    $("#wallet-meg")
                        .html(
                            `
                            <div class="card wallet-card">
                                <div class="card-body">
                                    <h5 class="card-title">${$(
                                        "#wallet-meg"
                                    ).data("wallet_balance")}</h5>
                                    <p class="card-text">${$(
                                        "#wallet-meg"
                                    ).data("insufficient_wallet_balance")}.</p>
                                    <p class="card-text">${$(
                                        "#wallet-meg"
                                    ).data("available_balance")}: <strong>${
                                currecy_details.symbol
                            }${data.wallet_amount}</strong></p>
                                </div>
                            </div>
                        `
                        )
                        .css("color", "red");

                    $("#wallet_status").prop("disabled", true);
                }
                $("#serviceLoader").hide();
            },
            error: function (xhr) {
                const errorMessage =
                    xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : "Failed to fetch details. Please try again.";
                toastr.error(errorMessage);
            },
        });
    }

    $(document).ready(function () {
        $("#coupon_btn").click(function () {
            $("#serviceLoader").show();
            let coupon_code = $("#coupon_code").val();
            let subcategory_id = $("#subcategory_id").val();
            let category_id = $("#category_id").val();
            let service_id = $("#service_id").val();

            $.ajax({
                url: "/check-coupon",
                type: "POST",
                data: {
                    coupon_code: coupon_code,
                    subcategory_id: subcategory_id,
                    category_id: category_id,
                    service_id: service_id,
                },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                success: function (response) {
                    if (response.success) {
                        $("#coupon_code_error")
                            .text(response.message)
                            .css("color", "green");
                        $("#coupon_btn").addClass("d-none");
                        $("#coupon_remove_btn").removeClass("d-none");
                        let serviceId = $("#service_id").val();
                        let addServiceIds = $(
                            "input[name='additionalService_id[]']:checked"
                        )
                            .map(function () {
                                return this.value;
                            })
                            .get();
                        $("#coupon_code").attr("readonly", true);
                        fetchPayout(serviceId, addServiceIds, response.coupon);
                    } else {
                        $("#serviceLoader").hide();
                        $("#coupon_code_error")
                            .text(response.message)
                            .css("color", "red");
                    }
                },
                error: function (xhr) {
                    let errorResponse = JSON.parse(xhr.responseText);
                    if (
                        errorResponse.errors &&
                        errorResponse.errors.coupon_code
                    ) {
                        $("#coupon_code_error")
                            .text(errorResponse.errors.coupon_code[0]) // Show backend error
                            .css("color", "red");
                    } else {
                        $("#coupon_code_error")
                            .text("An error occurred. Please try again.")
                            .css("color", "red");
                    }
                },
            });
        });

        $("#coupon_remove_btn").click(function () {
            $("#serviceLoader").show();
            $("#coupon_code").val("");
            $("#coupon_btn").removeClass("d-none");
            $("#coupon_remove_btn").addClass("d-none");
            $("#coupon_code").attr("readonly", false);
            $("#coupon_code_error").text("");
            let serviceId = $("#service_id").val();
            let addServiceIds = $(
                "input[name='additionalService_id[]']:checked"
            )
                .map(function () {
                    return this.value;
                })
                .get();
            fetchPayout(serviceId, addServiceIds);
        });
    });

    $(document).ready(function () {
        //Branch Validation
        $("#branch-btn").on("click", function (event) {
            event.preventDefault();

            let branchFormData = $("#branch-form").serializeArray();

            if ($('input[name="branch_id"]:checked').length === 0) {
                toastr.error("Please select a location before continuing.");
                return;
            }

            if ($("#branch-form").valid()) {
                let formDataCollection = {};
                branchFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                $("#bookcomplete").text("15");
                $("#first-field").css("display", "none");
                $("#second-field").css("display", "flex");
                $("#bokingwizard li.location")
                    .removeClass("active")
                    .addClass("activated");
                $("#bokingwizard li.staff").addClass("active");
            }
        });

        $("#branch-btn-additional").on("click", function (event) {
            event.preventDefault();

            let branchFormData = $("#branch-form").serializeArray();

            if ($('input[name="branch_id"]:checked').length === 0) {
                toastr.error("Please select a location before continuing.");
                return;
            }

            if ($("#branch-form").valid()) {
                let formDataCollection = {};
                branchFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                $("#bookcomplete").text("15");
                $("#first-field").css("display", "none");
                $("#second-field").css("display", "flex");
                $("#bokingwizard li.location")
                    .removeClass("active")
                    .addClass("activated");
                $("#bokingwizard li.staff").addClass("active");
            }
        });

        $("#branch-btn-staff").on("click", function (event) {
            event.preventDefault();

            let branchFormData = $("#branch-form").serializeArray();

            if ($('input[name="branch_id"]:checked').length === 0) {
                toastr.error("Please select a location before continuing.");
                return;
            }

            if ($("#branch-form").valid()) {
                let formDataCollection = {};
                branchFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                $("#bookcomplete").text("15");
                $("#first-field").css("display", "none");
                $("#third-field").css("display", "flex");
                $("#bokingwizard li.location")
                    .removeClass("active")
                    .addClass("activated");
                $("#bokingwizard li.addservice").addClass("active");
            }
        });

        $("#branch-btn-new-both").on("click", function (event) {
            event.preventDefault();

            let branchFormData = $("#branch-form").serializeArray();

            if ($('input[name="branch_id"]:checked').length === 0) {
                toastr.error("Please select a location before continuing.");
                return;
            }

            if ($("#branch-form").valid()) {
                let formDataCollection = {};
                branchFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });
                $("#slot-inut").empty();
                var serviceId = $("#service_id").val();
                let branchId = $('input[name="branch_id"]:checked').val();
                let staffId = $('input[name="staff_id"]:checked').val();

                let today = new Date();
                let day = String(today.getDate()).padStart(2, "0");
                let month = String(today.getMonth() + 1).padStart(2, "0"); // Months are zero-indexed
                let year = today.getFullYear();
                let selectedDate = `${day}-${month}-${year}`;

                fetchSlot(branchId, staffId, serviceId, selectedDate);
                let addServiceIds = $(
                    "input[name='additionalService_id[]']:checked"
                )
                    .map(function () {
                        return this.value;
                    })
                    .get();
                fetchlocstaffSlot(branchId, staffId, addServiceIds);

                $("#bookcomplete").text("30");
                $("#first-field").css("display", "none");
                $("#fourth-field").css("display", "flex");
                $("#bokingwizard li.location")
                    .removeClass("active")
                    .addClass("activated");
                $("#bokingwizard li.datetime").addClass("active");
            }
        });

        //Staff Validation
        $("#staff-btn-new").on("click", function (event) {
            event.preventDefault();

            let staffFormData = $("#staff-form").serializeArray();

            if ($('input[name="staff_id"]:checked').length == 0) {
                toastr.error("Please select a staff before continuing.");
                return;
            }

            if ($("#staff-form").valid()) {
                let formDataCollection = {};
                staffFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });
                $("#slot-inut").empty();
                var serviceId = $("#service_id").val();
                let branchId = $('input[name="branch_id"]:checked').val();
                let staffId = $('input[name="staff_id"]:checked').val();

                let today = new Date();
                let day = String(today.getDate()).padStart(2, "0");
                let month = String(today.getMonth() + 1).padStart(2, "0"); // Months are zero-indexed
                let year = today.getFullYear();
                let selectedDate = `${day}-${month}-${year}`;

                fetchSlot(branchId, staffId, serviceId, selectedDate);
                let addServiceIds = $(
                    "input[name='additionalService_id[]']:checked"
                )
                    .map(function () {
                        return this.value;
                    })
                    .get();
                fetchlocstaffSlot(branchId, staffId, addServiceIds);
                $("#bookcomplete").text("30");
                $("#second-field").css("display", "none");
                $("#fourth-field").css("display", "flex");
                $("#bokingwizard li.staff")
                    .removeClass("active")
                    .addClass("activated");
                $("#bokingwizard li.datetime").addClass("active");
            }
        });

        $("#staff-prev-new").on("click", function (event) {
            event.preventDefault();

            $("#second-field").css("display", "none");
            $("#first-field").css("display", "flex");
            $("#bokingwizard li.staff").removeClass("active");
            $("#bokingwizard li.location")
                .removeClass("activated")
                .addClass("active");
            $("#bookcomplete").text("0");
        });

        $("#staff-btn").on("click", function (event) {
            event.preventDefault();

            let staffFormData = $("#staff-form").serializeArray();

            if ($('input[name="staff_id"]:checked').length == 0) {
                toastr.error("Please select a staff before continuing.");
                return;
            }

            if ($("#staff-form").valid()) {
                let formDataCollection = {};
                staffFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });
                $("#bookcomplete").text("30");
                $("#second-field").css("display", "none");
                $("#third-field").css("display", "flex");
                $("#bokingwizard li.staff")
                    .removeClass("active")
                    .addClass("activated");
                $("#bokingwizard li.addservice").addClass("active");
            }
        });

        $("#staff-prev").on("click", function (event) {
            event.preventDefault();

            $("#second-field").css("display", "none");
            $("#first-field").css("display", "flex");
            $("#bokingwizard li.staff").removeClass("active");
            $("#bokingwizard li.location")
                .removeClass("activated")
                .addClass("active");
            $("#bookcomplete").text("0");
        });

        //addService Validation
        $("#addService-btn").on("click", function (event) {
            event.preventDefault();

            let addServiceFormData = $("#addService-form").serializeArray();

            if ($("#addService-form").valid()) {
                let formDataCollection = {};
                addServiceFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                $("#slot-inut").empty();
                var serviceId = $("#service_id").val();
                let branchId = $('input[name="branch_id"]:checked').val();
                let staffId = $('input[name="staff_id"]:checked').val();

                let today = new Date();
                let day = String(today.getDate()).padStart(2, "0");
                let month = String(today.getMonth() + 1).padStart(2, "0"); // Months are zero-indexed
                let year = today.getFullYear();
                let selectedDate = `${day}-${month}-${year}`;

                fetchSlot(branchId, staffId, serviceId, selectedDate);
                let addServiceIds = $(
                    "input[name='additionalService_id[]']:checked"
                )
                    .map(function () {
                        return this.value;
                    })
                    .get();
                fetchlocstaffSlot(branchId, staffId, addServiceIds);
                $("#third-field").css("display", "none");
                $("#fourth-field").css("display", "flex");
                $("#bokingwizard li.addservice")
                    .removeClass("active")
                    .addClass("activated");
                $("#bokingwizard li.datetime").addClass("active");
                $("#bookcomplete").text("45");
            }
        });

        $("#addService-prev").on("click", function (event) {
            event.preventDefault();

            $("#third-field").css("display", "none");
            $("#second-field").css("display", "flex");
            $("#bokingwizard li.addservice").removeClass("active");
            $("#bokingwizard li.staff")
                .removeClass("activated")
                .addClass("active");
            $("#bookcomplete").text("15");
        });

        $("#addService-prev-staff").on("click", function (event) {
            event.preventDefault();

            $("#third-field").css("display", "none");
            $("#first-field").css("display", "flex");
            $("#bokingwizard li.addservice").removeClass("active");
            $("#bokingwizard li.location")
                .removeClass("activated")
                .addClass("active");
            $("#bookcomplete").text("0");
        });

        //slot Validation
        $("#slot-btn").on("click", function (event) {
            event.preventDefault();

            let slotFormData = $("#slot-form").serializeArray();
            var timeStatus = $("#time_staus").val();

            if (
                timeStatus === "1" &&
                $('input[name="slot_id"]:checked').length === 0
            ) {
                toastr.error("Please select a slot before continuing.");
                return;
            }

            if ($("#slot-form").valid()) {
                let formDataCollection = {};
                slotFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                var selectedDate = $("#selected_date").val();
                var selectedSlot = $('input[name="slot_id"]:checked').val();

                fetchSlotInfo(selectedDate, selectedSlot);

                $("#fourth-field").css("display", "none");
                $("#fifth-field").css("display", "flex");
                $("#bokingwizard li.datetime")
                    .removeClass("active")
                    .addClass("activated");
                $("#bokingwizard li.prinfo").addClass("active");
                $("#bookcomplete").text("60");
            }
        });

        $("#slot-prev-new-both").on("click", function (event) {
            event.preventDefault();

            $("#fourth-field").css("display", "none");
            $("#first-field").css("display", "flex");
            $("#bokingwizard li.datetime").removeClass("active");
            $("#bokingwizard li.location")
                .removeClass("activated")
                .addClass("active");
            $("#bookcomplete").text("0");
        });

        $("#slot-prev-additional").on("click", function (event) {
            event.preventDefault();

            $("#fourth-field").css("display", "none");
            $("#second-field").css("display", "flex");
            $("#bokingwizard li.datetime").removeClass("active");
            $("#bokingwizard li.staff")
                .removeClass("activated")
                .addClass("active");
            $("#bookcomplete").text("15");
        });

        $("#slot-prev-staff").on("click", function (event) {
            event.preventDefault();

            $("#fourth-field").css("display", "none");
            $("#third-field").css("display", "flex");
            $("#bokingwizard li.datetime").removeClass("active");
            $("#bokingwizard li.addservice")
                .removeClass("activated")
                .addClass("active");
            $("#bookcomplete").text("15");
        });

        $("#slot-prev").on("click", function (event) {
            event.preventDefault();

            $("#fourth-field").css("display", "none");
            $("#third-field").css("display", "flex");
            $("#bokingwizard li.datetime").removeClass("active");
            $("#bokingwizard li.addservice")
                .removeClass("activated")
                .addClass("active");
            $("#bookcomplete").text("30");
        });

        $("#slot-prev-new").on("click", function (event) {
            event.preventDefault();

            $("#fourth-field").css("display", "none");
            $("#second-field").css("display", "flex");
            $("#bokingwizard li.datetime").removeClass("active");
            $("#bokingwizard li.staff")
                .removeClass("activated")
                .addClass("active");
            $("#bookcomplete").text("30");
        });

        //prinfo Validation
        $("#prinfo-btn").on("click", function (event) {
            event.preventDefault();

            let prinfoFormData = $("#prinfo-form").serializeArray();
            let isValid = true;

            $(".print-info").each(function () {
                if ($.trim($(this).val()) == "") {
                    let label = $(this)
                        .closest("div")
                        .find("label")
                        .text()
                        .replace(/[:*]/g, "")
                        .trim();
                    toastr.error(
                        label + " " + $("#prinfo-form").data("is_required_text")
                    );
                    isValid = false;
                    return false;
                }
            });

            if ($("#prinfo-form").valid() && isValid) {
                let formDataCollection = {};
                prinfoFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });
                let serviceId = $("#service_id").val();
                let addServiceIds = $(
                    "input[name='additionalService_id[]']:checked"
                )
                    .map(function () {
                        return this.value;
                    })
                    .get();
                fetchPayout(serviceId, addServiceIds);

                let authId = $("body").data("authid");

                if (bookingVerifyUserId || authId) {
                    $("#fifth-field").css("display", "none");
                    $("#sixth-field").css("display", "flex");
                    $("#bokingwizard li.prinfo")
                        .removeClass("active")
                        .addClass("activated");
                    $("#bokingwizard li.cart").addClass("active");
                    $("#bookcomplete").text("75");
                } else {
                    verifyUser(formDataCollection);
                }
            }
        });

        $("#prinfo-prev").on("click", function (event) {
            event.preventDefault();

            $("#fifth-field").css("display", "none");
            $("#fourth-field").css("display", "flex");
            $("#bokingwizard li.prinfo").removeClass("active");
            $("#bokingwizard li.datetime")
                .removeClass("activated")
                .addClass("active");
            $("#bookcomplete").text("30");
        });

        $("#cart-prev").on("click", function (event) {
            event.preventDefault();
            $("#bookcomplete").text("60");
        });

        $("#cart-btn").on("click", function (event) {
            event.preventDefault();
            $("#bookcomplete").text("90");
        });

        $("#back-cart").on("click", function (event) {
            event.preventDefault();
            $("#bookcomplete").text("75");
        });

        //payemant validation
        $("#pay-btns").on("click", function (event) {
            event.preventDefault();

            let addServiceFormData = $("#addService-form").serializeArray();
            let slotFormData = $("#slot-form").serializeArray();
            let prinfoFormData = $("#prinfo-form").serializeArray();
            let paymentFormData = $("#payment-form").serializeArray();

            const all = 1;

            if (all === 1) {
                let finalFormData = new FormData();

                finalFormData.append(
                    "_token",
                    $('meta[name="csrf-token"]').attr("content")
                );

                [
                    ...addServiceFormData,
                    ...slotFormData,
                    ...prinfoFormData,
                    ...paymentFormData,
                ].forEach(function (item) {
                    finalFormData.append(item.name, item.value);
                });

                $("#serviceLoader").show();

                $.ajax({
                    url: "/user/payment",
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

                        //CashFree
                        if (
                            response &&
                            response.cashfree &&
                            response.cashfree.redirect_url
                        ) {
                            window.location.href =
                                response.cashfree.redirect_url;
                        }

                        //Mercadopago
                        if (
                            response &&
                            response.mercadopago &&
                            response.mercadopago.redirect_url
                        ) {
                            window.location.href =
                                response.mercadopago.redirect_url;
                        }

                        //Paystack
                        if (
                            response &&
                            response.paystack &&
                            response.paystack.redirect_url
                        ) {
                            window.location.href =
                                response.paystack.redirect_url;
                        }

                        if (response.url) {
                            window.location.href = response.url;
                        }

                        if (response.stripurl) {
                            window.location.href = response.stripurl;
                        }

                        if (response.razorpay) {
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
                                    form.action = "/razorpay/payment-success";

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

                        if (response.code === 200) {
                            if (response.paypal_url) {
                                window.location.href = response.paypal_url; // Redirect to the PayPal URL
                                $("#serviceLoader").show();
                            } else {
                                $("#saventh-field").css("display", "none");
                                $("#eight-field").css("display", "flex");
                                $("#bokingwizard li.pay")
                                    .removeClass("active")
                                    .addClass("activated");
                                $("#bokingwizard li.confime")
                                    .removeClass("active")
                                    .addClass("activated");
                                $("#serviceLoader").hide();
                                $("#bookcomplete").text("100");
                            }
                        }
                    })
                    .fail((error) => {
                        $("#serviceLoader").hide();
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
                                        $("#" + key + "_error").text(val[0]);
                                    }
                                );
                            } else if (error.responseJSON.message) {
                                toastr(error.responseJSON.message, "bg-danger");
                            }
                        } else {
                            toastr(error.responseJSON.message, "bg-danger");
                        }
                    });
            }
        });
    });

    const bookingOtpEmailModal = new bootstrap.Modal(
        document.getElementById("booking-otp-email-modal"),
        { keyboard: false }
    );
    const bookingOtpSmsModal = new bootstrap.Modal(
        document.getElementById("booking-otp-phone-modal"),
        { keyboard: false }
    );

    const bookingOtpTimerDisplay = document.getElementById("booking-otp-timer");
    const bookingOtpSmsTimerDisplay = document.getElementById("booking-otp-sms-timer");
    const bookingOtpModal = document.getElementById("booking-otp-email-modal");
    const bookingOtpsmsModal = document.getElementById("booking-otp-phone-modal");

    bookingOtpModal.addEventListener("hidden.bs.modal", function () {
        const errorMessage = document.getElementById("booking_error_message");
        if (errorMessage) {
            errorMessage.textContent = "";
        }
    });
    bookingOtpsmsModal.addEventListener("hidden.bs.modal", function () {
        const errorMessage = document.getElementById("booking_error_sms_message");
        if (errorMessage) {
            errorMessage.textContent = "";
        }
    });

    function verifyUser(formDataCollection) {

        formDataCollection.type = "booking_verify_user";
        let email = formDataCollection.email;

        $.ajax({
            url: "/otp-settings",
            type: "POST",
            data: formDataCollection,
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
            },
            beforeSend: function () {
                showOtpLoader();
            },
            success: function (data) {
                if (data.provider_verified_status == 0) {
                    $("#provider_not_verified_modal").modal("show");
                    hideOtpLoader();
                } else {

                const userName = data.name;
                const otp = data.otp;
                const otpExpireTime = parseInt(
                    data.otp_expire_time.split(" ")[0]
                );
                const phoneNumber = data.phone_number;

                const otpDigitLimit = parseInt(data.otp_digit_limit);

                const inputContainer = $(".inputcontainer");
                inputContainer.empty();

                let inputsHtml = "<div class='d-flex align-items-center mb-3'>";

                for (let i = 1; i <= otpDigitLimit; i++) {
                    const nextId = `booking-digit-${i + 1}`;
                    const prevId = `booking-digit-${i - 1}`;
                    inputsHtml += `
                      <input type="text"
                      class="rounded w-100 py-sm-3 py-2 text-center fs-26 fw-bold me-3 booking-digit-${i}" 
                      id="booking-digit-${i}" 
                      name="booking-digit-${i}" 
                      data-next="${nextId}" 
                      data-previous="${prevId}" 
                      maxlength="1">`;
                }

                inputsHtml += "</div>";
                inputContainer.append(inputsHtml);

                $(".inputcontainer").on("input", "input", function () {
                    const maxLength = $(this).attr("maxlength") || 1;
                    if (this.value.length >= maxLength) {
                        const next = $(this).data("next");
                        if (next) {
                            $("#" + next).focus();
                        }
                    }
                });

                $(".inputcontainer").on("keydown", "input", function (e) {
                    if (e.key === "Backspace" && this.value === "") {
                        const prev = $(this).data("previous");
                        if (prev) {
                            $("#" + prev).focus();
                        }
                    }
                });

                $(".inputcontainer").on("click", "input", function () {
                    $(this).select();
                });

                const inputSMSContainer = $(".inputSMSContainer");
                inputSMSContainer.empty();

                let inputsSMSHtml =
                    "<div class='d-flex align-items-center mb-3'>";
                for (let i = 1; i <= otpDigitLimit; i++) {
                    const nextId = `booking-digitsms-${i + 1}`;
                    const prevId = `booking-digitsms-${i - 1}`;
                    inputsSMSHtml += `
                        <input type="text" 
                               class="rounded w-100 py-sm-3 py-2 text-center fs-26 fw-bold me-3 booking-digitsms-${i}" 
                               id="booking-digitsms-${i}" 
                               name="booking-digitsms-${i}" 
                               data-next="${nextId}" 
                               data-previous="${prevId}" 
                               maxlength="1">
                    `;
                }

                inputsSMSHtml += "</div>";
                inputSMSContainer.append(inputsSMSHtml);

                $(".inputSMSContainer").on("input", "input", function () {
                    const maxLength = $(this).attr("maxlength") || 1;
                    if (this.value.length >= maxLength) {
                        const next = $(this).data("next");
                        if (next) {
                            $("#" + next).focus();
                        }
                    }
                });

                $(".inputSMSContainer").on("keydown", "input", function (e) {
                    if (e.key === "Backspace" && this.value === "") {
                        const prev = $(this).data("previous");
                        if (prev) {
                            $("#" + prev).focus();
                        }
                    }
                });

                $(".inputSMSContainer").on("click", "input", function () {
                    $(this).select();
                });

                if (data.otp_type === "email") {
                    hideOtpLoader();
                    const otpEmailMessage = document.querySelector(".otp-email-message");
                    if (otpEmailMessage) {
                        otpEmailMessage.textContent = `OTP sent to your Email Address ${email}`;
                    }
                    bookingOtpEmailModal.show();
                    startTimer(otpExpireTime);
                } else if (data.otp_type === "sms") {
                    hideOtpLoader();
                    const otpSmsMessage = document.querySelector(".otp-email-message");
                    if (otpSmsMessage) {
                        otpSmsMessage.textContent = `OTP sent to your mobile number ending ******${phoneNumber.slice(
                            -4
                        )}`;
                    }
                    bookingOtpSmsModal.show();
                    startSmsTimer(otpExpireTime);
                } else {
                    hideOtpLoader();
                }
                }
            },
            error: function (xhr) {
                hideOtpLoader();
                toastr.error("Failed to send OTP.");
            },
        });
    }

    function showOtpLoader() {
        const loader = document.getElementById("pageLoader");
        if (loader) {
            loader.style.display = "block";
        }
    }

    function hideOtpLoader() {
        const loader = document.getElementById("pageLoader");
        if (loader) {
            loader.style.display = "none";
        }
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
                        toastr.error("An error occurred while sending OTP.");
                    } else {
                        toastr.error("An error occurred while sending OTP.");
                    }
                    reject(error);
                },
            });
        });
    }

    let emailTimerInterval, smsTimerInterval; // Separate interval variables
    let emailTimerTime, smsTimerTime; // Separate time variables

    function startTimer(expireTime) {
        clearInterval(emailTimerInterval); // Clear any existing timer
        emailTimerTime = expireTime * 60; // Convert minutes to seconds

        emailTimerInterval = setInterval(() => {
            let minutes = Math.floor(emailTimerTime / 60);
            let seconds = emailTimerTime % 60;
            bookingOtpTimerDisplay.textContent = `${String(minutes).padStart(
                2,
                "0"
            )}:${String(seconds).padStart(2, "0")}`;
            emailTimerTime--;

            if (emailTimerTime < 0) {
                clearInterval(emailTimerInterval);
                bookingOtpTimerDisplay.textContent = "00:00"; // Timer finished
            }
        }, 1000);
    }

    function startSmsTimer(expireSmsTime) {
        clearInterval(smsTimerInterval); // Clear any existing timer
        smsTimerTime = expireSmsTime * 60; // Convert minutes to seconds

        smsTimerInterval = setInterval(() => {
            let minutes = Math.floor(smsTimerTime / 60);
            let seconds = smsTimerTime % 60;
            bookingOtpSmsTimerDisplay.textContent = `${String(minutes).padStart(
                2,
                "0"
            )}:${String(seconds).padStart(2, "0")}`;
            smsTimerTime--;

            if (smsTimerTime < 0) {
                clearInterval(smsTimerInterval);
                bookingOtpSmsTimerDisplay.textContent = "00:00";
            }
        }, 1000);
    }

    function resendOtp(type) {
        const email = $("[name='email']").val();
        const forgot_email = $("[name='forgot_email']").val();

        let emailId = "";

        if (email) {
            emailId = email;
        } else if (forgot_email) {
            emailId = forgot_email;
        }

        const url = "/otp-settings"; // Replace with your actual endpoint
        const data = { email: emailId, type: type };

        $.ajax({
            url: url,
            type: "POST",
            data: data,
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
            },
            beforeSend: function () {
                showOtpLoader();
            },
            success: function (data) {
                const otpExpireTime = parseInt(
                    data.otp_expire_time.split(" ")[0]
                );

                if (type === "email") {
                    hideOtpLoader();
                    bookingOtpEmailModal.show();
                    startTimer(otpExpireTime);
                } else if (type === "sms") {
                    hideOtpLoader();
                    bookingOtpSmsModal.show();
                    startSmsTimer(otpExpireTime);
                } else {
                    hideOtpLoader();
                }
            },
            error: function (xhr) {
                hideOtpLoader();
                toastr.error("Failed to resend OTP. Please try again.");
            },
        });
    }

    $("#booking-verify-email-otp-btn").on("click", function () {
        const email = $("[name='email']").val();
        const otpDigitLimit = $(".inputcontainer input").length;
        const forgot_email = $("[name='forgot_email']").val();
        const login_type = "forgot_email";

        const otp = [];
        for (let i = 1; i <= otpDigitLimit; i++) {
            const digit = $(`#booking-digit-${i}`).val();
            otp.push(digit);
        }
        const otpString = otp.join("");

        let requestData = {
            otp: otpString,
        };

        if (email) {
            requestData.email = email;
        } else if (forgot_email) {
            requestData.forgot_email = forgot_email;
            requestData.login_type = login_type;
        }

        $.ajax({
            url: "/verify-otp",
            type: "POST",
            data: requestData,
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
            },
            beforeSend: function () {
                $(".booking-verify-email-otp-btn").attr("disabled", true);
                $(".booking-verify-email-otp-btn").html(
                    "<div class='spinner-border text-light' role='status'></div>"
                );
            },
            success: function (response) {
                $("#booking-otp-email-modal").modal("hide");
                bookingVerifyUserId = response.user_id;
                if (response.csrf_token) {
                    $('meta[name="csrf-token"]').attr("content", response.csrf_token);
                }

                $("#fifth-field").css("display", "none");
                $("#sixth-field").css("display", "flex");
                $("#bokingwizard li.prinfo")
                    .removeClass("active")
                    .addClass("activated");
                $("#bokingwizard li.cart").addClass("active");
                $("#bookcomplete").text("75");

                $("#bookcompletes").text("60");
            },
            error: function (xhr) {
                const errorMessage = xhr.responseJSON.error || "OTP Required";
                bookingVerifyUserId = null;

                $("#booking_error_message").text(errorMessage);
            },
            complete: function () {
                // Reset the button and remove the spinner
                $(".booking-verify-email-otp-btn").attr("disabled", false);
                $(".booking-verify-email-otp-btn").html("Verify OTP");
            },
        });
    });

    $("#booking-verify-sms-otp-btn").on("click", function () {
        const email = $("[name='email']").val();
        const otpDigitLimit = $(".inputcontainer input").length;

        const otp = [];
        for (let i = 1; i <= otpDigitLimit; i++) {
            const digit = $(`#booking-digitsms-${i}`).val();
            otp.push(digit);
        }
        const otpString = otp.join("");

        $.ajax({
            url: "/verify-otp",
            type: "POST",
            data: {
                email: email,
                otp: otpString,
            },
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content"),
            },
            success: function () {
                // Hide the OTP modal
                $("#booking-otp-phone-modal").modal("hide");
                bookingVerifyUserId = response.user_id;
                if (response.csrf_token) {
                    $('meta[name="csrf-token"]').attr("content", response.csrf_token);
                }

                $("#fifth-field").css("display", "none");
                $("#sixth-field").css("display", "flex");
                $("#bokingwizard li.prinfo")
                    .removeClass("active")
                    .addClass("activated");
                $("#bokingwizard li.cart").addClass("active");
                $("#bookcomplete").text("75");

                $("#bookcompletes").text("60");
            },
            error: function (xhr) {
                const errorMessage = xhr.responseJSON.error || "OTP Required";
                bookingVerifyUserId = null;
                $("#booking_error_sms_message").text(errorMessage); // Update the text content
            },
        });
    });

    $(".bookingResendEmailOtp").on("click", function () {
        resendOtp("email");
    });

    $(".bookingResendSMSOtp").on("click", function () {
        resendOtp("sms");
    });

    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
    // ---------------------------------------------------------------------------------
    // Booking without Location(Btanch) and Staff
    // ---------------------------------------------------------------------------------
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>

    // booking Datetimepicker
    if ($(".bookingDatepics").length > 0) {
        $(".bookingDatepics")
            .datetimepicker({
                format: "DD-MM-YYYY",
                keepOpen: true,
                inline: true,
                icons: {
                    up: "fas fa-angle-up",
                    down: "fas fa-angle-down",
                    next: "fas fa-angle-right",
                    previous: "fas fa-angle-left",
                },
                minDate: moment(), // Disables past dates
            })
            .on("dp.change", function (event) {
                var selectedDate = event.date.format("DD-MM-YYYY");
                $("#selected_date").val(selectedDate);
                $(".slot_time").text("");
                $(".slot_day").text("");
                $(".slot_time_day").text("");
                $(".final-time").text("");
                $("#from_time").val("");
                $("#to_time").val("");
                var serviceId = $("#service_id").val();
                fetchOnlySlot(serviceId, selectedDate);
            });
    }

    function fetchPersonal(addServiceIds = []) {
        $(".slotLoader").show();
        $.ajax({
            url: "/get-personal-info",
            type: "GET",
            data: {
                addService_ids: addServiceIds,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (data) {
                let serviceNames = data.addService_info
                    .map(function (service) {
                        return `${
                            service.name
                        } - ${service.symbol}${parseFloat(service.price).toFixed(2)}`;
                    })
                    .join("<br>"); // For HTML line breaks

                // Render into .service-names (HTML line breaks)
                $(".service-names").html(serviceNames);

                // Conditional rendering into .additional-service-output
                if (serviceNames.trim() !== "") {
                    $(".additional-service-output").html(serviceNames);
                } else {
                    $("h6.add:contains('Additional Service')").remove();
                }

                $("#booking_first_name").val(data.user_info.first_name);
                $("#booking_last_name").val(data.user_info.last_name);
                $("#booking_email").val(data.user_info.email);
                $("#phone_number").val(data.user_info.phone_number);
                $("#address").val(data.user_info.address);
                $("#city").val(data.user_info.city);
                $("#state").val(data.user_info.state);
                $("#postal").val(data.user_info.postal_code);
                $(".slot_time").text(data.formatted_date_times);
                $(".slot_day").text(data.formatted_date_times);
                $(".slot_time_day").text(data.formatted_date_times);
                $(".slotLoader").hide();
            },
            error: function (xhr) {
                const errorMessage =
                    xhr.responseJSON && xhr.responseJSON.error
                        ? xhr.responseJSON.error
                        : "Failed to fetch details. Please try again.";
                toastr.error(errorMessage);
            },
        });
    }

    $(document).ready(function () {
        //addService Validation
        $("#addService-btns").on("click", function (event) {
            event.preventDefault();

            let addServiceFormData = $("#addService-form").serializeArray();

            if ($("#addService-form").valid()) {
                let formDataCollection = {};
                addServiceFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                $("#slot-input").empty();
                let addServiceIds = $(
                    "input[name='additionalService_id[]']:checked"
                )
                    .map(function () {
                        return this.value;
                    })
                    .get();
                fetchPersonal(addServiceIds);

                var serviceId = $("#service_id").val();
                let today = new Date();
                let day = String(today.getDate()).padStart(2, "0");
                let month = String(today.getMonth() + 1).padStart(2, "0"); // Months are zero-indexed
                let year = today.getFullYear();
                let selectedDate = `${day}-${month}-${year}`;
                fetchOnlySlot(serviceId, selectedDate);
                $("#bookcompletes").text("20");

                $("#first-field").css("display", "none");
                $("#fourth-field").css("display", "flex");
                $("#bokingwizard li.addservice")
                    .removeClass("active")
                    .addClass("activated");
                $("#bokingwizard li.datetime").addClass("active");
            }
        });

        //slot Validation
        $("#slot-btns-new").on("click", function (event) {
            event.preventDefault();

            let slotFormData = $("#slot-form").serializeArray();

            var timeStatus = $("#time_staus").val();

            if (
                timeStatus === "1" &&
                $('input[name="slot_id"]:checked').length === 0
            ) {
                toastr.error("Please select a slot before continuing.");
                return;
            }

            if ($("#slot-form").valid()) {
                let formDataCollection = {};
                slotFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                var selectedDate = $("#selected_date").val();
                var selectedSlot = $('input[name="slot_id"]:checked').val();

                fetchSlotInfo(selectedDate, selectedSlot);
                let addServiceIds = $(
                    "input[name='additionalService_id[]']:checked"
                )
                    .map(function () {
                        return this.value;
                    })
                    .get();
                fetchPersonal(addServiceIds);
                $("#bookcompletes").text("25");
                $(".slotLoader").hide();
                $("#first-field").css("display", "none");
                $("#fifth-field").css("display", "flex");
                $("#bokingwizard li.datetime")
                    .removeClass("active")
                    .addClass("activated");
                $("#bokingwizard li.prinfo").addClass("active");
            }
        });

        $("#slot-btns").on("click", function (event) {
            event.preventDefault();

            let slotFormData = $("#slot-form").serializeArray();

            var timeStatus = $("#time_staus").val();

            if (
                timeStatus === "1" &&
                $('input[name="slot_id"]:checked').length === 0
            ) {
                toastr.error("Please select a slot before continuing.");
                return;
            }

            if ($("#slot-form").valid()) {
                let formDataCollection = {};
                slotFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });

                var selectedDate = $("#selected_date").val();
                var selectedSlot = $('input[name="slot_id"]:checked').val();

                fetchSlotInfo(selectedDate, selectedSlot);
                $("#bookcompletes").text("40");
                $("#fourth-field").css("display", "none");
                $("#fifth-field").css("display", "flex");
                $("#bokingwizard li.datetime")
                    .removeClass("active")
                    .addClass("activated");
                $("#bokingwizard li.prinfo").addClass("active");
            }
        });

        $("#slot-prevs").on("click", function (event) {
            event.preventDefault();
            $("#bookcompletes").text("0");
            $("#fourth-field").css("display", "none");
            $("#first-field").css("display", "flex");
            $("#bokingwizard li.datetime").removeClass("active");
            $("#bokingwizard li.addservice")
                .removeClass("activated")
                .addClass("active");
        });

        //prinfo Validation
        $("#prinfo-btns").on("click", function (event) {
            event.preventDefault();

            let prinfoFormData = $("#prinfo-form").serializeArray();
            let isValid = true;

            $(".print-info").each(function () {
                if ($.trim($(this).val()) == "") {
                    let label = $(this)
                        .closest("div")
                        .find("label")
                        .text()
                        .replace(/[:*]/g, "")
                        .trim();
                    toastr.error(
                        label + " " + $("#prinfo-form").data("is_required_text")
                    );
                    isValid = false;
                    return false;
                }
            });

            if ($("#prinfo-form").valid() && isValid) {
                let formDataCollection = {};
                prinfoFormData.forEach(function (item) {
                    formDataCollection[item.name] = item.value;
                });
                let serviceId = $("#service_id").val();
                let addServiceIds = $(
                    "input[name='additionalService_id[]']:checked"
                )
                    .map(function () {
                        return this.value;
                    })
                    .get();
                fetchPayout(serviceId, addServiceIds);
                let authId = $("body").data("authid");

                if (bookingVerifyUserId || authId) {
                    $("#bookcompletes").text("60");

                    $("#fifth-field").css("display", "none");
                    $("#sixth-field").css("display", "flex");
                    $("#bokingwizard li.prinfo")
                        .removeClass("active")
                        .addClass("activated");
                    $("#bokingwizard li.cart").addClass("active");
                } else {
                    verifyUser(formDataCollection);
                }
            }
        });

        $("#prinfo-prevs").on("click", function (event) {
            event.preventDefault();
            $(".slotLoader").hide();
            $("#bookcompletes").text("20");
            $("#fifth-field").css("display", "none");
            $("#fourth-field").css("display", "flex");
            $("#bokingwizard li.prinfo").removeClass("active");
            $("#bokingwizard li.datetime")
                .removeClass("activated")
                .addClass("active");
        });

        $("#prinfo-prevs-new").on("click", function (event) {
            event.preventDefault();
            $("#bookcompletes").text("0");
            $("#fifth-field").css("display", "none");
            $("#first-field").css("display", "flex");
            $("#bokingwizard li.prinfo").removeClass("active");
            $("#bokingwizard li.datetime")
                .removeClass("activated")
                .addClass("active");
        });

        $("#cart-prevs-new").on("click", function (event) {
            event.preventDefault();
            $("#bookcompletes").text("25");
        });

        $("#cart-btns-new").on("click", function (event) {
            event.preventDefault();
            $("#bookcompletes").text("75");
        });

        $("#back-prevs-new").on("click", function (event) {
            event.preventDefault();
            $("#bookcompletes").text("50");
        });

        $("#cart-prevs").on("click", function (event) {
            event.preventDefault();
            $("#bookcompletes").text("40");
        });

        $("#cart-btns").on("click", function (event) {
            event.preventDefault();
            $("#bookcompletes").text("80");
        });

        $("#back-prevs").on("click", function (event) {
            event.preventDefault();
            $("#bookcompletes").text("60");
        });

        const currentYear = new Date().getFullYear();
        const expiryYearSelect = $("#expiry_year");
        for (let i = 0; i < 15; i++) {
            const year = currentYear + i;
            expiryYearSelect.append(`<option value="${year}">${year}</option>`);
        }

        $("#pay-btn").on("click", function (event) {
            event.preventDefault();

            const selectedPaymentMethod = $(
                'input[name="payment_type"]:checked'
            ).val();

            if (!selectedPaymentMethod) {
                alert("Please select a payment method.");
                return;
            }

            if (selectedPaymentMethod === "bank") {
                $("#bankTransferModal").modal("show");
                return;
            }

            if (selectedPaymentMethod === "authorizenet") {
                $("#paymentModalNet").modal("show");
                return;
            }

            submitBooking(true);
        });

        /**
         * Place Order (No Payment)
         */
        $(document).on('click', '#cart-place-order', function (e) {
            e.preventDefault();

            // Force payment_type NULL
            $('input[name="payment_type"]').prop('checked', false);
            $('#payment_type').val('');

            $('#cart-place-order').prop("disabled", true)
                        .html('<span class="spinner-border spinner-border-sm align-middle" role="status"></span>');

            $("#cart-go-payment").prop("disabled", true);

            submitBooking(false); // no payment
        });

        /**
         * Go to Payment
         */
        $(document).on('click', '#cart-go-payment', function (e) {
            e.preventDefault();

            $("#sixth-field").hide();
            $("#saventh-field").css("display", "flex");

            $("#bokingwizard li.cart").removeClass("active").addClass("activated");
            $("#bokingwizard li.pay").addClass("active");
        });

        /**
         * Payment method change
         */
        $(document).on('change', '.payment-radio', function () {
            $('#payment_type').val($(this).val());
        });
        
        function submitBooking(isPayment) {
            let branchFormData = $("#branch-form").serializeArray();
            let staffFormData = $("#staff-form").serializeArray();
            let addServiceFormData = $("#addService-form").serializeArray();
            let slotFormData = $("#slot-form").serializeArray();
            let prinfoFormData = $("#prinfo-form").serializeArray();
            let paymentFormData = $("#payment-form").serializeArray();
    
            const all = 1;
    
            if (all === 1) {
                let finalFormData = new FormData();
    
                finalFormData.append(
                    "_token",
                    $('meta[name="csrf-token"]').attr("content")
                );
    
                [
                    ...branchFormData,
                    ...staffFormData,
                    ...addServiceFormData,
                    ...slotFormData,
                    ...prinfoFormData,
                    ...paymentFormData,
                ].forEach(function (item) {
                    finalFormData.append(item.name, item.value);
                });

                if (!isPayment) {
                    finalFormData.set('payment_type', '');
                }
    
                $("#serviceLoader").show();
    
                $.ajax({
                    url: "/user/payment",
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
                        $("#cart-place-order").removeAttr("disabled").html("Place Order");
                        $("#cart-go-payment").prop("disabled", false);
                        $("#serviceLoader").show();
                        $("#bookcomplete").text("100");
    
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
    
                        if (response.url) {
                            window.location.href = response.url;
                        }
    
                        //Stripe
                        if (response.stripurl) {
                            window.location.href = response.stripurl;
                        }
    
                        //CashFree
                        if (
                            response &&
                            response.cashfree &&
                            response.cashfree.redirect_url
                        ) {
                            window.location.href =
                                response.cashfree.redirect_url;
                        }
    
                        //Mercadopago
                        if (
                            response &&
                            response.mercadopago &&
                            response.mercadopago.redirect_url
                        ) {
                            window.location.href =
                                response.mercadopago.redirect_url;
                        }
    
                        //Paystack
                        if (
                            response &&
                            response.paystack &&
                            response.paystack.redirect_url
                        ) {
                            window.location.href =
                                response.paystack.redirect_url;
                        }
    
                        //RazerPay
                        if (response.razorpay) {
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
                                    form.action = "/razorpay/payment-success";
    
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
    
                        //Paypal
                        if (response.code === 200) {
                            if (response.paypal_url) {
                                window.location.href = response.paypal_url; // Redirect to the PayPal URL
                                $("#serviceLoader").show();
                            } else {
                                if (!isPayment) {
                                    $("#sixth-field").css("display", "none");
                                } 
                                $("#saventh-field").css("display", "none");
                                $("#eight-field").css("display", "flex");
                                $("#bokingwizard li.pay")
                                    .removeClass("active")
                                    .addClass("activated");
                                $("#bokingwizard li.confime")
                                    .removeClass("active")
                                    .addClass("activated");
                                $("#serviceLoader").hide();
                                $("#bookcomplete").text("100");
                            }
                        }
                    })
                    .fail((error) => {
                        $("#serviceLoader").hide();
                        $(".error-text").text("");
                        $(".form-control").removeClass("is-invalid");
                        $(".pay-btn").removeAttr("disabled");
                        $(".pay-btn").html("Pay");
                        $("#cart-place-order").removeAttr("disabled").html("Place Order");
                        $("#cart-go-payment").prop("disabled", false);
                        if (error.status === 422) {
                            if (error.responseJSON.errors) {
                                $.each(
                                    error.responseJSON.errors,
                                    function (key, val) {
                                        $("#" + key).addClass("is-invalid");
                                        $("#" + key + "_error").text(val[0]);
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
        }
        $("#submit-bank-payment").on("click", function (e) {
            e.preventDefault(); // prevent form submission

            const receipt = $("#payment_receipt").val();

            if (!receipt) {
                toastr.error(
                    "Please upload the payment receipt before proceeding."
                );
                return;
            }

            $("#bankTransferModal").modal("hide");

            let branchFormData = $("#branch-form").serializeArray();
            let staffFormData = $("#staff-form").serializeArray();
            let addServiceFormData = $("#addService-form").serializeArray();
            let slotFormData = $("#slot-form").serializeArray();
            let prinfoFormData = $("#prinfo-form").serializeArray();
            let paymentFormData = $("#payment-form").serializeArray();

            const all = 1;

            if (all === 1) {
                let finalFormData = new FormData();

                finalFormData.append(
                    "_token",
                    $('meta[name="csrf-token"]').attr("content")
                );

                [
                    ...branchFormData,
                    ...staffFormData,
                    ...addServiceFormData,
                    ...slotFormData,
                    ...prinfoFormData,
                    ...paymentFormData,
                ].forEach(function (item) {
                    finalFormData.append(item.name, item.value);
                });

                $("#serviceLoader").show();

                const receiptFile = $("#payment_receipt")[0].files[0];

                if (receiptFile) {
                    finalFormData.append("payment_receipt", receiptFile);
                }

                $.ajax({
                    url: "/user/payment",
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

                        if (response.url) {
                            window.location.href = response.url;
                        }

                        //Stripe
                        if (response.stripurl) {
                            window.location.href = response.stripurl;
                        }

                        //CashFree
                        if (
                            response &&
                            response.cashfree &&
                            response.cashfree.redirect_url
                        ) {
                            window.location.href =
                                response.cashfree.redirect_url;
                        }

                        //Paystack
                        if (
                            response &&
                            response.paystack &&
                            response.paystack.redirect_url
                        ) {
                            window.location.href =
                                response.paystack.redirect_url;
                        }

                        //RazerPay
                        if (response.razorpay) {
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
                                    form.action = "/razorpay/payment-success";

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

                        //Paypal
                        if (response.code === 200) {
                            if (response.paypal_url) {
                                window.location.href = response.paypal_url; // Redirect to the PayPal URL
                                $("#serviceLoader").show();
                            } else {
                                $("#saventh-field").css("display", "none");
                                $("#eight-field").css("display", "flex");
                                $("#bokingwizard li.pay")
                                    .removeClass("active")
                                    .addClass("activated");
                                $("#bokingwizard li.confime")
                                    .removeClass("active")
                                    .addClass("activated");
                                $("#serviceLoader").hide();
                                $("#bookcomplete").text("100");
                            }
                        }
                    })
                    .fail((error) => {
                        $("#serviceLoader").hide();
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
                                        $("#" + key + "_error").text(val[0]);
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
            if (!/^\d{13,19}$/.test(cardNumber) || !/^\d{3,4}$/.test(cardCvv)) {
                toastr.error("⚠️ Please enter valid card details.");
                return;
            }

            $("#paymentModalNet").modal("hide");

            let branchFormData = $("#branch-form").serializeArray();
            let staffFormData = $("#staff-form").serializeArray();
            let addServiceFormData = $("#addService-form").serializeArray();
            let slotFormData = $("#slot-form").serializeArray();
            let prinfoFormData = $("#prinfo-form").serializeArray();
            let paymentFormData = $("#payment-form").serializeArray();

            const all = 1;

            if (all === 1) {
                let finalFormData = new FormData();

                finalFormData.append(
                    "_token",
                    $('meta[name="csrf-token"]').attr("content")
                );

                [
                    ...branchFormData,
                    ...staffFormData,
                    ...addServiceFormData,
                    ...slotFormData,
                    ...prinfoFormData,
                    ...paymentFormData,
                ].forEach(function (item) {
                    finalFormData.append(item.name, item.value);
                });

                $("#serviceLoader").show();

                finalFormData.append(
                    "card_holder_name",
                    $("#cardHolderName").val().trim()
                );
                finalFormData.append(
                    "card_number",
                    $("#cardNumber").val().replace(/\s+/g, "")
                );
                finalFormData.append("card_cvv", $("#cardCvv").val().trim());
                finalFormData.append(
                    "card_expiry_month",
                    $("#cardExpiryMonth").val()
                );
                finalFormData.append(
                    "card_expiry_year",
                    $("#cardExpiryYear").val()
                );

                $.ajax({
                    url: "/user/payment",
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

                        if (response.url) {
                            window.location.href = response.url;
                        }

                        //Stripe
                        if (response.stripurl) {
                            window.location.href = response.stripurl;
                        }

                        //CashFree
                        if (
                            response &&
                            response.cashfree &&
                            response.cashfree.redirect_url
                        ) {
                            window.location.href =
                                response.cashfree.redirect_url;
                        }

                        //Paystack
                        if (
                            response &&
                            response.paystack &&
                            response.paystack.redirect_url
                        ) {
                            window.location.href =
                                response.paystack.redirect_url;
                        }

                        //RazerPay
                        if (response.razorpay) {
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
                                    form.action = "/razorpay/payment-success";

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

                        //Paypal
                        if (response.code === 200) {
                            if (response.paypal_url) {
                                window.location.href = response.paypal_url; // Redirect to the PayPal URL
                                $("#serviceLoader").show();
                            } else {
                                $("#saventh-field").css("display", "none");
                                $("#eight-field").css("display", "flex");
                                $("#bokingwizard li.pay")
                                    .removeClass("active")
                                    .addClass("activated");
                                $("#bokingwizard li.confime")
                                    .removeClass("active")
                                    .addClass("activated");
                                $("#serviceLoader").hide();
                                $("#bookcomplete").text("100");
                            }
                        }
                    })
                    .fail((error) => {
                        $("#serviceLoader").hide();
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
                                        $("#" + key + "_error").text(val[0]);
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

    document.addEventListener("DOMContentLoaded", function () {
        const fromTimeInput = document.getElementById("from_time");
        if (fromTimeInput) {
            fromTimeInput.addEventListener("input", function () {
                const [hours, minutes] = this.value.split(":").map(Number);
                let toHours = hours + 1;

                if (toHours >= 24) {
                    toHours = toHours - 24;
                }

                const toTime = `${toHours.toString().padStart(2, "0")}:${minutes
                    .toString()
                    .padStart(2, "0")}`;
                document.getElementById("to_time").value = toTime;
            });
        }
    });
}
