<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<meta name="description" content="{{ $companyName }} Admin">
	<meta name="keywords" content="admin">
	<meta name="author" content="{{ $companyName }}">
	<meta name="robots" content="noindex, nofollow">
	<title>{{ $companyName }}</title>
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="{{ $dynamicFavicon }}">

	@php
		$isRTL = isRTL(app()->getLocale());
	@endphp

	@if ($isRTL)
		<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.rtl.min.css') }}">
	@else
		<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
	@endif

	<!-- Feather CSS -->
	<link rel="stylesheet" href="{{ asset('assets/plugins/icons/feather/feather.css') }}">

	<!-- Tabler Icon CSS -->
	<link rel="stylesheet" href="{{ asset('assets/plugins/tabler-icons/tabler-icons.css') }}">

	@if (request()->routeIs('admin.add_page_builder') || request()->routeIs('admin.edit_page_builder') || request()->routeIs('admin.form-categories'))
	<!-- Dragula CSS -->
	<link rel="stylesheet" href="{{asset('assets/plugins/dragula/css/dragula.min.css')}}">

	<link rel="stylesheet" href="{{asset('assets/js/jquery-ui.min.css')}}">
	@endif

	<!-- Fontawesome CSS -->
	<link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">

	@if (!request()->routeIs(['admin.dashboard', 'admin.calendar', 'admin.bookinglist', 'admin.chat']))
	<!-- Daterangepikcer CSS -->
	<link rel="stylesheet" href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css') }}">

	<!-- Datetimepicker CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">

	<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.css') }}">

	<!-- Mobile CSS-->
	<link rel="stylesheet" href="{{ asset('assets/plugins/intltelinput/css/intlTelInput.css') }}">

	<!-- summernote CSS -->
	<link rel="stylesheet" href="{{ asset('assets/plugins/summernote/summernote-bs5.min.css') }}">
	@endif

	@if (!request()->routeIs(['admin.dashboard', 'admin.calendar', 'admin.chat']))
	<link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap5.min.css') }}">
	@endif

	<!-- Toastr CSS -->
	<link href="{{ asset('assets/plugins/toastr/toatr.css') }}" rel="stylesheet">

	@if (request()->routeIs(['admin.add_page_builder', 'admin.edit_page_builder', 'admin.blog-post']))
	<!-- bootstrap-tagsinput CSS -->
	<link href="{{ asset('assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css') }}" rel="stylesheet">
	@endif

	<!-- Boxicons CSS -->
	<link rel="stylesheet" href="{{ asset('assets/plugins/boxicons/css/boxicons.min.css')}}">

	<!-- Swiper CSS -->
	<link rel="stylesheet" href="{{ asset('assets/plugins/swiper/swiper.min.css')}}">

	@include('color.color-configuration')

	<link rel="stylesheet" href="{{ asset('assets/css/menu-style.css') }}">

	<!-- Main CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

	<!-- Custom CSS -->
	<link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

</head>

<body data-page="{{ Route::currentRouteName() }}" class="{{ $isRTL ? 'layout-mode-rtl' : '' }}" data-lang="{{ app()->getLocale() }}" data-authid="{{ Auth::id() ?? '' }}">
	<div id="language-settings" data-language-id="{{ session('languageId', 'null') }}"></div>
	<div id="datatable_data" data-length_menu="{{ __('lengthMenu') }}" data-info="{{ __('info') }}"
		data-info_empty="{{ __('infoEmpty') }}" data-info_filter="{{ __('infoFiltered') }}"
		data-search="{{ __('search') }}" data-zero_records="{{ __('zeroRecords') }}" data-first="{{ __('first') }}"
		data-last="{{ __('last') }}" data-next="{{ __('next') }}" data-prev="{{ __('previous') }}"></div>

	<!-- Main Wrapper -->
	<div class="main-wrapper">
		@include('admin.partials.header')
		@yield('content')
		@include('admin.partials.footer')
	</div>
	<!-- /Main Wrapper -->
	
	<!-- jQuery -->
	<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>

	@if (!request()->routeIs(['admin.dashboard', 'admin.calendar', 'admin.bookinglist', 'admin.chat']))
	<!-- jQuery validation -->
	<script src="{{ asset('assets/js/jquery-validation.min.js') }}"></script>
	<script src="{{ asset('assets/js/jquery-validation-additional-methods.min.js') }}"></script>
	@endif

	<!-- Bootstrap Core JS -->
	<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

	@if (!request()->routeIs(['admin.dashboard', 'admin.calendar', 'admin.bookinglist', 'admin.chat']))
	<!-- Daterangepikcer JS -->
	<script src="{{ asset('assets/js/moment.js') }}"></script>
	<script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
	<script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
	@endif

	<!-- Feather Icon JS -->
	<script src="{{ asset('assets/js/feather.min.js') }}"></script>

	@if (request()->routeIs('admin.add_page_builder') || request()->routeIs('admin.edit_page_builder') || request()->routeIs('admin.form-categories'))
	<!-- Dragula JS -->
	<script src="{{asset('assets/plugins/dragula/js/dragula.min.js') }}"></script>
	<script src="{{asset('assets/plugins/dragula/js/drag-drop.min.js') }}"></script>
	<script src="{{asset('assets/plugins/dragula/js/draggable-cards.js') }}"></script>
	<script src="{{asset('assets/js/jquery-ui.min.js') }}"></script>
	@endif

	@if (request()->routeIs(['admin.payment-report']))
	<!-- Chart JS -->
	<script src="{{ asset('assets/plugins/apexchart/apexcharts.min.js') }}"></script>
	<script src="{{ asset('assets/plugins/apexchart/chart-data.js') }}"></script>
	@endif

	<!-- Slimscroll JS -->
	<script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}"></script>

	@if (!request()->routeIs(['admin.dashboard', 'admin.calendar', 'admin.bookinglist', 'admin.chat']))
	<!-- Select2 JS -->
	<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
	@endif

	@if (request()->routeIs(['admin.dashboard']))
	<!-- Counter JS -->
	<script src="{{ asset('assets/plugins/countup/jquery.counterup.min.js') }}"></script>
	<script src="{{ asset('assets/plugins/countup/jquery.waypoints.min.js') }}">	</script>
	@endif

	<!-- Toastr JS -->
	<script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>

	@if (!request()->routeIs(['admin.dashboard', 'admin.calendar', 'admin.chat']))
	<!-- Datatable JS -->
	<script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
	<script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}"></script>

	<!-- Mobile Input -->
	<script src="{{ asset('assets/plugins/intltelinput/js/intlTelInput.js') }}"></script>
	@endif

	<!-- Firebase SDKs -->
	<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
	<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-messaging-compat.js"></script>

	@if (request()->routeIs(['admin.add_page_builder', 'admin.edit_page_builder', 'admin.blog-post']))
	<!-- bootstrap-tagsinput JS -->
	<script src="{{ asset('assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js') }}"></script>
	@endif

	@if (request()->routeIs('content.menu-builder'))
		<script src="{{ asset('assets/js/menu-builder.js') }}"></script>
	@endif

	<script src="{{ asset('assets/js/fcmscript.js') }}"></script>

	<!-- Custom JS -->
	<script src="{{ asset('assets/js/script.js') }}"></script>

	<script src="{{ asset('assets/js/common.js') }}"></script>

	@if (request()->routeIs(['admin.transaction', 'admin.providertransaction', 'admin.providerrequest', 'admin.refund']))
	<script src="{{ asset('assets/js/finance.js') }}"></script>
	@endif
	@if (!request()->is('admin/setting/*') && !request()->routeIs(['admin.transaction', 'admin.providertransaction', 'admin.providerrequest', 'admin.refund', 'admin.chat', 'admin.leads', 'admin.leadsinfo']))
	<script src="{{ asset('assets/js/adminscript.js') }}"></script>
	@endif
	@if (request()->is('admin/setting/*'))
	<script src="{{ asset('assets/js/settingscript.js') }}"></script>
	@endif
	@if (request()->routeIs('admin.services') || request()->routeIs('admin.addservice') || request()->routeIs('editservice'))
		<script src="{{ asset('assets/js/servicescript.js') }}"></script>
	@endif
	@if (request()->routeIs('admin.servicecategories') || request()->routeIs('admin.form-categories') || request()->routeIs('admin.servicesubcategories'))
		<script src="{{ asset('assets/js/categoryscript.js') }}"></script>
	@endif
	@if (request()->routeIs('admin.leads') || request()->routeIs('admin.leadsinfo'))
		<script src="{{ asset('assets/js/leadsscript.js') }}"></script>
	@endif

	<!-- summernote JS -->
	<script src="{{ asset('assets/plugins/summernote/summernote-bs5.min.js') }}"></script>
	<!-- Slimscroll JS -->
	<script src="{{ asset('assets/js/jquery.slimscroll.min.js')}}"></script>

	<!-- Swiper JS -->
	<script src="{{ asset('assets/plugins/swiper/swiper.min.js')}}"></script>

	@if (request()->routeIs('admin.add_page_builder') || request()->routeIs('admin.edit_page_builder'))
		<!-- Sticky Sidebar JS -->
		<script src="{{ asset('assets/plugins/theia-sticky-sidebar/ResizeSensor.js')}}"></script>
		<script src="{{ asset('assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js')}}"></script>
	@endif

	@if (request()->routeIs('admin.calendar'))
		<!-- Calendar JS -->
		<script src="{{ asset('assets/plugins/fullcalendar/calendar.js')}}"></script>
		<script src="{{ asset('assets/js/calendarscript.js')}}"></script>
	@endif

	<!-- Custom JS -->
	<script src="{{ asset('assets/js/custom.js') }}"></script>

	@stack('scripts')
</body>

</html>