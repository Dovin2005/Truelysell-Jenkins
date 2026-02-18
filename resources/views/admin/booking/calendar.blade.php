@extends('admin.admin')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">{{ __('calendar')}}</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{route('admin.dashboard')}}">{{ __('Dashboard')}}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">{{ __('application')}}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('calendar')}}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 col-md-8">
                <div class="card bg-white">
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">{{ __('Event Details')}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>{{ __('Title')}}:</strong> <span id="modalTitle"></span> </p>
                <p><strong>{{ __('Date')}}:</strong> <span id="modalDate"></span></p>
				<p><strong>{{ __('Status')}}:</strong> <span id="status"></span> </p>
				<p><strong>{{ __('user')}}:</strong> <span id="user"></span></p>
				<p><strong>{{ __('Location')}}:</strong> <span id="location"></span></p>
				<p><strong>{{ __('Amount')}}:</strong> <span id="amount"></span></p>
				<p><strong>{{ __('Provider')}}:</strong> <span id="provider"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close')}}</button>
            </div>
        </div>
    </div>
</div>

@endsection