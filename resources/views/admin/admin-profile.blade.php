@extends('admin.admin')

@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{__('Profile')}}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{__('Dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{__('Settings')}}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{__('Profile')}}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    <button type="submit" class="btn btn-primary" id="save_admin_profile" data-save="{{ __('Save') }}" data-save_success="{{ __('profile_save_success') }}">{{__('Save')}}</button>
                </div>
            </div>
        </div>
        <div class="d-md-flex d-block mt-3">
            <div class="flex-fill ps-0 border-0">
                <div class="d-md-flex">
                    <div class="flex-fill">
                        <form id="adminProfileForm">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5>{{__('Personal_Information')}}</h5>
                                </div>
                                <input type="hidden" name="id" id="id" value="{{ $data->id ?? '' }}">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="mb-3">
                                            <label class="form-label">{{__('image')}}</label>
                                            <div class="d-flex align-items-center flex-wrap row-gap-3 mb-3">
                                                <div
                                                    class="d-flex align-items-center justify-content-center avatar avatar-xxl border border-dashed me-2 flex-shrink-0 text-dark frames">
                                                    <img id="imagePreview" src="{{ $data->userDetails->profile_image ?? asset('assets/img/user-default.jpg')}}" width="100px" height="100px">
                                                </div>
                                                <div class="profile-upload">
                                                    <div class="profile-uploader d-flex align-items-center">
                                                        <div class="drag-upload-btn mb-3">
                                                            {{__('upload')}}
                                                            <input type="file" class="form-control image-sign" name="profile_image" id="profile_image">
                                                        </div>
                                                    </div>
                                                    <p>{{__('image_size_note')}}</p>
                                                </div>
                                            </div>
                                            <span class="text-danger error-text" id="profile_image_error" data-size="{{ __('image_filesize') }}" data-extension="{{ __('image_extension') }}"></span>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">{{__('first_name')}}<span class="text-danger"> *</span></label>
                                                <input type="text" class="form-control" name="first_name" id="first_name" placeholder="{{ __('enter_first_name') }}" value="{{ $data->userDetails->first_name ?? ''}}">
                                                <span class="text-danger error-text" id="first_name_error" data-required="{{ __('first_name_required') }}" data-max="{{ __('first_name_maxlength') }}" data-alpha="{{ __('alphabets_allowed') }}"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">{{__('last_name')}}<span class="text-danger"> *</span></label>
                                                <input type="text" class="form-control" name="last_name" id="last_name"
                                                    placeholder="{{ __('enter_last_name') }}" value="{{ $data->userDetails->last_name ?? ''}}">
                                                <span class="text-danger error-text" id="last_name_error" data-required="{{ __('last_name_required') }}" data-max="{{ __('last_name_maxlength') }}" data-alpha="{{ __('alphabets_allowed') }}"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">{{__('email')}}<span class="text-danger"> *</span></label>
                                                <input type="text" class="form-control" name="email" id="email" placeholder="{{ __('enter_email') }}" value="{{ $data->email ?? ''}}">
                                                <span class="text-danger error-text" id="email_error" data-required="{{ __('email_required') }}" data-email_format="{{ __('email_format') }}" data-exists="{{ __('email_exists') }}"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">{{__('user_name')}}<span class="text-danger"> *</span></label>
                                                <input type="text" class="form-control" name="user_name" id="user_name" placeholder="{{ __('enter_user_name') }}" value="{{ $data->name ?? ''}}">
                                                <span class="text-danger error-text" id="user_name_error" data-required="{{ __('user_name_required') }}" data-max="{{ __('user_name_maxlength') }}" data-exists="{{ __('user_name_exists') }}"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">{{__('phone_number')}}<span class="text-danger"> *</span></label>
                                                <input type="text" class="form-control user_phone_number" name="phone_number" id="phone_number" placeholder="{{ __('enter_phone_number') }}" value="{{ $data->phone_number ?? ''}}">
                                                <input type="hidden" id="international_phone_number" name="international_phone_number">
                                                <span class="text-danger error-text" id="phone_number_error" data-required="{{ __('phone_number_required') }}" data-digits="{{ __('phone_number_digits') }}" data-between="{{ __('phone_number_between') }}"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5>{{__('address_information')}}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">{{__('address')}} <span class="text-danger"> *</span></label>
                                                <input type="text" class="form-control" name="address" id="address" placeholder="{{ __('enter_address') }}" value="{{ $data->userDetails->address ?? ''}}">
                                                <span class="text-danger error-text" id="address_error" data-required="{{ __('address_required') }}" data-max="{{ __('address_maxlength') }}"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">{{__('country')}} <span class="text-danger"> *</span></label>
                                                <select name="country" id="country" class="form-control select2" data-placeholder="{{ __('select_country') }}" data-country="{{ $data->userDetails->country ?? ''}}">
                                                </select>
                                                <span class="text-danger error-text" id="country_error" data-required="{{ __('country_required') }}"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">{{__('state')}}<span class="text-danger"> *</span></label>
                                                <select name="state" id="state" class="form-control select2" data-placeholder="{{ __('select_state') }}" data-state="{{ $data->userDetails->state ?? ''}}">
                                                </select>
                                                <span class="text-danger error-text" id="state_error" data-required="{{ __('state_required') }}"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">{{__('city')}} <span class="text-danger"> *</span></label>
                                                <select name="city" id="city" class="form-control select2" data-placeholder="{{ __('select_city') }}" data-city="{{ $data->userDetails->city ?? ''}}">
                                                </select>
                                                <span class="text-danger error-text" id="city_error" data-required="{{ __('city_required') }}"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">{{__('postal_code')}} <span class="text-danger"> *</span></label>
                                                <input type="text" class="form-control" name="postal_code" id="postal_code"
                                                    placeholder="{{ __('Enter Postal Code') }}" value="{{ $data->userDetails->postal_code ?? ''}}">
                                                <span class="text-danger error-text" id="postal_code_error" data-required="{{ __('postal_code_required') }}" data-max="{{ __('postal_maxlength') }}" data-char_allowed="{{ __('postal_code_char') }}"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <form id="changePasswordForm">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5>{{__('Password')}}</h5>
                                    <button class="btn btn-primary" id="change_password" type="submit" data-save="{{ __('Save') }}">{{__('Save')}}</button>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">{{__('Current Password')}} <span class="text-danger"> *</span></label>
                                        <div class="pass-group d-flex">
                                            <input type="password" class="pass-input form-control" name="current_password" id="current_password" >
											<span class="ti toggle-password ti-eye-off"></span>
                                        </div>
                                        <span class="text-danger error-text" id="current_password_error" data-required="{{ __('current_password_required') }}" data-min="{{ __('current_password_minlength') }}" data-incorrect="{{ __('incorrect_password') }}"></span>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">{{__('New Password')}} <span class="text-danger"> *</span></label>
                                        <div class="pass-group d-flex">
                                            <input type="password" class="pass-inputs form-control" name="new_password" id="new_password">
											<span class="ti toggle-passwords ti-eye-off"></span>
                                        </div>
                                        <span class="text-danger error-text" id="new_password_error" data-required="{{ __('new_password_required') }}" data-min="{{ __('new_password_minlength') }}" data-not_equal="{{ __('new_password_notEqualTo') }}"></span>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label">{{__('Confirm Password')}} <span class="text-danger"> *</span></label>
                                        <div class="pass-group d-flex">
                                            <input type="password" class="pass-inputa form-control" name="confirm_password" id="confirm_password">
											<span class="ti toggle-passworda ti-eye-off"></span>
                                        </div>
                                        <span class="text-danger error-text" id="confirm_password_error" data-required="{{ __('confirm_password_required') }}" data-equal="{{ __('confirm_password_equalTo') }}"></span>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($locationStatus == 1)
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    let input = document.getElementById("address");

    const autocomplete = new google.maps.places.Autocomplete(input, {
        types: ["geocode"],
        componentRestrictions: { country: ["us", "in"] }, // adjust if needed
    });

    autocomplete.addListener("place_changed", function () {
        const place = autocomplete.getPlace();

        if (!place.address_components) return;

        let selectedAddress = "";
        let city = "";
        let state = "";
        let country = "";
        let postalCode = "";

        place.address_components.forEach((component) => {
            const types = component.types;

            if (types.includes("street_number") || types.includes("route") || types.includes("sublocality")) {
                selectedAddress += component.long_name + " ";
            }
            if (types.includes("locality")) {
                city = component.long_name;
            }
            if (types.includes("administrative_area_level_1")) {
                state = component.long_name;
            }
            if (types.includes("country")) {
                country = component.long_name;
            }
            if (types.includes("postal_code")) {
                postalCode = component.long_name;
            }
        });

        /** SET ADDRESS + POSTAL */
        $("#address").val(selectedAddress.trim());
        $("#postal_code").val(postalCode);

        /** AUTO SELECT COUNTRY */
        $("#country option").each(function () {
            if ($(this).text().trim().toLowerCase() === country.toLowerCase()) {
                $("#country").val($(this).val()).trigger("change");
            }
        });

        /** After country loads states (with AJAX), auto-select state */
        setTimeout(() => {
            $("#state option").each(function () {
                if ($(this).text().trim().toLowerCase() === state.toLowerCase()) {
                    $("#state").val($(this).val()).trigger("change");
                }
            });
        }, 400); // wait for state dropdown reload

        /** After state loads cities (with AJAX), auto-select city */
        setTimeout(() => {
            $("#city option").each(function () {
                if ($(this).text().trim().toLowerCase() === city.toLowerCase()) {
                    $("#city").val($(this).val()).trigger("change");
                }
            });
        }, 700);

    });

});
</script>
@endif

@endsection