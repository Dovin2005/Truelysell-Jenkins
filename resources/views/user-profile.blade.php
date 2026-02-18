@extends('front')

@section('content')

<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h2 class="breadcrumb-title mb-2">{{__('settings')}}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}"><i
                                    class="ti ti-home-2"></i></a></li>
                        <li class="breadcrumb-item">{{__('user')}}</li>
                        <li class="breadcrumb-item active" aria-current="page">{{__('profile_settings')}}</li>
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
                    <h4 class="mb-3">{{__('profile_settings')}}</h4>
                    <h6 class="mb-4">{{__('profile_picture')}}</h6>
                    <form id="userProfileForm">
                        <input type="hidden" name="id" id="id" value="{{ $data->id ?? ''}}">
                        <div class="pro-picture d-flex flex-wrap gap-2">
                            <div class="pro-img avatar avatar-xl flex-shrink-0">
                                <img src="{{ $data->userDetails->profile_image ?? asset('assets/img/profile-default.png') }}"
                                    alt="user" class="img-fluid rounded-circle profileImagePreview">
                            </div>
                            <div class="pro-info">
                                <div class="d-flex mb-2">
                                    <input type="file" name="profile_image" id="profile_image">
                                </div>
                                <p class="fs-14">{{__('image_size_note')}}</p>
                                <span class="text-danger error-text" id="profile_image_error"></span>
                            </div>
                        </div>
                        <h6>{{__('general_information')}}</h6>
                        <div class="general-info mb-0">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{__('first_name')}}<span class="text-danger">
                                                *</span></label>
                                            <input type="text" class="form-control" id="user_first_name" name="first_name"
                                                value="{{ $data->userDetails->first_name ?? ''}}">
                                        <span class="text-danger error-text" id="user_first_name_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{__('last_name')}}<span class="text-danger">
                                                *</span></label>
                                        <input type="text" class="form-control" id="user_last_name" name="last_name"
                                            value="{{ $data->userDetails->last_name ?? ''}}">
                                        <span class="text-danger error-text" id="user_last_name_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">{{__('email')}}<span class="text-danger">
                                                *</span></label>
                                        <input type="email" class="form-control" id="user_email" name="email"
                                            value="{{ $data->email ?? ''}}">
                                        <span class="text-danger error-text" id="user_email_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{__('user_name')}}<span class="text-danger">
                                                *</span></label>
                                        <input type="text" class="form-control" id="user_name" name="user_name"
                                            value="{{ $data->name ?? ''}}">
                                        <span class="text-danger error-text" id="user_name_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{__('phone_number')}}<span class="text-danger">
                                                *</span></label>
                                        <input type="text" class="form-control user_phone_number" id="phone_number"
                                            name="phone_number" value="{{ $data->phone_number ?? '' }}">
                                        <input type="hidden" id="international_phone_number"
                                            name="international_phone_number">
                                        <span class="text-danger error-text" id="phone_number_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{__('gender')}}<span class="text-danger">
                                                *</span></label>
                                        <select class="select select2" id="gender" name="gender">
                                            <option value="">{{__('select_gender')}}</option>
                                            <option value="male" {{ ($data->userDetails->gender ?? '') == 'male' ? 'selected' : '' }}>{{__('male')}}</option>
                                            <option value="female" {{ ($data->userDetails->gender ?? '') == 'female' ? 'selected' : '' }}>{{__('female')}}</option>
                                        </select>
                                        <span class="text-danger error-text" id="gender_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{__('date_of_birth')}}<span class="text-danger">
                                                *</span></label>
                                        <div class=" input-icon position-relative">
                                            <input type="date" class="form-control" id="dob" name="dob"
                                                placeholder="dd-mm-yyyy"
                                                max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                                                value="{{ $data->userDetails->dob ?? ''}}">
                                        </div>
                                        <span class="text-danger error-text" id="dob_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <div class="mb-3">
                                        <label class="form-label d-block">{{__('your_bio')}}</label>
                                        <textarea class="form-control" id="bio" name="bio"
                                            rows="5">{{ $data->userDetails->bio ?? ''}}</textarea>
                                        <span class="text-danger error-text" id="bio_error"></span>
                                    </div>
                                </div>
                            </div>
                            <h6 class="user-title">{{__('address_information')}}</h6>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class=" mb-3">
                                        <label class="form-label">{{__('address')}}<span class="text-danger">
                                                *</span></label>
                                        <input type="text" class="form-control" id="address" name="address"
                                            value="{{ $data->userDetails->address ?? ''}}">
                                        <span class="text-danger error-text" id="address_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class=" mb-3">
                                        <label class="form-label">{{__('country')}}<span class="text-danger">
                                                *</span></label>
                                        <select class="select select2" id="country" name="country"
                                            data-placeholder="{{__('select_country')}}"
                                            data-country="{{ $data->userDetails->country ?? ''}}">
                                        </select>
                                        <span class="text-danger error-text" id="country_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class=" mb-3">
                                        <label class="form-label">{{__('state')}}<span class="text-danger">
                                                *</span></label>
                                        <select class="select select2" id="state" name="state"
                                            data-placeholder="{{__('select_state')}}"
                                            data-state="{{ $data->userDetails->state ?? ''}}">
                                        </select>
                                        <span class="text-danger error-text" id="state_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class=" mb-3">
                                        <label class="form-label">{{__('city')}}<span class="text-danger">
                                                *</span></label>
                                        <select class="select select2" id="city" name="city"
                                            data-placeholder="{{__('select_city')}}"
                                            data-city="{{ $data->userDetails->city ?? ''}}">
                                        </select>
                                        <span class="text-danger error-text" id="city_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class=" mb-3">
                                        <label class="form-label">{{__('postal_code')}}<span class="text-danger">
                                                *</span></label>
                                        <input type="text" class="form-control" id="postal_code" name="postal_code"
                                            value="{{ $data->userDetails->postal_code ?? ''}}">
                                        <span class="text-danger error-text" id="postal_code_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6 d-none">
                                    <div class=" mb-3">
                                        <label class="form-label">{{__('currency_code')}}<span class="text-danger">
                                                *</span></label>
                                        <select class="select select2" id="currency_code" name="currency_code"
                                            data-placeholder="{{__('select_currency_code')}}">
                                            @if ($currencyDetails)
                                            @foreach ($currencyDetails as $currency)
                                            <option value="{{ $currency->code }}" {{ ($data->userDetails->currency_code ?? '') == $currency->code ? 'selected' : '' }}>{{ $currency->code }}
                                            </option>
                                            @endforeach
                                            @endif
                                        </select>
                                        <span class="text-danger error-text" id="currency_code_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">{{__('language')}}</label>
                                        <input class="input-tags form-control" type="text" data-role="tagsinput"
                                            name="language" id="language"
                                            value="{{ $data->userDetails->language ?? ''}}">
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-dark"
                                        id="saveProfile" data-save="{{ __('save_changes') }}">{{__('save_changes')}}</button>
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
    });

    autocomplete.addListener("place_changed", function () {
        const place = autocomplete.getPlace();

        if (!place.address_components) return;

        let address = "";
        let country = "";
        let country_id = "";
        let state = "";
        let state_id = "";
        let city = "";
        let postalCode = "";

        place.address_components.forEach((component) => {
            const types = component.types;

            if (types.includes("street_number") || types.includes("route") || types.includes("sublocality")) {
                address += component.long_name + " ";
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

        /** Fill form fields */
        document.getElementById("address").value = address;
        $("#postal_code").val(postalCode);

         /** AUTO SELECT COUNTRY */
        $("#country option").each(function () {
            if ($(this).text().trim().toLowerCase() === country.toLowerCase()) {
                $("#country").val($(this).val()).trigger("change");
            }
        });

        setTimeout(() => {
            $("#state option").each(function () {
                if ($(this).text().trim().toLowerCase() === state.toLowerCase()) {
                    $("#state").val($(this).val()).trigger("change");
                }
            });
        }, 400); // wait for state dropdown reload

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