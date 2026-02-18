<?php

namespace App\Http\Controllers;

use App\Http\Requests\WalletAmountRequest;
use App\Http\Requests\LeadPaymentRequest;
use App\Http\Requests\TransactionSuccessRequest;
use App\Repositories\Contracts\WalletInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WalletController extends Controller
{
    protected $walletRepository;

    public function __construct(WalletInterface $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    public function addWalletAmount(Request $request): JsonResponse
    {
        try {
            $walletHistory = $this->walletRepository->addWalletAmount([
                'user_id' => $request->userId,
                'amount' => $request->amount,
                'payment_method' => $request->paymentMethod,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Amount added to wallet successfully',
                'data' => $walletHistory,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding amount to wallet',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function walletsucess(Request $request): View
    {
        $this->walletRepository->confirmTransaction($request->token, 'Completed');
        return view('user.userwalletpaymentsuccess');
    }

    public function listWalletHistory(Request $request): JsonResponse
    {
        try {
            $walletHistory = $this->walletRepository->getWalletHistory($request->userId);
            $balance = $this->walletRepository->getWalletBalance($request->userId);
            $currencySymbol = getDefaultCurrencySymbol();

            return response()->json([
                'success' => true,
                'message' => 'Wallet history retrieved successfully',
                'data' => $walletHistory,
                'totalAmount' => number_format($balance['total'], 2, '.', ''),
                'totalAmountdebit' => number_format($balance['debit'], 2, '.', ''),
                'totalAmountBalance' => number_format($balance['balance'], 2, '.', ''),
                'Currency' => $currencySymbol,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving wallet history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function leasdwalletPayment(Request $request): JsonResponse
    {
        $authId = Auth::id();
        $balance = $this->walletRepository->getWalletBalance($authId)['balance'];

        if ($balance < $request->input('service_amount')) {
            return response()->json([
                'code' => 422,
                'message' => 'Insufficient balance in wallet!',
                'data' => []
            ], 422);
        }

        $this->walletRepository->processLeadPayment([
            'user_id' => $authId,
            'amount' => $request->input('service_amount'),
            'payment_type' => $request->paymenttype,
            'transaction_id' => $request->trx_id,
            'reference_id' => $request->refid,
        ]);

        return response()->json([
            'message' => 'Order created successfully.',
            'url' => route('leasdwalletPayment.leads.Success')
        ]);
    }

    public function leasdwalletPaymentSuccess(Request $request): View
    {
        return view('user.userpaymentsuccess');
    }

    public function addWalletAmountApi(WalletAmountRequest $request): JsonResponse
    {
        try {
            $transactionId = 'TXN' . strtoupper(uniqid());
            $validated = $request->validated();

            $walletHistory = $this->walletRepository->addWalletAmount([
                'user_id' => $validated['user_id'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $transactionId,
            ]);

            $paymentIntent = $this->walletRepository->createStripePaymentIntent($validated['amount']);

            return response()->json([
                'code' => 200,
                'message' => __('Success'),
                'data' => [
                    'amount' => $validated['amount'],
                    'client_secret' => $paymentIntent->client_secret,
                    'transaction_id' => $transactionId,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding amount to wallet',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function walletsucessApi(TransactionSuccessRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->walletRepository->confirmTransaction($validated['transaction_id'], 'Completed');

        if (!$result) {
            return response()->json([
                'code' => 404,
                'message' => __('Transaction not found'),
                'data' => [],
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => __('Wallet Payment Success'),
            'data' => [
                'transaction_id' => $validated['transaction_id'],
                'status' => 'Completed',
            ],
        ], 200);
    }

    public function addleadsAmountApi(LeadPaymentRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($validated['payment_method'] === "stripe") {
            $transactionId = 'TXN' . strtoupper(uniqid());
            $paymentIntent = $this->walletRepository->createStripePaymentIntent($validated['amount']);

            $this->walletRepository->addWalletAmount([
                'user_id' => $validated['user_id'],
                'amount' => $validated['amount'],
                'payment_method' => 'stripe',
                'status' => 'pending',
                'transaction_id' => $transactionId,
                'reference_id' => $validated['lead_id'],
                'type' => 2,
            ]);

            return response()->json([
                'code' => 200,
                'message' => __('Success'),
                'data' => [
                    'amount' => $validated['amount'],
                    'client_secret' => $paymentIntent->client_secret,
                    'transaction_id' => $transactionId,
                ],
            ], 200);
        }

        return response()->json([
            'code' => 400,
            'message' => __('Invalid Payment Method'),
            'data' => [],
        ], 400);
    }

    public function stripesucessApi(TransactionSuccessRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->walletRepository->confirmTransaction($validated['transaction_id'], 'Completed');

        if (!$result) {
            return response()->json([
                'code' => 404,
                'message' => __('Transaction not found'),
                'data' => [],
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => __('Payment Success'),
            'data' => [
                'transaction_id' => $validated['transaction_id'],
                'status' => 'Completed',
            ],
        ], 200);
    }
}