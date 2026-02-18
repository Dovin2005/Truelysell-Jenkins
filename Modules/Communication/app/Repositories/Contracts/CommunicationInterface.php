<?php

namespace Modules\Communication\app\Repositories\Contracts;

interface CommunicationInterface
{
    public function getOtpSettings(array $data): array;
    public function getRegisterOtpSettings(array $data): array;
    public function getProviderRegisterOtpSettings(array $data): array;
    public function generateOtp(int $digitLimit): string;
    
    public function verifyOtp(array $data): array;
    public function registerProvider(array $data): array;
    public function registerUser(array $data): array;
    public function handleForgotPassword(array $data): array;
    public function handleLogin(array $data): array;
    public function sendWelcomeEmail(array $data, bool $isProvider = false): void;
}