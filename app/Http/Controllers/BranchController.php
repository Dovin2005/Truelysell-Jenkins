<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\BranchRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    protected BranchRepositoryInterface $branchRepository;

    public function __construct(BranchRepositoryInterface $branchRepository)
    {
        $this->branchRepository = $branchRepository;
    }
    
    public function index(Request $request): JsonResponse
    {
        $response = $this->branchRepository->index($request);
        return response()->json($response, $response['code']);
    }

    public function addbranch(Request $request): View
    {
        $response = $this->branchRepository->addBranch($request);
        return view('provider.add_branch', $response);
    }

    public function branch(Request $request): View
    {
        return view('provider.branch_list');
    }

    public function getCountries(Request $request): JsonResponse
    {
        $response = $this->branchRepository->getCountries($request);
        return response()->json($response, $response['code']);
    }

    public function getStates(Request $request): JsonResponse
    {
        $response = $this->branchRepository->getStates($request);
        return response()->json($response, $response['code']);
    }

    public function getCities(Request $request): JsonResponse
    {
        $response = $this->branchRepository->getCities($request);
        return response()->json($response, $response['code']);
    }

    public function saveBranch(Request $request): JsonResponse
    {
        $response = $this->branchRepository->saveBranch($request);
        return response()->json($response, $response['code']);
    }

    public function updateBranch(Request $request): JsonResponse
    {
        $response = $this->branchRepository->updateBranch($request);
        return response()->json($response, $response['code']);
    }

    public function editBranch(Request $request)
    {
        $response = $this->branchRepository->editBranch($request);
        return view('provider.edit_branch', $response);
    }

    public function deleteBranch(Request $request)
    {
        $id = $request->id;
        $response = $this->branchRepository->deleteBranch($id);
        return response()->json($response, $response['code']);
    }

    public function checkUnique(Request $request): JsonResponse
    {
        $response = $this->branchRepository->checkUnique($request);
        return response()->json($response);
    }

    public function providerBranchLimit(Request $request): JsonResponse
    {
        $response = $this->branchRepository->providerBranchLimit($request);
        return response()->json($response, $response['code']);
    }

    public function providerBranchLimitApi(Request $request): JsonResponse
    {
        $response = $this->branchRepository->providerBranchLimitApi($request);
        return response()->json($response, $response['code']);
    }
}