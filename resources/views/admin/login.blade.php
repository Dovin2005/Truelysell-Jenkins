<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<meta name="description" content="{{ $companyName }} Admin">
	<meta name="keywords" content="admin">
	<meta name="author" content="{{ $companyName }}">
	<meta name="robots" content="noindex, nofollow">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ $companyName }} {{ __('Admin') }}</title>

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="{{ $dynamicFavicon }}">

	<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

	<!-- Feather CSS -->
	<link rel="stylesheet" href="{{ asset('assets/plugins/icons/feather/feather.css') }}">

	<!-- Tabler Icon CSS -->
	<link rel="stylesheet" href="{{ asset('assets/plugins/tabler-icons/tabler-icons.css') }}">

	<!-- Fontawesome CSS -->
	<link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">

	<!-- Select2 CSS -->
	<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">

	<!-- Toastr CSS -->
	<link href="{{ asset('assets/plugins/toastr/toatr.css') }}" rel="stylesheet">

	@include('color.color-configuration')

	<!-- Main CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

</head>

<body class="account-page" data-page="{{ Route::currentRouteName() }}">

	<div id="pageLoader" class="loader_front">
		<div class="loader-content">
			<div class="spinner-border text-primary" role="status">
				<span class="visually-hidden">{{ __('loading') }}</span>
			</div>
			<p>{{ __('sending_otp_please_wait') }}</p>
		</div>
	</div>

	<div class="main-wrapper">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-5 mx-auto">
					<form name="adminLoginForm" id="adminLoginForm" action="javascript:void(0);">
						<div class="d-flex flex-column justify-content-between">
							<div class="mx-auto p-4 text-center">
								<img src="{{ $dynamicLogo }}"
									class="img-fluid" alt="Logo" style="height: 36px">
							</div>
							<div class="card">
								<div class="card-body p-4">
									<div class=" mb-4">
										<h2 class="mb-2">{{ __('Welcome') }}</h2>
										<p class="mb-0">{{ __('Please enter your details to sign in') }}</p>
									</div>
									<div class="mb-3 ">
										<label class="form-label">{{ __('Email Address') }}</label>
										<div class="input-icon mb-3 position-relative">
											<span class="input-icon-addon">
												<i class="ti ti-mail"></i>
											</span>
											<input type="text" name="email" id="email" class="form-control">
										</div>
										<div class="d-flex align-items-center justify-content-between flex-wrap">
											<label class="form-label">{{ __('Password') }}</label>
											<a href="#!" class="text-primary fw-medium text-decoration-underline mb-1 fs-14" data-bs-toggle="modal" data-bs-target="#forgot-modal">{{ __('Forgot Password') }}</a>
										</div>
										<div class="pass-group">
											<input type="password" name="password" id="password" class="pass-input form-control">
											<span class="ti toggle-password ti-eye-off"></span>
										</div>
									</div>
									<div class="mb-3 mt-3">
										<button type="submit" class="btn btn-primary w-100" id="signInBtn">{{ __('Signin') }}</button>
									</div>
									<div id="error-message" class="text-danger"></div>
									<div class="mt-3">
										<h6 class="mb-1">{{ __('Admin Login Info') }}:</h6>
										<div class="p-3 border d-flex flex-wrap align-items-center justify-content-between">
											<div>
												<span class="d-block"><strong>{{ __('Email') }} </strong>: demoadmin@gmail.com</span>
												<span class="d-block"><strong>{{ __('Password') }} </strong>: 12345678</span>
											</div>
											<div>
												<a class="btn btn-primary copy-admin-details" data-email="demoadmin@gmail.com"
													data-password="12345678">
													<i class="far fa-copy"></i>
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="p-4 text-center">
								<p class="mb-0 ">{!! $copyRight !!}</p>
							</div>
						</div>
					</form>

				</div>
			</div>

			<div class="modal fade" id="otp-email-modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content">
						<div class="modal-header d-flex align-items-center justify-content-end pb-0 border-0">
							<a href="#!" data-bs-dismiss="modal" aria-label="Close">
								<i class="ti ti-circle-x-filled fs-20"></i>
							</a>
						</div>
						<div class="modal-body p-4">
							<form action="#" class="digit-group">
								<div class="text-center mb-3">
									<h3 class="mb-2">{{ __('Email OTP Verification') }}</h3>
									<p class="fs-14" id="otp-email-message">{{ __('OTP sent to your Email Address') }}</p>
								</div>
								<div class="text-center otp-input">
									<div class="inputcontainer">

									</div>
									<span id="error_message" class="text-danger"></span>
									<div>
										<div class="badge bg-danger-transparent mb-3">
											<p class="d-flex align-items-center "><i class="ti ti-clock me-1"></i><span
													id="otp-timer">00:00</span></p>
										</div>
										<div class="mb-3 d-flex justify-content-center">
											<p> {{ __('Didn t get the OTP?') }} <a href="#!"
													class="resendEmailOtp text-primary">{{ __('Resend OTP') }}</a></p>
										</div>
										<div>
											<button type="button" id="verify-email-otp-btn"
												class="verify-email-otp-btn btn btn-primary w-100">{{ __('Verify & Proceed') }}</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

			<div class="modal fade" id="otp-phone-modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content">
						<div class="modal-header d-flex align-items-center justify-content-end pb-0 border-0">
							<a href="#!" data-bs-dismiss="modal" aria-label="Close"><i
									class="ti ti-circle-x-filled fs-20"></i></a>
						</div>
						<div class="modal-body p-4">
							<form action="#" class="digit-group">
								<div class="text-center mb-3">
									<h3 class="mb-2">{{ __('Phone OTP Verification') }}</h3>
									<p id="otp-sms-message" class="fs-14">{{ __('OTP sent to your mobile number') }}</p>
								</div>
								<div class="text-center otp-input">
									<div class="inputSMSContainer">
									</div>
									<span id="error_sms_message" class="text-danger"></span>
									<div>
										<div class="badge bg-danger-transparent mb-3">
											<p class="d-flex align-items-center "><i class="ti ti-clock me-1"></i><span
													id="otp-sms-timer">00:00</span></p>
										</div>
										<div class="mb-3 d-flex justify-content-center">
											<p>{{ __('Didn t get the OTP?') }} <a href="#!"
													class="resendSMSOtp text-primary">{{ __('Resend OTP') }}</a></p>
										</div>
										<div>
											<button type="button" id="verify-sms-otp-btn"
												class="btn btn-primary w-100">{{ __('Verify & Proceed') }}</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

			<div class="modal fade" id="forgot-modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content">
						<div class="modal-header d-flex align-items-center justify-content-end pb-0 border-0">
							<a href="#!" data-bs-dismiss="modal" aria-label="Close">
								<i class="ti ti-circle-x-filled fs-20"></i>
							</a>
						</div>
						<div class="modal-body p-4">
							<form id="forgot_login">
								<div class="text-center mb-4">
									<h2 class="mb-2">{{ __('Forgot Password') }}</h2>
									<p>{{ __('Enter your email, we will send you a otp to reset your password.') }}</p>
								</div>
								<div class="mb-3">
									<label class="form-label">{{ __('email') }}</label>
									<div class="input-icon mb-3 position-relative">
										<span class="input-icon-addon position-absolute">
											<i class="ti ti-mail"></i>
										</span>
										<input type="email" name="forgot_email" id="forgot_email" class="form-control" placeholder="{{ __('Enter Email') }}">
									</div>
									<div class="invalid-feedback" id="forgot_email_error"></div>
								</div>
								<div class="mb-3">
									<button type="button" class="btn btn-primary w-100" id="otp_signin_forgot">{{ __('submit') }}</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

			<div class="modal fade" id="reset-password" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content">
						<div class="modal-header d-flex align-items-center justify-content-end pb-0 border-0">
							<a href="#!" data-bs-dismiss="modal" aria-label="Close"><i
									class="ti ti-circle-x-filled fs-20"></i></a>
						</div>
						<div class="modal-body p-4">
							<div class="text-center mb-3">
								<h3 class="mb-2">{{ __('Reset Password') }}</h3>
								<p class="fs-14">{{ __('reset_password_description') }}</p>
							</div>
							<form id="forgotPassword" autocomplete="off" novalidate="novalidate">
								{{ csrf_field() }}
								<input type="hidden" name="email_id" id="email_id" value="" autocomplete="username">
								<div class="input-block mb-3">
									<div class="mb-3">
										<label class="form-label">{{ __('New Password') }}</label>
										<div class="pass-group" id="passwordInput">
											<input type="password" name="new_password" id="new_password"
												class="form-control pass-input" placeholder="Enter New Password"
												autocomplete="new-password" required>
											<div class="invalid-feedback" id="new_password_error"></div>
										</div>
									</div>
								</div>
								<div class="mb-3">
									<div class="d-flex align-items-center justify-content-between flex-wrap">
										<label class="form-label">{{ __('Confirm Password') }}</label>
									</div>
									<input type="password" name="confirm_password" id="confirm_password" class="form-control"
										placeholder="Confirm Password" autocomplete="new-password" required>
									<div class="invalid-feedback" id="confirm_password_error"></div>
								</div>
								<div>
									<button type="submit" class="btn btn-primary w-100 forgot_btn">{{ __('Save Changes') }}</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

			<div class="modal fade" id="otp_error" tabindex="-1" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered modal-lg">
					<div class="modal-content">
						<div class="modal-body text-center">
							<div class="mb-4">
								<span class="error-icon mx-auto mb-4">
									<i class="ti ti-alert"></i>
								</span>
								<h4 class="mb-1">{{ __('OTP Verification Failed') }}</h4>
								<p>{{ __('otp_verification_failed_description') }}</p>
							</div>
							<a href="#!" data-bs-dismiss="modal" class="btn btn-primary">{{ __('Close') }}</a>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>

	<!-- jQuery -->
	<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>

	<!-- jQuery validation -->
	<script src="{{ asset('assets/js/jquery-validation.min.js') }}"></script>
	<script src="{{ asset('assets/js/jquery-validation-additional-methods.min.js') }}"></script>

	<!-- Bootstrap Core JS -->
	<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

	<!-- Feather Icon JS -->
	<script src="{{ asset('assets/js/feather.min.js') }}"></script>

	<!-- Slimscroll JS -->
	<script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}"></script>

	<!-- Select2 JS -->
	<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>

	<!-- Custom JS -->
	<script src="{{ asset('assets/js/script.js') }}"></script>

	<!-- Toastr JS -->
	<script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>

	<!-- Custom JS -->
	<script src="{{ asset('assets/js/adminscript.js') }}"></script>

</body>

</html>
