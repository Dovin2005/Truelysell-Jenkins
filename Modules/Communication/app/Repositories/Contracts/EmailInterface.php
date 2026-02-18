<?php

namespace Modules\Communication\app\Repositories\Contracts;

interface EmailInterface
{
    public function sendEmail(array $data): array;
    public function sendBulkEmail(array $emails, array $data): array;
}   