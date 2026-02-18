<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Bookings;
use App\Models\PayoutDetail;
use App\Repositories\Contracts\TransactionInterface;
use Modules\Categories\app\Models\Categories;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\Product\app\Models\Productmeta;
use App\Models\UserDetail;
use App\Models\User;
use App\Models\PayoutHistory;
use App\Models\ProviderRequestAmount;
use Carbon\Carbon;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Models\Rating;
use Modules\GlobalSetting\app\Models\Currency;
use Illuminate\Support\Facades\Cache;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Support\Facades\DB;
use Modules\Communication\app\Http\Controllers\EmailController;
use Modules\Communication\app\Models\Templates;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Leads\app\Models\Payments;

class TransactionController extends Controller
{
    private EmailController $emailController;
    private TransactionInterface $transactionRepository;

    public function __construct(
        EmailController $emailController,
        TransactionInterface $transactionRepository
    ) {
        $this->emailController = $emailController;
        $this->transactionRepository = $transactionRepository;
    }

    public function listTransactions(TransactionRequest $request): JsonResponse
    {
        try {
            $transactions = $this->transactionRepository->listTransactions(
                $request->input('user_id'),
                $request->input('customer_id'),
                $request->input('provider_id'),
                $request->input('search'),
                $request->input('order_by', 'desc'),
                $request->input('sort_by', 'id')
            );

            $productIds = $transactions->pluck('product.id')->unique()->filter()->values()->toArray();
            $productImagesMap = $this->transactionRepository->getProductImages($productIds);

            $response = $transactions->map(function ($booking) use ($productImagesMap) {
                try {
                    $statusMap = $this->transactionRepository->getTransactionStatusMap();
                    $paymentTypeMap = $this->transactionRepository->getPaymentTypeMap();
                    $paymentStatusMap = $this->transactionRepository->getPaymentStatusMap();
                    $commissionRate = $this->transactionRepository->getCommissionRate();

                    $status = $statusMap[$booking->booking_status] ?? 'Unknown';
                    $paymentType = $paymentTypeMap[$booking->payment_type] ?? 'Unknown';
                    $paymentStatus = $paymentStatusMap[$booking->payment_status] ?? 'Unknown';

                    $customerDetails = UserDetail::withTrashed()
                        ->where('user_id', $booking->user->id ?? null)
                        ->select('user_id', 'profile_image')
                        ->first();

                    $providerDetails = UserDetail::withTrashed()
                        ->where('user_id', $booking->product->created_by ?? null)
                        ->select('user_id', 'profile_image')
                        ->first();

                    $serviceAmount = $booking->service_amount ?? 0;
                    $totalAmount = ($booking->total_amount ?? 0) - ($booking->amount_tax ?? 0);

                    $commissionAmount = 0;
                    if ($booking->payment_type == 5) {
                        $commissionAmount = 0;
                    } else {
                        if ($commissionRate > 0) {
                            $commissionAmount = ($totalAmount * $commissionRate) / 100;
                        }
                    }
                    $productId = $booking->product->id ?? null;
                    $images = $productImagesMap->get($productId, collect());

                    $additionalServices = $booking->additional_services 
                        ? json_decode($booking->additional_services) 
                        : [];

                    return [
                        'id' => $booking->id,
                        'order_id' => $booking->order_id,
                        'customer' => [
                            'id' => $booking->user->id ?? 'N/A',
                            'name' => ucfirst($booking->user->name ?? 'N/A'),
                            'email' => $booking->user->email ?? 'N/A',
                            'image' => $customerDetails->profile_image ?? 'N/A',
                            'image_url' => $this->transactionRepository->getCustomerProfileImage($customerDetails->profile_image ?? null),
                        ],
                        'provider' => [
                            'id' => $booking->product->createdBy->id ?? 'N/A',
                            'name' => ucfirst($booking->product->createdBy->name ?? 'N/A'),
                            'email' => $booking->product->createdBy->email ?? 'N/A',
                            'image' => $providerDetails->profile_image ?? 'N/A',
                            'image_url' => $this->transactionRepository->getProviderProfileImage($providerDetails->profile_image ?? null),
                        ],
                        'service' => [
                            'id' => $booking->product->id ?? 'N/A',
                            'name' => ucfirst($booking->product->source_name ?? 'N/A'),
                            'category' => $this->transactionRepository->getCategoryName($booking->product->source_category ?? null),
                            'service_image_url' => $this->transactionRepository->getProductImageUrl($images),
                        ],
                        'additional_services' => $additionalServices,
                        'amount' => [
                            'service_amount' => number_format($serviceAmount, 2),
                            'total_amount' => number_format($totalAmount, 2),
                            'tax' => number_format($booking->amount_tax ?? 0, 2),
                            'commission' => number_format($commissionAmount, 2),
                            'final_total' => number_format($totalAmount - $commissionAmount, 2),
                        ],
                        'payment' => [
                            'type' => $paymentType,
                            'status' => $paymentStatus,
                            'payment_proof' => $booking->payment_proof_path,
                            'transaction_id' => $booking->tranaction,
                            'bank_transfer_proof' => file_exists(public_path('storage/' . $booking->bank_transfer_proof ?? ''))
                                && $booking->bank_transfer_proof
                                ? url('storage/' . $booking->bank_transfer_proof ?? '') : '',
                        ],
                        'status' => $status,
                        'date' => date($this->transactionRepository->getDateFormat(), strtotime($booking->created_at)),
                        'currencySymbol' => $this->transactionRepository->getCurrencySymbol(),
                    ];
                } catch (\Exception $e) {
                    Log::error("Error processing booking {$booking->id}: " . $e->getMessage());
                    return [
                        'id' => $booking->id ?? 'N/A',
                        'error' => "Error processing booking",
                    ];
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaction list retrieved successfully',
                'data' => [
                    'transactions' => $response,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving transactions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving transactions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function listTransactionsapi(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        try {
            $customer = User::where('id', $user->id)
            ->where('user_type', "3")
            ->first();

            $provider = User::where('id', $user->id)
            ->where('user_type', "2")
            ->first();
            $userId = $request->input('user_id', null);
            $orderBy = $request->input('order_by', 'desc');
            $sortBy = $request->input('sort_by', 'id');
            $search = $request->input('search', null);
            $customerId = $customer ? $customer->id : null;
            $providerId = $provider ? $provider->id : null;
            $statusMap = [
                1 => 'Open',
                2 => 'Accepted',
                3 => 'Cancelled',
                4 => 'In Progress',
                5 => 'Completed',
            ];

            $paymentTypeMap = [
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

            $paymentStatusMap = [
                1 => 'Unpaid',
                2 => 'Paid',
                3 => 'Refund',
            ];

            $commissionRate = 0;
            $commissionSetting = GlobalSetting::where('key', 'commission_rate_percentage')->first();
            //echo ."DDDD";
            if ($commissionSetting) {
                $commissionRate = (float) $commissionSetting->value;
            }

            $query = Bookings::with(['user', 'product'])
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
                ->when($search, function ($query, $search) {
                    $query->whereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
                })
                ->orderBy($sortBy, $orderBy);

            $transactions = $query->get();

            $productIds = $transactions->pluck('product.id')->unique()->filter()->values();

            $productImagesMap = Productmeta::whereIn('product_id', $productIds)
                ->where('source_key', 'product_image')
                ->whereNull('deleted_at')
                ->get()
                ->groupBy('product_id');

            /**
             * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Bookings> $transactions
             * @return \Illuminate\Support\Collection<int, array<string, mixed>>
             */
            $response = $transactions->map(function ($booking) use ($statusMap, $paymentTypeMap, $paymentStatusMap, $commissionRate, $productImagesMap) {
                $status = $statusMap[$booking->booking_status] ?? 'Unknown';
                $paymentType = $paymentTypeMap[$booking->payment_type] ?? 'Unknown';
                $paymentStatus = $paymentStatusMap[$booking->payment_status] ?? 'Unknown';

                $customerDetails = UserDetail::where('user_id', $booking->user->id ?? null)
                ->select('user_id', 'profile_image')
                ->first();

            if ($customerDetails) {
                $customerDetails->profile_image_url = $customerDetails->profile_image
                    ? url('storage/profile/' . $customerDetails->profile_image)
                    : null;
            }

            $providerDetails = UserDetail::where('user_id', $booking->product->created_by ?? null)
                ->select('user_id', 'profile_image')
                ->first();

            if ($providerDetails) {
                $providerDetails->profile_image_url = $providerDetails->profile_image
                    ? url('storage/profile/' . $providerDetails->profile_image)
                    : null;
            }


                $categoryId = $booking->product->source_category;
                $category = Categories::where('id', $categoryId)->select('id', 'name')->first();
                $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();

                $serviceAmount = $booking->service_amount ?? 0;
                $tax = $booking->product->tax ?? 0;
                $totalAmount = ($booking->total_amount ?? 0) - ($booking->amount_tax ?? 0);

                $commissionAmount = 0;
                if ($booking->payment_type == 5) {
                    $commissionAmount = 0;
                } else {
                    if ($commissionRate > 0) {
                        $commissionAmount = ($totalAmount * $commissionRate) / 100;
                    }
                }

                $productId = $booking->product->id ?? null;

                $images = $productImagesMap->get($productId, collect());

                $validImage = $images->first(function ($img) {
                    return !empty($img->source_Values) && file_exists(public_path('storage/' . $img->source_Values));
                });

                $productImage = $validImage->source_Values ?? 'N/A';
                $productImageUrl = $validImage
                    ? url('storage/' . $validImage->source_Values)
                    : url('front/img/default-placeholder-image.png');

                $currencySymbol=Cache::remember('currecy_details', 86400, function () {
                    return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default',1)->first();
                });
                return [
                    'id' => $booking->id,
                    'order_id' => $booking->order_id,
                    'customer' => [
                        'id' => $booking->user->id ?? 'N/A',
                        'name' => ucfirst($booking->user->name ?? 'N/A'),
                        'email' => $booking->user->email ?? 'N/A',
                        'image' => $customerDetails->profile_image ?? 'N/A',
                        'image_url' => $customerDetails->profile_image_url ?? 'N/A',
                    ],
                    'provider' => [
                        'id' => $booking->product->createdBy->id ?? 'N/A',
                        'name' => ucfirst($booking->product->createdBy->name ?? 'N/A'),
                        'email' => $booking->product->createdBy->email ?? 'N/A',
                        'image' => $providerDetails->profile_image ?? 'N/A',
                        'image_url' => $providerDetails->profile_image_url ?? 'N/A',
                    ],
                    'service' => [
                        'id' => $booking->product->id ?? 'N/A',
                        'name' => ucfirst($booking->product->source_name ?? 'N/A'),
                        'category' => ucfirst($category ? $category->name : 'No Category'),
                        'service_image' => $productImage,
                        'service_image_url' => $productImageUrl,
                    ],
                    'amount' => [
                        'service_amount' => number_format($serviceAmount, 2),
                        'total_amount' => number_format($totalAmount, 2),
                        'tax' => number_format($booking->amount_tax ?? 0, 2),
                        'commission' => number_format($commissionAmount, 2),
                        'final_total' => number_format($totalAmount - $commissionAmount, 2),
                    ],
                    'payment' => [
                        'type' => $paymentType,
                        'status' => $paymentStatus,
                        'payment_proof' => $booking->payment_proof_path,
                        'transaction_id' => $booking->tranaction,
                    ],
                    'status' => $status,
                    'date' => date($dateformatSetting->value, strtotime($booking->created_at)),
                    'currencySymbol' => $currencySymbol->symbol,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaction list retrieved successfully',
                'data' => [
                    'transactions' => $response,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving transactions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadPaymentProof(TransactionRequest $request): JsonResponse
    {
        try {
            $filePath = $request->file('payment_proof')->store('payment-proofs', 'public');
            
            $booking = $this->transactionRepository->uploadPaymentProof([
                'booking_id' => $request->booking_id,
                'file_path' => $filePath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment proof uploaded successfully.',
                'data' => [
                    'file_path' => $filePath,
                    'payment_status' => $booking->payment_status,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Payment proof upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading the payment proof.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getProviderDetails(TransactionRequest $request): JsonResponse
    {
        try {
            $data = $this->transactionRepository->getProviderDetails($request->provider_id);
            
            return response()->json([
                'code' => 200,
                'message' => 'Provider details retrieved successfully.',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Get provider details failed: ' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function providerTransaction(TransactionRequest $request): JsonResponse
    {
        try {
            $response = $this->transactionRepository->providerTransaction($request->input('provider_id'));
            
            return response()->json([
                'success' => true,
                'message' => 'Provider transactions retrieved successfully.',
                'data' => $response,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Provider transactions failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving provider transactions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function storePayoutHistroy(TransactionRequest $request): JsonResponse
    {
        try {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
            
            $payoutHistory = $this->transactionRepository->storePayoutHistory([
                'provider_id' => $request->provider_id,
                'total_bookings' => $request->total_bookings,
                'total_earnings' => $request->total_earnings,
                'admin_earnings' => $request->admin_earnings,
                'provider_pay_due' => $request->provider_pay_due,
                'entered_amount' => $request->entered_amount,
                'payment_proof_path' => $paymentProofPath,
                'remaining_amount' => $request->remaining_amount,
                'payment_method' => $request->payment_method ?? null
            ]);

            return response()->json([
                'success' => true, 
                'data' => $payoutHistory
            ], 201);
        } catch (\Exception $e) {
            Log::error('Store payout history failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while processing the request.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function savePayouts(TransactionRequest $request): JsonResponse
    {
        try {
            $this->transactionRepository->savePayouts($request->all());
            
            return response()->json([
                'code' => 200,
                'message' => 'Payout saved successfully.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Save payouts failed: ' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'message' => 'Error! while saving payout',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPayoutDetails(TransactionRequest $request): JsonResponse
    {
        try {
            $data = $this->transactionRepository->getPayoutDetails($request->provider_id);
            
            return response()->json([
                'code' => 200,
                'message' => 'Payout details retrieved successfully.',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get payout details failed: ' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'message' => 'Error! while getting payout details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getProviderPayoutHistory(TransactionRequest $request): JsonResponse
    {
        try {
            $data = $this->transactionRepository->getProviderPayoutHistory($request->provider_id);
            
            return response()->json([
                'code' => 200,
                'message' => 'Provider Payout history retrieved successfully.',
                'data' => $data,
                'currency_symbol' => getDefaultCurrencySymbol()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get provider payout history failed: ' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'message' => 'Error! while getting payout history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getProviderPayoutRequest(TransactionRequest $request): JsonResponse
    {
        try {
            $data = $this->transactionRepository->getProviderPayoutRequest($request->provider_id);
            
            return response()->json([
                'code' => 200,
                'message' => 'Provider Payout requests retrieved successfully.',
                'data' => $data,
                'currency_symbol' => getDefaultCurrencySymbol()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error! while getting payout history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listProviderRequest(TransactionRequest $request): JsonResponse
    {
        try {
            $providerRequests = $this->transactionRepository->listProviderRequests();
            $currencySymbol = $this->getCurrencySymbol();

            return response()->json([
                'success' => true,
                'data' => $providerRequests,
                'currencySymbol' => $currencySymbol,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve provider requests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve provider requests.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateProviderRequest(TransactionRequest $request): JsonResponse
    {
        try {
            $filePath = $request->file('payment_proof')->store('payment_proofs', 'public');

            $this->transactionRepository->updateProviderRequest([
                'id' => $request->id,
                'provider_id' => $request->provider_id,
                'provider_amount' => $request->provider_amount,
                'payment_proof_path' => $filePath,
                'payment_method' => $request->payment_method ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Provider request updated and payout history recorded successfully.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Update provider request failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function sendProviderRequestAmount(TransactionRequest $request): JsonResponse
    {
        try {
            $this->transactionRepository->sendProviderRequestAmount([
                'provider_id' => $request->provider_id,
                'payment_id' => $request->payment_id,
                'amount' => $request->amount,
            ]);

            return response()->json([
                'code' => 200,
                'message' => 'Request sent successfully.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Send provider request amount failed: ' . $e->getMessage());
            $statusCode = $e->getMessage() === 'Please set your payment method.' ? 404 : 500;
            return response()->json([
                'code' => $statusCode,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    public function getProviderBalance(TransactionRequest $request): JsonResponse
    {
        try {
            $data = $this->transactionRepository->getProviderBalance($request->provider_id);
            $currencySymbol = $this->getCurrencySymbol();

            return response()->json([
                'code' => 200,
                'message' => 'Provider balance retrieved successfully.',
                'data' => array_merge($data, ['currency_symbol' => $currencySymbol])
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get provider balance failed: ' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'message' => 'Error! while getting provider balance',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function userpayoutrequestlist(TransactionRequest $request): JsonResponse
    {
        try {
            $transactions = $this->transactionRepository->getUserPayoutRequests();
            $currencySymbol = $this->getCurrencySymbol();

            return response()->json([
                'code' => 200,
                'message' => 'Refund list retrieved successfully',
                'data' => [
                    'transactions' => $transactions,
                    'currencySymbol' => $currencySymbol,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get user payout requests failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving transactions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updaterefund(TransactionRequest $request): JsonResponse
    {
        try {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');

            $this->transactionRepository->updateRefund([
                'bookingid' => $request->bookingid,
                'payment_proof_path' => $paymentProofPath,
            ]);

            $this->sendRefundEmailNotification($request->bookingid);

            return response()->json([
                'code' => 200,
                'message' => 'Payment Refund Successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Update refund failed: ' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getCurrencySymbol(): string
    {
        $currency = Cache::remember('currency_details', 86400, function () {
            return Currency::select('symbol')
                ->orderBy('id', 'DESC')
                ->where('is_default', 1)
                ->first();
        });

        return $currency->symbol ?? '';
    }

    private function sendRefundEmailNotification($bookingId): void
    {
        $bookingdata = Bookings::select(
            'bookings.*',
            DB::raw("
                CASE
                    WHEN bookings.payment_type = 1 THEN 'Paypal'
                    WHEN bookings.payment_type = 2 THEN 'Stripe'
                    WHEN bookings.payment_type = 3 THEN 'Razorpay'
                    WHEN bookings.payment_type = 4 THEN 'Bank Transfer'
                    WHEN bookings.payment_type = 5 THEN 'COD'
                    ELSE 'Unknown'
                END AS paymenttype"),
            DB::raw("DATE_FORMAT(bookings.created_at, '%d-%m-%Y') AS bookingdate"),
            DB::raw("TIME_FORMAT(bookings.from_time, '%H:%i') AS fromtime"),
            DB::raw("TIME_FORMAT(bookings.to_time, '%H:%i') AS totime"),
            'products.source_name',
            'users.name as user_name',
            'user_details.first_name as user_first_name',
            'user_details.last_name as user_last_name',
            'provider.name as provider_name',
            'provider.email as provideremail',
            'provider_details.first_name as provider_first_name',
            'provider_details.last_name as provider_last_name',
            'payout_history.id as refundid',
            'products.created_by',
            DB::raw("DATE_FORMAT(payout_history.created_at, '%d-%m-%Y') AS trxdate"),
            DB::raw("DATE_FORMAT(payout_history.updated_at, '%d-%m-%Y') AS refunddate")
        )
            ->leftJoin('products', 'products.id', '=', 'bookings.product_id')
            ->leftJoin('users', 'users.id', '=', 'bookings.user_id')
            ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
            ->leftJoin('users as provider', 'provider.id', '=', 'products.created_by')
            ->leftJoin('user_details as provider_details', 'provider_details.user_id', '=', 'provider.id')
            ->leftJoin('payout_history', 'payout_history.reference_id', '=', 'bookings.id')
            ->where('bookings.id', $bookingId)
            ->with(['user.userDetails', 'product.createdBy.userDetails'])
            ->first();

        $settingData = getCommonSettingData(['company_name', 'site_email', 'phone_no', 'site_address', 'postal_code', 'website']);

        if (isset($bookingdata) && $bookingdata->user_email) {
            $toEmail = $bookingdata->user_email;
            $this->sendRefundEmail('Refund Completed', $bookingdata, $settingData, $toEmail);
        }

        $adminUsers = User::with('userDetails')->where('user_type', 1)->get();
        if (isset($bookingdata) && $adminUsers->isNotEmpty()) {
            foreach ($adminUsers as $admin) {
                $toEmail = $admin->email;
                
                $adminName = isset($admin->name) ? ucwords($admin->name) : 'Admin';
                if ($admin->userDetails) {
                    $firstName = $admin->userDetails->first_name ?? '';
                    $lastName = $admin->userDetails->last_name ?? '';
                    $adminName = trim(ucwords($firstName . ' ' . $lastName));
                }

                $this->sendRefundEmail('Refund Completed email to Admin', $bookingdata, $settingData, $toEmail, $adminName);
            }
        }
    }

    private function sendRefundEmail($source, $bookingdata, $settingData, $toEmail, $adminName = '')
    {
        $gettemplate = Templates::select('templates.subject', 'templates.content')
            ->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')
            ->where('notification_types.type', $source)
            ->where('templates.type', 1)
            ->where('templates.status', 1)
            ->first();
    
        if (isset($gettemplate) && isset($bookingdata)) {
            $tempdata = [];
            $service = "";
            $fromtime = $bookingdata->fromtime ?? "";
            $totime = $bookingdata->totime ?? "";
            $service = $bookingdata->source_name;

            $userName = ucwords($bookingdata->user_first_name . ' ' . $bookingdata->user_last_name);
            $providerName = ucwords($bookingdata->provider_first_name . ' ' . $bookingdata->provider_last_name);

            $tempdata = [
                '{{user_name}}' => $userName,
                '{{customer_name}}' => $userName,
                '{{admin_name}}' => $adminName,
                '{{booking_id}}' => $bookingdata->order_id,
                '{{service_name}}' => $service,
                '{{appointment_date}}' => $bookingdata->bookingdate,
                '{{appointment_time}}' => $fromtime ? $fromtime . '-' . $totime : "",
                '{{team_name}}' => $providerName,
                '{{provider_name}}' => $providerName,
                '{{contact}}' => $bookingdata->provideremail,
                '{{website_link}}' => $bookingdata['product']['createdBy']['userDetails']->company_website ?? $settingData['website'] ?? "",
                '{{company_name}}' => $bookingdata['product']['createdBy']['userDetails']->company_name ?? $settingData['company_name'] ?? "",
                '{{service_address}}' => $bookingdata['product']['createdBy']['userDetails']->company_address ?? $bookingdata['product']['createdBy']['userDetails']->address ?? $settingData['site_address'] ?? "",
                '{{refund_id}}' => $bookingdata->refundid,
                '{{refund_amount}}' => getDefaultCurrencySymbol() . $bookingdata->service_amount,
                '{{transaction_date}}' => $bookingdata->trxdate,
                '{{refund_date}}' => $bookingdata->refunddate,
                '{{payment_method}}' => $bookingdata->paymenttype,
            ];

            // Replace placeholders dynamically
            $finalContent = Str::replace(array_keys($tempdata), array_values($tempdata), $gettemplate->content);
            $subject = Str::replace(array_keys($tempdata), array_values($tempdata), $gettemplate->subject);

            $data = [
                'to_email' => $toEmail,
                'subject' => $subject,
                'content' => $finalContent
            ];

            try {
                $request = new Request($data);
                $emailController = new EmailController();
                $emailController->sendEmail($request);
            } catch (\Exception $e) {
                Log::error('Error sending email: ' . $e->getMessage());
            }
        }
    }

    public function leadsTransactionList(Request $request)
    {
        try {
            $orderBy = $request->order_by ?? 'desc';
            $customerId = $request->customer_id ?? '';
            $providerId = $request->provider_id ?? '';

            $query = Payments::orderBy('payments.id', $orderBy)
                ->join('user_form_inputs', 'user_form_inputs.id', '=', 'payments.reference_id')
                ->join('provider_forms_input', 'provider_forms_input.user_form_inputs_id', '=', 'user_form_inputs.id')
                ->where(['provider_forms_input.user_status' => 2, 'payments.status' => 2]);

            if (!empty($customerId)) {
                $query->where(['user_form_inputs.user_id' => $customerId]);
            }

            if (!empty($providerId)) {
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

            $paymentTypeMap = [
                1 => 'Paypal',
                2 => 'Stripe',
                3 => 'Razorpay',
                4 => 'Bank Transfer',
                5 => 'COD',
                6 => 'Wallet',
                7 => 'Mollie',
            ];

            $paymentStatusMap = [
                1 => 'Unpaid',
                2 => 'Paid',
                3 => 'Refund',
            ];

            $currency = Cache::remember('currency_details', 86400, function () {
                return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
            });

            $response = $transactions->map(function ($lead) use ($paymentTypeMap, $paymentStatusMap, $currency) {
                // Get customer details with null checks
                $customerDetails = User::withTrashed()
                    ->where('users.id', $lead->user_id)
                    ->join('user_details', 'users.id', '=', 'user_details.user_id')
                    ->select('users.id', 'user_details.profile_image', 'user_details.first_name', 'user_details.last_name', 'users.email')
                    ->first();

                $customerProfileImage = asset('assets/img/profile-default.png');
                if ($customerDetails && !empty($customerDetails->profile_image)) {
                    $profilePath = public_path('storage/profile/' . $customerDetails->profile_image);
                    $customerProfileImage = file_exists($profilePath) ? url('storage/profile/' . $customerDetails->profile_image) : $customerProfileImage;
                }

                // Get provider details with null checks
                $providerDetails = User::withTrashed()
                    ->where('users.id', $lead->provider_id)
                    ->join('user_details', 'users.id', '=', 'user_details.user_id')
                    ->select('users.id', 'user_details.profile_image', 'user_details.first_name', 'user_details.last_name', 'users.email')
                    ->first();

                $providerProfileImage = asset('assets/img/profile-default.png');
                if ($providerDetails && !empty($providerDetails->profile_image)) {
                    $profilePath = public_path('storage/profile/' . $providerDetails->profile_image);
                    $providerProfileImage = file_exists($profilePath) ? url('storage/profile/' . $providerDetails->profile_image) : $providerProfileImage;
                }

                // Get category
                $category = $lead->category_id 
                    ? Categories::where('id', $lead->category_id)->select('id', 'name')->first()
                    : null;

                     $currency = Cache::remember('currecy_details', 86400, function () {
                        return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default',1)->first();
                    });

                return [
                    'id' => $lead->payment_id,
                    'payment' => [
                        'type' => $paymentTypeMap[$lead->payment_type] ?? 'Unknown',
                        'date' => formatDateTime($lead->payment_date),
                        'status' => $paymentStatusMap[$lead->payment_status] ?? 'Unknown',
                        'amount' => number_format($lead->amount, 2)
                    ],
                    'customer' => [
                        'id' => $customerDetails->id ?? null,
                        'full_name' => ($customerDetails->first_name ?? '') . ' ' . ($customerDetails->last_name ?? ''),
                        'profile_image' => $customerProfileImage,
                        'email' => $customerDetails->email ?? null
                    ],
                    'provider' => [
                        'id' => $providerDetails->id ?? null,
                        'full_name' => ($providerDetails->first_name ?? '') . ' ' . ($providerDetails->last_name ?? ''),
                        'profile_image' => $providerProfileImage,
                        'email' => $providerDetails->email ?? null
                    ],
                    'currency' => $currency->symbol ?? '$',
                    'category' => $category->name ?? '-'
                ];
            });

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'Leads Transaction list retrieved successfully',
                'data' => $response
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in leadsTransactionList: ' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'Error while retrieving leads transactions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyBankTransfer(Request $request)
    {
        try {
            $bookingId = $request->input('booking_id');

            Bookings::where('id', $bookingId)->update([
                'payment_status' => 2
            ]);
            
            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'Transaction verified successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'Error while verifying transaction',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}

