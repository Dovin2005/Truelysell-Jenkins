@extends('front')
@section('content')
<div class="breadcrumb-bar text-center">
	<div class="container">
		<div class="row">
			<div class="col-md-12 col-12">
				<h2 class="breadcrumb-title mb-2">{{ __('Wallet')}}</h2>
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb justify-content-center mb-0">
						<li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="ti ti-home-2"></i></a></li>
						<li class="breadcrumb-item">{{ __('Leads')}}</li>
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

<div class="page-wrapper">
	<div class="content">
		<div class="container">
			<div class="row justify-content-center">
				@include('user.partials.sidebar')
				<div class="col-xl-9 col-lg-8">
					<div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-4">
						<h4>{{__('Wallet')}}</h4>
						<div class="col-12">
							<div class="card">
								<div class="card-header">
									<div class="row align-items-center">
										<h6> {{__('Success')}}</h6>
										<p> {{__('Your Payment has been Successfully Competed')}}</p>
										<div class="book-submit">
											<a href="{{ (Auth::user()->user_type == 3) ? route('user.wallet') : route('user.wallet') }}" class="btn btn-dark"><i class="feather-arrow-left-circle"></i>  {{__('Go to Wallet')}}</a>
										</div>
									</div>
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
