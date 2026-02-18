@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content bg-white">
        <form id="invoice_setting_form">
            <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('invoice_settings') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);">{{ __('Settings') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('invoice_settings') }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="mb-3">
                        @if(isset($permission))
                            @if(hasPermission($permission, 'General Settings', 'edit'))
                                <div class="skeleton label-skeleton label-loader"></div>
                                <button class="btn btn-primary invoice_save_btn fixed-size-btn d-none real-label" type="submit" data-save="{{ __('Save') }}">{{ __('Save') }}</button>
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
                            <div class="row flex-fill">
                                <div class="col-xl-12">
                                    <div class="settings-middle-info invoice-setting-wrap">
                                        <div class="skeleton input-skeleton input-loader mb-2 p-2"></div>
                                        <div class="row align-items-center  mb-2 d-none real-input">
                                            <div class="col-xxl-7 col-lg-6">
                                                <div class="invoice-info-title">
                                                    <h6>{{ __('Invoice_Logo') }}</h6>
                                                    <p>{{ __('upload_logo_info') }}</p>
                                                </div>
                                            </div>
                                            <div class="col-xxl-5 col-lg-6">
                                                <div class="card mt-2">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between mb-3">
                                                            <div class="d-flex align-items-center">
                                                                <span class="avatar avatar-xl border rounded d-flex align-items-center justify-content-center p-2 me-2">
                                                                    <img id="invoice_company_logo_image" src="{{ asset('assets/img/logo-small.svg') }}" alt="{{ __('Invoice_Company_Logo') }}">
                                                                </span>
                                                                <h5>{{ __('Logo') }}</h5>
                                                            </div>
                                                        </div>
                                                        <div class="profile-uploader profile-uploader-two mb-0">
                                                            <span class="d-block text-center lh-1 fs-24 mb-1"><i class="ti ti-upload"></i></span>
                                                            <div class="drag-upload-btn bg-transparent me-0 border-0">
                                                                <p class="fs-12 mb-2"><span class="text-primary">{{ __('click_to_upload') }}</span> {{ __('drag_and_drop') }}</p>
                                                                <h6>{{ __('image_format') }}</h6>
                                                                <h6>{{ __('max_size', ['width' => 155, 'height' => 40]) }}</h6>
                                                            </div>
                                                            <input type="file" class="form-control"  accept="image/*" id="image_sign">
                                                            <div id="frames"></div>
                                                        </div>
                                                        <span class="text-danger error-text" id="image_sign_error" data-image_size="{{ __('The file size must be less than 5 MB.') }}" data-image_format="{{ __('Only JPG, PNG, and SVG images are allowed.') }}"></span>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" name="group_id" id="group_id" class="form-control" value="2" >

                                        <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                            <div class="row align-items-center flex-fill">
                                                <div class="col-xxl-7 col-lg-6">
                                                    <div class="mb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>
                                                        <h6 class="d-none real-label">{{ __('invoice_prefix') }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-5 col-lg-6">
                                                    <div class="mb-3">
                                                        <div class="skeleton input-skeleton input-loader"></div>
                                                        <input type="text" class="form-control d-none real-input" name="invoice_prefix" id="invoice_prefix">
                                                        <span class="text-danger error-text" id="invoice_prefix_error" data-empty="{{ __('Invoice prefix cannot be empty.') }}"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                            <div class="row align-items-center flex-fill">
                                                <div class="col-xxl-7 col-lg-6">
                                                    <div class="mb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>
                                                        <h6 class="d-none real-label">{{ __('Provider Logo') }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-5 col-lg-6">
                                                    <div class="mb-3">
                                                        <div class="status-toggle modal-status">
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <input type="checkbox" name="providerlogo" id="providerlogo" class="check">
                                                            <label for="providerlogo" class="checktoggle d-none real-input"> </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                            <div class="row align-items-center flex-fill">
                                                <div class="col-xxl-7 col-lg-6">
                                                    <div class="mb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>
                                                        <h6 class="d-none real-label">{{ __('Invoice_Starts') }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-5 col-lg-6">
                                                    <div class="mb-3">
                                                        <div class="skeleton input-skeleton input-loader"></div>
                                                        <input type="text" class="form-control d-none real-input" name="invoice_starts" id="invoice_starts">
                                                        <span class="text-danger error-text" id="invoice_starts_error" data-empty="{{ __('Invoice starts cannot be empty.') }}"></span>
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
</div>
@endsection
