<section class="section testimonial-section bg-white">
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

        <div class="testimonial-slider owl-carousel nav-center">
            @foreach($section['section_content'] as $testimonial)
            <div class="testimonial-item wow fadeInUp" data-wow-delay="0.2s">
                <div class="d-flex align-items-center mb-3">
                    <i class="fa-solid fa-star text-warning me-1"></i>
                    <i class="fa-solid fa-star text-warning me-1"></i>
                    <i class="fa-solid fa-star text-warning me-1"></i>
                    <i class="fa-solid fa-star text-warning me-1"></i>
                    <i class="fa-solid fa-star text-warning"></i>
                </div>
                <p class="mb-2 text-truncate-testimonial">{{ $testimonial->description }}</p>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center overflow-hidden">
                        <span class="avatar avatar-lg flex-shrink-0">
                            <img src="{{ $testimonial->client_image }}" class="img-fluid rounded-circle" alt="img" loading="lazy">
                        </span>
                        <h6 class="text-truncate ms-2">{{ $testimonial->client_name }}</h6>
                    </div>
                    <p>{{ \Carbon\Carbon::parse($testimonial->updated_at)->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center wow fadeInUp" data-wow-delay="0.2s">
            <h6 class="mb-2">{{ __('Each listing is designed to be clear and concise, providing customers') }}</h6>
            <p>
                <span class="text-dark fw-medium">{{ __('Excellent') }}</span>
                <img src="{{ asset('front/img/icons/star-01.svg') }}" class="img-fluid" alt="img" loading="lazy">
                <img src="{{ asset('front/img/icons/star-01.svg') }}" class="img-fluid" alt="img" loading="lazy">
                <img src="{{ asset('front/img/icons/star-01.svg') }}" class="img-fluid" alt="img" loading="lazy">
                <img src="{{ asset('front/img/icons/star-01.svg') }}" class="img-fluid" alt="img" loading="lazy">
                <img src="{{ asset('front/img/icons/star-01.svg') }}" class="img-fluid" alt="img" loading="lazy">
                <span class="fs-14">{{ __('basedon') }} 8 {{ __('reviews') }}</span>
            </p>
        </div>
        @else
        <h6 class="text-center">{{ __('No customers review available.') }}</h6>
        @endif
    </div>
</section>