@extends('provider.provider')
@section('content')
 <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-4">
                    <h4>{{ __('Booking List') }}</h4>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-xxl-12 col-lg-12">
					@if(count($data['bookingdata'])>0)
					    @foreach($data['bookingdata'] as $val)
							<div class="card shadow-none booking-list">
									<div class="card-body d-md-flex align-items-start">
										<div class="booking-widget d-sm-flex align-items-center row-gap-3 flex-fill  mb-3 mb-md-0">
											<div class="booking-img me-sm-3 mb-3 mb-sm-0">
												<a href="#" class="avatar booking_details_btn" data-bs-toggle="modal" data-bs-target="#booking_details" data-booking-details-id="{{ $val->id }}">
                                                    <img src="{{ $val->productimage }}" alt="Product Image" class="img-fluid">
                                                </a>
											</div>
											<div class="booking-det-info">
												<h6 class="mb-3">
													<a href="#" class="booking_details_btn" data-bs-toggle="modal" data-bs-target="#booking_details" data-booking-details-id="{{ $val->id }}">
                                                        <span>{{ $val->source_name ?? ''}}</span>
                                                    </a>
                                                    <span class="booking-status bookingstatus{{$val->id}}" data-status="{{$val->booking_status}}">{{$val->booking_status_label}}</span>
												</h6>
												<ul class="booking-details">
                                                    <li class="d-flex align-items-center mb-2">
                                                        <span class="book-item">{{__('order_id')}}</span> <small class="me-2">:</small> {{ $val->order_id ?? '-' }}
                                                    </li>
													<li class="d-flex align-items-center mb-2">
														<span class="book-item">{{ __('Booking Date') }}</span> <small class="me-2">: </small>{{$val->bookingdate}}
													</li>
													<li class="d-flex align-items-center mb-2">
														<span class="book-item">{{ __('Amount') }}</span> <small class="me-2">: </small> {{$data['currency']}}{{$val->total_amount}}<span class="badge badge-soft-primary ms-2">{{ $val->paymenttype }}</span>
													</li>
													<li class="d-flex align-items-center mb-2">
														<span class="book-item">{{ __('Location') }}</span> <small class="me-2">: </small>{{$val->user_city}}
													</li>
													<li class="d-flex align-items-center flex-wrap">
														<span class="book-item">{{ __('User') }}</span> <small class="me-2">: </small>
														<div class="user-book d-flex align-items-center flex-wrap me-2">
															<div class="avatar avatar-xs me-2">
                                                                @if (isset( $val->profile_image) && file_exists(public_path('storage/profile/' . $val->profile_image)))
                                                                    <img src="{{ asset('storage/profile/' . $val->profile_image) }}" alt="User Profile Image" class="img-fluid rounded-circle profileImagePreview">
                                                                @else
                                                                    <img src="{{ asset('assets/img/profile-default.png') }}" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview">
                                                                @endif
															</div>
															<span class="me-4"> {{$val->user_name ?? ""}} </span>
														</div>
                                                        @if($val->booking_status_label=='Open')
                                                            @if($val->email!='')
                                                                <p class="mb-0 me-2"><i class="ti ti-email fs-10 text-muted me-1"></i>{{($val->email ?? null) ? maskEmail($val->email) : ''}}</p>
                                                            @endif
                                                            @if($val->contact!='')
                                                            <p><i class="ti ti-phone-filled fs-10 text-muted me-2"></i>{{ ($val->contact ?? null) ? mask_mobile_number($val->contact) : '' }}
                                                            </p>
                                                            @endif
                                                        @else
                                                        @if($val->email!='')
                                                        <p class="mb-0 me-2"><i class="ti ti-email fs-10 text-muted me-1"></i>{{$val->email ?? ''}}</p>
                                                        @endif
                                                            @if($val->contact!='')
                                                            <p><i class="ti ti-phone-filled fs-10 text-muted me-2"></i>{{ $val->contact ?? '' }}</p>
                                                            @endif
                                                        @endif
													</li>
												</ul>
											</div>
										</div>
						                    @if($val->booking_status_label=='Open')
                                                <div class="acceptdiv{{$val->id}} d-flex justify-content-start gap-2">
                                                    <a href="#" class="btn btn-success accept" data-bs-toggle="modal" data-bs-target="#accept" data-id="{{$val->id}}">{{ __('Accept') }}</a>
                                                    <a href="#" class="btn btn-danger cancel" data-bs-toggle="modal" data-bs-target="#cancel_appointment" data-id="{{$val->id}}">{{ __('Cancel') }}</a>
                                                </div>
                                            @elseif($val->booking_status_label=='In progress')
                                                <div class="completediv{{$val->id}} d-flex justify-content-start gap-2">
                                                    <a href="#" class="btn btn-success complete" data-bs-toggle="modal" data-bs-target="#completed" data-id="{{$val->id}}">{{ __('Complete') }}</a>
                                                    <a href="{{ route('provider.chat.with-user', ['user_id' => customEncrypt($val->user_id, \App\Models\User::$userSecretKey)]) }}" class="btn btn-dark me-2 chattab" data-userid="{{$val->user_id}}" data-user="{{$val->user_name}}" data-authuserid="{{$data['authuserid']}}"><i class="ti ti-message-2 me-2"></i>{{ __('Chat') }}</a>
                                                </div>
                                            @endif
									</div>
								</div>
						@endforeach
					@else
                    <div class="card shadow-none booking-list h-80">
							<div class="card-body d-flex align-items-center justify-content-center">
								<p class="fw-bold">{{ __('No Bookings Available') }}</p>
							</div>
						</div>
					@endif
                </div>
            </div>

            <div class="d-flex justify-content-center">
                {{ $data['bookingdata']->links('pagination::bootstrap-4') }}
            </div>
        </div>
     </div>

    <div class="modal fade" id="accept">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
                <form>
                    <div class="modal-body text-center">
                        <h4>{{ __('Confirm Accept') }}</h4>
                        <p>{{ __('Do You want to accept this booking?') }}</p>
                        <div class="d-flex justify-content-center">
                            <a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                            <button  class="btn btn-success bookingid" id="acceptbooking" data-id="" data-type="2">{{ __('Yes, Confirm') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
	</div>

    <div class="modal fade" id="completed">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
                <form>
                    <div class="modal-body text-center">
                        <h4>{{ __('Confirm Complete') }}</h4>
                        <p>{{ __('Do You want to complete this booking?') }}</p>
                        <div class="d-flex justify-content-center">
                            <a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                            <button  class="btn btn-success bookingid" id="completebooking" data-id="" data-type="5">{{ __('Yes, Confirm') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
	</div>

    <div class="modal fade custom-modal" id="add_booking">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                    <h5 class="modal-title">Add Booking</h5>
                    <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
                </div>
                <form action="provider-booking.html">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Staff</label>
                                    <select class="select">
                                        <option>Select</option>
                                        <option>Jeff Fitch</option>
                                        <option>Donald Gordon</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Service</label>
                                    <select class="select">
                                        <option>Select</option>
                                        <option>Computer Services</option>
                                        <option>Car Repair Services</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Customer</label>
                                    <select class="select">
                                        <option>Select</option>
                                        <option>Jeff Fitch</option>
                                        <option>Donald Gordon</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="sel-cal Calendar-icon">
                                        <span><i class="ti ti-calendar-month"></i></span>
                                        <input class="form-control datetimepicker" type="text" placeholder="dd-mm-yyyy">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <div class="sel-cal Calendar-icon">
                                                <span><i class="ti ti-clock"></i></span>
                                                <input class="form-control timepicker" type="text" placeholder="dd-mm-yyyy">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <div class="sel-cal Calendar-icon">
                                                <span><i class="ti ti-clock"></i></span>
                                                <input class="form-control timepicker" type="text" placeholder="dd-mm-yyyy">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-0">
                                    <label class="form-label">Booking Message</label>
                                    <textarea rows="4" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</a>
                        <button type="submit" class="btn btn-dark">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

	<div class="modal fade custom-modal" id="reschedule">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                    <h5 class="modal-title">Reschedule Appointment</h5>
                    <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
                </div>
                <div class="modal-body">
                    <form action="user-bookings.html">
                        <div class="mb-3">
							<label class="form-label">Appointment Date</label>
							<div class="form-icon">
								<input type="text" class="form-control datetimepicker" placeholder="DD/MM/YYYY">
								<span class="cus-icon"><i class="feather-calendar"></i></span>
							</div>
						</div>
						<div class="mb-0">
							<label class="form-label">Appointment Time</label>
							<div class="form-icon">
								<input type="text" class="form-control timepicker" placeholder="DD/MM/YYYY">
								<span class="cus-icon"><i class="feather-clock"></i></span>
							</div>
						</div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="acc-submit">
                        <a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</a>
                        <button class="btn btn-dark" type="submit">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

	<div class="modal fade custom-modal" id="cancel_appointment">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                    <h5 class="modal-title">{{ __('Cancel Booking') }}</h5>
                    <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
                </div>
				<form>
					<div class="modal-body">
						<p>{{ __('Are you sure you want to cancel this booking?') }}</p>
					</div>
					<div class="modal-footer">
						<div class="acc-submit">
							<a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Dismiss') }}</a>
							<button class="btn btn-dark bookingid" id="cancelbooking" data-id="" data-type="3" type="submit">{{ __('Yes, Cancel') }}</button>
						</div>
					</div>
				</form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="booking_details" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Booking Details') }} <span id="order_id" class="badge bg-primary ms-2"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body fs-12">

                    <!-- Service Information -->
                    <div class="mb-3">
                        <div class="skeleton label-skeleton label-loader w-75"></div>
                        <h6 class="fs-16 fw-bold mb-2 text-decoration-underline d-none real-label">{{ __('Service Information') }}</h6>
                        <div class="d-flex flex-column gap-2">
                            <!-- Service Name -->
                            <div class="d-flex align-items-center gap-2">
                                <div class="skeleton label-skeleton label-loader flex-grow-1 w-50"></div>
                                <label class="d-none real-label d-flex gap-2 w-100">
                                    <strong class="w-25">{{ __('Service Name') }}</strong>:
                                    <span class="text-dark flex-grow-1" id="service_name"></span>
                                </label>
                            </div>

                            <!-- Service Code -->
                            <div class="d-flex align-items-center gap-2">
                                <div class="skeleton label-skeleton label-loader flex-grow-1 w-50"></div>
                                <label class="d-none real-label d-flex gap-2 w-100">
                                    <strong class="w-25">{{ __('service_code') }}</strong>:
                                    <span class="text-dark flex-grow-1" id="service_code"></span>
                                </label>
                            </div>

                            <!-- Amount -->
                            <div class="d-flex align-items-center gap-2">
                                <div class="skeleton label-skeleton label-loader flex-grow-1 w-50"></div>
                                <label class="d-none real-label d-flex gap-2 w-100">
                                    <strong class="w-25">{{ __('Service Amount') }}</strong>:
                                    <span class="text-dark flex-grow-1" id="service_amount"></span>
                                </label>
                            </div>

                            <!-- Total Amount -->
                            <div class="d-flex align-items-center gap-2">
                                <div class="skeleton label-skeleton label-loader flex-grow-1 w-50"></div>
                                <label class="d-none real-label d-flex gap-2 w-100">
                                    <strong class="w-25">{{ __('Total Amount') }}</strong>:
                                    <span class="text-dark flex-grow-1" id="total_amount"></span>
                                </label>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <div class="skeleton label-skeleton label-loader flex-grow-1 w-50"></div>
                                <label class="d-none real-label d-flex gap-2 w-100">
                                    <strong class="w-25">{{ __('booking_status') }}</strong>:
                                    <span class="text-dark flex-grow-1" id="booking_status"></span>
                                </label>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <div class="skeleton label-skeleton label-loader flex-grow-1 w-50"></div>
                                <label class="d-none real-label d-flex gap-2 w-100">
                                    <strong class="w-25">{{ __('Payment Status') }}</strong>:
                                    <span class="text-dark flex-grow-1" id="payment_type"></span>
                                </label>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <div class="skeleton label-skeleton label-loader flex-grow-1 w-50"></div>
                                <label class="d-none real-label d-flex gap-2 w-100">
                                    <strong class="w-25">{{ __('Payment Type') }}</strong>:
                                    <span class="text-dark flex-grow-1" id="payment_status"></span>
                                </label>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <div class="skeleton label-skeleton label-loader flex-grow-1 w-50"></div>
                                <label class="d-none real-label d-flex gap-2 w-100">
                                    <strong class="w-25">{{ __('Booking Date') }}</strong>:
                                    <span class="text-dark flex-grow-1" id="booking_date"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="additional_service d-none mb-3">
                        <h6 class="fs-16 fw-bold mb-2 text-decoration-underline d-none real-label">{{ __('Additional Services') }}</h6>
                        <div class="mb-3" id="additional_service_list"></div>
                    </div>

                    <!-- Slot Details -->
                    <div class="mb-3 slot_info" style="display: none;">
                        <div class="skeleton label-skeleton label-loader w-75"></div>
                        <h6 class="fs-16 fw-bold mb-2 text-decoration-underline d-none real-label">{{ __('slot_details') }}</h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('slot_date') }}</strong>:
                                <span class="text-dark flex-grow-1" id="slot_date"></span>
                            </label>

                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('slot_day') }}</strong>:
                                <span class="text-dark flex-grow-1" id="slot_day"></span>
                            </label>

                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('slot_time') }}</strong>:
                                <span class="text-dark flex-grow-1" id="slot_time"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Buyer Information -->
                    <div class="mb-3">
                        <div class="skeleton label-skeleton label-loader w-75"></div>
                        <h6 class="fs-16 fw-bold mb-2 text-decoration-underline d-none real-label">{{ __('buyer_information') }}</h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('name') }}</strong>:
                                <span class="text-dark flex-grow-1" id="buyer_name"></span>
                            </label>

                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('email') }}</strong>:
                                <span class="text-dark flex-grow-1" id="buyer_email"></span>
                            </label>

                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('phone_number') }}</strong>:
                                <span class="text-dark flex-grow-1" id="buyer_phone"></span>
                            </label>

                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('city') }}</strong>:
                                <span class="text-dark flex-grow-1" id="buyer_city"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Provider Information -->
                    <div class="mb-3">
                        <div class="skeleton label-skeleton label-loader w-75"></div>
                        <h6 class="fs-16 fw-bold mb-2 text-decoration-underline d-none real-label">{{ __('provider_information') }}</h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('name') }}</strong>:
                                <span class="text-dark flex-grow-1" id="provider_name"></span>
                            </label>

                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('email') }}</strong>:
                                <span class="text-dark flex-grow-1" id="provider_email"></span>
                            </label>

                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('phone_number') }}</strong>:
                                <span class="text-dark flex-grow-1" id="provider_mobile"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Staff Information -->
                    <div class="mb-3 staff_info" style="display: none;">
                        <div class="skeleton label-skeleton label-loader w-75"></div>
                        <h6 class="fs-16 fw-bold mb-2 text-decoration-underline d-none real-label">{{ __('staff_information') }}</h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('name') }}</strong>:
                                <span class="text-dark flex-grow-1" id="staff_name"></span>
                            </label>

                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('email') }}</strong>:
                                <span class="text-dark flex-grow-1" id="staff_email"></span>
                            </label>

                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('phone_number') }}</strong>:
                                <span class="text-dark flex-grow-1" id="staff_mobile"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Branch Information -->
                    <div class="mb-3 branch_info" style="display: none;">
                        <div class="skeleton label-skeleton label-loader w-75"></div>
                        <h6 class="fs-16 fw-bold mb-2 text-decoration-underline d-none real-label">{{ __('Branch Information') }}</h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('Branch Name') }}</strong>:
                                <span class="text-dark flex-grow-1" id="branch_name"></span>
                            </label>

                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('email') }}</strong>:
                                <span class="text-dark flex-grow-1" id="branch_email"></span>
                            </label>

                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('phone_number') }}</strong>:
                                <span class="text-dark flex-grow-1" id="branch_mobile"></span>
                            </label>

                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <label class="d-none real-label d-flex gap-2">
                                <strong class="w-25">{{ __('address') }}</strong>:
                                <span class="text-dark flex-grow-1" id="branch_address"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3 notes_info d-none">
                        <div class="skeleton label-skeleton label-loader w-75"></div>
                        <h6 class="fs-16 fw-bold mb-2 text-decoration-underline d-none real-label">{{ __('Notes') }}</h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="skeleton label-skeleton label-loader w-50"></div>
                            <span class="text-dark d-none real-label" id="notes"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                </div>
            </div>
        </div>
    </div>

@endsection
