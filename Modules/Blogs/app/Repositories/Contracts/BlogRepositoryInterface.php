<?php

namespace Modules\Blogs\app\Repositories\Contracts;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

interface BlogRepositoryInterface
{
    public function index(Request $request): JsonResponse;
    public function store(Request $request): JsonResponse;
    public function destroy(Request $request): JsonResponse;
    public function categoryStatusChange(Request $request): JsonResponse;
    public function getBlogCategory(Request $request): JsonResponse;
    public function getCategory(Request $request): JsonResponse;
    public function savePost(Request $request): JsonResponse;
    public function listPost(Request $request): JsonResponse;
    public function getPost(Request $request): JsonResponse;
    public function deletePost(Request $request): JsonResponse;
    public function postStatusChange(Request $request): JsonResponse;
    public function checkUniqueCategoryName(Request $request): JsonResponse;
    public function checkUniqueCategorySlug(Request $request): JsonResponse;
    public function checkUniquePostTitle(Request $request): JsonResponse;
    public function checkUniquePostSlug(Request $request): JsonResponse;
    public function blogList(Request $request): JsonResponse | View;
    public function blogDetails(Request $request): JsonResponse | View;
    public function addComment(Request $request): JsonResponse;
    public function listComments(Request $request): JsonResponse;
}
