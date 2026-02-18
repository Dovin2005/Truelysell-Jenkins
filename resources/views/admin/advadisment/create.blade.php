@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content bg-white">
        <div class="d-md-flex d-block align-items-center justify-content-between pb-3">
            <div class="my-auto mb-2">
                <div class="skeleton label-skeleton label-loader"></div>
                <h3 class="page-title mb-1 d-none real-label">{{ __('Advertisement')}}</h3>
                <div class="skeleton label-skeleton label-loader"></div>
                <nav class="d-none real-label">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Advertisement') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Create') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="mb-2">
                @if(isset($permission))
                @if(hasPermission($permission, 'FAQ', 'create'))
                <div class="skeleton label-skeleton label-loader"></div>
                <a href="#" class="btn btn-primary d-none real-label" data-bs-toggle="modal" data-bs-target="#add_advertisement"><i class="ti ti-square-rounded-plus-filled me-2"></i>{{ __('Create') }}</a>
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
                                    <table class="table d-none real-label border-0" id="data_datatable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>{{ __('SN') }}</th>
                                                <th>{{ __('AD Name') }}</th>
                                                <th>{{ __('Slug') }}</th>
                                                <th>{{ __('Clicks') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                @if ($isVisible == 1)
                                                <th class="no-sort">{{ __('Action') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>

                                    <!-- loader Datatable Start-->
                                    <table id="loader-table" class="table table-striped table-bordered">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <p class="d-none real-label">ID</p>
                                                </th>
                                                <th>
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <p class="d-none real-label">Name</p>
                                                </th>
                                                <th>
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <p class="d-none real-label">Email</p>
                                                </th>
                                                <th>
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <p class="d-none real-label">Role</p>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">1</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">John Doe</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">johndoe@example.com</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Admin</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">2</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Jane Smith</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">janesmith@example.com</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Manager</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">3</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Robert Brown</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">robertbrown@example.com</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">User</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">3</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Robert Brown</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">robertbrown@example.com</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">User</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">3</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">Robert Brown</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">robertbrown@example.com</p>
                                                </td>
                                                <td>
                                                    <div class="skeleton data-skeleton data-loader"></div>
                                                    <p class="d-none real-data">User</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <!-- loader Datatable End -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add_advertisement">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Add Advertisement') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="addAdvertisementForm" autocomplete="off">
                <div class="modal-body">
                    <div class="row">
                        <!-- Ad Name -->
                        <div class="form-group col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Ad Name') }}</label>
                                <input type="text" name="ad_name" id="ad_name" class="form-control"
                                    placeholder="{{ __('Enter Ad Name') }}" maxlength="100">
                                <div class="invalid-feedback" id="ad_name_error" data-required="{{ __('Ad name is required') }}"></div>
                            </div>
                        </div>

                        <!-- Ad Type Selection -->
                        <div class="form-group col-md-6 d-flex gap-3 mt-1 mb-2">
                            <div class="d-flex gap-2">
                                <input type="radio" class="form_control" name="ad_type" id="html_ad" value="HTML" checked>
                                <label for="html_ad" class="text-dark fw-bold">HTML Ad</label>
                            </div>
                            <div>
                                <input type="radio" class="form_control" name="ad_type" id="image_ad" value="IMAGE">
                                <label for="image_ad" class="text-dark fw-bold">IMAGE Ad</label>
                            </div>
                        </div>

                        <!-- HTML Ad Body -->
                        <div class="form-group col-md-12" id="html_ad_container">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Ad Body') }}</label>
                                <textarea name="body" id="body" class="form-control"
                                    placeholder="{{ __('Enter Ad Type') }}"
                                    style="height: 200px;"></textarea>
                                <div class="invalid-feedback" id="body_error" data-required="{{ __('Body is required') }}"></div>
                            </div>
                        </div>

                        <!-- Image Ad Fields (Initially Hidden) -->
                        <div id="image_ad_container" style="display: none;">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Image') }}</label>
                                    <input type="file" name="ad_image" id="ad_image" class="form-control">
                                    <div class="invalid-feedback" id="ad_image_error" data-required="{{ __('Image is required') }}"></div>
                                </div>
                            </div>

                            <div id="add-image-preview"></div>

                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Image URL') }}</label>
                                    <input type="text" name="ad_url" id="ad_url" class="form-control" placeholder="{{ __('Enter Image URL') }}" maxlength="300">
                                    <div class="invalid-feedback" id="ad_url_error" data-required="{{ __('Image URL is required') }}"></div>
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Image Alt') }}</label>
                                    <input type="text" name="ad_alt" id="ad_alt" class="form-control" placeholder="{{ __('Enter Image Alt Text') }}" maxlength="300">
                                    <div class="invalid-feedback" id="ad_alt_error" data-required="{{ __('Image Alt text is required') }}"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Position') }}</label>
                                <select name="ad_position" id="ad_position" class="form-control">
                                    <option value="">{{ __('Select option') }}</option>
                                    <option value="afterbegin">{{ __('After Begin') }}</option>
                                    <option value="beforeend">{{ __('Before End') }}</option>
                                </select>
                                <div class="invalid-feedback" id="ad_position_error" data-required="{{ __('Ad position is required') }}"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Selector') }}</label>
                                <input type="text" name="ad_selector" id="ad_selector" class="form-control" placeholder="{{ __('CSS selector like #id-name / .class-name / body > p') }}" maxlength="300">
                                <div class="invalid-feedback" id="ad_selector_error" data-required="{{ __('CSS selector like #id-name / .class-name / body > p') }}"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Custom CSS') }}</label>
                                <input name="ad_custom" id="ad_custom" class="form-control"
                                    placeholder="{{ __('Custom CSS Style like float:right; margin: 10px;') }}">
                                <div class="invalid-feedback" id="ad_custom_error" data-required="{{ __('Custom CSS is required') }}"></div>
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
                    <button type="submit" id="btn_advertisement" class="btn btn-primary add_advertisement_btn"
                        data-save_text="{{ __('Save') }}">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="edit_advertisement">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Edit Advertisement') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="editAdvertisementForm" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="row">
                        <!-- Ad Name -->
                        <div class="form-group col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Ad Name') }}</label>
                                <input type="text" name="edit_ad_name" id="edit_ad_name" class="form-control" placeholder="{{ __('Enter Ad Name') }}" maxlength="100">
                                <div class="invalid-feedback" id="edit_ad_name_error"></div>
                            </div>
                        </div>

                        <!-- Ad Type Selection -->
                        <div class="form-group col-md-6 d-flex gap-3 mt-1 mb-2">
                            <div class="d-flex gap-2">
                                <input type="radio" class="form_control" name="edit_ad_type" id="edit_html_ad" value="HTML">
                                <label for="edit_html_ad" class="text-dark fw-bold">HTML Ad</label>
                            </div>
                            <div>
                                <input type="radio" class="form_control" name="edit_ad_type" id="edit_image_ad" value="IMAGE">
                                <label for="edit_image_ad" class="text-dark fw-bold">IMAGE Ad</label>
                            </div>
                        </div>

                        <!-- HTML Ad Body -->
                        <div class="form-group col-md-12" id="edit_html_ad_container">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Ad Body') }}</label>
                                <textarea name="edit_body" id="edit_body" class="form-control" placeholder="{{ __('Enter Ad Type') }}" style="height: 200px;"></textarea>
                                <div class="invalid-feedback" id="edit_body_error"></div>
                            </div>
                        </div>

                        <!-- Image Ad Fields -->
                        <div id="edit_image_ad_container" style="display: none;">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Image') }}</label>
                                    <input type="file" name="edit_ad_image" id="edit_ad_image" class="form-control">
                                    <div class="invalid-feedback" id="edit_ad_image_error"></div>
                                </div>
                            </div>

                            <div id="image-preview"></div>

                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Image URL') }}</label>
                                    <input type="text" name="edit_ad_url" id="edit_ad_url" class="form-control" placeholder="{{ __('Enter Image URL') }}" maxlength="300">
                                    <div class="invalid-feedback" id="edit_ad_url_error"></div>
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Image Alt') }}</label>
                                    <input type="text" name="edit_ad_alt" id="edit_ad_alt" class="form-control" placeholder="{{ __('Enter Image Alt Text') }}" maxlength="300">
                                    <div class="invalid-feedback" id="edit_ad_alt_error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Position') }}</label>
                                <select name="edit_ad_position" id="edit_ad_position" class="form-control">
                                    <option value="afterbegin">{{ __('After Begin') }}</option>
                                    <option value="beforeend">{{ __('Before End') }}</option>
                                </select>
                                <div class="invalid-feedback" id="edit_ad_position_error"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Selector') }}</label>
                                <input type="text" name="edit_ad_selector" id="edit_ad_selector" class="form-control" placeholder="{{ __('CSS selector like #id-name / .class-name / body > p') }}" maxlength="300">
                                <div class="invalid-feedback" id="edit_ad_selector_error"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Custom CSS') }}</label>
                                <input name="edit_ad_custom" id="edit_ad_custom" class="form-control" placeholder="{{ __('Custom CSS Style like float:right; margin: 10px;') }}">
                                <div class="invalid-feedback" id="edit_ad_custom_error"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                <div class="status-title">
                                    <h5>{{ __('Status') }}</h5>
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
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" id="edit_btn_advertisement" class="btn btn-primary edit_advertisement_btn" data-update_text="{{ __('Update') }}">{{ __('Update') }}</button>
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
                            id="confirmDelete">{{ __('Yes, Delete') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection