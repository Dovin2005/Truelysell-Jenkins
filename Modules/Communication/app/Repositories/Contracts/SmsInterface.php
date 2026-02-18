<?php

namespace Modules\Communication\app\Repositories\Contracts;

interface SmsInterface
{
    public function sendSms(array $data): array;
    public function getActiveSmsSettings(): array;
}