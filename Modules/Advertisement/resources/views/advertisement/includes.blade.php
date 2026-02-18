@push('scripts')
@if (file_exists(public_path('assets/js/advertisement.js')))
    <script src="{{ asset('assets/js/advertisement.js') }}"></script>
@endif
@endpush
