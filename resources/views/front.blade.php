<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ trim($__env->yieldContent('description')) ?: '' }}">
    <meta name="keywords" content="{{ trim($__env->yieldContent('keywords')) ?: '' }}">
    <meta property="og:image" content="{{ $dynamicLogo }}">
    <title>
        @if (trim($__env->yieldContent('title')))
            @yield('title')
        @else
            {{ $companyName }}
        @endif
    </title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ $dynamicFavicon }}">
    <link rel="icon" href="{{ $dynamicFavicon }}" sizes="any">

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

    <!-- Datepicker CSS -->
    @if (!request()->routeIs(['productdetail', 'home']))
        <link rel="stylesheet" href="{{ asset('front/css/bootstrap-datetimepicker.min.css') }}">
    @endif

    <!-- Toastr CSS -->
    <link href="{{ asset('assets/plugins/toastr/toatr.css') }}" rel="stylesheet">

    <!-- Animation CSS -->
    <link rel="stylesheet" href="{{ asset('front/css/animate.css') }}">

    @if (!request()->routeIs(['productdetail', 'home']))
        <link rel="stylesheet" href="{{ asset('front/css/bootstrap-datetimepicker.min.css') }}">
    @endif

    <!-- Tabler Icon CSS -->
    <link rel="stylesheet" href="{{ asset('front/plugins/tabler-icons/tabler-icons.css') }}">

    <!-- Fontawesome Icon CSS -->
    <link rel="stylesheet" href="{{ asset('front/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('front/plugins/fontawesome/css/all.min.css') }}">

    <!-- select CSS -->
    <link rel="stylesheet" href="{{ asset('front/plugins/select2/css/select2.min.css') }}">

    @if (request()->routeIs('productlists') || request()->routeIs('productlistcategory'))
        <!-- Rangeslider CSS -->
        <link rel="stylesheet" href="{{ asset('front/plugins/ion-rangeslider/css/ion.rangeSlider.min.css') }}">
    @endif

    @if (!request()->routeIs(['home', 'productlists', 'productlistcategory']))
        <!-- summernote CSS -->
        <link rel="stylesheet" href="{{ asset('assets/plugins/summernote/summernote-bs4.min.css') }}">
    @endif

    <!-- Owlcarousel CSS -->
    @if ($isRTL)
        <link rel="stylesheet" href="{{ asset('front/plugins/owlcarousel/owlrtl.carousel.min.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('front/plugins/owlcarousel/owl.carousel.min.css') }}">
    @endif

    @if (request()->routeIs('user.profile'))
        <!-- Tagsinput CSS -->
        <link rel="stylesheet" href="{{ asset('front/plugins/bootstrap-tagsinput/css/bootstrap-tagsinput.css') }}">
    @endif

    <!-- Fancybox -->
    <link rel="stylesheet" href="{{ asset('front/plugins/fancybox/jquery.fancybox.min.css') }}">

    <!-- Mobile CSS-->
    <link rel="stylesheet" href="{{ asset('front/plugins/intltelinput/css/intlTelInput.css') }}">

    @if (!request()->routeIs('home'))
        <!-- Datatable CSS -->
        <link rel="stylesheet" href="{{ asset('front/plugins/datatables/datatables.min.css') }}">
    @endif

    <!-- Feather CSS -->
    <link rel="stylesheet" href="{{ asset('front/css/feather.css') }}">
    <!-- Boxicons CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/boxicons/css/boxicons.min.css') }}">

	@include('color.color-configuration')

    <!-- Bootstrap CSS -->
    @if ($isRTL)
        <link rel="stylesheet" href="{{ asset('front/css/stylertl.css?v=2.3') }}">
    @else
        <link rel="stylesheet" href="{{ asset('front/css/stylenew.css?v=2.3') }}">
    @endif

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
</head>

<body data-frontend="{{ Route::currentRouteName() }}" data-lang="{{ app()->getLocale() }}"
    data-authid="{{ Auth::id() ?? '' }}" data-language_id="{{ getLanguageId(app()->getLocale()) }}"
    data-error="{{ session('error') }}">
    @include('cookie.cookie-consent.index')
    <div id="pageLoader" class="loader_front">
        <div class="loader-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ __('loading') }}</span>
            </div>
            <p>{{ __('sending_otp_please_wait') }}</p>
        </div>
    </div>

    <div id="NewletterpageLoader" class="loader_front">
        <div class="loader-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ __('loading') }}</span>
            </div>
            <p>{{ __('sending_newsletter_please_wait') }}</p>
        </div>
    </div>


    @include('user.partials.header')
    @yield('content')
    @if ($addvertismentStatus === 1 && View::exists('advertisement::advertisement.ad'))
        @include('advertisement::advertisement.ad')
    @endif
    @include('user.partials.footer')

    @include('user.auth.login')
    @include('user.auth.user_register')
    @include('user.auth.provider_register')
    <div id="language-settings" data-language-id="{{ $selectedLanguageId }}"></div>
    <div id="lead-settings" data-lead-status="{{ $leadStatus }}"></div>
    <div id="datatable_data" data-length_menu="{{ __('lengthMenu') }}" data-info="{{ __('info') }}"
        data-info_empty="{{ __('infoEmpty') }}" data-info_filter="{{ __('infoFiltered') }}"
        data-search="{{ __('search') }}" data-zero_records="{{ __('zeroRecords') }}"
        data-first="{{ __('first') }}" data-last="{{ __('last') }}" data-next="{{ __('next') }}"
        data-prev="{{ __('previous') }}"></div>

    <div class="modal fade" id="newsletter_success_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="mb-4">
                        <span class="success-icon mx-auto mb-4">
                            <i class="ti ti-check"></i>
                        </span>
                        <h4 class="mb-1">{{ __('Newsletter Submission Successful') }}</h4>
                        <p>{{ __('newsletter_success_description') }}</p>
                    </div>
                    <a href="#!" data-bs-dismiss="modal" class="btn btn-linear-primary">{{ __('Close') }}</a>
                </div>
            </div>
        </div>
    </div>

    <!-- success message Modal -->
    <div class="modal fade" id="success-modal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center justify-content-end pb-0 border-0">
                    <a href="#!" data-bs-dismiss="modal" aria-label="Close"><i
                            class="ti ti-circle-x-filled fs-20"></i></a>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center">
                        <span class="success-check mb-3 mx-auto"><i class="ti ti-check"></i></span>
                        <h4 class="mb-2">{{ __('Success') }}</h4>
                        <p>{{ __('Your new password has been successfully saved') }}</p>
                        <div>
                            <button type="submit"
                                class="btn btn-lg btn-linear-primary w-100">{{ __('Back to Sign In') }}</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- /success message Modal -->

    <!-- Delete Account -->
    <div class="modal fade custom-modal" id="del-account">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center justify-content-between border-bottom">
                    <h5 class="modal-title">{{ __('Delete Account') }}</h5>
                    <a href="#!" data-bs-dismiss="modal" aria-label="Close"><i
                            class="ti ti-circle-x-filled fs-20"></i></a>
                </div>
                <form id="deleteAccountForm" autocomplete="off">
                    <div class="modal-body">
                        <p class="mb-3">{{ __('delete_account_confirm') }}</p>
                        <div class="mb-0">
                            <label class="form-label">{{ __('Password') }}</label>
                            <div class="pass-group">
                                <input type="password" class="form-control pass-input" name="password"
                                    id="password_del" placeholder="*************">
                                <span class="toggle-password feather-eye-off"></span>
                            </div>
                            <span class="error-text text-danger" id="password_del_error"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#!" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-dark" id="deleteAccountBtn"
                            data-id="{{ Auth::id() }}" data-delete="{{ __('Delete Account') }}"
                            data-password_required="{{ __('password_required') }}">{{ __('Delete Account') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Delete Account -->

    <div class="back-to-top">
        <a class="back-to-top-icon align-items-center justify-content-center d-flex" href="#top">
            <i class="fa-solid fa-arrow-up"></i>
        </a>
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

    @if (Auth::check())
        <!-- Firebase SDKs -->
        <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
        <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-messaging-compat.js"></script>
        <script src="{{ asset('assets/js/fcmscript.js') }}"></script>
    @endif

    @if (!request()->routeIs(['home', 'productlists', 'productlistcategory']))
        <!-- summernote JS -->
        <script src="{{ asset('assets/plugins/summernote/summernote-bs4.min.js') }}"></script>
    @endif
    <script src="{{ asset('vendor/smart-ads/js/smart-banner.min.js') }}"></script>

    <!-- Slimscroll JS -->
    <script src="{{ asset('front/js/jquery.slimscroll.min.js') }}"></script>

    @if (module_view_exists('paymentgateway::script') && $PaymentGatewayStatus == 1)
        @include('paymentgateway::script')
    @endif

    <!-- Bootstrap JS -->
    <script src="{{ asset('front/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('front/js/moment.min.js') }}"></script>

    <!-- Wow JS -->
    @if (request()->routeIs('home'))
        <script src="{{ asset('front/js/wow.min.js') }}"></script>
    @endif

    <!-- Owlcarousel Js -->
    <script src="{{ asset('front/plugins/owlcarousel/owl.carousel.min.js') }}"></script>

    <!-- select JS -->
    <script src="{{ asset('front/plugins/select2/js/select2.min.js') }}"></script>

    @if (!request()->routeIs('home'))
        <!-- Datatable JS -->
        <script src="{{ asset('front/plugins/datatables/datatables.min.js') }}"></script>
    @endif

    <!-- Sticky Sidebar JS -->
    <script src="{{ asset('front/plugins/theia-sticky-sidebar/ResizeSensor.js') }}"></script>
    <script src="{{ asset('front/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js') }}"></script>

    <!-- Rangeslider JS -->
    @if (request()->routeIs('productlists') || request()->routeIs('productlistcategory'))
        <script src="{{ asset('front/plugins/ion-rangeslider/js/custom-rangeslider.js') }}"></script>
        <script src="{{ asset('front/plugins/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>
    @endif

    <!-- counterup JS -->
    <script src="{{ asset('front/js/cursor.js') }}"></script>

    @if (!request()->routeIs(['productdetail', 'home']))
        <!-- Datepicker JS -->
        <script src="{{ asset('front/js/bootstrap-datetimepicker.min.js') }}"></script>
    @endif

    <!-- Toastr JS -->
    <script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>

    @if (!request()->routeIs('home'))
        <!-- FancyBox JS -->
        <script src="{{ asset('front/plugins/fancybox/jquery.fancybox.min.js') }}"></script>
    @endif

    @if (request()->routeIs('user.profile'))
        <!-- Tagsinput JS -->
        <script src="{{ asset('front/plugins/bootstrap-tagsinput/js/bootstrap-tagsinput.js') }}"></script>
    @endif

    <!-- Mobile Input -->
    <script src="{{ asset('front/plugins/intltelinput/js/intlTelInput.js') }}"></script>
    <script src="{{ asset('front/plugins/intltelinput/js/utils.js') }}"></script>
    <script src="{{ asset('front/plugins/ityped/index.js') }}"></script>
    <!-- Validation-->
    <script src="{{ asset('front/js/validation.min.js') }}"></script>

    @if ($isRTL)
        <!-- Bootstrap CSS -->
        <script src="{{ asset('front/js/scriptrtl.js') }}"></script>
    @else
        <!-- Bootstrap CSS -->
        <script src="{{ asset('front/js/script.js') }}"></script>
    @endif

    <!-- Home Page Script JS -->
    @if (request()->routeIs('home'))
        <script src="{{ asset('front/js/home-page.js') }}"></script>
    @endif

    <!-- User Script JS -->
    <script src="{{ asset('front/js/user-script.js') }}"></script>

    @if (!Auth::check())
        <!-- User Regsiter Script JS -->
        <script src="{{ asset('front/js/user-register.js') }}"></script>

        <!-- User Login Script JS -->
        <script src="{{ asset('front/js/user-login.js') }}"></script>

        <!-- User Login Script JS -->
        <script src="{{ asset('front/js/provider-register.js') }}"></script>
    @endif
    @if (!request()->routeIs('home') && Auth::check())
        <script src="{{ asset('front/js/notify.js') }}"></script>
    @endif


    <!-- Custom JS -->
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="{{ asset('assets/js/booking.js') }}"></script>

    @stack('scripts')
</body>

</html>
