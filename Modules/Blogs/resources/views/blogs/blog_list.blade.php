@extends('front')

@section('content')

<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h2 class="breadcrumb-title mb-2">{{ __('blogs') }}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="ti ti-home-2"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('blogs') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="breadcrumb-bg">
            <img src="assets/img/bg/breadcrumb-bg-01.png" class="breadcrumb-bg-1" alt="">
            <img src="assets/img/bg/breadcrumb-bg-02.png" class="breadcrumb-bg-2" alt="">
        </div>
    </div>
</div>

<div class="page-wrapper bg-white">
    <div class="content">
        <div class="container">
            <div class="row justify-content-center align-items-center ">

                @if ($blogsData->isNotEmpty())

                    @foreach ($blogsData as $blog)
                        <div class="col-xl-4 col-md-6">
                            <div class="card p-0">
                                <div class="card-body p-0">
                                    <div class="img-sec w-100s blog-list-img">
                                        <a href="{{ url('blog-details/'. $blog['slug']) }}"><img src="{{ $blog['image'] }}" class="img-fluid rounded-top w-100" alt="img"></a>
                                        <div class="image-tag d-flex justify-content-end align-items-center">
                                            <span class="trend-tag">{{ $blog['category_name'] }}</span>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="d-flex align-items-center mb-3  ">
                                            <div class="d-flex align-items-center border-end pe-2">
                                                <span class="avatar avatar-sm me-2">
                                                    <img src="{{ $blog['author_image'] }}" class="rounded-circle" alt="user">
                                                </span>
                                                <h6 class="fs-14 text-gray-6">{{ $blog['author_name'] }}</h6>
                                            </div>
                                            <div class="d-flex align-items-center ps-2">
                                                <span><i class="ti ti-calendar me-2"></i></span>
                                                <span class="fs-14">{{ $blog['created_at'] }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="fs-16 text-truncate mb-1"><a href="{{ url('blog-details/'. $blog['slug']) }}">{{ $blog['title'] }}</a></h6>
                                            <p class="two-line-ellipsis fs-14">{{ strip_tags(html_entity_decode($blog['description'])) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <span class="text-center text-black">{{ __('no_blog_info') }}</span>
                @endif

            </div>
            @if ($blogs->hasPages())
                <div class="table-paginate d-flex justify-content-center align-items-center flex-wrap row-gap-3">
                    <nav aria-label="Page navigation">
                        <ul class="paginations d-flex justify-content-center align-items-center">

                            @if ($blogs->onFirstPage())
                                <li class="page-item me-2 disabled">
                                    <span class="d-flex justify-content-center align-items-center">
                                        <i class="ti ti-arrow-left me-2"></i>{{ __('Prev') }}
                                    </span>
                                </li>
                            @else
                                <li class="page-item me-2">
                                    <a class="d-flex justify-content-center align-items-center" href="{{ $blogs->previousPageUrl() }}">
                                        <i class="ti ti-arrow-left me-2"></i>{{ __('Prev') }}
                                    </a>
                                </li>
                            @endif

                            @foreach ($blogs->links()->elements[0] as $page => $url)
                                <li class="page-item me-2">
                                    <a class="page-link-1 d-flex justify-content-center align-items-center {{ $blogs->currentPage() == $page ? 'active' : '' }}"
                                    href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endforeach

                            @if ($blogs->hasMorePages())
                                <li class="page-item me-2">
                                    <a class="d-flex justify-content-center align-items-center" href="{{ $blogs->nextPageUrl() }}">
                                        {{ __('Next') }} <i class="ti ti-arrow-right ms-2"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item me-2 disabled">
                                    <span class="d-flex justify-content-center align-items-center">
                                        {{ __('Next') }} <i class="ti ti-arrow-right ms-2"></i>
                                    </span>
                                </li>
                            @endif

                        </ul>
                    </nav>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
