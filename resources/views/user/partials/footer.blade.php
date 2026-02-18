<footer>
    <div class="footer-top" @if (request()->routeIs('user.booking.location.service_booking') || request()->routeIs('user.booking.service_booking')) style="display:none" @endif >
        <div class="container">
            <div class="row">
                @if (!empty($footerList))
                    @foreach ($footerList as $footerGroup)
                        @foreach ($footerGroup as $footer)
                            @if (!empty($footer['title']) && !empty($footer['footer_content']))
                                <div class="col-md-6 col-xl-2">
                                    <div class="footer-widget">
                                        <h5 class="mb-4">{{ $footer['title'] }}</h5>
                                        {!! $footer['footer_content'] !!}
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endforeach
                @endif

                <div class="col-md-12 col-xl-4">
                    <div class="footer-widget">
                        <div class="card bg-light-200 border-0 mb-3">
                            <div class="card-body">
                                <h5 class="mb-3">{{ __('SignUp For Subscription') }}</h5>
                                <form id="subscriberForm" autocomplete="off">
                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="subscriber_email"
                                            id="subscriber_email" placeholder="{{ __('Enter Email') }}">
                                        <span class="text-danger error-text" id="subscriber_email_error"></span>
                                    </div>
                                    <button type="submit" class="btn btn-lg btn-linear-primary w-100"
                                        id="subscriberBtn">{{ __('Subscribe') }}</button>
                                </form>
                            </div>
                        </div>
                        <div class="d-flex align-items-center ">
                            <h6 class="fs-14 fw-normal me-2">{{ __('Download Our App') }}</h6>
                            <img src="{{ asset('front/img/icons/app-store.svg') }}" class="me-2" alt="img">
                            <img src="{{ asset('front/img/icons/goolge-play.svg') }}" class="me-2" alt="img">
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="d-flex align-items-center justify-content-between flex-wrap mt-3">
                <ul class="social-icon mb-2">
                    @if(!empty($socialLinks) && count($socialLinks) > 0)
                        @foreach($socialLinks as $socialLink)
                            <li>
                                <a href="{{ $socialLink->link }}" target="_blank" class="ms-2" aria-label="{{ $socialLink->platform_name }}">
                                    <i class="{{ $socialLink->icon }}"></i>
                                </a>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <!-- /Copyright Menu -->
    <div class="footer-bottom" @if (request()->routeIs('user.booking.location.service_booking') || request()->routeIs('user.booking.service_booking')) style="display:none" @endif>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                        <div>
                            <div class="mb-2 text-start">{!! $copyRight !!}</div>
                        </div>
                        <ul class="menu-links mb-2">
                            <li>
                                <a href="{{ url('terms-conditions') }}">{{ __('Terms and Conditions') }}</a>
                            </li>
                            <li>
                                <a href="{{ url('privacy-policy') }}">{{ __('Privacy Policy') }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</footer>