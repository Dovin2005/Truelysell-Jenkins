<div class="modal fade" id="login-modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-end pb-0 border-0">
                <a href="#!" data-bs-dismiss="modal" aria-label="Close"><i
                        class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <div class="modal-body p-4">
                <form id="userlogins" autocomplete="off">
                    {{ csrf_field() }}

                    <div class="text-center mb-3">
                        <h3 class="mb-2">{{ __('Welcome') }} </h3>
                        <p>{{ __('Enter your credentials to access your account') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('email') }}</label>
                        <input type="text" name="email" class="form-control" placeholder="{{ __('Enter Email') }}"
                            autocomplete="username">
                    </div>
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <label class="form-label">{{ __('Password') }}</label>
                            <a href="#!"
                                class="text-primary fw-medium text-decoration-underline mb-1 fs-14"
                                data-bs-toggle="modal" data-bs-target="#forgot-modal">{{ __('Forgot Password') }}?</a>
                        </div>
                        <div class="input-group">
                            <input type="password" name="password" id="login_password" class="form-control password-field"
                                maxlength="100" placeholder="{{ __('Enter Password') }}"
                                autocomplete="current-password">
                            <button class="btn btn-outline-dark toggle-eye-btn" type="button" id="loginTogglePassword" tabindex="-1">
                                <i class="fas fa-eye-slash toggleIcon" id="logintoggleIcon"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    @if ($otpStatus == 1)
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-end flex-wrap row-gap-2">
                            <div class="form-check">
                                <a class="form-check-label text-decoration-underline cursor-pointer" id="otp_signin">
                                    {{ __('Sign in with OTP') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($reSetting)
                    <div class="mb-3 text-center">
                        <div class="g-recaptcha d-inline-block" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                    </div>
                    @endif
                    <div id="error_login_message" class="text-danger text-center m-1"></div>
                    <div class="mb-3">
                        <button type="submit" class="login_btn btn btn-lg btn-linear-primary w-100">{{ __('Signin') }}
                        </button>
                    </div>
                    @if ($sso_status == 1)
                    <div class="login-or mb-3">
                        <span class="span-or">{{ __('Or sign in with') }} </span>
                    </div>
                    <div class="text-center mb-3">
                        <a href="{{ route('auth.redirect', 'google') }}"
                            class="btn btn-light d-flex align-items-center justify-content-center">
                            <img src="/assets/img/icons/google-icon.svg" class="me-2" alt="Google Icon">Google
                        </a>
                    </div>
                    @endif
                    <div class="d-flex justify-content-center">
                        <p>{{ __('Dont have a account') }} <a href="#!" class="text-primary"
                                data-bs-toggle="modal" data-bs-target="#register-modal"> {{ __('Join us Today') }}</a>
                        </p>
                    </div>
                </form>

                <div class="pe-3 mt-3">
                    <div class="mb-3">
                        <h6 class="fs-16 mb-1">{{ __('User Login Info') }}:</h6>
                        <div class="p-3 border d-flex flex-wrap align-items-center justify-content-between">
                            <div>
                                <span class="d-block"><strong>{{ __('user_name') }} </strong>: demouser@gmail.com</span>
                                <span class="d-block"><strong>{{ __('Password') }} </strong>: 12345678</span>
                            </div>
                            <div>
                                <a class="btn btn-primary copy-login-details" data-email="demouser@gmail.com"
                                    data-password="12345678">
                                    <i class="far fa-copy"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h6 class="fs-16 mb-1">{{ __('Provider Login Info') }}:</h6>
                        <div class="p-3 border d-flex flex-wrap align-items-center justify-content-between">
                            <div>
                                <span class="d-block"><strong>{{ __('user_name') }} </strong>: demoprovider@gmail.com</span>
                                <span class="d-block"><strong>{{ __('Password') }} </strong>: 12345678</span>
                            </div>
                            <div>
                                <a href="#" class="btn btn-primary copy-login-details"
                                    data-email="demoprovider@gmail.com" data-password="12345678">
                                    <i class="far fa-copy"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="otp-email-modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
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
                        <p class="fs-14" id="otp-email-message">{{ __('OTP sent to your Email Address') }}</p>
                    </div>
                    <div class="text-center otp-input">
                        <div class="inputcontainer">

                        </div>
                        <span id="error_message" class="text-danger"></span>
                        <div>
                            <div class="badge bg-danger-transparent mb-3">
                                <p class="d-flex align-items-center "><i class="ti ti-clock me-1"></i><span
                                        id="otp-timer">00:00</span></p>
                            </div>
                            <div class="mb-3 d-flex justify-content-center">
                                <p> {{ __('Didn t get the OTP?') }} <a href="#!"
                                        class="resendEmailOtp text-primary">{{ __('Resend OTP') }}</a></p>
                            </div>
                            <div>
                                <button type="button" id="verify-email-otp-btn"
                                    class="verify-email-otp-btn btn btn-lg btn-linear-primary w-100">{{ __('Verify & Proceed') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="otp-phone-modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
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
                        <p id="otp-sms-message" class="fs-14">{{ __('OTP sent to your mobile number') }}</p>
                    </div>
                    <div class="text-center otp-input">
                        <div class="inputSMSContainer">
                        </div>
                        <span id="error_sms_message" class="text-danger"></span>
                        <div>
                            <div class="badge bg-danger-transparent mb-3">
                                <p class="d-flex align-items-center "><i class="ti ti-clock me-1"></i><span
                                        id="otp-sms-timer">00:00</span></p>
                            </div>
                            <div class="mb-3 d-flex justify-content-center">
                                <p>{{ __('Didn t get the OTP?') }} <a href="#!"
                                        class="resendSMSOtp text-primary">{{ __('Resend OTP') }}</a></p>
                            </div>
                            <div>
                                <button type="button" id="verify-sms-otp-btn"
                                    class="btn btn-lg btn-linear-primary w-100">{{ __('Verify & Proceed') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="forgot-modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-end pb-0 border-0">
                <a href="#!" data-bs-dismiss="modal" aria-label="Close"><i
                        class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <div class="modal-body p-4">
                <form id="forgot_login">
                    <div class="text-center mb-3">
                        <h3 class="mb-2">{{ __('Forgot Password') }}</h3>
                        <p>{{ __('Enter your email, we will send you a otp to reset your password.') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('email') }}</label>
                        <input type="email" name="forgot_email" id="forgot_email" class="form-control"
                            placeholder="{{ __('Enter Email') }}">
                        <div class="invalid-feedback" id="forgot_email_error"></div>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-lg btn-linear-primary w-100"
                            id="otp_signin_forgot">{{ __('submit') }}</button>
                    </div>
                    <div class=" d-flex justify-content-center">
                        <p>{{ __('Remember Password?') }} <a href="#!" class="text-primary"
                                data-bs-toggle="modal" data-bs-target="#login-modal">{{ __('Signin') }}</a></p>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>


<div class="modal fade" id="reset-password" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-end pb-0 border-0">
                <a href="#!" data-bs-dismiss="modal" aria-label="Close"><i
                        class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <h3 class="mb-2">{{ __('Reset Password') }}</h3>
                    <p class="fs-14">{{ __('reset_password_description') }}</p>
                </div>
                <form id="forgotPassword" autocomplete="off" novalidate="novalidate">
                    {{ csrf_field() }}
                    <input type="hidden" name="email_id" id="email_id" value="" autocomplete="username">
                    <div class="input-block mb-3">
                        <div class="mb-3">
                            <label class="form-label">{{ __('New Password') }}</label>
                            <div class="pass-group" id="passwordInput">
                                <input type="password" name="new_password" id="new_password"
                                    class="form-control pass-input" placeholder="Enter New Password"
                                    autocomplete="new-password" required>
                                <div class="invalid-feedback" id="new_password_error"></div>
                            </div>
                        </div>
                        <div class="password-strength d-flex" id="passwordStrength">
                            <span id="poor"></span>
                            <span id="weak"></span>
                            <span id="strong"></span>
                            <span id="heavy"></span>
                        </div>
                        <div id="passwordInfo" class="mb-2"></div>
                        <p class="fs-12">{{ __('Use 8 or more characters with a mix of letters, numbers & symbols.') }}</p>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <label class="form-label">{{ __('Confirm Password') }}</label>
                        </div>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                            placeholder="Confirm Password" autocomplete="new-password" required>
                        <div class="invalid-feedback" id="confirm_password_error"></div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-lg btn-linear-primary w-100 forgot_btn">{{ __('Save Changes') }}</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="success_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="mb-4">
                    <span class="success-icon mx-auto mb-4">
                        <i class="ti ti-check"></i>
                    </span>
                    <h4 class="mb-1">{{ __('Login Successful') }}</h4>
                    <p>{{ __("login_success_info") }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="provider_not_verified_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="mb-4">
                    <span class="error-icon mx-auto mb-4">
                        <i class="ti ti-alert-circle"></i>
                    </span>
                    <h4 class="mb-1">{{ __('account_not_verified') }}</h4>
                    <p>{{ __('provider_not_verified_info') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="otp_error" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="mb-4">
                    <span class="error-icon mx-auto mb-4">
                        <i class="ti ti-alert"></i>
                    </span>
                    <h4 class="mb-1">{{ __('OTP Verification Failed') }}</h4>
                    <p>{{ __('otp_verification_failed_description') }}</p>
                </div>
                <a href="#!" data-bs-dismiss="modal" class="btn btn-linear-primary">{{ __('Close') }}</a>
            </div>
        </div>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>