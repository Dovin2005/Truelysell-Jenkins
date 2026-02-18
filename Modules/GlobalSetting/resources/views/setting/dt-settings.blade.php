@extends('admin.admin')

@section('content')

    <div class="page-wrapper">
        <form id="dtsettingform">
            <div class="content bg-white">
                <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                    <div class="my-auto mb-2">
                        <h3 class="page-title mb-1">{{ __('Date Settings') }}</h3>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="javascript:void(0);">{{ __('Settings') }}</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">{{ __('Date Settings') }}</li>
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
                                                <input type="hidden" name="group_id" id="group_id" class="form-control" value="31">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mb-3 p-3 pb-0 rounded">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <label class="form-label d-none real-label">{{ __('Date Formate') }}</label>
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <div class="d-none real-input">
                                                                <select class="form-control select2" name="date_format_view" id="date_format_view" >
                                                                    @if ($dateFormats)
                                                                        @foreach ($dateFormats as $format)
                                                                            <option value="{{ $format->name }}">{{ $format->name }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                            <span class="text-danger error-text" id="otp_type_error"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-3 p-3 pb-0 rounded">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <label class="form-label d-none real-label">{{ __('Time Format') }}</label>
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <div class="d-none real-input">
                                                                <select class="form-control select2" name="time_format_view" id="time_format_view">
                                                                    @if ($timeFormats)
                                                                        @foreach ($timeFormats as $format)
                                                                            <option value="{{ $format->name }}">{{ $format->name }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-3 p-3 pb-0 rounded">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <label class="form-label d-none real-label">{{ __('TimeZone') }}</label>
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <div class="d-none real-input">
                                                                <select class="form-control select2" name="timezone_format_view" id="timezone_format_view">
                                                                    @if ($timezones)
                                                                        @foreach ($timezones as $format)
                                                                            <option value="{{ $format->name }}">{{ $format->name }}</option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-3 p-3 pb-0 rounded">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <label class="form-label d-none real-label">{{ __('booking_prefix') }}</label>
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <input type="text" class="form-control d-none real-input" id="booking_prefix" name="booking_prefix" value="">
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
