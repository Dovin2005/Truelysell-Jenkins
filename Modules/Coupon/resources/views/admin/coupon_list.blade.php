@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('Coupons') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('application') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Coupons') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    @if(isset($permission))
                        @if(hasPermission($permission, 'Coupon', 'create'))
                        <div class="skeleton label-skeleton label-loader"></div>
                        <a href="{{ route('admin.create-coupon') }}" class="btn btn-primary d-none real-label"><i class="ti ti-square-rounded-plus-filled me-2"></i>{{ __('add_coupon') }}</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @php $isVisible = 0; @endphp
        @if(isset($permission))
            @if(hasPermission($permission, 'Coupon', 'delete'))
                @php $delete = 1; $isVisible = 1; @endphp
            @else
                @php $delete = 0; @endphp
            @endif
            @if(hasPermission($permission, 'Coupon', 'edit'))
                @php $edit = 1; $isVisible = 1; @endphp
            @else
                @php $edit = 0; @endphp
            @endif
            <div id="has_permission" data-delete="{{ $delete }}" data-edit="{{ $edit }}" data-visible="{{ $isVisible }}"></div>
        @else
            <div id="has_permission" data-delete="1" data-edit="1"></div>
        @endif
        <div class="card">
            <ul class="nav nav-tabs p-3 pb-0" id="transactionsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active valid_coupon"
                        data-bs-toggle="tab"
                        data-bs-target="#all-booking"
                        type="button"
                        role="tab"
                        aria-controls="all-booking"
                        aria-selected="true"
                        data-valid="1">
                        {{ __('Valid') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link valid_coupon"
                        data-bs-toggle="tab"
                        data-bs-target="#pending"
                        type="button"
                        role="tab"
                        aria-controls="pending"
                        aria-selected="false"
                        data-valid="0">
                        {{ __('Expired') }}
                    </button>
                </li>
            </ul>
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
                    <table class="table d-none" id="couponTable" data-empty="{{ __('coupon_empty_info') }}">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('Coupon Code') }}</th>
                                <th>{{ __('Coupon Type') }}</th>
                                <th>{{ __('Coupon Value') }}</th>
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

<div class="modal fade" id="delete_coupon_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteCouponForm">
                <div class="modal-body text-center">
                    <span class="delete-icon">
                        <i class="ti ti-trash-x"></i>
                    </span>
                    <h4>{{ __('Confirm Deletion') }}</h4>
                    <p>{{ __('confirm_delete') }}</p>
                    <input type="hidden" name="delete_id" id="delete_id">
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-danger delete_coupon_confirm">{{ __('Yes, Delete') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@include('coupon::admin.includes')
