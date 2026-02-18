@extends('front')

@section('content')

	<!-- Breadcrumb -->
	<div class="breadcrumb-bar text-center">
		<div class="container">
			<div class="row">
				<div class="col-md-12 col-12">
					<h2 class="breadcrumb-title mb-2">Favourites</h2>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb justify-content-center mb-0">
							<li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="ti ti-home-2"></i></a></li>
							<li class="breadcrumb-item">Customer</li>
							<li class="breadcrumb-item active" aria-current="page">Favourites</li>
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
				<div class="row justify-content-center">
                  @include('user.partials.sidebar')

					<div class="col-xl-9 col-lg-8">
						<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
							<h4>Favorites</h4>
							
						</div>
						<div class="row justify-content-center align-items-center">
						
                        <?php foreach($faourlist as $faourlistValues) { ?>
							<div class="col-xxl-4 col-md-6">
								<div class="card p-0">
									<div class="card-body p-0">
										<div class="img-sec-2 w-100">
											<a href="service-details.html"><img src="{{ route('home') }}/storage/{{Modules\Product\app\Models\Productmeta::select('source_Values','source_key')->where('product_id','=',$faourlistValues->product_id)->where('source_key', 'product_image')->first()->showImage()}}" class="img-fluid rounded-top w-100" alt="img"></a>
											<div class="image-tag d-flex justify-content-end align-items-center">
												
												<a href="javascript:void(0);" class="like-icon d-flex justify-content-center align-items-center"><i class="ti ti-heart-filled filled"></i></a>
												<span class="trend-tag-2">Construction</span>
											</div>
										</div>
										<div class="img-content p-3">
											<h6 class="fs-16 mb-3 text-truncate"><a href="service-details.html">
                                            {{  Modules\Product\app\Models\Product::select('source_name','id')->where('id','=',$faourlistValues->product_id)->first()->showproductname() }}    
                                            </a></h6>
											<div class="d-flex justify-content-between align-items-center">
												<div class="d-flex">
													<span class="avatar avatar-md me-2">
														<img src="assets/img/user/user-09.jpg" class="rounded-circle" alt="user">
													</span>
													<div>
														<h6 class="fs-14">Roberts</h6>
														<span class="fs-12"><i class="ti ti-map-pin me-1"></i>Houston, USA</span>
													</div>
												</div>
												<div class="d-flex justify-content-center align-items-center">
													<a href="service-details.html" class="btn btn-light btn-sm ">View Details</a>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
                            <?php 
                            }
                            ?>
						</div>
					

					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /Page Wrapper -->



@endsection
