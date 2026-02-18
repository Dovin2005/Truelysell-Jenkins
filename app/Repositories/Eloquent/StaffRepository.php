<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\StaffRepositoryInterface;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\GlobalSetting\app\Models\Currency;
use App\Models\Bookings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use App\Models\PackageTrx;
use Illuminate\Support\Facades\Cookie;
use App\Models\BranchStaffs;
use App\Models\PayoutHistory;
use App\Models\ServiceBranch;
use App\Models\ServiceStaff;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\GlobalSetting\app\Models\Language;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\Product\app\Models\Productmeta as ModelsProductmeta;
use Modules\Service\app\Models\Productmeta;
use Modules\Service\app\Models\Service;

class StaffRepository implements StaffRepositoryInterface
{
    protected ?int $authUserId;

    public function __construct()
    {
        $this->authUserId = Auth::id() ?? Cache::get('provider_auth_id');
    }

    public function index(string $routeName): View
    {
        $title = $routeName == 'admin.providerslist' ? 'Providers' : 'Users';
        return view('people.list', compact('title'));
    }

    public function getDashboard(): array
    {
        $user = Auth::user();
        $permissions = DB::table('permissions')
            ->join('roles', 'roles.id', '=', 'permissions.role_id')
            ->where('permissions.role_id', $user->role_id)
            ->get();

        return [
            'user' => $user,
            'permissions' => $permissions
        ];
    }

    public function getSubscription(): array
    {
        try {
            $data['standardplan'] = DB::table('subscription_packages')->select('price', 'package_title', 'package_term', 'package_duration')->where('status', 1)->where('order_by', 2)->whereNull('deleted_at')->first();
            $currency = Currency::select('symbol')->where('is_default', 1)->where('status', 1)->first();
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

    public function getTotalBookingCount(Request $request): array
    {
        try {
            $authId = $request->provider_id ?? Auth::id();
            $productIds = Service::where('user_id', $authId)->pluck('id')->toArray();

            $data['totalcount']['upcoming_count'] = Bookings::whereIn('booking_status', [1, 2])
                ->where(function($query) use ($productIds, $authId) {
                    $query->whereIn('product_id', $productIds)
                        ->orWhere('staff_id', $authId);
                })
                ->count();
            $data['totalcount']['cancelled_count'] = Bookings::whereIn('booking_status', [3, 8])
                ->where(function($query) use ($productIds, $authId) {
                    $query->whereIn('product_id', $productIds)
                        ->orWhere('staff_id', $authId);
                })
                ->count();
            $data['totalcount']['completed_count'] = Bookings::whereIn('booking_status', [5])
                ->where(function($query) use ($productIds, $authId) {
                    $query->whereIn('product_id', $productIds)
                        ->orWhere('staff_id', $authId);
                })
                ->count();
            $data['totalcount']['order_completed_count'] = Bookings::whereIn('booking_status', [6])
                ->where(function($query) use ($productIds, $authId) {
                    $query->whereIn('product_id', $productIds)
                        ->orWhere('staff_id', $authId);
                })
                ->count();
            $data['totalcount']['completed_total_amount'] = Bookings::whereIn('booking_status', [6])
                ->where(function($query) use ($productIds, $authId) {
                    $query->whereIn('product_id', $productIds)
                        ->orWhere('staff_id', $authId);
                })
                ->sum('total_amount');
            $data['totalcount']['overall_total_amount'] = $data['totalcount']['completed_total_amount'];
            $currency = Currency::select('symbol')->where('is_default', 1)->where('status', 1)->first();
            $data['currency'] = "$";
            if (isset($currency)) {
                $data['currency'] = $currency->symbol;
            }

            if (isset($data['totalcount'])) {
                $data['totalcount'] = (array)$data['totalcount'];
                $data['totalcount']['processed_amount'] = $this->calculateProviderBalance($authId, 'total_earnings', $productIds);
                $data['totalcount']['due_amount'] = $this->calculateProviderBalance($authId, '', $productIds);
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

    function calculateProviderBalance(int $providerId, string $type = '', array $productIds): string
    {
        $commissionRate = 0;
        $commissionSetting = GlobalSetting::where('key', 'commission_rate_percentage')->first();
        if ($commissionSetting) {
            $commissionRate = (float) $commissionSetting->value;
        }

        $transactionsQuery = Bookings::with(['product'])
            ->where('booking_status', 6)
            ->where(function($query) use ($productIds, $providerId) {
                $query->whereIn('product_id', $productIds)
                    ->orWhere('staff_id', $providerId);
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

                $commissionAmount = ($grossAmount * $commissionRate) / 100;
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

    public function getLatestBookings(Request $request): array
    {
        try {
            $language_id = $request->language_id ?? $this->getLanguage();
            $authId = $request->provider_id ?? null;

            $productIds = Service::where('user_id', $authId)->pluck('id')->toArray();

            $latestBookingsByCreator = DB::table('bookings')
                ->select(
                    'bookings.id as booking_id',
                    'bookings.booking_date',
                    DB::raw("DATE_FORMAT(bookings.from_time, '%H:%i') AS fromtime"),
                    DB::raw("DATE_FORMAT(bookings.to_time, '%H:%i') AS totime"),
                    'bookings.booking_status',
                    'bookings.staff_id',
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
                ->join('users', 'products.created_by', '=', 'users.id')
                ->leftJoin('user_details', 'user_details.user_id', '=', 'products.created_by')
                ->whereNull('products.deleted_at')
                ->where('products.language_id', $language_id)
                ->whereNull('bookings.deleted_at')
                ->where(function ($query) use ($authId, $productIds) {
                    $query->whereIn('product_id', $productIds)
                        ->orWhere('staff_id', $authId);
                })
                ->orderBy('bookings.created_at', 'desc')
                ->limit(5)
                ->get();

            return [
                'code' => 200,
                'message' => __('Booking detail retrieved successfully.'),
                'data' => $latestBookingsByCreator
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Error! while getting details'),
                'error' => $e->getMessage()
            ];
        }
    }

    public function getLatestReviews(Request $request): array
    {
        try {
            $language_id = $request->language_id ?? $this->getLanguage();
            $authId = $request->provider_id ?? null;
            
            $productIds = Service::where('user_id', $authId)->pluck('id')->toArray();

            $latestRatings = DB::table('ratings')
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
                ->join('users', 'products.created_by', '=', 'users.id')
                ->join('users as user', 'user.id', '=', 'ratings.user_id')
                ->leftJoin('user_details', 'user.id', '=', 'user_details.user_id')
                ->whereNull('products.deleted_at')
                ->whereNull('ratings.deleted_at')
                ->where('products.language_id', $language_id)
                ->where(function ($query) use ($authId, $productIds) {
                    $query->whereIn('ratings.product_id', $productIds)
                        ->orWhere('ratings.user_id', $authId);
                })
                ->orderBy('ratings.created_at', 'desc')
                ->limit(5)
                ->get();

            return [
                'code' => 200,
                'message' => __('Reviews detail retrieved successfully.'),
                'data' => $latestRatings
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Error! while getting details'),
                'error' => $e->getMessage()
            ];
        }
    }

    public function getLatestProductService(Request $request): array
    {
        try {
            $language_id = $request->language_id ?? $this->getLanguage();
            $authId = $request->provider_id ?? null;
            $productIds = Service::where('user_id', $authId)->pluck('id')->toArray();

            $topServices = DB::table('products')
                ->select(
                    'products.id as product_id',
                    'products.source_name as product_name',
                    'products.created_by as creator_id',
                    'products.slug as product_slug',
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
                        });
                })
                ->leftJoin('ratings', 'ratings.product_id', '=', 'products.id')
                ->join('users', 'products.created_by', '=', 'users.id')
                ->whereNull('products.deleted_at')
                ->whereNull('bookings.deleted_at')
                ->whereNull('ratings.deleted_at')
                ->where(function ($query) use ($authId, $productIds) {
                    $query->whereIn('products.id', $productIds)
                        ->orWhere('bookings.staff_id', $authId);
                })
                ->where('products.source_type', 'service')
                ->where('products.language_id', $language_id)
                ->where('products.status', 1)
                ->where('products.verified_status', 1)
                ->groupBy('products.id', 'products.slug', 'products.source_name', 'products.created_by', 'users.name')
                ->orderByDesc('total_bookings')
                ->orderByDesc('average_rating')
                ->limit(5)
                ->get();

            return [
                'code' => 200,
                'message' => __('Reviews detail retrieved successfully.'),
                'data' => $topServices
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Error! while getting details'),
                'error' => $e->getMessage()
            ];
        }
    }

    public function getSubscribedPack(Request $request): array
    {
        try {
            $authUserId = $request->has('is_mobile') && $request->get('is_mobile') === "yes"
                ? $request->provider_id
                : (Auth::id() ?? Cache::get('provider_auth_id'));

            $data['standardplan'] = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
                ->where('provider_id', $authUserId)
                ->where('package_transactions.status', 1)
                ->where('package_transactions.payment_status', 2)
                ->where('subscription_packages.subscription_type', 'regular')
                ->select('subscription_packages.package_title', 'subscription_packages.package_term', 'subscription_packages.package_duration', 'subscription_packages.price')
                ->orderByDesc('package_transactions.id')
                ->first();

            $data['topupplan'] = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
                ->where('provider_id', $authUserId)
                ->where('package_transactions.status', 1)
                ->where('package_transactions.payment_status', 2)
                ->where('subscription_packages.subscription_type', 'topup')
                ->select('subscription_packages.package_title', 'subscription_packages.package_term', 'subscription_packages.package_duration', 'subscription_packages.price')
                ->orderByDesc('package_transactions.id')
                ->first();

            $currency = Currency::select('symbol')
                ->where('is_default', 1)
                ->where('status', 1)
                ->first();

            $data['currency'] = $currency ? $currency->symbol : '$';

            return [
                'code' => 200,
                'message' => __('Detail retrieved successfully.'),
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('Error! while getting details'),
                'error' => $e->getMessage()
            ];
        }
    }

    public function calendarIndex(): array
    {
        $data = [];
        $data['user'] = Auth::id();

        // Get today's bookings for all users
        $users = User::with(['bookings' => function ($query) {
            $query->whereDate('created_at', now()->toDateString());
        }])->get();

        // Get staff's service branches and services
        $branchIds = ServiceStaff::where('staff_id', $data['user'])
            ->pluck('service_branch_id')
            ->toArray();

        $serviceIds = ServiceBranch::whereIn('id', $branchIds)
            ->pluck('service_id')
            ->toArray();

        $staffCategory = UserDetail::select('category_id')
            ->where('user_id', $data['user'])
            ->first();

        $services = Service::whereIn('id', $serviceIds)
            ->where('source_category', $staffCategory->category_id)
            ->get();

        // Get latest customers
        $latestBookingUserIds = Bookings::latest()
            ->take(20)
            ->pluck('user_id')
            ->toArray();

        $customers = empty($latestBookingUserIds)
            ? User::select('id', 'name')
                ->where('user_type', 3)
                ->latest()
                ->take(5)
                ->get()
            : User::select('id', 'name')
                ->where('user_type', 3)
                ->whereIn('id', $latestBookingUserIds)
                ->get();

        $branchIds = BranchStaffs::select('branch_id')
            ->where('staff_id', $data['user'])
            ->get();

        return [
            'users' => $users,
            'data' => $data,
            'customers' => $customers,
            'services' => $services,
            'branchIds' => $branchIds
        ];
    }

    public function getStaffBookings(Request $request): array
    {
        $staffId = $request->input('staffid');
        $bookings = [];

        if ($staffId) {
            $userdata = UserDetail::where('user_id', $staffId)
                ->select('category_id', 'parent_id')
                ->first();

            $branchIds = ServiceStaff::where('staff_id', $staffId)
                ->pluck('service_branch_id')
                ->toArray();

            $serviceIds = ServiceBranch::whereIn('id', $branchIds)
                ->pluck('service_id')
                ->toArray();

            $bookings = Bookings::select(
                    'bookings.id',
                    'bookings.booking_status',
                    'bookings.created_at',
                    'bookings.product_id',
                    'products.source_name',
                    'users.name as user_name',
                    'users.email',
                    'bookings.user_id',
                    'users.phone_number',
                    'bookings.total_amount',
                    'bookings.slot_id',
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
                    DB::raw("(SELECT source_Values FROM products_meta WHERE products_meta.product_id = bookings.product_id AND source_key = 'product_image' LIMIT 1) as productimage")
                )
                ->leftJoin('products', 'products.id', '=', 'bookings.product_id')
                ->leftJoin('users', 'users.id', '=', 'bookings.user_id')
                ->where('staff_id', $staffId)
                ->with([
                    'user.userDetails',
                    'product.createdBy.userDetails',
                ])
                ->whereIn('products.id', $serviceIds)
                ->groupBy('bookings.id', 'bookings.booking_status', 'bookings.product_id', 'bookings.created_at', 'products.source_name', 'users.name', 'users.email', 'bookings.user_id', 'users.phone_number', 'bookings.total_amount', 'bookings.slot_id')
                ->get()
                ->map(function ($booking) {
                    $statusColors = [
                        1 => '#FF008A', // Open
                        2 => '#5625E8', // In progress
                        3 => '#E70D0D', // Cancelled
                        4 => '#856404', // Refund Initiated
                        5 => '#03C95A', // Completed
                        6 => '#03C95A', // Order Completed
                        7 => '#03C95A', // Refund Completed
                        8 => '#E70D0D'  // Customer Cancelled
                    ];

                    $currency = Currency::select('symbol')
                        ->where('is_default', 1)
                        ->where('status', 1)
                        ->first();
                    $currencySymbol = $currency ? $currency->symbol : '$';

                    $slotTime = ModelsProductmeta::select('source_Values')
                        ->where('id', $booking->slot_id)
                        ->first();

                    $formattedTime = '';
                    if ($slotTime && strpos($slotTime->source_Values, ' - ') !== false) {
                        $timeRange = explode(' - ', $slotTime->source_Values);
                        if (count($timeRange) === 2 && strtotime($timeRange[0]) && strtotime($timeRange[1])) {
                            $startTime = (new DateTime($timeRange[0]))->format('h:i A');
                            $endTime = (new DateTime($timeRange[1]))->format('h:i A');
                            $formattedTime = "$startTime - $endTime";
                        }
                    }

                    $productImage = UserDetail::select('profile_image')
                        ->where('user_id', $booking->user_id)
                        ->first();

                    $baseUrl = url('/storage/profile');
                    $userImage = $productImage && $productImage->profile_image
                        ? $baseUrl . '/' . $productImage->profile_image
                        : "N/A";

                    return [
                        'title' => $booking->source_name,
                        'start' => $booking->created_at,
                        'end' => $booking->created_at,
                        'provider' => $booking['product']['createdBy']['userDetails']->first_name . ' ' .
                                    ($booking['product']['createdBy']['userDetails']->last_name ?? ""),
                        'amount' => $currencySymbol . $booking->total_amount,
                        'location' => $booking->user_city ?? "-",
                        'user' => isset($booking['user']['userDetails'])
                            ? (($booking['user']['userDetails']->first_name ?? '') . ' ' .
                            ($booking['user']['userDetails']->last_name ?? ''))
                            : $booking->user_name,
                        'phone' => $booking->phone_number ?? "-",
                        'email' => $booking->email,
                        'id' => $booking->id,
                        'slot_time' => $formattedTime,
                        'status' => $booking->booking_status_label,
                        'status_no' => $booking->booking_status,
                        'color' => $statusColors[$booking->booking_status] ?? '#FF008A',
                        'userimage' => $userImage
                    ];
                });
        }

        return $bookings->toArray();
    }

    public function getLanguage(): mixed
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

    public function providerStaffLimit(Request $request): array
    {
        $id = Auth::id() ?? $request->user_id;

        $packageTrx = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('package_transactions.provider_id', $id)
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('subscription_packages.subscription_type', 'regular')
            ->select(
                'package_transactions.id',
                'package_transactions.provider_id',
                'subscription_packages.number_of_staff',
                'package_transactions.package_id',
                'package_transactions.trx_date',
                'package_transactions.end_date'
            )
            ->orderByDesc('package_transactions.id')
            ->first();

        $topup = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('package_transactions.provider_id', $id)
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('subscription_packages.subscription_type', 'topup')
            ->select(
                'package_transactions.id',
                'package_transactions.provider_id',
                'subscription_packages.number_of_staff',
                'package_transactions.package_id',
                'package_transactions.trx_date',
                'package_transactions.end_date'
            )
            ->orderByDesc('package_transactions.id')
            ->first();

        if (!$packageTrx && !$topup) {
            return [
                'code' => 200,
                'success' => true,
                'no_package' => true,
                'message' => 'No Subscription and Topup Found.',
            ];
        }

        $staffCount = UserDetail::where('parent_id', $id)->count();
        $currentDate = now();

        $packageEndDateCount = $packageTrx ? $currentDate->diffInDays(Carbon::parse($packageTrx->end_date), false) : -999;
        $topupEndDateCount   = $topup ? $currentDate->diffInDays(Carbon::parse($topup->end_date), false) : -999;

        $number_of_staff = 0;

        if ($packageEndDateCount > -1) {
            $number_of_staff += $packageTrx->number_of_staff;
        }

        if ($topupEndDateCount > -1) {
            $number_of_staff += $topup->number_of_staff;
        }

        if ($packageEndDateCount <= -1 && $topupEndDateCount <= -1) {
            return [
                'code' => 200,
                'success' => true,
                'sub_end' => true,
                'message' => 'Subscription or Topup Ended.',
            ];
        }

        if ($staffCount >= $number_of_staff) {
            return [
                'code' => 200,
                'success' => true,
                'sub_count_end' => true,
                'message' => 'Subscription or Topup limit Ended.',
            ];
        }

        return [
            'code' => 200,
            'success' => true,
            'message' => 'Subscription or Topup found.',
        ];
    }

    public function providerStaffLimitApi(Request $request): array
    {
        $id = $request->provider_id;

        $packageTrx = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('package_transactions.provider_id', $id)
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('subscription_packages.subscription_type', 'regular')
            ->select(
                'package_transactions.id',
                'package_transactions.provider_id',
                'subscription_packages.number_of_staff',
                'package_transactions.package_id',
                'package_transactions.trx_date',
                'package_transactions.end_date'
            )
            ->orderByDesc('package_transactions.id')
            ->first();

        $topup = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('package_transactions.provider_id', $id)
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('subscription_packages.subscription_type', 'topup')
            ->select(
                'package_transactions.id',
                'package_transactions.provider_id',
                'subscription_packages.number_of_staff',
                'package_transactions.package_id',
                'package_transactions.trx_date',
                'package_transactions.end_date'
            )
            ->orderByDesc('package_transactions.id')
            ->first();

        if (!$packageTrx && !$topup) {
            return [
                'code' => 422,
                'success' => true,
                'no_package' => true,
                'message' => 'No Subscription and Topup Found.',
            ];
        }

        $staffCount = UserDetail::where('parent_id', $id)->count();
        $currentDate = now();

        $packageEndDateCount = $packageTrx ? $currentDate->diffInDays(Carbon::parse($packageTrx->end_date), false) : -999;
        $topupEndDateCount   = $topup ? $currentDate->diffInDays(Carbon::parse($topup->end_date), false) : -999;

        $number_of_staff = 0;

        if ($packageEndDateCount > -1) {
            $number_of_staff += $packageTrx->number_of_staff;
        }

        if ($topupEndDateCount > -1) {
            $number_of_staff += $topup->number_of_staff;
        }

        if ($packageEndDateCount <= -1 && $topupEndDateCount <= -1) {
            return [
                'code' => 422,
                'success' => true,
                'sub_end' => true,
                'message' => 'Subscription or Topup Ended.',
            ];
        }

        if ($staffCount >= $number_of_staff) {
            return [
                'code' => 422,
                'success' => true,
                'sub_count_end' => true,
                'message' => 'Subscription or Topup limit Ended.',
            ];
        }

        return [
            'code' => 200,
            'success' => true,
            'message' => 'Subscription or Topup found.',
        ];
    }

    protected function getPackageTransaction(int $providerId, string $type)
    {
        return PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('package_transactions.provider_id', $providerId)
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('subscription_packages.subscription_type', $type)
            ->select(
                'package_transactions.id',
                'package_transactions.provider_id',
                'subscription_packages.number_of_staff',
                'package_transactions.package_id',
                'package_transactions.trx_date',
                'package_transactions.end_date'
            )
            ->orderByDesc('package_transactions.id')
            ->first();
    }

    public function adminStaff(): array
    {
        $countries = DB::table('countries')->get();
        return ['countries' => $countries];
    }

    public function staffStatusChange(Request $request): array
    {
        try {
            User::where('id', $request->id)->update(['status' => $request->status]);

            return [
                'code' => 200,
                'message' => __('Staff status changed successfully.', [], $request->language_code ?? 'en')
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => 'Error! while changing staff status'
            ];
        }
    }

    public function getCustomer(Request $request): array
    {
        $userId = $request->input('user_id');
        $authUserId = $request->input('auth_user_id');

        $user = User::find($userId);
        $userDetails = $user ? UserDetail::where('user_id', $userId)->first() : null;
        $authUser = User::find($authUserId);
        $authUserDetails = $authUser ? UserDetail::where('user_id', $authUserId)->first() : null;

        if (!$user || !$authUser) {
            return ['error' => 'User(s) not found'];
        }

        return [
            'userInfo' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'first_name' => $userDetails->first_name ?? '',
                'last_name' => $userDetails->last_name ?? '',
                'profile_image' => $userDetails->profile_image
                    ? asset('storage/profile/' . $userDetails->profile_image)
                    : asset('assets/img/profile-default.png'),
            ],
            'staffInfo' => [
                'name' => $authUser->name,
                'email' => $authUser->email,
                'phone_number' => $authUser->phone_number,
                'first_name' => $authUserDetails->first_name ?? '',
                'last_name' => $authUserDetails->last_name ?? '',
                'profile_image' => $authUserDetails->profile_image
                    ? asset('storage/profile/' . $authUserDetails->profile_image)
                    : asset('assets/img/profile-default.png'),
            ]
        ];
    }

    public function getStaffSlot(Request $request): array
    {
        $selectedDate = Carbon::createFromFormat('d-m-Y', $request->selected_date);

        if ($selectedDate->isPast()) {
            return ['slot_message' => 'Please select a current or future date.'];
        }

        $dayOfWeek = strtolower($selectedDate->format('l'));
        $serviceInfo = Service::select('source_name', 'source_code', 'source_price', 'price_type')
            ->where('id', $request->service_id)
            ->first();

        $slots = Productmeta::where('product_id', $request->service_id)
            ->where(function ($query) use ($dayOfWeek) {
                for ($i = 1; $i <= 10; $i++) {
                    $query->orWhere('source_key', "{$dayOfWeek}_slot_{$i}");
                }
            })
            ->get(['id', 'source_key', 'source_Values']);

        $formattedDate = $selectedDate->format('Y-m-d');
        $slotData = [];

        foreach ($slots as $slot) {
            $isBooked = Bookings::where('slot_id', $slot->id)
                ->where('booking_date', $formattedDate)
                ->where('staff_id', $request->staff_id)
                ->whereIn('booking_status', [1, 2])
                ->exists();

            $slotData[] = [
                'id' => $slot->id,
                'source_key' => $slot->source_key,
                'source_values' => $slot->source_Values,
                'slot_status' => $isBooked ? 'no' : 'yes',
            ];
        }

        return [
            'slot' => $slotData,
            'service_info' => $serviceInfo,
            'message' => 'Date processed successfully.'
        ];
    }

    public function payment(Request $request): array
    {
        if ($request->service_id === null) {
            return ['message' => 'User not found.'];
        }

        $formattedBookingDate = Carbon::createFromFormat('d-m-Y', $request->input('booking_date'))->format('Y-m-d');
        $authId = Auth::id();
        $user = User::find($request->user_id);
        $userDetails = UserDetail::where('user_id', $request->user_id)->first();

        $data = [
            "product_id" => $request->input('service_id'),
            "branch_id" => $request->input('branch_id') ?? 0,
            "staff_id" => $authId,
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
            "user_city" => $request->input('user_city'),
            "user_state" => $request->input('user_state'),
            "user_address" => $userDetails->address,
            "user_postal" => $userDetails->postal_code,
            "note" => $request->input('note'),
            "payment_type" => 5,
            "payment_status" => 1,
            "service_qty" => 1,
            "service_amount" => $request->input('total_amount'),
            "total_amount" => $request->input('total_amount'),
            "created_at" => $formattedBookingDate,
        ];

        $save = Bookings::create($data);

        return $save
            ? ['code' => 200, 'message' => 'Booking successfully created!', 'data' => []]
            : ['error' => 'Failed to create booking'];
    }
}