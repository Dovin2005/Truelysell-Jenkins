<?php

use Illuminate\Support\Facades\Route;
use Modules\PaymentGateway\app\Http\Controllers\PaymentGatewayController;
use Modules\PaymentGateway\app\Http\Controllers\RazerpayController;
use Modules\PaymentGateway\app\Http\Controllers\PayUController;
use Modules\PaymentGateway\app\Http\Controllers\CashfreeController;
use Modules\PaymentGateway\app\Http\Controllers\AuthorizeNetController;
use Modules\PaymentGateway\app\Http\Controllers\PaystackController;
use Modules\PaymentGateway\app\Http\Controllers\MercadoPagoController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('paymentgateways', PaymentGatewayController::class)->names('paymentgateway');
});


//Payment Gate
Route::get('admin/paymentgateways', [PaymentGatewayController::class, 'index']);

//RazerPay
Route::post('/razorpay/payment-success', [RazerpayController::class, 'razorpaySuccess'])->name('razorpay.callback');
//RazerPay Subscription
Route::post('razorpay-sub-pay', [RazerpayController::class, 'razorpaySubscription'])->name('razorpaySubPay');
Route::post('razorpay-sub-success', [RazerpayController::class, 'razorpaySubscriptionSuccess'])->name('razorpaySubscriptionSuccess');
//_______________________________________________________________________


//PayU
Route::get('/payu/success', [PayUController::class, 'success'])->name('payu.success');
Route::get('/payu/failure', [PayUController::class, 'failed'])->name('payu.failure');
//PayuFree Subscription
Route::post('payu-sub-pay', [PayUController::class, 'payuSubscription'])->name('payuSubPay');
Route::get('payu/sub/success', [PayUController::class, 'payuSubscriptionSuccess']);
//_______________________________________________________________________


//CashFree
Route::any('cashfree/payments/success', [CashfreeController::class, 'success'])->name('success');
//CashFree Subscription
Route::post('cashfree-sub-pay', [CashfreeController::class, 'cashfreeSubscription'])->name('cashfreeSubPay');
Route::get('cashfree/sub/success', [CashfreeController::class, 'cashfreeSubscriptionSuccess']);
//_______________________________________________________________________


//Authorize-net
Route::get('/authorize-net', [AuthorizeNetController::class, 'showPaymentForm'])->name('authorize.form');
//Authorize-net Subscription
Route::post('authorize-sub-pay', [AuthorizeNetController::class, 'authorizeSubscription'])->name('authorizeSubPay');
Route::get('authorize/sub/success', [AuthorizeNetController::class, 'authorizeSubscriptionSuccess']);
//_______________________________________________________________________


//Paystack
Route::get('/paystack/payments/success', [PaystackController::class, 'success'])->name('paystack.success');
//PayuFree Subscription
Route::post('paystack-sub-pay', [PaystackController::class, 'paystackSubscription'])->name('paystackSubPay');
Route::get('paystack/sub/success', [PaystackController::class, 'paystackSubscriptionSuccess'])->name('paystack.sub.success');
//_______________________________________________________________________


//Mercadopago
Route::get('/mercadopago/success', [MercadoPagoController::class, 'success'])->name('mercadopago.success');
Route::get('/mercadopago/failure', action: [MercadoPagoController::class, 'failure'])->name('mercadopago.failure');
Route::get('/mercadopago/pending', [MercadoPagoController::class, 'pending'])->name('mercadopago.pending');
//Mercadopago Subscription
Route::post('mercadopago-sub-pay', [MercadoPagoController::class, 'mercadopagoSubscription'])->name('mercadopagoSubPay');
Route::get('mercadopago/sub/success', [MercadoPagoController::class, 'mercadopagoSubscriptionSuccess'])->name('mercadopago.sub.success');
//_______________________________________________________________________
