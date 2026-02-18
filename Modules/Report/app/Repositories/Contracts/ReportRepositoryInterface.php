<?php

namespace Modules\Report\app\Repositories\Contracts;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

interface ReportRepositoryInterface
{
    public function listAllTransactions(Request $request): JsonResponse;
    public function listProviderTransactions(Request $request): JsonResponse;
}
