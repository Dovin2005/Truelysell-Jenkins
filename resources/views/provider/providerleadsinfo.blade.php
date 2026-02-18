@extends('provider.provider')

@section('content')
<div class="page-wrapper notes-page-wrapper">
    <div class="content">
        <div id="pageLoader" class="loader_front">
            <div>
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
        <div
            class="d-md-flex d-block align-items-center justify-content-between mb-1 -bottom position-relative">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{__('LeadsInfo')}}
                </h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ Auth::user()->user_type == 2 ? route('provider.dashboard') : route('staff.dashboard') }}">{{__('Dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item">
                            {{__('Leads')}}
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{__('Info')}}
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    <a href="{{ route('provider.leads') }}" class="btn btn-primary d-flex align-items-center">{{__('Back')}}</a>
                </div>
            </div>
        </div>
        <div class="row row-gap-3">
            <div class="col-xl-12 budget-role-notes">
                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade active show" id="v-pills-profile" role="tabpanel"
                        aria-labelledby="v-pills-profile-tab">
                        <div class="row row-gap-3">
                            <div class="col-lg-12">
                                <div class="content p-0">
                                    <div class="row">
                                        <div class="col-12">
                                            <div>
                                                <div class="card">
                                                    <div class="card-header">
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <div class="d-flex align-items-center">
                                                                    <h4 class="username me-2"></h4>
                                                                    <p class="badge bg-outline-danger d-inline-flex align-items-center mb-0 status">
                                                                        <i class="fas fa-circle fs-6 me-1"></i>
                                                                    </p>
                                                                </div>
                                                                <div class="d-flex justify-content-start">
                                                                    <i class="ti ti-calendar mt-2 me-1"></i><p class="times mt-1 "></p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="card">
                                                                    <div class="card-body p-0">
                                                                        <p class="category mb-2 mt-2 d-flex justify-content-center"></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="card">
                                                                    <div class="card-body p-0">
                                                                    <p class="mb-2 mt-2 d-flex justify-content-center sub_category"></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 text-end" id="status_div" style="display: none;">
                                                                <a href="#" class="btn btn-success accept_btn" id="accept_btn" data-id="">{{__('Accept')}}</a>
                                                                <a href="#" class="btn btn-danger reject_btn" id="reject_btn" data-id="">{{__('Reject')}}</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-body lead-design-service">


                                                        <div class="">
                                                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                                                <li class="nav-item" role="presentation">
                                                                    <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true">
                                                                        {{ __('Request Info') }}
                                                                    </button>
                                                                </li>

                                                                <li class="nav-item"   id="notes-tab" role="presentation">
                                                                    <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab" aria-controls="notes" aria-selected="false">
                                                                        {{ __('Quote') }}
                                                                    </button>
                                                                </li>
                                                            </ul>
                                                            <div class="tab-content" id="myTabContent">
                                                                <div class="tab-pane fade show active p-2" id="details" role="tabpanel" aria-labelledby="details-tab">
                                                                    <div class="category-info mt-0">
                                                                        <div class="row" id="form-inputs"></div>
                                                                    </div>
                                                                </div>

                                                                <div class="tab-pane fade p-3" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                                                                    <div class="card shadow-sm border-0">

                                                                        <div class="card-body ">
                                                                            <form id="providerQuoteForm">
                                                                                <div class="row">
                                                                                    <div class="col-md-6">
                                                                                        <div class="mb-3">
                                                                                            <label for="quote" class="form-label">{{ __('Quote in') }} </label><span class="currency mx-2 fw-bold"></span>
                                                                                            <input type="number" name="quote" id="quote" class="form-control quote" placeholder="{{ __('Enter Quote') }}" maxlength="4">
                                                                                            <small id="quote_error" class="text-danger"></small>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-md-6">
                                                                                            <div class="mb-3">
                                                                                                <label for="start_date" class="form-label">{{ __('Start Date') }}</label>
                                                                                                <input type="date" name="start_date" id="start_date" class="form-control start_date" min="">
                                                                                                <small id="start_date_error" class="text-danger"></small>
                                                                                            </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="mb-3">
                                                                                    <label for="description" class="form-label">{{ __('Description') }}</label>
                                                                                    <textarea name="description" id="description" class="form-control description" rows="4" placeholder="{{ __('Enter Description') }}"></textarea>
                                                                                    <small id="description_error" class="text-danger"></small>
                                                                                </div>

                                                                                <div class="text-end">
                                                                                    <button type="submit" class="btn btn-primary fixed-size-btn">{{ __('Submit Quote') }}</button>
                                                                                </div>
                                                                            </form>
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
