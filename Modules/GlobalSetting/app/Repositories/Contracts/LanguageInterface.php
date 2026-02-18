<?php

namespace Modules\GlobalSetting\app\Repositories\Contracts;

interface LanguageInterface
{
    public function index(array $filters);
    public function store(array $data);
    public function setDefault(int $id, string $type, int $status);
    public function delete(int $id);
    public function updateTranslation(int $languageId, array $translations);
    public function getTranslations(?int $languageId);
    public function setUserLanguage(int $userId, int $languageId);
}