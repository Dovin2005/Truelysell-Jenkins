@extends('admin.admin')

@section('content')

    <div class="page-wrapper">
        <div class="content">
            <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('category_list') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);">{{ __('blogs') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('category_list') }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="mb-2">
                        @if(isset($permission))
                            @if(hasPermission($permission, 'Blogs', 'create'))
                            <div class="skeleton label-skeleton label-loader"></div>
                            <a href="#" class="btn btn-primary d-none real-label" id="add_category_btn" data-bs-toggle="modal"
                                data-bs-target="#blog_category_modal"><i class="ti ti-square-rounded-plus-filled me-2"></i>{{ __('add_category') }}</a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            @php $isVisible = 0; @endphp
            @if(isset($permission))
                @if(hasPermission($permission, 'Blogs', 'delete'))
                    @php $delete = 1; $isVisible = 1; @endphp
                @else
                    @php $delete = 0; @endphp
                @endif
                @if(hasPermission($permission, 'Blogs', 'edit'))
                    @php $edit = 1; $isVisible = 1; @endphp
                @else
                    @php $edit = 0; @endphp
                @endif
                <div id="has_permission" data-delete="{{ $delete }}" data-edit="{{ $edit }}" data-visible="{{ $isVisible }}"></div>
            @else
                <div id="has_permission" data-delete="1" data-edit="1"></div>
            @endif
            <div class="card">
                <div class="card-body p-0 py-3">
                    <div class="custom-datatable-filter">
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
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table d-none" id="blogCategoryTable" data-edit="{{ __('Edit') }}" data-delete="{{ __('Delete') }}" data-empty="{{ __('category_empty_info') }}">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('name') }}</th>
                                        @if ($edit == 1)
                                        <th>{{ __('Status') }}</th>
                                        @endif
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
    <div class="modal fade" id="blog_category_modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title category_modal_title">{{ __('add_category') }}</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="blogCategoryForm">
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="method" id="method">
                            <input type="hidden" name="id" id="id">
                            <div class="d-none" id="translate_container">
                                <div class="card rounded-0">
                                    <h4 class="p-2 lang_title">{{ __('available_translations') }}</h4>
                                    @php
                                        $langCode = \App::getLocale();
                                        $language = \Modules\GlobalSetting\app\Models\Language::where('code', $langCode)->first();
                                    @endphp
                                    <div class="d-flex align-items-center justify-content-between p-2">
                                        <div class="col-md-4">
                                            <select class="form-select" name="language_id" id="language_id" data-lang="{{ $language->id }}">
                                                @if ($allLanguages->isNotEmpty())
                                                @foreach ($allLanguages as $allLanguage)
                                                    <option value="{{ $allLanguage->id }}" {{ ($allLanguage->id) == $language->id ? 'selected' : '' }}>{{ $allLanguage->name }}</option>
                                                @endforeach
                                                @else
                                                    <option value="">No Lang Found</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="category_name" class="form-label">{{ __('category_name') }}<span class="text-danger"> *</span></label>
                                    <input type="text" class="form-control" id="category_name" name="category_name" placeholder="{{ __('enter_category_name') }}">
                                    <span class="text-danger error-text" id="category_name_error"></span>
                                </div>
                                <div class="mb-3">
                                    <label for="slug" class="form-label">{{ __('Slug') }}<span class="text-danger"> *</span></label>
                                    <input type="text" class="form-control" id="slug" name="slug" placeholder="{{ __('enter_slug') }}">
                                    <span class="text-danger error-text" id="slug_error"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-light me-2 cancelbtn" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary save_category_btn">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="delete_blog_category_modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="deleteBlogCategoryForm">
                    <div class="modal-body text-center">
                        <span class="delete-icon">
                            <i class="ti ti-trash-x"></i>
                        </span>
                        <h4>{{ __('Confirm Deletion') }}</h4>
                        <p>{{ __('Are you sure you want to delete this item? This action cannot be undone.') }}</p>
                        <input type="hidden" name="delete_id" id="delete_id">
                        <div class="d-flex justify-content-center">
                            <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-danger delete_category_confirm">{{ __('Yes, Delete') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
