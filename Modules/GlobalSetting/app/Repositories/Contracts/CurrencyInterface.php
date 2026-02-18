<?php

namespace Modules\GlobalSetting\app\Repositories\Contracts;

interface CurrencyInterface
{
    public function index(array $filters);
    public function store(array $data);
    public function setDefault(int $id);
    public function changeStatus(int $id, bool $status);
    public function destroy(int $id);
    public function checkUnique(string $field, string $value);
}