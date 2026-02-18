<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

interface ProviderRepositoryInterface
{
    public function index();
    public function getsubscription(Request $request);
    public function gettotalbookingcount(Request $request);
    public function gettotalbookingcountapi(Request $request);
    public function getlatestbookingsapi(Request $request);
    public function getlatestbookings(Request $request);
    public function getlatestreviews(Request $request);
    public function getlatestreviewsapi(Request $request);
    public function getlatestproductservice(Request $request);
    public function getsubscribedpack(Request $request);
    public function getsubscribedpackapi(Request $request);
    public function providerCalendarIndex();
    public function providergetBookingsapi(Request $request);
    public function providergetBookings(Request $request);
    public function providergetBookApi(Request $request);
    public function getStaffDetails(Request $request);
    public function getStaffDetailsApi(Request $request);
    public function getBranchStaff(Request $request);
    public function getCustomer(Request $request);
    public function fetchStaffService(Request $request);
    public function providerCalenderBooking(Request $request);
    public function providerCalenderBookingApi(Request $request);
    public function getUserList(Request $request);
    public function getServiceList(Request $request);
    public function getBranchList(Request $request);
    public function getStaffLists(Request $request);
}
