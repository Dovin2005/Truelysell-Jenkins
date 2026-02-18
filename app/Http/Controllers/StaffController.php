<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetStaffBookingsRequest;
use App\Http\Requests\StaffPaymentRequest;
use App\Http\Requests\StaffSlotRequest;
use App\Http\Requests\StaffStatusChangeRequest;
use App\Repositories\Contracts\StaffRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class StaffController extends Controller
{
    protected StaffRepositoryInterface $staffRepository;

    public function __construct(StaffRepositoryInterface $staffRepository)
    {
        $this->staffRepository = $staffRepository;
    }

    public function index(): View
    {
        return $this->staffRepository->index(Route::currentRouteName());
    }

    public function getDashboard(): View
    {
        $data = $this->staffRepository->getDashboard();
        return view('staff.staffdashboard', compact('data'));
    }

    public function getSubscription(): JsonResponse
    {
        $response = $this->staffRepository->getSubscription();
        return response()->json($response, $response['code']);
    }

    public function getTotalBookingCount(Request $request): JsonResponse
    {
        $response = $this->staffRepository->getTotalBookingCount($request);
        return response()->json($response, $response['code']);
    }

    public function getStaffBookings(GetStaffBookingsRequest $request): JsonResponse
    {
        $response = $this->staffRepository->getStaffBookings($request);
        return response()->json($response);
    }

    public function getLatestBookings(Request $request): JsonResponse
    {
        $response = $this->staffRepository->getLatestBookings($request);
        return response()->json($response, $response['code']);
    }

    public function getLatestReviews(Request $request): JsonResponse
    {
        $response = $this->staffRepository->getLatestReviews($request);
        return response()->json($response, $response['code']);
    }

    public function getLatestProductService(Request $request): JsonResponse
    {
        $response = $this->staffRepository->getLatestProductService($request);
        return response()->json($response, $response['code']);
    }

    public function getSubscribedPack(Request $request): JsonResponse
    {
        $response = $this->staffRepository->getSubscribedPack($request);
        return response()->json($response, $response['code']);
    }

    public function getLanguage(): JsonResponse
    {
        $response = $this->staffRepository->getLanguage();
        return response()->json([
            'code' => 200,
            'success' => true,
            'language_id' => $response,
            'message' => 'Language retrieved successfully'
        ]);
    }

    public function CalendarIndex(): View
    {
        $data = $this->staffRepository->calendarIndex();
        return view('staff.calendar', $data);
    }

    public function providerStaffLimit(Request $request): JsonResponse
    {
        $response = $this->staffRepository->providerStaffLimit($request);
        return response()->json($response, $response['code']);
    }

    public function providerStaffLimitApi(Request $request): JsonResponse
    {
        $response = $this->staffRepository->providerStaffLimitApi($request);
        return response()->json($response, $response['code']);
    }

    public function adminStaff(): View
    {
        $data = $this->staffRepository->adminStaff();
        return view('admin.staffs', $data);
    }

    public function staffStatusChange(StaffStatusChangeRequest $request): JsonResponse
    {
        $response = $this->staffRepository->staffStatusChange($request);
        return response()->json($response, $response['code']);
    }

    public function getCustomer(Request $request): JsonResponse
    {
        $response = $this->staffRepository->getCustomer($request);
        return isset($response['error'])
            ? response()->json($response, 404)
            : response()->json($response);
    }

    public function getStaffSlot(StaffSlotRequest $request): JsonResponse
    {
        $response = $this->staffRepository->getStaffSlot($request);
        return response()->json($response);
    }

    public function payment(StaffPaymentRequest $request): JsonResponse
    {
        $response = $this->staffRepository->payment($request);
        return isset($response['error'])
            ? response()->json($response, 500)
            : response()->json($response);
    }
}