<!-- Header -->
<div class="header">

    <!-- Logo -->
    <div class="header-left active">
        <a href="{{ route('admin.dashboard') }}" class="logo logo-normal">
            <img src="{{ $dynamicLogo }}" alt="Logo">
        </a>
        <a href="{{ route('admin.dashboard') }}" class="logo-small">
            <img src="{{ $dynamicSmallLogo }}" alt="Logo">
        </a>
        <a href="{{ route('admin.dashboard') }}" class="dark-logo">
            <img src="{{ $dynamicDarkLogo }}" alt="Logo">
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
            <div class="nav-item nav-search-inputs me-auto">
                <div class="top-nav-search">
                    <a href="javascript:void(0);" class="responsive-search">
                        <i class="fa fa-search"></i>
                    </a>

                </div>
            </div>
            <!-- /Search -->

            @if(isset($permission))
            @if(hasPermission($permission, 'Notifications', 'view'))
            <div class="provider-head-links">
                <a href="javascript:void(0);" class="d-flex align-items-center justify-content-center notify-link" data-bs-toggle="dropdown" data-bs-auto-close="outside"><i class="ti ti-bell bellcount"></i></a>
                <div class="dropdown-menu dropdown-menu-end notification-dropdown p-4 notify-users">
                    <div class="d-flex dropdown-body align-items-center justify-content-between border-bottom p-0 pb-3 mb-3">
                        <h6 class="notification-title">{{ __('Notifications') }} <span class="fs-18 text-gray notificationcount"></span></h6>
                        <div class="d-flex align-items-center">
                            <a class="text-primary fs-15 me-3 lh-1 markallread">{{ __('Mark all as read') }}</a>
                        </div>
                    </div>
                    <div class="noti-content">
                        <div class="d-flex flex-column" id="notification-data" data-empty_info="{{ __('No New Notification Found') }}">
                        </div>
                    </div>
                    <div class="d-flex p-0 notification-footer-btn">
                    </div>
                    <div class="d-flex p-0 notification-footer-btn">
                        <a href="#" class="btn btn-light rounded  me-2 cancel cancelnotify">{{ __('Cancel') }}</a>
                        <a href="{{route('admin.notification')}}" class="btn btn-dark rounded viewall">{{ __('View All') }}</a>
                    </div>
                </div>
            </div>
            @endif
            @endif
            <div class="pe-1 ms-1">
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
                    <ul class="dropdown-menu" aria-labelledby="languageDropdown">
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
            </div>
            @if(isset($permission))
            @if(hasPermission($permission, 'Chat', 'view'))
            <div class="pe-1">
                <a href="{{route('admin.chat')}}" class="btn btn-outline-light bg-white btn-icon position-relative me-1">
                    <i class="ti ti-brand-hipchat"></i>
                    <span class="chat-status-dot"></span>
                </a>
            </div>
            @endif
            @endif
            <div class="dropdown ms-1">
                <a href="javascript:void(0);" class="dropdown-toggle d-flex align-items-center"
                    data-bs-toggle="dropdown">
                    <span class="avatar avatar-md rounded">
                        <img src="{{ (\App\Models\UserDetail::getAdminImage()) }}" alt="Img" class="img-fluid headerProfileImg">
                    </span>
                </a>
                <div class="dropdown-menu">
                    <div class="d-block">
                        <hr class="m-0">
                        <a class="dropdown-item d-inline-flex align-items-center p-2" href="{{ route('admin.profile') }}">
                            <i class="ti ti-user-circle me-2"></i>{{ __('my_profile') }}</a>
                        <hr class="m-0">
                        <a id="logout-button" class="dropdown-item d-inline-flex align-items-center p-2" href="javascript:void(0);">
                            <i class="ti ti-login me-2"></i>{{ __('logout') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="dropdown mobile-user-menu">
        <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
            aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="{{ route('admin.profile') }}">{{ __('my_profile') }}</a>
            <a class="dropdown-item" href="#">{{ __('logout') }}</a>
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
                @if(isset($permission))

                @if (hasPermission($permission, 'Dashboard', 'view'))
                <li>
                    <h6 class="submenu-hdr"><span>{{ __('main') }} </span></h6>
                    <ul>
                        @if(hasPermission($permission, 'Dashboard', 'view'))
                        <li class="{{ request()->is('admin/dashboard') ? 'active' : '' }}"><a href="{{ route('admin.dashboard') }}"><i class="ti ti-layout-sidebar"></i><span>{{ __('Dashboard') }}
                                </span></a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if (hasPermission($permission, ['Bookings', 'Calendar', 'Chat', 'Leads', 'Service', 'Notifications'], 'view'))
                <li>
                    <h6 class="submenu-hdr"><span>{{ __('application') }}</span></h6>
                    <ul>
                        @if (hasPermission($permission, 'Bookings', 'view'))
                        <li class="{{ request()->is('admin/bookinglist') ? 'active' : '' }}"><a href="{{ route('admin.bookinglist') }}"> <i class="ti ti-list"></i><span>{{ __('Bookings') }}</span></a></li>
                        @endif

                        @if (hasPermission($permission, 'Calendar', 'view'))
                        <li class="{{ request()->is('admin/calendar') ? 'active' : '' }}"><a href="{{ route('admin.calendar') }}"> <i class="ti ti-calendar"></i><span>{{ __('calendar') }}</span></a></li>
                        @endif

                        @if (hasPermission($permission, 'Chat', 'view'))
                        <li class="{{ request()->is('admin/chat') ? 'active' : '' }}"><a href="{{ route('admin.chat') }}"> <i class="ti ti-message"></i><span>{{ __('Chat') }}</span></a></li>
                        @endif

                        @if (hasPermission($permission, 'Leads', 'view'))
                        @if($leadStatus != 0)
                        <li class="{{ request()->is('admin/leads') ? 'active' : '' }}">
                            <a href="{{ route('admin.leads') }}">
                                <i class="ti ti-world"></i>
                                <span>{{ __('Leads') }}</span>
                            </a>
                        </li>
                        @endif
                        @endif

                        @if (hasPermission($permission, ['Service', 'Categories', 'Addons'], 'view'))
                        <li class="submenu categories_tab">
                            <a href="javascript:void(0);" class="{{ request()->is('admin/services') || request()->is('admin/service/categories') || request()->is('admin/service/subcategories') ? 'subdrop active' : '' }}">
                                <i class="ti ti-book"></i><span>{{ __('Services') }}</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                @if (hasPermission($permission, 'Service', 'view'))
                                <li><a href="{{ route('admin.services') }}" class="{{ request()->is('admin/services') ? 'active' : '' }}">{{ __('Services') }}</a></li>
                                @endif
                                @if (hasPermission($permission, 'Categories', 'view'))
                                <li><a href="{{ route('admin.servicecategories') }}" class="{{ request()->is('admin/service/categories') ? 'active' : '' }}">{{ __('Category') }} </a></li>
                                <li><a href="{{ route('admin.servicesubcategories') }}" class="{{ request()->is('admin/service/subcategories') ? 'active' : '' }}">{{ __('sub_category') }} </a></li>
                                @endif
                            </ul>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Notifications', 'view') && Auth::user()->user_type == 1)
                        <li class="{{ request()->is('admin/notifications') ? 'active' : '' }}">
                            <a href="{{ route('admin.notification') }}">
                                <i class="ti ti-bell"></i>
                                <span>{{__('Notification')}}</span>
                            </a>
                        </li>
                        @endif
                        @if (hasPermission($permission, 'Notifications', 'view') && Auth::user()->user_type == 5)
                        <li class="{{ request()->is('admin/notifications') ? 'active' : '' }}">
                            <a href="{{ route('admin.notification') }}">
                                <i class="ti ti-bell"></i>
                                <span>{{__('Notification')}}</span>
                            </a>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Addons', 'view'))
                            <li class="{{ request()->is('admin/addon-modules') ? 'active' : '' }}">
                                <a href="{{ route('admin.addons') }}">
                                    <i class="ti ti-circle-plus"></i>
                                    <span>{{__('Addons')}}</span>
                                </a>
                            </li>
                        @endif

                        @if (isset($addonModules) && Auth::user()->user_type == 1)
                            @foreach ($addonModules as $addon)
                                @if (hasAddonModule($addonModules, $addon->name) && $addon->name !== 'PaymentGateway')
                                    <li class="{{ request()->is('admin/' . $addon->slug) ? 'active' : '' }}">
                                        <a href="/admin/{{ $addon->slug }}">
                                            <i class="ti ti-discount"></i>
                                            <span>{{ __($addon->name) }}</span>
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endif

                    </ul>
                </li>
                @endif

                @if (hasPermission($permission, ['Pages', 'Menu Builder', 'Footer Builder', 'Testimonials', 'FAQ', 'Newsletter', 'Blogs'], 'view'))
                <li>
                    <h6 class="submenu-hdr"><span>{{ __('content') }}</span></h6>
                    <ul>
                        @if (hasPermission($permission, 'Pages', 'view'))
                        <li class="submenu">
                            <a href="javascript:void(0);" class="{{ request()->is('admin/content/page-builder') || request()->is('admin/content/add/page-builder') || request()->is('admin/content/edit/page-builder') || request()->is('admin/content/page-section') || request()->is('admin/content/how-it-work') ? 'subdrop active' : '' }}">
                                <i class="ti ti-receipt"></i><span>{{ __('Pages') }}</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('admin.page-builder')}}" class="{{ request()->is('admin/content/page-builder') ? 'active' : '' }}">{{ __('Page Builder') }}</a></li>
                                <li><a href="{{ route('admin.page-section')}}" class="{{ request()->is('admin/content/page-section') ? 'active' : '' }}">{{ __('Sections') }}</a></li>
                                <li><a href="{{ route('admin.how-it-work') }}" class="{{ request()->is('admin/content/how-it-work') ? 'active' : '' }}">{{ __('how_it_work') }}</a></li>
                            </ul>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Menu Builder', 'view'))
                        <li class="{{ request()->is('admin/content/menu-builder') ? 'active' : '' }}">
                            <a href="{{ route('content.menu-builder') }}">
                                <i class="ti ti-layout-list"></i>
                                <span>{{ __('menu_builder') }}</span>
                            </a>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Footer Builder', 'view'))
                        <li class="{{ request()->is('admin/content/footer-builder') ? 'active' : '' }}">
                            <a href="{{ route('admin.footer-builder') }}">
                                <i class="ti ti-menu"></i>
                                <span>{{ __('footer_builder') }}</span>
                            </a>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Testimonials', 'view'))
                        <li class="{{ request()->is('admin/content/testimonials') ? 'active' : '' }}">
                            <a href="{{ route('admin.testimonials') }}">
                                <i class="ti ti-quote"></i>
                                <span>{{ __('testimonials') }}</span>
                            </a>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'FAQ', 'view'))
                        <li class="{{ request()->is('admin/content/faq') ? 'active' : '' }}">
                            <a href="{{ route('admin.faq') }}">
                                <i class="ti ti-help"></i>
                                <span>{{ __('faq') }}</span>
                            </a>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Newsletter', 'view'))
                        <li class="submenu">
                            <a href="javascript:void(0);" class="{{ request()->is('admin/content/subscriber-list')? 'subdrop active' : '' }}">
                                <i class="ti ti-news"></i><span>{{ __('newsletter') }}</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('admin.subscriber-list') }}" class="{{ request()->is('admin/content/subscriber-list') ? 'active' : '' }}">{{ __('subscriber_list') }}</a></li>
                            </ul>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Blogs', 'view'))
                        <li class="submenu">
                            <a href="javascript:void(0);" class="{{ request()->is('admin/content/blog-category') || request()->is('admin/content/blog-post') ? 'subdrop active' : '' }}">
                                <i class="ti ti-brand-blogger"></i><span>{{ __('blogs') }}</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('admin.blog-category') }}" class="{{ request()->is('admin/content/blog-category') ? 'active' : '' }}">{{ __('category_list') }}</a></li>
                                <li><a href="{{ route('admin.blog-post') }}" class="{{ request()->is('admin/content/blog-post') ? 'active' : '' }}">{{ __('post_list') }}</a></li>
                            </ul>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if (hasPermission($permission, ['Providers', 'Users', 'Staffs'], 'view'))
                <li>
                    <h6 class="submenu-hdr"><span>{{ __('people') }}</span></h6>
                    <ul>
                        @if (hasPermission($permission, 'Providers', 'view'))
                        <li class="{{ request()->is('admin/providers') ? 'active' : '' }}">
                            <a href="{{ route('admin.providerslist') }}">
                                <i class="ti ti-user-shield"></i>
                                <span>{{ __('providers') }}</span>
                            </a>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Users', 'view'))
                        <li class="{{ request()->is('admin/users') ? 'active' : '' }}">
                            <a href="{{ route('admin.userlist') }}">
                                <i class="ti ti-users"></i>
                                <span>{{ __('users') }}</span>
                            </a>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Staffs', 'view'))
                        <li class="{{ request()->is('admin/staffs') ? 'active' : '' }}">
                            <a href="{{ route('admin.staffs') }}">
                                <i class="ti ti-users-group"></i>
                                <span>{{ __('Staffs') }}</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if (hasPermission($permission, ['Transactions', 'Provider Earning', 'Provider Request', 'Refund', 'Subscription'], 'view'))
                <li>
                    <h6 class="submenu-hdr"><span>{{ __('finance') }}</span></h6>
                    <ul>
                        @if (hasPermission($permission, 'Transactions', 'view'))
                        <li class="{{ request()->is('admin/transaction') ? 'active' : '' }}">
                            <a href="{{ route('admin.transaction') }}">
                                <i class="ti ti-swipe"></i>
                                <span>{{ __('transactions') }}</span>
                            </a>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Provider Earning', 'view'))
                        <li class="{{ request()->is('admin/providertransaction') ? 'active' : '' }}">
                            <a href="{{ route('admin.providertransaction') }}">
                                <i class="ti ti-user-bolt"></i>
                                <span>{{ __('provider_earning') }}</span>
                            </a>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Provider Request', 'view'))
                        <li class="{{ request()->is('admin/providerrequest') ? 'active' : '' }}">
                            <a href="{{ route('admin.providerrequest') }}">
                                <i class="ti ti-user-question"></i>
                                <span>{{ __('provider_request') }}</span>
                            </a>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Refund', 'view'))
                        <li class="{{ request()->is('admin/refund') ? 'active' : '' }}">
                            <a href="{{ route('admin.refund') }}">
                                <i class="ti ti-clipboard-data"></i>
                                <span>{{ __('user_payout') }}</span>
                            </a>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Subscription', 'view'))
                        <li class="{{ request()->is('admin/subscriptionlist') ? 'active' : '' }}">
                            <a href="{{ route('admin.subscriptionlist') }}">
                                <i class="ti ti-license"></i>
                                <span>{{ __('subscription_list') }}</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if (hasPermission($permission, ['Tickets'], 'view'))
                <li>
                    <h6 class="submenu-hdr"><span>{{ __('Support') }}</span></h6>
                    <ul>
                        @if (hasPermission($permission, 'Tickets', 'view') && Auth::user()->user_type == 1 )
                        <li class="{{ request()->is('admin/tickets') ? 'active' : '' }}">
                            <a href="{{ route('admin.ticket') }}">
                                <i class="ti ti-ticket"></i>
                                <span>{{ __('Tickets') }}</span>
                            </a>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Tickets', 'view') && Auth::user()->user_type == 5)
                        <li class="{{ request()->is('staff/tickets') ? 'active' : '' }}">
                            <a href="{{ route('staff.tickets') }}">
                                <i class="ti ti-ticket"></i>
                                <span>{{ __('Tickets') }}</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if (hasPermission($permission, ['General Settings', 'Communication Settings'], 'view'))
                <li>
                    <h6 class="submenu-hdr"><span>{{ __('Settings') }}</span></h6>
                    <ul>
                        @if (hasPermission($permission, 'General Settings', 'view'))
                        <li class="{{ request()->is('admin/setting/*') ? 'active' : '' }}">
                            <a href="{{ route('admin.general-settings') }}">
                                <i class="ti ti-shield-cog"></i>
                                <span>{{ __('general_settings') }}</span>
                            </a>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Communication Settings', 'view'))
                        <li class="submenu">
                            <a href="javascript:void(0);" class="{{ request()->is('admin/settings/*') ? 'subdrop active' : '' }}">
                                <i class="ti ti-device-laptop"></i><span>{{ __('communication_settings') }}</span><span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('settings.email-settings') }}" class="{{ request()->is('admin/settings/email-settings') ? 'active' : '' }}">{{ __('email_settings') }}</a></li>
                                <li><a href="{{ route('settings.sms-settings') }}" class=" {{ request()->is('admin/settings/sms-settings') ? 'active' : '' }}">{{ __('sms_settings') }}</a></li>
                                <li><a href="{{ route('settings.notification-settings') }}" class="{{ request()->is('admin/settings/notification-settings') ? 'active' : '' }}">{{ __('notification_settings') }}</a></li>
                                <li><a href="{{ route('settings.email-templates') }}" class="{{ request()->is('admin/settings/email-templates') ? 'active' : '' }}"> {{ __('templates') }}</a></li>
                            </ul>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if (hasPermission($permission, ['Request Dispute', 'Reviews'], 'view'))
                <li>
                    <h6 class="submenu-hdr"><span>{{ __('feedback_disputes') }}</span></h6>
                    <ul>
                        @if (hasPermission($permission, 'Request Dispute', 'view'))
                        <li class="{{ request()->is('admin/request-dispute') ? 'active' : '' }}">
                            <a href="{{ route('admin.request.dispute') }}">
                                <i class="ti ti-receipt"></i>
                                <span>{{ __('request_dispute_list') }}</span>
                            </a>
                        </li>
                        @endif

                        @if (hasPermission($permission, 'Reviews', 'view'))
                        <li class="{{ request()->is('admin/reviews') ? 'active' : '' }}">
                            <a href="{{ route('admin.reviews') }}">
                                <i class="ti ti-star"></i>
                                <span>{{ __('Reviews') }}</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @if (hasPermission($permission, ['Roles & Permissions'], 'view'))
                <li>
                    <h6 class="submenu-hdr"><span>{{ __('User Management') }}</span></h6>
                    <ul>
                        @if (hasPermission($permission, 'Roles & Permissions', 'view'))
                        <li class="{{ request()->is('admin/roles-permissions') ? 'active' : '' }}">
                            <a href="{{ route('admin.roles-permissions') }}">
                                <i class="ti ti-shield-plus"></i>
                                <span>{{ __('Roles & Permissions') }}</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                @endif
            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->
