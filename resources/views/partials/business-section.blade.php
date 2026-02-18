<!-- Business Section -->
<section class="section business-section bg-black">
    <div class="container">
        <div class="row align-items-center bg-01">
            <div class="col-md-6 wow fadeInUp" data-wow-delay="0.2s">
                <div class="section-header mb-md-0 mb-4">
                    <h2 class="text-white display-4">{{ $section['section_title'] ?? '' }} <span
                            class="text-linear-primary"></span></h2>
                    <p class="text-light">{{ $section['section_label'] ?? '' }}</p>
                    <a href="#!" data-bs-toggle="modal" data-bs-target="#provider"
                        class="btn btn-linear-primary"><i class="ti ti-user-filled me-2"></i>{{ __('Join Us') }}</a>
                </div>
            </div>
            <div class="col-md-6 text-md-end wow fadeInUp" data-wow-delay="0.2s">
                <div class="business-img">
                    <img src="{{ asset('front/img/business.webp') }}" class="img-fluid" alt="img" loading="lazy">
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /Business Section -->