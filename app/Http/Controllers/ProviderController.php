<?php

namespace App\Http\Controllers;
use App\Repositories\Contracts\ProviderRepositoryInterface;
use Modules\GlobalSetting\Entities\GlobalSetting;
use App\Models\PayoutHistory;
use App\Models\User;
use App\Models\Bookings;
use Modules\GlobalSetting\app\Models\Language;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderController extends Controller
{
    protected ProviderRepositoryInterface $providerRepository;

    public function __construct(ProviderRepositoryInterface $providerRepository)
    {
        $this->providerRepository = $providerRepository;
    }

    public function index()
    {
        $response = $this->providerRepository->index();
        return view('people.list', $response);
    }

    public function getsubscription(Request $request)
    {
        $response = $this->providerRepository->getsubscription($request);
        return response()->json($response, $response['code']);
    }

    public function gettotalbookingcount(Request $request)
    {
        $response = $this->providerRepository->gettotalbookingcount($request);
        return response()->json($response, $response['code']);
    }

    function calculateProviderBalance(int $providerId): float
    {
        $commissionRate = 0;
        $commissionSetting = GlobalSetting::where('key', 'commission_rate_percentage')->first();
        if ($commissionSetting) {
            $commissionRate = (float) $commissionSetting->value;
        }

        $transactions = Bookings::with(['product'])
            ->where('booking_status', 6)
            ->whereHas('product', function ($query) use ($providerId) {
                $query->where('created_by', $providerId);
            })->get();

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
        return number_format($remainingAmount, 2, '.', '');
    }

    public function gettotalbookingcountapi(Request $request)
    {
        $response = $this->providerRepository->gettotalbookingcountapi($request);
        return response()->json($response, $response['code']);
    }

    public function getlatestbookingsapi(Request $request)
    {
        $response = $this->providerRepository->getlatestbookingsapi($request);
        return response()->json($response, $response['code']);
    }

    public function getlatestbookings(Request $request)
    {
        $response = $this->providerRepository->getlatestbookings($request);
        return response()->json($response, $response['code']);
    }

    public function getlatestreviews(Request $request)
    {
        $response = $this->providerRepository->getlatestreviews($request);
        return response()->json($response, $response['code']);
    }

    public function getlatestreviewsapi(Request $request)
    {
        $response = $this->providerRepository->getlatestreviewsapi($request);
        return response()->json($response, $response['code']);
    }

    public function getlatestproductservice(Request $request)
    {
        $response = $this->providerRepository->getlatestproductservice($request);
        return response()->json($response, $response['code']);
    }

    public function getsubscribedpack(Request $request)
    {
        $response = $this->providerRepository->getsubscribedpack($request);
        return response()->json($response, $response['code']);
    }

    public function getsubscribedpackapi(Request $request)
    {
        $response = $this->providerRepository->getsubscribedpackapi($request);
        return response()->json($response, $response['code']);
    }

    public function providerCalendarIndex(Request $request)
    {
        $response = $this->providerRepository->providerCalendarIndex($request);
        return view('provider.booking.calendarnew', $response);
    }

    public function getstafflist()
    {
        $users = User::where('user_type', 4)->get();
        return response()->json($users);
    }

    public function providergetBookingsapi(Request $request)
    {
        $response = $this->providerRepository->providergetBookingsapi($request);
        return response()->json($response);
    }

    public function providergetBookings(Request $request)
    {
        $response = $this->providerRepository->providergetBookings($request);
        return response()->json($response);
    }

    public function providergetBookApi(Request $request)
    {
        $response = $this->providerRepository->providergetBookApi($request);
        return response()->json($response, $response['code']);
    }

    public function getlanguage(): mixed
    {
        $languages = Language::select('id', 'code')
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
        $response = $this->providerRepository->getStaffDetails($request);
        return response()->json($response);
    }

    public function getStaffDetailsApi(Request $request)
    {
        $response = $this->providerRepository->getStaffDetailsApi($request);
        return response()->json($response, $response['code']);
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
        $response = $this->providerRepository->getBranchStaff($request);
        return response()->json($response);
    }

    public function getCustomer(Request $request)
    {
        $response = $this->providerRepository->getCustomer($request);
        return response()->json($response);
    }

    public function fetchStaffService(Request $request)
    {
        $response = $this->providerRepository->fetchStaffService($request);
        return response()->json($response);
    }

    public function providerCalenderBooking(Request $request)
    {
        $response = $this->providerRepository->providerCalenderBooking($request);
        return response()->json($response, $response['code']);
    }

    public function providerCalenderBookingApi(Request $request)
    {
        $response = $this->providerRepository->providerCalenderBookingApi($request);
        return response()->json($response, $response['code']);
    }

    public function getUserList(Request $request)
    {
        $response = $this->providerRepository->getUserList($request);
        return response()->json($response, $response['code']);
    }

    public function getServiceList(Request $request)
    {
        $response = $this->providerRepository->getServiceList($request);
        return response()->json($response, $response['code']);
    }

    public function getBranchList(Request $request)
    {
        $response = $this->providerRepository->getBranchList($request);
        return response()->json($response, $response['code']);
    }

    public function getStaffLists(Request $request)
    {
        $response = $this->providerRepository->getStaffLists($request);
        return response()->json($response, $response['code']);
    }

}
