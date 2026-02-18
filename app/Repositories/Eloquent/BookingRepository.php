<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BookingRepositoryInterface;
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

class BookingRepository implements BookingRepositoryInterface
{
    public function index(): array
    {
        if (Auth::id()) {
            $authUserId = Auth::id();
        } else {
            $authUserId = Cache::get('user_auth_id');
        }
        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $timeformatSetting = GlobalSetting::where('key', 'time_format_view')->first();
        $timeFormat = $timeformatSetting->value ?? '%H:%i';
        $sqlTimeFormat = $this->mapTimeFormatToSQL($timeFormat);
        $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
        $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);
        $data['bookingdata'] = Bookings::select(
            'bookings.id',
            'bookings.order_id',
            'bookings.total_amount',
            'bookings.user_city',
            'bookings.booking_status',
            'bookings.payment_type',
            DB::raw("DATE_FORMAT(
                CASE 
                    WHEN bookings.booking_date IS NOT NULL THEN bookings.booking_date
                    ELSE bookings.created_at
                END, '{$sqlDateFormat}'
            ) AS bookingdate"),
            DB::raw("TIME_FORMAT(bookings.from_time, '{$sqlTimeFormat}') AS fromtime"),
            DB::raw("TIME_FORMAT(bookings.to_time, '{$sqlTimeFormat}') AS totime"),
            'bookings.product_id',
            'products.source_name',
            'users.name as user_name',
            'provider.name as provider_name',
            'provider.email as email',
            'provider.phone_number as phone_number',
            DB::raw("CONCAT(user_details.first_name, ' ', user_details.last_name) as user_full_name"),
            DB::raw("CONCAT(provider_details.first_name, ' ', provider_details.last_name) as provider_full_name"),
            DB::raw('(SELECT mobile_number
            FROM user_details WHERE user_details.user_id = products.created_by and user_details.deleted_at is NULL  LIMIT 1) as contact'),
            DB::raw('(SELECT  profile_image
            FROM user_details
            WHERE user_details.user_id = products.created_by and user_details.deleted_at is NULL  LIMIT 1) as profile_image'),
            'disputes.subject',
            'disputes.content',
            'disputes.admin_reply',
            DB::raw("
        CASE
            WHEN bookings.booking_status = 1 THEN 'Open'
            WHEN bookings.booking_status = 2 THEN 'In progress'
            WHEN bookings.booking_status = 3 THEN 'Provider Cancelled'
            WHEN bookings.booking_status = 4 THEN 'Refund Initiated'
            WHEN bookings.booking_status = 5 THEN 'Completed'
            WHEN bookings.booking_status = 6 THEN 'Order Completed'
            WHEN bookings.booking_status = 7 THEN 'Refund Completed'
            WHEN bookings.booking_status = 8 THEN 'Cancelled'
            ELSE 'Unknown'
        END AS booking_status_label
        "),
            DB::raw("
        CASE
            WHEN bookings.payment_type = 1 THEN 'Paypal'
            WHEN bookings.payment_type = 2 THEN 'Stripe'
            WHEN bookings.payment_type = 3 THEN 'Razorpay'
            WHEN bookings.payment_type = 4 THEN 'Bank Transfer'
            WHEN bookings.payment_type = 5 THEN 'COD'
            WHEN bookings.payment_type = 6 THEN 'Wallet'
            WHEN bookings.payment_type = 7 THEN 'Mollie'
            WHEN bookings.payment_type = 8 THEN 'PayU'
            WHEN bookings.payment_type = 9 THEN 'Cashfree'
            WHEN bookings.payment_type = 10 THEN 'Authorize.net'
            WHEN bookings.payment_type = 11 THEN 'Paystack'
            WHEN bookings.payment_type = 12 THEN 'Mercado Pago'
            ELSE 'Unpaid'
        END AS paymenttype"),
            DB::raw("(select payment_proof from payout_history where reference_id=bookings.id order by created_at desc limit 1 ) as payment_proof")
        )
            ->leftJoin('products', 'products.id', '=', 'bookings.product_id')
            ->leftJoin('users', 'users.id', '=', 'bookings.user_id')
            ->leftJoin('users as provider', 'provider.id', '=', 'products.created_by')
            ->leftJoin('disputes', 'disputes.booking_id', '=', 'bookings.id')
            ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
            ->leftJoin('user_details as provider_details', 'provider_details.user_id', '=', 'provider.id')
            ->where('bookings.user_id', $authUserId)
            ->with(['user.userDetails', 'product.createdBy.userDetails', 'review.reply'])
            ->groupBy('bookings.order_id', 'bookings.id', 'provider_details.first_name', 'provider_details.last_name', 'user_details.first_name', 'user_details.last_name', 'provider.email', 'bookings.total_amount', 'products.created_by', 'products.id', 'bookings.product_id', 'bookings.booking_date', 'bookings.from_time', 'bookings.to_time', 'bookings.booking_status', 'bookings.payment_type', 'bookings.user_id', 'bookings.user_city', 'bookings.created_at', 'products.source_name', 'users.name', 'provider.phone_number', 'provider.name', 'disputes.subject', 'disputes.content', 'disputes.admin_reply')
            ->orderByDesc('bookings.id')
            ->paginate(10);

        $productIds = $data['bookingdata']->pluck('product_id')->unique()->filter()->values();

        $productImagesMap = ModelsProductmeta::whereIn('product_id', $productIds)
            ->where('source_key', 'product_image')
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('product_id');

        foreach ($data['bookingdata'] as $booking) {
            if ($booking->review) {
                $booking->review->review_date = formatDateTime($booking->review->review_date, true);
            }

            if ($booking->review && $booking->review->reply) {
                $booking->review->reply->review_date = formatDateTime($booking->review->reply->review_date, true);
            }

            $images = $productImagesMap->get($booking->product_id, collect());

            $validImage = $images->first(function ($img) {
                return !empty($img->source_Values) && file_exists(public_path('storage/' . $img->source_Values));
            });

            $booking->productimage = $validImage
                ? url('storage/' . $validImage->source_Values)
                : url('front/img/default-placeholder-image.png');

            $booking->user_name = $booking->user_full_name ? ucwords($booking->user_full_name) : ucwords($booking->user_name);
            $booking->provider_name = $booking->provider_full_name ? ucwords($booking->provider_full_name) : ucwords($booking->provider_name);
        }

        $currency = Cache::remember('currecy_details', 86400, function () {
            return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
        });

        $data['currency'] = "$";
        if (isset($currency)) {
            $data['currency'] = $currency->symbol;
        }
        $data['authuserid'] = $authUserId;
        foreach ($data['bookingdata'] as $booking) {
            $couponLog = DB::table('coupon_logs')->where('booking_id', $booking->id)->first();
            $booking->coupon_code = $couponLog ? $couponLog->coupon_code : null;
        }
                $data['paymentInfo'] = GlobalSetting::where('group_id', 13)
            ->whereIn('key', [
                'stripe_status',
                'paypal_status',
                'cod_status',
                'wallet_status',
                'mollie_status',
            ])
            ->pluck('value', 'key')
            ->toArray();
        return ['data' => $data];
    }
    
    public function userBookingList(): array
    {
        $user = Auth::user();
        $authUserId = $user->id;
        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
        $dateFormat1 = '%d-%m-%Y %h:%i %A';

        $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);
        $sqlDateFormat1 = $this->mapDateFormatToSQL($dateFormat1);

        $data['bookingdata'] = Bookings::select(
            'bookings.id',
            'bookings.order_id',
            'bookings.total_amount',
            'bookings.tranaction',
            'bookings.from_time',
            'bookings.to_time',
            'bookings.amount_tax',
            'bookings.service_qty',
            'bookings.service_amount',
            'bookings.user_city',
            'bookings.booking_status',
            'bookings.payment_type',
            'bookings.product_id',
            DB::raw("DATE_FORMAT(bookings.created_at, '{$sqlDateFormat}') AS bookingdate"),
            DB::raw("DATE_FORMAT(bookings.booking_date, '{$sqlDateFormat}') AS bookedon"),
            DB::raw("bookings.created_at AS tractiondate"),
            DB::raw("bookings.updated_at AS processdate"),
            'products.source_name',
            'categories.name as category_name',
            'users.name as user_name',
            'provider.name as provider_name',
            'provider.email as email',
            DB::raw('(SELECT mobile_number
            FROM user_details WHERE user_details.user_id = products.created_by and user_details.deleted_at is NULL  LIMIT 1) as contact'),
            DB::raw('(SELECT  profile_image
            FROM user_details
            WHERE user_details.user_id = products.created_by and user_details.deleted_at is NULL  LIMIT 1) as profile_image'),
            'disputes.subject',
            'disputes.content',
            'disputes.admin_reply',
            DB::raw("
        CASE
            WHEN bookings.booking_status = 1 THEN 'Open'
            WHEN bookings.booking_status = 2 THEN 'In progress'
            WHEN bookings.booking_status = 3 THEN 'Provider Cancelled'
            WHEN bookings.booking_status = 4 THEN 'Refund Initiated'
            WHEN bookings.booking_status = 5 THEN 'Completed'
            WHEN bookings.booking_status = 6 THEN 'Order Completed'
            WHEN bookings.booking_status = 7 THEN 'Refund Completed'
            WHEN bookings.booking_status = 8 THEN 'Cancelled'
            ELSE 'Unknown'
        END AS booking_status_label
        "),
            DB::raw("
        CASE
            WHEN bookings.payment_type = 1 THEN 'Paypal'
            WHEN bookings.payment_type = 2 THEN 'Stripe'
            WHEN bookings.payment_type = 3 THEN 'Razorpay'
            WHEN bookings.payment_type = 4 THEN 'Bank Transfer'
            WHEN bookings.payment_type = 5 THEN 'COD'
            WHEN bookings.payment_type = 6 THEN 'Wallet'
            WHEN bookings.payment_type = 7 THEN 'Mollie'
            WHEN bookings.payment_type = 8 THEN 'PayU'
            WHEN bookings.payment_type = 9 THEN 'Cashfree'
            WHEN bookings.payment_type = 10 THEN 'Authorize.net'
            WHEN bookings.payment_type = 11 THEN 'Paystack'
            WHEN bookings.payment_type = 12 THEN 'Mercado Pago'
            ELSE 'Unpaid'
        END AS paymenttype"),
            DB::raw('(SELECT products_meta.source_Values
        FROM products_meta
        WHERE products_meta.product_id = products.id
        AND products_meta.source_key = "product_image" LIMIT 1) as productimage'),
            DB::raw("(select payment_proof from payout_history where reference_id=bookings.id order by created_at desc limit 1 ) as payment_proof")
        )
            ->leftJoin('products', 'products.id', '=', 'bookings.product_id')
            ->leftJoin('users', 'users.id', '=', 'bookings.user_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.source_category')
            ->leftJoin('users as provider', 'provider.id', '=', 'products.created_by')
            ->leftJoin('disputes', 'disputes.booking_id', '=', 'bookings.id')
            ->where('bookings.user_id', $authUserId)
            ->with(['user.userDetails', 'product.createdBy.userDetails', 'review.reply'])
            ->orderByDesc('bookings.created_at')
            ->paginate(10)->getCollection()->transform(function ($item) {
                if ($item->review) {
                    $item->review->review_date = formatDateTime($item->review->review_date, true);
                }

                if ($item->review && $item->review->reply) {
                    $item->review->reply->review_date = formatDateTime($item->review->reply->review_date, true);
                }

                return $item;
            });
        $currency = Currency::select('symbol')->where('is_default', 1)->where('status', 1)->first();
        $data['currency'] = "$";
        if (isset($currency)) {
            $data['currency'] = $currency->symbol;
        }
        $data['authuserid'] = $authUserId;
        $response['user'] = $data;
        return $response;
    }

    public function providerIndex(Request $request): array
    {
        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $authUserId = $request->provider_id;
        } else {
            if (Auth::id()) {
                $authUserId = Auth::id();
            }
        }
        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
        $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);
        $timeformatSetting = GlobalSetting::where('key', 'time_format_view')->first();
        $timeFormat = $timeformatSetting->value ?? '%H:%i';
        $sqlTimeFormat = $this->mapTimeFormatToSQL($timeFormat);
        $data['bookingdata'] = Bookings::select(
            'bookings.id',
            'bookings.order_id',
            'bookings.user_id',
            'bookings.tranaction',
            'bookings.total_amount',
            'bookings.amount_tax',
            'bookings.service_qty',
            'bookings.service_amount',
            'bookings.user_city',
            'bookings.booking_status',
            'bookings.payment_type',
            DB::raw("DATE_FORMAT(
                CASE 
                    WHEN bookings.booking_date IS NOT NULL THEN bookings.booking_date
                    ELSE bookings.created_at
                END, '{$sqlDateFormat}'
            ) AS bookingdate"),
            DB::raw("TIME_FORMAT(bookings.from_time, '{$sqlTimeFormat}') AS fromtime"),
            DB::raw("TIME_FORMAT(bookings.to_time, '{$sqlTimeFormat}') AS totime"),
            'products.source_name',
            'bookings.product_id',
            'users.name as user_name',
            'provider.name as provider_name',
            'users.email as email',
            'users.phone_number as phone_number',
            DB::raw("CONCAT(user_details.first_name, ' ', user_details.last_name) as user_full_name"),
            DB::raw("CONCAT(provider_details.first_name, ' ', provider_details.last_name) as provider_full_name"),
            DB::raw('(SELECT mobile_number
            FROM user_details WHERE user_details.user_id = users.id and user_details.deleted_at is NULL  LIMIT 1) as contact'),
            DB::raw('(SELECT  profile_image
            FROM user_details
            WHERE user_details.user_id = users.id and user_details.deleted_at is NULL  LIMIT 1) as profile_image'),
            DB::raw("CASE
                WHEN bookings.booking_status = 1 THEN 'Open'
                WHEN bookings.booking_status = 2 THEN 'In progress'
                WHEN bookings.booking_status = 3 THEN 'Cancelled'
                WHEN bookings.booking_status = 4 THEN 'Refund Initiated'
                WHEN bookings.booking_status = 5 THEN 'Completed'
                WHEN bookings.booking_status = 6 THEN 'Order Completed'
                WHEN bookings.booking_status = 7 THEN 'Refund Completed'
                WHEN bookings.booking_status = 8 THEN 'Customer Cancelled'
                ELSE 'Unknown'
            END AS booking_status_label
        "),
            DB::raw("
        CASE
            WHEN bookings.payment_type = 1 THEN 'Paypal'
            WHEN bookings.payment_type = 2 THEN 'Stripe'
            WHEN bookings.payment_type = 3 THEN 'Razorpay'
            WHEN bookings.payment_type = 4 THEN 'Bank Transfer'
            WHEN bookings.payment_type = 5 THEN 'COD'
            WHEN bookings.payment_type = 6 THEN 'Wallet'
            WHEN bookings.payment_type = 7 THEN 'Mollie'
            WHEN bookings.payment_type = 8 THEN 'PayU'
            WHEN bookings.payment_type = 9 THEN 'Cashfree'
            WHEN bookings.payment_type = 10 THEN 'Authorize.net'
            WHEN bookings.payment_type = 11 THEN 'Paystack'
            WHEN bookings.payment_type = 12 THEN 'Mercado Pago'
            ELSE 'Unpaid'
        END AS paymenttype")
        )
            ->leftJoin('products', 'products.id', '=', 'bookings.product_id')
            ->leftJoin('users', 'users.id', '=', 'bookings.user_id') // Join for the user who made the booking
            ->leftJoin('users as provider', 'provider.id', '=', 'products.created_by') // Join for the provider
            ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
            ->leftJoin('user_details as provider_details', 'provider_details.user_id', '=', 'provider.id')
            ->with([
                'user.userDetails', // Load user details from the user model
                'product.createdBy.userDetails', // Load provider's user details via product relationship
                'review.reply'
            ])->where('products.created_by', $authUserId)
            ->groupBy('bookings.order_id', 'bookings.id', 'provider_details.first_name', 'provider_details.last_name', 'user_details.first_name', 'user_details.last_name', 'bookings.tranaction', 'bookings.amount_tax', 'bookings.service_qty', 'bookings.service_amount', 'bookings.user_id', 'bookings.total_amount', 'products.id', 'users.id', 'users.email', 'users.phone_number', 'bookings.product_id', 'bookings.booking_date', 'bookings.from_time', 'bookings.to_time', 'bookings.booking_status', 'bookings.payment_type', 'bookings.user_id', 'bookings.user_city', 'bookings.created_at', 'products.source_name', 'users.name', 'provider.name')
            ->orderByDesc('bookings.created_at')
            ->paginate(10);

        $productIds = $data['bookingdata']->pluck('product_id')->unique()->filter()->values();

        $productImagesMap = ModelsProductmeta::whereIn('product_id', $productIds)
            ->where('source_key', 'product_image')
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('product_id');

        foreach ($data['bookingdata'] as $booking) {
            $images = $productImagesMap->get($booking->product_id, collect());

            $validImage = $images->first(function ($img) {
                return !empty($img->source_Values) && file_exists(public_path('storage/' . $img->source_Values));
            });

            $booking->productimage = $validImage
                ? url('storage/' . $validImage->source_Values)
                : url('front/img/default-placeholder-image.png');

            $booking->user_name = $booking->user_full_name ? ucwords($booking->user_full_name) : ucwords($booking->user_name);
            $booking->provider_name = $booking->provider_full_name ? ucwords($booking->provider_full_name) : ucwords($booking->provider_name);
        }

        $currency = Currency::select('symbol')->where('is_default', 1)->where('status', 1)->first();
        $data['currency'] = "$";
        $data['authuserid'] = $authUserId;
        if (isset($currency)) {
            $data['currency'] = $currency->symbol;
        }
        foreach ($data['bookingdata'] as $booking) {
            $couponLog = DB::table('coupon_logs')->where('booking_id', $booking->id)->first();
            $booking->coupon_code = $couponLog ? $couponLog->coupon_code : null;
        }
        return $data;
    }

    public function staffIndex(Request $request): array
    {
        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $authUserId = $request->provider_id;
        } else {
            if (Auth::id()) {
                $authUserId = Auth::id();
            } else {
                $authUserId = Cache::get('provider_auth_id');
            }
        }
        $productIds = Bookings::where('staff_id', $authUserId)
            ->pluck('product_id')
            ->toArray();
        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
        $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);
        $timeformatSetting = GlobalSetting::where('key', 'time_format_view')->first();
        $timeFormat = $timeformatSetting->value ?? '%H:%i';
        $sqlTimeFormat = $this->mapTimeFormatToSQL($timeFormat);
        $data['bookingdata'] = Bookings::select(
            'bookings.id',
            'bookings.order_id',
            'bookings.user_id',
            'bookings.tranaction',
            'bookings.total_amount',
            'bookings.amount_tax',
            'bookings.service_qty',
            'bookings.service_amount',
            'bookings.user_city',
            'bookings.booking_status',
            'bookings.payment_type',
            'bookings.product_id',
            DB::raw("DATE_FORMAT(
                CASE 
                    WHEN bookings.booking_date IS NOT NULL THEN bookings.booking_date
                    ELSE bookings.created_at
                END, '{$sqlDateFormat}'
            ) AS bookingdate"),
            DB::raw("TIME_FORMAT(bookings.from_time, '{$sqlTimeFormat}') AS fromtime"),
            DB::raw("TIME_FORMAT(bookings.to_time, '{$sqlTimeFormat}') AS totime"),
            'products.source_name',
            'users.name as user_name',
            'provider.name as provider_name',
            'users.email as email',
            DB::raw("CONCAT(user_details.first_name, ' ', user_details.last_name) as user_full_name"),
            DB::raw("CONCAT(provider_details.first_name, ' ', provider_details.last_name) as provider_full_name"),
            DB::raw('(SELECT mobile_number
            FROM user_details WHERE user_details.user_id = users.id and user_details.deleted_at is NULL  LIMIT 1) as contact'),
            DB::raw('(SELECT  profile_image
            FROM user_details
            WHERE user_details.user_id = users.id and user_details.deleted_at is NULL  LIMIT 1) as profile_image'),
            DB::raw("CASE
                WHEN bookings.booking_status = 1 THEN 'Open'
                WHEN bookings.booking_status = 2 THEN 'In progress'
                WHEN bookings.booking_status = 3 THEN 'Cancelled'
                WHEN bookings.booking_status = 4 THEN 'Refund Initiated'
                WHEN bookings.booking_status = 5 THEN 'Completed'
                WHEN bookings.booking_status = 6 THEN 'Order Completed'
                WHEN bookings.booking_status = 7 THEN 'Refund Completed'
                WHEN bookings.booking_status = 8 THEN 'Customer Cancelled'
                ELSE 'Unknown'
            END AS booking_status_label
        "),
            DB::raw("
        CASE
            WHEN bookings.payment_type = 1 THEN 'Paypal'
            WHEN bookings.payment_type = 2 THEN 'Stripe'
            WHEN bookings.payment_type = 3 THEN 'Razorpay'
            WHEN bookings.payment_type = 4 THEN 'Bank Transfer'
            WHEN bookings.payment_type = 5 THEN 'COD'
            WHEN bookings.payment_type = 6 THEN 'Wallet'
            WHEN bookings.payment_type = 7 THEN 'Mollie'
            WHEN bookings.payment_type = 8 THEN 'PayU'
            WHEN bookings.payment_type = 9 THEN 'Cashfree'
            WHEN bookings.payment_type = 10 THEN 'Authorize.net'
            WHEN bookings.payment_type = 11 THEN 'Paystack'
            WHEN bookings.payment_type = 12 THEN 'Mercado Pago'
            ELSE 'Unknown'
        END AS paymenttype")
        )
            ->where('staff_id', $authUserId)
            ->leftJoin('products', 'products.id', '=', 'bookings.product_id')
            ->leftJoin('users', 'users.id', '=', 'bookings.user_id') // Join for the user who made the booking
            ->leftJoin('users as provider', 'provider.id', '=', 'products.created_by') // Join for the provider
            ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
            ->leftJoin('user_details as provider_details', 'provider_details.user_id', '=', 'provider.id')
            ->with([
                'user.userDetails', // Load user details from the user model
                'product.createdBy.userDetails', // Load provider's user details via product relationship
            ])->whereIn('products.id', $productIds)
            ->groupBy('bookings.order_id', 'provider_details.first_name', 'provider_details.last_name', 'user_details.first_name', 'user_details.last_name', 'bookings.id', 'bookings.tranaction', 'bookings.amount_tax', 'bookings.service_qty', 'bookings.service_amount', 'bookings.user_id', 'bookings.total_amount', 'products.id', 'users.id', 'users.email', 'bookings.product_id', 'bookings.booking_date', 'bookings.from_time', 'bookings.to_time', 'bookings.booking_status', 'bookings.payment_type', 'bookings.user_id', 'bookings.user_city', 'bookings.created_at', 'products.source_name', 'users.name', 'provider.name')
            ->orderByDesc('bookings.created_at')
            ->paginate(10);

        $productIds = $data['bookingdata']->pluck('product_id')->unique()->filter()->values();

        $productImagesMap = ModelsProductmeta::whereIn('product_id', $productIds)
            ->where('source_key', 'product_image')
            ->whereNull('deleted_at')
            ->get()
            ->groupBy('product_id');

        foreach ($data['bookingdata'] as $booking) {
            $images = $productImagesMap->get($booking->product_id, collect());

            $validImage = $images->first(function ($img) {
                return !empty($img->source_Values) && file_exists(public_path('storage/' . $img->source_Values));
            });

            $booking->productimage = $validImage
                ? url('storage/' . $validImage->source_Values)
                : url('front/img/default-placeholder-image.png');

            $booking->user_name = $booking->user_full_name ? ucwords($booking->user_full_name) : ucwords($booking->user_name);
            $booking->provider_name = $booking->provider_full_name ? ucwords($booking->provider_full_name) : ucwords($booking->provider_name);
        }

        $currency = Currency::select('symbol')->where('is_default', 1)->where('status', 1)->first();
        $data['currency'] = "$";
        $data['authuserid'] = $authUserId;
        if (isset($currency)) {
            $data['currency'] = $currency->symbol;
        }

        return $data;
    }

    public function updateBookingStatus(Request $request): array
    {
        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            Bookings::where('id', '=', $request->booking_id)->update(['booking_status' => $request->status, 'updated_at' => Carbon::now()]);
        } else {
            Bookings::where('id', '=', $request['id'])->update(['booking_status' => $request['status'], 'updated_at' => Carbon::now()]);
        }
        
        $statusdata['status'] = $source = $status = $description = $sourceProvider = $sourceAdmin = $sourceStaff = '';
        if ($request['status'] == 2) {
            $statusdata['status'] = 'In progress';
            $status = 'Accepted';
            $source = 'Booking Accept';
            $sourceProvider = 'Booking Accept email to Provider';
            $sourceAdmin = 'Booking Accept email to Admin';
            $sourceStaff = 'Booking Accept email to Staff';
            $description = "Booking accepted";
        } else if ($request['status'] == 3) {
            $status = $statusdata['status'] = 'Cancelled';
            $source = 'Provider Booking Cancel';
            $sourceProvider = 'Provider Booking Cancel email to Provider';
            $sourceAdmin = 'Provider Booking Cancel email to Admin';
            $sourceStaff = 'Provider Booking Cancel email to Staff';
            $description = "Booking Cancelled";
        } else if ($request['status'] == 5) {
            $status = $statusdata['status'] = 'Completed';
            $source = 'Booking Completed';
            $sourceProvider = 'Booking Completed email to Provider';
            $sourceAdmin = 'Booking Completed email to Admin';
            $sourceStaff = 'Booking Completed email to Staff';
            $description = "Booking Completed";
        } else if ($request['status'] == 4) {
            $status = $statusdata['status'] = 'Refund Initiated';
            $source = 'Refund Initiation';
            $sourceAdmin = 'Refund Initiation email to Admin';
            $description = "Refund Initiated";
        } else if ($request['status'] == 6) {
            $status = $statusdata['status'] = 'Completed';
            $source = 'Order Completed';
            $sourceAdmin = 'Order Completed email to Admin';
            $sourceProvider = 'Order Completed email to Provider';
            $sourceStaff = 'Order Completed email to Staff';
            $description = "Order Completed";
        } else if ($request['status'] == 7) {
            $status = $statusdata['status'] = 'Refund Completed';
            $source = 'Refund Completed';
            $sourceAdmin = 'Refund Completed email to Admin';
            $description = "Refund Process Completed";
        } else if ($request['status'] == 8) {
            $status = $statusdata['status'] = 'Customer Cancelled';
            $source = 'Booking Cancel';
            $sourceAdmin = 'Customer Cancelled email to Admin';
            $sourceProvider = 'Customer Cancelled email to Provider';
            $sourceStaff = 'Customer Cancelled email to Staff';
            $description = "Cancelled Booking";
        }

        // Handle Google Calendar cancellation for cancelled bookings
        if (in_array($request['status'], [3, 8])) { // 3 = Provider Cancelled, 8 = Customer Cancelled
            try {
                $bookingId = request()->has('is_mobile') && request()->get('is_mobile') === "yes" 
                    ? $request->booking_id 
                    : $request['id'];

                $booking = Bookings::find($bookingId);
                if ($booking) {
                    $googleCalendarController = new GoogleCalendarSyncController();
                    $googleCalendarController->cancelGoogleCalendarEvent($booking);
                }
            } catch (\Throwable $e) {
                // Don't fail booking cancellation if calendar sync fails
                Log::error('Calendar cancellation error', [
                    'booking_id' => $bookingId ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        if ($request['status'] == 4) {
            $getbookings = Bookings::where('id', '=', $request['id'])->first();
            if (isset($getbookings)) {
                $authUserId = Auth::id();
                $data['user_id'] = $getbookings->user_id;
                $data['reference_id'] = $request['id'];
                $data['type'] = 2;
                $data['total_bookings'] = 1;
                $serviceamount = 0;
                if ($getbookings->total_amount != '') {
                    $serviceamount = $getbookings->total_amount;
                }
                $data['total_earnings'] = $data['pay_due'] = $serviceamount;
                $data['process_amount'] = $serviceamount;
                $data['remaining_amount'] = $serviceamount;
                $data['created_by'] = $getbookings->user_id;
                $data['created_at'] = Carbon::now();
                $savedata = PayoutHistory::insertGetId($data);
            }
        }
        
        $statusdata['status_code'] = $request['status'];
        
        // Get booking data
        $bookingdata = Bookings::select(
            'bookings.*',
            DB::raw("CASE
            WHEN bookings.booking_status = 1 THEN 'Open'
            WHEN bookings.booking_status = 2 THEN 'In progress'
            WHEN bookings.booking_status = 3 THEN 'Provider Cancelled'
            WHEN bookings.booking_status = 4 THEN 'Refund Initiated'
            WHEN bookings.booking_status = 5 THEN 'Completed'
            WHEN bookings.booking_status = 6 THEN 'Order Completed'
            WHEN bookings.booking_status = 7 THEN 'Refund Completed'
            WHEN bookings.booking_status = 8 THEN 'Customer Cancelled'
            ELSE 'Unknown'
        END AS booking_status_label"),
            DB::raw("
            CASE
                WHEN bookings.payment_type = 1 THEN 'Paypal'
                WHEN bookings.payment_type = 2 THEN 'Stripe'
                WHEN bookings.payment_type = 3 THEN 'Razorpay'
                WHEN bookings.payment_type = 4 THEN 'Bank Transfer'
                WHEN bookings.payment_type = 5 THEN 'COD'
                WHEN bookings.payment_type = 6 THEN 'Wallet'
                WHEN bookings.payment_type = 7 THEN 'Mollie'
                WHEN bookings.payment_type = 8 THEN 'PayU'
                WHEN bookings.payment_type = 9 THEN 'Cashfree'
                WHEN bookings.payment_type = 10 THEN 'Authorize.net'
                WHEN bookings.payment_type = 11 THEN 'Paystack'
                WHEN bookings.payment_type = 12 THEN 'Mercado Pago'
                ELSE 'Unknown'
            END AS paymenttype"),
            DB::raw("DATE_FORMAT(bookings.created_at, '%d-%m-%Y') AS bookingdate"),
            DB::raw("TIME_FORMAT(bookings.from_time, '%H:%i') AS fromtime"),
            DB::raw("TIME_FORMAT(bookings.to_time, '%H:%i') AS totime"),
            'products.source_name',
            'users.name as user_name',
            'provider.name as provider_name',
            'provider.email as provideremail',
            'user_details.first_name as user_first_name',
            'user_details.last_name as user_last_name',
            'provider_details.first_name as provider_first_name',
            'provider_details.last_name as provider_last_name',
            'staff.name as staff_name',
            'staff.email as staffemail',
            'staff_details.first_name as staff_first_name',
            'staff_details.last_name as staff_last_name',
            'payout_history.id as refundid',
            DB::raw("DATE_FORMAT(payout_history.created_at, '%d-%m-%Y') AS trxdate"),
            DB::raw("DATE_FORMAT(payout_history.updated_at, '%d-%m-%Y') AS refunddate"),
            'products.created_by'
        )
            ->leftJoin('products', 'products.id', '=', 'bookings.product_id')
            ->leftJoin('users', 'users.id', '=', 'bookings.user_id')
            ->leftJoin('user_details', 'user_details.user_id', '=', 'users.id')
            ->leftJoin('users as provider', 'provider.id', '=', 'products.created_by')
            ->leftJoin('user_details as provider_details', 'provider_details.user_id', '=', 'provider.id')
            ->leftJoin('users as staff', 'staff.id', '=', 'bookings.staff_id')
            ->leftJoin('user_details as staff_details', 'staff_details.user_id', '=', 'staff.id')
            ->leftJoin('payout_history', 'payout_history.reference_id', '=', 'bookings.id')
            ->where('bookings.id', $request['id'])
            ->with(['user.userDetails', 'product.createdBy.userDetails'])
            ->first();

        $settingData = getCommonSettingData(['company_name', 'site_email', 'phone_no', 'site_address', 'postal_code', 'website']);

        // Send email to customer
        $this->sendEmailToRecipient($source, $bookingdata, $settingData, $bookingdata->user_email ?? null);
        
        // Send email to admin if sourceAdmin is set
        if (isset($sourceAdmin)) {
            $admins = User::with('userDetails')->where('user_type', 1)->get();
            foreach ($admins as $admin) {
                $adminName = isset($admin->name) ? ucwords($admin->name) : 'Admin';
                if ($admin->userDetails) {
                    $firstName = $admin->userDetails->first_name ?? '';
                    $lastName = $admin->userDetails->last_name ?? '';
                    $adminName = trim(ucwords($firstName . ' ' . $lastName));
                }
                $this->sendEmailToRecipient($sourceAdmin, $bookingdata, $settingData, $admin->email, $adminName);
            }
        }
        
        // Send email to provider if sourceProvider is set and bookingdata has provider email
        if (isset($sourceProvider) && isset($bookingdata->provideremail)) {
            $this->sendEmailToRecipient($sourceProvider, $bookingdata, $settingData, $bookingdata->provideremail);
        }
        
        if (isset($bookingdata->staff_id) && $bookingdata->staff_id != 0 && $bookingdata->staff_id != '') {
            // Send email to staff if sourceStaff is set and bookingdata has staff email
            if (isset($sourceStaff) && isset($bookingdata->staffemail)) {
                $this->sendEmailToRecipient($sourceStaff, $bookingdata, $settingData, $bookingdata->staffemail);
            }
        }

        /*Notification*/
        $this->sendNotification($source, $bookingdata, $settingData, $request->updated_by ?? '');
        
        return ['code' => 200, 'message' => 'Booking ' . $status . ' Successfully', 'data' => $statusdata];
    }

    /**
     * Send email to a recipient
     */
    private function sendEmailToRecipient($source, $bookingdata, $settingData, $recipientEmail, $adminName = '')
    {
        if (!$recipientEmail) {
            return;
        }

        $gettemplate = Templates::select('templates.subject', 'templates.content')
            ->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')
            ->where('notification_types.type', $source)
            ->where('templates.type', 1)
            ->where('templates.status', 1)
            ->first();

        if (!$gettemplate || !$bookingdata) {
            return;
        }

        $service = $bookingdata->source_name;
        $fromtime = $bookingdata->fromtime ?? "";
        $totime = $bookingdata->totime ?? "";

        $userName = ucwords($bookingdata->user_first_name . ' ' . $bookingdata->user_last_name);
        $providerName = ucwords($bookingdata->provider_first_name . ' ' . $bookingdata->provider_last_name);
        $staffName = isset($bookingdata->staff_first_name) ? ucwords($bookingdata->staff_first_name . ' ' . $bookingdata->staff_last_name) : '';

        $tempdata = [
            '{{customer_name}}' => $userName,
            '{{user_name}}' => $userName,
            '{{admin_name}}' => $adminName,
            '{{booking_id}}' => $bookingdata->order_id,
            '{{service_name}}' => $bookingdata->source_name,
            '{{appointment_date}}' => $bookingdata->bookingdate,
            '{{appointment_time}}' => $fromtime ? $fromtime . '-' . $totime : '',
            '{{provider_name}}' => $providerName,
            '{{staff_name}}' => $staffName,
            '{{contact}}' => $bookingdata->provideremail,
            '{{website_link}}' => $bookingdata['product']['createdBy']['userDetails']->company_website ?? $settingData['website'] ?? "",
            '{{refund_id}}' => $bookingdata->refundid,
            '{{refund_amount}}' => $bookingdata->total_amount,
            '{{transaction_date}}' => $bookingdata->trxdate,
            '{{company_name}}' => $bookingdata['product']['createdBy']['userDetails']->company_name ?? $settingData['company_name'] ?? "",
            '{{refund_date}}' => $bookingdata->refunddate,
            '{{payment_method}}' => $bookingdata->paymenttype,
            '{{service_address}}' => $bookingdata['product']['createdBy']['userDetails']->company_address ?? $bookingdata['product']['createdBy']['userDetails']->address ?? $settingData['site_address'] ?? "",
        ];

        $finalContent = Str::replace(array_keys($tempdata), array_values($tempdata), $gettemplate->content);
        $subject = Str::replace(array_keys($tempdata), array_values($tempdata), $gettemplate->subject);

        $data = [
            'to_email' => $recipientEmail,
            'subject' => $subject,
            'content' => $finalContent
        ];

        try {
            $emailController = app(EmailController::class);
            $emailController->sendEmail(new Request($data));
        } catch (\Exception $e) {
            Log::error('Email sending failed to ' . $recipientEmail . ': ' . $e->getMessage());
        }
    }

    /**
     * Send notification
     */
    private function sendNotification($source, $bookingdata, $settingData, $updatedBy = '')
    {
        $receipientType = 2;
        if (($source == 'Provider Booking Cancel' || $source == 'Booking Accept' || $source == 'Booking Completed') && $updatedBy == 'staff') {
            $receipientType = 4;
        }
        $gettemplate = Templates::select('templates.subject', 'templates.content')
            ->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')
            ->where('notification_types.type', $source)
            ->where('recipient_type', $receipientType)
            ->where('templates.type', 3)
            ->where('templates.status', 1)
            ->first();

        if ($gettemplate && $bookingdata) {

            $fromUserId = $bookingdata->user_id;
            $toUserId = $bookingdata->created_by;

            if ($source == 'Provider Booking Cancel' || $source == 'Booking Accept' || $source == 'Booking Completed') {
                $fromUserId = $updatedBy == 'staff' ? $bookingdata->staff_id : $bookingdata->created_by;
                $toUserId = $bookingdata->user_id;
            }

            $fromtime = $bookingdata->fromtime ?? "";
            $totime = $bookingdata->totime ?? "";

            $userName = ucwords($bookingdata->user_first_name . ' ' . $bookingdata->user_last_name);
            $providerName = ucwords($bookingdata->provider_first_name . ' ' . $bookingdata->provider_last_name);
            $staffName = isset($bookingdata->staff_first_name) ? ucwords($bookingdata->staff_first_name . ' ' . $bookingdata->staff_last_name) : '';

            $tempdata = [
                '{{user_name}}' => $userName,
                '{{customer_name}}' => $userName,
                '{{booking_id}}' => $bookingdata->order_id,
                '{{service_name}}' => $bookingdata->source_name,
                '{{appointment_date}}' => $bookingdata->bookingdate,
                '{{appointment_time}}' => $fromtime ? $fromtime . '-' . $totime : '',
                '{{provider_name}}' => $providerName,
                '{{staff_name}}' => $staffName,
                '{{contact}}' => $bookingdata->provideremail,
                '{{website_link}}' => $bookingdata['product']['createdBy']['userDetails']->company_website ?? $settingData['website'] ?? "",
                '{{refund_id}}' => $bookingdata->refundid,
                '{{refund_amount}}' => $bookingdata->total_amount,
                '{{transaction_date}}' => $bookingdata->trxdate,
                '{{company_name}}' => $bookingdata['product']['createdBy']['userDetails']->company_name ?? $settingData['company_name'] ?? "",
                '{{refund_date}}' => $bookingdata->refunddate,
                '{{payment_method}}' => $bookingdata->paymenttype,
                '{{service_address}}' => $bookingdata['product']['createdBy']['userDetails']->company_address ?? $bookingdata['product']['createdBy']['userDetails']->address ?? $settingData['site_address'] ?? "",
            ];

            $todescription = Str::replace(array_keys($tempdata), array_values($tempdata), $gettemplate->content);

            $getfromtemplate = Templates::select('templates.subject', 'templates.content')
                ->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')
                ->where('notification_types.type', $source)
                ->where('recipient_type', 1)
                ->where('templates.type', 3)
                ->where('templates.status', 1)
                ->first();

            $fromdescription = "";
            if ($getfromtemplate) {
                $fromdescription = Str::replace(array_keys($tempdata), array_values($tempdata), $getfromtemplate->content);
            }

            // Default data for recipient types 1 and 2
            $data = [
                'communication_type' => '3',
                'source' => $source,
                'reference_id' => $bookingdata->id,
                'user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'to_description' => $todescription,
                'from_description' => $fromdescription,
            ];

            try {
                $notification = new NotificationController();
                $notification->Storenotification(new Request($data));
            } catch (\Exception $e) {
                Log::error('Notification send failed: ' . $e->getMessage());
            }

            if (isset($bookingdata->staff_id) && $bookingdata->staff_id != 0 && $bookingdata->staff_id != '' && $updatedBy != 'staff') {
                $staffData = [
                    'communication_type' => '3',
                    'source' => $source,
                    'reference_id' => $bookingdata->id,
                    'user_id' => $bookingdata->user_id,
                    'to_user_id' => $bookingdata->staff_id,
                    'from_description' => '',
                    'to_description' => $todescription,
                ];
                
                try {
                    $notification->Storenotification(new Request($staffData));
                } catch (\Exception $e) {
                    Log::error('Notification send failed for staff: ' . $e->getMessage());
                }
            }
        }

        $admins = User::where('user_type', 1)->get();
        
        $getrecipient3template = Templates::select('templates.subject', 'templates.content')
            ->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')
            ->where('notification_types.type', $source)
            ->where('recipient_type', 3)
            ->where('templates.type', 3)
            ->where('templates.status', 1)
            ->first();

        $recipient3description = null;
        if ($getrecipient3template) {
            $recipient3description = Str::replace(array_keys($tempdata), array_values($tempdata), $getrecipient3template->content);

            try {
                $notification = new NotificationController();
                
                $notification->Storenotification(new Request($data));

                if ($getrecipient3template) {
                    foreach ($admins as $admin) {
                        $adminData = [
                            'communication_type' => '3',
                            'source' => $source,
                            'reference_id' => $bookingdata->id,
                            'user_id' => $bookingdata->user_id,
                            'to_user_id' => $admin->id,
                            'to_description' => $recipient3description,
                            'from_description' => "",
                        ];
                        
                        $notification->Storenotification(new Request($adminData));
                    }
                }
            } catch (\Exception $e) {
                Log::error('Notification send failed: ' . $e->getMessage());
            }
        }
    }

    public function getBookings(Request $request): JsonResponse
    {
        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
        $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);
        $productImageSub = DB::table('products_meta')
                        ->select('product_id', DB::raw('MAX(source_values) as source_values'))
                        ->where('source_key', 'product_image')
                        ->groupBy('product_id');

        $bookings = Bookings::select(
            'bookings.*',
            'bookings.created_at as bookingdate',
            'products.source_name',
            'users.name as user_name',
            DB::raw("
                CASE
                    WHEN bookings.booking_status = 1 THEN 'Open'
                    WHEN bookings.booking_status = 2 THEN 'In progress'
                    WHEN bookings.booking_status = 3 THEN 'Provider Cancelled'
                    WHEN bookings.booking_status = 4 THEN 'Refund Initiated'
                    WHEN bookings.booking_status = 5 THEN 'Completed'
                    WHEN bookings.booking_status = 6 THEN 'Order Completed'
                    WHEN bookings.booking_status = 7 THEN 'Refund Completed'
                    WHEN bookings.booking_status = 8 THEN 'Customer Cancelled'
                    ELSE 'Unknown'
                END AS booking_status_label
            "),
            'product_image_table.source_values as productimage'
        )
        ->leftJoin('products', 'products.id', '=', 'bookings.product_id')
        ->leftJoinSub($productImageSub, 'product_image_table', function ($join) {
            $join->on('product_image_table.product_id', '=', 'bookings.product_id');
        })
        ->leftJoin('users', 'users.id', '=', 'bookings.user_id')
        ->with([
            'user.userDetails',
            'product.createdBy.userDetails',
        ])
        ->whereNull('bookings.deleted_at')
        ->get()->map(function ($booking) {
            $currency = Currency::select('symbol')->where('is_default', 1)->where('status', 1)->first();
            $data['currency'] = "$";
            if (isset($currency)) {
                $data['currency'] = $currency->symbol;
            }
            $color = '#FF008A';
            if ($booking->booking_status == 1) {
                $color = '#FF008A';
            } else  if ($booking->booking_status == 2) {
                $color = '#5625E8';
            } else  if ($booking->booking_status == 3 || $booking->booking_status == 8) {
                $color = '#E70D0D';
            } else  if ($booking->booking_status == 5 || $booking->booking_status == 6 || $booking->booking_status == 7) {
                $color = '#03C95A';
            } elseif ($booking->booking_status == 4) {
                $color = '#eab300';
            }
            return [
                'title' => $booking->source_name,  // Replace with your booking field
                'start' => $booking->bookingdate, // Replace with your booking field
                'end' => $booking->bookingdate,    // Optional: adjust as needed
                'provider' => $booking['product']['createdBy']['userDetails']->first_name ?? "",
                'amount' =>  $data['currency'] . $booking->total_amount,
                'location' => $booking->user_address,
                'user' => $booking['user']['userDetails']->first_name ?? "",
                'status' => $booking->booking_status_label,
                'color' => $color,
            ];
        });

        return response()->json($bookings);
    }

    function mapDateFormatToSQL($phpFormat)
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

    protected function mapTimeFormatToSQL($timeFormat)
    {
        $map = [
            'hh:mm A' => '%h:%i %p', // 12-hour format with AM/PM
            'hh:mm a' => '%h:%i %p', // Same as above
            'HH:mm'   => '%H:%i',    // 24-hour format
        ];

        return $map[$timeFormat] ?? '%H:%i'; // Default to 24-hour format
    }

    public function getBookinglists(Request $request): array
    {
        $currency = Currency::select('symbol')->where('is_default', 1)->where('status', 1)->first();
        $data['currency'] = "$";
        if (isset($currency)) {
            $data['currency'] = $currency->symbol;
        }
        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
        $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);
        $bookingdatas = Bookings::select(
            'bookings.id',
            'bookings.order_id',
            'bookings.user_city',
            'bookings.product_id',
            'bookings.user_id',
            'bookings.created_at',
            DB::raw("FORMAT(bookings.service_amount, 2) AS serviceamount"),
            DB::raw("DATE_FORMAT(bookings.created_at, '{$sqlDateFormat}') AS bookingdate"),
            DB::raw("DATE_FORMAT(bookings.from_time, '%H:%i') AS fromtime"),
            DB::raw("DATE_FORMAT(bookings.to_time, '%H:%i') AS totime"),
            'products.source_name',
            'bookings.booking_status',
            'users.name as user_name',
            'provider.name as provider_name',
            DB::raw("
            CASE
                WHEN bookings.booking_status = 1 THEN 'Open'
                WHEN bookings.booking_status = 2 THEN 'In progress'
                WHEN bookings.booking_status = 3 THEN 'Provider Cancelled'
                WHEN bookings.booking_status = 4 THEN 'Refund Initiated'
                WHEN bookings.booking_status = 5 THEN 'Completed'
                WHEN bookings.booking_status = 6 THEN 'Order Completed'
                WHEN bookings.booking_status = 7 THEN 'Refund Completed'
                WHEN bookings.booking_status = 8 THEN 'Customer Cancelled'
                ELSE 'Unknown'
            END AS booking_status_label
        ")
        )
            ->leftJoin('products', 'products.id', '=', 'bookings.product_id')
            ->leftJoin('users', 'users.id', '=', 'bookings.user_id') // Join for the user who made the booking
            ->leftJoin('users as provider', 'provider.id', '=', 'products.created_by') // Join for the provider
            ->with([
                'user.userDetails', // Load user details from the user model
                'product.createdBy.userDetails', // Load provider's user details via product relationship
            ])
            ->orderByDesc('bookings.created_at');
        if (isset($request['type']) && $request['type'] != 'all-booking') {
            $type = "";
            if ($request['type'] == 'pending') {
                $type = 1;
            } elseif ($request['type'] == 'inprogress') {
                $type = 2;
            } elseif ($request['type'] == 'cancelled') {
                $type = 3;
            } elseif ($request['type'] == 'completed') {
                $type = 5;
            } elseif ($request['type'] == 'order-completed') {
                $type = 6;
            } elseif ($request['type'] == 'refund-completed') {
                $type = 7;
            } elseif ($request['type'] == 'customer-cancelled') {
                $type = 8;
            }
            $bookingdata = $bookingdatas->where('booking_status', $type);
        }
        $data['bookingdata'] = $bookingdatas->get();

        return ['code' => 200, 'message' => 'Data Fetched Successfully', 'data' => $data];
    }

    public function indexRequest(Request $request): array
    {
         /** @var string $orderBy */
        $orderBy = $request->input('order_by', 'desc');
        /** @var string $orderBy */
        $sortBy = $request->input('sort_by', 'id');
        /** @var \App\Models\Dispute $disputes */
        // Eager load related data
        $disputes = Dispute::with([
            'user:id,name,email',
            'provider:id,name,email',
            'product:id,source_name',
            'booking:id,order_id'
        ])
        ->whereHas('booking')
        ->whereHas('user')
        ->whereHas('provider')
        ->whereHas('product')
        ->orderBy($sortBy, $orderBy)
        ->get();

        if (empty($disputes)) {
            return ['code' => 200, 'message' => 'Dispute Not Found!', 'data' => []];
        }

        $data = $disputes->map(function ($dispute) {
            /** @var \App\Models\Dispute $dispute */
            return [
                'id' => $dispute->id,
                'user' => [
                    'id' => is_object($dispute->user) ? $dispute->user->id : null,
                    'name' => is_object($dispute->user) ? $dispute->user->name : null,
                    'email' => is_object($dispute->user) ? $dispute->user->email : null,
                ],
                'provider' => [
                    'id' => is_object($dispute->provider) ? $dispute->provider->id : null,
                    'name' => is_object($dispute->provider) ? $dispute->provider->name : null,
                    'email' => is_object($dispute->provider) ? $dispute->provider->email : null,
                ],
                'booking_id' => is_object($dispute->booking) ? $dispute->booking->id : null,
                'order_id' => is_object($dispute->booking) ? $dispute->booking->order_id : null,
                'product' => [
                    'id' => is_object($dispute->product) ? $dispute->product->id : null,
                    'source_name' => is_object($dispute->product) ? $dispute->product->source_name : null,
                ],
                'subject' => $dispute->subject,
                'content' => $dispute->content,
                'admin_reply' => $dispute->admin_reply,
                'status' => $dispute->status,
            ];
        });

        return [
            'code' => 200,
            'message' => __('Request dispute details retrieved successfully.'),
            'data' => $data
        ];
    }

    public function requestDispute(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $rules = [
            'subject' => 'required',
            'content' => 'required',
        ];

        $messages = [];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $data = [
                'user_id' => $userId,
                'product_id' => $request->product_id,
                'provider_id' => $request->provider_id,
                'subject' => $request->subject,
                'content' => $request->content,
                'status' => 1,
            ];

            $dispute = Dispute::updateOrCreate(
                ['booking_id' => $request->booking_id, 'user_id' => $userId],
                $data
            );

            return response()->json([
                'code' => 200,
                'message' => __('Dispute submitted successfully'),
                'data' => $dispute
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong while saving!'], 500);
        }
    }

    public function requestDisputeApi(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $rules = [
            'subject' => 'required',
            'content' => 'required',
        ];

        $messages = [];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $data = [
                'user_id' => $request->user_id,
                'product_id' => $request->product_id,
                'provider_id' => $request->provider_id,
                'subject' => $request->subject,
                'content' => $request->content,
                'status' => 1,
            ];

            $dispute = Dispute::updateOrCreate(
                ['booking_id' => $request->booking_id, 'user_id' => $userId],
                $data
            );

            return response()->json([
                'code' => 200,
                'message' => __('Dispute submitted successfully'),
                'data' => []
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong while saving!'], 500);
        }
    }

    public function UpdateRequest(Request $request): JsonResponse
    {
        $rules = [
            'edit_reply' => 'required',
        ];

        $messages = [
            'edit_reply.required' => 'The reply comment field is required.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $id = $request->edit_id;

        $data = [
            'admin_reply' => $request->edit_reply,
        ];

        $update = Dispute::where('id', $id)->update($data);

        if (!$update) {
            return response()->json(['message' => 'Something went wrong while saving!'], 500);
        }

        return response()->json(['code' => 200, 'message' => __('dispute_setting_update'), 'data' => []], 200);
    }

    public function getDisputeDetails(Request $request): array
    {
        $dispute = Dispute::where('booking_id', $request->booking_id)
            ->where('product_id', $request->product_id)
            ->where('provider_id', $request->provider_id)
            ->first();

        if ($dispute) {
            return [
                'exists' => true,
                'admin_reply' => $dispute->admin_reply,
                'subject' => $dispute->subject,
                'content' => $dispute->content,
                'status' => $dispute->status,
            ];
        }

        return ['exists' => false];
    }

    public function getDisputeDetailsApi(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|integer',
            'product_id' => 'required|integer',
            'provider_id' => 'required|integer',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'errors' => $validator->errors()->toArray(),
                'data' => []
            ], 422);
        }
    
        $dispute = Dispute::where('booking_id', $request->booking_id)
            ->where('product_id', $request->product_id)
            ->where('provider_id', $request->provider_id)
            ->first();
    
        if ($dispute) {
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'data' => [
                    'admin_reply' => $dispute->admin_reply,
                    'subject' => $dispute->subject,
                    'content' => $dispute->content,
                    'status' => $dispute->status,
                ]
            ]);
        }
    
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => []
        ]);
    }
    
    public function getDisputeInfo(Request $request): JsonResponse
    {
        $bookingId = $request->input('booking_id');

        $booking = Bookings::find($bookingId);

        if (!$booking) {
            return response()->json(['exists' => false]);
        }

        $productId = $booking->product_id;

        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['exists' => false]);
        }
        $providerId = $product->createdBy->id ?? null;

        return response()->json([
            'exists' => true,
            'booking_id' => $bookingId,
            'product_id' => $productId,
            'provider_id' => $providerId,
        ]);
    }

    public function getBookingDetails(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:bookings,id',
        ]);

        // Retrieve the booking details
        $booking = Bookings::with('review.reply')
            ->select(
                'id',
                'order_id',
                'product_id',
                'user_id',
                'branch_id',
                'staff_id',
                'slot_id',
                'additional_services',
                'service_amount',
                'total_amount',
                'first_name',
                'last_name',
                'user_email',
                'user_phone',
                'user_city',
                'user_state',
                'user_address',
                'payment_type',
                'payment_status',
                'booking_status',
                'booking_date',
                'notes'
            )
            ->where('id', $request->id)->first();

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        if ($booking->review) {
            $booking->review->review_date = formatDateTime($booking->review->review_date, true);
        }

        if ($booking->review && $booking->review->reply) {
            $booking->review->reply->review_date = formatDateTime($booking->review->reply->review_date, true);
        }

        $dateFormat = GlobalSetting::where('group_id', 31)
            ->where('key', 'date_format_view')
            ->pluck('value', 'key')
            ->first();

        if (!$dateFormat) {
            $dateFormat = 'd-m-Y';
        }

        $booking->formatted_booking_date = date($dateFormat, strtotime($booking->booking_date));

        $bookingStatusMap = [
            1 => 'Open',
            2 => 'Accepted',
            3 => 'Cancelled',
            4 => 'Refund Initiated',
            5 => 'Completed',
            6 => 'Order Completed',
            7 => 'Refund Completed',
            8 => 'User Cancelled',
        ];

        $paymentTypeMap = [
            1 => 'PayPal',
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

        $booking->booking_status = $bookingStatusMap[$booking->booking_status] ?? 'Unknown';
        $booking->payment_type = $paymentTypeMap[$booking->payment_type] ?? 'Unknown';
        $booking->payment_status = $paymentStatusMap[$booking->payment_status] ?? 'Unknown';

        $service = Service::select('source_name', 'source_code', 'user_id')
            ->where('id', $booking->product_id)
            ->first();

        $provider = User::select('name', 'email', 'phone_number')
            ->where('id', $service->user_id)
            ->first();

        $providerDetails = UserDetail::select('first_name', 'last_name', 'mobile_number')
            ->where('user_id', $service->user_id)
            ->first();

        $staff = User::select('name', 'email', 'phone_number')
            ->where('id', $booking->staff_id)
            ->first();

        $staffDetails = UserDetail::select('first_name', 'last_name', 'mobile_number')
            ->where('user_id', $booking->staff_id)
            ->first();

        $branch = Branches::select('branch_name', 'branch_mobile', 'branch_email', 'branch_address')
            ->where('id', $booking->branch_id)
            ->first();

        $slot = Productmeta::select('source_key', 'source_Values')
            ->where('id', $booking->slot_id)
            ->first();

        $formattedSlotKey = null;
        if ($slot && isset($slot->source_key)) {
            $slotParts = explode('_', $slot->source_key);
            $formattedSlotKey = ucfirst($slotParts[0]); // Only capitalize the day part
        }

        $additionalServices = [];
        if ($booking->additional_services) {
            $additionalServices = json_decode($booking->additional_services);
        }

        $response = [
            'booking' => $booking,
            'formatted_booking_date' => $booking->formatted_booking_date,
            'slot' => [
                'formatted_source_key' => $formattedSlotKey,
                'source_values' => $slot->source_Values ?? null,
            ],
            'service' => $service,
            'provider' => $provider,
            'provider_details' => $providerDetails,
            'staff' => $staff,
            'staff_details' => $staffDetails,
            'branch' => $branch,
            'status' => [
                'booking_status' => $booking->booking_status,
                'payment_type' => $booking->payment_type,
                'payment_status' => $booking->payment_status,
            ],
            'additional_services' => $additionalServices,
            'currency' => getDefaultCurrencySymbol()
        ];

        return response()->json($response);
    }

    public function WalletCheck(Request $request): array
    {
        $userId = $request->user_id;
        $totalAmount = $request->total_amount;

        // Calculate total credited and debited amounts
        $totalCredit = WalletHistory::where('user_id', $userId)
            ->where('status', 'completed')
            ->where('type', '1') // Credit type
            ->sum('amount');

        $totalDebit = WalletHistory::where('user_id', $userId)
            ->where('status', 'completed')
            ->where('type', '2') // Debit type
            ->sum('amount');

        // Calculate wallet balance
        $walletTotalAmount = $totalCredit - $totalDebit;

        // Check if the wallet balance is enough
        if ($walletTotalAmount < $totalAmount) {
            return [
                'code' => 422,
                'status' => false,
                'message' => 'Insufficient balance in wallet!',
                'wallet_balance' => $walletTotalAmount,
                'data' => []
            ];
        }

        // If wallet has sufficient balance
        return [
            'code' => 200,
            'status' => true,
            'message' => 'Sufficient balance in wallet!',
            'wallet_balance' => $walletTotalAmount,
            "data" => []
        ];
    }
}