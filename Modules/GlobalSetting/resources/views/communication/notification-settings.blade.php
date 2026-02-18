@extends('admin.admin')
@section('content')

<div class="page-wrapper">
	<div class="content bg-white">
		<div class="d-md-flex d-block align-items-center justify-content-between pb-3">
			<div class="my-auto mb-2">
			<h3 class="page-title mb-1 translatable" data-translate="Notification_Settings">{{ __('Notification_Settings')}}</h3>
				<nav>
					<ol class="breadcrumb mb-0">
						<li class="breadcrumb-item">
							<a href="{{ route('admin.dashboard') }}" class="translatable" data-translate="Dashboard">{{ __('Dashboard')}}</a>
						</li>
						<li class="breadcrumb-item">
							<a href="javascript:void(0);" class="translatable" data-translate="Communication_Settings">{{ __('Communication_Settings')}}</a>
						</li>
						<li class="breadcrumb-item active translatable" aria-current="page" data-translate="Notification_Settings">{{ __('Notification_Settings')}}</li>
					</ol>
				</nav>
			</div>
			<div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
				<div class="pe-1 mb-2">
                    @if(isset($permission))
                        @if(hasPermission($permission, 'Communication Settings', 'edit'))
							<div class="skeleton label-skeleton label-loader"></div>
                            <button type="submit" class="btn btn-primary me-2 savesettings translatable d-none real-label" data-translate="Save">{{ __('Save')}}</button>
                        @endif
                    @endif
				</div>
			</div>
		</div>
		<div class="card">
			<div class="card-body p-3 pb-0">
				<div class="col-xxl-10 col-xl-9">
						<form id="notificationForm">
							<div class="row">
								<div class="col-xxl-4 col-lg-4 col-md-6">
									<div class="align-items-center justify-content-between bg-white p-3 mb-3">
										<div class="skeleton label-skeleton label-loader"></div>
										<label class="translatable d-none real-label"   data-translate="Email_Notifications">
											<input type="hidden" class="form-check-input" name="type" id="type" value="notification_settings">
											<input type="checkbox" class="form-check-input"   name="emailNotifications" id="emailNotifications">
												{{ __('Email_Notifications')}}
										</label>
									</div>
								</div>
								<div class="col-xxl-4 col-lg-4 col-md-6">
									<div class="align-items-center justify-content-between bg-white p-3 mb-3">
										<div class="skeleton label-skeleton label-loader"></div>
										<label class="translatable d-none real-label"   data-translate="SMS_Notifications">
											<input type="checkbox" class="form-check-input" name="smsNotifications" id="smsNotifications">
											{{ __('SMS_Notifications')}}
										</label>
									</div>
								</div>
								<div class="col-xxl-4 col-lg-4 col-md-6">
									<div class="align-items-center justify-content-between bg-white p-3 mb-3">
										<div class="skeleton label-skeleton label-loader"></div>
										<label class="translatable d-none real-label"  data-translate="Push_Notifications">
											<input type="checkbox" class="form-check-input" name="pushNotifications" id="pushNotifications">
											{{ __('Push_Notifications')}}
										</label>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<div class="card-body ps-4 mt-3">
			<div class="col-xxl-10 col-xl-9">
				<form>
					<div class="d-md-flex">
						<div class="flex-fill">
							<div class="row">
								<div class="col-xxl-4 col-xl-6">
									<div class="card">
										<div class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
											<div class="d-flex align-items-center">
												<div class="skeleton label-skeleton label-loader"></div>
												<h6 class="translatable d-none real-label" data-translate="FCM Configuration">{{ __('FCM Configuration')}}</h6>
											</div>
										</div>
										<div class="card-footer d-flex justify-content-between align-items-center">
											<div class="skeleton input-skeleton label-loader"></div>
											<div class="d-none real-input">
												<a href="#" class="btn btn-outline-light integrate translatable" id="viewFcm" data-bs-toggle="modal" data-bs-target="#configuration_modal" data-id="1" data-type="smtp" data-translate="View_Integration"><i class="ti ti-tool me-2"></i>{{ __('View_Integration')}}</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
</div>
<div class="modal fade" id="configuration_modal">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title translatable" data-translate="FCM Configuration">{{ __('FCM Configuration') }}</h4>
				<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
					<i class="ti ti-x"></i>
				</button>
			</div>
			<form id="configurationForm">
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div class="mb-3">
								<label class="form-label translatable" data-translate="Project ID">{{ __('Project ID')}}</label><span class="text-danger"> *</span>
								<input type="text" name="project_id" id="project_id" class="form-control">
								<span class="text-danger error-text" id="project_id_error" data-required="{{ __('project_id_required') }}"></span>
							</div>
							<div class="mb-3">
								<label class="form-label translatable" data-translate="Client Email">{{ __('Client Email')}}</label><span class="text-danger"> *</span>
								<input type="text" name="client_email" id="client_email" class="form-control">
								<span class="text-danger error-text" id="client_email_error" data-required="{{ __('project_id_required') }}" data-email_format="{{ __('email_format') }}"></span>
							</div>
							<div class="mb-3">
								<label class="form-label translatable" data-translate="Private Key">{{ __('Private Key')}}</label><span class="text-danger"> *</span>
								<input type="text" name="private_key" id="private_key" class="form-control">
								<span class="text-danger error-text" id="private_key_error" data-required="{{ __('project_id_required') }}"></span>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<a href="#" class="btn btn-light me-2 translatable" data-translate="Cancel" data-bs-dismiss="modal">{{ __('Cancel')}}</a>
                    @if(isset($permission))
                        @if(hasPermission($permission, 'Communication Settings', 'edit'))
                            <button type="submit"  class="btn btn-primary translatable"  data-translate="Save" id="configurationSaveBtn" data-save="{{ __('Save') }}">{{ __('Save')}}</button>
                        @endif
                    @endif
				</div>
			</form>
		</div>
	</div>
</div>

@endsection
