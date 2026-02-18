	@extends('front')
	@section('content')
    <!-- Breadcrumb -->
	<div class="breadcrumb-bar text-center">
		<div class="container">
			<div class="row">
				<div class="col-md-12 col-12">
					<h2 class="breadcrumb-title mb-2">Cart</h2>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb justify-content-center mb-0">
							<li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="ti ti-home-2"></i></a></li>
							<li class="breadcrumb-item active" aria-current="page">Cart</li>
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
                    
					<div class="col-xl-12 col-lg-8">
						
						<div class="row">
							<div class="col-md-12">
                            <?php
                            $total=0;
                            foreach($shoppingCart as $shoppingCartValues)
                            {
                                $productId=$shoppingCartValues['productId'];
                                $total=$total+$shoppingCartValues['amount'];

                            ?>
								<!-- Service List -->
								<div class="service-list" id="prodsec{{$productId}}">
									<div class="service-cont">
										<div class="service-cont-img">
												<img class="img-fluid serv-img" alt="Service Image" src="{{Modules\Product\app\Models\Productmeta::select('source_Values','source_key')->where('product_id','=',$productId)->where('source_key', 'product_image')->first()->showImage()}}">
											
											
										</div>
										<div class="service-cont-img">
											<h5 class="title">
												
                                                  {{ Modules\Product\app\Models\Product::select('id','source_name')->where('id','=',$productId)->first()->showproductname() }}
											</h5>
											
										</div>
                                        <div class="service-cont-img">
                                        <h6>{{$currecy_details->symbol}}{{ Modules\Product\app\Models\Productmeta::select('source_Values','source_key')->where('product_id','=',$productId)->whereIn('source_key', ['Fixed', 'Minitue', 'Squre-metter','Hourly', 'Squre-Feet'])->first()->showPrice() }}</h6>											
										</div>
                                        <div class="service-cont-img">
                                        <input  type="number" name="user_qty" id="user_qty" value="1" class="form-control">
										</div>
                                      
									</div>
									<div class="service-action">
                                    <h6>{{$currecy_details->symbol}}{{ Modules\Product\app\Models\Productmeta::select('source_Values','source_key')->where('product_id','=',$productId)->whereIn('source_key', ['Fixed', 'Minitue', 'Squre-metter','Hourly', 'Squre-Feet'])->first()->showPrice() }}</h6>											

                                    <div class="user-icon d-inline-flex">
												<a href="javascript:void(0);" onclick="removefromcart({{ $productId }})" class=""><i class="ti ti-trash"></i></a>
											</div>
									</div>
								</div>
								<!-- /Service List -->
							<?php
                            }
                            ?>
								
							
							</div>
						</div>
						
					</div>
                </div>
                <div class="row">
							<div class="col-lg-6">
                                
                            </div> 
							<div class="col-lg-6">
                            <h5 class="pay-title">Cart Total</h5>
                            <div class="booking-summary">
										
										<ul class="booking-date">
											<li>Subtotal <span>{{$currecy_details->symbol}}{{$total}}</span></li>
											
										</ul>
										<div class="booking-total">
											<ul class="booking-total-list">
												<li>
													<span>Total</span>
													<span class="total-cost">{{$currecy_details->symbol}}{{$total}}</span>
												</li>
											</ul>
										</div>
									</div>
                                    <div class="booking-pay">
                                        
                            <button type="submit" class="btn btn-dark">Proceed to Checkout </button>

                            </div> 
                            </div> 
                           

                </div>              
            </div>
        </div>
     </div>
     <!-- /Page Wrapper -->


	@endsection


	