@extends('provider.provider')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-4">
            <div class="skeleton label-skeleton label-loader"></div>
            <h4 class="d-none real-label">{{__('Staffs')}}</h4>
            <div class="d-flex align-items-center flex-wrap row-gap-3">
                @if(isset($permission) && Auth::user()->user_type == 4)
                @if(hasPermission($permission, 'Staff', 'create'))
                <div class="skeleton label-skeleton label-loader"></div>
                <button class="btn btn-dark fixed-size-btn d-none real-label" id="add_staff_btn" data-add_text="{{ __('Add Staff') }}"><i class="ti ti-circle-plus me-2"></i>{{__('Add Staff')}}</button>
                @endif
                @else
                <div class="skeleton label-skeleton label-loader"></div>
                <button class="btn btn-dark fixed-size-btn d-none real-label" id="add_staff_btn" data-add_text="{{ __('Add Staff') }}"><i class="ti ti-circle-plus me-2"></i>{{__('Add Staff')}}</button>
                @endif
            </div>
        </div>
        @if(isset($permission) && Auth::user()->user_type == 4)
        @if(hasPermission($permission, 'Staff', 'delete'))
        @php $delete = 1; @endphp
        @else
        @php $delete = 0; @endphp
        @endif
        @if(hasPermission($permission, 'Staff', 'edit'))
        @php $edit = 1; @endphp
        @else
        @php $edit = 0; @endphp
        @endif
        <div id="has_permission" data-delete="{{ $delete }}" data-edit="{{ $edit }}"></div>
        @else
        <div id="has_permission" data-delete="1" data-edit="1"></div>
        @endif
        <div class="row">
            <div class="custom-datatable-filter p-2 border-0">
                <div class="table-responsive">
                    <table class="table d-none real-label" id="staffTable" data-empty="{{ __('staff_empty') }}" a>
                        <thead class="thead-light">
                            <tr>
                                <th>{{__('S.No')}}</th>
                                <th>{{__('Staffs')}}</th>
                                <th>{{__('Created On')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Action')}}</th>
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
                                    <p class="d-none real-label"></p>
                                </th>
                                <th>
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <p class="d-none real-label"></p>
                                </th>
                                <th>
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <p class="d-none real-label"></p>
                                </th>
                                <th>
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <p class="d-none real-label"></p>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < 8; $i++)
                                <tr>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                        <p class="d-none real-data"></p>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                        <p class="d-none real-data"></p>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                        <p class="d-none real-data"></p>
                                    </td>
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                        <p class="d-none real-data"></p>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                    <!-- loader Datatable End -->
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Page Wrapper -->

<!-- Add Staff-->
<div class="modal fade custom-modal" id="add_staff_modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content doctor-profile">
            <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                <h5 class="modal-title" id="model_staff_title">{{__('Add Staff')}}</h5>
                <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i
                        class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <form id="staffForm">
                <div class="modal-body pb-0">
                    <input type="hidden" name="parent_id" id="parent_id" value="{{ Auth::user()->id }}">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('profile_picture') }}</label>
                                <div class="d-flex align-items-center flex-wrap row-gap-3 gap-3">
                                    <div class="file-upload d-flex align-items-center justify-content-center flex-column">
                                        <i class="ti ti-photo mb-2"></i>
                                        <label class="form-label">{{ __('Add Image') }}</label>
                                        <input type="file" name="profile_image" id="profile_image" class="form-control">
                                    </div>
                                    <img id="imagePreview" src="{{ asset("front/img/default-placeholder-image.png") }}" alt="Image" width="120" height="120" data-image="{{ asset('assets/img/profile-default.png') }}">
                                </div>
                                <span class="text-danger error-text" id="profile_image_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('first_name')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control pass-input" name="first_name" id="first_name">
                                <span class="text-danger error-text" id="first_name_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('last_name')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control pass-input" name="last_name" id="last_name">
                                <span class="text-danger error-text" id="last_name_error"></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{__('email')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control pass-input" name="email" id="email">
                                <span class="text-danger error-text" id="email_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('user_name')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control pass-input" name="user_name" id="user_name">
                                <span class="text-danger error-text" id="user_name_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('phone_number')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control pass-input staff_phone_number" name="phone_number" id="phone_number">
                                <input type="hidden" id="staff_phone_number" name="international_phone_number">
                                <span class="text-danger error-text" id="phone_number_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('gender')}}<span class="text-danger"> *</span></label>
                                <select class="form-control select select2" id="gender" name="gender" data-placeholder="{{__('select_gender')}}">
                                    <option value="" selected disabled>{{__('select_gender')}}</option>
                                    <option value="male">{{__('male')}}</option>
                                    <option value="female">{{__('female')}}</option>
                                </select>
                                <span class="text-danger error-text" id="gender_error"></span>
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
                                <input type="text" class="form-control pass-input" name="address" id="address">
                                <span class="text-danger error-text" id="address_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('country')}}<span class="text-danger"></span></label>
                                <select class="select2 selects country" id="country" name="country" data-placeholder="{{__('select_country')}}">
                                </select>
                                <span class="text-danger error-text" id="country_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('state')}}<span class="text-danger"></span></label>
                                <select class="select2 selects state" id="state" name="state" data-placeholder="{{__('select_state')}}">
                                </select>
                                <span class="text-danger error-text" id="state_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('city')}}<span class="text-danger"></span></label>
                                <select class="select2 selects city" id="city" name="city" data-placeholder="{{__('select_city')}}">
                                </select>
                                <span class="text-danger error-text" id="city_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('postal_code')}}<span class="text-danger"></span></label>
                                <input type="text" class="form-control pass-input" name="postal_code" id="postal_code">
                                <span class="text-danger error-text" id="postal_code_error"></span>
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
                                <label class="form-label">{{__('Category')}}<span class="text-danger"> *</span></label>
                                <select name="category" id="category" class="form-control select select2" data-placeholder="{{ __('Select Category') }}">
                                    <option value="" selected disabled>{{ __('Select Category') }}</option>
                                    @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-text" id="category_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('Sub Category')}}</label>
                                <select name="subcategory_id" id="subcategory_id" class="form-control select select2 subcategory-list" data-placeholder="{{ __('select_sub_category') }}">
                                    <option value="" selected disabled>{{ __('select_sub_category') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{__('Branch Location')}}</label>
                                <select class="select form-control select2 branch-list" id="branch_id" name="branch_id[]" data-placeholder="{{__('Select Branch')}}" multiple>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('Role')}}<span class="text-danger"> *</span></label>
                                <select class="form-control select select2 role-list" id="role_id" name="role_id" data-placeholder="{{__('Select Role')}}">
                                    <option value="" selected disabled>{{ __('Select Role') }}</option>
                                </select>
                                <span class="text-danger error-text" id="role_id_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('Status')}}</label>
                                <select class="form-control select select2" name="status" id="status">
                                    <option value="1">{{ __('Active') }}</option>
                                    <option value="0">{{ __('Inactive') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-end align-items-center">
                        <a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{__('Cancel')}}</a>
                        <button class="btn btn-dark" type="submit" id="staff_save_btn" data-save_text="{{ __('Save') }}">{{__('Save')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Add Staff -->

<!-- /Edit Staff-->
<div class="modal fade custom-modal" id="edit_staff_modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content doctor-profile">
            <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                <h5 class="modal-title" id="model_staff_title">{{__('edit_staff')}}</h5>
                <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i
                        class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <form id="editStaffForm">
                <div class="modal-body pb-0">
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('profile_picture') }}</label>
                                <div class="d-flex align-items-center flex-wrap row-gap-3 gap-3">
                                    <div class="file-upload d-flex align-items-center justify-content-center flex-column">
                                        <i class="ti ti-photo mb-2"></i>
                                        <label class="form-label">{{ __('Edit Image') }}</label>
                                        <input type="file" name="profile_image" id="edit_profile_image" class="form-control">
                                    </div>
                                    <img id="editImagePreview" src="{{ asset("front/img/default-placeholder-image.png") }}" alt="Image" width="120" height="120" data-image="{{ asset('assets/img/profile-default.png') }}">
                                </div>
                                <span class="text-danger error-text" id="edit_profile_image_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('first_name')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control pass-input" name="first_name" id="edit_first_name">
                                <span class="text-danger error-text" id="edit_first_name_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('last_name')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control pass-input" name="last_name" id="edit_last_name">
                                <span class="text-danger error-text" id="edit_last_name_error"></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{__('email')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control pass-input" name="email" id="edit_email">
                                <span class="text-danger error-text" id="edit_email_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('user_name')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control pass-input" name="user_name" id="edit_user_name">
                                <span class="text-danger error-text" id="edit_user_name_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('phone_number')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control pass-input edit_staff_phone_number" name="phone_number" id="edit_phone_number">
                                <input type="hidden" id="edit_staff_phone_number" name="international_phone_number">
                                <span class="text-danger error-text" id="edit_phone_number_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('gender')}}<span class="text-danger"> *</span></label>
                                <select class="form-control select select2" id="edit_gender" name="gender" data-placeholder="{{__('select_gender')}}">
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
                                <input type="text" class="form-control pass-input" name="address" id="edit_address">
                                <span class="text-danger error-text" id="edit_address_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('country')}}<span class="text-danger"></span></label>
                                <select class="select2 selects country" id="edit_country" name="country" data-placeholder="{{__('select_country')}}">
                                </select>
                                <span class="text-danger error-text" id="edit_country_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('state')}}<span class="text-danger"></span></label>
                                <select class="select2 selects state" id="edit_state" name="state" data-placeholder="{{__('select_state')}}">
                                </select>
                                <span class="text-danger error-text" id="edit_state_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('city')}}<span class="text-danger"></span></label>
                                <select class="select2 selects city" id="edit_city" name="city" data-placeholder="{{__('select_city')}}">
                                </select>
                                <span class="text-danger error-text" id="edit_city_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('postal_code')}}<span class="text-danger"></span></label>
                                <input type="text" class="form-control pass-input" name="postal_code" id="edit_postal_code">
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
                                <label class="form-label">{{__('Category')}}<span class="text-danger"> *</span></label>
                                <select name="category" id="edit_category" class="form-control select select2" data-placeholder="{{ __('Select Category') }}">
                                    <option value="" selected disabled>{{ __('Select Category') }}</option>
                                    @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-text" id="edit_category_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('Sub Category')}}</label>
                                <select name="subcategory_id" id="edit_subcategory_id" class="form-control select select2 subcategory-list" data-placeholder="{{ __('select_sub_category') }}">
                                    <option value="" selected disabled>{{ __('select_sub_category') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{__('Branch Location')}}</label>
                                <select class="select form-control select2 branch-list" id="edit_branch_id" name="branch_id[]" data-placeholder="{{__('Select Branch')}}" multiple>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class=" mb-3">
                                <label class="form-label">{{__('Role')}}<span class="text-danger"> *</span></label>
                                <select class="form-control select select2 role-list" id="edit_role" name="role_id" data-placeholder="{{__('Select Role')}}">
                                    <option value="" selected disabled>{{ __('Select Role') }}</option>
                                </select>
                                <span class="text-danger error-text" id="edit_role_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{__('Status')}}</label>
                                <select class="form-control select select2" name="status" id="edit_status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-end align-items-center">
                        <a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{__('Cancel')}}</a>
                        <button class="btn btn-dark" type="submit" id="staff_edit_btn">{{__('Update')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Add Staff -->

<!-- Delete Staff -->
<div class="modal fade custom-modal" id="del-staff">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                <h5 class="modal-title">{{__('Delete Staff')}}</h5>
                <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i
                        class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <div class="modal-body">
                <div class="write-review">
                    <form>
                        <p>{{__('Are you sure want to delete this Staff?')}}</p>
                        <div class="modal-submit text-end">
                            <a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{__('Cancel')}}</a>
                            <button type="submit" class="btn btn-dark" id="confirm_staff_delete">{{__('Yes, Delete')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Delete Staff -->

<div class="modal fade" id="no_sub" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="mb-4">
                    <span class="warning-icon mx-auto mb-4">
                        <i class="ti ti-alert-circle"></i>
                    </span>
                    <h4 class="mb-1">No Active Subscription Found</h4>
                    <p class="text-muted">
                        It seems like you do not have an active subscription. Please purchase a subscription to add staff and manage your account effectively.
                    </p>
                    <a href="{{ route('provider.subscription') }}" class="btn btn-linear-primary">Get a Subscription</a>
                    <a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-outline-secondary">Close</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sub_count_end" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="mb-4">
                    <span class="warning-icon mx-auto fs-2 mb-4">
                        <i class="ti ti-alert-circle"></i>
                    </span>
                    <h4 class="mb-1">Your Staff Limit Has Been Reached</h4>
                    <p class="text-muted">
                        You have reached the maximum allowed staff count for your subscription. To continue using the staff or add new features, please upgrade your subscription.
                    </p>
                    <a href="{{ route('provider.subscription') }}" class="btn btn-linear-primary">Renew Subscription</a>
                    <a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-outline-secondary text-end">Close</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sub_end" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="mb-4">
                    <span class="warning-icon mx-auto fs-2 mb-4">
                        <i class="ti ti-alert-circle"></i>
                    </span>
                    <h4 class="mb-1">Your Subscription Has Ended</h4>
                    <p class="text-muted">
                        Your subscription period has ended. To continue using the staff and adding new features, please renew or purchase a new subscription.
                    </p>
                    <a href="{{ route('provider.subscription') }}" class="btn btn-linear-primary">Renew Subscription</a>
                    <a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-outline-secondary text-end">Close</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection