<!-- Register Modal -->
<div class="modal fade" id="register-modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-end pb-0 border-0">
                <a href="#!" data-bs-dismiss="modal" aria-label="Close"><i
                        class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <div class="modal-body p-4">
                <form id="userregister" autocomplete="off">
                    {{ csrf_field() }}

                    <div class="text-center mb-3">
                        <h3 class="mb-2">{{ __('Registration') }}</h3>
                        <p>{{ __('Enter your credentials to access your account') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('first_name') }}</label>
                        <input type="text" name="first_name" id="first_name" class="form-control" maxlength="100"
                            placeholder="{{ __('Enter First Name') }}">
                        <div class="invalid-feedback" id="first_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('last_name') }}</label>
                        <input type="text" name="last_name" id="last_name" class="form-control" maxlength="100"
                            placeholder="{{ __('Enter Last Name') }}">
                        <div class="invalid-feedback" id="last_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('user_name') }}</label>
                        <input type="text" name="name" id="name" class="form-control" maxlength="100"
                            placeholder="{{ __('Enter Name') }}">
                        <div class="invalid-feedback" id="name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('email') }}</label>
                        <input type="email" name="email" id="email" class="form-control" maxlength="100"
                            placeholder="{{ __('Enter Email') }}">
                        <div class="invalid-feedback" id="email_error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('phone_number') }}</label>
                        <input class="form-control" id="phone" name="phone_number" maxlength="12" type="tel"
                            placeholder="{{ __('Enter Phone Number') }}" autocomplete="tel">
                        <div class="invalid-feedback" id="phone_number_error"></div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <label class="form-label">{{ __('Password') }}</label>
                        </div>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" maxlength="100"
                                placeholder="{{ __('Enter Password') }}" autocomplete="current-password">
                            <button class="btn btn-outline-dark" type="button" id="togglePassword" tabindex="-1">
                                <i class="fas fa-eye-slash toggleIcon" id="usertoggleIcon"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                            <div class="form-check">
                                <input class="form-check-input border-primary border-1" name="terms_policy" type="checkbox" value=""
                                    id="terms_policy">
                                <label class="form-check-label" for="terms_policy">
                                    {{ __('I agree to') }} <a href="{{ route('terms.conditions') }}"
                                        class="text-primary text-decoration-underline">{{ __('Terms and Conditions') }}</a>
                                    & <a href="{{ route('privacy.policy') }}"
                                        class="text-primary text-decoration-underline">{{ __('Privacy Policy') }}</a>
                                </label>
                                <div class="invalid-feedback" id="terms_policy_error"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <button type="submit" id="register_btn"
                            class="register_btn btn btn-lg btn-linear-primary w-100">{{ __('Sign up') }}</button>
                    </div>
                    <div class=" d-flex justify-content-center">
                        <p>{{ __('Already have a account?') }} <a href="#!" class="text-primary"
                                data-bs-target="#login-modal" data-bs-toggle="modal">{{ __('Signin') }}</a></p>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="otp-email-reg-modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
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
                        <p id="otp-reg-email-message" class="fs-14">{{ __('OTP sent to your Email Address') }}</p>
                    </div>
                    <div class="text-center otp-input">
                        <div class="inputcontainerreg">

                        </div>
                        <span id="error_email_reg_message" class="text-danger"></span>
                        <div>
                            <div class="badge bg-danger-transparent mb-3">
                                <p class="d-flex align-items-center "><i class="ti ti-clock me-1"></i><span
                                        id="otp-reg-timer">00:00</span></p>
                            </div>
                            <div class="mb-3 d-flex justify-content-center">
                                <p>{{ __('Didn t get the OTP?') }} <a href="#!"
                                        class="resendRegEmailOtp text-primary">{{ __('Resend OTP') }}</a></p>
                            </div>
                            <div>
                                <button type="button" id="verify-email-red-otp-btn"
                                    class="verify-email-reg-otp-btn btn btn-lg btn-linear-primary w-100">{{ __('Verify & Proceed') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>


<div class="modal fade" id="otp-reg-phone-modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
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
                        <p id="otp-reg-sms-message" class="fs-14">{{ __('OTP sent to your mobile number') }}</p>
                    </div>
                    <div class="text-center otp-input">
                        <div class="inputRegSMSContainer">

                        </div>
                        <span id="error_reg_sms_message" class="text-danger"></span>
                        <div>
                            <div class="badge bg-danger-transparent mb-3">
                                <p class="d-flex align-items-center "><i class="ti ti-clock me-1"></i><span
                                        id="otp-reg-sms-timer">00:00</span></p>
                            </div>
                            <div class="mb-3 d-flex justify-content-center">
                                <p>{{ __('Didn t get the OTP?') }} <a href="#!"
                                        class="resendRegSMSOtp text-primary">{{ __('Resend OTP') }}</a></p>
                            </div>
                            <div>
                                <button type="button" id="verify-reg-sms-otp-btn"
                                    class="btn btn-lg btn-linear-primary w-100">{{ __('Verify & Proceed') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
