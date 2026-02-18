@extends('admin.admin')

@section('content')

    <div class="page-wrapper">
        <form id="searchsettingform">
            <div class="content bg-white">
                <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                    <div class="my-auto mb-2">
                        <h3 class="page-title mb-1">{{ __('Search Settings') }}</h3>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="javascript:void(0);">{{ __('Settings') }}</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">{{ __('Search Settings') }}</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                        <div class="pe-1 mb-2">
                            @if(isset($permission))
                                @if(hasPermission($permission, 'General Settings', 'edit'))
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <button class="btn btn-primary dt_save_btn fixed-size-btn d-none real-label" type="submit" data-update="{{ __('Update') }}">{{ __('Update') }}</button>
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
                            <div class="row flex-fill">
                                <div class="col-xl-12">
                                    <div class="card">
                                        <div class="card-body p-0 py-3">
                                            <div class="d-block">
                                                <input type="hidden" name="group_id" id="group_id" class="form-control" value="32">
                                            
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3 p-3 pb-0 rounded">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <label class="form-label d-none real-label">{{ __('Miles Radius') }}</label>
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <input type="text" name="milesradius" id="milesradius" class="form-control d-none real-input">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3 p-3 pb-0 rounded">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <label class="form-label d-none real-label">{{ __('Google Map Key') }}</label>
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <input type="text" name="goe_key" id="goe_key" class="form-control d-none real-input">
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
