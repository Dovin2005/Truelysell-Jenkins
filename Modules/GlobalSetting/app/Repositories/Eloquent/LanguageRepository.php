<?php

namespace Modules\GlobalSetting\app\Repositories\Eloquent;

use Modules\GlobalSetting\app\Repositories\Contracts\LanguageInterface;
use Modules\GlobalSetting\app\Models\Language;
use Modules\GlobalSetting\app\Models\TranslationLanguage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LanguageRepository implements LanguageInterface
{
    public function index(array $filters)
    {
        $query = Language::query()->orderBy('id', 'desc');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                ->orWhere('code', 'like', '%' . $filters['search'] . '%');
            });
        }

        $languages = $query->get()->map(function ($language) {
            $language->direction = strtolower($language->direction);
            return $language;
        });

        return $languages;
    }


    public function store(array $data)
    {
        $translationLanguage = TranslationLanguage::find($data['translation_language_id']);

        if (!$translationLanguage) {
            throw new \Exception('Translation language not found.');
        }

        $languageData = [
            'name' => $translationLanguage->name,
            'code' => $translationLanguage->code,
            'direction' => $data['direction'] ?? 'ltr',
            'status' => 1,
        ];

        $basePath = resource_path('lang/en.json');
        $langDirectory = resource_path('lang');
        $newPath = $langDirectory . '/' . $languageData['code'] . '.json';
        
        if (!is_dir($langDirectory)) {
            mkdir($langDirectory, 0777, true);
        }
        
        if (file_exists($basePath)) {
            $baseJson = json_decode(file_get_contents($basePath), true);
            $newJson = $baseJson;
            file_put_contents($newPath, json_encode($newJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return Language::create($languageData);
        }

        throw new \Exception('Base language file not found.');
    }

    public function setDefault(int $id, string $type, int $status)
    {
        $language = Language::findOrFail($id);

        if ($type === 'default') {
            if ($language->status != 1) {
                throw new \Exception('The language must be active to set as default.');
            }

            Language::where('is_default', 1)->update(['is_default' => 0]);
            Language::where('id', $id)->update(['is_default' => 1]);

            if (Auth::check()) {
                Auth::user()->update(['user_language_id' => $id]);
            }
        } elseif ($type === 'status') {
            $activeLanguages = Language::where('status', 1)->count();

            if ($language->is_default) {
                throw new \Exception('Default language cannot be disabled. Please set another language as default.');
            }

            if ($activeLanguages == 0) {
                throw new \Exception('At least one language must be active.');
            }

            $language->update(['status' => $status]);
        } elseif ($type === 'rtl') {
            $direction = $status == 1 ? 'rtl' : 'ltr';
            $language->update(['direction' => $direction]);
        }

        return $language;
    }

    public function delete(int $id)
    {
        $language = Language::findOrFail($id);
        $language->deleted_at = Carbon::now();
        $language->save();
        return $language;
    }

    public function updateTranslation(int $languageId, array $translations)
    {
        $language = Language::findOrFail($languageId);
        $jsonFilePath = resource_path("lang/{$language->code}.json");

        // Get existing translations
        if (!File::exists($jsonFilePath)) {
            return redirect()->back()->with('error', 'Translation file not found.');
        }

        $existingTranslations = json_decode(File::get($jsonFilePath), true);

        // Update values from form input
        foreach ($translations as $key => $value) {
            $existingTranslations[$key] = $value ?? '';
        }

        // Save updated JSON (preserving Unicode characters)
        File::put($jsonFilePath, json_encode($existingTranslations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return true;
    }

    public function getTranslations(?int $languageId)
    {
        $language = Language::findOrFail($languageId);
        $basePath = resource_path("lang/en.json");
        $targetPath = resource_path("lang/{$language->code}.json");

        if (!File::exists($basePath)) {
            throw new \Exception('Base language file not found.');
        }

        $baseData = json_decode(File::get($basePath), true);
        $targetData = File::exists($targetPath) ? json_decode(File::get($targetPath), true) : [];

        foreach ($baseData as $key => $value) {
            if (!array_key_exists($key, $targetData)) {
                $targetData[$key] = '';
            }
        }

        ksort($targetData);
        return $targetData;
    }

    public function setUserLanguage(int $userId, int $languageId)
    {
        $user = User::findOrFail($userId);
        $language = Language::findOrFail($languageId);

        if ($language->status != 1) {
            throw new \Exception('The language must be active to set as default.');
        }

        $user->update(['user_language_id' => $languageId]);
        return $language;
    }
}