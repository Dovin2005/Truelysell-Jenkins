@extends('admin.admin')

@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content me-0 me-md-0 me-lg-4">
            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h4 class="mb-1">{{ __('settings') }}</h4>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">{{ __('home') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('settings') }}</li>
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
                                <h6 class="fw-bold mb-3">{{ __('social_links') }}</h6>
                                <button class="btn btn-primary add-new">{{ __('add_social_link') }}</button>
                            </div>
                            <div class="custom-datatable-filter table-responsive table-loader position-relative vh-10">
                                @include('admin.content-loader')
                            </div>
                            <div class="d-none real-table">
                                <div class="custom-datatable-filter table-responsive brandstable">
                                    <table class="table w-100" id="socialLinksTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>{{ __('platform_name') }}</th>
                                                <th>{{ __('link') }}</th>
                                                <th>{{ __('icon') }}</th>
                                                <th>{{ __('status') }}</th>
                                                <th>{{ __('action') }}</th>
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
<div class="modal fade" id="add_social_link" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="addSocialLinkForm">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('add_social_link') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">

                    <div class="mb-3">
                        <label for="platform_name" class="form-label">{{ __('platform') }}</label>
                        <input type="text" name="platform_name" id="platform_name" class="form-control">
                        <small class="text-danger error-text" id="platform_name_error"></small>
                    </div>

                    <div class="mb-3">
                        <label for="link" class="form-label">{{ __('link') }}</label>
                        <input type="url" name="link" id="link" class="form-control">
                        <small class="text-danger error-text" id="link_error"></small>
                    </div>

                    <div class="mb-3">
                        <label for="icon" class="form-label">{{ __('icon') }}</label>
                        <input type="text" name="icon" id="icon" class="form-control" placeholder="fa-brands fa-facebook">
                        <small class="text-danger error-text" id="icon_error"></small>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" class="form-check-input" id="status" name="status" value="1">
                        <label class="form-check-label" for="status">{{ __('Status') }}</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                    <button type="submit" class="btn btn-primary submitbtn">{{ __('create_new') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="delete_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="deleteForm">
            <input type="hidden" name="id" id="delete_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Confirm Deletion') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Delete Confirmation Message') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('yes_delete') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/social-links.js') }}"></script>
@endpush