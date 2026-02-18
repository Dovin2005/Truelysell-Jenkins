@extends('provider.provider')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
	<div class="content pb-lg-4 pb-2">
		<div class="d-md-flex d-block align-items-center justify-content-between mb-3">
			<div class="my-auto mb-2">
				<h3 class="page-title mb-1">{{ __('Tickets') }}</h3>
				<nav>
					<ol class="breadcrumb mb-0">
						<li class="breadcrumb-item">
							<a href="{{ Auth::user()->user_type == 2 ? route('provider.dashboard') : route('staff.dashboard')}}">{{ __('Dashboard') }}</a>
						</li>
						<li class="breadcrumb-item"><a href="{{ Auth::user()->user_type == 2 ? route('provider.ticket') : route('staff.ticket') }}">{{ __('Tickets') }}</a></li>
						<li class="breadcrumb-item active" aria-current="page">{{ __('Ticket Details') }}</li>
					</ol>
				</nav>
			</div>

		</div>

		<div class="row">

			<!-- Tickets -->
			<div class="col-xl-8 col-xxl-9">

				<!-- Ticket List -->
				<div class="card fs-14">
					<div class="card-header d-flex align-items-center justify-content-between p-3">
						<div class="d-flex align-items-center">
							<span class="fw-semibold text-muted me-2">Status :</span>
							<span class="ticket-status ticketstatus{{$data['ticketdata']->id}}  d-flex align-items-center ms-1" data-status="{{$data['ticketdata']->status}}"><i class="ti ti-circle-filled fs-5 me-1"></i>{{ $data['ticketdata']->ticket_status ?? '-'}}</span>
						</div>
					</div>
					<div>
						<div class="ticket-information ticket-details">
							<div class="d-flex align-items-center justify-content-between border-bottom p-3">
								<div class="d-flex align-items-center">
									<div class="d-flex align-items-start flex-column">
										<div>
											<span class="fw-semibold text-muted me-2">Ticket ID :</span>
											<span class="badge bg-info text-light rounded-pill">#{{ $data['ticketdata']->ticket_id ?? '-'}}</span>
										</div>
										<div class="d-flex align-items-center mt-2">
											<h6 class="fw-semibold">{{ $data['ticketdata']->subject ?? '-'}}</h6>
										</div>
										<div class="d-flex align-items-center fs-10">
											<p><i class="ti ti-calendar-bolt"></i>Updated {{ \App\Helpers\TimeHelper::getRelativeTime($data['ticketdata']->updated_at) }}</p>
										</div>
									</div>
								</div>
								@if ($data['ticketdata']->status != 4)
								<div class="">
									<a href="#" class="btn btn-sm btn-primary fs-12" data-bs-toggle="modal" data-bs-target="#add_reply"><i class="ti ti-reload me-1"></i>Post a Reply</a>
								</div>
								@endif
							</div>

							<div class="border-bottom">
								<div class="border-bottom p-3">
									<h6 class="fw-semibold fs-16 me-2 mb-1">Description</h6>
									<p class="fs-14">{!! $data['ticketdata']->description ?? '-' !!} </p>
								</div>
								<div id="comments-section" class="p-3">
									@if(count($data['tickethistory'])>0)
									@foreach($data['tickethistory'] as $hval)
									<div class="comment-item pt-3">
										<div class="d-flex align-items-center">
											<span class="avatar avatar-l me-2 flex-shrink-0">
												@if (isset($hval->profile_image) && file_exists(public_path('storage/profile/' .$hval->profile_image)))
												<img src="{{ asset('storage/profile/' .$hval->profile_image) }}" alt="User Profile Image" class="img-fluid rounded-circle profileImagePreview">
												@else

												<img src="{{ asset('assets/img/user-default.jpg') }}" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview">
												@endif
											</span>
											<div>
												<h6 class="mb-1">{{$hval['username']}}</h6>
												<p><i class="ti ti-calendar-bolt me-1"></i>Updated {{ \App\Helpers\TimeHelper::getRelativeTime($hval['created_at']) }}</p>
											</div>
										</div>
										<div>
											<div class="border-bottom p-3">
												@php
												$content = $hval['description']; // Summernote content
												$dom = new DOMDocument();
												@$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));

												$imageLinks = []; // Array to store download links

												// Extract image tags and prepare download links
												foreach ($dom->getElementsByTagName('img') as $img) {
												$src = $img->getAttribute('src');
												$dataFilename = $img->getAttribute('data-filename'); // Get the data-filename attribute
												$imageName = $dataFilename ?: basename($src); // Use data-filename if available, else fallback to src basename

												// Prepare the download link HTML
												$imageLinks[] = "
												<div>
													<a href='{$src}' download='{$imageName}' class='d-flex align-items-center'>
														<i class='ti ti-download me-2'></i> {$imageName}
													</a>
												</div>
												";

												// Optionally, remove the image tag from content
												$img->parentNode->removeChild($img);
												}

												// Get the remaining content without images
												$modifiedContent = $dom->saveHTML();
												@endphp

												{{-- Display the modified content --}}
												<div>{!! $modifiedContent !!}</div>

												{{-- Display the download links --}}
												@foreach ($imageLinks as $link)
												{!! $link !!}
												@endforeach
											</div>
										</div>
									</div>
									@endforeach
									@endif
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /Ticket List -->
			</div>
			<!-- /Tickets -->

			<!-- Ticket Details -->
			<div class="col-xl-4 col-xxl-3">
				<div class="card">
					<div class="card-header p-3">
						<h4>Ticket Details</h4>
					</div>
					<div class="card-body p-0">
						<div class="border-bottom p-3">
							<div class="mb-3">
								<label class="form-label">Priority</label>
								<select class="form-control select2" disabled>
									<option value="High" {{ $data['ticketdata']->priority == 'High' ? 'selected' : '' }}>High</option>
									<option value="Medium" {{ $data['ticketdata']->priority == 'Medium' ? 'selected' : '' }}>Medium</option>
									<option value="Low" {{ $data['ticketdata']->priority == 'Low' ? 'selected' : '' }}>Low</option>
								</select>
							</div>
							@if (!empty($data['ticketdata']->assignee_id))
							<div class="mb-3">
								<label class="form-label">Assign To</label>
								<select class="form-control select2 assign_id" id="assign_id" disabled>
									@if(count($data['userlist']) > 0)
									<option value="">Please Select</option>
									@foreach($data['userlist'] as $val)
									<option value="{{$val->id}}" @if($val->id == $data['ticketdata']->assignee_id) selected @endif>{{$val->user_name}}</option>
									@endforeach
									@endif
								</select>
							</div>
							@endif
							<div class="mb-0">
								<label class="form-label">Ticket Status</label>
								<select name="status" id="status" class="form-control select2 status validate-input" disabled>
									<option value="1" @if($data['ticketdata']->status == 1) selected @endif>Open</option>
									<option value="2" @if($data['ticketdata']->status == 2) selected @endif>Assigned</option>
									<option value="3" @if($data['ticketdata']->status == 3) selected @endif>InProgress</option>
									<option value="4" @if($data['ticketdata']->status == 4) selected @endif>Closed</option>
								</select>
							</div>
						</div>
						<div class="d-flex align-items-center border-bottom p-3">
							<span class="avatar avatar-md me-2 flex-shrink-0">
								@if (isset($data['ticketdata']->profile_image) && file_exists(public_path('storage/profile/' .$data['ticketdata']->profile_image)))
								<img src="{{ asset('storage/profile/' .$data['ticketdata']->profile_image) }}" alt="User Profile Image" class="img-fluid rounded-circle profileImagePreview">
								@else
								@if($data['ticketdata']->user_type=='User')
								<img src="{{ asset('assets/img/user-default.jpg') }}" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview">
								@else
								<img src="{{ asset('assets/img/profile-default.png') }}" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview">
								@endif
								@endif
							</span>
							<div>
								<span class="fs-12">{{ $data['ticketdata']->user_type ?? 'User'}}</span>
								<p class="text-dark">{{ $data['ticketdata']->username ?? '-'}}</p>
							</div>
						</div>
						<div class="border-bottom p-3">
							<span class="fs-12">User Email</span>
							<p class="text-dark">{{ $data['ticketdata']->email ?? '-'}}</p>
						</div>
						@if($data['ticketdata']->status=='4')
						<div class="p-3">
							<span class="fs-12">Last Updated / Closed On</span>
							<p class="text-dark">{{ formatDateTime($data['ticketdata']->updated_at, true) ?? '-'}}</p>
						</div>
						@endif
					</div>
				</div>
			</div>
			<!-- /Ticket Details -->

		</div>

	</div>
</div>
<!-- /Page Wrapper -->

<!-- Add Ticket -->
<div class="modal fade" id="add_reply">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header d-flex align-items-center justify-content-between border-bottom">
				<h4 class="modal-title">Reply</h4>
				<a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
			</div>
			<form id="replyform">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div class="mb-3">
								<div class="input-blocks summer-description-box notes-summernote">
									<label class="form-label">{{ __('Descriptions')}}</label><span class="text-danger"> *</span>
									<input type="hidden" class="description" id="description" name="description" value="">
									<input type="hidden" class="user_id" id="user_id" name="user_id" value="{{$data['authUserId']}}">
									<input type="hidden" class="ticket_id" id="ticket_id" name="ticket_id" value="{{$data['ticketdata']->id}}">
									<div id="summernote">
									</div>
									<span class="text-danger error-text" id="description_error"></span>

								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</a>
					<button type="submit" class="btn btn-primary postreply" id="postreply">Post</button>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- /Add Ticket -->

@endsection