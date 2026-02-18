<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request; 
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Contracts\AdminLoginRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    protected $adminLoginRepository;

    public function __construct(AdminLoginRepositoryInterface $adminLoginRepository)
    {
        $this->adminLoginRepository = $adminLoginRepository;
    }

    public function login(AdminLoginRequest $request): JsonResponse
    {
        $response = $this->adminLoginRepository->login($request);
        return $response;
    }

    public function userlogin(AdminLoginRequest $request): JsonResponse
    {
        $response = $this->adminLoginRepository->userlogin($request);
        return $response;
    }

    public function logout(Request $request): JsonResponse
    {
        if (Auth::check()) {

            Cache::forget('auth_user_id');
            Auth::logout();
    
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

    public function saveAdminDetails(Request $request): JsonResponse
    {
        $response = $this->adminLoginRepository->saveAdminDetails($request);
        return $response;
        
    }

    public function getAdminDetails(Request $request): View|JsonResponse
    {
        $response = $this->adminLoginRepository->getAdminDetails($request);
        return $response;
    }

    public function changePassword(Request $request): JsonResponse
    {
        $response = $this->adminLoginRepository->changePassword($request);
        return $response;
    }

    public function checkPassword(Request $request)
    {
        $id = $request->id;
        $user = User::find($id);

        if (!$user) {
            return response()->json(false);
        }
    
        $isValid = Hash::check($request->current_password, $user->password);
    
        return response()->json($isValid);
    }

}

