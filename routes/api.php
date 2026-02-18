<?php

use App\Http\Controllers\AddonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SubscriptionController;
use Modules\Faq\app\Http\Controllers\FaqController;
use Modules\GlobalSetting\app\Http\Controllers\LanguageController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ChatgptController;
use App\Http\Controllers\PayPalWebhookController;
use App\Http\Controllers\ShopsController;
use Modules\GlobalSetting\app\Http\Controllers\FileStorageController;
use Modules\Service\app\Http\Controllers\ServiceController as ControllersServiceController;
use App\Http\Controllers\ProviderSocialLinkController;
use App\Http\Controllers\StripeWebhookController;

Route::post('/userregister', [AuthController::class, 'register'])->name('userregister');
Route::post('/userlogin', [AdminLoginController::class, 'userlogin']);
Route::post('/addtocart', [ServiceController::class, 'addtocart'])->name('addtocart');
Route::post('/removefromcart', [ServiceController::class, 'removefromcart'])->name('removefromcart');


Route::post('/loginapi', [AuthController::class, 'loginapi'])->name('loginapi');
Route::post('/loginapi1', [AuthController::class, 'loginapi'])->name('loginapi1');
Route::post('/user-logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/detail',[AuthController::class,'detail'])->middleware('auth:sanctum');
Route::get('/Userdetail',[AuthController::class,'Userdetail'])->middleware('auth:sanctum');
Route::get('user/bookinglist',[BookingController::class,'userBookinglist'])->name('user.bookinglistapi')->middleware('auth:sanctum');

Route::post('/save-profile-details', [UserController::class, 'saveProfileDetails']);
Route::post('/get-profile-details', [UserController::class, 'getProfileDetails']);
Route::post('/getuserlist', [UserController::class, 'getuserlist']);
Route::post('/user/addfavour', [UserController::class, 'addfavour'])->name('user.addfavour');
Route::post('live_mobile',[StripeController::class,'live_mobile'])->name('live_mobile_p');
Route::post('live_mobile_pay',[StripeController::class,'live_mobile_pay'])->name('live_mobile_pay');
Route::post('sub-payment-success',[StripeController::class,'sub_payment_success'])->name('sub_payment_success');
Route::post('/strip-payment-success', [BookController::class, 'stripPaymentSuccess'])->name('strips.payment.success');

Route::post('/save-admin-details', [AdminLoginController::class, 'saveAdminDetails']);
Route::post('/get-admin-details', [AdminLoginController::class, 'getAdminDetails']);
Route::post('/admin/change-password', [AdminLoginController::class, 'changePassword']);
Route::post('/change-password', [AdminLoginController::class, 'changePassword']);
Route::post('/user/check-unique', [UserController::class, 'checkUnique']);
Route::post('/admin/check-password', [AdminLoginController::class, 'checkPassword']);
Route::post('/forgot/check-password', [UserController::class, 'checkPassword']);
Route::get('/people/get-status', [UserController::class, 'getpeoplestatus']);
Route::post('/admin/deleteuser', [UserController::class, 'deleteuser']);
Route::post('updatebookingstatus',[BookingController::class, 'updatebookingstatus']);
Route::post('cancel-calendar-event',[BookingController::class, 'cancelCalendarEvent']);
Route::get('/bookings', [BookingController::class, 'getBookings']);
Route::post('/bookinglists', [BookingController::class, 'getBookinglists']);
Route::post('/booking/request-dispute', [BookingController::class, 'indexRequest']);
Route::post('/booking/raise-dispute/update', [BookingController::class, 'UpdateRequest']);

Route::post('/provider/get-staff-list', [UserController::class, 'getStaffList']);
Route::post('/provider/delete-staff', [UserController::class, 'deleteStaff']);

Route::post('/add-comments', [ServiceController::class, 'addComments']);
Route::post('/list-comments', [ServiceController::class, 'listComments']);
Route::get('/social-shares', [ServiceController::class, 'getSocialShares']);
Route::get('/footer-social-links', [ServiceController::class, 'getFooterLinks']);
Route::get('/provider-profile-links', [ServiceController::class, 'getProviderSocialLinks']);

/* provider Dashboard */
Route::post('/getsubscription',[ProviderController::class,'getsubscription'])->name('provider.getsubscription');
Route::post('/gettotalbookingcount',[ProviderController::class,'gettotalbookingcount'])->name('provider.totalcount');
Route::post('/getlatestbookings',[ProviderController::class,'getlatestbookings'])->name('provider.getlatestbookings');
Route::post('/getlatestreviews',[ProviderController::class,'getlatestreviews'])->name('provider.getlatestreviews');
Route::post('/getsubscribedpack',[ProviderController::class,'getsubscribedpack'])->name('provider.getsubscribedpack');
Route::post('/providerbookings', [ProviderController::class, 'providergetBookings']);

Route::post('/getlatestbookingsapi',[ProviderController::class,'getlatestbookingsapi'])->name('provider.getlatestbookingsapi')->middleware('auth:sanctum');
Route::post('/getlatestreviewsapi',[ProviderController::class,'getlatestreviewsapi'])->name('provider.getlatestreviewsapi')->middleware('auth:sanctum');
Route::post('/getsubscribedpackapi',[ProviderController::class,'getsubscribedpackapi'])->name('provider.getsubscribedpackapi')->middleware('auth:sanctum');
Route::get('/providerbookingsapi', [ProviderController::class, 'providergetBookingsapi'])->middleware('auth:sanctum');
Route::post('/providerbookings-list', [ProviderController::class, 'providergetBookApi'])->middleware('auth:sanctum');
Route::post('/gettotalbookingcountapi',[ProviderController::class,'gettotalbookingcountapi'])->name('provider.totalcountapi')->middleware('auth:sanctum');

Route::post('/getlatestproductservice',[ProviderController::class,'getlatestproductservice'])->name('provider.getlatestproductservice');

Route::post('/staff/getlatestproductservice',[StaffController::class,'getlatestproductservice'])->name('staff.getlatestproductservice');
Route::post('/staff/getlatestbookings',[StaffController::class,'getlatestbookings'])->name('staff.getlatestbookings');
Route::post('/staff/getlatestreviews',[StaffController::class,'getlatestreviews'])->name('staff.getlatestreviews');
Route::post('/staff/get-total-bookingcount',[StaffController::class,'getTotalBookingCount'])->name('staff.getTotalBookingCount');
Route::post('/getdefaultcurrency', [Controller::class, 'getdefaultcurrency']);
Route::post('/userdashboard', [UserController::class, 'getUserDashboard']);
Route::get('/userdashboarddata', [UserController::class, 'getUserDashboardapi'])->middleware('auth:sanctum');
Route::post('/delete-account', [UserController::class, 'deleteAccount']);

Route::post('/transactionlist', [TransactionController::class, 'listTransactions']);
Route::post('/transactionlistapi', [TransactionController::class, 'listTransactionsapi'])->middleware('auth:sanctum');
Route::post('/upload-payment-proof', [TransactionController::class, 'uploadPaymentProof']);
Route::post('/provider/details', [TransactionController::class, 'getProviderDetails']);
Route::post('/providertransactionlist', [TransactionController::class, 'providerTransaction']);
Route::post('/storePayoutHistroy', [TransactionController::class, 'storePayoutHistroy']);
Route::post('/save-payout-details', [TransactionController::class, 'savePayouts']);
Route::post('/get-payout-details', [TransactionController::class, 'getPayoutDetails']);
Route::post('/storepackagetransaction', [SubscriptionController::class, 'storepacktrx']);
Route::post('/getpaymentmethod', [SubscriptionController::class, 'getpaymentmethod']);
Route::post('/getpaymentmethodProvider', [SubscriptionController::class, 'getpaymentmethodProvider']);
Route::post('/provider/get-payout-history', [TransactionController::class, 'getProviderPayoutHistory']);
Route::post('/provider/get-payout-request', [TransactionController::class, 'getProviderPayoutRequest']);
Route::post('/list/provider/request', [TransactionController::class, 'listProviderRequest']);
Route::post('/updateproviderrequest', [TransactionController::class, 'updateProviderRequest']);
Route::post('/provider/send-request-amount', [TransactionController::class, 'sendProviderRequestAmount']);
Route::post('/get-provider-balance', [TransactionController::class, 'getProviderBalance']);
Route::post('/userpayoutrequestlist', [TransactionController::class, 'userpayoutrequestlist'])->name('admin.userpayoutrequestlist');
Route::post('/updaterefund',[TransactionController::class, 'updaterefund'])->name('admin.updaterefund');
Route::post('/getsubscriptionlist', [SubscriptionController::class, 'getsubscriptionlist'])->name('admin.getsubscriptionlist');
Route::post('/getsubscriptionhistorylist', [SubscriptionController::class, 'getsubscriptionhistorylist'])->name('provider.subscriptionhistorys');
Route::post('/save-contact-details', [ContactController::class, 'store']);
Route::post('get-review-list', [ServiceController::class, 'getReviewList']);
Route::post('delete-review', [ServiceController::class, 'deleteReview']);
Route::post('translate', [LanguageController::class, 'translate']);
Route::post('/admin/get-faq', [FaqController::class, 'getFaq']);
//booking list
Route::post('/provider/bookinglist', [BookingController::class, 'providerindex']);
//get staff
Route::post('/get-staff', [ProviderController::class, 'getStaffDetailsApi']);
Route::post('/provider/getstafflist', [ProviderController::class, 'getstafflist']);
Route::get('getstaffBookings',[StaffController::class,'getstaffBookings']);
Route::post('/addWalletAmount', [WalletController::class, 'addWalletAmount']);
Route::post('/addWalletAmountApi', [WalletController::class, 'addWalletAmountApi']);
Route::post('/walletsucessApi', [WalletController::class, 'walletsucessApi']);
Route::post('/walletHistory', [WalletController::class, 'listWalletHistory']);
Route::post('/ticket/list',[TicketController::class,'index'])->name('user.ticketlist');
Route::post('/user/addticket',[TicketController::class,'store'])->name('user.addticket');
Route::post('/provider/addticket',[TicketController::class,'store'])->name('provider.addticket');
Route::post('updateticketstatus',[TicketController::class,'updateticketstatus']);
Route::post('ticket/storehistory',[TicketController::class,'storehistory']);

Route::get('/get-countries', [BranchController::class, 'getCountries']);
Route::get('/get-states', [BranchController::class, 'getStates']);
Route::get('/get-cities', [BranchController::class, 'getCities']);

Route::post('/admin/get-staff-list', [UserController::class, 'getStaffList'])->middleware('auth:sanctum');
Route::post('/admin/delete-staff', [UserController::class, 'deleteStaff']);
Route::post('/admin/staff-status-change', [StaffController::class, 'staffStatusChange']);
//My Boooking
Route::post('/user/service-booking', [BookController::class, 'serviceBooking'])->name('user.booking.location.service_booking')->middleware('auth:sanctum');
Route::post('/get-branch-staff', [BookController::class, 'getStaffs'])->middleware('auth:sanctum');
Route::post('/get-slot', [BookController::class, 'getSlot'])->middleware('auth:sanctum');
Route::post('/get-branch-staff-info', [BookController::class, 'getInfo'])->middleware('auth:sanctum');
Route::post('/get-slot-info', [BookController::class, 'getSlotInfo'])->middleware('auth:sanctum');
Route::post('/get-payout', [BookController::class, 'getPayout']);
Route::post('/get-payout-api', [BookController::class, 'getPayoutApi'])->middleware('auth:sanctum');
Route::post('/user/payment', [BookController::class, 'payment'])->middleware('auth:sanctum');
Route::post('/chatgpt', [ChatgptController::class, 'chat']);

Route::prefix('file-storage')->group(function () {
    Route::post('/list', [FileStorageController::class, 'index']);
    Route::post('/status/local', [FileStorageController::class, 'statuslocal']);
    Route::post('/status/aws', [FileStorageController::class, 'statusAws']);
    Route::post('/save/aws', [FileStorageController::class, 'storeAws']);
});

Route::post('/branch/save', [BranchController::class, 'saveBranch'])->middleware('auth:sanctum');
Route::post('/branch/list', [BranchController::class, 'index'])->middleware('auth:sanctum');
Route::post('/branch/delete', [BranchController::class, 'deleteBranch'])->middleware('auth:sanctum');
Route::post('/branch/update', [BranchController::class, 'updateBranch'])->name('provider.updatebranch');

Route::post('/user-register', [UserController::class, 'register'])->name('userregister');
Route::post('/provider/register', [UserController::class, 'providerRegister'])->name('provider.register');
Route::post('/user/deviceslist', [UserController::class, 'getUserDevices']);

Route::post('/admin/addon-module-list', [AddonController::class, 'index']);
Route::post('/admin/change-addon-status', [AddonController::class, 'changeAddonStatus']);
Route::post('/new-addon-modules', [AddonController::class, 'listNewAddonModules']);
Route::post('/purchase-module', [AddonController::class, 'purchaseModule']);
Route::post('/device/delete', [UserController::class, 'devideDelete'])->name('device.delete');

Route::post('/wallet/check', [BookingController::class, 'WalletCheck']);

Route::post('/addleadsAmountApi', [WalletController::class, 'addleadsAmountApi']);
Route::post('/stripesucessApi', [WalletController::class, 'stripesucessApi']);

Route::get('/get-states', [ServiceController::class, 'getState'])->name('getState');
Route::get('/get-city', [ServiceController::class, 'getCity'])->name('getCity');

Route::post('/get-customer-provider', [ProviderController::class, 'getCustomer']);
Route::post('/fetch-staff-service', [ProviderController::class, 'fetchStaffService']);
Route::post('/get-branch', [ProviderController::class, 'getBranchStaff']);
Route::post('/get-staff-slot', [StaffController::class, 'getStaffSlot']);


Route::post('/get-user', [ProviderController::class, 'getUserList']);
Route::post('/get-service', [ProviderController::class, 'getServiceList']);
Route::post('/get-branch', [ProviderController::class, 'getBranchList']);
Route::post('/get-staffs', [ProviderController::class, 'getStaffLists']);
Route::post('/provider/calender/booking-api', [ProviderController::class, 'providerCalenderBookingApi']);
Route::post('/provider/branch/check-limit', [BranchController::class, 'providerBranchLimitApi']);
Route::post('/provider/staff/check-limit', [StaffController::class, 'providerStaffLimitApi']);
Route::post('/user-update-password-api', [UserController::class, 'forgotPasswordApi']);

Route::post('/booking/dispute',[BookingController::class,'requestDisputeApi'])->name('user.requestDispute');
Route::post('/booking/dispute/view',[BookingController::class,'getDisputeDetailsApi'])->name('user.requestDispute');

Route::get('/provider-social-links', [ProviderSocialLinkController::class, 'getProviderSocialLinksApi']);
Route::post('/provider-social-links/store', [ProviderSocialLinkController::class, 'saveProviderSocialLinksApi']);

//Chat Module
Route::prefix('chat')->middleware('auth:sanctum')->group(function () {
    Route::get('list', [MessageController::class, 'chatList']);
    Route::post('send-message', [MessageController::class, 'sendMessage']);
    Route::post('get-messages', [MessageController::class, 'getMessages']);
    Route::post('search-users', [MessageController::class, 'searchUsers']);
});

Route::get('/servicedetail/{slug}', [ServiceController::class, 'productdetail']);
Route::get('/services/{slug}/{is_mobile?}', [ServiceController::class, 'productlistcategory']);
Route::get('/services', [ServiceController::class, 'productlist']);
Route::get('/categories', [ServiceController::class, 'catlist']);

Route::post('/update-banktransfer-payment', [SubscriptionController::class, 'updateBankTransferPayment'])->middleware('auth:sanctum');

Route::get('/addon-payment', [ServiceController::class, 'addonStatusPayment']);

//stript webhook routes
Route::post('stripe/subscription/webhook', [StripeWebhookController::class, 'handleSubscriptionWebhook']);
Route::post('webhook/paypal-subscription', [PayPalWebhookController::class, 'handleSubscriptionWebhook']);