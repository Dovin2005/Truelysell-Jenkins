@extends('front')

@section('content')

<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h2 class="breadcrumb-title mb-2">{{__('Leads')}}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}"><i class="ti ti-home-2"></i></a></li>
                        <li class="breadcrumb-item">{{__('Customer')}}</li>
                        <li class="breadcrumb-item active" aria-current="page">{{__('Leads')}}</li>
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
    <div class="content leads_service px-0 pb-4">
        <div class="container">
            <div class="row">
                @include('user.partials.sidebar')
                <div class="col-xl-9 col-lg-8">
                    <div class="row row-gap-3">
                        <div class="col-xl-3 col-md-12 sidebars-right section-bulk-widget">
                            <div class="border rounded-3 bg-white p-3">
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <h4 class="d-flex align-items-center d-none real-label"><i class="ti ti-file-text me-2"></i>{{__('Leads')}}</h4>
                                </div>
                                <div>
                                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                                        aria-orientation="vertical">
                                        <div class="skeleton input-skeleton input-loader mb-1"></div>
                                        <button class="d-flex text-start align-items-center flex-fill fw-medium fs-15 nav-link mb-1 active d-none real-input"
                                            id="inbox-tab"
                                            onclick="setActiveTab(this, null); loadLeads(1);">
                                            <i class="ti ti-inbox me-2"></i>{{__('All')}}
                                            <span class="ms-auto badge badge-xs rounded-pill bg-primary"></span>
                                        </button>

                                        <div class="skeleton input-skeleton input-loader mb-1"></div>
                                        <button class="d-flex text-start align-items-center flex-fill fw-medium fs-15 nav-link mb-1 d-none real-input"
                                            id="new-tab"
                                            onclick="setActiveTab(this, 1); loadLeads(1, 1);">
                                            <i class="ti ti-circle-check me-2"></i>{{__('New')}}
                                            <span class="ms-auto badge badge-xs rounded-pill bg-warning"></span>
                                        </button>

                                        <div class="skeleton input-skeleton input-loader mb-1"></div>
                                        <button class="d-flex text-start align-items-center flex-fill fw-medium fs-15 nav-link mb-1 d-none real-input"
                                            id="accept-tab"
                                            onclick="setActiveTab(this, 2); loadLeads(1, 2);">
                                            <i class="ti ti-star me-2"></i>{{__('Accept')}}
                                            <span class="ms-auto badge badge-xs rounded-pill bg-success"></span>
                                        </button>

                                        <div class="skeleton input-skeleton input-loader mb-1"></div>
                                        <button class="d-flex text-start align-items-center flex-fill fw-medium fs-15 nav-link mb-1 d-none real-input"
                                            id="reject-tab"
                                            onclick="setActiveTab(this, 3); loadLeads(1, 3);">
                                            <i class="ti ti-trash me-2"></i>{{__('Reject')}}
                                            <span class="ms-auto badge badge-xs rounded-pill bg-danger"></span>
                                        </button>

                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="col-xl-9 col-md-12 budget-role-notes">
                            <div class="bg-white rounded-3 d-flex align-items-center justify-content-end flex-wrap mb-3 pb-0 d-none real-label">
                                <div class="form-sort mb-3 d-none">
                                    <i class="ti ti-filter feather-filter info-img"></i>
                                    <select class="select" id="sortSelect">
                                        <option value="created_at">{{__('Recent')}}</option>
                                        <option value="status">{{__('Status')}}</option>
                                        <option value="id">{{__('Id')}}</option>
                                    </select>
                                </div>
                                <div class="form-sort mb-3">
                                    <i class="ti ti-filter feather-filter info-img"></i>
                                    <select class="select" id="order_byselect">
                                        <option value="desc">{{__('Descending')}}</option>
                                        <option value="asc">{{__('Ascending')}}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="tab-content" id="v-pills-tabContent">
                                <div class="tab-pane fade active show" id="v-pills-profile" role="tabpanel"
                                    aria-labelledby="v-pills-profile-tab">
                                    <div class="row">
                                        <div id="leadsLoader" class="col-lg-12">
                                            <div>
                                                <div class="accordion todo-accordion">
                                                    <div class="card shadow-none mb-2">
                                                        <div class="card-body p-2">
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="accordion todo-accordion">
                                                    <div class="card shadow-none mb-2">
                                                        <div class="card-body p-2">
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="accordion todo-accordion">
                                                    <div class="card shadow-none mb-2">
                                                        <div class="card-body p-2">
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="accordion todo-accordion">
                                                    <div class="card shadow-none mb-2">
                                                        <div class="card-body p-2">
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="accordion todo-accordion">
                                                    <div class="card shadow-none mb-2">
                                                        <div class="card-body p-2">
                                                            <div class="skeleton input-skeleton input-loader"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion todo-accordion" id="accordionExample">

                                        </div>
                                        <div id="pagination" class="d-flex justify-content-end"></div>
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