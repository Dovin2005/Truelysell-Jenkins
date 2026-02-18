@extends('admin.admin')
@section('content')

<div class="page-wrapper">
    <div class="container">
        <div class="row">
            <div class="col-md-4 d-flex">
                <div class="card chat-user-1 flex-fill">
                    <div class="card-header">
                       <div class="mb-3">
                            <h6>{{ __('All Chats')}}</h6>
                        </div>
                        <div class="position-relative mb-0">
                            <input type="text" name="chatSearch" id="chatSearch" placeholder="{{ __('search_for_users') }}" class="form-control">
                            <div class="search-icon-right">
                                <span class="search_btn"><i class="ti ti-search"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="sidebar-body chat-body card-body" id="chatsidebar" data-userid="{{ $chatUserId ?? '' }}" data-current_page="{{ $current_page ?? 1 }}" data-last_page="{{ $last_page ?? 1 }}" data-empty_info="{{ __('no_users_found') }}">
                        <ul class="user-list">
                            @if(!empty($users) && count($users) > 0)
                                @foreach($users as $user)
                                    <li class="user-list-item">
                                        <a href="javascript:void(0);" class="p-2 border rounded d-block mb-2 userprofile" data-userid="{{ $user->id }}" data-username="{{ $user->name }}" data-avatar="{{ $user->profile_image }}">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-lg avatar-online me-2 flex-shrink-0">
                                                    <img src="{{ $user->profile_image }}" alt="Profile Image" class="img-fluid rounded-circle">
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden me-2">
                                                    <h6 class="mb-1 text-truncate">{{ $user->name }}</h6>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-8 d-flex">
                <div class="chat chat-messages card flex-fill" id="middle">
                    <div class="chat-header card-header">
                        <div class="user-details d-flex align-items-center">
                            <div class="d-lg-none">
                                <ul class="list-inline mt-2 me-2">
                                    <li class="list-inline-item">
                                        <a class="text-muted px-0 left_sides" href="#" data-chat="open">
                                            <i class="fas fa-arrow-left"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="avatar avatar-lg me-2" id="chatimg">
                                <img src="{{ asset('assets/img/profile-default.png') }}" alt="Profile Image" id="chat_avatar" class="img-fluid rounded-circle" data-userid="">
                            </div>
                            <div>
                                <h6 class="chat-user-name">
                                    <span class="visually-hidden">{{ __('user') }}</span>
                                </h6>
                            </div>
                        </div>
                    </div>
                    <div class="chat-body card-body chat-scroll chats_scroll" id="chatscroll">
                        <div class="messages">
                            <div class="chats">
                                <div id="messageArea" class="chat-content Chat window chat-cont-type">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ms-3 d-flex admin-select">
                        <span class="selected_file border-0 rounded-pill text-light px-2 py-1 me-2 bg-dark d-none"></span>
                    </div>
                    <div class="chat-footer">
                        <form id="addmsgform">
                            <div class="chat-message">
                                <div class="form-item input-group position-relative overflow-hidden rounded">
                                    <button type="button" class="btn-file btn" id="openFile">
                                        <i class="fa fa-paperclip"></i>
                                    </button>
                                    <input type="file" id="fileupload" class="d-none">
                                    <input type="text" class="form-control" id="messageinput" placeholder="{{ __('type_your_message_here') }}">
                                    <button class="btn btn-primary" type="button" id="sendmsg" data-senderid="{{ $sender->id }}">
                                        <i class="ti ti-send"></i>
                                    </button>
                                </div>  
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/mqtt.min.js') }}"></script>
<script src="{{ asset('assets/js/chat.js') }}"></script>
@endpush
