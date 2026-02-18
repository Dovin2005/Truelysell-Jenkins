<?php

namespace Modules\GlobalSetting\app\Repositories\Contracts;

interface DbbackupInterface
{
    public function index(array $filters);
    public function createBackup();
    public function downloadBackup(int $id);
}