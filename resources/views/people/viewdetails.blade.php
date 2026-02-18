@extends('admin.admin')
@section('content')
	<div class="page-wrapper">
		<div class="content">
			<div class="row">
				<div class="col-md-12">
					<div class="d-md-flex d-block align-items-center justify-content-between mb-3">
						<div class="my-auto mb-2">
							<h3 class="page-title mb-1">   {{ $data['userlist'][0]->user_type == 2 ? __('Provider') : __('user') }} {{ __('Details')}}</h3>
							<nav>
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item">
										<a href="{{route('admin.dashboard')}}">{{ __('Dashboard')}}</a>
									</li>
									<li class="breadcrumb-item">
										@if($data['userlist'][0]->user_type == 2)	
											<a href="{{route('admin.providerslist')}}">   {{ __('Provider') }}</a>
										@else
											<a href="{{route('admin.userlist')}}">   {{ __('user') }}</a>
										@endif
										</li>
									<li class="breadcrumb-item active" aria-current="page">   {{ $data['userlist'][0]->user_type == 2 ? __('Provider') : __('user') }} {{ __('Details')}}</li>
								</ol>
							</nav>
						</div>
						<div class="d-flex my-xl-auto right-content align-items-center  flex-wrap">	
							@if($data['userlist'][0]->user_type == 2)	
								<a href="{{route('admin.providerslist')}}" class="custom-btn-close d-flex align-items-center justify-content-center"  aria-label="Close">
								<i class="ti ti-x"></i></a>	
							@else
							<a href="{{route('admin.userlist')}}" class=" custom-btn-close d-flex align-items-center justify-content-center"  aria-label="Close">
							<i class="ti ti-x"></i></a>	
							@endif
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-xxl-3 col-xl-4 ">
					<div class="card border-white">
						<div class="card-header">
							<div class="d-flex align-items-center flex-wrap row-gap-3">
								<div class="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames">
								@if(!empty($data['userlist'][0]['userDetails']) && !empty($data['userlist'][0]['userDetails']->profile_image) && file_exists(public_path('storage/profile/' . $data['userlist'][0]['userDetails']->profile_image)))
								<img src="{{ asset('storage/profile/' . $data['userlist'][0]['userDetails']->profile_image) }}" alt="User Profile Image" class="img-fluid rounded-circle profileImagePreview">
									@else
										<img src="{{ asset('assets/img/profile-default.png') }}" alt="Default Profile Image" class="img-fluid rounded-circle profileImagePreview">
									@endif
								</div>                                              
								<div class="overflow-hidden">
									@if($data['userlist'][0]->status==1)
									    <span class="badge badge-soft-success d-inline-flex align-items-center mb-1"><i class="ti ti-circle-filled fs-5 me-1"></i>{{ __('Active') }}</span>
									@else
									    <span class="badge badge-soft-danger d-inline-flex align-items-center mb-1"><i class="ti ti-circle-filled fs-5 me-1"></i>{{ __('Inactive') }}</span>
                                    @endif
									@if ($data['userlist'][0]->user_type == 2)
										@if($data['userlist'][0]->provider_verified_status == 1)
											<span class="badge bg-success ms-2">{{ __('verified') }}</span>
										@else
											<button type="button" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#verifyProviderModal">
												{{ __('verify') }}
											</button>
										@endif
									@endif
									<h5 class="mb-1">
										{{$data['userlist'][0]['userDetails']->first_name ? $data['userlist'][0]['userDetails']->first_name . ' ' . $data['userlist'][0]['userDetails']->last_name : '  -'}}
									</h5>
									<p class="text-primary">{{$data['userlist'][0]->email ?? '  -'}}</p>
								</div>
							</div>
						</div>

						<div class="card-body">
							<h5 class="mb-3">{{ __('Basic Information') }}</h5>
							<dl class="row mb-0"> 
								<dt class="col-6 fw-medium text-dark mb-3">{{ __('name')}}</dt> 
								<dd class="col-6 mb-3">{{$data['userlist'][0]['userDetails']->first_name ? $data['userlist'][0]['userDetails']->first_name . ' ' . $data['userlist'][0]['userDetails']->last_name : '  -'}}</dd> 
								<dt class="col-6 fw-medium text-dark mb-3">{{ __('gender')}}</dt> 
								<dd class="col-6 mb-3">{{$data['userlist'][0]['userDetails']->gender ?? '  -'}}</dd> 
								<dt class="col-6 fw-medium text-dark mb-3">{{ __('date_of_birth')}}</dt> 
								<dd class="col-6 mb-3">@if(!empty($data['userlist'][0]['userDetails']->dob))
									{{ \Carbon\Carbon::parse($data['userlist'][0]['userDetails']->dob)->format($data['DateFormat'] ?? 'd-m-Y') }}
								@else
									-
								@endif</dd> 
							</dl>
						</div>
					</div>
					
					<div class="card border-white">
						<div class="card-body">
							<h5 class="mb-3">{{ __('Primary Contact Info')}}</h5>
							<div class="d-flex align-items-center mb-3">
								<span class="avatar avatar-md bg-light-300 rounded me-2 flex-shrink-0 text-default"><i class="ti ti-phone"></i></span>
								<div>
									<span class="text-dark fw-medium mb-1">{{ __('phone_number')}}</span>
									<p>{{$data['userlist'][0]->phone_number ?? '   -'}}</p>
								</div>
							</div>
							<div class="d-flex align-items-center">
								<span class="avatar avatar-md bg-light-300 rounded me-2 flex-shrink-0 text-default"><i class="ti ti-mail"></i></span>
								<div>
									<span class="text-dark fw-medium mb-1">{{ __('Email Address')}}</span>
									<p>{{$data['userlist'][0]->email ?? '  -'}}</p>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="col-xxl-9 col-xl-8">
					<div class="row">
						<div class="col-md-12">

							<ul class="nav nav-tabs nav-tabs-bottom mb-4">
								<li>
									<a href="#" class="nav-link active"><i class="ti ti-user me-2"></i>   {{ $data['userlist'][0]->user_type == 2 ? __('Provider') : __('user') }} {{ __('Details')}}</a>
								</li>
							</ul>

							@if($data['userlist'][0]->user_type == 2)	
							<div class="card">
								<div class="card-header">
									<h5>{{ __('Category Information')}}</h5>
								</div>
								<div class="card-body">
									<div class="border rounded p-3 pb-0 mb-3">									
										<div class="row">									
											<div class="col-sm-6 col-lg-4">
												<div class="d-flex align-items-center mb-3">
													<span class="avatar avatar-lg flex-shrink-0">
													@if (!empty($data['userlist'][0]['userDetails']['category']->image) && file_exists(public_path('storage/' . $data['userlist'][0]['userDetails']['category']->image)))
														<img src="{{ asset('storage/' . $data['userlist'][0]['userDetails']['category']->image) }}" alt="category image" class="img-fluid rounded-circle profileImagePreview">
													@else
														<img src="{{ asset('front/img/default-placeholder-image.png') }}" alt="category image" class="img-fluid rounded-circle profileImagePreview">
													@endif   
													</span>
													<div class="ms-2 overflow-hidden">
														<h6 class="text-truncate">{{$data['userlist'][0]['userDetails']['category']->name ?? '-'}}</h6>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							@endif
						</div>
						<div class="col-xxl-12 d-flex">
							<div class="card flex-fill">
								<div class="card-header">
									<h5>{{ __('address')}}</h5>
								</div>
								<div class="card-body">
									<div class="d-flex align-items-center mb-3">
										<span class="avatar avatar-md bg-light-300 rounded me-2 flex-shrink-0 text-default"><i class="ti ti-map-pin-up"></i></span>
										<div>
											<p class="text-dark fw-medium mb-1">{{ __('address')}}</p>
											<p>{{$data['userlist'][0]['userDetails']->address ?? '  -'}} </p>
											<p>{{  App\Models\UserDetail::select('city','state','country')->where('user_id','=',$data['userlist'][0]['userDetails']->user_id)->first()->showfulladdress() }}</p>
											</div>
									</div>
								</div>
							</div>								
						</div>

						<div class="col-xxl-12 d-flex">
							<div class="card flex-fill">
								<div class="card-header">
									<h5>{{ __('id_or_documents')}}</h5>
								</div>
								<div class="card-body">
									<div class="d-flex justify-content-start document-preview-container">
										@if ($data['userlist'][0]->documents && count($data['userlist'][0]->documents) > 0)
											@foreach ($data['userlist'][0]->documents as $document)
												<div class="document-preview me-2">
													<a href="{{ $document->document_url }}" target="_blank" class="btn btn-sm btn-light me-0">
														<i class="ti ti-file-text fs-40"></i>
													</a>
												</div>
											@endforeach
										@else
											<p class="text-muted">{{ __('no_documents_available') }}</p>
										@endif
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="verifyProviderModal">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<form method="POST" id="verifyProviderForm">
					@csrf
					<input type="hidden" name="provider_id" id="provider_id" value="{{ $data['userlist'][0]->id }}">
					<div class="modal-body text-center">
						<span class="fs-30 text-success">
							<i class="ti ti-shield-check"></i>
						</span>
						<h4>{{ __('confirm_verification') }}</h4>
						<p>{{ __('confirm_provider_verification_info') }}</p>
						<div class="d-flex justify-content-center">
							<button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
							<button type="button" class="btn btn-success" id="confirmVerifyBtn" data-verifying="{{ __('verifying') }}" data-yes_verify="{{ __('Yes, Verify') }}">{{ __('Yes, Verify') }}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

@endsection