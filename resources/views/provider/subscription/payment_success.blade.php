@extends('provider.provider')
@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">
            <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-4">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('Subscription') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{route('provider.dashboard')}}">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Subscription') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-xxl-12 col-lg-12">
                    <!-- Subscription Cards -->
                    <div id="" class="row g-3">
                        <div id="paymentSuccess" class="d-flex justify-content-center align-items-center vh-80">
                            <div class="card mx-auto subscribe-success-message text-center p-3 py-5">
                                <h3>Payment Successful</h3>
                                <p>Your payment was successful! Click "OK" to proceed to the dashboard.</p>
                                <a href="/provider/dashboard" class="btn btn-primary w-100">OK</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    @endsection