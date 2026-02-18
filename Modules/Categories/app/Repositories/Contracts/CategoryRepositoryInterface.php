<?php

namespace Modules\Categories\app\Repositories\Contracts;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

interface CategoryRepositoryInterface
{
    public function index(Request $request): JsonResponse;
    public function show(Request $request, $id): JsonResponse;
    public function store(Request $request): JsonResponse;
    public function destroy(Request $request): JsonResponse;
    public function changeFeatured(Request $request): JsonResponse;
    public function subcategoryList(Request $request): JsonResponse;
    public function subcategoryStore(Request $request): JsonResponse;
    public function getSubcategories(Request $request): JsonResponse;
    public function categories(Request $request): JsonResponse;
    public function getRegisterSubcategories(Request $request): JsonResponse;
    public function getAllLanguages(): JsonResponse;
}
