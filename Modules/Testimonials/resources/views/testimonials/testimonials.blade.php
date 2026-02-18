@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('testimonials') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('content') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('testimonials') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    @if(isset($permission))
                        @if(hasPermission($permission, 'Testimonials', 'create'))
                        <div class="skeleton label-skeleton label-loader"></div>
                        <a href="#" class="btn btn-primary d-none real-label" id="add_testimonial_btn" data-bs-toggle="modal"
                            data-bs-target="#add_testimonial_modal"><i class="ti ti-square-rounded-plus-filled me-2"></i>{{ __('add_testimonial') }}</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @php $isVisible = 0; @endphp
        @if(isset($permission))
            @if(hasPermission($permission, 'Testimonials', 'delete'))
                @php $delete = 1; $isVisible = 1; @endphp
            @else
                @php $delete = 0; @endphp
            @endif
            @if(hasPermission($permission, 'Testimonials', 'edit'))
                @php $edit = 1; $isVisible = 1; @endphp
            @else
                @php $edit = 0; @endphp
            @endif
            <div id="has_permission" data-delete="{{ $delete }}" data-edit="{{ $edit }}" data-visible="{{ $isVisible }}"></div>
        @else
            <div id="has_permission" data-delete="1" data-edit="1"></div>
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

                    <table class="table d-none" id="testimonialsTable" data-empty="{{ __('testimonial_empty') }}">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('client') }}</th>
                                <th>{{ __('position') }}</th>
                                <th>{{ __('description') }}</th>
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
<div class="modal fade" id="add_testimonial_modal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title testimonial_modal_title" data-add_title="{{ __('add_testimonial') }}" data-edit_title="{{ __('edit_testimonial') }}" data-edit="{{ __('Edit') }}" data-delete="{{ __('Delete') }}">{{ __('add_testimonial') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="testimonialForm">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="method" id="method">
                        <input type="hidden" name="id" id="id">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('client_name') }}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control" id="client_name" name="client_name" placeholder="{{ __('enter_client_name') }}">
                                <span class="text-danger error-text" id="client_name_error" data-required="{{ __('client_name_required') }}" data-max="{{ __('client_name_max') }}"></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">{{ __('position') }}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control" id="position" name="position" placeholder="{{ __('enter_client_position') }}">
                                <span class="text-danger error-text" id="position_error" data-required="{{ __('position_required') }}" data-max="{{ __('position_max') }}"></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">{{ __('image') }}<span class="text-danger"> *</span></label>
                                <div class="d-flex align-items-center flex-wrap row-gap-3 mb-3">
                                    <div
                                        class="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames">
                                        <img id="clientImagePreview" src="{{ asset("front/img/default-placeholder-image.png") }}" alt="Client Image" width="100" height="100">
                                        <i class="ti ti-photo-plus fs-16 upload_icon"></i>
                                    </div>
                                    <div class="profile-upload">
                                        <div class="profile-uploader d-flex align-items-center">
                                            <div class="drag-upload-btn mb-3">
                                                {{ __('upload') }}
                                                <input type="file" class="form-control image-sign" name="client_image" id="client_image">
                                            </div>
                                        </div>
                                        <p>{{ __('image_size_note') }}</p>
                                    </div>
                                </div>
                                <span class="text-danger error-text" id="client_image_error" data-required="{{ __('client_image_required') }}" data-extension="{{ __('client_image_extension') }}" data-filesize="{{ __('client_image_filesize') }}"></span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('description') }}<span class="text-danger"> *</span></label>
                                <textarea class="form-control" rows="8" name="description" id="description"></textarea>
                                <span class="text-danger error-text" id="description_error" data-required="{{ __('descriptions_required') }}" data-max="{{ __('description_max') }}"></span>
                            </div>
                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                <div class="status-title">
                                    <h5>{{ __('Status') }}</h5>
                                </div>
                                <div class="status-toggle modal-status">
                                    <input type="checkbox" id="status" name="status" class="check" checked>
                                    <label for="status" class="checktoggle"> </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary save_testimonial_btn" data-save="{{ __('Save') }}">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="delete_testimonial_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteTestimonialForm">
                <div class="modal-body text-center">
                    <span class="delete-icon">
                        <i class="ti ti-trash-x"></i>
                    </span>
                    <h4>{{ __('Confirm Deletion') }}</h4>
                    <p>{{ __('You want to delete all the marked items, this cant be undone once you delete.') }}</p>
                    <input type="hidden" name="delete_id" id="delete_id">
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-danger delete_testimonial_confirm">{{ __('Yes, Delete') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
