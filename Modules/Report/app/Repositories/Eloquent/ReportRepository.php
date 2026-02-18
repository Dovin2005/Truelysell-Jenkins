<?php

namespace Modules\Report\app\Repositories\Eloquent;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Modules\Report\app\Repositories\Contracts\ReportRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;
use App\Models\Bookings;
use Modules\Categories\app\Models\Categories;
use App\Models\User;
use Modules\GlobalSetting\app\Models\Currency;
use App\Models\PackageTrx;
use Modules\Leads\app\Models\Payments;
use Illuminate\Support\Facades\Cache;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ReportRepository implements ReportRepositoryInterface
{
    public function listAllTransactions(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('user_id', null);
            $orderBy = $request->input('order_by', 'desc');
            $search = $request->input('search', null);
            $customerId = $request->input('customer_id', null);
            $providerId = $request->input('provider_id', null);
            $dateRange = $request->input('date_range');
            $filterPayment = $request->input('filter_payment');
            $filterType = $request->input('filter_type');
            $filterSort = $request->input('sort_by');

            $statusMap = [
                1 => 'Open',
                2 => 'Accepted',
                3 => 'Cancelled',
                4 => 'In Progress',
                5 => 'Completed'
            ];

            $paymentTypeMap = [
                1 => 'Paypal',
                2 => 'Stripe',
                3 => 'Razorpay',
                4 => 'Bank Transfer',
                5 => 'COD',
                6 => 'Wallet',
                7 => 'Mollie'
            ];

            $paymentStatusMap = [1 => 'Unpaid', 2 => 'Paid', 3 => 'Refund'];

            $commissionRate = GlobalSetting::where('key', 'commission_rate_percentage')->value('value') ?? 0;

            // Fetch transactions from bookings
            $bookings = Bookings::with(['user', 'product'])
                ->where('payment_status', '!=', '1')
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->when($customerId, fn($q) => $q->whereHas('user', fn($q) => $q->where('id', $customerId)))
                ->when($providerId, fn($q) => $q->whereHas('product', fn($q) => $q->where('created_by', $providerId)))
                ->when($search, fn($q) => $q->whereHas('user', fn($q) => $q->where('name', 'like', "%$search%")))
                ->get();


            $totalBookingAmount = 0;

            $bookingTransactions = $bookings->map(function ($booking) use ($statusMap, $paymentTypeMap, $paymentStatusMap, &$totalBookingAmount) {
                $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
                $amount = $booking->total_amount ?? 0;
                $totalBookingAmount += $amount;

                $currencySymbol = Cache::remember('currecy_details', 86400, function () {
                    return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
                });
                return [
                    'id' => $booking->id,
                    'payment' => [
                        'type' => $paymentTypeMap[$booking->payment_type] ?? 'Unknown',
                        'status' => $paymentStatusMap[$booking->payment_status] ?? 'Unknown',
                        'amount' => number_format($amount, 2)
                    ],
                    'customer' => [
                        'id' => $booking->user->id ?? '-',
                        'name' => ucfirst($booking->user->name ?? '-')
                    ],
                    'provider' => [
                        'id' => $booking->product->createdBy->id ?? '-',
                        'name' => ucfirst($booking->product->createdBy->name ?? '-')
                    ],
                    'date' => date($dateformatSetting->value, strtotime($booking->created_at)),
                    'type' => "Booking",
                    'currency' => $currencySymbol->symbol ?? '',

                ];
            });

            // Fetch transactions from payments
            $query = Payments::orderBy('payments.id', $orderBy)
                ->join('user_form_inputs', 'user_form_inputs.id', '=', 'payments.reference_id')
                ->join('provider_forms_input', 'provider_forms_input.user_form_inputs_id', '=', 'user_form_inputs.id')
                ->where(['provider_forms_input.user_status' => 2, 'payments.status' => 2]);

            if ($customerId) {
                $query->where(['user_form_inputs.user_id' => $customerId]);
            }

            if ($providerId) {
                $query->where('provider_forms_input.provider_id', $providerId);
            }

            $transactions = $query->get([
                'payments.id as payment_id',
                'payments.payment_date',
                'payments.payment_type',
                'payments.status as payment_status',
                'payments.amount',
                'user_form_inputs.user_id',
                'user_form_inputs.category_id',
                'provider_forms_input.provider_id',
            ]);

            $totalPaymentAmount = 0;

            $paymentTransactions = $transactions->map(function ($leads) use ($paymentTypeMap, $paymentStatusMap, &$totalPaymentAmount) {
                $customerDetails = User::where('users.id', $leads->user_id ?? null)
                    ->join('user_details', 'users.id', '=', 'user_details.user_id')
                    ->select('users.id', 'user_details.profile_image', 'user_details.first_name', 'user_details.last_name', 'users.email')
                    ->first();

                $customerDetails->profile_image = $customerDetails->profile_image && file_exists(public_path('storage/profile/' . $customerDetails->profile_image))
                    ? url('storage/profile/' . $customerDetails->profile_image)
                    : url('assets/img/profile-default.png');

                $providerDetails = User::where('users.id', $leads->provider_id ?? null)
                    ->join('user_details', 'users.id', '=', 'user_details.user_id')
                    ->select('users.id', 'user_details.profile_image', 'user_details.first_name', 'user_details.last_name', 'users.email')
                    ->first();

                $providerDetails->profile_image = $providerDetails->profile_image && file_exists(public_path('storage/profile/' . $providerDetails->profile_image))
                    ? url('storage/profile/' . $providerDetails->profile_image)
                    : url('assets/img/profile-default.png');

                $category = $leads->category_id
                    ? Categories::where('id', $leads->category_id)->select('id', 'name')->first()
                    : null;

                $currency = Cache::remember('currecy_details', 86400, function () {
                    return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
                });

                $amount = $leads->amount ?? 0;
                $totalPaymentAmount += $amount;

                return [
                    'id' => $leads->payment_id,
                    'payment' => [
                        'type' => $paymentTypeMap[$leads->payment_type] ?? 'Paypal',
                        'status' => $paymentStatusMap[$leads->payment_status] ?? 'Unknown',
                        'amount' => number_format($amount, 2)
                    ],
                    'customer' => [
                        'id' => $customerDetails->id ?? '-',
                        'name' => $customerDetails->first_name . ' ' . $customerDetails->last_name,
                    ],
                    'provider' => [
                        'id' => $providerDetails->id ?? '-',
                        'name' => $providerDetails->first_name . ' ' . $providerDetails->last_name,
                    ],
                    'currency' => $currency->symbol ?? '',
                    'category' => $category->name ?? '-',
                    'date' => formatDateTime($leads->payment_date),
                    'type' => "Leads",

                ];
            });

            $subscriptions = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')->join('users', 'users.id', '=', 'package_transactions.provider_id')->leftJoin('user_details', function ($join) {
                $join->on('user_details.user_id', '=', 'users.id')
                    ->whereNull('user_details.deleted_at');
            })->select('subscription_packages.package_title', 'subscription_packages.package_term', 'subscription_packages.package_duration', 'subscription_packages.price', 'subscription_packages.description', 'package_transactions.created_at', DB::raw("
        CASE
            WHEN subscription_packages.status = 1 THEN 'Active'
           else 'Inactive' end as status"), DB::raw("UPPER(subscription_packages.subscription_type) as subscription_type"), DB::raw("CONCAT(user_details.first_name, ' ', user_details.last_name) as name"))->where('subscription_packages.package_title', '!=', 'Free')->orderBy('subscription_packages.created_at', 'desc')->get();

            $currency = Cache::remember('currecy_details', 86400, function () {
                return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
            });

            $subscriptionTransactions = [];
            $totalSubscriptionAmount = 0;
            foreach ($subscriptions as $subscription) {
                $totalSubscriptionAmount += $subscription->price;
                $subscriptionTransactions[] = [
                    'date' => formatDateTime($subscription->created_at),
                    'type' => "Subscription",
                    'currency' => $currency->symbol ?? '',
                    'provider' => [
                        'name' => $subscription->name ?? '',
                    ],
                    'payment' => [
                        'type' => $subscription->subscription_type,
                        'status' => $subscription->package_title,
                        'amount' => $subscription->price,
                    ],
                ];
            }

            // Merge and sort transactions by date
            $transactions = $bookingTransactions->merge($paymentTransactions)->sortByDesc('date')->values();
            $allTransactions = $transactions->merge($subscriptionTransactions)->sortByDesc('date')->values();

            $paymentSummary = $allTransactions->groupBy('payment.type')->map(function ($transactions, $type) {
                return [
                    'type' => $type,
                    'amount' => collect($transactions)->sum(function ($transaction) {
                        return (float)str_replace(',', '', $transaction['payment']['amount'] ?? '0');
                    }),
                ];
            })->values();



            $filteredTransactions = $allTransactions->filter(function ($allTransaction) use ($filterPayment, $dateRange, $filterType) {
                // If filterPayment is "All", include all transactions
                $paymentMatch = ($filterPayment === "All") ||
                    (isset($allTransaction['payment']['type']) && $allTransaction['payment']['type'] == $filterPayment);

                $TypeMatch = ($filterType === "All") ||
                    (isset($allTransaction['type']) && $allTransaction['type'] == $filterType);

                // Check if date filtering is needed
                if (!empty($dateRange)) {
                    [$startDate, $endDate] = explode(' - ', $dateRange); // Split the date range

                    // Convert to Carbon for date comparison
                    $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate))->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate))->endOfDay();

                    // Ensure transaction date exists and is within the range
                    if (isset($allTransaction['date'])) {
                        $transactionDate = Carbon::createFromFormat('d/m/Y', $allTransaction['date'])->startOfDay();
                        $dateMatch = $transactionDate->between($startDate, $endDate);
                    } else {
                        $dateMatch = false;
                    }
                } else {
                    $dateMatch = true; // No date filter applied
                }

                // Return only if both conditions match
                return $paymentMatch && $dateMatch && $TypeMatch;
            });

            // **Sort the filtered results based on $filterSort (ascending or descending order by date)**
            $filteredTransactions = $filteredTransactions->sortBy(function ($item) {
                return Carbon::createFromFormat('d/m/Y', $item['date'])->timestamp;
            });

            // If sorting is DESC, reverse the order
            if ($filterSort === 'desc') {
                $filteredTransactions = $filteredTransactions->reverse();
            }

            // Convert collection to array if needed
            $filteredTransactions = $filteredTransactions->values()->toArray();

            $currency = Cache::remember('currecy_details', 86400, function () {
                return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
            });
            // Calculate total amount for all transactions
            $totalAmount = $totalBookingAmount + $totalPaymentAmount + $totalSubscriptionAmount;
            return response()->json([
                'success' => true,
                'message' => 'All transactions retrieved successfully',
                'data' => [
                    'transactions' => $filteredTransactions,
                    'total_booking_amount' => number_format($totalBookingAmount),
                    'total_payment_amount' => number_format($totalPaymentAmount),
                    'total_amount' => number_format($totalAmount),
                    'total_subscription_amount' => number_format($totalSubscriptionAmount),
                    'payment_summary' => $paymentSummary,
                    'currency' => $currency->symbol ?? '',
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving transactions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listProviderTransactions(Request $request): JsonResponse
    {
        try {
            $userId = $request->input('user_id', null);
            $orderBy = $request->input('order_by', 'desc');
            $search = $request->input('search', null);
            $customerId = $request->input('customer_id', null);
            $providerId = $request->input('provider_id', null);
            $dateRange = $request->input('date_range');
            $filterPayment = $request->input('filter_payment');
            $filterType = $request->input('filter_type');
            $filterSort = $request->input('sort_by');

            $statusMap = [
                1 => 'Open',
                2 => 'Accepted',
                3 => 'Cancelled',
                4 => 'In Progress',
                5 => 'Completed'
            ];

            $paymentTypeMap = [
                1 => 'Paypal',
                2 => 'Stripe',
                3 => 'Razorpay',
                4 => 'Bank Transfer',
                5 => 'COD',
                6 => 'Wallet',
                7 => 'Mollie'
            ];

            $paymentStatusMap = [1 => 'Unpaid', 2 => 'Paid', 3 => 'Refund'];

            $commissionRate = GlobalSetting::where('key', 'commission_rate_percentage')->value('value') ?? 0;

            // Fetch transactions from bookings
            $bookings = Bookings::with(['user', 'product'])
                ->where('payment_status', '!=', '1')
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->when($customerId, fn($q) => $q->whereHas('user', fn($q) => $q->where('id', $customerId)))
                ->when($providerId, fn($q) => $q->whereHas('product', fn($q) => $q->where('created_by', $providerId)))
                ->when($search, fn($q) => $q->whereHas('user', fn($q) => $q->where('name', 'like', "%$search%")))
                ->get();


            $totalBookingAmount = 0;

            $bookingTransactions = $bookings->map(function ($booking) use ($statusMap, $paymentTypeMap, $paymentStatusMap, &$totalBookingAmount) {
                $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
                $amount = $booking->total_amount ?? 0;
                $totalBookingAmount += $amount;

                $currencySymbol = Cache::remember('currecy_details', 86400, function () {
                    return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
                });
                return [
                    'id' => $booking->id,
                    'payment' => [
                        'type' => $paymentTypeMap[$booking->payment_type] ?? 'Unknown',
                        'status' => $paymentStatusMap[$booking->payment_status] ?? 'Unknown',
                        'amount' => number_format($amount, 2)
                    ],
                    'customer' => [
                        'id' => $booking->user->id ?? '-',
                        'name' => ucfirst($booking->user->name ?? '-')
                    ],
                    'provider' => [
                        'id' => $booking->product->createdBy->id ?? '-',
                        'name' => ucfirst($booking->product->createdBy->name ?? '-')
                    ],
                    'date' => date($dateformatSetting->value, strtotime($booking->created_at)),
                    'type' => "Booking",
                    'currency' => $currencySymbol->symbol ?? '',

                ];
            });

            // Fetch transactions from payments
            $query = Payments::orderBy('payments.id', $orderBy)
                ->join('user_form_inputs', 'user_form_inputs.id', '=', 'payments.reference_id')
                ->join('provider_forms_input', 'provider_forms_input.user_form_inputs_id', '=', 'user_form_inputs.id')
                ->where('payments.user_id', $providerId)
                ->where(['provider_forms_input.user_status' => 2, 'payments.status' => 2]);

            if ($customerId) {
                $query->where(['user_form_inputs.user_id' => $customerId]);
            }

            if ($providerId) {
                $query->where('provider_forms_input.provider_id', $providerId);
            }

            $transactions = $query->get([
                'payments.id as payment_id',
                'payments.payment_date',
                'payments.payment_type',
                'payments.status as payment_status',
                'payments.amount',
                'user_form_inputs.user_id',
                'user_form_inputs.category_id',
                'provider_forms_input.provider_id',
            ]);

            $totalPaymentAmount = 0;

            $paymentTransactions = $transactions->map(function ($leads) use ($paymentTypeMap, $paymentStatusMap, &$totalPaymentAmount) {
                $customerDetails = User::where('users.id', $leads->user_id ?? null)
                    ->join('user_details', 'users.id', '=', 'user_details.user_id')
                    ->select('users.id', 'user_details.profile_image', 'user_details.first_name', 'user_details.last_name', 'users.email')
                    ->first();

                $customerDetails->profile_image = $customerDetails->profile_image && file_exists(public_path('storage/profile/' . $customerDetails->profile_image))
                    ? url('storage/profile/' . $customerDetails->profile_image)
                    : url('assets/img/profile-default.png');

                $providerDetails = User::where('users.id', $leads->provider_id ?? null)
                    ->join('user_details', 'users.id', '=', 'user_details.user_id')
                    ->select('users.id', 'user_details.profile_image', 'user_details.first_name', 'user_details.last_name', 'users.email')
                    ->first();

                $providerDetails->profile_image = $providerDetails->profile_image && file_exists(public_path('storage/profile/' . $providerDetails->profile_image))
                    ? url('storage/profile/' . $providerDetails->profile_image)
                    : url('assets/img/profile-default.png');

                $category = $leads->category_id
                    ? Categories::where('id', $leads->category_id)->select('id', 'name')->first()
                    : null;

                $currency = Cache::remember('currecy_details', 86400, function () {
                    return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
                });

                $amount = $leads->amount ?? 0;
                $totalPaymentAmount += $amount;

                return [
                    'id' => $leads->payment_id,
                    'payment' => [
                        'type' => $paymentTypeMap[$leads->payment_type] ?? 'Paypal',
                        'status' => $paymentStatusMap[$leads->payment_status] ?? 'Unknown',
                        'amount' => number_format($amount, 2)
                    ],
                    'customer' => [
                        'id' => $customerDetails->id ?? '-',
                        'name' => $customerDetails->first_name . ' ' . $customerDetails->last_name,
                    ],
                    'provider' => [
                        'id' => $providerDetails->id ?? '-',
                        'name' => $providerDetails->first_name . ' ' . $providerDetails->last_name,
                    ],
                    'currency' => $currency->symbol ?? '',
                    'category' => $category->name ?? '-',
                    'date' => formatDateTime($leads->payment_date),
                    'type' => "Leads",

                ];
            });

            // Merge and sort transactions by date
            $allTransactions = $bookingTransactions->merge($paymentTransactions)->sortByDesc('date')->values();

            $paymentSummary = $allTransactions->groupBy('payment.type')->map(function ($transactions, $type) {
                return [
                    'type' => $type,
                    'amount' => collect($transactions)->sum(function ($transaction) {
                        return (float)str_replace(',', '', $transaction['payment']['amount'] ?? '0');
                    }),
                ];
            })->values();



            $filteredTransactions = $allTransactions->filter(function ($allTransaction) use ($filterPayment, $dateRange, $filterType) {
                // If filterPayment is "All", include all transactions
                $paymentMatch = ($filterPayment === "All") ||
                    (isset($allTransaction['payment']['type']) && $allTransaction['payment']['type'] == $filterPayment);

                $TypeMatch = ($filterType === "All") ||
                    (isset($allTransaction['type']) && $allTransaction['type'] == $filterType);

                // Check if date filtering is needed
                if (!empty($dateRange)) {
                    [$startDate, $endDate] = explode(' - ', $dateRange); // Split the date range

                    // Convert to Carbon for date comparison
                    $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate))->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate))->endOfDay();

                    // Ensure transaction date exists and is within the range
                    if (isset($allTransaction['date'])) {
                        $transactionDate = Carbon::createFromFormat('d/m/Y', $allTransaction['date'])->startOfDay();
                        $dateMatch = $transactionDate->between($startDate, $endDate);
                    } else {
                        $dateMatch = false;
                    }
                } else {
                    $dateMatch = true; // No date filter applied
                }

                // Return only if both conditions match
                return $paymentMatch && $dateMatch && $TypeMatch;
            });

            // **Sort the filtered results based on $filterSort (ascending or descending order by date)**
            $filteredTransactions = $filteredTransactions->sortBy(function ($item) {
                return Carbon::createFromFormat('d/m/Y', $item['date'])->timestamp;
            });

            // If sorting is DESC, reverse the order
            if ($filterSort === 'desc') {
                $filteredTransactions = $filteredTransactions->reverse();
            }

            // Convert collection to array if needed
            $filteredTransactions = $filteredTransactions->values()->toArray();

            $currency = Cache::remember('currecy_details', 86400, function () {
                return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
            });
            // Calculate total amount for all transactions
            $totalAmount = $totalBookingAmount + $totalPaymentAmount;
            return response()->json([
                'success' => true,
                'message' => 'All transactions retrieved successfully',
                'data' => [
                    'transactions' => $filteredTransactions,
                    'total_booking_amount' => number_format($totalBookingAmount),
                    'total_payment_amount' => number_format($totalPaymentAmount),
                    'total_amount' => number_format($totalAmount),
                    'payment_summary' => $paymentSummary,
                    'currency' => $currency->symbol ?? '',
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving transactions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
