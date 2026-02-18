@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{__('post_list')}}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{__('Dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{__('blogs')}}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{__('post_list')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    @if(isset($permission))
                        @if(hasPermission($permission, 'Blogs', 'create'))
                        <div class="skeleton label-skeleton label-loader"></div>
                        <a href="#" class="btn btn-primary d-none real-label" id="add_post_btn" data-bs-toggle="modal"
                            data-bs-target="#blog_post_modal"><i class="ti ti-square-rounded-plus-filled me-2"></i>{{__('add_post')}}</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @php $isVisible = 0; @endphp
        @if(isset($permission))
            @if(hasPermission($permission, 'Blogs', 'delete'))
                @php $delete = 1; $isVisible = 1; @endphp
            @else
                @php $delete = 0; @endphp
            @endif
            @if(hasPermission($permission, 'Blogs', 'edit'))
                @php $edit = 1; $isVisible = 1; @endphp
            @else
                @php $edit = 0; @endphp
            @endif
            <div id="has_permission" data-delete="{{ $delete }}" data-edit="{{ $edit }}" data-visible="{{ $isVisible }}"></div>
        @else
            <div id="has_permission" data-delete="1" data-edit="1"></div>
        @endif
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
                                    <td>
                                        <div class="skeleton data-skeleton data-loader"></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table d-none" id="blogPostTable" data-edit="{{ __('Edit') }}" data-delete="{{ __('Delete') }}" data-yes="{{ __('Yes') }}" data-no="{{ __('no') }}" data-empty="{{ __('post_empty_info') }}">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{__('title')}}</th>
                                    <th>{{__('category')}}</th>
                                    <th>{{__('popular')}}</th>
                                    @if ($edit == 1)
                                    <th>{{ __('Status') }}</th>
                                    @endif
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
<div class="modal fade" id="blog_post_modal">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title post_modal_title">{{__('add_post')}}</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="blogPostForm">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="method" id="method">
                        <input type="hidden" name="id" id="id">
                        <div class="d-none" id="translate_container">
                            <div class="card rounded-0">
                                <h4 class="p-2 lang_title">{{ __('available_translations') }}</h4>
                                @php
                                    $langCode = \App::getLocale();
                                    $language = \Modules\GlobalSetting\app\Models\Language::where('code', $langCode)->first();
                                @endphp
                                <div class="d-flex align-items-center justify-content-between p-2">
                                    <div class="col-md-3">
                                        <select class="form-select" name="language_id" id="language_id">
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
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="image" class="form-label">{{__('image')}}<span class="text-danger"> *</span></label>
                                <div class="d-flex align-items-center flex-wrap row-gap-3 mb-3">
                                    <div class="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames">
                                        <img id="imagePreview" src="{{ asset("front/img/default-placeholder-image.png") }}" alt="Profile picture preview" width="100" height="100">
                                        <i class="ti ti-photo-plus fs-16 upload_icon"></i>
                                    </div>
                                    <div class="profile-upload">
                                        <div class="profile-uploader d-flex align-items-center">
                                            <div class="drag-upload-btn mb-3">
                                                <span id="upload_text">{{ __('upload') }}</span>
                                                <input type="file" class="form-control image-sign" accept="image/*" name="image" id="image">
                                            </div>
                                        </div>
                                        <p id="imageNote">{{ __('image_size_note') }}</p>
                                    </div>
                                </div>
                                <span class="text-danger error-text" id="image_error"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="title" class="form-label">{{__('Title')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control field-input" id="title" name="title" placeholder="{{__('enter_title')}}">
                                <span class="text-danger error-text" id="title_error"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="slug" class="form-label">{{__('Slug')}}<span class="text-danger"> *</span></label>
                                <input type="text" class="form-control field-input" id="slug" name="slug" placeholder="{{__('enter_slug')}}">
                                <span class="text-danger error-text" id="slug_error"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="category" class="form-label">{{__('category')}}<span class="text-danger"> *</span></label>
                                <select name="category" id="category" class="form-select">
                                    <option value="" selected>{{__('select_category')}}</option>
                                </select>
                                <span class="text-danger error-text" id="category_error"></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="description" class="form-label m-0">
                                    {{ __('Description') }}<span class="text-danger"> *</span>
                                </label>
                                @if ($chatStatus)
                                <button type="button" id="openChatModal" class="btn btn-primary d-flex align-items-center gap-1 py-1 px-2">
                                    <img class="chat-image" src="{{ asset('front/img/stat.png') }}" alt="icon">
                                    {{ __('generate_ai_content') }}
                                </button>
                                @endif
                            </div>
                            <textarea class="form-control summernote-editor field-input" rows="10" name="description" id="description"></textarea>
                            <span class="text-danger error-text" id="description_error"></span>
                        </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tags" class="form-label">{{__('tags')}}</label>
                                <input type="text" class="input-tags form-control field-input" id="tags" name="tags" data-role="tagsinput" placeholder="{{__('enter_tag')}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="seo_title" class="form-label">{{__('seo_title')}}</label>
                                <input type="text" class="form-control field-input" id="seo_title" name="seo_title" placeholder="{{__('enter_seo_title')}}">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="seo_description" class="form-label">{{__('seo_description')}}</label>
                                <textarea class="form-control field-input" rows="8" name="seo_description" id="seo_description"></textarea>
                                <span class="text-danger error-text" id="seo_description_error"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="modal-satus-toggle d-flex align-items-center">
                                    <h5 class="me-2" id="popular_text">{{__('popular_text')}}</h5>
                                    <div class="status-toggle modal-status">
                                        <input type="checkbox" id="popular" name="popular" class="check">
                                        <label for="popular" class="checktoggle"> </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="modal-satus-toggle d-flex align-items-center">
                                    <h5 class="me-2" id="status_text">{{__('Status')}}</h5>
                                    <div class="status-toggle modal-status">
                                        <input type="checkbox" id="status" name="status" class="check" checked>
                                        <label for="status" class="checktoggle"> </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-light me-2 cancelbtn" data-bs-dismiss="modal">{{__('Cancel')}}</a>
                    <button type="submit" class="btn btn-primary save_post_btn" data-save="{{ __('Save') }}">{{__('Save')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="delete_blog_post_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteBlogPostForm">
                <div class="modal-body text-center">
                    <span class="delete-icon">
                        <i class="ti ti-trash-x"></i>
                    </span>
                    <h4>{{__('Confirm Deletion')}}</h4>
                    <p>{{__('Are you sure you want to delete this item? This action cannot be undone.')}}</p>
                    <input type="hidden" name="delete_id" id="delete_id">
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-3" data-bs-dismiss="modal">{{__('Cancel')}}</a>
                        <button type="submit" class="btn btn-danger delete_post_confirm">{{__('Yes, Delete')}}</button>
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
                    <i class="ti ti-copy cursor-pointer" id="copyChatResponse" title="Copy Response"></i>
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
                    <input type="text" id="userMessage" name="message" class="form-control" placeholder="Type your message...">
                    <button class="btn btn-primary" id="sendMessage">Send</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
