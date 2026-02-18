<?php

namespace App\Http\Controllers;

use app\Http\Controllers;
use Modules\GlobalSetting\app\Models\Placeholders;
use Modules\Categories\app\Models\Categories;
use App\Models\Bookings;
use App\Models\Branches;
use App\Models\Dispute;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Models\PayoutHistory;
use App\Models\UserDetail;
use App\Models\WalletHistory;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Support\Carbon;
use Modules\Communication\app\Http\Controllers\EmailController;
use Modules\Communication\app\Http\Controllers\NotificationController;
use Modules\Communication\app\Models\Templates;
use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\GlobalSetting\app\Models\Currency;
use Modules\Product\app\Models\Product;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\Product\app\Models\Productmeta as ModelsProductmeta;
use Modules\Service\app\Models\AdditionalService;
use Modules\Service\app\Models\Productmeta;
use Modules\Service\app\Models\Service;
use Modules\GoogleCalendarSync\app\Http\Controllers\GoogleCalendarSyncController;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Checkout\Session;
class BookingController extends Controller
{
    protected BookingRepositoryInterface $bookingRepository;

    public function __construct(BookingRepositoryInterface $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    public function index()
    {
        $data = $this->bookingRepository->index();
        // return response()->json($data, 200);
        return view('booking.bookinglist', $data);
    }

    public function userBookinglist()
    {
        $response = $this->bookingRepository->userBookinglist();
        return response()->json($response, 200);
    }

    public function providerindex(Request $request): JsonResponse|View
    {
        $data = $this->bookingRepository->providerindex($request);
        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            if ($data['bookingdata']->isEmpty()) {
                return response()->json(['code' => 200, 'message' => "Booking list details not found", 'data' => []], 200);
            }
            return response()->json(['code' => 200, 'message' => "Booking list fetched successfully", 'data' => $data], 200);
        } else {
            return view('provider.booking.bookinglist', compact('data'));
        }
    }

    public function staffindex(Request $request): JsonResponse|View
    {
        $data = $this->bookingRepository->staffindex($request);

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            if ($data['bookingdata']->isEmpty()) {
                return response()->json(['code' => 200, 'message' => "Booking list details not found", 'data' => []], 200);
            }
            return response()->json(['code' => 200, 'message' => "Booking list fetched successfully", 'data' => $data], 200);
        } else {
            return view('staff.bookinglist', compact('data'));
        }
    }

    public function updatebookingstatus(Request $request): JsonResponse
    {
        $response = $this->bookingRepository->updateBookingStatus($request);
        return response()->json($response, $response['code']);
    }

    public function calenderview(): View
    {
        return view('admin.booking.calendar');
    }

    public function getBookings(Request $request): JsonResponse
    {
        $bookings = $this->bookingRepository->getBookings($request);
        return $bookings;
    }

    public function listindex(Request $request): View
    {
        return view('admin.booking.list');
    }

    public function getBookinglists(Request $request): JsonResponse
    {
        $data = $this->bookingRepository->getBookinglists($request);
        return response()->json($data, $data['code']);
    }

    public function indexRequest(Request $request): JsonResponse
    {
        $data = $this->bookingRepository->indexRequest($request);
        return response()->json($data, $data['code']);
    }

    public function requestDispute(Request $request): JsonResponse
    {
        return $this->bookingRepository->requestDispute($request);
    }

    public function requestDisputeApi(Request $request): JsonResponse
    {
        return $this->bookingRepository->requestDisputeApi($request);
    }

    public function UpdateRequest(Request $request): JsonResponse
    {
        return $this->bookingRepository->UpdateRequest($request);
    }

    public function getDisputeDetails(Request $request)
    {
        $response = $this->bookingRepository->getDisputeDetails($request);
        return response()->json($response);
    }

    public function getDisputeDetailsApi(Request $request)
    {
        return $this->bookingRepository->getDisputeDetailsApi($request);
    }

    public function getDisputeInfo(Request $request)
    {
        return $this->bookingRepository->getDisputeInfo($request);
    }

    public function getBookingDetails(Request $request)
    {
        return $this->bookingRepository->getBookingDetails($request);
    }

    public function WalletCheck(Request $request)
    {
        $response = $this->bookingRepository->WalletCheck($request);
        return response()->json($response, $response['code']);
    }

    /**
     * Cancel Google Calendar event for a booking

     */
    public function cancelCalendarEvent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|integer|exists:bookings,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid booking ID',
                'errors' => $validator->errors()
            ], 400);
        }
        try {
            $booking = Bookings::find($request->booking_id);
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            $googleCalendarController = new GoogleCalendarSyncController();
            $result = $googleCalendarController->cancelGoogleCalendarEvent($booking);

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Calendar event cancelled successfully' : 'Failed to cancel calendar event'
            ], $result ? 200 : 400);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling calendar event: ' . $e->getMessage()
            ], 500);
        }
    }
    public function makePaymentBooking(Request $request)
    {
        DB::beginTransaction();

        try {
            $authId = Auth::id();

            /* ------------------------------------
            | Normalize payment type
            ------------------------------------ */
            $paymentType = $request->input('payment_type');
            if ($paymentType === '' || $paymentType === 'null') {
                $paymentType = null;
            }

            /* ------------------------------------
            | Validate
            ------------------------------------ */
            $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'total_amount' => 'required|numeric|min:0',
                'payment_type' => 'nullable|in:stripe,wallet,cod,paypal',
            ]);

            /* ------------------------------------
            | Fetch Booking
            ------------------------------------ */
            $booking = Bookings::where('id', $request->booking_id)
                ->where('user_id', $authId)
                ->first();

            if (!$booking) {
                return response()->json([
                    'code' => 404,
                    'message' => 'Booking not found.'
                ], 404);
            }

            if ($booking->payment_type !== null) {
                return response()->json([
                    'code' => 400,
                    'message' => 'Payment already completed.'
                ], 400);
            }

            $totalAmount = (float) $request->total_amount;


            if ($paymentType === 'cod' || is_null($paymentType)) {

                $booking->update([
                    'payment_type' => 5, // COD
                    'payment_status' => 1, // Pending
                ]);

                DB::commit();

                // sendBookingNotification($booking->id);

                return response()->json([
                    'code' => 200,
                    'message' => 'Booking confirmed. Pay later.',
                ]);
            }


            if ($paymentType === 'wallet') {

                $credit = WalletHistory::where('user_id', $authId)
                    ->where('type', 1)->sum('amount');

                $debit = WalletHistory::where('user_id', $authId)
                    ->where('type', 2)->sum('amount');

                if (($credit - $debit) < $totalAmount) {
                    DB::rollBack();
                    return response()->json([
                        'code' => 400,
                        'message' => 'Insufficient wallet balance.'
                    ], 400);
                }

                WalletHistory::create([
                    'user_id' => $authId,
                    'amount' => $totalAmount,
                    'payment_type' => 'Wallet',
                    'status' => 'Completed',
                    'type' => 2,
                    'reference_id' => $booking->id,
                    'transaction_id' => $booking->order_id,
                    'transaction_date' => now(),
                ]);

                $booking->update([
                    'payment_type' => 6, // Wallet
                    'payment_status' => 2, // Paid
                ]);

                DB::commit();

                // sendBookingNotification($booking->id);

                return response()->json([
                    'code' => 200,
                    'message' => 'Payment completed using wallet.'
                ]);
            }


            if ($paymentType === 'stripe') {

                $stripeSecret = config('stripe.test.sk');
                if (!$stripeSecret) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Stripe unavailable.'
                    ], 422);
                }

                Stripe::setApiKey($stripeSecret);

                $currency = Currency::where('is_default', 1)->first();

                $session = Session::create([
                    'line_items' => [
                        [
                            'price_data' => [
                                'currency' => $currency?->code ?? 'usd',
                                'product_data' => [
                                    'name' => $chore?->title ?? 'Booking Payment',
                                ],
                                'unit_amount' => intval($totalAmount * 100),
                            ],
                            'quantity' => 1,
                        ]
                    ],
                    'mode' => 'payment',
                    'success_url' => route('stripe.success.common') .
                        "?session_id={CHECKOUT_SESSION_ID}&order_id={$booking->order_id}",
                    'cancel_url' => route('stripe.chore.cancel', ['order_id' => $booking->order_id]),
                    'metadata' => [
                        'booking_id' => $booking->id,
                        'order_id' => $booking->order_id,
                        'user_id' => $authId,
                    ],
                ]);

                $booking->update([
                    'payment_type' => 2, // Stripe
                    'payment_status' => 1, // Pending
                ]);

                DB::commit();

                return response()->json([
                    'code' => 200,
                    'message' => 'Redirecting to Stripe...',
                    'data' => [
                        'checkout_url' => $session->url
                    ]
                ]);
            }

            if ($paymentType === 'paypal') {

                $provider = new \Srmklive\PayPal\Services\PayPal;
                $provider->setApiCredentials(config('paypal'));
                $provider->getAccessToken();

                $response = $provider->createOrder([
                    "intent" => "CAPTURE",
                    "application_context" => [
                        "return_url" => route('user.paypal.success.booking'),
                        "cancel_url" => route('paypal.cancel'),
                    ],
                    "purchase_units" => [
                        [
                            "reference_id" => (string) $booking->id,
                            "custom_id" => (string) $booking->order_id,
                            "amount" => [
                                "currency_code" => "USD",
                                "value" => number_format($totalAmount, 2, '.', '')
                            ]
                        ]
                    ]
                ]);

                if (!isset($response['id'])) {
                    DB::rollBack();
                    return response()->json([
                        'code' => 500,
                        'message' => 'Unable to create PayPal payment.'
                    ], 500);
                }

                $booking->update([
                    'payment_type' => 1, // PayPal
                    'payment_status' => 1, // Pending
                    'tranaction' => $response['id'],
                ]);

                DB::commit();

                foreach ($response['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        return response()->json([
                            'code' => 200,
                            'message' => 'Redirecting to PayPal...',
                            'data' => [
                                'checkout_url' => $link['href']
                            ]
                        ]);
                    }
                }

                return response()->json([
                    'code' => 500,
                    'message' => 'PayPal approval link not found.'
                ], 500);
            }



        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('makeChorePayment Error: ' . $e->getMessage());

            return response()->json([
                'code' => 500,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }

    public function stripeSuccessCommon(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
            $orderId = $request->get('order_id');

            $booking = Bookings::where('order_id', $orderId)->firstOrFail();

            if ($booking->payment_status == 2) {
                return redirect()->route('payment.two');
            }

            $booking->update([
                'payment_status' => 2
            ]);

            try {
                // Chore OR Service
                if ($booking->chore_id) {
                    $providerId = optional(
                        PostChore::find($booking->chore_id)
                    )->assigned_provider_id;
                } else {
                    $providerId = optional(
                        Product::find($booking->product_id)
                    )->created_by;
                }

                if ($providerId) {
                    \App\Helpers\InvoiceHelper::generateInvoice(
                        $booking->id,
                        $booking->total_amount,
                        2,
                        $providerId
                    );
                }
            } catch (\Exception $e) {
                \Log::error('Invoice generation failed: ' . $e->getMessage());
            }

            // sendBookingNotification($booking->id);

            return redirect()->route('user.bookinglist');

        } catch (\Exception $e) {
            \Log::error('Stripe Success Error: ' . $e->getMessage());
            return redirect()->route('payment.failed');
        }
    }

    public function paypalSuccess(Request $request)
    {
        try {
            if (!$request->token) {
                \Log::error('PayPal missing token', $request->all());
                return redirect()->route('payment.failed');
            }

            $provider = new \Srmklive\PayPal\Services\PayPal;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            // Fetch order details
            $order = $provider->showOrderDetails($request->token);

            if (!isset($order['status'])) {
                \Log::error('PayPal invalid order response', $order);
                return redirect()->route('payment.failed');
            }

            /**
             * IMPORTANT FIX:
             * Accept BOTH APPROVED and COMPLETED
             */
            if (!in_array($order['status'], ['APPROVED', 'COMPLETED'])) {
                \Log::error('PayPal invalid order status', $order);
                return redirect()->route('payment.failed');
            }

            // Capture only if needed
            if ($order['status'] === 'APPROVED') {
                $capture = $provider->capturePaymentOrder($request->token);

                if (!isset($capture['status']) || $capture['status'] !== 'COMPLETED') {
                    \Log::error('PayPal capture failed', $capture);
                    return redirect()->route('payment.failed');
                }
            }

            $booking = Bookings::where('tranaction', $request->token)->first();

            if (!$booking) {
                \Log::error('Booking not found for PayPal order', [
                    'token' => $request->token
                ]);
                return redirect()->route('payment.failed');
            }

            // Prevent double payment
            if ($booking->payment_status == 2) {
                return redirect()->route('user.bookinglist');
            }

            $booking->update([
                'payment_status' => 2, // Paid
            ]);

            /* -------------------------
            | Generate Invoice
            ------------------------- */
            try {
                if ($booking->chore_id) {
                    $providerId = optional(
                        PostChore::find($booking->chore_id)
                    )->assigned_provider_id;
                } else {
                    $providerId = optional(
                        Product::find($booking->product_id)
                    )->created_by;
                }

                if ($providerId) {
                    \App\Helpers\InvoiceHelper::generateInvoice(
                        $booking->id,
                        $booking->total_amount,
                        3, // PayPal
                        $providerId
                    );
                }
            } catch (\Exception $e) {
                \Log::error('PayPal Invoice Error: ' . $e->getMessage());
            }

            return redirect()->route('user.bookinglist');

        } catch (\Exception $e) {
            \Log::error('PayPal Success Error: ' . $e->getMessage());
            return redirect()->route('payment.failed');
        }
    }


    public function paypalCancel()
    {
        return redirect()->route('payment.failed');
    }

}
