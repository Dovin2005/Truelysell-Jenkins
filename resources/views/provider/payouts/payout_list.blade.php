@extends('provider.provider')

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-4">
            <div class="skeleton label-skeleton label-loader"></div>
            <h4 class="d-none real-label">{{ __('Payout') }}</h4>
            <div class="d-flex align-items-center flex-wrap row-gap-3">
                @if(isset($permission) && Auth::user()->user_type == 4)
                    @if(hasPermission($permission, 'Payout', 'create'))
                    <div class="skeleton label-skeleton label-loader"></div>
                    <a href="javascript:void(0);" class="btn btn-dark d-flex justify-content-center align-items-center d-none real-label"
                        data-bs-toggle="modal" data-bs-target="#set-payout" id="set_payout_btn"><i class="ti ti-settings me-2"></i>{{ __('Set Payout') }}</a>
                    @else
                @endif
                @else
                <div class="skeleton label-skeleton label-loader"></div>
                <a href="javascript:void(0);" class="btn btn-dark d-flex justify-content-center align-items-center d-none real-label"
                    data-bs-toggle="modal" data-bs-target="#set-payout" id="set_payout_btn"><i class="ti ti-settings me-2"></i>{{ __('Set Payout') }}</a>
                @endif
            </div>
        </div>
        <div class="row">

            <!-- Payout card -->
            <div class="col-xl-6 col-md-6 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div class="d-flex align-items-center">
                            <span
                                class="app-icon d-flex justify-content-center align-items-center bg-light rounded-circle fs-20 me-2">
                                <i class="ti ti-wallet"></i>
                            </span>
                            <div>
                                <div class="skeleton label-skeleton label-loader"></div>
                                <span class="fs-14 d-none real-label">{{ __('Available Amount') }}</span>
                                <div class="skeleton label-skeleton label-loader"></div>
                                <h5 class="d-none real-label"><span class="counter available_amount"></span></h5>
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            @if(isset($permission) && Auth::user()->user_type == 4)
                            @if(hasPermission($permission, 'Payout', 'create'))
                            <div class="skeleton label-skeleton label-loader"></div>
                            <a href="#" class="btn btn-dark btn-sm d-none real-label" style="display: none" data-bs-toggle="modal" id="send_request" data-bs-target="#request_amount">{{ __('Send Request') }}</a>
                            @else

                            @endif
                            @else
                            <div class="skeleton label-skeleton label-loader"></div>
                            <a href="#" class="btn btn-dark btn-sm d-none real-label" style="display: none" data-bs-toggle="modal" id="send_request" data-bs-target="#request_amount">{{ __('Send Request') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-md-6 d-flex">
                <div class="card w-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div class="d-flex align-items-center">
                            <span
                                class="app-icon d-flex justify-content-center align-items-center bg-light rounded-circle fs-20 me-2">
                                <i class="ti ti-wallet"></i>
                            </span>
                            <div>
                                <div class="skeleton label-skeleton label-loader"></div>
                                <span class="fs-14 d-none real-label">{{ __('Last Payout') }}</span>
                                <div class="skeleton label-skeleton label-loader"></div>
                                <h5 class="d-none real-label"><span class="counter" id="last_payout"></span></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Payout card -->

        </div>
        <div class="row">
            <div class="card">
                <ul class="nav nav-tabs p-3 pb-3" id="payoutTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <div class="skeleton label-skeleton label-loader"></div>
                        <button class="nav-link active d-none real-label"
                            id="payoutRequest"
                            data-bs-toggle="tab"
                            data-bs-target="#payout-request"
                            type="button"
                            role="tab"
                            aria-controls="payout-request"
                            aria-selected="true">
                            {{ __('payout_request') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <div class="skeleton label-skeleton label-loader"></div>
                        <button
                            class="nav-link d-none real-label"
                            id="payoutHistory"
                            data-bs-toggle="tab"
                            data-bs-target="#payout-history"
                            type="button"
                            role="tab"
                            aria-controls="payout-history"
                            aria-selected="false">
                            {{ __('payout_history') }}
                        </button>
                    </li>
                </ul>
                <div class="custom-datatable-filter border-0">
                    <div class="table-responsive">
                        <table class="table d-none real-label" id="payoutRequestTable" data-empty="{{ __('no_data_available') }}">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Payout Date') }}</th>
                                    <th class="text-center">{{ __('requested_amount') }}</th>
                                    <th>{{ __('Payment Method') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <table class="table d-none" id="payoutHistoryTable" data-empty="{{ __('no_data_available') }}">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Payout Date') }}</th>
                                    <th>{{ __('Total Amount') }}</th>
                                    <th>{{ __('Processed Amount') }}</th>
                                    <th>{{ __('Payment Method') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Payment Processed') }}</th>
                                    <th>{{ __('Payment Proof') }}</th>
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
                                @for ($i = 0; $i < 8; $i++)
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
                                @endfor
                            </tbody>
                        </table>
                        <!-- loader Datatable End -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Page Wrapper -->

<!-- Set Payout -->
<div class="modal fade wallet-modal" id="set-payout" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between  border-0">
                <h5>{{ __('Set Your Payouts') }}</h5>
                <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i
                        class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="bank-selection">
                            <input type="radio" value="1" id="rolelink" class="payout_type"
                                name="attachment">
                            <label for="rolelink">
                                <img src="{{ asset('front/img/icons/paypal.svg') }}" alt="Paypal">
                                <span class="role-check"><i class="fa-solid fa-circle-check"></i></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bank-selection">
                            <input type="radio" value="2" id="rolelink1" class="payout_type"
                                name="attachment">
                            <label for="rolelink1">
                                <img src="{{ asset('front/img/icons/stripe.svg') }}" alt="Stripe">
                                <span class="role-check"><i class="fa-solid fa-circle-check"></i></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bank-selection">
                            <input type="radio" value="4" id="rolelink2" class="payout_type"
                                name="attachment">
                            <label for="rolelink2">
                                <img src="{{ asset('front/img/icons/bank-transfer.svg') }}" alt="image">
                                <span class="role-check"><i class="fa-solid fa-circle-check"></i></span>
                            </label>
                        </div>
                    </div>
                </div>
                <form id="payoutForm">
                    <input type="hidden" id="provider_id" name="provider_id" value="{{ Auth::id() }}">
                    <input type="hidden" id="payout_type" name="payout_type" value="1">
                    <input type="hidden" id="id" name="id" value="">

                    <div id="paypalContainer" style="display: none">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Paypal ID') }}</label>
                                    <input type="text" id="paypal_id" name="paypal_id" class="form-control">
                                    <span class="error-text text-danger" id="paypal_id_error" data-required="{{ __('paypal_id_required') }}"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="stripeContainer" style="display: none">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Stripe ID') }}</label>
                                    <input type="text" id="stripe_id" name="stripe_id" class="form-control">
                                    <span class="error-text text-danger" id="stripe_id_error" data-required="{{ __('stripe_id_required') }}"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="bankContainer" style="display: none">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Account Holder Name') }}</label>
                                    <input type="text" id="holder_name" name="holder_name" class="form-control">
                                    <span class="error-text text-danger" id="holder_name_error" data-required="{{ __('holder_name_required') }}"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Bank Name') }}</label>
                                    <input type="text" id="bank_name" name="bank_name" class="form-control">
                                    <span class="error-text text-danger" id="bank_name_error" data-required="{{ __('bank_name_required') }}"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Account Number') }}</label>
                                    <input type="text" id="account_number" name="account_number" class="form-control">
                                    <span class="error-text text-danger" id="account_number_error" data-required="{{ __('account_number_required') }}"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('IFSC Code') }}</label>
                                    <input type="text" id="ifsc" name="ifsc" class="form-control">
                                    <span class="error-text text-danger" id="ifsc_error" data-required="{{ __('ifsc_required') }}"></span>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gray" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-dark" id="payout_save_btn" data-save="{{ __('Save') }}">{{ __('Save') }}</button>
            </div>
            </form>
        </div>
    </div>
</div>
<!-- /Set payout -->

<!-- Set Payout -->
<div class="modal fade wallet-modal" id="request_amount" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between  border-0">
                <h5>{{ __('Send Amount Request') }}</h5>
                <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i
                        class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <form id="requestForm">
                <div class="modal-body">
                    <div class="bg-light border p-3 rounded mb-3">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div>
                                    <p class="mb-1">{{ __('Available Amount') }}</p>
                                    <span class="text-dark available_amount"></span>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div>
                                    <p class="mb-1">{{ __('Payment Method') }}</p>
                                    <span class="text-dark" id="payment_method"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Enter Amount') }}</label>
                        <input type="text" name="amount" id="amount" class="form-control">
                        <span class="error-text text-danger" id="amount_error"></span>
                    </div>
                    {{-- <p class="d-flex align-items-center mt-2"><i class="feather-info me-2"></i>Minimum request amount is $1.00</p> --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gray" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-dark" id="requestSendBtn">{{ __('Send') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Set payout -->
@endsection