<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h2 class="breadcrumb-title mb-2">{{__('About Us')}}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item">{{__('Home')}}</li>
                        <li class="breadcrumb-item active" aria-current="page">{{__('About Us')}}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="breadcrumb-bg">
            <img src="{{ asset('front/img/bg/breadcrumb-bg-01.png') }}" class="breadcrumb-bg-1" alt="Img">
            <img src="{{ asset('front/img/bg/breadcrumb-bg-02.png') }}" class="breadcrumb-bg-2" alt="Img">
        </div>
    </div>
</div>

<div class="page-wrapper">
    <div class="content">
        <div class="about-sec bg-white">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="about-img d-none d-md-block">
                            <div class="about-exp">
                                <span>12+ years of experiences</span>
                            </div>
                            <div class="abt-img">
                                <img src="{{ asset('front/img/providers/provider-02.jpg') }}" class="img-fluid"
                                    alt="img">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="about-content">
                            @php
                            $aboutUsContent = null;
                            @endphp

                            @foreach ($content_sections as $section)
                            @if (isset($section['section_type']) && $section['section_type'] === 'about_us' &&
                            !empty($section['about_us']))
                            @php
                            $aboutUsContent = $section['about_us'];
                            @endphp
                            {!! $aboutUsContent !!}
                            @endif
                            @endforeach

                            @if (!$aboutUsContent)
                            <p class="text-center text-black">{{ __('No content available') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>