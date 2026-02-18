@extends('provider.provider')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid pb-0">
        <div class="row justify-content-center">
            <div class="col-xxl-6 col-md-6">
                <div class="row flex-fill">
                    <div class="col-6">
                        <div class="card prov-widget">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <p class="mb-1 d-none real-label">{{__('Upcoming Bookings')}}</p>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <h5><span class="counter upcomingcount d-none real-label">0</span></h5>
                                    </div>
                                    <span class="prov-icon bg-info d-flex justify-content-center align-items-center rounded">
                                        <i class="ti ti-calendar-check"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card prov-widget">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <p class="mb-1 d-none real-label">{{__('Completed Bookings')}}</p>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <h5><span class="counter completecount d-none real-label">0</span></h5>
                                    </div>
                                    <span class="prov-icon bg-success d-flex justify-content-center align-items-center rounded">
                                        <i class="ti ti-calendar-check"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row flex-fill">
                    <div class="col-6">
                        <div class="card prov-widget">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <p class="mb-1 d-none real-label">{{__('Order Completed')}}</p>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <h5><span class="counter order_completed_count d-none real-label">0</span></h5>
                                    </div>
                                    <span class="prov-icon bg-success d-flex justify-content-center align-items-center rounded">
                                        <i class="ti ti-calendar-check"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card prov-widget">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <p class="mb-1 d-none real-label">{{__('Canceled Bookings')}}</p>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <h5><span class="counter cancelcount d-none real-label">0</span></h5>
                                    </div>
                                    <span class="prov-icon bg-danger d-flex justify-content-center align-items-center rounded">
                                        <i class="ti ti-calendar-check"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6 col-md-6">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div>
                            <div class="d-flex justify-content-center flex-column mb-3">
                                <div class="skeleton label-skeleton label-loader"></div>
                                <h6 class="text-center d-none real-label">{{__('Total Earnings')}}</h6>
                                <div class="skeleton label-skeleton label-loader"></div>
                                <h5 class="text-center totalincome d-none real-label"> <span class="text-success">
                                        <i class="ti ti-arrow-badge-up-filled"></i>
                                    </span></h5>

                            </div>
                            <div class="d-flex justify-content-around mb-3">
                                <div>
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <p class="mb-0 d-none real-label">{{__('Total Income')}}</p>
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <h5 class="completeincome d-none real-label"></h5>
                                </div>
                                <div>
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <p class="mb-0 d-none real-label">{{__('Total Due')}}</p>
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <h5 class="totaldue d-none real-label"></h5>
                                </div>
                            </div>
                            <div id="daily-chart"></div>
                            <div class="d-flex justify-content-center flex-column">
                                <div class="skeleton label-skeleton label-loader"></div>
                                <a href="{{route('provider.transaction')}}" class="btn btn-dark d-none real-label">{{__('View All Earnings')}}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="skeleton label-skeleton label-loader"></div>
                        <h6 class="mb-3 d-none real-label">{{ __("Subscription")}}</h6>
                        <div class="d-flex gap-3 flex-warp">
                            <div class="bg-light-300 rounded p-3 w-50">
                                <div class="d-flex justify-content-between flex-wrap row-gap-2 mb-2 nosubscribe">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <p class="mb-0 text-dark fw-medium subscribeplan d-none real-label">{{ __('notsubscribe') }}</p>
                                </div>
                                <div class="subscribedpack">
                                    <div class="d-flex justify-content-between flex-wrap row-gap-2 mb-2">
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <span class="badge badge-success d-none real-label">
                                            <i class="ti ti-point-filled"></i>{{ __('Current Plan') }}
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <p class="mb-0 text-dark fw-medium subscribedplantitle"></p>
                                        <span class="description"></span>
                                    </div>
                                    <div class="d-flex mb-2">
                                        <h5 class="me-2 subprice"></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-light-300 rounded p-3 w-50 topup">
                                <div class="d-flex justify-content-between flex-wrap row-gap-2 mb-2">
                                    <div class="skeleton label-skeleton label-loader"></div>
                                    <span class="badge badge-success d-none real-label">
                                        <i class="ti ti-point-filled"></i>{{ __('Top-Up') }}
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <p class="mb-0 text-dark fw-medium topupplantitle"></p>
                                </div>
                                <div class="d-flex mb-2">
                                    <h5 class="me-2 topupprice"></h5>
                                </div>
                            </div>
                            <div class="bg-light-500 rounded p-3 popularplan">
                                <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                                    <div class="">
                                        <p class="mb-0 text-dark fw-medium plantitle"></p>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <span class="d-block mb-2 d-none real-label fs-12">{{ __('popularplan')}}</span>
                                        <div class="skeleton label-skeleton label-loader"></div>
                                        <a href="{{route('provider.subscription')}}" class="text-info d-block d-none real-label fs-10">{{ __('View All Plans')}}</a>
                                    </div>
                                    <div class="d-flex">
                                        <h5 class="planprice"></h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-xxl-6 col-md-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="skeleton label-skeleton label-loader"></div>
                            <h6 class="d-none real-label">{{ __('Top Services')}}</h6>
                            <div class="skeleton label-skeleton label-loader"></div>
                            <a href="{{route('provider.service')}}" class="btn border serviceview d-none real-label">{{ __('View All')}}</a>
                        </div>
                        <div class="servicecard">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6 col-md-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="skeleton label-skeleton label-loader"></div>
                            <h6 class="d-none real-label">{{__('Bookings')}}</h6>
                            <div class="skeleton label-skeleton label-loader"></div>
                            <a href="{{route('provider.bookinglist')}}" class="btn border bookview d-none real-label">{{__('View All')}}</a>
                        </div>
                        <div class="bookcard">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="skeleton label-skeleton label-loader"></div>
                            <h6 class="d-none real-label">{{__('Latest Reviews')}}</h6>
                        </div>
                        <div class="ratecard">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection