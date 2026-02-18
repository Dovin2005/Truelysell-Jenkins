@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content bg-white">
        <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('Currency Settings') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Settings') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Currency Settings') }}</li>
                    </ol>
                </nav>
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
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    @if(isset($permission))
                        @if(hasPermission($permission, 'General Settings', 'create'))
                            <div class="skeleton label-skeleton label-loader"></div>
                            <a href="#" class="btn btn-primary d-none real-label" id="add_currency_btn"><i class="ti ti-square-rounded-plus-filled me-2"></i>{{ __('Add Currency') }}</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>

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
                                <table class="table d-none" id="currency_table" data-empty_info="{{ __('no_curency_available') }}">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ __('Currency') }}</th>
                                            <th>{{ __('Code') }}</th>
                                            <th>{{ __('Symbol') }}</th>
                                            <th>{{ __('Status') }} </th>
                                            @if ($edit == 1)
                                            <th>{{ __('Is Default') }}</th>
                                            @endif 
                                            @if ($isVisible == 1)
                                            <th class="no-sort">{{ __('Action') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="currency_list"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<div class="modal fade" id="save_currency">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title currency_modal_title" data-add-title="{{ __('Add Currency') }}" data-update-title="{{ __('Edit Currency') }}">{{ __('Add Currency') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="currencyForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Currency') }}<span class="text-danger"> *</span></label>
                                <select class="select select2" id="available_currency_id" name="available_currency_id" data-placeholder="{{ __('Select') }}">
                                    <option value="">{{ __('Select') }}</option>
                                    @if ($availableCurrencies)
                                        @foreach($availableCurrencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->currency_name }}  ({{ $currency->code }})</option>
                                        @endforeach
                                    @endif
                                </select>
                                <span class="text-danger error-text" id="available_currency_id_error" data-required="{{ __('currency_required') }}"></span>
                            </div>
                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between mb-3">
                                <div class="status-title">
                                    <h5>{{ __('Default') }}</h5>
                                    <p>{{ __('Change the Default by toggle') }} </p>
                                </div>
                                <div class="status-toggle modal-status">
                                    <input type="checkbox" id="save_currency_default" name="is_default"  class="check">
                                    <label for="save_currency_default" class="checktoggle"> </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                    <button type="submit" id="btn_currency" class="btn btn-primary currency_save_btn" data-update-text="{{ __('Save') }}" data-edit="{{ __('Edit') }}" data-delete="{{ __('Delete') }}">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="currency_delete">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form>
                <div class="modal-body text-center">
                    <span class="delete-icon">
                        <i class="ti ti-trash-x"></i>
                    </span>
                    <h4>{{ __('Confirm Deletion') }}</h4>
                    <p>{{ __('You want to delete all the marked items, this cant be undone once you delete.') }}</p>
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-2"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-danger currency_delete_btn">{{ __('Yes, Delete') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
