@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content bg-white">
        <div class="d-md-flex d-block align-items-center justify-content-between">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1 translate" data-translate="menu_builder" >{{ __('menu_builder') }}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}" class="translate" data-translate="Dashboard">{{ __('Dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);" class="translate" data-translate="content">{{ __('content') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active translate" aria-current="page" data-translate="menu_builder">{{ __('menu_builder') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        @php $isVisible = 0; @endphp
        @if(isset($permission))
            @if(hasPermission($permission, 'Menu Builder', 'delete'))
                @php $delete = 1; $isVisible = 1; @endphp
            @else
                @php $delete = 0; @endphp
            @endif
            @if(hasPermission($permission, 'Menu Builder', 'edit'))
                @php $edit = 1; $isVisible = 1; @endphp
            @else
                @php $edit = 0; @endphp
            @endif
            @if(hasPermission($permission, 'Menu Builder', 'create'))
                @php $create = 1; $isVisible = 1; @endphp
            @else
                @php $create = 0; @endphp
            @endif
            <div id="has_permission" data-create="{{ $create }}" data-delete="{{ $delete }}" data-edit="{{ $edit }}" data-visible="{{ $isVisible }}"></div>
        @else
            <div id="has_permission" data-delete="1" data-edit="1" data-create="1"></div>
        @endif
        <div class="row pt-2">
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
            <div class="{{ $edit == 0 && $create == 0 ? 'col-xxl-6 col-xl-6' : 'col-xxl-4 col-xl-4'}}">
                <div class="card">
                    <div class="card-header justify-content-between d-flex flex-wrap">
                        <div class="skeleton label-skeleton label-loader"></div>
                        <div class="card-title translate d-none real-label" data-translate="built_in_menus">
                            {{ __('built_in_menus') }}
                        </div>
                    </div>
                    <div class="card-body built_in_menus">
                        <div class="card mb-2 card-loader">
                            <div class="skeleton card-skeleton"></div>
                        </div>
                        <div class="card mb-2 card-loader">
                            <div class="skeleton card-skeleton"></div>
                        </div>
                        <div class="card mb-2 card-loader">
                            <div class="skeleton card-skeleton"></div>
                        </div>
                        <div class="card mb-2 card-loader">
                            <div class="skeleton card-skeleton"></div>
                        </div>
                        <div class="card mb-2 card-loader">
                            <div class="skeleton card-skeleton"></div>
                        </div>                        
                    </div>
                </div>
            </div>
            <div class="{{ $edit == 0 && $create == 0 ? 'col-xxl-6 col-xl-6' : 'col-xxl-4 col-xl-4'}}">
                <div class="card">
                    <div class="card-header justify-content-between d-flex flex-wrap">
                        <div class="skeleton label-skeleton label-loader"></div>
                        <div class="card-title translate d-none real-label" data-translate="website_menus">
                            {{ __('website_menus') }}
                        </div>
                        <div class="ms-auto">
                        @if($edit)
                            <button class="btn btn-primary d-none" id="save_all_menus" type="submit" data-save="{{ __('Save') }}">{{ __('Save') }}</button>
                        @endif
                        </div>
                    </div>
                    <div class="card-body website_menus" data-empty="{{ __('no_data_available') }}">
                        <div class="card mb-2 card-loader">
                            <div class="skeleton card-skeleton"></div>
                        </div>
                        <div class="card mb-2 card-loader">
                            <div class="skeleton card-skeleton"></div>
                        </div>
                        <div class="card mb-2 card-loader">
                            <div class="skeleton card-skeleton"></div>
                        </div>
                        <div class="card mb-2 card-loader">
                            <div class="skeleton card-skeleton"></div>
                        </div>
                        <div class="card mb-2 card-loader">
                            <div class="skeleton card-skeleton"></div>
                        </div>
                        <div id="empty_web_menus" style="display: none">
                            <span class="d-flex align-items-center justify-content-center empty_info">
                                {{ __('no_data_available') }}
                            </span>
                        </div>
                        <div class="dd nestable" id="nestable">
                            <ol class="dd-list">
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            @if ($create == 1 || $edit == 1)
            <div class="col-xxl-4 col-xl-4">
                <div class="card">
                    <div class="card-header justify-content-between d-flex">
                        <div class="skeleton label-skeleton label-loader"></div>
                        <div class="card-title translate d-none real-label" data-translate="add_edit_menu">
                            {{__('add_edit_menu')}}
                        </div>
                    </div>
                    <div class="card-body">
                      <form class="" id="menuBuilderForm">
                        <input type="hidden" name="method" id="method" value="add">
                        <input type="hidden" name="currentEditURL" id="currentEditURL">
                        <h3 class="mb-2 d-none">Editing <span id="currentEditName"></span></h3>
                        <div class="form-group mb-3">
                            <div class="skeleton label-skeleton label-loader"></div>
                            <label for="name" class="form-label d-none real-label">{{ __('name') }}<span class="text-danger"> *</span></label>

                            <div class="skeleton input-skeleton input-loader"></div>
                            <input type="text" class="form-control d-none real-input" name="name" id="name" placeholder="{{__('enter_menu_name')}}">
                            <span class="text-danger error-text" id="name_error"></span>
                        </div>
  
                        <div class="form-group mb-3">
                            <div class="skeleton label-skeleton label-loader"></div>
                            <label for="url" class="form-label d-none real-label">{{ __('url') }}<span class="text-danger"> *</span></label>

                            <div class="skeleton input-skeleton input-loader"></div>
                            <input type="text" class="form-control d-none real-input" name="url" id="url" placeholder="{{__('enter_menu_url')}}">
                            <span class="text-danger error-text" id="url_error"></span>
                        </div>
  
                        <div class="form-group mb-3">
                            <div class="skeleton label-skeleton label-loader"></div>
                            <label for="target" class="form-label d-none real-label">{{ __('target') }}<span class="text-danger"> *</span></label>

                            <div class="skeleton input-skeleton input-loader"></div>
                            <select name="target" id="target" class="form-select d-none real-input">
                                <option value="_self">{{ __('self') }}</option>
                                <option value="_blank">{{ __('blank') }}</option>
                            </select>
                        </div>
                      </form>
                    </div>
                    <div class="card-footer">
                        @if($edit)
                            <button type="button" class="btn btn-primary d-none" id="updateBtn">{{__('Save')}}</button>
                        @endif
                        @if($create)
                            <div class="skeleton label-skeleton label-loader"></div>
                            <button type="button" class="btn btn-primary d-none real-label" id="addNewBtn">{{__('add')}}</button>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection