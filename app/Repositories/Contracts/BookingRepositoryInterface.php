<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface BookingRepositoryInterface
{
    public function index(): array;
    public function userBookingList(): array;
    public function providerIndex(Request $request): array;
    public function staffIndex(Request $request): array;
    public function updateBookingStatus(Request $request): array;
    public function getBookings(Request $request): JsonResponse;
    public function getBookinglists(Request $request): array;
    public function indexRequest(Request $request): array;
    public function requestDispute(Request $request): JsonResponse;
    public function requestDisputeApi(Request $request): JsonResponse;
    public function UpdateRequest(Request $request): JsonResponse;
    public function getDisputeDetails(Request $request): array;
    public function getDisputeDetailsApi(Request $request): JsonResponse;
    public function getDisputeInfo(Request $request): JsonResponse;
    public function getBookingDetails(Request $request): JsonResponse;
    public function WalletCheck(Request $request): array;
}