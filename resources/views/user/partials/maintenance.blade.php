<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>{{$companyName}}</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('front/img/favicon.svg') }}">

    <link rel="stylesheet" href="{{ asset('front/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('front/css/bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{ asset('front/plugins/tabler-icons/tabler-icons.css') }}">

    <link rel="stylesheet" href="{{ asset('front/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('front/plugins/fontawesome/css/all.min.css') }}">

	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('front/css/style.css') }}">
</head>

<body>
		<div class="main-wrapper">

			<div class="container">

				<div class="authentication-header">
				<div class="container">
					<div class="col-md-12">
						<div class="text-center">
                        <img src="{{ $dynamicLogo }}" class="img-fluid" alt="Logo">
					</div>
				</div>
			</div>


			<div class="page-wrapper">
					<div class="content">
						<div class="maintenance">
							<div class="row ">
                                <div class="col-md-6 d-flex justify-content-center align-items-center">
                                    <div class="maintenance-content">
                                        <div class="maintenance-title">
                                            {!! $maintenanceContent->value !!}
                                        </div>
                                    </div>
                                </div>
								<div class="col-md-6">
									<div class="maintenance-img">
										<img src="/front/img/bg/maintenance.png" alt="img" class="img-fluid">
									</div>
								</div>
							</div>
						</div>
					</div>
           </div>



		</div>


	<div class="xb-cursor tx-js-cursor">
		<div class="xb-cursor-wrapper">
			<div class="xb-cursor--follower xb-js-follower"></div>
		</div>
	</div>

    <script src="{{ asset('front/js/jquery-3.7.1.min.js') }}"></script>

    <script src="{{ asset('front/js/bootstrap.bundle.min.js') }}"></script>

    <script src="{{ asset('front/js/script.js') }}"></script>
</body>

</html>
