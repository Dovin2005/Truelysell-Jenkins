<?php

namespace Modules\Page\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Modules\GlobalSetting\app\Models\Language;
use Modules\Page\app\Models\Footer;
use Illuminate\Support\Facades\Cache;
use Modules\Page\app\Repositories\Contracts\PageRepositoryInterface;

class FooterController extends Controller
{
    protected $footerRepository;

    public function __construct(PageRepositoryInterface $footerRepository)
    {
        $this->footerRepository = $footerRepository;
    }
    public function index(Request $request): JsonResponse
    {
       $response = $this->footerRepository->index($request);
       return $response;
    }

    public function store(Request $request): JsonResponse
    {
        $response = $this->footerRepository->store($request);
        return $response;
    }

    public function getFooterDetails(): JsonResponse
    {
        $response = $this->footerRepository->getFooterDetails();
        return $response;
    }

}
