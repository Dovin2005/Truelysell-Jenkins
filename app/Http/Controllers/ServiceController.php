<?php

namespace App\Http\Controllers;

use App\Models\AddonModule;
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
use App\Models\SocialMediaShare;
use App\Models\ProviderSocialLink;
use Modules\GlobalSetting\App\Models\SocialLink;

class ServiceController extends Controller
{
    protected $serviceRepository;

    public function __construct(ServiceRepositoryInterface $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function productlistcategory(Request $request, $slug, $is_mobile = false): JsonResponse | View
    {
        $response = $this->serviceRepository->productlistcategory($request, $slug, $is_mobile);
        return $response;
    }
    public function viewcart(Request $request)
    {
        $currecy_details = Cache::remember('currecy_details', 86400, function () {
            return Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();
        });

        $shoppingCart = session('shoppingCart');
        return view('cart', compact('shoppingCart', 'currecy_details'));
    }
    public function catlist(Request $request): JsonResponse | View
    {
        $response = $this->serviceRepository->catlist($request);
        return $response;
    }

    public function removefromcart(Request $request)
    {
        $productId = $request->id;
        $shoppingCart = session('shoppingCart', []);

        if (!isset($shoppingCart[$productId])) {
            // should not happen, and should throw an error.
            return null;
        } else {
            unset($shoppingCart[$productId]);
        }

        session(['shoppingCart' => $shoppingCart]);
    }
    public function addtocart(Request $request): JsonResponse
    {
        $response = $this->serviceRepository->addtocart($request);
        return $response;
    }
    public function onlyproductlist(Request $request): JsonResponse | View
    {
        $response = $this->serviceRepository->onlyproductlist($request);
        return $response;
    }
    public function productlist(Request $request): JsonResponse | View
    {
        $response = $this->serviceRepository->productlist($request);
        return $response;
    }

    public function productonlydetail(Request $request): JsonResponse | View
    {
        $response = $this->serviceRepository->productonlydetail($request);
        return $response;
    }

    public function productdetail(Request $request): JsonResponse | View
    {
        $response = $this->serviceRepository->productdetail($request);
        return $response;
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

    public function editservice(Request $request): View | JsonResponse
    {
        $response = $this->serviceRepository->editservice($request);
        return $response;
    }

    public function add(Request $request): View
    {
        $getplaceholder = Placeholders::select('placeholder_name', 'id')->where('status', 1)->where('deleted_at', null)->get();
        return view('admin.invoice-template', compact('getplaceholder'));
    }

    public function showFormCategories(Request $request): View
    {
        $categoryId = $request->session()->get('category_id');

        $categoryName = Categories::where('id', $categoryId)->value('name');

        return view('admin.form-categories', compact('categoryId', 'categoryName'));
    }

    public function addComments(Request $request): JsonResponse
    {
        $response = $this->serviceRepository->addComments($request);
        return $response;
    }

    public function listComments(Request $request): JsonResponse | View
    {
        $response = $this->serviceRepository->listComments($request);
        return $response;
    }

    public function getReviewList(Request $request): JsonResponse
    {
        $response = $this->serviceRepository->getReviewList($request);
        return $response;
    }

    public function deleteReview(Request $request): JsonResponse
    {
        $reviewId = $request->review_id;

        try {
            Rating::where('id', $reviewId)->delete();

            return response()->json([
                'code' => 200,
                'message' => __('Review deleted successfully.'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('Error! while deleting review'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $country = Country::orderBy('id', 'desc')->get();
        return view("admin.addservice", compact("country"));
    }

    public function getState(Request $request)
    {
        $countryId = $request->country_id;
        $state = State::where('country_id', $countryId)->orderBy('id', 'desc')->get();
        return response()->json($state);
    }

    public function getCity(Request $request)
    {
        $stateId = $request->state_id;
        $city = City::where('state_id', $stateId)->orderBy('id', 'desc')->get();
        return response()->json($city);
    }

    public function getSocialShares(Request $request)
    {
        $socialshareLinks = SocialMediaShare::where('status', 1)->orderBy('id', 'desc')->get();
        return response()->json([
            'code' => 200,
            'message' => __('Social Shares retrieved successfully.'),
            'data' => $socialshareLinks
        ], 200);
    }

    public function getFooterLinks(Request $request)
    {
        $footerSocialLinks = SocialLink::where('status', 1)->orderBy('id', 'desc')->get();
        return response()->json([
            'code' => 200,
            'message' => __('Footer Social Links retrieved successfully.'),
            'data' => $footerSocialLinks
        ], 200);
    }

    public function getProviderSocialLinks(Request $request)
    {
        $providerId = $request->query('provider_id');

        if (!$providerId) {
            return response()->json([
                'code' => 422,
                'message' => 'provider_id is required.',
            ], 422);
        }

        $provider_social_links = ProviderSocialLink::with('socialMedia')
            ->where('provider_id', $providerId)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'provider_id' => $item->provider_id,
                    'social_link_id' => $item->social_link_id,
                    'platform_name' => $item->platform_name,
                    'link' => $item->link,
                    'status' => $item->status,
                    'icon' => $item->socialLink->icon ?? null,
                ];
            });

        return response()->json([
            'code' => 200,
            'message' => __('Provider Social Links retrieved successfully.'),
            'data' => $provider_social_links,
        ]);
    }

    public function addonStatusPayment()
    {
        $status = AddonModule::where('slug', 'paymentgateway')
            ->where('status', 1)
            ->exists();

        return response()->json([
            'status' => $status
        ]);
    }
}
