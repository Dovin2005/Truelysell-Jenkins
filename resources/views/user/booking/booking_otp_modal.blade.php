<div class="modal fade" id="booking-otp-email-modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-end pb-0 border-0">
                <a href="#!" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-circle-x-filled fs-20"></i>
                </a>
            </div>
            <div class="modal-body p-4">
                <form action="#" class="digit-group">
                    <div class="text-center mb-3">
                        <h3 class="mb-2">{{ __('Email OTP Verification') }}</h3>
                        <p class="fs-14 otp-email-message">{{ __('OTP sent to your Email Address') }}</p>
                    </div>
                    <div class="text-center otp-input">
                        <div class="inputcontainer">

                        </div>
                        <span id="booking_error_message" class="text-danger"></span>
                        <div>
                            <div class="badge bg-danger-transparent mb-3">
                                <p class="d-flex align-items-center "><i class="ti ti-clock me-1"></i><span
                                        id="booking-otp-timer">00:00</span></p>
                            </div>
                            <div class="mb-3 d-flex justify-content-center">
                                <p> {{ __('Didn t get the OTP?') }} <a href="#!"
                                        class="bookingResendEmailOtp text-primary">{{ __('Resend OTP') }}</a></p>
                            </div>
                            <div>
                                <button type="button" id="booking-verify-email-otp-btn"
                                    class="booking-verify-email-otp-btn btn btn-lg btn-linear-primary w-100">{{ __('Verify & Proceed') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="booking-otp-phone-modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-end pb-0 border-0">
                <a href="#!" data-bs-dismiss="modal" aria-label="Close"><i
                        class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <div class="modal-body p-4">
                <form action="#" class="digit-group">
                    <div class="text-center mb-3">
                        <h3 class="mb-2">{{ __('Phone OTP Verification') }}</h3>
                        <p id="booking-otp-sms-message" class="fs-14">{{ __('OTP sent to your mobile number') }}</p>
                    </div>
                    <div class="text-center otp-input">
                        <div class="inputSMSContainer">
                        </div>
                        <span id="error_sms_message" class="text-danger"></span>
                        <div>
                            <div class="badge bg-danger-transparent mb-3">
                                <p class="d-flex align-items-center "><i class="ti ti-clock me-1"></i><span
                                        id="booking-otp-sms-timer">00:00</span></p>
                            </div>
                            <div class="mb-3 d-flex justify-content-center">
                                <p>{{ __('Didn t get the OTP?') }} <a href="#!"
                                        class="bookingResendSMSOtp text-primary">{{ __('Resend OTP') }}</a></p>
                            </div>
                            <div>
                                <button type="button" id="booking-verify-sms-otp-btn"
                                    class="btn btn-lg btn-linear-primary w-100">{{ __('Verify & Proceed') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
