<?php

namespace App\Repositories\Contracts;

interface WalletInterface
{
    public function addWalletAmount(array $data);
    public function getWalletHistory(int $userId);
    public function getWalletBalance(int $userId);
    public function processWalletPayment(array $data);
    public function processLeadPayment(array $data);
    public function confirmTransaction(string $transactionId, string $status);
    public function createStripePaymentIntent(float $amount);
    public function getCurrencySymbol();
}