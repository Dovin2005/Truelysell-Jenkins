<section class="section blog-section bg-white">
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

        <div class="blog-slider owl-carousel nav-center nav-center" data-blog-count="{{ count($section['section_content']) }}">
            @foreach($section['section_content'] as $blog)
            <div class="blog-item wow fadeInUp" data-wow-delay="0.2s">
                <div class="blog-img blog-details">
                    <a href="{{ url('blog-details/' . $blog->slug) }}">
                        <img src="{{ $blog->image }}" class="img-fluid" alt="img" loading="lazy">
                    </a>
                </div>
                <div class="blog-content">
                    <p class="fs-14 fw-meium text-gray-9 d-inline-flex align-items-center mb-2">{{ __('Admin') }}<i
                            class="ti ti-circle-filled fs-6 mx-1"></i>{{ \Carbon\Carbon::parse($blog->updated_at)->format('j M Y') }}
                    </p>
                    <h6 class="text-truncate mb-2"><a
                            href="{{ url('blog-details/' . $blog->slug) }}">{{ $blog->title }}</a>
                    </h6>
                    <p class="two-line-ellipsis mb-3">{{ strip_tags(html_entity_decode($blog->description)) }}</p>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center wow fadeInUp" data-wow-delay="0.2s">
            <a href="{{ route('blog-list') }}" class="btn btn-dark">{{ __('View All') }}<i
                    class="ti ti-arrow-right ms-2"></i></a>
        </div>
        @else
        <h6 class="text-center">{{ __('No recent blogs available.') }}</h6>
        @endif
    </div>
</section>