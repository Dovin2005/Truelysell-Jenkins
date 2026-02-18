@extends('front')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
	<div class="content">
		<div class="container">

			<!-- Booking -->
			<div class="row">
				<div class="col-xxl-10 col-xl-11 mx-auto">
					<div class="card border-0 mb-0">
						<div class="card-body p-3 fieldset-wizard">
							<div class="row">

								<!-- Booking Sidebar -->
								<div class="col-lg-3 theiaStickySidebar">
									<div class="card bg-dark booking-sidebar mb-4 mb-lg-0">
										<div class="card-body">
											<div class="booking-wizard">
												<h6 class="text-white fs-14 mb-3">Bookings</h6>
												<ul class="wizard-progress" id="bokingwizard">
													<li class="addservice activated pb-3">
														<span>1. Additional Services</span>
													</li>
													<li class="datetime  activated pb-3">
														<span>2. Date & Time</span>
													</li>
													<li class="prinfo  activated pb-3">
														<span>3. Personal Information</span>
													</li>
													<li class="cart  activated pb-3">
														<span>4. Cart</span>
													</li>
													<li class="pay  activated pb-3">
														<span>5. Payment</span>
													</li>
													<li class="confime activated ">
														<span>6. Confirmation</span>
													</li>
												</ul>
											</div>
										</div>
									</div>
								</div>
								<!-- /Booking Sidebar -->

								<div class="col-lg-9">

									<!-- Confirmation -->
									<fieldset class="booking-content" id="first-field">
										<div class="book-card">
											<h6 class="fs-16 me-2 mb-3">Payment Confirmation</h6>
											<div class="card">
												<div class="card-body">
													<div class="card shadow-none mb-0">
														<div class="card-body p-3">
															<p class="fw-bold text-success">Booking is successful! Your booking has been confirmed.</p>
														</div>
													</div>
													<div class="d-flex align-items-center justify-content-center flex-wrap">
														<a href="{{ route('user.dashboard') }}" class="btn btn-sm btn-primary d-inline-flex align-items-center mt-3"><i class="ti ti-circle-plus me-1"></i>Go to Dashboard</a>
													</div>
												</div>
											</div>
										</div>
									</fieldset>
									<!-- /Confirmation -->

								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /Booking -->

		</div>
	</div>
</div>
<!-- /Page Wrapper -->

<!-- Cursor -->
<div class="xb-cursor tx-js-cursor">
	<div class="xb-cursor-wrapper">
		<div class="xb-cursor--follower xb-js-follower"></div>
	</div>
</div>
<!-- /Cursor -->
@endsection