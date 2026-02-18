@extends('admin.admin')

@section('content')

<div class="page-wrapper">
	<div class="content bg-white">
		<div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
			<div class="my-auto mb-2">
				<h3 class="page-title mb-1">{{ __('admin_commission') }}</h3>
				<nav>
					<ol class="breadcrumb mb-0">
						<li class="breadcrumb-item">
							<a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
						</li>
						<li class="breadcrumb-item">
							<a href="javascript:void(0);">{{ __('Settings') }}</a>
						</li>
						<li class="breadcrumb-item active" aria-current="page">{{ __('admin_commission') }}</li>
					</ol>
				</nav>
			</div>
		</div>
		<div class="row">
			@include('admin.partials.general_settings_side_menu')
			<div class="col-xxl-10 col-xl-9">
				<div class="flex-fill ps-1">
					<form id="adminCommissionForm">
						<div class="d-flex align-items-center justify-content-between flex-wrap border-bottom pt-3 mb-3">
							<div class="mb-3">
								<h5 class="mb-1">{{ __('admin_commission') }}</h5>
								<p>{{ __('change_admin_commission_here') }}</p>
							</div>
							<div class="mb-3">
                                @if(isset($permission))
                                    @if(hasPermission($permission, 'General Settings', 'edit'))
										<div class="skeleton label-skeleton label-loader"></div>
                                        <button class="btn btn-primary admin_commission_btn d-none real-label" type="submit" data-save_text="{{ __('Save') }}">{{ __('Save') }}</button>
                                    @endif
                                @endif
							</div>
						</div>
						<input type="hidden" name="group_id" id="group_id" class="form-control" value="2" >
						<div class="mb-3 row">
							<div class="skeleton label-skeleton label-loader"></div>
							<label class="form-label col-md-2 d-none real-label">{{ __('commission_type') }}<span class="text-danger"> *</span></label>
							<div class="col-md-10">
								<div class="skeleton input-skeleton input-loader"></div>
								<select class="form-select d-none real-input" name="commission_type" id="commission_type">
									<option value="fixed">{{ __('fixed') }}</option>
									<option value="percentage">{{ __('percentage') }}</option>
								</select>
								<span class="text-danger error-text" id="commission_type_error"></span>
							</div>
						</div>
						<div class="mb-3 row">
							<div class="skeleton label-skeleton label-loader"></div>
							<label class="form-label col-md-2 d-none real-label">{{ __('commission_rate') }}<span class="text-danger"> *</span></label>
							<div class="col-md-10">
								<div class="skeleton input-skeleton input-loader"></div>
								<input type="text" class="form-control d-none real-input" id="commission_rate" name="commission_rate">
								<span class="text-danger error-text" id="commission_rate_error"></span>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
