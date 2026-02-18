@push('scripts')
@if (file_exists(public_path('front/js/coupon.js')))
    <script src="{{ asset('front/js/coupon.js') }}"></script>
@endif
@endpush
