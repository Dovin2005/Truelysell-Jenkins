<?php if($singlevendor=='off') { ?>

<section class="section provide-section bg-black">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 wow fadeInUp" data-wow-delay="0.2s">
                <div class="section-header mb-md-0 mb-3">
                    <p class="sub-title fw-medium text-light mb-1">{{ $section['section_title'] ?? '' }}</p>
                    <h2 class="text-white">{{ $section['section_label'] ?? '' }}<span
                            class="text-linear-primary"></span>
                    </h2>
                </div>
            </div>
            <div class="col-md-6 text-md-end wow fadeInUp" data-wow-delay="0.2s">
                <a href="#!" data-bs-toggle="modal" data-bs-target="#provider"
                    class="btn btn-linear-primary"><i class="ti ti-user-filled me-2"></i>{{ __('Join Us') }}</a>
            </div>
        </div>
    </div>
    <div class="provider-bg1">
        <img src="{{ asset('front/img/bg/provide-bg-01.svg') }}" class="img-fluid" alt="img">
    </div>
    <div class="provider-bg2">
        <img src="{{ asset('front/img/bg/provide-bg-02.svg') }}" class="img-fluid" alt="img">
    </div>
</section>
<?php
}
?>