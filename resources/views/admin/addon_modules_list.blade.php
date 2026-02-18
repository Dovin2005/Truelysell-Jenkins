@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('Addons') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('application') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Addons') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        @php $isVisible = 0; @endphp
        @if(isset($permission))
            @if(hasPermission($permission, 'Addons', 'delete'))
                @php $delete = 1; $isVisible = 1; @endphp
            @else
                @php $delete = 0; @endphp
            @endif
            @if(hasPermission($permission, 'Addons', 'edit'))
                @php $edit = 1; $isVisible = 1; @endphp
            @else
                @php $edit = 0; @endphp
            @endif
            <div id="has_permission" data-delete="{{ $delete }}" data-edit="{{ $edit }}" data-visible="{{ $isVisible }}"></div>
        @else
            <div id="has_permission" data-delete="1" data-edit="1"></div>
        @endif
        <div class="card">
            <ul class="nav nav-tabs p-3 pb-0" id="addonsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active"
                        id="installed_addon"
                        data-bs-toggle="tab"
                        type="button"
                        role="tab">
                        {{ __('installed_addons') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link"
                        id="new_addon"
                        data-bs-toggle="tab"
                        type="button"
                        role="tab">
                        {{ __('new_addons') }}
                    </button>
                </li>
            </ul>
            <div class="card-body p-0 py-3">
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
                <div class="custom-datatable-filter table-responsive">
                    <table class="table d-none" id="addonModuleTable" data-empty="{{ __('no_data_available') }}">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('module_name') }}</th>
                                <th>{{ __('module_image') }}</th>
                                <th>{{ __('module_price') }}</th>
                                @if ($edit == 1)
                                <th>{{ __('Status') }}</th>
                                @endif
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                    <table class="table d-none" id="newAddonModuleTable" data-empty="{{ __('no_data_available') }}">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('Module Name') }}</th>
                                <th>{{ __('Module Image') }}</th>
                                <th>{{ __('Module Price') }}</th>
                                <th>{{ __('Action') }}</th>
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

<div class="modal fade" id="purchase_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" data-add_text="{{ __('Purchase Module') }}">{{ __('Purchase Module') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="purchaseForm">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="module_name" id="module_name">
                        <input type="hidden" name="module_price" id="module_price">
                        <input type="hidden" name="module_version" id="module_version">
                        <input type="hidden" name="git_link" id="git_link">
                        <div class="col-md-12">
                            <div class="mb-0">
                                <label class="form-label">{{ __('Key') }}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control" id="purchase_key" name="purchase_key" placeholder="{{ __('Enter Key') }}">
                                <span class="text-danger error-text" id="role_name_error" data-required="{{ __('Role name is required.') }}" data-exists="{{ __('Role name already exists.') }}" data-max="{{ __('role_name_maxlength') }}"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary purchase_confirm_btn" data-save="{{ __('submit') }}">{{ __('submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
