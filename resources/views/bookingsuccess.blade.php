	@extends('front')
	@section('content')

     <!-- Breadcrumb -->
	<div class="breadcrumb-bar text-center">
		<div class="container">
			<div class="row">
				<div class="col-md-12 col-12">
					<h2 class="breadcrumb-title mb-2">{{ __('Bookings') }}</h2>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb justify-content-center mb-0">
							<li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="ti ti-home-2"></i></a></li>
                            <li class="breadcrumb-item">{{ __('Customer') }}</li>
							<li class="breadcrumb-item active" aria-current="page">{{ __('Bookings') }}</li>
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
	<!-- /Breadcrumb -->

    <!-- Page Wrapper -->
     <div class="page-wrapper">
        <div class="content">
            <div class="container">
                <div class="row">
					
					<!-- Booking -->
					<div class="col-lg-12">
					
						<!-- Booking Step -->
						<ul class="step-register row">
							<li class=" col-md-4">
								<div class="multi-step-icon">
									<img src="{{ asset('front/img/icons/calendar-icon.svg') }}" alt="img">
								</div>
								<div class="multi-step-info">
									<h6>{{ __('Appointment') }}</h6>
									<p>{{ __('Choose time & date for the service') }}</p>
								</div>
							</li>
							<li class=" col-md-4">
								<div class="multi-step-icon">
									<img src="{{ asset('front/img/icons/wallet-icon.svg') }}" alt="img">
								</div>
								<div class="multi-step-info">
									<h6>{{ __('Payment') }}</h6>
									<p>{{ __('Select Payment Gateway') }}</p>
								</div>
							</li>
							<li class="active col-md-4">
								<div class="multi-step-icon">
									<img src="{{ asset('front/img/icons/book-done.svg') }}" alt="img">
								</div>
								<div class="multi-step-info">
									<h6>{{ __('Done') }} </h6>
									<p>{{ __('Completion of Booking') }}</p>
								</div>
							</li>
						</ul>
						<!-- /Booking Step -->
						
						<!-- Booking Done -->
						<div class="row align-items-center">
							<div class="col-md-5">
								<div class="booking-done">
									<img src="{{ asset('front/img/payment-success.svg') }}" class="img-fluid" alt="image">
								</div>
							</div>
							<div class="col-md-7">
								<div class="booking-done">
									<h6> {{ __('Successfully Completed Payment') }}</h6>
									<p>{{ __('Your Booking has been Successfully Competed') }}</p>					
									<div class="book-submit">
										<a href="{{ (Auth::user()->user_type == 3) ? route('user.dashboard') : route('provider.dashboard') }}" class="btn btn-dark"><i class="feather-arrow-left-circle"></i> {{ __('Go to Home') }}</a>
									</div>
								</div>
							</div>
							
						</div>			
						<!-- /Booking Done -->
						
					</div>
					<!-- /Booking -->
					
				</div>
            </div>
        </div>
     </div>
    <!-- /Page Wrapper -->
 
	@endsection


	