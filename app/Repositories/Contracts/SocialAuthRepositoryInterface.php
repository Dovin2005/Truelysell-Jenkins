<?php

namespace App\Repositories\Contracts;

interface SocialAuthRepositoryInterface
{
    public function findUserByEmail(string $email);
    public function createUser(array $userData, array $userDetailData);
    public function updateUserAuthProvider($user, string $provider, string $providerId);
    public function loginUser($user);
}