@extends('admin.admin')

@section('content')

<div class="page-wrapper page-settings">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between -bottom pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('provider_request') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Finance') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('provider_request') }}</li>
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
                            <table class="table d-none" id="providerrequestlist">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('provider_name') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Payment Type') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="providerrequestlist">
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
                <h4 class="modal-title">{{ __('Provider_Requested_Amount') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="provider_id">
                <input type="hidden" id="id">
                <input type="hidden" id="provider_amount">
                <input type="hidden" id="payment_method">

                <h5>{{ __('provider_payout_details') }} :</h5>
                <div class="provider_payout" data-payment_method="{{ __('payment_method') }}" data-id="{{ __('Id') }}" data-bank_name="{{ __('bank_name') }}" data-holder_name="{{ __('Account Holder Name') }}" data-account_number="{{ __('account_number') }}" data-ifsc="{{ __('IFSC Code') }}">
                    <p class="mt-3" id="payout_type"></p>
                    <div id="payout_details"></div>
                    <div class="text-center d-none" id="no_payout_info">{{ __('no_payout_details_found') }}</div>
                </div>
                <div class="mt-3"><label class="me-2 form-label">{{ __('Provider_Requested_Amount') }}: </label><span class="provider_requested_amount"></span></div>
                <div id="filePreview" class=""></div>
                <div id="codUploadSection" class="">
                    <label for="codFile" class="form-label mt-3">{{ __('Upload_Payment_Proof') }}</label>
                    <input type="file" id="codFile" class="form-control" accept="image/*,application/pdf">
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

@endsection
