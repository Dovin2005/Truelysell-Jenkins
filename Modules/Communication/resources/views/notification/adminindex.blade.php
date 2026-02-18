@extends('admin.admin')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between ">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1 translatable" data-translate="Email_Settings">{{ __('Notifications') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}" class="translatable"
                                    data-translate="Dashboard">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);">{{ __('application') }}</a>
                            </li>
                            <li class="breadcrumb-item active translatable" aria-current="page">{{ __('Notifications') }}
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row justify-content-center">
                @if ($data['notifications']->isEmpty())
                    <div class="card shadow-none booking-list h-80">
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <p class="text-center fw-bold w-100">{{ __('No notifications found.') }}</p>
                        </div>
                    </div>
                @else
                    <div class="col-xxl-12 col-md-12 d-flex">
                        <div class="flex-fill">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                </div>

                                <!-- Loop through notifications -->
                                @foreach ($data['notifications'] as $notification)
                                    <div class="card book-crd mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap row-gap-2">

                                                <!-- Sender's Details -->
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-lg flex-shrink-0 me-2">
                                                        @php
                                                            if ($data['auth_user'] == $notification->from_user_id || $data['auth_user']==$notification->to_user_id) {
                                                                $profileimage = $notification->from_profileimg;
                                                            } else {
                                                                $profileimage = $notification->to_profileimg;
                                                            }

                                                            if($data['auth_user']==$notification->from_user_id) {
                                                                $description=$notification->from_description;
                                                            } else {
                                                                $description=$notification->to_description;
                                                            }
                                                        @endphp
                                                        @if (file_exists(public_path('storage/profile/' . $profileimage)) && $profileimage)
                                                            <img src="{{ asset('storage/profile/' . $profileimage) }}"
                                                                alt="User Profile Image"
                                                                class="img-fluid rounded-circle profileImagePreview">
                                                        @else
                                                            <img src="{{ asset('assets/img/profile-default.png') }}"
                                                                alt="Default Profile Image"
                                                                class="img-fluid rounded-circle profileImagePreview">
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <div class="fw-medium fs-16 text-dark mb-2"><b>{{ $notification->source }}</b></div>
                                                        <div class="fs-14 text-dark">{{ $description }}</div>
                                                    </div>
                                                </div>
                                                <div class="align-items-end">
                                                    <span class="d-block fs-12 text-muted">
                                                        <i class="ti ti-calendar"></i> {{ $notification->notificationdate }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="d-flex justify-content-center mb-3">
                @if ($data['notifications']->isNotEmpty())
                    {{ $data['notifications']->links('pagination::bootstrap-4') }}
                @endif
            </div>
        </div>
    </div>
@endsection
