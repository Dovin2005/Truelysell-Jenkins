<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title> Truelysell - Installer</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('front/img/favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('front/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('front/css/fontawesome-all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('front/vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('global/toastr/toastr.min.css') }}">
	@include('color.color-configuration')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body>
    <main class="container mt-5 main installer-main">
        <h1 class="text-center text-uppercase text-primary mb-3">Installer</h1>

        <div class="row">
        <ul class="progressbar installer-progress-bar">
            <li class="@if (request()->routeIs('setup.verify') || session('step-1-complete')) active @endif">
                <a href="{{route('setup.verify')}}">Verification</a>
            </li>

            <li class="@if (request()->routeIs('setup.requirements') || session('step-2-complete') || session('step-3-complete') || session('step-4-complete') || session('step-5-complete') || session('step-6-complete') || session('step-7-complete')) active @endif">
                <a href="@if (session('step-1-complete')) {{route('setup.requirements')}} @else # @endif" 
                class="@if (!session('step-1-complete')) text-muted @endif">
                    Requirements
                </a>
            </li>

            <li class="@if (request()->routeIs('setup.database') || session('step-3-complete') || session('step-4-complete') || session('step-5-complete') || session('step-6-complete') || session('step-7-complete')) active @endif">
                <a href="@if (session('step-2-complete') && session('requirements-complete')) {{route('setup.database')}} @else # @endif" 
                class="@if (!session('requirements-complete')) text-muted @endif">
                    Database Setup
                </a>
            </li>

            <li class="@if (request()->routeIs('setup.account') || session('step-4-complete') || session('step-5-complete') || session('step-6-complete') || session('step-7-complete')) active @endif">
                <a href="@if (session('step-3-complete')) {{route('setup.account')}} @else # @endif" 
                class="@if (!session('step-3-complete')) text-muted @endif">
                    Account Setup
                </a>
            </li>

            <li class="@if (request()->routeIs('setup.configuration') || session('step-5-complete') || session('step-6-complete') || session('step-7-complete')) active @endif">
                <a href="@if (session('step-4-complete')) {{route('setup.configuration')}} @else # @endif" 
                class="@if (!session('step-4-complete')) text-muted @endif">
                    Configuration
                </a>
            </li>

            <li class="@if (request()->routeIs('setup.complete') || session('step-7-complete')) active @endif">
                <a href="@if (session('step-6-complete')) {{route('setup.complete')}} @else # @endif" 
                class="@if (!session('step-6-complete')) text-muted @endif">
                    Complete
                </a>
            </li>
        </ul>

        </div>
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                @if (isset($errors) && $errors->any())
                    <div class="mb-1 card">
                        <div class="card-body text-danger">
                            {{ $errors->first() }}
                        </div>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>

    </main>
</body>
<script src="{{ asset('global/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('global/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('front/js/bootstrap.min.js') }}"></script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const makeAjaxRequest = (formData, actionUrl) => {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: actionUrl,
                method: "post",
                data: formData,
                success: function(res) {
                    resolve(res);
                },
                error: function(err) {
                    reject(err);
                }
            });
        });
    }
</script>
@stack('scripts')

</html>
