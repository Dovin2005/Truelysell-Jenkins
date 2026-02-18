@extends('admin.admin')

@section('content')

    <div class="page-wrapper">
        <form id="otpsettingform">
            <div class="content bg-white">
                <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                    <div class="my-auto mb-2">
                        <h3 class="page-title mb-1">{{ __('OTP Settings') }}</h3>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="javascript:void(0);">{{ __('Settings') }}</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">{{ __('OTP Settings') }}</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                        <div class="pe-1 mb-2">
                            @if(isset($permission))
                                @if(hasPermission($permission, 'General Settings', 'edit'))
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <button class="btn btn-primary otp_save_btn fixed-size-btn d-none real-label" type="submit">{{ __('Update') }}</button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    @include('admin.partials.general_settings_side_menu')
                    <div class="col-xxl-10 col-xl-9">
                        <div class="flex-fill ps-1">
                            <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                            </div>
                            <div class="d-md-flex d-block">
                                <div class="flex-fill">
                                    <input type="hidden" name="group_id" id="group_id" class="form-control" value="9">
                                    <div class="card">
										<div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                                        <div class="skeleton input-skeleton label-loader mb-3"></div>
                                                        <div class="row align-items-center flex-fill d-none real-input">
                                                            <div class="col-xxl-10 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">{{ __('Register') }}</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-xxl-2 col-lg-6">
                                                                <div class="mb-3">
                                                                    <div class="status-toggle modal-status">
                                                                        <input type="checkbox" name="register" id="register" class="check">
                                                                        <label for="register" class="checktoggle"> </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                                        <div class="skeleton input-skeleton input-loader mb-3"></div>
                                                        <div class="row align-items-center flex-fill d-none real-input">
                                                            <div class="col-xxl-10 col-lg-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label">{{ __('Login') }}</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-xxl-2 col-lg-6">
                                                                <div class="mb-3">
                                                                    <div class="status-toggle modal-status">
                                                                        <input type="checkbox" name="login" id="login" class="check">
                                                                        <label for="login" class="checktoggle"> </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3 p-3 pb-0 rounded">
                                                        <div class="skeleton label-skeleton label-loader"></div>
                                                        <label class="form-label d-none real-label">{{ __('OTP Type') }}</label>
                                                        <div class="skeleton input-skeleton input-loader"></div>
                                                        <select class="form-control d-none real-input" name="otp_type[]" id="otp_type" >
                                                            <option value="sms">{{ __('SMS') }}</option>
                                                            <option value="email">{{ __('Email') }}</option>
                                                        </select>
                                                        <span class="text-danger error-text" id="otp_type_error"></span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3 p-3 pb-0 rounded">
                                                        <div class="skeleton label-skeleton label-loader"></div>
                                                        <label class="form-label d-none real-label">{{ __('OTP Digit Limit') }}</label>
                                                        <div class="skeleton input-skeleton input-loader"></div>
                                                        <select class="form-control d-none real-input" name="otp_digit_limit" id="otp_digit_limit">
                                                            <option>4</option>
                                                            <option>5</option>
                                                            <option>6</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3 p-3 pb-0 rounded">
                                                        <div class="skeleton label-skeleton label-loader"></div>
                                                        <label class="form-label d-none real-label">{{ __('OTP Expire Time') }}</label>
                                                        <div class="skeleton input-skeleton input-loader"></div>
                                                        <select class="form-control d-none real-input" name="otp_expire_time" id="otp_expire_time">
                                                            <option>5 mins</option>
                                                            <option>2 mins</option>
                                                            <option>10 mins</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
										</div>
									</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
