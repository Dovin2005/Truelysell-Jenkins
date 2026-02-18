@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content bg-white">
        <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1 translate-key" data-translate="footer_builder">{{ __('footer_builder') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}" class="translate-key" data-translate="Dashboard">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);" class="translate-key" data-translate="content">{{ __('content') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active translate-key" aria-current="page" data-translate="footer_builder">{{ __('footer_builder') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        @if(isset($permission))
            @if(hasPermission($permission, 'Footer Builder', 'edit'))
                @php $edit = 1; @endphp
            @else
                @php $edit = 0; @endphp
            @endif
        @endif
        <div class="row pt-3">
            <div class="col-xxl-8 col-xl-8">
                <div class="flex-fill">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="general_tab" role="tabpanel">
                            <form id="footerForm">
                                <div class="d-md-flex d-block">
                                    <div class="flex-fill">
                                        <div class="card">
                                            <div class="card-body pb-1">

                                                <div class="row">
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
                                                                        <option value="{{ $allLanguage->id }}" {{ $allLanguage->id == $language->id ? 'selected' : '' }}>{{ $allLanguage->name }}</option>
                                                                        @endforeach
                                                                        @else
                                                                        <option value="">No Lang Found</option>
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <div class="textareasContainer">
                                                                <input type="hidden" id="id" name="id">

                                                                <div class="textarea-item mb-4">
                                                                    <div class="d-flex mb-3">
                                                                        <div class="me-auto">
                                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                                            <h4 class="text-left translate-key d-none real-label" data-translate="footer_section_1">{{ __('footer_section_1') }}</h4>
                                                                        </div>
                                                                        <div class="ms-auto">
                                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between d-none real-label">
                                                                                @if ($edit == 1)
                                                                                    <div class="status-title me-2">
                                                                                        <h5 class="translate-key" data-translate="Status">{{ __('Status') }}</h5>
                                                                                    </div>
                                                                                    <div class="status-toggle modal-status">
                                                                                        <input type="checkbox" id="status_1" name="sections[1][status]" class="check" checked>
                                                                                        <label for="status_1" class="checktoggle"> </label>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="mb-3">
                                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                                <label class="form-label translate-key d-none real-label" data-translate="section_title">{{ __('section_title') }}</label>

                                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                                <input type="text" class="form-control d-none real-input" id="section_title_1" name="sections[1][title]" placeholder="{{ __('enter_section_title') }}">
                                                                                <span class="invalid-feedback" id="section_title_error_1"></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="skeleton input-skeleton input-loader"></div>
                                                                    <textarea name="sections[1][footer_content]" id="footer_content_1" class="form-control page_content custom-summernote d-none real-input"  placeholder="{{ __('enter_footer_content') }}"></textarea>
                                                                </div>

                                                                <div class="textarea-item mb-4">
                                                                    <div class="d-flex mb-3">
                                                                        <div class="me-auto">
                                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                                            <h4 class="text-left translate-key d-none real-label" data-translate="footer_section_2">{{ __('footer_section_2') }}</h4>
                                                                        </div>
                                                                        <div class="ms-auto">
                                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between d-none real-label">
                                                                                @if ($edit == 1)
                                                                                    <div class="status-title me-2">
                                                                                        <h5 class="translate-key" data-translate="Status">{{ __('Status') }}</h5>
                                                                                    </div>
                                                                                    <div class="status-toggle modal-status">
                                                                                        <input type="checkbox" id="status_2" name="sections[2][status]" class="check" checked>
                                                                                        <label for="status_2" class="checktoggle"> </label>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="mb-3">
                                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                                <label class="form-label translate-key d-none real-label" data-translate="section_title">{{ __('section_title') }}</label>

                                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                                <input type="text" class="form-control d-none real-input" id="section_title_2" name="sections[2][title]" placeholder="{{ __('enter_section_title') }}">
                                                                                <span class="invalid-feedback" id="section_title_error_2"></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="skeleton input-skeleton input-loader"></div>
                                                                    <textarea name="sections[2][footer_content]" id="footer_content_2" class="form-control page_content custom-summernote d-none real-input"  placeholder="{{ __('enter_footer_content') }}"></textarea>
                                                                </div>

                                                                <div class="textarea-item mb-4">
                                                                    <div class="d-flex mb-3">
                                                                        <div class="me-auto">
                                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                                            <h4 class="text-left translate-key d-none real-label" data-translate="footer_section_3">{{ __('footer_section_3') }}</h4>
                                                                        </div>
                                                                        <div class="ms-auto">
                                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between d-none real-label">
                                                                                @if ($edit == 1)
                                                                                    <div class="status-title me-2">
                                                                                        <h5 class="translate-key" data-translate="Status">{{ __('Status') }}</h5>
                                                                                    </div>
                                                                                    <div class="status-toggle modal-status">
                                                                                        <input type="checkbox" id="status_3" name="sections[3][status]" class="check" checked>
                                                                                        <label for="status_3" class="checktoggle"> </label>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="mb-3">
                                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                                <label class="form-label translate-key d-none real-label" data-translate="section_title">{{ __('section_title') }}</label>

                                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                                <input type="text" class="form-control d-none real-input" id="section_title_3" name="sections[3][title]" placeholder="{{ __('enter_section_title') }}">
                                                                                <span class="invalid-feedback" id="section_title_error_3"></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="skeleton input-skeleton input-loader"></div>
                                                                    <textarea name="sections[3][footer_content]" id="footer_content_3" class="form-control page_content custom-summernote d-none real-input"  placeholder="{{ __('enter_footer_content') }}"></textarea>
                                                                </div>

                                                                <div class="textarea-item mb-4">
                                                                    <div class="d-flex mb-3">
                                                                        <div class="me-auto">
                                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                                            <h4 class="text-left translate-key d-none real-label" data-translate="footer_section_4">{{ __('footer_section_4') }}</h4>
                                                                        </div>
                                                                        <div class="ms-auto">
                                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between d-none real-label">
                                                                                @if ($edit == 1)
                                                                                    <div class="status-title me-2">
                                                                                        <h5 class="translate-key" data-translate="Status">{{ __('Status') }}</h5>
                                                                                    </div>
                                                                                    <div class="status-toggle modal-status">
                                                                                        <input type="checkbox" id="status_4" name="sections[4][status]" class="check" checked>
                                                                                        <label for="status_4" class="checktoggle"> </label>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="mb-3">
                                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                                <label class="form-label translate-key d-none real-label" data-translate="section_title">{{ __('section_title') }}</label>

                                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                                <input type="text" class="form-control d-none real-input" id="section_title_4" name="sections[4][title]" placeholder="{{ __('enter_section_title') }}">
                                                                                <span class="invalid-feedback" id="section_title_error_4"></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="skeleton input-skeleton input-loader"></div>
                                                                    <textarea name="sections[4][footer_content]" id="footer_content_4" class="form-control page_content custom-summernote d-none real-input"  placeholder="{{ __('enter_footer_content') }}"></textarea>
                                                                </div>

                                                                <div class="textarea-item mb-4">
                                                                    <div class="d-flex mb-3">
                                                                        <div class="me-auto">
                                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                                            <h4 class="text-left translate-key d-none real-label" data-translate="footer_section_5">{{ __('footer_section_5') }}</h4>
                                                                        </div>
                                                                        <div class="ms-auto">
                                                                            <div class="skeleton label-skeleton label-loader"></div>
                                                                            <div class="modal-satus-toggle d-flex align-items-center justify-content-between d-none real-label">
                                                                                @if ($edit == 1)
                                                                                    <div class="status-title me-2">
                                                                                        <h5 class="translate-key" data-translate="Status">{{ __('Status') }}</h5>
                                                                                    </div>
                                                                                    <div class="status-toggle modal-status">
                                                                                        <input type="checkbox" id="status_5" name="sections[5][status]" class="check" checked>
                                                                                        <label for="status_5" class="checktoggle"> </label>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="mb-3">
                                                                                <div class="skeleton label-skeleton label-loader"></div>
                                                                                <label class="form-label translate-key d-none real-label" data-translate="section_title">{{ __('section_title') }}</label>

                                                                                <div class="skeleton input-skeleton input-loader"></div>
                                                                                <input type="text" class="form-control d-none real-input" id="section_title_5" name="sections[5][title]" placeholder="{{ __('enter_section_title') }}">
                                                                                <span class="invalid-feedback" id="section_title_error_5"></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="skeleton input-skeleton input-loader"></div>
                                                                    <textarea name="sections[5][footer_content]" id="footer_content_5" class="form-control page_content custom-summernote d-none real-input"  placeholder="{{ __('enter_footer_content') }}"></textarea>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="form-group col-md-12 mb-2">
                                                        <div class="modal-satus-toggle d-flex align-items-center justify-content-between">
                                                            @if ($edit == 1)
                                                                <div class="status-title">
                                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                                    <h5 class="translate-key d-none real-label" data-translate="Status">{{ __('Status') }}</h5>
                                                                </div>
                                                                <div class="status-toggle modal-status">
                                                                    <div class="skeleton label-skeleton label-loader"></div>
                                                                    <input type="checkbox" id="status" name="status" class="check">
                                                                    <label for="status" class="checktoggle d-none real-label"></label>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal-footer my-3">
                                                    @if ($edit == 1)
                                                        <div class="skeleton label-skeleton label-loader"></div>
                                                        <button type="submit" id="save_footer" class="btn btn-primary d-none real-label">{{ __('Save') }}</button>
                                                    @endif
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
        </div>
    </div>
</div>
@endsection
