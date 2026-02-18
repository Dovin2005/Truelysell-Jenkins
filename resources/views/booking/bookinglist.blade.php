@extends('front')
@section('content')
<div class="breadcrumb-bar text-center">
	<div class="container">
		<div class="row">
			<div class="col-md-12 col-12">
				<h2 class="breadcrumb-title mb-2">{{__('Bookings')}}</h2>
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb justify-content-center mb-0">
						<li class="breadcrumb-item"><a href="{{route('user.dashboard')}}"><i class="ti ti-home-2"></i></a></li>
						<li class="breadcrumb-item">{{__('Customer')}}</li>
						<li class="breadcrumb-item active" aria-current="page">{{__('Bookings')}}</li>
					</ol>
				</nav>
			</div>
		</div>
	</div>
</div>
<div class="page-wrapper">
	<div class="content">
		<div class="container">
			<div class="row justify-content-center">
				@include('user.partials.sidebar')
				<div class="col-xl-9 col-lg-8">
					<div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-4">
						<h4>{{__('Booking List')}}</h4>
					</div>
					@if(count($data['bookingdata']) > 0)
					@foreach($data['bookingdata'] as $val)
					<div class="card shadow-none booking-list">
						<div class="card-body d-md-flex align-items-center">
							<div class="booking-widget d-sm-flex align-items-center row-gap-3 flex-fill mb-3 mb-md-0">
								<div class="booking-img me-sm-3 mb-3 mb-sm-0">
									<a href="#" data-bs-toggle="modal" data-bs-target="#booking_details" data-booking-details-id="{{ $val->id }}" class="avatar booking_details_btn">
										<img src="{{ $val->productimage }}" alt="Product Image" class="img-fluid profileImagePreview">
									</a>
								</div>
								<div class="booking-det-info">
									<h6 class="mb-3">
										<a href="#" class="booking_details_btn" data-bs-toggle="modal" data-bs-target="#booking_details" data-booking-details-id="{{ $val->id }}">
											<span>{{ $val->source_name ?? ''}}</span>
										</a>
										<span class="booking-status bookingstatus{{$val->id}}" data-status="{{ $val->booking_status }}">{{ $val->booking_status_label }}</span>
										</h5>
										<ul class="booking-details">
											@if (!empty($val->service_offer))
											@php
											$service_offer = unserialize($val->service_offer);
											@endphp
											<div class="additional-service">
												<h6 class="mb-3 fs-16 fw-bold">{{__('Additional Services')}}</h6>
												<ul class="list-unstyled ms-5">
													@foreach ($service_offer as $offer)
													@php
													[$serviceName, $price] = explode('_', $offer);
													@endphp
													<li class="d-flex justify-content-between align-items-center mb-2">
														<span class="service-name font-weight-bold">{{ $serviceName }}</span>
														<span class="service-price text-muted">{{ $data['currency'] }}{{ number_format($price, 2) }}</span>
													</li>
													@endforeach
												</ul>
											</div>
											@endif
											<li class="d-flex align-items-center mb-2">
												<span class="book-item font-weight-bold">{{__('order_id')}}</span> <small class="mx-2">:</small> {{ $val->order_id ?? '-' }}
											</li>
											<li class="d-flex align-items-center mb-2">
												<span class="book-item font-weight-bold">{{__('Booking Date')}}</span> <small class="mx-2">:</small> {{ $val->bookingdate }} {{ $val->fromtime }} {{ $val->totime }}
											</li>
											<li class="d-flex align-items-center mb-2">
												<span class="book-item font-weight-bold">{{__('Amount')}}</span> <small class="mx-2">:</small> {{ $data['currency'] }}{{ $val->total_amount }}
												<span class="badge badge-soft-primary ms-2">{{ $val->paymenttype }}</span>
											</li>
											<li class="d-flex align-items-center mb-2">
												<span class="book-item font-weight-bold">{{__('Location')}}</span> <small class="mx-2">:</small> {{ $val->user_city }}
											</li>
											<li class="d-flex align-items-center flex-wrap">
												<span class="book-item font-weight-bold">{{__('Provider')}}</span> <small class="mx-2">:</small>
												<div class="user-book d-flex align-items-center flex-wrap">
													<div class="avatar avatar-xs me-2">
														@if (isset($val->profile_image) && file_exists(public_path('storage/profile/' .$val->profile_image)))
														<img src="{{ asset('storage/profile/' .$val->profile_image) }}" alt="User Profile Image" class="img-fluid rounded-circle profileImagePreview">
														@else
														<img src="{{ asset('assets/img/profile-default.png') }}" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview">
														@endif
													</div>
													<span class="me-4">{{ $val->provider_name ?? '' }}</span>
												</div>
												@if($val->booking_status_label=='Open')
												@if($val->email!='')
												<p class="mb-0 me-2"><i class="ti ti-mail fs-10 text-muted me-1"></i> {{($val->email ?? null) ? maskEmail($val->email) : ''}}</p>
												@endif
												@if($val->phone_number!='')
												<p><i class="ti ti-phone-filled fs-10 text-muted me-2"></i>{{ ($val->phone_number ?? null) ? mask_mobile_number($val->phone_number) : '' }}
												</p>
												@endif
												@else
												@if($val->email!='')
												<p class="mb-0 me-3"><i class="ti ti-mail fs-10 text-muted me-1"></i> {{$val->email ?? ''}}</p>
												@endif
												@if($val->phone_number!='')
												<p><i class="ti ti-phone-filled fs-10 text-muted me-2"></i> {{ $val->phone_number ?? '' }}</p>
												@endif
												@endif
											</li>
											@if(!empty($val->coupon_code))
											<li class="d-flex align-items-center mb-2 mt-2">
												<span class="book-item font-weight-bold">{{ __('Coupon Applied') }}</span>
												<small class="mx-2">:</small>
												{{ $val->coupon_code }}
											</li>
											@endif

										</ul>

										<div class="mt-3 d-flex gap-5">
											<a class="raise-dispute-btn text-danger fs-14"
												href="#"
												data-bs-toggle="modal"
												data-bs-target="#reschedule"
												data-booking-id="{{ $val->id }}">
												<i class="ti ti-pennant-filled me-2"></i>{{__('Raise Dispute')}}
											</a>
											@if($val->booking_status_label == 'Refund Completed')
											<a class="viewpaymentproof" href="javascript:void(0);" data-bookingid="{{ $val->id }}" data-proof="{{ $val->payment_proof ?? '' }}">
												<i class="ti ti-file fs-20"></i><span>{{__('View Proof')}}</span>
											</a>
											@endif
										</div>
								</div>
							</div>

							@if($val->booking_status_label == 'Cancelled' || $val->booking_status_label == 'Provider Cancelled' )
							<div class="refunddiv{{ $val->id }}">
								<a class="btn btn-success refund" data-bs-toggle="modal" data-bs-target="#refund" data-id="{{ $val->id }}">{{__('Refund')}}</a>
							</div>
							@elseif($val->booking_status_label == 'Completed')
							<div class="orderdiv{{ $val->id }}">
								<a class="btn btn-success ordercomplete" data-bs-toggle="modal" data-bs-target="#orderclose" data-id="{{ $val->id }}">{{__('Order Complete')}}</a>
							</div>
							@elseif($val->booking_status_label == 'Open')
							<div class="orderdiv{{ $val->id }}">
								<a href="#" class="btn btn-danger cancel" data-bs-toggle="modal" data-bs-target="#cancel_appointment" data-id="{{$val->id}}">{{__('Cancel')}}</a>
							</div>
							@elseif($val->booking_status_label == 'Order Completed')
							@if(is_null($val->payment_type))

									<div class="orderdiv{{ $val->id }}">
										<a href="#"
										class="btn btn-warning make-payment-btn"
										data-bs-toggle="modal"
										data-bs-target="#makePaymentModal"
										data-booking-id="{{ $val->id }}"
										data-chore-id="{{ $val->chore_id }}"
										data-amount="{{ $val->total_amount }}">
										Make Payment
										</a>
									</div>

								@else
								@if ($val->review)
									<a href="#!" class="booking_details_btn btn btn-success" data-bs-toggle="modal" data-bs-target="#booking_details" data-booking-details-id="{{ $val->id }}">
										{{__('View Order')}}
									</a>
								@else
								<div class="orderdiv{{ $val->id }}">
									<a href="#!" class="btn btn-success add_review_btn" data-bs-toggle="modal"
										data-bs-target="#add-review"
										data-id="{{$val->id}}"
										data-booking_id="{{ $val->id }}"
										data-product_id="{{ $val->product_id }}">
										{{__('add_review')}}</a>
								</div>
								@endif
								@endif
							@endif
						</div>
					</div>
					@endforeach
					@else
					<div class="card shadow-none booking-list h-80">
						<div class="card-body d-flex align-items-center justify-content-center">
							<p class="fw-bold">{{__('No Bookings Available')}}</p>
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
</div>

<div class="modal fade" id="add-review" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header d-flex align-items-center justify-content-between">
				<h5>{{ __('add_review') }}</h5>
				<a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
			</div>
			<form id="addCommentsForm">
				<div class="modal-body">
					<input type="hidden" id="user_id" value="{{ Auth::id() }}">
					<input type="hidden" id="review_booking_id" name="review_booking_id">
					<input type="hidden" id="review_product_id" name="review_product_id">
					<div class="mb-3">
						<label class="form-label">{{ __('rate_your_review') }}</label>
						<div class="rating-select mb-0">
							<a href="javascript:void(0);"><i class="fas fa-star"></i></a>
							<a href="javascript:void(0);"><i class="fas fa-star"></i></a>
							<a href="javascript:void(0);"><i class="fas fa-star"></i></a>
							<a href="javascript:void(0);"><i class="fas fa-star"></i></a>
							<a href="javascript:void(0);"><i class="fas fa-star"></i></a>
						</div>
					</div>
					<div class="mb-0">
						<label class="form-label">{{ __('write_your_review') }}</label>
						<textarea class="form-control" name="review" id="review" rows="3" maxlength="350"></textarea>
						<span class="error-text text-danger" id="review_error"></span>
					</div>
				</div>
				<div class="modal-footer d-flex align-items-center justify-content-end">
					<a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
					<button type="submit" class="btn btn-primary" id="save_comment_btn">{{ __('Save') }}</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade custom-modal" id="reschedule">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header d-flex align-items-center justify-content-between border-bottom">
				<h5 class="modal-title">{{__('Raise Dispute')}}</h5>
				<a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
			</div>
			<div class="modal-body">
				<div id="loadingMessage" style="display: none; text-align: center; font-weight: bold; font-style: italic;">
					{{__('Loading, please wait...')}}
				</div>

				<!-- Dispute Form -->
				<form id="raiseDispute" style="display: none;">
					{{ csrf_field() }}
					<input type="hidden" name="booking_id" id="booking_id" value="">
					<input type="hidden" name="product_id" id="product_id" value="">
					<input type="hidden" name="provider_id" id="provider_id" value="">

					<div class="row">
						<div class="col-md-12">
							<div class="mb-3">
								<label class="form-label">{{__('Subject')}} <span class="text-danger">*</span></label>
								<input type="text" name="subject" id="subject" class="form-control" placeholder="{{ __('Enter Subject') }}">
								<span class="invalid-feedback" id="subject_error" data-required="{{ __('this_field_required') }}"></span>
							</div>
						</div>

						<div class="col-md-12">
							<div class="mb-3">
								<label class="form-label">{{__('Content')}} <span class="text-danger">*</span></label>
								<textarea name="content" id="content" class="form-control" rows="5" placeholder="{{ __('Enter Content') }}"></textarea>
								<span class="invalid-feedback" id="content_error"></span>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{__('Cancel')}}</a>
						<button class="btn btn-dark btn_dispute" type="submit">{{__('Save')}}</button>
					</div>
				</form>

				<!-- Admin Reply Section -->
				<!-- Admin Reply Section -->
				<div id="adminReplySection" style="display: none;">
					<div class="mb-3">
						<label class="form-label fw-bold">{{__('Subject')}} :</label>
						<input type="text" id="admin_subject" class="form-control border-0 text-danger fw-bold" readonly>
					</div>
					<div class="mb-3">
						<label class="form-label fw-bold">{{__('Content')}} :</label>
						<textarea id="admin_content" class="form-control border-0 text-danger fw-bold" rows="2" readonly></textarea>
					</div>
					<hr style="border: 1px solid #000; margin: 10px 0;">
					<div class="mb-3">
						<label class="form-label fw-bold">{{__('Admin Reply')}}:</label>
						<textarea id="admin_reply" class="form-control border-0 fw-bold" rows="5" readonly></textarea>
					</div>
				</div>


			</div>


		</div>
	</div>
</div>

<div class="modal fade custom-modal" id="cancel_appointment">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header d-flex align-items-center justify-content-between border-bottom">
				<h5 class="modal-title">{{__('Cancel Booking')}}</h5>
				<a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
			</div>
			<form>
				<div class="modal-body">
					<p>{{__('Are you sure you want to cancel this booking?')}}</p>
				</div>
				<div class="modal-footer">
					<div class="acc-submit">
						<a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{__('Dismiss')}}</a>
						<button class="btn btn-dark cancelbooking bookingid" data-id="" data-type="8" data-yes_cancel="{{ __('Yes, Cancel') }}" type="button">{{__('Yes, Cancel')}}</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade custom-modal" id="refund">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header d-flex align-items-center justify-content-between border-bottom">
				<h5 class="modal-title">{{__('Refund Process')}}</h5>
				<a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
			</div>
			<form>
				<div class="modal-body">
					<p>{{__('Are you sure you want to Initiate Refund Process?')}}</p>
					</p>
				</div>
				<div class="modal-footer">
					<div class="acc-submit">
						<a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{__('Dismiss')}}</a>
						<button class="btn btn-success refundprocess" type="submit" data-id='' data-type='4' data-yes="{{ __('Yes') }}">{{__('Yes')}}</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade custom-modal" id="orderclose">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header d-flex align-items-center justify-content-between border-bottom">
				<h5 class="modal-title">{{__('Order Complete')}}</h5>
				<a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
			</div>
			<form>
				<div class="modal-body">
					<p>{{__('Are you sure you want to complete order?')}}</p>
					</p>
				</div>
				<div class="modal-footer">
					<div class="acc-submit">
						<a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{__('Dismiss')}}</a>
						<button class="btn btn-success ordercompleteprocess" type="submit" data-id='' data-type='6' data-yes="{{ __('Yes') }}">{{__('Yes')}}</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="dispute_modal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body text-center">
				<div class="mb-4">
					<span class="success-icon mx-auto mb-4">
						<i class="ti ti-check"></i>
					</span>
					<h4 class="mb-1">{{ __('Dispute Submitted Successfully') }}</h4>
					<p>{{ __('dispute_submit_success_info') }}</p>
				</div>
				<a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-linear-primary">{{__('Close')}}</a>
			</div>
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

				<!-- Review Information -->
				<div class="mb-3 review-section d-none">
					<h6 class="fs-16 fw-bold mb-2 text-decoration-underline">{{ __('Review Details') }}</h6>
					<div class="d-flex flex-column gap-2">

						<label class="d-flex gap-2">
							<strong class="w-25">{{ __('Rating') }}</strong>:
							<span class="text-dark flex-grow-1 d-flex" id="review_rating"></span>
						</label>

						<label class="d-flex gap-2">
							<strong class="w-25">{{ __('Review') }}</strong>:
							<span class="text-dark flex-grow-1" id="review_text"></span>
						</label>

						<label class="d-flex gap-2 provider_reply_section">
							<strong class="w-25">{{ __('Provider Reply') }}</strong>:
							<span class="text-dark flex-grow-1" id="reply_review"></span>
						</label>

						<label class="d-flex gap-2">
							<strong class="w-25">{{ __('Review Date') }}</strong>:
							<span class="text-dark flex-grow-1" id="review_date"></span>
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
<div class="modal fade" id="makePaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Complete Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="make-payment-form">
                @csrf

                <input type="hidden" id="mp_booking_id" name="booking_id">
                <input type="hidden" id="mp_chore_id" name="chore_id">
                <input type="hidden" id="mp_total_amount" name="total_amount">
                <input type="hidden" name="booking_type" value="chore">

                <div class="modal-body">

                    <div class="alert alert-info mb-3">
                        Your booking is confirmed.
                        Please complete the payment to proceed.
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold mb-2 d-block">
                            Choose Payment Method
                        </label>

                        {{-- Stripe --}}
                        @if(($data['paymentInfo']['stripe_status'] ?? 0) == 1)
                        <div class="form-check mb-2">
                            <input class="form-check-input"
                                   type="radio"
                                   name="payment_type"
                                   value="stripe"
                                   checked>
                            <label class="form-check-label">Stripe</label>
                        </div>
                        @endif

                        {{-- Wallet --}}
                        @if(($data['paymentInfo']['wallet_status'] ?? 0) == 1)
                        <div class="form-check mb-2">
                            <input class="form-check-input"
                                   type="radio"
                                   name="payment_type"
                                   value="wallet">
                            <label class="form-check-label">Wallet</label>
                        </div>
                        @endif

                        {{-- COD --}}
                        @if(($data['paymentInfo']['cod_status'] ?? 0) == 1)
                        <div class="form-check mb-2">
                            <input class="form-check-input"
                                   type="radio"
                                   name="payment_type"
                                   value="cod">
                            <label class="form-check-label">
                                Cash on Delivery (COD)
                            </label>
                        </div>
                        @endif

                        {{-- PayPal --}}
                        @if(($data['paymentInfo']['paypal_status'] ?? 0) == 1)
                        <div class="form-check mb-2">
                            <input class="form-check-input"
                                   type="radio"
                                   name="payment_type"
                                   value="paypal">
                            <label class="form-check-label">PayPal</label>
                        </div>
                        @endif

                        {{-- Mollie --}}
                        @if(($data['paymentInfo']['mollie_status'] ?? 0) == 1)
                        <div class="form-check mb-2">
                            <input class="form-check-input"
                                   type="radio"
                                   name="payment_type"
                                   value="mollie">
                            <label class="form-check-label">Mollie</label>
                        </div>
                        @endif
                    </div>

                    <div class="text-end fw-bold fs-16">
                        Amount:
                        {{ $data['currency'] ?? '$' }}
                        <span id="mp_amount_text"></span>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary pay-now-btn">
                        Pay Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
	document.addEventListener('click', function (e) {

    const btn = e.target.closest('.make-payment-btn');
    if (!btn) return;

    document.getElementById('mp_booking_id').value =
        btn.dataset.bookingId;

    document.getElementById('mp_chore_id').value =
        btn.dataset.choreId;

    document.getElementById('mp_total_amount').value =
        btn.dataset.amount;

    document.getElementById('mp_amount_text').textContent =
        btn.dataset.amount;
});


document.getElementById('make-payment-form')
?.addEventListener('submit', function (e) {

    e.preventDefault();

    const form = this;
    const btn  = form.querySelector('.pay-now-btn');
    const formData = new FormData(form);

    btn.disabled = true;
    btn.innerHTML =
        '<span class="spinner-border spinner-border-sm"></span>';

    fetch('/user/make-payment-booking', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN':
                document.querySelector(
                    'meta[name="csrf-token"]'
                ).getAttribute('content')
        }
    })
    .then(res => res.json())
    .then(response => {

        if (response.data?.checkout_url) {
            window.location.href =
                response.data.checkout_url;
            return;
        }

        toastr.success(response.message);
        location.reload();
    })
    .catch(() => {
        toastr.error('Payment failed');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Pay Now';
    });
});


</script>
@endsection