@extends('admin.admin')

@section('content')
<div class="page-wrapper">
    <div class="content bg-white">
        <div class="d-md-flex d-block align-items-center justify-content-between -bottom pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('Categories Settings') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Settings') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Categories Settings') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-3">
                    <a href="#" class="btn btn-primary save_category_modal" id="save_category_modal" data-id=""><i class="ti ti-square-rounded-plus-filled me-2"></i>Add <span class="subcategory_title">Category</span></a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xxl-12 col-xl-12">
                <div class="-start ">

                    <div class="card">
                        <div
                            class="card-header d-flex align-items-center justify-content-between flex-wrap pb-0">
                            <h4 class="mb-3 subcategories_title">{{ __('Categories') }}</h4>
                            <div class="d-flex align-items-center flex-wrap">
                                <input type="hidden" name="main_parent_id" id="category_parent_id" class="category_parent_id" value="0">
                                <div class="mb-3 me-3">
                                    <input type="text" class="form-control category_search">
                                </div>
                                <div class="mb-3 me-3">
                                    <select name="category_order_btn" id="category_order_btn" class="form-control category_order_btn">
                                        <option value="asc">{{ __('Ascending') }}</option>
                                        <option value="desc">{{ __('Descending') }}</option>
                                    </select>
                                </div>
                                <div class="mb-3 me-3">
                                    <select name="category_sort_btn" id="category_sort_btn" class="form-control category_sort_btn">
                                        <option value="10">{{ __('10') }}</option>
                                        <option value="25">{{ __('25') }}</option>
                                        <option value="50">{{ __('50') }}</option>
                                        <option value="100">{{ __('100') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0 py-3">

                            <div class="custom-datatable-filter table-responsive">
                                <table class="table" id="categories_table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="subcategory_title">{{ __('Categories') }}</th>
                                            <th>{{ __('Slug') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Featured') }}</th>
                                            <th class="no-sort">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="categories_list"></tbody>
                                </table>
                                <nav class="mx-3 mt-3">
                                    <ul class="pagination" id="pagination">
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="save_category">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title category_modal_title"></h4>
                                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                                        aria-label="Close">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>
                                <form>
                                    <div class="modal-body">
                                        <div class="row">
                                            <input type="hidden" name="id" id="save_category_id">
                                            <div class="col-md-12">
                                                <div class="mb-3 d-none" id="parent_category_section">
                                                    <label class="form-label">{{ __('Parent Category') }}</label>
                                                    <select name="parent_id" id="save_parent_id" class="form-control">
                                                        <option value="">{{ __('Select') }}</option>
                                                    </select>
                                                </div>
                                                <input type="hidden" name="parent_id" id="parent_id" class="form-control parent_id" placeholder="{{ __('Enter Parent Category ID') }}">

                                                <div class="mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="form-label"><span class="subcategory_title">{{ __('Category') }}</span> {{ __('Name') }}</label>
                                                            <input type="text" id="save_category_name" name="name" class="form-control" placeholder="{{ __('Enter Category Name') }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">{{ __('Slug') }}</label>
                                                            <input type="text" id="save_category_slug" name="slug" class="form-control" placeholder="{{ __('Enter Slug') }}">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="form-label">{{ __('Image') }}</label>
                                                            <div class="d-flex justify-content-between mb-3">
                                                                <div class="d-flex align-items-center">
                                                                    <span class="avatar avatar-xl border rounded d-flex align-items-center justify-content-center p-2 me-2">
                                                                        <img id="save_category_image_view"  src="" alt="{{ __('Category Image') }}">
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="profile-uploader profile-uploader-two mb-0">
                                                                <span class="d-block text-center lh-1 fs-24 mb-1"><i class="ti ti-upload"></i></span>
                                                                <div class="drag-upload-btn bg-transparent me-0 border-0">
                                                                    <p class="fs-12 mb-2"><span class="text-primary">{{ __('Click to Upload') }}</span> {{ __('or drag and drop') }}</p>
                                                                    <h6>{{ __('JPG or PNG') }}</h6>
                                                                    <h6>{{ __('(Max 450 x 450 px)') }}</h6>
                                                                </div>
                                                                <input type="file" class="form-control" accept="image/*" id="save_category_image">
                                                                <div id="frames"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">{{ __('Icon') }}</label>
                                                            <div class="d-flex justify-content-between mb-3">
                                                                <div class="d-flex align-items-center">
                                                                    <span class="avatar avatar-xl border rounded d-flex align-items-center justify-content-center p-2 me-2">
                                                                        <img id="save_category_icon_view"  src="" alt="{{ __('Category Icon') }}">
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="profile-uploader profile-uploader-two mb-0">
                                                                <span class="d-block text-center lh-1 fs-24 mb-1"><i class="ti ti-upload"></i></span>
                                                                <div class="drag-upload-btn bg-transparent me-0 border-0">
                                                                    <p class="fs-12 mb-2"><span class="text-primary">{{ __('Click to Upload') }}</span> {{ __('or drag and drop') }}</p>
                                                                    <h6>{{ __('JPG or PNG') }}</h6>
                                                                    <h6>{{ __('(Max 450 x 450 px)') }}</h6>
                                                                </div>
                                                                <input type="file" class="form-control" accept="image/*" id="save_category_icon">
                                                                <div id="frames"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">

                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Description') }}</label>
                                                    <textarea class="form-control" name="description" id="save_category_description" cols="3" rows="3" placeholder="{{ __('Enter Description') }}"></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between mb-3">
                                                                <div class="status-title">
                                                                    <h5>{{ __('Status') }}</h5>
                                                                </div>
                                                                <div class="status-toggle modal-status">
                                                                    <input type="checkbox" id="save_category_status" name="status" class="check" checked >
                                                                    <label for="save_category_status" class="checktoggle"> </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                                                <div class="status-title">
                                                                    <h5>{{ __('Featured') }}</h5>
                                                                </div>
                                                                <div class="status-toggle modal-status">
                                                                    <input type="checkbox" id="save_category_featured" name="featured" class="check" checked>
                                                                    <label for="save_category_featured" class="checktoggle"> </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                                        <button type="submit" class="btn btn-primary category_save_btn">{{ __('Add Category') }}</button>
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
