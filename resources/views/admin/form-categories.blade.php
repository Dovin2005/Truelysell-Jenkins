@extends('admin.admin')

@section('content')
<div class="page-wrapper cardhead">
    <div class="content bg-white">
        <div class="d-md-flex d-block align-items-center justify-content-between -bottom pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('Forms Input for') }} {{ $categoryName }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Categories') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page"> {{ __('Forms Input') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="pe-1 mb-2">
                    @if(isset($permission))
                        @if(hasPermission($permission, 'Categories', 'create'))
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#add_language" id="addFormInputButton"><i
                                    class="ti ti-square-rounded-plus-filled me-2"></i>{{ __('Add Forms Input') }}</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @php $isVisible = 0; @endphp
        @if(isset($permission))
            @if(hasPermission($permission, 'Categories', 'delete'))
                @php $delete = 1; $isVisible = 1; @endphp
            @else
                @php $delete = 0; @endphp
            @endif
            @if(hasPermission($permission, 'Categories', 'edit'))
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
                <div class="-start ps-3">
                    <div class="row mt-2">
                        <input type="hidden" name="category_id" id="category_id" class="category_id" value="{{ $categoryId }}">

                        <div class="col-xl-10" id="draggable-left">

                        </div>
                    </div>
                    <div class="modal fade" id="add_language">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">{{ __('Add Form Input') }}</h4>
                                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>
                                <form id="addFormsInputForm">
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="hidden" name="category_id" id="category_id" class="category_id" value="{{ $categoryId }}">

                                                <div class="modal-satus-toggle d-flex align-items-center justify-content-between mb-3">
                                                    <div class="status-title">
                                                        <label class="form-label">{{ __('Required Status') }}</label>
                                                    </div>
                                                    <div class="status-toggle modal-status">
                                                        <input type="checkbox" name="required_status" id="required_status" class="check"  name="direction" value="1" checked >
                                                        <label for="required_status" class="checktoggle"></label>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Label') }}</label>
                                                    <input type="text" name="form_label" id="form_label" class="form-control" placeholder="{{ __('Enter Label') }}" >
                                                    <span class="text-danger error-text" id="form_label_error"></span>

                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Short Description') }}</label>
                                                    <input type="text" name="form_description" id="form_description" class="form-control" placeholder="{{ __('Enter description') }}" >
                                                    <span class="text-danger error-text" id="form_description_error"></span>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Input Fields') }}</label>
                                                    <select id="placeholder_select_edit" class="form-control select2">
                                                        <option value="">{{ __('Select an Input Field') }}</option>
                                                        <option value="text_field">{{ __('Text Field') }}</option>
                                                        <option value="number_field">{{ __('Number Field') }}</option>
                                                        <option value="select">{{ __('Select') }}</option>
                                                        <option value="checkbox">{{ __('Checkbox') }}</option>
                                                        <option value="textarea">{{ __('Textarea') }}</option>
                                                        <option value="datepicker">{{ __('Datepicker') }}</option>
                                                        <option value="timepicker">{{ __('Timepicker') }}</option>
                                                        <option value="file">{{ __('File') }}</option>
                                                        <option value="location">{{ __('Location') }}</option>
                                                        <option value="radio">{{ __('Radio') }}</option>
                                                    </select>
                                                    <span class="text-danger error-text" id="input_type_error"></span>

                                                </div>

                                                <div class="mb-3" id="placeholder_div" style="display: none;">
                                                    <label class="form-label">{{ __('Place Holder') }}</label>
                                                    <input type="text" name="form_placeholder" id="form_placeholder" class="form-control" placeholder="{{ __('Enter PlaceHolder') }}">
                                                </div>

                                                <div class="mb-3" id="file_size" style="display: none;">
                                                    <label class="form-label">{{ __('Maximum Size of Uploaded File') }}</label>
                                                    <input type="number" name="file_size" id="file_size_no" class="form-control" placeholder="{{ __('Enter Size of the File') }}">
                                                </div>

                                                <div class="mb-3" id="options-container" style="display: none;">
                                                    <button type="button" id="add-option-btn" class="btn btn-primary mb-3">{{ __('Add Option') }}</button><br>
                                                    <span class="text-danger error-text" id="options_error"></span>

                                                </div>

                                                <div class="mb-3" id="other_option" style="display: none;">
                                                    <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                                        <div class="status-title">
                                                            <label class="form-label">{{ __('Other Option') }}</label>
                                                        </div>
                                                        <div class="status-toggle modal-status">
                                                            <input type="checkbox" id="status_toggle" class="check user8" checked>
                                                            <label for="status_toggle" class="checktoggle"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                        <button type="submit" id="addLanguageBtn" class="btn btn-primary">{{ __('Add Form Input') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="edit_language">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">{{ __('Edit Form Input') }}</h4>
                                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>
                                <form id="editFormsInputForm">
                                    @csrf 
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="hidden" name="category_id" id="category_id" class="category_id" value="{{ $categoryId }}">

                                                <div class="modal-satus-toggle d-flex align-items-center justify-content-between mb-3">
                                                    <div class="status-title">
                                                        <label class="form-label">{{ __('Required Status') }}</label>
                                                    </div>
                                                    <div class="status-toggle modal-status">
                                                        <input type="checkbox" name="required_status" id="required_status_edit" class="check required_status" name="direction" value="">
                                                        <label for="required_status" class="checktoggle"></label>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Label') }}</label>
                                                    <input type="text" name="edit_form_label" id="edit_form_label" class="form-control" placeholder="{{ __('Enter Label') }}" >
                                                    <span class="text-danger error-text" id="form_label_error"></span>

                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Short Description') }}</label>
                                                    <input type="text" name="form_description" id="edit_form_description" class="form-control" placeholder="{{ __('Enter description') }}" >
                                                    <span class="text-danger error-text" id="form_description_error"></span>
                                                </div>

                                                <div class="mb-3" id="edit_placeholder_div" style="display: none;">
                                                    <label class="form-label">{{ __('Place Holder') }}</label>
                                                    <input type="text" name="edit_form_placeholder" id="edit_form_placeholder" class="form-control" placeholder="{{ __('Enter PlaceHolder') }}">
                                                </div>

                                                <div class="mb-3" id="edit_file_size" style="display: none;">
                                                    <label class="form-label">{{ __('Maximum Size of Uploaded File') }}</label>
                                                    <input type="text" name="edit_file_size" id="edit_file_size_no" class="form-control" placeholder="{{ __('Enter Size of the File') }}">
                                                </div>

                                                <div class="mb-3" id="edit_options-container" style="display: none;">
                                                    <button type="button" id="edit-add-option-btn" class="btn btn-primary mb-3">{{ __('Add Option') }}</button>
                                                </div>

                                                <div class="mb-3" id="edit_other_option" style="display: none;">
                                                    <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                                        <div class="status-title">
                                                            <label class="form-label">{{ __('Other Option') }}</label>
                                                        </div>
                                                        <div class="status-toggle modal-status">
                                                            <input type="checkbox" id="edit_status_toggle" class="check user8">
                                                            <label for="edit_status_toggle" class="checktoggle"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                        <button type="submit" id="editLanguageBtn" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="delete-modal">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form >
                                    <div class="modal-body text-center">
                                        <span class="delete-icon">
                                            <i class="ti ti-trash-x"></i>
                                        </span>
                                        <h4>{{ __('Confirm Deletion') }}</h4>
                                        <p>{{ __('You want to delete all the marked items, this cant be undone once you delete.') }}</p>
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

