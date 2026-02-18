<?php

namespace App\Repositories\Eloquent;

use App\Models\AddonModule;
use App\Models\PackageTrx;
use App\Models\Paymentmethod;
use App\Repositories\Contracts\SubscriptionInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\GlobalSetting\app\Models\Currency;
use Modules\GlobalSetting\app\Models\SubscriptionPackage;
use Modules\GlobalSetting\Entities\GlobalSetting;

class SubscriptionRepository implements SubscriptionInterface
{
    public function getActiveSubscription(int $userId, string $type)
    {
        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
        $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);
        $currentDate = now();

        return PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('package_transactions.provider_id', $userId)
            ->where('subscription_packages.subscription_type', $type)
            ->whereDate('package_transactions.end_date', '>=', $currentDate)
            ->select(
                'subscription_packages.package_title',
                'subscription_packages.description',
                'subscription_packages.package_term',
                'subscription_packages.package_duration',
                'subscription_packages.price',
                DB::raw("DATE_FORMAT(package_transactions.updated_at, '{$sqlDateFormat}') AS payment_date"),
                DB::raw("DATE_FORMAT(package_transactions.end_date, '{$sqlDateFormat}') AS end_date"),
                DB::raw($this->getNextPaymentDateSql($sqlDateFormat)),
                'package_transactions.status',
                'package_transactions.payment_status',
                DB::raw("CASE WHEN package_transactions.status = 1 THEN 'Active' else 'Inactive' end as activestatus")
            )
            ->orderByDesc('package_transactions.id')
            ->first();
    }

    public function getCurrencySymbol()
    {
        return Cache::remember('currency_details', 86400, function () {
            return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
        });
    }

    public function createPackageTransaction(array $data)
    {
        $packageData = [
            'provider_id' => $data['provider_id'],
            'package_id' => $data['package_id'],
            'amount' => $data['amount'],
            'trx_date' => date('Y-m-d'),
            'payment_status' => $data['type'] == 'free' ? 2 : 1,
            'created_by' => $data['provider_id'],
            'created_at' => Carbon::now(),
        ];

        $package = DB::table('subscription_packages')
            ->select('package_term', 'package_title', 'package_duration', 'subscription_type')
            ->where('id', $data['package_id'])
            ->whereNull('deleted_at')
            ->first();

        if ($package) {
            if ($data['type'] == 'free') {
                $subscriptionType = $package->subscription_type;
                if ($subscriptionType == 'regular') {
                    $subscriptionPackageIds = SubscriptionPackage::where('subscription_type', 'regular')->pluck('id')->toArray();
                    PackageTrx::where('provider_id', $packageData['provider_id'])
                        ->whereIn('package_id', $subscriptionPackageIds)
                        ->update(['status' => 0]);
                } else if ($subscriptionType == 'topup') {
                    $subscriptionPackageIds = SubscriptionPackage::where('subscription_type', 'topup')->pluck('id')->toArray();
                    PackageTrx::where('provider_id', $packageData['provider_id'])
                        ->whereIn('package_id', $subscriptionPackageIds)
                        ->update(['status' => 0]);
                }
            } else {
                $packageData['status'] = 0;
            }

            $packageData['end_date'] = $this->calculateEndDate(
                $packageData['trx_date'],
                $package->package_term,
                $package->package_duration
            );
        }

        return PackageTrx::insertGetId($packageData);
    }

    public function calculateEndDate(string $date, string $term, ?int $duration)
    {
        $trxDate = Carbon::parse($date);

        switch (strtolower($term)) {
            case 'month':
                return $trxDate->addMonths($duration)->format('Y-m-d');
            case 'yearly':
                return $trxDate->addYears($duration)->format('Y-m-d');
            case 'lifetime':
                return Carbon::create(9999, 12, 31)->format('Y-m-d');
            case 'day':
                return $trxDate->addDays($duration)->format('Y-m-d');
            default:
                return $trxDate->addMonths($duration)->format('Y-m-d');
        }
    }

    public function getPaymentMethods(bool $excludeWallet = false, bool $filterByAddon = true)
    {
        $query = Paymentmethod::where('status', 1);

        if ($excludeWallet) {
            $query->where('payment_type', '!=', 'Wallet');
        }

        $methods = $query->get();

        // Filtering logic moved here
        $coreLabels = ['paypal', 'stripe'];
        $addonLabels = ['razerpay', 'cashfree', 'payu', 'paystack', 'authorizenet', 'mercadopago'];

        if ($filterByAddon) {
            $PaymentGatewayStatus = AddonModule::where('slug', 'paymentgateway')->value('status') ?? 0;

            if ($PaymentGatewayStatus == 1) {
                // Allow only core + addon-supported methods
                $allowed = array_merge($coreLabels, $addonLabels);
                $methods = $methods->filter(function ($method) use ($allowed) {
                    return in_array(strtolower($method->label), $allowed);
                })->values();
            } else {
                // Allow only core + methods not dependent on addon
                $methods = $methods->filter(function ($method) use ($coreLabels, $addonLabels) {
                    $label = strtolower($method->label);
                    return in_array($label, $coreLabels) || !in_array($label, $addonLabels);
                })->values();
            }
        } else {
            // Allow only core methods
            $methods = $methods->filter(function ($method) use ($coreLabels) {
                return in_array(strtolower($method->label), $coreLabels);
            })->values();
        }
        
        return $methods;
    }


    public function getAllSubscriptions()
    {
        return PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->join('users', 'users.id', '=', 'package_transactions.provider_id')
            ->leftJoin('user_details', function ($join) {
                $join->on('user_details.user_id', '=', 'users.id')
                    ->whereNull('user_details.deleted_at');
            })
            ->select(
                'subscription_packages.package_title',
                'subscription_packages.package_term',
                'subscription_packages.package_duration',
                'subscription_packages.price',
                'subscription_packages.description',
                DB::raw("UPPER(subscription_packages.subscription_type) as subscription_type"),
                DB::raw("CONCAT(user_details.first_name, ' ', user_details.last_name) as name"),
                'package_transactions.trx_date',
                'package_transactions.end_date',
                'package_transactions.payment_proof',
                'package_transactions.payment_status',
                'package_transactions.id',
                'package_transactions.status',
                'package_transactions.payment_type'
            )
            ->orderBy('package_transactions.created_at', 'desc')
            ->get()->map(function ($subscription) {
                $subscription->trx_date = formatDateTime($subscription->trx_date);
                $subscription->end_date = formatDateTime($subscription->end_date);
                $subscription->payment_proof = !empty($subscription->payment_proof) && file_exists(public_path('storage/' . $subscription->payment_proof))
                    ? url('storage/' . $subscription->payment_proof) : '';

                return $subscription;
            });
    }

    public function getUserSubscriptionHistory(int $userId)
    {
        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
        $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);

        $history = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->join('users', 'users.id', '=', 'package_transactions.provider_id')
            ->select(
                'users.name',
                DB::raw("DATE_FORMAT(package_transactions.trx_date, '{$sqlDateFormat}') AS trx_date"),
                DB::raw("DATE_FORMAT(package_transactions.end_date, '{$sqlDateFormat}') AS end_date"),
                DB::raw("
                    CASE
                        WHEN package_transactions.payment_status = 1 THEN 'Open'
                        WHEN package_transactions.payment_status = 2 THEN 'Paid'
                        ELSE 'Unknown'
                    END AS paymentstatus"),
                'package_transactions.payment_status',
                DB::raw("UPPER(SUBSTRING(subscription_packages.subscription_type, 1, 1)) as subscription_type"),
                'subscription_packages.package_title',
                'subscription_packages.package_term',
                'subscription_packages.package_duration',
                'subscription_packages.price',
                'subscription_packages.description',
                DB::raw("CASE WHEN package_transactions.status = 1 THEN 'Active' else 'Inactive' end as activestatus"),
                'package_transactions.status',
                DB::raw("UPPER(subscription_packages.subscription_type) as subscription_type")
            )
            ->orderBy('subscription_packages.created_at', 'desc')
            ->where('package_transactions.provider_id', $userId)
            ->get();

        $currency = $this->getCurrencySymbol();

        return [
            'historydata' => $history,
            'currency' => $currency ? $currency->symbol : '$'
        ];
    }

    public function mapDateFormatToSQL(string $phpFormat)
    {
        $replacements = [
            'd' => '%d',
            'D' => '%a',
            'j' => '%e',
            'l' => '%W',
            'F' => '%M',
            'm' => '%m',
            'M' => '%b',
            'n' => '%c',
            'Y' => '%Y',
            'y' => '%y',
        ];

        return strtr($phpFormat, $replacements);
    }

    public function deactivateActiveSubscriptions(int $userId, string $type)
    {
        return PackageTrx::where('provider_id', $userId)
            ->where('status', 1)
            ->whereHas('package', function ($query) use ($type) {
                $query->where('subscription_type', $type);
            })
            ->update(['status' => 0]);
    }

    private function getNextPaymentDateSql(string $dateFormat): string
    {
        return "
            CASE 
                WHEN subscription_packages.package_term = 'day' THEN 
                    DATE_FORMAT(DATE_ADD(package_transactions.updated_at, INTERVAL subscription_packages.package_duration DAY), '{$dateFormat}')
                WHEN subscription_packages.package_term = 'week' THEN 
                    DATE_FORMAT(DATE_ADD(package_transactions.updated_at, INTERVAL subscription_packages.package_duration WEEK), '{$dateFormat}')
                WHEN subscription_packages.package_term = 'month' THEN 
                    DATE_FORMAT(DATE_ADD(package_transactions.updated_at, INTERVAL subscription_packages.package_duration MONTH), '{$dateFormat}')
                WHEN subscription_packages.package_term = 'yearly' THEN 
                    DATE_FORMAT(DATE_ADD(package_transactions.updated_at, INTERVAL subscription_packages.package_duration YEAR), '{$dateFormat}')
                ELSE NULL
            END AS next_payment_date
        ";
    }
}
