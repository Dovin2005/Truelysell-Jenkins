document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("export_pdf").addEventListener("click", exportToPDF);
    document.getElementById("export_excel").addEventListener("click", exportToExcel);
});

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Load a custom font that supports the ₹ symbol
    doc.addFont("https://fonts.gstatic.com/s/notosans/v27/o-0IIpQlx3QUlC5A4PNr6zRF.ttf", "NotoSans", "normal");
    doc.setFont("NotoSans"); // Set the font to NotoSans

    // Title
    doc.text("Payment Report", 14, 10);

    let headers = [];
    let data = [];

    // Extract headers
    document.querySelectorAll("#paymentReportList thead th").forEach((th) => {
        headers.push(th.innerText);
    });

    // Extract all rows (including hidden rows due to pagination)
    const allRows = document.querySelectorAll("#paymentReportList tbody tr");

    allRows.forEach((row) => {
        let rowData = [];
        row.querySelectorAll("td").forEach((cell, index) => {
            let cellText = cell.innerText;

            // Ensure correct display of currency in "Paid Amount" column
            if (headers[index]?.toLowerCase().includes("paid amount")) {
                cellText = "\u20B9" + cellText.replace(/[^0-9.,]/g, "").trim(); // Prepend ₹ symbol
            }

            rowData.push(cellText);
        });
        data.push(rowData);
    });

    // Generate PDF table
    doc.autoTable({
        head: [headers],
        body: data,
        styles: { font: "NotoSans" }, // Set the font for the table
    });

    // Save the PDF
    doc.save("Payment_Report.pdf");
}

function exportToExcel() {
    let table = document.querySelector("#paymentReportList");
    let wb = XLSX.utils.book_new();
    let ws_data = [];
    let headers = [];

    // Extract headers
    document.querySelectorAll("#paymentReportList thead th").forEach(th => {
        headers.push(th.innerText);
    });
    ws_data.push(headers);

    // Extract all rows (including hidden rows due to pagination)
    const allRows = document.querySelectorAll("#paymentReportList tbody tr");

    allRows.forEach(row => {
        let rowData = [];
        row.querySelectorAll("td").forEach(cell => {
            rowData.push(cell.innerText);
        });
        ws_data.push(rowData);
    });

    // Create worksheet and append to workbook
    let ws = XLSX.utils.aoa_to_sheet(ws_data);
    XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

    // Save the Excel file
    XLSX.writeFile(wb, "Payment_Report.xlsx");
}

if (pageValue === "provider.payment-report") {
    
    const user_id = $('#userId').val();
    $(document).ready(function () {
        
     
        let filter_date = $(".filter_date").val();
        let filter_payment = $(".filter_payment").val();
        let filter_sort = $(".filter_sort").val();
        let filter_type = $(".filter_type").val();
        paymentReportList(filter_date, filter_payment, filter_type, filter_sort, user_id);


        let dateInput = $(".bookingrange");

        // Initialize daterangepicker
        dateInput.daterangepicker({
            opens: "left",
            autoUpdateInput: false, // Keeps the input empty on page load
            locale: {
                format: "DD/MM/YYYY",
                cancelLabel: "Clear",
            },
        });

        // Ensure input is empty and placeholder is shown on page load
        dateInput.val("").attr("placeholder", "dd/mm/yyyy");

        // Update input field only when a date range is selected
        dateInput.on("apply.daterangepicker", function (ev, picker) {
            let selectedDateRange =
                picker.startDate.format("DD/MM/YYYY") +
                " - " +
                picker.endDate.format("DD/MM/YYYY");
            $(this).val(selectedDateRange); // Set selected date range in input

            let filter_date = selectedDateRange;
            let filter_payment = $(".filter_payment").val();
            let filter_sort = $(".filter_sort").val();
            let filter_type = $(".filter_type").val();
            paymentReportList(filter_date, filter_payment, filter_type, filter_sort, user_id);
        });

        // Clear input when cancel is clicked and show placeholder again
        dateInput.on("cancel.daterangepicker", function (ev, picker) {
            $(this).val("").attr("placeholder", "dd/mm/yyyy - dd/mm/yyyy"); // Reset input field and placeholder

            let filter_date = "";
            let filter_type = $(".filter_type").val();
            let filter_sort = $(".filter_sort").val();
            let filter_payment = $(".filter_payment").val();
            paymentReportList(filter_date, filter_payment, filter_type, filter_sort, user_id);
        });

        // Trigger filter function when payment filter changes
        $(document).on("change", ".filter_payment", function () {
            let filter_type = $(".filter_type").val();
            let filter_sort = $(".filter_sort").val();
            let filter_payment = $(this).val();
            let filter_date = dateInput.val(); // Get selected date range (if any)
            paymentReportList(filter_date, filter_payment, filter_type, filter_sort, user_id);
        });
        
        $(document).on("change", ".filter_type", function () {
            let filter_type = $(this).val();
            let filter_payment = $(".filter_payment").val();
            let filter_sort = $(".filter_sort").val();
            let filter_date = dateInput.val(); // Get selected date range (if any)
            paymentReportList(filter_date, filter_payment, filter_type, filter_sort, user_id);
        });

        $(document).on("change", ".filter_sort", function () {
            let filter_sort = $(this).val();
            let filter_type = $(".filter_type").val();
            let filter_payment = $(".filter_payment").val();
            let filter_date = dateInput.val(); // Get selected date range (if any)
            paymentReportList(filter_date, filter_payment, filter_type, filter_sort, user_id);
        });

    });

    function paymentReportList(filter_date, filter_payment, filter_type, filter_sort, user_id) {
        // Get filter values
        let dateRange = filter_date;

        // Send AJAX request with filters
        $.ajax({
            url: "/api/providerpaymentreport",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                order_by: filter_sort,
                sort_by: filter_sort,
                date_range: dateRange,
                filter_payment: filter_payment,
                filter_type: filter_type,
                provider_id: user_id,
            },
            success: function (response) {
                if (
                    response.success &&
                    response.data &&
                    response.data.transactions
                ) {
                    let transactions = response.data.transactions;
                    let paymentSummary = response.data.payment_summary || [];

                    let totalPaymentAmount =
                        response.data.total_payment_amount || "0.00";
                    let totalBookingAmount =
                        response.data.total_booking_amount || "0.00";
                    let totalAmount = response.data.total_amount || "0.00";
                    let currency_total = response.data.transactions[0]?.currency || response.data.currency;

                    // Update the card values dynamically
                    $("#total-payments h5").text(currency_total + totalAmount);
                    $("#total-payments span").text(
                        currency_total + totalAmount
                    );
                    $("#provider-payments h5").text(
                        currency_total + totalBookingAmount
                    );
                    $("#leads-payments h5").text(
                        currency_total + totalPaymentAmount
                    );
                    $("#total_currency span").text(currency_total);

                    let typeToIdMap = {
                        Paypal: "#paypal_chat h5",
                        Stripe: "#strpie_chat h5",
                        Wallet: "#wallet_chat h5",
                        REGULAR: "#subs_chat h5", // REGULAR will be added here
                        TOPUP: "#subs_chat h5", // TOPUP will also be added to the same
                    };

                    let paypalAmount = 0;
                    let stripeAmount = 0;
                    let walletAmount = 0;
                    let regularAmount = 0;
                    let topupAmount = 0;

                    // Loop through the payment summary and accumulate Regular and Topup amounts
                    paymentSummary.forEach((payment) => {
                        if (payment.type === "REGULAR") {
                            regularAmount += payment.amount;
                        }
                        if (payment.type === "TOPUP") {
                            topupAmount += payment.amount;
                        }
                        if (payment.type === "Paypal") {
                            paypalAmount += payment.amount;
                        }
                        if (payment.type === "Mollie") {
                            paypalAmount += payment.amount;
                        }
                        if (payment.type === "Stripe") {
                            stripeAmount += payment.amount;
                        }
                        if (payment.type === "Wallet") {
                            walletAmount += payment.amount;
                        }

                        // Update the amount for other types (Paypal, Stripe, Wallet)
                        let selector = typeToIdMap[payment.type];
                        if (
                            selector &&
                            payment.type !== "REGULAR" &&
                            payment.type !== "TOPUP"
                        ) {
                            $(selector).text(currency_total + payment.amount); // Update the amount dynamically
                        }
                    });

                    let totalSubAmount = regularAmount + topupAmount;
                    $("#subs_chat h5").text(currency_total + totalSubAmount); // Display the sum of REGULAR and TOPUP

                    // Update the respective HTML elements with the amounts for Paypal, Stripe, and Wallet
                    $("#paypal_chat h5").text(currency_total + paypalAmount);
                    $("#strpie_chat h5").text(currency_total + stripeAmount);
                    $("#wallet_chat h5").text(currency_total + walletAmount);

                    if ($("#payment-report").length > 0) {
                        var options = {
                            series: [
                                paypalAmount,
                                stripeAmount,
                                walletAmount,
                                totalSubAmount,
                            ],
                            chart: {
                                type: "donut",
                            },
                            colors: [
                                "#0DCAF0",
                                "#FD3995",
                                "#AB47BC",
                                "#FFC107",
                            ],
                            labels: [
                                "Paypal",
                                "Stripe",
                                "Wallet",
                            ],
                            plotOptions: {
                                pie: {
                                    startAngle: -90,
                                    endAngle: 270,
                                    stroke: {
                                        show: true,
                                        width: 15, // Width of the gap
                                        colors: ["#FFFFFF"], // Color of the gap
                                    },
                                    donut: {
                                        size: "80%", // Adjusts the size of the donut hole
                                    },
                                },
                            },
                            dataLabels: {
                                enabled: false,
                            },
                            legend: {
                                show: false, // Set this to false to hide the legend
                            },
                            annotations: {
                                position: "front", // Ensure it appears above other elements
                                style: {
                                    fontSize: "24px", // Adjust font size
                                    fontWeight: "bold",
                                    color: "#000000", // Change color if needed
                                },
                                text: {
                                    // Set the annotation text
                                    text: "+14%",
                                    // Optional styling for the text box
                                    background: {
                                        enabled: true,
                                        foreColor: "#FFFFFF", // Text color
                                        border: "#000000", // Border color
                                        borderWidth: 1,
                                        borderRadius: 2,
                                        opacity: 0.7,
                                    },
                                },
                                x: "100%", // Center horizontally
                                y: "100%", // Center vertically
                            },
                            responsive: [
                                {
                                    breakpoint: 480,
                                    options: {
                                        chart: {
                                            width: 300,
                                        },
                                        legend: {
                                            show: false, // Also hide legend on smaller screens
                                        },
                                    },
                                },
                            ],
                        };

                        var chart = new ApexCharts(
                            document.querySelector("#payment-report"),
                            options
                        );
                        chart.render();
                    }

                    let tableBody = "";
                    if ($.fn.DataTable.isDataTable("#paymentReportList")) {
                        $("#paymentReportList").DataTable().destroy(); // Destroy existing DataTable instance
                    }
                    $("#paymentReportList tbody").empty(); // Clear the old data

                    if (transactions.length === 0) {
                        $("#paymentReportList").DataTable().destroy();
                        tableBody += `
                            <tr>
                                <td colspan="8" class="text-center">No Data Found!</td>
                            </tr>`;
                            $("#paymentReportList tbody").html(tableBody);
                            return;
                    } else {
                        transactions.forEach((transaction, index) => {
                            let formattedDate = transaction.date;

                            let statusClass = "";
                            switch (transaction.payment?.status) {
                                case "Unpaid":
                                    statusClass = "text-warning";
                                    break;
                                case "Paid":
                                case "Completed":
                                    statusClass = "text-success";
                                    break;
                                case "Refund":
                                    statusClass = "text-danger";
                                    break;
                                case "In Progress":
                                    statusClass = "text-primary";
                                    break;
                                default:
                                    statusClass = "text-secondary";
                                    break;
                            }

                            let currency = transaction.currency || "";
                            let paymentType = transaction.payment?.type || "-";
                            let paymentStatus =
                                transaction.payment?.status || "-";

                            tableBody += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <span class="fw-bold d-block">${
                                                    transaction.customer
                                                        ?.name || "-"
                                                }</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <span class="fw-bold d-block">${
                                                    transaction.provider
                                                        ?.name || "-"
                                                }</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">${
                                        transaction?.type
                                    }</td>
                                    <td class="text-center">${paymentType}</td>
                                    <td>${formattedDate}</td>
                                    <td>${currency}${
                                transaction.payment?.amount || "0.00"
                            }</td>
                                    <td><h6 class="badge-active ${statusClass}">${paymentStatus}</h6></td>
                                </tr>
                            `;
                        });
                    }

                    $("#paymentReportList tbody").html(tableBody);

                    // Reinitialize DataTable after updating content
                    $("#paymentReportList").DataTable({
                        ordering: true,
                        pageLength: 10,
                        language: datatableLang,
                    });
                }
                $('#loader-table').addClass('d-none');
                $(".label-loader, .input-loader").hide();
                $('#paymentReportList, .real-label, .real-input').removeClass('d-none');
            },
            error: function () {
                toastr.error("Unable to fetch session data. Please try again.");
            },
        });
    }
}