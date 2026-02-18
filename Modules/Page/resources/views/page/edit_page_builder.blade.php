@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content bg-white">
        <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('edit_page_builder') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}" class="dashboard">{{ __('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);" class="">{{ __('content') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);" class="">{{ __('pages') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">
                                <span class="label-page-builder ">{{ __('page_builder') }}</span>
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('edit_page_builder') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row pt-3">
            <div id="translate_container">
                <div class="card rounded-0">
                    <div class="mt-2 ms-2 skeleton label-skeleton label-loader"></div>
                    <h4 class="p-2 lang_title d-none real-label">{{ __('available_translations') }}</h4>
                    @php
                    $langCode = \App::getLocale();
                    $language = \Modules\GlobalSetting\app\Models\Language::where('code', $langCode)->first();
                    @endphp
                    <div class="d-flex align-items-center justify-content-between p-2">
                        <div class="skeleton input-skeleton input-loader"></div>
                        <div class="col-md-3">
                            <select class="form-select d-none real-input" name="language_id" id="language_id">
                                @if ($allLanguages->isNotEmpty())
                                @foreach ($allLanguages as $allLanguage)
                                <option value="{{ $allLanguage->id }}" {{ ($allLanguage->id) == $language->id ? 'selected' : '' }}>{{ $allLanguage->name }}</option>
                                @endforeach
                                @else
                                <option value="">No Lang Found</option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-8 col-xl-8 ">
                <div class="flex-fill">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="general_tab" role="tabpanel">
                            <form id="editPageBuilderForm" enctype="multipart/form-data">
                                <input type="hidden" id="edit_slug" name="edit_slug">
                                <input type="hidden" id="id" name="id" value="{{ $id }}">
                                <input type="hidden" id="parent_id" name="parent_id">
                                <input type="hidden" id="lang_id" name="lang_id">
                                <input type="hidden" name="language_id" id="language_id_input" value="1">
                                <div class="d-md-flex d-block">
                                    <div class="flex-fill">
                                        <div class="card">
                                            <div class="card-body pb-1">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <label class="form-label page_title_label d-none real-label">{{ __('page_title_label') }}</label>

                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <input type="text" name="page_title" id="page_title" class="form-control page_title_placeholder d-none real-input" placeholder="{{ __('page_title_placeholder') }}">
                                                            <span class="invalid-feedback" id="page_title_error"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <label class="form-label slug_label d-none real-label">{{ __('slug_label') }}</label>
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <input type="text" name="slug" id="slug" class="form-control slug_placeholder d-none real-input" placeholder="{{ __('slug_placeholder') }}">
                                                            <span class="invalid-feedback" id="slug_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Additional Sections -->
                                                <!-- Add Section -->
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <div class="d-flex justify-content-between m-1">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <label class="form-label"></label>
                                                                <button type="button" id="addTextarea" class="btn btn-primary rounded-0 btn-md mb-2 add_section d-none real-label">{{ __('add_section') }}</button>
                                                            </div>
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <div class="textareasContainer" id="draggable-left" data-section_title="{{ __("section_title") }}" data-section_title_placeholder="{{ __("section_title_placeholder") }}" data-section_label="{{ __("section_label") }}" data-section_label_placeholder="{{ __("section_label_placeholder") }}" data-enter_page_content="{{ __("enter_page_content") }}" data-status="{{ __("status") }}"></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- About Us -->
                                                <div class="row" id="aboutUsContainer">
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <label class="form-label about_us_label d-none real-label">{{ __('about_us_label') }}</label>

                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <textarea name="about_us" id="aboutUsSummernote" class="form-control about_us d-none real-input" placeholder="{{ __('about_us_placeholder') }}"></textarea>
                                                            <span class="invalid-feedback" id="about_us_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Terms and Conditions -->
                                                <div class="row" id="termsConditionsContainer">
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <label class="form-label terms_conditions_label d-none real-label">{{ __('terms_conditions_label') }}</label>

                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <textarea name="terms_conditions" id="termsConditionsSummernote" class="form-control terms_conditions d-none real-input" placeholder="{{ __('terms_conditions_placeholder') }}"></textarea>
                                                            <span class="invalid-feedback" id="terms_conditions_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Privacy Policy -->
                                                <div class="row" id="privacyPolicyContainer">
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <label class="form-label privacy_policy_label d-none real-label">{{ __('privacy_policy_label') }}</label>

                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <textarea name="privacy_policy" id="privacyPolicySummernote" class="form-control privacy_policy d-none real-input" placeholder="{{ __('privacy_policy_placeholder') }}"></textarea>
                                                            <span class="invalid-feedback" id="privacy_policy_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Contact Us -->
                                                <div class="row" id="contactUsContainer">
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <label class="form-label contact_us_label d-none real-label">{{ __('contact_us_label') }}</label>

                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <textarea name="contact_us" id="contactUsSummernote" class="form-control contact_us d-none real-input" placeholder="{{ __('contact_us_placeholder') }}"></textarea>
                                                            <span class="invalid-feedback" id="contact_us_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- SEO Fields -->
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <label class="form-label seo_tags_label d-none real-label">{{ __('seo_tags_label') }}</label>

                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <input type="text" class="form-control seo_tags_placeholder d-none real-input" id="tag" name="tag" data-role="tagsinput" placeholder="{{ __('seo_tags_placeholder') }}">
                                                            <span class="invalid-feedback" id="tag_error"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <label class="form-label seo_title_label d-none real-label">{{ __('seo_title_label') }}</label>

                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <input type="text" class="form-control seo_title_placeholder d-none real-input" id="seo_title" name="seo_title" placeholder="{{ __('seo_title_placeholder') }}">
                                                            <span class="invalid-feedback" id="seo_title_error"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <label class="form-label seo_description_label d-none real-label">{{ __('seo_description_label') }}</label>

                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <textarea cols="4" rows="7" class="form-control seo_description_placeholder d-none real-input" id="seo_description" name="seo_description" placeholder="{{ __('seo_description_placeholder') }}"></textarea>
                                                            <span class="invalid-feedback" id="seo_description_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Status Toggle -->
                                                <div class="row">
                                                    <div class="form-group col-md-12 mb-2">
                                                        <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                                            <div class="status-title">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <h5 class="status_toggle_label d-none real-label">{{ __('status_toggle_label') }}</h5>
                                                            </div>
                                                            <div class="status-toggle modal-status">
                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                <input type="checkbox" id="status" class="check user8">
                                                                <label for="status" class="checktoggle d-none real-label"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Modal Footer -->
                                                <div class="modal-footer my-3">
                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                    <button type="submit" id="edit_btn_page" class="btn btn-primary edit_btn_page d-none real-label" data-update-text="{{ __('update_button') }}" data-update-success="{{ __('page_update_success') }}">{{ __('update_button') }}</button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-xl-4 theiaStickySidebar">
                <div id="cardContainer">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="skeleton card-skeleton card-loader"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="skeleton card-skeleton card-loader"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="skeleton card-skeleton card-loader"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="skeleton card-skeleton card-loader"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="skeleton card-skeleton card-loader"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="skeleton card-skeleton card-loader"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="skeleton card-skeleton card-loader"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="skeleton card-skeleton card-loader"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="skeleton card-skeleton card-loader"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="skeleton card-skeleton card-loader"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="skeleton card-skeleton card-loader"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="skeleton card-skeleton card-loader"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const translations = {
        statusToggle: "{{ __('Status') }}",
        sectionTitle: "{{ __('Section Title') }}",
        sectionLabel: "{{ __('Section Label') }}",
        enterSectionTitle: "{{ __('Enter Section Title') }}",
        enterSectionLabel: "{{ __('Enter Section Label') }}",
        enterPageContent: "{{ __('Enter Page Content') }}",
    };
</script>

@endsection
