@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content bg-white">
        <div class="d-md-flex d-block align-items-center justify-content-between pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('FAQ')}}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('content') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('FAQ') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="mb-2">
                @if(isset($permission))
                    @if(hasPermission($permission, 'FAQ', 'create'))
                        <div class="skeleton label-skeleton label-loader"></div>
                        <a href="#" class="btn btn-primary d-none real-label" data-bs-toggle="modal" data-bs-target="#add_faq"><i class="ti ti-square-rounded-plus-filled me-2"></i>{{ __('add_faq') }}</a>
                    @endif
                @endif
            </div>
        </div>
        @php $isVisible = 0; @endphp
        @if(isset($permission))
            @if(hasPermission($permission, 'FAQ', 'delete'))
                @php $delete = 1; $isVisible = 1; @endphp
            @else
                @php $delete = 0; @endphp
            @endif
            @if(hasPermission($permission, 'FAQ', 'edit'))
                @php $edit = 1; $isVisible = 1; @endphp
            @else
                @php $edit = 0; @endphp
            @endif
            <div id="has_permission" data-delete="{{ $delete }}" data-edit="{{ $edit }}" data-visible="{{ $isVisible }}"></div>
        @else
            <div id="has_permission" data-delete="1" data-edit="1"></div>
        @endif
        <div class="row">
            <div class="col-xxl-12 col-xl-12">
                <div class="border-0">
                    <div class="card">
                        <div class="card-body p-0 py-3">
                            <div class="custom-datatable-filter table-responsive">
                                <div class="table-responsive">
                                    <table id="loader-table" class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                </th>
                                                <th>
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                </th>
                                                <th>
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                </th>
                                                <th>
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                </th>
                                                <th>
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table class="table border-0 d-none" id="faq_datatable" data-empty="{{ __('faq_empty_info') }}">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>{{ __('SN') }}</th>
                                                <th>{{ __('question') }}</th>
                                                <th>{{ __('answer') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                @if ($isVisible == 1)
                                                <th class="no-sort">{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add_faq">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('add_faq') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="addFAQForm" autocomplete="off">
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('question') }}</label>
                                <input type="text" name="question" id="question" class="form-control"
                                    placeholder="{{ __('enter_question') }}" maxlength="100">
                                <div class="invalid-feedback" id="question_error" data-required="{{ __('question_required') }}" data-min="{{ __('question_minlength') }}"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('answer') }}</label>
                                <textarea name="answer" id="answer" class="form-control"
                                    placeholder="{{ __('enter_answer') }}" maxlength="300"
                                    style="height: 150px;"></textarea>
                                <div class="invalid-feedback" id="answer_error" data-required="{{ __('answer_required') }}" data-min="{{ __('answer_minlength') }}"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                <div class="status-title">
                                    <h5>{{ __('Status') }}</h5>
                                </div>
                                <div class="status-toggle modal-status">
                                    <input type="checkbox" id="status" class="check user8" checked>
                                    <label for="status" class="checktoggle"></label>
                                </div>
                                <div class="invalid-feedback" id="status_error"></div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" id="btn_faq" class="btn btn-primary add_faq_btn"
                        data-save_text="{{ __('Save') }}">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="edit_faq">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title edit_modal_title">{{ __('edit_faq') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="editFAQForm" autocomplete="off">
                @csrf

                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="row">
                        <div id="translate_container">
                            <div class="card rounded-0">
                                <h4 class="p-2 lang_title">{{ __('available_translations') }}</h4>
                                @php
                                    $langCode = \App::getLocale();
                                    $language = \Modules\GlobalSetting\app\Models\Language::where('code', $langCode)->first();
                                @endphp
                                <div class="d-flex align-items-center justify-content-between p-2">
                                    <div class="col-md-3">
                                        <select class="form-select" name="language_id" id="language_id">
                                            @if ($allLanguages->isNotEmpty())
                                            @foreach ($allLanguages as $allLanguage)
                                            <option value="{{ $allLanguage->id }}" {{ $allLanguage->id == $language->id ? 'selected' : '' }}>{{ $allLanguage->name }}</option>
                                            @endforeach
                                            @else
                                            <option value="">No Lang Found</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <div class="mb-3">
                                <label for="edit_question" class="form-label">{{ __('question') }}</label>
                                <input type="text" name="edit_question" id="edit_question" class="form-control"
                                    placeholder="{{ __('Enter Question') }}" maxlength="100">
                                <span class="invalid-feedback" id="edit_question_error"></span>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="mb-3">
                                <label for="edit_answer" class="form-label">{{ __('answer') }}</label>
                                <textarea name="edit_answer" id="edit_answer" class="form-control"
                                    placeholder="{{ __('Enter Answer') }}" maxlength="300"></textarea>
                                <div class="invalid-feedback" id="edit_answer_error"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                <div class="status-title">
                                    <h5 id="status_text">{{ __('Status') }}</h5>
                                </div>
                                <div class="status-toggle modal-status">
                                    <input type="checkbox" id="edit_status" class="check user8">
                                    <label for="edit_status" class="checktoggle"></label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <a href="#" class="btn btn-light me-2 cancelbtn" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                    <button type="submit" id="edit_btn_faq" class="btn btn-primary edit_faq_btn"
                        data-update-text="{{ __('Save') }}">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="delete-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form>
                <div class="modal-body text-center">
                    <span class="delete-icon">
                        <i class="ti ti-trash-x"></i>
                    </span>
                    <h4>{{ __('Confirm Deletion') }}</h4>
                    <p>{{ __('Are you sure you want to delete this item? This action cannot be undone.') }}</p>
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-2"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-danger"
                            id="confirmDeleteFaq">{{ __('Yes, Delete') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection