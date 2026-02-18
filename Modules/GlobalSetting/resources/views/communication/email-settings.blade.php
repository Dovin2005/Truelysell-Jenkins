@extends('admin.admin')
@section('content')

		<div class="page-wrapper">
			<div class="content bg-white">
				<div class="d-md-flex d-block align-items-center justify-content-between ">
					<div class="my-auto mb-2">
						<h3 class="page-title mb-1 translatable" data-translate="Email_Settings">{{ __('Email_Settings')}}</h3>
                     	<nav>
                       		<ol class="breadcrumb mb-0">
                          		<li class="breadcrumb-item">
                            		<a href="{{ route('admin.dashboard') }}" class="translatable" data-translate="Dashboard">{{ __('Dashboard')}}</a>
                          		</li>
                          		<li class="breadcrumb-item">
                            		<a href="javascript:void(0);" class="translatable" data-translate="Communication_Settings">{{ __('Communication_Settings')}}</a>
                          		</li>
                          		<li class="breadcrumb-item active translatable" aria-current="page"  data-translate="Email_Settings">{{ __('Email_Settings')}}</li>
                        	</ol>
                      	</nav>
                    </div>
                </div>
				<div >
                    <div class="card-body p-0 py-3">
					    <div class="col-xxl-10 col-xl-9">
							<form>
								<div class="d-md-flex">
									<div class="flex-fill">
										<div class="row">
											<div class="col-xxl-4 col-xl-6" style="display:none;">
												<div class="card">
													<div class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
														<div class="skeleton label-skeleton label-loader"></div>
														<div class="d-flex align-items-center d-none real-label">
															<span class="avatar avatar-lg p-2 rounded bg-gray flex-shrink-0 me-2"><img src="{{ asset('assets/img/icons/php-icon.svg')}}" alt="Img"></span>
															<h6>PHP Mailer</h6>
														</div><div class="status-toggle modal-status d-none real-label">
                                                            @if(isset($permission))
                                                                @if(hasPermission($permission, 'Communication Settings', 'edit'))
                                                                <input type="checkbox" id="phpmail" class="check make_default" data-type="phpmail">
                                                                <label for="phpmail" class="checktoggle"> </label>
                                                                @endif
                                                            @endif
														</div>
													</div>
													<div class="card-body pt-0">
														<div class="skeleton input-skeleton input-loader"></div>
														<p class="d-none real-input">Used to send emails safely and easily via PHP code from a web server.</p>
													</div>
													<div class="card-footer d-flex justify-content-between align-items-center">
														<div class="skeleton input-skeleton input-loader"></div>
														<div class="d-none real-input">
															<a href="#" class="btn btn-outline-light integrate" data-bs-toggle="modal" data-bs-target="#connect_php" data-id="1" data-type="phpmail"><i class="ti ti-tool me-2"></i>View Integration</a>
														</div>
													</div>
												</div>
											</div>
											<div class="col-xxl-4 col-xl-6">
												<div class="card">
													<div class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
														<div class="skeleton label-skeleton label-loader"></div>
														<div class="d-flex align-items-center d-none real-label">
															<span class="avatar avatar-lg p-2 rounded bg-gray flex-shrink-0 me-2"><img src="{{ asset('assets/img/icons/smtp-icon.svg')}}" alt="Img"></span>
															<h6 class="translatable" data-translate="SMTP">{{ __('SMTP')}}</h6>
														</div>
														<div class="status-toggle modal-status d-none real-label">
                                                            @if(isset($permission))
                                                                @if(hasPermission($permission, 'Communication Settings', 'edit'))
                                                                <input type="checkbox" id="smtp" class="check make_default" data-type="smtp">
                                                                <label for="smtp" class="checktoggle"> </label>
                                                                @endif
                                                            @endif
														</div>
													</div>
													<div class="card-body pt-0">
														<div class="skeleton input-skeleton input-loader"></div>
														<p class="translatable d-none real-input" data-translate="smtp_content">{{ __('smtp_content')}}</p>
													</div>
													<div class="card-footer d-flex justify-content-between align-items-center">
														<div class="skeleton input-skeleton input-loader"></div>
														<div class="d-none real-input">
															<a href="#" class="btn btn-outline-light integrate translatable" data-bs-toggle="modal" data-bs-target="#connect_smtp" data-id="1" data-type="smtp"  data-translate="Email_Settings"><i class="ti ti-tool me-2"></i>{{ __('View_Integration')}}</a>
														</div>
													</div>
												</div>
											</div>
											<div class="col-xxl-4 col-xl-6">
												<div class="card">
													<div class="card-header d-flex align-items-center justify-content-between border-0 mb-3 pb-0">
														<div class="skeleton label-skeleton label-loader"></div>
														<div class="d-flex align-items-center d-none real-label">
															<span class="avatar avatar-lg p-2 rounded bg-gray flex-shrink-0 me-2"><img src="{{ asset('assets/img/icons/google-icon-02.svg')}}" alt="Img"></span>
															<h6 class="translatable" data-translate="Twilio (Sendgrid)">{{ __('Twilio (Sendgrid)') }}</h6>
														</div>
														<div class="status-toggle modal-status d-none real-label">
                                                            @if(isset($permission))
                                                                @if(hasPermission($permission, 'Communication Settings', 'edit'))
                                                                <input type="checkbox" id="sendgrid" class="check make_default" data-type="sendgrid">
                                                                <label for="sendgrid" class="checktoggle"> </label>
                                                                @endif
                                                            @endif
														</div>
													</div>
													<div class="card-body pt-0">
														<div class="skeleton input-skeleton input-loader"></div>
														<p class="translatable d-none real-input" data-translate="twilio_content">{{ __('twilio_content')}}</p>
													</div>
													<div class="card-footer d-flex justify-content-between align-items-center">
														<div class="skeleton input-skeleton input-loader"></div>
														<div class="d-none real-input">
															<a href="#" class="btn btn-outline-light integrate translatable" data-bs-toggle="modal" data-bs-target="#connect_sendgrid" data-id="1" data-type="sendgrid"  data-translate="Email_Settings"><i class="ti ti-tool me-2"></i>{{ __('View_Integration')}}</a>
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
			</div>
		</div>
		<div class="modal fade" id="connect_php">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">PHP Mailer</h4>
						<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
							<i class="ti ti-x"></i>
						</button>
					</div>
					<form id="phpemailform">
						<div class="modal-body">
							<div class="row">
								<div class="col-md-12">
								<input type="hidden" name="type" id="type1" class="form-control type" value="phpmail">
									<div class="mb-3">
										<label class="form-label">From Email Address</label>
										<input type="email" name="from_email" id="phpmail_from_email" class="form-control from_email" placeholder="Enter Email">
										<span class="text-danger error-text" id="phpmail_from_email_error"></span>
									</div>
									<div class="mb-3">
										<label class="form-label">Email Password</label>
										<input type="password" name="password" id="phpmail_password" class="form-control password" placeholder="Enter Password">
										<span class="text-danger error-text" id="phpmail_password_error"></span>
									</div>
									<div class="mb-3">
										<label class="form-label">From Email Name</label>
										<input type="text" name="name" id="phpmail_from_name" class="form-control name" placeholder="Enter Email Name">
										<span class="text-danger error-text" id="phpmail_from_name_error"></span>
									</div>
									<div class="modal-satus-toggle d-flex align-items-center justify-content-between" style="display:none;">
										<div class="status-title">
											<h5>Status</h5>
											<p>Change the Status by toggle </p>
										</div>
										<div class="status-toggle modal-status">
											<input type="checkbox" id="statusToggle" name="status" class="check phpmail_status" checked="">
											<label for="statusToggle" class="checktoggle"> </label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<a href="#" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</a>
                            @if(isset($permission))
                                @if(hasPermission($permission, 'Communication Settings', 'edit'))
                                    <button type="submit"  class="btn btn-primary">Save</button>
                                @endif
                            @endif
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="modal fade" id="connect_smtp">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title translatable" data-translate="SMTP">{{ __('SMTP')}}</h4>
						<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
							<i class="ti ti-x"></i>
						</button>
					</div>
					<form id="smtpform">
						<div class="modal-body">
							<div class="row">
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label translatable" data-translate="From_Email">{{ __('From_Email')}}</label><span class="text-danger"> *</span>
										<input type="text" name="from_email" id="smtp_from_email" class="form-control from_email validate-input" placeholder="{{ __('enter_email') }}">
										<input type="hidden" name="type" id="type2" class="form-control type" value="smtp">
										<span class="text-danger error-text" id="smtp_from_email_error"></span>
									</div>
									<div class="mb-3">
										<label class="form-label translatable" data-translate="Email_Password">{{ __('Email_Password')}}</label><span class="text-danger"> *</span>
										<input type="password" name="password" id="smtp_password" class="form-control password validate-input" placeholder="{{ __('enter_password') }}">
										<span class="text-danger error-text" id="smtp_password_error"></span>
									</div>
									<div class="mb-3">
										<label class="form-label translatable" data-translate="From_Name">{{ __('From_Name')}}</label><span class="text-danger"> *</span>
										<input type="text" name="smtp_from_name" id="smtp_from_name" class="form-control smtp_from_name validate-input" placeholder="{{ __('enter_from_email_name') }}">
										<span class="text-danger error-text" id="smtp_from_name_error"></span>
									</div>
									<div class="mb-3">
										<label class="form-label translatable"  data-translate="Host">{{ __('Host')}}</label><span class="text-danger"> *</span>
										<input type="text" name="host" id="host" class="form-control host validate-input" placeholder="{{ __('enter_email_host') }}">
										<span class="text-danger error-text" id="host_error"></span>
									</div>
									<div class="mb-3">
										<label class="form-label translatable"  data-translate="Port">{{ __('Port')}}</label><span class="text-danger"> *</span>
										<input type="text" name="port" id="port"  class="form-control port validate-input" placeholder="{{ __('enter_port') }}">
									    <span class="text-danger error-text" id="port_error"></span>
									</div>
									<div class="modal-satus-toggle d-flex align-items-center justify-content-between" style="display:none!important;">
										<div class="status-title">
											<h5 class="translatable"  data-translate="Status">Status</h5>
											<p>Change the Status by toggle </p>
										</div>
										<div class="status-toggle modal-status">
											<input type="checkbox" id="statusToggle" class="check smtp_status" checked="">
											<label for="statusToggle" class="checktoggle"> </label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<a href="#" class="btn btn-light me-2 translatable" data-bs-dismiss="modal"  data-translate="Cancel">{{ __('Cancel')}}</a>
                            @if(isset($permission))
                                @if(hasPermission($permission, 'Communication Settings', 'edit'))
                                    <button type="submit" class="btn btn-primary smtp_btn translatable"  data-translate="Save" data-save="{{ __('Save') }}">{{ __('Save')}}</button>
                                @endif
                            @endif
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="modal fade" id="connect_sendgrid">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title translatable" data-translate="Twilio (Sendgrid)">{{ __('Twilio (Sendgrid)') }}</h4>
						<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
							<i class="ti ti-x"></i>
						</button>
					</div>
					<form id="sendgridform">
						<div class="modal-body">
							<div class="row">
								<div class="col-md-12">
									<div class="mb-3">
										<label class="form-label translatable" data-translate="From_Email">{{ __('From_Email')}}</label><span class="text-danger"> *</span>
										<input type="text" name="from_email" id="sendgrid_from_email" class="form-control from_email validate-input" placeholder="{{ __('enter_email') }}">
										<input type="hidden" name="type" id="type3" class="form-control type" value="sendgrid">
										<span class="text-danger error-text" id="sendgrid_from_email_error"></span>
									</div>
									<div class="mb-3">
										<label class="form-label translatable" data-translate="Sendgrid_Key">{{ __('Sendgrid_Key')}}</label><span class="text-danger"> *</span>
										<input type="text" name="sendgridkey" id="sendgrid_key" class="form-control sendgridkey validate-input" placeholder="{{ __('enter_sendgrid_key') }}">
										<span class="text-danger error-text" id="sendgrid_key_error"></span>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<a href="#" class="btn btn-light me-2  translatable" data-translate="Cancel" data-bs-dismiss="modal">{{ __('Cancel')}}</a>
                            @if(isset($permission))
                                @if(hasPermission($permission, 'Communication Settings', 'edit'))
                                    <button type="submit"  class="btn btn-primary  translatable sendgrid_btn" data-translate="Save" data-save="{{ __('Save') }}">{{ __('Save')}}</button>
                                @endif
                            @endif
						</div>
					</form>
				</div>
			</div>
		</div>
@endsection
