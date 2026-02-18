<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $companyName }}</title>

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

    <!-- Animation CSS -->
    <link rel="stylesheet" href="{{ asset('front/css/animate.css') }}">

    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="{{ asset('front/plugins/tabler-icons/tabler-icons.css') }}">

    <!-- Fontawesome Icon CSS -->
    <link rel="stylesheet" href="{{ asset('front/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('front/plugins/fontawesome/css/all.min.css') }}">

    <!-- Datepicker CSS -->
    <link rel="stylesheet" href="{{ asset('front/css/bootstrap-datetimepicker.min.css') }}">

    <!-- Toastr CSS -->
    <link href="{{ asset('assets/plugins/toastr/toatr.css') }}" rel="stylesheet">

    <!-- select CSS -->
    <link rel="stylesheet" href="{{ asset('front/plugins/select2/css/select2.min.css') }}">

    <!-- Owlcarousel CSS -->
    <link rel="stylesheet" href="{{ asset('front/plugins/owlcarousel/owl.carousel.min.css') }}">

    <!-- Datatable CSS -->
    <link rel="stylesheet" href="{{ asset('front/plugins/datatables/datatables.min.css') }}">

    <!-- Mobile CSS-->
    <link rel="stylesheet" href="{{ asset('front/plugins/intltelinput/css/intlTelInput.css') }}">

    <!-- Tagsinput CSS -->
    <link rel="stylesheet" href="{{ asset('front/plugins/bootstrap-tagsinput/css/bootstrap-tagsinput.css') }}">

    <!-- Feather CSS -->
    <link rel="stylesheet" href="{{ asset('front/css/feather.css') }}">

    <!-- Boxicons CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/boxicons/css/boxicons.min.css') }}">

    <!-- summernote CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/summernote/summernote-bs4.min.css') }}">

    @include('color.color-configuration')

    @if ($isRTL)
        <!-- Style CSS -->
        <link rel="stylesheet" href="{{ asset('front/css/stylertl.css') }}">
    @else
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="{{ asset('front/css/stylenew.css') }}">
    @endif

    <!-- Custom JS -->
    <script src="{{ asset('assets/js/custom.js') }}"></script>

</head>

<body data-provider="{{ Route::currentRouteName() }}" class="provider-page" data-lang="{{ app()->getLocale() }}"
    data-authid="{{ Auth::id() ?? '' }}" data-error="{{ session('error') }}">
    <div id="language-settings" data-language-id="{{ getLanguageId(app()->getLocale()) }}"></div>
    <div id="datatable_data" data-length_menu="{{ __('lengthMenu') }}" data-info="{{ __('info') }}"
        data-info_empty="{{ __('infoEmpty') }}" data-info_filter="{{ __('infoFiltered') }}"
        data-search="{{ __('search') }}" data-zero_records="{{ __('zeroRecords') }}"
        data-first="{{ __('first') }}" data-last="{{ __('last') }}" data-next="{{ __('next') }}"
        data-prev="{{ __('previous') }}"></div>
    <!-- Main Wrapper -->
    <div class="main-wrapper">
        @include('provider.partials.header')
        @yield('content')
        @include('provider.partials.footer')
    </div>
    <!-- /Main Wrapper -->

    <!-- Delete Account -->
    <div class="modal fade custom-modal" id="del-account">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                    <h5 class="modal-title">{{ __('Delete Account') }}</h5>
                    <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i
                            class="ti ti-circle-x-filled fs-20"></i></a>
                </div>
                <form id="deleteAccountForm">
                    <div class="modal-body">
                        <p class="mb-3">{{ __('delete_account_confirm') }}</p>
                        <div class="mb-0">
                            <label class="form-label">{{ __('Password') }}</label>
                            <div class="pass-group">
                                <input type="password" class="form-control pass-input" name="password" id="password"
                                    placeholder="*************">
                                <span class="toggle-password feather-eye-off"></span>
                            </div>
                            <span class="error-text text-danger" id="password_error"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="javascript:void(0);" class="btn btn-light me-2"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-dark" id="deleteAccountBtn" data-id="{{ Auth::id() }}"
                            data-delete="{{ __('Delete Account') }}"
                            data-password_required="{{ __('password_required') }}">{{ __('Delete Account') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /Delete Account -->

    @if (session('permission-error'))
        <div id="permissionError" data-error="{{ session('permission-error') }}"></div>
    @else
        <div id="permissionError" data-error=""></div>
    @endif

    <!-- Jquery JS -->
    <script src="{{ asset('front/js/jquery-3.7.1.min.js') }}"></script>

    <!-- jQuery validation -->
    <script src="{{ asset('assets/js/jquery-validation.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-validation-additional-methods.min.js') }}"></script>
    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-messaging-compat.js"></script>
    <script src="{{ asset('assets/js/fcmscript.js') }}"></script>
    <!-- Slimscroll JS -->
    <script src="{{ asset('front/js/jquery.slimscroll.min.js') }}"></script>

    <!-- Bootstrap JS -->
    <script src="{{ asset('front/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Wow JS -->
    <script src="{{ asset('front/js/wow.min.js') }}"></script>

    @if (module_view_exists('paymentgateway::script') && $PaymentGatewayStatus == 1)
        @include('paymentgateway::script')
    @endif

    <!-- Owlcarousel Js -->
    <script src="{{ asset('front/plugins/owlcarousel/owl.carousel.min.js') }}"></script>

    <!-- Toastr JS -->
    <script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>

    <!-- select JS -->
    <script src="{{ asset('front/plugins/select2/js/select2.min.js') }}"></script>

    <!-- Datatable JS -->
    <script src="{{ asset('front/plugins/datatables/datatables.min.js') }}"></script>

    <script src="{{ asset('front/js/cursor.js') }}"></script>

    <!-- Datepicker Core JS -->
    <script src="{{ asset('front/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('front/js/bootstrap-datetimepicker.min.js') }}"></script>

    <!-- Tagsinput JS -->
    <script src="{{ asset('front/plugins/bootstrap-tagsinput/js/bootstrap-tagsinput.js') }}"></script>

    <!-- Mobile Input -->
    <script src="{{ asset('front/plugins/intltelinput/js/intlTelInput.js') }}"></script>
    <script src="{{ asset('front/plugins/ityped/index.js') }}"></script>

    <!-- Validation-->
    <script src="{{ asset('front/js/validation.js') }}"></script>

    <!-- Script JS -->
    <script src="{{ asset('front/js/script.js') }}"></script>

    <!-- Home Page Script JS -->
    <script src="{{ asset('front/js/provider.js') }}"></script>

    @if (request()->routeIs('provider.calendar') || request()->routeIs('staff.calendar'))
        <!-- Calendar JS -->
        <script src="{{ asset('assets/plugins/fullcalendar/calendar.js') }}"></script>
        <script src="{{ asset('front/js/calendarscript.js') }}"></script>
        <script src="{{ asset('front/js/staffcalendarscript.js') }}"></script>
    @endif

    <!-- Custom JS -->
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="{{ asset('front/js/notify.js') }}"></script>

    <!-- summernote JS -->
    <script src="{{ asset('assets/plugins/summernote/summernote-bs4.min.js') }}"></script>

    @stack('scripts')
</body>

</html>
