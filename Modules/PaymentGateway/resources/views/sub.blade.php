    <!-- Razerpay -->
    <div class="modal fade" id="connect_payment_razorpay">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('Razerpay') }}</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('close') }}">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form enctype="multipart/form-data" id="razorpay_credentials_form">
                    <input type="hidden" name="group_id" id="group_id" class="form-control" value="13">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Razorpay Key') }}</label>
                                    <input type="text" required id="razorpay_key" name="razorpay_key"
                                        class="form-control" placeholder="{{ __('Enter Razorpay Key') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Razorpay Secret') }}</label>
                                    <input type="text" required id="razorpay_secret" name="razorpay_secret"
                                        class="form-control" placeholder="{{ __('Enter Razorpay Secret') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Mode') }}</label>
                                    <select name="razorpay_mode" id="razorpay_mode" class="form-control" required>
                                        <option value="test">{{ __('Test') }}</option>
                                        <option value="live">{{ __('Live') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        @if (isset($permission))
                            @if (hasPermission($permission, 'General Settings', 'edit'))
                                <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                            @endif
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- payu -->
    <div class="modal fade" id="connect_payment_payu">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('PayU') }}</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('close') }}">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form enctype="multipart/form-data" id="payu_credentials_form">
                    <input type="hidden" name="group_id" id="group_id" class="form-control" value="13">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="mb-3">
                                    <label class="form-label">{{ __('PayU Merchant Key') }}</label>
                                    <input type="text" required id="payu_merchant_key" name="payu_merchant_key"
                                        class="form-control" placeholder="{{ __('Enter PayU Merchant Key') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('PayU Merchant Salt') }}</label>
                                    <input type="text" required id="payu_merchant_salt" name="payu_merchant_salt"
                                        class="form-control" placeholder="{{ __('Enter PayU Merchant Salt') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('PayU Base URL') }}</label>
                                    <input type="text" required id="payu_base_url" name="payu_base_url"
                                        class="form-control" placeholder="{{ __('e.g., https://test.payu.in') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Mode') }}</label>
                                    <select name="payu_mode" id="payu_mode" class="form-control" required>
                                        <option value="test">{{ __('Test') }}</option>
                                        <option value="live">{{ __('Live') }}</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        @if (isset($permission))
                            @if (hasPermission($permission, 'General Settings', 'edit'))
                                <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                            @endif
                        @endif
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Cashfree -->
    <div class="modal fade" id="connect_payment_cashfree">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('Cashfree') }}</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('close') }}">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form enctype="multipart/form-data" id="cashfree_credentials_form">
                    <input type="hidden" name="group_id" id="group_id" class="form-control" value="13">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Cashfree API Key') }}</label>
                                    <input type="text" required id="cashfree_api_key" name="cashfree_api_key"
                                        class="form-control" placeholder="{{ __('Enter Cashfree API Key') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Cashfree API Secret') }}</label>
                                    <input type="text" required id="cashfree_api_secret"
                                        name="cashfree_api_secret" class="form-control"
                                        placeholder="{{ __('Enter Cashfree API Secret') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Mode') }}</label>
                                    <select name="cashfree_mode" id="cashfree_mode" class="form-control" required>
                                        <option value="test">{{ __('Test (Sandbox)') }}</option>
                                        <option value="live">{{ __('Live (Production)') }}</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        @if (isset($permission))
                            @if (hasPermission($permission, 'General Settings', 'edit'))
                                <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                            @endif
                        @endif
                    </div>
                </form>


            </div>
        </div>
    </div>

    <!-- Authorize.Net -->
    <div class="modal fade" id="connect_payment_authorizenet">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">{{ __('Authorize.Net') }}</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('Close') }}">
                        <i class="ti ti-x"></i>
                    </button>
                </div>

                <form enctype="multipart/form-data" id="authorizenet_credentials_form">
                    <input type="hidden" name="group_id" id="group_id" class="form-control" value="13">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="mb-3">
                                    <label class="form-label">{{ __('API Login ID') }}</label>
                                    <input type="text" required id="authorizenet_api_login_id"
                                        name="authorizenet_api_login_id" class="form-control"
                                        placeholder="{{ __('Enter API Login ID') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Transaction Key') }}</label>
                                    <input type="text" required id="authorizenet_transaction_key"
                                        name="authorizenet_transaction_key" class="form-control"
                                        placeholder="{{ __('Enter Transaction Key') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Environment') }}</label>
                                    <select name="authorizenet_env" id="authorizenet_env" class="form-control"
                                        required>
                                        <option value="test">{{ __('Test (Sandbox)') }}</option>
                                        <option value="live">{{ __('Live (Production)') }}</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        @if (isset($permission))
                            @if (hasPermission($permission, 'General Settings', 'edit'))
                                <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                            @endif
                        @endif
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Paystack -->
    <div class="modal fade" id="connect_payment_paystack">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">{{ __('Paystack') }}</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('Close') }}">
                        <i class="ti ti-x"></i>
                    </button>
                </div>

                <form enctype="multipart/form-data" id="paystack_credentials_form">
                    <input type="hidden" name="group_id" id="group_id" class="form-control" value="13">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <label class="form-label d-none real-label">{{ __('Public Key') }}</label>
                                    <div class="skeleton input-skeleton input-loader"></div>
                                    <input type="text" name="paystack_public_key" id="paystack_public_key"
                                        class="form-control d-none real-input"
                                        placeholder="{{ __('Enter Paystack Public Key') }}">
                                    <div class="invalid-feedback" id="paystack_public_key_error"></div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <label class="form-label d-none real-label">{{ __('Secret Key') }}</label>
                                    <div class="skeleton input-skeleton input-loader"></div>
                                    <input type="text" name="paystack_secret_key" id="paystack_secret_key"
                                        class="form-control d-none real-input"
                                        placeholder="{{ __('Enter Paystack Secret Key') }}">
                                    <div class="invalid-feedback" id="paystack_secret_key_error"></div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <label class="form-label d-none real-label">{{ __('Payment URL') }}</label>
                                    <div class="skeleton input-skeleton input-loader"></div>
                                    <input type="text" name="paystack_payment_url" id="paystack_payment_url"
                                        class="form-control d-none real-input"
                                        placeholder="{{ __('e.g., https://api.paystack.co') }}">
                                    <div class="invalid-feedback" id="paystack_payment_url_error"></div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <label class="form-label d-none real-label">{{ __('Callback URL') }}</label>
                                    <div class="skeleton input-skeleton input-loader"></div>
                                    <input type="text" name="paystack_callback_url" id="paystack_callback_url"
                                        class="form-control d-none real-input"
                                        placeholder="{{ __('e.g., http://yourdomain.com/payment-success') }}">
                                    <div class="invalid-feedback" id="paystack_callback_url_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        @if (isset($permission))
                            @if (hasPermission($permission, 'General Settings', 'edit'))
                                <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                            @endif
                        @endif
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Mercadopago -->
    <div class="modal fade" id="connect_payment_mercadopago">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">{{ __('Mercadopago') }}</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('Close') }}">
                        <i class="ti ti-x"></i>
                    </button>
                </div>

                <form enctype="multipart/form-data" id="mercadopago_credentials_form">
                    <input type="hidden" name="group_id" id="group_id" class="form-control" value="13">

                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <label class="form-label d-none real-label">{{ __('Public Key') }}</label>
                                    <div class="skeleton input-skeleton input-loader"></div>
                                    <input type="text" name="mercadopago_public_key" id="mercadopago_public_key"
                                        class="form-control d-none real-input"
                                        placeholder="{{ __('Enter Mercado Pago Public Key') }}">
                                    <div class="invalid-feedback" id="mercadopago_public_key_error"></div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <label class="form-label d-none real-label">{{ __('Access Token') }}</label>
                                    <div class="skeleton input-skeleton input-loader"></div>
                                    <input type="text" name="mercadopago_access_token"
                                        id="mercadopago_access_token" class="form-control d-none real-input"
                                        placeholder="{{ __('Enter Mercado Pago Access Token') }}">
                                    <div class="invalid-feedback" id="mercadopago_access_token_error"></div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <label class="form-label d-none real-label">{{ __('Callback URL') }}</label>
                                    <div class="skeleton input-skeleton input-loader"></div>
                                    <input type="text" name="mercadopago_callback_url"
                                        id="mercadopago_callback_url" class="form-control d-none real-input"
                                        placeholder="{{ __('e.g., http://yourdomain.com/payment-success') }}">
                                    <div class="invalid-feedback" id="mercadopago_callback_url_error"></div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        @if (isset($permission))
                            @if (hasPermission($permission, 'General Settings', 'edit'))
                                <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                            @endif
                        @endif
                    </div>
                </form>

            </div>
        </div>
    </div>
