@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <form id="maintenance_setting_form">
        <div class="content bg-white">
            <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('Maintenance Settings') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);">{{ __('Settings') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Maintenance Settings') }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="pe-1 mb-2">
                        @if(isset($permission))
                            @if(hasPermission($permission, 'General Settings', 'edit'))
                            <div class="skeleton label-skeleton label-loader"></div>
                                <button class="btn btn-primary maintenance_update_btn fixed-size-btn d-none real-label" type="submit">{{ __('Save') }}</button>
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
                                    <input type="hidden" name="group_id" id="group_id" class="form-control" value="11" >

                                    <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                        <div class="row align-items-center flex-fill">
                                            <div class="col-xxl-3 col-lg-6">
                                                <div class="mb-3">
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <h6 class="d-none real-label">{{ __('Maintenance') }}</h6>
                                                </div>
                                            </div>
                                            <div class="col-xxl-9 col-lg-6">
                                                <div class="mb-3">
                                                    <div class="status-toggle modal-status">
                                                        <div class="skeleton label-skeleton label-loader"></div>
                                                        <input type="checkbox" name="maintenance" id="maintenance" class="check">
                                                        <label for="maintenance" class="checktoggle d-none real-label"> </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                        <div class="row align-items-center flex-fill">
                                            <div class="col-xxl-12 col-lg-12">
                                                <div class="mb-3">
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <h6 class="d-none real-label">{{ __('Maintenance Content') }}</h6>
                                                </div>
                                            </div>
                                            <div class="col-xxl-12 col-lg-12">
                                                <div class="mb-3">
                                                    <div class="skeleton input-skeleton input-loader"></div>
                                                    <textarea rows="3" class="form-control maintenance_content d-none real-input" name="maintenance_content" id="summernote"></textarea>
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
