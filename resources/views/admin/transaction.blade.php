@extends('admin.admin')

@section('content')
<div class="page-wrapper page-settings">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between -bottom pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('Transactions') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Finance') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Transactions') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 ">
                <div class="card">
                    <ul class="nav nav-tabs p-3 pb-0" id="transactionsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active"
                                id="bookingTransaction"
                                data-bs-toggle="tab"
                                type="button"
                                role="tab">
                                {{ __('Booking Transaction') }}
                            </button>
                        </li>
                        @if ($leadStatus != 0)
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="leadsTransation"
                                data-bs-toggle="tab"
                                type="button"
                                role="tab">
                                {{ __('Leads Transaction') }}
                            </button>
                        </li>
                        @endif
                    </ul>
                    <div class="card-body p-0 py-3">
                        <table id="loader-table" class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                    </th>
                                    <th>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                    </th>
                                    <th>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                    </th>
                                    <th>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                    </th>
                                    <th>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="custom-datatable-filter  table-responsive">
                            <table class="table admin-transactionList d-none" id="transactionList" data-empty="{{ __('transaction_empty_info') }}">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Provider') }}</th>
                                        <th>{{ __('Service') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Tax') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Payment Type') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Action') }}</th>
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
                                        <th>{{ __('Provider') }}</th>
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
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Transaction Details') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>{{__('Customer')}}:</strong> <span id="transactionCustomer"></span></p>
                <p><strong>{{__('Provider')}}:</strong> <span id="transactionProvider"></span></p>
                <p><strong>{{__('Service')}}:</strong> <span id="transactionService"></span></p>
                <div class="additional_service d-none mb-3">
                    <p class="mb-3"><strong>{{__('Additional Services')}}:</strong></p>
                    <div class="mb-3" id="additional_service_list"></div>
                </div>
                <p><strong>{{__('Amount')}}:</strong> <span id="transactionAmount"></span></p>
                <p><strong>{{__('Tax')}}:</strong> <span id="transactionTax"></span></p>
                <p><strong>{{__('Date')}}:</strong> <span id="transactionDate"></span></p>
                <p><strong>{{__('Type')}}:</strong> <span id="transactionPaymentType"></span></p>
                <p><strong>{{__('Status')}}:</strong> <span id="transactionPaymentStatus"></span></p>
                <div class="mb-3 bank_transfer_proof d-none">
                    <p class="mb-3"><strong>{{__('Bank Transfer Proof')}}:</strong></p>
                    <div class="mb-3 d-flex align-items-center">
                        <a href="#!" target="_blank" class="btn btn-primary me-3" id="view_bank_transfer_proof">
                            <i class="ti ti-eye"></i> {{ __('View Payment Proof') }}
                        </a>
                        <button type="button" class="btn btn-primary d-none" id="verify_bank_transfer">{{ __('Verify') }}</button>
                    </div>
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
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Transaction Details') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
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
