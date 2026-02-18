@extends('front')
@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h2 class="breadcrumb-title mb-2">{{ __('Tickets') }}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="ti ti-home-2"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Tickets') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="breadcrumb-bg">
            <img src="{{ asset('front/img/bg/breadcrumb-bg-01.png') }}" class="breadcrumb-bg-1" alt="Img">
            <img src="{{ asset('front/img/bg/breadcrumb-bg-02.png') }}" class="breadcrumb-bg-2" alt="Img">
        </div>
    </div>
</div>
<!-- /Breadcrumb -->
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content">
        <div class="container">
            <div class="row justify-content-center">
                @include('user.partials.sidebar')
                <div class="col-xl-9 col-lg-8">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4>{{__('Tickets')}}</h4>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_ticket" class="btn btn-primary"><i class="ti ti-square-rounded-plus me-2"></i>{{ __('add_ticket') }}</a>
                    </div>
                    <div class="row">
                        <!-- Tickets -->
                        <div class="col-xl-12 col-xxl-12">
                            <div id="ticket-list">
                                @if(count($data['ticketdata']) > 0)
                                @foreach($data['ticketdata'] as $val)
                                <!-- Ticket List -->
                                <div class="card">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                                            <div class="d-flex align-items-center flex-wrap">
                                                <span class="avatar avatar-xxl ms-2 me-2 ">
                                                    @if (isset($val->profile_image) && file_exists(public_path('storage/profile/' .$val->profile_image)))
                                                    <img src="{{ asset('storage/profile/' .$val->profile_image) }}" alt="User Profile Image" class="img-fluid rounded-circle profileImagePreview" style="width: 60px; height: 60px;">
                                                    @else
                                                    @if($val->user_type=='User')
                                                    <img src="{{ asset('assets/img/user-default.jpg') }}" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview" style="width: 60px; height: 60px;">
                                                    @else
                                                    <img src="{{ asset('assets/img/profile-default.png') }}" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview" style="width: 60px; height: 60px;">
                                                    @endif
                                                    @endif
                                                </span>
                                                <div class="mt-2">
                                                    <div class="d-flex flex-wrap align-items-center mb-1">
                                                        <h6 class="fw-semibold me-2 mb-0 text-truncate">
                                                            <a href="{{ route('user.ticketdetails', ['ticket_id' => $val->ticket_id]) }}" class="text-decoration-none">
                                                                {{ Str::limit($val->subject ?? "-", 20, '...') }}
                                                            </a>
                                                        </h6>
                                                        <span class="ticket-status ticketstatus{{$val->id}} d-flex align-items-center fs-10 ms-2" data-status="{{$val->status}}">
                                                            <i class="ti ti-circle-filled me-1"></i>{{$val->ticket_status ?? "-"}}
                                                        </span>
                                                    </div>

                                                    <div class="d-flex flex-wrap align-items-center">
                                                        @if($val->assignee_id != '')
                                                        <p class="d-flex align-items-center me-3 mb-1 assigneddetails{{$val->id}}">
                                                            @if(isset($val->assign_profileimage) && file_exists(public_path('storage/profile/' . $val->assign_profileimage)))
                                                            <img src="{{ asset('storage/profile/' . $val->assign_profileimage) }}" class="rounded-circle me-2" width="10" height="10" alt="img">
                                                            @else
                                                            <img src="{{ asset('assets/img/user-default.jpg') }}" class="rounded-circle me-2" width="10" height="10" alt="img">
                                                            @endif
                                                            <span class="text-dark">{{ __('Assigned to') }} <span class="fw-semibold ms-1 assigneename">{{$val->assignee_name ?? "-"}}</span></span>
                                                        </p>
                                                        @endif

                                                        <p class="d-flex align-items-center mb-1 me-2 fs-10">
                                                            <i class="ti ti-calendar-bolt me-1"></i>{{ __('Updated') }} {{ \App\Helpers\TimeHelper::getRelativeTime($val->created_at) }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column align-items-end ">
                                                <div class="d-flex flex-wrap align-items-center fs-10">
                                                    <span class="fw-semibold text-muted me-2">{{ __('Ticket ID') }}:</span>
                                                    <span class="badge bg-info text-light rounded-pill me-3">#{{$val->ticket_id ?? "-"}}</span>

                                                    <span class="fw-semibold text-muted me-2">{{ __('Priority') }}:</span>
                                                    <span class="priority-status d-inline-flex align-items-center me-4" data-status="{{$val->priority}}">
                                                        <i class="ti ti-circle-filled fs-6 me-1"></i>{{$val->priority ?? "-"}}
                                                    </span>
                                                </div>
                                                <a href="{{ route('user.ticketdetails', ['ticket_id' => $val->ticket_id]) }}" class="fs-14 bg-primary px-2 py-1 text-light mt-1 fw-bold d-flex align-items-center me-4 rounded"><i class="ti ti-eye me-1"></i> </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Ticket List -->
                                @endforeach
                                @else
                                <div class="card shadow-none ticket-list h-80">
                                    <div class="card-body d-flex align-items-center justify-content-center">
                                        <p class="fw-bold">{{__('No Tickets Available')}}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        <!-- /Tickets -->
                    </div>

                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center">
            {{ $data['ticketdata']->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
<!-- /Page Wrapper -->

<!-- Add Ticket -->
<div class="modal fade" id="add_ticket">
    <div class="modal-dialog modal-dialog-centered  modal-lg">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                <h4 class="modal-title">{{ __('add_ticket')}}</h4>
                <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <form id="Ticketform">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden" name="id" id="id" class="form-control id">
                            <input type="hidden" name="user_id" class="form-control user_id" value="{{$data['authUserId']}}">
                            <div class="mb-3">
                                <label class="form-label">{{ __('Priority')}}</label><span class="text-danger"> *</span>
                                <select name="priority" id="priority" class="form-control select priority validate-input">
                                    <option value="High">High</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                                <span class="text-danger error-text" id="type_error"></span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Subject')}}</label><span class="text-danger"> *</span>
                                <input type="text" name="subject" id="subject" class="form-control subject validate-input" placeholder="{{ __('enter_subject') }}">
                                <span class="text-danger error-text" id="subject_error"></span>
                            </div>
                            <div class="mb-3">
                                <div class="input-blocks summer-description-box notes-summernote maildiv">
                                    <label class="form-label">{{ __('Descriptions')}}</label><span class="text-danger"> *</span>
                                    <input type="hidden" class="description" id="description" name="description" value="">

                                    <div id="summernote">
                                    </div>
                                    <span class="text-danger error-text" id="description_error"></span>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel')}}</a>
                    <button type="submit" class="btn btn-primary add_ticket_btn">{{ __('Save')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Add Ticket -->
<!-- Assign Ticket -->
<div class="modal fade" id="assign_ticket">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Assign Ticket</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form id="assignticketform">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <input type="hidden" name="ticket_id" class="ticketid">
                                <label class="form-label">Assign To</label>
                                <select class="select" id="user_id">
                                    @if(count($data['userlist']) > 0)
                                    <option value="">Please Select</option>
                                    @foreach($data['userlist'] as $val)
                                    <option value="{{$val->id}}">{{$val->user_name}}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary" id="assignticket">Assign</button>
                        <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</a>
                    </div>
            </form>
        </div>
    </div>
</div>
@endsection