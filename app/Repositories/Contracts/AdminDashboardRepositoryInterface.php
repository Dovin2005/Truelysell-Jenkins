<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

interface AdminDashboardRepositoryInterface
{
    public function index(Request $request): array;
    public function add(Request $request): array;
    public function showFormCategories(Request $request): array;
}
