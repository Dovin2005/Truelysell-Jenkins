@extends('provider.provider')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-4">
                    <div class="my-auto mb-2">
                        <div class="skeleton label-skeleton label-loader"></div>
                        <h3 class="page-title mb-1 d-none real-label">{{ __('Subscription') }}</h3>
                        <div class="skeleton label-skeleton label-loader"></div>
                        <nav class="d-none real-label">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('provider.dashboard') }}">{{ __('Dashboard') }}</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">{{ __('Subscription') }}</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex align-items-center justify-content-end">
                        @if ($activeSubscription && $activeSubscription->status == 1 && $activeSubscription->amount > 0)
                            <div class="skeleton label-skeleton label-loader"></div>
                            <button class="btn btn-danger d-none real-label" data-bs-toggle="modal"
                                data-bs-target="#cancel_subscription">Cancel Subscription</button>
                        @endif
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-xxl-12 col-lg-12">
                        <!-- Tabs -->
                        <div class="tabs mb-4">
                            <div class="skeleton label-skeleton label-loader"></div>
                            <button class="tab-link btn btn-primary me-2 d-none real-label" id="regularTab"
                                onclick="loadTabData('regular')">{{ __('Subscription') }}</button>
                            <div class="skeleton label-skeleton label-loader"></div>
                            <button class="tab-link btn btn-secondary d-none real-label" id="topupTab"
                                onclick="loadTabData('topup')">{{ __('topup') }}</button>
                        </div>
                        <!-- Subscription Cards -->
                        <div id="subscriptionCards" class="row g-3 d-none real-label"
                            data-empty_topup="{{ __('no_topup_found') }}"
                            data-empty_subscription="{{ __('no_subscription_found') }}">
                            <!-- Cards will be dynamically rendered here -->
                        </div>

                        <div class="d-flex gap-5">
                            <div id="subscriptionCards" class="row w-100 g-3">
                                <div class="skeleton subscription-skeleton subscription-loader"></div>
                            </div>
                            <div id="subscriptionCards" class="row w-100 g-3">
                                <div class="skeleton subscription-skeleton subscription-loader"></div>
                            </div>
                            <div id="subscriptionCards" class="row w-100 g-3">
                                <div class="skeleton subscription-skeleton subscription-loader"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel">{{ __('Select Payment Method') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if ($activeSubscription && $activeSubscription->status == 1 && $activeSubscription->amount > 0)
                        <div id="subscription-warning" class="mb-3 text-danger">
                            You already have an active subscription. If you proceed, your current subscription will be cancelled and a new one will be created.    
                        </div>
                        @endif
                        <form id="payment" enctype="multipart/form-data" name="paybook">
                            {{ csrf_field() }}
                            <input type="hidden" name="package_id" class="package_id" value="">
                            <input type="hidden" name="package_name" class="package_name" value="">
                            <input type="hidden" name="package_amount" class="package_amount" value="">
                            <input type="hidden" name="trx_id" class="trx_id" value="">
                            <div class="mb-3" id="paymentmethoddiv">
                                <label class="form-check-label mb-2">{{ __('choose_payment_method') }}:</label>
                                @if ($paymentMethods)
                                    @foreach ($paymentMethods as $payementMethod)
                                        <div class="form-check">
                                            <input class="form-check-input paymentmethod" type="radio" name="paymentMethod" id="{{ $payementMethod->label }}" value="{{ $payementMethod->label }}">
                                            <label class="form-check-label" for="{{ $payementMethod->label }}">{{ $payementMethod->payment_type }}</label>
                                        </div>
                                    @endforeach
                                    <div class="mt-3 bank-details d-none" data-payment_proof_required="{{ __('payment_proof_required') }}">
                                        <div class="mb-3">
                                            <label for="payment_proof" class="form-label">{{ __('Payment Proof') }}:</label>
                                            <input type="file" name="payment_proof" class="form-control" id="payment_proof">
                                        </div>
                                        <h6 class="mb-3">{{ __('bank_details') }}:</h6>
                                        <p>{{ __('bank_name') }}: {{ $adminBankDetails['bank_name'] ?? '-' }}</p>
                                        <p>{{ __('Account Holder Name') }}: {{ $adminBankDetails['account_name'] ?? '-'}}</p>
                                        <p>{{ __('Account Number') }}: {{ $adminBankDetails['account_number'] ?? '-' }}</p>
                                        <p>{{ __('branch_code') }}: {{ $adminBankDetails['branch_code'] ?? '-' }}</p>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100 mt-3" id="payNowButton">{{ __('Pay Now') }}</button>
                                @else
                                    <p>{{ __('There are no payment methods available.') }}</p>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cancel Subscription Modal --}}
        <div class="modal fade" id="cancel_subscription" tabindex="-1" aria-labelledby="cancel_subscription"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancel_subscription">{{ __('Cancel Subscription') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('Are you sure you want to cancel your subscription?') }}</p>
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('cancel.subscription') }}" method="post">
                            @csrf
                            <div>
                                <a href="javascript:void(0);" class="btn btn-light me-2"
                                    data-bs-dismiss="modal">{{ __('Dismiss') }}</a>
                                <button class="btn btn-danger cancel_subscription"
                                    data-yes_cancel="{{ __('Yes, Cancel') }}"
                                    type="submit">{{ __('Yes, Cancel') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if (module_view_exists('paymentgateway::model') && $PaymentGatewayStatus == 1)
            @include('paymentgateway::model')
        @endif
    @endsection
