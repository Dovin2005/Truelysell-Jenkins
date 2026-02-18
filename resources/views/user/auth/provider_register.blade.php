<!-- Provider Modal -->
<div class="modal fade" id="provider" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between">
                <h5>{{ __('become_provider') }}</h5>
                <a href="#!" data-bs-dismiss="modal" aria-label="Close"><i
                        class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <div class="wizard-fieldset">
                <fieldset class="first-field" id="first-field">
                    <form id="providerRegister" autocomplete="off">
                        <!-- {{ csrf_field() }} -->

                        <div class="modal-body pb-1">

                            <div class="mb-3">
                                <div class="text-center mb-3">
                                    <h3 class="mb-2">{{ __('Registration') }}</h3>
                                    <p>{{ __('Enter your credentials to access your account') }}</p>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('first_name') }}</label>
                                            <input type="text" name="provider_first_name" id="provider_first_name"
                                                class="form-control" placeholder="{{ __('Enter First Name') }}">
                                            <div class="invalid-feedback" id="provider_first_name_error"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('last_name') }}</label>
                                            <input type="text" name="provider_last_name" id="provider_last_name"
                                                class="form-control" placeholder="{{ __('Enter Last Name') }}">
                                            <div class="invalid-feedback" id="provider_last_name_error"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('user_name') }}</label>
                                            <input type="text" name="provider_name" id="provider_name"
                                                class="form-control" placeholder="{{ __('Enter Name') }}">
                                            <div class="invalid-feedback" id="provider_name_error"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('email') }}</label>
                                            <input type="email" name="provider_email" id="provider_email"
                                                class="form-control" placeholder="{{ __('Enter Email') }}">
                                            <div class="invalid-feedback" id="provider_email_error"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('phone_number') }}</label>
                                            <input class="form-control" id="provider_phone_number"
                                                name="provider_phone_number" maxlength="12" type="text"
                                                placeholder="{{ __('Enter Phone Number') }}" autocomplete="tel">
                                            <div class="invalid-feedback" id="provider_phone_number_error"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                                <label class="form-label">{{ __('Password') }}</label>
                                            </div>
                                            <div class="input-group">
                                                <input type="password" name="provider_password" id="provider_password"
                                                    class="form-control" placeholder="{{ __('Enter Password') }}"
                                                    autocomplete="current-password">
                                                <button class="btn btn-outline-dark" type="button"
                                                    id="providerTogglePassword" tabindex="-1">
                                                    <i class="fas fa-eye-slash providertoggleIcon" id="providertoggleIcon"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback" id="provider_password_error"></div>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                        <div class="modal-footer text-end">
                            <button class="btn btn-linear-primary" id="get_started_btn">{{ __('Get Started') }}</button>
                        </div>
                    </form>

                </fieldset>
                <fieldset class="second-field" id="second-field">
                    <form id="companyInfo">
                        <div class="modal-body pb-1">
                            <div class="bg-light-300 p-3 br-10 text-center mb-4">
                                <h4>{{ __('service_heading') }}</h4>
                                <p>{{ __('service_description') }}</p>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">{{ __('service_label') }}</label>
                                <select name="category_id" id="categorySelect" class="form-control select">
                                    <option value="">{{ __('select_category') }}</option>
                                    @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="category_id_error"></div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">{{ __('sub_service_label') }}</label>
                                <div class="form-check ps-0" id="subcategories">
                                    <!-- Dynamically populated subcategories go here -->
                                </div>
                                <span class="invalid-feedback" id="subcategory_ids_error"></span>
                            </div>
                        </div>
                        <div class="modal-body pb-1">
                            <div
                                class="bg-light-300 p-3 br-10 text-center mb-4 d-flex align-items-center justify-content-around">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <input type="radio" id="individual" name="user_type" checked>
                                    <label for="individual" class="fw-bold">{{ __('individual_label') }}</label>
                                </div>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <input type="radio" id="company" name="user_type">
                                    <label for="company" class="fw-bold">{{ __('company_label') }}</label>
                                </div>
                            </div>

                            <div style="display: none;" id="company_details">
                                <div class="mb-4">
                                    <label class="form-label">{{ __('company_name_label') }}</label>
                                    <input type="text" name="company_name" id="company_name" class="form-control"
                                        placeholder="{{ __('company_name_placeholder') }}">
                                    <div class="invalid-feedback" id="company_name_error"></div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">{{ __('company_website_label') }}</label>
                                    <input type="text" name="company_website" id="company_website" class="form-control"
                                        placeholder="{{ __('company_website_placeholder') }}">
                                    <div class="invalid-feedback" id="company_website_error"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input border-primary border-1" type="checkbox" name="provider_terms_policy"
                                            id="provider_terms_policy">
                                        <label class="form-check-label" for="provider_terms_policy">
                                            {{ __('terms_policy_label') }} <a href="{{ route('terms.conditions') }}"
                                                class="text-primary text-decoration-underline">{{ __('terms_of_use') }}</a>
                                            & <a href="{{ route('privacy.policy') }}"
                                                class="text-primary text-decoration-underline">{{ __('privacy_policy') }}</a>
                                        </label>
                                        <div class="invalid-feedback" id="provider_terms_policy_error"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex align-items-center justify-content-between">
                            <a href="#!" class="btn btn-light prev_btn"><i
                                    class="ti ti-arrow-left me-2"></i>{{ __('back_button') }}</a>
                            <button id="provider_register_btn"
                                class="provider_register_btn btn btn-linear-primary">{{ __('sign_up_button') }}</button>
                        </div>
                    </form>
                </fieldset>
            </div>
        </div>
    </div>
</div>
<!-- /Provider Modal -->

<!-- Email Reg otp Modal -->
<div class="modal fade" id="otp-email-prov-reg-modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
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
                        <p id="otp-prov-reg-email-message" class="fs-14">{{ __('OTP sent to your Email Address') }}</p>
                    </div>
                    <div class="text-center otp-input">
                        <div class="inputProvideContainerreg">

                        </div>
                        <span id="error_prov_email_reg_message" class="text-danger"></span>
                        <div>
                            <div class="badge bg-danger-transparent mb-3">
                                <p class="d-flex align-items-center "><i class="ti ti-clock me-1"></i><span
                                        id="otp-pro-timer">00:00</span></p>
                            </div>
                            <div class="mb-3 d-flex justify-content-center">
                                <p>{{ __('Didn t get the OTP?') }} <a href="#!"
                                        class="resendProRegEmailOtp text-primary">{{ __('Resend OTP') }}</a></p>
                            </div>
                            <div>
                                <button type="button" id="verify-email-prov-reg-otp-btn"
                                    class="verify-email-prov-reg-otp-btn btn btn-lg btn-linear-primary w-100">{{ __('Verify & Proceed') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
<!-- /Email otp Modal -->


<!-- Phone otp Modal -->
<div class="modal fade" id="otp-pro-reg-phone-modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
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
                        <p id="otp-prov-reg-sms-message" class="fs-14">{{ __('OTP sent to your mobile number') }}</p>
                    </div>
                    <div class="text-center otp-input">
                        <div class="inputProRegSMSContainer">

                        </div>
                        <span id="error_pro_reg_sms_message" class="text-danger"></span>
                        <div>
                            <div class="badge bg-danger-transparent mb-3">
                                <p class="d-flex align-items-center "><i class="ti ti-clock me-1"></i><span
                                        id="otp-pro-reg-sms-timer">00:00</span></p>
                            </div>
                            <div class="mb-3 d-flex justify-content-center">
                                <p>{{ __('Didn t get the OTP?') }} <a href="#!"
                                        class="resendProRegSMSOtp text-primary">{{ __('Resend OTP') }}</a></p>
                            </div>
                            <div>
                                <button type="button" id="verify-pro-reg-sms-otp-btn"
                                    class="verify-pro-reg-sms-otp-btn btn btn-lg btn-linear-primary w-100">{{ __('Verify & Proceed') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
<!-- /Phone otp Modal -->

<!-- Success Modal -->
<div class="modal fade" id="reg_success_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="mb-4">
                    <span class="success-icon mx-auto mb-4">
                        <i class="ti ti-check"></i>
                    </span>
                    <h4 class="mb-1">{{ __('Registration Successful') }}</h4>
                    <p>{{ __('registration_success_info')}}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Success Modal -->

<!-- Success Modal -->
<div class="modal fade" id="provider_approval_success_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="mb-4">
                    <span class="success-icon mx-auto mb-4">
                        <i class="ti ti-check"></i>
                    </span>
                    <h4 class="mb-1">{{ __('Registration Successful') }}</h4>
                    <p>{{ __('provider_register_approval_success_info')}}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Success Modal -->