@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content bg-white">
        <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('Subscription Package') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Settings') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Subscription Package') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="mb-2">
                @if(isset($permission))
                    @if(hasPermission($permission, 'General Settings', 'create'))
                        <div class="skeleton label-skeleton label-loader"></div>
                        <a href="#" class="btn btn-primary d-none real-label" data-bs-toggle="modal" data-bs-target="#add_subscription_package"><i class="ti ti-square-rounded-plus-filled me-2"></i>{{ __('Add Package') }}</a>
                    @endif
                @endif           
            </div>
        </div>
        @php $isVisible = 0; @endphp
            @if(isset($permission))
                @if(hasPermission($permission, 'General Settings', 'delete'))
                    @php $delete = 1; $isVisible = 1; @endphp
                @else
                    @php $delete = 0; @endphp
                @endif
                @if(hasPermission($permission, 'General Settings', 'edit'))
                    @php $edit = 1; $isVisible = 1; @endphp
                @else
                    @php $edit = 0; @endphp
                @endif
                <div id="has_permission" data-delete="{{ $delete }}" data-edit="{{ $edit }}" data-visible="{{ $isVisible }}"></div>
            @else
            <div id="has_permission" data-delete="1" data-edit="1"></div>
            @endif
        <div class="row">
            @include('admin.partials.general_settings_side_menu')
            <div class="col-xxl-10 col-xl-9">
                <div class="ps-1">
                    <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                    </div>
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
                                <table class="table d-none" id="subscription_datatable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ __('SN') }}</th>
                                            <th>{{ __('Package Title') }}</th>
                                            <th>{{ __('Subscription Type') }}</th>
                                            <th>{{ __('Price') }}</th>
                                            <th>{{ __('Package Term') }}</th>
                                            <th>{{ __('Package Duration') }}</th>
                                            <th>{{ __('No of services') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            @if ($isVisible == 1)
												<th>{{ __('Action') }}</th>
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
    </div>
</div>

<div class="modal fade" id="add_subscription_package">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Add Package') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="addSubscriptionForm" autocomplete="off">
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Package Title') }}</label>
                                <input type="text" name="package_title" id="package_title" class="form-control" placeholder="{{ __('Enter Package title') }}" maxlength="30">
                                <div class="invalid-feedback" id="package_title_error"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Price') }}</label>
                                <input type="number" name="price" id="price" class="form-control" placeholder="{{ __('Enter Price') }}" maxlength="5">
                                <div class="invalid-feedback" id="price_error"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Package Term') }}</label>
                                <select name="package_term" id="package_term" class="form-control" onchange="toggleInputFields()">
                                    <option value="">{{ __('--Select Option--') }}</option>
                                    <option value="day">{{ __('Day') }}</option>
                                    <option value="month">{{ __('Month') }}</option>
                                    <option value="yearly">{{ __('Yearly') }}</option>
                                    <option value="lifetime">{{ __('Lifetime') }}</option>
                                </select>
                                <div class="invalid-feedback" id="package_term_error"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <div class="mb-3">
                                <label id="show_input" style="display: block;" class="form-label">{{ __('Select Day/Month') }}</label>
                                <label id="day_input" style="display: none;" class="form-label">{{ __('Number Of Day') }}</label>
                                <label id="month_input" style="display: none;" class="form-label">{{ __('Number Of Month') }}</label>
                                <input type="number" name="package_duration" id="package_duration" class="form-control" placeholder="{{ __('Enter Package Duration') }}" maxlength="3" disabled>
                                <div class="invalid-feedback" id="package_duration_error"></div>
                            </div>

                        </div>
                        <div class="form-group col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Subscription Type') }}</label>
                                <select name="subscription_type" id="subscription_type" class="form-control">
                                    <option value="regular">{{ __('Regular') }}</option>
                                    <option value="topup">{{ __('Topup') }}</option>
                                    <option value="promote">{{ __('promote') }}</option>
                                </select>
                                <div class="invalid-feedback" id="subscription_type_error"></div>
                            </div>
                        </div>
                        <div class="form-group col-md-6 hide_subscription_data">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Number of services') }}</label>
                                <input type="text" name="number_of_service" id="number_of_service" class="form-control" placeholder="{{ __('Enter Number of services') }}" maxlength="3">
                                <div class="invalid-feedback" id="number_of_service_error"></div>
                            </div>
                        </div>
                        <div class="form-group col-md-6 hide_subscription_data">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Number of locations') }}</label>
                                <input type="text" name="number_of_locations" id="number_of_locations" class="form-control" placeholder="{{ __('Enter Number of locations') }}" maxlength="3">
                                <div class="invalid-feedback" id="number_of_locations_error"></div>
                            </div>
                        </div>
                        <div class="form-group col-md-6 hide_subscription_data">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Number of Staff') }}</label>
                                <input type="number" class="form-control" name="number_of_staff" id="number_of_staff" placeholder="{{ __('Enter No of staff') }}">
                                <div class="invalid-feedback" id="number_of_staff_error"></div>
                            </div>
                        </div>

                        <div class="row hide_subscription_data">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="modal-satus-toggle d-flex align-items-center">
                                        <h5 class="me-2">{{ __('featured') }}</h5>
                                        <div class="status-toggle modal-status">
                                            <input type="checkbox" id="featured" name="featured" class="check">
                                            <label for="featured" class="checktoggle"> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="modal-satus-toggle d-flex align-items-center">
                                        <h5 class="me-2">{{ __('badge') }}</h5>
                                        <div class="status-toggle modal-status">
                                            <input type="checkbox" id="badge" name="badge" class="check">
                                            <label for="badge" class="checktoggle"> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row recurring_subscription">
                            <h5 class="mb-3">{{ __('Recurring Subscription') }}</h5>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="modal-satus-toggle d-flex align-items-center">
                                        <h6 class="me-2">{{ __('stripe') }}</h6>
                                        <div class="status-toggle modal-status">
                                            <input type="checkbox" id="stripe_recurring" name="stripe_recurring" class="check">
                                            <label for="stripe_recurring" class="checktoggle"> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="modal-satus-toggle d-flex align-items-center">
                                        <h6 class="me-2">{{ __('paypal') }}</h6>
                                        <div class="status-toggle modal-status">
                                            <input type="checkbox" id="paypal_recurring" name="paypal_recurring" class="check">
                                            <label for="paypal_recurring" class="checktoggle"> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row promote_subscription d-none">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="modal-satus-toggle d-flex align-items-center">
                                        <h5 class="me-2">{{ __('promote_service') }}</h5>
                                        <div class="status-toggle modal-status">
                                            <input type="checkbox" id="promoted_service" name="promoted_service" class="check">
                                            <label for="promoted_service" class="checktoggle"> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="modal-satus-toggle d-flex align-items-center">
                                        <h5 class="me-2">{{ __('promote_provider') }}</h5>
                                        <div class="status-toggle modal-status">
                                            <input type="checkbox" id="promoted_jobber" name="promoted_jobber" class="check">
                                            <label for="promoted_jobber" class="checktoggle"> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea type="text" name="description" id="description" class="form-control" placeholder="{{ __('Enter Description') }}" maxlength="200"></textarea>
                                <div class="invalid-feedback" id="description_error"></div>
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                <div class="status-title">
                                    <h5>{{ __('Status Toggle') }}</h5>
                                    <p>{{ __('Change the Status by toggle') }}</p>
                                </div>
                                <div class="status-toggle modal-status">
                                    <input type="checkbox" id="status" class="check user8">
                                    <label for="status" class="checktoggle"></label>
                                </div>
                                <div class="invalid-feedback" id="status_error"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" id="btn_sub" class="btn btn-primary subscription_package_btn" data-update-text="{{ __('Save') }}">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="edit_subscription_package">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Edit Package') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="editSubscriptionForm" autocomplete="off">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <input type="number" name="edit_id" id="edit_id" hidden>
                        <div class="form-group col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Package Title') }}</label>
                                <input type="text" class="form-control" name="edit_package_title" id="edit_package_title" placeholder="{{ __('Enter Package title') }}">
                                <div class="invalid-feedback" id="edit_package_title_error"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Price') }}</label>
                                <input type="number" class="form-control" name="edit_price" id="edit_price" placeholder="{{ __('Enter Price') }}">
                                <div class="invalid-feedback" id="edit_price_error"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Package Term') }}</label>
                                <select name="edit_package_term" id="edit_package_term" class="form-control">
                                    <option value="day">{{ __('Day') }}</option>
                                    <option value="month">{{ __('Month') }}</option>
                                    <option value="yearly">{{ __('Yearly') }}</option>
                                    <option value="lifetime">{{ __('Lifetime') }}</option>
                                </select>
                                <div class="invalid-feedback" id="package_term_error"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <div class="mb-3">
                                <label id="duration_label" class="form-label">{{ __('Package Duration') }}</label>
                                <input type="number" name="edit_package_duration" id="edit_package_duration" class="form-control duration" placeholder="{{ __('Enter Day/Month') }}" disabled>
                                <div class="invalid-feedback" id="edit_package_duration_error"></div>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Subscription Type') }}</label>
                                <select name="edit_subscription_type" id="edit_subscription_type" class="form-control">
                                    <option value="regular">{{ __('Regular') }}</option>
                                    <option value="topup">{{ __('Topup') }}</option>
                                    <option value="promote">{{ __('promote') }}</option>
                                </select>
                                <div class="invalid-feedback" id="subscription_type_error"></div>
                            </div>
                        </div>
                        <div class="form-group col-md-6 hide_subscription_data">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Number of services') }}</label>
                                <input type="number" class="form-control" name="edit_number_of_service" id="edit_number_of_service" placeholder="{{ __('Enter No of services') }}">
                                <div class="invalid-feedback" id="edit_number_of_service_error"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-6 hide_subscription_data">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Number of locations') }}</label>
                                <input type="number" class="form-control" name="edit_number_of_locations" id="edit_number_of_locations" placeholder="{{ __('Enter No of locations') }}">
                            </div>
                        </div>
                        <div class="form-group col-md-6 hide_subscription_data">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Number of Staff') }}</label>
                                <input type="number" class="form-control" name="edit_number_of_staff" id="edit_number_of_staff" placeholder="{{ __('Enter No of staff') }}">
                                <div class="invalid-feedback" id="edit_number_of_staff_error"></div>
                            </div>
                        </div>

                        <div class="row hide_subscription_data">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="modal-satus-toggle d-flex align-items-center">
                                        <h5 class="me-2">{{ __('featured') }}</h5>
                                        <div class="status-toggle modal-status">
                                            <input type="checkbox" id="edit_featured" name="featured"
                                                class="check">
                                            <label for="edit_featured" class="checktoggle"> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="modal-satus-toggle d-flex align-items-center">
                                        <h5 class="me-2">{{ __('badge') }}</h5>
                                        <div class="status-toggle modal-status">
                                            <input type="checkbox" id="edit_badge" name="badge" class="check">
                                            <label for="edit_badge" class="checktoggle"> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row recurring_subscription">
                            <h5 class="mb-3">{{ __('Recurring Subscription') }}</h5>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="modal-satus-toggle d-flex align-items-center">
                                        <h6 class="me-2">{{ __('stripe') }}</h6>
                                        <div class="status-toggle modal-status">
                                            <input type="checkbox" id="edit_stripe_recurring" name="edit_stripe_recurring" class="check">
                                            <label for="edit_stripe_recurring" class="checktoggle"> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="modal-satus-toggle d-flex align-items-center">
                                        <h6 class="me-2">{{ __('paypal') }}</h6>
                                        <div class="status-toggle modal-status">
                                            <input type="checkbox" id="edit_paypal_recurring" name="edit_paypal_recurring" class="check">
                                            <label for="edit_paypal_recurring" class="checktoggle"> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row promote_subscription">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="modal-satus-toggle d-flex align-items-center">
                                        <h5 class="me-2">{{ __('promote_service') }}</h5>
                                        <div class="status-toggle modal-status">
                                            <input type="checkbox" id="edit_promoted_service" name="edit_promoted_service" class="check">
                                            <label for="edit_promoted_service" class="checktoggle"> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="modal-satus-toggle d-flex align-items-center">
                                        <h5 class="me-2">{{ __('promote_provider') }}</h5>
                                        <div class="status-toggle modal-status">
                                            <input type="checkbox" id="edit_promoted_jobber" name="edit_promoted_jobber" class="check">
                                            <label for="edit_promoted_jobber" class="checktoggle"> </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Description') }}</label>
                                    <textarea type="text" name="edit_description" id="edit_description" class="form-control" placeholder="{{ __('Enter Description') }}" maxlength="200"></textarea>
                                    <div class="invalid-feedback" id="edit_description_error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                <div class="status-title">
                                    <h5>{{ __('Status Toggle') }}</h5>
                                    <p>{{ __('Change the Status by toggle') }}</p>
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
                    <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                    <button type="submit" id="edit_btn_sub" class="btn btn-primary editPackageBtn" data-update-text="{{ __('Update') }}">{{ __('Update') }}</button>
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
                        <button type="submit" class="btn btn-danger" id="confirmDelete">{{ __('Yes, Delete') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection