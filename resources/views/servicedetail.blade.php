@extends('front')
@section('content')
<div class="breadcrumb-bar text-center">
	<div class="container">
		<div class="row">
			<div class="col-md-12 col-12">
				<h2 class="breadcrumb-title mb-2">{{ __('Service Details') }}</h2>
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb justify-content-center mb-0">
						<li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="ti ti-home-2"></i></a></li>
						<li class="breadcrumb-item">{{ __('Services') }}</li>
						<li class="breadcrumb-item active" aria-current="page">{{ __('Service Details') }}</li>
					</ol>
				</nav>
			</div>
		</div>
		<div class="breadcrumb-bg">
			<img src="{{ asset('front/img/bg/breadcrumb-bg-01.png') }}" class="breadcrumb-bg-1" alt="Img">
			<img src="{{ asset('front/img/bg/breadcrumb-bg-02.png') }}" class="breadcrumb-bg-2" alt="Img">
		</div>
	</div>
</div>

<div class="page-wrapper">
	<div class="content">
		<div class="container">
			<div class="row">
				<div class="col-xl-8">
					<div class="card border-0">
						<div class="card-body">
							<div class="service-head mb-2">
								<div class="d-flex align-items-center justify-content-between flex-wrap">
									<h3 class="mb-2"><?php echo $products->source_name ?></h3>
									<span class="badge badge-purple-transparent mb-2"><i class="ti ti-calendar-check me-1"></i>{{$order_product_count}}+ {{ __('Bookings') }}</span>
								</div>
								<div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
									<div class="d-flex align-items-center flex-wrap">
										<p class="me-3 mb-2">
											<i class="ti ti-map-pin me-2"></i>
											{{ optional(App\Models\UserDetail::select('city', 'state', 'country')->where('user_id', $products->user_id)->first())->showfulladdress() ?? '' }}
										</p>
										<p class="mb-2"><i class="ti ti-star-filled text-warning me-2"></i><span class="text-gray-9">{{ $ratings['avg_rating'] ?? 0.0 }} </span>({{ $ratings['total_count'] }} {{ __('reviews') }})</p>
									</div>

									<!-- Share Now Button -->
									<a href="javascript:void(0)" id="shareNowBtn" class="d-flex align-items-center m-0" data-bs-toggle="modal" data-bs-target="#shareModal">
										<i class="ti ti-share me-2 text-gray "></i>
										{{ __('share_now') }}
									</a>

									<!-- Share Modal -->
									<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
										<div class="modal-dialog modal-dialog-centered">
											<div class="modal-content border-0 rounded-4">
												<div class="modal-header">
													<h5 class="modal-title" id="shareModalLabel">{{ __('share_now') }}</h5>
													<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
												</div>
												<div class="modal-body">
													@if (!empty($socialMediaShares) && count($socialMediaShares))
														<div class="d-flex flex-wrap gap-2 justify-content-center">
															@foreach ($socialMediaShares as $share)
																@php
																	$currentUrl = urlencode(url()->current());
																	$platform = strtolower($share->platform_name);

																	switch ($platform) {
																		case 'facebook':
																			$targetUrl = "https://www.facebook.com/sharer/sharer.php?u=$currentUrl";
																			break;
																		case 'twitter':
																			$targetUrl = "https://twitter.com/intent/tweet?url=$currentUrl";
																			break;
																		case 'linkedin':
																			$targetUrl = "https://www.linkedin.com/sharing/share-offsite/?url=$currentUrl";
																			break;
																		case 'whatsapp':
																			$targetUrl = "https://api.whatsapp.com/send?text=$currentUrl";
																			break;
																		case 'telegram':
																			$targetUrl = "https://t.me/share/url?url=$currentUrl";
																			break;
																		default:
																			$targetUrl = $share->url ?? "#";
																	}
																@endphp

																<a href="{{ $targetUrl }}" target="_blank" class="rounded-circle d-flex align-items-center justify-content-center"
																title="Share on {{ ucfirst($platform) }}"
																style="width: 40px; height: 40px; background-color: #3b5998;">
																	<i class="{{ $share->icon }} text-white" style="font-size: 18px;"></i>
																</a>
															@endforeach
														</div>
													@else
														<p class="text-center text-muted">{{ __('No Service Links Available') }}</p>
													@endif
												</div>
											</div>
										</div>
									</div>
									<div class="d-flex align-items-center flex-wrap">
										<p class="me-3 mb-2"><i class="ti ti-eye me-2"></i>{{$products->views}} {{ __('Views') }}</p>
									</div>
								</div>
							</div>


							<div class="service-wrap mb-4">
								<div class="slider-wrap">
									<div class="owl-carousel service-carousel nav-center mb-3" id="large-img">
										@if($productImages && $productImages->count())
										@foreach($productImages as $image)
										@if (file_exists(public_path('storage/'. $image)))
										<img src="{{ asset('storage/' . $image) }}" class="img-fluid img_one" alt="Slider Img">
										@else
										<img src="{{ asset('front/img/default-placeholder-image.png') }}" class="img-fluid" alt="Default Slider Img">
										@endif
										@endforeach
										@else
										<img src="{{ asset('front/img/default-placeholder-image.png') }}" class="img-fluid" alt="Default Slider Img">
										@endif
									</div>
								</div>
								<div class="owl-carousel slider-nav-thumbnails nav-center" id="small-img">
									@if($productImages && $productImages->count())
									@foreach($productImages as $image)
									@if (file_exists(public_path('storage/'. $image)))
									<div><img src="{{ asset('storage/' . $image) }}" class="img-fluid img_two" alt="Slider Img"></div>
									@else
									<div><img src="{{ asset('front/img/default-placeholder-image.png') }}" alt="img"></div>
									@endif
									@endforeach
									@else
									<div><img src="{{ asset('front/img/default-placeholder-image.png') }}" alt="img"></div>
									@endif
								</div>
							</div>

							<div class="accordion service-accordion">
								<div class="accordion-item mb-4">
									<h2 class="accordion-header">
										<button class="accordion-button p-0" type="button" data-bs-toggle="collapse" data-bs-target="#overview" aria-expanded="false">
											{{ __('Service Overview') }}
										</button>
									</h2>
									<div id="overview" class="accordion-collapse collapse show">
										<div class="accordion-body border-0 p-0 pt-3">
											<div class="more-text">
												<p>{!! $products->source_description !!} </p>

											</div>
											<a href="javascript:void(0);" class="link-primary text-decoration-underline more-btn mb-4">{{ __('Read More') }}</a>
											<?php
											$imageard = [];
											if ($product_offeerservice_count != 0) {
												$aim = 0;
												foreach ($productServices as $productServicesValues) {
													$imageard[$aim] = $productServicesValues->image;
													$aim++;
												}

											?>
												<div class="bg-light-200 p-3 offer-wrap">
													<h4 class="mb-3">{{ __('Services Offered') }}</h4>
													<?php

													$products_servicename = unserialize($products_details2->source_Values);
													$products_servieprice = unserialize($products_details3->source_Values);
													$products_servicedesc = unserialize($products_details4->source_Values);

													$i = 0;
													foreach ($products_servicename as $offerValues) {
													?>

														<!--
														<div class="d-flex align-items-center justify-content-between mb-3">
															<h6 class="fs-16 fw-medium mb-0"> <?php echo $offerValues; ?>
															<p>$<?php echo $products_servieprice[$i]; ?></p>
														</div>
													-->
														<div class="offer-item d-md-flex align-items-center justify-content-between bg-white mb-2">
															<div class="d-sm-flex align-items-center mb-2">
																<?php
																if (count($imageard) != 0) {
																?>
																	<span class="avatar avatar-lg flex-shrink-0 me-2 mb-2">
																		<img src="{{ asset('storage/' . $imageard[$i]) }}" alt="img" class="br-10">
																	</span>
																<?php
																}
																?>
																<div class="mb-2">
																	<h6 class="fs-16 fw-medium"><?php echo $offerValues; ?></h6>
																	<p class="fs-14"><?php echo $products_servicedesc[$i]; ?></p>
																</div>
															</div>
															<div class="pb-3">
																<h6 class="fs-16 fw-medium text-primary mb-0">{{$currecy_details->symbol}}<?php echo $products_servieprice[$i]; ?></h6>
															</div>
														</div>
													<?php
														$i++;
													}

													?>
												</div>
											<?php  } ?>
											<div class="bg-light-200 p-3 offer-wrap" style="display:none">
												<?php if ($product_offeerservice_count != 0) { ?>

													<h4 class="mb-3">{{ __('Services Offered') }}</h4>
													<?php


													$products_servicename = unserialize($products_details2->source_Values);
													$products_servieprice = unserialize($products_details3->source_Values);
													$products_servicedesc = unserialize($products_details4->source_Values);
													$i = 0;
													foreach ($products_servicename as $offerValues) {
													?>
														<div class="offer-item d-md-flex align-items-center justify-content-between bg-white mb-2">
															<div class="d-sm-flex align-items-center mb-2">
																<span class="avatar avatar-lg flex-shrink-0 me-2 mb-2">
																	<img src="" alt="img" class="br-10">
																</span>
																<div class="mb-2">
																	<h6 class="fs-16 fw-medium"><?php echo $offerValues; ?></h6>
																	<p class="fs-14"><?php echo $products_servicedesc[$i]; ?></p>
																</div>
															</div>
															<div class="pb-3">
																<h6 class="fs-16 fw-medium text-primary mb-0">{{$currecy_details->symbol}}<?php echo $products_servieprice[$i]; ?></h6>
															</div>
														</div>
												<?php
														$i++;
													}
												}
												?>



											</div>
										</div>
									</div>
								</div>

								<div class="accordion-item mb-4" <?php if (count($includedServices) == 1) { ?> style="display:none" <?php } ?>>
									<h2 class="accordion-header">
										<button class="accordion-button p-0" type="button" data-bs-toggle="collapse" data-bs-target="#include" aria-expanded="false">
											{{ __('Includes') }}
										</button>
									</h2>
									<div id="include" class="accordion-collapse collapse show">
										<div class="accordion-body border-0 p-0 pt-3">
											<div class="bg-light-200 p-3 pb-2 br-10">
												@foreach($includedServices as $service)
												<p class="d-inline-flex align-items-center mb-2 me-4">
													<i class="feather-check-circle text-success me-2"></i>{{ $service }}
												</p>
												@endforeach
											</div>
										</div>
									</div>
								</div>

								<div class="accordion-item mb-4">
									<h2 class="accordion-header">
										<button class="accordion-button p-0" type="button" data-bs-toggle="collapse" data-bs-target="#gallery" aria-expanded="false">
											{{ __('Gallery') }}
										</button>
									</h2>
									<div id="gallery" class="accordion-collapse collapse show">
										<div class="accordion-body border-0 p-0 pt-3">
											<div class="gallery-slider owl-carousel nav-center gallery-img">
												@if($productImages && $productImages->count())
												@foreach($productImages as $image)
												@if (file_exists(public_path('storage/'. $image)))
												<a href="{{ asset('storage/' . $image) }}" data-fancybox="gallery" class="gallery-item">
													<img src="{{ asset('storage/' . $image) }}" alt="img">
												</a>
												@else
												<a href="{{ asset('front/img/default-placeholder-image.png') }}" data-fancybox="gallery" class="gallery-item">
													<img src="{{ asset('front/img/default-placeholder-image.png') }}" alt="img">
												</a>
												@endif
												@endforeach
												@else
												<a href="{{ asset('front/img/default-placeholder-image.png') }}" data-fancybox="gallery" class="gallery-item">
													<img src="{{ asset('front/img/default-placeholder-image.png') }}" alt="img">
												</a>
												@endif
											</div>
										</div>
									</div>
								</div>
								@if ($videolink != "")
									@php
										preg_match('/(?:youtu\.be\/|v=)([^&?]+)/', $videolink, $matches);
										$videoId = $matches[1] ?? null;
										$thumbnail = $videoId ? "https://img.youtube.com/vi/$videoId/maxresdefault.jpg" : null;
									@endphp
									<div class="accordion-item mb-4">
										<h2 class="accordion-header">
											<button class="accordion-button p-0" type="button" data-bs-toggle="collapse" data-bs-target="#video" aria-expanded="false">
												{{ __('Video') }}
											</button>
										</h2>
										<div id="video" class="accordion-collapse collapse show">
											<div class="accordion-body border-0 p-0 pt-3">
												<div class="video-wrap" @if ($thumbnail) style="background-image: url('{{ $thumbnail }}'); background-size: cover; "@endif>
													<a class="video-btn video-effect" data-fancybox="" href="{{$videolink}}">
														<i class="fa-solid fa-play"></i>
													</a>
												</div>
											</div>
										</div>
									</div>
								@endif
							</div>
						</div>
					</div>
					<div class="card border-0 mb-xl-0 mb-4">
						<div class="card-body" id="review_container" data-is_allow_reply="{{ isAllowReply($products->id, Auth::user()->id ?? '') }}">
							<div class="d-flex align-items-center justify-content-between flex-wrap">
								<h4 class="mb-3">{{ __('Reviews') }} (<span class="total_review_count">{{ $ratings['total_count'] }}</span>)</h4>
							</div>
							<div class="row align-items-center">
								<div class="col-md-5">
									<div class="rating-item bg-light-500 text-center mb-3">
										<h5 class="mb-3">{{ __('Customer Reviews & Ratings') }}</h5>
										<div class="d-inline-flex align-items-center justify-content-center" id="stars_container">
											@for ($i = 1; $i <= 5; $i++)
												@if ($i <=floor($ratings['avg_rating']))
												<i class="ti ti-star-filled text-warning me-1"></i>
												@elseif ($i == ceil($ratings['avg_rating']) && $ratings['avg_rating'] > floor($ratings['avg_rating']))
												<i class="ti ti-star-half-filled text-warning me-1"></i>
												@else
												<i class="ti ti-star text-warning me-1"></i>
												@endif
												@endfor
										</div>
										<p class="mb-3">(<span id="avg_rating">{{ $ratings['avg_rating'] ?? '0.0' }}</span> {{ __('out of') }} 5.0)</p>
										<p class="text-gray-9">{{ __('basedon') }} <span class="total_review_count"> {{ $ratings['total_count'] }}</span> {{ __('Reviews') }}</p>
									</div>
								</div>
								<div class="col-md-7">
									<div class="rating-progress mb-3" id="review_progress_container">
										@if ($ratings['star_rating'])
										@foreach (range(5, 1) as $star)
										@php
										$starCount = $ratings['star_rating']['star' . $star] ?? 0;
										$percentage = ($ratings['total_count'] > 0) ? ($starCount / $ratings['total_count']) * 100 : 0;
										@endphp
										<div class="d-flex align-items-center mb-2">
											<p class="me-2 text-nowrap mb-0">{{ $star }} {{ __('Star Ratings') }}</p>
											<div class="progress w-100" role="progressbar" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
												<div class="progress-bar bg-warning" style="width: {{ $percentage }}%"></div>
											</div>
											<p class="progress-count ms-2">{{ $starCount }}</p>
										</div>
										@endforeach
										@endif
									</div>
								</div>
							</div>
							<div class="list-reviews">
							</div>
							<div class="text-center">
								<a href="javascript:void(0);" class="btn btn-light btn-sm d-none" id="load_more_reviews">{{ __('load_more') }}</a>
							</div>
						</div>
					</div>
				</div>

				<div class="col-xl-4 theiaStickySidebar">
					<div class="card border-0">
						<div class="card-body">
							<div class="d-flex align-items-center justify-content-between border-bottom mb-3">
								<div class="d-flex align-items-center">
									<div class="mb-3">
										<h4><span class="display-6 fw-bold">{{$currecy_details->symbol}}{{ $products_details1->source_Values }}</span> / <span class="text-default">{{ formatServicePriceType($products_details1->source_key ?? '', app()->getLocale()) }}</span></h4>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- my booking -->
					<input type="hidden" id="product_slug" value="{{ $products->slug }}">
					@if (Auth::check())
					<?php
					if (auth()->user()->user_type == 3) {
					?>
						<button id="bookServiceButton" class="btn btn-lg btn-primary w-100 d-flex align-items-center justify-content-center mb-3 book-btn">
							<i class="ti ti-calendar me-2"></i>{{ __('Book Service') }}
						</button>
					<?php } ?>
					<?php
					if (auth()->user()->user_type == 2 && auth()->user()->id == $products->user_id) {
					?>
						<a href="{{ route('provider.service') }}">
							<button type="button" class="btn btn-lg btn-primary w-100 d-flex align-items-center justify-content-center mb-3"><i class="ti ti-calendar me-2"></i>{{__('My Service')}} </button>
						</a>
					<?php } ?>
					@else
						<button id="bookServiceButton" class="btn btn-lg btn-primary w-100 d-flex align-items-center justify-content-center mb-3 book-btn">
							<i class="ti ti-calendar me-2"></i>{{ __('Book Service') }}
						</button>
					@endif
					<div class="card border-0">
						<div class="card-body">
							<h4 class="mb-3">{{ __('Service Provider') }}</h4>
							<div class="provider-info text-center bg-light-500 p-3 mb-3">
								<div class="avatar avatar-xl mb-3">
									<img src="{{ optional(App\Models\UserDetail::select('first_name', 'last_name', 'profile_image')->where('user_id', $products->user_id)->first())->showprofilepic() ?? asset('assets/img/profile-default.png') }}"
										alt="img" class="img-fluid rounded-circle">
									<span class="service-active-dot"><i class="ti ti-check"></i></span>
								</div>
								<h5>{{ ucfirst(optional($user_details)->first_name ?? '') }} {{ optional($user_details)->last_name ?? '' }}</h5>
								<p class="fs-14"><i class="ti ti-star-filled text-warning me-2"></i><span class="text-gray-9 fw-semibold">{{ $ratings['avg_rating'] ?? 0.0 }}</span> ({{ $ratings['total_count'] }} {{ __('reviews') }})</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3">
								<h6 class="fs-16 fw-medium mb-0"><i class="ti ti-user text-default me-2"></i>{{ __('Member Since') }}</h6>
								<p>{{ optional($user_details)->created_at ? \Carbon\Carbon::parse($user_details->created_at)->format('Y') : '-' }}</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3">
								<h6 class="fs-16 fw-medium mb-0"><i class="ti ti-map-pin me-1"></i>{{ __('Address') }}</h6>
								<p>{{ optional(App\Models\UserDetail::select('city', 'state', 'country')->where('user_id', $products->user_id)->first())->showfulladdress() ?? '-' }}</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3">
								<h6 class="fs-16 fw-medium mb-0"><i class="ti ti-mail me-1"></i>{{ __('Email') }}</h6>
								<p>XXXX@XXXX.com</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3">
								<h6 class="fs-16 fw-medium mb-0"><i class="ti ti-phone me-1"></i>{{ __('Phone') }}</h6>
								<p>+X XXX XXX XXXX</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3">
								<h6 class="fs-16 fw-medium mb-0"><i class="ti ti-file-text me-1"></i>{{ __('No of Listings') }}</h6>
								<p>{{$user_product_count}}</p>
							</div>
							<div class="d-flex align-items-center justify-content-between mb-3">
								<h6 class="fs-16 fw-medium mb-0"><i class="ti ti-file-text me-1"></i>{{ __('Social Profiles') }}</h6>
								@if(!empty($merged_social_links) && $merged_social_links->count() > 0)
									<div class="social-icon d-flex align-items-center gap-2">
										@foreach($merged_social_links as $socialLink)
											@if(!empty($socialLink->link) && !empty($socialLink->icon))
												<a href="{{ $socialLink->link }}"
												target="_blank"
												style="font-size: 20px; color: {{ $loop->iteration % 2 === 0 ? '#ff4081' : '#3b5998' }};"
												aria-label="{{ $socialLink->platform_name ?? 'Social link' }}">
													<i class="{{ $socialLink->icon }}"></i>
												</a>
											@endif
										@endforeach
									</div>
								@endif
							</div>
						</div>
					</div>

					<div class="card border-0">
						<div class="card-body">
							<h4 class="mb-3">{{ __('Location') }}</h4>
							<div class="map-wrap">
								@if($mapHasError)
								<div class="text-danger fw-bold fs-12">
									{{ __('Unable to load the map. Please check the location or contact support.') }}
								</div>
								@else
								<iframe
									src="{{ $googleMapUrl }}"
									allowfullscreen
									loading="lazy"
									referrerpolicy="no-referrer-when-downgrade"
									class="contact-map"
									style="width: 100%; height: 400px; border: 0;">
								</iframe>
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="review-not-allowed-modal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header d-flex align-items-center justify-content-between">
				<h5>{{ __('add_review') }}</h5>
				<a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
			</div>
			<div class="modal-body">
				<p class="text-danger">{{ __('You can only leave a review after the order for this service is completed.') }}</p>
			</div>
			<div class="modal-footer d-flex align-items-center justify-content-end">
				<a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="reply-not-allowed-modal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header d-flex align-items-center justify-content-between">
				<h5>{{ __('Reply') }}</h5>
				<a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
			</div>
			<div class="modal-body">
				<p class="text-danger">{{ __('You can only leave a reply after the order for this service is completed.') }}</p>
			</div>
			<div class="modal-footer d-flex align-items-center justify-content-end">
				<a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
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
					<input type="hidden" class="product_id" value="{{ $products->id }}">
					<input type="hidden" id="user_id" value="{{ Auth::id() }}">
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

@endsection