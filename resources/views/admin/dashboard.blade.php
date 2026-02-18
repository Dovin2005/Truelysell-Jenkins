@extends('admin.admin')

@section('content')

<div class="page-wrapper">
	<div class="content">
		<div class="d-md-flex d-block align-items-center justify-content-between mb-3">
			<div class="my-auto mb-2">
				<h3 class="page-title mb-1">{{ __('Admin Dashboard')}}</h3>
				<nav>
					<ol class="breadcrumb mb-0">
						<li class="breadcrumb-item">
							<a href="{{route('admin.dashboard')}}">{{ __('Dashboard')}}</a>
						</li>
						<li class="breadcrumb-item active" aria-current="page">{{ __('Admin Dashboard')}}</li>
					</ol>
				</nav>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="card bg-dark">
					<div class="overlay-img">
						<img src="{{ asset('assets/img/bg/shape-04.png') }}" alt="img" class="img-fluid shape-01">
						<img src="{{ asset('assets/img/bg/shape-01.png') }}" alt="img" class="img-fluid shape-02">
						<img src="{{ asset('assets/img/bg/shape-02.png') }}" alt="img" class="img-fluid shape-03">
						<img src="{{ asset('assets/img/bg/shape-03.png') }}" alt="img" class="img-fluid shape-04">
					</div>
					<div class="card-body">
						<div
							class="d-flex align-items-xl-center justify-content-xl-between flex-xl-row flex-column">
							<div class="mb-3 mb-xl-0">
								<div class="d-flex align-items-center flex-wrap mb-2">
									<h1 class="text-white me-2">{{ __('Welcome Back')}}, {{ Auth::user()->userDetails->first_name ?? '' }} {{ Auth::user()->userDetails->last_name ?? '' }}</h1>
								</div>
								<p class="text-white">{{ __('Have a Good day at work')}}</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xxl-3 col-sm-6 d-flex">
				<div class="card flex-fill animate-card">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div class="avatar avatar-xl bg-danger-transparent me-2 p-1">
								<img src="{{ asset('assets/img/icons/provider.svg') }}" alt="img">
							</div>
							<div class="overflow-hidden flex-fill">
								<div class="d-flex align-items-center justify-content-between">
									<h2 class="counter">{{$data['providercount']}}</h2>
								</div>
								<p>{{ __('Total Providers')}}</p>
							</div>
						</div>
						<div class="d-flex align-items-center justify-content-between border-top mt-3 pt-3">
							<p class="mb-0">{{ __('Active')}} : <span class="text-dark fw-semibold">{{$data['provideractivecnt']}}</span></p>
							<span class="text-light">|</span>
							<p>Inactive : <span class="text-dark fw-semibold">{{$data['providerinactivecnt']}}</span></p>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xxl-3 col-sm-6 d-flex">
				<div class="card flex-fill animate-card">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div class="avatar avatar-xl me-2 bg-secondary-transparent p-1">
								<img src="{{ asset('assets/img/icons/service.svg') }}" alt="img">
							</div>
							<div class="overflow-hidden flex-fill">
								<div class="d-flex align-items-center justify-content-between">
									<h2 class="counter">{{$data['servicecount']}}</h2>
								</div>
								<p>{{ __('Total Services')}}</p>
							</div>
						</div>
						<div class="d-flex align-items-center justify-content-between border-top mt-3 pt-3">
							<p class="mb-0">{{ __('Active')}} : <span class="text-dark fw-semibold">{{$data['serviceactivecnt']}}</span></p>
							<span class="text-light">|</span>
							<p>{{ __('Inactive')}} : <span class="text-dark fw-semibold">{{$data['serviceinactivecnt']}}</span></p>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xxl-3 col-sm-6 d-flex">
				<div class="card flex-fill animate-card">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div class="avatar avatar-xl me-2 bg-warning-transparent p-1">
								<img src="{{ asset('assets/img/icons/booking.svg') }}" alt="img">
							</div>
							<div class="overflow-hidden flex-fill">
								<div class="d-flex align-items-center justify-content-between">
									<h2 class="counter">{{$data['bookingcount']}}</h2>
								</div>
								<p>{{ __('Total Bookings')}}</p>
							</div>
						</div>
						<div class="d-flex align-items-center justify-content-between border-top mt-3 pt-3">
							<p class="mb-0">{{ __('Completed')}} : <span class="text-dark fw-semibold">{{$data['completedbooking']}}</span></p>
							<span class="text-light">|</span>
							<p>{{ __('Pending')}} : <span class="text-dark fw-semibold">{{$data['pendingbooking']}}</span></p>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xxl-3 col-sm-6 d-flex">
				<div class="card flex-fill animate-card">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div class="avatar avatar-xl me-2 bg-success-transparent p-1">
								<img src="{{ asset('assets/img/icons/dollar.svg') }}" alt="img">
							</div>
							<div class="overflow-hidden flex-fill">
								<div class="d-flex align-items-center justify-content-between">
								<h2 class=""><span class="text-dark">{{$data['currency'][0]['symbol'] ?? '$'}}</span>{{ $data['bookingamount'] ?? 0 }}</h2>
								</div>
								<p>{{ __('Total Amount')}}</p>
							</div>
						</div>
						<div class="d-flex align-items-center justify-content-between border-top mt-3 pt-3">
							<p class="mb-0">{{ __('Completed')}} : <span class="text-dark fw-semibold">{{$data['currency'][0]['symbol'] ?? '$'}}{{$data['completedamount']}}</span></p>
							<span class="text-light">|</span>
							<p>{{ __('Pending')}} : <span class="text-dark fw-semibold">{{$data['currency'][0]['symbol'] ?? '$'}}{{$data['pendingamount']}}</span></p>
						</div>

					</div>
				</div>
			</div>
			<div class="col-xxl-6 col-md-6 d-flex">
				<div class="card flex-fill">
					<div class="card-body">
						<div class="d-flex justify-content-between align-items-center mb-4">
							<h6>{{ __('Leads')}}</h6>
							@if(isset($data) && count($data['leads'])>0)
							<a href="{{route('admin.leads')}}" class="btn border leadsview">{{ __('View All')}}</a>
							@endif

						</div>
						<div class="servicecard">
							@if(isset($data) && count($data['leads'])>0)
									@foreach ($data['leads'] as $val)
										<div class="card mb-3">
											<div class="card-body p-3">
												<div class="d-flex justify-content-between align-items-center flex-wrap row-gap-2">
													<div class="d-flex align-items-center">
														<a href="booking-details.html" class="avatar avatar-md bg-light flex-shrink-0 me-2">
														<i class="ti ti-user text-info fs-20"></i>
														</a>
														<div>
															<p class="fw-medium mb-0">{{$val['user']->name ?? ''}}</p>
															<span class="d-block fs-12">{{$val['category']->name ?? ''}}</span>
														</div>
													</div>
													<div class="d-flex align-items-center">
														<p class="badge bg-outline-primary mb-0">
															{{$val->status_label ?? ''}}
														</p>
													</div>
												</div>
											</div>
										</div>
									@endforeach
							@else
								<div class="text-center">
										<span><b> {{ __('No Data Found')}}</b> </span>
								</div>
							@endif

						</div>
					</div>
				</div>
			</div>
			<div class="col-xxl-6 col-md-6 d-flex">
				<div class="card flex-fill">
					<div class="card-body">
						<div class="d-flex justify-content-between align-items-center mb-4">
							<h6>{{ __('Bookings')}}</h6>
							@if(isset($data) && count($data['recentbookings'])>0)
								<a href="{{route('admin.bookinglist')}}" class="btn border bookingsview">{{ __('View All')}}</a>
							@endif
						</div>
						<div class="book-crd">
							@if(isset($data) && count($data['recentbookings'])>0)
								@foreach ($data['recentbookings'] as $val)
									<div class="card mb-3">
										<div class="card-body p-3">
											<div class="d-flex justify-content-between align-items-center flex-wrap row-gap-2">
												<div class="d-flex align-items-center">
													<a href="#" class="avatar avatar-md flex-shrink-0 me-2">
													@if (isset($val->productimage) && file_exists(public_path('storage/' . $val->productimage)))
														<img src="{{ asset('storage/'.$val->productimage)}}" alt="Product Image" class="img-fluid profileImagePreview">
													@else
														<img src="{{ asset('front/img/services/add-service-04.jpg') }}" alt="Product Image" class="img-fluid profileImagePreview">
													@endif																</a>
													<div>
														<a href="#" class="fw-medium">{{$val->product_name}}</a>
														<span class="d-block fs-12">@if($val->fromtime!='')<i class="ti ti-clock me-1"></i>{{$val->fromtime}} - {{$val->totime}}@endif</span>
														<span class="d-block fs-12">{{ $val->user }}</span>
													</div>
												</div>
												<div class="d-flex align-items-center">
												<span class="d-block fs-12 booking-status" data-status="{{$val->booking_status}}">{{ $val->booking_status_label }}</span>

												</div>
											</div>
										</div>
									</div>
								@endforeach
							@else
								<div class="text-center">
										<span><b> {{ __('No Data Found')}}</b> </span>
								</div>
							@endif

						</div>
					</div>
				</div>
			</div>
			<div class="col-xxl-6 col-md-6 d-flex">
				<div class="card flex-fill">
					<div class="card-body">
						<div class="d-flex justify-content-between align-items-center mb-4">
							<h6>{{ __('Subscriptions')}}</h6>
							@if(isset($data) && count($data['subscriptions'])>0)
								<a href="{{route('admin.subscriptionlist')}}" class="btn border">{{ __('View All')}}</a>
							@endif
						</div>
						<div class="servicecard">
							@if(isset($data) && count($data['subscriptions'])>0)
									@foreach ($data['subscriptions'] as $val)
										<div class="card mb-3">
											<div class="card-body p-3">
												<div class="d-flex justify-content-between align-items-center flex-wrap row-gap-2">
													<div class="d-flex align-items-center">
														<a href="#" class="avatar avatar-md bg-light flex-shrink-0 me-2">
														@if (isset( $val->profile_image) && file_exists(public_path('storage/profile/' . $val->profile_image)))
															<img src="{{ asset('storage/profile/' . $val->profile_image) }}" alt="User Profile Image" class="img-fluid rounded-circle profileImagePreview">
														@else
															<img src="{{ asset('front/img/profiles/avatar-01.jpg') }}" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview">
														@endif
														</a>
														<div>
															<p class="fw-medium mb-0">{{$val->name ?? ''}}</p>
															<span class="d-block fs-12">{{$val->package_title ?? ''}}</span>
														</div>
													</div>
													<div class="d-flex align-items-center">
														<p class="badge bg-outline-primary mb-0">
														{{$data['currency'][0]['symbol'] ?? '$'}}{{$val->price ?? ''}}
														</p>

													</div>
												</div>
											</div>
										</div>
									@endforeach
								@else

								<div class="text-center">
									<span><b> {{ __('No Data Found')}}</b> </span>
								</div>
							@endif
						</div>
					</div>
				</div>
			</div>
			<div class="col-xxl-6 col-md-6 d-flex">
				<div class="card flex-fill">
					<div class="card-body">
						<div class="d-flex justify-content-between align-items-center mb-4">
							<h6>{{ __('Transactions')}}</h6>
							@if(isset($data) && count($data['transactions'])>0)
								<a href="{{route('admin.transaction')}}" class="btn border trxview">{{ __('View All')}}</a>
							@endif
						</div>
						<div class="book-crd">
							@if(isset($data) && count($data['transactions'])>0)
								@foreach ($data['transactions'] as $val)
									<div class="card mb-3">
										<div class="card-body p-2">
											<div class="d-flex justify-content-between align-items-center flex-wrap row-gap-2">
												<div class="d-flex align-items-center">
												<a href="#" class="avatar avatar-md flex-shrink-0 me-2">
													@if (isset($val->productimage) && file_exists(public_path('storage/' . $val->productimage)))
														<img src="{{ asset('storage/'.$val->productimage)}}" alt="Product Image" class="img-fluid profileImagePreview">
													@else
														<img src="{{ asset('front/img/services/add-service-04.jpg') }}" alt="Product Image" class="img-fluid profileImagePreview">
													@endif
													</a>
													<div>
														<a href="#" class="fw-medium">{{$val->product_name}}</a>
															<span class="d-block fs-12">{{ $val->user }}</span>
															<span class="d-block fs-12">{{$data['currency'][0]['symbol'] ?? '$'}}{{ $val->service_amount }}</span>
													</div>
												</div>
												<div class="d-flex align-items-center">
												<span class="d-block fs-12">{{ $val->paymentstatus }}</span>

												</div>
											</div>
										</div>
									</div>
								@endforeach
							@else
								<div class="text-center">
									<span><b> {{ __('No Data Found')}}</b> </span>
								</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection










