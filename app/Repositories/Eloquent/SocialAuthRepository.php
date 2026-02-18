<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\UserDetail;
use App\Repositories\Contracts\SocialAuthRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class SocialAuthRepository implements SocialAuthRepositoryInterface
{
    protected $user;
    protected $userDetail;

    public function __construct(User $user, UserDetail $userDetail)
    {
        $this->user = $user;
        $this->userDetail = $userDetail;
    }

    public function findUserByEmail(string $email)
    {
        return $this->user->where('email', $email)->first();
    }

    public function createUser(array $userData, array $userDetailData)
    {
        $user = $this->user->create($userData);
        $userDetailData['user_id'] = $user->id;
        $this->userDetail->create($userDetailData);
        return $user;
    }

    public function updateUserAuthProvider($user, string $provider, string $providerId)
    {
        $user->auth_provider = $provider;
        $user->auth_provider_id = $providerId;
        $user->save();
        return $user;
    }

    public function loginUser($user)
    {
        Auth::login($user);
        session(['user_id' => $user->id]);
        Cache::forget('user_auth_id');
        Cache::forever('user_auth_id', $user->id);
    }
}