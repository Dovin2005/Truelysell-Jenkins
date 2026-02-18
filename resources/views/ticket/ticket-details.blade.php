@extends('admin.admin')
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
                                        <a href="{{route('admin.dashboard')}}">{{ __('Dashboard') }}</a>
									</li>
									<li class="breadcrumb-item">{{ __('Support') }}</li>
									@if (Auth::user()->user_type == 1) 
									<li class="breadcrumb-item"><a href="{{route('admin.ticket')}}">{{ __('Tickets') }}</a></li>
									@else
									<li class="breadcrumb-item"><a href="{{route('staff.tickets')}}">{{ __('Tickets') }}</a></li>
									@endif
									<li class="breadcrumb-item active" aria-current="page">{{ __('Ticket Details') }}</li>
								</ol>
						  </nav>
					</div>
					<div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
						<div class="mb-2">
							<a href="#" class="btn btn-primary updatestatus" data-status="{{$data['ticketdata']->status}}" data-ticket_id="{{$data['ticketdata']->id}}" data-update_status_text="{{ __('Update Ticket Status') }}">
								<i class="ti ti-edit me-2"></i>{{ __('Update Ticket Status') }}</a>
						</div>
					  </div>
				</div>

				<div class="row">

					<!-- Tickets -->
					<div class="col-xl-8 col-xxl-9">
						@php 
							$ticketStatus = '-';
							$ticketStatusClass = '';

							if ($data['ticketdata']->status == 1) {
								$ticketStatus = __('Open');
								$ticketStatusClass = 'badge badge-primary-transparent ms-2';
							} else if ($data['ticketdata']->status == 2) {
								$ticketStatus = __('Assigned');
								$ticketStatusClass = 'badge badge-soft-warning ms-2';
							} else if ($data['ticketdata']->status == 3) {
								$ticketStatus = __('inProgress');
								$ticketStatusClass = 'badge badge-soft-info ms-2';
							} else if ($data['ticketdata']->status == 4) {
								$ticketStatus = __('Closed');
								$ticketStatusClass = 'badge badge-soft-success ms-2';
							}
						@endphp

						<!-- Ticket List -->
						<div class="card">
							<div class="card-header d-flex align-items-center justify-content-between p-3">
								<h5 class="text-primary">
									@if($data['ticketdata']->user_type=='User')
										{{ __('User') }}
									@elseif($data['ticketdata']->user_type=='Provider')
										{{ __('Provider') }}
									@else
										-
									@endif
								</h5>
								<div class="d-flex align-items-center">
									<span class="ticket-status ticketstatus{{$data['ticketdata']->id}} d-flex align-items-center ms-1 {{ $ticketStatusClass }}" data-status="{{$data['ticketdata']->status}}"><i class="ti ti-circle-filled fs-5 me-1"></i>{{ $ticketStatus ?? '-'}}</span>
								</div>
							</div>
							<div class="card-body p-0">
								<div class="ticket-information ticket-details" data-updated="{{ __('Updated') }}" data-open="{{ __('Open') }}" data-assigned="{{ __('Assigned') }}" data-inprogress="{{ __('inProgress') }}" data-closed="{{ __('Closed') }}">
									<div class="d-flex align-items-center justify-content-between flex-wrap p-3 pb-0 border-bottom">
										<div class="d-flex align-items-center flex-wrap">
											<div class="mb-2">
												<span class="badge bg-pending rounded-pill mb-2">#{{ $data['ticketdata']->ticket_id ?? '-'}}</span>
												<div class="d-flex align-items-center mb-2">
													<h5 class="fw-semibold me-2">{{ $data['ticketdata']->subject ?? '-'}}</h5>
												</div>
												<div class="d-flex align-items-center flex-wrap">
													<p class="d-flex align-items-center mb-1 me-2 updatedTime{{ $data['ticketdata']->id }}"><i class="ti ti-calendar-bolt me-1"></i>{{ __('Updated') }} {{  \App\Helpers\TimeHelper::getRelativeTime($data['ticketdata']->updated_at) }}</p>
												</div>
											</div>
										</div>
										<div class="mb-3">
											<a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_reply"><i class="fa fa-reply me-1"></i>{{ __('post_reply') }}</a>
										</div>
									</div>
									<div class="border-bottom p-3">
										<div class="border-bottom">
										<h5 class="fw-semibold me-2 mb-2">{{ __('Description') }}</h5>
											<p class="mb-3">{!! $data['ticketdata']->description ?? '-' !!}</p>

										</div>
											<div id="comments-section">
											@if(count($data['tickethistory'])>0)
											@foreach($data['tickethistory'] as $hval)
												<div class="comment-item mt-3">
													<div class="d-flex align-items-center mb-1">
														<span class="avatar avatar-l me-2 flex-shrink-0">
														@if (isset($hval->profile_image) && file_exists(public_path('storage/profile/' .$hval->profile_image)))
															<img src="{{ asset('storage/profile/' .$hval->profile_image) }}" alt="User Profile Image" class="img-fluid rounded-circle profileImagePreview">
														@else

															<img src="{{ asset('assets/img/profile-default.png') }}" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview">
														@endif
														</span>
														<div>
															<h6 class="mb-1">{{$hval['username']}}</h6>
															<p><i class="ti ti-calendar-bolt me-1"></i>{{ __('Updated') }} {{  \App\Helpers\TimeHelper::getRelativeTime($hval['created_at']) }}</p>
														</div>
													</div>
													{{-- <div> --}}
													<div class="border-bottom p-2">
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
													{{-- </div> --}}
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
								<h4>{{ __('Ticket Details') }}</h4>
							</div>
							<div class="card-body p-0">
								<div class="border-bottom p-3">
									<div class="mb-3">
										<label class="form-label">{{ __('Priority') }}</label>
										<select class="select" disabled>
											<option value="High" {{ $data['ticketdata']->priority == 'High' ? 'selected' : '' }}>{{ __('High') }}</option>
											<option value="Medium" {{ $data['ticketdata']->priority == 'Medium' ? 'selected' : '' }}>{{ __('Medium') }}</option>
											<option value="Low" {{ $data['ticketdata']->priority == 'Low' ? 'selected' : '' }}>{{ __('Low') }}</option>
										</select>
									</div>
									<div class="mb-3">
										<label class="form-label">{{ __('Assign To') }}</label>
										<select class="select assign_id" id="assign_id" >
                                        @if(count($data['userlist']) > 0)
                                                <option value="">{{ __('Select') }}</option>
                                                    @foreach($data['userlist'] as $val)
                                                    <option value="{{$val->id}}"  @if($val->id == $data['ticketdata']->assignee_id) selected @endif>{{$val->user_name}}</option>
                                                    @endforeach
                                                @endif
										</select>
									</div>
									<div class="mb-0">
										<label class="form-label">{{ __('Ticket Status') }}</label>
										<select name="status" id="status" class="form-control select2 status validate-input">
                                            <option value="1" @if($data['ticketdata']->status == 1) selected @endif>{{ __('Open') }}</option>
                                            <option value="2" @if($data['ticketdata']->status == 2) selected @endif>{{ __('Assigned') }}</option>
                                            <option value="3" @if($data['ticketdata']->status == 3) selected @endif>{{ __('inProgress') }}</option>
                                            <option value="4" @if($data['ticketdata']->status == 4) selected @endif>{{ __('Closed') }}</option>
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
									<span class="fs-12">{{ __('User Email') }}</span>
									<p class="text-dark">{{ $data['ticketdata']->email ?? '-'}}</p>
								</div>
								<div class="p-3 {{ $data['ticketdata']->status=='4' ? '' : 'd-none' }}" id="ticketCloseDate">
									<span class="fs-12">{{ __('Last Updated') }} / {{ __('Closed On') }}</span>
									<p class="text-dark">{{ formatDateTime($data['ticketdata']->updated_at, true) ?? '-'}}</p>
								</div>
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
					<div class="modal-header">
						<h4 class="modal-title">{{ __('Reply') }}</h4>
						<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
							<i class="ti ti-x"></i>
						</button>
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
							<a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
							<button type="submit" class="btn btn-primary postreply" id="postreply">{{ __('Post') }}</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- /Add Ticket -->

        @endsection
