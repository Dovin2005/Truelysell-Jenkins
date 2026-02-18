<div class="modal fade" id="paymentModalNet" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">{{ __('Complete Your Secure Payment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>

            <div class="modal-body">
                <div class="row">

                    <div class="col-md-6">
                        <div class="alert alert-danger p-2 small rounded-0 mt-2" role="alert">
                            <strong>{{ __('Note:') }}</strong> {{ __('Authorize.Net only supports merchants in the United States, Canada, United Kingdom, and Australia. Cards issued outside these countries may not be accepted.') }}
                        </div>

                        <img src="https://www.justinmind.com/wp-content/uploads/2019/09/card-ui-design-principles-examples.png"
                            class="img-fluid mt-2" alt="{{ __('Accepted Credit Cards') }}">
                    </div>

                    <div class="col-md-6">
                        <h5 class="text-center mb-4">{{ __('ENTER PAYMENT DETAILS') }}</h5>

                        <div class="mb-3">
                            <label for="cardHolderName" class="form-label">{{ __('Name on Card') }} <span> *</span></label>
                            <input type="text" class="form-control" id="cardHolderName" placeholder="{{ __('S Cooper') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="cardNumber" class="form-label">{{ __('Card Number') }} <span> *</span></label>
                            <input type="text" class="form-control" id="cardNumber" placeholder="{{ __('3561 - 6857 - 3292 - 4801') }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="cardCvv" class="form-label">{{ __('CVV Number') }} <span> *</span></label>
                                <input type="password" class="form-control" id="cardCvv" placeholder="{{ __('702') }}" maxlength="4" required>
                            </div>

                            <div class="col-md-7 mb-3">
                                <label for="cardExpiryMonth" class="form-label">{{ __('Expire') }} <span> *</span></label>
                                <div class="input-group">
                                    <select class="form-select" id="cardExpiryMonth" required>
                                        <option selected disabled value="">{{ __('Month') }}</option>
                                        <option value="01">1</option>
                                        <option value="02">2</option>
                                        <option value="03">3</option>
                                        <option value="04">4</option>
                                        <option value="05">5</option>
                                        <option value="06">6</option>
                                        <option value="07">7</option>
                                        <option value="08">8</option>
                                        <option value="09">9</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                    </select>
                                    <select class="form-select" id="cardExpiryYear" required>
                                        <option selected disabled value="">{{ __('Year') }}</option>
                                        <option>2025</option>
                                        <option>2026</option>
                                        <option>2027</option>
                                        <option>2028</option>
                                        <option>2029</option>
                                        <option>2030</option>
                                        <option>2031</option>
                                        <option>2032</option>
                                        <option>2033</option>
                                        <option>2034</option>
                                        <option>2035</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">&leftarrow; {{ __('Previous Step') }}</button>
                <button type="submit" class="btn btn-primary" form="payment-form" id="submit-authnet-payment">{{ __('Pay Now') }}</button>
            </div>

        </div>
    </div>
</div>
