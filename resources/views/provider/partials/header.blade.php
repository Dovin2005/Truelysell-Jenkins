 <!-- Header -->
 <div class="header provider-header">

    <!-- Logo -->
    <div class="header-left active">
        <a href="{{ route('provider.dashboard') }}" class="logo logo-normal">
            <img src="{{ $dynamicLogo }}" alt="Logo">
        </a>
        <a href="{{ route('provider.dashboard') }}" class="logo-small">
            <img src="{{ $dynamicSmallLogo }}" alt="Logo">
        </a>
        <a id="toggle_btn" href="javascript:void(0);">
            <i class="ti ti-menu-deep"></i>
        </a>
    </div>
    <!-- /Logo -->

    <a id="mobile_btn" class="mobile_btn" href="#sidebar">
        <span class="bar-icon">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </a>

    <div class="header-user">
        <div class="nav user-menu">

            <!-- Search -->

            <!-- /Search -->
            <ul>
                <li class="d-none d-lg-block">
                    <div class="dropdown">
                        <button class="btn dropdown-toggle position-relative d-flex align-items-center language-selects" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            @php
                                $selectedLanguage = $languages->firstWhere('id', $selectedLanguageId);
                                $flagPath = "/front/img/flags/" . ($selectedLanguage->code ?? 'default') . ".png";
                                $flagPath = file_exists(public_path($flagPath)) ? $flagPath : "/front/img/flags/default.png";
                            @endphp
                            @if ($selectedLanguage)
                                <img src="{{ $flagPath }}" class="me-2" alt="Logo">

                            @else
                                 {{ __('Select Language') }}
                            @endif
                        </button>
                        <ul class="dropdown-menu dropdown-profile" aria-labelledby="languageDropdown">
                            @if ($languages->isNotEmpty())
                                @foreach ($languages as $language)
                                    @php
                                        $langFlagPath = "/front/img/flags/" . ($language->code ?? 'default') . ".png";
                                        $langFlagPath = file_exists(public_path($langFlagPath)) ? $langFlagPath : "/front/img/flags/default.png";
                                    @endphp
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center language-select" data-id="{{ $language->id }}" href="javascript:void(0);">
                                            <img src="{{ $langFlagPath }}" class="me-2" alt="Logo">
                                            {{ $language->name }}
                                        </a>
                                    </li>
                                @endforeach
                            @else
                                <li>
                                    <span class="dropdown-item disabled">Languages unavailable</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            </ul>

            <div class="d-flex align-items-center">
                <div class="me-2 site-link">
                    <a href="{{ route('home') }}" class="d-flex align-items-center justify-content-center mx-2"><i
                            class="feather-globe mx-1"></i>{{ __('Visit Website') }}</a>
                </div>
                <div class="provider-head-links">
                    <div>
                        <a href="javascript:void(0);" id="dark-mode-toggle" class="dark-mode-toggle me-2">
                            <i class="fa-regular fa-moon"></i>
                        </a>
                        <a href="javascript:void(0);" id="light-mode-toggle" class="dark-mode-toggle me-2">
                            <i class="ti ti-sun-filled"></i>
                        </a>
                    </div>
                </div>
                <div class="provider-head-links">
                    <a href="javascript:void(0);" class="d-flex align-items-center justify-content-center me-2  notify-link" data-bs-toggle="dropdown" data-bs-auto-close="outside"><i class="feather-bell bellcount"></i></a>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown p-4 notify-users">
                    <div class="d-flex dropdown-body align-items-center justify-content-between border-bottom p-0 pb-3 mb-3">
                        <h6 class="notification-title">{{ __('Notifications') }} <span class="fs-18 text-gray notificationcount"></span></h6>
                        <div class="d-flex align-items-center">
                            <a  class="text-primary fs-15 me-3 lh-1 markallread">{{ __('Mark all as read') }}</a>
                        </div>
                    </div>
                    <div class="noti-content">
                        <div class="d-flex flex-column"  id="notification-data" data-empty_info="{{ __('No New Notification Found') }}">
                        </div>
                    </div>
                    <div class="d-flex p-0 notification-footer-btn">
                    </div>
                    <div class="d-flex p-0 notification-footer-btn">
                        <a href="#" class="btn btn-light rounded  me-2 cancel cancelnotify">{{ __('Cancel') }}</a>
                        <a href="{{route('provider.notification')}}" class="btn btn-dark rounded viewall">{{ __('View All') }}</a>
                    </div>
                    </div>
                </div>
                <div class="dropdown">
                    <a href="javascript:void(0);" data-bs-toggle="dropdown">
                        <div class="booking-user d-flex align-items-center">
                            <span class="user-img">
                                <img src="{{ optional(Auth::user()->userDetails)->profile_image && file_exists(public_path('storage/profile/' . Auth::user()->userDetails->profile_image)) ? asset('storage/profile/' . Auth::user()->userDetails->profile_image) : asset('assets/img/profile-default.png') }}" class="headerProfileImg" alt="user">
                            </span>
                        </div>
                    </a>
                    <ul class="dropdown-menu p-2 dropdown-profile">
                        @if(isset($permission) && Auth::user()->user_type == 4)
                            @if(hasPermission($permission, 'Dashboard', 'view'))
                                <li><a class="dropdown-item d-flex align-items-center" href="{{ route('staff.dashboard') }}"><i class="ti ti-layout-grid me-1"></i>{{ __('Dashboard') }}</a></li>
                            @endif
                            @if(hasPermission($permission, 'Profile Settings', 'view'))
                                <li><a class="dropdown-item d-flex align-items-center" href="{{ route('provider.profile') }}"><i class="ti ti-user me-1"></i>{{ __('My Profile') }}</a></li>
                            @endif
                        @else
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('provider.dashboard') }}"><i class="ti ti-layout-grid me-1"></i>{{ __('Dashboard') }}</a></li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('provider.profile') }}"><i class="ti ti-user me-1"></i>{{ __('My Profile') }}</a></li>
                        @endif
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('logout') }}"><i class="ti ti-logout me-1"></i>{{ __('Logout') }}</a></li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="dropdown mobile-user-menu d-flex align-items-center w-auto">
        <div class="dropdown">
            <button class="btn dropdown-toggle d-flex align-items-center language-selects" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                @php
                    $selectedLanguage = $languages->firstWhere('id', $selectedLanguageId);
                    $flagPath = "/front/img/flags/" . ($selectedLanguage->code ?? 'default') . ".png";
                    $flagPath = file_exists(public_path($flagPath)) ? $flagPath : "/front/img/flags/default.png";
                @endphp
                @if ($selectedLanguage)
                    <img src="{{ $flagPath }}" class="me-2" alt="Logo">

                @else
                     {{ __('Select Language') }}
                @endif
            </button>
            <ul class="dropdown-menu dropdown-profile" aria-labelledby="languageDropdown">
                @if ($languages->isNotEmpty())
                    @foreach ($languages as $language)
                        @php
                            $langFlagPath = "/front/img/flags/" . ($language->code ?? 'default') . ".png";
                            $langFlagPath = file_exists(public_path($langFlagPath)) ? $langFlagPath : "/front/img/flags/default.png";
                        @endphp
                        <li>
                            <a class="dropdown-item d-flex align-items-center language-select" data-id="{{ $language->id }}" href="javascript:void(0);">
                                <img src="{{ $langFlagPath }}" class="me-2" alt="Logo">
                                {{ $language->name }}
                            </a>
                        </li>
                    @endforeach
                @else
                    <li>
                        <span class="dropdown-item disabled">Languages unavailable</span>
                    </li>
                @endif
            </ul>
        </div>
        <a href="javascript:void(0);" class="nav-link dropdown-toggle ms-2" data-bs-toggle="dropdown"
            aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
        <div class="dropdown-menu dropdown-menu-end">
            @if(isset($permission) && Auth::user()->user_type == 4)
                @if(hasPermission($permission, 'Dashboard', 'view'))
                    <a class="dropdown-item" href="{{ route('staff.dashboard') }}">{{ __('Dashboard') }}</a>
                @endif
                @if(hasPermission($permission, 'Profile Settings', 'view'))
                    <a class="dropdown-item" href="{{ route('provider.profile') }}">{{ __('My Profile') }}</a>
                @endif
            @else
                <a class="dropdown-item" href="{{ route('provider.dashboard') }}">{{ __('Dashboard') }}</a>
                <a class="dropdown-item" href="{{ route('provider.profile') }}">{{ __('My Profile') }}</a>
            @endif
            <a class="dropdown-item" href="{{ route('logout') }}">{{ __('Logout') }}</a>
        </div>
    </div>
    <!-- /Mobile Menu -->

</div>
<!-- /Header -->

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                @if(Auth::user()->user_type == 4)
                    @if(hasPermission($permission, 'Dashboard', 'view'))
                    <li class="{{ request()->is('staff/dashboard') ? 'active' : '' }}">
                        <a href="{{ route('staff.dashboard') }}" class="{{ request()->is('staff/dashboard') ? 'active' : '' }}"><i
                                class="ti ti-layout-grid"></i><span>{{__('Dashboard')}}</span></a>
                    </li>
                    @endif
                @else
                    <li class="{{ request()->is('provider/dashboard') ? 'active' : '' }}">
                        <a href="{{ route('provider.dashboard') }}" class="{{ request()->is('provider/dashboard') ? 'active' : '' }}"><i
                                class="ti ti-layout-grid"></i><span>{{__('Dashboard')}}</span></a>
                    </li>
                @endif

                @if(hasPermission($permission, 'Leads', 'view'))
                    @if($leadStatus != 0)
                    <li class="{{ request()->is('provider/leads*') ? 'active' : '' }}">
                        <a href="{{ route('provider.leads') }}" class="{{ request()->is('provider/leads*') ? 'active' : '' }}">
                            <i class="ti ti-world"></i><span>{{__('Leads')}}</span>
                        </a>
                    </li>
                    @endif
                @endif

                @if(hasPermission($permission, 'Transaction', 'view'))
                <li class="{{ request()->is('provider/transaction') ? 'active' : '' }}">
                    <a href="{{ route('provider.transaction') }}" class="{{ request()->is('provider/transaction') ? 'active' : '' }}">
                        <i class="ti ti-credit-card"></i><span>{{__('Transaction')}}</span>
                    </a>
                </li>
                @endif

                @if(hasPermission($permission, 'Payout', 'view'))
                <li class="{{ request()->is('provider/payouts') ? 'active' : '' }}">
                    <a href="{{ route('provider.payouts') }}"><i class="ti ti-wallet"></i><span>{{__('Payout')}}</span></a>
                </li>
                @endif

                @if(hasPermission($permission, 'Service', 'view'))
                <li class="{{ request()->is('provider/service') || request()->is('provider/service/create') || request()->is('provider/edit') ? 'active' : '' }}">
                    <a href="{{ route('provider.service') }}"><i class="ti ti-briefcase"></i><span>{{__('My Service')}}</span></a>
                </li>
                @endif

                @if(Auth::user()->user_type == 4)
                    @if(hasPermission($permission, 'Bookings', 'view'))
                        <li class="{{ request()->is('staff/bookinglist') ? 'active' : '' }}">
                                <a href="{{ route('staff.bookinglist') }}" class="{{ request()->is('staff/bookinglist') ? 'active' : '' }}"><i class="ti ti-calendar-month"></i><span>{{__('Bookings')}} </span></a>
                        </li>
                    @endif
                    @if(hasPermission($permission, 'Calendar', 'view'))
                        <li class="{{ request()->is('staff/calendar') ? 'active' : '' }}">
                            <a href="{{ route('staff.calendar') }}" class="{{ request()->is('staff/calendar') ? 'active' : '' }}"><i class="ti ti-calendar"></i><span>{{__('Calendar')}}</span></a>
                        </li>
                    @endif
                @else
                    <li class="{{ request()->is('provider/bookinglist') ? 'active' : '' }}">
                        <a href="{{ route('provider.bookinglist') }}" class="{{ request()->is('provider/bookinglist') ? 'active' : '' }}"><i class="ti ti-calendar-month"></i><span>{{__('Bookings')}} </span></a>
                    </li>
                    <li class="{{ request()->is('provider/calendar') ? 'active' : '' }}">
                        <a href="{{ route('provider.calendar') }}" class="{{ request()->is('provider/calendar') ? 'active' : '' }}"><i class="ti ti-calendar"></i><span>{{__('Calendar')}}</span></a>
                    </li>
                @endif

                @if(hasPermission($permission, 'Subscription', 'view'))
                <li class="{{ request()->is('provider/subscription') ? 'active' : '' }}">
                    <a href="{{ route('provider.subscription') }}" class="{{ request()->is('provider/subscription') ? 'active' : '' }}"><i class="ti ti-bell-plus"></i><span>{{__('Subscription')}}</span></a>
                </li>
                @endif

                @if(hasPermission($permission, 'Reviews', 'view'))
                <li  class="{{ request()->is('provider/reviews') ? 'active' : '' }}">
                    <a href="{{ route('provider.reviews') }}"><i class="ti ti-star"></i><span>{{__('Reviews')}}</span></a>
                </li>
                @endif

                @if(hasPermission($permission, 'Chat', 'view'))
                <li class="{{ request()->is('provider/chat') ? 'active' : '' }}">
                    <a href="{{ route('providers.chat') }}" 
                    class="d-flex align-items-center justify-content-between {{ request()->is('provider/chat') ? 'active' : '' }}">

                        <span class="d-flex align-items-center">
                            <i class="ti ti-message me-2"></i>
                            {{ __('Chat') }}
                        </span>

                        <span id="sidebarProviderChatBadge" class="badge bg-danger text-white d-none"></span>
                    </a>
                </li>
                @endif

                @if(hasPermission($permission, 'Notifications', 'view'))
                <li class="{{ request()->is('provider/notifications') ? 'active' : '' }}">
                    <a href="{{ route('provider.notification') }}" class="d-flex align-items-center {{ request()->is('provider/notifications') ? 'active' : '' }}">
                        <i class="ti ti-bell me-2"></i>
                        <span>{{__('Notification')}}</span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->user_type == 4)
                    @if(hasPermission($permission, 'Tickets', 'view'))
                    <li class="{{ request()->is('staff/ticket') ? 'active' : '' }}">
                        <a href="{{ route('staff.ticket') }}" class="{{ request()->is('staff/ticket') ? 'active' : '' }}"><i class="ti ti-ticket"></i><span>{{__('Tickets')}}</span></a>
                    </li>
                    @endif
                @else
                    <li class="{{ request()->is('provider/ticket') ? 'active' : '' }}">
                        <a href="{{ route('provider.ticket') }}" class="{{ request()->is('provider/ticket') ? 'active' : '' }}"><i class="ti ti-ticket"></i><span>{{__('Tickets')}}</span></a>
                    </li>
                @endif

                @if(hasPermission($permission, 'Branch', 'view'))
                <li  class="{{ request()->is('provider/branch') || request()->is('provider/add-branch') || request()->is('provider/edit-branch/*') ? 'active' : '' }}">
                    <a href="{{ route('provider.branch') }}"><i class="ti ti-git-branch"></i><span>{{__('Branch')}}</span></a>
                </li>
                @endif

                @if(hasPermission($permission, 'Staff', 'view'))
                <li  class="{{ request()->is('provider/staff-list') ? 'active' : '' }}">
                    <a href="{{ route('provider.staffs') }}"><i class="ti ti-users"></i><span>{{__('Staffs')}}</span></a>
                </li>
                @endif

                @if(hasPermission($permission, 'Roles & Permission', 'view'))
                <li  class="{{ request()->is('provider/roles-permissions') ? 'active' : '' }}">
                    <a href="{{ route('provider.roles-permissions') }}"><i class="ti ti-shield-plus"></i><span>{{__('Roles & Permissions')}}</span></a>
                </li>
                @endif

                @if (isset($addonModules) && Auth::user()->user_type == 2)
                    @foreach ($addonModules as $addon)
                        @if (hasAddonModule($addonModules, $addon->name) && $addon->name !== 'PaymentGateway')
                        <li class="{{ request()->is('provider/'. $addon->slug) ? 'active' : '' }}">
                            <a href="/provider/{{ $addon->slug }}">
                                <i class="ti ti-circle-plus"></i>
                                <span>{{__($addon->name)}}</span>
                            </a>
                        </li>
                        @endif
                    @endforeach
                @endif
                <li class="submenu">
                    <a href="javascript:void(0);"><i class="ti ti-settings"></i><span>{{__('Settings')}}</span><span
                            class="menu-arrow"></span></a>
                    <ul>
                        <li class="{{ request()->is('provider/profile') ? 'active' : '' }}">
                            <a href="{{ route('provider.profile') }}" class="{{ request()->is('provider/profile') ? 'active' : '' }}">
                                <i class="ti ti-chevrons-right me-1"></i>{{__('Profile Settings')}}
                            </a>
                        </li>

                        @if(hasPermission($permission, 'Security Settings', 'view'))
                        <li class="{{ request()->is('provider/security') ? 'active' : '' }}">
                            <a href="{{ route('provider.security') }}" class="{{ request()->is('provider/security') ? 'active' : '' }}">
                                <i class="ti ti-chevrons-right me-1"></i>{{__('Security Settings')}}
                            </a>
                        </li>
                        @endif

                        @if(hasPermission($permission, 'Plan & Billings', 'view'))
                        <li class="{{ request()->is('provider/subscriptionhistory') ? 'active' : '' }}">
                            <a href="{{ route('provider.subscriptionhistory') }}" class="{{ request()->is('provider/subscriptionhistory') ? 'active' : '' }}">
                                <i class="ti ti-chevrons-right me-1"></i>{{__('Plan & Billings')}}
                            </a>
                        </li>
                        @endif

                        @if(hasPermission($permission, 'Social Media Links', 'view'))
                        <li class="{{ request()->is('provider/social-links') ? 'active' : '' }}">
                            <a href="{{ route('provider.sociallinks.index') }}" class="{{ request()->is('provider/social-links') ? 'active' : '' }}">
                                <i class="ti ti-chevrons-right me-1"></i>{{__('social_media_links')}}
                            </a>
                        </li>
                        @endif
                        <li>
                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#del-account" id="del_account_btn"><i class="ti ti-chevrons-right me-2"></i>{{__('Delete Account')}}</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="{{ route('logout') }}"><i class="ti ti-logout-2"></i><span>{{__('Logout')}}</span></a>
                </li>
            </ul>
        </div>
    </div>
</div>