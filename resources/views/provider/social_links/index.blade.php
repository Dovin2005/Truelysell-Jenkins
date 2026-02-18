@extends('provider.provider')
@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Social Links Section -->
        <div class="row">
            <div class="col-12">
                <form action="{{ route('provider.sociallinks.bulkUpdate') }}" method="POST">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">{{ __('social_profiles') }}</h5>
                        </div>
                        <div class="card-body">
                            @if ($socialLinks && $socialLinks->count() > 0)
                                @foreach ($socialLinks as $index => $socialLink)
                                    @php
                                        $link = $providerSocialLinks->firstWhere('social_link_id', $socialLink->id);
                                    @endphp
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('platform') }}</label>
                                            <input type="text" class="form-control" value="{{ $socialLink->platform_name }}" disabled>
                                            <input type="hidden" name="profiles[{{ $index }}][social_link_id]" value="{{ $socialLink->id }}">
                                            @if (isset($link->id))
                                                <input type="hidden" name="profiles[{{ $index }}][id]" value="{{ $link->id }}">
                                            @endif
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('URL') }}</label>
                                            <input type="url" name="profiles[{{ $index }}][link]"
                                                class="form-control" placeholder="https://example.com"
                                                value="">
                                        </div>

                                        <div class="col-md-2 d-flex align-items-center mt-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox"
                                                    name="profiles[{{ $index }}][status]"
                                                    {{ isset($link->status) && $link->status ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary">{{ __('save_changes') }}</button>
                                </div>
                            @else
                                <div class="text-center">
                                    <p>{{ __('no_social_profiles_found') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- End Social Links Section -->
@endsection
@push('scripts')
<script src="{{ asset('/front/js/social-link.js') }}"></script>
@endpush