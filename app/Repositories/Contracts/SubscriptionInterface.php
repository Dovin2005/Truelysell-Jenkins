<?php

namespace App\Repositories\Contracts;

interface SubscriptionInterface
{
    public function getActiveSubscription(int $userId, string $type);
    public function getCurrencySymbol();
    public function createPackageTransaction(array $data);
    public function calculateEndDate(string $date, string $term, int $duration);
    public function getPaymentMethods(bool $excludeWallet = false, bool $filterByAddon = true);
    public function getAllSubscriptions();
    public function getUserSubscriptionHistory(int $userId);
    public function mapDateFormatToSQL(string $phpFormat);
    public function deactivateActiveSubscriptions(int $userId, string $type);
}