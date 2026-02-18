<?php

use Illuminate\Support\Facades\Route;
use Modules\PaymentGateway\app\Http\Controllers\PaymentGatewayController;
use Modules\PaymentGateway\app\Http\Controllers\RazerpayController;
use Modules\PaymentGateway\app\Http\Controllers\PayUController;
use Modules\PaymentGateway\app\Http\Controllers\CashfreeController;
use Modules\PaymentGateway\app\Http\Controllers\AuthorizeNetController;
use Modules\PaymentGateway\app\Http\Controllers\PaystackController;
use Modules\PaymentGateway\app\Http\Controllers\MercadoPagoController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('paymentgateways', PaymentGatewayController::class)->names('paymentgateway');
});


//RazerPay
Route::post('credential/save/razorpay', [RazerpayController::class, 'storeRazorpay']);
Route::post('credential/status/razorpay-status', [RazerpayController::class, 'statusRazorpay']);
// API for the mobile app
Route::post('/payment/initiate', [RazerpayController::class, 'initiatePayment']);
Route::post('/payment/verify', [RazerpayController::class, 'verifyPayment']);
//_______________________________________________________________________


//PayU
Route::post('credential/save/payu', [PayUController::class, 'storePayU']);
Route::post('credential/status/payu-status', [PayUController::class, 'statusPayU']);
// API for the mobile app
Route::post('payu/payment/initiate', [PayUController::class, 'initiatePayment']);
Route::post('payu/payment/verify', [PayUController::class, 'verifyPayment']);
//_______________________________________________________________________


//CashFree
Route::post('credential/save/cashfree', [CashfreeController::class, 'storeCashfree']);
Route::post('credential/status/cashfree-status', [CashfreeController::class, 'statusCashfree']);
// API for the mobile app
Route::post('cashfree/payment/initiate', [CashfreeController::class, 'initiatePayment']);
Route::post('cashfree/payment/verify', [CashfreeController::class, 'verifyPayment']);
//_______________________________________________________________________


//Authorize-net
Route::post('credential/save/authorizenet', [AuthorizeNetController::class, 'storeAuthorizeNet']);
Route::post('credential/status/authorizenet-status', [AuthorizeNetController::class, 'statusAuthorizeNet']);
// API for the mobile app
Route::post('authorizenet/payment/initiate', [AuthorizeNetController::class, 'initiatePayment']);
//_______________________________________________________________________


//Paystack
Route::post('credential/save/paystack', [PaystackController::class, 'storePaystack']);
Route::post('credential/status/paystack-status', [PaystackController::class, 'statusPaystack']);
// API for the mobile app
Route::post('paystack/payment/initiate', [PaystackController::class, 'initiatePayment']);
Route::post('paystack/payment/verify', [PaystackController::class, 'verifyPayment']);
//_______________________________________________________________________


// Mercado Pago
Route::post('credential/save/mercadopago', [MercadoPagoController::class, 'storeMercadoPago']);
Route::post('credential/status/mercadopago-status', [MercadoPagoController::class, 'statusMercadoPago']);
// API for the mobile app
Route::post('mercadopago/payment/initiate', [MercadoPagoController::class, 'initiatePayment']);
Route::post('mercadopago/payment/verify', [MercadoPagoController::class, 'verifyPayment']);
//_______________________________________________________________________
