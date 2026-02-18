<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ trim($__env->yieldContent('description')) ?: '' }}">
    <meta name="keywords" content="{{ trim($__env->yieldContent('keywords')) ?: '' }}">
    <meta property="og:image" content="{{ $dynamicLogo }}">
    <title>
        @if (trim($__env->yieldContent('title')))
            @yield('title')
        @else
            {{ $companyName }}
        @endif
    </title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ $dynamicFavicon }}">
    <link rel="icon" href="{{ $dynamicFavicon }}" sizes="any">

    @php
        $isRTL = isRTL(app()->getLocale());
    @endphp

    @if ($isRTL)
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="{{ asset('front/css/bootstrap.rtl.min.css') }}">
    @else
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="{{ asset('front/css/bootstrap.min.css') }}">
    @endif

    <!-- Animation CSS -->
    <link rel="stylesheet" href="{{ asset('front/css/animate.css') }}">

    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="{{ asset('front/plugins/tabler-icons/tabler-icons.css') }}">

    <!-- Fontawesome Icon CSS -->
    <link rel="stylesheet" href="{{ asset('front/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('front/plugins/fontawesome/css/all.min.css') }}">

    <!-- Feather CSS -->
    <link rel="stylesheet" href="{{ asset('front/css/feather.css') }}">
    <!-- Boxicons CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/boxicons/css/boxicons.min.css') }}">

    @include('color.color-configuration')

    <!-- Bootstrap CSS -->
    @if ($isRTL)
        <link rel="stylesheet" href="{{ asset('front/css/stylertl.css?v=1.1') }}">
    @else
        <link rel="stylesheet" href="{{ asset('front/css/stylenew.css?v=2.1') }}">
    @endif

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
</head>

<body data-frontend="{{ Route::currentRouteName() }}" data-lang="{{ app()->getLocale() }}">

    <div class="page-wrapper">
        <div class="row">
            @if ($data['status'] == 1)
                @foreach ($content_sections as $section)
                    @if (isset($section['section_type'], $section['status']) && $section['status'] == 1)
                        @if ($section['section_type'] === 'banner')
                            @include('partials.hero-section', ['section' => $section])
                        @elseif ($section['section_type'] === 'category')
                            @include('partials.category-section', ['section' => $section])
                        @elseif ($section['section_type'] === 'featured_service')
                            @include('partials.service-section', ['section' => $section])
                        @elseif ($section['section_type'] === 'popular_service')
                            @include('partials.popular-section', ['popularSection' => $section])
                        @elseif ($section['section_type'] === 'how_it_works')
                            @include('partials.how-it-work-section', ['section' => $section])
                        @elseif ($section['section_type'] === 'service')
                            @include('partials.preferred-services-section')
                        @elseif ($section['section_type'] === 'popular_provider')
                            @include('partials.popular_provider_section', ['section' => $section])
                        @elseif ($section['section_type'] === 'rated_service')
                            @include('partials.rated_section', ['ratedSection' => $section])
                        @elseif ($section['section_type'] === 'testimonial')
                            @include('partials.testimonial-section', ['section' => $section])
                        @elseif ($section['section_type'] === 'become_provider')
                            @include('partials.provider-section', ['section' => $section])
                        @elseif ($section['section_type'] === 'blog')
                            @include('partials.blog-section', ['section' => $section])
                        @elseif ($section['section_type'] === 'business_with_us')
                            @include('partials.business-section', ['section' => $section])
                        @elseif ($section['section_type'] === 'link')
                            @include('partials.link-section')
                        @elseif ($section['section_type'] === 'about_us')
                            @include('partials.about-section', ['section' => $section])
                        @elseif ($section['section_type'] === 'contact_us')
                            @include('partials.contact-section', ['section' => $section])
                        @elseif ($section['section_type'] === 'terms_conditions')
                            @include('partials.terms-condition-section', ['section' => $section])
                        @elseif ($section['section_type'] === 'privacy_policy')
                            @include('partials.privacy-policy-section', ['section' => $section])
                        @elseif ($section['section_type'] === 'advertisement')
                            @include('partials.advertisement-section')
                        @elseif ($section['section_type'] === 'multiple_section')
                            <section class="section">
                                <div class="container">
                                    <h2 class="text-center">{{ $section['section_title'] }}</h2>
                                    {!! $section['section_content'] !!}
                                </div>
                            </section>
                        @elseif ($section['section_type'] === 'faq')
                            @include('partials.faq-section', ['section' => $section])
                        @endif
                    @endif
                @endforeach
            @endif
        </div>
    </div>

    <div class="back-to-top">
        <a class="back-to-top-icon align-items-center justify-content-center d-flex" href="#top">
            <i class="fa-solid fa-arrow-up"></i>
        </a>
    </div>

    <!-- Cursor -->
    <div class="xb-cursor tx-js-cursor">
        <div class="xb-cursor-wrapper">
            <div class="xb-cursor--follower xb-js-follower"></div>
        </div>
    </div>
    <!-- /Cursor -->

    <!-- Jquery JS -->
    <script src="{{ asset('front/js/jquery-3.7.1.min.js') }}"></script>
</body>

</html>
