<section class="section pt-0 pb-0 bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12 text-center wow fadeInUp" data-wow-delay="0.2s">
                <div class="section-header text-center">
                    <h2 class="mb-1">
                        {{ $section['section_title_main'] ?? '' }}<span class="text-linear-primary">
                            {{ $section['section_title_last'] ?? '' }}</span>
                    </h2>
                    <p class="sub-title">{{ $section['section_label'] ?? '' }}</p>
                </div>
            </div>
        </div>
        @if(!empty($section['section_content']) && count($section['section_content']) > 0)

        <div class="popular-slider-3 owl-carousel nav-center">
            @foreach($section['section_content'] as $featuredservice)
            <div class="service-item">
                <div class="service-img">
                    <div class="img-slider owl-carousel nav-center">
                        @if(!empty($featuredservice->product_images) && is_array($featuredservice->product_images))
                        @foreach(array_slice($featuredservice->product_images, 0, 3) as $image)
                        <div class="slide-images prefered_image">
                            <a href="{{ url('servicedetail/' . $featuredservice->slug) }}">
                                <img src="{{ $image }}" class="img-fluid" alt="img" loading="lazy">
                            </a>
                        </div>
                        @endforeach
                        @else
                        <div class="slide-images">
                            <a href="{{ url('servicedetail/' . $featuredservice->slug) }}">
                                <img src="{{ asset('front/img/default-placeholder-image.png') }}" class="img-fluid" alt="default img" loading="lazy">
                            </a>
                        </div>
                        @endif
                    </div>
                    <div class="trend-icon">
                        <span class="bg-success">
                            <i class="feather-trending-up"></i>
                        </span>
                    </div>
                    <div class="fav-item">
                        <a href="#!" class="fav-icon">
                            <i class="ti ti-heart"></i>
                        </a>
                    </div>
                </div>
                <div class="service-content">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-2 text-truncate"><a
                                href="{{ url('servicedetail/' . $featuredservice->slug) }}">{{ $featuredservice->source_name}}</a>
                        </h6>
                        <small class="mb-2">{{ __('From') }}
                            {{$currency_details->symbol ?? '$'}}{{ $featuredservice->source_price }}</small>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        @php
                            $rating = $featuredservice->average_rating ?? 0;
                        @endphp

                        <p class="fs-14 mb-0">
                            @if ($rating >= 1)
                                {{-- Filled star --}}
                                <i class="ti ti-star-filled text-warning me-1"></i>

                            @elseif ($rating >= 0.5)
                                {{-- Half star --}}
                                <i class="ti ti-star-half-filled text-warning me-1"></i>

                            @else
                                {{-- Empty star --}}
                                <i class="ti ti-star text-warning me-1"></i>
                            @endif

                            {{ number_format($rating, 1) }}
                            ({{ $featuredservice->review_count }} {{ __('Reviews') }})
                        </p>

                        <span class="badge badge-dark-transparent fw-medium p-2">{{ $featuredservice->booking_count}}
                            {{ __('Bookings') }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @else
        <h6 class="text-center">{{ __('No preferred service available.') }}</h6>
        @endif
    </div>
</section>