@extends('admin.admin')

@section('content')
<div class="page-wrapper notes-page-wrapper">
    <div class="content pb-4">
        <div
            class="d-md-flex d-block align-items-center justify-content-between  -bottom position-relative">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('Leads')}}
                </h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ __('Leads')}}
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class=" d-flex align-items-center justify-content-between flex-wrap">
                    <div class="form-sort mb-3 d-none">
                        <select class="form-control" id="sortSelect">
                            <option value="created_at">{{ __('Recent')}}</option>
                            <option value="status">{{ __('Status')}}</option>
                            <option value="id">{{ __('Id')}}</option>
                        </select>
                    </div>
                    <div class="form-sort mb-3">
                        <div class="skeleton label-skeleton label-loader"></div>
                        <select class="form-control d-none real-label" id="order_byselect">
                            <option value="desc">{{ __('Descending')}}</option>
                            <option value="asc">{{ __('Ascending')}}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-gap-3">
            <div class="col-xl-3 col-md-12 sidebars-right  section-bulk-widget">
                <div class="border rounded-3 mt-2 bg-white p-3">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="skeleton label-skeleton label-loader"></div>
                        <h4 class="d-flex align-items-center d-none real-label"><i class="ti ti-file-text me-2"></i>{{ __('Leads')}}</h4>
                    </div>
                    <div class="-bottom pb-3 ">
                        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                            aria-orientation="vertical">
                            <input type="hidden" id="activeStatusInput" value="">

                    <div class="skeleton input-skeleton input-loader mb-2"></div>
                    <button class="d-flex text-start align-items-center flex-fill fw-medium fs-15 nav-link mb-1 active d-none real-input"
                            id="inbox-tab"
                            onclick="setActiveTab(this, null); loadLeads(1);">
                        <i class="ti ti-inbox me-2"></i>{{__('All')}}
                        <span class="ms-auto badge badge-xs rounded-pill bg-primary"></span>
                    </button>

                    <div class="skeleton input-skeleton input-loader mb-2"></div>
                    <button class="d-flex text-start align-items-center flex-fill fw-medium fs-15 nav-link mb-1 d-none real-input"
                            id="new-tab"
                            onclick="setActiveTab(this, 1); loadLeads(1, 1);">
                        <i class="ti ti-circle-check me-2"></i>{{__('New')}}
                        <span class="ms-auto badge badge-xs rounded-pill bg-warning"></span>
                    </button>

                    <div class="skeleton input-skeleton input-loader mb-2"></div>
                    <button class="d-flex text-start align-items-center flex-fill fw-medium fs-15 nav-link mb-1 d-none real-input"
                            id="accept-tab"
                            onclick="setActiveTab(this, 2); loadLeads(1, 2);">
                        <i class="ti ti-star me-2"></i>{{__('Accept')}}
                        <span class="ms-auto badge badge-xs rounded-pill bg-success"></span>
                    </button>

                    <div class="skeleton input-skeleton input-loader mb-2"></div>
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

            <div class="col-xl-9 budget-role-notes mt-2">
                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade active show" id="v-pills-profile" role="tabpanel">
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
                                <div class="accordion todo-accordion d-none" id="accordionExample">

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
<div class="modal fade" id="view-note-units">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="page-wrapper-new p-0">
                <div class="content">
                    <div class="modal-header">
                        <div class="d-flex align-items-center">
                            <h4 class="modal-title me-3">Leads</h4>
                            <p class="text-info created-at"></p>
                        </div>
                        <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div>
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h4 class="mb-2">Leads</h4>
                                                    <p class="badge bg-outline-danger d-inline-flex align-items-center mb-0 status">
                                                        <i class="fas fa-circle fs-6 me-1"></i>
                                                    </p>
                                                </div>
                                                <div class="col-md-6 " id="status_div" style="display: none;">
                                                    <a href="#" class="btn btn-success accept_btn" id="accept_btn" data-id="">Accept</a>
                                                    <a href="#" class="btn btn-danger reject_btn" id="reject_btn" data-id="">Reject</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row border border-1">
                                                <div class="col-md-3 border border-1">
                                                    <p class=" mb-2 ">Provider : user_provider </p>
                                                </div>

                                                <div class="col-md-3  border border-1">
                                                    <p class="category mb-2"></p>
                                                </div>
                                                <div class="col-md-3  border border-1">
                                                    <p class="mb-2">Sub category : cleaning plan</p>
                                                </div>
                                                <div class="col-md-3  border border-1">
                                                    <p class="times mb-2"></p>
                                                </div>
                                            </div>
                                            <div class="category-info mt-4">
                                                <h5>Category Info</h5>
                                                <div class="row " id="form-inputs"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-danger" data-bs-dismiss="modal">Close</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
