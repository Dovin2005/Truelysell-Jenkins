@extends('admin.admin')

@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content bg-white">
        <form action="email-templates.html">
            <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('Invoice_Template_Settings') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);">{{ __('Settings') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Invoice_Template_Settings') }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="mb-3">
                        @if(isset($permission))
                            @if(hasPermission($permission, 'General Settings', 'create'))
                                <div class="skeleton label-skeleton label-loader"></div>
                                <a href="#" class="btn btn-outline-light bg-white btn-icon me-2 d-none real-label" data-bs-toggle="modal" data-bs-target="#add_email_template"><i class="ti ti-plus"></i></a>
                            @endif
                        @endif
                    </div>
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
                    <div class="flex-fill ps-1">
                        <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                        </div>
                        <div class="d-md-flex d-block">
                            <div class="flex-fill">
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
                                            <table class="table d-none" id="invoiceTemplatesTable">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>{{ __('Invoice_Title') }}</th>
                                                        <th>{{ __('Type') }}</th>
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
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- /Page Wrapper -->

<!-- Add Email Template -->
<div class="modal fade" id="add_email_template">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Add_Invoice_Template') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="email_template_form">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Invoice_Title') }}</label>
                                <input type="text" class="form-control" name="invoice_title" id="invoice_title" placeholder="{{ __('enter_title') }}">
                                <span class="text-danger error-text" id="invoice_title_error"></span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Invoice_Type') }}</label>
                                <select class="form-control" name="invoice_type" id="invoice_type">
                                    <option value="">{{ __('Select Type') }}</option>
                                    @foreach($getinvoicetype as $invoicetype)
                                        <option value="{{ $invoicetype->type }}">{{ $invoicetype->type }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-text" id="invoice_type_error"></span>
                            </div>
                            <div class="mb-3">
                                <div><label class="form-label">{{ __('Place Holder')}}</label></div>
                                @foreach($getplaceholder as $value)
                                    <button type="button" class="btn btn-secondary btn-sm mb-2 add_placeholder_value" data-value="{{ $value->placeholder_name }}">{{ $value->placeholder_name }}</button>
                                @endforeach
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Template_Content') }}</label>
                                <div class="summernote-add">
                                </div>
                                <span class="text-danger error-text" id="template_content_error"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary email_template_save_btn" data-save="{{ __('Save') }}">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Add Email Template -->

<!-- Edit Email Template -->
<div class="modal fade" id="edit_email_template">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Edit_Template') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="editTemplateForm">
                <div class="modal-body">
                    <input type="hidden" name="template_id">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Invoice_Title') }}</label>
                        <input type="text" class="form-control" name="invoice_title" id="edit_invoice_title" placeholder="{{ __('enter_title') }}">
                        <span class="text-danger error-text" id="invoice_title_error"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Invoice_Type') }}</label>
                        <select class="form-control" name="invoice_type" id="edit_invoice_type">
                            <option value="">{{ __('Select Type') }}</option>
                            @foreach($getinvoicetype as $invoicetype)
                                 <option value="{{ $invoicetype->type }}">{{ $invoicetype->type }}</option>
                            @endforeach
                        </select>
                        <span class="text-danger error-text" id="invoice_type_error"></span>
                    </div>
                    <div class="mb-3">
                        <div><label class="form-label">{{ __('Place Holder')}}</label></div>
                        @foreach($getplaceholder as $value)
                            <button type="button" class="btn btn-secondary btn-sm mb-2 placeholder_value" data-value="{{ $value->placeholder_name }}">{{ $value->placeholder_name }}</button>
                        @endforeach
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Template_Content') }}</label>
                        <input type="hidden" name="template_content" id="edit_template_content">
                        <div id="summernote">
                        </div>
                        <span class="text-danger error-text" id="template_content_error"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary" data-edit="{{ __('Edit') }}">{{ __('Edit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Edit Email Template -->

<!-- Delete Modal -->
<div class="modal fade" id="delete-modal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteTemplateForm" action="javascript:void(0);">
                <div class="modal-body text-center">
                    <span class="delete-icon">
                        <i class="ti ti-trash-x"></i>
                    </span>
                    <h4>{{ __('Confirm Deletion') }}</h4>
                    <p>{{ __('Are you sure you want to delete this item? This action cannot be undone.') }}</p>
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-danger">{{ __('Yes, Delete') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- /Delete Modal -->
@endsection
