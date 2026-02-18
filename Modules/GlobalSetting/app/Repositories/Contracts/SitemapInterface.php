<?php

namespace Modules\GlobalSetting\app\Repositories\Contracts;

interface SitemapInterface
{
    public function index(array $params);
    public function store(array $data);
    public function delete(int $id);
    public function generate();
    public function getUrls(array $params);
}