<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface StaffRepositoryInterface
{
    public function index(string $routeName): \Illuminate\Contracts\View\View;
    public function getDashboard(): array;
    public function getSubscription(): array;
    public function getTotalBookingCount(Request $request): array;
    public function getLatestBookings(Request $request): array;
    public function getLatestReviews(Request $request): array;
    public function getLatestProductService(Request $request): array;
    public function getSubscribedPack(Request $request): array;
    public function calendarIndex(): array;
    public function getStaffBookings(Request $request): array;
    public function getLanguage(): mixed;
    public function providerStaffLimit(Request $request): array;
    public function providerStaffLimitApi(Request $request): array;
    public function adminStaff(): array;
    public function staffStatusChange(Request $request): array;
    public function getCustomer(Request $request): array;
    public function getStaffSlot(Request $request): array;
    public function payment(Request $request): array;
}