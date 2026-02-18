<?php

namespace App\Repositories\Eloquent;

use App\Models\Contact;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AuthRepository implements AuthRepositoryInterface
{
    public function detail(): JsonResponse
    {
        $user = Auth::user();

        $userdetail = UserDetail::where('user_id', $user->id)->first();

        $data = [
            'name'=>$user->name,
            'email'=>$user->email,
            'user_type'=>$user->user_type,
            'profile_pic'=>$userdetail->profile_image,      
        ];
        $response['user'] = $data;
        return response()->json($response,200);
    }

    public function Userdetail(): JsonResponse
    {
        $user = Auth::user();


        $userdetail = UserDetail::where('user_id', $user->id)->first();

        $data = [
            'name'=>$user->name,
            'email'=>$user->email,
            'user_type'=>$user->user_type,
            'profile_pic'=>$userdetail->profile_image,  
            'mobile_number'=>$userdetail->mobile_number,
            'gender'=>$userdetail->gender,
            'dob'=>$userdetail->dob,
            'bio'=>$userdetail->bio,
            'address'=>$userdetail->address,
            'country'=>$userdetail->country,
            'state'=>$userdetail->state,
            'city'=>$userdetail->city,    
            'postal_code'=>$userdetail->postal_code,    
            'currency_code'=>$userdetail->currency_code,    
            'language'=>$userdetail->language,    
            'company_image'=>$userdetail->company_image,    
            'company_name'=>$userdetail->company_name,    
            'company_website'=>$userdetail->company_website,    
            'company_address'=>$userdetail->company_address,    

        ];
        $response['user'] = $data;
        return response()->json($response,200);
    }

     public function loginapi(Request $request): JsonResponse
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $providerApprovalStatus = providerApprovalStatus();
        $user = User::where('email', $request->email)->first();
        
        if ($user && ($user->user_type == 1 || $user->user_type == 5)) {
            return response()->json([
                'status'  => false,
                'code'    => 422,
                'message' => __('Admin access is not allowed!'),
            ], 422);
        }

         if ($user && $user->user_type == 2 && $user->provider_verified_status == 0 && $providerApprovalStatus == 1) {
            return response()->json([
                'code'    => 200,
                'message' => __('provider_not_verified_info'),
                'user_type' => $user->user_type,
                'provider_verified_status' => 0,
            ], 200);
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('user-token')->plainTextToken;

            return response()->json([
                'code' => 200,
                'message' => 'Login successful',
                'token' => $token,
                'user_id' => $user->id,
                'user_type' => $user->user_type,
                'provider_verified_status' => 1
            ], 200);
        }

        return response()->json([
            'code' => 401,
            'message' => 'Invalid login credentials',
        ], 401);
    }

    public function register(Request $request): JsonResponse
    {
        // Validate input
        $validatedData = $request->validated();

        // Create the user
        $user = User::create([
            'name' => $validatedData['first_name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
        ]);

        // Create the user details
        UserDetail::create([
            'user_id' => $user->id,
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'] ?? null,
        ]);

        // Return a successful response
        return response()->json([
            'code' => 200,
            'message' => 'Registration successful',
            'user_id' => $user->id, // Return the user ID
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {

        // Get the currently authenticated user
        $user = Auth::guard('sanctum')->user();

        // Check if the user is authenticated
        if ($user) {
            // Revoke the user's current token
            $user->currentAccessToken()->delete();

            return response()->json([
                'code' => 200,
                'message' => 'Logout successful',
            ], 200);
        }

        return response()->json([
            'code' => 401,
            'message' => 'Unauthorized',
        ], 401);
    }
}