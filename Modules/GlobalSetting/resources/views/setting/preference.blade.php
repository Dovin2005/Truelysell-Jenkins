@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <form id="product_setting_form">
        <div class="content bg-white">
            <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('Preference') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);">{{ __('Settings') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Preference') }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="pe-1 mb-2">
                        @if(isset($permission))
                            @if(hasPermission($permission, 'General Settings', 'edit'))
                                <div class="skeleton label-skeleton label-loader"></div>
                                <button class="btn btn-primary leads_setting_btn fixed-size-btn d-none real-label" type="submit">{{ __('Save') }}</button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                @include('admin.partials.general_settings_side_menu')
                <div class="col-xxl-10 col-xl-9">
                    <div class="flex-fill ps-1">
                        <div class="d-md-flex justify-content-between flex-wrap mb-3">
                        </div>
                        <div class="d-md-flex">
                            <div class="row flex-fill">
                                <div class="col-xl-12">
                                    <div>
                                        <input type="hidden" name="group_id" id="group_id" class="form-control" value="12" >

                                        <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                            <div class="row align-items-center flex-fill">

                                                <div class="col-md-6">
                                                    <div class="card border mb-3">
                                                        <div class="card-body d-flex align-items-center justify-content-between">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <div class="d-flex align-items-center d-none real-label">
                                                                <img src="{{ asset('/assets/img/icons/company-icon-01.svg') }}" alt="">
                                                                <h6 class="fw-semibold ms-3">{{ __('Leads') }}</h6>
                                                            </div>
                                                            <div class="status-toggle modal-status">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <input type="checkbox" name="leads_status" id="leads_status" class="check">
                                                                <label for="leads_status" class="checktoggle d-none real-label"> </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6">
                                                    <div class="card border mb-3">
                                                        <div class="card-body d-flex align-items-center justify-content-between">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <div class="d-flex align-items-center d-none real-label">
                                                                <img src="{{ asset('/assets/img/icons/company-icon-02.svg') }}" alt="">
                                                                <h6 class="fw-semibold ms-3">{{ __('Service') }}</h6>
                                                            </div>
                                                            <div class="status-toggle modal-status">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <input type="checkbox" name="service_status" id="service_status" class="check">
                                                                <label for="service_status" class="checktoggle d-none real-label"> </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="card border mb-3">
                                                        <div class="card-body">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <img src="{{ asset('/assets/img/icons/company-icon-03.svg') }}" alt="">
                                                                <h6 class="fw-semibold ms-3">{{ __('Payment Mode In Booking') }}</h6>
                                                            </div>

                                                            <!-- Loader -->
                                                            <div class="skeleton label-skeleton label-loader mb-2"></div>

                                                            <!-- Radio Options -->
                                                            <div class="d-none real-label">

                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input"
                                                                        type="radio"
                                                                        name="paymentmode_status"
                                                                        id="paymentmode_online"
                                                                        value="1">
                                                                    <label class="form-check-label" for="paymentmode_online">
                                                                        {{ __('Enable online payment only') }}
                                                                    </label>
                                                                </div>

                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input"
                                                                        type="radio"
                                                                        name="paymentmode_status"
                                                                        id="paymentmode_place"
                                                                        value="2">
                                                                    <label class="form-check-label" for="paymentmode_place">
                                                                        {{ __('Enable place order only') }}
                                                                    </label>
                                                                </div>

                                                                <div class="form-check">
                                                                    <input class="form-check-input"
                                                                        type="radio"
                                                                        name="paymentmode_status"
                                                                        id="paymentmode_both"
                                                                        value="3">
                                                                    <label class="form-check-label" for="paymentmode_both">
                                                                        {{ __('Both') }}
                                                                    </label>
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
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
