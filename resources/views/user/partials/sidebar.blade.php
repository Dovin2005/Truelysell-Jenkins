<div class="col-xl-3 col-lg-4 theiaStickySidebar">
    <div class="card user-sidebar mb-4 mb-lg-0">
        <div class="card-header user-sidebar-header mb-4">
            <div class="d-flex justify-content-center align-items-center flex-column">
                <span class="user rounded-circle avatar avatar-xxl mb-2">
                    @if (!empty(Auth::user()->userDetails->profile_image) && file_exists(public_path('storage/profile/' . Auth::user()->userDetails->profile_image)))
                        <img src="{{ optional(Auth::user()->userDetails)->profile_image ? asset('storage/profile/' . Auth::user()->userDetails->profile_image) : asset('assets/img/profile-default.png') }}" class="img-fluid rounded-circle headerProfileImg" alt="user">
                    @else
                        <img src="{{ asset('assets/img/profile-default.png') }}" alt="Default Profile Image" class="img-fluid rounded-circle headerProfileImg">
                    @endif
                </span>
                <h6 class="mb-2 headerName">{{ (Auth::user()->userDetails->first_name ?? '') }} {{(Auth::user()->userDetails->last_name ?? '')}}</h6>
                <p class="fs-14"> {{__('Member Since')}} {{ \Carbon\Carbon::parse(Auth::user()->created_at)->format('M Y') }}</p>
            </div>
        </div>
        <div class="card-body user-sidebar-body p-0">
            <ul>
                <li class="mb-4">
                    <a href="{{ route('user.dashboard') }}" class="d-flex align-items-center  {{ request()->is('user/dashboard') ? 'active' : '' }}">
                        <i class="ti ti-layout-grid me-2"></i>
                        {{__('Dashboard')}}
                    </a>
                </li>
                <li class="mb-4">
                    <a href="{{ route('user.bookinglist') }}" class="d-flex align-items-center {{ request()->is('user/bookinglist') ? 'active' : '' }}">
                        <i class="ti ti-device-mobile me-2"></i>
                        {{__('Bookings')}}
                    </a>
                </li>
                <li class="mb-4">
                    <a href="{{ route('users.chat') }}" 
                    class="d-flex align-items-center justify-content-between {{ request()->is('user/chat') ? 'active' : '' }}">

                        <div class="d-flex align-items-center">
                            <i class="ti ti-message-circle me-2"></i>
                            {{ __('Chat') }}
                        </div>

                        <span id="sidebarCustomerChatBadge" class="badge bg-danger text-white ms-2 d-none"></span>
                    </a>
                </li>
                @if($leadStatus != 0)
                <li class="mb-4">
                    <a href="{{ route('user.leads') }}" class="d-flex align-items-center {{ request()->is('user/leads*') ? 'active' : '' }}">
                        <i class="ti ti-world me-2"></i>
                        {{__('Leads')}}
                    </a>
                </li>
                @endif
                <li class="mb-4">
                    <a href="{{ route('user.transaction') }}" class="d-flex align-items-center {{ request()->is('user/transaction') ? 'active' : '' }}">
                        <i class="ti ti-credit-card me-2"></i>
                        {{__('Transaction')}}
                    </a>
                </li>
                <li class="mb-4">
                    <a href="{{ route('user.wallet') }}" class="d-flex align-items-center {{ request()->is('user/wallet') ? 'active' : '' }}">
                        <i class="ti ti-wallet me-2"></i>
                        {{__('Wallet')}}
                    </a>
                </li>
                <li class="mb-4">
                    <a href="{{ route('user.notification') }}" class="d-flex align-items-center {{ request()->is('user/notifications') ? 'active' : '' }}">
                        <i class="ti ti-bell me-2"></i>
                        {{__('Notification')}}
                    </a>
                </li>
                <li class="mb-4">
                    <a href="{{ route('user.ticket') }}" class="d-flex align-items-center {{ request()->is('user/ticket') ? 'active' : '' }}">
                        <i class="ti ti-ticket me-2"></i>
                        {{__('Tickets')}}
                    </a>
                </li>
                <li class="submenu mb-4">
                    <a href="javascript:void(0);" class="d-block mb-3"><i class="ti ti-settings me-2"></i><span>{{__('Settings')}}</span><span class="menu-arrow"></span></a>
                    <ul class="ms-4">
                        <li class="mb-3">
                            <a href="{{ route('user.profile') }}" class="fs-14 d-inline-flex align-items-center {{ request()->is('user/profile') ? 'active' : '' }}"><i class="ti ti-chevrons-right me-2"></i>{{__('Profile Settings')}}</a>
                        </li>
                        <li class="mb-3">
                            <a href="{{ route('user.security') }}" class="fs-14 d-inline-flex align-items-center {{ request()->is('user/security') ? 'active' : '' }}"><i class="ti ti-chevrons-right me-2"></i>{{__('Security Settings')}}</a>
                        </li>
                        <li class="mb-3">
                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#del-account" class="fs-14" id="del_account_btn"><i class="ti ti-chevrons-right me-2"></i>{{__('Delete Account')}}</a>
                        </li>
                    </ul>
                </li>
                <li class="mb-0">
                    <a href="{{ route('logout') }}" class="d-flex align-items-center">
                        <i class="ti ti-logout me-2"></i>
                        {{__('Logout')}}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
