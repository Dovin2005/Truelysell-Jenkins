<?php

namespace App\Repositories\Eloquent;

use App\Models\Bookings;
use App\Models\PayoutDetail;
use App\Models\PayoutHistory;
use App\Models\User;
use App\Models\UserDetail;
use App\Repositories\Contracts\TransactionInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Models\Productmeta;
use Modules\Product\app\Models\Rating;
use Modules\GlobalSetting\app\Models\Currency;
use App\Models\ProviderRequestAmount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Models\Categories;
use Modules\Service\app\Models\Service;

class TransactionRepository implements TransactionInterface
{
     public function listTransactions(
        ?int $userId = null,
        ?int $customerId = null,
        ?int $providerId = null,
        ?string $search = null,
        string $orderBy = 'desc',
        string $sortBy = 'id'
    ): Collection {

        $providerUserType = User::where('id', $providerId)
            ->where('user_type', 4)
            ->exists();

        $query = Bookings::with([
                'user',
                'product',
                'product.createdBy'
            ])
            ->when($userId, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($customerId, function ($query, $customerId) {
                $query->whereHas('user', function ($q) use ($customerId) {
                    $q->where('id', $customerId);
                });
            })
            ->when($providerId, function ($query, $providerId) {
                $query->whereHas('product', function ($q) use ($providerId) {
                    $q->where('created_by', $providerId);
                });
            })
            ->when($providerId && $providerUserType, function ($query) use ($providerId) {
                $query->orWhere(function ($q) use ($providerId) {
                    $q->where('staff_id', $providerId);
                });
            })
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })->orWhereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            })
            ->withTrashed()
            ->orderBy($sortBy, $orderBy);

        return $query->get();
    }

    public function getTransactionStatusMap(): array
    {
        return [
            1 => 'Open',
            2 => 'Accepted',
            3 => 'Cancelled',
            4 => 'In Progress',
            5 => 'Completed',
        ];
    }

    public function getPaymentTypeMap(): array
    {
        return [
            1 => 'Paypal',
            2 => 'Stripe',
            3 => 'Razorpay',
            4 => 'Bank Transfer',
            5 => 'COD',
            6 => 'Wallet',
            7 => 'Mollie',
            8 => 'PayU',
            9 => 'Cashfree',
            10 => 'Authorize.net',
            11 => 'Paystack',
            12 => 'Mercado Pago'
        ];
    }

    public function getPaymentStatusMap(): array
    {
        return [
            1 => 'Unpaid',
            2 => 'Paid',
            3 => 'Refund',
        ];
    }

    public function getCommissionRate(): float
    {
        $commissionSetting = GlobalSetting::where('key', 'commission_rate_percentage')->first();
        return $commissionSetting ? (float) $commissionSetting->value : 0;
    }

    public function getProductImages(array $productIds): Collection
    {
        return Productmeta::whereIn('product_id', $productIds)
            ->where('source_key', 'product_image')
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('product_id');
    }

    public function getCurrencySymbol(): string
    {
        $currency = Cache::remember('currency_details', 86400, function () {
            return Currency::select('symbol')
                ->orderBy('id', 'DESC')
                ->where('is_default', 1)
                ->first();
        });

        return $currency->symbol ?? '';
    }

    public function getDateFormat(): string
    {
        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        return $dateformatSetting->value ?? 'Y-m-d';
    }

    public function getCustomerProfileImage(?string $image): string
    {
        if (!empty($image)) {
            return file_exists(public_path('storage/profile/' . $image)) 
                ? url('storage/profile/' . $image) 
                : asset('assets/img/profile-default.png');
        }
        return asset('assets/img/profile-default.png');
    }

    public function getProviderProfileImage(?string $image): string
    {
        return $this->getCustomerProfileImage($image);
    }

    public function getCategoryName(?int $categoryId): string
    {
        if (!$categoryId) {
            return 'No Category';
        }

        $category = Categories::where('id', $categoryId)
            ->select('id', 'name')
            ->first();

        return $category ? ucfirst($category->name) : 'No Category';
    }

    public function getProductImageUrl(Collection $images): string
    {
        $validImage = $images->first(function ($img) {
            return !empty($img->source_Values) && file_exists(public_path('storage/' . $img->source_Values));
        });

        return $validImage
            ? url('storage/' . $validImage->source_Values)
            : url('front/img/default-placeholder-image.png');
    }
    public function uploadPaymentProof(array $data)
    {
        return DB::transaction(function () use ($data) {
            $booking = Bookings::findOrFail($data['booking_id']);
            $booking->update([
                'payment_proof_path' => $data['file_path'],
                'payment_status' => 2,
            ]);
            return $booking;
        });
    }

    public function getProviderDetails(int $providerId)
    {
        $user = User::where('id', $providerId)
            ->where('user_type', 2)
            ->with(['userDetails.category'])
            ->firstOrFail();

        $products = Product::where('created_by', $providerId)->get();

        foreach ($products as $product) {
            $ratingDetails = Rating::getProductRatingDetails($product->id);
            $product->average_rating = $ratingDetails['average_rating'];
            $product->rating_count = $ratingDetails['rating_count'];
            $productImageMeta = Productmeta::where('product_id', $product->id)
                ->where('source_key', 'product_image')
                ->select('source_Values')
                ->first();
            $product->image_url = $productImageMeta->source_Values ?? null;
        }

        $city_name = 'California, USA';

        if ($user->userDetails) {
            if (!empty($user->userDetails->city)) {
                $city = DB::table('cities')->where('id', $user->userDetails->city)->first();
                if ($city) {
                    $city_name = $city->name;
                }
            }
        }

        $googleMapUrl = 'https://www.google.com/maps/embed/v1/place?key='
            . config('services.google_maps.key')
            . '&q=' . urlencode($city_name);

        $locationSetting = DB::table('general_settings')->where('key', 'location_status')->first();
        $locationEnabled = $locationSetting && $locationSetting->value == 1;

        $mapHasError = !$locationEnabled || $this->isInvalidGoogleMapKey($googleMapUrl);

        return [
            'user' => $user,
            'products' => $products,
            'googleMapUrl' => $googleMapUrl,
            'mapHasError' => $mapHasError,
        ];
    }

    protected function isInvalidGoogleMapKey(string $url): bool
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode !== 200 || str_contains($response, 'Maps Platform rejected your request');
    }

    public function providerTransaction(?int $providerId = null)
    {
        $commissionRate = 0;
        $commissionSetting = GlobalSetting::where('key', 'commission_rate_percentage')->first();
        if ($commissionSetting) {
            $commissionRate = (float) $commissionSetting->value;
        }

        $query = Bookings::with(['product.createdBy'])
            ->where('booking_status', 6)
            ->where('payment_type', '!=', 5)
            ->when($providerId, function ($query, $providerId) {
                $query->whereHas('product', function ($q) use ($providerId) {
                    $q->where('created_by', $providerId);
                });
            });

        $transactions = $query->get()->filter(function ($transaction) {
            return !is_null($transaction->product);
        });

        $providerDetailsQuery = User::query()->where('user_type', 2);
        if ($providerId) {
            $providerDetailsQuery->where('id', $providerId);
        }
        $providers = $providerDetailsQuery->get();

        $currencySymbol = Cache::remember('currency_details', 86400, function () {
            return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
        });

        $response = [];
        foreach ($providers as $provider) {
            $providerTransactions = $transactions->filter(function ($transaction) use ($provider) {
                return optional($transaction->product)->created_by == $provider->id;
            });

            $totals = $providerTransactions->reduce(function ($carry, $booking) use ($commissionRate) {
                $grossAmount = ($booking->total_amount ?? 0) - ($booking->amount_tax ?? 0);
                $commissionAmount = ($grossAmount * $commissionRate) / 100;
                $reducedAmount = $grossAmount - $commissionAmount;

                $carry['totalGrossAmount'] += $grossAmount;
                $carry['totalCommission'] += $commissionAmount;
                $carry['totalReducedAmount'] += $reducedAmount;
                return $carry;
            }, ['totalGrossAmount' => 0, 'totalCommission' => 0, 'totalReducedAmount' => 0]);

            $enteredAmount = PayoutHistory::where('user_id', $provider->id)
                ->sum('process_amount');

            $profileImage = $provider->userDetails->profile_image ?? '';
            $profileImage = !empty($profileImage) && file_exists(public_path('storage/profile/' . $profileImage)) 
                ? url('storage/profile/' . $profileImage) 
                : asset('assets/img/profile-default.png');

            

            $response[] = [
                'payout_details' => $this->getProviderPayoutDetails($provider->id),
                'provider' => [
                    'id' => $provider->id,
                    'name' => ucfirst($provider->name),
                    'email' => $provider->email,
                    'profile_image' => $profileImage,
                ],
                'transactions' => [
                    'total_bookings' => $providerTransactions->count(),
                    'total_gross_amount' => $totals['totalGrossAmount'],
                    'total_commission_amount' => $totals['totalCommission'],
                    'total_reduced_amount' => $totals['totalReducedAmount'],
                    'entered_amount' => $enteredAmount,
                    'remaining_amount' => $totals['totalReducedAmount'] - $enteredAmount,
                    'commission_rate' => $commissionRate,
                ],
                'currencySymbol' => optional($currencySymbol)->symbol ?? '',
            ];
        }

        usort($response, function ($a, $b) {
            return $b['transactions']['total_bookings'] <=> $a['transactions']['total_bookings'];
        });

        return $response;
    }

    private function getProviderPayoutDetails($providerId, ?int $payoutType = null): array
    {
        $payoutDetails = PayoutDetail::when(empty($payoutType), function ($query) {
                $query->where('status', 1);
            })
            ->when($payoutType, function ($query, $payoutType) {
                $query->where('payout_type', $payoutType);
            })
            ->where('provider_id', $providerId)
            ->first();

        $finalPayoutDetails = [];
        if ($payoutDetails) {
            $details = $payoutDetails->payout_detail;
            if ($payoutDetails && is_string($payoutDetails->payout_detail)) {
                $decoded = json_decode($payoutDetails->payout_detail, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $details = $decoded;
                }
            }
            $payoutMethod = '';
            switch ($payoutDetails->payout_type) {
                case 1:
                    $payoutMethod = "PayPal";
                    break;
                case 2:
                    $payoutMethod = "Stripe";
                    break;
                case 4:
                    $payoutMethod = "Bank Transfer";
                    break;
            }

            $finalPayoutDetails = [
                'payment_method' => $payoutMethod,
                'payout_details' => $details
            ];
        }

        return $finalPayoutDetails;
    }

    public function storePayoutHistory(array $data)
    {
        return DB::transaction(function () use ($data) {
            $payoutHistory = PayoutHistory::create([
                'type' => 1,
                'user_id' => $data['provider_id'],
                'total_bookings' => $data['total_bookings'],
                'total_earnings' => $data['total_earnings'],
                'admin_earnings' => $data['admin_earnings'],
                'pay_due' => $data['provider_pay_due'],
                'process_amount' => $data['entered_amount'],
                'payment_proof' => $data['payment_proof_path'],
                'remaining_amount' => $data['remaining_amount'],
                'payment_method' => $data['payment_method'],
            ]);

            try {
                \App\Helpers\InvoiceHelper::generateInvoice(
                    $payoutHistory->id,
                    $data['entered_amount'],
                    '3',
                    $data['provider_id'],
                );
            } catch (\Exception $e) {
                // Log the error but don't fail the transaction
                Log::error('Invoice generation failed: ' . $e->getMessage());
            }

            return $payoutHistory;
        });
    }

    public function savePayouts(array $data)
    {
        return DB::transaction(function () use ($data) {
            $payoutData = [
                'provider_id' => $data['provider_id'],
                'payout_type' => $data['payout_type'],
                'status' => 1,
            ];

            if ($data['payout_type'] == 1) {
                $payoutData['payout_detail'] = $data['paypal_id'];
            } elseif ($data['payout_type'] == 2) {
                $payoutData['payout_detail'] = $data['stripe_id'];
            } elseif ($data['payout_type'] == 4) {
                $bankData = [
                    'holder_name' => $data['holder_name'],
                    'bank_name' => $data['bank_name'],
                    'account_number' => $data['account_number'],
                    'ifsc' => $data['ifsc'],
                ];
                $payoutData['payout_detail'] = json_encode($bankData);
            }

            PayoutDetail::updateOrCreate(['id' => $data['id'] ?? ''], $payoutData);
            PayoutDetail::where('provider_id', $data['provider_id'])
                ->where('payout_type', '!=', $data['payout_type'])
                ->update(['status' => 0]);

            return true;
        });
    }

    public function getPayoutDetails(int $providerId)
    {
        return PayoutDetail::where(['provider_id' => $providerId])
            ->get(['id', 'provider_id', 'payout_type', 'payout_detail', 'status'])
            ->map(function ($data) {
                if ($data['payout_type'] == 4) {
                    $data['payout_detail'] = json_decode($data['payout_detail']);
                }
                return $data;
            });
    }

    public function getProviderPayoutHistory(int $providerId)
    {
        return PayoutHistory::where('user_id', $providerId)
            ->orderBy('id', 'DESC')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'provider_id' => $item->user_id,
                    'total_amount' => ($item->process_amount + $item->remaining_amount),
                    'processed_amount' => $item->process_amount,
                    'available_amount' => $item->remaining_amount,
                    'payment_proof_path' => url('storage/' . $item->payment_proof),
                    'payment_method' => $item->payment_method,
                    'created_at' => formatDateTime($item->created_at),
                ];
            });
    }

    public function getProviderPayoutRequest(?int $providerId)
    {
        return ProviderRequestAmount::where('provider_id', $providerId)
            ->where('status', 0)
            ->orderBy('id', 'DESC')
            ->get()
            ->map(function ($item) {
                $paymentId = $item->payment_id;
                $payoutMethod = '';
                switch ($paymentId) {
                    case 1:
                        $payoutMethod = "PayPal";
                        break;
                    case 2:
                        $payoutMethod = "Stripe";
                        break;
                    case 4:
                        $payoutMethod = "Bank Transfer";
                        break;
                }
                $item->payment_method = $payoutMethod;
                $item->amount = number_format($item->amount, 2, '.', '');
                $item->created_date = formatDateTime($item->created_at);
                return $item;
            });
    }

    public function listProviderRequests()
    {
        return ProviderRequestAmount::with('provider:id,name')
            ->select('id', 'provider_id', 'payment_id', 'amount', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'provider_id' => $request->provider_id,
                    'provider_name' => $request->provider->name ?? 'N/A',
                    'payment_id' => $request->payment_id,
                    'amount' => $request->amount,
                    'status' => $request->status,
                    'status_label' => $request->status === 0 ? 'UnPaid' : ($request->status === 1 ? 'Paid' : 'Refund'),
                    'created_at' => $request->created_at,
                    'provider_payout_details' => $this->getProviderPayoutDetails($request->provider_id, $request->payment_id),
                ];
            });
    }

    public function updateProviderRequest(array $data)
    {
        return DB::transaction(function () use ($data) {
            $providerRequest = ProviderRequestAmount::findOrFail($data['id']);

            $providerRequest->update([
                'status' => 1,
                'payment_proof_path' => $data['payment_proof_path'],
            ]);

            $commissionRate = GlobalSetting::where('key', 'commission_rate_percentage')
                ->value('value') ?? 0;

            $enteredAmount = PayoutHistory::where('user_id', $data['provider_id'])
                ->sum('process_amount');

            $bookings = Bookings::with(['product.createdBy'])
                ->where('booking_status', 6)
                ->whereHas('product', function ($query) use ($data) {
                    $query->where('created_by', $data['provider_id']);
                })
                ->get();

            $totals = $bookings->reduce(function ($carry, $booking) use ($commissionRate) {
                $totalAmount = ($booking->total_amount ?? 0) - ($booking->amount_tax ?? 0);
                $commissionAmount = ($totalAmount * $commissionRate) / 100;
                $carry['totalEarnings'] += $totalAmount;
                $carry['adminEarnings'] += $commissionAmount;
                return $carry;
            }, ['totalEarnings' => 0, 'adminEarnings' => 0]);

            $providerPayDue = $totals['totalEarnings'] - $totals['adminEarnings'];
            $remainingAmount = $providerPayDue - $enteredAmount;

            $payoutHistory = PayoutHistory::create([
                'user_id' => $data['provider_id'],
                'type' => 1,
                'total_bookings' => $bookings->count(),
                'total_earnings' => $totals['totalEarnings'],
                'admin_earnings' => $totals['adminEarnings'],
                'pay_due' => $providerPayDue,
                'process_amount' => $data['provider_amount'],
                'payment_proof' => $data['payment_proof_path'],
                'remaining_amount' => $remainingAmount - $data['provider_amount'],
                'payment_method' => $data['payment_method'],
            ]);

            try {
                \App\Helpers\InvoiceHelper::generateInvoice(
                    $payoutHistory->id,
                    $data['provider_amount'],
                    '3',
                    $data['provider_id'],
                );
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }

            return $payoutHistory;
        });
    }

    public function sendProviderRequestAmount(array $data)
    {
        return DB::transaction(function () use ($data) {
           $payment = PayoutDetail::where([
                        'provider_id' => $data['provider_id'],
                        'status' => 1
                    ])->first();
            if (!$payment) {
                throw new \Exception('Please set your payment method.');
            }

            $remainingAmount = $this->calculateProviderBalance($data['provider_id']);
            $requestAmount = ProviderRequestAmount::where(['provider_id' => $data['provider_id'], 'status' => 0])->sum('amount');
            $available_amount = $remainingAmount - $requestAmount;

            if ($data['amount'] > $available_amount) {
                throw new \Exception('Amount must be less than available amount.');
            }

            return ProviderRequestAmount::create([
                'provider_id' => $data['provider_id'],
                'payment_id' => $data['payment_id'],
                'amount' => $data['amount']
            ]);
        });
    }

    public function getProviderBalance(int $providerId)
    {
        $remainingAmount = $this->calculateProviderBalance($providerId);
        $payout = PayoutHistory::select('process_amount')
            ->where('user_id', $providerId)
            ->orderBy('id', 'DESC')
            ->first();
        $requestAmount = ProviderRequestAmount::where(['provider_id' => $providerId, 'status' => 0])->sum('amount');
        
        $available_amount = $remainingAmount - $requestAmount;
        $last_payout = $payout->process_amount ?? 0;

        return [
            'available_amount' => number_format($available_amount, 2, '.', ''),
            'last_payout' => number_format($last_payout, 2, '.', ''),
        ];
    }

    public function calculateProviderBalance(int $providerId): float
    {
        $commissionRate = (float) (GlobalSetting::where('key', 'commission_rate_percentage')->value('value') ?? 0);
        $productIds = Service::where('user_id', $providerId)->pluck('id')->toArray();

        $transactions = Bookings::with(['product'])
            ->where('booking_status', 6)
            ->where('payment_type', '!=', 5)
            ->where(function($query) use ($productIds, $providerId) {
                $query->whereIn('product_id', $productIds)
                    ->orWhere('staff_id', $providerId);
            })
            ->get();

        $totals = $transactions->reduce(function ($carry, $booking) use ($commissionRate) {
            $grossAmount = ($booking->total_amount ?? 0) - ($booking->amount_tax ?? 0);
            $commissionAmount = ($grossAmount * $commissionRate) / 100;
            $reducedAmount = $grossAmount - $commissionAmount;

            $carry['totalGrossAmount'] += $grossAmount;
            $carry['totalCommission'] += $commissionAmount;
            $carry['totalReducedAmount'] += $reducedAmount;
            return $carry;
        }, ['totalGrossAmount' => 0, 'totalCommission' => 0, 'totalReducedAmount' => 0]);

        $enteredAmount = PayoutHistory::where('user_id', $providerId)->sum('process_amount');
        return $totals['totalReducedAmount'] - $enteredAmount;
    }

    public function getUserPayoutRequests()
    {
        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
        $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);

        return Bookings::select(
            DB::raw("DATE_FORMAT(bookings.created_at, '{$sqlDateFormat}') AS bookingdate"),
            DB::raw("
                CASE
                    WHEN bookings.payment_type = 1 THEN 'Paypal'
                    WHEN bookings.payment_type = 2 THEN 'Stripe'
                    WHEN bookings.payment_type = 3 THEN 'Razor Pay'
                    WHEN bookings.payment_type = 4 THEN 'Bank Transfer'
                    WHEN bookings.payment_type = 5 THEN 'COD'
                    ELSE 'Unknown'
                END AS paymenttype
            "),
            DB::raw("'Refund Initiated' as status"),
            'bookings.id',
            'bookings.service_amount',
            'users.id as userid',
            'provider.id as provider_id',
            'products.source_name',
            'users.name as user_name',
            'provider.name as provider_name',
            'products.created_by',
            'products.id as productid'
        )
            ->leftJoin('products', 'products.id', '=', 'bookings.product_id')
            ->leftJoin('users', 'users.id', '=', 'bookings.user_id')
            ->leftJoin('users as provider', 'provider.id', '=', 'products.created_by')
            ->with([
                'user.userDetails',
                'product.createdBy.userDetails',
            ])
            ->where('bookings.booking_status', 4)
            ->orderBy('bookings.updated_at', 'desc')
            ->get()
            ->map(function ($booking) {
                $customerDetails = UserDetail::where('user_id', $booking->userid ?? null)
                    ->select('user_id', 'profile_image', DB::raw("CONCAT(first_name, ' ', last_name) as user_name"))
                    ->first();
                $providerDetails = UserDetail::where('user_id', $booking->created_by ?? null)
                    ->select('user_id', 'profile_image', DB::raw("CONCAT(first_name, ' ', last_name) as provider_name"))
                    ->first();

                $productImageMeta = Productmeta::where('product_id', $booking->productid ?? null)
                    ->where('source_key', 'product_image')
                    ->select('source_Values')
                    ->first();

                $providerImage = $this->getProfileImageUrl($providerDetails->profile_image ?? '');
                $customerImage = $this->getProfileImageUrl($customerDetails->profile_image ?? '');

                return [
                    'id' => $booking->id,
                    'bookingdate' => $booking->bookingdate ?? '',
                    'username' => ucfirst($customerDetails->user_name ?? ''),
                    'useremail' => ucfirst($booking->email ?? ''),
                    'userimage' => $customerImage,
                    'providername' => ucfirst($providerDetails->provider_name ?? ''),
                    'providerimage' => $providerImage,
                    'productname' => ucfirst($booking->source_name ?? ''),
                    'productimage' => $productImageMeta->source_Values ?? '',
                    'service_amount' => $booking->service_amount ?? 0,
                    'total' => $booking->service_amount,
                    'payment_type' => $booking->paymenttype ?? '',
                    'status' => $booking->status,
                    'payment_proof' => $booking->payment_proof_path,
                ];
            });
    }

    public function updateRefund(array $data)
    {
        return DB::transaction(function () use ($data) {
            $payoutHistory = PayoutHistory::where('reference_id', $data['bookingid'])
                ->update([
                    'payment_proof' => $data['payment_proof_path'],
                    'remaining_amount' => 0
                ]);

            Bookings::where('id', $data['bookingid'])
                ->update(['booking_status' => '7']);

            return $payoutHistory;
        });
    }

    public function mapDateFormatToSQL(string $phpFormat): string
    {
        $replacements = [
            'd' => '%d', 'D' => '%a', 'j' => '%e', 'l' => '%W',
            'F' => '%M', 'm' => '%m', 'M' => '%b', 'n' => '%c',
            'Y' => '%Y', 'y' => '%y',
            'a' => '%p', 'A' => '%p', 'g' => '%l', 'G' => '%k',
            'h' => '%I', 'H' => '%H', 'i' => '%i', 's' => '%S',
        ];

        return strtr($phpFormat, $replacements);
    }

    private function getProfileImageUrl(?string $image): string
    {
        if (!empty($image)) {
            return file_exists(public_path('storage/profile/' . $image)) 
                ? url('storage/profile/' . $image) 
                : asset('assets/img/profile-default.png');
        }
        return asset('assets/img/profile-default.png');
    }
}