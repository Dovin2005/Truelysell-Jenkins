@push('scripts')
@if (file_exists(public_path('assets/js/chatbot.js')))
    <script src="{{ asset('assets/js/chatbot.js') }}"></script>
@endif
@endpush
