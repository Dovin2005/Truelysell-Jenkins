@extends('provider.provider')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="service-wizard mb-4">
                        <ul class="d-flex align-items-center flex-wrap row-gap-2" id="progressbar">
                            <li class="active me-2">
                                <span class="me-2"><i class="ti ti-map-pin"></i></span>
                                <h6 class="translatable" data-translate="service_information">
                                    {{ __('Branch Information') }}
                                </h6>
                            </li>
                            <li class="me-2">
                                <span class="me-2"><i class="ti ti-map-pin"></i></span>
                                <h6 class="translatable" data-translate="location_info">{{ __('Add Staff') }}
                                </h6>
                            </li>
                        </ul>
                    </div>

                    <div class="service-inform-fieldset">
                        <fieldset id="first-field" style="display: block;">
                            <form id="branchForm">

                                <h4 class="mb-3" >{{ __('Branch Information') }}</h4>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label"  for="branch_name">{{ __('Branch Name') }}<span class="text-danger"> *</span></label>
                                                    <input type="text" name="branch_name" id="branch_name" class="form-control" placeholder="{{ __('Enter Branch Name') }}">
                                                    <span class="text-danger error-text" id="branch_name_error" data-required="{{ __('branch_name_required') }}" data-exists="{{ __('branch_name_exists') }}" data-max="{{ __('branch_name_max') }}"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{__('phone_number')}}<span class="text-danger"> *</span></label>
                                                    <input type="text" class="form-control branch_phone_number" id="phone_number" name="phone_number" placeholder="{{ __('enter_phone_number') }}">
                                                    <input type="hidden" id="branch_phone_number" name="international_phone_number">
                                                    <span class="text-danger error-text" id="phone_number_error" data-required="{{ __('phone_number_required') }}" data-between="{{ __('phone_number_between') }}"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label"  for="email">{{ __('Email') }}<span class="text-danger"> *</span></label>
                                                    <input type="text" name="email" id="email" class="form-control" placeholder="{{ __('enter_email') }}">
                                                    <span class="text-danger error-text" id="email_error" data-required="{{ __('email_required') }}" data-format="{{ __('email_format') }}"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{__('address')}}<span class="text-danger"> *</span></label>
                                                    <input type="text" class="form-control" id="branch_address" name="branch_address" value="{{ $data->userDetails->address ?? ''}}" placeholder="{{ __('enter_address') }}">
                                                    <span class="text-danger error-text" id="branch_address_error" data-required="{{ __('address_required') }}" data-max="{{ __('address_maxlength') }}"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{__('country')}}<span class="text-danger"> *</span></label>
                                                    <select class="select form-control select2" id="country" name="country" data-placeholder="{{__('select_country')}}">
                                                        <option value="">{{ __('select_country') }}</option>
                                                        @if ($countries)
                                                            @foreach ($countries as $country)
                                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <span class="text-danger error-text" id="country_error"  data-required="{{ __('country_required') }}"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{__('state')}}<span class="text-danger">
                                                            *</span></label>
                                                    <select class="select form-control select2" id="state" name="state" data-placeholder="{{__('select_state')}}">
                                                    </select>
                                                    <span class="text-danger error-text" id="state_error" data-required="{{ __('state_required') }}"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{__('city')}}<span class="text-danger">
                                                            *</span></label>
                                                    <select class="select form-control select2" id="city" name="city"
                                                        data-placeholder="{{__('select_city')}}">
                                                    </select>
                                                    <span class="text-danger error-text" id="city_error" data-required="{{ __('city_required') }}"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{__('ZIP Code')}}<span class="text-danger">
                                                            *</span></label>
                                                    <input type="text" class="form-control" id="zip_code" name="zip_code">
                                                    <span class="text-danger error-text" id="zip_code_error" data-required="{{ __('zip_code_required') }}" data-max="{{ __('zip_code_maxlength') }}" data-char="{{ __('zip_code_char') }}"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{__('Working Start Hour')}}<span class="text-danger"> *</span></label>
                                                    <input type="time" class="form-control" name="start_hour" id="start_hour">
                                                    <span class="text-danger error-text" id="start_hour_error" data-required="{{ __('working_star_hour_required') }}"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">{{__('Working End Hour')}}<span class="text-danger"> *</span></label>
                                                    <input type="time" class="form-control" name="end_hour" id="end_hour">
                                                    <span class="text-danger error-text" id="end_hour_error" data-required="{{ __('working_end_hour_required') }}"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">{{__('Working Day')}}<span class="text-danger"> *</span></label>
                                                    <div class="">
                                                    @php
                                                        $days = [
                                                            __('Sunday') => 'sunday',
                                                            __('Monday') => 'monday',
                                                            __('Tuesday') => 'tuesday',
                                                            __('Wednesday') => 'wednesday',
                                                            __('Thursday') => 'thursday',
                                                            __('Friday') => 'friday',
                                                            __('Saturday') => 'saturday',
                                                        ];
                                                    @endphp
                                                    @foreach ($days as $dayText => $dayValue)
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input working_day" type="checkbox" id="working_day_{{ $dayValue }}" name="working_day[]" value="{{ $dayValue }}">
                                                            <label class="form-check-label" for="working_day_{{ $dayValue }}">{{ $dayText }}</label>
                                                        </div>
                                                    @endforeach
                                                    </div>
                                                    <span class="text-danger error-text" id="working_day_sunday_error" data-required="{{ __('working_day_required') }}"></span>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">{{__('Holiday')}}</label>
                                                <div id="holidayContainer">
                                                    <div class="row">
                                                        <div class="col-xl-4">
                                                            <div class="mb-3">
                                                                <div class=" input-icon position-relative">
                                                                    <input type="date" class="form-control" name="holiday[]" id="holiday" placeholder="dd-mm-yyyy">
                                                                    <span class="text-danger error-text" id="holiday_error"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <a href="javascript:void(0);"
                                                    class="text-primary d-inline-flex align-items-center fs-14 mb-3 addHolidayBtn"
                                                    data-translate="new"><i class="ti ti-circle-plus me-2"></i>{{ __('Add Holiday') }}</a>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label" for="branch_image">{{ __('image') }}<span class="text-danger"> *</span></label>
                                                    <div class="d-flex align-items-center flex-wrap row-gap-3 gap-2">
                                                        <div
                                                            class="file-upload d-flex align-items-center justify-content-center flex-column">
                                                            <i class="ti ti-photo mb-2"></i>
                                                            <label class="form-label">{{ __('image') }}</label>
                                                            <input type="file" name="branch_image" id="branch_image" class="form-control">
                                                        </div>
                                                        
                                                        <div id="image_preview_container" class="d-flex flex-wrap">
                                                            <div class="avatar avatar-gallery me-3" id="branch_img_container" style="display: none">
                                                                <img src="" alt="Img" id="branch_img_preview" style="width: 100px; height: 100px; object-fit: cover;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span class="text-danger error-text" id="branch_image_error" data-required="{{ __('image_required') }}" data-size="{{ __('image_filesize') }}" data-extension="{{ __('image_extension') }}"></span>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="d-flex align-items-center justify-content-end">
                                            <button id="branch_btn" class="btn btn-dark translatable"
                                                data-translate="continue">{{ __('Continue') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </fieldset>

                        <fieldset id="second-field" style="display: none">
                            <form id="staffsForm">
                                <h4 class="mb-3" >{{ __('Staffs') }}</h4>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="border-bottom mb-3 pb-3">
                                            <h4 class="fs-20">{{ __('Add Staff') }}</h4>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">{{__('Staffs')}}</label>
                                                    <select class="select form-control select2" id="staff" name="staffs[]" data-placeholder="{{__('select_staff')}}" multiple>
                                                        <option value="">{{ __('Selct staff') }}</option>
                                                        @if ($staffs)
                                                            @foreach ($staffs as $staff)
                                                                <option value="{{ $staff->user_id }}">{{ $staff->first_name }} {{ $staff->last_name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <span class="text-danger error-text" id="staff_error"></span>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="d-flex align-items-center justify-content-end">
                                            <a id="staff_prev" class="btn btn-light me-3 translatable"
                                                data-translate="back">{{ __('Back') }}</a>
                                            <a id="staff_btn" class="btn btn-dark translatable"
                                                data-translate="continue" data-save="{{ __('Save') }}">{{ __('Save') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </fieldset>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
</div>

@endsection