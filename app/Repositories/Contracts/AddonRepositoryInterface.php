<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

interface AddonRepositoryInterface
{
    public function index(Request $request): array;
    public function changeAddonStatus(Request $request): array;
    public function listNewAddonModules(Request $request): array;
    public function purchaseModule(Request $request): array;
    public function updateModule(Request $request): string;
}
