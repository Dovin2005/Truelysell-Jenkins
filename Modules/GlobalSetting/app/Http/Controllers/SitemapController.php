<?php

namespace Modules\GlobalSetting\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\GlobalSetting\app\Http\Requests\SitemapSettingRequest;
use Modules\GlobalSetting\app\Repositories\Contracts\SitemapInterface;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    protected $sitemapRepository;

    public function __construct(SitemapInterface $sitemapRepository)
    {
        $this->sitemapRepository = $sitemapRepository;
    }

    public function index(): View
    {
        return $this->sitemapRepository->index([]);
    }

    public function store(SitemapSettingRequest $request): JsonResponse
    {
        try {
            $this->sitemapRepository->store($request->validated());
            $this->sitemapRepository->generate();

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => __('sitemap_settings_create_success'),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => __('An error occurred while adding the sitemap URL.'),
                'error' => $th->getMessage()
            ], 422);
        }
    }

    public function generateSitemap(): JsonResponse
    {
        try {
            $result = $this->sitemapRepository->generate();

            return response()->json([
                'status' => $result ? 'success' : 'warning',
                'code' => 200,
                'message' => $result 
                    ? __('Sitemap generated successfully.') 
                    : __('No URLs available to generate sitemap.'),
                'path' => $result ? asset($result) : null,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => __('Failed to generate sitemap.'),
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getSitemapUrls(Request $request): JsonResponse
    {
        try {
            $params = [
                'limit' => $request->input('length', 10),
                'offset' => $request->input('start', 0),
                'search' => $request->input('search.value', ''),
            ];

            $result = $this->sitemapRepository->getUrls($params);

            return response()->json([
                'draw' => $request->input('draw', 0),
                'recordsTotal' => $result['total'],
                'recordsFiltered' => $result['filtered'],
                'data' => $result['data'],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => __('Failed to retrieve sitemap URLs.'),
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function deleteSitemapUrl(Request $request): JsonResponse
    {
        try {
            $this->sitemapRepository->delete($request->id);
            $this->sitemapRepository->generate();

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => __('sitemap_settings_delete_success'),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => __('An error occurred while deleting the sitemap URL.'),
                'error' => $th->getMessage()
            ], 422);
        }
    }
}