<section class="section rated-section">
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
            <li class="nav-item mb-3">
                <a class="nav-link {{ $index === 0 ? 'active' : '' }}" href=".{{ Str::slug($category->name, '-') }}"
                    data-bs-toggle="tab">
                    {{ $category->name }}
                </a>
            </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach ($categories as $index => $category)
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }} {{ Str::slug($category->name, '-') }}">
                <div class="row g-4">
                    @php
                    // Filter the rated services for the current category
                    $filteredRatedServices = collect($ratedSection['section_content'])->filter(function($service) use
                    ($category) {
                    return $service->source_category == $category->id;
                    });
                    @endphp

                    @if ($filteredRatedServices->isEmpty())
                    <p class="text-center fw-bold mb-5">{{ __('No high rated services found for this category.') }}</p>
                    @else
                    @foreach ($filteredRatedServices as $ratedService)
                    <div class="col-md-6 col-lg-3 wow fadeInUp rated-img">
                        <a href="{{ url('servicedetail/' . $ratedService->slug) }}" class="rated-wrap">
                            @if (!empty($ratedService->product_images) && is_array($ratedService->product_images))
                            @foreach (array_slice($ratedService->product_images, 0, 1) as $image)
                            <img src="{{ asset($image) }}" alt="img" class="img-fluid" loading="lazy">
                            @endforeach
                            @endif
                            <h6 class="text-white">{{ $ratedService->source_name }}</h6>
                        </a>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center view-all wow fadeInUp" data-wow-delay="0.2s">
            <a href="/services" class="btn btn-dark">{{ __('View All') }}<i class="ti ti-arrow-right ms-2"></i></a>
        </div>

        @else
        <h6 class="text-center">{{ __('No high rated service available.') }}</h6>
        @endif
    </div>
</section>