@extends('provider.provider')

@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('Create Coupon') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ Auth::user()->user_type == 2 ? route('provider.dashboard') : route('staff.dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('provider.coupon') }}">{{ __('Coupons') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Edit Coupon') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form id="couponForm">
                    <div class="general-info">
                        <input type="hidden" id="id" name="id" value="{{ $data->id ?? ''}}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Code') }}<span class="text-danger"> *</span></label>
                                    <input type="text" class="form-control" id="code" name="code" placeholder="{{ __('enter_code') }}" value="{{ $data->code ?? '' }}">
                                    <span class="text-danger error-text" id="code_error" data-required="{{ __('code_required') }}" data-exists="{{ __('code_exists') }}" data-max="{{ __('code_max') }}"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Product Type') }}<span class="text-danger"> *</span></label>
                                    <select class="form-control select2" id="product_type" name="product_type" data-placeholder="{{ __('select_product_type') }}">
                                        <option value="">{{ __('select_product_type') }}</option>
                                        <option value="all" {{ $data->product_type == 'all' ? 'selected' : '' }}>{{ __('All') }}</option>
                                        <option value="category" {{ $data->product_type == 'category' ? 'selected' : '' }}>{{ __('Category') }}</option>
                                        <option value="subcategory" {{ $data->product_type == 'subcategory' ? 'selected' : '' }}>{{ __('sub_category') }}</option>
                                        <option value="service" {{ $data->product_type == 'service' ? 'selected' : '' }}>{{ __('Service') }}</option>
                                    </select>
                                    <span class="text-danger error-text" id="product_type_error" data-required="{{ __('product_type_required') }}" ></span>
                                </div>
                            </div>
                            <div class="col-md-6 {{ ($data->product_type != 'all' && $data->product_type == 'category') ? '' : 'd-none'}}" id="category_field" >
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Category') }}<span class="text-danger"> *</span></label>
                                    <select class="form-control select2" id="category_id" name="category_id[]" data-placeholder="{{ __('select_category') }}" multiple>
                                        @if ($categories)
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" {{ in_array($category->id, $data->category_id) ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <span class="text-danger error-text" id="category_id_error" data-required="{{ __('category_required') }}" ></span>
                                </div>
                            </div>
                            <div class="col-md-6 {{ ($data->product_type != 'all' && $data->product_type == 'subcategory') ? '' : 'd-none'}}" id="subcategory_field">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Sub Category') }}<span class="text-danger"> *</span></label>
                                    <select class="form-control select2" id="subcategory_id" name="subcategory_id[]" data-placeholder="{{ __('select_sub_category') }}" multiple>
                                        @if ($subcategories)
                                            @foreach ($subcategories as $subcategory)
                                                <option value="{{ $subcategory->id }}" {{ in_array($subcategory->id, $data->subcategory_id)  ? 'selected' : '' }}>{{ $subcategory->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <span class="text-danger error-text" id="subcategory_id_error" data-required="{{ __('sub_category_required') }}" ></span>
                                </div>
                            </div>
                            <div class="col-md-6 {{ ($data->product_type != 'all' && $data->product_type == 'service') ? '' : 'd-none'}}" id="product_field" >
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Service') }}<span class="text-danger"> *</span></label>
                                    <select class="form-control select2" id="product_id" name="product_id[]" data-placeholder="{{ __('select_product') }}" multiple>
                                        @if ($products)
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}" {{ in_array($product->id, $data->product_id) ? 'selected' : ''}}>{{ $product->source_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <span class="text-danger error-text" id="product_id_error" data-required="{{ __('product_required') }}" ></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Coupon Type') }}<span class="text-danger"> *</span></label>
                                    <select class="form-control select2" id="coupon_type" name="coupon_type" data-placeholder="{{ __('select_coupon_type') }}">
                                        <option value="">{{ __('select_coupon_type') }}</option>
                                        <option value="percentage" {{ $data->coupon_type == 'percentage' ? 'selected' : '' }}>{{ __('percentage') }}</option>
                                        <option value="fixed" {{ $data->coupon_type == 'fixed' ? 'selected' : '' }}>{{ __('fixed') }}</option>
                                    </select>
                                    <span class="text-danger error-text" id="coupon_type_error" data-required="{{ __('coupon_type_required') }}" ></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Coupon Value') }}<span class="text-danger"> *</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="coupon_value" name="coupon_value" placeholder="{{ __('enter_coupon_value') }}" value="{{ $data->coupon_value ?? '' }}">
                                        <span class="input-group-text coupon_type_symbol">{{ $data->coupon_type == 'percentage' ? '%' : '$' }}</span>
                                    </div>
                                    <span class="text-danger error-text" id="coupon_value_error" data-required="{{ __('coupon_value_required') }}" ></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Quantity') }}<span class="text-danger"> *</span></label>
                                    <select class="form-control select2" id="quantity" name="quantity" data-placeholder="{{ __('select_quantity') }}">
                                        <option value="">{{ __('select_quantity') }}</option>
                                        <option value="limited" {{ $data->quantity == 'limited' ? 'selected' : '' }}>{{ __('Limited') }}</option>
                                        <option value="unlimited" {{ $data->quantity == 'unlimited' ? 'selected' : '' }}>{{ __('Unlimited') }}</option>
                                    </select>
                                    <span class="text-danger error-text" id="quantity_error" data-required="{{ __('quantity_required') }}" ></span>
                                </div>
                            </div>
                            <div class="col-6  {{ $data->quantity == 'limited' ? '' : 'd-none' }}" id="quantity_value_field" >
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Quantity Value') }}<span class="text-danger"> *</span></label>
                                    <input type="text" class="form-control" id="quantity_value" name="quantity_value" placeholder="{{ __('enter_quantity_value') }}" value="{{ $data->quantity_value ?? '' }}">
                                    <span class="text-danger error-text" id="quantity_value_error" data-required="{{ __('quantity_value_required') }}" ></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Start Date') }}<span class="text-danger"> *</span></label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" placeholder="dd-mm-yyyy" value="{{ $data->start_date ?? '' }}">
                                    <span class="text-danger error-text" id="start_date_error" data-required="{{ __('start_date_required') }}" ></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('End Date') }}<span class="text-danger"> *</span></label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" placeholder="dd-mm-yyyy" value="{{ $data->end_date ?? '' }}">
                                    <span class="text-danger error-text" id="end_date_error" data-required="{{ __('end_date_required') }}" ></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="acc-submit d-flex justify-content-end">
                        <button type="submit" class="btn btn-dark" id="save_coupon_btn" data-save="{{ __('Save') }}">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection

@include('coupon::provider.includes')
