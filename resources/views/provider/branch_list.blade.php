@extends('provider.provider')
@section('content')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content">
        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-4">
        <div class="skeleton label-skeleton label-loader"></div>
            <h4 class="d-none real-label">{{__('Branch')}}</h4>
            <div class="d-flex align-items-center flex-wrap row-gap-3">
                @if(isset($permission) && Auth::user()->user_type == 4)
                @if(hasPermission($permission, 'Branch', 'create'))
                <div class="skeleton label-skeleton label-loader"></div>
                <button class="btn btn-dark fixed-size-btn d-none real-label" id="addBranchBtn" data-add_text="{{ __('Add Branch') }}">{{__('Add Branch')}}</button>
                @endif
                @else
                <div class="skeleton label-skeleton label-loader"></div>
                <button class="btn btn-dark fixed-size-btn d-none real-label" id="addBranchBtn" data-add_text="{{ __('Add Branch') }}">{{__('Add Branch')}}</button>
                @endif
            </div>
        </div>
        @if(isset($permission) && Auth::user()->user_type == 4)
        @if(hasPermission($permission, 'Branch', 'delete'))
        @php $delete = 1; @endphp
        @else
        @php $delete = 0; @endphp
        @endif
        @if(hasPermission($permission, 'Branch', 'edit'))
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
                    <table class="table d-none real-label" id="branchTable" data-id="{{ Auth::id() }}">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center">{{__('S.No')}}</th>
                                <th>{{__('Branch Name')}}</th>
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


<!-- Delete  -->
<div class="modal fade custom-modal" id="del_branch">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                <h5 class="modal-title">{{__('Delete Branch')}}</h5>
                <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i
                        class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <div class="modal-body">
                <div class="write-review">
                    <form>
                        <p>{{__('Are you sure want to delete this Branch?')}}</p>
                        <div class="modal-submit text-end">
                            <a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{__('Cancel')}}</a>
                            <button type="submit" class="btn btn-dark" id="confirm_branch_delete">{{__('Yes, Delete')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Delete  -->


<div class="modal fade" id="no_sub" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="mb-4">
                    <span class="warning-icon mx-auto mb-4">
                        <i class="ti ti-alert-circle"></i>
                    </span>
                    <h4 class="mb-1">No Active Subscription or Topup Found</h4>
                    <p class="text-muted">
                        It seems like you do not have an active subscription or topup. Please purchase a subscription or topup to add branch location and manage your account effectively.
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
                    <h4 class="mb-1">Your Branch Location Limit Has Been Reached</h4>
                    <p class="text-muted">
                        You have reached the maximum allowed branch count for your subscription or topup. To continue using the branch locations or add new features, please upgrade your subscription or topup.
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
                    <h4 class="mb-1">Your Subscription or Topup Has Ended</h4>
                    <p class="text-muted">
                        Your subscription or topup period has ended. To continue using the branch locations and adding new features, please renew or purchase a new subscription or topup.
                    </p>
                    <a href="{{ route('provider.subscription') }}" class="btn btn-linear-primary">Renew Subscription</a>
                    <a href="javascript:void(0);" data-bs-dismiss="modal" class="btn btn-outline-secondary text-end">Close</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection