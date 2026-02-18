@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <form id="cookies_setting_form">
        <div class="content bg-white">
            <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1 cookies_settings">{{ __('Cookies Settings')}}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="dashboard">{{ __('Dashboard')}}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);" class="Settings">{{ __('Settings')}}</a>
                            </li>
                            <li class="breadcrumb-item active cookies_settings" aria-current="page">{{ __('Cookies Settings')}}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="pe-1 mb-2">
                        @if(isset($permission))
                            @if(hasPermission($permission, 'General Settings', 'edit'))
                                <div class="skeleton label-skeleton label-loader"></div>
                                <button class="btn btn-primary cookies_update_btn fixed-size-btn save d-none real-label" type="submit">{{ __('Save')}}</button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                @include('admin.partials.general_settings_side_menu')
                <div class="col-xxl-10 col-xl-9">
                    <div class="flex-fill ps-1">
                        <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                        </div>
                        <div class="d-md-flex d-block">
                            <div class="flex-fill">
                                <div class="row">
                                    <div class="col-12 col-md-12 mb-3">
                                        <div id="translate_container">
                                            <div class="rounded-0">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <h4 class="lang_title mb-3 d-none real-label">{{ __('available_translations') }}</h4>
                                                @php
                                                $langCode = \App::getLocale();
                                                $language = \Modules\GlobalSetting\app\Models\Language::where('code', $langCode)->first();
                                                @endphp
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="col-md-3">
                                                        <div class="skeleton input-skeleton input-loader"></div>
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
                                    </div>
                                    <div class="col-xl-12">
                                        <input type="hidden" name="group_id" id="group_id" class="form-control" value="10">

                                        <div class="d-flex align-items-center justify-content-between flex-wrap pb-0 rounded">
                                            <div class="row align-items-center flex-fill">
                                                <div class="col-xxl-12 col-lg-12">
                                                    <div class="mb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>
                                                        <h6 class="cookies_content d-none real-label">{{ __('Cookies Content Text')}}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-12 col-lg-12">
                                                    <div class="mb-3">
                                                        <div class="skeleton input-skeleton input-loader"></div>
                                                        <textarea rows="3" class="form-control cookies_content_text enter_cookies_content d-none real-input" name="cookies_content_text" id="summernote" placeholder="{{ __('Enter the cookies content text')}}"></textarea>
                                                        <span class="text-danger error-text" id="cookies_content_text_error"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                            <div class="row align-items-center flex-fill">
                                                <div class="col-xxl-3 col-lg-6">
                                                    <div class="mb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>
                                                        <h6 class="aggree_txt d-none real-label">{{ __('Agree Button Text')}}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-9 col-lg-6">
                                                    <div class="mb-3">
                                                        <div class="skeleton input-skeleton input-loader"></div>
                                                        <input type="text" class="form-control d-none real-input" name="agree_button_text" id="agree_button_text" placeholder="{{ __('Enter agree button text')}}">
                                                        <span class="text-danger error-text" id="agree_button_text_error"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                            <div class="row align-items-center flex-fill">
                                                <div class="col-xxl-3 col-lg-6">
                                                    <div class="mb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>
                                                        <h6 class="decline_txt d-none real-label">{{ __('Decline Button Text')}}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-9 col-lg-6">
                                                    <div class="mb-3">
                                                        <div class="skeleton input-skeleton input-loader"></div>
                                                        <input type="text" class="form-control d-none real-input" name="decline_button_text" id="decline_button_text" placeholder="{{ __('Enter decline button text')}}">
                                                        <span class="text-danger error-text" id="decline_button_text_error"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                            <div class="row align-items-center flex-fill">
                                                <div class="col-xxl-3 col-lg-6">
                                                    <div class="mb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>
                                                        <h6 class="show_text d-none real-label">{{ __('Show Decline Button')}}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-9 col-lg-6">
                                                    <div class="mb-3">
                                                        <div class="status-toggle modal-status">
                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                            <input type="checkbox" name="show_decline_button" id="show_decline_button" class="check">
                                                            <label for="show_decline_button" class="checktoggle d-none real-label"> </label>
                                                            <span class="text-danger error-text" id="show_decline_button_error"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                            <div class="row align-items-center flex-fill">
                                                <div class="col-xxl-3 col-lg-6">
                                                    <div class="mb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>
                                                        <h6 class="lint_txt d-none real-label">{{ __('Link for Cookies Page')}}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-9 col-lg-6">
                                                    <div class="mb-3">
                                                        <div class="skeleton input-skeleton input-loader"></div>
                                                        <input type="text" class="form-control d-none real-input" name="lin_for_cookies_page" id="lin_for_cookies_page" placeholder="{{ __('Enter cookies page link')}}">
                                                        <span class="text-danger error-text" id="lin_for_cookies_page_error"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
@endsection
