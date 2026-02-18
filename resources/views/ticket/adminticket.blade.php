@extends('admin.admin')
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
                                        <a href="{{route('admin.dashboard')}}">{{ __('Dashboard') }}</a>
                                    </li>
                                    <li class="breadcrumb-item">{{ __('Support') }}</li>
                                    <li class="breadcrumb-item active" aria-current="page">{{ __('Tickets') }}</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <!-- /Page Header -->
            </div>

            <div class="row">
                <!-- Tickets -->
                <div class="col-xl-12 col-xxl-12" id="ticketList" data-assignto="{{ __('Assigned to') }}" data-assigned="{{ __('Assigned') }}" data-assign="{{ __('Assign') }}" data-updated="{{ __('Updated') }}">
                @if(count($data['ticketdata']) > 0)
                    @foreach($data['ticketdata'] as $val)

                        @php
                            $ticketStatus = '-';
                            $priority = '';
                            $priorityStatusClass = '';
                            $ticketStatusClass = '';

                            if ($val->status == 1) {
                                $ticketStatus = __('Open');
                                $ticketStatusClass = 'badge badge-primary-transparent ms-2';
                            } else if ($val->status == 2) {
                                $ticketStatus = __('Assigned');
                                $ticketStatusClass = 'badge badge-soft-warning ms-2';
                            } else if ($val->status == 3) {
                                $ticketStatus = __('inProgress');
                                $ticketStatusClass = 'badge badge-soft-info ms-2';
                            } else if ($val->status == 4) {
                                $ticketStatus = __('Closed');
                                $ticketStatusClass = 'badge badge-soft-success ms-2';
                            }

                            if ($val->priority == 'High') {
                                $priority = __('High');
                                $priorityStatusClass = 'badge badge-danger';
                            } else if ($val->priority == 'Low') {
                                $priority = __('Low');
                                $priorityStatusClass = 'badge badge-warning';
                            } else if ($val->priority == 'Medium') {
                                $priority = __('Medium');
                                $priorityStatusClass = 'badge badge-orange';
                            }
                        @endphp

                        <!-- Ticket List -->
                        <div class="card">
                            <div class="card-body p-3 pb-0">
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-success align-items-center me-4">
                                        @if($val->user_type=='User')
                                            {{ __('User') }}
                                        @elseif($val->user_type=='Provider')
                                            {{ __('Provider') }}
                                        @else
                                            -
                                        @endif
                                    </span>

                                    <span class="fw-semibold text-muted me-2">{{ __('Ticket ID') }}:</span>
                                    <span class="badge badge-soft-info me-3 rounded-pill">#{{$val->ticket_id ?? "-"}}</span>

                                    <span class="fw-semibold text-muted me-2">{{ __('Priority') }}:</span>
                                    <span class="priority-status d-inline-flex align-items-center me-4 {{ $priorityStatusClass }}" data-status="{{$val->priority}}"><i class="ti ti-circle-filled fs-5 me-1"></i>{{$priority ?? "-"}} </span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div id="assignBtn{{$val->id}}">
                                        @if($val->assignee_id=='')
                                            <a href="" class="btn btn-primary btn-sm assignid" data-bs-toggle="modal" data-bs-target="#assign_ticket"  data-id="{{$val->id}}"><i class="ti ti-user me-1"></i>{{ __('Assign') }}</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                                <div class="d-flex align-items-center justify-content-between flex-wrap">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="avatar avatar-xl me-2">
                                            @if (isset($val->profile_image) && file_exists(public_path('storage/profile/' .$val->profile_image)))
                                                <img src="{{ asset('storage/profile/' .$val->profile_image) }}" alt="User Profile Image" class="img-fluid rounded-circle profileImagePreview">
                                            @else
                                               @if($val->user_type=='User')
                                                    <img src="{{ asset('assets/img/profile-default.png') }}" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview">
                                                @else
                                                     <img src="{{ asset('assets/img/profile-default.png') }}" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview">
                                                @endif
                                            @endif
                                        </span>
                                        <div class="mb-3">
                                            <div class="d-flex align-items-center mb-2 mt-3">
                                                @if (Auth::user()->user_type == 1)
                                                <h5 class="fw-semibold me-2"> <a href="{{ route('admin.ticketdetails', ['ticket_id' => $val->ticket_id]) }}">{{ Str::limit($val->subject ?? "-", 50, '...') }}</a></h5>
                                                @else
                                                <h5 class="fw-semibold me-2"> <a href="{{ route('staff.ticketdetails', ['ticket_id' => $val->ticket_id]) }}">{{Str::limit($val->subject ?? "-", 50, '...')}}</a></h5>
                                                @endif
                                                <span class="ticket-status ticketstatus{{$val->id}} d-flex align-items-center ms-1 {{ $ticketStatusClass }}" data-status="{{$val->status}}"><i class="ti ti-circle-filled fs-5 me-1"></i>{{$ticketStatus ?? "-"}}</span>
                                            </div>
                                            <div class="d-flex align-items-center flex-wrap">
                                                <p class="d-flex align-items-center me-2 mb-1 assigneddetails{{$val->id}}">
                                                  @if($val->assignee_id!='')
                                                    @if (isset($val->assign_profileimage) && file_exists(public_path('storage/profile/' .$val->assign_profileimage)))
                                                        <img src="{{ asset('storage/profile/' . $val->assign_profileimage) }}" class="avatar avatar-xs rounded-circle me-2" alt="img">
                                                    @else
                                                        <img src="{{ asset('assets/img/user-default.jpg') }}" class="avatar avatar-xs rounded-circle me-2" alt="img">
                                                    @endif
                                                    {{ __('Assigned to') }}<span class="text-dark ms-1 assigneename">{{$val->assignee_name ?? "-"}}</span>
                                                   @endif
                                                </p>
                                                <p class="d-flex align-items-center mb-1 me-2 updatedTime{{ $val->id }}"><i class="ti ti-calendar-bolt me-1"></i>{{ __('Updated') }} {{  \App\Helpers\TimeHelper::getRelativeTime($val->updated_at) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        @if (Auth::user()->user_type == 1)
                                        <a href="{{ route('admin.ticketdetails', ['ticket_id' => $val->ticket_id]) }}" class="fs-4 fw-bold text-dark d-flex align-items-center"><i class="ti ti-eye me-1"></i> </a>
                                        @else
                                        <a href="{{ route('staff.ticketdetails', ['ticket_id' => $val->ticket_id]) }}" class="fs-4 fw-bold text-dark d-flex align-items-center"><i class="ti ti-eye me-1"></i> </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Ticket List -->
                    @endforeach
				@else
                    <div class="card shadow-none booking-list h-80">
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <p class="fw-bold">{{__('No Tickets Available')}}</p>
                        </div>
                    </div>
				@endif

                </div>
                <!-- /Tickets -->
            </div>
            <div class="d-flex justify-content-center">
                {{ $data['ticketdata']->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
    <!-- /Page Wrapper -->

    <!-- Assign Ticket -->
    <div class="modal fade" id="assign_ticket">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('Assign Ticket') }}</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form id="assignticketform">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <input type="hidden" name="ticket_id" class="ticketid" value="">
                                    <input type="hidden" name="auth_id" class="auth_id" value="{{$data['authUserId']}}">
                                    <label class="form-label">{{ __('Assign To') }}</label>
                                    <select class="select" id="user_id">
                                        @if(count($data['userlist']) > 0)
                                        <option value="">{{ __('Select') }}</option>
                                            @foreach($data['userlist'] as $val)
                                            <option value="{{$val->id}}">{{$val->user_name}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                       </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary" id="assignticket">{{ __('Assign') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
