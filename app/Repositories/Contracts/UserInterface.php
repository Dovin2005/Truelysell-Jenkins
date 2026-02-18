<?php

namespace App\Repositories\Contracts;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as SupportCollection;


interface UserInterface
{
    public function register(array $data): array;
    public function createUser(array $userData): \App\Models\User;
    public function createUserDetails(array $detailsData): \App\Models\UserDetail;
    public function generateOtp(string $email, int $digitLimit, string $otpExpireTime): array;
    public function sendWelcomeEmail(array $userData): void;
    public function getProfileDetails(int $userId);
    public function saveProfileDetails(array $data, ?int $userId);
    public function updateProfileImage(string $path, int $userId);
    public function updateCompanyImage(string $path, int $userId);
    public function updateBranchStaffs(array $branchIds, int $staffId);
    public function getUserList(
        int $type,
        array | string | null $categoryIds = null,
        ?string $keywords = null,
        ?string $location = null,
        ?array $ratings = null,
        ?string $listType = null,
        int | string $languageId = '1'
    ): EloquentCollection;
    
    public function getUserFavorites(int $userId): EloquentCollection;
    public function getUserDetails(int $userId): EloquentCollection;
    public function verifyProvider(int $userId): bool;
    public function updateUserStatus(int $userDetailId, int $status): bool;
    public function deleteUser(int $userId): bool;
    public function checkUniqueField(array $data): bool;
     public function registerProvider(
        array $providerData,
        ?array $subcategoryIds = null,
        array $companyDetails = [],
        bool $isMobile = false
    ): array;

    public function getRegistrationStatus();
    
    public function generateOtpVerification(
        string $email,
        string $name,
        ?string $firstName,
        ?string $lastName,
        string $phoneNumber,
        string $password,
        int $categoryId,
        ?array $subcategoryIds = null,
        ?string $companyName = null,
        ?string $companyWebsite = null,
        ?string $subServiceType = null,
        bool $isMobile
    ): array;
    
    public function sendProviderWelcomeEmail(
        array $providerData,
        int $notificationType = 1,
        string $signupDate = ''
    ): void;
    
    public function sendProviderSignupEmailToAdmin(
        array $providerData,
        int $notificationType = 32,
        string $signupDate = ''
    ): void;

    public function getStaffList(int $userId): SupportCollection;
    public function deleteStaff(int $staffId): bool;
    public function getUserDashboardData(int $userId): array;
    public function getUserDashboardDataForAdmin(int $userId): array;

    public function updatePassword(string $email, string $newPassword): bool;
    public function deleteAccount(int $id, string $password, string $languageCode = 'en'): bool;

    public function deleteDevice(int $deviceId): bool;
    public function verifyPassword(string $email, string $password): bool;
    public function updateUserPassword(int $userId, string $newPassword): bool;
    public function getUserDevices(int $userId): SupportCollection;
}