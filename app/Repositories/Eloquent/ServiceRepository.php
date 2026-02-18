<?php

namespace App\Repositories\Eloquent;

use Modules\GlobalSetting\app\Models\Placeholders;
use Modules\Categories\app\Models\Categories;
use Modules\Product\app\Models\Product;
use Modules\Service\app\Models\Productmeta;
use Modules\Product\app\Models\Rating;
use App\Models\UserDetail;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Modules\GlobalSetting\app\Models\Language;
use App\Models\Bookings;
use App\Models\PackageTrx;
use App\Models\ServicePromotion;
use Carbon\Carbon;
use Modules\GlobalSetting\app\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Modules\Service\app\Models\AdditionalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\GlobalSetting\Entities\GlobalSetting;
use App\Repositories\Contracts\ServiceRepositoryInterface;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function productlistcategory(Request $request, $slug, $is_mobile = false): JsonResponse | View
    {
        session(['link' => url()->current()]);
        $languages = Language::select('id', 'code')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->get();
        if (Auth::check()) {
            $language_id = Auth::user()->user_language_id;
        } elseif (Cookie::get('languageId')) {
            $language_id = Cookie::get('languageId');
        } else {
            $defaultLanguage = Language::select('id', 'code')
                ->where('is_default', 1)
                ->whereNull('deleted_at')
                ->first();
            $language_id = $defaultLanguage->id;
        }
        if ($language_id === null || strlen($language_id) == 0) {
            $language_id = 1;
        }

        $productscategory = Categories::query()->where('slug', '=', $request->slug)->first();
        $productscategorycount = Categories::query()->where('slug', '=', $request->slug)->count();
        if ($productscategorycount == 0) {
            $productscategoryid = 0;
        } else {
            $productscategoryid = $productscategory->id;
        }
        
        $products = DB::table('products')
            ->select('products.id', 'products.user_id', 'categories.name', 'products.slug', 'products.source_name', 'products.source_price')
            ->join('categories', 'products.source_category', '=', 'categories.id')
            ->where(['products.source_category' => $productscategoryid])
            ->where('products.status', 1)
            ->where('products.verified_status', 1)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'products.user_id')
                    ->whereNull('users.deleted_at');
            });
        $products = $products->where('products.deleted_at', null);
        $productImageMeta = DB::table('products_meta')
            ->where('source_key', 'product_image')
            ->whereIn('product_id', $products->pluck('id'))
            ->get()
            ->keyBy('product_id');

        $productsuser = Product::query()->where('source_type', '=', 'service')->get();
        $userIds = $productsuser->pluck('user_id');

        $productscategory = Categories::query()->where(['status' => 1, 'language_id' => $language_id, 'parent_id' => 0, 'source_type' => 'service'])->get();
        $cities = UserDetail::whereIn('user_id', $userIds)->select('city')->distinct()->get();
        if ($is_mobile == true) {
            $products = $products->get();

            foreach ($products as $product) {
                $imagePath = $productImageMeta[$product->id]->source_Values ?? null;
                $product->image_url = $imagePath ? asset('storage/' . $imagePath) : null;
            }
            return response()->json(['code' => "200", 'message' => __('Service details retrieved successfully.'), 'data' => $products], 200);
        }
        $products = $products->paginate(9);

        $products->getCollection()->transform(function ($product) {

            $promotion = $this->getProviderPromotionFlags(
                $product->user_id,
                $product->id 
            );

            $product->featured        = $promotion['featured'];
            $product->badge           = $promotion['badge'];
            $product->promotedJobber  = $promotion['promotedJobber'];
            $product->promotedService = $promotion['promotedService'];

            return $product;
        });

        $currecy_details = Cache::remember('currecy_details', 86400, function () {
            return Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();
        });

        return view('services', compact('products', 'currecy_details', 'productscategory', 'cities'));
    }

    public function catlist(Request $request): JsonResponse | View
    {
        session(['link' => url()->current()]);
        $parent_id_list = 0;
        if (isset($request->category_id)) {
            $parent_id_list = $request->category_id;
        }
        $language_id = 1;
        $languages = Language::select('id', 'code')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->get();
        if (Auth::check()) {
            $language_id = Auth::user()->user_language_id;
        } elseif (Cookie::get('languageId')) {
            $language_id = Cookie::get('languageId');
        } else {
            $defaultLanguage = $languages->firstWhere('is_default', 1);
            $language_id = $defaultLanguage ? $defaultLanguage->id : null;
        }
        if ($language_id === null || strlen($language_id) == 0) {
            $language_id = 1;
        }
        $productscategory = Categories::query()->select('name', 'id', 'slug', 'image', 'parent_id', 'featured')
            ->withCount(['products as service_count' => function ($query) {
                $query->whereColumn('source_category', '=', 'categories.id')
                    ->where('status', 1)
                    ->where('verified_status', 1)
                    ->whereExists(function ($subQuery) {
                        $subQuery->select(DB::raw(1))
                            ->from('users')
                            ->whereColumn('users.id', 'products.user_id')
                            ->whereNull('users.deleted_at');
                    });
            }])
            ->where(['status' => 1, 'language_id' => $language_id, 'parent_id' => 0, 'source_type' => 'service'])
            ->get()
            ->map(function ($category) {
                if (str_contains($category->image, 'amazonaws')) {
                } else {
                    $category->image = $category->image ? url('storage/' . $category->image) : null;
                }
                return $category;
            });

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            return response()->json([
                'code' => 200,
                'message' => __('Category retrieved successfully.'),
                'data' => $productscategory
            ], 200);
        }

        return view('category_list', compact('productscategory'));
    }

    public function addtocart(Request $request): JsonResponse
    {
        $shoppingCart = session('shoppingCart', []);

        $shoppingCart[$request->id] = [
            'productId' => $request->id,
            'qty' => 1,
            'amount'    => $request->price
        ];
        session(['link' => $request->id]);

        session(['shoppingCart' => $shoppingCart]);
        $request->session()->put('shoppingCart111', $request->id);
        $gets = session('shoppingCart');
        $data = "";
        return response()->json([
            'code' => 200,
            'message' => __('Detail retrieved successfully.'),
            'data' => $data,
        ], 200);
    }

    public function onlyproductlist(Request $request): JsonResponse | View
    {
        session(['link' => url()->current()]);
        $citiescount = UserDetail::select('city')->count();
        $productscategory = Categories::query()->where(['status' => 1, 'parent_id' => 0, 'source_type' => 'product'])->get();
        $todaydate = today()->format('Y-m-d  H:i:s');
        $Valid_provider = PackageTrx::select('provider_id')->where('payment_status', "=", 2)->where('end_date', '>=', $todaydate)->get();

        $Valid_provider_user = "";
        foreach ($Valid_provider as $Valid_provider_values) {
            $Valid_provider_user .= $Valid_provider_values->provider_id . ",";
        }
        $Valid_provider_user = substr($Valid_provider_user, 0, -1);

        $products = Product::query()->where('source_type', '=', 'product')->get();
        $collect_price_range = Productmeta::select('product_id')->whereIn('source_key', ['Fixed', 'Minitue', 'Minute', 'Squre-metter', 'Hourly', 'Squre-Feet'])->orderByRaw('CONVERT(source_Values, SIGNED) asc')->get();
        if (isset($request->sortprice) && $request->sortprice == 'highl') {
            $collect_price_range = Productmeta::select('product_id')->whereIn('source_key', ['Fixed', 'Minitue', 'Minute', 'Squre-metter', 'Hourly', 'Squre-Feet'])->orderByRaw('CONVERT(source_Values, SIGNED) desc')->get();
        }
        $order_product = "";
        $order_product_amount = "";

        foreach ($collect_price_range as $collect_price_rangeValues) {
            $order_product .= $collect_price_rangeValues->product_id . ",";
        }
        $order_product = substr($order_product, 0, -1);
        if (isset($request->range_price)) {
            list($minPrice, $maxPrice) = explode(';', $request->range_price);
            $collect_price_amout = Productmeta::select('product_id')->whereIn('source_key', ['Fixed', 'Minitue', 'Minute', 'Squre-metter', 'Hourly', 'Squre-Feet'])->whereBetween('source_Values', [$minPrice, $maxPrice])->get();
            foreach ($collect_price_amout as $collect_price_rangeValues) {
                $order_product_amount .= $collect_price_rangeValues->product_id . ",";
            }
            $order_product_amount = substr($order_product_amount, 0, -1);
        }
        $userIds = $products->pluck('user_id');
        $cities = UserDetail::whereIn('user_id', $userIds)->select('city')->distinct()->get();

        $products = DB::table('products')
            ->select('products.id', 'products.user_id', 'products.slug', 'categories.name', 'products.source_name', DB::raw('ROUND(AVG(ratings.rating), 1) as average_rating'))
            ->join('categories', 'products.source_category', '=', 'categories.id')
            ->leftJoin('ratings', 'products.id', '=', 'ratings.product_id')
            ->groupBy('products.id', 'products.user_id', 'products.slug', 'categories.name', 'products.source_name')
            ->where('products.verified_status', '=', 1)
            ->where('products.status', '=', 1);
        $products = $products->where('products.source_type', '=', 'product');

        $products = $products->whereNull('products.deleted_at');

        $val_user = explode(",", $Valid_provider_user);

        if ($request->keywords != '') {

            $products = $products->where('products.source_name', 'like', '%' . $request->keywords . '%');
        }
        if (isset($request->range_price)) {
            list($minPrice, $maxPrice) = explode(';', $request->range_price);
            $prproducts = explode(",", $order_product_amount);
            if (($maxPrice != 0)) {
                if (count($prproducts) > 0) {
                    $products = $products->whereIn('products.id', $prproducts);
                } else {
                    $products = $products->whereRaw('1 = 0');
                }
            }
        }

        if (isset($request->cate)) {
            foreach ($request->cate as $catev) {
                $products = $products->orwhere('products.source_category', '=', $catev);
            }
        }

        if (isset($request->catev)) {
            $products = $products->where('products.source_category', '=', $request->catev);
        }

        if (isset($request->subcategory)) {
            $products = $products->orWhere('products.source_subcategory', $request->subcategory);
        }

        if (isset($request->rating) && is_array($request->rating)) {

            if (count($request->rating) == 1) {
                $rating = max($request->rating);
                $products = $products->orHavingRaw('average_rating >= ? AND average_rating < ?', [$rating, ($rating + 1)]);
            } else {
                foreach ($request->rating as $rating) {
                    if ($rating == 5) {
                        $products = $products->orHavingRaw('average_rating >= ? AND average_rating < ?', [5, 6]);
                    } else if ($rating == 4) {
                        $products = $products->orHavingRaw('average_rating >= ? AND average_rating < ?', [4, 5]);
                    } else if ($rating == 3) {
                        $products = $products->orHavingRaw('average_rating >= ? AND average_rating < ?', [3, 4]);
                    } else if ($rating == 2) {
                        $products = $products->orHavingRaw('average_rating >= ? AND average_rating < ?', [2, 3]);
                    } else if ($rating == 1) {
                        $products = $products->orHavingRaw('average_rating >= ? AND average_rating < ?', [1, 2]);
                    }
                }
            }
        }

        if (isset($request->location) && $request->location != '') {
            $products = $products->join('user_details', 'products.user_id', '=', 'user_details.user_id')
                ->orWhere('user_details.city', $request->location);
        }

        $products = $products->orderByRaw("FIELD(products.id, " . $order_product . " )");

        $products = $products->paginate(9);

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            return response()->json(['code' => "200", 'message' => __('Page details retrieved successfully.'), 'data' => $products], 200);
        }
        $email = "";
        if (Auth::check()) {
            $email = Auth::user()->email;
        }
        $data = [
            'email' => $email,
        ];
        return view('products', compact('data', 'products', 'productscategory', 'cities'));
    }

    public function productlist(Request $request): JsonResponse | View
    {
        $cityQuery = $request->query('city');
        $cityUserIds = [];

        if (!empty($cityQuery)) {
            $cityUserIds = ["0"];
            $cityName = explode(' - ', $cityQuery)[0];

            $cityRecord = DB::table('cities')->where('name', $cityName)->first();

            if ($cityRecord) {
                $cityUserIds = DB::table('user_details')
                    ->where('city', $cityRecord->id)
                    ->pluck('user_id')
                    ->toArray();
            }
        }
        
        $locationDistanceValue = DB::table('general_settings')
            ->where('key', 'location_distance_value')
            ->value('value') ?? 10;

        $locationUnitValue = DB::table('general_settings')
            ->where('key', 'location_unit')
            ->value('value');

        $locationUnitValue = strtolower($locationUnitValue ?? 'km');

        // Set Earth radius based on unit
        $earthRadius = $locationUnitValue == 'km' ? 6371 : 3959;

        // Get provider user IDs within range
        $nearbyUserIds = collect();

        if (!empty($request->lat) && !empty($request->lang)) {

            $lat = $request->lat;
            $lng = $request->lang;

            $nearbyUserIds = UserDetail::select(
                'user_id',
                DB::raw("
                    ($earthRadius * acos(
                        least(1,
                            cos(radians($lat)) *
                            cos(radians(lat)) *
                            cos(radians($lng) - radians(lang)) +
                            sin(radians($lat)) *
                            sin(radians(lat))
                        )
                    )) AS distance
                ")
            )
            ->whereNotNull('lat')
            ->whereNotNull('lang')
            ->whereHas('user', function ($q) {
                $q->whereIn('user_type', [1, 2, 4]);
            })
            ->having('distance', '<=', $locationDistanceValue)
            ->orderBy('distance', 'asc')
            ->pluck('user_id');
        }

        session(['link' => url()->current()]);
        $citiescount = UserDetail::select('city')->count();
        $getmileradius = GlobalSetting::where('key', 'milesradious')->first();
        $val_brach_ser = [];
        // validate presence
        if (!empty($request->lat) && !empty($request->lang)) {
            // cast to floats to avoid empty strings
            $lat = (float) $request->lat;
            $lng = (float) $request->lang;

            // ensure radius is numeric; fallback to a default (e.g. 10 miles)
            $radiusMiles = (isset($getmileradius->value) && is_numeric($getmileradius->value))
                ? (float) $getmileradius->value
                : 10.0;

            // distance expression (miles) using ST_Distance_Sphere with parameter bindings
            $distanceExpr = "ST_Distance_Sphere(point(`lang`,`lat`), point(?,?)) * 0.000621371192";

            // Use query builder + whereRaw with bindings (call the expression twice via string)
            $branches = DB::table('branches')
                ->select('id', DB::raw("({$distanceExpr}) as distance"))
                ->whereRaw("({$distanceExpr}) <= ?", [$lng, $lat, $lng, $lat, $radiusMiles])
                ->get();

            // If no branches found, short-circuit with empty arrays
            if ($branches->isEmpty()) {
                $val_brach_ser = [];
            } else {
                // collect branch IDs
                $branchIds = $branches->pluck('id')->unique()->values()->all();

                // fetch service ids for these branches
                $serviceIds = DB::table('service_branches')
                    ->whereIn('branch_id', $branchIds)
                    ->pluck('service_id');

                // unique, zero-indexed array of service IDs
                $val_brach_ser = $serviceIds->unique()->values()->all();
            }

            // Now $val_brach_ser is an array of unique service IDs (or empty array)
        }
        $languages = Language::select('id', 'code')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->get();
        if (Auth::check()) {
            $language_id = Auth::user()->user_language_id;
        } elseif (Cookie::get('languageId')) {
            $language_id = Cookie::get('languageId');
        } else {
            $defaultLanguage = Language::select('id', 'code')
                ->where('is_default', 1)
                ->whereNull('deleted_at')
                ->first();
            $language_id = $defaultLanguage->id;
        }
        if ($language_id === null || strlen($language_id) == 0) {
            $language_id = 1;
        }
        $productscategory = Categories::query()->where(['status' => 1, 'language_id' => $language_id, 'parent_id' => 0, 'source_type' => 'service'])->get();

        $todaydate = today()->format('Y-m-d  H:i:s');
        $Valid_provider = PackageTrx::select('provider_id')->where('payment_status', "=", 2)->where('end_date', '>=', $todaydate)->get();

        $Valid_provider_user = "";
        foreach ($Valid_provider as $Valid_provider_values) {
            $Valid_provider_user .= $Valid_provider_values->provider_id . ",";
        }
        $Valid_provider_user = substr($Valid_provider_user, 0, -1);
        $products = Product::query()->where('source_type', '=', 'service')->get();
        $collect_price_range = Productmeta::select('product_id')->whereIn('source_key', ['Fixed', 'Minitue', 'Minute', 'Squre-metter', 'Hourly', 'Squre-Feet'])->orderByRaw('CONVERT(source_Values, SIGNED) asc')->get();

        if (isset($request->sortprice) && $request->sortprice == 'highl') {
            $collect_price_range = Productmeta::select('product_id')->whereIn('source_key', ['Fixed', 'Minitue', 'Minute', 'Squre-metter', 'Hourly', 'Squre-Feet'])->orderByRaw('CONVERT(source_Values, SIGNED) desc')->get();
        }
        $order_product = "";
        $order_product_amount = "";

        foreach ($collect_price_range as $collect_price_rangeValues) {
            $order_product .= $collect_price_rangeValues->product_id . ",";
        }
        $order_product = substr($order_product, 0, -1);
        if (isset($request->range_price)) {
            list($minPrice, $maxPrice) = explode(';', $request->range_price);

            $collect_price_amout = Productmeta::select('product_id', 'source_Values')->whereIn('source_key', ['Fixed', 'Minitue', 'Minute', 'Squre-metter', 'Hourly', 'Squre-Feet'])->whereBetween(DB::raw('CAST(source_Values AS DECIMAL(10,2))'), [$minPrice, $maxPrice])->get();
            foreach ($collect_price_amout as $collect_price_rangeValues) {
                if ($maxPrice >= $collect_price_rangeValues->source_Values && $minPrice <= $collect_price_rangeValues->source_Values) {

                    $order_product_amount .= $collect_price_rangeValues->product_id . ",";
                }
            }
            $order_product_amount = substr($order_product_amount, 0, -1);
        }
        $userIds = $products->pluck('user_id');
        $cities = UserDetail::whereIn('user_id', $userIds)->select('city')->distinct()->get();

        $products = DB::table('products')
            ->select('products.id', 'products.user_id', 'products.slug', 'categories.name', 'products.source_name', DB::raw('ROUND(AVG(ratings.rating), 1) as average_rating'))
            ->join('categories', 'products.source_category', '=', 'categories.id')
            ->leftJoin('ratings', 'products.id', '=', 'ratings.product_id')
            ->where('products.verified_status', '=', 1)
            ->where('products.status', '=', 1)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'products.user_id')
                    ->whereNull('users.deleted_at');
            })
            ->groupBy('products.id', 'products.user_id', 'products.slug', 'categories.name', 'products.source_name');

        $products = $products->whereNull('products.deleted_at');
        $products = $products->where('products.source_type', '=', 'service');
        
        $products = $products->where('products.language_id', '=', $language_id);

        $val_user = explode(",", $Valid_provider_user);
        if (isset($request->lat) && count($val_brach_ser) != 0) {
            $products = $products->whereIn('products.id', $val_brach_ser);
        }
        if (isset($request->category_id)) {
            $products = $products->where('products.source_category', $request->category_id);
        }
        if ($request->keywords != '') {

            $products = $products->where('products.source_name', 'like', '%' . $request->keywords . '%');
        }

        if (isset($request->cate)) {
            $products = $products->whereIn('products.source_category', $request->cate);
        }
        $catId = Categories::select('id')->where('slug', $request->category)->first();
        if (isset($request->category)) {
            $products = $products->where('products.source_category', $catId->id);
        }
        if (isset($request->catev)) {
            $products = $products->where('products.source_category', '=', $request->catev);
        }
        if (isset($request->subcategory)) {
            $products = $products->where('products.source_subcategory', $request->subcategory);
        }
        if (isset($request->range_price)) {
            list($minPrice, $maxPrice) = explode(';', $request->range_price);
            $prproducts = explode(",", $order_product_amount);
            if (($maxPrice != 0)) {
                if (count($prproducts) > 0) {
                    $products = $products->whereIn('products.id', $prproducts);
                } else {
                    $products = $products->whereRaw('1 = 0');
                }
            }
        }

        if (isset($request->rating) && is_array($request->rating)) {

            if (count($request->rating) == 1) {
                $rating = max($request->rating);
                $products = $products->orHavingRaw('average_rating >= ? AND average_rating < ?', [$rating, ($rating + 1)]);
            } else {
                foreach ($request->rating as $rating) {
                    if ($rating == 5) {
                        $products = $products->orHavingRaw('average_rating >= ? AND average_rating < ?', [5, 6]);
                    } else if ($rating == 4) {
                        $products = $products->orHavingRaw('average_rating >= ? AND average_rating < ?', [4, 5]);
                    } else if ($rating == 3) {
                        $products = $products->orHavingRaw('average_rating >= ? AND average_rating < ?', [3, 4]);
                    } else if ($rating == 2) {
                        $products = $products->orHavingRaw('average_rating >= ? AND average_rating < ?', [2, 3]);
                    } else if ($rating == 1) {
                        $products = $products->orHavingRaw('average_rating >= ? AND average_rating < ?', [1, 2]);
                    }
                }
            }
        }

        if (isset($request->location) && $request->location != '') {
            $products = $products->join('user_details', 'products.user_id', '=', 'user_details.user_id')
                ->where('user_details.city', $request->location);
        }

        if (isset($cityUserIds) && !empty($cityUserIds) || (isset($cityQuery) && $cityQuery != '')) {
            $products = $products->whereIn('products.user_id', $cityUserIds);
        }

        if (isset($request->provider)) {
            $products = $products->where('products.created_by', '=', $request->provider);
        }

        $products = $products->whereNull('products.deleted_at');

        $products = $products->orderByRaw("FIELD(products.id, " . $order_product . " )");
        // Apply location-based filtering if user is logged in and no specific city is selected
        if (!empty($request->lat) && !empty($request->lang)) {
            $products = $products->whereIn('products.user_id', $nearbyUserIds);
        }
        $products = $products->paginate(9);
        
        $products->getCollection()->transform(function ($product) {

            $promotion = $this->getProviderPromotionFlags(
                $product->user_id,
                $product->id 
            );

            $product->featured        = $promotion['featured'];
            $product->badge           = $promotion['badge'];
            $product->promotedJobber  = $promotion['promotedJobber'];
            $product->promotedService = $promotion['promotedService'];

            return $product;
        });

        $currecy_details = Cache::remember('currecy_details', 86400, function () {
            return Currency::select('id', 'name', 'code', 'symbol')->orderBy('id', 'DESC')->where('is_default', 1)->first();
        });

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $products->map(function ($category) {
                $category->image = Productmeta::query()
                    ->where('product_id', $category->id)
                    ->where('source_key', 'product_image')
                    ->value('source_Values');

                $category->price = Productmeta::query()
                    ->where('product_id', $category->id)
                    ->whereIn('source_key', ['Fixed', 'Minitue', 'Minute', 'Squre-metter', 'Hourly', 'Squre-Feet'])
                    ->value('source_Values');

                // Ensure location is not null
                $location = UserDetail::select('city', 'state', 'country')
                    ->where('user_id', $category->user_id)
                    ->first();

                $category->location = $location ? $location->showaaddress() : '';

                $category->showrating = Rating::where(['product_id' => $category->id, 'parent_id' => 0])->avg('rating');

                return $category;
            });

            return response()->json([
                'code' => "200",
                'message' => __('Page details retrieved successfully.'),
                'data' => $products
            ], 200);
        }
        $email = "";
        if (Auth::check()) {
            $email = Auth::user()->email;
        }
        $data = [
            'email' => $email,
        ];

        return view('services', compact('data', 'products', 'productscategory', 'cities', 'currecy_details'));
    }
        private function getProviderPromotionFlags($providerId, $serviceId = null)
    {
        $now = now();

        $jobberPromotion = ServicePromotion::where('provider_id', $providerId)
            ->whereNull('service_id')
            ->where('payment_status', 'paid')
            ->where('expires_at', '>', $now)
            ->exists();

        $servicePromotion = false;

        if ($serviceId) {
            $servicePromotion = ServicePromotion::where('provider_id', $providerId)
                ->where('service_id', $serviceId)
                ->where('payment_status', 'paid')
                ->where('expires_at', '>', $now)
                ->exists();
        }
                $packageTrx = PackageTrx::join(
                'subscription_packages',
                'subscription_packages.id',
                '=',
                'package_transactions.package_id'
            )
            ->where('package_transactions.provider_id', $providerId)
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('subscription_packages.subscription_type', 'regular')
            ->whereDate('package_transactions.end_date', '>=', $now)
            ->select(
                'subscription_packages.featured',
                'subscription_packages.badge',
                'subscription_packages.promoted_jobber',
                'subscription_packages.promoted_service'
            )
            ->orderByDesc('package_transactions.id')
            ->first();

        $topup = PackageTrx::join(
                'subscription_packages',
                'subscription_packages.id',
                '=',
                'package_transactions.package_id'
            )
            ->where('package_transactions.provider_id', $providerId)
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('subscription_packages.subscription_type', 'topup')
            ->whereDate('package_transactions.end_date', '>=', $now)
            ->select(
                'subscription_packages.featured',
                'subscription_packages.badge',
                'subscription_packages.promoted_jobber',
                'subscription_packages.promoted_service'
            )
            ->orderByDesc('package_transactions.id')
            ->first();

        $source = $packageTrx ?? $topup;

        return [
            'featured'         => $source->featured ?? 0,
            'badge'            => $source->badge ?? 0,
            'promotedJobber'   => $jobberPromotion ? 1 : 0,
            'promotedService'  => $servicePromotion ? 1 : 0,
        ];
    }

    public function productonlydetail(Request $request): JsonResponse | View
    {
        session(['link' => url()->current()]);
        $products_f = Product::query()->where('slug', '=', $request->slug)->firstOrFail();
        $result_date = substr($products_f->updated_at, 0, 16);
        $cudate = date("Y-m-d H:i");
        if ($result_date != $cudate) {
            $product_views = Product::find($products_f->id);
            $product_views->views = $products_f->views + 1;
            $product_views->save();
        }
        $products = Product::query()
            ->where('slug', '=', $request->slug)
            ->where('status', 1)
            ->where('verified_status', 1)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                        ->from('users')
                        ->whereColumn('users.id', 'products.user_id')
                        ->whereNull('users.deleted_at');
            })
            ->firstOrFail();

        if ($products) {
            $includedServices = explode(',', $products->include);
        } else {
            $includedServices = [];
        }
        $user_product_count = Product::query()->where('user_id', '=', $products->user_id)->count();
        $order_product_count = Bookings::query()->where('product_id', '=', $products->id)->count();

        $products_details = Productmeta::query()->where('product_id', '=', $products->id)->where('source_key', '=', 'product_image')->first();
        $d = $products->id;
        $products_details1 = Productmeta::where(function ($query) {
            $query->where('source_key', '=', 'Fixed')
                ->orWhere('source_key', '=', 'Hourly')
                ->orWhere('source_key', '=', 'Squre-metter')
                ->orWhere('source_key', '=', 'Minute')
                ->orWhere('source_key', '=', 'Minitue');
        })->where(function ($query) use ($d) {
            $query->where('product_id', '=', $d);
        })->first();

        $videolink = "";
        $product_offeerservice_count = Productmeta::query()->where('product_id', '=', $products->id)->where('source_key', '=', 'service_name')->count();

        $products_details2 = Productmeta::query()->where('product_id', '=', $products->id)->where('source_key', '=', 'service_name')->first();
        $products_details3 = Productmeta::query()->where('product_id', '=', $products->id)->where('source_key', '=', 'service_price')->first();
        $products_details4 = Productmeta::query()->where('product_id', '=', $products->id)->where('source_key', '=', 'service_desc')->first();
        $products_details5 = Productmeta::query()->where('product_id', '=', $products->id)->where('source_key', '=', 'video_link')->count();
        if ($products_details5 != 0) {
            $products_video_details = Productmeta::query()->where('product_id', '=', $products->id)->where('source_key', '=', 'video_link')->first();
            $videolink = $products_video_details->source_Values;
        }
        $user_details = UserDetail::query()->where('user_id', '=', $products->user_id)->first();

        $starRating = DB::table('ratings')
            ->selectRaw('rating, COUNT(*) as count')
            ->where(['product_id' => $products->id, 'parent_id' => 0])
            ->whereNull('deleted_at')
            ->groupBy('rating')
            ->pluck('count', 'rating');

        $starCount = [
            'star1' => $starRating->get(1, 0),
            'star2' => $starRating->get(2, 0),
            'star3' => $starRating->get(3, 0),
            'star4' => $starRating->get(4, 0),
            'star5' => $starRating->get(5, 0),
        ];

        $ratingCount = DB::table('ratings')
            ->selectRaw('COUNT(*) as total_count, ROUND(AVG(rating), 1) as average_rating')
            ->where(['product_id' => $products->id, 'parent_id' => 0])
            ->whereNull('deleted_at')
            ->first();

        $ratingsData = DB::table('ratings')
            ->leftJoin('user_details', 'ratings.user_id', '=', 'user_details.user_id')
            ->select(
                'ratings.id',
                'ratings.rating',
                'ratings.review',
                'user_details.first_name',
                'ratings.review_date',
                'user_details.profile_image',
            )
            ->where('ratings.product_id', $products->id)
            ->where('ratings.parent_id', 0)
            ->whereNull('ratings.deleted_at')
            ->orderBy('ratings.id', 'DESC')
            ->get()
            ->map(function ($rating) {
                $rating->profile_image = $rating->profile_image ? url('storage/profile/' . $rating->profile_image) : url('assets/img/profile-default.png');
                $rating->review_date = Carbon::parse($rating->review_date)->diffForHumans();
                $rating->replies = $this->fetchReplies($rating->id);
                return $rating;
            });

        $ratings = [
            'total_count' => $ratingCount->total_count,
            'avg_rating' => $ratingCount->average_rating,
            'star_rating' => $starCount,
            'ratings' => $ratingsData
        ];

        $product_id = $products->id;
        $productImages = Productmeta::where('product_id', $product_id)
            ->where('source_key', 'product_image')
            ->pluck('source_Values');
        $productServices = AdditionalService::where('service_id', $product_id)
            ->get(['name', 'price', 'duration', 'image']);
        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            return response()->json(['code' => "200", 'message' => __('Page details retrieved successfully.'), 'data' => $products], 200);
        }
        $rating_count = Rating::where('user_id', $products->user_id)->count();

        return view('productdetail', compact('rating_count', 'order_product_count', 'product_offeerservice_count', 'user_product_count', 'user_details', 'products', 'products_details', 'products_details1', 'products_details2', 'videolink', 'products_details3', 'products_details4', 'ratings', 'productImages', 'productServices', 'includedServices'));
    }

    public function productdetail(Request $request): JsonResponse | View
    {
        session(['link' => url()->current()]);
        $products_f = Product::query()->where('slug', '=', $request->slug)->firstOrFail();
        $result_date = substr($products_f->updated_at, 0, 16);
        $cudate = date("Y-m-d H:i");

        $products = Product::query()
            ->where('slug', '=', $request->slug)
            ->where('status', 1)
            ->where('verified_status', 1)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                        ->from('users')
                        ->whereColumn('users.id', 'products.user_id')
                        ->whereNull('users.deleted_at');
            })
            ->firstOrFail();

        if ($result_date != $cudate) {
            $product_views = Product::find($products_f->id);
            $product_views->views = $products_f->views + 1;
            $product_views->save();
        }

        if ($products) {
            $includedServices = explode(',', $products->include);
        } else {
            $includedServices = [];
        }

        $user_product_count = Product::query()->where('user_id', '=', $products->user_id)->count();
        $order_product_count = Bookings::query()->where('product_id', '=', $products->id)->count();

        $products_details = Productmeta::query()->where('product_id', '=', $products->id)
            ->where('source_key', '=', 'product_image')->first();

        $d = $products->id;
        $products_details1 = Productmeta::where(function ($query) {
            $query->where('source_key', '=', 'Fixed')
                ->orWhere('source_key', '=', 'Hourly')
                ->orWhere('source_key', '=', 'Squre-metter')
                ->orWhere('source_key', '=', 'Minute')
                ->orWhere('source_key', '=', 'Minitue')
                ->orWhere('source_key', '=', 'Square-feet');
        })->where(function ($query) use ($d) {
            $query->where('product_id', '=', $d);
        })->first();

        $videolink = "";
        $product_offeerservice_count = Productmeta::query()->where('product_id', '=', $products->id)
            ->where('source_key', '=', 'service_name')->count();

        $products_details2 = Productmeta::query()->where('product_id', '=', $products->id)
            ->where('source_key', '=', 'service_name')->first();
        $products_details3 = Productmeta::query()->where('product_id', '=', $products->id)
            ->where('source_key', '=', 'service_price')->first();
        $products_details4 = Productmeta::query()->where('product_id', '=', $products->id)
            ->where('source_key', '=', 'service_desc')->first();
        $products_details5 = Productmeta::query()->where('product_id', '=', $products->id)
            ->where('source_key', '=', 'video_link')->count();

        if ($products_details5 != 0) {
            $products_video_details = Productmeta::query()->where('product_id', '=', $products->id)
                ->where('source_key', '=', 'video_link')->first();
            $videolink = $products_video_details->source_Values;
        }

        $user_details = UserDetail::with('user:id,email,phone_number')->where('user_id', '=', $products->user_id)->first();

        // Get provider social links and merge with icon data
        $provider_social_links = DB::table('provider_social_links')
            ->where('provider_id', $user_details->user_id)
            ->where('status', 1)
            ->get();

        // Get social link icons configuration
        $social_links_config = DB::table('social_links') // Adjust table name as needed
            ->select('platform_name', 'icon')
            ->get()
            ->keyBy('platform_name');

        // Merge social links with their icons
        $merged_social_links = $provider_social_links->map(function($link) use ($social_links_config) {
            $platform = $link->platform_name; // Make sure this field matches in both tables
            $link->icon = $social_links_config[$platform]->icon ?? null;
            return $link;
        });

        $starRating = DB::table('ratings')
            ->selectRaw('rating, COUNT(*) as count')
            ->where(['product_id' => $products->id, 'parent_id' => 0])
            ->whereNull('deleted_at')
            ->groupBy('rating')
            ->pluck('count', 'rating');

        $starCount = [
            'star1' => $starRating->get(1, 0),
            'star2' => $starRating->get(2, 0),
            'star3' => $starRating->get(3, 0),
            'star4' => $starRating->get(4, 0),
            'star5' => $starRating->get(5, 0),
        ];

        $ratingCount = DB::table('ratings')
            ->selectRaw('COUNT(*) as total_count, ROUND(AVG(rating), 1) as average_rating')
            ->where(['product_id' => $products->id, 'parent_id' => 0])
            ->whereNull('deleted_at')
            ->first();

        $ratingsData = DB::table('ratings')
            ->leftJoin('user_details', 'ratings.user_id', '=', 'user_details.user_id')
            ->select(
                'ratings.id',
                'ratings.rating',
                'ratings.review',
                'user_details.first_name',
                'ratings.review_date',
                'user_details.profile_image',
            )
            ->where('ratings.product_id', $products->id)
            ->where('ratings.parent_id', 0)
            ->whereNull('ratings.deleted_at')
            ->orderBy('ratings.id', 'DESC')
            ->get()
            ->map(function ($rating) {
                $rating->profile_image = $rating->profile_image ? url('storage/profile/' . $rating->profile_image) : url('assets/img/profile-default.png');
                $rating->review_date = Carbon::parse($rating->review_date)->diffForHumans();
                $rating->replies = $this->fetchReplies($rating->id);
                return $rating;
            });

        $ratings = [
            'total_count' => $ratingCount->total_count,
            'avg_rating' => $ratingCount->average_rating,
            'star_rating' => $starCount,
            'ratings' => $ratingsData
        ];

        $product_id = $products->id;
        $productImages = Productmeta::where('product_id', $product_id)
            ->where('source_key', 'product_image')
            ->pluck('source_Values');
        $productServices = AdditionalService::where('service_id', $product_id)
            ->get(['name', 'price', 'duration', 'image']);

        $rating_count = Rating::where('user_id', $products->user_id)->count();
        $currecy_details = Cache::remember('currecy_details', 86400, function () {
            return Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();
        });

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $products->source_description = strip_tags(html_entity_decode($products->source_description ?? ''));
            $products['products_gallery'] = $products_details;
            $products['products_pricetype'] = $products_details1;
            $products['service_name'] = $products_details2;
            $products['service_price'] = $products_details3;
            $products['service_desc'] = $products_details4;
            $products['videolink'] = $videolink;
            $products['rating_count'] = $rating_count;
            $products['currecy_details'] = $currecy_details;
            $products['order_product_count'] = $order_product_count;
            $products['product_offeerservice_count'] = $product_offeerservice_count;
            $products['user_product_count'] = $user_product_count;
            $products['user_details'] = $user_details;
            $products['ratings'] = $ratings;
            $products['productImages'] = $productImages;
            $products['productServices'] = $productServices;
            $products['includedServices'] = $includedServices;
            $products['provider_social_links'] = $merged_social_links;

            $country_details = Country::query()->where('id', '=', $products['country'])->value('name');
            $state_details = State::query()->where('id', '=', $products['state'])->value('name');
            $city_details = City::query()->where('id', '=', $products['city'])->value('name');

            $products['country'] = $country_details;
            $products['state'] = $state_details;
            $products['city'] = $city_details;

            $country_details = Country::query()->where('id', '=', $user_details['country'])->value('name');
            $state_details = State::query()->where('id', '=', $user_details['state'])->value('name');
            $city_details = City::query()->where('id', '=', $user_details['city'])->value('name');

            $user_details['country'] = $country_details;
            $user_details['state'] = $state_details;
            $user_details['city'] = $city_details;

            return response()->json(['code' => "200", 'message' => __('Page details retrieved successfully.'), 'data' => $products], 200);
        }

        $country_details = Country::query()->where('id', '=', $user_details['country'])->value('name');
        $state_details = State::query()->where('id', '=', $user_details['state'])->value('name');
        $city_details = City::query()->where('id', '=', $user_details['city'])->value('name');

        $fullAddress = $user_details['address'] . ', ' . $city_details . ', ' . $state_details . ', ' . $country_details;
        $address = str_replace(' ', '+', $fullAddress);

        $googleMapUrl = 'https://www.google.com/maps/embed/v1/place?key='
            . config('services.google_maps.key')
            . '&q=' . urlencode($address);

        $locationSetting = DB::table('general_settings')->where('key', 'location_status')->first();
        $locationEnabled = $locationSetting && $locationSetting->value == 1;

        $mapHasError = !$locationEnabled || $this->isInvalidGoogleMapKey($googleMapUrl);

        return view('servicedetail', compact(
            'rating_count',
            'currecy_details',
            'order_product_count',
            'product_offeerservice_count',
            'user_product_count',
            'user_details',
            'products',
            'products_details',
            'products_details1',
            'products_details2',
            'videolink',
            'products_details3',
            'products_details4',
            'ratings',
            'productImages',
            'productServices',
            'includedServices',
            'googleMapUrl',
            'address',
            'mapHasError',
            'merged_social_links'
        ));
    }

    public function editservice(Request $request): View | JsonResponse
    {
        $products = Product::query()->where('id', '=', $request->id)->firstOrFail();
        if ($products) {
            $includedServices = explode(',', $products->include);
        } else {
            $includedServices = [];
        }

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

        $products_details2 = Productmeta::query()->where('product_id', '=', $products->id)->where('source_key', '=', 'service_name')->first();
        $products_details3 = Productmeta::query()->where('product_id', '=', $products->id)->where('source_key', '=', 'service_price')->first();
        $products_details4 = Productmeta::query()->where('product_id', '=', $products->id)->where('source_key', '=', 'service_desc')->first();
        $user_details = UserDetail::query()->where('user_id', '=', $products->user_id)->first();

        $product_id = $products->id;
        $productImages = Productmeta::where('product_id', $product_id)
            ->where('source_key', 'product_image')
            ->pluck('source_Values', 'id')
            ->map(function ($imagePath) {
                return 'storage/' . $imagePath;
            });
        $productServices = AdditionalService::where('service_id', $product_id)
            ->get(['name', 'price', 'duration', 'image']);
        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            return response()->json(['code' => "200", 'message' => __('Page details retrieved successfully.'), 'data' => $products], 200);
        }
        $subCategories = Categories::select('id', 'name')->where('id', $products->source_subcategory)->get();
        $addservices = AdditionalService::select('id', 'service_id', 'name', 'price', 'duration', 'image')->where('service_id', $products->id)->get();

        $location = Product::select('country', 'state', 'city')
            ->where('id', '=', $request->id)
            ->first();

        return view('admin.editservice', compact('products', 'subCategories', 'addservices', 'productImages', 'location'));
    }

    public function addComments(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'review' => 'required',
            'product_id' => 'required',
            'user_id' => 'required',
        ], [
            'review.required' => __('Review is required.'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'code' => 422,
                'errors' => $validator->messages()->toArray(),
            ], 422);
        }

        $userId = Auth::id() ?? $request->user_id;
        $parentId = $request->parent_id ?? 0;
        $bookingId = $request->booking_id ?? null;
        $isBooking = isBookingCompleted($request->product_id, $userId, $bookingId);

        $parentReview = Rating::where('id', $parentId)->value('booking_id');
        
        if ($parentReview) {
            $bookingId = $parentReview;
        }

        if ($parentId == 0) {
            if (!$isBooking) {
                return response()->json([
                    'status' => 'error',
                    'code' => 403,
                    'message' => __('You can only leave a review after the order for this service is completed.')
                ], 403);
            }
        } else {
            $productUserId = getProductUserId($request->product_id);
            if (!$isBooking && ($productUserId != $userId)) {
                return response()->json([
                    'status' => 'error',
                    'code' => 403,
                    'message' => __('You can only leave a reply after the order for this service is completed.'),
                ], 403);
            }
        }

        $data = [
            'review' => $request->review,
            'rating' => $request->rating,
            'parent_id' => $parentId,
            'product_id' => $request->product_id,
            'user_id' => $userId,
            'review_date' => Carbon::now(),
            'booking_id' => $bookingId
        ];

        try {
            DB::table('ratings')->insert($data);

            return response()->json([
                'code' => 200,
                'message' => __('Review added successfully.'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('Error! while adding review'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listComments(Request $request): JsonResponse | View
    {
        $product_id = $request->product_id;
        $limit = $request->per_page ?? 10;
        $offset = $request->skip ?? 0;

        try {

            $starRating = DB::table('ratings')
                ->selectRaw('rating, COUNT(*) as count')
                ->where(['product_id' => $product_id, 'parent_id' => 0])
                ->whereNull('deleted_at')
                ->groupBy('rating')
                ->pluck('count', 'rating');

            $starCount = [
                'star5' => $starRating->get(5, 0),
                'star4' => $starRating->get(4, 0),
                'star3' => $starRating->get(3, 0),
                'star2' => $starRating->get(2, 0),
                'star1' => $starRating->get(1, 0),
            ];

            $ratingCount = DB::table('ratings')
                ->selectRaw('COUNT(*) as total_count, ROUND(AVG(rating), 1) as average_rating')
                ->where(['product_id' => $product_id, 'parent_id' => 0])
                ->whereNull('deleted_at')
                ->first();

            $rating_counts = [
                'total_count' => $ratingCount->total_count,
                'avg_rating' => $ratingCount->average_rating,
                'star_count' => $starCount,
            ];

            $ratings = DB::table('ratings')
                ->leftJoin('user_details', 'ratings.user_id', '=', 'user_details.user_id')
                ->select(
                    'ratings.id',
                    'ratings.rating',
                    'ratings.review',
                    'user_details.first_name',
                    'user_details.last_name',
                    'ratings.review_date',
                    'user_details.profile_image',
                )
                ->where('ratings.product_id', $product_id)
                ->where('ratings.parent_id', 0)
                ->whereNull('ratings.deleted_at')
                ->orderBy('ratings.id', 'DESC')
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->map(function ($rating) {
                    $rating->profile_image = $rating->profile_image && file_exists(public_path('storage/profile/' . $rating->profile_image))
                        ? url('storage/profile/' . $rating->profile_image) : url('assets/img/profile-default.png');
                    $rating->review_date = Carbon::parse($rating->review_date)->diffForHumans();
                    $fullName = $rating->first_name . ' ' . $rating->last_name;
                    $rating->name = ucwords($fullName);
                    $rating->replies = $this->fetchReplies($rating->id);
                    return $rating;
                });

            $ratingsData = [
                'rating_counts' => $rating_counts,
                'ratings' => $ratings
            ];

            return response()->json([
                'code' => 200,
                'message' => __('Comments retrieved successfully.'),
                'data' => $ratingsData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('Error! while retrieving comments'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    function fetchReplies($parentId)
    {
        return DB::table('ratings')
            ->leftJoin('user_details', 'ratings.user_id', '=', 'user_details.user_id')
            ->select(
                'ratings.id',
                'ratings.review',
                'user_details.first_name',
                'user_details.last_name',
                'ratings.review_date',
                'user_details.profile_image',
            )
            ->where('ratings.parent_id', $parentId)
            ->whereNull('ratings.deleted_at')
            ->get()
            ->map(function ($reply) {
                $reply->profile_image = $reply->profile_image && file_exists(public_path('storage/profile/' . $reply->profile_image))
                    ? url('storage/profile/' . $reply->profile_image) : url('assets/img/profile-default.png');
                $reply->review_date = Carbon::parse($reply->review_date)->diffForHumans();
                $fullName = $reply->first_name . ' ' . $reply->last_name;
                $reply->name = ucwords($fullName);
                $reply->replies = $this->fetchReplies($reply->id);
                return $reply;
            });
    }

    public function getReviewList(Request $request): JsonResponse
    {
        $userId = $request->user_id ?? '';
        $perPage = $request->get('per_page', 7);
        $page = $request->get('page', 1);

        try {
            $query = Product::query()
                ->where('products.source_type', 'service')
                ->join('products_meta', function ($join) {
                    $join->on('products.id', '=', 'products_meta.product_id')
                        ->where('products_meta.source_key', '=', 'product_image');
                })
                ->join('ratings', function ($join) {
                    $join->on('products.id', '=', 'ratings.product_id')
                        ->where(['ratings.parent_id' => 0, 'ratings.deleted_at' => NULL]);
                })
                ->join('user_details', 'ratings.user_id', '=', 'user_details.user_id')
                ->select(
                    'ratings.id',
                    'ratings.review',
                    'ratings.rating',
                    'ratings.review_date',
                    'products.source_name as service_name',
                    DB::raw('MIN(products_meta.source_values) as service_image'),
                    'user_details.profile_image',
                    DB::raw('CONCAT(user_details.first_name, " ", user_details.last_name) as full_name')
                );

            if ($userId) {
                $query->where('products.user_id', $userId);
            }

            $services = $query->orderBy('ratings.id', 'DESC')
                ->groupBy(
                    'ratings.id',
                    'ratings.review',
                    'ratings.rating',
                    'ratings.review_date',
                    'products.source_name',
                    'user_details.profile_image',
                    'user_details.first_name',
                    'user_details.last_name'
                )
                ->paginate($perPage);

            $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
            $timeformatSetting = GlobalSetting::where('key', 'time_format_view')->first();
            $timezoneSetting = GlobalSetting::where('key', 'timezone_format_view')->first();

            $dateFormat = $dateformatSetting->value ?? 'Y-m-d';
            $timeFormat = $timeformatSetting->value ?? 'H:i:s';
            $timezone = $timezoneSetting->value ?? 'Asia/Kolkata';

            $services->getCollection()->transform(function ($service) use ($dateFormat, $timeFormat, $timezone) {
                $service->service_image = url('storage/' . $service->service_image);
                $service->profile_image = file_exists(public_path('storage/profile/' . $service->profile_image)) ? url('storage/profile/' . $service->profile_image) : url('assets/img/profile-default.png');
                if ($service->review_date) {
                    $service->review_date = formatDateTime($service->review_date, true);
                }
                return $service;
            });

            return response()->json([
                'code' => 200,
                'message' => __('Reviews retrieved successfully.'),
                'data' => $services
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('Error! while retrieving reviews'),
                'error' => $e->getMessage()
            ], 500);
        }
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
}