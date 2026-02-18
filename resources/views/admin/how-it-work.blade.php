@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <form id="how_it_work_setting_form">
        <div class="content bg-white">
            <div class="d-md-flex d-block align-items-center justify-content-between -bottom pb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('how_it_work') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="dashboard_text">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);" class="how_it_work_text">{{ __('how_it_work') }}</a>
                            </li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="pe-1 mb-2">
                        @if(isset($permission))
                            @if(hasPermission($permission, 'Pages', 'edit'))
                            <div class="mt-2 ms-2 skeleton label-skeleton label-loader"></div>
                            <button class="btn btn-primary how_it_work_update_btn d-none real-label" type="submit">{{ __('Save') }}</button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xxl-12">
                    <div class="flex-fill">
                            <div class="d-md-flex">
                                <div class="row flex-fill">
                                    <div class="col-xl-12">
                                        <div>
                                            <input type="hidden" name="group_id" id="group_id" class="form-control" value="14" >

                                            <div class="d-flex align-items-center justify-content-between flex-wrap border mb-3 p-3 pb-0 rounded">
                                                <div class="row align-items-center flex-fill">
                                                    <div id="translate_container">
                                                        <div class="card rounded-0">
                                                            <div class="mt-2 ms-2 skeleton label-skeleton label-loader"></div>
                                                            <h4 class="p-2 lang_title d-none real-label">{{ __('available_translations') }}</h4>
                                                            @php
                                                                $langCode = \App::getLocale();
                                                                $language = \Modules\GlobalSetting\app\Models\Language::where('code', $langCode)->first();
                                                            @endphp
                                                            <div class="d-flex align-items-center justify-content-between p-2">
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
                                                    <div class="col-xxl-12 col-lg-12">
                                                        <div class="mb-3">
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                            <textarea rows="3" class="form-control how_it_work_content d-none real-input" name="how_it_work_content" id="summernote"></textarea>
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
