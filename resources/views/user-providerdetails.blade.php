@extends('front')

@section('content')

	<div class="breadcrumb-bar text-center">
		<div class="container">
			<div class="row">
				<div class="col-md-12 col-12">
					<h2 class="breadcrumb-title mb-2">{{__('Provider Detail')}}</h2>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb justify-content-center mb-0">
							<li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}"><i class="ti ti-home-2"></i></a></li>
							<li class="breadcrumb-item"><a href="{{ route('user.providerlist') }}">{{__('Provider List')}}</a></li>
							<li class="breadcrumb-item active" aria-current="page">{{__('Provider Detail')}}</li>
						</ol>
					</nav>
				</div>
			</div>
			<div class="breadcrumb-bg">
				<img src="/assets/img/bg/breadcrumb-bg-01.png" class="breadcrumb-bg-1" alt="Img">
				<img src="/assets/img/bg/breadcrumb-bg-02.png" class="breadcrumb-bg-2" alt="Img">
			</div>
		</div>
	</div>

	<div class="page-wrapper">
		<div class="content content-two">
            <div id="pageLoader1" class="loader_front">
                <div>
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">..</span>
                    </div>
                </div>
            </div>
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<div class="card">
							<div class="card-body">
								<div class="row gy-3">
									<div class="col-xl-5">
										<div class="provider-detail d-flex align-items-center flex-wrap row-gap-2">
											{{-- provider picture & basic info --}}
											<span class="avatar provider-pic flex-shrink-0 me-3">
												<img src="{{ $user->userDetails->profile_image ?? '' }}" alt="Provider Profile">
											</span>
											<div>
												<div class="rating provider_rate mb-2">
													@if($highestRatedProduct)
														@php
															$avg = (float) $highestRatedProduct->average_rating;
															$full = floor($avg);
															$half = ($avg - $full) >= 0.5;
														@endphp

														@for($i=0;$i<5;$i++)
															@if($i < $full)
																<i class="fa fa-star filled"></i>
															@elseif($i === $full && $half)
																<i class="fa-solid fa-star-half-stroke filled"></i>
															@else
																<i class="fa fa-star"></i>
															@endif
														@endfor
														{{ number_format($avg, 1) }} <span class="d-inline-block">({{ $highestRatedProduct->rating_count ?? 0 }} reviews)</span>
													@else
														<i class="fa fa-star"></i>
														<i class="fa fa-star"></i>
														<i class="fa fa-star"></i>
														<i class="fa fa-star"></i>
														<i class="fa fa-star"></i> 0.0 <span class="d-inline-block">(0 review)</span>
													@endif
												</div>

												<h5 class="d-flex align-items-center mb-1">
													<a href="#">{{ ucfirst($user->userDetails->first_name ?? '') }} {{ ucfirst($user->userDetails->last_name ?? '') }}</a>
													<span class="text-success ms-2"><i class="fa fa-check-circle fs-14"></i></span>
												</h5>

												<div class="d-flex align-items-center flex-wrap row-gap-2">
													<p class="mb-0 fs-14 me-2"><i class="feather feather-grid me-1"></i><span class="category_name">{{ $user->userDetails->category->name ?? 'N/A' }}</span></p>
													<p class="mb-0 fs-14"><i class="ti ti-calendar me-1"></i><span class="date_format">{{ __('Member Since') }} {{ \Carbon\Carbon::parse($user->created_at)->format('M d, Y') }}</span></p>
												</div>
											</div>
										</div>
									</div>
									<div class="col-xl-7">
										<div class="row">
											<div class="col-md-4">
												<div class="provider-bio-info mb-3">
													<h6><i></i>{{ __('Email') }}</h6>
													<p>{{ $maskedEmail }}</p>
												</div>
												<div class="provider-bio-info">
													<h6><i></i>{{ __('Phone Number') }}</h6>
													<p>{{ $maskedPhone }}</p>
												</div>
											</div>
											<div class="col-md-4">
												<div class="provider-bio-info mb-3">
													<h6><i></i>{{ __('Language Known') }}</h6>
													<p>{{ $user->userDetails->language ?? '' }}</p>
												</div>
												<div class="provider-bio-info">
													<h6><i></i>{{ __('Address') }}</h6>
													<p>{{ $user->userDetails->address ?? '' }}</p>
												</div>
											</div>
											<div class="col-md-4">
												<div>
													<a href="/services?provider={{ $user->id }}" class="btn btn-primary w-100 mb-3 provider_id"><i class="feather-calendar me-2"></i>{{ __('Book Service') }}</a>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-8">
						<div class="card">
							<div class="card-body">
								<div class="accordion" id="accordionPanelsStayOpenExample">
									<div class="accordion-item mb-3">
										<div class="accordion-header" id="accordion-headingFour">
											<div class="accordion-button p-0" data-bs-toggle="collapse" data-bs-target="#accordion-collapseFour" aria-expanded="true" aria-controls="accordion-collapseFour" role="button">
												{{__('Our Services')}}
											</div>
										</div>
										<div id="accordion-collapseFour" class="accordion-collapse collapse show" aria-labelledby="accordion-headingFour">
										<div class="accordion-body p-0 mt-3 pb-1">
											<div class="row">
												<div class="col-md-12">
													@if (!empty($products) && count($products) > 0)
														<div class="our-services-slider custom-owl-dot owl-carousel product_details">
															@foreach($products as $product)
																@php
																	$avg = $product->average_rating ?? 0;
																	$full = floor($avg);
																	$half = ($avg - $full) >= 0.5;
																	$serviceImage = $product->image_url && $product->image_url !== 'N/A'
																		? asset('storage/'.$product->image_url)
																		: asset('/front/img/default-placeholder-image.png');
																@endphp

																<div class="card">
																	<div class="card-body">
																		<div class="img-sec w-100 mb-3">
																			<a href="{{ url('/servicedetail/'.$product->slug) }}"><img src="{{ $serviceImage }}" class="img-fluid rounded" alt="img"></a>
																		</div>
																		<div>
																			<h5 class="mb-2 text-truncate">
																				<a href="{{ url('/servicedetail/'.$product->slug) }}">{{ $product->source_name }}</a>
																			</h5>
																			<div class="d-flex justify-content-between align-items-center mb-2">
																				<p class="fs-14 mb-0"><i class="ti ti-map-pin me-2"></i>{{ $user->userDetails->address ?? '' }}</p>
																				<span class="rating text-gray fs-14">
																					@for($i=0;$i<5;$i++)
																						@if($i < $full)
																							<i class="fa fa-star filled"></i>
																						@elseif($i === $full && $half)
																							<i class="fa-solid fa-star-half-stroke filled"></i>
																						@else
																							<i class="fa fa-star"></i>
																						@endif
																					@endfor
																					{{ number_format($avg, 1) }} <span class="d-inline-block">({{ $product->rating_count ?? 0 }} reviews)</span>
																				</span>
																			</div>
																			<div>
																				<span>Price</span>
																				<h6 class="text-primary fs-16 mt-1">{{ $currency ?? '$' }}{{ $product->source_price }}</h6>
																			</div>
																		</div>
																	</div>
																</div>
															@endforeach
														</div>
													@else
														<p class="text-center text-muted">{{ __('No services available') }}</p>
													@endif
												</div>
											</div>
										</div>
										</div>
									</div>
									<div class="accordion-item mb-3 d-none">
										<div class="accordion-header" id="accordion-headingSix">
											<div class="accordion-button p-0" data-bs-toggle="collapse" data-bs-target="#accordion-collapseSix" aria-expanded="true" aria-controls="accordion-collapseSix" role="button">
												{{__('Our Branches')}}
											</div>
										</div>
										<div id="accordion-collapseSix" class="accordion-collapse collapse show" aria-labelledby="accordion-headingSix">
										<div class="accordion-body p-0 mt-3 pb-1">
											<div class="our-branches-slider owl-carousel custom-owl-dot">
												<div class="card shadow-none rounded">
													<div class="card-body text-center px-2">
														<span class="d-block mb-2">
															<img src="{{ asset('/front/img/icons/branch-icon-01.svg') }}" class="w-auto m-auto" alt="Company Image">
														</span>
														<h6 class="mb-2">{{ $user->userDetails->company_name ?? 'N/A' }}</h6>
														<p class="d-flex align-items-center justify-content-center fs-14">
															<i class="ti ti-map-pin me-1"></i>{{ $user->userDetails->company_address ?? 'Address Not Available' }}
														</p>
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
					<div class="col-xl-4 theiaStickySidebar">
						<div class="card shadow-none">
							<div class="card-body lh-1">
								<h4 class="mb-3">{{__('Location')}}</h4>
								@if($mapHasError)
									<div class="text-danger fw-bold fs-12 map-error">
										{{ __('Unable to load the map. Please check the location or contact support.') }}
									</div>
								@else
									<div class="map-iframe">
										<iframe
											allowfullscreen
											loading="lazy"
											referrerpolicy="no-referrer-when-downgrade"
											class="contact-map"
											style="width: 100%; height: 400px; border: 0;"
											src="{{ $googleMapUrl }}">
										</iframe>
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
