<?php

namespace Modules\Advertisement\app\Repositories\Contracts;

use Illuminate\Http\Request;

interface AdvertisementRepositoryInterface
{
    public function index(): array;
    public function createAd(Request $request): array;
    public function editAd(Request $request): array;
    public function deleteAd(Request $request): array;
}