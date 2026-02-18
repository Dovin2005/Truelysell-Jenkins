	@extends('front')
	@section('content')

     <!-- Breadcrumb -->
	<div class="breadcrumb-bar text-center">
		<div class="container">
			<div class="row">
				<div class="col-md-12 col-12">
					<h2 class="breadcrumb-title mb-2">Bookings</h2>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb justify-content-center mb-0">
							<li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="ti ti-home-2"></i></a></li>
                            <li class="breadcrumb-item">Customer</li>
							<li class="breadcrumb-item active" aria-current="page">Bookings</li>
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
						
						<!-- Booking Done -->
						<div class="row align-items-center">
							<div class="col-md-5">
								<div class="booking-done">
									<img src="{{ asset('front/img/payment-success.svg') }}" class="img-fluid" alt="image">
								</div>
							</div>
							<div class="col-md-7">
								<div class="booking-done">
									<h6>Sorry we could not now Completed Payment</h6>
									<p>Please check with Administrator</p>					
									<div class="book-submit">
										<a href="{{ (Auth::user()->user_type == 3) ? route('user.profile') : route('provider.dashboard') }}" class="btn btn-dark"><i class="feather-arrow-left-circle"></i> Go to Home</a>
										<!-- <a href="invoice.html" class="btn btn-light">Booking History</a> -->
										<!-- <a class="btn btn-primary" href="{{route('make.payment')}}">Pay $100 via Paypal</a> -->

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


	