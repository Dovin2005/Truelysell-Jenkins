<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Modules\GlobalSetting\app\Models\Currency;
use App\Models\Bookings;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use App\Models\PackageTrx;
use Modules\GlobalSetting\app\Models\Language;
use Illuminate\Support\Facades\Cookie;
use App\Models\BranchStaffs;
use App\Models\Branches;
use App\Models\PayoutHistory;
use App\Models\ServiceBranch;
use App\Models\ServiceStaff;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Models\Productmeta;
use Modules\Service\app\Models\Service;
use App\Repositories\Contracts\ProviderRepositoryInterface;

class ProviderRepository implements ProviderRepositoryInterface
{
    /* get provider and user details*/
    public function index()
    {
        $currentRouteName = Route::currentRouteName();
        if ($currentRouteName == 'admin.providerslist') {
            $title = 'Providers';
        } else {
            $title = 'Users';
        }
        return [
            'title' => $title,
        ];
    }

    public function getsubscription(Request $request)
    {
        try {
            $authUserId = $request->provider_id;

            $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
            $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
            $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);

            $standardPlan = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
                ->where('package_transactions.status', 1)
                ->where('package_transactions.provider_id', $authUserId)
                ->where('subscription_packages.subscription_type', 'regular')
                ->select(
                    'subscription_packages.package_title',
                    'subscription_packages.package_term',
                    'subscription_packages.package_duration',
                    'subscription_packages.price',
                    DB::raw("DATE_FORMAT(package_transactions.updated_at, '{$sqlDateFormat}') AS payment_date"),
                    DB::raw("DATE_FORMAT(package_transactions.end_date, '{$sqlDateFormat}') AS end_date"),
                    DB::raw("
                    CASE
                        WHEN subscription_packages.package_term = 'day' THEN
                            DATE_FORMAT(DATE_ADD(package_transactions.updated_at, INTERVAL subscription_packages.package_duration DAY), '{$sqlDateFormat}')
                        WHEN subscription_packages.package_term = 'week' THEN
                            DATE_FORMAT(DATE_ADD(package_transactions.updated_at, INTERVAL subscription_packages.package_duration WEEK), '{$sqlDateFormat}')
                        WHEN subscription_packages.package_term = 'month' THEN
                            DATE_FORMAT(DATE_ADD(package_transactions.updated_at, INTERVAL subscription_packages.package_duration MONTH), '{$sqlDateFormat}')
                        WHEN subscription_packages.package_term = 'yearly' THEN
                            DATE_FORMAT(DATE_ADD(package_transactions.updated_at, INTERVAL subscription_packages.package_duration YEAR), '{$sqlDateFormat}')
                        ELSE NULL
                    END AS next_payment_date
                ")
                )
                ->orderByDesc('package_transactions.id')
                ->first();

            // Get currency symbol (from cache or fallback)
            $currency = Cache::remember('currency_details', 86400, function () {
                return Currency::select('symbol')->where('is_default', 1)->where('status', 1)->orderByDesc('id')->first();
            });

            return [
                'code' => 200,
                'message' => __('Detail retrieved successfully.'),
                'data' => [
                    'standardplan' => [
                        'price' => $standardPlan->price ?? null,
                        'package_title' => $standardPlan->package_title ?? null,
                        'package_term' => $standardPlan->package_term ?? null,
                        'package_duration' => $standardPlan->package_duration ?? null,
                        'next_payment_date' => $standardPlan->next_payment_date ?? null,
                        'end_date' => $standardPlan->end_date ?? null,
                        'payment_date' => $standardPlan->payment_date ?? null,
                    ],
                    'currency' => $currency->symbol ?? '$',
                ]
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Error! while getting details'),
                'error' => $e->getMessage()
            ];
        }
    }

    public function gettotalbookingcount(Request $request)
    {
        try {
            $authId = $request->provider_id;

            $data['totalcount'] = DB::table('users')->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw("CONCAT(user_details.first_name, ' ', user_details.last_name) AS full_name"),
                DB::raw("COUNT(CASE WHEN v1.booking_status IN ('1', '2') THEN 1 END) AS upcoming_count"),
                DB::raw("COUNT(CASE WHEN v1.booking_status IN ('3', '8') THEN 1 END) AS cancelled_count"),
                DB::raw("COUNT(CASE WHEN v1.booking_status = '5' THEN 1 END) AS completed_count"),
                DB::raw("COUNT(CASE WHEN v1.booking_status = '6' THEN 1 END) AS order_completed_count"),
                DB::raw("SUM(CASE WHEN booking_status = 6 THEN total_amount ELSE 0 END) AS completed_total_amount"),
                DB::raw("SUM(CASE WHEN booking_status IN (6) THEN total_amount ELSE 0 END) AS overall_total_amount"),
                DB::raw("(SELECT SUM(payout_history.process_amount)
                  FROM payout_history
                  WHERE payout_history.user_id = users.id
                  AND payout_history.deleted_at IS NULL) AS processed_amount"),
                DB::raw("SUM(CASE WHEN v1.booking_status = 6 THEN v1.total_amount ELSE 0 END) -
                  (SELECT SUM(payout_history.process_amount)
                   FROM payout_history
                   WHERE payout_history.user_id = users.id
                   AND payout_history.deleted_at IS NULL) AS remaining_amount")
            )->leftJoin('user_details', 'users.id', '=', 'user_details.user_id')
                ->leftJoin(DB::raw('(
                SELECT
                    products.created_by,
                    bookings.booking_status,bookings.total_amount
                FROM products
                LEFT JOIN bookings ON bookings.product_id = products.id
                WHERE products.deleted_at IS NULL
            ) as v1'), 'users.id', '=', 'v1.created_by')->where('users.user_type', 2)->where('users.id', $authId)->whereNull('users.deleted_at')
                ->groupBy('users.id', 'users.name', 'users.email', 'user_details.first_name', 'user_details.last_name')->first();
            $currency = Cache::remember('currecy_details', 86400, function () {
                return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
            });
            $data['currency'] = "$";
            if (isset($currency)) {
                $data['currency'] = $currency->symbol;
            }

            if (isset($data['totalcount'])) {
                $data['totalcount'] = (array)$data['totalcount'];
                $data['totalcount']['processed_amount'] = $this->calculateProviderBalance($authId, 'total_earnings');
                $data['totalcount']['due_amount'] = $this->calculateProviderBalance($authId);
                $data['totalcount']['overall_total_amount'] = number_format($data['totalcount']['overall_total_amount'], 2, '.', '');
            }

            return [
                'code' => 200,
                'message' => __('Provider Count detail retrieved successfully.'),
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Error! while getting user details'),
                'error' => $e->getMessage()
            ];
        }
    }

    function calculateProviderBalance(int $providerId, string $type = ''): string
    {
        $commissionRate = 0;
        $commissionSetting = GlobalSetting::where('key', 'commission_rate_percentage')->first();
        if ($commissionSetting) {
            $commissionRate = (float) $commissionSetting->value;
        }

        $transactionsQuery = Bookings::with(['product'])
            ->where('booking_status', 6)
            ->whereHas('product', function ($query) use ($providerId) {
                $query->where('created_by', $providerId);
            });

        if ($type == '') {
            $transactionsQuery->where('payment_type', '!=', 5);
        }

        $transactions = $transactionsQuery->get();

        $totalGrossAmount = 0;
        $totalCommission = 0;
        $totalReducedAmount = 0;
        $remainingAmount = 0;

        if ($transactions) {
            foreach ($transactions as $booking) {
                $grossAmount = ($booking->total_amount ?? 0) - ($booking->amount_tax ?? 0);

                $commissionAmount = 0;
                if ($booking->payment_type == 5) {
                    $commissionAmount = 0;
                } else {
                    if ($commissionRate > 0) {
                        $commissionAmount = ($grossAmount * $commissionRate) / 100;
                    }
                }
                
                $reducedAmount = $grossAmount - $commissionAmount;

                $totalGrossAmount += $grossAmount;
                $totalCommission += $commissionAmount;
                $totalReducedAmount += $reducedAmount;
            }

            $enteredAmount = PayoutHistory::where('user_id', $providerId)->sum('process_amount');
            $remainingAmount = $totalReducedAmount - $enteredAmount;
        }

        if ($type == 'total_earnings') {
            return number_format($totalReducedAmount, 2, '.', '');
        }

        return number_format($remainingAmount, 2, '.', '');
    }

    public function gettotalbookingcountapi(Request $request)
    {
        try {
            if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
                $authId = $request->provider_id;
            }
            $user = Auth::user();

            $authId = $user->id;
            $data['totalcount'] = DB::table('users')->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw("CONCAT(user_details.first_name, ' ', user_details.last_name) AS full_name"),
                DB::raw("COUNT(CASE WHEN v1.booking_status IN ('1', '2') THEN 1 END) AS upcoming_count"),
                DB::raw("COUNT(CASE WHEN v1.booking_status ('3', '8') THEN 1 END) AS cancelled_count"),
                DB::raw("COUNT(CASE WHEN v1.booking_status = '5' THEN 1 END) AS completed_count"),
                DB::raw("COUNT(CASE WHEN v1.booking_status = '6' THEN 1 END) AS order_completed_count"),
                DB::raw("SUM(CASE WHEN booking_status = 6 THEN total_amount ELSE 0 END) AS completed_total_amount"),
                DB::raw("SUM(CASE WHEN booking_status IN (6) THEN total_amount ELSE 0 END) AS overall_total_amount"),
                DB::raw("(SELECT SUM(payout_history.process_amount)
                  FROM payout_history
                  WHERE payout_history.user_id = users.id
                  AND payout_history.deleted_at IS NULL) AS processed_amount"),
                DB::raw("SUM(CASE WHEN v1.booking_status = 6 THEN v1.total_amount ELSE 0 END) -
                  (SELECT SUM(payout_history.process_amount)
                   FROM payout_history
                   WHERE payout_history.user_id = users.id
                   AND payout_history.deleted_at IS NULL) AS remaining_amount")
            )->leftJoin('user_details', 'users.id', '=', 'user_details.user_id')
                ->leftJoin(DB::raw('(
                SELECT
                    products.created_by,
                    bookings.booking_status,bookings.total_amount
                FROM products
                LEFT JOIN bookings ON bookings.product_id = products.id
                WHERE products.deleted_at IS NULL
            ) as v1'), 'users.id', '=', 'v1.created_by')->where('users.user_type', 2)->where('users.id', $authId)->whereNull('users.deleted_at')
                ->groupBy('users.id', 'users.name', 'users.email', 'user_details.first_name', 'user_details.last_name')->first();
            $currency = Cache::remember('currecy_details', 86400, function () {
                return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
            });
            $data['currency'] = "$";
            if (isset($currency)) {
                $data['currency'] = $currency->symbol;
            }

            if (isset($data['totalcount'])) {
                $data['totalcount'] = (array)$data['totalcount'];
                $data['totalcount']['processed_amount'] = $this->calculateProviderBalance($authId, 'total_earnings');
                $data['totalcount']['due_amount'] = $this->calculateProviderBalance($authId);
                $data['totalcount']['overall_total_amount'] = number_format($data['totalcount']['overall_total_amount'], 2, '.', '');
            }
            
            return [
                'code' => 200,
                'message' => __('Provider Count detail retrieved successfully.'),
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Error! while getting user details'),
                'error' => $e->getMessage()
            ];
        }
    }

    public function getlatestbookingsapi(Request $request)
    {
        try {
            $language_id = $this->getlanguage();

            if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
                $authId = $request->provider_id;
            }
            $user = Auth::user();
            $authId = $user->id;

            $latestBookingsByCreator = DB::table('bookings')
                ->select(
                    'bookings.id as booking_id',
                    'bookings.booking_date',
                    DB::raw("DATE_FORMAT(bookings.from_time, '%H:%i') AS fromtime"),
                    DB::raw("DATE_FORMAT(bookings.to_time, '%H:%i') AS totime"),
                    'bookings.booking_status',
                    'products.id as product_id',
                    'products.source_name as product_name',
                    'products.created_by as creator_id',
                    'user_details.profile_image',
                    'users.name as creator_name',
                    DB::raw('(SELECT products_meta.source_Values
                FROM products_meta
                WHERE products_meta.product_id = products.id
                AND products_meta.source_key = "product_image" LIMIT 1) as productimage')
                )
                ->join('products', 'bookings.product_id', '=', 'products.id')
                ->join('users', 'products.created_by', '=', 'users.id')->leftjoin('user_details', 'user_details.user_id', '=', 'products.created_by') // Assuming the user table stores creators
                ->whereNull('products.deleted_at')->where('products.language_id', $language_id)->whereNull('bookings.deleted_at')->where('users.id', $authId) // Ensure only non-deleted products
                ->orderBy('bookings.created_at', 'desc') // Sort by latest bookings
                ->limit(5) // Limit to the latest 5 bookings
                ->get();

            $productIds = $latestBookingsByCreator->pluck('product_id')->unique()->filter()->values();

            $productImagesMap = Productmeta::whereIn('product_id', $productIds)
                ->where('source_key', 'product_image')
                ->whereNull('deleted_at')
                ->get()
                ->groupBy('product_id');

            $latestBookingsByCreator = $latestBookingsByCreator->map(function ($booking) use ($productImagesMap) {
                $productId = $booking->product_id ?? null;

                $images = $productImagesMap->get($productId, collect());

                $validImage = $images->first(function ($img) {
                    return !empty($img->source_Values) && file_exists(public_path('storage/' . $img->source_Values));
                });

                $productImage = $validImage->source_Values ?? null;
                $productImageUrl = $validImage
                    ? url('storage/' . $validImage->source_Values)
                    : url('front/img/default-placeholder-image.png');
                $booking->productimage = $productImage;
                $booking->product_image_url = $productImageUrl;

                return $booking;
            });

            return [
                'code' => 200,
                'message' => __('Booking detail retrieved successfully.'),
                'data' => $latestBookingsByCreator,
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Error! while getting details'),
                'error' => $e->getMessage()
            ];
        }
    }

    public function getlatestbookings(Request $request)
    {
        try {
            $language_id = $request->language_id ?? $this->getlanguage();
            $authId = $request->provider_id;

            $latestBookingsByCreator = DB::table('bookings')
                ->select(
                    'bookings.id as booking_id',
                    'bookings.booking_date',
                    DB::raw("DATE_FORMAT(bookings.from_time, '%H:%i') AS fromtime"),
                    DB::raw("DATE_FORMAT(bookings.to_time, '%H:%i') AS totime"),
                    'bookings.booking_status',
                    'products.id as product_id',
                    'products.source_name as product_name',
                    'products.created_by as creator_id',
                    'user_details.profile_image',
                    'users.name as creator_name',
                )
                ->join('products', 'bookings.product_id', '=', 'products.id')
                ->join('users', 'products.created_by', '=', 'users.id')->leftjoin('user_details', 'user_details.user_id', '=', 'products.created_by') // Assuming the user table stores creators
                ->whereNull('products.deleted_at')->where('products.language_id', $language_id)->whereNull('bookings.deleted_at')->where('users.id', $authId) // Ensure only non-deleted products
                ->orderBy('bookings.created_at', 'desc') // Sort by latest bookings
                ->limit(5) // Limit to the latest 5 bookings
                ->get();

            $productIds = $latestBookingsByCreator->pluck('product_id')->unique()->filter()->values();

            $productImagesMap = Productmeta::whereIn('product_id', $productIds)
                ->where('source_key', 'product_image')
                ->whereNull('deleted_at')
                ->get()
                ->groupBy('product_id');

            $latestBookingsByCreator = $latestBookingsByCreator->map(function ($booking) use ($productImagesMap) {
                $productId = $booking->product_id ?? null;

                $images = $productImagesMap->get($productId, collect());

                $validImage = $images->first(function ($img) {
                    return !empty($img->source_Values) && file_exists(public_path('storage/' . $img->source_Values));
                });

                $productImage = $validImage->source_Values ?? null;
                $productImageUrl = $validImage
                    ? url('storage/' . $validImage->source_Values)
                    : url('front/img/default-placeholder-image.png');
                $booking->productimage = $productImage;
                $booking->product_image_url = $productImageUrl;

                return $booking;
            });

            return [
                'code' => 200,
                'message' => __('Booking detail retrieved successfully.'),
                'data' => $latestBookingsByCreator,
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Error! while getting details'),
                'error' => $e->getMessage()
            ];
        }
    }

    public function getlatestreviews(Request $request)
    {
        try {
            $language_id = $request->language_id ?? $this->getlanguage();
            $authId = $request->provider_id;

            $latestratings = DB::table('ratings')
                ->select(
                    'ratings.id as rating_id',
                    'ratings.rating',
                    'ratings.review',
                    'products.id as product_id',
                    'products.source_name as product_name',
                    'products.created_by as creator_id',
                    'users.name as provider_name',
                    'user.id',
                    'user_details.profile_image',
                    DB::raw("CONCAT(IFNULL(user_details.first_name, ''), ' ', IFNULL(user_details.last_name, '')) AS username")
                )
                ->join('products', 'ratings.product_id', '=', 'products.id')
                ->join('users', 'products.created_by', '=', 'users.id') // Creator of the product
                ->join('users as user', 'user.id', '=', 'ratings.user_id') // User who gave the rating
                ->leftJoin('user_details', 'user.id', '=', 'user_details.user_id') // Details of the user who gave the rating
                ->whereNull('products.deleted_at') // Ensure products are not deleted
                ->whereNull('ratings.deleted_at') // Ensure ratings are not deleted
                ->where('ratings.parent_id', 0)
                ->where('users.id',  $authId)->where('products.language_id', $language_id) // Filter by product creator ID
                ->orderBy('ratings.created_at', 'desc') // Sort by latest ratings
                ->limit(5) // Limit to the latest 5 ratings
                ->get();

            $productIds = $latestratings->pluck('product_id')->unique()->filter()->values();

            $productImagesMap = Productmeta::whereIn('product_id', $productIds)
                ->where('source_key', 'product_image')
                ->whereNull('deleted_at')
                ->get()
                ->groupBy('product_id');

            $latestratings = $latestratings->map(function ($rating) use ($productImagesMap) {
                $productId = $rating->product_id ?? null;

                $images = $productImagesMap->get($productId, collect());

                $validImage = $images->first(function ($img) {
                    return !empty($img->source_Values) && file_exists(public_path('storage/' . $img->source_Values));
                });

                $productImage = $validImage
                    ? url('storage/' . $validImage->source_Values)
                    : url('front/img/default-placeholder-image.png');
                $rating->product_image = $productImage;

                return $rating;
            });


            return [
                'code' => 200,
                'message' => __('Reviews detail retrieved successfully.'),
                'data' => $latestratings,
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Error! while getting details'),
                'error' => $e->getMessage()
            ];
        }
    }

    public function getlatestreviewsapi(Request $request)
    {
        try {
            $language_id = $this->getlanguage();
            $user = Auth::user();
            $authId = $user->id;
            $latestratings = DB::table('ratings')
                ->select(
                    'ratings.id as rating_id',
                    'ratings.rating',
                    'ratings.review',
                    'products.id as product_id',
                    'products.source_name as product_name',
                    'products.created_by as creator_id',
                    'users.name as provider_name',
                    'user.id',
                    'user_details.profile_image',
                    DB::raw("CONCAT(IFNULL(user_details.first_name, ''), ' ', IFNULL(user_details.last_name, '')) AS username")
                )
                ->join('products', 'ratings.product_id', '=', 'products.id')
                ->join('users', 'products.created_by', '=', 'users.id') // Creator of the product
                ->join('users as user', 'user.id', '=', 'ratings.user_id') // User who gave the rating
                ->leftJoin('user_details', 'user.id', '=', 'user_details.user_id') // Details of the user who gave the rating
                ->whereNull('products.deleted_at') // Ensure products are not deleted
                ->whereNull('ratings.deleted_at') // Ensure ratings are not deleted
                ->where('ratings.parent_id', 0)
                ->where('users.id',  $authId)->where('products.language_id', $language_id) // Filter by product creator ID
                ->orderBy('ratings.created_at', 'desc') // Sort by latest ratings
                ->limit(5) // Limit to the latest 5 ratings
                ->get();
            return [
                'code' => 200,
                'message' => __('Reviews detail retrieved successfully.'),
                'data' => $latestratings,
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Error! while getting details'),
                'error' => $e->getMessage()
            ];
        }
    }

    public function getlatestproductservice(Request $request)
    {
        try {
            $language_id = $request->language_id ?? $this->getlanguage();
            $authId = $request->provider_id;

            $topServices = DB::table('products')
                ->select(
                    'products.id as product_id',
                    'products.source_name as product_name',
                    'products.slug as product_slug',
                    'products.created_by as creator_id',
                    'users.name as provider_name',
                    DB::raw('(SELECT products_meta.source_Values
            FROM products_meta
            WHERE products_meta.product_id = products.id
            AND products_meta.source_key = "product_image" LIMIT 1) as productimage'),
                    DB::raw('COUNT(DISTINCT bookings.id) as total_bookings'),
                    DB::raw('IFNULL(ROUND(AVG(ratings.rating), 1), "") as average_rating'),
                    DB::raw('COUNT(DISTINCT ratings.id) as total_ratings')
                )
                ->leftJoin('bookings', function ($join) {
                    $join->on('bookings.product_id', '=', 'products.id')
                        ->where(function ($query) {
                            $query->where('bookings.booking_status', '=', '5')
                                ->orWhere('bookings.booking_status', '=', '6');
                        }); // Filter completed bookings
                }) // Link bookings to products
                ->leftJoin('ratings', 'ratings.product_id', '=', 'products.id') // Link ratings to products
                ->join('users', 'products.created_by', '=', 'users.id') // Link products to creators
                ->whereNull('products.deleted_at') // Ensure products are not deleted
                ->whereNull('bookings.deleted_at') // Ensure bookings are not deleted
                ->whereNull('ratings.deleted_at') // Ensure ratings are not deleted
                ->where('products.created_by', $authId)->where('products.source_type', 'service')->where('products.language_id', $language_id) // Filter by the creator (logged-in user)
                ->where('products.status', 1)
                ->where('products.verified_status', 1)
                ->groupBy('products.id', 'products.slug', 'products.source_name', 'products.created_by', 'users.name') // Group by unique products
                ->orderByDesc('total_bookings') // Prioritize products with the highest bookings
                ->orderByDesc('average_rating') // Secondary sort by highest ratings
                ->limit(5) // Limit to top 5 services
                ->get();

            return [
                'code' => 200,
                'message' => __('Reviews detail retrieved successfully.'),
                'data' => $topServices,
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Error! while getting details'),
                'error' => $e->getMessage()
            ];
        }
    }

    public function getsubscribedpack(Request $request)
    {
        try {
            $authUserId = $request->provider_id;
            $currentDate = now();

            $data['standardplan'] = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
                ->where('provider_id', $authUserId)->where('package_transactions.status', 1)
                ->where('package_transactions.payment_status', 2)
                ->where('subscription_packages.subscription_type', 'regular')
                ->select('subscription_packages.package_title', 'subscription_packages.package_term', 'subscription_packages.package_duration', 'subscription_packages.price')
                ->orderbydesc('package_transactions.id')
                ->whereDate('package_transactions.end_date', '>=', $currentDate)
                ->first();
            $data['topupplan'] = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id'
                )->where('provider_id', $authUserId)
                ->where('package_transactions.status', 1)
                ->where('package_transactions.payment_status', 2)
                ->where('subscription_packages.subscription_type', 'topup')
                ->select('subscription_packages.package_title', 'subscription_packages.package_term', 'subscription_packages.package_duration', 'subscription_packages.price')
                ->orderbydesc('package_transactions.id')
                ->whereDate('package_transactions.end_date', '>=', $currentDate)
                ->first();
            $currency = Cache::remember('currecy_details', 86400, function () {
                return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
            });
            $data['currency'] = "$";
            if (isset($currency)) {
                $data['currency'] = $currency->symbol;
            }

            return [
                'code' => 200,
                'message' => __('Detail retrieved successfully.'),
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Error! while getting details'),
                'error' => $e->getMessage()
            ];
        }
    }
    public function getsubscribedpackapi(Request $request)
    {
        try {
            $user = Auth::user();
            $authUserId = $user->id;
            $currentDate = now();

            $data['standardplan'] = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
                ->where('provider_id', $authUserId)->where('package_transactions.status', 1)
                ->where('package_transactions.payment_status', 2)
                ->where('subscription_packages.subscription_type', 'regular')
                ->select('subscription_packages.package_title', 'subscription_packages.package_term', 'subscription_packages.package_duration', 'subscription_packages.price')
                ->orderbydesc('package_transactions.id')
                ->whereDate('package_transactions.end_date', '>=', $currentDate)
                ->first();
            $data['topupplan'] = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id'
                )->where('provider_id', $authUserId)
                ->where('package_transactions.status', 1)
                ->where('package_transactions.payment_status', 2)
                ->where('subscription_packages.subscription_type', 'topup')
                ->select('subscription_packages.package_title', 'subscription_packages.package_term', 'subscription_packages.package_duration', 'subscription_packages.price')
                ->orderbydesc('package_transactions.id')
                ->whereDate('package_transactions.end_date', '>=', $currentDate)
                ->first();
            $currency = Cache::remember('currecy_details', 86400, function () {
                return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
            });
            $data['currency'] = "$";
            if (isset($currency)) {
                $data['currency'] = $currency->symbol;
            }

            return [
                'code' => 200,
                'message' => __('Detail retrieved successfully.'),
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Error! while getting details'),
                'error' => $e->getMessage()
            ];
        }
    }
    public function providerCalendarIndex()
    {
        $users = User::with(['bookings' => function ($query) {
            $query->whereDate('created_at', now()->toDateString()); // Filter by today's bookings
        }])->get();

        $authId = Auth::id();
        $branches = Branches::where('created_by', $authId)->get();

        $services = Service::where('user_id', $authId)->get();

        $latestBookingUserIds = Bookings::latest()
            ->take(20)
            ->pluck('user_id')
            ->toArray();

        // If no latest bookings found, get the last 5 users
        if (empty($latestBookingUserIds)) {
            $customers = User::select('id', 'name')
                ->where('user_type', 3)
                ->latest()
                ->take(5)
                ->get();
        } else {
            $customers = User::select('id', 'name')
                ->where('user_type', 3)
                ->whereIn('id', $latestBookingUserIds)
                ->get();
        }

        return [
            'users' => $users,
            'branches' => $branches,
            'customers' => $customers,
            'services' => $services
        ];
    }

    public function getstafflist()
    {
        $users = User::where('user_type', 4)->get();
        return response()->json($users);
    }

    public function providergetBookingsapi(Request $request)
    {
        // Retrieve the provider_id from the request
        $user = Auth::user();
        $providerId = $user->id;
        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
        $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);
        $timeformatSetting = GlobalSetting::where('key', 'time_format_view')->first();
        $timeFormat = $timeformatSetting->value ?? '%H:%i';
        $sqlTimeFormat = $this->mapTimeFormatToSQL($timeFormat);
        // Fetch bookings with the provider_id filter
        $bookings = Bookings::select(
            'bookings.*',
            DB::raw("DATE_FORMAT(
                CASE
                    WHEN bookings.booking_date IS NOT NULL THEN bookings.booking_date
                    ELSE bookings.created_at
                END, '%d-%m-%Y'
            ) AS bookingdate"),
            DB::raw("TIME_FORMAT(bookings.from_time, '%H:%i') AS fromtime"),
            DB::raw("TIME_FORMAT(bookings.to_time, '%H:%i') AS totime"),
            'products.source_name',
            'users.name as user_name',
            'users.email',
            'users.phone_number',
            DB::raw("
                CASE
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
            DB::raw("(select CASE
            WHEN user_details.first_name IS NOT NULL AND user_details.last_name IS NOT NULL
                THEN CONCAT(user_details.first_name, ' ', user_details.last_name)
            ELSE users.name
         END  from user_details where u1.id=user_details.user_id LIMIT 1) AS staff_name"),
            DB::raw("(select branch_name from branches where branches.id=bookings.branch_id LIMIT 1) as branchname"),
            DB::raw("(SELECT source_Values FROM products_meta WHERE products_meta.product_id = bookings.product_id AND source_key = 'product_image' LIMIT 1) as productimage")
        )
            ->leftJoin('products', 'products.id', '=', 'bookings.product_id')
            ->leftJoin('users', 'users.id', '=', 'bookings.user_id')->leftJoin('users as u1', 'u1.id', '=', 'bookings.staff_id')
            ->with([
                'user.userDetails',
                'product.createdBy.userDetails',
            ])->where('products.created_by', $providerId)
            ->get()
            ->map(function ($booking) {
                $color = '#FF008A';
                if ($booking->booking_status == 1) {
                    $color = '#FF008A';
                } else if ($booking->booking_status == 2) {
                    $color = '#5625E8';
                } else if ($booking->booking_status == 3) {
                    $color = '#E70D0D';
                } else if ($booking->booking_status == 5 || $booking->booking_status == 6 || $booking->booking_status == 7) {
                    $color = '#03C95A';
                } elseif ($booking->booking_status == 4) {
                    $color = '#856404';
                }
                $currency = Cache::remember('currecy_details', 86400, function () {
                    return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
                });
                $data['currency'] = "$";
                if (isset($currency)) {
                    $data['currency'] = $currency->symbol;
                }
                $bookSlotTime = Bookings::select('slot_id')->where('id', $booking->id)->first();
                $slotTime = Productmeta::select('source_Values')->where('id', $bookSlotTime->slot_id)->first();
                $formattedTime = '';
                if ($slotTime && strpos($slotTime->source_Values, ' - ') !== false) {
                    $timeRange = explode(' - ', $slotTime->source_Values);

                    if (count($timeRange) === 2 && strtotime($timeRange[0]) && strtotime($timeRange[1])) {
                        $startTime = (new DateTime($timeRange[0]))->format('h:i A');
                        $endTime = (new DateTime($timeRange[1]))->format('h:i A');
                        $formattedTime = "$startTime - $endTime";
                    }
                }
                $productImage = UserDetail::select('profile_image')->where('user_id', $booking->user_id)->first();
                $baseUrl = url('/storage/profile');
                $userImage = $productImage && $productImage->profile_image ? $baseUrl . '/' . $productImage->profile_image : "N/A";
                return [
                    'title' => $booking->source_name,
                    'start' =>  DateTime::createFromFormat('d-m-Y', $booking->bookingdate)->format('Y-m-d'),
                    'end' => $booking->bookingdate,
                    'fromtime' => $booking->fromtime ?? "",
                    'totime' => $booking->totime ?? "",
                    'branch' => $booking->branchname ?? "-",
                    'staffname' => $booking->staff_name ?? "-",
                    'provider' => isset($booking['product']['createdBy']['userDetails'])
                        ? ucwords(($booking['product']['createdBy']['userDetails']['first_name'] ?? '') . ' ' . ($booking['product']['createdBy']['userDetails']['last_name'] ?? ''))
                        : '',
                    'amount' => $data['currency'] . $booking->total_amount,
                    'location' => $booking->user_city ?? "-",
                    'user' =>  isset($booking['user']['userDetails'])
                        ? ucwords(($booking['user']['userDetails']->first_name ?? '') . ' ' . ($booking['user']['userDetails']->last_name ?? ''))
                        : ucwords($booking->user_name),
                    'phone' => $booking->phone_number ?? "-",
                    'email' => $booking->email,
                    'id' => $booking->id,
                    'slot_time' => $formattedTime,
                    'status' => $booking->booking_status_label,
                    'status_no' => $booking->booking_status,
                    'color' => $color,
                    'userimage' => $userImage ?? ''
                    // 'url' => route('booking.show', $booking->id), // Optional: link to booking details
                ];
            });

        return $bookings;
    }

    public function providergetBookings(Request $request)
    {
        // Retrieve the provider_id from the request
        $providerId = $request->input('provider_id');
        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
        $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);
        $timeformatSetting = GlobalSetting::where('key', 'time_format_view')->first();
        $timeFormat = $timeformatSetting->value ?? '%H:%i';
        $sqlTimeFormat = $this->mapTimeFormatToSQL($timeFormat);
        // Fetch bookings with the provider_id filter
        $bookings = Bookings::select(
            'bookings.*',
            DB::raw("DATE_FORMAT(
                CASE
                    WHEN bookings.booking_date IS NOT NULL THEN bookings.booking_date
                    ELSE bookings.created_at
                END, '%d-%m-%Y'
            ) AS bookingdate"),
            DB::raw("TIME_FORMAT(bookings.from_time, '%H:%i') AS fromtime"),
            DB::raw("TIME_FORMAT(bookings.to_time, '%H:%i') AS totime"),
            'products.source_name',
            'users.name as user_name',
            'users.email',
            'users.phone_number',
            DB::raw("
                CASE
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
            DB::raw("(select CASE
            WHEN user_details.first_name IS NOT NULL AND user_details.last_name IS NOT NULL
                THEN CONCAT(user_details.first_name, ' ', user_details.last_name)
            ELSE users.name
         END  from user_details where u1.id=user_details.user_id LIMIT 1) AS staff_name"),
            DB::raw("(select branch_name from branches where branches.id=bookings.branch_id LIMIT 1) as branchname"),
            DB::raw("(SELECT source_Values FROM products_meta WHERE products_meta.product_id = bookings.product_id AND source_key = 'product_image' LIMIT 1) as productimage")
        )
            ->leftJoin('products', 'products.id', '=', 'bookings.product_id')
            ->leftJoin('users', 'users.id', '=', 'bookings.user_id')->leftJoin('users as u1', 'u1.id', '=', 'bookings.staff_id')
            ->with([
                'user.userDetails',
                'product.createdBy.userDetails',
            ])->where('products.created_by', $providerId)
            ->get()
            ->map(function ($booking) {
                $color = '#FF008A';
                if ($booking->booking_status == 1) {
                    $color = '#FF008A';
                } else if ($booking->booking_status == 2) {
                    $color = '#5625E8';
                } else if ($booking->booking_status == 3) {
                    $color = '#E70D0D';
                } else if ($booking->booking_status == 5 || $booking->booking_status == 6 || $booking->booking_status == 7) {
                    $color = '#03C95A';
                } elseif ($booking->booking_status == 4) {
                    $color = '#856404';
                }
                $currency = Cache::remember('currecy_details', 86400, function () {
                    return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
                });
                $data['currency'] = "$";
                if (isset($currency)) {
                    $data['currency'] = $currency->symbol;
                }
                $bookSlotTime = Bookings::select('slot_id')->where('id', $booking->id)->first();
                $slotTime = Productmeta::select('source_Values')->where('id', $bookSlotTime->slot_id)->first();
                $formattedTime = '';
                if ($slotTime && strpos($slotTime->source_Values, ' - ') !== false) {
                    $timeRange = explode(' - ', $slotTime->source_Values);

                    if (count($timeRange) === 2 && strtotime($timeRange[0]) && strtotime($timeRange[1])) {
                        $startTime = (new DateTime($timeRange[0]))->format('h:i A');
                        $endTime = (new DateTime($timeRange[1]))->format('h:i A');
                        $formattedTime = "$startTime - $endTime";
                    }
                }
                $productImage = UserDetail::select('profile_image')->where('user_id', $booking->user_id)->first();
                $baseUrl = url('/storage/profile');
                $userImage = $productImage && $productImage->profile_image ? $baseUrl . '/' . $productImage->profile_image : "N/A";
                return [
                    'title' => $booking->source_name,
                    'start' =>  DateTime::createFromFormat('d-m-Y', $booking->bookingdate)->format('Y-m-d'),
                    'end' => $booking->bookingdate,
                    'fromtime' => $booking->fromtime ?? "",
                    'totime' => $booking->totime ?? "",
                    'branch' => $booking->branchname ?? "-",
                    'staffname' => ucwords($booking->staff_name) ?? "-",
                    'provider' => isset($booking['product']['createdBy']['userDetails'])
                        ? ucwords(($booking['product']['createdBy']['userDetails']['first_name'] ?? '') . ' ' . ($booking['product']['createdBy']['userDetails']['last_name'] ?? ''))
                        : '',
                    'amount' => $data['currency'] . $booking->total_amount,
                    'location' => $booking->user_city ?? "-",
                    'user' =>  isset($booking['user']['userDetails'])
                        ? ucwords(($booking['user']['userDetails']->first_name ?? '') . ' ' . ($booking['user']['userDetails']->last_name ?? ''))
                        : $booking->user_name,
                    'phone' => $booking->phone_number ?? "-",
                    'email' => $booking->email,
                    'id' => $booking->id,
                    'slot_time' => $formattedTime,
                    'status' => $booking->booking_status_label,
                    'status_no' => $booking->booking_status,
                    'color' => $color,
                    'userimage' => $userImage ?? ''
                ];
            });

        return $bookings;
    }

    public function providergetBookApi(Request $request)
    {
        // Retrieve the provider_id from the request
        $providerId = $request->input('provider_id');
        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
        $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);
        $timeformatSetting = GlobalSetting::where('key', 'time_format_view')->first();
        $timeFormat = $timeformatSetting->value ?? '%H:%i';
        $sqlTimeFormat = $this->mapTimeFormatToSQL($timeFormat);
        // Fetch bookings with the provider_id filter
        $bookings = Bookings::select(
            'bookings.*',
            DB::raw("DATE_FORMAT(
                CASE
                    WHEN bookings.booking_date IS NOT NULL THEN bookings.booking_date
                    ELSE bookings.created_at
                END, '%d-%m-%Y'
            ) AS bookingdate"),
            DB::raw("TIME_FORMAT(bookings.from_time, '%H:%i') AS fromtime"),
            DB::raw("TIME_FORMAT(bookings.to_time, '%H:%i') AS totime"),
            'products.source_name',
            'users.name as user_name',
            'users.email',
            'users.phone_number',
            DB::raw("
                CASE
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
            DB::raw("(select CASE
            WHEN user_details.first_name IS NOT NULL AND user_details.last_name IS NOT NULL
                THEN CONCAT(user_details.first_name, ' ', user_details.last_name)
            ELSE users.name
         END  from user_details where u1.id=user_details.user_id LIMIT 1) AS staff_name"),
            DB::raw("(select branch_name from branches where branches.id=bookings.branch_id LIMIT 1) as branchname"),
            DB::raw("(SELECT source_Values FROM products_meta WHERE products_meta.product_id = bookings.product_id AND source_key = 'product_image' LIMIT 1) as productimage")
        )
            ->leftJoin('products', 'products.id', '=', 'bookings.product_id')
            ->leftJoin('users', 'users.id', '=', 'bookings.user_id')->leftJoin('users as u1', 'u1.id', '=', 'bookings.staff_id')
            ->with([
                'user.userDetails',
                'product.createdBy.userDetails',
            ])->where('products.created_by', $providerId)
            ->get()
            ->map(function ($booking) {
                $color = '#FF008A';
                if ($booking->booking_status == 1) {
                    $color = '#FF008A';
                } else if ($booking->booking_status == 2) {
                    $color = '#5625E8';
                } else if ($booking->booking_status == 3) {
                    $color = '#E70D0D';
                } else if ($booking->booking_status == 5 || $booking->booking_status == 6 || $booking->booking_status == 7) {
                    $color = '#03C95A';
                } elseif ($booking->booking_status == 4) {
                    $color = '#856404';
                }
                $currency = Cache::remember('currecy_details', 86400, function () {
                    return Currency::select('symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
                });
                $data['currency'] = "$";
                if (isset($currency)) {
                    $data['currency'] = $currency->symbol;
                }
                $bookSlotTime = Bookings::select('slot_id')->where('id', $booking->id)->first();
                $slotTime = Productmeta::select('source_Values')->where('id', $bookSlotTime->slot_id)->first();
                $formattedTime = '';
                if ($slotTime && strpos($slotTime->source_Values, ' - ') !== false) {
                    $timeRange = explode(' - ', $slotTime->source_Values);

                    if (count($timeRange) === 2 && strtotime($timeRange[0]) && strtotime($timeRange[1])) {
                        $startTime = (new DateTime($timeRange[0]))->format('h:i A');
                        $endTime = (new DateTime($timeRange[1]))->format('h:i A');
                        $formattedTime = "$startTime - $endTime";
                    }
                }
                $productImage = UserDetail::select('profile_image')->where('user_id', $booking->user_id)->first();
                $baseUrl = url('/storage/profile');
                $userImage = $productImage && $productImage->profile_image ? $baseUrl . '/' . $productImage->profile_image : "N/A";
                return [
                    'title' => $booking->source_name,
                    'start' =>  DateTime::createFromFormat('d-m-Y', $booking->bookingdate)->format('Y-m-d'),
                    'end' => $booking->bookingdate,
                    'fromtime' => $booking->fromtime ?? "",
                    'totime' => $booking->totime ?? "",
                    'branch' => $booking->branchname ?? "-",
                    'staffname' => $booking->staff_name ?? "-",
                    'provider' => isset($booking['product']['createdBy']['userDetails'])
                        ? ($booking['product']['createdBy']['userDetails']['first_name'] ?? '') . ' ' . ($booking['product']['createdBy']['userDetails']['last_name'] ?? '')
                        : '',
                    'amount' => $data['currency'] . $booking->total_amount,
                    'location' => $booking->user_city ?? "-",
                    'user' =>  isset($booking['user']['userDetails'])
                        ? (($booking['user']['userDetails']->first_name ?? '') . ' ' . ($booking['user']['userDetails']->last_name ?? ''))
                        : $booking->user_name,
                    'phone' => $booking->phone_number ?? "-",
                    'email' => $booking->email,
                    'id' => $booking->id,
                    'slot_time' => $formattedTime,
                    'status' => $booking->booking_status_label,
                    'status_no' => $booking->booking_status,
                    'color' => $color,
                    'userimage' => $userImage ?? ''
                ];
            });

        return [
            'code' => 200,
            'message' => 'Booking Details Received Successfully',
            'data' => $bookings,
        ];
    }

    public function getlanguage(): mixed
    {
        $languages = Language::select('id', 'code', 'is_default')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->get();
        if (Auth::check()) {
            $language_id = User::select('user_language_id')->where('id', Auth::id())->first();
            if ($language_id) {
                $language_id = $language_id->user_language_id;
            } else {
                $defaultLanguage = $languages->firstWhere('is_default', 1);
                $language_id = $defaultLanguage ? $defaultLanguage->id : null;
            }
        } elseif (Cookie::get('languageId')) {
            $language_id = Cookie::get('languageId');
        } else {
            $defaultLanguage = $languages->firstWhere('is_default', 1);
            $language_id = $defaultLanguage ? $defaultLanguage->id : null;
        }
        return $language_id;
    }

    public function getStaffDetails(Request $request)
    {
        $authId = Auth::id();
        $branches = Branches::where('created_by', $authId)->get();

        if ($branches->isEmpty()) {
            return ['error' => 'No branches found'];
        }

        $staffDetails = $branches->map(function ($branch) {
            $branchStaffs = BranchStaffs::where('branch_id', $branch->id)->get();

            $userIds = $branchStaffs->pluck('staff_id');
            $userDetails = UserDetail::with('user')
                ->whereIn('user_id', $userIds)->get(['user_id', 'first_name', 'last_name', 'mobile_number', 'gender', 'dob', 'bio', 'profile_image']);

            $staff = $userDetails->map(function ($user) {
                $profileImage = $user->profile_image && file_exists(public_path('storage/profile/' . $user->profile_image));
                $profileImageUrl = $profileImage ? url('storage/profile/' . $user->profile_image) : url('assets/img/profile-default.png');

                return [
                    'user' => [
                        'user_id' => $user->user_id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'mobile_number' => $user->user->phone_number ?? null,
                        'gender' => $user->gender,
                        'dob' => $user->dob,
                        'bio' => $user->bio,
                        'profile_image' => $profileImageUrl,
                    ],
                ];
            });
            $branchImage = $branch->branch_image && file_exists(public_path('storage/branch/' . $branch->branch_image));

            $branchImageUrl = $branchImage ? url('storage/branch/' . $branch->branch_image) : url('front/img/default-placeholder-image.png');

            return [
                'branch' => [
                    'id' => $branch->id,
                    'branch_name' => $branch->branch_name,
                    'branch_mobile' => $branch->branch_mobile,
                    'branch_email' => $branch->branch_email,
                    'branch_image' => $branchImageUrl,
                    'branch_address' => $branch->branch_address,
                    'branch_country' => $branch->branch_country,
                    'branch_state' => $branch->branch_state,
                    'branch_city' => $branch->branch_city,
                    'branch_zip' => $branch->branch_zip,
                    'branch_startworkhour' => $branch->branch_startworkhour,
                    'branch_endworkhour' => $branch->branch_endworkhour,
                    'branch_workingday' => $branch->branch_workingday,
                    'branch_holiday' => $branch->branch_holiday,
                ],
                'staff' => $staff,
            ];
        });

        return ['branches' => $staffDetails];
    }

    public function getStaffDetailsApi(Request $request)
    {
        $authId = $request->provider_id;

        if (empty($authId)) {
            return [
                'status' => 'error',
                'code' => 422,
                'message' => 'Provider ID is required.',
                'data' => []
            ];
        }

        $branches = Branches::where('created_by', $authId)->get();

        if ($branches->isEmpty()) {
            return [
                'status' => 'success',
                'code' => 200,
                'message' => 'No branches found.',
                'data' => []
            ];
        }

        $staffDetails = $branches->map(function ($branch) {
            $branchStaffs = BranchStaffs::where('branch_id', $branch->id)->get();

            $userDetails = collect();
            if (!$branchStaffs->isEmpty()) {
                $userIds = $branchStaffs->pluck('staff_id');
                $userDetails = UserDetail::whereIn('user_id', $userIds)
                    ->get(['user_id', 'first_name', 'last_name', 'mobile_number', 'gender', 'dob', 'bio', 'profile_image']);
            }

            $staff = $userDetails->map(function ($user) {
                return [
                    'user_id' => $user->user_id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'mobile_number' => $user->mobile_number,
                    'gender' => $user->gender,
                    'dob' => $user->dob,
                    'bio' => $user->bio,
                    'profile_image' => $user->profile_image ? Storage::url('profile/' . $user->profile_image) : null,
                ];
            });

            return [
                'branch' => [
                    'id' => $branch->id,
                    'branch_name' => $branch->branch_name,
                    'branch_mobile' => $branch->branch_mobile,
                    'branch_email' => $branch->branch_email,
                    'branch_image' => $branch->branch_image ? Storage::url('branch/' . $branch->branch_image) : null,
                    'branch_address' => $branch->branch_address,
                    'branch_country' => $branch->branch_country,
                    'branch_state' => $branch->branch_state,
                    'branch_city' => $branch->branch_city,
                    'branch_zip' => $branch->branch_zip,
                    'branch_startworkhour' => $branch->branch_startworkhour,
                    'branch_endworkhour' => $branch->branch_endworkhour,
                    'branch_workingday' => $branch->branch_workingday,
                    'branch_holiday' => $branch->branch_holiday,
                ],
                'staff' => $staff
            ];
        });

        return [
            'status' => 'success',
            'code' => 200,
            'message' => 'Branch and staff details fetched successfully.',
            'data' => $staffDetails
        ];
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

    public function getBranchStaff(Request $request)
    {
        $branchId = $request->branch_id;

        $branch = Branches::where('id', $branchId)->first();

        if (!$branch) {
            return ['error' => 'Branch not found.'];
        }

        $staffIds = BranchStaffs::where('branch_id', $branchId)->pluck('staff_id');

        $staff = User::whereIn('id', $staffIds)->get(['id', 'name']);

        return [
            'branch' => [
                'name' => $branch->branch_name,
                'phone_number' => $branch->branch_mobile,
                'email' => $branch->branch_email,
            ],
            'staff' => $staff
        ];
    }

    public function getCustomer(Request $request)
    {
        $userId = $request->input('user_id');

        $user = User::find($userId);
        $userDetails = $user ? UserDetail::where('user_id', $userId)->first() : null;

        if ($user) {
            $userInfo = [
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'first_name' => $userDetails->first_name ?? '',
                'last_name' => $userDetails->last_name ?? '',
                'profile_image' => $userDetails->profile_image
                    ? asset('storage/profile/' . $userDetails->profile_image)
                    : asset('assets/img/profile-default.png'),
            ];

            return [
                'userInfo' => $userInfo,
            ];
        }

        return ['error' => 'User(s) not found'];
    }

    public function fetchStaffService(Request $request)
    {
        $staffId = $request->staff_id;

        $staff = User::select('name', 'email', 'phone_number')
            ->where('id', $staffId)
            ->first();

        if (!$staff) {
            return ['error' => 'Staff not found.'];
        }

        $branchIds = ServiceStaff::where('staff_id', $staffId)
            ->pluck('service_branch_id')
            ->toArray();

        $serviceIds = ServiceBranch::whereIn('id', $branchIds)
            ->pluck('service_id')
            ->toArray();

        $staffCategory = UserDetail::select('category_id')
            ->where('user_id', $staffId)
            ->first();

        if (!$staffCategory) {
            return ['error' => 'Staff category not found.'];
        }

        $services = Service::whereIn('id', $serviceIds)
            ->where('source_category', $staffCategory->category_id)
            ->get(['id', 'source_name']);

        return [
            'staff' => $staff,
            'services' => $services
        ];
    }

    public function providerCalenderBooking(Request $request)
    {
        $formattedBookingDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->input('booking_date'))->format('Y-m-d');

        $authId = Auth::id();

        $user = User::find($request->user_id);

        $userDetails = UserDetail::where('user_id', $request->user_id)->first();
        $serviceAmount = Service::select('source_price')->where('id', $request->service_id)->first();

        $data = [
            "product_id" => $request->input('service_id'),
            "branch_id" => $request->input('branch_id') ?? 0,
            "staff_id" => $request->input('staff_id'),
            "slot_id" => $request->input('slot_id') ?? 0,
            "booking_date" => $formattedBookingDate,
            "from_time" => $request->input('from_time'),
            "to_time" => $request->input('to_time'),
            "booking_status" => 1,
            "user_id" => $request->user_id,
            "first_name" => $userDetails->first_name,
            "last_name" => $userDetails->last_name,
            "user_email" => $user->email,
            "user_phone" => $user->phone_number,
            "user_city" => "Singapore",
            "user_state" => "Singapore",
            "user_address" => $userDetails->address,
            "user_postal" => $userDetails->postal_code,
            "note" => $request->input('note'),
            "payment_type" => 5,
            "payment_status" => 1,
            "service_qty" => 1,
            "service_amount" => $serviceAmount->source_price,
            "total_amount" => $serviceAmount->source_price,
            "created_at" => $formattedBookingDate,
            "created_by" => $authId
        ];

        $save = Bookings::create($data);

        if ($save) {
            return ['code' => 200, 'message' => 'Booking successfully created!', 'data' => []];
        } else {
            return ['error' => 'Failed to create booking'];
        }
    }

    public function providerCalenderBookingApi(Request $request)
    {
        $formattedBookingDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->input('booking_date'))->format('Y-m-d');

        $authId = Auth::id();

        $user = User::find($request->user_id);

        $userDetails = UserDetail::where('user_id', $request->user_id)->first();
        $serviceAmount = Service::select('source_price')->where('id', $request->service_id)->first();

        $data = [
            "product_id" => $request->input('service_id'),
            "branch_id" => $request->input('branch_id') ?? 0,
            "staff_id" => $request->input('staff_id'),
            "slot_id" => $request->input('slot_id') ?? 0,
            "booking_date" => $formattedBookingDate,
            "from_time" => $request->input('from_time'),
            "to_time" => $request->input('to_time'),
            "booking_status" => 1,
            "user_id" => $request->user_id,
            "first_name" => $userDetails->first_name,
            "last_name" => $userDetails->last_name,
            "user_email" => $user->email,
            "user_phone" => $user->phone_number,
            "user_city" => "Singapore",
            "user_state" => "Singapore",
            "user_address" => $userDetails->address,
            "user_postal" => $userDetails->postal_code,
            "note" => $request->input('note'),
            "payment_type" => 5,
            "payment_status" => 1,
            "service_qty" => 1,
            "service_amount" => $serviceAmount->source_price,
            "total_amount" => $serviceAmount->source_price,
            "created_at" => $formattedBookingDate,
            "created_by" => $request->user_id
        ];

        $save = Bookings::create($data);

        if ($save) {
            return ['code' => 200, 'message' => 'Booking successfully created!', 'data' => []];
        } else {
            return ['error' => 'Failed to create booking'];
        }
    }

    public function getUserList(Request $request)
    {
        $users = User::where("user_type", 3)
            ->select("id", "name", "phone_number")
            ->get()
            ->map(function ($user) {
                $user->name = ucfirst(strtolower($user->name)); // Capitalize first letter
                return $user;
            });

        return [
            'code' => 200,
            'message' => __('user detail retrieved'),
            'data' => $users,
        ];
    }

    public function getServiceList(Request $request)
    {
        $product = Product::where("user_id", $request->provider_id)
            ->select("id", "source_name")
            ->get()
            ->map(function ($product) {
                $product->source_name = ucfirst(strtolower($product->source_name)); // Capitalize first letter
                return $product;
            });

        return [
            'code' => 200,
            'message' => __('service detail retrieved'),
            'data' => $product,
        ];
    }

    public function getBranchList(Request $request)
    {
        $branch = Branches::where("created_by", $request->provider_id)
            ->select("id", "branch_name")
            ->get()
            ->map(function ($branch) {
                $branch->branch_name = ucfirst(strtolower($branch->branch_name)); // Capitalize first letter
                return $branch;
            });

        return [
            'code' => 200,
            'message' => __('brnach detail retrieved'),
            'data' => $branch,
        ];
    }

    public function getStaffLists(Request $request)
    {
        $staff = BranchStaffs::where("branch_id", $request->branch_id)
            ->with(['user:id,name']) // Eager load user details
            ->select("id", "staff_id")
            ->get()
            ->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'staff_id' => $staff->staff_id,
                    'name' => ucfirst(strtolower(optional($staff->user)->name)), // Ensure safe access
                ];
            });

        return [
            'code' => 200,
            'message' => __('Branch staff details retrieved'),
            'data' => $staff,
        ];
    }
}
