@extends('provider.provider')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('Calendar') }}</h3>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 col-md-8">
                <div class="card bg-white">
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="calendarModal" aria-labelledby="calendarModalLabel">
    <div class="offcanvas-header">
        <h5 id="calendarModalLabel">Booking Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="d-flex align-items-center justify-content-between w-100 border-top p-3">
            <div class="d-flex align-items-center gap-3">
                <span class="p-2 border border-light rounded-circle" id="color">
                    <span class="visually-hidden">Status</span></span>
                <h5 class="mb-0" id="appointment-status"></h5>
            </div>
        </div>
        <div class="border-top border-bottom">
            <div class="d-flex text-center date-time">
                <div class="col-6 py-3"><i>On</i> <strong id="appointment-date"></strong></div>
                <div class="col-6 py-3"><i>At</i> <strong id="appointment-time"></strong></div>
            </div>
        </div>
        <div class="py-3">
            <div class="d-flex align-items-start gap-3 mb-2">
                <img src="" alt="avatar" class="img-fluid avatar avatar-60 rounded-pill">
                <div class="flex-grow-1">
                    <div class="gap-2">
                        <strong id="client-name"></strong>
                    </div>
                </div>
            </div>
            <div class="row">
                <label class="col-3"><i>Phone:</i></label><strong class="col" id="client-phone"></strong>
            </div>
            <div class="row mb-2">
                <label class="col-3"><i>E-mail:</i></label><strong class="col" id="client-email"></strong>
            </div>
            <div class="row mb-2">
                <label class="col-3"><i>Staff:</i></label><strong class="col" id="client-staff"></strong>
            </div>
            <div class="row mb-2">
                <label class="col-3"><i>Servcie:</i></label><strong class="col" id="service-title"></strong>
            </div>
            <div class="row mb-2">
                <label class="col-3"><i>Location:</i></label><strong class="col" id="client-location"></strong>
            </div>
            <div class="row mb-2">
                <label class="col-3"><i>Amount:</i></label><strong class="col" id="total">-</strong>
            </div>
        </div>

    </div>
    <a id="custom-link" href="#" class="btn btn-primary mt-3" target="_blank" style="display: none;">View Lead</a>
    <div class="card-footer">
        <button class="btn btn-danger w-100 cancelbooking" data-bs-toggle="modal" data-bs-target="#cancel_appointment" data-id="">Cancel Booking</button>
    </div>
</div>
<!-- Right-Side Modal -->

<div class="modal fade custom-modal" id="cancel_appointment">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                <h5 class="modal-title">{{ __('Cancel Booking') }}</h5>
                <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <form>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this booking?</p>
                </div>
                <div class="modal-footer">
                    <div class="acc-submit">
                        <a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Dismiss') }} </a>
                        <button class="btn btn-dark bookingid" id="cancelbooking" data-id="" data-type="3" type="submit" data-yes_cancel="{{ __('Yes, Cancel') }}">{{ __('Yes, Cancel') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="offcanvas offcanvas-end" tabindex="-1" id="providerAppointmentModal" aria-labelledby="appointmentModalLabel">
    <div class="offcanvas-header">
        <h5 id="appointmentModalLabel">Appointment Booking</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="providerCalenderBookingForm">

            <input type="hidden" name="booking_date" id="selected-date">
            <input type="hidden" name="booking_time" id="selected-time">

            <div class="d-flex">
                <p id="selected-date-text">Date: </p>
                <p id="selected-time-text">Time: </p>
            </div>

            <div class="customer">
                <div class="mb-3">
                    <label for="user_id" class="fw-bold">Customer *</label>
                    <select class="form-control select2 fw-bold" name="user_id" id="user_id" onchange="fetchProviderCustomer()">
                        <option value="">Select Customer</option>
                        @forelse ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @empty
                        <option value="">No customer found</option>
                        @endforelse
                    </select>
                    <span class="invalid-feedback" id="user_id_error"></span>
                </div>
                <div id="customerDetails" class="mt-3"></div>
            </div>

            <div class="service">
                <div class="mb-3">
                    <label for="user_id" class="fw-bold">Services *</label>
                    <select class="form-control select2 fw-bold" name="service_id" id="service_id" onchange="fetchServices()">
                        <option value="">Select Service</option>
                        @forelse ($services as $service)
                        <option value="{{ $service->id }}">{{ $service->source_name }}</option>
                        @empty
                        <option value="">No Service found</option>
                        @endforelse
                    </select>
                    <span class="invalid-feedback" id="service_id_error"></span>
                </div>
                <div id="serviceInfo" class="mt-3"></div>
                <div id="slot-input" class="mt-1 mb-3 d-flex flex-wrap gap-3"></div>
            </div>

            <div class="branch">
                <div class="mb-3">
                    <label for="branch_id" class="fw-bold">Branch *</label>
                    <select class="form-control select2 fw-bold" name="branch_id" id="branch_id" onchange="fetchBranchStaff()">
                        <option value="">Select Branch</option>
                        @forelse ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                        @empty
                        <option value="">No branch found</option>
                        @endforelse
                    </select>
                    <span class="invalid-feedback" id="user_id_error"></span>
                </div>
                <div id="branchDetails" class="mt-3"></div>
            </div>

            <div class="staff">
                <div class="mb-3">
                    <label for="staff_id" class="fw-bold">Staff *</label>
                    <select class="form-control select2 fw-bold" name="staff_id" id="staff_id" onchange="fetchStaffService()">
                        <option value="">Select Staff</option>
                    </select>
                    <span class="invalid-feedback" id="user_id_error"></span>
                </div>
                <div id="staffDetails" class="mt-3"></div>
            </div>

            <div>
                <label for="note" class="fw-bold mt-3">Add Note</label>
                <textarea name="note" id="note" class="note form-control"></textarea>
            </div>
        </form>
    </div>
    <div class="card-footer">
        <button class="btn btn-danger w-100 pay-btn-p" id="pay-btn-p">Add Appointment</button>
    </div>
</div>


@endsection