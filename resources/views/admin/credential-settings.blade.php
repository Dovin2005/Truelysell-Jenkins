@extends('admin.admin')

@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('credential_setting') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);">{{ __('Settings') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('credential_setting') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- /Page Header -->

            <div class="row">
                @include('admin.partials.general_settings_side_menu')
                <div class="col-xxl-10 col-xl-9">
                    <div class="flex-fill ps-1">
                        <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                        </div>
                        <div class="d-md-flex d-block">
                            <div class="flex-fill">
                                <div class="row">
                                    <div class="col-xxl-6 col-xl-6">
                                        <form id="ssoForm">
                                            <div class="card">
                                                <div
                                                    class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <div class="d-flex align-items-center d-none real-label">
                                                        <span
                                                            class="avatar avatar-lg p-2 rounded bg-gray flex-shrink-0 me-1">
                                                            <img src="/assets/img/icons/google-icon.svg" alt="Img">
                                                        </span>
                                                        <h6>{{ __('SSO Credential') }}</h6>
                                                    </div>
                                                    <div class="status-toggle modal-status d-none real-label">
                                                        <input type="checkbox" name="sso_status" id="sso_status"
                                                            class="check">
                                                        <label for="sso_status" class="checktoggle"></label>
                                                    </div>
                                                </div>
                                                <div class="card-body pt-0">
                                                    <div class="skeleton input-skeleton input-loader"></div>
                                                    <p class="d-none real-input">
                                                        {{ __('Configure your Single Sign-On (SSO) credentials below') }}
                                                    </p>
                                                </div>
                                                <div class="card-footer d-flex justify-content-end align-items-end">
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                                    <label
                                                                        class="form-label d-none real-label">{{ __('Client ID') }}</label>
                                                                    <div class="skeleton input-skeleton input-loader"></div>
                                                                    <input type="text" name="sso_client_id"
                                                                        id="sso_client_id"
                                                                        class="form-control d-none real-input"
                                                                        placeholder="{{ __('Enter Client ID') }}">
                                                                    <div class="invalid-feedback" id="sso_client_id_error">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                                    <label
                                                                        class="form-label d-none real-label">{{ __('Client Secret') }}</label>
                                                                    <div class="skeleton input-skeleton input-loader"></div>
                                                                    <input type="text" name="sso_client_secret"
                                                                        id="sso_client_secret"
                                                                        class="form-control d-none real-input"
                                                                        placeholder="{{ __('Enter Client Secret') }}">
                                                                    <div class="invalid-feedback"
                                                                        id="sso_client_secret_error"></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                                    <label
                                                                        class="form-label d-none real-label">{{ __('Redirect URL') }}</label>
                                                                    <div class="skeleton input-skeleton input-loader"></div>
                                                                    <input type="text" name="sso_redirect_url"
                                                                        id="sso_redirect_url"
                                                                        class="form-control d-none real-input"
                                                                        placeholder="Ex: https://example.com/auth/google-callback">
                                                                    <div class="invalid-feedback"
                                                                        id="sso_redirect_url_error"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            @if (isset($permission))
                                                                @if (hasPermission($permission, 'General Settings', 'edit'))
                                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                                    <button type="submit"
                                                                        class="btn btn-primary sso_setting_btn d-none real-label"
                                                                        data-update="{{ __('Update') }}">
                                                                        {{ __('Update') }}
                                                                    </button>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="col-xxl-6 col-xl-6">
                                        <form id="chatgptForm">
                                            <div class="card">
                                                <div
                                                    class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <div class="d-flex align-items-center d-none real-label">
                                                        <span
                                                            class="avatar avatar-lg p-2 rounded bg-gray flex-shrink-0 me-2"><img
                                                                src="/assets/img/icons/chatgpt-logo.jpg"
                                                                alt="Img"></span>
                                                        <h6>{{ __('ChatGPT') }}</h6>
                                                    </div>
                                                    <div class="status-toggle modal-status d-none real-label">
                                                        <input type="checkbox" name="chatgpt_status" id="chatgpt_status"
                                                            class="check">
                                                        <label for="chatgpt_status" class="checktoggle"> </label>
                                                    </div>
                                                </div>
                                                <div class="card-body pt-0">
                                                    <div class="skeleton input-skeleton input-loader"></div>
                                                    <p class="d-none real-input">{{ __('chatgpt_info') }}</p>
                                                </div>
                                                <div class="card-footer d-flex justify-content-between align-items-center">
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <input type="text" name="group_id" id="group_id"
                                                                        value="4" hidden>
                                                                    <div class="skeleton label-skeleton label-loader">
                                                                    </div>
                                                                    <label
                                                                        class="form-label d-none real-label">{{ __('API_Key') }}</label>
                                                                    <div class="skeleton input-skeleton input-loader">
                                                                    </div>
                                                                    <input type="text" name="chatgpt_api_key"
                                                                        id="chatgpt_api_key"
                                                                        class="form-control d-none real-input"
                                                                        placeholder="{{ __('enter_api_key') }}">
                                                                    <div class="invalid-feedback"
                                                                        id="chatgpt_api_key_error"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            @if (isset($permission))
                                                                @if (hasPermission($permission, 'General Settings', 'edit'))
                                                                    <div class="skeleton label-skeleton label-loader">
                                                                    </div>
                                                                    <button type="submit"
                                                                        class="btn btn-primary chatgpt_setting_btn d-none real-label">{{ __('Update') }}</button>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Google Location API -->
                                    <div class="col-xxl-6 col-xl-6">
                                        <form id="googleLocationApi">
                                            <div class="card">
                                                <div
                                                    class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <div class="d-flex align-items-center d-none real-label">
                                                        <span
                                                            class="avatar avatar-lg p-2 rounded bg-gray flex-shrink-0 me-2"><img
                                                                src="/assets/img/icons/google-map.png"
                                                                alt="Img"></span>
                                                        <h6>{{ __('Google Location API') }}</h6>
                                                    </div>
                                                    <div class="status-toggle modal-status d-none real-label">
                                                        <input type="checkbox" name="location_status"
                                                            id="location_status" class="check">
                                                        <label for="location_status" class="checktoggle"> </label>
                                                    </div>
                                                </div>
                                                <div class="card-body pt-0">
                                                    <div class="skeleton input-skeleton input-loader"></div>
                                                    <p class="d-none real-input">{{ __('location_infos') }}</p>
                                                </div>
                                                <div class="card-footer d-flex justify-content-between align-items-center">
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <input type="text" name="group_id" id="group_id"
                                                                        value="4" hidden>
                                                                    <div class="skeleton label-skeleton label-loader">
                                                                    </div>
                                                                    <label
                                                                        class="form-label d-none real-label">{{ __('API_Key') }}</label>
                                                                    <div class="skeleton input-skeleton input-loader">
                                                                    </div>
                                                                    <input type="text" name="location_api_key"
                                                                        id="location_api_key"
                                                                        class="form-control d-none real-input"
                                                                        placeholder="{{ __('enter_api_key') }}">
                                                                    <div class="invalid-feedback"
                                                                        id="location_api_key_error"></div>
                                                                         <!-- Unit Dropdown -->
                                                                    <label class="form-label d-none real-label mt-2">{{ __('Select Unit') }}</label>
                                                                    <select name="location_unit" id="location_unit" class="form-control d-none real-input">
                                                                        <option value="" disabled selected>{{ __('Select Unit') }}</option>
                                                                        <option value="km">{{ __('Km') }}</option>
                                                                        <option value="miles">{{ __('Miles') }}</option>
                                                                    </select>

                                                                    <!-- Distance Input -->
                                                                    <label class="form-label d-none real-label mt-2" id="distance_label" style="display:none;">
                                                                        {{ __('Distance Radius') }}
                                                                    </label>
                                                                    <input type="text" name="location_distance" id="location_distance"
                                                                        class="form-control d-none real-input" style="display:none;"
                                                                        placeholder="{{ __('Enter Value') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            @if (isset($permission))
                                                                @if (hasPermission($permission, 'General Settings', 'edit'))
                                                                    <div class="skeleton label-skeleton label-loader">
                                                                    </div>
                                                                    <button type="submit"
                                                                        class="btn btn-primary location_setting_btn d-none real-label">{{ __('Update') }}</button>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- End -->

                                    <!-- Google recaptcha -->
                                    <div class="col-xxl-6 col-xl-6">
                                        <form id="googlerecaptchaApi">
                                            <div class="card">
                                                <div
                                                    class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <div class="d-flex align-items-center d-none real-label">
                                                        <span
                                                            class="avatar avatar-lg p-2 rounded bg-gray flex-shrink-0 me-2"><img
                                                                src="/assets/img/icons/recaptcha.png"
                                                                alt="Img"></span>
                                                        <h6>{{ __('Google reCaptcha') }}</h6>
                                                    </div>
                                                    <div class="status-toggle modal-status d-none real-label">
                                                        <input type="checkbox" name="recaptcha_status"
                                                            id="recaptcha_status" class="check">
                                                        <label for="recaptcha_status" class="checktoggle"> </label>
                                                    </div>
                                                </div>
                                                <div class="card-body pt-0">
                                                    <div class="skeleton input-skeleton input-loader"></div>
                                                    <p class="d-none real-input">{{ __('reCaptcha_infos') }}</p>
                                                </div>
                                                <div class="card-footer d-flex justify-content-between align-items-center">
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <input type="text" name="group_id" id="group_id"
                                                                        value="4" hidden>
                                                                    <div class="skeleton label-skeleton label-loader">
                                                                    </div>
                                                                    <label
                                                                        class="form-label d-none real-label">{{ __('Client ID') }}</label>
                                                                    <div class="skeleton input-skeleton input-loader">
                                                                    </div>
                                                                    <input type="text" name="recaptcha_api_key"
                                                                        id="recaptcha_api_key"
                                                                        class="form-control d-none real-input"
                                                                        placeholder="{{ __('enter_api_key') }}">
                                                                    <div class="invalid-feedback"
                                                                        id="recaptcha_api_key_error"></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <input type="text" name="group_id" id="group_id"
                                                                        value="4" hidden>
                                                                    <div class="skeleton label-skeleton label-loader">
                                                                    </div>
                                                                    <label
                                                                        class="form-label d-none real-label">{{ __('Client Secret') }}</label>
                                                                    <div class="skeleton input-skeleton input-loader">
                                                                    </div>
                                                                    <input type="text" name="recaptcha_secret_key"
                                                                        id="recaptcha_secret_key"
                                                                        class="form-control d-none real-input"
                                                                        placeholder="{{ __('enter_api_key') }}">
                                                                    <div class="invalid-feedback"
                                                                        id="recaptcha_secret_key_error"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            @if (isset($permission))
                                                                @if (hasPermission($permission, 'General Settings', 'edit'))
                                                                    <div class="skeleton label-skeleton label-loader">
                                                                    </div>
                                                                    <button type="submit"
                                                                        class="btn btn-primary recaptcha_setting_btn d-none real-label">{{ __('Update') }}</button>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- End -->

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
