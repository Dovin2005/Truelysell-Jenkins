@extends('front')

@section('content')

<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h2 class="breadcrumb-title mb-2">{{ __('Blog Details') }}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="ti ti-home-2"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Blog Details') }}</li>
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
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-12 blog-details">
                    <div class="blog-head">
                        <div class="blog-category">
                            <ul>
                                <li><span class="badge badge-light text-dark">{{ $blogDetail['category_name'] }}</span></li>
                                <li><i class="feather-calendar me-1"></i>{{ $blogDetail['created_at'] }}</li>
                                <li>
                                    <div class="post-author">
                                        <a href="javascript:void(0);"><img src="{{ $blogDetail['author_image'] }}" alt="Post Author"><span>{{ $blogDetail['author_name'] }}</span></a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <h4 class="mb-3">{{ $blogDetail['title'] }}</h4>
                    </div>

                    <div class="card blog-list shadow-none">
                        <div class="card-body">
                            <div class="blog-image">
                                <a href="{{ url('blog-details/'. $blogDetail['slug']) }}"><img class="img-fluid" src="{{ $blogDetail['image'] }}" alt="Post Image"></a>
                            </div>
                            <div class="blog-content">
                                <p>{!! $blogDetail['description'] !!}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="service-wrap blog-review" id="blog_comments_container" style="@if ($comments->isEmpty()){{'display:none'}}@endif">
                        <h4>{{ __('Comments') }}</h4>
                        <ul id="list_blog_comments">
                            @foreach ($comments as $comment)
                                <li>
                                    <div class="review-box">
                                        <div class="card shadow-none">
                                            <div class="card-body">
                                                <div class="d-flex align-items-start justify-content-between mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <span class="avatar avatar-md flex-shrink-0 me-2"><img src="{{ $comment->image }}" class="img-fluid rounded-circle" alt="img"></span>
                                                        <div class="review-name">
                                                            <h6 class="fs-16 fw-medium mb-1">{{ $comment->name }}</h6>
                                                            <p class="fs-14">{{ $comment->comment_date }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <p>{{ $comment->comment }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="new-comment">
                        <h4> {{ __('Write a Comment') }}</h4>
                        <form id="blogCommentForm">
                            <div class="row">
                                <input type="hidden" name="user_id" id="user_id" value="{{ Auth::id() }}">
                                <input type="hidden" name="post_id" id="post_id" value="{{ $blogDetail['id'] }}">

                                @if (empty(Auth::id()))
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('name') }}</label>
                                            <input type="text" class="form-control" name="name" id="post_name" placeholder="{{ __('Enter Name') }}">
                                            <span class="error-text text-danger" id="post_name_error"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('email') }}</label>
                                            <input type="email" class="form-control" name="email" id="post_email" placeholder="{{ __('Enter Email') }}">
                                            <span class="error-text text-danger" id="post_email_error"></span>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('Comment') }}</label>
                                        <textarea rows="6" class="form-control" name="comment" id="comment" placeholder="{{ __('Enter your comment here') }}"></textarea>
                                        <span class="error-text text-danger" id="comment_error"></span>
                                    </div>
                                </div>
                                <div>
                                    <button  type="submit" class="btn btn-dark" id="blogCommentBtn" data-name_required="{{ __('name_required') }}" data-email_required="{{ __('email_required') }}" data-comment_required="{{ __('comment_required') }}" data-name_max="{{ __('name_maxlength') }}" data-email_format="{{ __('email_format') }}">{{ __('Post Comment') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
