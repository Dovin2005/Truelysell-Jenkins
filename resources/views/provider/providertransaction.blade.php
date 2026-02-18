@extends('provider.provider')

@section('content')
<div class="page-wrapper page-settings">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between -bottom pb-3">
            <div class="my-auto mb-2">
                <div class="skeleton label-skeleton label-loader"></div>
                <h3 class="page-title mb-1 d-none real-label">{{ __('Transaction')}}</h3>
                <div class="skeleton label-skeleton label-loader"></div>
                <nav class="d-none real-label">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ Auth::user()->user_type == 2 ? route('provider.dashboard') : route('staff.dashboard') }}">{{ __('Dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Transaction')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">

            </div>
        </div>
        <div class="row">
            <div class="col-md-12 ">
                <div class="card">
                    <input type="hidden" name="user_id" id="user_id" value="{{ Auth::id() }}">
                    <ul class="nav nav-tabs p-3 pb-0" id="transactionsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <div class="skeleton label-skeleton label-loader"></div>
                            <button class="nav-link active d-none real-label"
                                id="bookingTransaction"
                                data-bs-toggle="tab"
                                data-bs-target="#all-booking"
                                type="button"
                                role="tab"
                                aria-controls="all-booking"
                                aria-selected="true">
                                {{ __('Booking Transaction') }}
                            </button>
                        </li>
                        @if ($leadStatus != 0)
                        <li class="nav-item" role="presentation">
                            <div class="skeleton label-skeleton label-loader"></div>
                            <button
                                class="nav-link d-none real-label"
                                id="leadsTransation"
                                data-bs-toggle="tab"
                                data-bs-target="#pending"
                                type="button"
                                role="tab"
                                aria-controls="pending"
                                aria-selected="false">
                                {{ __('Leads Transaction') }}
                            </button>
                        </li>
                        @endif
                    </ul>
                    <div class="card-body p-0">
                        <div class="custom-datatable-filter p-2 border-0">
                            <div class="table-responsive">
                                <table class="table d-none real-label" id="transactionList" data-empty="{{ __('transaction_empty_info') }}">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Customer')}}</th>
                                            <th>{{ __('Service')}}</th>
                                            <th>{{ __('Amount')}}</th>
                                            <th>{{ __('Commission')}}</th>
                                            <th>{{ __('Final Amount')}}</th>
                                            <th>{{ __('Date')}}</th>
                                            <th>{{ __('Payment Type')}}</th>
                                            <th>{{ __('Status')}}</th>
                                            <th>{{ __('Action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="transactionlist">

                                    </tbody>
                                </table>

                                <table class="table d-none" id="leadsTransactionTable" data-empty="{{ __('transaction_empty_info') }}">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Customer') }}</th>
                                            <th>{{ __('Category') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Payment Type') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>

                                <!-- loader Datatable Start-->
                                <table id="loader-table" class="table table-striped table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <p class="d-none real-label"></p>
                                            </th>
                                            <th>
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <p class="d-none real-label"></p>
                                            </th>
                                            <th>
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <p class="d-none real-label"></p>
                                            </th>
                                            <th>
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <p class="d-none real-label"></p>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                            <td>
                                                <div class="skeleton data-skeleton data-loader"></div>
                                                <p class="d-none real-data"></p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <!-- loader Datatable End -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="veiw_transaction">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                <h4 class="modal-title">{{ __('Transaction Details') }}</h4>
                <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <div class="modal-body">
                <p><strong>{{ __('Customer') }}:</strong> <span id="transactionCustomer"></span></p>
                <p><strong>{{ __('Provider') }}:</strong> <span id="transactionProvider"></span></p>
                <p><strong>{{ __('Service') }}:</strong> <span id="transactionService"></span></p>
                <div class="additional_service d-none mb-2">
                    <p class="mb-3"><strong>{{__('Additional Services')}}:</strong></p>
                    <div class="mb-3" id="additional_service_list"></div>
                </div>
                <p><strong>{{ __('Amount') }}:</strong> <span id="transactionAmount"></span></p>
                <p><strong>{{ __('Tax') }}:</strong> <span id="transactionTax"></span></p>
                <p><strong>{{ __('Date') }}:</strong> <span id="transactionDate"></span></p>
                <p><strong>{{ __('Payment Type') }}:</strong> <span id="transactionPaymentType"></span></p>
                <p><strong>{{ __('Transaction Id') }}:</strong>
                    <span id="transactionId" class="text-truncate d-inline-block transaction-id" title="Transaction ID"></span>
                </p>
                <p><strong>Status:</strong> <span id="transactionStatus"></span></p>
                <div id="filePreview" class="mt-3 d-none"></div>
                <div id="codUploadSection" class="mt-3 d-none">
                    <label for="codFile" class="form-label">{{ __('Upload Payment Proof') }}</label>
                    <input type="file" id="codFile" class="form-control" accept="image/*,application/pdf">
                    <div id="filePreview" class="mt-3"></div>
                    <button id="uploadPaymentProof" class="btn btn-primary mt-3" disabled>{{ __('Submit Proof') }}</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="veiw_leads_transaction_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                <h4 class="modal-title">{{ __('Transaction Details') }}</h4>
                <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <div class="modal-body">
                <p><strong>{{ __('Customer') }}:</strong> <span id="leadsTransactionCustomer"></span></p>
                <p><strong>{{ __('Provider') }}:</strong> <span id="leadsTransactionProvider"></span></p>
                <p><strong>{{ __('Category') }}:</strong> <span id="leadsTransactionService"></span></p>
                <p><strong>{{ __('Amount') }}:</strong> <span id="leadsTransactionAmount"></span></p>
                <p><strong>{{ __('Date') }}:</strong> <span id="leadsTransactionDate"></span></p>
                <p><strong>{{ __('Payment Type') }}:</strong> <span id="leadsTransactionPaymentType"></span></p>
                <p><strong>{{ __('Status') }}:</strong> <span id="leadsTransactionPaymentStatus"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>


@endsection