<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\SocialAuthRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class SocialiteController extends Controller
{
    protected $socialAuthRepository;

    public function __construct(SocialAuthRepositoryInterface $socialAuthRepository)
    {
        $this->socialAuthRepository = $socialAuthRepository;
    }

    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            $rawUser = $socialUser->user;

            $firstName = $rawUser['given_name'] ?? null;
            $lastName = $rawUser['family_name'] ?? null;
            $email = $socialUser->getEmail();

            $user = $this->socialAuthRepository->findUserByEmail($email);

            if (!$user) {
                $user = $this->socialAuthRepository->createUser([
                    'name' => strtolower(str_replace(' ', '', $socialUser->getName())),
                    'email' => $email,
                    'user_type' => 3,
                    'password' => Hash::make('Password@1234'),
                    'auth_provider_id' => $socialUser->getId(),
                    'auth_provider' => $provider,
                ], [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]);
            } else {
                $this->socialAuthRepository->updateUserAuthProvider(
                    $user,
                    $provider,
                    $socialUser->getId()
                );
            }

            $this->socialAuthRepository->loginUser($user);

            return redirect()->route('home');
        } catch (Exception $e) {
            Log::error("{$provider} OAuth Error: " . $e->getMessage());
            return redirect('/')->with('error', 'Failed to authenticate.');
        }
    }
}