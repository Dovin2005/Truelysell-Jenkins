@extends('admin.admin')

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content me-0 me-md-0 me-lg-4">
        <!-- Breadcrumb -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h4 class="mb-1">{{ __('social_shares') }}</h4>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Settings') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('social_shares') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /Breadcrumb -->

        <div class="row">
            @include('admin.partials.general_settings_side_menu')
            <div class="col-lg-10 col-md-9">
                <div class="card">
                    <div class="card-body pb-0 d-noe real-card mb-3">
                        <div class="d-flex justify-content-between mb-3">
                            <h6 class="fw-bold mb-3">{{ __('Social Media Shares') }}</h6>
                            <button class="btn btn-primary add-new">{{ __('Add Social Media Share') }}</button>
                        </div>

                        <div class="custom-datatable-filter table-responsive table-loader position-relative vh-10">
                            @include('admin.content-loader')
                        </div>

                        <div class="d-none real-table">
                            <div class="custom-datatable-filter table-responsive">
                                <table class="table w-100" id="socialMediaShareTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ strtoupper(__('platform_name')) }}</th>
                                            <th>{{ strtoupper(__('url')) }}</th>
                                            <th>{{ strtoupper(__('icon')) }}</th>
                                            <th>{{ strtoupper(__('status')) }}</th>
                                            <th>{{ strtoupper(__('action')) }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="table-footer d-none"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.partials.footer')
</div>
<!-- /Page Wrapper -->

<!-- Add/Edit Modal -->
<div class="modal fade" id="add_social_media_share" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <form id="addSocialMediaShareForm" method="POST" action="{{ route('admin.store-social-media-share') }}">
                @csrf
                <input type="hidden" name="id" id="id">

                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Add Social Media Share') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('platform_name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="platform_name" id="platform_name">
                        <span id="platform_name_error" class="text-danger error-text"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('url') }} <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" name="url" id="url">
                        <span id="url_error" class="text-danger error-text"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('icon') }}</label>
                        <input type="text" class="form-control" name="icon" id="icon" placeholder="fa-brands fa-facebook">
                        <span id="icon_error" class="text-danger error-text"></span>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="status" name="status" checked>
                        <label class="form-check-label" for="status">{{ __('Status') }}</label>
                    </div>
                    <div>
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary submitbtn">{{ __('create_new') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Add/Edit Modal -->


<!-- Delete Modal -->
<div class="modal fade" id="delete_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteForm">
                 @csrf
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Confirm Deletion') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Delete Confirmation Message') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('yes_delete') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/global_setting/social_media_share.js') }}"></script>
@endpush