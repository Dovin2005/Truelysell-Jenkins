<?php

namespace Modules\Report\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Modules\Report\app\Repositories\Contracts\ReportRepositoryInterface;

class ReportController extends Controller
{
     protected ReportRepositoryInterface $reportRepository;

    public function __construct(ReportRepositoryInterface $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }
    
    public function listAllTransactions(Request $request): JsonResponse
    {
        $response = $this->reportRepository->listAllTransactions($request);
        return $response;
    }


    function mapDateFormatToSQL($phpFormat)
    {
        $replacements = [
            'd' => '%d',
            'D' => '%a',
            'j' => '%e',
            'l' => '%W',
            'F' => '%M',
            'm' => '%m',
            'M' => '%b',
            'n' => '%c',
            'Y' => '%Y',
            'y' => '%y',
        ];

        return strtr($phpFormat, $replacements);
    }

    public function getProviderPaymentList(): View
    {
        return view('report::provider.paymentreport');
    }

    public function listProviderTransactions(Request $request): JsonResponse
    {
        $response = $this->reportRepository->listProviderTransactions($request);
        return $response;
    }
}
