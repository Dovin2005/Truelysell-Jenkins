<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

interface BranchRepositoryInterface
{
    public function index(Request $request): array;
    public function addBranch(Request $request): array;
    public function getCountries(Request $request): array;
    public function getStates(Request $request): array;
    public function getCities(Request $request): array;
    public function saveBranch(Request $request): array;
    public function updateBranch(Request $request): array;
    public function editBranch(Request $request): array;
    public function deleteBranch(?int $id): array;
    public function checkUnique(Request $request): bool;
    public function providerBranchLimit(Request $request): array;
    public function providerBranchLimitApi(Request $request): array;
}
