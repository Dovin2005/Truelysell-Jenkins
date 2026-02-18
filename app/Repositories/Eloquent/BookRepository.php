<?php

namespace App\Repositories\Eloquent;

use App\Models\Contact;
use App\Repositories\Contracts\BookRepositoryInterface;
use Modules\Categories\app\Models\Categories;
use Modules\Product\app\Models\Product;
use Modules\Service\app\Models\Productmeta;
use Modules\Product\app\Models\Book;
use Modules\Product\app\Models\Rating;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\GlobalSetting\app\Models\Currency;
use App\Models\WalletHistory;
use Illuminate\Support\Facades\Cache;
use App\Models\Bookings;
use App\Models\Branches;
use App\Models\BranchStaffs;
use App\Models\ServiceBranch;
use App\Models\ServiceStaff;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Storage;
use Modules\Product\app\Models\Productmeta as ModelsProductmeta;
use Modules\Service\app\Models\AdditionalService;
use Modules\Service\app\Models\Service;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Mollie\Laravel\Facades\Mollie;
use Illuminate\Support\Facades\Auth;
use DateTime;
use DB;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\BankTransferController as BankTransferController;
//Addons -->
use Modules\PaymentGateway\app\Http\Controllers\RazerpayController as RazorpayController;
use Modules\PaymentGateway\app\Http\Controllers\AuthorizeNetController as AuthorizeNetController;
use Modules\PaymentGateway\app\Http\Controllers\PayUController as PayUController;
use Modules\PaymentGateway\app\Http\Controllers\CashfreeController as CashfreeController;
use Modules\PaymentGateway\app\Http\Controllers\PaystackController as PaystackController;
use Modules\PaymentGateway\app\Http\Controllers\MercadoPagoController as MercadoPagoController;
use Modules\GoogleCalendarSync\app\Http\Controllers\GoogleCalendarSyncController;

//Addons <--
class BookRepository implements BookRepositoryInterface
{
    private $provider;
    public function __construct()
    {
        if (empty(env('PAYPAL_SANDBOX_CLIENT_ID')) || empty(env('PAYPAL_SANDBOX_CLIENT_SECRET'))) {
            $this->provider = null;
        } else {
            $this->provider = new PayPalClient();
            $this->provider->getAccessToken();
        }
    }

    public function productdetail(Request $request): View
    {
        $products = Product::query()->where('slug', '=', $request->slug)->first();
        $products_details = Productmeta::query()->where('product_id', '=', $products->id)->where('source_key', '=', 'product_image')->first();
        $c = "";
        $d = $products->id;
        $products_details1 = Productmeta::where(function ($query) {
            $query->where('source_key', '=', 'Fixed')
                ->orWhere('source_key', '=', 'Hourly')
                ->orWhere('source_key', '=', 'Squre-metter')
                ->orWhere('source_key', '=', 'Minute')
                ->orWhere('source_key', '=', 'Minitue');
        })->where(function ($query) use ($c, $d) {
            $query->where('product_id', '=', $d);
        })->first();
        $email = "";
        if (Auth::check()) {
            $email = Auth::user()->email;
        }
        $data = [
            'email' => $email,
        ];
        return view('servicedetail', compact('data', 'products', 'products_details', 'products_details1'));
    }

    public function serviceBooking(Request $request)
    {
        $serviceSlug = $request->slug;
        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $serviceId = $request->product_id;
        } else {
            $id = Service::select('id')->where('slug', $serviceSlug)->first();
            $serviceId = $id->id;
        }
        $service = Service::find($serviceId);
        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $userId = $request->user_id;
        } else {
            $userId = FacadesAuth::id();
        }

        $serviceDuration = Service::select('price_type')->where('id', $serviceId)->first();

        if ($serviceDuration) {
            if (in_array($serviceDuration->price_type, ['fixed', 'hourly', 'square-meter', 'square-feet'])) {
                $serviceDuration = 'hr';
            } else {
                $serviceDuration = 'min';
            }
        } else {
            $serviceDuration = 'min';
        }

        if (!$service) {
            return redirect()->back()->with('error', 'Service not found');
        }

        $productImages = Productmeta::where('product_id', $serviceId)
            ->where('source_key', 'product_image')
            ->whereNull('deleted_at')
            ->get();
        $validImage = $productImages->first(function ($img) {
            return !empty($img->source_Values) && file_exists(public_path('storage/' . $img->source_Values));
        });
        $productImage = $validImage->source_Values ?? null;
        $firstImage = $validImage
            ? url('storage/' . $productImage)
            : url('front/img/default-placeholder-image.png');

        $baseUrl = url('/storage');

        $additionalServices = AdditionalService::where('service_id', $serviceId)->get();
        $additionalServicesCount = str_pad($additionalServices->count(), 2, '0', STR_PAD_LEFT);
        $additionalServices->transform(function ($additional) {
            if ($additional->image && file_exists(public_path('storage/' . $additional->image))) {
                $additional->image = url('storage/' . $additional->image);
            } else {
                $additional->image = url('front/img/default-placeholder-image.png');
            }
            unset($additional->source_Values);
            return $additional;
        });

        $ratings = Rating::where('product_id', $serviceId)->get();
        $averageRating = $ratings->avg('rating') ?? 0;
        $averageRating = number_format($averageRating, 1);
        $ratingCount = $ratings->count() ?? 0;

        $branchDetails = ServiceBranch::select('branch_id')->where('service_id', $serviceId)->get();
        $branches = Branches::whereIn('id', $branchDetails->pluck('branch_id'))->get();
        $branchesCount = str_pad($branches->count(), 2, '0', STR_PAD_LEFT);
        $branches->transform(function ($branch) use ($serviceId) {
            $branchImage = $branch->branch_image && file_exists(public_path('storage/branch/' . $branch->branch_image));
            $branch->branch_image = $branchImage
                ? url('storage/branch/' . $branch->branch_image)
                : url('front/img/default-placeholder-image.png');

            $branchIDs = ServiceBranch::where('service_id', $serviceId)->where('branch_id', $branch->id)->pluck('id');

            $branch->staff_count = ServiceStaff::whereIn('service_branch_id', $branchIDs)
                ->whereIn('staff_id', function ($query) {
                    $query->select('id')->from('users')
                        ->whereNull('deleted_at');
                })
                ->count();

            return $branch;
        });

        $staff_count_status = $branches->some(function ($branch) {
            return $branch->staff_count > 0;
        }) ? 1 : 0;
        $paymentInfo = GlobalSetting::where('group_id', 13)
            ->whereIn('key', [
                'stripe_status',
                'paypal_status',
                'cod_status',
                'wallet_status',
                'mollie_status',
            ])->pluck('value', 'key')->toArray();

        $currecy_details = Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();
        $totalAmount = WalletHistory::where('user_id', $userId)->where('status', 'completed')->where('type', '1')->sum('amount');
        $totalAmountdebit = WalletHistory::where('user_id', $userId)->where('status', 'completed')->where('type', '2')->sum('amount');
        $wallet_total_Amount = $totalAmount - $totalAmountdebit;

        $data = [
            'service' => $service,
            'serviceDuration' => $serviceDuration,
            'averageRating' => $averageRating,
            'ratingCount' => $ratingCount,
            'branches' => $branches,
            'branchesCount' => $branchesCount,
            'additionalServices' => $additionalServices,
            'additionalServicesCount' => $additionalServicesCount,
            'currecy_details' => $currecy_details,
            'paymentInfo' => $paymentInfo,
            'wallet_total_Amount' => $wallet_total_Amount,
            'firstImage' => $firstImage,
        ];

        $couponModuleStatus = 0;

        $addonModuleExists = \App\Models\AddonModule::exists();

        if (!$addonModuleExists) {
            $couponModuleStatus = 0;
        } else {
            $couponModule = \App\Models\AddonModule::where('slug', 'coupons')->where('status', 1)->first();

            if ($couponModule) {
                $couponModuleStatus = 1;
            }
        }

        $couponData = collect();

        if ($couponModuleStatus == 1 && Schema::hasTable('coupons')) {
            $currentDate = Carbon::today();
            $sourceSubcategoryId = $service->source_subcategory ?? 0;
            $sourceCategoryId = $service->source_category ?? 0;
            $serviceId = $service->id ?? 0;

            $couponData = collect(get_coupon_data($sourceCategoryId, $sourceSubcategoryId, $serviceId));
        }


        $bankInfo = [
            'bank_name'      => optional(GlobalSetting::where('key', 'bank_name')->first())->value,
            'account_number' => optional(GlobalSetting::where('key', 'account_number')->first())->value,
            'branch_code'    => optional(GlobalSetting::where('key', 'branch_code')->first())->value,
        ];
        $paymentmodeStatus = GlobalSetting::where('key', 'paymentmode_status')->value('value') ?? '0';


        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            return response()->json(['code' => "200", 'message' => __('Service information retrieved successfully.'), 'data' => $data], 200);
        } else {
            return view('user.booking.service_booking_one', compact(
                'service',
                'firstImage',
                'additionalServices',
                'averageRating',
                'ratingCount',
                'branches',
                'branchesCount',
                'additionalServicesCount',
                'currecy_details',
                'paymentInfo',
                'wallet_total_Amount',
                'serviceDuration',
                'staff_count_status',
                'couponModuleStatus',
                'couponData',
                'bankInfo',
            'paymentmodeStatus'

            ));
        }
    }

    public function serviceIndexBooking(Request $request)
    {

        $serviceSlug = $request->slug;
        $id = Service::select('id')->where('slug', $serviceSlug)->first();
        $serviceId = $id->id;
        $service = Service::find($serviceId);
        $userId = FacadesAuth::id();

        $serviceDuration = Service::select('price_type')->where('id', $serviceId)->first();

        if ($serviceDuration) {
            if (in_array($serviceDuration->price_type, ['fixed', 'hourly', 'square-meter', 'square-feet'])) {
                $serviceDuration = 'hr';
            } else {
                $serviceDuration = 'min';
            }
        } else {
            $serviceDuration = 'min';
        }

        if (!$service) {
            return redirect()->back()->with('error', 'Service not found');
        }

        $productMeta = ProductMeta::where('product_id', $serviceId)
            ->where('source_key', 'product_image')
            ->first();

        $baseUrl = url('/storage');
        $firstImage = $productMeta ? $baseUrl . '/' . $productMeta->source_Values : null;

        $additionalServices = AdditionalService::where('service_id', $serviceId)->get();
        $additionalServicesCount = str_pad($additionalServices->count(), 2, '0', STR_PAD_LEFT);
        $additionalServices->transform(function ($additional) use ($baseUrl) {
            if ($additional->image) {
                $additional->image = $baseUrl . '/' . $additional->image;
            }
            unset($additional->source_Values);
            return $additional;
        });

        $ratings = Rating::where('product_id', $serviceId)->get();
        $averageRating = $ratings->avg('rating') ?? 0;
        $averageRating = number_format($averageRating, 1);
        $ratingCount = $ratings->count() ?? 0;

        $branchDetails = ServiceBranch::select('branch_id')->where('service_id', $serviceId)->get();
        $branches = Branches::whereIn('id', $branchDetails->pluck('branch_id'))->get();
        $branchesCount = str_pad($branches->count(), 2, '0', STR_PAD_LEFT);
        $branches->transform(function ($branch) use ($baseUrl) {
            $branch->branch_image = $branch->branch_image ? $baseUrl . '/branch/' . $branch->branch_image : null;
            $branch->staff_count = BranchStaffs::where('branch_id', $branch->id)->count();
            return $branch;
        });

        $paymentInfo = GlobalSetting::where('group_id', 13)
            ->whereIn('key', [
                'stripe_status',
                'paypal_status',
                'cod_status',
                'wallet_status',
                'mollie_status',
            ])->pluck('value', 'key')->toArray();

        $currecy_details = Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();
        $totalAmount = WalletHistory::where('user_id', $userId)->where('status', 'completed')->where('type', '1')->sum('amount');
        $totalAmountdebit = WalletHistory::where('user_id', $userId)->where('status', 'completed')->where('type', '2')->sum('amount');
        $wallet_total_Amount = $totalAmount - $totalAmountdebit;

        $couponModuleStatus = 0;

        $addonModuleExists = \App\Models\AddonModule::exists();

        if (!$addonModuleExists) {
            $couponModuleStatus = 0;
        } else {
            $couponModule = \App\Models\AddonModule::where('slug', 'coupons')->where('status', 1)->first();

            if ($couponModule) {
                $couponModuleStatus = 1;
            }
        }

        $couponData = collect();
        if ($couponModuleStatus == 1 && Schema::hasTable('coupons')) {
            $sourceSubcategoryId = $service->source_subcategory ?? 0;
            $sourceCategoryId = $service->source_category ?? 0;
            $serviceId = $service->id ?? 0;

            $couponData = collect(get_coupon_data($sourceCategoryId, $sourceSubcategoryId, $serviceId));
        }

        $bankInfo = [
            'bank_name'      => optional(GlobalSetting::where('key', 'bank_name')->first())->value,
            'account_number' => optional(GlobalSetting::where('key', 'account_number')->first())->value,
            'branch_code'    => optional(GlobalSetting::where('key', 'branch_code')->first())->value,
        ];
        $paymentmodeStatus = GlobalSetting::where('key', 'paymentmode_status')->value('value') ?? '0';

        return view('user.booking.service_booking_two', compact(
            'service',
            'firstImage',
            'additionalServices',
            'averageRating',
            'ratingCount',
            'branches',
            'branchesCount',
            'additionalServicesCount',
            'currecy_details',
            'paymentInfo',
            'wallet_total_Amount',
            'serviceDuration',
            'couponModuleStatus',
            'couponData',
            'bankInfo',
            'paymentmodeStatus'
        ));
    }

    public function getStaffs(Request $request)
    {
        $branchId = $request->input('branch_id');
        $serviceId = $request->input('service_id');
        $servicebranch = ServiceBranch::select('id')->where('service_id', $serviceId)->where('branch_id', $branchId)->first();
        $branchStaffs = ServiceStaff::where('service_branch_id', $servicebranch->id)->get();
        $staffs = [];

        foreach ($branchStaffs as $branchStaff) {
            $userDetails = UserDetail::where('user_id', $branchStaff->staff_id)->first();

            if ($userDetails) {
                $user = User::where('id', $userDetails->user_id)->first();
                $email = $user ? $user->email : null;

                if ($email) {
                    $emailParts = explode('@', $email);
                    $email = 'xxxx@' . $emailParts[1]; // Replaces the username part with 'xxxx'
                }

                $profileImage = $userDetails->profile_image && file_exists(public_path('storage/profile/' . $userDetails->profile_image));
                $profileImageUrl = $profileImage ? url('storage/profile/' . $userDetails->profile_image) : url('assets/img/profile-default.png');

                $bookingCount = Bookings::where('staff_id', $branchStaff->staff_id)
                    ->whereIn('booking_status', [5, 6])
                    ->count();

                $staffs[] = [
                    'user' => [
                        'user_id' => $userDetails->user_id,
                        'first_name' => $userDetails->first_name,
                        'last_name' => $userDetails->last_name,
                        'mobile_number' => $userDetails->mobile_number,
                        'email' => $email,
                        'gender' => $userDetails->gender,
                        'dob' => $userDetails->dob,
                        'bio' => $userDetails->bio,
                        'profile_image' => $profileImageUrl,
                    ],
                    'services_count' => $bookingCount ?? 0,
                    'rating' => $branchStaff->rating ?? 0,
                ];
            }
        }

        $staffCount = $branchStaffs->count();

        $data = [
            'staffs' => $staffs,
            'staff_count' => str_pad($staffCount, 2, '0', STR_PAD_LEFT),
        ];

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            return response()->json(['code' => "200", 'message' => __('Staff information retrieved successfully.'), 'data' => $data], 200);
        } else {
            return response()->json([
                'staffs' => $staffs,
                'staff_count' => str_pad($staffCount, 2, '0', STR_PAD_LEFT),
            ]);
        }
    }

    public function getInfo(Request $request)
    {
        $branchId = $request->input('branch_id');
        $staffId = $request->input('staff_id');

        $baseUrl = url('/storage');

        $branchInfo = Branches::select('branch_name', 'branch_email', 'branch_image')
            ->where('id', $branchId)
            ->first();

        $staffInfo = UserDetail::select('first_name', 'last_name', 'profile_image')
            ->where('user_id', $staffId)
            ->first();

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $authId = $request->user_id;
        } else {
            $authId = FacadesAuth::id();
        }

        $userDetail = UserDetail::select('first_name', 'last_name', 'address', 'city', 'state', 'postal_code')
            ->where('user_id', $authId)
            ->first();

        $states = json_decode(file_get_contents(public_path('states.json')), true);
        $cities = json_decode(file_get_contents(public_path('cities.json')), true);

        $stateMap = collect($states['states'])->pluck('name', 'id')->all();
        $cityMap = collect($cities['cities'])->pluck('name', 'id')->all();

        $stateName = $stateMap[$userDetail->state ?? null] ?? null;
        $cityName = $cityMap[$userDetail->city ?? null] ?? null;

        $user = User::select('email', 'phone_number')
            ->where('id', $authId)
            ->first();

        // Get additional service IDs
        $addServiceIds = $request->input('addService_ids', []); // Default to an empty array if not provided

        // Fetch additional services only if IDs are provided
        $additionalServices = !empty($addServiceIds)
            ? AdditionalService::whereIn('id', $addServiceIds)->get()
            : collect(); // Return an empty collection if no IDs are provided

        $response = [
            'branch_info' => $branchInfo ? [
                'branch_name' => $branchInfo->branch_name,
                'branch_email' => $branchInfo->branch_email,
                'branch_image_url' => $baseUrl . '/branch/' . $branchInfo->branch_image,
            ] : (object) [],

            'staff_info' => $staffInfo ? [
                'first_name' => $staffInfo->first_name ?? "",
                'last_name' => $staffInfo->last_name ?? "",
                'profile_image_url' => $staffInfo->profile_image ? $baseUrl . '/profile/' . $staffInfo->profile_image : "",
            ] : (object) [],

            'user_info' => [
                'first_name' => $userDetail->first_name ?? "",
                'last_name' => $userDetail->last_name ?? "",
                'address' => $userDetail->address ?? null,
                'city' => $cityName ?? "",
                'state' => $stateName ?? "",
                'postal_code' => $userDetail->postal_code ?? null,
                'email' => $user->email ?? "",
                'phone_number' => $user->phone_number ?? "",
            ],
            'addService_info' => $additionalServices->isNotEmpty()
                ? $additionalServices->map(function ($service) {
                    return [
                        'symbol'  => getDefaultCurrencySymbol(),
                        'name'  => $service->name,
                        'price' => $service->price,
                    ];
                })->values()
                : [],
        ];

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            return response()->json(['code' => "200", 'message' => __('User information retrieved successfully.'), 'data' => $response], 200);
        } else {
            return response()->json($response);
        }
    }

    public function getPersonalInfo(Request $request)
    {
        $authId = FacadesAuth::id();

        $userDetail = UserDetail::select('first_name', 'last_name', 'address', 'city', 'state', 'postal_code')
            ->where('user_id', $authId)
            ->first();

        $states = json_decode(file_get_contents(public_path('states.json')), true);
        $cities = json_decode(file_get_contents(public_path('cities.json')), true);

        $stateMap = collect($states['states'])->pluck('name', 'id')->all();
        $cityMap = collect($cities['cities'])->pluck('name', 'id')->all();

        $stateName = $stateMap[$userDetail->state ?? null] ?? null;
        $cityName = $cityMap[$userDetail->city ?? null] ?? null;

        $user = User::select('email', 'phone_number')
            ->where('id', $authId)
            ->first();

        // Get additional service IDs
        $addServiceIds = $request->input('addService_ids', []); // Default to an empty array if not provided

        // Fetch additional services only if IDs are provided
        $additionalServices = !empty($addServiceIds)
            ? AdditionalService::whereIn('id', $addServiceIds)->get()
            : collect();

        $response = [
            'user_info' => [
                'first_name' => $userDetail->first_name ?? "",
                'last_name' => $userDetail->last_name ?? "",
                'address' => $userDetail->address ?? "",
                'city' => $cityName ?? "",
                'state' => $stateName ?? "",
                'postal_code' => $userDetail->postal_code ?? "",
                'email' => $user->email ?? "",
                'phone_number' => $user->phone_number ?? "",
            ],
            'addService_info' => $additionalServices->map(function ($service) {
                return [
                    'symbol'  => getDefaultCurrencySymbol(),
                    'name'  => $service->name,
                    'price' => $service->price,
                ];
            })
        ];

        return response()->json($response);
    }

    public function getSlot(Request $request)
    {
        $validatedData = $request->validate([
            'service_id' => 'required|integer',
            'selected_date' => 'required|date_format:d-m-Y',
        ]);

        $selectedDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->selected_date);
        $dayOfWeek = strtolower($selectedDate->format('l'));

        $slots = ModelsProductmeta::where('product_id', $request->service_id)
            ->where('source_key', 'LIKE', "{$dayOfWeek}_slot_%")
            ->get(['id', 'source_key', 'source_Values']);

        $formattedDate = $selectedDate->format('Y-m-d');
        $slotData = [];

        foreach ($slots as $slot) {
            $isBooked = Bookings::where('slot_id', $slot->id)
                ->where('booking_date', $formattedDate)
                ->whereIn('booking_status', [1, 2])
                ->exists();

            // Get current time
            $currentTime = \Carbon\Carbon::now()->format('H:i');

            // Extract the slot time range and check if it's in the past
            $slotTimeRange = explode(' - ', $slot->source_Values);
            $slotStartTime = \Carbon\Carbon::createFromFormat('H:i', trim($slotTimeRange[0]));

            // Determine slot status
            if ($isBooked) {
                $stas = 'no';
                continue;
            } elseif ($selectedDate->isToday() && $slotStartTime->format('H:i') < $currentTime) {
                // If the slot is for today and its start time is in the past, set status to "no"
                $stas = 'no';
                continue;
            } else {
                $stas = 'yes';
            }

            $slotData[] = [
                'id' => $slot->id,
                'source_key' => $slot->source_key,
                'source_values' => $slot->source_Values,
                'source_values2' => $slot->source_Values,
                'slot_status' => 'yes',
            ];
        }

        if (empty($slotData)) {
            $futureAvailableDates = [];
            $formattedSlotAvailability = [];
            $currentDate = $selectedDate->copy();
            $maxDaysToCheck = 8; // Limit to 8 days
            $daysChecked = 0;

            while (count($futureAvailableDates) < 3 && $daysChecked < $maxDaysToCheck) {
                $currentDate->addDay();
                $daysChecked++;

                $currentDayOfWeek = strtolower($currentDate->format('l'));

                $futureSlots = ModelsProductmeta::where('product_id', $request->service_id)
                    ->where(function ($query) use ($currentDayOfWeek) {
                        for ($i = 1; $i <= 10; $i++) {
                            $query->orWhere('source_key', "{$currentDayOfWeek}_slot_{$i}");
                        }
                    })
                    ->exists();

                $dateFormat = GlobalSetting::where('group_id', 31)
                    ->where('key', 'date_format_view')
                    ->pluck('value', 'key')->first();

                if (!$dateFormat) {
                    $dateFormat = 'd-m-Y';
                }

                if ($futureSlots) {
                    $futureAvailableDates[] = $currentDate->format($dateFormat);
                    $formattedSlotAvailability[] = [
                        'value' => $currentDate->format('d-m-Y'),
                        'label' => $currentDate->format($dateFormat),
                    ];
                }
            }

            if (empty($futureAvailableDates)) {
                return response()->json([
                    'slot' => [],
                    'slot_availability' => [],
                    'formatted_slot_availability' => [],
                    'message' => 'No slots available for the given date or in the next 8 days.',
                ]);
            }

            return response()->json([
                'slot' => [],
                'slot_availability' => $futureAvailableDates,
                'formatted_slot_availability' => $formattedSlotAvailability,
                'message' => 'No slots available for the given date. Future availability provided.',
            ]);
        }

        return response()->json([
            'slot' => $slotData,
            'slot_availability' => [],
            'formatted_slot_availability' => [],
            'message' => 'Date processed successfully.',
        ]);
    }

    public function getSlots(Request $request)
    {
        $validatedData = $request->validate([
            'service_id' => 'required|integer',
            'selected_date' => 'required|date_format:d-m-Y',
        ]);

        $selectedDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->selected_date);
        $dayOfWeek = strtolower($selectedDate->format('l'));

        $slots = ModelsProductmeta::where('product_id', $request->service_id)
            ->where('source_key', 'LIKE', "{$dayOfWeek}_slot_%")
            ->get(['id', 'source_key', 'source_Values']);

        $formattedDate = $selectedDate->format('Y-m-d');
        $slotData = [];

        foreach ($slots as $slot) {
            $isBooked = Bookings::where('slot_id', $slot->id)
                ->where('booking_date', $formattedDate)
                ->whereIn('booking_status', [1, 2])
                ->exists();

            $ispaid = Bookings::where('slot_id', $slot->id)
                ->where('booking_date', $formattedDate)
                ->where('payment_status', 2)
                ->count();

            // Get current time
            $currentTime = \Carbon\Carbon::now()->format('H:i');

            // Extract the slot time range and check if it's in the past
            $slotTimeRange = explode(' - ', $slot->source_Values);
            $slotStartTime = \Carbon\Carbon::createFromFormat('H:i', trim($slotTimeRange[0]));

            // Determine slot status
            if ($isBooked) {
                $stas = 'no';
                continue;
            } elseif ($selectedDate->isToday() && $slotStartTime->format('H:i') < $currentTime) {
                // If the slot is for today and its start time is in the past, set status to "no"
                $stas = 'no';
                continue;
            } else {
                $stas = 'yes';
            }

            $slotData[] = [
                'id' => $slot->id,
                'source_key' => $slot->source_key,
                'source_values' => $slot->source_Values,
                'source_values2' => $slot->source_Values,
                'slot_status' => 'yes',
            ];
        }

        if (empty($slotData)) {
            $futureAvailableDates = [];
            $formattedSlotAvailability = [];
            $currentDate = $selectedDate->copy();
            $maxDaysToCheck = 8; // Limit to 8 days
            $daysChecked = 0;

            while (count($futureAvailableDates) < 3 && $daysChecked < $maxDaysToCheck) {
                $currentDate->addDay();
                $daysChecked++;

                $currentDayOfWeek = strtolower($currentDate->format('l'));

                $futureSlots = ModelsProductmeta::where('product_id', $request->service_id)
                    ->where(function ($query) use ($currentDayOfWeek) {
                        for ($i = 1; $i <= 10; $i++) {
                            $query->orWhere('source_key', "{$currentDayOfWeek}_slot_{$i}");
                        }
                    })
                    ->exists();

                $dateFormat = GlobalSetting::where('group_id', 31)
                    ->where('key', 'date_format_view')
                    ->pluck('value', 'key')->first();

                if (!$dateFormat) {
                    $dateFormat = 'd-m-Y';
                }

                if ($futureSlots) {
                    $futureAvailableDates[] = $currentDate->format($dateFormat);
                    $formattedSlotAvailability[] = [
                        'value' => $currentDate->format('d-m-Y'),
                        'label' => $currentDate->format($dateFormat),
                    ];
                }
            }

            if (empty($futureAvailableDates)) {
                return response()->json([
                    'slot' => [],
                    'slot_availability' => [],
                    'formatted_slot_availability' => [],
                    'message' => 'No slots available for the given date or in the next 8 days.',
                ]);
            }

            return response()->json([
                'slot' => [],
                'slot_availability' => $futureAvailableDates,
                'formatted_slot_availability' => $formattedSlotAvailability,
                'message' => 'No slots available for the given date. Future availability provided.',
            ]);
        }

        return response()->json([
            'slot' => $slotData,
            'slot_availability' => [],
            'formatted_slot_availability' => [],
            'message' => 'Date processed successfully.',
        ]);
    }


    public function getSlotInfo(Request $request)
    {
        $validatedData = $request->validate([
            'slot_id' => 'nullable|integer',
            'selected_date' => 'required|date_format:d-m-Y',
        ]);

        $slot = null;

        if ($request->has('slot_id') && $request->slot_id) {
            $slot = ModelsProductmeta::find($request->slot_id);

            if (!$slot) {
                return response()->json([
                    'message' => 'Slot not found.',
                ], 404);
            }
        }

        $dateFormat = GlobalSetting::where('group_id', 31)
            ->where('key', 'date_format_view')
            ->pluck('value', 'key')->first();

        if (!$dateFormat) {
            $dateFormat = 'd-m-Y';
        }

        $selectedDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->selected_date)
            ->format($dateFormat);

        $fromTime = '';
        $toTime = '';
        $formattedSourceValues = '';

        if ($slot) {
            $times = explode(' - ', $slot->source_Values);

            try {
                $fromTime = \Carbon\Carbon::createFromFormat('H:i', trim($times[0]))->format('H:i:s');
                $toTime = \Carbon\Carbon::createFromFormat('H:i', trim($times[1]))->format('H:i:s');

                $formattedTimes = array_map(function ($time) {
                    return \Carbon\Carbon::createFromFormat('H:i', $time)->format('h:i A');
                }, $times);
            } catch (\Exception $e) {
                $fromTime = \Carbon\Carbon::createFromFormat('h:i A', trim($times[0]))->format('H:i:s');
                $toTime = \Carbon\Carbon::createFromFormat('h:i A', trim($times[1]))->format('H:i:s');

                $formattedTimes = array_map(function ($time) {
                    return \Carbon\Carbon::createFromFormat('h:i A', $time)->format('h:i A');
                }, $times);
            }

            $formattedSourceValues = implode(' - ', $formattedTimes);
        }

        $selectedDateSlot = $selectedDate . ' at ' . $formattedSourceValues;

        $data = [
            'slot' => $slot ? [  // If slot exists, return slot data, else empty array
                'id' => $slot->id,
                'source_key' => $slot->source_key,
                'source_Values' => $formattedSourceValues,
            ] : [],
            'from_time' => $fromTime,  // Return from_time as N/A or actual time
            'to_time' => $toTime,      // Return to_time as N/A or actual time
            'selected_date' => $selectedDate,
            'selected_date_slot' => $selectedDateSlot
        ];

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            return response()->json(['code' => "200", 'message' => __('Slot information retrieved successfully.'), 'data' => $data], 200);
        } else {
            return response()->json([
                'slot' => $slot ? [  // If slot exists, return slot data, else empty array
                    'id' => $slot->id,
                    'source_key' => $slot->source_key,
                    'source_Values' => $formattedSourceValues,
                ] : [],
                'from_time' => $fromTime,  // Return from_time as N/A or actual time
                'to_time' => $toTime,      // Return to_time as N/A or actual time
                'selected_date' => $selectedDate,
                'selected_date_slot' => $selectedDateSlot,
                'message' => 'Slot information retrieved successfully.'
            ]);
        }
    }

    public function getPayout(Request $request)
    {
        $validatedData = $request->validate([
            'service_id' => 'required|integer',
        ]);

        $authId = FacadesAuth::id();

        $service = Service::find($request->service_id);

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            if (!$service) {
                return response()->json(['code' => "404", 'message' => __('Service not found'), 'data' => []], 404);
            }
        }

        $sourcePrice = $service->source_price;
        $currecy_details = Cache::remember('currecy_details', 86400, function () {
            return Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();
        });
        $taxSettings = GlobalSetting::where('group_id', 3)
            ->where('key', 'LIKE', 'tax_status_%')
            ->where('value', 1)
            ->pluck('key')->map(function ($key) {
                return str_replace('tax_status_', '', $key);
            });

        $taxDetails = [];

        foreach ($taxSettings as $taxIndex) {
            $taxType = GlobalSetting::where('key', "tax_type_{$taxIndex}")->value('value');
            $taxRate = GlobalSetting::where('key', "tax_rate_{$taxIndex}")->value('value');

            if ($taxRate) {
                $taxAmount = ($sourcePrice * $taxRate) / 100;
                $taxDetails[] = [
                    'tax_type' => $taxType,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                ];
            }
        }

        $addServiceIds = $request->addService_ids ?? [0];

        // $additionalServicesTotal = AdditionalService::whereIn('id', $addServiceIds)->sum('price');
        $additionalServicesTotal = round(AdditionalService::whereIn('id', $addServiceIds)->sum('price'));

        $totalTax = array_sum(array_column($taxDetails, 'tax_amount'));
        $totalAmount = $sourcePrice + $additionalServicesTotal + $totalTax;

        // Fetch Discount Details
        $discountSetting = GlobalSetting::where('key', 'discount_status')->where('value', 1)->first();
        $discountDetails = [];
        $discountAmount = 0;

        if ($discountSetting) {
            $discountType = Service::where('id', $request->service_id)->value('discount_type');
            $discountValue = Service::where('id', $request->service_id)->value('discount_value');

            if ($discountType === 'percentage') {
                $discountAmount = ($totalAmount * $discountValue) / 100;
            } elseif ($discountType === 'amount') {
                $discountAmount = min($totalAmount, $discountValue); // Ensure discount doesn't exceed total
            }

            $discountDetails = [
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
            ];
        }

        $couponDiscountAmount = 0;
        if ($request->coupon_type == 'percentage') {
            $couponValue = $request->coupon_value == 0 ? 1 : $request->coupon_value;
            $couponDiscountAmount = number_format(($totalAmount * ($couponValue / 100)), 2, '.', '');
        } else {
            $couponDiscountAmount = number_format($request->coupon_value ?? 0, 2, '.', '');
        }

        $finalTotalAmount = max(0, $totalAmount - ($discountAmount + $couponDiscountAmount));


        $WtotalAmount = WalletHistory::where('user_id', $authId)->where('status', 'completed')->where('type', '1')->sum('amount');
        $WtotalAmountdebit = WalletHistory::where('user_id', $authId)->where('status', 'completed')->where('type', '2')->sum('amount');
        $walletTotalAmoun = max(0, $WtotalAmount - $WtotalAmountdebit);
        $walletTotalAmount = number_format($walletTotalAmoun, 2, '.', '');


        $walletAvailable = ($walletTotalAmount >= $totalAmount) ? 'yes' : 'no';

        $data = [
            'sub_total' => $sourcePrice,
            'addService_total' => $additionalServicesTotal,
            'tax_used' => $taxDetails,
            'tax_total' => $totalTax,
            'discount_details' => $discountDetails,
            'discount_total' => $discountAmount,
            'coupon_details' => [
                'id' => $request->coupon_id,
                'coupon_code' => $request->coupon_code,
                'coupon_type' => $request->coupon_type,
                'coupon_value' => number_format($request->coupon_value ?? 0, 2, '.', ''),
                'coupon_discount_amount' => $couponDiscountAmount
            ],
            'total_amount' => $finalTotalAmount,
            'wallet_amount' => $walletTotalAmount,
            'wallet_availabe' => $walletAvailable,
            'currecy_details' => $currecy_details
        ];
        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            return response()->json(['code' => "200", 'message' => __('Payment information retrieved successfully.'), 'data' => $data], 200);
        } else {
            return response()->json([
                'sub_total' => $sourcePrice,
                'addService_total' => $additionalServicesTotal,
                'tax_used' => $taxDetails,
                'tax_total' => $totalTax,
                'discount_details' => $discountDetails,
                'discount_total' => $discountAmount,
                'coupon_details' => ($request->coupon_id && $request->coupon_code && $request->coupon_type && $request->coupon_value) ? [
                    'id' => $request->coupon_id,
                    'coupon_code' => $request->coupon_code,
                    'coupon_type' => $request->coupon_type,
                    'coupon_value' => number_format($request->coupon_value ?? 0, 2, '.', ''),
                    'coupon_discount_amount' => $couponDiscountAmount
                ] : [],
                'total_amount' => $finalTotalAmount,
                'wallet_amount' => $walletTotalAmount,
                'wallet_availabe' => $walletAvailable,
                'currecy_details' => $currecy_details,
                'message' => 'Payout calculated successfully.'
            ]);
        }
    }

    public function getPayoutApi(Request $request)
    {
        $validatedData = $request->validate([
            'service_id' => 'required|integer',
        ]);

        $authId = FacadesAuth::id();

        $service = Service::findOrFail($request->service_id);
        $sourcePrice = $service->source_price;
        $currecy_details = Cache::remember('currecy_details', 86400, function () {
            return Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();
        });
        $taxSettings = GlobalSetting::where('group_id', 3)
            ->where('key', 'LIKE', 'tax_status_%')
            ->where('value', 1)
            ->pluck('key')->map(function ($key) {
                return str_replace('tax_status_', '', $key);
            });

        $taxDetails = [];

        foreach ($taxSettings as $taxIndex) {
            $taxType = GlobalSetting::where('key', "tax_type_{$taxIndex}")->value('value');
            $taxRate = GlobalSetting::where('key', "tax_rate_{$taxIndex}")->value('value');

            if ($taxRate) {
                $taxAmount = ($sourcePrice * $taxRate) / 100;
                $taxDetails[] = [
                    'tax_type' => $taxType,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                ];
            }
        }

        $addServiceIds = $request->addService_ids ?? [0];

        $additionalServicesTotal = AdditionalService::whereIn('id', $addServiceIds)->sum('price');



        $totalTax = array_sum(array_column($taxDetails, 'tax_amount'));
        $totalAmount = $sourcePrice + $additionalServicesTotal + $totalTax;

        // Fetch Discount Details
        $discountSetting = GlobalSetting::where('key', 'discount_status')->where('value', 1)->first();
        $discountDetails = [];
        $discountAmount = 0;

        if ($discountSetting) {
            $discountType = Service::where('id', $request->service_id)->value('discount_type');
            $discountValue = Service::where('id', $request->service_id)->value('discount_value');

            if ($discountType === 'percentage') {
                $discountAmount = ($totalAmount * $discountValue) / 100;
            } elseif ($discountType === 'amount') {
                $discountAmount = min($totalAmount, $discountValue); // Ensure discount doesn't exceed total
            }

            $discountDetails = [
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
            ];
        }

        $couponDiscountAmount = $request->coupon_value ?? 0;
        $finalTotalAmount = $totalAmount - ($discountAmount + $couponDiscountAmount);


        $WtotalAmount = WalletHistory::where('user_id', $authId)->where('status', 'completed')->where('type', '1')->sum('amount');
        $WtotalAmountdebit = WalletHistory::where('user_id', $authId)->where('status', 'completed')->where('type', '2')->sum('amount');
        $walletTotalAmoun = max(0, $WtotalAmount - $WtotalAmountdebit);
        $walletTotalAmount = number_format($walletTotalAmoun, 2, '.', '');


        $walletAvailable = ($walletTotalAmount >= $totalAmount) ? 'yes' : 'no';

        $data = [
            'sub_total' => $sourcePrice,
            'addService_total' => $additionalServicesTotal,
            'tax_used' => $taxDetails,
            'tax_total' => $totalTax,
            'discount_details' => $discountDetails,
            'discount_total' => $discountAmount,
            'coupon_details' => [
                'id' => $request->coupon_id,
                'coupon_code' => $request->coupon_code,
                'coupon_type' => $request->coupon_type,
                'coupon_value' => $request->coupon_value,
            ],
            'total_amount' => $finalTotalAmount,
            'wallet_amount' => $walletTotalAmount,
            'wallet_availabe' => $walletAvailable,
            'currecy_details' => $currecy_details
        ];
        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            return response()->json(['code' => "200", 'message' => __('Payment information retrieved successfully.'), 'data' => $data], 200);
        } else {
            return response()->json([
                'sub_total' => $sourcePrice,
                'addService_total' => $additionalServicesTotal,
                'tax_used' => $taxDetails,
                'tax_total' => $totalTax,
                'discount_details' => $discountDetails,
                'discount_total' => $discountAmount,
                'coupon_details' => ($request->coupon_id && $request->coupon_code && $request->coupon_type && $request->coupon_value) ? [
                    'id' => $request->coupon_id,
                    'coupon_code' => $request->coupon_code,
                    'coupon_type' => $request->coupon_type,
                    'coupon_value' => $request->coupon_value,
                ] : [],
                'total_amount' => $finalTotalAmount,
                'wallet_amount' => $walletTotalAmount,
                'wallet_availabe' => $walletAvailable,
                'currecy_details' => $currecy_details,
                'message' => 'Payout calculated successfully.'
            ]);
        }
    }

    public function payment(Request $request)
    {
        if (
            !request()->has('is_mobile')
            && in_array(request()->get('payment_type'), ["stripe", "paypal", "wallet", "cod", "payu", "cashfree", "razorpay", "authorizenet", "mercadopago", "paystack", "bank"])
        ) {
            $formattedBookingDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->input('booking_date'))->format('Y-m-d');
        } else {
            $formattedBookingDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->input('booking_date'))->format('Y-m-d');
        }

        $authId = FacadesAuth::id();

        $additionalServiceData = null;

        if ($request->has('additionalService_id') && is_array($request->input('additionalService_id'))) {
            $additionalServiceId = $request->input('additionalService_id') ?? [];
            $additionalServices = AdditionalService::select(
                'id',
                'service_id',
                'name',
                'price',
                'duration as description'
            )
                ->whereIn('id', $additionalServiceId)->get()->toArray();
            $additionalServiceData = json_encode($additionalServices);
        }

        $fromTime = null;
        $toTime = null;
        if (!empty($request->slot_id)) {
            $slotTime = Productmeta::select('source_Values')->where('id', $request->slot_id)->first();
            if ($slotTime && strpos($slotTime->source_Values, ' - ') !== false) {
                $timeRange = explode(' - ', $slotTime->source_Values);
                if (count($timeRange) === 2 && strtotime($timeRange[0]) && strtotime($timeRange[1])) {
                    $fromTime = (new DateTime($timeRange[0]))->format('H:i');
                    $toTime = (new DateTime($timeRange[1]))->format('H:i');
                }
            }
        }
        if (is_null($request->payment_type)) {

            $data = [
                "product_id" => $request->input('service_id'),
                "branch_id" => $request->input('branch_id') ?? 0,
                "staff_id" => $request->input('staff_id') ?? 0,
                "slot_id" => $request->input('slot_id') ?? 0,
                "booking_date" => $formattedBookingDate,
                "from_time" => $request->input('from_time') ?? $fromTime,
                "to_time" => $request->input('to_time') ?? $toTime,
                "booking_status" => 1,
                "amount_tax" => $request->input('tax_amount') ?? 0,
                "user_id" => $authId,
                "first_name" => $request->input('first_name'),
                "last_name" => $request->input('last_name'),
                "user_email" => $request->input('email'),
                "user_phone" => $request->input('phone_number'),
                "user_city" => $request->input('city'),
                "user_state" => $request->input('state'),
                "user_address" => $request->input('address'),
                "note" => $request->input('note'),
                "user_postal" => $request->input('postal'),

                "payment_type" => null,
                "payment_status" => 1, // unpaid / pending
                "service_qty" => 1,
                "service_amount" => $request->input('sub_amount') ?? 0,
                "total_amount" => $request->input('total_amount') ?? 0
            ];

            if ($additionalServiceData) {
                $data['additional_services'] = $additionalServiceData;
            }

            $save = Bookings::create($data);

            if ($save) {
                $orderId = $this->getBookingOrderId($save->id);
                $save->update(['order_id' => $orderId]);
                sendBookingNotification($save->id);
            }

            if ($save && $request->filled('coupon_id')) {
                FacadesDB::table('coupon_logs')->insert([
                    'user_id' => $save->user_id,
                    'booking_id' => $save->id,
                    'coupon_id' => $request->input('coupon_id'),
                    'coupon_code' => $request->input('coupon_code'),
                    'coupon_value' => $request->input('coupon_value'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            return response()->json([
                'code' => 200,
                'message' => 'Booking successfully created!',
                'data' => ['order_id' => $save->order_id]
            ], 200);
        }


        if ($request->payment_type == "cod") {
            $data = [
                "product_id" => $request->input('service_id'),
                "branch_id" => $request->input('branch_id') ?? 0,
                "staff_id" => $request->input('staff_id') ?? 0,
                "slot_id" => $request->input('slot_id') ?? 0,
                "booking_date" => $formattedBookingDate,
                "from_time" => $request->input('from_time') ?? $fromTime,
                "to_time" => $request->input('to_time') ?? $toTime,
                "booking_status" => 1,
                "amount_tax" => $request->input('tax_amount'),
                "user_id" => $authId,
                "first_name" => $request->input('first_name'),
                "last_name" => $request->input('last_name'),
                "user_email" => $request->input('email'),
                "user_phone" => $request->input('phone_number'),
                "user_city" => $request->input('city'),
                "user_state" => $request->input('state'),
                "user_address" => $request->input('address'),
                "notes" => $request->input('note'),
                "user_postal" => $request->input('postal'),
                "payment_type" => 5,
                "payment_status" => 1,
                "service_qty" => 1,
                "service_amount" => $request->input('sub_amount'),
                "total_amount" => $request->input('total_amount')
            ];

            if ($additionalServiceData) {
                $data['additional_services'] = $additionalServiceData;
            }

            $save = Bookings::create($data);

            if ($save) {
                $orderId = getBookingOrderId($save->id);
                $save->update(['order_id' => $orderId]);
                sendBookingNotification($save->id);
                // --- Call Google Calendar Sync ---
                try {
                    $googleCalendarController = new GoogleCalendarSyncController();
                    // Pass booking, service_id, end date, and end time
                    $googleCalendarController->syncGoogleCalendar(
                        $save,
                        $request->input('service_id'),
                        $save->booking_date, // end date (can adjust if needed)
                        $save->to_time       // end time (optional)
                    );
                } catch (\Throwable $e) {
                    Log::error('Failed to sync booking to Google Calendar: ' . $e->getMessage());
                }
            }

            if ($save && $request->filled('coupon_id')) {
                FacadesDB::table('coupon_logs')->insert([
                    'user_id' => $save->user_id,
                    'booking_id' => $save->id,
                    'coupon_id' => $request->input('coupon_id'),
                    'coupon_code' => $request->input('coupon_code'),
                    'coupon_value' => $request->input('coupon_value'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $provider_id = Product::select('created_by')->where('id', $request->input('service_id'))->first();
            try {
                $pdfPath = \App\Helpers\InvoiceHelper::generateInvoice(
                    $save->id,
                    $request->input('total_amount'),
                    '2',
                    $provider_id->created_by,
                );
            } catch (\Exception $e) {
                Log::error("Failed to generate invoice: " . $e->getMessage());
            }

            if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
                return response()->json(['code' => "200", 'message' => __('Booking successfully created.'), 'data' => $data], 200);
            }

            if ($save) {
                return response()->json(['code' => 200, 'message' => 'Booking successfully created!', 'data' => ['order_id' => $save->order_id]], 200);
            } else {
                return response()->json(['error' => 'Failed to create booking'], 500);
            }
        }

        if ($request->payment_type == "wallet") {

            $totalAmount = WalletHistory::where('user_id', $authId)->where('status', 'completed')->where('type', '1')->sum('amount');
            $totalAmountdebit = WalletHistory::where('user_id', $authId)->where('status', 'completed')->where('type', '2')->sum('amount');
            $walletTotalAmount = $totalAmount - $totalAmountdebit;

            if ($walletTotalAmount < $request->input('total_amount')) {
                return response()->json(['code' => 422, 'message' => 'Insufficient balance in wallet!', 'data' => []], 422);
            }

            $data = [
                "product_id" => $request->input('service_id'),
                "branch_id" => $request->input('branch_id') ?? 0,
                "staff_id" => $request->input('staff_id') ?? 0,
                "slot_id" => $request->input('slot_id') ?? 0,
                "booking_date" => $formattedBookingDate,
                "from_time" => $request->input('from_time') ?? $fromTime,
                "to_time" => $request->input('to_time') ?? $toTime,
                "booking_status" => 1,
                "amount_tax" => $request->input('tax_amount'),
                "user_id" => $authId,
                "first_name" => $request->input('first_name'),
                "last_name" => $request->input('last_name'),
                "user_email" => $request->input('email'),
                "user_phone" => $request->input('phone_number'),
                "user_city" => $request->input('city'),
                "user_state" => $request->input('state'),
                "user_address" => $request->input('address'),
                "notes" => $request->input('note'),
                "user_postal" => $request->input('postal'),
                "payment_type" => 6,
                "payment_status" => 2,
                "service_qty" => 1,
                "service_amount" => $request->input('sub_amount'),
                "total_amount" => $request->input('total_amount')
            ];

            if ($additionalServiceData) {
                $data['additional_services'] = $additionalServiceData;
            }

            $save = Bookings::create($data);

            if ($save) {
                $orderId = getBookingOrderId($save->id);
                $save->update(['order_id' => $orderId]);
                sendBookingNotification($save->id);
                // --- Call Google Calendar Sync ---
                try {
                    $googleCalendarController = new GoogleCalendarSyncController();
                    // Pass booking, service_id, end date, and end time
                    $googleCalendarController->syncGoogleCalendar(
                        $save,
                        $request->input('service_id'),
                        $save->booking_date, // end date (can adjust if needed)
                        $save->to_time       // end time (optional)
                    );
                } catch (\Throwable $e) {
                    Log::error('Failed to sync booking to Google Calendar: ' . $e->getMessage());
                }
            }

            if ($save && $request->filled('coupon_id')) {
                FacadesDB::table('coupon_logs')->insert([
                    'user_id' => $save->user_id,
                    'booking_id' => $save->id,
                    'coupon_id' => $request->input('coupon_id'),
                    'coupon_code' => $request->input('coupon_code'),
                    'coupon_value' => $request->input('coupon_value'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $provider_id = Product::select('created_by')->where('id', $request->input('service_id'))->first();
            try {
                $pdfPath = \App\Helpers\InvoiceHelper::generateInvoice(
                    $save->id,
                    $request->input('total_amount'),
                    '2',
                    $provider_id->created_by,
                );
            } catch (\Exception $e) {
                Log::error("Failed to generate invoice: " . $e->getMessage());
            }
            $walletData = [
                "user_id" => $authId,
                "amount" => $request->input('total_amount'),
                "payment_type" => "Paypal",
                "status" => "Completed",
                "reference_id" => $save->id,
                "transaction_id" => $save->id,
                "transaction_date" => now(),
                "type" => 2,
            ];

            $wallet = WalletHistory::create($walletData);

            if ($save) {
                return response()->json(['code' => 200, 'message' => 'Booking successfully created!', 'data' => ['order_id' => $save->order_id]], 200);
            } else {
                return response()->json(['error' => 'Failed to create booking'], 500);
            }
        }

        if ($request->payment_type == "paypal") {
            if (!$this->provider) {
                return response()->json([
                    'message' => 'PayPal is currently unavailable. Please choose another payment method.'
                ], 422);
            }
            $order['intent'] = 'CAPTURE';

            $currecy_details = Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();
            $service_details = Product::orderBy('id', 'DESC')->where('id', $request->service_id)->first();

            $added_amount = 0;
            $purchase_units = [];

            $unit = [
                'items' => [
                    [
                        'name' => $service_details->source_name ?? 'Service',
                        'quantity' => 1,
                        'unit_amount' => [
                            'currency_code' => $currecy_details->code,
                            'value' => $request->total_amount,
                        ]
                    ],

                ],
                'amount' => [
                    'currency_code' => $currecy_details->code,
                    'value' => $request->total_amount,
                    'breakdown' => [
                        'item_total' => [
                            'currency_code' => $currecy_details->code,
                            'value' => $request->total_amount,
                        ],
                    ]
                ]
            ];

            $purchase_units[] = $unit;

            $order['purchase_units'] = $purchase_units;

            $order['application_context'] = [
                'return_url' => url('paypal-payment-success'),
                'cancel_url' => url('payment-failed')
            ];

            $response = $this->provider->createOrder($order);

            if (!is_array($response) || !array_key_exists('id', $response)) {
                return response()->json([
                    'message' => 'PayPal is currently unavailable. Please choose another payment method.'
                ], 422);
            }

            $data = [
                "product_id" => $request->input('service_id'),
                "branch_id" => $request->input('branch_id') ?? 0,
                "staff_id" => $request->input('staff_id') ?? 0,
                "slot_id" => $request->input('slot_id') ?? 0,
                "booking_date" => $formattedBookingDate,
                "from_time" => $request->input('from_time') ?? $fromTime,
                "to_time" => $request->input('to_time') ?? $toTime,
                "booking_status" => 1,
                "amount_tax" => $request->input('tax_amount'),
                "user_id" => $authId,
                "first_name" => $request->input('first_name'),
                "last_name" => $request->input('last_name'),
                "user_email" => $request->input('email'),
                "user_phone" => $request->input('phone_number'),
                "user_city" => $request->input('city'),
                "user_state" => $request->input('state'),
                "user_address" => $request->input('address'),
                "notes" => $request->input('note'),
                "user_postal" => $request->input('postal'),
                'tranaction' =>  $response['id'],
                "payment_type" => 1,
                "payment_status" => 1,
                "service_qty" => 1,
                "service_amount" => $request->input('sub_amount'),
                "total_amount" => $request->input('total_amount')
            ];

            if ($additionalServiceData) {
                $data['additional_services'] = $additionalServiceData;
            }

            $save = Bookings::create($data);

            if ($save) {
                $orderId = getBookingOrderId($save->id);
                $save->update(['order_id' => $orderId]);
                sendBookingNotification($save->id);
                // --- Call Google Calendar Sync ---
                try {
                    $googleCalendarController = new GoogleCalendarSyncController();
                    // Pass booking, service_id, end date, and end time
                    $googleCalendarController->syncGoogleCalendar(
                        $save,
                        $request->input('service_id'),
                        $save->booking_date, // end date (can adjust if needed)
                        $save->to_time       // end time (optional)
                    );
                } catch (\Throwable $e) {
                    Log::error('Failed to sync booking to Google Calendar: ' . $e->getMessage());
                }
            }

            if ($save && $request->filled('coupon_id')) {
                FacadesDB::table('coupon_logs')->insert([
                    'user_id' => $save->user_id,
                    'booking_id' => $save->id,
                    'coupon_id' => $request->input('coupon_id'),
                    'coupon_code' => $request->input('coupon_code'),
                    'coupon_value' => $request->input('coupon_value'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            $provider_id = Product::select('created_by')->where('id', $request->input('service_id'))->first();
            try {
                $pdfPath = \App\Helpers\InvoiceHelper::generateInvoice(
                    $save->id,
                    $request->input('total_amount'),
                    '2',
                    $provider_id->created_by,
                );
            } catch (\Exception $e) {
                Log::error("Failed to generate invoice: " . $e->getMessage());
            }
            $approve_paypal_url = $response['links'][1]['href'];

            return response()->json([
                'code' => 200,
                'message' => 'Order created successfully.',
                'paypal_url' => $approve_paypal_url,
                'data' => ['order_id' => $save->order_id ?? '']
            ]);
        }

        if ($request->payment_type == "stripe") {
            $stripeSecret = config('stripe.test.sk') ?? '';
            if (empty($stripeSecret)) {
                return response()->json([
                    'message' => 'Stripe is currently unavailable. Please choose another payment method.'
                ], 422);
            }
            Stripe::setApiKey(is_string($stripeSecret) ? $stripeSecret : '');
            $purchase_units = [];
            $currecy_details = Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();
            $service_details = Product::orderBy('id', 'DESC')->where('id', $request->service_id)->first();

            $session = Session::create([
                'line_items'  => [
                    [
                        'price_data' => [
                            'currency'     => $currecy_details->code,
                            'product_data' => [
                                'name' => $service_details->source_name,
                            ],
                            'unit_amount' => intval($request->total_amount * 100),
                        ],
                        'quantity'   => 1,
                    ],
                ],
                'mode'        => 'payment',
                'customer_creation' => 'always',
                'billing_address_collection' => 'required',
                'success_url' => route('strip.payment.success') . "?session_id={CHECKOUT_SESSION_ID}",
                'cancel_url'  => route('checkout'),
            ]);

            $data = [
                "product_id" => $request->input('service_id'),
                "branch_id" => $request->input('branch_id') ?? 0,
                "staff_id" => $request->input('staff_id') ?? 0,
                "slot_id" => $request->input('slot_id') ?? 0,
                "booking_date" => $formattedBookingDate,
                "from_time" => $request->input('from_time') ?? $fromTime,
                "to_time" => $request->input('to_time') ?? $toTime,
                "booking_status" => 1,
                "amount_tax" => $request->input('tax_amount'),
                "user_id" => $authId,
                "first_name" => $request->input('first_name'),
                "last_name" => $request->input('last_name'),
                "user_email" => $request->input('email'),
                "user_phone" => $request->input('phone_number'),
                "user_city" => $request->input('city'),
                "user_state" => $request->input('state'),
                "user_address" => $request->input('address'),
                "notes" => $request->input('note'),
                "user_postal" => $request->input('postal'),
                'tranaction' => $session->id,
                "payment_type" => 2,
                "payment_status" => 1,
                "service_qty" => 1,
                "service_amount" => $request->input('sub_amount'),
                "total_amount" => $request->input('total_amount')
            ];

            if ($additionalServiceData) {
                $data['additional_services'] = $additionalServiceData;
            }

            $save = Bookings::create($data);

            if ($save) {
                $orderId = getBookingOrderId($save->id);
                $save->update(['order_id' => $orderId]);
                sendBookingNotification($save->id);
                // --- Call Google Calendar Sync ---
                try {
                    $googleCalendarController = new GoogleCalendarSyncController();
                    // Pass booking, service_id, end date, and end time
                    $googleCalendarController->syncGoogleCalendar(
                        $save,
                        $request->input('service_id'),
                        $save->booking_date, // end date (can adjust if needed)
                        $save->to_time       // end time (optional)
                    );
                } catch (\Throwable $e) {
                    Log::error('Failed to sync booking to Google Calendar: ' . $e->getMessage());
                }
            }

            if ($save && $request->filled('coupon_id')) {
                FacadesDB::table('coupon_logs')->insert([
                    'user_id' => $save->user_id,
                    'booking_id' => $save->id,
                    'coupon_id' => $request->input('coupon_id'),
                    'coupon_code' => $request->input('coupon_code'),
                    'coupon_value' => $request->input('coupon_value'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $provider_id = Product::select('created_by')->where('id', $request->input('service_id'))->first();
            try {
                $pdfPath = \App\Helpers\InvoiceHelper::generateInvoice(
                    $save->id,
                    $request->input('total_amount'),
                    '2',
                    $provider_id->created_by,
                );
            } catch (\Exception $e) {
                Log::error("Failed to generate invoice: " . $e->getMessage());
            }
            $stripURL = $session->url;

            if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
                $request->merge(['transaction_id' => $session->id]);
                return app(\App\Http\Controllers\StripeController::class)->live_mobile($request);
            } else {
                return response()->json([
                    'message' => 'Order created successfully.',
                    'stripurl' => $stripURL,
                    'data' => ['order_id' => $save->order_id ?? '']
                ]);
            }
        }

        if ($request->payment_type == "mollie") {
            $currecy_details = Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();

            $payment = Mollie::api()->payments->create([
                "amount" => [
                    "currency" => $currecy_details->code,
                    "value" => number_format($request->total_amount, 2) // You must send the correct number of decimals, thus we enforce the use of strings
                ],
                "description" => "truelysell service",
                "redirectUrl" => route('make.sucesspayment.molliesucess'),
                "metadata" => [
                    "order_id" => "12345",
                ],
            ]);

            session(['paymentid' => $payment->id]);

            $data = [
                "product_id" => $request->input('service_id'),
                "branch_id" => $request->input('branch_id') ?? 0,
                "staff_id" => $request->input('staff_id') ?? 0,
                "slot_id" => $request->input('slot_id') ?? 0,
                "booking_date" => $formattedBookingDate,
                "from_time" => $request->input('from_time') ?? $fromTime,
                "to_time" => $request->input('to_time') ?? $toTime,
                "booking_status" => 1,
                "amount_tax" => $request->input('tax_amount'),
                "user_id" => $authId,
                "first_name" => $request->input('first_name'),
                "last_name" => $request->input('last_name'),
                "user_email" => $request->input('email'),
                "user_phone" => $request->input('phone_number'),
                "user_city" => $request->input('city'),
                "user_state" => $request->input('state'),
                "user_address" => $request->input('address'),
                "notes" => $request->input('note'),
                "user_postal" => $request->input('postal'),
                'tranaction' => $payment->id,
                "payment_type" => 7,
                "payment_status" => 1,
                "service_qty" => 1,
                "service_amount" => $request->input('sub_amount'),
                "total_amount" => $request->input('total_amount'),
            ];

            if ($additionalServiceData) {
                $data['additional_services'] = $additionalServiceData;
            }

            $save = Bookings::create($data);

            if ($save) {
                $orderId = getBookingOrderId($save->id);
                $save->update(['order_id' => $orderId]);
                sendBookingNotification($save->id);
                // --- Call Google Calendar Sync ---
                try {
                    $googleCalendarController = new GoogleCalendarSyncController();
                    // Pass booking, service_id, end date, and end time
                    $googleCalendarController->syncGoogleCalendar(
                        $save,
                        $request->input('service_id'),
                        $save->booking_date, // end date (can adjust if needed)
                        $save->to_time       // end time (optional)
                    );
                } catch (\Throwable $e) {
                    Log::error('Failed to sync booking to Google Calendar: ' . $e->getMessage());
                }
            }

            if ($save && $request->filled('coupon_id')) {
                FacadesDB::table('coupon_logs')->insert([
                    'user_id' => $save->user_id,
                    'booking_id' => $save->id,
                    'coupon_id' => $request->input('coupon_id'),
                    'coupon_code' => $request->input('coupon_code'),
                    'coupon_value' => $request->input('coupon_value'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $mollieURL = $payment->getCheckoutUrl();

            return response()->json([
                'message' => 'Order created successfully.',
                'url' => $mollieURL,
                'data' => ['order_id' => $save->order_id ?? '']
            ]);
        }

        //Addons -->
        if ($request->payment_type == "razorpay") {
            if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
                if ($request->payment_type == "razorpay") {
                    return (new RazorpayController())->initiatePayment(
                        $request,
                    );
                }
            } else {
                return (new RazorpayController())->handlePayment(
                    $request,
                    $formattedBookingDate,
                    $authId,
                    $additionalServiceData,
                    $fromTime,
                    $toTime
                );
            }
        }

        if ($request->payment_type == "authorizenet") {
            if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
                if ($request->payment_type == "authorizenet") {
                    return (new AuthorizeNetController())->initiatePayment(
                        $request
                    );
                }
            } else {
                return (new AuthorizeNetController())->handlePayment(
                    $request,
                    $formattedBookingDate,
                    $authId,
                    $additionalServiceData,
                    $fromTime,
                    $toTime
                );
            }
        }

        if ($request->payment_type == "payu") {
            if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
                if ($request->payment_type == "payu") {
                    return (new PayUController())->initiatePayment(
                        $request
                    );
                }
            } else {
                return (new PayUController())->handlePayment(
                    $request,
                    $formattedBookingDate,
                    $authId,
                    $additionalServiceData,
                    $fromTime,
                    $toTime
                );
            }
        }

        if ($request->payment_type == "cashfree") {
            if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
                if ($request->payment_type == "cashfree") {
                    return (new CashfreeController())->initiatePayment(
                        $request
                    );
                }
            } else {
                return (new CashfreeController())->handlePayment(
                    $request,
                    $formattedBookingDate,
                    $authId,
                    $additionalServiceData,
                    $fromTime,
                    $toTime
                );
            }
        }

        if ($request->payment_type == "paystack") {
            return (new PaystackController())->handlePayment(
                $request,
                $formattedBookingDate,
                $authId,
                $additionalServiceData,
                $fromTime,
                $toTime
            );
        }

        if ($request->payment_type == "mercadopago") {
            return (new MercadoPagoController())->handlePayment(
                $request,
                $formattedBookingDate,
                $authId,
                $additionalServiceData,
                $fromTime,
                $toTime
            );
        }
        //Addons <--

        if ($request->payment_type == "bank") {
            return (new BankTransferController())->handlePayment(
                $request,
                $formattedBookingDate,
                $authId,
                $additionalServiceData,
                $fromTime,
                $toTime
            );
        }
    }
    private function getBookingOrderId(?int $orderId)
    {
        $orderId = str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);
        $bookingPrefix = GlobalSetting::where('key', 'booking_prefix')->value('value');
        $bookingPrefix = $bookingPrefix ?? 'RES';

        return $bookingPrefix . $orderId;
    }
    public function paypalPaymentSuccess(Request $request)
    {
        try {
            $response = $this->provider->capturePaymentOrder($request->get('token'));

            if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                Bookings::where('tranaction', $response['id'])->update(['payment_status' => 2]);

                return redirect()->route('payment.two');
            } else {
                return response()->json([
                    'code' => 400,
                    'message' => 'Payment capture failed.',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function stripPaymentSuccess(Request $request)
    {
        try {
            Stripe::setApiKey(config('stripe.test.pk'));
            $sessionId = $request->get('session_id');

            if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
                Bookings::where('tranaction', $request->transaction_id)->update(['payment_status' => 2]);
            } else {
                Bookings::where('tranaction', $sessionId)->update(['payment_status' => 2]);
            }

            if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
                return response()->json([
                    'code' => 200,
                    'message' => 'Payment successful. Subscription activated.',
                    'data' => [
                        'transaction_id' => $request->transaction_id,
                    ],
                ], 200);
            } else {
                return redirect()->route('payment.two');
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function checkProductUser(Request $request)
    {
        try {
            $productSlug = $request->get('product_slug'); // Get product_slug from the request

            $product = Product::where('slug', $productSlug)->first();

            if (!$product) {
                return response()->json(['exists' => 'no'], 400);
            }

            $branchExists = ServiceBranch::where('service_id', $product->id)->exists();

            if ($branchExists) {
                return response()->json(['exists' => 'yes']);
            } else {
                return response()->json(['exists' => 'no']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
