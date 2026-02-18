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
							<li class="breadcrumb-item"><a href="{{ route('home') }}
							"><i class="ti ti-home-2"></i></a></li>
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
							<li class="active col-md-4">
								<div class="multi-step-icon">
									<img src="{{ asset('front/img/icons/calendar-icon.svg') }}" alt="img">
								</div>
								<div class="multi-step-info">
									<h6>{{ __('Appointment') }}</h6>
									<p>{{ __('Choose time & date for the service') }}</p>
								</div>
							</li>
							<li class="col-md-4">
								<div class="multi-step-icon">
									<img src="{{ asset('front/img/icons/wallet-icon.svg') }}" alt="img">
								</div>
								<div class="multi-step-info">
									<h6>{{ __('Payment') }}</h6>
									<p>{{ __('Select Payment Gateway') }}</p>
								</div>
							</li>
							<li class="col-md-4">
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
						
						<!-- Appointment -->
						<div class="booking-service card shadow-none">
							<div class="card-body">
								<div class="row align-items-center">
									<div class="col-lg-5">
										<div class="d-flex align-items-center">
											<div class="flex-shrink-0 service-img me-3">
												<img src="{{Modules\Product\app\Models\Productmeta::select('source_Values','source_key')->where('product_id','=',$products->id)->where('source_key', 'product_image')->first()->showImage()}}" alt="img">
											</div>
											<div class="serv-profile">
												<span class="badge badge-soft-primary">{{  Modules\Categories\app\Models\Categories::select('name')->where('id','=',$products->source_category)->first()->showcategoryname() }}</span>
												<h5 class="my-2">{{ $products->source_name }}</h5>
												<?php
												$added_amount=0;
												if(count($service_offerd)!=0)
												{
												//echo count($service_offerd); exit();
												foreach($service_offerd as $service_offerdValues)
												{
													$actualvalue=explode("_",$service_offerdValues);
													$added_amount=$added_amount+$actualvalue[1];
												?>

												<p><?php echo $actualvalue[0]; ?></p>
												<?php
												}
												}
												?>
												
												<div class="d-flex align-items-center">
													<span class="avatar avatar-md rounded-circle me-2"><img src="{{  App\Models\UserDetail::select('first_name','last_name','profile_image')->where('user_id','=',$products->user_id)->first()->showprofilepic() }}" class="rounded-circle" alt="img"></span>
													<div class="serv-pro-info">
														<h6 class="fs-14 fw-medium">{{  App\Models\UserDetail::select('first_name','last_name')->where('user_id','=',$products->user_id)->first()->showname() }}</h6>
														<p class="serv-review"><i class="fa-solid fa-star"></i> <span>{{  Modules\Product\app\Models\Rating::select('rating')->where('product_id','=',$products->id)->sum('rating') }} </span>({{  Modules\Product\app\Models\Rating::select('rating')->where('product_id','=',$products->id)->count() }} {{ __('reviews') }} )</p>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-lg-5">
										<div class="row">
											<div class="col-lg-6">
												<div class="">
													<div class="provide-box d-flex align-items-center mb-3">
														<span class="me-2"><i class="feather-phone"></i></span>
														<div class="provide-info">
															<h6 class="fs-14 fw-medium mb-1">{{ __('phone_number') }}</h6>
															<p>+xx xxx xx xxx</p>
														</div>
													</div>
													<div class="provide-box d-flex align-items-center">
														<span class="me-2"><i class="feather-mail"></i></span>
														<div class="provide-info">
															<h6 class="fs-14 fw-medium mb-1">{{ __('email') }}</h6>
															<p>xxx@xxx.xx</p>
														</div>
													</div>
												</div>
											</div>
											<div class="col-lg-6">
												<div class="">
													<div class="provide-box d-flex align-items-center mb-3">
														<span class="me-2"><i class="feather-map-pin"></i></span>
														<div class="provide-info">
															<h6 class="fs-14 fw-medium mb-1">{{ __('address') }}</h6>
															<p>xxxxx, xxxxx</p>
														</div>
													</div>
													<div class="provide-box d-flex align-items-center">
														<span class="me-2"><i class="ti ti-wallet"></i></span>
														<div class="provide-info">
															<h6 class="fs-14 fw-medium mb-1">{{ __('Service Amount') }}</h6>
															<h5>{{$currecy_details->symbol}}{{ $products_details1->source_Values+$added_amount }}</h5>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-lg-2">
										<div class="d-flex align-items-center social">
											<a href="javascript:void(0);" class="d-flex align-items-center justify-content-center"></a>
										</div>
									</div>
								</div>
							</div>
						</div>						
						<div class="book-form border-top border-bottom pt-4 pb-2">
						<form  enctype="multipart/form-data" method="POST" action="{{ route('bookingpayment') }}">
						<?php
												foreach($service_offerd as $service_offerdValues)
												{
													
												?>
												<input  name="service_offer[]" type="hidden" value="<?php echo $service_offerdValues; ?>" > 

												<?php
												}
												?>
                            <div class="row">
								<div class="col-md-4">		
                                <div class="provide-box d-flex align-items-center mb-3">
														<div class="provide-info">
															<h6 class="fs-14 fw-medium mb-1">{{ __('Service Amount') }}
                                                            </h6>
															<p>{{$currecy_details->symbol}}{{ $products_details1->source_Values }}
                                                            </p>
														</div>
													</div>
								</div>
								<div class="col-md-4">	 
									<div class="mb-3">		
										<label class="form-label">Unit</label>					
                                        <input <?php if($product_type=='Fixed') { ?> readonly="true" <?php } ?> type="number" name="user_qty" id="user_qty" value="1" class="form-control">

									</div>
								</div>
								<div class="col-md-4">		
                                <div class="provide-box d-flex align-items-center mb-3">
														<div class="provide-info">
															<h6 class="fs-14 fw-medium mb-1">{{ __('Service Amount') }}
                                                            </h6>
															<p>{{$currecy_details->symbol}}{{ $products_details1->source_Values }}
                                                            </p>
														</div>
													</div>
							</div>
							
							{{ csrf_field() }}  
							<input type="hidden" value="{{ $products->id }}" name="product_id" />

							<label for="" class="fw-bold mb-2">{{ __('Additional information') }} :</label>
                            <div class="row">
								<div class="col-md-4">		
									<div class="mb-3">		
										<label class="form-label">{{ __('first_name') }}</label>
                                        <input type="text" name="first_name" required class="form-control">

									</div>
								</div>
								<div class="col-md-4">	
									<div class="mb-3">		
										<label class="form-label">{{ __('last_name') }}</label>					
                                        <input type="text" name="last_name" required class="form-control">

									</div>
								</div>
								<div class="col-md-4">		
									<div class="mb-3">		
										<label class="form-label">{{ __('email') }}</label>					
                                        <input type="email" name="user_email" required class="form-control">
									</div>
								</div>
							</div>    
							
							<div class="row">
								<div class="col-md-4">		
									<div class="mb-3">		
										<label class="form-label">{{ __('phone_number') }}</label>
                                        <input type="text" required name="user_phone" class="form-control">

									</div>
								</div>
								<div class="col-md-4">	
									<div class="mb-3">		
										<label class="form-label">{{ __('address') }}</label>					
                                        <input type="text" required name="user_address" class="form-control">

									</div>
								</div>
								<div class="col-md-4">		
									<div class="mb-3">		
										<label class="form-label">{{ __('city') }}</label>					
                                        <input type="text" required name="user_city" class="form-control">
									</div>
								</div>
							</div>                           

							<div class="row">
								<div class="col-md-4">		
									<div class="mb-3">		
										<label class="form-label">{{ __('state') }}</label>
                                        <input type="text" required name="user_state" class="form-control">

									</div>
								</div>
								<div class="col-md-4">	
									<div class="mb-3">		
										<label class="form-label">{{ __('postal_code') }}</label>					
                                        <input type="text" required name="user_postalcode"  class="form-control">

									</div>
								</div>
								<div class="col-md-4">		
									<div class="mb-3">		
										<label class="form-label">{{ __('Notes') }}</label>					
                                        <input type="text" name="user_notes"  class="form-control">
									</div>
								</div>
							</div>                           

						</div>

                            
                        

                                              
						<!-- /Appointment -->						
						
						<!-- Appointment Date & Time -->
						<div class="row">
							<div class="col-lg-4" style="display: none;">	
								<div class="book-title">	
									<h5>Appointment Date</h5>
								</div>
								<div class="card">
									<div class="card-body p-2 pt-3">
										<div id="datetimepickershow"></div>
									</div>
								</div>
							</div>
							<div class="col-lg-8">	
								<div class="row" style="display: none;">	
									<div class="col-md-12">	
										<div class="book-title">	
											<h5>Appointment Time</h5>
										</div>
									</div>
								</div>
								<div class="token-slot mt-2" style="display: none;">
                                   <?php
								   foreach($products_details2 as $slotvalues)
								   {
									//print_r($slotvalues); exit();
								   ?>
									<div class="form-check-inline visits me-0">
                                       <label class="visit-btns">
                                           <input type="radio" class="form-check-input" name="appintment">
                                           <span class="visit-rsn"><?php echo $slotvalues->source_Values ?></span>
                                       </label>
                                   </div>
									<?php
								   }
									?>
                                   
								</div>
								<div class="d-flex align-items-center justify-content-end mt-4">
									<a href="javascript:void(0);" class="btn btn-light me-2">{{ __('Cancel') }}</a>
									<button type="submit" class="btn btn-dark">{{ __('Book Appointment') }}</button>
								</div>															
							</div>
						</div>
						</form>

						<!-- /Appointment Date & Time -->
						
					</div>
					<!-- /Booking -->
					
				</div>
            </div>
        </div>
     </div>
    <!-- /Page Wrapper -->

	@endsection


	