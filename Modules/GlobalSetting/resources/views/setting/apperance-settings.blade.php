@extends('admin.admin')
@section('content')

<div class="page-wrapper">
    <form id="apperance_setting_form">
        <div class="content bg-white">
            <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('Appearance Settings') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);">{{ __('Settings') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Appearance Settings') }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="pe-1 mb-2">
                        <button class="btn btn-primary apperance_setting_update_btn fixed-size-btn" type="submit">{{ __('Save') }}</button>
                    </div>
                </div>
            </div>
            <div class="row">
                @include('admin.partials.general_settings_side_menu')
                <div class="col-xxl-10 col-xl-9">
                    <div class="border-start ps-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                        </div>
                        <div class="d-md-flex">
                            <div class="row flex-fill">
                                <div class="col-xl-12">
                                    <div>
                                        <input type="hidden" name="group_id" id="group_id" class="form-control" value="15" >

                                        <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                            <div class="row align-items-center flex-fill">
                                                <div class="col-xxl-3 col-lg-6">
                                                    <div class="mb-3">
                                                        <h6>{{ __('Primary Color') }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-2 col-lg-6">
                                                    <div class="mb-3">
                                                        <input class="form-control form-input-color" type="color" id="primary_color" value="#136bd0">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                            <div class="row align-items-center flex-fill">
                                                <div class="col-xxl-3 col-lg-6">
                                                    <div class="mb-3">
                                                        <h6>{{ __('Secondary Colour') }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-2 col-lg-6">
                                                    <div class="mb-3">
                                                        <input class="form-control form-input-color" type="color" id="secondary_color" value="#136bd0">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                            <div class="row align-items-center flex-fill">
                                                <div class="col-xxl-3 col-lg-6">
                                                    <div class="mb-3">
                                                        <h6>{{ __('Button colour') }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-2 col-lg-6">
                                                    <div class="mb-3">
                                                        <input class="form-control form-input-color" type="color" id="button_color" value="#136bd0">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                            <div class="row align-items-center flex-fill">
                                                <div class="col-xxl-3 col-lg-6">
                                                    <div class="mb-3">
                                                        <h6>{{ __('Button hover color') }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-2 col-lg-6">
                                                    <div class="mb-3">
                                                        <input class="form-control form-input-color" type="color" id="button_hover_color" value="#136bd0">
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
