<?php

namespace App\Http\Controllers;

use Modules\Categories\app\Models\Categories;
use Modules\Product\app\Models\Product;
use App\Models\Bookings;
use Modules\Service\app\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Repositories\Contracts\BookRepositoryInterface;
use Illuminate\Contracts\View\View;

class BookController extends Controller
{
    protected $bookRepository;
    public function __construct(BookRepositoryInterface $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    public function index(Request $request): View
    {
        return view('admin.dashboard'); // Return the view for the dashboard
    }

    public function productlist(Request $request): View
    {
        $products = Product::query()->where('source_type', '=', 'service')->get();
        $products = DB::table('products')
            ->select('products.id', 'products.slug', 'products.source_name', 'products_meta.source_Values')
            ->join('products_meta', 'products.id', '=', 'products_meta.product_id')
            ->where(['products_meta.source_key' => 'product_image'])
            ->get();

        $email = "";
        if (Auth::check()) {
            $email = Auth::user()->email;
        }
        $data = [
            'email' => $email,
        ];
        return view('services', compact('data', 'products'));
    }

    public function productdetail(Request $request): View
    {
        $response = $this->bookRepository->productdetail($request);
        return $response;
    }

    public function showFormCategories(Request $request): View
    {
        $categoryId = $request->session()->get('category_id');

        $categoryName = Categories::where('id', $categoryId)->value('name');

        return view('admin.form-categories', compact('categoryId', 'categoryName'));
    }

    public function getServiceDetails($product_id)
    {
        $service = Service::where('id', $product_id)->first();

        if ($service) {
            return response()->json([
                'success' => true,
                'data' => $service
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }
    }

    public function serviceBooking(Request $request)
    {
        $response = $this->bookRepository->serviceBooking($request);
        return $response;
    }

    public function serviceIndexBooking(Request $request)
    {
        $response = $this->bookRepository->serviceIndexBooking($request);
        return $response;
    }

    public function getStaffs(Request $request)
    {
        $response = $this->bookRepository->getStaffs($request);
        return $response;
    }

    public function getInfo(Request $request)
    {
        $response = $this->bookRepository->getInfo($request);
        return $response;
    }

    public function getPersonalInfo(Request $request)
    {
        $response = $this->bookRepository->getPersonalInfo($request);
        return $response;
    }

    public function getSlot(Request $request)
    {
        $response = $this->bookRepository->getSlot($request);
        return $response;
    }

    public function getSlots(Request $request)
    {
        $response = $this->bookRepository->getSlots($request);
        return $response;
    }


    public function getSlotInfo(Request $request)
    {
        $response = $this->bookRepository->getSlotInfo($request);
        return $response;
    }

    public function getPayout(Request $request)
    {
        $response = $this->bookRepository->getPayout($request);
        return $response;
    }

    public function getPayoutApi(Request $request)
    {
        $response = $this->bookRepository->getPayoutApi($request);
        return $response;
    }

    public function payment(Request $request)
    {
        $response = $this->bookRepository->payment($request);
        return $response;
    }

    public function paypalPaymentSuccess(Request $request)
    {
        $response = $this->bookRepository->paypalPaymentSuccess($request);
        return $response;
    }

    public function stripPaymentSuccess(Request $request)
    {
        $response = $this->bookRepository->stripPaymentSuccess($request);
        return $response;
    }

    public function sucesspaymentMollie(Request $request): View
    {
        Bookings::where('tranaction', Session('paymentid'))->update(['payment_status' => 2]);
        return view('user.booking.service_success_two');
    }

    public function successTwo(Request $request): View
    {
        return view("user.booking.service_success_two");
    }

    public function checkProductUser(Request $request)
    {
        $response = $this->bookRepository->checkProductUser($request);
        return $response;
    }
}
