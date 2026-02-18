@extends('admin.admin')

@section('content')
<div class="page-wrapper page-settings">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between -bottom pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('Leads Transactions') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Finance') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Leads Transactions') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 ">
                <div class="card">
                    <div class="card-body p-0 py-3">
                        <div class="custom-datatable-filter  table-responsive">
                            <table class="table admin-transactionList" id="transactionList">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Provider') }}</th>
                                        <th>{{ __('Service') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Payment Type') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="transactionlist">

                                </tbody>
                            </table>
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
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Transaction Details') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Customer:</strong> <span id="transactionCustomer"></span></p>
                <p><strong>Provider:</strong> <span id="transactionProvider"></span></p>
                <p><strong>Service:</strong> <span id="transactionService"></span></p>
                <p><strong>Amount:</strong> $<span id="transactionAmount"></span></p>
                <p><strong>Tax:</strong> $<span id="transactionTax"></span></p>
                <p><strong>Date:</strong> <span id="transactionDate"></span></p>
                <p><strong>Payment Type:</strong> <span id="transactionPaymentType"></span></p>
                <p><strong>Status:</strong> <span id="transactionPaymentStatus"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection
