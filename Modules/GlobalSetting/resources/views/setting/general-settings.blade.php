@extends('admin.admin')

@section('content')

<div class="page-wrapper">
	<form id="generalSettingForm" >
		<div class="content">
			<div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-4">
				<div class="my-auto mb-2">
					<h3 class="page-title mb-1">{{ __('Company Settings')}}</h3>
					<nav>
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item">
								<a href="{{ route('admin.dashboard') }}">{{ __('Dashboard')}}</a>
							</li>
							<li class="breadcrumb-item">
								<a href="javascript:void(0);">{{ __('Settings')}}</a>
							</li>
							<li class="breadcrumb-item active" aria-current="page">{{ __('Company Settings')}}</li>
						</ol>
					</nav>
				</div>

				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
					<div class="mb-2">
                    @if(isset($permission))
                        @if(hasPermission($permission, 'General Settings', 'edit'))
							<div class="skeleton label-skeleton label-loader"></div>
                            <button class="btn btn-primary general_setting_btn fixed-size-btn d-none real-label" type="submit">{{ __('Update')}}</button>
                        @endif
                    @endif
					</div>
				</div>
			</div>
			<div class="row">
				@include('admin.partials.general_settings_side_menu')
				<div class="col-xxl-10 col-xl-9">
					<div class="flex-fill ps-1">
						<div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
						</div>
						<div class="d-md-flex d-block">
							<div class="flex-fill">
								<input type="hidden" name="group_id" id="group_id" class="form-control" value="1" >
								<div class="card">
									<div class="card-header">
										<div class="skeleton label-skeleton label-loader"></div>
										<h5 class="d-none real-label">{{ __('Single Vendor') }}</h5>
									</div>
									<div class="card-body pb-1">
										<div class="modal-satus-toggle d-flex align-items-center justify-content-between">
											<div class="status-title">
												<div class="skeleton label-skeleton label-loader"></div>
												<p class="d-none real-label">{{ __('Change the Status by toggle') }} </p>
											</div>
											<div class="status-toggle modal-status d-none real-label">
												<div class="skeleton label-skeleton label-loader"></div>
												<select class="form-control select d-none real-label" id="save_single_vendor_status" name="save_single_vendor_status" >
													<option value="on">On</option>
													<option value="off">Off</option>
												</select>
											</div>
										</div>
									</div>
								</div>

								<div class="card">
									<div class="card-header">
										<div class="skeleton label-skeleton label-loader"></div>
										<h5 class="d-none real-label">{{ __('SSO Status') }}</h5>
									</div>
									<div class="card-body pb-1">
										<div class="modal-satus-toggle d-flex align-items-center justify-content-between">
											<div class="status-title">
												<div class="skeleton label-skeleton label-loader"></div>
												<p class="d-none real-label">{{ __('Change the Status by toggle') }} </p>
											</div>
											<div class="status-toggle modal-status d-none real-label">
												<div class="skeleton label-skeleton label-loader"></div>
												<select class="form-control select d-none real-label" id="sso_status" name="sso_status" >
													<option value="1">On</option>
													<option value="0">Off</option>
												</select>
											</div>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-md-6">
										<div class="card">
											<div class="card-body d-flex align-items-center justify-content-between">
												<div class="skeleton label-skeleton label-loader"></div>
												<div class="d-flex align-items-center d-none real-label">
													<h6 class="fw-semibold">{{ __('provider_approval') }}</h6>
												</div>
												<div class="status-toggle modal-status">
													<div class="skeleton label-skeleton label-loader"></div>
													<input type="checkbox" name="provider_approval_status" id="provider_approval_status" value="1" class="check">
													<label for="provider_approval_status" class="checktoggle d-none real-label"> </label>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="card">
											<div class="card-body d-flex align-items-center justify-content-between">
												<div class="skeleton label-skeleton label-loader"></div>
												<div class="d-flex align-items-center d-none real-label">
													<h6 class="fw-semibold">{{ __('service_approval') }}</h6>
												</div>
												<div class="status-toggle modal-status">
													<div class="skeleton label-skeleton label-loader"></div>
													<input type="checkbox" name="service_approval_status" id="service_approval_status" value="1" class="check">
													<label for="service_approval_status" class="checktoggle d-none real-label"> </label>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="card">
									<div class="card-header">
										<div class="skeleton label-skeleton label-loader"></div>
										<h5 class="d-none real-label">{{ __('Company Information') }}</h5>
									</div>
									<div class="card-body pb-1">
										<div class="row">
											<div class="col-12 col-md-6 mb-3">
												<div class="skeleton label-skeleton label-loader"></div>
												<label class="form-label d-none real-label" for="app_name">{{ __('App Name') }}<span class="text-danger"> *</span></label>
												<div class="skeleton input-skeleton input-loader"></div>
												<input type="text" name="app_name" id="app_name" class="form-control d-none real-input" placeholder="{{ __('Enter App Name') }}" maxlength="100">
												<span class="text-danger error-text" id="app_name_error"></span>
											</div>
											<div class="col-12 col-md-6 mb-3">
												<div class="skeleton label-skeleton label-loader"></div>
												<label class="form-label d-none real-label" for="company_name">{{ __('Company Name') }}<span class="text-danger"> *</span></label>
												<div class="skeleton input-skeleton input-loader"></div>
												<input type="text" name="company_name" id="company_name" class="form-control d-none real-input" placeholder="{{ __('Enter Company Name') }}" maxlength="100">
												<span class="text-danger error-text" id="company_name_error"></span>
											</div>
										</div>
										<div class="row">
											<div class="col-12 col-md-6 mb-3">
												<div class="skeleton label-skeleton label-loader"></div>
												<label class="form-label d-none real-label" for="phone_no">{{ __('Phone Number') }}<span class="text-danger"> *</span></label>
												<div class="skeleton input-skeleton input-loader"></div>
												<input type="number" name="phone_no" id="phone_no" class="form-control d-none real-input" oninput="validatePhoneNumber(this)" placeholder="{{ __('Enter Phone Number') }}" maxlength="12">
												<span class="text-danger error-text" id="phone_no_error"></span>
											</div>
											<div class="col-12 col-md-6 mb-3">
												<div class="skeleton label-skeleton label-loader"></div>
												<label class="form-label d-none real-label" for="site_email">{{ __('Email') }}<span class="text-danger"> *</span></label>
												<div class="skeleton input-skeleton input-loader"></div>
												<input type="email" name="site_email" id="site_email" class="form-control d-none real-input" placeholder="{{ __('Enter Site Email') }}" maxlength="100">
												<span class="text-danger error-text" id="site_email_error"></span>
											</div>
										</div>
										<div class="row">
											<div class="col-12 col-md-6 mb-3">
												<div class="skeleton label-skeleton label-loader"></div>
												<label class="form-label d-none real-label" for="fax_no">{{ __('Fax Number') }}<span class="text-danger"> *</span></label>
												<div class="skeleton input-skeleton input-loader"></div>
												<input type="number" name="fax_no" id="fax_no" class="form-control d-none real-input" oninput="validateFaxNumber(this)" placeholder="{{ __('Enter Fax Number') }}" maxlength="12">
												<span class="text-danger error-text" id="fax_no_error"></span>
											</div>
											<div class="col-12 col-md-6 mb-3">
												<div class="skeleton label-skeleton label-loader"></div>
												<label class="form-label d-none real-label" for="website">{{ __('Website') }}<span class="text-danger"> *</span></label>
												<div class="skeleton input-skeleton input-loader"></div>
												<input type="text" name="website" id="website" class="form-control d-none real-input" placeholder="{{ __('Enter Website') }}" maxlength="150">
												<span class="text-danger error-text" id="website_error"></span>
											</div>
										</div>
										<div class="d-block d-xl-flex">
											<div class="col-12 col-md-12 mb-3">
												<div class="skeleton label-skeleton label-loader"></div>
												<label class="form-label d-none real-label">{{ __('Timezone') }}<span class="text-danger"> *</span></label>
												<div class="skeleton input-skeleton input-loader"></div>
												<select name="timezone" id="timezone" class="form-control select2 d-none real-input"></select>
												<span class="text-danger error-text" id="timezone_error"></span>
											</div>
										</div>
									</div>
								</div>

								<div class="card">
									<div class="card-header">
										<div class="skeleton label-skeleton label-loader"></div>
										<h5 class="d-none real-label">{{ __('Address Information') }}</h5>
									</div>
									<div class="card-body pb-1">
										<div class="mb-3">
											<div class="skeleton label-skeleton label-loader"></div>
											<label class="form-label d-none real-label">{{ __('Address') }}<span class="text-danger"> *</span></label>
											<div class="skeleton input-skeleton input-loader"></div>
											<input type="text" name="site_address" id="site_address" class="form-control d-none real-input" placeholder="{{ __('Enter Address') }}"  maxlength="150">
											<span class="text-danger error-text" id="site_address_error"></span>
										</div>
										<div class="row">
											<div class="col-12 col-md-6 mb-3">
												<div class="skeleton label-skeleton label-loader"></div>
												<label class="form-label d-none real-label" for="country">{{ __('Country') }}<span class="text-danger"> *</span></label>
												<div class="skeleton input-skeleton input-loader"></div>
												<select name="country" id="country" class="form-control select2 d-none real-input"></select>
												<span class="text-danger error-text" id="country_error"></span>
											</div>
											<div class="col-12 col-md-6 mb-3">
												<div class="skeleton label-skeleton label-loader"></div>
												<label class="form-label d-none real-label" for="state">{{ __('State / Province') }}<span class="text-danger"> *</span></label>
												<div class="skeleton input-skeleton input-loader"></div>
												<select name="state" id="state" class="form-control select2 d-none real-input"></select>
												<span class="text-danger error-text" id="state_error"></span>
											</div>
										</div>
										<div class="row">
											<div class="col-12 col-md-6 mb-3">
												<div class="skeleton label-skeleton label-loader"></div>
												<label class="form-label d-none real-label" for="city">{{ __('City') }}<span class="text-danger"> *</span></label>
												<div class="skeleton input-skeleton input-loader"></div>
												<select name="city" id="city" class="form-control select2 d-none real-input"></select>
												<span class="text-danger error-text" id="city_error"></span>
											</div>
											<div class="col-12 col-md-6 mb-3">
												<div class="skeleton label-skeleton label-loader"></div>
												<label class="form-label d-none real-label" for="postal_code">{{ __('Postal Code') }}<span class="text-danger"> *</span></label>
												<div class="skeleton input-skeleton input-loader"></div>
												<input type="text" name="postal_code" id="postal_code" class="form-control d-none real-input" placeholder="{{ __('Enter Postal Code') }}" maxlength="6">
												<span class="text-danger error-text" id="postal_code_error"></span>
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
	</form>
</div>

@if ($locationStatus == 1)
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    let input = document.getElementById("site_address"); // FIXED
    if (!input) return;

    const autocomplete = new google.maps.places.Autocomplete(input, {
        types: ["geocode"],
    });

    autocomplete.addListener("place_changed", function () {

        const place = autocomplete.getPlace();
        if (!place.address_components) return;

        let fullAddress = "";
        let city = "";
        let state = "";
        let country = "";
        let postalCode = "";

        place.address_components.forEach((comp) => {
            const type = comp.types[0];

            if (["street_number", "route"].includes(type)) {
                fullAddress += comp.long_name + " ";
            }
            if (type === "locality") {
                city = comp.long_name;
            }
            if (type === "administrative_area_level_1") {
                state = comp.long_name;
            }
            if (type === "country") {
                country = comp.long_name;
            }
            if (type === "postal_code") {
                postalCode = comp.long_name;
            }
        });

        // SET Address
        $("#site_address").val(fullAddress.trim());
        $("#postal_code").val(postalCode);

        /** ---------------------------
         * AUTO SELECT COUNTRY
         * --------------------------- */
        $("#country option").each(function () {
            if ($(this).text().trim().toLowerCase() === country.toLowerCase()) {
                $("#country").val($(this).val()).trigger("change");
            }
        });

        /** ---------------------------
         * After Country loads STATES
         * --------------------------- */
        setTimeout(() => {
            $("#state option").each(function () {
                if ($(this).text().trim().toLowerCase() === state.toLowerCase()) {
                    $("#state").val($(this).val()).trigger("change");
                }
            });
        }, 400);

        /** ---------------------------
         * After State loads CITIES
         * --------------------------- */
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
