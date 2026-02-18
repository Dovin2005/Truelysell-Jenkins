@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content bg-white">
        <form id="appointmentForm">
            <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('Appointment Settings') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);">{{ __('Settings') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Appointment Settings') }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="pe-1 mb-2">
                        @if(isset($permission))
                            @if(hasPermission($permission, 'General Settings', 'edit'))
                                <button class="btn btn-primary appointment_setting_btn fixed-size-btn" type="submit" data-save="{{ __('Save') }}">{{ __('Save') }}</button>
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
                                        <input type="hidden" name="group_id" id="group_id" class="form-control" value="33" >

                                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                                            <div class="row align-items-center flex-fill">
                                                <div class="col-md-12">
                                                    <div class="card border mb-3">
                                                        <div class="card-body d-flex align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ asset('assets/img/icons/clock.svg') }}" alt="">
                                                                <h6 class="fw-semibold ms-3">{{ __('Appointment time intervals') }}</h6><span class="ms-1">(30 {{ __('minutes') }})</span>
                                                            </div>
                                                            <div class="status-toggle modal-status">
                                                                <input type="checkbox" name="appointment_time_intervals" id="appointment_time_intervals" class="check">
                                                                <label for="appointment_time_intervals" class="checktoggle"> </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="card border mb-3">
                                                        <div class="card-body d-flex align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ asset('assets/img/icons/booking.svg') }}" alt="">
                                                                <h6 class="fw-semibold ms-3">{{ __('Multiple booking for same time slot') }}</h6><span class="ms-1">({{__('Time Slot')}})</span>
                                                            </div>
                                                            <div class="status-toggle modal-status">
                                                                <input type="checkbox" name="multiple_booking_same_time" id="multiple_booking_same_time" class="check">
                                                                <label for="multiple_booking_same_time" class="checktoggle"> </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="card border mb-3">
                                                        <div class="card-body d-flex align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ asset('assets/img/icons/appointment-time.svg') }}" alt="">
                                                                <h6 class="fw-semibold ms-3">{{ __('Minimum advance booking time') }}</h6><span class="ms-1">(1 {{ __('hours') }} to 10 {{ __('days') }})</span>
                                                            </div>
                                                            <div class="status-toggle modal-status">
                                                                <input type="checkbox" name="min_booking_time" id="min_booking_time" class="check">
                                                                <label for="min_booking_time" class="checktoggle"> </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="card border mb-3">
                                                        <div class="card-body d-flex align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ asset('assets/img/icons/appointment-time.svg') }}" alt="">
                                                                <h6 class="fw-semibold ms-3">{{ __('Maximum advance booking time') }}</h6><span class="ms-1">(1 {{ __('Month') }} to 5 {{ __('years') }})</span>
                                                            </div>
                                                            <div class="status-toggle modal-status">
                                                                <input type="checkbox" name="max_booking_time" id="max_booking_time" class="check">
                                                                <label for="max_booking_time" class="checktoggle"> </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="card border mb-3">
                                                        <div class="card-body d-flex align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ asset('assets/img/icons/cancel-booking.svg') }}" alt="">
                                                                <h6 class="fw-semibold ms-3">{{ __('Cancellation time before appointment scheduled') }}</h6><span class="ms-1">(1 {{ __('hour') }} to 12 {{ __('hours') }}, 12 {{ __('hours') }}, 24 {{ __('hours') }}, 36 {{ __('hours') }}, 48 {{ __('hours') }})</span>
                                                            </div>
                                                            <div class="status-toggle modal-status">
                                                                <input type="checkbox" name="cancel_time_before" id="cancel_time_before" class="check">
                                                                <label for="cancel_time_before" class="checktoggle"> </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="card border mb-3">
                                                        <div class="card-body d-flex align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ asset('assets/img/icons/booking-2.svg') }}" alt="">
                                                                <h6 class="fw-semibold ms-3">{{ __('Rescheduling time before appointment scheduled') }}</h6><span class="ms-1">(1 {{ __('hours') }} to 12 {{ __('hours') }})</span>
                                                            </div>
                                                            <div class="status-toggle modal-status">
                                                                <input type="checkbox" name="reschedule_time_before" id="reschedule_time_before" class="check">
                                                                <label for="reschedule_time_before" class="checktoggle"> </label>
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
</div>
@endsection
