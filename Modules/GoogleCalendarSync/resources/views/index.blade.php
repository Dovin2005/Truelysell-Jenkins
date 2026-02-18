@extends('admin.admin')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">

        {{-- Page Header --}}
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Google Calendar</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Calendar Sync</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Display Success/Error Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
             <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif


        <div class="row">
            <div class="col-lg-10 mx-auto">

                {{-- Connection Status Box --}}
                @if (Auth::user() && Auth::user()->google_calendar_email)
                    <div class="alert alert-success">
                        <h5 class="alert-heading">Connected: Your Google Calendar is connected.</h5>
                        <p>Calendar ID: {{ Auth::user()->google_calendar_email }}</p>
                        <hr>
                        <form action="{{ route('provider.google.disconnect') }}" method="POST" onsubmit="return confirm('Are you sure you want to disconnect your calendar?');">
                            @csrf
                            <button type="submit" class="btn btn-danger">Disconnect Calendar</button>
                        </form>
                    </div>
                @endif
                
                {{-- This form is for the admin to save credentials --}}
                {{-- You can add a check here, e.g., @if(Auth::user()->isAdmin()) --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Google Calendar Credentials <span class="text-muted">(Admin Setup)</span></h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">You can get these from the <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>.</p>
                        
                        <div class="alert alert-warning">
                            <strong>Required Redirect URI</strong><br>
                            Important: You must add this exact redirect URI to your Google Cloud Console project.<br>
                            <code>{{ route('provider.google.callback') }}</code>
                        </div>
                        
                        <h6>Steps to configure:</h6>
                        <ol class="space-y-5 p-2">
                            <li class="flex items-start gap-6">
                                <span class="text-black font-bold text-lg">1.</span>
                                <span class="text-gray-700 font-medium ps-2">
                                    Go to <strong>Google Cloud Console</strong>
                                </span>
                            </li>

                            <li class="flex items-start gap-6 pt-1">
                                <span class="text-black font-bold text-lg">2.</span>
                                <span class="text-gray-700 font-medium ps-2">
                                    Select your <strong>project</strong>
                                </span>
                            </li>

                            <li class="flex items-start gap-6 pt-1">
                                <span class="text-black font-bold text-lg">3.</span>
                                <span class="text-gray-700 font-medium ps-2">
                                    Go to <strong>"APIs & Services"</strong> > <strong>"Credentials"</strong>
                                </span>
                            </li>

                            <li class="flex items-start gap-6 pt-1">
                                <span class="text-black font-bold text-lg">4.</span>
                                <span class="text-gray-700 font-medium ps-2">
                                    Click <strong>"Create Credentials"</strong> in the header
                                </span>
                            </li>

                            <li class="flex items-start gap-6 pt-1">
                                <span class="text-black font-bold text-lg">5.</span>
                                <span class="text-gray-700 font-medium ps-2">
                                    Choose <strong>OAuth Client ID</strong>
                                </span>
                            </li>

                            <li class="flex items-start gap-6 pt-1">
                                <span class="text-black font-bold text-lg">6.</span>
                                <span class="text-gray-700 font-medium ps-2">
                                    Click <strong>"Configure Consent Screen"</strong> and fill in the project details
                                </span>
                            </li>

                            <li class="flex items-start gap-6 pt-1">
                                <span class="text-black font-bold text-lg">7.</span>
                                <span class="text-gray-700 font-medium ps-2">
                                    Click <strong>"Metrics"</strong> → <strong>"Create OAuth Client"</strong>
                                </span>
                            </li>

                            <li class="flex items-start gap-6 pt-1">
                                <span class="text-black font-bold text-lg">8.</span>
                                <span class="text-gray-700 font-medium ps-2">
                                    Select Application Type → <strong>Web Application</strong>
                                </span>
                            </li>

                            <li class="flex items-start gap-6 pt-1">
                                <span class="text-black font-bold text-lg">9.</span>
                                <span class="text-gray-700 font-medium ps-2">
                                    Add the redirect URI to <strong>"Authorized redirect URIs"</strong>
                                </span>
                            </li>

                            <li class="flex items-start gap-6 pt-1">
                                <span class="text-black font-bold text-lg">10.</span>
                                <span class="text-gray-700 font-medium ps-2">
                                    <strong>Save</strong> the changes
                                </span>
                            </li>

                            <li class="flex items-start gap-6 pt-1">
                                <span class="text-black font-bold text-lg">11.</span>
                                <span class="text-gray-700 font-medium ps-2">
                                    Copy the <strong>Client ID</strong> and <strong>Client Secret</strong>
                                </span>
                            </li>

                            <li class="flex items-start gap-6 pt-1">
                                <span class="text-black font-bold text-lg">12.</span>
                                <span class="text-gray-700 font-medium ps-2">
                                    Search <strong>"Google Calendar API"</strong> and enable it
                                </span>
                            </li>

                        </ol>

                        <form action="{{ route('provider.google.credentials.save') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="google_client_id" class="form-label">Google Client ID</label>
                                <input type="text" class="form-control" id="google_client_id" name="google_client_id" value="{{ $credentials->google_client_id ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label for="google_client_secret" class="form-label">Google Client Secret</label>
                                <input type="password" class="form-control" id="google_client_secret" name="google_client_secret" value="{{ $credentials->google_client_secret ?? '' }}">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Credentials</button>
                        </form>
                    </div>
                </div>
                {{-- @endif --}}


                @if (Auth::user() && !Auth::user()->google_calendar_email)
                <div class="card mt-4">
                     <div class="card-header">
                        <h5 class="card-title">Authorize Google Calendar Access</h5>
                    </div>
                    <div class="card-body">
                        <p>Click the button below to authorize this application to access your Google Calendar.</p>
                        <a href="{{ route('provider.google.connect') }}" class="btn btn-danger">
                            <i class="fab fa-google"></i> Authorize Google Calendar
                        </a>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection