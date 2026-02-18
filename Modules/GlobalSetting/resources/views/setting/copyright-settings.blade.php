@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <form id="copyright_setting_form">
        <div class="content bg-white">
            <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1 copyright_settings">{{ __('Copyright Settings') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="dashboard">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);" class="Settings">{{ __('Settings') }}</a>
                            </li>
                            <li class="breadcrumb-item active copyright_settings" aria-current="page">{{ __('Copyright Settings') }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="pe-1 mb-2">
                            @if(isset($permission))
                                @if(hasPermission($permission, 'General Settings', 'edit'))
                                <div class="skeleton label-skeleton label-loader"></div>
                                <button class="btn btn-primary copyright_update_btn fixed-size-btn update d-none real-label" type="submit">{{ __('Update') }}</button>
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
                                        <input type="hidden" name="group_id" id="group_id" class="form-control" value="8">

                                        <div class="d-flex align-items-center justify-content-between flex-wrap pb-0 rounded">
                                            <div class="row align-items-center flex-fill">
                                                <div class="col-xxl-12 col-lg-12">
                                                    <div class="mb-3">
                                                        <div class="skeleton label-skeleton label-loader"></div>
                                                        <h6 class="mb-3 Copyright d-none real-label">{{ __('Copyright') }}</h6>
                                                    </div>
                                                </div>
                                                <div class="col-xxl-12 col-lg-12">
                                                    <div class="mb-3">
                                                        <div class="skeleton input-skeleton input-loader"></div>
                                                        <textarea
                                                            class="form-control copyright d-none real-input"
                                                            name="copyright"
                                                            id="summernote"
                                                            rows="3"
                                                            placeholder="{{ __('Enter Copyright Text') }}">
                                                        </textarea>
                                                        <span class="text-danger error-text" id="copyright_error"></span>
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
