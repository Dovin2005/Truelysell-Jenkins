<?php

namespace App\Repositories\Contracts;
use Illuminate\Support\Collection;
interface TransactionInterface
{
    public function listTransactions(
        ?int $userId = null,
        ?int $customerId = null,
        ?int $providerId = null,
        ?string $search = null,
        string $orderBy = 'desc',
        string $sortBy = 'id'
    ): Collection;
        public function getTransactionStatusMap(): array;
    public function getPaymentTypeMap(): array;
    public function getPaymentStatusMap(): array;
    public function getCommissionRate(): float;
    public function getProductImages(array $productIds): Collection;
    public function getCurrencySymbol(): string;
    public function getDateFormat(): string;
    public function getCustomerProfileImage(?string $image);
    public function getProviderProfileImage(?string $image);
    public function getCategoryName(?int $categoryId);
    public function getProductImageUrl(Collection $images);
    public function uploadPaymentProof(array $data);
    public function getProviderDetails(int $providerId);
    public function providerTransaction(?int $providerId);
    public function storePayoutHistory(array $data);
    public function savePayouts(array $data);
    public function getPayoutDetails(int $providerId);
    public function getProviderPayoutHistory(int $providerId);
    public function getProviderPayoutRequest(?int $providerId);
    public function listProviderRequests();
    public function updateProviderRequest(array $data);
    public function sendProviderRequestAmount(array $data);
    public function getProviderBalance(int $providerId);
    public function calculateProviderBalance(int $providerId): float;
    public function getUserPayoutRequests();
    public function updateRefund(array $data);
    public function mapDateFormatToSQL(string $phpFormat): string;
}