@extends('admin.admin')

@section('content')

    <div class="page-wrapper">
        <form id="dtsettingform">
            <div class="content bg-white">
                <div class="d-md-flex d-block align-items-center justify-content-between border-bottom pb-3">
                    <div class="my-auto mb-2">
                        <h3 class="page-title mb-1">{{ __('clear_cache') }}</h3>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="javascript:void(0);">{{ __('Settings') }}</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">{{ __('clear_cache') }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="row">
                    @include('admin.partials.general_settings_side_menu')
                    <div class="col-xxl-10 col-xl-9">
                        <div class="flex-fill ps-1">
                            <div class="d-md-flex justify-content-between flex-wrap mb-3">
                            </div>
                            <div class="row flex-fill">
                                <div class="col-xl-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div>
                                                <h6 class="mb-3">{{ __("clear_cache") }}</h6>
                                                <p class="mb-3">{{ __("clear_cache_description") }}</p>
                                                <a href="#!" data-bs-toggle="modal" data-bs-target="#clear_cache" class="btn btn-primary">{{ __('Clear Cache') }}</a>
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

    <div class="modal fade" id="clear_cache">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
                <div class="modal-body text-center">
                    <span class="delete-icon">
                        <i class="ti ti-trash-x fs-20"></i>
                    </span>
                    <h4>{{ __('clear_cache') }}</h4>
                    <p>{{ __('Are you sure you want to clear the cache?') }}</p>
                    <div class="d-flex justify-content-center">
                        <a href="javascript:void(0);" class="btn btn-light me-2" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                        <button type="button" class="btn btn-danger" id="clear-cache">{{ __('Clear') }}</button>
                    </div>
                </div>
			</div>
		</div>
	</div>
@endsection
