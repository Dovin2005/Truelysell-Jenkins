<?php

namespace App\Repositories\Eloquent;

use Illuminate\Http\Request; 
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\UserDetail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Modules\Communication\app\Http\Controllers\EmailController;
use Modules\GlobalSetting\app\Models\Templates;
use Modules\GlobalSetting\Entities\GlobalSetting;
use App\Repositories\Contracts\AdminLoginRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AdminLoginRepository implements AdminLoginRepositoryInterface
{
    public function login(Request $request): JsonResponse
    {
        try {
            $validated = $request->validated();
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'message' => $e->errors(), // Validation errors
            ], 422);
        }

        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

        $user = User::where('email', $request->email)->first();
        if ($user && ($user->status == 0)) {
            return response()->json([
                'status'  => false,
                'code'    => 403,
                'message' => __('account_blocked_info'),
            ], 403);
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user['user_type'] == 1 || $user['user_type'] == 5) {

                session(['auth_user_id' => $user->id]); 
                Cache::forever('auth_user_id',  $user->id);
                $token = $user->createToken('admin-token')->plainTextToken;
                $user_id = Auth::id();

                return response()->json([
                    'code' => 200,
                    'message' => 'Login successful',
                    'token' => $token,
                    'user_id' => $user_id
                ], 200);
            }
            else{
                return response()->json([
                    'code' => 401,
                    'message' => 'Invalid login credentials',
                ], 401); 
            }
        }

        return response()->json([
            'code' => 401,
            'message' => 'Invalid login credentials',
        ], 401);
    }

    public function userlogin(Request $request): JsonResponse
    {
        try {
            // Validate the incoming request data
            $validated = $request->validated();
        } catch (ValidationException $e) {
            // Return a 422 response with validation errors
            return response()->json([
                'code' => 422,
                'message' => $e->errors(), // Validation errors
            ], 422);
        }

        // Credentials array to use for the admin login
        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

        // Attempt to authenticate using the 'admin' guard
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            session(['auth_user_id' => $user->id]); 
            Cache::forever('auth_user_id',  $user->id);
            $token = $user->createToken('admin-token')->plainTextToken;
            $user_id = Auth::id();
            
            // Return success response with the token
            return response()->json([
                'code' => 200,
                'message' => 'Login successful',
                'token' => $token,
                'user_id' => $user_id
            ], 200);
        
        }

        // If authentication fails, return an error response
        return response()->json([
            'code' => 401,
            'message' => 'Invalid login credentials',
        ], 401);
    }

     public function saveAdminDetails(Request $request): JsonResponse
    {
        $data = $request->except(['id', 'email', 'user_name', 'profile_image', 'phone_number', 'status', 'role_id']);
        $id = $request->id ?? '';
        $method = $id == '' ? 'add' : 'update';

        $addUserData = [];

        $user = User::find($id);
        $userType = $user->user_type ?? '';
        
        $rules = [
            'email' => 'required|email|unique:users,email,' . $id . ',id',
            'user_name' => 'required|max:255|unique:users,name,' . $id . ',id',
            'profile_image' => 'mimes:jpeg,jpg,png|max:2048',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'international_phone_number' => 'required',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
        ];
        $messages = [
            'email.rquired' => __('email_required'),
            'email.unique' => __('email_exists'),
            'email.email' => __('email_format'),
            'user_name.required' => __('user_name_required'),
            'user_name.max' => __('user_name_maxlength'),
            'user_name.unique' => __('user_name_exists'),
            'profile_image.mimes' => __('image_extension'),
            'profile_image.max' => __('image_filesize'),
            'first_name.required' => __('first_name_required'),
            'first_name.max' => __('first_name_maxlength'),
            'last_name.required' => __('last_name_required'),
            'last_name.max' => __('last_name_maxlength'),
            'international_phone_number.required' => __('phone_number_required'),
        ];

        $success_msg = __("profile_update_success");
        $error_msg = __('profile_update_error');
        $validator = null;

        if ($userType == 1) {
            $validator = Validator::make($request->all(), $rules, $messages);

        } else if ($userType == 5 || $request->has('parent_id')) {

            if ($request->has('parent_id')) {
                $extraRules = [
                    'gender' => 'required',
                ];
                $extraMessages = [
                    'gender.required' => __('gender_required'),
                ];
                $addUserData = [
                    'user_type' => 5,
                    'status' => $request->status,
                    'role_id' => $request->role_id
                ];
    
                $success_msg = __("Staff saved successfully.");
                $error_msg = __('Error! while saving staff');
                $rules = array_merge($rules, $extraRules);
                $messages = array_merge($messages, $extraMessages);
    
                $validator = Validator::make($request->all(), $rules, $messages);
            } else {
                $validator = Validator::make($request->all(), $rules, $messages);
            }
        }
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'code' => 422,
                'errors' => $validator->messages()->toArray(),
            ], 422);
        }
        
        try {

            $latitude="";
            $longitude="";

            $address = $request->address;
            $apikey= GlobalSetting::where('key', 'goglemapkey')->value('value');
            $url = "https://maps.google.com/maps/api/geocode/json?address=".urlencode($address)."&key=".$apikey;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    
            $responseJson = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($responseJson);
            $status = $response->status ?? '';

            if ($status == 'OK') {
                $latitude = $response->results[0]->geometry->location->lat;
                $longitude = $response->results[0]->geometry->location->lng;
            }
            $data['lat'] = $latitude;
            $data['lang'] = $longitude;

            $userData = [
                'name' => $request->user_name,
                'email' => $request->email,
                'phone_number' => $request->international_phone_number
            ];
            $userData = array_merge($userData, $addUserData);

            $result = User::updateOrCreate(['id' => $id], $userData);
            $id = $result->id;

            $user_detail = UserDetail::where('user_id', $id)->first();
            $oldImage = '';
            if ($user_detail) {
                $oldImage = $user_detail->profile_image ?? '';
            }

            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                if ($file instanceof UploadedFile && $file->isValid()) {
                    if (Storage::disk('public')->exists('profile/' . $oldImage)) {
                        Storage::disk('public')->delete('profile/' . $oldImage);
                    }
                    $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('profile', $filename, 'public');
                    $data['profile_image'] = $filename;
                }
            }

            UserDetail::updateOrCreate(['user_id' => $id], $data);

            if (request()->has('parent_id') && $method == 'add') {

                $notificationType = 27;
                $template = Templates::select('subject', 'content')
                            ->where('type', 1)
                            ->where('notification_type', $notificationType)
                            ->first();

                $link = route('set-password', ['id' => Crypt::encrypt($id)]);

                if (!empty($template)) {
                    $replaceData = [$request->first_name, $request->last_name, $link];
                    $template->content = str_replace(['{{first_name}}', '{{last_name}}', '{{link}}'], $replaceData, $template->content);
                }
                $data = [
                    'to_email' => $request->email,
                    'subject' => $template->subject ?? 'Reg - Set Up Your Account Password',
                    'content' => $template->content ?? $link
                ];

                try {
                    $request = new Request($data);
                    $emailController = new EmailController();
                    $emailController->sendEmail($request);
                } catch (\Exception $e) {
                    Log::error('Error while sending email: ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'code' => 200,
                'message' => $success_msg,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => $error_msg,
                'error' => $e->getMessage()
            ], 500);
        }
        
    }

    public function getAdminDetails(Request $request): View|JsonResponse
    {
        $id = Auth::id() ?? $request->id;
        $userDetails = User::with('userDetails')->where('id', $id)->first();

        if (!empty($userDetails->userDetails->profile_image)) {
            $profileImage = $userDetails->userDetails->profile_image;
            $userDetails->userDetails->profile_image = file_exists(public_path('storage/profile/' . $profileImage)) ? url('storage/profile/' . $profileImage) : asset('assets/img/user-default.jpg');
        } else {
            $userDetails->userDetails->profile_image = asset('assets/img/user-default.jpg');
        }

        if (isset($request->isMobile)) {
            return response()->json([
                'code' => 200,
                'message' => __('Admin detail retrieved successfully.'),
                'data' => $userDetails,
            ], 200);
        }

        $data = $userDetails;
        return view('admin.admin-profile', compact('data'));
    }

     public function changePassword(Request $request): JsonResponse
    {
        $data = $request->except(['id']);
        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $id = $request->auth_id;
        } else {
            $id = $request->id;
        }
        $user = DB::table('users')->where('id', $id)->first();
        $restrictedEmails = ['demoprovider@gmail.com', 'demouser@gmail.com'];
        if (in_array($user->email, $restrictedEmails)) {
            return response()->json([
                'code' => 500,
                'message' => __('Password cannot be changed for this account.'),
                'data' => []
            ], 500);
        }
        $validator = Validator::make($request->all(), [
            'current_password' => [
                'required',
                function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail(__('The current password is incorrect.'));
                    }
                },
            ],
            'new_password' => 'required|string|min:8|different:current_password',
            'confirm_password' => 'required|string|same:new_password',
        ]);

        if ($validator->fails()) {
            $messages = $validator->messages()->toArray();
            $errorMessage = array_values($messages)[0][0] ?? 'Validation error';
        
            return response()->json([
                'code' => 422,
                'message' => $errorMessage,
                'data' => []
            ], 422);
        }        

        try {
            $password = Hash::make($data['new_password']);
            User::where('id', $id)->update(['password' => $password]);
            
            return response()->json([
                'code' => 200,
                'message' => __('Password saved successfully.'),
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('Error! while updating password'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}