@if ($PaymentGatewayStatus == 1)
    @if ($razerpayStatus == 1)
        <!-- Razorpay Index -->
        <div class="payment-item d-flex align-items-center justify-content-between mb-2" id="razorpayPayment">
            <div class="form-check d-flex align-items-center ps-0">
                <input class="form-check-input ms-0 mt-0 payment-radio" name="payment_type" type="radio"
                    id="razorpay_status" value="razorpay">
                <label class="form-check-label ms-2" for="razorpay_status">{{ __('Razorpay') }}</label>
            </div>
            <div>
                <img src="{{ asset('front/img/icons/razerpay.png') }}" style="height: 40px; width: 50px" alt="payment">
            </div>
        </div>
    @endif

    @if ($payuStatus == 1)
        <!-- PayU Index -->
        <div class="payment-item d-flex align-items-center justify-content-between mb-2" id="payuPayment">
            <div class="form-check d-flex align-items-center ps-0">
                <input class="form-check-input ms-0 mt-0 payment-radio" name="payment_type" type="radio"
                    id="payu_status" value="payu">
                <label class="form-check-label ms-2" for="payu_status">{{ __('PayU') }}</label>
            </div>
            <div>
                <img src="{{ asset('front/img/icons/payu.png') }}" style="height: 40px; width: 50px" alt="payment">
            </div>
        </div>
    @endif

    @if ($cashfreeStatus == 1)
        <!-- Cashfree Index -->
        <div class="payment-item d-flex align-items-center justify-content-between mb-2" id="cashfree">
            <div class="form-check d-flex align-items-center ps-0">
                <input class="form-check-input ms-0 mt-0 payment-radio" name="payment_type" type="radio"
                    id="cashfree_status" value="cashfree">
                <label class="form-check-label ms-2" for="cashfree_status">{{ __('Cashfree') }}</label>
            </div>
            <div>
                <img src="{{ asset('front/img/icons/cashfree.png') }}" style="height: 40px; width: 50px" alt="payment">
            </div>
        </div>
    @endif

    @if ($authorizenetStatus == 1)
        <!-- Authorizenet Index -->
        <div class="payment-item d-flex align-items-center justify-content-between mb-2" id="authorizenet">
            <div class="form-check d-flex align-items-center ps-0">
                <input class="form-check-input ms-0 mt-0 payment-radio" name="payment_type" type="radio"
                    id="authorizenet_status" value="authorizenet">
                <label class="form-check-label ms-2" for="authorizenet_status">{{ __('Authorizenet') }}</label>
            </div>
            <div>
                <img src="{{ asset('front/img/icons/auth.png') }}" style="height: 40px; width: 50px" alt="payment">
            </div>
        </div>
    @endif

    @if ($paystackStatus == 1)
        <!-- Paystack Index -->
        <div class="payment-item d-flex align-items-center justify-content-between mb-2" id="paystack">
            <div class="form-check d-flex align-items-center ps-0">
                <input class="form-check-input ms-0 mt-0 payment-radio" name="payment_type" type="radio"
                    id="paystack_status" value="paystack">
                <label class="form-check-label ms-2" for="paystack_status">{{ __('Paystack') }}</label>
            </div>
            <div>
                <img src="{{ asset('front/img/icons/paystack.png') }}" style="height: 40px; width: 50px" alt="paystack">
            </div>
        </div>
    @endif

    @if ($mercadoStatus == 1)
        <!-- Mercado Pago Index -->
        <div class="payment-item d-flex align-items-center justify-content-between mb-2" id="mercadopago">
            <div class="form-check d-flex align-items-center ps-0">
                <input class="form-check-input ms-0 mt-0 payment-radio" name="payment_type" type="radio"
                    id="mercadopago_status" value="mercadopago">
                <label class="form-check-label ms-2" for="mercadopago_status">{{ __('Mercado Pago') }}</label>
            </div>
            <div>
                <img src="{{ asset('front/img/icons/mercadopago.png') }}" style="height: 40px; width: 50px"
                    alt="mercadopago">
            </div>
        </div>
    @endif
@endif
