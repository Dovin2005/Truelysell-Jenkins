@extends('admin.admin')

@section('content')
    <div class="page-wrapper">

        <div class="content">
            <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('color_settings')}}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">{{ __('dashboard')}}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);">{{ __('Settings')}}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('color_settings')}}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="mb-2">

                    </div>
                </div>
            </div>

            <div class="row">
                @include('admin.partials.general_settings_side_menu')
                <div class="col-lg-9">
                    <form id="colorSettingForm">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="fw-bold">{{ __('color_settings') }}</h5>
                            </div>
                            <div class="card-body">
                                <input type="hidden" name="group_id" id="group_id" class="form-control" value="16">

                                <div class="localization-content mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <label class="form-label d-none real-label">
                                                    {{ __('Primary Color') }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="color" class="form-control form-control-color real-input d-none"
                                                    id="primary_color" name="primary_color"
                                                    value="{{ old('primary_color', $settings['primary_color'] ?? '#FD2692') }}">
                                                <span id="primary_color_error" class="text-danger error-text"></span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <label class="form-label d-none real-label">
                                                    {{ __('Secondary Colour')}} <span class="text-danger">*</span>
                                                </label>
                                                <input type="color" class="form-control form-control-color real-input d-none"
                                                    id="secondary_color" name="secondary_color"
                                                    value="{{ old('secondary_color', $settings['secondary_color'] ?? '#0A67F2') }}">
                                                <span id="secondary_color_error" class="text-danger error-text"></span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <label class="form-label d-none real-label">
                                                    {{ __('primary_hover_color') }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="color" class="form-control form-control-color real-input d-none"
                                                    id="primary_hover_color" name="primary_hover_color"
                                                    value="{{ old('primary_hover_color', $settings['primary_hover_color'] ?? '#db0077') }}">
                                                <span id="primary_hover_color_error" class="text-danger error-text"></span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <label class="form-label d-none real-label">
                                                    {{ __('secondary_hover_color') }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="color" class="form-control form-control-color real-input d-none"
                                                    id="secondary_hover_color" name="secondary_hover_color"
                                                    value="{{ old('secondary_hover_color', $settings['secondary_hover_color'] ?? '#20226f') }}">
                                                <span id="secondary_hover_color_error"
                                                    class="text-danger error-text"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-end">
                                    <div class="skeleton label-skeleton label-loader me-3"></div>
                                    <a href="{{ route('admin.dashboard') }}"
                                        class="btn btn-light me-3 d-none real-label">{{ __('cancel') }}</a>
                                    <div class="skeleton button-skeleton label-loader"></div>
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <button type="submit" class="btn btn-primary saveCustomSettings d-none real-label">{{ __('save_changes') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('assets/js/color-settings.js') }}"></script>
@endpush