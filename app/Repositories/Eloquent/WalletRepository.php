<?php

namespace App\Repositories\Eloquent;

use App\Models\WalletHistory;
use App\Repositories\Contracts\WalletInterface;
use Modules\GlobalSetting\app\Models\Currency;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\Leads\app\Models\Payments;
use Illuminate\Support\Facades\Cache;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class WalletRepository implements WalletInterface
{
    public function addWalletAmount(array $data)
    {
        return WalletHistory::create([
            'user_id' => $data['user_id'],
            'amount' => $data['amount'],
            'payment_type' => $data['payment_method'],
            'status' => $data['status'] ?? 'pending',
            'transaction_id' => $data['transaction_id'] ?? null,
            'transaction_date' => now(),
            'type' => $data['type'] ?? 1,
        ]);
    }

    public function getWalletHistory(int $userId)
    {
        $walletHistory = WalletHistory::where('user_id', $userId)
            ->where('type', '1')
            ->orderBy('transaction_date', 'desc')
            ->get();

        $dateFormatSetting = GlobalSetting::where('key', 'date_format_view')->first();

        return $walletHistory->map(function ($record) use ($dateFormatSetting) {
            $record->transaction_date = \Carbon\Carbon::parse($record->transaction_date)
                ->format($dateFormatSetting->value);
            return $record;
        });
    }

    public function getWalletBalance(int $userId)
    {
        $totalAmount = WalletHistory::where('user_id', $userId)
            ->where('status', 'completed')
            ->where('type', '1')
            ->sum('amount');

        $totalAmountdebit = WalletHistory::where('user_id', $userId)
            ->where('status', 'completed')
            ->where('type', '2')
            ->sum('amount');

        return [
            'total' => $totalAmount,
            'debit' => $totalAmountdebit,
            'balance' => $totalAmount - $totalAmountdebit
        ];
    }

    public function processWalletPayment(array $data)
    {
        $balance = $this->getWalletBalance($data['user_id'])['balance'];
        
        if ($balance < $data['amount']) {
            return false;
        }

        return WalletHistory::create([
            'user_id' => $data['user_id'],
            'amount' => $data['amount'],
            'payment_type' => $data['payment_type'],
            'status' => 'Completed',
            'reference_id' => $data['reference_id'],
            'transaction_id' => $data['transaction_id'],
            'transaction_date' => now(),
            'type' => 2,
        ]);
    }

    public function processLeadPayment(array $data)
    {
        Payments::where('id', $data['transaction_id'])->update([
            'status' => 2,
            'payment_type' => $data['payment_type']
        ]);

        return $this->addWalletAmount([
            'user_id' => $data['user_id'],
            'amount' => $data['amount'],
            'payment_method' => $data['payment_type'],
            'status' => 'Completed',
            'transaction_id' => $data['transaction_id'],
            'type' => 2,
        ]);
    }

    public function confirmTransaction(string $transactionId, string $status)
    {
        $model = WalletHistory::where('transaction_id', $transactionId)->first();
        
        if ($model) {
            return $model->update(['status' => $status]);
        }

        $payment = Payments::where('transaction_id', $transactionId)->first();
        
        if ($payment) {
            return $payment->update(['status' => 2]);
        }

        return false;
    }

    public function createStripePaymentIntent(float $amount)
    {
        Stripe::setApiKey(config('stripe.test.sk'));
        
        return PaymentIntent::create([
            'amount' => $amount * 100,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
        ]);
    }

    public function getCurrencySymbol()
    {
        return Cache::remember('currency_details', 86400, function () {
            return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
        });
    }
}