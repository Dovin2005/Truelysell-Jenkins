@extends('admin.admin')

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-4">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('File Storage') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Settings') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('File Storage') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            @include('admin.partials.general_settings_side_menu')
            <div class="col-xxl-10 col-xl-9">
                <div class="flex-fill ps-1">
                    <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                    </div>
                    <div class="d-md-flex">
                        <div class="flex-fill">
                            <div class="row">
                                <div class="col-xxl-12 col-xl-12">
                                    <div class="card">
                                        <div class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                                            <div class="d-flex align-items-center">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <h6 class="d-none real-label">{{ __('Local Storage') }}</h6>
                                            </div>
                                            <div class="status-toggle modal-status">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <input type="checkbox" name="local_status" id="local_status" class="check" onchange="toggleCheckbox('local_status', 'aws_status')">
                                                <label for="local_status" class="checktoggle d-none real-label"> </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xxl-12 col-xl-12">
                                    <div class="card">
                                        <div class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
                                            <div class="d-flex align-items-center">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <h6 class="d-none real-label">{{ __('AWS S3') }}</h6>
                                            </div>
                                            <div class="status-toggle modal-status">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <input type="checkbox" name="aws_status" id="aws_status" class="check" onchange="toggleCheckbox('aws_status', 'local_status')">
                                                <label for="aws_status" class="checktoggle d-none real-label"> </label>
                                            </div>
                                        </div>
                                        <div class="card-footer d-flex justify-content-between align-items-center">
                                            <div class="modal-body">
                                                <form id="fileStorageForm">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <label class="form-label d-none real-label">{{ __('AWS ACCESS KEY ID') }}</label>
                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                <input type="text" name="aws_access_key" id="aws_access_key" class="form-control d-none real-input" placeholder="{{ __('Enter AWS Key ID') }}">
                                                                <span class="invalid-feedback" id="aws_access_key_error"></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <label class="form-label d-none real-label">{{ __('AWS SECRET ACCESS KEY') }}</label>
                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                <input type="text" name="aws_secret_access_key" id="aws_secret_access_key" class="form-control d-none real-input" placeholder="{{ __('Enter AWS Secret Access Key') }}">
                                                                <div class="invalid-feedback" id="aws_secret_access_key_error"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <label class="form-label d-none real-label">{{ __('AWS DEFAULT REGION') }}</label>
                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                <input type="text" name="aws_region" id="aws_region" class="form-control d-none real-input" placeholder="{{ __('Enter AWS Default Region') }}">
                                                                <div class="invalid-feedback" id="aws_region_error"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <label class="form-label d-none real-label">{{ __('AWS BUCKET') }}</label>
                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                <input type="text" name="aws_bucket" id="aws_bucket" class="form-control d-none real-input" placeholder="{{ __('Enter AWS Bucket') }}">
                                                                <div class="invalid-feedback" id="aws_bucket_error"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <label class="form-label d-none real-label">{{ __('AWS URL') }}</label>
                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                <input type="text" name="aws_url" id="aws_url" class="form-control d-none real-input" placeholder="{{ __('Enter AWS URL') }}">
                                                                <div class="invalid-feedback" id="aws_url_error"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <div class="skeleton label-skeleton label-loader"></div>
                                                        <button type="submit" id="btn_file" class="btn btn-primary aws_setting_btn d-none real-label" data-update-text="{{ __('Update') }}">{{ __('Update') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection