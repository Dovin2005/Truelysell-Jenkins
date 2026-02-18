@extends('admin.admin')

@section('content')
<div class="page-wrapper">
    <div class="content bg-white">
        <div class="d-md-flex d-block align-items-center justify-content-between -bottom pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('sub_category') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Service') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('sub_category') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                @php
                    $langCode = app()->getLocale();
                    $language = \Modules\GlobalSetting\app\Models\Language::where('code', $langCode)->first();
                @endphp
                <div class="mb-3 me-2">
                    <div class="skeleton label-skeleton label-loader"></div>
                    <select class="form-select d-none real-input" name="language" id="language">
                        <option value="">{{ __('Select') }}</option>
                        @if ($allLanguages->isNotEmpty())
                            @foreach ($allLanguages as $allLanguage)
                                <option value="{{ $allLanguage->id }}" {{ $allLanguage->id == $language->id ? 'selected' : '' }}>{{ $allLanguage->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="mb-3 me-2">
                    <div class="skeleton label-skeleton label-loader"></div>
                    <select class="form-select d-none real-input" name="category" id="category" data-select="{{ __('select_category') }}">
                        <option value="">{{ __('select_category') }}</option>
                        @if ($categories->isNotEmpty())
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="mb-3">
                    @if(isset($permission))
                        @if(hasPermission($permission, 'Categories', 'create'))
                        <div class="skeleton label-skeleton label-loader"></div>
                        <a href="#" class="btn btn-primary d-none real-label" id="add_category_btn" data-bs-toggle="modal" data-bs-target="#save_category_modal" data-id=""><i class="ti ti-square-rounded-plus-filled me-2"></i>{{ __('add_sub_category') }}</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @php $isVisible = 0; @endphp
        @if(isset($permission))
            @if(hasPermission($permission, 'Categories', 'delete'))
                @php $delete = 1; $isVisible = 1; @endphp
            @else
                @php $delete = 0; @endphp
            @endif
            @if(hasPermission($permission, 'Categories', 'edit'))
                @php $edit = 1; $isVisible = 1; @endphp
            @else
                @php $edit = 0; @endphp
            @endif
            @if(hasPermission($permission, 'Categories', 'view'))
                @php $view = 1; $isVisible = 1; @endphp
            @else
                @php $view = 0; @endphp
            @endif
            <div id="has_permission" data-view="{{ $view }}" data-delete="{{ $delete }}" data-edit="{{ $edit }}" data-visible="{{ $isVisible }}"></div>
        @else
            <div id="has_permission" data-delete="1" data-edit="1"></div>
        @endif
        <div class="row">
            <div class="col-xxl-12 col-xl-12">
                <div class="-start">
                    <div class="card">
                        <div class="card-body p-0 py-3">
                            <div class="custom-datatable-filter table-responsive">
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
                                <table class="table d-none" id="categories_table" data-empty="{{ __('no_data_available') }}">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('sub_category') }}</th>
                                            <th>{{ __('Slug') }}</th>
                                            <th>{{ __('Category') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            @if ($edit == 1)
                                            <th>{{ __('Featured') }}</th>
                                            @endif
                                            @if ($isVisible == 1)
                                            <th class="no-sort">{{ __('Action') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="categories_list"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="save_category_modal">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title category_modal_title" data-add_category="{{ __('add_sub_category') }}" data-edit_category="{{ __('edit_sub_category') }}">{{ __('add_sub_category') }}</h4>
                                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                                        aria-label="Close">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>
                                <form id="subcategoryForm">
                                    <div class="modal-body">
                                        <div class="row">
                                            <input type="hidden" name="id" id="id">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Category') }}</label>
                                                    <select class="form-select real-input" name="category_id" id="category_id" data-select="{{ __('Select') }}">
                                                        <option value="">{{ __('Select') }}</option>
                                                        @if ($categories->isNotEmpty())
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <span class="error-text text-danger" id="category_id_error" data-required="{{ __('category_required') }}"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="form-label">{{ __('sub_category_name') }}</label>
                                                            <input type="text" id="subcategory_name" name="subcategory_name" class="form-control translatable">
                                                            <span class="error-text text-danger" id="subcategory_name_error" data-required="{{ __('sub_category_name_required') }}"></span>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label translatable" data-translate="slug">{{ __('Slug') }}</label>
                                                            <input type="text" id="slug" name="slug" class="form-control translatable" placeholder="{{ __('Enter Slug') }}">
                                                            <span class="error-text text-danger" id="slug_error" data-required="{{ __('slug_required') }}"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="form-label translatable" data-translate="image">{{ __('Image') }}</label>
                                                            <div class="d-flex justify-content-between mb-3">
                                                                <div class="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames">
                                                                    <img id="category_image_view" src="{{ asset("front/img/default-placeholder-image.png") }}" alt="{{ __('Category Image') }}" data-translate="image_alt">
                                                                    <i class="ti ti-photo-plus fs-16 upload_icon"></i>
                                                                </div>
                                                            </div>
                                                            <div class="profile-uploader profile-uploader-two mb-0 translatable">
                                                                <span class="d-block text-center lh-1 fs-24 mb-1"><i class="ti ti-upload"></i></span>
                                                                <div class="drag-upload-btn bg-transparent me-0 border-0">
                                                                    <p class="fs-12 mb-2 translatable" data-translate="drag_drop"><span class="text-primary translatable">{{ __('Click to Upload') }}</span> {{ __('or drag and drop') }}</p>
                                                                    <h6 class="translatable" data-translate="jpg">{{ __('JPG or PNG') }}</h6>
                                                                    <h6 class="translatable" data-translate="maxpx">{{ __('(Max 450 x 450 px)') }}</h6>
                                                                </div>
                                                                <input type="file" class="form-control" accept="image/*" id="category_image" name="category_image" data-translate="image_upload">
                                                            </div>
                                                            <span class="error-text text-danger" id="category_image_error" data-required="{{ __('image_required') }}"></span>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label translatable" data-translate="icon">{{ __('Icon') }}</label>
                                                            <div class="d-flex justify-content-between mb-3">
                                                                <div class="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames">
                                                                    <img id="category_icon_view" src="{{ asset("front/img/default-placeholder-image.png") }}" alt="{{ __('Category Icon') }}" data-translate="icon_alt">
                                                                    <i class="ti ti-photo-plus fs-16 upload_icon_2"></i>
                                                                </div>
                                                            </div>
                                                            <div class="profile-uploader profile-uploader-two mb-0 translatable">
                                                                <span class="d-block text-center lh-1 fs-24 mb-1"><i class="ti ti-upload"></i></span>
                                                                <div class="drag-upload-btn bg-transparent me-0 border-0">
                                                                    <p class="fs-12 mb-2 translatable" data-translate="drag_drop"><span class="text-primary translatable">{{ __('Click to Upload') }}</span> {{ __('or drag and drop') }}</p>
                                                                    <h6 class="translatable" data-translate="jpg">{{ __('JPG or PNG') }}</h6>
                                                                    <h6 class="translatable" data-translate="maxpx">{{ __('(Max 450 x 450 px)') }}</h6>
                                                                </div>
                                                                <input type="file" class="form-control translatable" accept="image/*" id="category_icon" name="category_icon" data-translate="icon_upload">
                                                            </div>
                                                            <span class="error-text text-danger" id="category_icon_error" data-required="{{ __('icon_required') }}"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label" data-translate="description">{{ __('Description') }}</label>
                                                    <textarea class="form-control" name="description" id="description" cols="3" rows="3" placeholder="{{ __('Enter Description') }}"></textarea>
                                                    <span class="error-text text-danger" id="description_error" data-required="{{ __('description_required') }}"></span>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between mb-3">
                                                                <div class="status-title">
                                                                    <h5>{{ __('Status') }}</h5>
                                                                </div>
                                                                <div class="status-toggle modal-status">
                                                                    <input type="checkbox" id="status" name="status" class="check translatable" checked>
                                                                    <label for="status" class="checktoggle translatable" data-translate="status_toggle"> </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                                                <div class="status-title">
                                                                    <h5 class="translatable" data-translate="featured">{{ __('Featured') }}</h5>
                                                                </div>
                                                                <div class="status-toggle modal-status">
                                                                    <input type="checkbox" id="featured" name="featured" class="check translatable" checked>
                                                                    <label for="featured" class="checktoggle translatable" data-translate="featured_toggle"> </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <a href="#" class="btn btn-light me-2 translatable" data-bs-dismiss="modal" data-translate="cancel">{{ __('Cancel') }}</a>
                                        <button type="submit" class="btn btn-primary category_save_btn" data-save="{{ __('Save') }}">{{ __('Save') }}</button>
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
                                        <p>{{ __('You want to delete all the marked items, this can\'t be undone once you delete.') }}</p>
                                        <div class="d-flex justify-content-center">
                                            <a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                                            <button type="submit" class="btn btn-danger category_delete_btn">{{ __('Yes, Delete') }}</button>
                                        </div>
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
@endsection