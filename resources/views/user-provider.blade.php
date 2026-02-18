@extends('front')

@section('content')

<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h2 class="breadcrumb-title mb-2">{{__('Providers')}}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}"><i class="ti ti-home-2"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{__('Providers')}}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="breadcrumb-bg">
            <img src="/assets/img/bg/breadcrumb-bg-01.png" class="breadcrumb-bg-1" alt="Img">
            <img src="/assets/img/bg/breadcrumb-bg-02.png" class="breadcrumb-bg-2" alt="Img">
        </div>
    </div>
</div>
<div class="page-wrapper">
    <div class="content content-two">
        <div class="container">
            <div class="row align-items-start">
                <div class="col-xl-12 col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check d-flex align-items-center">
                            <input class="form-check-input select-all-checkbox me-2" type="checkbox">
                            <label class="form-label mb-0">{{__('Select All')}}</label>
                        </div>
                        <button class="btn btn-primary submit-selected" data-send_request_text="{{__('Send Request')}}">{{__('Send Request')}}</button>
                    </div>
                    <div class="row" id="providers-container" data-sending_text="{{ __('sending') }}" data-empty_info="{{ __('no_providers_found_selected_category') }}" data-requested_text="{{__('Requested')}}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="success_modal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body text-center">
				<div class="mb-4">
					<span class="success-icon mx-auto mb-4">
						<i class="ti ti-check"></i>
					</span>
					<p>{{__('Your_request_has_been_successfully_submitted')}}</p>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
