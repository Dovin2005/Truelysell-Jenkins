<?php

namespace Modules\GlobalSetting\app\Repositories\Contracts;

interface SubscriptionPackageInterface
{
    public function index(array $filters = []);
    public function store(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function find(int $id);
    public function getByType(string $type);
}