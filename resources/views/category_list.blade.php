@extends('front')

@section('content')

<!-- Breadcrumb -->
<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h2 class="breadcrumb-title mb-2">{{__('Categories')}}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="ti ti-home-2"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{__('Categories')}}</li>
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
<!-- /Breadcrumb -->

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content content-two">
        <div class="container">
            <section class="section category-section bg-white p-0">
                <div class="container">
                    <div class="row g-4 row-cols-lg-6 row-cols-md-4 row-cols-sm-3 row-cols-2 justify-content-center">
                        @if ($productscategory->isNotEmpty())
                        @foreach ($productscategory as $category)
                        <div class="col d-flex">
                            <div class="category-item text-center flex-fill wow fadeInUp" data-wow-delay="0.2s">
                                <div class="mx-auto mb-3">
                                    <img src="{{ $category->image }}" class="img-fluid" alt="img">
                                </div>
                                <h6 class="fs-14 mb-1">{{ $category->name }}</h6>
                                <p class="fs-14 mb-0">{{ $category->service_count }}
                                    @if ($category->service_count > 0)
                                     {{__('Services')}}
                                    @else
                                     {{__('Service')}}
                                    @endif
                                </p>
                                <a href="{{route('productlistcategory',$category->slug )}}" class="link-primary text-decoration-underline fs-14">{{ __('View All') }}</a>
                            </div>
                        </div>
                        @endforeach
                        @else
                        <span class="text-center text-black">{{ __('category_not_available_info') }}</span>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<!-- /Page Wrapper -->

@endsection