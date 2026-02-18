<?php

namespace Modules\Blogs\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Blogs\app\Http\Requests\BlogCategoryRequest;
use Modules\Blogs\app\Http\Requests\BlogPostRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Modules\Blogs\app\Repositories\Contracts\BlogRepositoryInterface;
use Modules\GlobalSetting\Entities\GlobalSetting;

class BlogsController extends Controller
{
    protected BlogRepositoryInterface $blogRepository;

    public function __construct(BlogRepositoryInterface $blogRepository)
    {
        $this->blogRepository = $blogRepository;
    }

    public function blogPost(): View
    {
        $chatStatus = GlobalSetting::where('group_id', 4)
            ->where('key', 'chatgpt_status')
            ->pluck('value')
            ->first();
        return view('blogs::blogs.blog-post', compact('chatStatus'));
    }

    public function index(Request $request): JsonResponse
    {
        $response = $this->blogRepository->index($request);
        return $response;
    }

    public function store(BlogCategoryRequest $request): JsonResponse
    {
         $response = $this->blogRepository->store($request);
        return $response;
    }

    public function destroy(Request $request): JsonResponse
    {
        $response = $this->blogRepository->destroy($request);
        return $response;
    }

    public function categoryStatusChange(Request $request): JsonResponse
    {
        $response = $this->blogRepository->categoryStatusChange($request);
        return $response;
    }

    public function getBlogCategory(Request $request): JsonResponse
    {
        $response = $this->blogRepository->getBlogCategory($request);
        return $response;
    }

    public function getCategory(Request $request): JsonResponse
    {
       $response = $this->blogRepository->getCategory($request);
        return $response;
    }

    public function savePost(BlogPostRequest $request): JsonResponse
    {
         $response = $this->blogRepository->savePost($request);
        return $response;
    }

    public function listPost(Request $request): JsonResponse
    {
       $response = $this->blogRepository->listPost($request);
        return $response;
    }

    public function getPost(Request $request): JsonResponse
    {
        $response = $this->blogRepository->getPost($request);
        return $response;
    }

    public function deletePost(Request $request): JsonResponse
    {
        $response = $this->blogRepository->deletePost($request);
        return $response;
    }

    public function postStatusChange(Request $request): JsonResponse
    {
       $response = $this->blogRepository->postStatusChange($request);
        return $response;
    }

    public function checkUniqueCategoryName(Request $request): JsonResponse
    {
        $response = $this->blogRepository->checkUniqueCategoryName($request);
        return $response;
    }

    public function checkUniqueCategorySlug(Request $request): JsonResponse
    {
        $response = $this->blogRepository->checkUniqueCategorySlug($request);
        return $response;
    }

    public function checkUniquePostTitle(Request $request): JsonResponse
    {
        $response = $this->blogRepository->checkUniquePostTitle($request);
        return $response;
    }

    public function checkUniquePostSlug(Request $request): JsonResponse
    {
        $response = $this->blogRepository->checkUniquePostSlug($request);
        return $response;
    }

    public function blogList(Request $request): JsonResponse | View
    {
        $response = $this->blogRepository->blogList($request);
        return $response;
    }

    public function blogDetails(Request $request): JsonResponse | View
    {
        $response = $this->blogRepository->blogDetails($request);
        return $response;

    }

    public function addComment(Request $request): JsonResponse
    {
       $response = $this->blogRepository->addComment($request);
        return $response;
    }

    public function listComments(Request $request): JsonResponse
    {
        $response = $this->blogRepository->listComments($request);
        return $response;

    }

}
