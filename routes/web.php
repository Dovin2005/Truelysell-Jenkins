<?php

use App\Http\Controllers\AddonController;
use Illuminate\Support\Facades\Route;
use Modules\GlobalSetting\app\Http\Controllers\LanguageController;
use Modules\GlobalSetting\app\Http\Controllers\DbbackupController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\StaffController;
use Illuminate\Http\Request;
use Modules\Page\app\Http\Controllers\Api\PageController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\MollieController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Categories\app\Http\Controllers\CategoriesController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\PromotedJobberPaymentController;
use App\Http\Controllers\PromotedServicePaymentController;
use Illuminate\Support\Facades\Artisan;
use Modules\GlobalSetting\app\Http\Controllers\SocialLinkController;
use App\Http\Controllers\ProviderSocialLinkController;
use App\Services\PayPalSubscriptionService;
use Modules\GlobalSetting\app\Http\Controllers\admin\SocialMediaShareController;

Route::middleware('securityHeaders')->group(function () {
    
Route::get('/storage-link', function () {
    Artisan::call('storage:link');
    return redirect()->route('home');
})->name('storage-link');

Route::get('/storage-linkadmin', function () {
    Artisan::call('storage:link');
    return redirect()->route('login');
})->name('storage-linkadmin');

Route::get('db_backup', function () {

    Artisan::call('backup:run');

    dd("Backup done 1");

});
Route::group(['prefix' => 'user'], function() {
    Route::get('/login', function () {
        return view('user.login');
    })->name('userlogin');
    Route::get('/register', function () {
        return view('user.register');
    })->name('userregister');
    Route::post('/userregister', [AuthController::class, 'register'])->name('userregister');
    Route::post('/booking/dispute',[BookingController::class,'requestDispute'])->name('user.requestDispute');

});

Route::get('auth/redirect/{provider}', [SocialiteController::class, 'redirectToProvider'])->name('auth.redirect');
Route::get('auth/{provider}-callback', [SocialiteController::class, 'handleProviderCallback'])->name('auth.callback');
Route::get('auth/{provider}/callback', [SocialiteController::class, 'handleProviderCallback']);

Route::get('admin/login', function (Request $request) {
    if(Auth::check() && (Auth::user()->user_type == 1 || Auth::user()->user_type == 5 )){
        return redirect()->route('admin.dashboard');
    }
    return view('admin.login');

})->name('login');

Route::get('admin', function () {
    if(Auth::check() && (Auth::user()->user_type == 1 || Auth::user()->user_type == 5 )){
        return redirect()->route('admin.dashboard');
    }
    return view('admin.login');
})->name('adminlogin');

Route::post('/admin/login-process', [AdminLoginController::class, 'login']);
Route::get('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
Route::post('/provider/promote/pay', [PromotedServicePaymentController::class, 'pay'])
    ->name('provider.promote.pay');

Route::get('/provider/promote/paypal/success', [PromotedServicePaymentController::class, 'paypalSuccess'])
    ->name('provider.promote.paypal.success');
// routes/web.php
Route::get(
    '/provider/promote/stripe/success',
    [PromotedServicePaymentController::class, 'stripeSuccess']
)->name('provider.promote.stripe.success');

Route::get('/provider/promote/paypal/cancel', [PromotedServicePaymentController::class, 'paypalCancel'])
    ->name('provider.promote.paypal.cancel');
// routes/web.php
Route::get(
    '/provider/promote-service/{service}',
    [PromotedServicePaymentController::class, 'showPromotedServicePage']
    
)->name('provider.promote.service');

Route::get(
    '/provider/promote-profile',
    [PromotedJobberPaymentController::class, 'showPromotedJobberPage']
)->name('provider.promote.jobber');

Route::post(
    '/provider/promote-jobber/pay',
    [PromotedJobberPaymentController::class, 'pay']
)->name('provider.promote.jobber.pay');

Route::get(
    '/provider/promote-jobber/paypal/success',
    [PromotedJobberPaymentController::class, 'paypalSuccess']
)->name('provider.promote.jobber.paypal.success');

Route::get(
    '/provider/promote-jobber/stripe/success',
    [PromotedJobberPaymentController::class, 'stripeSuccess']
)->name('provider.promote.jobber.stripe.success');
Route::group(['prefix' => 'admin', 'middleware' => ['admin.auth', 'permission']], function() {
    Route::get('/dashboard',[AdminDashboardController::class,'index'] )->name('admin.dashboard');
    Route::get('/setting/payment-settings', function () {
        return view('admin.payment-settings');

    })->name('admin.payment-settings');

    Route::get('/services', function () {
        return view('admin.services');

    })->name('admin.services');

    Route::get('/addservice', [ServiceController::class, 'index'])->name('admin.addservice');

    Route::get('/addproduct', function () {
        return view('admin.addproduct');
    })->name('admin.addproduct');

    Route::get('/editservice/{id}', [ServiceController::class, 'editservice'])->name('editservice');
    Route::get('/setting/listwords/{id}', [LanguageController::class, 'listkeywords'])->name('listkeywords');

    Route::post('/savelangword', [LanguageController::class, 'savelangword'])->name('savelangword');
    Route::post('/savedbbackup', [DbbackupController::class, 'savelanguage'])->name('savedbbackup');
    Route::get('/setting/dbdownload/{id}', [DbbackupController::class, 'listkeywords'])->name('dbdownload');

    Route::get('/setting/language-settings', [LanguageController::class, 'languageSettings'])->name('admin.language-settings');

    Route::get('/setting/dbbackuplist', function () {
        return view('admin.db-settings');
    })->name('admin.db-settings');

    Route::get('/content/how-it-work', function () {
        return view('admin.how-it-work');
    })->name('admin.how-it-work');

    Route::get('/categories', function () {
        return view('admin.categories-settings');
    })->name('admin.categories');

    Route::get('/service/categories', [CategoriesController::class, 'servicecategories'])->name('admin.servicecategories');
    Route::get('/service/subcategories', [CategoriesController::class, 'serviceSubcategories'])->name('admin.servicesubcategories');
    Route::get('/form-categories', [AdminDashboardController::class, 'showFormCategories'])->name('admin.form-categories');

    Route::get('/setting/credential-settings', function () {
        return view('admin.credential-settings');
    })->name('admin.credential-settings');

    Route::get('/setting/subscription-package', function () {
        return view('admin.subscription-package');
    })->name('admin.subscription-package');

    Route::get('/content/faq', function () {
        return view('admin.faq-setting');
    })->name('admin.faq');

    Route::get('/transaction', function () {
        return view('admin.transaction');
    })->name('admin.transaction');

    Route::get('/providertransaction', function () {
        return view('admin.providertransaction');
    })->name('admin.providertransaction');

    Route::get('/providerrequest', function () {
        return view('admin.providerrequest');
    })->name('admin.providerrequest');

    Route::get('/request-dispute', function () {
        return view('admin.request_dispute');
    })->name('admin.request.dispute');

    Route::get('/calendar',  [BookingController::class, 'calenderview'])->name('admin.calendar');

    Route::get('/reviews', function () {
        return view('admin.review_list');
    })->name('admin.reviews');

    Route::get('/staffs',  [StaffController::class, 'adminStaff'])->name('admin.staffs');

    Route::get('/setting/file-storage', function () {
        return view('admin.file-storage');
    })->name('admin.file-storage');

    Route::get('/addon-modules', function () {
        return view('admin.addon_modules_list');
    })->name('admin.addons');

    //social links
    Route::get('social-links', [SocialLinkController::class, 'socialLinks'])->name('admin.social-links');
    Route::post('store-social-link', [SocialLinkController::class, 'storeSocialLinks'])->name('admin.store-social-link');
    Route::post('get_social_links', [SocialLinkController::class, 'getSocialLinks'])->name('admin.get_social_links');
    Route::get('get_social_link/{id}', [SocialLinkController::class, 'getSocialLink'])->name('admin.get_social_link');
    Route::post('delete-social-link', [SocialLinkController::class, 'deleteSocialLink'])->name('admin.delete-social-link');

    // SocialMedia Share
    Route::get('social-media-shares', [SocialMediaShareController::class, 'index'])->name('admin.social-media-shares');
    Route::post('store-social-media-share', [SocialMediaShareController::class, 'store'])->name('admin.store-social-media-share');
    Route::post('get-social-media-shares', [SocialMediaShareController::class, 'getList'])->name('admin.get-social-media-shares');
    Route::get('get-social-media-share/{id}', [SocialMediaShareController::class, 'show'])->name('admin.get-social-media-share');
    Route::post('delete-social-media-share', [SocialMediaShareController::class, 'destroy'])->name('admin.delete-social-media-share');

});
Route::post('admin/set-category-id', function (Request $request) {
    $request->session()->put('category_id', $request->input('category_id'));
    return response()->json(['success' => true]);
})->name('set.category.id');

Route::get('/', function () {
    return view('welcome');
})->name('login');

Route::get('/products', [ServiceController::class, 'onlyproductlist'])->name('allproducts');
Route::get('/services/{slug}/{is_mobile?}', [ServiceController::class, 'productlistcategory'])->name('productlistcategory');
Route::get('/services', [ServiceController::class, 'productlist'])->name('productlists');
Route::get('/categories', [ServiceController::class, 'catlist'])->name('catlist');

Route::get('/languagedefault/{id}', [LanguageController::class, 'languagedefault'])->name('languagedefault');
Route::get('/adminLanguagedefault/{id}', [LanguageController::class, 'adminLanguagedefault'])->name('adminLanguagedefault');
Route::get('/lang/{file_name}', function () {
    return response()->file(resource_path('lang/' . request()->file_name));
});
Route::post('/book', [BookController::class, 'book'])->name('book');
Route::post('/booking-payment', [BookController::class, 'bookingpayment'])->name('bookingpayment');
Route::post('/booking-success', [BookController::class, 'bookingsuccess'])->name('bookingsuccess');

Route::get('/servicedetail/{slug}', [ServiceController::class, 'productdetail'])->name('productdetail');

Route::get('/cart', [ServiceController::class, 'viewcart'])->name('viewcart');

Route::post('/chatbot/save-message', [ChatbotController::class, 'saveMessage']);

Route::post('/userlogins', [UserController::class, 'login'])->name('userlogins');
Route::post('/userregister', [UserController::class, 'register'])->name('userregister');
Route::post('/user-update-password', [UserController::class, 'forgotPassword'])->name('forgotPassword');
Route::get('reset-password/{token}', [UserController::class, 'showResetForm'])->name('password.reset');
Route::get('logout', [UserController::class, 'logout'])->name('logout');
Route::post('/user/delete-account', [UserController::class, 'deleteAccount']);
Route::middleware(['auth'])->group(function () {
});
Route::post('/provider/register', [UserController::class, 'providerRegister'])->name('provider.register');
Route::get('/user/profile', [UserController::class, 'getProfileDetails'])->name('user.profile')->middleware('auc');
Route::get('/user/search', [UserController::class, 'getProfileDetailssearch'])->name('user.profilesearch')->middleware('auc');
Route::get('/user/security', [UserController::class, 'userSecuritySettings'])->name('user.security')->middleware('track.device');
Route::get('/provider/security', [UserController::class, 'providerSecuritySettings'])->name('provider.security')->middleware('track.device', 'permission');
Route::post('/device/delete', [UserController::class, 'devideDelete'])->name('device.delete');
Route::get('user/bookinglist',[BookingController::class,'index'])->name('user.bookinglist')->middleware('auc');
Route::post('/dispute/details', [BookingController::class, 'getDisputeDetails']);
Route::post('/user/make-payment-booking', [BookingController::class, 'makePaymentBooking'])->middleware('auc');
Route::get('/stripe/success', [BookingController::class, 'stripeSuccessCommon'])->name('stripe.success.common');
Route::get('/stripe/chore/cancel', [BookingController::class, 'stripeChoreCancel'])->name('stripe.chore.cancel');
Route::get('/paypal/success', [BookingController::class, 'paypalSuccess'])->name('user.paypal.success.booking');
Route::get('/paypal/cancel', [BookingController::class, 'paypalCancel'])->name('paypal.cancel');



Route::get('/admin/profile', [AdminLoginController::class, 'getAdminDetails'])->name('admin.profile')->middleware('admin.auth');

Route::get('/api/get-session-user-id', function () {
    $userId = session('user_id');
    if ($userId) {
        return response()->json(['user_id' => $userId]);
    } else {
        return response()->json(['user_id' => null]);
    }
});
Route::get('/admin/users', [UserController::class, 'index'])->name('admin.userlist')->middleware('admin.auth', 'permission');
Route::get('/admin/providers', [UserController::class, 'index'])->name('admin.providerslist')->middleware('admin.auth', 'permission');
Route::get('/admin/bookinglist', [BookingController::class, 'listindex'])->name('admin.bookinglist')->middleware('admin.auth', 'permission');

Route::get('/provider/leads', function () {
    return view('provider.providerleads');
})->name('provider.leads')->middleware('auc', 'permission');

Route::get('/provider/leadsinfo', function () {
    return view('provider.providerleadsinfo');
})->name('provider.leadsinfo')->middleware('auc');

Route::get('/provider/dashboard', function () {
    return view('provider.providerdashboard');
})->name('provider.dashboard')->middleware('auc', 'permission');
Route::get('/staff/dashboard', [StaffController::class, 'getdashboard'])->name('staff.dashboard')->middleware('auc', 'permission');
Route::get('/provider/transaction', function () {
    return view('provider.providertransaction');
})->name('provider.transaction')->middleware('auc', 'permission');

Route::get('/provider/reviews', function () {
    return view('provider.review_list');
})->name('provider.reviews')->middleware('auc', 'permission');

Route::get('/user/dashboard', function () {
    return view('user-dashboard');
})->name('user.dashboard')->middleware('auc');

Route::get('/user/wallet', function () {
    return view('user.user-wallet');
})->name('user.wallet')->middleware('auc');

Route::get('/user/walletsucess', [WalletController::class, 'walletsucess'])->name('user.walletsucess')->middleware('auc');

Route::get('/user/leads', function () {
    return view('user-leads');
})->name('user.leads')->middleware('auc');

Route::get('/user/leadsinfo', function () {
    return view('user-leadsinfo');
})->name('user.leadsinfo')->middleware('auc');

Route::get('/user/providerlist', function () {
    return view('user-providerlist');
})->name('user.providerlist');

Route::get('/user/providerdetails', function () {
    return view('user-providerdetails');
})->name('user.providerdetails');

Route::get('/user/transaction', function () {
    return view('user-transaction');
})->name('user.transaction')->middleware('auc');

Route::get('/user/providerlist', [UserController::class, 'userProviderList'])->name('user.providerlist');
Route::get('/user/provider', [UserController::class, 'userProvider'])->name('user.provider')->middleware('auc');

Route::get('/admin/user/view/{id}', [UserController::class, 'renderUserViewPage'])->name('user.viewdetails.page');
Route::get('/admin/provider/view/{id}', [UserController::class, 'renderUserViewPage'])->name('provider.viewdetails.page');
Route::get('/user/favourites', [UserController::class, 'getuserfavour'])->name('user.favourites')->middleware('auc');
Route::post('admin/verify-provider', [UserController::class, 'verifyProvider'])->name('admin.verify.provider');
Route::post('/admin/viewuserdata', [UserController::class, 'getuserviewdetails'])->name('admin.viewusers');

Route::get('/', [PageController::class, 'pageBuilderApi'])->name('home');
Route::get('/about-us', [PageController::class, 'pageBuilderApi'])->name('test');
Route::get('/terms-conditions', [PageController::class, 'pageBuilderApi'])->name('terms.conditions');
Route::get('/privacy-policy', [PageController::class, 'pageBuilderApi'])->name('privacy.policy');
Route::get('/contact-us', [PageController::class, 'pageBuilderApi'])->name('contact-us');
Route::get('/page-not-found', [PageController::class, 'pageBuilderApi'])->name('page.not.found');

Route::get('/maintenance', function () {
    return view('user.partials.maintenance');
})->name('maintenance');

Route::post('handle-payment',[PaypalController::class,'handlePayment'])->name('make.payment');
Route::post('preparePayment',[MollieController::class,'preparePayment'])->name('make.preparePayment');
Route::get('sucesspayment',[MollieController::class,'handleWebhookNotification'])->name('make.molliesucess');
Route::post('molliepayment',[MollieController::class,'molliepayment'])->name('molliepayment');
Route::get('/mollie-payment-success',[MollieController::class,'handleMolliepayment'])->name('make.molliepayment');
Route::get('/payment-success-leads',[MollieController::class,'handleMolliepaymentLeads'])->name('make.molliepayment.leads');
Route::post('/walletPayment',[WalletController::class,'leasdwalletPayment'])->name('leasdwalletPayment.leads');
Route::get('/wallet-payment-success',[WalletController::class,'leasdwalletPaymentSuccess'])->name('leasdwalletPayment.leads.Success');
Route::post('handle-cod-payment',[PaypalController::class,'handlecodPayment'])->name('make.codpayment');
Route::post('handle-wallet-payment',[PaypalController::class,'handleWalletPayment'])->name('make.walletpayment');
Route::post('handleBankPayment',[PaypalController::class,'handleBankPayment'])->name('makebank.bankpayment');
Route::post('processpayment',[PaypalController::class,'ProcessPayment'])->name('processpayment');
Route::get('payment-success',[PaypalController::class,'paymentSuccess'])->name('payment.success');
Route::get('payment-failed',[PaypalController::class,'paymentFailed'])->name('payment.failed');
Route::get('/provider/paymentsuccess',[PaypalController::class,'Successpayment'])->name('providerpayment.success');
Route::get('/user/paymentsuccess',[PaypalController::class,'UserSuccesspayment'])->name('userpayment.success');
Route::post('stripecheckout',[StripeController::class,'test'])->name('stripecheckout');
Route::get('success',[StripeController::class,'paymentSuccess'])->name('success');
Route::get('/provider/subscriptionpaymentsuccess',[StripeController::class,'subscriptionpaymentsuccess'])->name('provider.subscriptionsuccess');
Route::get('checkout',[StripeController::class,'checkout'])->name('checkout');
Route::get('live_mobile',[StripeController::class,'live_mobile'])->name('live_mobile');
Route::post('stripepayment',[StripeController::class,'stripepayment'])->name('stripepayment');
Route::get('/user/stripepaymentsuccess',[StripeController::class,'UserstripeSuccesspayment'])->name('userstripepayment.success');
Route::get('/provider/profile', [UserController::class, 'getProfileDetails'])->name('provider.profile')->middleware('auc', 'permission');
Route::get('/provider/bookinglist', [BookingController::class, 'providerindex'])->name('provider.bookinglist')->middleware('auc', 'permission');
Route::get('/staff/bookinglist', [BookingController::class, 'staffindex'])->name('staff.bookinglist')->middleware('auc', 'permission');
Route::get('/provider/calendar', [ProviderController::class, 'providerCalendarIndex'])->name('provider.calendar')->middleware('auc');
Route::get('/staff/calendar', [StaffController::class, 'CalendarIndex'])->name('staff.calendar')->middleware('auc', 'permission');
Route::get('/provider/subscription', [SubscriptionController::class, 'index'])->name('provider.subscription')->middleware('auc', 'permission');
Route::get('/provider/subscriptionhistory', [SubscriptionController::class, 'historyindex'])->name('provider.subscriptionhistory')->middleware('auc', 'permission');
Route::post('/getsubscriptionhistorylist', [SubscriptionController::class, 'getsubscriptionhistorylist'])->middleware('auc');
Route::post('/set-session', function (Illuminate\Http\Request $request) {
    $existingData = session("{$request->type}.{$request->authid}.chat", []);
    // Add or update the specific key-value pair
    $existingData[$request->key] = $request->value;
    // Update the session with the modified data
    session()->put("{$request->type}.{$request->authid}.chat", $existingData);
    session()->put('fromurl','bookinglist');
    // session("$request->type.{$request->authUserId}.chat",[$request->key => $request->value]);
    return response()->json(['success' => true, 'message' => 'Session value set successfully!']);
});
Route::get('/provider/staff-list', function () {
    return view('provider.staff_list');
})->name('provider.staffs')->middleware('auc', 'permission');

Route::get('/provider/payouts', function() {
    return view('provider.payouts.payout_list');
})->name('provider.payouts')->middleware('auc', 'permission');

Route::get('/provider/branch', [BranchController::class, 'branch'])->name('provider.branch')->middleware('auc', 'permission');
Route::get('/provider/add-branch', [BranchController::class, 'addBranch'])->name('provider.addbranch')->middleware('auc', 'permission');
Route::get('/provider/edit-branch/{id}', [BranchController::class, 'editBranch'])->name('provider.editbranch')->middleware('auc', 'permission');
Route::post('/provider/save-branch-details', [BranchController::class, 'saveBranch'])->middleware('auc');
Route::post('/provider/delete-branch', [BranchController::class, 'deleteBranch'])->middleware('auc');
Route::post('/provider/get-branch-list', [BranchController::class, 'index'])->middleware('auc');
Route::post('/provider/branch/check-unique', [BranchController::class, 'checkUnique'])->middleware('auc');
Route::post('/provider/branch/check-limit', [BranchController::class, 'providerBranchLimit'])->middleware('auc');
Route::post('/provider/staff/check-limit', [StaffController::class, 'providerStaffLimit'])->middleware('auc');

Route::get('/get-countries', [BranchController::class, 'getCountries']);
Route::get('/get-states', [BranchController::class, 'getStates']);
Route::get('/get-cities', [BranchController::class, 'getCities']);

Route::get('/admin/refund', function() { return view('admin.finance.userrequest');})->name('admin.refund')->middleware('admin.auth', 'permission');
Route::get('/admin/subscriptionlist',function() { return view('admin.finance.subscriptionlist');})->name('admin.subscriptionlist')->middleware('admin.auth', 'permission');

Route::post('/get-dispute-info', [BookingController::class, 'getDisputeInfo']);
Route::get('/user/ticket',[TicketController::class, 'ticketindex'])->name('user.ticket')->middleware('auc');
Route::get('/provider/ticket',[TicketController::class, 'ticketindex'])->name('provider.ticket')->middleware('auc');
Route::get('/staff/ticket',[TicketController::class, 'ticketindex'])->name('staff.ticket')->middleware('auc');
Route::get('/admin/tickets',[TicketController::class, 'ticketindex'])->name('admin.ticket')->middleware('admin.auth');
Route::get('admin/ticket-details/{ticket_id}',[TicketController::class,'ticketdetails'])->name('admin.ticketdetails')->middleware('admin.auth');
Route::get('/staff/tickets',[TicketController::class, 'ticketindex'])->name('staff.tickets')->middleware('admin.auth', 'permission');
Route::get('staff/ticketdetails/{ticket_id}',[TicketController::class,'ticketdetails'])->name('staff.ticketdetails')->middleware('admin.auth', 'permission');
Route::post('/store-ticket-id', [TicketController::class, 'storeTicketId'])->name('store.ticket.id');
Route::get('user/ticket-details/{ticket_id}',[TicketController::class,'ticketdetails'])->name('user.ticketdetails')->middleware('auc');
Route::get('provider/ticket-details/{ticket_id}',[TicketController::class,'ticketdetails'])->name('provider.ticketdetails')->middleware('auc');
Route::get('staff/ticket-details/{ticket_id}',[TicketController::class,'ticketdetails'])->name('staff.ticket_details')->middleware('auc');
//my booking
Route::get('/user/booking/service-booking/{slug}', [BookController::class, 'serviceBooking'])->name('user.booking.location.service_booking');
Route::get('/user/booking/{slug}', [BookController::class, 'serviceIndexBooking'])->name('user.booking.service_booking');
Route::get('/get-branch-staff', [BookController::class, 'getStaffs']);
Route::get('/get-branch-staff-info', [BookController::class, 'getInfo']);
Route::get('/get-personal-info', [BookController::class, 'getPersonalInfo']);
Route::post('/get-slot', [BookController::class, 'getSlot']);
Route::post('/get-slots', [BookController::class, 'getSlots']);
Route::post('/get-slot-info', [BookController::class, 'getSlotInfo']);
Route::post('/get-payout', [BookController::class, 'getPayout']);
Route::post('/user/payment', [BookController::class, 'payment']);
Route::get('/paypal-payment-success', [BookController::class, 'paypalPaymentSuccess'])->name('paypal.payment.success');
Route::get('/strip-payment-success', [BookController::class, 'stripPaymentSuccess'])->name('strip.payment.success');
Route::get('/payment-success-one', [BookController::class, 'successOne'])->name('payment.one');
Route::get('/payment-success', [BookController::class, 'successTwo'])->name('payment.two');
Route::get('/molliesucess/sucesspayment',[BookController::class,'sucesspaymentMollie'])->name('make.sucesspayment.molliesucess');
Route::get('/check-product-user', [BookController::class, 'checkProductUser'])->name('new-txt');
Route::post('/get-customer', [StaffController::class, 'getCustomer']);
Route::post('/get-staff-slot', [StaffController::class, 'getStaffSlot']);
Route::post('/staff/payment', [StaffController::class, 'payment']);
Route::post('/get-branch', [ProviderController::class, 'getBranchStaff']);
Route::post('/get-customer-provider', [ProviderController::class, 'getCustomer']);
Route::post('/fetch-staff-service', [ProviderController::class, 'fetchStaffService']);
Route::post('/provider/calender/booking', [ProviderController::class, 'providerCalenderBooking']);
Route::post('/leads/transaction-list', [TransactionController::class, 'leadsTransactionList']);

Route::get('/set-password/{id}', [UserController::class, 'setPassword'])->name('set-password');
Route::post('/update-password', [UserController::class, 'updatePassword']);
Route::get('/get-staff', [ProviderController::class, 'getStaffDetails']);
Route::post('/get/booking/details',[BookingController::class, 'getBookingDetails'])->name('user.getBookingDetails');

Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('optimize:clear');
    return redirect()->route('admin.addons');

});

Route::get('/reload/{module}', function (Request $request) {
    $module = $request->module;
    Artisan::call('module:enable '. $module);
    return redirect()->route('admin.addons');
});

// Social Links
Route::get('/provider/social-links', [ProviderSocialLinkController::class, 'providerSocialLinkIndex'])
    ->name('provider.sociallinks.index');
Route::get('/provider/get/social-links', [ProviderSocialLinkController::class, 'getSocialLinks'])
    ->name('provider.sociallinks.getSocialLinks');
Route::post('/provider/social-links', [ProviderSocialLinkController::class, 'store'])
    ->name('provider.sociallinks.store');
Route::put('/provider/social-links/{providerSocialLink}', [ProviderSocialLinkController::class, 'update'])
    ->name('provider.sociallinks.update');
Route::delete('/provider/social-links/{providerSocialLink}', [ProviderSocialLinkController::class, 'destroy'])
    ->name('provider.sociallinks.destroy');
Route::patch('/provider/social-links/{providerSocialLink}/toggle-status', [ProviderSocialLinkController::class, 'toggleStatus'])
    ->name('provider.sociallinks.toggle-status');
Route::post('/provider/social-links/bulk-update', [ProviderSocialLinkController::class, 'bulkUpdate'])
    ->name('provider.sociallinks.bulkUpdate');

Route::get('/update-module/{module}', [AddonController::class, 'updateModule']);

Route::get('/backup', [DbbackupController::class, 'backupDatabase'])->name('backup');
Route::get('download-backup/{id}', [DbbackupController::class, 'downloadDatabaseBackup'])->name('download.backup');
Route::post('/payment/update-banktransfer', [SubscriptionController::class, 'updateBankTransfer']);
Route::post('/admin/verify-banktransfer-payment', [SubscriptionController::class, 'verifyBankTransferPayment'])->name('admin.verifyBankTransferPayment');

Route::get('/{slug}', [PageController::class, 'pageBuilderApi'])->name('dynamic.page');

// for stripe subscription
Route::post('/provider/stripe/checkout', [StripeController::class, 'createCheckoutSession'])->name('provider.stripe.checkout');
Route::get('/provider/stripe/subs-payment-success', [StripeController::class, 'subscriptionSuccess'])->name('provider.stripe.subscription.success');
Route::get('/provider/stripe/subs-payment-cancel', [StripeController::class, 'subscriptionCancel'])->name('provider.stripe.subscription.cancel');

// for paypal subscription
Route::get('/paypal/plans', [PayPalSubscriptionService::class, 'fetchPlans'])->name('paypal.plans');
Route::get('paypal/plan/{planId}', [PayPalSubscriptionService::class, 'fetchPlan'])->name('paypal.plan');
Route::get('/paypal/products', [PayPalSubscriptionService::class, 'fetchProducts'])->name('paypal.products');
Route::get('/paypal/subscriptions', [PayPalSubscriptionService::class, 'fetchSubscriptions'])->name('paypal.subscriptions');
Route::post('/paypal/subscription/checkout', [PaypalController::class, 'subscriptionCheckout'])->name('paypal.subscription.checkout');
Route::get('/paypal/subscription/success', [PaypalController::class, 'subscriptionSuccess'])->name('paypal.subscription.success');
Route::get('/paypal/subscription/cancel', [PaypalController::class, 'cancelSubscription'])->name('paypal.subscription.cancel');
Route::get('/paypal/subscription/{subscriptionId}', [PayPalSubscriptionService::class, 'fetchSubscription'])->name('paypal.subscription');

//cancel subscription
Route::post('/cancel-subscription', [SubscriptionController::class, 'cancelSubscription'])->name('cancel.subscription');

Route::post('booking/verify-bank-transfer', [TransactionController::class, 'verifyBankTransfer'])->name('verifyBankTransfer');

});