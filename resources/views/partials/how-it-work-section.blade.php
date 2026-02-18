<section class="section pt-0 bg-white">
    <div class="container">
        <div class="work-section bg-black m-0">
            <div class="row align-items-center bg-01">
                <div class="col-md-12 wow fadeInUp" data-wow-delay="0.2s">
                    <div class="section-header text-center">
                        <h2 class="mb-1 text-white">
                            {{ $section['section_title_main'] ?? '' }}<span class="text-linear-primary">
                                {{ $section['section_title_last'] ?? '' }}</span>
                        </h2>
                        <p class="text-light">{{ $section['section_label'] ?? '' }}
                        </p>
                    </div>
                </div>
            </div>
            @if(!empty($section['section_content']) && count($section['section_content']) > 0)

            <div class="row gx-0 gy-4">
                @foreach($section['section_content'] as $howitworks)
                <div class="col-lg-12 d-flex">
                    <div class=" text-center flex-fill">
                        <div class="mb-3">
                            <img src="{{ asset('front/img/icons/work-01.svg') }}" alt="img" style="display: none;" loading="lazy">
                        </div>
                        <div>{!! $howitworks->value !!}</div>
                    </div>
                </div>
                @endforeach
            </div>

            @else
            <h6 class="text-center">{{ __('No how it works available.') }}</h6>
            @endif
        </div>
    </div>
</section>