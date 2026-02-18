<?php

namespace App\Repositories\Contracts;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;

interface StripeRepositoryInterface
{
    public function live_mobile(Request $request): JsonResponse;
    public function live_mobile_pay(Request $request): JsonResponse;
     public function sub_payment_success(Request $request): JsonResponse;
     public function test(Request $request): RedirectResponse;
     public function live(): RedirectResponse;
     public function paymentSuccess(Request $request);
     public function stripepayment(Request $request): RedirectResponse;
     public function subscriptionpaymentsuccess(Request $request);
}