@extends('admin.admin')

@section('content')
    <div class="page-wrapper">
        <div class="content bg-white">
            <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('payment_settings') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">{{ __('dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);">{{ __('settings') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('payment_settings') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row">
                @include('admin.partials.general_settings_side_menu')
                <div class="col-xxl-10 col-xl-9">
                    <div class="ps-1">
                        <form action="payment-gateways.html">
                            <div
                                class="d-flex align-items-center justify-content-between flex-wrap border-bottom pt-3 mb-3">
                                <div class="mb-3">
                                    <h5 class="mb-1">{{ __('payment_gateways') }}</h5>
                                    <p>{{ __('payment_settings_configuration') }}</p>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-xxl-4 col-xl-6 d-flex">
                                    <div class="card flex-fill">
                                        <div
                                            class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                                            <div class="skeleton label-skeleton label-loader"></div>
                                            <span
                                                class="d-inline-flex align-items-center justify-content-center border rounded p-2 d-none real-label"><img
                                                    src="{{ asset('assets/img/payment-gateway/payment-gateway-01.svg') }}"
                                                    alt="Img"></span>
                                            <div class="d-flex align-items-center d-none real-label">

                                                <div class="status-toggle modal-status">
                                                    @if (isset($permission))
                                                        @if (hasPermission($permission, 'General Settings', 'edit'))
                                                            <input type="checkbox" id="paypal_status_show" class="check">
                                                            <label for="paypal_status_show" class="checktoggle"> </label>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="skeleton input-skeleton input-loader"></div>
                                            <p class="d-none real-input">{{ __('paypal_description') }}</p>
                                        </div>
                                        <div class="card-footer">
                                            <div class="skeleton input-skeleton input-loader"></div>
                                            <a href="#"
                                                class="btn btn-outline-light d-flex justify-content-center align-items-center fw-semibold d-none real-input"
                                                data-bs-toggle="modal" data-bs-target="#connect_payment_paypal"><i
                                                    class="ti ti-tool me-2"></i>{{ __('configuration') }}</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-xl-6 d-flex">
                                    <div class="card flex-fill">
                                        <div
                                            class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                                            <div class="skeleton label-skeleton label-loader"></div>
                                            <span
                                                class="d-inline-flex align-items-center justify-content-center border rounded p-2 d-none real-label"><img
                                                    src="{{ asset('assets/img/payment-gateway/payment-gateway-02.svg') }}"
                                                    alt="Img"></span>
                                            <div class="d-flex align-items-center">

                                                <div class="status-toggle modal-status d-none real-label">
                                                    @if (isset($permission))
                                                        @if (hasPermission($permission, 'General Settings', 'edit'))
                                                            <input type="checkbox" id="stripe_status_show" class="check">
                                                            <label for="stripe_status_show" class="checktoggle"> </label>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="skeleton input-skeleton input-loader"></div>
                                            <p class="d-none real-input">{{ __('paypal_apis_description') }}</p>
                                        </div>
                                        <div class="card-footer">
                                            <div class="skeleton input-skeleton input-loader"></div>
                                            <a href="#"
                                                class="btn btn-outline-light d-flex justify-content-center align-items-center fw-semibold d-none real-input"
                                                data-bs-toggle="modal" data-bs-target="#connect_payment_stripe"><i
                                                    class="ti ti-tool me-2"></i>{{ __('configuration') }}</a>
                                        </div>
                                    </div>
                                </div>
                                @if (module_view_exists('paymentgateway::credential') && $PaymentGatewayStatus == 1)
                                    @include('paymentgateway::credential')
                                @endif
                                <div class="col-xxl-4 col-xl-6 d-flex d-none">
                                    <div class="card flex-fill">
                                        <div
                                            class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                                            <div class="skeleton label-skeleton label-loader"></div>
                                            <span
                                                class="d-inline-flex align-items-center justify-content-center border rounded p-2 d-none real-label"><img
                                                    src="{{ asset('assets/img/payment-gateway/payment-gateway-16.svg') }}"
                                                    alt="Img"></span>
                                            <div class="d-flex align-items-center">

                                                <div class="status-toggle modal-status d-none real-label">
                                                    @if (isset($permission))
                                                        @if (hasPermission($permission, 'General Settings', 'edit'))
                                                            <input type="checkbox" id="moillie_status_show" class="check">
                                                            <label for="moillie_status_show" class="checktoggle"> </label>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="skeleton input-skeleton input-loader"></div>
                                            <p class="d-none real-input">Mollie</p>
                                        </div>
                                        <div class="card-footer">
                                            <div class="skeleton input-skeleton input-loader"></div>
                                            <a href="#"
                                                class="btn btn-outline-light d-flex justify-content-center align-items-center fw-semibold d-none real-input"
                                                data-bs-toggle="modal" data-bs-target="#connect_payment_mollie"><i
                                                    class="ti ti-tool me-2"></i>{{ __('configuration') }}</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-xl-6 d-flex">
                                    <div class="card flex-fill">
                                        <div
                                            class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                                            <div class="skeleton label-skeleton label-loader"></div>
                                            <span
                                                class="d-inline-flex align-items-center justify-content-center border rounded p-2 d-none real-label"><img
                                                    src="{{ asset('assets/img/payment-gateway/payment-gateway-14.svg') }}"
                                                    alt="Img"></span>
                                            <div class="d-flex align-items-center">

                                                <div class="status-toggle modal-status d-none real-label">
                                                    @if (isset($permission))
                                                        @if (hasPermission($permission, 'General Settings', 'edit'))
                                                            <input type="checkbox" id="bank_status_show" class="check">
                                                            <label for="bank_status_show" class="checktoggle"> </label>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="skeleton input-skeleton input-loader"></div>
                                            <p class="d-none real-input">{{ __('direct_bank_transfer_description') }}</p>
                                        </div>
                                        <div class="card-footer">
                                            <div class="skeleton input-skeleton input-loader"></div>
                                            <a href="#"
                                                class="btn btn-outline-light d-flex justify-content-center align-items-center fw-semibold d-none real-input"
                                                data-bs-toggle="modal" data-bs-target="#connect_payment"><i
                                                    class="ti ti-tool me-2"></i>{{ __('configuration') }}</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-xl-6 d-flex">
                                    <div class="card flex-fill">
                                        <div
                                            class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                                            <div class="skeleton label-skeleton label-loader"></div>
                                            <span
                                                class="d-inline-flex align-items-center justify-content-center border rounded p-2 d-none real-label"><img
                                                    src="{{ asset('assets/img/payment-gateway/payment-gateway-15.svg') }}"
                                                    alt="Img"></span>
                                            <div class="d-flex align-items-center">

                                                <div class="status-toggle modal-status d-none real-label">
                                                    @if (isset($permission))
                                                        @if (hasPermission($permission, 'General Settings', 'edit'))
                                                            <input type="checkbox" id="cod_status_show" class="check">
                                                            <label for="cod_status_show" class="checktoggle"> </label>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="skeleton input-skeleton input-loader"></div>
                                            <p class="d-none real-input">COD</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-xl-6 d-flex">
                                    <div class="card flex-fill">
                                        <div
                                            class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                                            <div class="skeleton label-skeleton label-loader"></div>
                                            <span
                                                class="d-inline-flex align-items-center justify-content-center border rounded p-2 d-none real-label"><img
                                                    src="{{ asset('assets/img/payment-gateway/payment-method-04.svg') }}"
                                                    alt="Img"></span>
                                            <div class="d-flex align-items-center">
                                                <div class="status-toggle modal-status d-none real-label">
                                                    @if (isset($permission))
                                                        @if (hasPermission($permission, 'General Settings', 'edit'))
                                                            <input type="checkbox" id="wallet_status_show"
                                                                class="check">
                                                            <label for="wallet_status_show" class="checktoggle"> </label>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="skeleton input-skeleton input-loader"></div>
                                            <p class="d-none real-input">Wallet</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="connect_payment">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('bank_transfer') }}</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('close') }}">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form enctype="multipart/form-data" id="BankSettingForm">
                    <input type="hidden" name="group_id" id="group_id" class="form-control" value="13">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('bank_name') }}</label>
                                    <input type="text" required id="bank_name" name="bank_name" class="form-control"
                                        placeholder="{{ __('enter_bank_name') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Account Holder Name') }}</label>
                                    <input type="text" required id="account_name" name="account_name"
                                        class="form-control" placeholder="{{ __('enter_account_holder_name') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('account_number') }}</label>
                                    <input type="text" required id="account_number" name="account_number"
                                        class="form-control" placeholder="{{ __('enter_account_number') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('branch_code') }}</label>
                                    <input type="text" required id="branch_code" name="branch_code"
                                        class="form-control" placeholder="{{ __('enter_branch_code') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('cancel') }}</a>
                        @if (isset($permission))
                            @if (hasPermission($permission, 'General Settings', 'edit'))
                                <button type="submit" class="btn btn-primary">{{ __('submit') }}</button>
                            @endif
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="connect_payment_paypal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('paypal') }}</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('close') }}">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form enctype="multipart/form-data" id="PaypalSettingForm">
                    <input type="hidden" name="group_id" id="group_id" class="form-control" value="13">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('paypal_client_id') }}</label>
                                    <input type="text" required id="paypal_id" name="paypal_id" class="form-control"
                                        placeholder="{{ __('enter_api_key') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('paypal_client_secret') }}</label>
                                    <input type="text" required id="paypal_secret" name="paypal_secret"
                                        class="form-control" placeholder="{{ __('enter_secret_key') }}">
                                </div>
                                <div class="mb-3">
                                    <div class="form-label">{{ __('paypal_endpoint_url') }}</div>
                                    <div class="text-gray">https:://yourdomain.com/api/webhook/paypal-subscription</div>
                                </div>
                                <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                    <div class="status-title">
                                        <h5>{{ __('paypal_sandbox_mode') }}</h5>
                                    </div>
                                    <div class="status-toggle modal-status">
                                        <input type="checkbox" id="paypal_live" name="paypal_live" class="check">
                                        <label for="paypal_live" class="checktoggle"> </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('cancel') }}</a>
                        @if (isset($permission))
                            @if (hasPermission($permission, 'General Settings', 'edit'))
                                <button type="submit" id="paypal_button"
                                    class="btn btn-primary">{{ __('submit') }}</button>
                            @endif
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="connect_payment_stripe">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('stripe') }}</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('close') }}">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form enctype="multipart/form-data" id="stiprSettingForm">
                    <input type="hidden" name="group_id" id="group_id" class="form-control" value="13">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('stripe_key') }}</label>
                                    <input type="text" required id="stripe_key" name="stripe_key"
                                        class="form-control" placeholder="{{ __('enter_api_key') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('stripe_secret') }}</label>
                                    <input type="text" required id="stripe_secret" name="stripe_secret"
                                        class="form-control" placeholder="{{ __('enter_secret_key') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('stripe_webhook_secret') }}</label>
                                    <input type="text" id="stripe_webhook_secret" name="stripe_webhook_secret"
                                        class="form-control" placeholder="{{ __('enter_stripe_webhook_secret') }}">
                                </div>
                                <div class="mb-3">
                                    <div class="form-label">{{ __('stripe_endpoint_url') }}</div>
                                    <div class="text-gray">https:://yourdomain.com/api/stripe/subscription/webhook</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('cancel') }}</a>
                        @if (isset($permission))
                            @if (hasPermission($permission, 'General Settings', 'edit'))
                                <button type="submit" class="btn btn-primary">{{ __('submit') }}</button>
                            @endif
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if (module_view_exists('paymentgateway::sub') && $PaymentGatewayStatus == 1)
        @include('paymentgateway::sub')
    @endif
@endsection
