<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

interface BookRepositoryInterface
{
    public function productdetail(Request $request): View;
    public function serviceBooking(Request $request);
    public function serviceIndexBooking(Request $request);
    public function getStaffs(Request $request);
    public function getInfo(Request $request);
    public function getPersonalInfo(Request $request);
    public function getSlot(Request $request);
    public function getSlots(Request $request);
    public function getSlotInfo(Request $request);
    public function getPayout(Request $request);
    public function getPayoutApi(Request $request);
    public function payment(Request $request);
    public function paypalPaymentSuccess(Request $request);
    public function stripPaymentSuccess(Request $request);
    public function checkProductUser(Request $request);
}