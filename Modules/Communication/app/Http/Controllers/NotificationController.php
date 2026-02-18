<?php

namespace Modules\Communication\app\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Communication\app\Models\CommunicationSettings;
use Modules\Communication\app\Models\Templates;
use Modules\Communication\app\Models\Notifications;
use App\Models\Administrator;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Communication\Entities\Communication;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Support\Facades\Auth;
class NotificationController extends Controller
{

    public function saveToken(Request $request) : JsonResponse
    {
        if($request->type=="/provider/dashboard"){
            if(Auth::id()){
                $userid= Auth::id();
            }else{
                $userid= Cache::get('provider_auth_id');
            }
        }elseif($request->type=="/admin/dashboard"){
            $userid= Auth::id();
        }else{
            if(Auth::id()){
                $userid= Auth::id();
            }else{
                $userid= Cache::get('user_auth_id');
            }
        }
        User::where('id',$userid)->update(['fcm_token'=>$request->token]);
        return response()->json(['code'=>'200','message' => 'FCM Token Saved successfully']);
    }

    public function Storenotification(Request $request) : JsonResponse
    {
        $data['communication_type']=$request->communication_type;
        $title=$data['source']=$request->source;
        $data['reference_id']=$request->reference_id;
        $data['user_id']=$request->user_id;
        $data['to_user_id']=$request->to_user_id;
        $data['notification_date']=date('Y-m-d');
        $frombody=$data['from_description']=$request->from_description;
        $tobody=$data['to_description']=$request->to_description;
        if(isset($request->description)){
            $data['to_description']=$request->description;
        }
        $data['created_at']=date('Y-m-d H:i:s');
        $data['created_by']=$request->user_id;
        Notifications::insert($data);
        $gettoken= User::select('fcm_token')->where('id', $request->user_id)->first();
        if(isset($gettoken->fcm_token)){
           $this->sendPushNotification($gettoken->fcm_token, $title, $frombody);
        }
        $gettotoken= User::select('fcm_token')->where('id', $request->to_user_id)->first();
        if(isset($gettotoken->fcm_token)){
            $this->sendPushNotification($gettotoken->fcm_token, $title, $tobody);
        }
        return response()->json(['code'=>'200','message' => 'Notification sent successfully']);
    }
    public function sendPushNotification(string $token1, string $title, string $body) : mixed
    {
        $clientEmail =  $projectId = $privateKey = '';

        try {

                $$clientEmail = "firebase-adminsdk-70jk4@truelysell-570e9.iam.gserviceaccount.com";
                $privateKey = "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCn+4QEXHct3XNy\nr8LZCia5ImOiflGcgdm1QdET4RSqPGd16o4qVEQjlVIboQsBynYy0Til1/BuMPCm\nE7k08lozOtPzeGONCeL7FlL9XQPbXf4g/Uzne9ifBuka0WCpo6gyI1SNySlhg9tv\nUNaFUcrZeN0xnqZdEHMgE/sxU095cXIcw0dYEArB8J3Vnp6uvB1XxGDkIHsXyLab\nozpl5/zrQcEUXIEQFGrJ/xYk4UkWmydCtFZKf7h8HvfLI1Itweat7KDN6eBfiuMy\nxEWkW2x2Mu5YwHQKFrnc8+cawkC2C3bEZ5mMNL5bt45t37KE2QURKgQqt/EzK5NX\n4mTKYcgPAgMBAAECggEAARkrZU3nd5TNlu/gQcQHuckoRp010mSk9ELwZ4Veubg4\nYvR8Jnmzkln9FfIpHMNin3VvveHaEBZg1G0nllmJTnFxhlOAryDj64lbVI3BozML\noNKXKKJOKcgFSSgaTjGkPWOuv1iT3S/cBO/N30RII8DfDV6ArvYXEpwe1dU6HUym\nXzDsNu0w7uIneTlOIJq64T4kAEWfq8c77Wg8Tg1H4pUNC4L2yCuzoGCJlbIeXM8P\n3ftK201b5NNh8OwcolaX+9/g2/wQypWROzP3LTaGF/UokXyGZL0X2lBpsrM8RmsB\n1aRPpneOh/A+PtgMEpqMY50FEhCDgofHv4nebwJETQKBgQDdN7p+P9Y6xK82XWr/\niK8Q1lh18r6ExQQ1HHntn/dUqFpx/FUSXBiVJpodoaxF+0nPGq2jfLFjA+8ionJR\n8pHnQxI9pHgBe35v/rzIrxcAHfjJQEiIQ5PT+3/SGLOLiTGkNWnBin92Z4uOhgIn\nm7uJgqk0R4pR0NXu1cBZm33YWwKBgQDCZQH4rjziKpuztxB2R0sw9xqFbnhmZooi\nHy0Od+yDTWWXDBM0q+2eB6pLtw/vu8YJkWC1b5Tc+faeT8qoI8BiT8CO2znYhzt1\nbnXJUqVSqoW0ZJXdR5CVlNLElfY6T6HPx/Wamob7L8BaEOnW/kBvz2EJybyp0Ba+\n51PyKf29XQKBgC5jlFELM/Jo8lEVL+3cDNIcELrTJ46R/frhONRSujvffe/vOSu7\nclA8ow7Zq1WVh3nNn2HsMQqRdaEurYhvtJbpYrbYeW3eJrp3kcEHrmwQ3O3e9BbX\nDPXkBH7bPTNilq1XohC66VW8CvDdOzBxINeKNc9HvULA50vJOSfRSoeHAoGBAJYD\nuuy9EXD7rqKWoFaWmBkWaFoWMUXc1baVVoD23QdK4B4bjGq2Ty4H/kxl3h+EScSy\ncu4xSLDrGX18WAk/ZcS4hQH5ff9yBNmPv8f4Rmqv+3SFtv1Hr/UvHyPkEltXfqjJ\nR+jiXU5UwF0A9mQaW4GtiNYz36swUY1LGYYlD5JlAoGAEnLrMytlTbth0jSq1eLD\n1dUoNWHAHjMiDGuXhDULpAgHuyP+O9rC1UVYTjbNaYL7oCVRd19T5S/kD5dXxmwJ\nn4cdEVtZMoF51Z6gJOUWpIQ1sRcnfrpw8kvnlIcejOxOQERbJa+ugN9YiJpfPMD6\nD9+se0P/LjJtKde3VPKAw6I=\n-----END PRIVATE KEY-----\n";

                $fcmUrl ="https://fcm.googleapis.com/v1/projects/truelysell-570e9/messages:send";
                $token = [
                    'iss' => $clientEmail,
                    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                    'aud' => 'https://oauth2.googleapis.com/token',
                    'exp' => time() + 3600,
                    'iat' => time(),
                ];

                $jwt = JWT::encode($token, $privateKey, 'RS256');
                $client = new Client();
                $payload = [
                    'message' => [
                        'token' => $token1,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => [
                            'custom_key' => 'custom_value',
                        ],
                    ],
                ];

                $authResponse = $client->post('https://oauth2.googleapis.com/token', [
                    'form_params' => [
                        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                        'assertion' => $jwt,
                    ],
                ]);

                $accessToken = json_decode($authResponse->getBody()->getContents(), true)['access_token'];

                $response = $client->post($fcmUrl, [
                    'json' => $payload,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                    ],
                ]);
                return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    public function notificationlist(Request $request){
        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? '%d-%m-%Y';
        $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);
        $timeformatSetting = GlobalSetting::where('key', 'time_format_view')->first();
        $timeFormat = $timeformatSetting->value ?? ' %H:%i:%s';
        $sqltimeFormat = $this->mapDateFormatToSQL($timeFormat);
        $routeName = Route::currentRouteName();
        $type='admin';
        if($routeName== 'user.notification'){
            $type='user';
        }elseif($routeName== 'provider.notification'){
            $type='provider';
        }elseif($routeName== 'admin.notification'){
            $type='admin';
        }elseif(isset($request['type'])){
            $type=$request['type'];
        }

        if(isset($request['authid'])){
            $authUserId=$request['authid'];
        }else{
            $authUserId= Auth::id();
        }

        $userType = User::where('id', $authUserId)->value('user_type');

        $notifications = Notifications::join('users as fromuser', 'fromuser.id', '=', 'notifications.user_id')
        ->join('users as touser', 'touser.id', '=', 'notifications.to_user_id')
        ->where(function ($query) use ($authUserId) {
            $query->where('notifications.to_user_id', $authUserId)
                  ->orWhere('notifications.user_id', $authUserId);
        })->when(function ($query) {
                return $query->where(function ($q) {
                    $q->where('notifications.source', 'LIKE', '%Booking%')
                      ->orWhere('notifications.source', 'LIKE', '%Order%')
                      ->orWhere('notifications.source', 'LIKE', '%Refund%')
                      ->orWhere('notifications.source', 'LIKE', '%Lead%')
                      ->orWhere('notifications.source', 'LIKE', '%Ticket%')
                      ->orWhere('notifications.source', 'LIKE', '%Service%');
                })->exists();            
            },
            function ($query) use($sqlDateFormat,$sqltimeFormat) {
                $query->where(function ($q) {
                    $q->where('notifications.source', 'LIKE', '%Booking%')
                      ->orWhere('notifications.source', 'LIKE', '%Order%')
                      ->orWhere('notifications.source', 'LIKE', '%Refund%')
                      ->orWhere('notifications.source', 'LIKE', '%Lead%')
                      ->orWhere('notifications.source', 'LIKE', '%Ticket%')
                      ->orWhere('notifications.source', 'LIKE', '%Service%');
                })
                      ->leftJoin('bookings', 'notifications.reference_id', '=', 'bookings.id')
                      ->leftJoin('products', 'bookings.product_id', '=', 'products.id')
                      ->addSelect(
                          'bookings.id as booking_id','bookings.booking_status',
                          DB::raw("
                          CASE
                              WHEN DATE_FORMAT(bookings.created_at, '%H:%i:%s') = '00:00:00'
                              THEN DATE_FORMAT(bookings.created_at, '{$sqlDateFormat}')
                              ELSE DATE_FORMAT(bookings.created_at, '{$sqlDateFormat} {$sqltimeFormat}')
                          END AS booking_date"),
                          'products.id as product_id',
                          'products.source_name as product_name'
                      );
            }
        )
        ->addSelect(
            'notifications.id as notification_id','notifications.source',
            'notifications.from_description','notifications.to_description',
            DB::raw("
            CASE
                WHEN DATE_FORMAT(notifications.created_at, '%H:%i:%s') = '00:00:00'
                THEN DATE_FORMAT(notifications.created_at, '{$sqlDateFormat}')
                ELSE DATE_FORMAT(notifications.created_at, '{$sqlDateFormat} {$sqltimeFormat}')
            END AS notificationdate"),
            'fromuser.name as from_name','fromuser.id as from_user_id',
            'touser.name as to_name','touser.id as to_user_id',
            'fromuser.user_type as from_user_type',
            'touser.user_type as to_user_type',DB::raw('(SELECT profile_image
            FROM user_details WHERE user_details.user_id = fromuser.id and user_details.deleted_at is NULL  LIMIT 1) as from_profileimg')
            ,DB::raw('(SELECT profile_image
            FROM user_details WHERE user_details.user_id = touser.id and user_details.deleted_at is NULL  LIMIT 1) as to_profileimg')
        )
        ->orderByDesc('notifications.id');
        if($routeName== 'user.notification'){
            $notifications=$notifications
                ->whereNotNull('notifications.from_description')
                ->where('notifications.from_description', '!=', '')
                ->paginate(5);
        }elseif($routeName== 'provider.notification'){
            $notifications=$notifications->paginate(8);
        }elseif($routeName== 'admin.notification'){
            $notifications=$notifications->paginate(8);
        }else {
            $notifications=$notifications
            ->when($userType == 3, function ($query) {
                $query->whereNotNull('notifications.from_description')
                ->where('notifications.from_description', '!=', '');
            })
            ->where(function ($query) use ($authUserId) {
                $query->where(function ($q) use ($authUserId) {
                    $q->where('notifications.user_id', $authUserId)
                    ->where('notifications.from_read_type', 0);
                })->orWhere(function ($q) use ($authUserId) {
                    $q->where('notifications.to_user_id', $authUserId)
                    ->where('notifications.to_read_type', 0);
                });
            })
            ->take(10)
            ->get()->map(function ($item) {
                $fromProfileImage = $item->from_profileimg && file_exists(public_path('/storage/profile/' . $item->from_profileimg ?? ''));
                $toProfileImage = $item->to_profileimg && file_exists(public_path('/storage/profile/' . $item->to_profileimg ?? ''));
                
                $item->from_profileimg = $fromProfileImage ? url('storage/profile/' . $item->from_profileimg) : url('assets/img/profile-default.png');
                $item->to_profileimg = $toProfileImage ? url('storage/profile/' . $item->to_profileimg) : url('assets/img/profile-default.png');

                return $item;
            });
        }
        $unreadNotificationCount = Notifications::join('users as fromuser', 'fromuser.id', '=', 'notifications.user_id')
        ->join('users as touser', 'touser.id', '=', 'notifications.to_user_id')
        ->where(function ($query) use ($authUserId) {
            $query->where(function ($subQuery) use ($authUserId) {
                 $subQuery->where('notifications.user_id', $authUserId)
                        ->where('notifications.from_read_type', 0);
            })
            ->orWhere(function ($subQuery) use ($authUserId) {
                $subQuery->where('notifications.to_user_id', $authUserId)
                        ->where('notifications.to_read_type', 0);
            });
        })->count();
        $data['count']=$unreadNotificationCount;
        $data['notifications']=$notifications;
        $data['auth_user']=$authUserId;
        if($routeName== 'user.notification'){
            return view('communication::notification.index', compact('data'));
        }elseif($routeName== 'provider.notification'){
            return view('communication::notification.providerindex', compact('data'));
        }elseif($routeName== 'admin.notification'){
            return view('communication::notification.adminindex', compact('data'));
        }
        return response()->json(['code'=>'200','message' => 'Notification Retrieved successfully','data'=>$data]);
    }
    public function updatereadstatus(Request $request){
        if(isset($request['authid'])){
            $authUserId=$request['authid'];
        }else{
            $authUserId= Auth::id();
        }

        $update = Notifications::where(function ($query) use ($authUserId) {
            $query->where('notifications.user_id', $authUserId)
                  ->where('notifications.from_read_type', 0); // Update condition for from_user_id
        })
        ->orWhere(function ($query) use ($authUserId) {
            $query->where('notifications.to_user_id', $authUserId)
                  ->where('notifications.to_read_type', 0); // Update condition for to_user_id
        })
        ->update([
            'from_read_type' => DB::raw("IF(notifications.user_id = {$authUserId}, 1, from_read_type)"),
            'to_read_type' => DB::raw("IF(notifications.to_user_id = {$authUserId}, 1, to_read_type)"),
        ]);
        return response()->json(['code'=>'200','message' => 'Notification Read status updated successfully','data'=>[]]);
    }
    public function getnotificationcount(Request $request){

        if(isset($request['authid'])){
            $authUserId=$request['authid'];
        }else{
            $authUserId= Auth::id();
        }
        $data['unreadNotificationCount'] = Notifications::join('users as fromuser', 'fromuser.id', '=', 'notifications.user_id')
        ->join('users as touser', 'touser.id', '=', 'notifications.to_user_id')
        ->where(function ($query) use ($authUserId) {
            $query->where(function ($subQuery) use ($authUserId) {
                 $subQuery->where('notifications.user_id', $authUserId)
                        ->where('notifications.from_read_type', 0);
            })
            ->orWhere(function ($subQuery) use ($authUserId) {
                $subQuery->where('notifications.to_user_id', $authUserId)
                        ->where('notifications.to_read_type', 0);
            });
        })->count();
        return response()->json(['code'=>'200','message' => 'Notification count Retrieved successfully','data'=>$data]);
    }
    function mapDateFormatToSQL($phpFormat) {
        $replacements = [
            'd' => '%d', 'D' => '%a', 'j' => '%e', 'l' => '%W',
            'F' => '%M', 'm' => '%m', 'M' => '%b', 'n' => '%c',
            'Y' => '%Y', 'y' => '%y',
            'a' => '%p', 'A' => '%p', 'g' => '%l', 'G' => '%k',
            'h' => '%I', 'H' => '%H', 'i' => '%i', 's' => '%S',
        ];

        return strtr($phpFormat, $replacements);
    }
}
