<section class="section service-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center wow fadeInUp" data-wow-delay="0.2s">
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

        <div class="service-slider owl-carousel nav-center">
            @foreach($section['section_content'] as $featuredservice)
            <div class="service-item wow fadeInUp" data-wow-delay="0.2s">
                <div class="service-img">
                    <div class="img-slider owl-carousel nav-center">
                        @if(!empty($featuredservice->product_images) && is_array($featuredservice->product_images))
                        @foreach(array_slice($featuredservice->product_images, 0, 3) as $image)
                        <div class="slide-images featured-image">
                            <a href="{{ url('servicedetail/' . $featuredservice->slug) }}">
                                <img src="{{ $image }}" class="img-fluid" alt="img" loading="lazy">
                            </a>
                        </div>
                        @endforeach
                        @else
                        <div class="slide-images">
                            <a href="{{ url('servicedetail/' . $featuredservice->slug) }}">
                                <img src="{{ asset('default-image.jpg') }}" class="img-fluid" alt="default img" loading="lazy">
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
                    <h6 class="text-truncate mb-1"><a
                            href="{{ url('servicedetail/' . $featuredservice->slug) }}">{{ $featuredservice->source_name}}</a>
                    </h6>
                    <div class="d-flex align-items-center justify-content-between">
                        <p class="fw-medium fs-14 mb-0">{{ __('Service starts at') }} {{$currency_details->symbol ?? '$'}}
                            {{ $featuredservice->source_price }}</p>
                        @php
                            $rating = $featuredservice->average_rating ?? 0;
                        @endphp

                        <span class="d-inline-flex align-items-center fs-14">

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
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="text-center wow fadeInUp" data-wow-delay="0.2s">
                    <a href="/services" class="btn btn-dark">{{ __('View All') }}<i
                            class="ti ti-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
        @else
        <h6 class="text-center">{{ __('No featured service available.') }}</h6>
        @endif
    </div>
</section>