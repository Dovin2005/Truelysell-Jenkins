@extends('provider.provider')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="service-wizard mb-4">
                        <ul class="d-flex align-items-center flex-wrap row-gap-2" id="progressbar">
                            <li class="active me-2">
                                <span class="me-2"><i class="ti ti-info-circle"></i></span>
                                <h6 class="translatable" data-translate="service_information">
                                    {{ __('Service Information') }}
                                </h6>
                            </li>
                            <li class="me-2 location_tab d-none">
                                <span class="me-2"><i class="ti ti-map-pin"></i></span>
                                <h6 class="translatable" data-translate="Branch Information">{{ __('Branch Information') }}
                                </h6>
                            </li>
                            <li class="me-2">
                                <span class="me-2"><i class="ti ti-photo"></i></span>
                                <h6 class="translatable" data-translate="gallery">{{ __('Gallery') }}</h6>
                            </li>
                            <li class="me-2">
                                <span class="me-2"><i class="ti ti-shield"></i></span>
                                <h6 class="translatable" data-translate="seo">{{ __('Seo') }}</h6>
                            </li>
                        </ul>
                    </div>

                    <fieldset id="first-field" style="display: block;">
                        <div class="skeleton label-skeleton label-loader"></div>
                        <h4 class="mb-3 translatable d-none real-label" data-translate="available_translations">{{ __('available_translations') }}</h4>
                        @php
                        $langCode = \App::getLocale();
                        $language = \Modules\GlobalSetting\app\Models\Language::where('code', $langCode)->first();
                        @endphp
                        <div class="card rounded-0">
                            <div class="d-flex align-items-center justify-content-between p-2">
                                <div class="col-md-3">
                                    <input type="hidden" name="service_id" id="service_id" readonly>
                                    <input type="hidden" name="service_slug" id="service_slug" readonly>
                                    <input type="hidden" name="parent_id" id="parent_id" readonly>
                                    <input type="hidden" name="category_id" id="category_id" readonly>
                                    <div class="skeleton input-skeleton input-loader"></div>
                                    <select class="form-select d-none real-input" name="language_id" id="language_id"
                                        data-url="{{ url('/api/provider/service-details/') }}"
                                        onchange="fetchPageDetailsDynamic(); setLanguageId();">
                                        @if ($allLanguages->isNotEmpty())
                                        @foreach ($allLanguages as $allLanguage)
                                        <option value="{{ $allLanguage->id }}" {{ ($allLanguage->id) == $language->id ? 'selected' : '' }}>{{ $allLanguage->name }}</option>
                                        @endforeach
                                        @else
                                        <option value="">No Translations Available</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <form id="service-form">
                            <input type="hidden" name="id" id="id" readonly>
                            <input type="hidden" name="userLangId" id="userLangId" value="{{ $userLangId }}">

                            <div class="skeleton label-skeleton label-loader"></div>
                            <h4 class="mb-3 translatable d-none real-label" data-translate="service">Service Information</h4>
                            <div class="card">
                                <div class="card-body">
                                    <div class="accordion" id="accordionPanelsStayOpenExample">
                                        <div class="accordion-item mb-3">
                                            <div class="accordion-header" id="accordion-headingOne">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <div class="accordion-button p-0 translatable d-none real-label"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#accordion-collapseOne" aria-expanded="true"
                                                    data-translate="basic" aria-controls="accordion-collapseOne"
                                                    role="button">
                                                    Basic Information
                                                </div>
                                            </div>
                                            <div id="accordion-collapseOne" class="accordion-collapse collapse show"
                                                aria-labelledby="accordion-headingOne">
                                                <div class="accordion-body p-0 mt-3 pb-1">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <label class="form-label text-name translatable d-none real-label"
                                                                    data-translate="service_name_label"
                                                                    for="service_name">
                                                                    Service Name<span class="text-danger">*</span>
                                                                </label>
                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                <input type="text" name="service_name"
                                                                    id="service_name"
                                                                    class="form-control field-input translatable d-none real-input"
                                                                    data-translate="service_name_placeholder"
                                                                    placeholder="Enter Service Name">
                                                                <span class="invalid-feedback translatable"
                                                                    data-translate="error_1"></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <label class="form-label text-name translatable d-none real-label"
                                                                    data-translate="product_code_label"
                                                                    for="product_code">
                                                                    Product Code<span class="text-danger">*</span>
                                                                </label>
                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                <input type="text" name="product_code"
                                                                    id="product_code"
                                                                    class="form-control field-input translatable d-none real-input"
                                                                    data-translate="product_code_placeholder"
                                                                    placeholder="Enter Product Code">
                                                                <span class="invalid-feedback"
                                                                    id="product_code_error"></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <label class="form-label text-name translatable d-none real-label"
                                                                    for="category" data-translate="category">
                                                                    Category <span class="text-danger">*</span>
                                                                </label>
                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                <select name="category" id="category"
                                                                    class="form-control categoryProviderSelect d-none real-input">
                                                                    <option value=""
                                                                        data-translate="select_category">Select
                                                                        Category</option>

                                                                </select>
                                                                <span class="invalid-feedback"
                                                                    id="category_error"></span>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <label class="form-label text-name translatable d-none real-label"
                                                                    for="sub_category"
                                                                    data-translate="sub_category">
                                                                    Sub Category <span class="text-danger">*</span>
                                                                </label>
                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                <select name="sub_category" id="sub_category"
                                                                    class="form-control subcategories d-none real-input">
                                                                    <option value=""
                                                                        data-translate="select_sub_category">Select
                                                                        Sub Category</option>
                                                                </select>
                                                                <span class="invalid-feedback"
                                                                    id="sub_category_error"></span>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <div class="mb-3 d-none real-label">
                                                                <div class="d-flex align-items-basline justify-content-between">
                                                                    <label class="form-label translatable" data-translate="Description">{{ __('Description') }} <span class="text-danger">*</span></label>
                                                                    @if($chat_status === "1")
                                                                    <div class="mb-1" id="chat">
                                                                        <img src="{{ asset('front/img/stat.png') }}" alt="">
                                                                        <a href="javascript:void(0)" id="openChatModal" class="form-label text-light px-2 py-1 fw-medium">{{ __('generate_ai_content') }}</a>
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                <textarea name="description" id="description" class="form-control translatable d-none real-input" rows="4" data-translate="description_placeholder" placeholder="{{ __('Enter Description') }}"></textarea>
                                                                <span class="invalid-feedback" id="description_error"></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="skeleton label-skeleton label-loader mt-2"></div>
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <div class="mb-3 d-none real-label">
                                                                <label class="form-label text-name translatable"
                                                                    for="include" data-translate="includes">Includes
                                                                    <span class="text-danger"> *</span></label>
                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                <input type="text" name="include" id="include"
                                                                    class="form-control field-input translatable d-none real-input"
                                                                    data-translate="includes_placeholder"
                                                                    data-role="tagsinput"
                                                                    placeholder="Enter Includes">
                                                                <span class="invalid-feedback"
                                                                    id="include_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item mb-3">
                                            <div class="accordion-header" id="accordion-headingTwo">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <div class="accordion-button p-0 translatable d-none real-label"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#accordion-collapseTwo" aria-expanded="true"
                                                    aria-controls="accordion-collapseTwo" data-translate="pricing">
                                                    Pricing
                                                    <div class="form-check form-switch ms-3">
                                                        <div class="skeleton input-skeleton input-loader"></div>
                                                        <input class="form-check-input d-none real-label" value="free_price"
                                                            id="free_price" type="checkbox">
                                                        <label class="form-check-label translatable"
                                                            data-translate="free">Free</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="accordion-collapseTwo" class="accordion-collapse collapse show"
                                                aria-labelledby="accordion-headingTwo">
                                                <div class="accordion-body p-0 mt-3 pb-1">
                                                    <div class="row">
                                                        <div class="col-xl-3 col-md-6">
                                                            <div class="mb-3">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <label class="form-label text-name translatable d-none real-label"
                                                                    data-translate="price_type"
                                                                    for="price_type">Price Type
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                <select name="price_type" id="price_type"
                                                                    class="form-control field-input d-none real-input">
                                                                    <option value=""
                                                                        data-translate="select_price_type">Select
                                                                        Price Type</option>
                                                                    <option value="fixed"
                                                                        data-translate="price_type_fixed">Fixed
                                                                    </option>
                                                                    <option value="hourly"
                                                                        data-translate="price_type_hourly">Hourly
                                                                    </option>
                                                                    <option value="minute"
                                                                        data-translate="price_type_minute">Minutes
                                                                    </option>
                                                                    <option value="squre-metter"
                                                                        data-translate="price_type_square_meter">
                                                                        Square Meter</option>
                                                                    <option value="square-feet"
                                                                        data-translate="price_type_square_feet">
                                                                        Square Feet</option>
                                                                </select>
                                                                <span class="invalid-feedback"
                                                                    id="price_type_error"></span>
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-3 col-md-6 hours-section d-none real-label"
                                                            style="display: none;">
                                                            <div class="mb-3">
                                                                <label class="form-label">{{ __('Add Duration (In Hours)') }}
                                                                    <span class="text-danger">*</span></label>
                                                                <input type="text" name="duration_hours" id="hours_select" class="form-control" value="" maxlength="2">
                                                                <!-- <select id="hours_select" name="duration"
                                                                    class="form-control">
                                                                    @for ($hour = 1; $hour <= 24; $hour++)
                                                                        <option value="{{ $hour }}" {{ $hour == 1 ? 'selected' : '' }}>
                                                                        {{ $hour }}
                                                                        {{ __('hour') }}{{ $hour > 1 ? 's' : '' }}
                                                                        </option>
                                                                        @endfor
                                                                </select> -->
                                                            </div>
                                                        </div>

                                                        <div class="col-xl-3 col-md-6 minutes-section d-none real-label"
                                                            style="display: none;">
                                                            <div class="mb-3">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <label class="form-label d-none real-label">{{ __('Add Minutes') }}
                                                                    <span class="text-danger">*</span></label>
                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                <select id="minutes_select" name="duration_minute"
                                                                    class="form-control d-none real-input">
                                                                    <option value="">{{ __('Select Minutes') }}
                                                                    </option>
                                                                    @for ($minute = 0; $minute < 60; $minute +=5)
                                                                        <option value="{{ $minute }}">
                                                                        {{ str_pad($minute, 2, '0', STR_PAD_LEFT) }}
                                                                        {{ __('minute') }}{{ $minute != 1 ? 's' : '' }}
                                                                        </option>
                                                                        @endfor
                                                                </select>
                                                            </div>
                                                        </div>

                                                        @if ($show_package)
                                                        <!-- Do nothing if show_package is 1 (true)  -->
                                                        @else
                                                        <div class="col-xl-3 col-md-6">
                                                            <div class="mb-3">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <label class="form-label text-name translatable d-none real-label"
                                                                    for="service_price" data-translate="price">Price
                                                                    <span class="text-danger">*</span></label>
                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                <input type="text" name="service_price"
                                                                    id="service_price"
                                                                    class="form-control field-input translatable d-none real-input"
                                                                    data-translate="pricing_placeholder"
                                                                    placeholder="Enter Service Price">
                                                                <span class="invalid-feedback"
                                                                    id="service_price_error"></span>
                                                            </div>
                                                        </div>
                                                        @endif

                                                        @if($show_package)


                                                        <div class="mt-2 mb-2">
                                                            <div class="d-flex gap-3">
                                                                <a href="#"
                                                                    class="btn price-btn btn-primary fw-bold fs-14 rounded-0"
                                                                    id="basic_btn">Basic</a>
                                                                <a href="#"
                                                                    class="btn price-btn btn-primary fw-bold fs-14 rounded-0"
                                                                    id="premium_btn">Premium</a>
                                                                <a href="#"
                                                                    class="btn price-btn btn-primary fw-bold fs-14 rounded-0"
                                                                    id="pro_btn">Pro</a>
                                                            </div>
                                                        </div>

                                                        <div id="basic_container" style="display: none;">
                                                            <input type="hidden" name="basic" id="basic"
                                                                value="basic" class="plans">
                                                            <div class="row">
                                                                <div class="col-xl-3 col-md-6 mt-3">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Price <span
                                                                                class="text-danger">*</span></label>
                                                                        <input type="text"
                                                                            name="basic_service_price"
                                                                            id="basic_service_price"
                                                                            class="form-control"
                                                                            placeholder="Enter Service Price">
                                                                        <span class="invalid-feedback"
                                                                            id="basic_service_price_error"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Price Description
                                                                            <span
                                                                                class="text-danger">*</span></label>
                                                                        <textarea name="basic_price_description"
                                                                            id="basic_price_description"
                                                                            class="form-control" rows="4"
                                                                            placeholder="Enter Price Description"></textarea>
                                                                        <span class="invalid-feedback"
                                                                            id="basic_price_description_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="premium_container" style="display: none;">
                                                            <input type="hidden" name="premium" id="premium"
                                                                value="premium" class="plans">
                                                            <div class="row">
                                                                <div class="col-xl-3 col-md-6 mt-3">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Price <span
                                                                                class="text-danger">*</span></label>
                                                                        <input type="text"
                                                                            name="premium_service_price"
                                                                            id="premium_service_price"
                                                                            class="form-control"
                                                                            placeholder="Enter Service Price">
                                                                        <span class="invalid-feedback"
                                                                            id="premium_service_price_error"></span>
                                                                    </div>
                                                                </div>



                                                                <div class="col-md-12">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Price Description
                                                                            <span
                                                                                class="text-danger">*</span></label>
                                                                        <textarea name="premium_price_description"
                                                                            id="premium_price_description"
                                                                            class="form-control" rows="4"
                                                                            placeholder="Enter Price Description"></textarea>
                                                                        <span class="invalid-feedback"
                                                                            id="premium_price_description_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>


                                                        </div>
                                                        <div id="pro_container" style="display: none;">
                                                            <input type="hidden" name="pro" id="pro" value="pro"
                                                                class="plans">
                                                            <div class="row">
                                                                <div class="col-xl-3 col-md-6 mt-3">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Price <span
                                                                                class="text-danger">*</span></label>
                                                                        <input type="text" name="pro_service_price"
                                                                            id="pro_service_price"
                                                                            class="form-control"
                                                                            placeholder="Enter Service Price">
                                                                        <span class="invalid-feedback"
                                                                            id="pro_service_price_error"></span>
                                                                    </div>
                                                                </div>



                                                                <div class="col-md-12">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Price Description
                                                                            <span
                                                                                class="text-danger">*</span></label>
                                                                        <textarea name="pro_price_description"
                                                                            id="pro_price_description"
                                                                            class="form-control" rows="4"
                                                                            placeholder="Enter Price Description"></textarea>
                                                                        <span class="invalid-feedback"
                                                                            id="pro_price_description_error"></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @endif


                                                    </div>
                                                </div>
                                            </div>
                                            @if($show_slot)

                                            <div class="accordion-item mb-3">
                                                <div class="accordion-header" id="accordion-headingFour">
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <div class="accordion-button p-0 translatable d-none real-label"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#accordion-collapseFour"
                                                        aria-expanded="true" aria-controls="accordion-collapseFour"
                                                        role="button">
                                                        {{ __('Add Service Slot') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="accordion-collapseFour"
                                                class="accordion-collapse collapse show"
                                                aria-labelledby="accordion-headingFour">
                                                <div class="accordion-body p-0 mt-3 pb-1">
                                                    <div class="addservice-info">
                                                        <div>


                                                            <div class="skeleton label-skeleton label-loader"></div>

                                                            <div class="col-xl-12 col-md-6 d-none real-label" id="slotData">
                                                                <div class="mb-3 p-1" style="margin-top: 33px;">
                                                                    @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                                                    <div class="mb-4"
                                                                        id="{{ strtolower($day) }}Data">
                                                                        <div class="d-flex gap-2 mb-2">
                                                                            <input type="checkbox"
                                                                                name="day_checkbox[]"
                                                                                id="{{ strtolower($day) }}_checkbox"
                                                                                value="{{ $day }}">
                                                                            <h6 style="margin-top: 5.2px;">
                                                                                {{ __($day) }}
                                                                            </h6>
                                                                            <a class="add-time-btn"
                                                                                style="margin-top: 0px;">
                                                                                <i class="ti ti-plus me-2 fw-bold"
                                                                                    style="font-size: 15px;"></i>
                                                                            </a>
                                                                        </div>
                                                                        <div id="slotinputs"
                                                                            style="display: none;">
                                                                            <div
                                                                                class="d-flex gap-3 mt-2 additional-time">

                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @endforeach
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @endif

                                        <div class="accordion-item mb-3">
                                            <div class="accordion-header" id="accordion-headingFive">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <div class="accordion-button p-0 translatable d-none real-label"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#accordion-collapseFive"
                                                    data-translate="additional" aria-expanded="true"
                                                    aria-controls="accordion-collapseFive" role="button">
                                                    Add Additional Services
                                                </div>
                                            </div>
                                            <div id="accordion-collapseFive"
                                                class="accordion-collapse collapse show"
                                                aria-labelledby="accordion-headingFive">
                                                <div class="accordion-body p-0 mt-3 pb-1">
                                                    <div class="addservice-info d-none real-label">
                                                        <div class="row" id="appendaddservice" data-name="{{ __("name") }}" data-service_name_placeholder="{{ __("service_name_placeholder") }}" data-price="{{ __("price") }}" data-pricing_placeholder="{{ __("pricing_placeholder") }}" data-description="{{ __("description") }}" data-enter_description="{{ __("enter_description") }}">

                                                        </div>
                                                    </div>
                                                    <div class="skeleton label-skeleton label-loader"></div>

                                                    <a href="javascript:void(0);"
                                                        class="text-primary d-inline-flex align-items-center fs-14 add-extra mb-3 translatable d-none real-label"
                                                        data-translate="new"><i
                                                            class="ti ti-circle-plus me-2"></i>Add New</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-end">
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <button id="service_btn" class="btn btn-dark translatable d-none real-label"
                                            data-translate="continue">Continue</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>

                    <fieldset id="second-field" style="display: none;">
                        <form id="location-form" style="display: none;">
                            <h4 class="mb-3 translatable" data-translate="loc">Location</h4>
                            <div class="card">
                                <div class="card-body">
                                    <div class="border-bottom mb-3 pb-3">
                                        <h4 class="fs-20 translatable" data-translate="add_loc">Add Location</h4>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label text-name translatable"
                                                    data-translate="address_label" for="address">
                                                    Address <span>*</span>
                                                </label>
                                                <input type="text" name="address" id="address"
                                                    class="form-control field-input translatable"
                                                    data-translate="address_placeholder"
                                                    placeholder="Enter Address">
                                            </div>
                                        </div>

                                        <div class="col-xl-4 col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label text-name translatable"
                                                    data-translate="country_label">
                                                    Country <span class="text-danger">*</span>
                                                </label>
                                                <select class="select selects translatable" id="country"
                                                    name="country" data-translate="country_placeholder">
                                                </select>
                                                <span class="invalid-feedback" id="country_error"></span>
                                            </div>
                                        </div>

                                        <div class="col-xl-4 col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label text-name translatable"
                                                    data-translate="state_label">
                                                    State <span class="text-danger">*</span>
                                                </label>
                                                <select class="select selects translatable" id="state" name="state"
                                                    data-translate="state_placeholder">
                                                </select>
                                                <span class="invalid-feedback" id="state_error"></span>
                                            </div>
                                        </div>

                                        <div class="col-xl-4 col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label text-name translatable"
                                                    data-translate="city_label">
                                                    City <span class="text-danger">*</span>
                                                </label>
                                                <select class="select selects translatable" id="city" name="city[]"
                                                    data-translate="city_placeholder" multiple>
                                                </select>
                                                <span class="invalid-feedback" id="city_error"></span>
                                            </div>
                                        </div>

                                        <div class="col-xl-4 col-lg-6">
                                            <div class="mb-3">
                                                <label class="form-label text-name translatable"
                                                    data-translate="pincode_label" for="pincode">
                                                    Pincode <span class="text-danger">(optional)</span>
                                                </label>
                                                <input type="text" name="pincode" id="pincode"
                                                    class="form-control field-input translatable"
                                                    data-translate="pincode_placeholder"
                                                    placeholder="Enter Pincode">
                                            </div>
                                        </div>

                                    </div>
                                    <div class="d-flex align-items-center justify-content-end">
                                        <a id="location_prv" class="btn btn-light me-3 translatable"
                                            data-translate="back">Back</a>
                                        <a id="location_btn" class="btn btn-dark translatable"
                                            data-translate="continue">Continue</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <form id="staff-form">
                            <h4 class="mb-3 translatable" data-translate="">Available Branch</h4>
                            <div class="card">
                                <div class="card-body">
                                    <div class="border-bottom mb-3 pb-3">
                                        <h4 class="fs-20 translatable" data-translate="">Select Branch</h4>
                                    </div>
                                    <div id="staff-container">
                                        <!-- Staff details will be appended here -->
                                    </div>
                                    <div class="d-flex align-items-center justify-content-end">
                                        <a id="staff_prv" class="btn btn-light me-3 translatable"
                                            data-translate="back">Back</a>
                                        <a id="staff_btn" class="btn btn-dark translatable"
                                            data-translate="continue">Continue</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>

                    <fieldset id="third-field" style="display: none;">
                        <form id="image-form">
                            <h4 class="mb-3 translatable" data-translate="gallery">Gallery</h4>
                            <div class="card">
                                <div class="card-body">
                                    <div class="border-bottom mb-3 pb-3">
                                        <h4 class="fs-20 translatable" data-translate="add_images_heading">Add
                                            Images</h4>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label text-name translatable"
                                                    data-translate="add_service_images_label" for="service_images">
                                                    Add Service Images <span class="text-danger">*</span>
                                                </label>
                                                <div class="d-flex align-items-center flex-wrap row-gap-3 gap-2">
                                                    <div
                                                        class="file-upload d-flex align-items-center justify-content-center flex-column">
                                                        <i class="ti ti-photo mb-2"></i>
                                                        <label class="form-label translatable"
                                                            data-translate="image_label">Image</label>
                                                        <input type="file" name="service_images[]"
                                                            id="service_images"
                                                            class="form-control field-input translatable"
                                                            data-translate="service_images_placeholder"
                                                            accept="image/*" multiple>
                                                        <span class="invalid-feedback"
                                                            id="service_images_error"></span>
                                                    </div>
                                                    <div id="image_preview_container" class="d-flex flex-wrap">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Video') }} <span></span></label>
                                                    <input type="text" name="service_video" id="service_video" class="form-control" placeholder="Add video URL">
                                                    <span class="invalid-feedback" id="service_video_error"></span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="d-flex align-items-center justify-content-end border-top pt-3">
                                        <a id="image_prv" class="btn btn-light me-3 translatable"
                                            data-translate="back">Back</a>
                                        <button id="image_btn" class="btn btn-dark translatable"
                                            data-translate="continue">Continue</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>

                    <fieldset id="forth-field" style="display: none;">
                        <form id="seo-form">
                            <input type="hidden" name="language_id" id="language_id_input" value="1">
                            <h4 class="mb-3 translatable" data-translate="ser_seo">Seo</h4>
                            <div class="card">
                                <div class="card-body">
                                    <div class="border-bottom mb-3 pb-3">
                                        <h4 class="fs-20 translatable" data-translate="add_seo">Add Seo</h4>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label text-name translatable"
                                                    data-translate="seo_title_label" for="seo_title">
                                                    SEO Title <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="seo_title" id="seo_title"
                                                    class="form-control field-input translatable"
                                                    data-translate="seo_title_placeholder"
                                                    placeholder="Enter SEO Title">
                                                <span class="invalid-feedback" id="seo_title_error"></span>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label text-name translatable"
                                                    data-translate="seo_tag_label" for="seo_tag">
                                                    SEO Tag <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="seo_tag" id="seo_tag"
                                                    class="form-control field-input translatable"
                                                    data-role="tagsinput" data-translate="seo_tag_placeholder"
                                                    placeholder="Enter SEO Tag">
                                                <span class="invalid-feedback" id="seo_tag_error"></span>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label text-name translatable"
                                                    data-translate="seo_description_label" for="seo_description">
                                                    SEO Description <span class="text-danger">*</span>
                                                </label>
                                                <textarea name="seo_description" id="seo_description"
                                                    class="form-control field-input translatable" rows="4"
                                                    data-translate="seo_description_placeholder"
                                                    placeholder="Enter SEO Description"></textarea>
                                                <span class="invalid-feedback" id="seo_description_error"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-end">
                                        <a id="seo_prv" class="btn btn-light me-3 translatable"
                                            data-translate="back">Back</a>
                                        <button id="seo_btn" class="btn btn-dark add_btn translatable"
                                            data-translate="update">Update</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </fieldset>

                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="add_transulate">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form>
                <div class="modal-body text-center">
                    <!-- <span class="delete-icon">
                        <i class="ti ti-trash-x"></i>
                    </span> -->
                    <h4>{{ __('Confirm Translation') }}</h4>
                    <p>{{ __('Are you sure you want to translation this will take some time.') }}</p>
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-2"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <a class="btn btn-danger" id="translate_button">{{ __('Yes') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <h6 class="modal-title" id="chatModalLabel">Chat with ChatGPT</h6>
                    <i class="ti ti-copy" id="copyChatResponse" style="cursor: pointer;" title="Copy Response"></i>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="chat-container" style="height: 300px; overflow-y: scroll;">
                    <div id="chat-box">
                        <!-- Chat messages will appear here -->
                    </div>
                </div>
                <div class="input-group mt-3">
                    <input type="text" id="userMessage" name="message" class="form-control" placeholder="Type your message..." />
                    <button class="btn btn-primary" id="sendMessage">Send</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection