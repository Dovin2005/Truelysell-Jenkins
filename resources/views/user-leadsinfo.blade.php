@extends('front')

@section('content')

<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h2 class="breadcrumb-title mb-2">{{__('LeadsInfo')}}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}"><i class="ti ti-home-2"></i></a></li>
                        <li class="breadcrumb-item">{{__('Leads')}}</li>
                        <li class="breadcrumb-item active" aria-current="page">{{__('Info')}}</li>
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

<div class="page-wrapper notes-page-wrapper">
    <div class="content leads_service pb-4">
        <div class="container">
            <div class="row row-gap-3">
                @include('user.partials.sidebar')
                <div class="col-xl-9 col-lg-8 budget-role-notes">
                    <div class="tab-content" id="v-pills-tabContent">
                        <div class="tab-pane fade active show" id="v-pills-profile" role="tabpanel"
                            aria-labelledby="v-pills-profile-tab">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="content p-0">
                                        <div class="row">
                                            <div class="col-12">
                                                <div>
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <div class="row align-items-center">
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

                                                                <div class="col-md-3">
                                                                    <div class="d-flex justify-content-end my-xl-auto right-content align-items-end flex-wrap">
                                                                        <div class="mb-2">
                                                                            <a href="{{ route('user.leads') }}" class="btn btn-primary d-flex align-items-center">{{__('Back')}}</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-body lead-design-service">


                                                            <div class="">
                                                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                                                    <li class="nav-item" role="presentation">
                                                                        <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true">
                                                                        {{__('Request Info')}}
                                                                        </button>
                                                                    </li>
                                                                    <li class="nav-item" role="presentation">
                                                                        <button class="nav-link provider_list" id="attachments-tab" data-bs-toggle="tab" data-bs-target="#attachments" type="button" role="tab" aria-controls="attachments" aria-selected="false">
                                                                            {{__('Provider')}}
                                                                        </button>
                                                                    </li>

                                                                </ul>
                                                                <div class="tab-content" id="myTabContent">
                                                                    <div class="tab-pane fade show active p-3" id="details" role="tabpanel" aria-labelledby="details-tab">
                                                                        <div class="category-info  user_leads_inf">
                                                                            <div class="row" id="form-inputs"></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="tab-pane fade p-3" id="attachments" role="tabpanel" aria-labelledby="attachments-tab">
                                                                        <div class="mt-2" id="provider-cards-container"></div>
                                                                        <div class="tabs d-block">
                                                                            <ul class="nav nav-tabs">
                                                                                <li class="nav-item">
                                                                                    <a class="nav-link active" id="new-tab" data-bs-toggle="tab" href="#new-tab-content">{{__('New')}}</a>
                                                                                </li>
                                                                                <li class="nav-item">
                                                                                    <a class="nav-link" id="accepted-tab" data-bs-toggle="tab" href="#accepted-tab-content">{{__('Accepted')}}</a>
                                                                                </li>
                                                                                <li class="nav-item">
                                                                                    <a class="nav-link" id="rejected-tab" data-bs-toggle="tab" href="#rejected-tab-content">{{__('Rejected')}}</a>
                                                                                </li>
                                                                            </ul>
                                                                            <div class="tab-content">
                                                                                <div class="tab-pane fade show active" id="new-tab-content"></div>
                                                                                <div class="tab-pane fade" id="accepted-tab-content"></div>
                                                                                <div class="tab-pane fade" id="rejected-tab-content"></div>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                    <div class="tab-pane fade p-3" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                                                                        <h6 class="mb-2">Notes</h6>
                                                                        <p>Add and review any important notes for this lead.</p>
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
