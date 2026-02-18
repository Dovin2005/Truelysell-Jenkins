<?php

namespace Modules\Page\app\Repositories\Contracts;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

interface PageRepositoryInterface
{
    public function index(Request $request): JsonResponse;
    public function store(Request $request): JsonResponse;
    public function getFooterDetails(): JsonResponse;
    public function pageBuilderApi(Request $request): JsonResponse | view;
    public function deletePage(Request $request): JsonResponse;
    public function indexSection(Request $request): JsonResponse;
    public function getPageDetails(Request $request): JsonResponse;
    public function getDetails(Request $request): JsonResponse;
    public function indexBuilderList(Request $request): JsonResponse;
    public function storeSection(Request $request): JsonResponse;
    public function pageBuilderStore(Request $request): JsonResponse;
    public function pageBuilderUpdate(Request $request): JsonResponse;
    public function delete(Request $request): JsonResponse;
}
