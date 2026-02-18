@extends('front')

@section('content')

<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}"><i
                                    class="ti ti-home-2"></i></a></li>
                        <li class="breadcrumb-item">{{__('user')}}</li>
                        <li class="breadcrumb-item active" aria-current="page">{{__('Security Settings')}}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="breadcrumb-bg">
            <img src="{{ asset('front/img/bg/breadcrumb-bg-01.png') }}" class="breadcrumb-bg-1" alt="Img">
            <img src="{{ asset('front/img/bg/breadcrumb-bg-02.png') }}" class="breadcrumb-bg-2" alt="Img">
        </div>
    </div>
</div>

<div class="page-wrapper">
    <div class="content">
        <div class="container">
            <div class="row justify-content-center">

                @include('user.partials.sidebar')

                <div class="col-xl-9 col-lg-8">
                    <h4 class="mb-3">{{__('Security Settings')}}</h4>

                    <div class="row">
                        <div class="col-xl-4 col-md-4 d-flex mb-3">
                            <div class="linked-item flex-fill">
                                <div class="linked-wrap">
                                    <div class="linked-acc">
                                        <span class="link-icon rounded-circle">
                                            <i class="ti ti-lock"></i>
                                        </span>
                                        <div class="linked-info">
                                            <h6 class="fs-16">{{__('Password Management')}}</h6>
                                            <p class="text-gray fs-12 text-truncate">{{__('Last Changed: ')}} <span class="text-dark fs-12">{{ $passwordLastSeenFormatted }}</span></p>
                                        </div>
                                    </div>
                                    <div class="linked-action">
                                        <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#change-password">{{__('Change Password')}}</button>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-xl-4 col-md-4 d-flex mb-3">
                            <div class="linked-item flex-fill">
                                <div class="linked-wrap">
                                    <div class="linked-acc">
                                        <span class="link-icon rounded-circle">
                                            <i class="ti ti-circle-check-filled text-success"></i>
                                        </span>
                                        <div class="linked-info">
                                            <h6 class="fs-16">{{ __('Device Management') }}</h6>
                                            <p class="text-gray fs-12 text-truncate">{{__('Last Changed: ')}}
                                                <span class="text-dark fs-12">
                                                    {{ $devices->first()->last_seen_formatted }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="linked-action">
                                        <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#device-management">{{__('Manage')}}</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="change-password" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between  border-0">
                <h5>{{__('Change Password')}}</h5>
                <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-circle-x-filled fs-20"></i></a>
            </div>
            <form id="changePasswordForm" class="mt-3 p-3">
                <input type="hidden" name="id" id="id" value="{{ $data }}">
                <div class="">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">{{__('Current Password')}} <span class="text-danger">
                                    *</span></label>
                            <div class="pass-group d-flex">
                                <input type="password" class="pass-input form-control" name="current_password"
                                    id="current_password">
                                <span class="ti toggle-password ti-eye-off"></span>
                            </div>
                            <span class="text-danger error-text" id="current_password_error"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{__('New Password')}} <span class="text-danger">
                                    *</span></label>
                            <div class="pass-group d-flex">
                                <input type="password" class="pass-inputs form-control" name="new_password"
                                    id="new_password">
                                <span class="ti toggle-passwords ti-eye-off"></span>
                            </div>
                            <span class="text-danger error-text" id="new_password_error"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{__('Confirm Password')}} <span class="text-danger">
                                    *</span></label>
                            <div class="pass-group d-flex">
                                <input type="password" class="pass-inputa form-control" name="confirm_password"
                                    id="confirm_password">
                                <span class="ti toggle-passworda ti-eye-off"></span>
                            </div>
                            <span class="text-danger error-text" id="confirm_password_error"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn bg-gray" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                    <button class="btn btn-dark" id="change_password" type="submit">{{__("Save Changes")}}</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="device-management" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body text-center">

                <div class="modal-header d-flex align-items-center justify-content-between border-0">
                    <h5>{{ __('Device Management') }}</h5>
                    <a href="javascript:void(0);" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-circle-x-filled fs-20"></i>
                    </a>
                </div>
                <input type="hidden" name="id" id="id" value="{{ $data }}">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>{{ __('browser') }}</th>
                                <th>{{ __('device') }}</th>
                                <th>{{ __('ip_address') }}</th>
                                <th>{{ __('Location') }}</th>
                                <th>{{ __('last_seen') }}</th>
                                <th>{{ __('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($devices->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center">{{ __('no_devices_found') }}</td>
                            </tr>
                            @else
                            @foreach ($devices as $device)
                            <tr>
                                <form class="delete-device-form" method="POST" action="/device/delete">
                                    @csrf
                                    <input type="hidden" name="device_id" value="{{ $device->id }}">
                                    <td>{{ $device->browser }}</td>
                                    <td>{{ $device->device }}</td>
                                    <td>{{ $device->ip_address }}</td>
                                    <td>{{ $device->location }}</td>
                                    <td>{{ \Carbon\Carbon::parse($device->last_seen)->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <button type="submit" class="btn btn-sm btn-danger">{{__('Delete')}}</button>
                                    </td>
                                </form>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gray" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection