@extends('admin.admin')

@section('content')
<div class="page-wrapper">
    <div class="content bg-white">
        <div class="d-md-flex d-block align-items-center justify-content-between -bottom pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('Services') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Services') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Services') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        @php $isVisible = 0; @endphp
        @if(isset($permission))
            @if(hasPermission($permission, 'Service', 'delete'))
                @php $delete = 1; $isVisible = 1; @endphp
            @else
                @php $delete = 0; @endphp
            @endif
            @if(hasPermission($permission, 'Service', 'edit'))
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
                <div class="-start ">
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
                                <table class="table d-none" id="currency_table">
                                    <thead class="thead-light">
                                        <tr>
											<th>{{ __('name') }}</th>
											<th>{{ __('Category') }}</th>
											<th>{{ __('Code') }}</th>                                                   
											@if ($edit == 1)
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('verify_status') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="currency_list"></tbody>
                                </table>
                                <nav class="mx-3 mt-3">
                                    <ul class="pagination" id="pagination">
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="delete-modal">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form>
                                    <input type="hidden" name="delte_ser_id" id="delte_ser_id">
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
                    <div class="modal fade" id="verifyServiceModal">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" id="verifyServiceForm">
                                    @csrf
                                    <input type="hidden" name="service_id" id="service_id">
                                    <div class="modal-body text-center">
                                        <span class="fs-30 text-success">
                                            <i class="ti ti-shield-check"></i>
                                        </span>
                                        <h4>{{ __('confirm_verification') }}</h4>
                                        <p>{{ __('confirm_service_verification_info') }}</p>
                                        <div class="d-flex justify-content-center">
                                            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                            <button type="button" class="btn btn-success" id="confirmVerifyBtn" data-verifying="{{ __('verifying') }}" data-yes_verify="{{ __('Yes, Verify') }}">{{ __('Yes, Verify') }}</button>
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
