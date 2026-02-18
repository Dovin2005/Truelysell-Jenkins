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
							<li class="active col-md-4">
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

						<!-- Booking Payment -->
						<div class="row">
							<div class="col-lg-6">
								<h5 class="pay-title">{{ __('Payment Methods') }}</h5>
								<div class="row g-3">
								<?php
									if($payment_details_setting_paypal->value=='0' && $payment_details_setting_stripe->value=='0' && $payment_details_setting_bank->value=='0')
									{
									?>
									<div class="col-lg-6">
										<div>Please enable atleast any one Paymentgateway</div>

									</div>

								<?php
									}
									if($payment_details_setting_stripe->value=='1')
									{
									?>
									<div class="col-lg-6">
										<div>
											<input type="radio" onclick="chgAction('stipe')" class="btn-check" name="btnradio" id="btnradio1"  checked>
											<label class="btn  btn-check-label bg-light-500 w-100 d-flex justify-content-between align-items-center" for="btnradio1">
												<span class="d-flex align-items-center"><span class="check-outer me-2"><i></i></span>Stripe</span>
												<span><i class="ti ti-wallet"></i></span>
											</label>
										</div>
									</div>
									<?php
									}
									if($payment_details_setting_paypal->value=='1')
									{
									?>
									<div class="col-lg-6">
										<div>
											<input type="radio" onclick="chgAction('paypal')" class="btn-check" name="btnradio" id="btnradio2" >
											<label class="btn btn-check-label bg-light-500 w-100 d-flex justify-content-between align-items-center" for="btnradio2">
												<span class="d-flex align-items-center"><span class="check-outer me-2"><i></i></span>Paypal</span>
												<span><i class="ti ti-brand-paypal"></i></span>
											</label>
										</div>
									</div>
									<?php
									}
									?>
									<?php

									if($payment_details_setting_cod->value=='1')
									{
									?>
									<div class="col-lg-6">
										<div>
											<input type="radio" onclick="chgAction('cod')" class="btn-check" name="btnradio" id="btnradio8" >
											<label class="btn btn-check-label bg-light-500 w-100 d-flex justify-content-between align-items-center" for="btnradio8">
												<span class="d-flex align-items-center"><span class="check-outer me-2"><i></i></span>COD</span>
												<span><i class="ti ti-cash"></i></span>
											</label>
										</div>
									</div>
									<?php
									}
									?>
<?php

									if($payment_details_setting_walet->value=='1')
									{
									?>
									<?php
                                    $added_amount = 0;

                                    if (!empty($service_offerd)) {
                                        foreach ($service_offerd as $service_offerdValues) {
                                            $actualvalue = explode("_", $service_offerdValues);
                                            if (isset($actualvalue[1]) && is_numeric($actualvalue[1])) {
                                                $added_amount += floatval($actualvalue[1]);
                                            }
                                        }
                                    }

                                    $amount_tax = 0;
                                    $taxdata = [];

                                    foreach ($tax_payment as $tax_payment_values) {
                                        $section_explode = explode("_", $tax_payment_values->key);
                                        $key1 = $section_explode[1];
                                        $key2 = $section_explode[2];
                                        $taxdata[$key1][$key2] = $tax_payment_values->value;
                                    }

                                    $amount_cal = ($products_details1->source_Values * $data['service_qty']) + $added_amount;
                                    foreach ($taxdata['type'] as $tax_payment_values => $kevy) {
                                        $new_width = ($taxdata['rate'][$tax_payment_values] / 100) * $amount_cal;
                                        $amount_tax += $new_width;
                                    }

                                    $totalCost = $amount_cal + $amount_tax;
                                    ?>

                                    <div class="col-lg-6">
                                        <div>
                                            <input type="radio" onclick="chgAction('wallet')" class="btn-check" name="btnradio"
                                                id="btnradio9"
                                                <?php if ($wallet_total_Amount < $totalCost) { echo 'disabled'; }  ?>>
                                            <label
                                                class="btn btn-check-label bg-light-500 w-100 d-flex justify-content-between align-items-center"
                                                for="btnradio9">
                                                <span class="d-flex align-items-center">
                                                    <span class="check-outer me-2"><i></i></span>
                                                    Wallet
                                                </span>
                                                <span><i class="ti ti-brand-wallet"></i></span>
                                            </label>
                                        </div>

                                        <?php if ($wallet_total_Amount < $totalCost): ?>
                                            <div class="alert alert-warning mt-2" role="alert">
                                                Wallet balance (<?php echo $currecy_details->symbol . number_format($wallet_total_Amount, 2); ?>)
                                                is insufficient to complete this purchase (<?php echo $currecy_details->symbol . number_format($totalCost, 2); ?>).
                                            </div>
                                        <?php endif; ?>
                                    </div>

									<?php
									}
									?>

<?php

									if($payment_details_setting_mollie->value=='1')
									{
									?>
									<div class="col-lg-6">
										<div>
											<input type="radio" onclick="chgAction('mollie')" class="btn-check" name="btnradio" id="btnradio10" >
											<label class="btn btn-check-label bg-light-500 w-100 d-flex justify-content-between align-items-center" for="btnradio10">
												<span class="d-flex align-items-center"><span class="check-outer me-2"><i></i></span>Mollie</span>
												<span><i class="ti ti-cash"></i></span>
											</label>
										</div>
									</div>
									<?php
									}
									?>

									<?php
									if($payment_details_setting_bank->value=='1')
									{
									?>
                                    <div class="col-lg-6">
                                        <div>
                                            <input type="radio"
                                                   class="btn-check"
                                                   name="btnradio"
                                                   id="btnradio3"
                                                   onclick="$('#bankTransferModal').modal('show');">
                                            <label class="btn btn-check-label bg-light-500 w-100 d-flex justify-content-between align-items-center" for="btnradio3">
                                                <span class="d-flex align-items-center">
                                                    <span class="check-outer me-2"><i></i></span>Bank Transfer
                                                </span>
                                                <span><i class="ti ti-building-bank"></i></span>
                                            </label>
                                        </div>

                                        <!-- Bank Transfer Modal -->
                                        <div class="modal fade" id="bankTransferModal" tabindex="-1" aria-labelledby="bankTransferModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="bankTransferModalLabel">Upload Payment Proof</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
													<form  enctype="multipart/form-data" name="paybook1" method="POST" action="{{ route('make.payment') }}">

													{{ csrf_field() }}
													<?php
														foreach($service_offerd as $service_offerdValues)
														{

														?>
														<input  name="service_offer[]" type="hidden" value="<?php echo $service_offerdValues; ?>" >

														<?php
														}
														?>
														<input type="hidden" value="{{ $products->id }}" name="product_id" />
														<input type="hidden" value="{{ $data['first_name'] }}" name="first_name" />
														<input type="hidden" value="{{ $data['last_name'] }}" name="last_name" />
														<input type="hidden" value="{{ $data['user_email'] }}" name="user_email" />
														<input type="hidden" value="{{ $data['user_phone'] }}" name="user_phone" />
														<input type="hidden" value="{{ $data['user_city'] }}" name="user_city" />
														<input type="hidden" value="{{ $data['user_state'] }}" name="user_state" />
														<input type="hidden" value="{{ $data['user_postalcode'] }}" name="user_postalcode" />
														<input type="hidden" value="{{ $data['user_notes'] }}" name="user_notes" />
														<input type="hidden" value="{{ $data['service_qty'] }}" name="service_qty" />
														<input type="hidden" value="<?php echo $products_details1->source_Values; ?>" name="service_amount" />

                                                        <div class="modal-body">

															<div class="mb-3">
                                                                <label for="notes" class="form-label">Bank Name</label>
                                                                {{$payment_details_setting_bank1->value}}
                                                            </div>
															<div class="mb-3">
                                                                <label for="notes" class="form-label">Account Number</label>
                                                                {{$payment_details_setting_bank2->value}}
                                                            </div>
															<div class="mb-3">
                                                                <label for="notes" class="form-label">Brnach Code</label>
                                                                {{$payment_details_setting_bank3->value}}
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="notes" class="form-label">Notes</label>
                                                                <input type="text" class="form-control" id="notes" name="notes">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="paymentProof" class="form-label">Upload Proof of Payment</label>
                                                                <input type="file" class="form-control" id="paymentProof" name="payment_proof" accept="image/*,application/pdf">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Upload</button>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
									</div>

							<?php
									}
							?>


</div>
</div>
							<div class="col-lg-6">
								<h5 class="pay-title">{{ __('Booking Summary') }}</h5>
								<div class="summary-box">
									<div class="booking-info">
										<div class="service-book">
											<div class="service-book-img">
												<img src="{{Modules\Product\app\Models\Productmeta::select('source_Values','source_key')->where('product_id','=',$products->id)->where('source_key', 'product_image')->first()->showImage()}}" alt="img">
											</div>
											<div class="serv-profile">
												<span class="badge badge-soft-primary">{{  Modules\Categories\app\Models\Categories::select('name')->where('id','=',$products->source_category)->first()->showcategoryname() }}</span>
												<h2>{{ $products->source_name }}</h2>
												<u>{{ __('Additional Service') }}</u>

												<?php
												$added_amount=0;
												if(count($service_offerd)!=0)
												{
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

												<ul>
													<li class="serv-pro">
														<img src="{{  App\Models\UserDetail::select('first_name','last_name','profile_image')->where('user_id','=',$products->user_id)->first()->showprofilepic() }}" alt="img">
													</li>
													<li class="serv-review"><i class="fa-solid fa-star"></i> <span>{{  Modules\Product\app\Models\Rating::select('rating')->where('product_id','=',$products->id)->sum('rating') }} </span>({{  Modules\Product\app\Models\Rating::select('rating')->where('product_id','=',$products->id)->count() }} {{ __('reviews') }})</li>
													<li class="service-map"><i class="feather-map-pin"></i> {{  App\Models\UserDetail::select('city','state','country')->where('user_id','=',$products->user_id)->first()->showaaddress() }}</li>
												</ul>
											</div>
										</div>
									</div>
                                    <?php
                                    //print_r($data);
									$amount_tax=0;
									$taxdata=[];
									foreach($tax_payment as $tax_payment_values)
											{
												$section_explode=explode("_",$tax_payment_values->key);
												$key1=$section_explode[1];
												$key2=$section_explode[2];
												$taxdata[$key1][$key2]=$tax_payment_values->value;
											}
                                    ?>

									<div class="booking-summary">
										<ul class="booking-date">

											<li> {{ __('Service Provider') }} <span>{{  App\Models\UserDetail::select('first_name','last_name')->where('user_id','=',$products->user_id)->first()->showname() }}</span></li>
										</ul>
										<ul class="booking-date">
											<li>{{ __('Subtotal') }} <span>{{$currecy_details->symbol}}<?php $subtotal=$products_details1->source_Values*$data['service_qty']; ?><?php echo number_format($subtotal,2); ?></span></li>
											<li>{{ __('Additional Service') }}<span>{{$currecy_details->symbol}}<?php echo number_format($added_amount,2); ?></span></li>
											<?php
											$amount_cal=($products_details1->source_Values*$data['service_qty'])+$added_amount;
												foreach($taxdata['type'] as $tax_payment_values=> $kevy)
											{
											?>
												<li> <?php echo $kevy; ?><span><?php

												$new_width = ($taxdata['rate'][$tax_payment_values] / 100) * $amount_cal;
												echo $currecy_details->symbol."".number_format($new_width,2);
												$amount_tax=$amount_tax+$new_width;
												?></span></li>
											<?php
											}
											?>
										</ul>
										<div class="booking-total">
											<ul class="booking-total-list">
												<li>
													<span>{{ __('Total') }}</span>
													<span class="total-cost">{{$currecy_details->symbol}}<?php $diplatotal=($products_details1->source_Values*$data['service_qty'])+$added_amount+$amount_tax; echo number_format($diplatotal,2); ?>    </span>
												</li>
											</ul>
										</div>
									</div>
								</div>
								<!--
								<div class="booking-coupon">
									<div class="form-group w-100">
										<div class="coupon-icon">
											<input type="text" class="form-control" placeholder="Coupon Code">
											<span><img src="{{ asset('front/img/icons/coupon-icon.svg') }}" alt="image"></span>
										</div>
									</div>
									<div class="form-group">
										<button  class="btn btn-dark">Apply</button>
									</div>
								</div>
											-->
								<div class="booking-pay">
								<form  enctype="multipart/form-data" name="paybook" method="POST" action="{{ route('stripecheckout') }}">
									{{ csrf_field() }}
									<?php
												foreach($service_offerd as $service_offerdValues)
												{

												?>
												<input  name="service_offer[]" type="hidden" value="<?php echo $service_offerdValues; ?>" >

												<?php
												}
												?>

									<input type="hidden" value="{{ $amount_tax }}" name="amount_tax" />
									<input type="hidden" value="{{ $products->id }}" name="product_id" />
									<input type="hidden" value="{{ $data['first_name'] }}" name="first_name" />
									<input type="hidden" value="{{ $data['last_name'] }}" name="last_name" />
									<input type="hidden" value="{{ $data['user_email'] }}" name="user_email" />
									<input type="hidden" value="{{ $data['user_phone'] }}" name="user_phone" />
									<input type="hidden" value="{{ $data['user_city'] }}" name="user_city" />
									<input type="hidden" value="{{ $data['user_state'] }}" name="user_state" />
									<input type="hidden" value="{{ $data['user_postalcode'] }}" name="user_postalcode" />
									<input type="hidden" value="{{ $data['user_notes'] }}" name="user_notes" />
									<input type="hidden" value="{{ $data['service_qty'] }}" name="service_qty" />
									<input type="hidden" value="<?php echo $products_details1->source_Values; ?>" name="service_amount" />
									<?php
									if($payment_details_setting_paypal->value=='1' || $payment_details_setting_stripe->value=='1'|| $payment_details_setting_bank->value=='1')
									{
									?>
									<button type="submit" class="btn btn-dark">{{ __('Proceed to Pay') }} {{$currecy_details->symbol}}<?php $finpay=($products_details1->source_Values*$data['service_qty'])+$added_amount+$amount_tax; echo number_format($finpay,2); ?></button>
<?php
									}
?>

										<a href="javascript:void(0);" class="btn btn-light">{{ __('Cancel') }}</a>
								</form>
								</div>
							</div>
						</div>
						<!-- /Booking Payment -->

					</div>
					<!-- /Booking -->

				</div>
            </div>
        </div>
     </div>
	@endsection
<script>
	 function chgAction( action_name )
	{
		if( action_name=="stipe" ) {
			document.paybook.action = "<?php  echo url(''); ?>/stripecheckout";
		}
		if( action_name=="paypal" ) {
			document.paybook.action = "<?php  echo url(''); ?>/handle-payment";
		}
		if( action_name=="cod" ) {
			document.paybook.action = "<?php  echo url(''); ?>/handle-cod-payment";
		}
        if( action_name=="wallet" ) {
			document.paybook.action = "<?php  echo url(''); ?>/handle-wallet-payment";
		}
		if( action_name=="mollie" ) {
			document.paybook.action = "<?php  echo url(''); ?>/preparePayment";
		}

	}
	</script>
