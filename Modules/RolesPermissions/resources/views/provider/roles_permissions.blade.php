@extends('provider.provider')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-4">
            <div class="skeleton label-skeleton label-loader"></div>
            <h4 class="d-none real-label">{{__('Roles & Permissions')}}</h4>
            <div class="d-flex align-items-center flex-wrap row-gap-3">
                @if(isset($permission) && Auth::user()->user_type == 4)
                    @if(hasPermission($permission, 'Roles & Permission', 'create'))
                    <div class="skeleton label-skeleton label-loader"></div>
                        <a href="#" class="btn btn-dark d-flex align-items-center d-none real-label" id="add_role_btn" data-bs-toggle="modal" data-bs-target="#role_modal"><i class="ti ti-circle-plus me-2"></i>{{ __('Add Role') }}</a>
                    @endif
                @else
                <div class="skeleton label-skeleton label-loader"></div>
                    <a href="#" class="btn btn-dark d-flex align-items-center d-none real-label" id="add_role_btn" data-bs-toggle="modal" data-bs-target="#role_modal"><i class="ti ti-circle-plus me-2"></i>{{ __('Add Role') }}</a>
                @endif
            </div>
        </div>
        @if(isset($permission) && Auth::user()->user_type == 4)
            @if(hasPermission($permission, 'Roles & Permission', 'delete'))
                @php $delete = 1; @endphp
            @else
                @php $delete = 0; @endphp
            @endif
            @if(hasPermission($permission, 'Roles & Permission', 'edit'))
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
                    <table class="table d-none real-label" id="roleTable" data-empty="{{ __('No roles available') }}" data-edit="{{ __('Edit') }}" data-delete="{{ __('Delete') }}" data-permission="{{ __('Permissions') }}">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('S.No') }}</th>
                                <th>{{ __('Role Name') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
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
                                    <p class="d-none real-data">4</p>
                                </td>
                                <td>
                                    <div class="skeleton data-skeleton data-loader"></div>
                                    <p class="d-none real-data">Emily Davis</p>
                                </td>
                                <td>
                                    <div class="skeleton data-skeleton data-loader"></div>
                                    <p class="d-none real-data">emilydavis@example.com</p>
                                </td>
                                <td>
                                    <div class="skeleton data-skeleton data-loader"></div>
                                    <p class="d-none real-data">Customer</p>
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
<!-- /Page Wrapper -->


<div class="modal fade" id="role_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                <h4 class="modal-title role_modal_title" data-add_text="{{ __('Add Role') }}" data-edit_text="{{ __('Edit Role') }}">{{ __('Add Role') }}</h4>
                <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i
                    class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <form id="roleForm">
                <div class="modal-body pb-0">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden" name="method" id="method">
                            <input type="hidden" name="id" id="id">
                            <input type="hidden" name="user_id" id="user_id" value="{{ Auth::id() }}">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Role Name') }}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control" id="role_name" name="role_name" placeholder="{{ __('Enter Role Name') }}">
                                <span class="text-danger error-text" id="role_name_error" data-required="{{ __('Role name is required.') }}" data-exists="{{ __('Role name already exists.') }}" data-max="{{ __('role_name_maxlength') }}"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-end align-items-center">
                        <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-dark role_save_btn" data-save="{{ __('Save') }}">{{ __('Save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="delete_role_modal">
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
                        <a href="javascript:void(0);" class="btn btn-light me-3"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-danger delete_role_confirm">{{ __('Yes, Delete') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="permission_modal">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                <h4 class="modal-title">{{ __('Permission List') }}</h4>
                <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i
                    class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <form id="permissionForm">
                <div class="modal-body pb-0">
                    <div class="custom-datatable-filter p-2 border-0">
                        <div class="table-responsive">
                            <table class="table" id="permissionTable" data-empty="{{ __('No permissions available') }}">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('S.No') }}</th>
                                        <th>{{ __('Modules') }}</th>
                                        <th>{{ __('Create') }}</th>
                                        <th>{{ __('View') }}</th>
                                        <th>{{ __('Edit') }}</th>
                                        <th>{{ __('Delete') }}</th>
                                        <th>{{ __('Allow All') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
        
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-end align-items-center">
                        <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-dark" id="savePermissions" data-save="{{ __('Save') }}">{{ __('Save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
