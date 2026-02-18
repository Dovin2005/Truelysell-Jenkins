@extends('front')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper content-widget">
    <div class="content">
        <div class="container">

            <!-- Booking -->
            <div class="row">
                <div class="col-xxl-10 col-xl-11 mx-auto">
                    <div class="card border-0 mb-0">
                        <div class="card-body p-3 fieldset-wizard">
                            <div class="row">

                                <!-- Booking Sidebar -->
                                <div class="col-lg-3 theiaStickySidebar">
                                    <div class="card bg-dark booking-sidebar mb-4 mb-lg-0">
                                        <div class="card-body">
                                            <div class="skeleton label-skeleton label-loader"></div>
                                            <h6 class="text-white fs-14 mb-2  d-none real-label">
                                                {{ __('Service Details') }}
                                            </h6>
                                            <div class="service-info d-flex align-items-center">
                                                <span class="avatar avatar-md me-2 flex-shrink-0">
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <img src="{{ $firstImage }}" alt="img"
                                                        class="d-none real-label">
                                                </span>
                                                <div>
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <p class="fs-12 text-white fw-medium mb-1 d-none real-label">
                                                        {{ $service->source_name }}
                                                    </p>
                                                    <div class="skeleton label-skeleton label-loader d-none real-label">
                                                    </div>
                                                    <span class="fs-10 d-none real-label"><i
                                                            class="ti ti-star-filled text-warning me-1"></i>{{ $averageRating }}
                                                        ({{ $ratingCount }} {{ __('reviews') }}))</span>
                                                </div>
                                            </div>
                                            <div class="booking-wizard">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <h6 class="text-white fs-14 mb-3 d-none real-label">{{ __('Bookings') }}
                                                </h6>
                                                <ul class="wizard-progress" id="bokingwizard">
                                                    @php
                                                    $step = 1; // Start step number
                                                    @endphp
                                                    <li class="location active pb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>

                                                        <span class="d-none real-label">{{ $step++ }}.
                                                            {{ __('Location') }}</span>
                                                    </li>
                                                    @if ($staff_count_status !== 0)
                                                    <li class="staff pb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>

                                                        <span class="d-none real-label">{{ $step++ }}.
                                                            {{ __('Staffs') }}</span>
                                                    </li>
                                                    @endif
                                                    @if ($additionalServicesCount !== '00')
                                                    <li class="addservice pb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>

                                                        <span class="d-none real-label">{{ $step++ }}.
                                                            {{ __('Additional Services') }}</span>
                                                    </li>
                                                    @endif
                                                    <li class="datetime pb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>

                                                        <span class="d-none real-label">{{ $step++ }}.
                                                            {{ __('Date & Time') }}</span>
                                                    </li>
                                                    <li class="prinfo pb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>

                                                        <span class="d-none real-label">{{ $step++ }}.
                                                            {{ __('Personal Information') }}</span>
                                                    </li>
                                                    <li class="cart pb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>

                                                        <span class="d-none real-label">{{ $step++ }}.
                                                            {{ __('Cart') }}</span>
                                                    </li>
                                                    <li class="pay pb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>

                                                        <span class="d-none real-label">{{ $step++ }}.
                                                            {{ __('Payment') }}</span>
                                                    </li>
                                                    <li class="confime">
                                                        <div class="skeleton label-skeleton label-loader"></div>

                                                        <span class="d-none real-label">{{ $step++ }}.
                                                            {{ __('Confirmation') }}</span>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="status-report d-none">
                                                <h6 class="text-white fs-14 mb-2 pb-2">Bookings</h6>
                                                <p class="fs-10"> <span id="bookcomplete">0</span>% complete</p>
                                            </div>
                                            <div class="text-start d-flex align-items-center gap-2 d-none">
                                                @if (
                                                !empty(Auth::user()->userDetails->profile_image) &&
                                                file_exists(public_path('storage/profile/' . Auth::user()->userDetails->profile_image)))
                                                <img style="height: 30px; width: 30px"
                                                    src="{{ optional(Auth::user()->userDetails)->profile_image ? asset('storage/profile/' . Auth::user()->userDetails->profile_image) : asset('assets/img/profile-default.png') }}"
                                                    class="headerProfileImg" alt="user">
                                                @else
                                                <img style="height: 30px; width: 30px"
                                                    src="{{ asset('assets/img/profile-default.png') }}"
                                                    alt="Default Profile Image"
                                                    class="img-fluid rounded-circle profileImagePreview">
                                                @endif
                                                <p class="fs-10 text-white">
                                                    @if (Auth::check())
                                                    {{ Auth::user()->userDetails->first_name }}
                                                    {{ Auth::user()->userDetails->last_name}}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Booking Sidebar -->
                                <div class="col-lg-9">

                                    <!-- Location -->
                                    <fieldset class="booking-content" id="first-field">
                                        <div class="book-card">
                                            <div
                                                class="d-flex align-items-center justify-content-between flex-wrap booking-title">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <h6 class="fs-16 me-2 mb-2 d-none real-label">
                                                        {{ __('Select Location') }}
                                                    </h6>
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <span
                                                        class="badge badge-info-transparent mb-2 d-none real-label">{{ __('Total') }}
                                                        : {{ $branchesCount }}</span>
                                                </div>
                                            </div>
                                            <form id="branch-form">
                                                <div class="row g-3">
                                                    <div class="col-lg-4 col-md-6">
                                                        <div class="skeleton location-skeleton location-loader"></div>
                                                    </div>
                                                    <div class="col-lg-4 col-md-6">
                                                        <div class="skeleton location-skeleton location-loader"></div>
                                                    </div>
                                                    <div class="col-lg-4 col-md-6">
                                                        <div class="skeleton location-skeleton location-loader"></div>
                                                    </div>
                                                    @foreach ($branches as $branch)
                                                    <div class="col-lg-4 col-md-6 d-none real-label">
                                                        <div class="card location-card mb-0"
                                                            onclick="selectRadio('{{ $branch->id }}')">
                                                            <input type="radio" name="branch_id"
                                                                id="branch_{{ $branch->id }}"
                                                                value="{{ $branch->id }}" class="branch-radio"
                                                                hidden>
                                                            <div class="card-body p-3 text-center">
                                                                <span class="avatar avatar-lg mx-auto mb-2">
                                                                    <img src="{{ $branch->branch_image }}"
                                                                        alt="img">
                                                                </span>
                                                                <h6 class="mb-2 fw-medium">
                                                                    {{ $branch->branch_name }}
                                                                </h6>
                                                                <p
                                                                    class="d-flex align-items-center justify-content-center mb-2">
                                                                    <i
                                                                        class="ti ti-map-pin-check me-1"></i>{{ $branch->branch_address }}
                                                                </p>
                                                                <div
                                                                    class="d-flex align-items-center justify-content-between border-top pt-2">
                                                                    <p class="mb-0 d-flex align-items-center">
                                                                        <i
                                                                            class="ti ti-circle-check-filled text-danger fs-5 me-1"></i>
                                                                        {{ $branch->staff_count }}
                                                                        {{ intval($branch->staff_count) <= 1 ? 'Staff' : 'Staffs' }}
                                                                    </p>
                                                                    <p class="mb-0 d-flex align-items-center">
                                                                        <i
                                                                            class="ti ti-star-filled text-warning me-1"></i>{{ $averageRating ?? '0.0' }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </form>
                                        </div>
                                        <div class="booking-footer d-flex align-items-center justify-content-end">
                                            <div class="skeleton label-skeleton label-loader"></div>
                                            <a href="#!"
                                                id="{{ $staff_count_status == 0 && $additionalServicesCount == '00'
                                                        ? 'branch-btn-new-both'
                                                        : ($staff_count_status == 0
                                                            ? 'branch-btn-staff'
                                                            : ($additionalServicesCount == '00'
                                                                ? 'branch-btn-additional'
                                                                : 'branch-btn')) }}"
                                                class="btn btn-sm btn-dark d-inline-flex align-items-center d-none real-label">{{ __('Next') }}<i
                                                    class="ti ti-arrow-right ms-1"></i></a>
                                        </div>
                                    </fieldset>
                                    <!-- /Location -->

                                    @if ($staff_count_status !== 0)
                                    <!-- Staffs -->
                                    <fieldset class="booking-content" id="second-field">
                                        <div class="book-card">
                                            <div
                                                class="d-flex align-items-center justify-content-between flex-wrap booking-title">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h6 class="fs-16 me-2 mb-2">{{ __('select_staffs') }}</h6>
                                                    <span class="badge badge-info-transparent mb-2"
                                                        id="staff-count">{{ __('Total') }}: 00</span>
                                                </div>
                                            </div>
                                            <form id="staff-form">
                                                <div class="row g-3" id="staff-container">

                                                </div>
                                            </form>
                                        </div>
                                        <div
                                            class="booking-footer d-flex align-items-center justify-content-between flex-wrap">
                                            <div
                                                class="form-check d-inline-flex align-items-center ps-0 me-3 mt-3">
                                                <input class="form-check-input ms-0 mt-0" name="service1"
                                                    type="checkbox" id="service" hidden>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <a href="#!"
                                                    id="{{ $additionalServicesCount === '00' ? 'staff-prev-new' : 'staff-prev' }}"
                                                    class="btn btn-sm btn-light d-inline-flex align-items-center me-2"><i
                                                        class="ti ti-arrow-left me-1"></i>{{ __('Prev') }}</a>
                                                <a href="#!"
                                                    id="{{ $additionalServicesCount === '00' ? 'staff-btn-new' : 'staff-btn' }}"
                                                    class="btn btn-sm btn-dark d-inline-flex align-items-center">{{ __('Next') }}<i
                                                        class="ti ti-arrow-right ms-1"></i></a>
                                            </div>
                                        </div>
                                    </fieldset>
                                    <!-- /Staffs -->
                                    @endif

                                    @if ($additionalServicesCount !== '00')
                                    <!-- Additional Service -->
                                    <fieldset class="booking-content" id="third-field">
                                        <div class="book-card">
                                            <div
                                                class="d-flex align-items-center justify-content-between flex-wrap booking-title">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h6 class="fs-16 me-2 mb-2">
                                                        {{ __('Select Additional Service') }}
                                                    </h6>
                                                    <span class="badge badge-info-transparent mb-2">Total :
                                                        {{ $additionalServicesCount }}</span>
                                                </div>
                                            </div>
                                            <form id="addService-form">
                                                <div class="row g-3">
                                                    @if ($additionalServices->isEmpty())
                                                    <div class="col-12">
                                                        <p class="text-center fs-12 mt-5 fw-bold text-dark">No
                                                            additional services found at the moment. You can
                                                            Continue further.</p>
                                                    </div>
                                                    @else
                                                    @foreach ($additionalServices as $additionalService)
                                                    <div class="col-md-6">
                                                        <div class="select-item d-flex align-items-center justify-content-between flex-wrap border p-2 pb-0 mb-0"
                                                            onclick="selectRadioAddService('{{ $additionalService->id }}')">
                                                            <input type="checkbox"
                                                                name="additionalService_id[]"
                                                                id="additionalService_{{ $additionalService->id }}"
                                                                value="{{ $additionalService->id }}"
                                                                class="additionalService-radio" hidden>
                                                            <div class="d-flex align-items-center pb-2">
                                                                <span class="avatar avatar-lg">
                                                                    <img src="{{ $additionalService->image }}"
                                                                        alt="img" class="br-5">
                                                                </span>
                                                                <div class="ms-2">
                                                                    <h6 class="mb-1 fs-12 fw-medium">
                                                                        {{ $additionalService->name }}
                                                                    </h6>
                                                                    <p class="fs-10"><span
                                                                            class="fs-12 text-gray-9 fw-medium">{{ $currecy_details->symbol }}{{ $additionalService->price }}</span>
                                                                        /
                                                                        {{ $additionalService->duration }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center pb-2">
                                                                <p
                                                                    class="mb-0 d-flex align-items-center fs-12 me-2">
                                                                    <i
                                                                        class="ti ti-star-filled text-warning me-1"></i>4.9
                                                                </p>
                                                                <a href="#!"
                                                                    class="btn btn-light btn-sm btn-addon d-inline-flex align-items-center"><i
                                                                        class="feather-plus-circle me-1"></i>{{ __('Add') }}</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                    @endif
                                                </div>
                                            </form>
                                        </div>
                                        <div class="booking-footer d-flex align-items-center justify-content-end">
                                            <div class="d-flex align-items-center">
                                                <a href="#!"
                                                    id="{{ $staff_count_status == 0 && $additionalServicesCount == '00'
                                                                ? 'addService-prev-new-both'
                                                                : ($staff_count_status == 0
                                                                    ? 'addService-prev-staff'
                                                                    : ($additionalServicesCount == '00'
                                                                        ? 'addService-prev-additional'
                                                                        : 'addService-prev')) }}"
                                                    class="btn btn-sm btn-light d-inline-flex align-items-center me-2">
                                                    <i class="ti ti-arrow-left me-1"></i>{{ __('Prev') }}
                                                </a>

                                                <a href="#!" id="addService-btn"
                                                    class="btn btn-sm btn-dark d-inline-flex align-items-center">{{ __('Next') }}<i
                                                        class="ti ti-arrow-right ms-1"></i></a>
                                            </div>
                                        </div>
                                    </fieldset>
                                    <!-- /Additional Service -->
                                    @endif

                                    <!-- Date & Time -->
                                    <fieldset class="booking-content" id="fourth-field">
                                        <div class="book-card">
                                            <div
                                                class="d-flex align-items-center justify-content-between flex-wrap booking-title">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h6 class="fs-16 me-2 mb-2">{{ __('Select Date & Time') }}</h6>
                                                </div>
                                            </div>
                                            <form id="slot-form">
                                                <div class="row g-3">
                                                    <div class="col-md-5">
                                                        <h6 class="fs-13 fw-medium mb-2">{{ __('Select date') }}</h6>
                                                        <div class="card border mb-0">
                                                            <div class="card-body p-3">
                                                                <input type="hidden" name="service_id"
                                                                    id="service_id" value="{{ $service->id }}">
                                                                <input type="hidden" name="subcategory_id"
                                                                    id="subcategory_id"
                                                                    value="{{ $service->source_subcategory }}">
                                                                <input type="hidden" name="category_id"
                                                                    id="category_id"
                                                                    value="{{ $service->source_category }}">
                                                                <input type="hidden" name="booking_date"
                                                                    id="selected_date" value="">
                                                                <input type="hidden" name="from_time" id="from_time"
                                                                    value="">
                                                                <input type="hidden" name="to_time" id="to_time"
                                                                    value="">
                                                                <div class="bookingDatepic"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="time_staus" id="time_staus">
                                                    <div class="col-md-7 time-section">
                                                        <h6 class="fs-13 fw-medium mb-2">{{ __('Select time') }}</h6>
                                                        <div class="slotLoader-skaliaton">
                                                        </div>
                                                        <div class="row g-2 text-center pt-4 fw-bold text-primary"
                                                            id="slot-inut">
                                                            <p>{{ __('No slots available at this moment') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="booking-footer d-flex align-items-center justify-content-end">
                                            <div class="d-flex align-items-center">
                                                <a href="#!"
                                                    id="{{ $staff_count_status == 0 && $additionalServicesCount == '00'
                                                            ? 'slot-prev-new-both'
                                                            : ($staff_count_status == 0
                                                                ? 'slot-prev-staff'
                                                                : ($additionalServicesCount == '00'
                                                                    ? 'slot-prev-additional'
                                                                    : 'slot-prev')) }}"
                                                    class="btn btn-sm btn-light d-inline-flex align-items-center me-2">
                                                    <i class="ti ti-arrow-left me-1"></i>{{ __('Prev') }}
                                                </a>
                                                <a href="#!"
                                                    id="{{ $additionalServicesCount === '00' ? 'slot-btn' : 'slot-btn' }}"
                                                    class="btn btn-sm btn-dark d-inline-flex align-items-center">{{ __('Next') }}<i
                                                        class="ti ti-arrow-right ms-1"></i></a>
                                            </div>
                                        </div>
                                    </fieldset>
                                    <!-- /Date & Time -->

                                    <!-- Personal Information -->
                                    <fieldset class="booking-content" id="fifth-field">
                                        <div class="book-card">
                                            <div
                                                class="d-flex align-items-center justify-content-between flex-wrap booking-title">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h6 class="fs-16 me-2 mb-2">{{ __('Add Personal Information') }}
                                                    </h6>
                                                </div>
                                            </div>
                                            <form id="prinfo-form" data-is_required_text="{{ __('is_required') }}">
                                                <div class="row g-3">
                                                    <div class="col-md-5">
                                                        <div class="cart-info-wrap">
                                                            <div
                                                                class="mb-2 d-flex align-items-center justify-content-between">
                                                                <div>
                                                                    <h6 class="fw-medium fw-bold fs-16">
                                                                        {{ $service->source_name }}
                                                                    </h6>
                                                                    <p class="fs-10"> <span
                                                                            class="fw-bold text-dark fs-12">{{ __('Price Type') }}:</span>
                                                                        {{ \Illuminate\Support\Str::ucfirst($service->price_type) }}
                                                                    </p>
                                                                    <p class="fs-10"> <span
                                                                            class="fw-bold text-dark fs-12">{{ __('Duration') }}:</span>
                                                                        {{ $service->duration }}/{{ $serviceDuration }}
                                                                    </p>
                                                                </div>
                                                                <h6 class="fs-12 fw-medium">
                                                                    {{ $currecy_details->symbol }}{{ $service->source_price }}
                                                                </h6>
                                                            </div>
                                                            <div
                                                                class="border-top pt-3 mt-3 add-section location-section">
                                                                <h6 class="add mb-2">{{ __('Additional Service') }}
                                                                </h6>
                                                                <div class="d-flex align-items-center mb-3">
                                                                    <div class="ms-2">
                                                                        <h6 class="fw-medium mb-1 service-names"></h6>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="border-top pt-3 mt-3 location-section">
                                                                <h6 class="mb-2">{{ __('Location') }}</h6>
                                                                <div class="d-flex align-items-center mb-3">
                                                                    <span class="avatar avatar-lg">
                                                                        <img src="" alt="Location Image">
                                                                    </span>
                                                                    <div class="ms-2">
                                                                        <h6 class="fw-medium mb-1 branch-name"></h6>
                                                                        <p class="branch-email"></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="border-top pt-3 staff-section">
                                                                <h6 class="mb-2">{{ __('Staff') }}</h6>
                                                                <div class="d-flex align-items-center mb-3">
                                                                    <span class="avatar avatar-lg">
                                                                        <img src="" alt="">
                                                                    </span>
                                                                    <div class="ms-2">
                                                                        <h6 class="fw-medium mb-1 staff-name"></h6>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="border-top pt-3 mt-3">
                                                                <h6 class="mb-2">{{ __('Date') }}</h6>
                                                                <p
                                                                    class="mb-2 text-gray-9 fw-medium d-flex align-items-center">
                                                                    <i class="feather-calendar me-2"></i><span
                                                                        class="slot_day"></span>
                                                                </p>
                                                                <div class="slot-section">
                                                                    <h6 class="time mb-2">{{ __('Time') }}</h6>
                                                                    <p
                                                                        class="text-gray-9 fw-medium d-flex align-items-center">
                                                                        <i class="feather-clock me-2"></i> <span
                                                                            class="slot_time"></span>
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-7">
                                                        <div class="row g-2">
                                                            <div class="col-md-6">
                                                                <div>
                                                                    <label
                                                                        class="form-label fs-12">{{ __('First Name') }}<span
                                                                            class="text-danger"> *</span></label>
                                                                    <input type="text" name="first_name"
                                                                        id="booking_first_name"
                                                                        class="form-control print-info">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div>
                                                                    <label
                                                                        class="form-label fs-12">{{ __('Last Name') }}<span
                                                                            class="text-danger"> *</span></label>
                                                                    <input type="text" name="last_name"
                                                                        id="booking_last_name"
                                                                        class="form-control print-info">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div>
                                                                    <label
                                                                        class="form-label fs-12">{{ __('Email') }}<span
                                                                            class="text-danger"> *</span></label>
                                                                    <input type="email" name="email"
                                                                        id="booking_email"
                                                                        class="form-control print-info">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div>
                                                                    <label
                                                                        class="form-label fs-12">{{ __('Phone Number') }}<span
                                                                            class="text-danger"> *</span></label>
                                                                    <input type="text" name="phone_number"
                                                                        id="phone_number"
                                                                        class="form-control print-info">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div>
                                                                    <label
                                                                        class="form-label fs-12">{{ __('Street Address') }}<span
                                                                            class="text-danger"> *</span></label>
                                                                    <input type="text" name="address"
                                                                        id="address"
                                                                        class="form-control print-info">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div>
                                                                    <label
                                                                        class="form-label fs-12">{{ __('City') }}<span
                                                                            class="text-danger"> *</span></label>
                                                                    <input type="text" name="city"
                                                                        id="city"
                                                                        class="form-control print-info">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div>
                                                                    <label
                                                                        class="form-label fs-12">{{ __('State') }}<span
                                                                            class="text-danger"> *</span></label>
                                                                    <input type="text" name="state"
                                                                        id="state"
                                                                        class="form-control print-info">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div>
                                                                    <label
                                                                        class="form-label fs-12">{{ __('Postal Code') }}<span
                                                                            class="text-danger"> *</span></label>
                                                                    <input type="text" name="postal"
                                                                        id="postal"
                                                                        class="form-control print-info">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div>
                                                                    <label
                                                                        class="form-label fs-12">{{ __('Add booking notes') }}</label>
                                                                    <textarea class="form-control" name="note" id="note" rows="4"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="border-top pt-3 mt-3">
                                                            <h6 class="fs-13 fw-medium mb-1">
                                                                {{ __('Cancellation policy') }}
                                                            </h6>
                                                            <p>{{ __('Cancel for free anytime in advance, otherwise you will be charged 100% of the service price for not showing up.') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="booking-footer d-flex align-items-center justify-content-end">
                                            <div class="d-flex align-items-center">
                                                <a href="#!" id="prinfo-prev"
                                                    class="btn btn-sm btn-light d-inline-flex align-items-center me-2"><i
                                                        class="ti ti-arrow-left me-1"></i>Prev</a>
                                                <a href="#!" id="prinfo-btn"
                                                    class="btn btn-sm btn-dark d-inline-flex align-items-center">Next<i
                                                        class="ti ti-arrow-right ms-1"></i></a>
                                            </div>
                                        </div>
                                    </fieldset>
                                    <!-- /Personal Information -->

                                    <!-- Cart -->
                                    <fieldset class="booking-content" id="sixth-field">
                                        <div class="book-card">
                                            <div
                                                class="d-flex align-items-center justify-content-between flex-wrap booking-title">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h6 class="fs-16 me-2 mb-2">{{ __('Cart') }}</h6>
                                                </div>
                                                <div class="d-flex align-items-center flex-wrap mb-2">
                                                    <div class="dropdown mb-2">
                                                        <a href="#!"
                                                            class="bg-light-500 d-inline-flex align-items-center"
                                                            data-bs-toggle="dropdown">
                                                            <i
                                                                class="ti ti-shopping-cart me-1"></i>{{ __('Cart') }}<span
                                                                class="bg-primary num-count ms-1">1</span>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-sm p-3">
                                                            <h6 class="fs-13 mb-3">{{ __('Added In Cart') }} (01)
                                                            </h6>
                                                            <div
                                                                class="d-flex align-items-center p-2 bg-light rounded mb-3">
                                                                <span class="avatar avatar-lg">
                                                                    <img src="{{ $firstImage }}" alt="img">
                                                                </span>
                                                                <div class="ms-2">
                                                                    <h6 class="mb-1">{{ $service->source_name }}
                                                                    </h6>
                                                                    <p class="fs-12"><i
                                                                            class="ti ti-star-filled text-warning me-1"></i><span
                                                                            class="text-gray-9">{{ $averageRating }}</span>
                                                                        ({{ $ratingCount }} {{ __('reviews') }})
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
                                                                <div>
                                                                    <h6 class="fw-medium">{{ __('Total') }}</h6>
                                                                </div>
                                                                <h6 class="fw-medium">
                                                                    {{ $currecy_details->symbol }}{{ $service->source_price }}
                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6 d-flex">
                                                    <div class="card flex-fill">
                                                        <div
                                                            class="card-body p-3 d-flex justify-content-between flex-column">
                                                            <div>
                                                                <div
                                                                    class="d-flex align-items-center p-3 bg-light-400 rounded mb-2">
                                                                    <span class="avatar avatar-lg">
                                                                        <img src="{{ $firstImage }}"
                                                                            alt="img">
                                                                    </span>
                                                                    <div class="ms-2">
                                                                        <h6 class="fs-14 fw-medium mb-1">
                                                                            {{ $service->source_name }}
                                                                        </h6>
                                                                        <p><span
                                                                                class="fw-bold text-dark fs-12">{{ __('Price Type') }}:</span>
                                                                            {{ \Illuminate\Support\Str::ucfirst($service->price_type) }}
                                                                        </p>
                                                                        <p><span
                                                                                class="fw-bold text-dark fs-12">{{ __('Duration') }}:</span>
                                                                            {{ $service->duration }}/{{ $serviceDuration }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 add-section">
                                                                    <h6 class="fw-medium mb-1">
                                                                        {{ __('Additional Service') }}
                                                                    </h6>
                                                                    <p class="service-names"></p>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <h6 class="fw-medium mb-1">{{ __('Location') }}
                                                                    </h6>
                                                                    <p class="cart-location-name"></p>
                                                                </div>
                                                                <div class="mb-2 staff-section">
                                                                    <h6 class="fw-medium mb-1">{{ __('Staff') }}
                                                                    </h6>
                                                                    <p class="cart-staff-name"></p>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <h6 class="fw-medium">{{ __('Date') }}</h6>
                                                                    <p class="slot_day"></p>
                                                                    <div class="slot-section">
                                                                        <h6 class="time fw-medium mb-1">
                                                                            {{ __('Time') }}
                                                                        </h6>
                                                                        <p class="slot_time"></p>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-0">
                                                                    <h6 class="fw-medium mb-1">{{ __('Amount') }}
                                                                    </h6>
                                                                    <span
                                                                        class="badge badge-dark">{{ $currecy_details->symbol }}{{ $service->source_price }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="text-center border-top pt-3 mt-3">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="booking-footer d-flex align-items-center justify-content-end">
											<div class="d-flex align-items-center gap-2">

												{{-- Prev --}}
												<a href="#!"
												id="cart-prev"
												class="btn btn-sm btn-light prev_btn d-inline-flex align-items-center">
													<i class="ti ti-arrow-left me-1"></i>{{ __('Prev') }}
												</a>

												{{-- PAYMENTMODE = 2 (NO PAYMENT ONLY) --}}
												@if($paymentmodeStatus == 2)
													<a href="#!"
													id="cart-place-order"
													class="btn btn-sm btn-dark d-inline-flex align-items-center">
														{{ __('Place Order') }}
													</a>

												{{-- PAYMENTMODE = 3 (NO PAYMENT + ONLINE) --}}
												@elseif($paymentmodeStatus == 3)
													<a href="#!"
													id="cart-place-order"
													class="btn btn-sm btn-outline-dark d-inline-flex align-items-center">
														{{ __('Place Order') }}
													</a>

													<a href="#!"
													id="cart-go-payment"
													class="btn btn-sm btn-dark d-inline-flex align-items-center">
														{{ __('Continue to Payment') }}
														<i class="ti ti-arrow-right ms-1"></i>
													</a>

												{{-- PAYMENTMODE = 1 (ONLINE ONLY) --}}
												@else
													<a href="#!"
													id="cart-go-payment"
													class="btn btn-sm btn-dark d-inline-flex align-items-center">
														{{ __('Next') }}
														<i class="ti ti-arrow-right ms-1"></i>
													</a>
												@endif

											</div>
										</div>
                                    </fieldset>
                                    <!-- /Cart -->

                                    <!-- Payment Method -->
                                    <fieldset class="booking-content" id="saventh-field">
                                        <div class="book-card">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap booking-title">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h6 class="fs-16 me-2 mb-2">{{ __('Payment Method') }}</h6>
                                                </div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <a href="#!" id="back-cart"
                                                        class="btn btn-sm btn-secondary d-inline-flex align-items-center prev_btn mb-2"><i
                                                            class="ti ti-caret-left-filled me-1"></i>{{ __('back_to_cart') }}</a>
                                                </div>
                                            </div>
                                            <form id="payment-form">
                                                <div class="row g-3">
                                                    {{-- Add style for max-height and overflow here --}}
                                                    <div class="col-md-6 payment-radios" style="max-height: 450px; overflow-y: auto;">
                                                        <h6 class="fs-13 mb-3">{{ __('Payment Types') }}</h6>
                                                        @if (isset($paymentInfo['stripe_status']) && $paymentInfo['stripe_status'] == 1)
                                                        <div class="payment-item d-flex align-items-center justify-content-between mb-2"
                                                            id="stripePayment">
                                                            <div class="form-check d-flex align-items-center ps-0">
                                                                <input
                                                                    class="form-check-input ms-0 mt-0 payment-radio"
                                                                    name="payment_type" type="radio"
                                                                    id="stripe_status" value="stripe">
                                                                <label class="form-check-label ms-2"
                                                                    for="stripe_status">{{ __('Stripe') }}</label>
                                                            </div>
                                                            <div>
                                                                <img src="{{ asset('front/img/icons/payment1.svg') }}"
                                                                    alt="payment">
                                                            </div>
                                                        </div>
                                                        @endif
                                                        @if (isset($paymentInfo['paypal_status']) && $paymentInfo['paypal_status'] == 1)
                                                        <div class="payment-item d-flex align-items-center justify-content-between mb-2"
                                                            id="paypalPayment">
                                                            <div class="form-check d-flex align-items-center ps-0">
                                                                <input
                                                                    class="form-check-input ms-0 mt-0 payment-radio"
                                                                    name="payment_type" type="radio"
                                                                    id="paypal_status" value="paypal">
                                                                <label class="form-check-label ms-2"
                                                                    for="paypal_status">{{ __('Paypal') }}</label>
                                                            </div>
                                                            <div>
                                                                <img src="{{ asset('front/img/icons/payment2.svg') }}"
                                                                    alt="payment">
                                                            </div>
                                                        </div>
                                                        @endif
                                                        @if (isset($paymentInfo['mollie_status']) && $paymentInfo['mollie_status'] == 1)
                                                        <div class="payment-item d-flex align-items-center justify-content-between mb-2"
                                                            id="molliePayment">
                                                            <div class="form-check d-flex align-items-center ps-0">
                                                                <input
                                                                    class="form-check-input ms-0 mt-0 payment-radio"
                                                                    name="payment_type" type="radio"
                                                                    id="mollie_status" value="mollie">
                                                                <label class="form-check-label ms-2"
                                                                    for="paypal_status">{{ __('Mollie') }}</label>
                                                            </div>
                                                            <div>
                                                                <img src="{{ asset('front/img/icons/mollie.png') }}"
                                                                    style="height: 40px; width: 50px"
                                                                    alt="payment">
                                                            </div>
                                                        </div>
                                                        @endif
                                                        @if (module_view_exists(view: 'paymentgateway::index') && $PaymentGatewayStatus == 1)
                                                        @include('paymentgateway::index')
                                                        @endif
                                                        @if (isset($paymentInfo['cod_status']) && $paymentInfo['cod_status'] == 1)
                                                        <div class="payment-item d-flex align-items-center justify-content-between mb-2"
                                                            id="codPayment">
                                                            <div class="form-check d-flex align-items-center ps-0">
                                                                <input
                                                                    class="form-check-input ms-0 mt-0 payment-radio"
                                                                    name="payment_type" type="radio"
                                                                    id="cod_status" value="cod">
                                                                <label class="form-check-label ms-2"
                                                                    for="cod_status">{{ __('Cash on Delivery (COD)') }}</label>
                                                            </div>
                                                            <div>
                                                                <img src="{{ asset('front/img/icons/cod.png') }}"
                                                                    style="height: 40px; width: 30px"
                                                                    alt="payment">
                                                            </div>
                                                        </div>
                                                        @endif
                                                        @if ($bankStatus == 1)
                                                        <div class="payment-item d-flex align-items-center justify-content-between mb-2"
                                                            id="bank">
                                                            <div class="form-check d-flex align-items-center ps-0">
                                                                <input
                                                                    class="form-check-input ms-0 mt-0 payment-radio"
                                                                    name="payment_type" type="radio"
                                                                    id="bank_status" value="bank">
                                                                <label class="form-check-label ms-2"
                                                                    for="bank_status">{{ __('Bank Transfer') }}</label>
                                                            </div>
                                                            <div>
                                                                <img src="{{ asset('assets/img/payment-gateway/payment-gateway-14.svg') }}"
                                                                    style="height: 40px; width: 50px"
                                                                    alt="bank">
                                                            </div>
                                                        </div>
                                                        @endif
                                                        @if (isset($paymentInfo['wallet_status']) && $paymentInfo['wallet_status'] == 1)
                                                        <div class="payment-item d-flex align-items-center justify-content-between mb-2"
                                                            id="walletPayment">
                                                            <div class="form-check d-flex align-items-center ps-0">
                                                                <input
                                                                    class="form-check-input ms-0 mt-0 payment-radio"
                                                                    name="payment_type" type="radio"
                                                                    id="wallet_status" value="wallet">
                                                                <label class="form-check-label ms-2"
                                                                    for="wallet_status">{{ __('Wallet Amount') }}</label>
                                                            </div>
                                                            <div>
                                                                <img src="{{ asset('front/img/icons/digital-wallet.jpg') }}"
                                                                    style="height: 40px; width: 50px"
                                                                    alt="payment">
                                                            </div>
                                                        </div>
                                                        <div id="wallet-meg"
                                                            data-wallet_balance="{{ __('wallet_balance') }}"
                                                            data-your_wallet_balance_is_sufficient="{{ __('your_wallet_balance_is_sufficient') }}"
                                                            data-insufficient_wallet_balance="{{ __('insufficient_wallet_balance') }}"
                                                            data-available_balance="{{ __('available_balance') }}"
                                                            class="fw-bold">
                                                        </div>
                                                        @endif
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="card total-card">
                                                            <div
                                                                class="card-body p-3 d-flex justify-content-between flex-column">
                                                                <div>
                                                                    <div
                                                                        class="mb-2 d-flex align-items-center justify-content-between">
                                                                        <div>
                                                                            <h6 class="fw-bold fs-16">
                                                                                {{ $service->source_name }}
                                                                            </h6>
                                                                            <p class="fs-12"><span
                                                                                    class="fw-bold text-dark fs-12">{{ __('Price Type') }}:</span>
                                                                                {{ \Illuminate\Support\Str::ucfirst($service->price_type) }}
                                                                            </p>
                                                                            <p class="fs-12"><span
                                                                                    class="fw-bold text-dark fs-12">{{ __('Duration') }}:</span>
                                                                                {{ $service->duration }}/{{ $serviceDuration }}
                                                                            </p>
                                                                        </div>
                                                                        <h6 class="fs-16 fw-medium">
                                                                            {{ $currecy_details->symbol }}{{ $service->source_price }}
                                                                        </h6>
                                                                    </div>
                                                                </div>
                                                                @if ($couponModuleStatus === 1 && View::exists('coupon::booking.coupon'))
                                                                @include('coupon::booking.coupon')
                                                                @endif
                                                                <div>
                                                                    <div id="payout"
                                                                        data-sub_total="{{ __('sub_total') }}"
                                                                        data-additional_services_total="{{ __('additional_services_total') }}"
                                                                        data-tax="{{ __('tax') }}"
                                                                        data-total="{{ __('total') }}">

                                                                    </div>
                                                                    <input type="hidden" class="sub_amount"
                                                                        name="sub_amount" id="sub_amount">
                                                                    <input type="hidden" class="addService_amount"
                                                                        name="addService_amount"
                                                                        id="addService_amount">
                                                                    <input type="hidden" class="tax_amount"
                                                                        name="tax_amount" id="tax_amount">
                                                                    <input type="hidden" class="total_amount"
                                                                        name="total_amount" id="total_amount">
                                                                    <button id="pay-btn"
                                                                        class="btn btn-light w-100 pay-btn">{{ __('Pay') }}</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </fieldset>
                                    <!-- /Payment Method -->

                                    <!-- Confirmation -->
                                    <fieldset class="booking-content" id="eight-field">
                                        <div class="book-card">
                                            <h6 class="fs-16 me-2 mb-3">{{ __('Payment Method') }}</h6>
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="fs-14 fw-medium mb-3">
                                                        {{ __('your_booking_is_successful_on') }} <span
                                                            class="final-time"></span>
                                                    </h6>
                                                    <div class="card shadow-none mb-0">
                                                        <div class="card-body p-3">
                                                            <div
                                                                class="d-flex align-items-center justify-content-between flex-wrap p-2 bg-light-300 rounded mb-3">
                                                                <div class="d-flex align-items-center pb-2">
                                                                    <span class="avatar avatar-xl flex-shrink-0">
                                                                        <img src="{{ $firstImage }}"
                                                                            alt="img">
                                                                    </span>
                                                                    <div class="ms-2">
                                                                        <h6 class="mb-1">
                                                                            {{ $service->source_name }}
                                                                        </h6>
                                                                    </div>
                                                                </div>
                                                                <span class="badge badge-success"><i
                                                                        class="ti ti-circle-check-filled me-1"></i>{{ __('confirmed') }}</span>
                                                            </div>
                                                            <div
                                                                class="mb-2 d-flex align-items-center justify-content-between">
                                                                <div>
                                                                    <h6 class="fw-medium">{{ $service->source_name }}
                                                                    </h6>
                                                                    <p class="fs-10"><span
                                                                            class="fw-bold text-dark fs-12">{{ __('Price Type') }}:</span>
                                                                        {{ \Illuminate\Support\Str::ucfirst($service->price_type) }}
                                                                    </p>
                                                                    <p class="fs-10"><span
                                                                            class="fw-bold text-dark fs-12">{{ __('Duration') }}:</span>
                                                                        {{ $service->duration }}/{{ $serviceDuration }}
                                                                    </p>
                                                                </div>
                                                                <h6 class="fs-12 fw-medium">
                                                                    {{ $currecy_details->symbol }}{{ $service->source_price }}
                                                                </h6>
                                                            </div>
                                                            <div class="border-top pt-2">
                                                                <div
                                                                    class="mb-2 d-flex align-items-center justify-content-between">
                                                                    <h6 class="fw-medium">{{ __('sub_total') }}</h6>
                                                                    <p>{{ $currecy_details->symbol }}<span
                                                                            class="final-sub"></span> </p>
                                                                </div>
                                                                <div
                                                                    class="mb-2 d-flex align-items-center justify-content-between">
                                                                    <h6 class="fw-medium">{{ __('tax') }}</h6>
                                                                    <p>{{ $currecy_details->symbol }}<span
                                                                            class="final-tax"></span></p>
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="border-top pt-2 d-flex align-items-center justify-content-between">
                                                                <h6 class="fs-14">{{ __('total') }}</h6>
                                                                <h6 class="fs-14">
                                                                    {{ $currecy_details->symbol }}<span
                                                                        class="final-total"></span>
                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="d-flex align-items-center justify-content-center flex-wrap">
                                                        <a href="{{ route('user.dashboard') }}"
                                                            class="btn btn-sm btn-primary d-inline-flex align-items-center mt-3"><i
                                                                class="ti ti-circle-plus me-1"></i>{{ __('go_to_dashboard') }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                    <!-- /Confirmation -->

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Booking -->

        </div>
    </div>
</div>
<!-- /Page Wrapper -->
@if (module_view_exists('paymentgateway::model') && $PaymentGatewayStatus == 1)
@include('paymentgateway::model')
@endif

@include('user.booking.booking_otp_modal')

<div class="modal fade" id="bankTransferModal" tabindex="-1" aria-labelledby="bankTransferModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="bankTransferModalLabel">Bank Transfer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <!-- Bank Name -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Bank Name:</label>
                    <div class="form-control-plaintext">{{ $bankInfo['bank_name'] }}</div>
                </div>

                <!-- Account Number -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Account Number:</label>
                    <div class="form-control-plaintext">{{ $bankInfo['account_number'] }}</div>
                </div>

                <!-- Branch Code -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Branch Code:</label>
                    <div class="form-control-plaintext">{{ $bankInfo['branch_code'] }}</div>
                </div>

                <!-- Upload Payment Receipt -->
                <form id="bankTransfer-form" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="payment_receipt" class="form-label">Upload Payment Receipt</label>
                        <input type="file" class="form-control border border-2" name="payment_receipt"
                            id="payment_receipt" accept="image/*,application/pdf" required>
                    </div>
                </form>

                <!-- Optional Info Text -->
                <p class="text-muted small mt-2 mb-0">
                    Please upload a valid image or PDF file as proof of payment. Ensure the transfer is completed before
                    submitting.
                </p>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">← Cancel</button>
                <button type="submit" class="btn btn-primary" form="bankTransfer-form" id="submit-bank-payment">
                    Pay Now
                </button>
            </div>

        </div>
    </div>
</div>
<!-- Cursor -->
<div class="xb-cursor tx-js-cursor">
    <div class="xb-cursor-wrapper">
        <div class="xb-cursor--follower xb-js-follower"></div>
    </div>
</div>
<!-- /Cursor -->
@endsection
