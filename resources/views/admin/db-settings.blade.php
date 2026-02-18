@extends('admin.admin')

@section('content')
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content bg-white">
            <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('DB Backup') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);">{{ __('Settings') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('DB Backup') }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <form id="adminAddlong" enctype="multipart/form-data" method="POST" action="{{ route('savedbbackup') }}">
                @csrf
                <input type="hidden" name="name" id="save_language_name" class="form-control" value="admin" placeholder="{{ __('Enter_Language_Name') }}">

                    <div class="pe-1 mb-2">
                       
                        @if(isset($permission))
                            @if(hasPermission($permission, 'General Settings', 'edit'))
                            <div class="skeleton label-skeleton label-loader"></div>
                            <a href="{{ route('backup') }}" class="btn btn-primary d-none real-label">{{ __('DB Backup') }}</a>
                             {{-- <button type="submit"  class="btn btn-primary d-none real-label" data-save="{{ __('Save') }}">{{ __('DB Backup') }}</button> --}}
                            @endif
                        @endif

                    </div>
                </form>    
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
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="datatablediv d-none real-table">
                                        <table class="table" id="databaseTable">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>{{ __('Database') }}</th>
                                                    <th>{{ __('Date') }}</th>
                                                    <th class="no-sort">{{ __('Action') }}</th>
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
    </div>
	<!-- /Page Wrapper -->

    <!-- Add Language -->
    <div class="modal fade" id="add_language">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('Back up your DB') }}</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
             

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                </div>
                               
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_language">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('Edit_Language') }}</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="editlanguageForm">
                    @csrf <!-- Add CSRF token -->
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <!-- Language Name -->
                                <div class="mb-3">
                                    <label class="form-label">{{ __('language_name') }}</label>
                                    <input type="text" class="form-control" name="name" id="language_name" placeholder="{{ __('Enter_Language_Name') }}">
                                    <span class="error-text text-danger" id="name_error" data-required="{{ __('language_name_required') }}"></span>
                                </div>

                                <!-- Language Code -->
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Language_Code') }}</label>
                                    <input type="text" class="form-control" name="code" id="language_code" placeholder="{{ __('Enter Code') }}">
                                    <span class="error-text text-danger" id="code_error" data-required="{{ __('language_code_required') }}"></span>
                                </div>

                                <!-- RTL Toggle -->
                                <div class="modal-satus-toggle d-flex align-items-center justify-content-between mb-3">
                                    <div class="status-title">
                                        <label class="form-label">{{ __('RTL') }}</label>
                                    </div>
                                    <div class="status-toggle modal-status">
                                        <input type="checkbox" id="rtl_toggle" class="check user7">
                                        <label for="rtl_toggle" class="checktoggle"></label>
                                    </div>
                                </div>

                                <!-- Status Toggle -->
                                <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                    <div class="status-title">
                                        <label class="form-label">{{ __('Status') }}</label>
                                    </div>
                                    <div class="status-toggle modal-status">
                                        <input type="checkbox" id="status_toggle" class="check user8">
                                        <label for="status_toggle" class="checktoggle"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary" id="saveLanguage" data-save="{{ __('Save Changes') }}">{{ __('Save Changes') }}</button>
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
                        <p>{{ __('You want to delete all the marked items, this cannot be undone once you delete.') }}</p>
                        <div class="d-flex justify-content-center">
                            <a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-danger" id="confirmDelete">{{ __('Yes, Delete') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection










