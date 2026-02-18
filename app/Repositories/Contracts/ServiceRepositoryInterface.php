<?php

namespace App\Repositories\Contracts;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ServiceRepositoryInterface
{
    public function productlistcategory(Request $request, $slug, $is_mobile = false): JsonResponse | View;
    public function catlist(Request $request): JsonResponse | View;
    public function addtocart(Request $request): JsonResponse;
    public function onlyproductlist(Request $request): JsonResponse | View;
    public function productlist(Request $request): JsonResponse | View;
    public function productonlydetail(Request $request): JsonResponse | View;
    public function productdetail(Request $request): JsonResponse | View;
    public function editservice(Request $request): View | JsonResponse;
    public function addComments(Request $request): JsonResponse;
    public function listComments(Request $request): JsonResponse | View;
    public function getReviewList(Request $request): JsonResponse;
}