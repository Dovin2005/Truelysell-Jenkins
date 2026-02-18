@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content bg-white">
        <div class="d-md-flex d-block align-items-center justify-content-between pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('Sections') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Content') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('Pages') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Sections') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        @php $isVisible = 0; @endphp
        @if(isset($permission))
            @if(hasPermission($permission, 'Pages', 'delete'))
                @php $delete = 1; $isVisible = 1; @endphp
            @else
                @php $delete = 0; @endphp
            @endif
            @if(hasPermission($permission, 'Pages', 'edit'))
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
                <div class="">
                    <div class="card">
                        <div class="card-body p-0 py-3">
                            <div class="custom-datatable-filter">
                                <div class="table-responsive">
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
                                    <table class="table m-3 d-none" id="datatable_section">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>{{ __('SN') }}</th>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Status') }}</th>
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
</div>

<div class="modal fade" id="add_banner_sec">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('Edit Section') }}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="addBannerOneForm" autocomplete="off">
                <input type="hidden" name="section_id" id="section_id">
                <div class="modal-body">
                    <div id="section_id_1" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Title') }}</label>
                                    <input type="text" name="title" id="title" class="form-control" placeholder="{{ __('Enter title') }}">
                                    <div class="invalid-feedback" id="title_error"></div>
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Label') }}</label>
                                    <input type="text" name="label" id="label" class="form-control" placeholder="{{ __('Enter label') }}">
                                    <div class="invalid-feedback" id="label_error"></div>
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Background Image') }}</label>
                                    <input type="file" name="background_image" id="background_image" class="form-control" accept="image/*">
                                    <div class="invalid-feedback" id="background_image_error"></div>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="mb-3 d-flex">
                                    <img src="{{ asset("front/img/default-placeholder-image.png") }}" alt="Background image preview" id="background_img" style="height: 100px; width: 200px" class="img-fluid img-thumbnail rounded shadow-sm my-2">
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Thumbnail Image') }}</label>
                                    <input type="file" name="thumbnail_image" id="thumbnail_image" class="form-control" accept="image/*">
                                    <div class="invalid-feedback" id="thumbnail_image_error"></div>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <div class="mb-3 d-flex">
                                    <img src="{{ asset("front/img/default-placeholder-image.png") }}" alt="Thumbnail image preview" id="thumbnail_img" style="height: 100px; width: 200px" class="img-fluid img-thumbnail rounded shadow-sm my-2">
                                </div>
                            </div>

                            <div class="card p-1 px-4 form-group col-md-6 mb-3">
                                <div class="modal-status-toggle d-flex align-items-center justify-content-between">
                                    <div class="status-title">
                                        <h5>{{ __('Show Search') }}</h5>
                                        <p>{{ __('Toggle to show or hide search') }}</p>
                                    </div>
                                    <div class="status-toggle modal-status">
                                        <input type="checkbox" name="show_search" id="show_search" class="check">
                                        <label for="show_search" class="checktoggle"></label>
                                    </div>
                                    <div class="invalid-feedback" id="show_search_error"></div>
                                </div>
                            </div>

                            <div class="card p-1 px-4 form-group col-md-6 mb-3">
                                <div class="modal-status-toggle d-flex align-items-center justify-content-between">
                                    <div class="status-title">
                                        <h5>{{ __('Show Location') }}</h5>
                                        <p>{{ __('Toggle to show or hide location') }}</p>
                                    </div>
                                    <div class="status-toggle modal-status">
                                        <input type="checkbox" name="show_location" id="show_location" class="check">
                                        <label for="show_location" class="checktoggle"></label>
                                    </div>
                                    <div class="invalid-feedback" id="show_location_error"></div>
                                </div>
                            </div>

                            <div class="card p-1 px-4 form-group col-md-6 mb-3">
                                <div class="modal-status-toggle d-flex align-items-center justify-content-between">
                                    <div class="status-title">
                                        <h5>{{ __('Popular Search') }}</h5>
                                        <p>{{ __('Toggle to show or hide popular search') }}</p>
                                    </div>
                                    <div class="status-toggle modal-status">
                                        <input type="checkbox" name="popular_search" id="popular_search" class="check">
                                        <label for="popular_search" class="checktoggle"></label>
                                    </div>
                                    <div class="invalid-feedback" id="popular_search_error"></div>
                                </div>
                            </div>

                            <div class="card p-1 px-4 form-group col-md-6 mb-3">
                                <div class="modal-status-toggle d-flex align-items-center justify-content-between">
                                    <div class="status-title">
                                        <h5>{{ __('Provider Count') }}</h5>
                                        <p>{{ __('Toggle to show or hide provider count') }}</p>
                                    </div>
                                    <div class="status-toggle modal-status">
                                        <input type="checkbox" name="provider_count" id="provider_count" class="check">
                                        <label for="provider_count" class="checktoggle"></label>
                                    </div>
                                    <div class="invalid-feedback" id="provider_count_error"></div>
                                </div>
                            </div>

                            <div class="card p-1 px-4 form-group col-md-6 mb-3">
                                <div class="modal-status-toggle d-flex align-items-center justify-content-between">
                                    <div class="status-title">
                                        <h5>{{ __('Services Count') }}</h5>
                                        <p>{{ __('Toggle to show or hide services count') }}</p>
                                    </div>
                                    <div class="status-toggle modal-status">
                                        <input type="checkbox" name="services_count" id="services_count" class="check">
                                        <label for="services_count" class="checktoggle"></label>
                                    </div>
                                    <div class="invalid-feedback" id="services_count_error"></div>
                                </div>
                            </div>

                            <div class="card p-1 px-4 form-group col-md-6 mb-3">
                                <div class="modal-status-toggle d-flex align-items-center justify-content-between">
                                    <div class="status-title">
                                        <h5>{{ __('Review Count') }}</h5>
                                        <p>{{ __('Toggle to show or hide review count') }}</p>
                                    </div>
                                    <div class="status-toggle modal-status">
                                        <input type="checkbox" name="review_count" id="review_count" class="check">
                                        <label for="review_count" class="checktoggle"></label>
                                    </div>
                                    <div class="invalid-feedback" id="review_count_error"></div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div id="section_id_2" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Category') }}</label>
                                    <textarea rows="8" cols="10" name="category" id="category" class="form-control" placeholder="{{ __('Enter Category') }}"></textarea>
                                    <div class="invalid-feedback" id="category_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="section_id_3" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Feature Category') }}</label>
                                    <textarea rows="8" cols="10" name="feature_category" id="feature_category" class="form-control" placeholder="{{ __('Enter Feature Category') }}"></textarea>
                                    <div class="invalid-feedback" id="feature_category_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="section_id_4" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Popular Category') }}</label>
                                    <textarea rows="8" cols="10" name="popular_category" id="popular_category" class="form-control" placeholder="{{ __('Enter Popular Category') }}"></textarea>
                                    <div class="invalid-feedback" id="popular_category_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="section_id_5" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Services') }}</label>
                                    <textarea rows="8" cols="10" name="service" id="service" class="form-control" placeholder="{{ __('Enter Services') }}"></textarea>
                                    <div class="invalid-feedback" id="service_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="section_id_6" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Feature Services') }}</label>
                                    <textarea rows="8" cols="10" name="feature_service" id="feature_service" class="form-control" placeholder="{{ __('Enter Feature Services') }}"></textarea>
                                    <div class="invalid-feedback" id="feature_service_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="section_id_7" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Popular Services') }}</label>
                                    <textarea rows="8" cols="10" name="popular_service" id="popular_service" class="form-control" placeholder="{{ __('Enter Popular Services') }}"></textarea>
                                    <div class="invalid-feedback" id="popular_service_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="section_id_8" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Product') }}</label>
                                    <textarea rows="8" cols="10" name="product" id="product" class="form-control" placeholder="{{ __('Enter Product') }}"></textarea>
                                    <div class="invalid-feedback" id="product_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="section_id_9" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Feature Product') }}</label>
                                    <textarea rows="8" cols="10" name="feature_product" id="feature_product" class="form-control" placeholder="{{ __('Enter Feature Product') }}"></textarea>
                                    <div class="invalid-feedback" id="feature_product_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="section_id_10" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Popular Product') }}</label>
                                    <textarea rows="8" cols="10" name="popular_product" id="popular_product" class="form-control" placeholder="{{ __('Enter Popular Product') }}"></textarea>
                                    <div class="invalid-feedback" id="popular_product_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="section_id_11" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Faq') }}</label>
                                    <textarea rows="8" cols="10" name="faq" id="faq" class="form-control" placeholder="{{ __('Enter Faq') }}"></textarea>
                                    <div class="invalid-feedback" id="faq_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="section_id_12" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Service package') }}</label>
                                    <textarea rows="8" cols="10" name="service_package" id="service_package" class="form-control" placeholder="{{ __('Enter Service Package') }}"></textarea>
                                    <div class="invalid-feedback" id="service_package_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="section_id_13" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('About As') }}</label>
                                    <textarea rows="8" cols="10" name="about_as" id="about_as" class="form-control" placeholder="{{ __('Enter About As') }}"></textarea>
                                    <div class="invalid-feedback" id="about_as_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="section_id_14" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Testimonial') }}</label>
                                    <textarea rows="8" cols="10" name=" " id="testimonial" class="form-control" placeholder="{{ __('Enter Testimonial') }}"></textarea>
                                    <div class="invalid-feedback" id="testimonial_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="section_id_15" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('How It Work') }}</label>
                                    <textarea rows="8" cols="10" name="how_it_work" id="how_it_work" class="form-control" placeholder="{{ __('Enter How It Work') }}"></textarea>
                                    <div class="invalid-feedback" id="how_it_work_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="section_id_16" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Blog') }}</label>
                                    <textarea rows="8" cols="10" name="blog" id="blog" class="form-control" placeholder="{{ __('Enter Blog') }}"></textarea>
                                    <div class="invalid-feedback" id="blog_error"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" id="btn_banner_one" class="btn btn-primary banner_one" data-update-text="{{ __('Update') }}">{{ __('Update') }}</button>
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
                        <button type="submit" class="btn btn-danger" id="confirmDeleteFaq">{{ __('Yes, Delete') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection