@extends('admin.admin')

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('newsletter') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);">{{ __('content') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('subscriber_list') }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="mb-2">
                        @if(isset($permission))
                            @if(hasPermission($permission, 'Newsletter', 'create'))
                                <div class="skeleton label-skeleton label-loader"></div>
                                <button id="selected_email_btn" class="btn btn-primary mb-3 d-none real-label">
                                    <i class="fas fa-envelope"></i>
                                    <span>{{ __('send_email') }}</span>
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            @php $isVisible = 0; @endphp
            @if(isset($permission))
                @if(hasPermission($permission, 'Newsletter', 'delete'))
                    @php $delete = 1; $isVisible = 1; @endphp
                @else
                    @php $delete = 0; @endphp
                @endif
                @if(hasPermission($permission, 'Newsletter', 'edit'))
                    @php $edit = 1; @endphp
                @else
                    @php $edit = 0; @endphp
                @endif
                @if(hasPermission($permission, 'Newsletter', 'create'))
                    @php $create = 1; @endphp
                @else
                    @php $create = 0; @endphp
                @endif
                <div id="has_permission" data-delete="{{ $delete }}" data-edit="{{ $edit }}" data-visible="{{ $isVisible }}" data-create="{{ $create }}"></div>
            @else
                <div id="has_permission" data-delete="1" data-edit="1" data-create="1"></div>
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
                        <table class="table d-none" id="subscriberTable" data-empty="{{ __('subscriber_empty_info') }}" data-send_email="{{ __('send_email') }}" data-sending="{{ __('sending') }}" data-empty_select="{{ __('subscriber_empty_select') }}" data-delete="{{ __('Delete') }}">
                            <thead class="thead-light">
                                <tr>
                                    @if ($create == 1)
                                    <th>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select_all_subscriber">
                                        </div>
                                    </th>
                                    @endif
                                    <th>{{ __('Email') }}</th>
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

    <div class="modal fade" id="delete_subscriber_modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="deleteSubscriberForm">
                    <div class="modal-body text-center">
                        <span class="delete-icon">
                            <i class="ti ti-trash-x"></i>
                        </span>
                        <h4>{{ __('Confirm Deletion') }}</h4>
                        <p>{{ __('You want to delete all the marked items, this cant be undone once you delete.') }}</p>
                        <input type="hidden" name="delete_id" id="delete_id">
                        <div class="d-flex justify-content-center">
                            <a href="javascript:void(0);" class="btn btn-light me-3"
                                data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                            <button type="submit"
                                class="btn btn-danger delete_subscriber_confirm">{{ __('Yes, Delete') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
