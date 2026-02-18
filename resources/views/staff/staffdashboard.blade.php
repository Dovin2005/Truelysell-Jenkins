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
            <div class="col-xxl-6 col-md-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6>{{ __('Top Services')}}</h6>
                            <a href="{{route('provider.service')}}" class="btn border serviceview">{{ __('View All')}}</a>
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
                            <h6>{{__('Bookings')}}</h6>
                            <a href="{{route('provider.bookinglist')}}" class="btn border bookview">{{__('View All')}}</a>
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
                            <h6>{{__('Latest Reviews')}}</h6>
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
