@extends('provider.provider')
@section('content')

<div class="page-wrapper bg-white">
    <div class="content container-fluid">
        <div class="row">
            <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-4">
                <div class="skeleton label-skeleton label-loader"></div>
                <h4 class="d-none real-label">{{__('Reviews')}}</h4>
            </div>
        </div>
        <input type="hidden" id="user_id" name="user_id" value="{{ Auth::id() }}">
        @if(isset($permission) && Auth::user()->user_type == 4)
        @if(hasPermission($permission, 'Reviews', 'delete'))
        @php $delete = 1; @endphp
        @else
        @php $delete = 0; @endphp
        @endif
        <div id="has_permission" data-del_permission="{{ $delete }}"></div>
        @else
        <div id="has_permission" data-del_permission="1">
        </div>
        @endif
        <div class="row review_list_container d-none real-label" data-empty_info="{{ __('no_reviews_available') }}">

        </div>
        <div class="d-flex justify-content-between align-items-center d-none" id="paginate_container">
            <div class="value d-flex align-items-center">
                <span>{{__('show')}}</span>
                <select id="entries_per_page" onchange="getProviderReviews();">
                    <option value="7">7</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                </select>
                <span>{{__('entries')}}</span>
            </div>
            <div class="d-flex align-items-center justify-content-center">
                <span class="me-2 text-gray-9" id="pagination_info">1 - 7 of 10</span>
                <nav aria-label="Page navigation">
                    <ul class="paginations d-flex justify-content-center align-items-center" id="pagination_links">
                    </ul>
                </nav>
            </div>
        </div>

        <div class="row mb-3">
            <div class="w-100">
                <div class="skeleton review-skeleton review-loader"></div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="w-100">
                <div class="skeleton review-skeleton review-loader"></div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="w-100">
                <div class="skeleton review-skeleton review-loader"></div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade custom-modal" id="del-review">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                <h5 class="modal-title">{{__('delete_review')}}</h5>
                <a href="#" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <div class="modal-body">
                <div class="write-review">
                    <form id="deleteReviewForm">
                        <p>{{__('Are you sure want to delete this Review?')}}</p>
                        <div class="modal-submit text-end">
                            <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{__('Cancel')}}</a>
                            <button type="submit" class="btn btn-dark" id="deleteReviewConfirm" data-yes="{{ __('Yes') }}">{{__('Yes')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection