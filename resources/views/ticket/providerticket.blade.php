@extends('provider.provider')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content pb-lg-4 pb-2">

        <div class="row">
            <!-- Page Header -->
            <div class="col-md-12">
                <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
                    <div class="my-auto mb-2">
                        <h3 class="page-title mb-1">{{ __('Tickets') }}</h3>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ Auth::user()->user_type == 2 ? route('provider.dashboard') : route('staff.dashboard')}}">{{ __('Dashboard') }}</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">{{ __('Tickets') }}</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_ticket" class="btn btn-primary"><i class="ti ti-square-rounded-plus me-2"></i>Add New Ticket</a>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
        </div>


        <div class="row">
            <!-- Tickets -->
            <div class="col-xl-12 col-xxl-12">
                <div id="ticket-list" data-user_type="{{ Auth::user()->user_type == 2 ? Auth::user()->user_type : '4' }}">
                    @if(count($data['ticketdata']) > 0)
                    @foreach($data['ticketdata'] as $val)
                    <!-- Ticket List -->
                    <div class="card">
                        <div class="card-body p-0">

                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-xxl me-2">
                                        @if (isset($val->profile_image) && file_exists(public_path('storage/profile/' .$val->profile_image)))
                                        <img src="{{ asset('storage/profile/' .$val->profile_image) }}" alt="User Profile Image" class="img-fluid rounded-circle profileImagePreview" style="width: 60px; height: 60px;">
                                        @else
                                        <img src="{{ asset($val->user_type == 'User' ? 'assets/img/user-default.jpg' : 'assets/img/profile-default.png') }}" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview" style="width: 60px; height: 60px;">
                                        @endif
                                    </span>

                                    <div class="mb-2">

                                        <div class="d-flex flex-wrap align-items-center mt-3">
                                            <h6 class="fw-semibold me-2 mb-0">
                                                <a onclick="storeTicketId({{ $val->id }})" class="text-decoration-none text-dark">
                                                    {{ Str::limit($val->subject ?? "-", 20, '...') }}
                                                </a>
                                            </h6>
                                            <span class="ticket-status ticketstatus{{$val->id}} d-flex align-items-center ms-2 fs-10" data-status="{{$val->status}}">
                                                <i class="ti ti-circle-filled fs-6 me-1"></i>{{$val->ticket_status ?? "-"}}
                                            </span>
                                        </div>

                                        <div class="d-flex flex-wrap align-items-center mt-1">
                                            @if(!empty($val->assignee_id))
                                            <p class="d-flex align-items-center me-3 mb-1 assigneddetails{{$val->id}}">
                                                <span class="fw-semibold text-muted me-2">Assigned to:</span>
                                                <img src="{{ asset(isset($val->assign_profileimage) && file_exists(public_path('storage/profile/' . $val->assign_profileimage)) ? 'storage/profile/' . $val->assign_profileimage : 'assets/img/user-default.jpg') }}" class="rounded-circle me-2" width="24" height="24" alt="img">
                                                <span class="text-dark fw-semibold ms-1 assigneename">{{$val->assignee_name ?? "-"}}</span>
                                            </p>
                                            @endif

                                            <p class="d-flex align-items-center mb-1 me-2 fs-10">
                                                <i class="ti ti-calendar-bolt me-1"></i>
                                                <span class="fw-semibold text-muted me-1">Updated:</span> {{ \App\Helpers\TimeHelper::getRelativeTime($val->created_at) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-column align-items-end">
                                    <div class="d-flex flex-wrap align-items-center fs-10">
                                        <span class="fw-semibold text-muted me-2">Ticket ID:</span>
                                        <span class="badge bg-info text-light rounded-pill me-3">#{{$val->ticket_id ?? "-"}}</span>

                                        <span class="fw-semibold text-muted me-2">Priority:</span>
                                        <span class="priority-status d-inline-flex align-items-center me-4" data-status="{{$val->priority}}">
                                            <i class="ti ti-circle-filled fs-6 me-1"></i>{{$val->priority ?? "-"}}
                                        </span>
                                    </div>
                                    <div class="mb-3">
                                        <a href="{{ route(Auth::user()->user_type == 2 ? 'provider.ticketdetails' : 'staff.ticket_details', ['ticket_id' => $val->ticket_id]) }}" class="fs-14 bg-primary px-2 py-1 text-light mt-1 fw-bold text-dark d-flex align-items-center me-4 rounded">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </div>
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
                                <select name="priority" id="priority" class="form-control select2 priority validate-input">
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