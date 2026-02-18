<?php

namespace Modules\Page\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;
use Carbon\Carbon;
use Modules\Page\app\Models\Page;
use Illuminate\Support\Str;
use Modules\Page\app\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\GlobalSetting\app\Models\Language;
use Modules\Page\app\Repositories\Contracts\PageRepositoryInterface;
use Modules\Page\app\Http\Requests\PageRequest;
use Modules\Page\app\Http\Requests\pageBuilderUpdateRequest;
class SectionController extends Controller
{

    protected $footerRepository;

    public function __construct(PageRepositoryInterface $footerRepository)
    {
        $this->footerRepository = $footerRepository;
    }


    public function index(Request $request): JsonResponse
    {
        $response = $this->footerRepository->indexSection($request);
        return $response;
    }

    public function getPageDetails(Request $request): JsonResponse
    {
        $response = $this->footerRepository->getPageDetails($request);
        return $response;
    }

    public function getDetails(Request $request): JsonResponse
    {
        $response = $this->footerRepository->getDetails($request);
        return $response;
    }

    public function indexBuilderList(Request $request): JsonResponse
    {
        $response = $this->footerRepository->indexBuilderList($request);
        return $response;
    }

    public function store(Request $request): JsonResponse
    {
        $response = $this->footerRepository->storeSection($request);
        return $response;
    }

    public function pageBuilderStore(PageRequest $request): JsonResponse
    {
        $response = $this->footerRepository->pageBuilderStore($request);
        return $response;
    }


    public function pageBuilderUpdate(pageBuilderUpdateRequest $request): JsonResponse
    {
        $response = $this->footerRepository->pageBuilderUpdate($request);
        return $response;
    }


    public function delete(Request $request): JsonResponse
    {
        $response = $this->footerRepository->delete($request);
        return $response;
    }
}
