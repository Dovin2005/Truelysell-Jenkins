@extends('admin.admin')

@section('content')
    <!-- Page Wrapper -->
    <form id="adminAddService" enctype="multipart/form-data" method="POST" action="{{ route('savelangword') }}">

    <div class="page-wrapper">
        <div class="content bg-white">
            <div class="d-md-flex d-block align-items-center justify-content-between -bottom pb-3">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('Language Settings') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0);">{{ __('Settings') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Language Settings') }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="pe-1 mb-2">
                        <a href="{{ route('admin.language-settings') }}" class="btn btn-primary me-2">{{ __('Back') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body p-0 py-3">
                    <div class="custom-datatable-filter table-responsive">
                        @csrf
                        <input type="hidden" value="{{ $language_id }}" name="language_id" />
                        <table class="table" id="languagesTableList">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ __('Default Text') }}</th>
                                    <th>{{ __('Language Text') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($listwords as $key => $value)
                                    <tr>
                                        <td>{{ $key }}</td>
                                        <td>
                                            <input type="text" name="lantext[{{ $key }}]" class="form-control" value="{{ $value }}" />
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>

	<!-- /Page Wrapper -->
@endsection










