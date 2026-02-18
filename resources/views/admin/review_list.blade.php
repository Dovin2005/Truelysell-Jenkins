@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content bg-white">
        <div class="d-md-flex d-block align-items-center justify-content-between pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('Reviews') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('feedback_disputes') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Reviews') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        @php $isVisible = 0; @endphp
        @if(isset($permission))
            @if(hasPermission($permission, 'Reviews', 'delete'))
                @php $delete = 1; $isVisible = 1; @endphp
            @else
                @php $delete = 0; @endphp
            @endif
            <div id="has_permission" data-delete="{{ $delete }}" data-visible="{{ $isVisible }}"></div>
        @else
            <div id="has_permission" data-delete="1"></div>
        @endif

        <div class="row" id="reviewLoader">
            <div class="col-xxl-12 col-lg-12">
                <div class="card shadow-none">
                    <div class="card-body">
                        <div class="d-md-flex align-items-center">
                            <div class="review-widget d-sm-flex flex-fill">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex">
                                        <span class="review-img me-2">
                                            <div class="skeleton rectangle-md-skeleton label-loader"></div>
                                        </span>
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="user-icon d-inline-flex">
                                <div class="skeleton label-skeleton label-loader"></div>
                            </div>
                        </div>
                        <div>
                            <div class="skeleton input-skeleton input-loader"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-12 col-lg-12">
                <div class="card shadow-none">
                    <div class="card-body">
                        <div class="d-md-flex align-items-center">
                            <div class="review-widget d-sm-flex flex-fill">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex">
                                        <span class="review-img me-2">
                                            <div class="skeleton rectangle-md-skeleton label-loader"></div>
                                        </span>
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="user-icon d-inline-flex">
                                <div class="skeleton label-skeleton label-loader"></div>
                            </div>
                        </div>
                        <div>
                            <div class="skeleton input-skeleton input-loader"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-12 col-lg-12">
                <div class="card shadow-none">
                    <div class="card-body">
                        <div class="d-md-flex align-items-center">
                            <div class="review-widget d-sm-flex flex-fill">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex">
                                        <span class="review-img me-2">
                                            <div class="skeleton rectangle-md-skeleton label-loader"></div>
                                        </span>
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="user-icon d-inline-flex">
                                <div class="skeleton label-skeleton label-loader"></div>
                            </div>
                        </div>
                        <div>
                            <div class="skeleton input-skeleton input-loader"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-12 col-lg-12">
                <div class="card shadow-none">
                    <div class="card-body">
                        <div class="d-md-flex align-items-center">
                            <div class="review-widget d-sm-flex flex-fill">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex">
                                        <span class="review-img me-2">
                                            <div class="skeleton rectangle-md-skeleton label-loader"></div>
                                        </span>
                                        <div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="user-icon d-inline-flex">
                                <div class="skeleton label-skeleton label-loader"></div>
                            </div>
                        </div>
                        <div>
                            <div class="skeleton input-skeleton input-loader"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row review_list_container d-none real-input" data-empty="{{ __('no_reviews_available') }}" data-next="{{ __('next') }}" data-prev="{{ __('previous') }}">
            
        </div>
        <div class="d-flex justify-content-between align-items-center mb-5 d-none real-input" id="paginate_container">
            <div class="value d-flex align-items-center">
                <span class="me-2">{{ __('show') }}</span>
                <select id="entries_per_page" onchange="getReviewList();">
                    <option value="7">7</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                </select>
                <span class="ms-2">{{ __('entries') }}</span>
            </div>
            <div class="align-items-center justify-content-center">
                <div id="pagination" class="d-flex justify-content-end">
                    <nav>
                        <ul id="pagination_links" class="pagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="del-review">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteReviewForm">
                <div class="modal-body text-center">
                    <span class="delete-icon">
                        <i class="ti ti-trash-x"></i>
                    </span>
                    <h4>{{ __('Confirm Deletion') }}</h4>
                    <p>{{ __('You want to delete all the marked items, this cant be undone once you delete.') }}</p>
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-danger" id="deleteReviewConfirm" data-delete="{{ __('Yes, Delete') }}">{{ __('Yes, Delete') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection   