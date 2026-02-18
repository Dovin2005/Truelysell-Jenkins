<?php

namespace App\Repositories\Contracts;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

interface PaypalRepositoryInterface
{
    public function ProcessPayment(Request $request);
    public function Successpayment(Request $request);
    public function handlePayment(Request $request);
    public function handleBankPayment(Request $request);
    public function handlecodPayment(Request $request);
    public function handleWalletPayment(Request $request);
}