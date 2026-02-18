<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="description" content="{{ trim($__env->yieldContent('description')) ?: '' }}">
	<meta name="keywords" content="{{ trim($__env->yieldContent('keywords')) ?: '' }}">
	<title>
		@if(trim($__env->yieldContent('title')))
		@yield('title')
		@else
		{{$companyName}}
		@endif
	</title>

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="{{ $dynamicFavicon }}">

	@php
	$isRTL = isRTL(app()->getLocale());
	@endphp

	@if ($isRTL)
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="{{ asset('front/css/bootstrap.rtl.min.css') }}">
	@else
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="{{ asset('front/css/bootstrap.min.css') }}">
	@endif

	<!-- Toastr CSS -->
	<link href="{{ asset('assets/plugins/toastr/toatr.css') }}" rel="stylesheet">

	<!-- Tabler Icon CSS -->
	<link rel="stylesheet" href="{{ asset('front/plugins/tabler-icons/tabler-icons.css') }}">

	<!-- Fontawesome Icon CSS -->
	<link rel="stylesheet" href="{{ asset('front/plugins/fontawesome/css/fontawesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('front/plugins/fontawesome/css/all.min.css') }}">

	@include('color.color-configuration')

	<!-- Bootstrap CSS -->
	@if ($isRTL)
	<link rel="stylesheet" href="{{ asset('front/css/stylertl.css?v=1.1') }}">
	@else
	<link rel="stylesheet" href="{{ asset('front/css/stylenew.css?v=2.1') }}">
	@endif

	<!-- Custom CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
</head>

<body class="authentication-page">

	<div class="d-flex justify-content-between vh-100 overflow-auto flex-column">

		<!-- Header -->
		<div class="authentication-header">
			<div class="container">
				<div class="col-md-12">
					<div class="text-center">
						<img src="{{ $dynamicLogo }}" class="img-fluid" alt="logo" width="100" height="100">
					</div>
				</div>
			</div>
		</div>
		<!-- /Header -->

		<!-- Main Wrapper -->
		<div class="main-wrapper">
			<div class="container">
				<div class="row justify-content-center">
					<div class="col-md-5 mx-auto">
                        <div class="d-flex flex-column justify-content-center">
                            <div class="card p-sm-4 my-5">
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <h3 class="mb-2">{{ __('Reset Password') }}</h3>
                                        <p class="fs-14">{{ __('reset_password_description') }}</p>
                                    </div>
                                    <div>
                                        <form id="forgotPassword" autocomplete="off">
                                            @csrf
                                            <input type="hidden" name="email_id" id="email_id" value="{{ request('email') }}" autocomplete="username">
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
                                                <div class="password-strength d-flex" id="passwordStrength">
                                                    <span id="poor"></span>
                                                    <span id="weak"></span>
                                                    <span id="strong"></span>
                                                    <span id="heavy"></span>
                                                </div>
                                                <div id="passwordInfo" class="mb-2"></div>
                                                <p class="fs-12">{{ __('Use 8 or more characters with a mix of letters, numbers & symbols.') }}</p>
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
                                                <button type="submit" class="btn btn-lg btn-linear-primary w-100 forgot_btn" data-save_text="{{ __('Save Changes') }}">{{ __('Save Changes') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div>
                                        <img src="/front/img/bg/authentication-bg.png" class="bg-left-top"
                                            alt="Img">
                                        <img src="/front/img/bg/authentication-bg.png" class="bg-right-bottom"
                                            alt="Img">
                                    </div>
                                </div>
                            </div>
                        </div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Main Wrapper -->

	</div>

	<div class="back-to-top">
		<a class="back-to-top-icon align-items-center justify-content-center d-flex" href="#top"><i
				class="fa-solid fa-arrow-up"></i></a>
	</div>

	<!-- Cursor -->
	<div class="xb-cursor tx-js-cursor">
		<div class="xb-cursor-wrapper">
			<div class="xb-cursor--follower xb-js-follower"></div>
		</div>
	</div>
	<!-- /Cursor -->

    <!-- Jquery JS -->
	<script src="{{ asset('front/js/jquery-3.7.1.min.js') }}"></script>

	<!-- jQuery validation -->
	<script src="{{ asset('assets/js/jquery-validation.min.js') }}"></script>
	<script src="{{ asset('assets/js/jquery-validation-additional-methods.min.js') }}"></script>

	<!-- Toastr JS -->
	<script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>

	<!-- Validation-->
	<script src="{{ asset('front/js/validation.min.js') }}"></script>

	@if ($isRTL)
	<!-- Bootstrap CSS -->
	<script src="{{ asset('front/js/scriptrtl.js') }}"></script>
	@else
	<!-- Bootstrap CSS -->
	<script src="{{ asset('front/js/script.js') }}"></script>
	@endif

	<script src="{{ asset('front/js/reset-password.js') }}"></script>

	<!-- Custom JS -->
	<script src="{{ asset('assets/js/custom.js') }}"></script>

</body>

</html>