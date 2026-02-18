@extends('admin.admin')

@section('content')

    <div class="page-wrapper">
        <form id="breadImageForm">
			<div class="content">
				<div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
					<div class="my-auto mb-2">
						<h3 class="page-title mb-1">{{ __('BreadImage Settings')}}</h3>
						<nav>
							<ol class="breadcrumb mb-0">
								<li class="breadcrumb-item">
									<a href="{{ route('admin.dashboard') }}">{{ __('Dashboard')}}</a>
								</li>
                                <li class="breadcrumb-item">
                                    <a href="javascript:void(0);">{{ __('Settings')}}</a>
                                </li>
								<li class="breadcrumb-item active" aria-current="page">{{ __('BreadImage Settings')}}</li>
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
                                    <div class="card">
										<div class="card-body">
											<div class="mb-3">
												<div class="d-flex justify-content-between mb-3">
													<div class="d-flex align-items-center">
														<span class="avatar avatar-xl border rounded d-flex align-items-center justify-content-center p-2 me-2">
															<div class="skeleton rectangle-md-skeleton input-loader"></div>
															<img src="{{ asset('assets/img/logo-small.svg') }}" alt="Img" id="bread-image-preview" class="d-none real-input">
														</span>
														<div class="skeleton label-skeleton label-loader"></div>
														<h5 class="d-none real-label">{{ __('BreadImage')}}</h5>
													</div>
												</div>
												<div class="skeleton input-skeleton input-loader"></div>
												<code class="d-none real-input">{{ __('recommended_logo_size') }}</code>
												<div class="profile-uploader profile-uploader-two mb-0 d-none real-input">
													<span class="d-block text-center lh-1 fs-24 mb-1"><i class="ti ti-upload"></i></span>
													<div class="drag-upload-btn bg-transparent me-0 border-0">
														<p class="fs-12 mb-2">
															<span class="text-primary">{{ __('click_to_upload') }}</span> {{ __('drag_and_drop') }}
														</p>
														<h6>{{ __('image_format') }}</h6>
														<h6>{{ __('max_size', ['width' => 155, 'height' => 40]) }}</h6>
													</div>
													<input type="file" class="form-control image-sign" id="bread_image" name="bread_image" accept="image/*">
													<div class="frames"></div>
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

@endsection










