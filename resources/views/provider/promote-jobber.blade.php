@extends('provider.provider')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">

        {{-- PAGE HEADER --}}
        <div class="row">
            <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-4">
                <div class="my-auto mb-2">
                    <h3 class="page-title mb-1">{{ __('pomote_profile') }}</h3>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('provider.dashboard') }}">{{ __('Dashboard') }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ __('pomote_profile') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        {{-- PACKAGES --}}
        <div class="row justify-content-center g-4">
            
            @forelse ($packages as $plan)
            <div class="col-xl-4 col-lg-4 col-md-6">
                <div class="card border-0 shadow-lg h-100 pricing-card">

                    {{-- Card Header --}}
                    <div class="card-header text-center bg-primary text-white py-4">
                        <h5 class="mb-1 fw-semibold">
                            {{ $plan->package_title }}
                        </h5>
                    </div>

                    {{-- Card Body --}}
                    <div class="card-body text-center px-4">

                        <h1 class="fw-bold my-4 text-dark">
                            {{ $currencySymbol }}{{ number_format($plan->price, 2) }}
                        </h1>

                        <ul class="list-unstyled text-start mb-4">
                            <li class="mb-2">
                                <i class="ti ti-check text-success me-2"></i>
                                {{ $plan->package_duration }} {{ $plan->package_term }} {{ __('promote') }}
                            </li>

                            @if ($plan->description)
                            <li class="mb-2">
                                <i class="ti ti-check text-success me-2"></i>
                                {!! $plan->description !!}
                            </li>
                            @endif
                        </ul>

                        {{-- ACTIVE / BUY BUTTON --}}
                        @if($activePromotion && $activePromotion->package_id == $plan->id)
                        <span class="badge bg-success mb-3 px-3 py-2">
                            Active Plan
                        </span>

                        <button class="btn btn-success w-100 mt-2" disabled>
                            Active Until {{ $activePromotion->expires_at->format('d M Y') }}
                        </button>
                        @else
                        <button class="btn btn-primary btn-lg w-100 openPaymentModal" data-package-id="{{ $plan->id }}"
                            data-bs-toggle="modal" data-bs-target="#paymentModal">
                            <i class="ti ti-rocket me-1"></i>
                            Promote Now
                        </button>
                        @endif

                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <p class="text-muted">
                    No promote provider packages available
                </p>
            </div>
            @endforelse
        </div>
    </div>
</div>
<input type="hidden" id="hasActivePlan" value="{{ $activePromotion ? 1 : 0 }}">
<input type="hidden" id="activePackageId" value="{{ $activePromotion->package_id ?? '' }}">


{{-- PAYMENT MODAL --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-light">
                <h5 class="modal-title fw-semibold">
                    {{ __("Select Payment Gateway") }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-4">
                <form id="payment">
                    @csrf

                    <input type="hidden" name="package_id" class="package_id">

                    <div id="subscription-warning" class="alert alert-warning d-none"></div>

                    <div class="mb-4">
                        @php
                        $methods = [
                        'stripe_status' => 'Stripe',
                        'paypal_status' => 'PayPal',
                        ];
                        @endphp

                        @foreach($methods as $key => $label)
                        @if(!empty($paymentInfo[$key]) && $paymentInfo[$key] == 1)
                        <label class="d-flex align-items-center border rounded p-3 mb-3 payment-option">
                            <input class="form-check-input me-3" type="radio" name="payment_method"
                                value="{{ str_replace('_status','',$key) }}">
                            <strong>{{ $label }}</strong>
                        </label>
                        @endif
                        @endforeach
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="payBtn">
                            <span id="payText">
                                <i class="ti ti-lock me-1"></i>
                                Pay Securely
                            </span>
                            <span id="payLoader" class="d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
// Open modal & set package id
$(document).on('click', '.openPaymentModal', function () {
    let selectedPackageId = $(this).data('package-id');
    let hasActivePlan = $('#hasActivePlan').val();
    let activePackageId = $('#activePackageId').val();

    $('.package_id').val(selectedPackageId);
    $('input[name="payment_method"]').prop('checked', false);

    // Reset warning
    $('#subscription-warning').addClass('d-none');

    if (hasActivePlan == 1 && activePackageId != selectedPackageId) {
        $('#subscription-warning')
            .removeClass('d-none')
            .text('Your current plan will be expired if you continue with this purchase.');
    }
});


$('#payment').on('submit', function(e) {
    e.preventDefault();

    let method = $('input[name="payment_method"]:checked').val();

    if (!method) {
        $('#subscription-warning')
            .removeClass('d-none')
            .text('Please select a payment method');
        return;
    }

    $('#payBtn').prop('disabled', true);
    $('#payText').addClass('d-none');
    $('#payLoader').removeClass('d-none');

    $.ajax({
        url: "{{ route('provider.promote.jobber.pay') }}",
        type: "POST",
        data: $(this).serialize(),
        success: function(res) {
            if (res.url) {
                window.location.href = res.url;
            }
        },
        error: function(xhr) {
            $('#subscription-warning')
                .removeClass('d-none')
                .text(xhr.responseJSON?.message ?? 'Payment failed');

            $('#payBtn').prop('disabled', false);
            $('#payText').removeClass('d-none');
            $('#payLoader').addClass('d-none');
        }
    });
});

</script>
@endpush