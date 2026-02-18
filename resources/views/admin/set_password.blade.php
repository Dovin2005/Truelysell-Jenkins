<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

	<meta name="description" content="Truelysell Admin">

	<meta name="keywords" content="admin">

	<meta name="author" content="Dreams technologies - Truelysell">

	<meta name="robots" content="noindex, nofollow">


	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>{{ $companyName ?? 'Truelysell - Admin' }}</title>

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="{{ $dynamicFavicon }}">

	<!-- Bootstrap CSS -->
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



	<div class="main-wrapper">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-5 mx-auto">
					<form id="setPasswordForm" autocomplete="off">
						<div class="d-flex flex-column justify-content-between vh-100">
							<div class=" mx-auto p-4 text-center">
								<img src="{{ asset('front/img/logo.svg') }}"
									class="img-fluid" alt="Logo">
							</div>
							<div class="card">
								<div class="card-body p-4">
									<div class=" mb-4">
										<h2 class="mb-2">{{ __('Set Password') }}</h2>
									</div>
                                    <input type="hidden" id="id" name="id" value="{{ Crypt::decrypt(request('id')) }}">
									<div class="mb-3">
                                        <label class="form-label">{{__('New Password')}} <span class="text-danger"> *</span></label>
                                        <div class="pass-group d-flex">
                                            <input type="password" class="pass-inputs form-control" name="new_password" id="new_password">
											<span class="ti toggle-passwords ti-eye-off"></span>
                                        </div>
                                        <span class="text-danger error-text" id="new_password_error" data-required="{{ __('new_password_required') }}" data-min="{{ __('new_password_minlength') }}" data-not_equal="{{ __('new_password_notEqualTo') }}"></span>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label">{{__('Confirm Password')}} <span class="text-danger"> *</span></label>
                                        <div class="pass-group d-flex">
                                            <input type="password" class="pass-inputa form-control" name="confirm_password" id="confirm_password">
											<span class="ti toggle-passworda ti-eye-off"></span>
                                        </div>
                                        <span class="text-danger error-text" id="confirm_password_error" data-required="{{ __('confirm_password_required') }}" data-equal="{{ __('confirm_password_equalTo') }}"></span>
                                    </div>
									<div class="mb-3 mt-3">
										<button type="submit" class="btn btn-primary w-100" id="set_password_btn" data-save="{{ __('Set Password') }}">{{ __('Set Password') }}</button>
									</div>
								</div>
							</div>
							<div class="p-4 text-center">
								<p class="mb-0 ">Copyright &copy; {{ date('Y') }} - Truelysell</p>
							</div>
						</div>
					</form>

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