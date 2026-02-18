@extends('admin.admin')

@section('content')

<div class="page-wrapper page-settings">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between -bottom pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('provider_earning') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Finance') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('provider_earning') }}</li>
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
                            <table class="table d-none" id="providertransactionlist">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Provider') }}</th>
                                        <th>{{ __('booking') }}</th>
                                        <th>{{ __('total_earning') }}</th>
                                        <th>{{ __('admin_earning') }}</th>
                                        <th>{{ __('provider_pay_due') }}</th>
                                        <th>{{ __('provider_remaining_amount') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="providertransactionlist">
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
                <h4 class="modal-title">{{ __('Provider_Amount_Send') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="provider_id">
                <input type="hidden" id="provider_name">
                <input type="hidden" id="provider_email">
                <input type="hidden" id="total_bookings">
                <input type="hidden" id="total_gross_amount">
                <input type="hidden" id="total_commission_amount">
                <input type="hidden" id="total_reduced_amount">
                <input type="hidden" id="provider_amount_hidden">
                <input type="hidden" id="remaining_amount">
                <input type="hidden" id="payment_method">
                <h5>{{ __('provider_payout_details') }} :</h5>
                <div class="provider_payout" data-payment_method="{{ __('payment_method') }}" data-id="{{ __('Id') }}" data-bank_name="{{ __('bank_name') }}" data-holder_name="{{ __('Account Holder Name') }}" data-account_number="{{ __('account_number') }}" data-ifsc="{{ __('IFSC Code') }}">
                    <p class="mt-3" id="payout_type"></p>
                    <div id="payout_details"></div>
                    <div class="text-center d-none" id="no_payout_info">{{ __('no_payout_details_found') }}</div>
                </div>
                <div id="codUploadSection">
                    <div class="row">
                        <div class="col-12 mt-3">
                            <label for="provider_amount" class="form-label">{{ __('Amount') }}</label>
                            <input type="text" class="form-control mb-3" id="provider_amount" name="provider_amount">
                            <small id="amountError" class="text-danger " style="display: none;">{{ __('amount_exceed') }}</small>
                        </div>
                        <div class="col-12">
                            <label for="codFile" class="form-label mt-4">{{ __('Upload_Payment_Proof') }}</label>
                            <input type="file" id="codFile" class="form-control" accept="image/*,application/pdf">
                        </div>
                    </div>
                    <div id="filePreview" class="mt-3"></div>
                    <button id="uploadPaymentProof" class="btn btn-primary mt-3" disabled>{{ __('Submit_Proof') }}</button>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="payoutHistoryModal" tabindex="-1" aria-labelledby="payoutHistoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payoutHistoryLabel">{{ __('payout_history') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="payoutHistoryCards" class="row g-3">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
