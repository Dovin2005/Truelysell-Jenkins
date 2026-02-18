@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content bg-white">
        <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('add_page_builder') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Content') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Pages') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Page Builder') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('add_page_builder') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row pt-3">
            <div class="col-xxl-8 col-xl-8">
                <div class="flex-fill">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="general_tab" role="tabpanel">
                            <form id="addPageBuilderForm" enctype="multipart/form-data">
                                <div class="d-md-flex d-block">
                                    <div class="flex-fill">
                                        <div class="card">
                                            <div class="card-body pb-1">

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ __('Page Title') }}</label>
                                                            <input type="text" name="page_title" id="page_title" class="form-control" placeholder="{{ __('enter_page_title') }}">
                                                            <span class="invalid-feedback" id="page_title_error"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ __('Slug') }}</label>
                                                            <input type="text" name="slug" id="slug" class="form-control" placeholder="{{ __('Enter Slug') }}">
                                                            <span class="invalid-feedback" id="slug_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <div class="d-flex justify-content-between m-1">
                                                                <label class="form-label"></label>
                                                                <button type="button" id="addTextarea" class="btn btn-primary rounded-0 btn-md">{{ __('Add Section') }}</button>
                                                            </div>
                                                            <div class="textareasContainer" id="draggable-left" data-section_title="{{ __("section_title") }}" data-section_title_placeholder="{{ __("section_title_placeholder") }}" data-section_label="{{ __("section_label") }}" data-section_label_placeholder="{{ __("section_label_placeholder") }}" data-enter_page_content="{{ __("enter_page_content") }}" data-status="{{ __("status") }}">

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ __('Seo Tags') }}</label>
                                                            <input type="text" class="form-control" id="tag" name="tag" data-role="tagsinput" placeholder="{{ __('Enter Tag') }}">
                                                            <span class="invalid-feedback" id="tag_error"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ __('Seo Title') }}</label>
                                                            <input type="text" class="form-control" id="seo_title" name="seo_title" placeholder="{{ __('Enter Seo Title') }}">
                                                            <span class="invalid-feedback" id="seo_title_error"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <label class="form-label">{{ __('Seo Description') }}</label>
                                                            <textarea cols="4" rows="7" class="form-control" id="seo_description" name="seo_description" placeholder="{{ __('Enter Seo Description') }}"></textarea>
                                                            <span class="invalid-feedback" id="seo_description_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="form-group col-md-12 mb-2">
                                                        <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                                            <div class="status-title">
                                                                <h5>{{ __('Status Toggle') }}</h5>
                                                                <p>{{ __('Change the Status by toggle') }}</p>
                                                            </div>
                                                            <div class="status-toggle modal-status">
                                                                <input type="checkbox" id="status" class="check user8" checked>
                                                                <label for="status" class="checktoggle"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal-footer my-3">
                                                    <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Clear') }}</a>
                                                    <button type="submit" id="add_btn_page" class="btn btn-primary btn_page" data-update-text="{{ __('Save') }}" data-create-success="{{ __('page_create_success') }}">{{ __('Save') }}</button>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-xl-4" id="cardContainer">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="skeleton card-skeleton card-loader"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="skeleton card-skeleton card-loader"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="skeleton card-skeleton card-loader"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="skeleton card-skeleton card-loader"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="skeleton card-skeleton card-loader"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="skeleton card-skeleton card-loader"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="skeleton card-skeleton card-loader"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="skeleton card-skeleton card-loader"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="skeleton card-skeleton card-loader"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="skeleton card-skeleton card-loader"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="skeleton card-skeleton card-loader"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="skeleton card-skeleton card-loader"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
