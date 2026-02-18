<?php if($singlevendor=='off') { ?>

<section class="section pt-0 bg-white">
    <div class="container">
        <div class="provider-sec">
            <div class="row justify-content-center">
                <div class="col-lg-12 text-center wow fadeInUp" data-wow-delay="0.2s">
                    <div class="section-header text-center">
                        <h2 class="mb-1">{{ __('Popular') }} <span
                                class="text-linear-primary">{{ __('Providers') }}</span></h2>
                        <p class="sub-title">
                            {{ __('Each listing is designed to be clear and concise, providing customers') }}
                        </p>
                    </div>
                </div>
            </div>
            @if(!empty($section['section_content']) && count($section['section_content']) > 0)

            @php
                $providers = collect($section['section_content'])
                    ->sortByDesc(fn ($item) => $item['promotedJobber'] ?? 0)
                    ->values();
            @endphp

            <div class="row align-items-start">
            @foreach($providers as $provider)

            @php
                $isPromoted = !empty($provider['promotedJobber']) && $provider['promotedJobber'] == 1;
                $isFeatured = !empty($provider['featured']) && $provider['featured'] == 1;
            @endphp

            <div class="col-xl-3 col-lg-4 col-md-6 position-relative provider-card {{ $isPromoted ? 'promoted-provider' : '' }}">
                @if($isPromoted)
                    <div class="recommended-badge-provider">{{ __('recommended') }}</div>
                @endif
                <div class="card position-relative overflow-hidden">
                    <div class="card-body">
                        @if ($isFeatured)
                            <div class="feature-text">
                                <span class="bg-danger">{{ __('featured') }}</span>
                            </div>
                        @endif

                        {{-- IMAGE --}}
                        <div class="card-img card-provider-img card-img-hover mb-3">
                            <a href="/{{ $provider['user_slug'] ?? '' }}"
                            class="provider-details-link"
                            data-provider-id="{{ $provider['provider_id'] }}">
                                @if(!empty($provider['profile_image']) && file_exists(public_path('storage/profile/' . $provider['profile_image'])))
                                    <img src="{{ url('storage/profile/' . $provider['profile_image']) }}"
                                        class="img-fluid" alt="Profile">
                                @else
                                    <img src="{{ asset('assets/img/profile-default.png') }}"
                                        class="img-fluid" alt="Default">
                                @endif
                            </a>
                        </div>

                        {{-- CONTENT --}}
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <h5 class="d-flex align-items-center mb-1">
                                        <a href="/{{ $provider['user_slug'] ?? '' }}"
                                        class="provider-details-link"
                                        data-provider-id="{{ $provider['provider_id'] }}">
                                            {{ ucfirst(strtolower($provider['provider_name'] ?? '')) }}
                                        </a>

                                        {{-- VERIFIED --}}
                                        @if (!empty($provider['badge']) && $provider['badge'] == 1)
                                            <span class="text-success ms-2">
                                                <i class="fa fa-check-circle"></i>
                                            </span>
                                        @endif
                                    </h5>

                                    <span>{{ $provider['category_name'] ?? '' }}</span>
                                </div>

                                @if (!empty($provider['hourly_rate']))
                                    <p class="fs-18 fw-medium text-dark">
                                        {{ $currency_details->symbol ?? '$' }}{{ $provider['hourly_rate'] }}
                                        <span class="fw-normal fs-13 text-default">/hr</span>
                                    </p>
                                @endif
                            </div>

                            {{-- RATING --}}
                            <div class="rating d-flex align-items-center justify-content-between">
                                <div class="rating-stars d-flex align-items-center">
                                    @php
                                        $rating = (float)($provider['average_rating'] ?? 0);
                                        $fullStars = floor($rating);
                                        $halfStar = ($rating - $fullStars) >= 0.5;
                                    @endphp

                                    @for ($i = 0; $i < $fullStars; $i++)
                                        <i class="fas fa-star filled"></i>
                                    @endfor

                                    @if ($halfStar)
                                        <i class="fas fa-star-half-alt filled"></i>
                                    @endif

                                    @for ($i = 0; $i < (5 - $fullStars - ($halfStar ? 1 : 0)); $i++)
                                        <i class="far fa-star text-warning"></i>
                                    @endfor

                                    <span class="ms-2">
                                        {{ number_format($rating, 1) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            @endforeach
            </div>

            <div class="text-center view-all">
                <a href="{{ route('user.providerlist') }}" class="btn btn-dark">
                    {{ __('View All') }} <i class="ti ti-arrow-right ms-2"></i>
                </a>
            </div>

            @else
            <h6 class="text-center">{{ __('No popular providers available.') }}</h6>
            @endif
        </div>
    </div>
</section>
<?php } ?>