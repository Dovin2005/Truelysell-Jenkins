<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\AdminDashboardRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    protected AdminDashboardRepositoryInterface $adminDashboardRepository;

    public function __construct(AdminDashboardRepositoryInterface $adminDashboardRepository)
    {
        $this->adminDashboardRepository = $adminDashboardRepository;
    }

    public function index(Request $request): View
    {
        $response = $this->adminDashboardRepository->index($request);
        return view('admin.dashboard', $response);
    }

    public function add(Request $request): View
    {
        $response = $this->adminDashboardRepository->add($request);
        return view('admin.invoice-template', $response);
    }

    public function showFormCategories(Request $request): View
    {
        $response = $this->adminDashboardRepository->showFormCategories($request);
        return view('admin.form-categories', $response);
    }

}