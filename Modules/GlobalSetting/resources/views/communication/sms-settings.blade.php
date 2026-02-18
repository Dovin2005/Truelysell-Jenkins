@extends('admin.admin')
@section('content')

		<div class="page-wrapper">
			<div class="content bg-white">
				<div class="d-md-flex d-block align-items-center justify-content-between">
				<div class="my-auto mb-2">
						<h3 class="page-title mb-1 translatable" data-translate="Email_Settings">{{ __('SMS_Settings')}}</h3>
                     	<nav>
                       		<ol class="breadcrumb mb-0">
                          		<li class="breadcrumb-item">
                            		<a href="{{ route('admin.dashboard') }}" class="translatable" data-translate="Email_Settings">{{ __('Dashboard')}}</a>
                          		</li>
                          		<li class="breadcrumb-item">
                            		<a href="javascript:void(0);" class="translatable" data-translate="Email_Settings">{{ __('Communication_Settings')}}</a>
                          		</li>
                          		<li class="breadcrumb-item active translatable" data-translate="Email_Settings" aria-current="page">{{ __('SMS_Settings')}}</li>
                        	</ol>
                      	</nav>
                    </div>
                </div>
				<div>
                    <div class="card-body p-0 py-3">
					    <div class="col-xxl-10 col-xl-9">

							<div class="row">
							    <div class="col-xxl-4 col-xl-6">
									<div class="card">
										<div class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
											<div class="skeleton label-skeleton label-loader"></div>
											<div class="d-flex align-items-center d-none real-label">
												<span class="d-block"><img src="{{asset('assets/img/icons/sms-icon-01.svg')}}" alt="Img"></span>
											</div>
											<div class="status-toggle modal-status d-none real-label">
                                                @if(isset($permission))
                                                    @if(hasPermission($permission, 'Communication Settings', 'edit'))
                                                        <input type="checkbox" id="nexmo" class="check make_default" data-type="nexmo">
                                                        <label for="nexmo" class="checktoggle"> </label>
                                                    @endif
                                                @endif
											</div>
										</div>
										<div class="card-body pt-0">
											<div class="skeleton input-skeleton input-loader"></div>
											<p class="translatable d-none real-input" data-translate="nexmo_content">{{ __('nexmo_content')}}</p>
										</div>
										<div class="card-footer d-flex justify-content-between align-items-center">
											<div class="skeleton input-skeleton input-loader"></div>
											<div class="d-none real-input">
											<a href="#" class="btn btn-outline-light translatable" data-translate="View_Integration" data-bs-toggle="modal" data-bs-target="#connect_nexmo"><i class="ti ti-tool me-2"></i>{{ __('View_Integration')}}</a>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xxl-4 col-md-6" style="display:none;">
									<div class="d-flex align-items-center justify-content-between bg-white p-3 border rounded mb-3">
										<div class="skeleton label-skeleton label-loader"></div>
										<span class="d-block d-none real-label"><img src="{{asset('assets/img/icons/sms-icon-02.svg')}}" alt="Img"></span>
										<div class="d-flex align-items-center d-none real-label">
											<div class="status-toggle modal-status">
                                                @if(isset($permission))
                                                    @if(hasPermission($permission, 'Communication Settings', 'edit'))
                                                    <input type="checkbox" id="twofactor" class="check make_default" data-type="twofactor">
                                                    <label for="twofactor" class="checktoggle"> </label>
                                                    @endif
                                                @endif
											</div>
											<a href="#" class="btn btn-outline-light bg-white btn-icon ms-2" data-bs-toggle="modal" data-bs-target="#connect_factor"><i class="ti ti-settings-cog"></i></a>
										</div>
									</div>
								</div>
								<div class="col-xxl-4 col-xl-6">
									<div class="card">
										<div class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
											<div class="skeleton label-skeleton label-loader"></div>
											<div class="d-flex align-items-center d-none real-label">
											    <span class="d-block"><img src="{{asset('assets/img/icons/sms-icon-03.svg')}}" alt="Img"></span>
											</div>
											<div class="status-toggle modal-status d-none real-label">
                                                @if(isset($permission))
                                                    @if(hasPermission($permission, 'Communication Settings', 'edit'))
                                                        <input type="checkbox" id="twilio" class="check make_default" data-type="twilio">
                                                        <label for="twilio" class="checktoggle"> </label>
                                                    @endif
                                                @endif
											</div>
										</div>
										<div class="card-body pt-0">
											<div class="skeleton input-skeleton input-loader"></div>
											<p class="translatable d-none real-input" data-translate="twilio_sms">{{ __('twilio_sms')}}</p>
										</div>
										<div class="card-footer d-flex justify-content-between align-items-center">
											<div class="skeleton input-skeleton input-loader"></div>
											<div class="d-none real-input">
									     		<a href="#" class="btn btn-outline-light translatable" data-translate="View_Integration" data-bs-toggle="modal" data-bs-target="#connect_twilio"><i class="ti ti-tool me-2"></i>{{ __('View_Integration')}}</a>
											</div>
										</div>
									</div>
								</div>
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="connect_nexmo">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title translatable" data-translate="Nexmo">{{ __('Nexmo')}}</h4>
						<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
							<i class="ti ti-x"></i>
						</button>
					</div>
					<form id="addNexmoForm">
						<div class="modal-body">
							<div class="row">
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label translatable" data-translate="API_Key">{{ __('API_Key')}}</label><span class="text-danger"> *</span>
										<input type="hidden" name="type" value="nexmo">
										<input type="text" name="nexmo_api_key" id="nexmo_api_key" class="form-control validate-input" placeholder="{{ __('enter_api_key') }}">
										<span class="text-danger error-text" id="nexmo_api_key_error"></span>
									</div>
									<div class="mb-3">
										<label class="form-label translatable" data-translate="API_Secret">{{ __('API_Secret')}}</label><span class="text-danger"> *</span>
										<input type="text" name="nexmo_secret_key" id="nexmo_secret_key" class="form-control validate-input" placeholder="{{ __('enter_api_secret_key') }}">
										<span class="text-danger error-text" id="nexmo_secret_key_error"></span>
									</div>
									<div class="mb-3">
										<label class="form-label translatable" data-translate="Sender_ID">{{ __('Sender_ID')}}</label><span class="text-danger"> *</span>
										<input type="text" name="nexmo_sender_id" id="nexmo_sender_id" class="form-control validate-input" placeholder="{{ __('enter_sender_id') }}">
										<span class="text-danger error-text" id="nexmo_sender_id_error"></span>
									</div>

								</div>
							</div>
						</div>
						<div class="modal-footer">
							<a href="#" class="btn btn-light me-2 translatable" data-translate="Cancel" data-bs-dismiss="modal">{{ __('Cancel')}}</a>
                            @if(isset($permission))
                                @if(hasPermission($permission, 'Communication Settings', 'edit'))
                                    <button type="submit" class="btn btn-primary nexmo_btn translatable" data-translate="Save" data-save="{{ __('Save') }}">{{ __('Save')}}</button>
                                @endif
                            @endif
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="modal fade" id="connect_factor">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">{{ __('') }}2Factor</h4>
						<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
							<i class="ti ti-x"></i>
						</button>
					</div>
					<form id="addfactorForm">
						<div class="modal-body">
							<div class="row">
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label">{{ __('API_Key')}}</label><span class="text-danger"> *</span>
										<input type="hidden" name="type" value="twofactor">
										<input type="text" name="twofactor_api_key" id="factor_api_key" class="form-control validate-input" placeholder="{{ __('enter_api_key') }}">
										<span class="text-danger error-text" id="twofactor_api_key_error"></span>
									</div>
									<div class="mb-3">
										<label class="form-label">{{ __('API_Secret')}}</label><span class="text-danger"> *</span>
										<input type="text" name="twofactor_secret_key" id="factor_secret_key" class="form-control validate-input" placeholder="{{ __('enter_api_secret_key') }}">
										<span class="text-danger error-text" id="twofactor_secret_key_error"></span>
									</div>
									<div class="mb-3">
										<label class="form-label">{{ __('Sender_ID')}}</label><span class="text-danger"> *</span>
										<input type="text" name="twofactor_sender_id" id="factor_sender_id" class="form-control validate-input" placeholder="{{ __('enter_sender_id') }}">
										<span class="text-danger error-text" id="twofactor_sender_id_error"></span>
									</div>

								</div>
							</div>
						</div>
						<div class="modal-footer">
							<a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel')}}</a>
                            @if(isset($permission))
                                @if(hasPermission($permission, 'Communication Settings', 'edit'))
                                <button type="submit" class="btn btn-primary nexmo_btn">{{ __('Save')}}</button>
                                @endif
                            @endif
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="modal fade" id="connect_twilio">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title translatable" data-translate="Twilio">{{ __('Twilio')}}</h4>
						<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
							<i class="ti ti-x"></i>
						</button>
					</div>
					<form id="addtwilioForm">
						<div class="modal-body">
							<div class="row">
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label translatable" data-translate="Account_SID">{{ __('Account_SID')}}</label><span class="text-danger"> *</span>
										<input type="hidden" name="type" value="twilio">
										<input type="text" name="twilio_api_key" id="twilio_api_key" class="form-control validate-input" placeholder="{{ __('enter_account_sid') }}">
										<span class="text-danger error-text" id="twilio_api_key_error"></span>
									</div>
									<div class="mb-3">
										<label class="form-label translatable" data-translate="API_Secret">{{ __('API_Secret')}} {{ __('(Auth Token)')}}</label><span class="text-danger"> *</span>
										<input type="text" name="twilio_secret_key" id="twilio_secret_key" class="form-control validate-input" placeholder="{{  __('enter_api_secret_key') }}">
										<span class="text-danger error-text" id="twilio_secret_key_error"></span>
									</div>
									<div class="mb-3">
										<label class="form-label translatable" data-translate="Sender_ID">{{ __('Sender_ID')}} {{ __('Twilio Phone Number')}}</label><span class="text-danger"> *</span>
										<input type="text" name="twilio_sender_id" id="twilio_sender_id" class="form-control validate-input" placeholder="{{ __('enter_sender_id') }}">
										<span class="text-danger error-text" id="twilio_sender_id_error"></span>
									</div>

								</div>
							</div>
						</div>
						<div class="modal-footer">
							<a href="#" class="btn btn-light me-2 translatable" data-translate="Cancel" data-bs-dismiss="modal">{{ __('Cancel')}}</a>
                            @if(isset($permission))
                                @if(hasPermission($permission, 'Communication Settings', 'edit'))
                                    <button type="submit" class="btn btn-primary twilio_btn translatable" data-translate="Save" data-save="{{ __('Save') }}">{{ __('Save')}}</button>
                                @endif
                            @endif
						</div>
					</form>
				</div>
			</div>
		</div>
		@endsection
