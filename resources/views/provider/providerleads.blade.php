@extends('provider.provider')

@section('content')
<div class="page-wrapper">
    <div class="content pb-4">
        <div
            class="d-md-flex d-block align-items-center justify-content-between mb-3 -bottom position-relative">
            <div class="my-auto mb-2">
                <div class="skeleton label-skeleton label-loader"></div>
                <h3 class="page-title mb-1 d-none real-label">{{__('Leads')}}
                </h3>
                <div class="skeleton label-skeleton label-loader"></div>
                <nav>
                    <ol class="breadcrumb mb-0 d-none real-label">
                        <li class="breadcrumb-item">
                            <a href="{{ Auth::user()->user_type == 2 ? route('provider.dashboard') : route('staff.dashboard') }}">{{__('Dashboard')}}</a>
                        </li>

                        <li class="breadcrumb-item active" aria-current="page">
                            {{__('Leads')}}
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    <div class="bg-white rounded-3 d-flex align-items-end justify-content-end flex-wrap mb-3 pb-0">
                        <div class="form-sort d-none">
                            <i class="ti ti-filter feather-filter info-img"></i>
                            <select class="select" id="sortSelect">
                                <option value="created_at">{{__('Recent')}}</option>
                                <option value="status">{{__('Status')}}</option>
                                <option value="id">{{__('Id')}}</option>
                            </select>
                        </div>
                        <div class="skeleton input-skeleton input-loader"></div>
                        <div class="form-sort mx-1 d-none real-input">
                            <i class="ti ti-filter feather-filter info-img"></i>
                            <select class="select" id="order_byselect">
                                <option value="desc">{{__('Descending')}}</option>
                                <option value="asc">{{__('Ascending')}}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-3 col-md-12 sidebars-right  section-bulk-widget">
                <div class="border rounded-3 bg-white p-3 mb-4">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="skeleton label-skeleton label-loader"></div>
                        <h4 class="d-flex align-items-center d-none real-label"><i class="ti ti-file-text me-2"></i>{{__('Leads')}}</h4>
                    </div>
                    <div class="-bottom pb-3 ">
                        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                            aria-orientation="vertical">
                            <input type="hidden" id="activeStatusInput" value="">

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

            <div class="col-xl-9 budget-role-notes">
                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade active show" id="v-pills-profile" role="tabpanel"
                        aria-labelledby="v-pills-profile-tab">
                        <div class="row">
                            <div class="col-lg-12">
                            <div id="leadsLoader">
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
                                <div class="accordion todo-accordion" id="accordionExample">

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div id="pagination" class="d-flex justify-content-end"></div>


            </div>
        </div>
    </div>
</div>
@endsection