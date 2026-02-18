@push('scripts')
@if (file_exists(public_path('assets/js/coupon.js')))
    <script src="{{ asset('assets/js/coupon.js') }}"></script>
@endif
@endpush
