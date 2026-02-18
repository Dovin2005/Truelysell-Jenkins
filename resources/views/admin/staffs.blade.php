@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('Staffs') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('people') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Staffs') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    @if(isset($permission))
                        @if(hasPermission($permission, 'Staffs', 'create'))
                        <div class="skeleton label-skeleton label-loader"></div>
                        <a href="#" class="btn btn-primary d-none real-label" id="add_staff_btn" data-bs-toggle="modal"
                            data-bs-target="#add_staff_modal"><i class="ti ti-square-rounded-plus-filled me-2"></i>{{ __('Add Staff') }}</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @php $isVisible = 0; @endphp
        @if(isset($permission))
            @if(hasPermission($permission, 'Staffs', 'delete'))
                @php $delete = 1; $isVisible = 1; @endphp
            @else
                @php $delete = 0; @endphp
            @endif
            @if(hasPermission($permission, 'Staffs', 'edit'))
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
                    <table class="table d-none" id="staffTable" data-empty="{{ __('staff_empty') }}">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{__('Staffs')}}</th>
                                <th>{{__('Created On')}}</th>
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

<!-- Add Staff-->
<div class="modal fade" id="add_staff_modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{__('Add Staff')}}</h5>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="staffForm">
                <div class="modal-body pb-0">
                    <input type="hidden" name="parent_id" id="parent_id" >
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('profile_picture') }}<span class="text-danger"> *</span></label>
                                <div class="d-flex align-items-center flex-wrap row-gap-3 mb-3">
                                    <div
                                        class="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames">
                                        <img id="imagePreview" src="{{ asset('assets/img/user-default.jpg') }}" alt="Image" width="100" height="100" data-image="{{ asset('assets/img/user-default.jpg') }}">
                                    </div>
                                    <div class="profile-upload">
                                        <div class="profile-uploader d-flex align-items-center">
                                            <div class="drag-upload-btn mb-3">
                                                {{ __('upload') }}
                                                <input type="file" class="form-control image-sign" name="profile_image" id="profile_image">
                                            </div>
                                        </div>
                                        <p>{{ __('image_size_note') }}</p>
                                    </div>
                                </div>
                                <span class="text-danger error-text" id="profile_image_error" data-extension="{{ __('image_extension') }}" data-size="{{ __('image_filesize') }}"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('first_name')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control" name="first_name" id="first_name">
                                <span class="text-danger error-text" id="first_name_error" data-required="{{ __('first_name_required') }}" data-max="{{ __('first_name_maxlength') }}" data-alpha="{{ __('alphabets_allowed') }}"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('last_name')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control" name="last_name" id="last_name">
                                <span class="text-danger error-text" id="last_name_error" data-required="{{ __('last_name_required') }}" data-max="{{ __('last_name_maxlength') }}" data-alpha="{{ __('alphabets_allowed') }}"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('email')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control" name="email" id="email" >
                                <span class="text-danger error-text" id="email_error" data-required="{{ __('email_required') }}" data-format="{{ __('email_format') }}" data-exists="{{ __('email_exists') }}"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('user_name')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control" name="user_name" id="user_name">
                                <span class="text-danger error-text" id="user_name_error" data-required="{{ __('user_name_required') }}" data-max="{{ __('user_name_maxlength') }}" data-exists="{{ __('user_name_exists') }}"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('phone_number')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control staff_phone_number" name="phone_number" id="phone_number">
                                <input type="hidden" id="intl_phone_number" name="international_phone_number">
                                <span class="text-danger error-text" id="phone_number_error" data-required="{{ __('phone_number_required') }}" data-between="{{ __('phone_number_between') }}" ></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('Role')}}<span class="text-danger"> *</span></label>
                                <select class="select role-list" id="role_id" name="role_id" data-placeholder="{{__('Select Role')}}">
                                    <option value="" selected disabled>{{ __('Select Role') }}</option>
                                </select>
                                <span class="text-danger error-text" id="role_id_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('gender')}}<span class="text-danger"> *</span></label>
                                <select class="select" id="gender" name="gender">
                                    <option value="" selected disabled>{{__('select_gender')}}</option>
                                    <option value="male">{{__('male')}}</option>
                                    <option value="female">{{__('female')}}</option>
                                </select>
                                <span class="text-danger error-text" id="gender_error" data-required="{{ __('gender_required') }}"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('date_of_birth')}}<span class="text-danger"></span></label>
                                <div class=" input-icon position-relative">
                                    <input type="date" class="form-control" id="dob" name="dob" max="{{ date('Y-m-d', strtotime('-1 day')) }}">
                                </div>
                                <span class="text-danger error-text" id="dob_error"></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{__('address')}}<span class="text-danger"></span></label>
                                <input type="text" class="form-control" name="address" id="address">
                                <span class="text-danger error-text" id="address_error" data-required="{{ __('address_required') }}" data-max="{{ __('address_maxlength') }}"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('country')}}<span class="text-danger"></span></label>
                                <select class="select selects country" id="country" name="country" data-placeholder="{{__('select_country')}}">
                                    <option value="">{{ __('select_country') }}</option>
                                    @if ($countries)
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <span class="text-danger error-text" id="country_error" data-required="{{ __('country_required') }}"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('state')}}<span class="text-danger"></span></label>
                                <select class="select selects state" id="state" name="state" data-placeholder="{{__('select_state')}}">
                                </select>
                                <span class="text-danger error-text" id="state_error" data-required="{{ __('state_required') }}"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('city')}}<span class="text-danger"></span></label>
                                <select class="select selects city" id="city" name="city" data-placeholder="{{__('select_city')}}">
                                </select>
                                <span class="text-danger error-text" id="city_error" data-required="{{ __('city_required') }}"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('postal_code')}}<span class="text-danger"></span></label>
                                <input type="text" class="form-control" name="postal_code" id="postal_code">
                                <span class="text-danger error-text" id="postal_code_error" data-required="{{ __('postal_code_required') }}" data-max="{{ __('postal_code_maxlength') }}" data-char="{{ __('postal_code_char') }}"></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{__('Bio')}}</label>
                                <textarea class="form-control" rows="3" name="bio" id="bio"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                    <div class="status-title">
                                        <h5>{{ __('Status') }}</h5>
                                    </div>
                                    <div class="status-toggle modal-status">
                                        <input type="checkbox" id="status" name="status" class="check" checked>
                                        <label for="status" class="checktoggle"> </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-end align-items-center">
                        <a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{__('Cancel')}}</a>
                        <button class="btn btn-primary" type="submit" id="staff_save_btn" data-save="{{ __('Save') }}">{{__('Save')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Add Staff -->

<!-- /Edit Staff-->
<div class="modal fade" id="edit_staff_modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                <h5 class="modal-title">{{__('edit_staff')}}</h5>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="editStaffForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('profile_picture') }}<span class="text-danger"> *</span></label>
                                <div class="d-flex align-items-center flex-wrap row-gap-3 mb-3">
                                    <div
                                        class="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames">
                                        <img id="editImagePreview" src="{{ asset('assets/img/user-default.jpg') }}" alt="Image" width="100" height="100" data-image="{{ asset('assets/img/user-default.jpg') }}">
                                    </div>
                                    <div class="profile-upload">
                                        <div class="profile-uploader d-flex align-items-center">
                                            <div class="drag-upload-btn mb-3">
                                                {{ __('upload') }}
                                                <input type="file" class="form-control image-sign" name="profile_image" id="edit_profile_image">
                                            </div>
                                        </div>
                                        <p>{{ __('image_size_note') }}</p>
                                    </div>
                                </div>
                                <span class="text-danger error-text" id="edit_profile_image_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('first_name')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control" name="first_name" id="edit_first_name">
                                <span class="text-danger error-text" id="edit_first_name_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('last_name')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control" name="last_name" id="edit_last_name">
                                <span class="text-danger error-text" id="edit_last_name_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('email')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control" name="email" id="edit_email">
                                <span class="text-danger error-text" id="edit_email_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('user_name')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control" name="user_name" id="edit_user_name">
                                <span class="text-danger error-text" id="edit_user_name_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('phone_number')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control edit_staff_phone_number" name="phone_number" id="edit_phone_number">
                                <input type="hidden" id="edit_staff_phone_number" name="international_phone_number">
                                <span class="text-danger error-text" id="edit_phone_number_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('Role')}}<span class="text-danger"> *</span></label>
                                <select class="select role-list" id="edit_role" name="role_id" data-placeholder="{{__('Select Role')}}">
                                    <option value="" selected disabled>{{ __('Select Role') }}</option>
                                </select>
                                <span class="text-danger error-text" id="edit_role_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('gender')}}<span class="text-danger"> *</span></label>
                                <select class="select select2" id="edit_gender" name="gender">
                                    <option value="" selected disabled>{{__('select_gender')}}</option>
                                    <option value="male">{{__('male')}}</option>
                                    <option value="female">{{__('female')}}</option>
                                </select>
                                <span class="text-danger error-text" id="edit_gender_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('date_of_birth')}}<span class="text-danger"></span></label>
                                <div class=" input-icon position-relative">
                                    <input type="date" class="form-control" id="edit_dob" name="dob" max="{{ date('Y-m-d', strtotime('-1 day')) }}">
                                </div>
                                <span class="text-danger error-text" id="edit_dob_error"></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{__('address')}}<span class="text-danger"></span></label>
                                <input type="text" class="form-control" name="address" id="edit_address">
                                <span class="text-danger error-text" id="edit_address_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('country')}}<span class="text-danger"></span></label>
                                <select class="select selects country" id="edit_country" name="country" data-placeholder="{{__('select_country')}}">
                                    <option value="">{{ __('select_country') }}</option>
                                    @if ($countries)
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <span class="text-danger error-text" id="edit_country_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('state')}}<span class="text-danger"></span></label>
                                <select class="select selects state" id="edit_state" name="state" data-placeholder="{{__('select_state')}}">
                                </select>
                                <span class="text-danger error-text" id="edit_state_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('city')}}<span class="text-danger"></span></label>
                                <select class="select selects city" id="edit_city" name="city" data-placeholder="{{__('select_city')}}">
                                </select>
                                <span class="text-danger error-text" id="edit_city_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('postal_code')}}<span class="text-danger"></span></label>
                                <input type="text" class="form-control" name="postal_code" id="edit_postal_code">
                                <span class="text-danger error-text" id="edit_postal_code_error"></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{__('Bio')}}</label>
                                <textarea class="form-control" rows="3" name="bio" id="edit_bio"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                    <div class="status-title">
                                        <h5>{{ __('Status') }}</h5>
                                    </div>
                                    <div class="status-toggle modal-status">
                                        <input type="checkbox" id="edit_status" name="status" class="check" checked>
                                        <label for="edit_status" class="checktoggle"> </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{__('Cancel')}}</a>
                    <button class="btn btn-primary" type="submit" id="staff_edit_btn" data-save="{{ __('Save') }}">{{__('Save')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Add Staff -->

<div class="modal fade" id="del-staff">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteStaffForm">
                <div class="modal-body text-center">
                    <span class="delete-icon">
                        <i class="ti ti-trash-x"></i>
                    </span>
                    <h4>{{ __('Confirm Deletion') }}</h4>
                    <p>{{ __('Are you sure want to delete this Staff?') }}</p>
                    <input type="hidden" name="delete_id" id="delete_id">
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-danger" id="confirm_staff_delete">{{ __('Yes, Delete') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
