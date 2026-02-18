<?php

namespace Modules\Service\app\Repositories\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

interface ServiceRepositoryInterface
{
    public function setDefault(Request $request): array;
    public function delete(Request $request): array;
    public function index(Request $request): array;
    public function store(Request $request): JsonResponse | RedirectResponse;
    public function update(Request $request): RedirectResponse;
    public function providerServiceIndex(Request $request): array;
    public function getDetails(Request $request, string $slug): array;
    public function providerServiceStore(Request $request): JsonResponse;
    public function verifyService(Request $request): array;
    public function providerServiceUpdate(Request $request): JsonResponse;
    public function deleteServiceImage(string $id): array;
    public function deleteSlot(string $id): array;
    public function deleteAdditionalServices(string $id): array;
    public function deleteServices(Request $request): array;
    public function status(Request $request): array;
    public function checkUnique(Request $request): bool;
    public function checkEditUnique(Request $request): bool;
    public function providerSub(Request $request): array;
    public function providerSubApi(Request $request): array;
    public function translate(Request $request): array;
    public function deleteImage(Request $request): array;
    public function checkCoupon(Request $request): array;
}