<?php

namespace Modules\Page\app\Http\Controllers\Api;

use Modules\GlobalSetting\app\Models\Currency;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Page\app\Models\Page;
use Illuminate\Support\Facades\DB;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Modules\GlobalSetting\app\Models\Language;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Cache;
use Modules\Blogs\app\Http\Controllers\BlogsController;
use Modules\MenuBuilder\app\Models\Menu;
use Modules\Page\app\Repositories\Contracts\PageRepositoryInterface;
use function Laravel\Prompts\alert;

class PageController extends Controller
{
    protected $footerRepository;

    public function __construct(PageRepositoryInterface $footerRepository)
    {
        $this->footerRepository = $footerRepository;
    }

    public function pageBuilderApi(Request $request): JsonResponse | view
    {
        $response = $this->footerRepository->pageBuilderApi($request);
        return $response;
    }

    public function deletePage(Request $request): JsonResponse
    {
        $response = $this->footerRepository->deletePage($request);
        return $response;
    }
}
