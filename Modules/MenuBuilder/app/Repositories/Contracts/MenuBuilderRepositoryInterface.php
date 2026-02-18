<?php

namespace Modules\MenuBuilder\app\Repositories\Contracts;

use Illuminate\Http\Request;

interface MenuBuilderRepositoryInterface
{
    public function index(Request $request): array;
    public function store(Request $request): array;
    public function getBuiltMenus(Request $request): array;
}
