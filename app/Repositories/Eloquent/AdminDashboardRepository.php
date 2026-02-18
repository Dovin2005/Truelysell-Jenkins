<?php
namespace App\Repositories\Eloquent;

use Modules\GlobalSetting\app\Models\Placeholders;
use Modules\Categories\app\Models\Categories;
use App\Models\User;
use Modules\Service\app\Models\Service;
use App\Models\Bookings;
use App\Models\InvoiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\PackageTrx;
use Illuminate\Support\Facades\DB;
use Modules\Leads\app\Models\UserFormInput;
use Modules\GlobalSetting\app\Models\Currency;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Contracts\AdminDashboardRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class AdminDashboardRepository implements AdminDashboardRepositoryInterface
{
    public function index(Request $request): array
    {
        $authuserid = Auth::id();
        $data['currency']=Currency::select('symbol','position')->where('is_default',1)->get();
        $data['providercount']=User::where('user_type',2)->count();
        $data['provideractivecnt']=User::where('user_type',2)->where('status',1)->count();
        $data['providerinactivecnt']=User::where('user_type',2)->where('status',0)->count();
        $data['servicecount']=Service::where('source_type','service')->count();
        $data['serviceactivecnt']=Service::where('source_type','service')->where('status',1)->count();
        $data['serviceinactivecnt']=Service::where('source_type','service')->where('status',0)->count();
        $data['bookingcount']=Bookings::count();
        $data['completedbooking']=Bookings::where('booking_status', 6)->count();
        $data['pendingbooking']=Bookings::whereIn('booking_status', ['1', '2'])->count();
        $data['bookingamount']=Bookings::whereIn('booking_status', ['1', '2','5', '6'])->sum('total_amount');
        $data['completedamount']=Bookings::where('booking_status', 6)->sum('total_amount');
        $data['pendingamount']=Bookings::whereIn('booking_status', ['1', '2'])->sum('total_amount');
        $data['user']=User::select('name')->where('id',$authuserid)->first();
        $data['recentbookings']= DB::table('bookings')
        ->select(
            'bookings.id as booking_id',
            'bookings.booking_date',
            DB::raw("
            CASE
                WHEN bookings.booking_status = 1 THEN 'Open'
                WHEN bookings.booking_status = 2 THEN 'Accepted'
                WHEN bookings.booking_status = 3 THEN 'Cancelled'
                WHEN bookings.booking_status = 4 THEN 'Refund Initiated'
                WHEN bookings.booking_status = 5 THEN 'Completed'
                WHEN bookings.booking_status = 6 THEN 'Completed'
                WHEN bookings.booking_status = 7 THEN 'Refund Completed'
                ELSE 'Unknown'
            END AS booking_status_label
            "),
            DB::raw("DATE_FORMAT(bookings.from_time, '%H:%i') AS fromtime"),
            DB::raw("DATE_FORMAT(bookings.to_time, '%H:%i') AS totime"),
            'bookings.booking_status',
            'products.id as product_id',
            'products.source_name as product_name',
            'products.created_by as creator_id',
            'users.name as creator_name','user.name as user','products_meta.source_Values as productimage'
        )
        ->leftjoin('products', 'bookings.product_id', '=', 'products.id')->leftJoin('products_meta', function($join) {
            $join->on('products_meta.product_id', '=', 'bookings.product_id')
                 ->where('products_meta.source_key', '=', 'product_image');
        })->leftjoin('users', 'products.created_by', '=', 'users.id') // Assuming the user table stores creators
        ->leftjoin('users as user', 'bookings.user_id', '=', 'user.id') // Assuming the user table stores creators
        ->whereNull('products.deleted_at')->whereNull('bookings.deleted_at') // Ensure only non-deleted products
        ->orderBy('bookings.created_at', 'desc')->limit(5)->get();
        $data['subscriptions']=PackageTrx::join('subscription_packages','subscription_packages.id','=','package_transactions.package_id')->join('users','users.id','=','package_transactions.provider_id')->join('user_details','user_details.user_id','=','users.id')->select('users.name','subscription_packages.package_title','subscription_packages.package_term','subscription_packages.package_duration','subscription_packages.price','user_details.profile_image')->orderBy('subscription_packages.created_at', 'desc')->limit(5)->get();
        $data['leads']= UserFormInput::with(['category', 'user'])->select('id', 'user_id', 'category_id', 'status', 'form_inputs', 'created_at',DB::raw("CASE
        WHEN status = 1 THEN 'new'
        WHEN status = 2 THEN 'accept'
        WHEN status = 3 THEN 'reject'
        ELSE 'unknown'
     END as status_label"))->whereNull('deleted_at')->orderBy('user_form_inputs.created_at', 'desc')->limit(5)->get();
        $data['transactions']=Bookings::select('bookings.id as booking_id','bookings.service_amount','bookings.payment_status','bookings.booking_date',DB::raw("DATE_FORMAT(bookings.from_time, '%H:%i') AS fromtime"),
            DB::raw("DATE_FORMAT(bookings.to_time, '%H:%i') AS totime"),'bookings.booking_status','products.id as product_id',
            'products.source_name as product_name','products.created_by as creator_id','users.name as user',DB::raw("CASE
            WHEN bookings.payment_status = 1 THEN 'Initiated'
            WHEN bookings.payment_status = 2 THEN 'Success'
            WHEN bookings.payment_status = 3 THEN 'Failed'
            ELSE 'unknown'
         END as paymentstatus"),'products_meta.source_Values as productimage')->leftjoin('products', 'bookings.product_id', '=', 'products.id')->leftjoin('users', 'bookings.user_id', '=', 'users.id')->leftJoin('products_meta', function($join) {
            $join->on('products_meta.product_id', '=', 'bookings.product_id')
                 ->where('products_meta.source_key', '=', 'product_image');
        })->orderBy('bookings.created_at', 'desc')->limit(5)->get();

        return [
            'data' => $data
        ];
    }

    public function add(Request $request): array
    {
        $getplaceholder=Placeholders::select('placeholder_name','id')->where('status',1)->where('deleted_at',null)->get();
        $getinvoicetype=InvoiceType::select('type','id')->where('status',1)->where('deleted_at',null)->get();
        return [
            'getplaceholder' => $getplaceholder,
            'getinvoicetype' => $getinvoicetype
        ];
    }

    public function showFormCategories(Request $request): array
    {
        $categoryId = $request->session()->get('category_id');

        $categoryName = Categories::where('id', $categoryId)->value('name');
        return [
            'categoryId' => $categoryId,
            'categoryName' => $categoryName
        ];
    }
}