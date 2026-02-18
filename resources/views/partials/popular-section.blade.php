<section class="section popular-section bg-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center wow fadeInUp" data-wow-delay="0.2s">
                <div class="section-header text-center mb-4">
                    <h2 class="mb-1">
                        {{ $section['section_title_main'] ?? '' }}<span class="text-linear-primary">
                            {{ $section['section_title_last'] ?? '' }}</span>
                    </h2>
                    <p class="sub-title">{{ $section['section_label'] ?? '' }}</p>
                </div>
            </div>
        </div>
        @if(!empty($section['section_content']) && count($section['section_content']) > 0)

        <ul class="nav nav-tabs nav-tabs-solid justify-content-center mb-4">
            @foreach ($categories as $index => $category)
                <li class="nav-item mb-2">
                    <a class="nav-link {{ $index === 0 ? 'active' : '' }}"
                      href="#{{ Str::slug($category->name, '-') }}"
                      data-bs-toggle="tab">
                        {{ $category->name }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
        @foreach ($categories as $index => $category)
        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
            id="{{ Str::slug($category->name, '-') }}">

            @php
                $filteredServices = collect($popularSection['section_content'])
                    ->filter(fn ($service) => $service->source_category == $category->id);

                $filteredServices = $filteredServices
                    ->sortByDesc(fn ($service) => $service->promotedService ?? 0)
                    ->values();
            @endphp

            @if ($filteredServices->isEmpty())
                <p class="text-center fw-bold mb-5">
                    {{ __('No popular services found for this category.') }}
                </p>
            @else

            <div class="feature-slider owl-carousel nav-center">
                @foreach ($filteredServices as $popularService)
                <div class="service-item wow fadeInUp" data-wow-delay="0.1s">

                    <div class="service-img position-relative provider-card">

                        @if (!empty($popularService->promotedService) && $popularService->promotedService == 1)
                            <div class="feature-text">
                                <span class="bg-warning text-dark">
                                    Promoted
                                </span>
                            </div>
                        @endif

                        <div class="img-slider owl-carousel nav-center">
                            <div class="slide-images popular-image">
                                @if (!empty($popularService->product_images) && is_array($popularService->product_images))
                                    @foreach (array_slice($popularService->product_images, 0, 1) as $image)
                                        <a href="{{ url('servicedetail/' . $popularService->slug) }}">
                                            <img src="{{ asset($image) }}"
                                                alt="img"
                                                class="img-fluid"
                                                loading="lazy">
                                        </a>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="fav-item d-flex align-items-center justify-content-end w-100">
                            <a href="#!" class="fav-icon">
                                <i class="ti ti-heart"></i>
                            </a>
                        </div>

                    </div>

                    <div class="service-content">
                        <h6 class="mb-1 text-truncate">
                            <a href="{{ url('servicedetail/' . $popularService->slug) }}">
                                {{ $popularService->source_name }}
                            </a>
                        </h6>

                        <div class="d-flex align-items-center justify-content-between">
                            <p class="fs-14 mb-0">
                                <i class="ti ti-star-filled text-warning me-1"></i>
                                {{ $popularService->average_rating }}
                                ({{ $popularService->review_count }} {{ __('Reviews') }})
                            </p>
                            <small>
                                {{ __('From') }}
                                {{ $currency_details->symbol ?? '$' }}{{ $popularService->source_price }}
                            </small>
                        </div>
                    </div>

                </div>
                @endforeach
            </div>

            @endif
        </div>
        @endforeach
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="text-center wow fadeInUp" data-wow-delay="0.2s">
                    <a href="/services" class="btn btn-dark">
                        {{ __('View All') }}
                        <i class="ti ti-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
        @else
        <h6 class="text-center">{{ __('No popular service available.') }}</h6>
        @endif
    </div>
</section>