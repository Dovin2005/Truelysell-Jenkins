<section class="section category-section bg-white">
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

        <div class="row g-4 row-cols-lg-6 row-cols-md-4 row-cols-sm-3 row-cols-2 justify-content-center">
            @foreach($section['section_content'] as $category)
            <div class="col d-flex">
                <div class="category-item text-center flex-fill wow fadeInUp" data-wow-delay="0.2s">
                    <div class="mx-auto mb-3">
                        <img src="{{ $category->icon }}" class="img-fluid" alt="img">
                    </div>
                    <h6 class="fs-14 mb-1">{{ $category->name }}</h6>
                    <p class="fs-14 mb-0">{{ $category->product_count }}
                        @if ($category->product_count > 1)
                         {{ __('Services') }}
                        @else
                         {{ __('Service') }}
                        @endif
                    </p>
                    <a href="{{route('productlistcategory',$category->slug )}}"
                        class="link-primary text-decoration-underline fs-14">{{ __('View All') }}</a>
                </div>
            </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="text-center view-all wow fadeInUp" data-wow-delay="0.2s">
                    <a href="{{ route('catlist') }}" class="btn btn-dark">{{ __('View All') }}<i
                            class="ti ti-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
        @else
        <h6 class="text-center">{{ __('No category available.') }}</h6>
        @endif
    </div>
</section>