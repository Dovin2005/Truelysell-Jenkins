var pageValue = $('body').data('page');

var frontendValue = $('body').data('frontend');

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

function initTooltip() {
    $('[data-tooltip="tooltip"]').tooltip({
        trigger: 'hover',
    });
}

//transaction
if (pageValue === 'admin.transaction') {
    function truncateText(text, maxLength = 10) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }
    
    $(document).ready(function () {
        bookingTransactionList();
    });

    function bookingTransactionList() {

        $.ajax({
            url: '/api/transactionlist',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                order_by: 'desc',
                sort_by: "id",
            },
            success: function(response) {
                if (response.success && response.data && response.data.transactions) {
                    let transactions = response.data.transactions;
                    let tableBody = "";

                    if (transactions.length === 0) {
                        $('#transactionList').DataTable().destroy();
                        tableBody += `
                            <tr>
                                <td colspan="9" class="text-center">${$('#transactionList').data('empty')}</td>
                            </tr>`;
                    } else {

                        transactions.forEach((transaction, index) => {
                            let formattedDate = transaction.date;

                            let statusClass = '';
                            switch (transaction.payment.status) {
                                case 'Unpaid':
                                    statusClass = 'text-warning';
                                    break;
                                case 'Paid':
                                    statusClass = 'text-success';
                                    break;
                                case 'Refund':
                                    statusClass = 'text-danger';
                                    break;
                                case 'In Progress':
                                    statusClass = 'text-primary';
                                    break;
                                case 'Completed':
                                    statusClass = 'text-success';
                                    break;
                                default:
                                    statusClass = 'text-secondary';
                                    break;
                            }

                            const defaultImage = '/assets/img/profile-default.png';
                            const defaultImage1 = 'front/img/default-placeholder-image.png';

                            let customerImage = transaction.customer.image_url && transaction.customer.image_url !== ''
                                ? `${transaction.customer.image_url}`
                                : defaultImage;

                            let providerImage = transaction.provider.image_url && transaction.provider.image_url !== ''
                                ? `${transaction.provider.image_url}`
                                : defaultImage;

                            let serviceImage = transaction.service.service_image_url && transaction.service.service_image_url !== ''
                                ? `${transaction.service.service_image_url}`
                                : defaultImage1;

                            let currency = transaction.currencySymbol;
                            let paymentType = transaction.payment?.type || 'N/A';
                            let paymentStatus = transaction.payment?.status || 'N/A';

                            tableBody += `
                                <tr>
                                    <td>${transaction.order_id}</td>
                                    <td>
                                        <div class="d-flex align-items-center me-3">
                                            <img src="${customerImage}" class="transactionimg me-3 rounded-circle admin_provider_img" alt="Customer Image">
                                            <div>
                                                <span class="fw-bold d-block">${truncateText(transaction.customer.name)}</span>
                                                <small class="text-muted">${truncateText(transaction.customer.email)}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center me-3">
                                            <img src="${providerImage}" class="transactionimg me-3 rounded-circle admin_provider_img" alt="Provider Image">
                                            <div>
                                                <span class="fw-bold d-block">${truncateText(transaction.provider.name)}</span>
                                                <small class="text-muted">${truncateText(transaction.provider.email)}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="javascript:void(0);" class="table-imgname d-flex align-items-center me-3">
                                            <img src="${serviceImage}" class="transactionimg me-3 rounded-circle admin_provider_img" alt="Service Image">
                                            <span>${truncateText(transaction.service.name)}</span>
                                        </a>
                                    </td>
                                    <td>${currency}${transaction.amount.total_amount}</td>
                                    <td>${currency}${transaction.amount.tax}</td>
                                    <td>${formattedDate}</td>
                                    <td class="text-center">${paymentType}</td>
                                    <td <h6 class="badge-active ${statusClass}">${paymentStatus}</td>
                                    <td>
                                        <div class="table-actions d-flex">
                                            <a class="delete-table view-transaction" href="javascript:void(0);"
                                            data-customer="${transaction.customer.name}"
                                            data-provider="${transaction.provider.name}"
                                            data-service="${transaction.service.name}"
                                            data-amount="${transaction.amount.service_amount}"
                                            data-tax="${transaction.amount.tax}"
                                            data-date="${formattedDate}"
                                            data-payment-type="${paymentType}"
                                            data-payment-status="${paymentStatus}"
                                            data-status="${transaction.status}"
                                            data-currency="${transaction.currencySymbol}"
                                            data-bank_transfer_proof="${transaction.payment?.bank_transfer_proof}"
                                            data-booking_id="${transaction.id}"
                                            data-additional_services='${JSON.stringify(transaction.additional_services)}'>
                                                <i class="ti ti-eye fs-20 m-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            `;

                        });
                        
                    }
                    $('#transactionList tbody').html(tableBody);

                    $(document).on('click', '.view-transaction', function() {
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

                        let customer = $(this).data('customer');
                        let provider = $(this).data('provider');
                        let service = $(this).data('service');
                        let amount = $(this).data('amount');
                        let tax = $(this).data('tax');
                        let date = $(this).data('date');
                        let paymentType = $(this).data('payment-type');
                        let paymentStatus = $(this).data('payment-status');
                        let status = $(this).data('status');
                        let bankTransferProof = $(this).data('bank_transfer_proof');

                        $('#transactionCustomer').text(customer);
                        $('#transactionProvider').text(provider);
                        $('#transactionService').text(service);
                        $('#transactionAmount').text(currency + amount);
                        $('#transactionTax').text(currency + tax);
                        $('#transactionDate').text(date);
                        $('#transactionPaymentType').text(paymentType);
                        $('#transactionPaymentStatus').text(paymentStatus);
                        $('#transactionStatus').text(status);

                        if (paymentType == "Bank Transfer") {
                            if (bankTransferProof) {
                                $('.bank_transfer_proof').removeClass('d-none');
                                $('#view_bank_transfer_proof').attr('href', bankTransferProof);
                            } else {
                                $('.bank_transfer_proof').addClass('d-none');
                            }

                            if (paymentStatus == "Unpaid") {
                                $('#verify_bank_transfer').removeClass('d-none');
                                $('#verify_bank_transfer').attr('data-booking_id', $(this).data('booking_id'));
                            } else {
                                $('#verify_bank_transfer').addClass('d-none');
                            }
                        }

                        $('#veiw_transaction').modal('show');
                    });

                    $('#loader-table').addClass('d-none');
                    $(".label-loader, .input-loader").hide();
                    $('#transactionList, .real-label, .real-input').removeClass('d-none');

                    if ((transactions.length != 0) && !$.fn.DataTable.isDataTable('#transactionList')) {
                        $('#transactionList').DataTable({
                            ordering: true,
                            pageLength: 10,
                            language: datatableLang,
                            order: [[0, "desc"]],
                        });
                    }
                }
            },
            error: function() {
                toastr.error('Unable to fetch session data. Please try again.');
            }
        });

    }

    $(document).on('click', '#verify_bank_transfer', function() {
        let bookingId = $(this).data('booking_id');

        $.ajax({
            url: "/booking/verify-bank-transfer",
            type: "POST",
            dataType: "json",
            data: {
                booking_id: bookingId,
            },
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.code == 200) {
                    toastr.success(response.message);
                    bookingTransactionList();
                    $('#veiw_transaction').modal('hide');
                }
            },
            error: function (error) {
                if (error.responseJSON.code === 500) {
                    toastr.error(error.responseJSON.message);
                } else {
                    toastr.error("An error occurred while verifying bank transfer.");
                }
            }
        });
    });

    $(document).on('click', '#leadsTransation', function() {
        $('#transactionList').addClass('d-none');
        $('#leadsTransactionTable').removeClass('d-none');
        if ($.fn.DataTable.isDataTable('#transactionList')) {
            $('#transactionList').DataTable().destroy();
        }
        if ($.fn.DataTable.isDataTable('#leadsTransactionTable')) {
            $('#leadsTransactionTable').DataTable().destroy();
        }
        $('#leadsTransactionTable tbody').empty();
        $('#loader-table').removeClass('d-none');
        $(".label-loader, .input-loader").show();
        $('#leadsTransactionTable, .real-label, .real-input').addClass('d-none');

        listLeadsTransaction();
    });

    $(document).on('click', '#bookingTransaction', function() {
        $('#leadsTransactionTable').addClass('d-none');
        $('#transactionList').removeClass('d-none');
        if ($.fn.DataTable.isDataTable('#leadsTransactionTable')) {
            $('#leadsTransactionTable').DataTable().destroy();
        }
        if ($.fn.DataTable.isDataTable('#transactionList')) {
            $('#transactionList').DataTable().destroy();
        }
        $('#transactionList tbody').empty();
        $('#loader-table').removeClass('d-none');
        $(".label-loader, .input-loader").show();
        $('#transactionList, .real-label, .real-input').addClass('d-none');
        bookingTransactionList();
    });


    function listLeadsTransaction() {
        
        $.ajax({
            url: "/api/leads/transaction-list",
            type: "POST",
            dataType: "json",
            data: {
                order_by: "desc",
            },
            headers: {
                Authorization: "Bearer " + localStorage.getItem("admin_token"),
                Accept: "application/json",
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.code === 200) {
                    let transactions = response.data;
                    let tableBody = "";

                    if (transactions.length === 0) {
                        $('#leadsTransactionTable').DataTable().destroy();
                        tableBody += `
                            <tr>
                                <td colspan="9" class="text-center">${$('#leadsTransactionTable').data('empty')}</td>
                            </tr>`;
                    } else {
                        transactions.forEach((transaction, index) => {
                            tableBody += `
                                <tr>
                            <td>${index + 1}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="${transaction.customer.profile_image}" class="transactionimg me-3 rounded-circle admin_provider_img" alt="Customer Image">
                                    <div>
                                        <span class="fw-bold d-block">${truncateText(transaction.customer.full_name)}</span>
                                        <small class="text-muted">${truncateText(transaction.customer.email)}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="${transaction.provider.profile_image}" class="transactionimg me-3 rounded-circle admin_provider_img" alt="Provider Image">
                                    <div>
                                        <span class="fw-bold d-block">${truncateText(transaction.provider.full_name)}</span>
                                        <small class="text-muted">${truncateText(transaction.provider.email)}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                ${transaction.category}
                            </td>
                            <td>${transaction.currency}${transaction.payment.amount}</td>
                            <td>${transaction.payment.date}</td>
                            <td class="text-center">${transaction.payment.type}</td>
                            <td> 
                                <span class="badge ${transaction.payment.status == 'Paid' ? 'badge-soft-success' : 'badge-soft-danger'} d-flex align-items-center">
                                    <i class="ti ti-point-filled"></i> ${transaction.payment.status}
                                </span>
                            </td>
                            <td>
                                <div class="table-actions d-flex">
                                    <a class="view-leads-transaction" href="javascript:void(0);"
                                        data-customer="${transaction.customer.full_name}"
                                        data-provider="${transaction.provider.full_name}"
                                        data-category="${transaction.category}"
                                        data-amount="${transaction.payment.amount}"
                                        data-date="${transaction.payment.date}"
                                        data-payment_type="${transaction.payment.type}"
                                        data-payment_status="${transaction.payment.status}">
                                        <i class="ti ti-eye fs-20 m-2"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                            `;
                        });
                    }

                    $('#leadsTransactionTable tbody').html(tableBody);
                    $('#loader-table').addClass('d-none');
                    $(".label-loader, .input-loader").hide();
                    $('#leadsTransactionTable, .real-label, .real-input').removeClass('d-none');

                    if ((transactions.length != 0) && !$.fn.DataTable.isDataTable('#leadsTransactionTable')) {
                        $('#leadsTransactionTable').DataTable({
                            ordering: true,
                            language: datatableLang,
                            pageLength: 10
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

    $(document).on('click', '.view-leads-transaction', function() {
        let customer = $(this).data('customer');
        let provider = $(this).data('provider');
        let category = $(this).data('category');
        let amount = $(this).data('amount');
        let date = $(this).data('date');
        let paymentType = $(this).data('payment_type');
        let paymentStatus = $(this).data('payment_status');

        $('#leadsTransactionCustomer').text(customer);
        $('#leadsTransactionProvider').text(provider);
        $('#leadsTransactionService').text(category);
        $('#leadsTransactionAmount').text(amount);
        $('#leadsTransactionDate').text(date);
        $('#leadsTransactionPaymentType').text(paymentType);
        $('#leadsTransactionPaymentStatus').text(paymentStatus);

        $('#veiw_leads_transaction_modal').modal('show');
    });

}

if (pageValue === 'admin.providertransaction') {

    $('#provider_amount').on("input", function () {
        $(this).val($(this).val().replace(/[^a-zA-Z0-9]/g, ""));
    });

    $(document).on('click', '.transaction_histroy', function () {
        const providerId = $(this).data('provider-id');

        $('#payoutHistoryCards').html('');

        $.ajax({
            url: '/api/provider/get-payout-history',
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
                'Accept': 'application/json',
            },
            data: {
                provider_id: providerId,
            },
            beforeSend: function () {
                // Optionally show a loading indicator
            },
        }).done((response) => {
            if (response.code === 200) {
                const historyData = response.data;

                if (historyData.length > 0) {
                    historyData.forEach((item, index) => {
                        let proofContent = '';

                        if (item.payment_proof_path) {
                            // Check if the payment proof is an image
                            const isImage = /\.(jpg|jpeg|png|gif)$/i.test(item.payment_proof_path);
                            if (isImage) {
                                proofContent = `<img src="${item.payment_proof_path}" class="img-fluid rounded" style="max-height: 150px; max-width: 200px;" alt="Payment Proof">`;
                            } else {
                                proofContent = `<a href="${item.payment_proof_path}" target="_blank" class="btn btn-link">View Proof</a>`;
                            }
                        } else {
                            proofContent = `<span class="text-muted">N/A</span>`;
                        }

                        $('#payoutHistoryCards').append(`
                            <div class="col-12">
                                <div class="card shadow-sm border">
                                    <div class="card-body">
                                        <h5 class="card-title">Payout #${index + 1}</h5>
                                        <p class="mb-1"><strong>Total Amount:</strong> ${item.total_amount}</p>
                                        <p class="mb-1"><strong>Processed Amount:</strong> ${item.processed_amount}</p>
                                        <p class="mb-1"><strong>Available Amount:</strong> ${item.available_amount ?? 'N/A'}</p>
                                        <p class="mb-1"><strong>Payment Date:</strong> ${item.created_at}</p>
                                        <div>${proofContent}</div>
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                } else {
                    $('#payoutHistoryCards').append(`
                        <div class="col-12 text-center">
                            <p class="text-muted">No payout history found.</p>
                        </div>
                    `);
                }
            } else {
                toastr.error(response.message);
            }

            $('#payoutHistoryModal').modal('show');
        }).fail((error) => {
            toastr.error('Failed to fetch payout history. Please try again.');
        });
    });

    $(document).on('click', '#uploadPaymentProof', function() {

        let providerId = $('#provider_id').val();
        let providerName = $('#provider_name').val(); // Set provider name in modal
        let providerEmail = $('#provider_email').val(); // Set provider email in modal
        let totalBookings = $('#total_bookings').val(); // Set total bookings in modal
        let totalEarnings = $('#total_gross_amount').val(); // Set total earnings in modal
        let adminEarnings = $('#total_commission_amount').val(); // Set admin earnings in modal
        let providerPayDue = $('#total_reduced_amount').val(); // The entered amount (provider amount)
        let enteredAmount = parseFloat($('#provider_amount').val()) || 0;
        let remainingAmount = parseFloat($('#remaining_amount').val()) || 0;

        // Check if the entered amount is valid
        if (enteredAmount > providerPayDue) {
            $('#amountError').show(); // Show error if amount exceeds
            return;
        } else {
            $('#amountError').hide();
        }

        // Get the file (payment proof)
        let paymentProof = $('#codFile')[0].files[0]; // The file selected for proof

        // Create FormData object to send data with the file
        let formData = new FormData();
        formData.append('provider_id', providerId);
        formData.append('provider_name', providerName);
        formData.append('provider_email', providerEmail);
        formData.append('total_bookings', totalBookings);
        formData.append('total_earnings', totalEarnings);
        formData.append('admin_earnings', adminEarnings);
        formData.append('provider_pay_due', providerPayDue);
        formData.append('entered_amount', enteredAmount);
        formData.append('payment_proof', paymentProof);
        formData.append('remaining_amount', (remainingAmount - enteredAmount));
        formData.append('payment_method', $("#payment_method").val());

        $.ajax({
            url: '/api/storePayoutHistroy',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#uploadPaymentProof")
                    .attr("disabled", true)
                    .html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
            },
            complete: function () {
                $("#uploadPaymentProof")
                    .attr("disabled", false)
                    .html("Submit Proof");  
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Payout history stored successfully!');
                    podiverEarning();
                    $('#veiw_transaction').modal('hide');
                } else {
                    toastr.error(response.message || 'Failed to store payout history.');
                }
            },
            error: function(xhr, status, error) {
                toastr.error('An error occurred while submitting the proof');
            }
        });
    });

    podiverEarning();

    $(document).on('input', '#provider_amount', function() {
        $(this).val($(this).val().replace(/[^0-9]/g, "").slice(0, 12));
    });

    function podiverEarning(){
        $.ajax({
            url: '/api/providertransactionlist',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    let tableBody = $('.providertransactionlist');
                    tableBody.empty();

                    response.data.forEach((item, index) => {
                        let currency = item.currencySymbol;
                        let provider = item.provider;
                        let transactions = item.transactions;

                        let providerImage = provider.profile_image !== ''
                        ? `${provider.profile_image}`
                        : '/assets/img/profile-default.png';

                        let row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="${providerImage}" class="transactionimg me-3 rounded-circle" alt="Provider Image" style="width: 50px; height: 50px; object-fit: cover;">
                                        <div>
                                            <span class="fw-bold d-block">${provider.name}</span>
                                            <small class="text-muted">${provider.email}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>${transactions.total_bookings}</td>
                                <td>${currency}${transactions.total_gross_amount.toFixed(2)}</td>
                                <td>${currency}${transactions.total_commission_amount.toFixed(2)}</td>
                                <td>${currency}${transactions.total_reduced_amount.toFixed(2)}</td>
                                <td>${currency}${transactions.remaining_amount.toFixed(2)}</td>
                                <td>
                                    <div class="table-actions d-flex justify-content-start align-items-center">
                                        <a class="delete-table view-transaction me-3" href="javascript:void(0);"
                                            data-provider-id="${provider.id}"
                                            data-provider-name="${provider.name}"
                                            data-provider-email="${provider.email}"
                                            data-total-bookings="${transactions.total_bookings}"
                                            data-total-gross-amount="${transactions.total_gross_amount.toFixed(2)}"
                                            data-total-commission-amount="${transactions.total_commission_amount.toFixed(2)}"
                                            data-total-reduced-amount="${transactions.total_reduced_amount.toFixed(2)}"
                                            data-remaining-amount="${transactions.remaining_amount.toFixed(2)}"
                                            data-payout-details='${JSON.stringify(item.payout_details).replace(/'/g, "&apos;")}'>
                                            <i class="ti ti-eye fs-20">view</i>
                                        </a>
                                        <a class="transaction_histroy" href="javascript:void(0);"
                                            data-provider-id="${provider.id}">
                                            <i class="ti ti-file fs-20">history</i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        `;

                        tableBody.append(row);
                    });

                    $(document).on('click', '.view-transaction', function () {
                        let totalReducedAmount = $(this).data('total-reduced-amount');
                        let providerId = $(this).data('provider-id');
                        let providerName = $(this).data('provider-name');
                        let providerEmail = $(this).data('provider-email');
                        let totalBookings = $(this).data('total-bookings');
                        let totalGrossAmount = $(this).data('total-gross-amount');
                        let totalCommissionAmount = $(this).data('total-commission-amount');
                        let remaining_amount = $(this).data('remaining-amount');
                        let payoutDetailRaw = $(this).attr('data-payout-details'); // always use attr when dealing with full JSON strings

                        try {
                            let payoutDetail = JSON.parse(payoutDetailRaw);
                            if (payoutDetail && Object.keys(payoutDetail).length > 0) {
                                $('#payout_type').text($('.provider_payout').data('payment_method') + ': ' + payoutDetail.payment_method);
                                $('#payment_method').val(payoutDetail.payment_method);
                                if (payoutDetail.payment_method == 'Bank Transfer') {
                                    $('#payout_details').html(`
                                        <p>${$('.provider_payout').data('holder_name')}: ${payoutDetail.payout_details.holder_name}</p>
                                        <p>${$('.provider_payout').data('bank_name')}: ${payoutDetail.payout_details.bank_name}</p>
                                        <p>${$('.provider_payout').data('account_number')}: ${payoutDetail.payout_details.account_number}</p>
                                        <p>${$('.provider_payout').data('ifsc')}: ${payoutDetail.payout_details.ifsc}</p>
                                    `);
                                } else {
                                    $('#payout_details').html(`
                                        <p>${$('.provider_payout').data('id')}: ${payoutDetail.payout_details}</p>
                                    `);
                                }
                                $('#no_payout_info').addClass('d-none');
                            } else {
                                $('#payout_type').text('');
                                $('#payout_details').html('');
                                $('#no_payout_info').removeClass('d-none');
                            }
                        } catch (e) {
                            $('#payout_type').text('');
                            $('#payout_details').html('');
                            $('#no_payout_info').removeClass('d-none');
                        }

                        $('#provider_id').val(providerId);
                        $('#provider_name').val(providerName);
                        $('#provider_email').val(providerEmail);
                        $('#total_bookings').val(totalBookings);
                        $('#total_gross_amount').val(totalGrossAmount);
                        $('#total_commission_amount').val(totalCommissionAmount);
                        $('#total_reduced_amount').val(totalReducedAmount);
                        $('#provider_amount_hidden').val(0);
                        $('#provider_amount').val(); // For visible input
                        $('#remaining_amount').val(remaining_amount);


                        $('#provider_amount').attr('max', totalReducedAmount);
                        $('#uploadPaymentProof').prop('disabled', true); // Disable Submit Proof by default
                        $('#codFile').val('');

                        // Show the modal
                        $('#veiw_transaction').modal('show');

                        // Function to validate both conditions
                        function validateConditions() {
                            let enteredAmount = parseFloat($('#provider_amount').val()) || 0;
                            let remainingAmountValid = enteredAmount <= remaining_amount && enteredAmount > 0;
                            let fileUploaded = $('#codFile')[0].files.length > 0;

                            if (remainingAmountValid && fileUploaded) {
                                $('#amountError').hide(); // Hide error message if valid
                                $('#uploadPaymentProof').prop('disabled', false); // Enable submit button
                            } else {
                                if (!remainingAmountValid) {
                                    $('#amountError').show(); // Show error message for invalid amount
                                } else {
                                    $('#amountError').hide(); // Hide error message if valid
                                }
                                $('#uploadPaymentProof').prop('disabled', true); // Disable submit button
                            }
                        }

                        // Validate amount input
                        $('#provider_amount').on('input', function () {
                            validateConditions();
                        });

                        // Validate file upload
                        $('#codFile').on('change', function () {
                            validateConditions();
                        });
                    });

                    $('#loader-table').addClass('d-none');
                    $(".label-loader, .input-loader").hide();
                    $('#providertransactionlist, .real-label, .real-input').removeClass('d-none');

                    if ($('#providertransactionlist').length && !$.fn.DataTable.isDataTable('#providertransactionlist')) {
                        $('#providertransactionlist').DataTable({
                            ordering: true,
                            language: datatableLang
                        });
                    }
                } else {
                    toastr.error(response.message || 'Failed to load data.');
                }
            },
            error: function(error) {
                toastr.error('An error occurred while fetching the data.');
            }
        });
    }
}

if (pageValue === 'admin.providerrequest') {

    $(document).on('change', '#codFile', function () {
        let fileInput = $(this);
        let file = fileInput[0].files[0]; // Get the selected file

        // Check if a file is selected
        if (file) {
            // Enable the submit button
            $('#uploadPaymentProof').prop('disabled', false);
        } else {
            // Disable the submit button if no file is selected
            $('#uploadPaymentProof').prop('disabled', true);
        }
    });

    $(document).on('click', '#uploadPaymentProof', function () {
        let providerId = $('#provider_id').val();
        let Id = $('#id').val();
        let providerAmount = $('#provider_amount').val();
        let fileInput = $('#codFile')[0];
        let file = fileInput.files[0];

        if (!file) {
            toastr.error('Please select a file before submitting.');
            return;
        }

        let formData = new FormData();
        formData.append('provider_id', providerId);
        formData.append('id', Id);
        formData.append('provider_amount', providerAmount);
        formData.append('payment_proof', file);
        formData.append('payment_method', $("#payment_method").val());

        $.ajax({
            url: '/api/updateproviderrequest',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#uploadPaymentProof")
                    .attr("disabled", true)
                    .html(
                        '<div class="spinner-border text-light" role="status"></div>'
                    );
            },
            complete: function () {
                $("#uploadPaymentProof")
                    .attr("disabled", false)
                    .html("Submit Proof");  
            },
            success: function (response) {
                if (response.success) {
                    toastr.success('Payment proof submitted successfully.');
                    $('#veiw_transaction').modal('hide');
                    podiverRequest();
                } else {
                    toastr.error(response.message || 'Failed to submit payment proof.');
                }
            },
            error: function (xhr) {
                let errorMessage = xhr.responseJSON?.message || 'An error occurred while submitting the payment proof.';
                toastr.error(errorMessage);
            }
        });
    });

    podiverRequest();

    $(document).on('input', '#provider_amount', function() {
        $(this).val($(this).val().replace(/[^0-9]/g, "").slice(0, 12));
    });

    function podiverRequest(){
        $.ajax({
            url: '/api/list/provider/request',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {

                if (response.success) {
                    let tableBody = $('.providerrequestlist');
                    let currencySymbol = response.currencySymbol ?? '$';
                    tableBody.empty();

                    response.data.forEach(function(item, index) {
                        let statusLabel = item.status_label;
                        let date = new Date(item.created_at);
                        let formattedDate = `${String(date.getDate()).padStart(2, '0')}-${String(date.getMonth() + 1).padStart(2, '0')}-${date.getFullYear()}`;  // Show date in DD-MM-YYYY format

                        let paymentType = '';
                        switch (item.payment_id) {
                            case 1:
                                paymentType = 'PayPal';
                                break;
                            case 2:
                                paymentType = 'Stripe';
                                break;
                            case 4:
                                paymentType = 'Bank Transfer';
                                break;
                            default:
                                paymentType = 'Unknown';
                                break;
                        }

                        let row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.provider_name}</td>
                                <td>${currencySymbol}${item.amount}</td>
                                <td>${paymentType}</td>
                                <td>${formattedDate}</td>
                                <td>${statusLabel}</td>
                               <td>
                               <div class="table-actions d-flex">
                                    <a class="delete-table view-transaction" href="javascript:void(0);"
                                        data-provider-amount="${item.amount}"
                                        data-provider-id="${item.provider_id}"
                                        data-status="${item.status}"
                                        data-id="${item.id}"
                                        data-currency-symbol="${currencySymbol}"
                                        data-payout-details='${JSON.stringify(item.provider_payout_details).replace(/'/g, "&apos;")}'>
                                        <i class="ti ti-eye fs-20 m-2"></i>                                    
                                    </a>
                                </div>
                            </td>
                            </tr>
                        `;
                        tableBody.append(row);
                    });

                    $(document).on('click', '.view-transaction', function () {
                        let providerAmount = $(this).data('provider-amount');
                        let providerId = $(this).data('provider-id');
                        let status = $(this).data('status');
                        let Id = $(this).data('id');
                        let payoutDetailRaw = $(this).attr('data-payout-details'); // always use attr when dealing with full JSON strings
                        let currencySymbol = $(this).data('currency-symbol');

                        try {
                            let payoutDetail = JSON.parse(payoutDetailRaw);
                            if (payoutDetail && Object.keys(payoutDetail).length > 0) {
                                $('#payout_type').text($('.provider_payout').data('payment_method') + ': ' + payoutDetail.payment_method);
                                $('#payment_method').val(payoutDetail.payment_method);
                                if (payoutDetail.payment_method == 'Bank Transfer') {
                                    $('#payout_details').html(`
                                        <p>${$('.provider_payout').data('holder_name')}: ${payoutDetail.payout_details.holder_name}</p>
                                        <p>${$('.provider_payout').data('bank_name')}: ${payoutDetail.payout_details.bank_name}</p>
                                        <p>${$('.provider_payout').data('account_number')}: ${payoutDetail.payout_details.account_number}</p>
                                        <p>${$('.provider_payout').data('ifsc')}: ${payoutDetail.payout_details.ifsc}</p>
                                    `);
                                } else {
                                    $('#payout_details').html(`
                                        <p>${$('.provider_payout').data('id')}: ${payoutDetail.payout_details}</p>
                                    `);
                                }
                                $('#no_payout_info').addClass('d-none');
                            } else {
                                $('#payout_type').text('');
                                $('#payout_details').html('');
                                $('#no_payout_info').removeClass('d-none');
                            }
                        } catch (e) {
                            $('#payout_type').text('');
                            $('#payout_details').html('');
                            $('#no_payout_info').removeClass('d-none');
                        }

                        $('#id').val(Id);
                        $('#provider_id').val(providerId);
                        $('#provider_amount').val(providerAmount);
                        $('.provider_requested_amount').text(currencySymbol + providerAmount);
                        $('#codFile').val('');
                        if (status == 1) {
                            $('#codUploadSection').hide();
                            $('#filePreview')
                                .html('<div class="alert alert-success mt-3" role="alert">Amount Paid Successfully</div>')
                                .show();
                        } else if (status == 0) {
                            $('#codUploadSection').show();
                            $('#filePreview').hide();
                        }

                        // Open the modal
                        $('#veiw_transaction').modal('show');
                    });

                    $('#loader-table').addClass('d-none');
                    $(".label-loader, .input-loader").hide();
                    $('#providerrequestlist, .real-label, .real-input').removeClass('d-none');

                    if ($('#providerrequestlist').length && !$.fn.DataTable.isDataTable('#providerrequestlist')) {
                        $('#providerrequestlist').DataTable({
                            ordering: true,
                            language: datatableLang
                        });
                    }

                } else {
                    toastr.error('Failed to load provider requests.');
                }
            },
            error: function(error) {
                toastr.error('An error occurred while fetching the data.');
            }
        });

    }


}

if (pageValue === 'admin.refund') {
    function truncateText(text, maxLength = 10) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    $(document).ready(function () {
        list_table();
        // Handle search input
        $('#searchLanguage').on('input', function () {
            list_table(1); // Reset to the first page on new search
        });


    });

    function list_table(page) {
        $.ajax({
            url: '/api/userpayoutrequestlist',
            type: 'POST',
            dataType: 'json',
            data: {
                order_by: 'desc',
                sort_by: 'id',
                page: page,
                search: $('#searchLanguage').val(),
                type: ""
            },
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Accept': 'application/json'
            },
            success: function (response) {
                if (response.code == '200') {
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
                        toastr.error('An error occurred while fetching list.');
                    }
                } else {
                    toastr.error('An error occurred while fetching list.');
                }
            }
        });
    }

    function listTable(response, meta) {
        let list = response.transactions;
        let tableBody = '';

        if (list.length > 0) {
            i=0;
            list.forEach(transaction => {
                i++;
                let statusClass = '';
                switch (transaction.status) {
                    case 'Unpaid':
                        statusClass = 'text-warning';
                        break;
                    case 'Paid':
                        statusClass = 'text-success';
                        break;
                    case 'Refund':
                        statusClass = 'text-danger';
                        break;
                    case 'In Progress':
                        statusClass = 'text-primary';
                        break;
                    case 'Completed':
                        statusClass = 'text-success';
                        break;
                    default:
                        statusClass = 'text-secondary';
                        break;
                }

                const providerdefaultImage = '/assets/img/profile-default.png';
                const userdefaultImage = '/assets/img/user-default.jpg';
                let customerImage = transaction.userimage && transaction.userimage !== ''
                    ? `${transaction.userimage}`
                    : userdefaultImage;
                let productdefault='/front/img/services/add-service-04.jpg';
                let productImagePath = transaction.source_Values && transaction.source_Values !== 'N/A'
                    ? `/storage/${transaction.source_Values}` : productdefault;
                let currency = response.currencySymbol;
                let paymentType = transaction.payment_type || '';
                let paymentStatus = transaction.status || '';

                 tableBody += `
                    <tr>
                        <td>${i}</td>
                        <td>${transaction.bookingdate}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${customerImage}" class="transactionimg me-3 rounded-circle"  style="width: 50px; height: 50px; object-fit: cover;">
                                <div>
                                    <span class="fw-bold d-block">${truncateText(transaction.username)}</span>
                                    <small class="text-muted">${truncateText(transaction.useremail)}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="javascript:void(0);" class="table-imgname">
                               <img src="${productImagePath}" alt="Product Image" class="transactionimg me-2">
                               <span>${transaction.productname}</span>
                            </a>
                        </td>
                        <td>${currency}${transaction.service_amount}</td>
                        <td>${transaction.payment_type}</td>
                        <td <h6 class="badge-active ${statusClass}">${transaction.status}</td>
                        <td>
                            <div class="table-actions d-flex">
                                <a class="delete-table view-transaction" href="javascript:void(0);"
                                data-bookingid="${transaction.id}" data-currency="${currency}" data-amount="${transaction.service_amount}"> <i class="ti ti-receipt fs-20 m-2"></i></a>
                        </td>
                    </tr>
                `;

            });
            $('#userrequestlist tbody').html(tableBody);
        } else {
            if (!list || list.length === 0) {
                $('#userrequestlist').DataTable().destroy();
                $('#userrequestlist').DataTable({
                    paging: false,
                    language: {
                        emptyTable: $('#userrequestlist').data('empty')
                    },
                });
            }
        }

        $('#loader-table').addClass('d-none');
        $(".label-loader, .input-loader").hide();
        $('#userrequestlist, .real-label, .real-input').removeClass('d-none');

        if (!$.fn.dataTable.isDataTable('#userrequestlist')) {
            if (languageId === 2) {


                if ($('#userrequestlist').length && !$.fn.DataTable.isDataTable('#userrequestlist')) {
                    $('#userrequestlist').DataTable({
                        ordering: true,
                        paging: true,
                        pageLength: 10,
                        "language":
                        {
                            "sProcessing": "جارٍ التحميل...",
                            "sLengthMenu": "أظهر _MENU_ مدخلات",
                            "sZeroRecords": "لم يعثر على أية سجلات",
                            "sInfo": "إظهار _START_ إلى _END_ من أصل _TOTAL_ مدخل",
                            "sInfoEmpty": "يعرض 0 إلى 0 من أصل 0 سجل",
                            "sInfoFiltered": "(منتقاة من مجموع _MAX_ مُدخل)",
                            "sInfoPostFix": "",
                            "sSearch": "ابحث:",
                            "sUrl": "",
                            "oPaginate": {
                                "sFirst": "الأول",
                                "sPrevious": "السابق",
                                "sNext": "التالي",
                                "sLast": "الأخير"
                            }
                        }
                    });
                }
            }else{
                $('#userrequestlist').DataTable({
                    "ordering": true,
                });
            }
        }
    }


    function setupPagination(meta) {
        let paginationHtml = '';
        for (let i = 1; i <= meta.last_page; i++) {
            paginationHtml += `<li class="page-item ${meta.current_page === i ? 'active' : ''}"><a class="page-link" href="#">${i}</a></li>`;
        }

        // Handle click event for pagination
        $('#pagination').on('click', '.page-link', function (e) {
            e.preventDefault();
            const page = $(this).text();
        });
    }
    $(document).on('click', '.view-transaction', function() {
        let bookingid = $(this).data('bookingid');
        let amount = $(this).data('amount');
        let currency= $(this).data('currency');
        $('#booking_id').val(bookingid);
        if (amount && currency) {
            $('.refundamt').empty().append(currency+amount);
        }
        $('#veiw_transaction').modal('show');
    });
    $(document).on('change', '#codFile', function () {
        let fileInput = $(this);
        let file = fileInput[0].files[0]; // Get the selected file

        // Check if a file is selected
        if (file) {
            // Enable the submit button
            $('#uploadPaymentProof').prop('disabled', false);
        } else {
            // Disable the submit button if no file is selected
            $('#uploadPaymentProof').prop('disabled', true);
        }
    });
    $(document).on('click', '#uploadPaymentProof', function() {

        let bookingid = $('#booking_id').val();
        // Get the file (payment proof)
        let paymentProof = $('#codFile')[0].files[0]; // The file selected for proof
        let formData = new FormData();
        formData.append('bookingid', bookingid);
        formData.append('payment_proof', paymentProof);
        $.ajax({
            url: '/api/updaterefund',
            type: 'POST',
            data:formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.code==200) {
                    list_table();
                    toastr.success(response.message);
                    $('#veiw_transaction').modal('hide');
                } else {
                    toastr.error(response.message || 'Failed to Refund.');
                }
            },
            error: function(xhr, status, error) {
                toastr.error('An error occurred while submitting the proof: ' + error);
            }
        });
    });
}