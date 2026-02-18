@extends('front')

@section('content')

<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h2 class="breadcrumb-title mb-2">{{__('Dashboard')}}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}"><i class="ti ti-home-2"></i></a></li>
                        <li class="breadcrumb-item">{{__('Customer')}}</li>
                        <li class="breadcrumb-item active" aria-current="page">{{__('Dashboard')}}</li>
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
    <div class="content">
        <div class="container">
            <div class="row justify-content-center">
                @include('user.partials.sidebar')
                <div class="col-xl-9 col-lg-8">
                    <div class="skeleton label-skeleton label-loader"></div>
                    <h4 class="mb-3 d-none real-label">{{__('Dashboard')}}</h4>
                    <div class="row mb-1">
                        <div class="col-md-6">
                            <div class="card dash-widget">
                                <div class="card-body">
                                    <div class="d-flex  justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <span class="dash-icon bg-primary-transparent d-flex justify-content-center align-items-center rounded-circle">
                                                <i class="ti ti-shopping-cart"></i>
                                            </span>
                                            <div class="ms-2">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <span class="fs-14 d-none real-label">{{__('Total Orders')}}</span>
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <h5 class="d-none real-label"><span class="counter totalOrder"></span></h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card dash-widget">
                                <div class="card-body">
                                    <div class="d-flex  justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <span class="dash-icon bg-secondary-transparent d-flex justify-content-center align-items-center rounded-circle">
                                                <i class="ti ti-wallet"></i>
                                            </span>
                                            <div class="ms-2">
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <span class="fs-14 d-none real-label">{{__('Total Spend')}}</span>
                                                <div class="skeleton label-skeleton label-loader"></div>
                                                <h5 class="d-none real-label"><span class="symbol"></span><span class="counter totalSpend"></span></h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="row">

                        <div class="col-xxl-6 col-lg-6 d-flex">
                            <div class="w-100">
                                <div class="skeleton label-skeleton label-loader"></div>
                                <h5 class="mb-3 d-none real-label">{{__('Recent Transaction')}}</h5>
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <tbody class="recentTranction">

                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-6 col-lg-6 d-flex">
                            <div class="w-100">
                                <div class="skeleton label-skeleton label-loader"></div>
                                <h5 class="mb-3 d-none real-label">{{__('Recent Booking')}}</h5>
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <tbody class="recent_booking">


                                        </tbody>
                                    </table>

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