        <!-- Razerpay -->
        <div class="col-xxl-4 col-xl-6 d-flex">
            <div class="card flex-fill">
                <div class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                    <div class="skeleton label-skeleton label-loader"></div>
                    <span
                        class="d-inline-flex align-items-center justify-content-center payment_icon border rounded p-2 d-none real-label"><img
                            src="{{ asset('front/img/icons/razerpay.png') }}" alt="Img"></span>
                    <div class="d-flex align-items-center">

                        <div class="status-toggle modal-status d-none real-label">
                            @if (isset($permission))
                                @if (hasPermission($permission, 'General Settings', 'edit'))
                                    <input type="checkbox" id="razorpay_status" name="razorpay_status" class="check">
                                    <label for="razorpay_status" class="checktoggle"> </label>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="skeleton input-skeleton input-loader"></div>
                    <p class="d-none real-input">{{ __('razorpay_description') }}</p>
                </div>
                <div class="card-footer">
                    <div class="skeleton input-skeleton input-loader"></div>
                    <a href="#"
                        class="btn btn-outline-light d-flex justify-content-center align-items-center fw-semibold d-none real-input"
                        data-bs-toggle="modal" data-bs-target="#connect_payment_razorpay"><i
                            class="ti ti-tool me-2"></i>{{ __('configuration') }}</a>
                </div>
            </div>
        </div>

        <!-- PayU -->
        <div class="col-xxl-4 col-xl-6 d-flex">
            <div class="card flex-fill">
                <div class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                    <div class="skeleton label-skeleton label-loader"></div>
                    <span
                        class="d-inline-flex align-items-center justify-content-center payment_icon border rounded p-2 d-none real-label"><img
                            src="{{ asset('front/img/icons/payu.png') }}" alt="Img"></span>
                    <div class="d-flex align-items-center">

                        <div class="status-toggle modal-status d-none real-label">
                            @if (isset($permission))
                                @if (hasPermission($permission, 'General Settings', 'edit'))
                                    <input type="checkbox" id="payu_status_toggle" name="payu_status" class="check">
                                    <label for="payu_status_toggle" class="checktoggle">
                                    </label>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="skeleton input-skeleton input-loader"></div>
                    <p class="d-none real-input">{{ __('payu_description') }}</p>
                </div>
                <div class="card-footer">
                    <div class="skeleton input-skeleton input-loader"></div>
                    <a href="#"
                        class="btn btn-outline-light d-flex justify-content-center align-items-center fw-semibold d-none real-input"
                        data-bs-toggle="modal" data-bs-target="#connect_payment_payu"><i
                            class="ti ti-tool me-2"></i>{{ __('configuration') }}</a>
                </div>
            </div>
        </div>

        <!-- Cashfree -->
        <div class="col-xxl-4 col-xl-6 d-flex">
            <div class="card flex-fill">
                <div class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                    <div class="skeleton label-skeleton label-loader"></div>
                    <span
                        class="d-inline-flex align-items-center justify-content-center payment_icon border rounded p-2 d-none real-label"><img
                            src="{{ asset('front/img/icons/cashfree.png') }}" alt="Img"></span>
                    <div class="d-flex align-items-center">

                        <div class="status-toggle modal-status d-none real-label">
                            @if (isset($permission))
                                @if (hasPermission($permission, 'General Settings', 'edit'))
                                    <input type="checkbox" id="cashfree_status_toggle" name="cashfree_status"
                                        class="check">
                                    <label for="cashfree_status_toggle" class="checktoggle">
                                    </label>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="skeleton input-skeleton input-loader"></div>
                    <p class="d-none real-input">{{ __('cashfree_description') }}</p>
                </div>
                <div class="card-footer">
                    <div class="skeleton input-skeleton input-loader"></div>
                    <a href="#"
                        class="btn btn-outline-light d-flex justify-content-center align-items-center fw-semibold d-none real-input"
                        data-bs-toggle="modal" data-bs-target="#connect_payment_cashfree"><i
                            class="ti ti-tool me-2"></i>{{ __('configuration') }}</a>
                </div>
            </div>
        </div>

        <!-- Authorize.Net -->
        <div class="col-xxl-4 col-xl-6 d-flex">
            <div class="card flex-fill">
                <div class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                    <div class="skeleton label-skeleton label-loader"></div>
                    <span
                        class="d-inline-flex align-items-center justify-content-center payment_icon border rounded p-2 d-none real-label"><img
                            src="{{ asset('front/img/icons/auth.png') }}" alt="Img"></span>
                    <div class="d-flex align-items-center">

                        <div class="status-toggle modal-status d-none real-label">
                            @if (isset($permission))
                                @if (hasPermission($permission, 'General Settings', 'edit'))
                                    <input type="checkbox" id="authorizenet_status_toggle" name="authorizenet_status"
                                        class="check">
                                    <label for="authorizenet_status_toggle" class="checktoggle">
                                    </label>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="skeleton input-skeleton input-loader"></div>
                    <p class="d-none real-input">{{ __('authorizenet_description') }}</p>
                </div>
                <div class="card-footer">
                    <div class="skeleton input-skeleton input-loader"></div>
                    <a href="#"
                        class="btn btn-outline-light d-flex justify-content-center align-items-center fw-semibold d-none real-input"
                        data-bs-toggle="modal" data-bs-target="#connect_payment_authorizenet"><i
                            class="ti ti-tool me-2"></i>{{ __('configuration') }}</a>
                </div>
            </div>
        </div>

        <!-- Paystack.Net -->
        <div class="col-xxl-4 col-xl-6 d-flex">
            <div class="card flex-fill">
                <div class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                    <div class="skeleton label-skeleton label-loader"></div>
                    <span
                        class="d-inline-flex align-items-center justify-content-center payment_icon border rounded p-2 d-none real-label"><img
                            src="{{ asset('front/img/icons/paystack.png') }}" alt="Img"></span>
                    <div class="d-flex align-items-center">

                        <div class="status-toggle modal-status d-none real-label">
                            @if (isset($permission))
                                @if (hasPermission($permission, 'General Settings', 'edit'))
                                    <input type="checkbox" id="paystack_status_toggle" name="paystack_status"
                                        class="check">
                                    <label for="paystack_status_toggle" class="checktoggle">
                                    </label>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="skeleton input-skeleton input-loader"></div>
                    <p class="d-none real-input">{{ __('paystack_description') }}</p>
                </div>
                <div class="card-footer">
                    <div class="skeleton input-skeleton input-loader"></div>
                    <a href="#"
                        class="btn btn-outline-light d-flex justify-content-center align-items-center fw-semibold d-none real-input"
                        data-bs-toggle="modal" data-bs-target="#connect_payment_paystack"><i
                            class="ti ti-tool me-2"></i>{{ __('configuration') }}</a>
                </div>
            </div>
        </div>

        <!-- Mercadopago -->
        <div class="col-xxl-4 col-xl-6 d-flex">
            <div class="card flex-fill">
                <div class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                    <div class="skeleton label-skeleton label-loader"></div>
                    <span
                        class="d-inline-flex align-items-center justify-content-center payment_icon border rounded p-2 d-none real-label"><img
                            src="{{ asset('front/img/icons/mercadopago.png') }}" alt="Img"></span>
                    <div class="d-flex align-items-center">

                        <div class="status-toggle modal-status d-none real-label">
                            @if (isset($permission))
                                @if (hasPermission($permission, 'General Settings', 'edit'))
                                    <input type="checkbox" id="mercadopago_status_toggle" name="mercadopago_status"
                                        class="check">
                                    <label for="mercadopago_status_toggle" class="checktoggle">
                                    </label>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="skeleton input-skeleton input-loader"></div>
                    <p class="d-none real-input">{{ __('mercadopago_description') }}</p>
                </div>
                <div class="card-footer">
                    <div class="skeleton input-skeleton input-loader"></div>
                    <a href="#"
                        class="btn btn-outline-light d-flex justify-content-center align-items-center fw-semibold d-none real-input"
                        data-bs-toggle="modal" data-bs-target="#connect_payment_mercadopago"><i
                            class="ti ti-tool me-2"></i>{{ __('configuration') }}</a>
                </div>
            </div>
        </div>
