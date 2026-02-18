<?php

namespace Modules\GlobalSetting\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\GlobalSetting\app\Http\Requests\StoreLanguageRequest;
use Modules\GlobalSetting\app\Http\Requests\IndexLanguageRequest;
use Modules\GlobalSetting\app\Models\Language;
use Modules\GlobalSetting\app\Repositories\Contracts\LanguageInterface;
use Modules\GlobalSetting\app\Models\TranslationLanguage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;

class LanguageController extends Controller
{
    protected LanguageInterface $languageRepository;

    public function __construct(LanguageInterface $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    public function index(IndexLanguageRequest $request): JsonResponse
    {
        try {
            $languages = $this->languageRepository->index($request->validated());
            
            return response()->json([
                'code' => 200,
                'message' => __('Languages retrieved successfully.'),
                'data' => $languages,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('An error occurred while retrieving languages.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function languageSettings(): View
    {
        $TranslationLanguages = TranslationLanguage::get();
        return view('admin.language-settings', compact('TranslationLanguages'));
    }

    public function store(StoreLanguageRequest $request): JsonResponse
    {
        try {
            $this->languageRepository->store($request->validated());
            
            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => __('language_create_success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'An error occurred while creating the language.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function setDefault(Request $request): JsonResponse
    {
        try {
            $language = $this->languageRepository->setDefault(
                $request->id,
                $request->type,
                $request->status
            );

            if ($request->type == 'default') {
                Cookie::queue('languageId', $request->id, 1440);
                App::setLocale($language->code);
            }

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => __('language_update_success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteLanguage(Request $request): JsonResponse
    {
        try {
            $langCode = $request->language_code ?? app()->getLocale();
            $language = Language::findOrFail($request->input('id'));

            if ($language->is_default == 1) {
                return response()->json([
                    'code' => 400,
                    'success' => false,
                    'message' => 'Default language cannot be deleted.',
                ], 400);
            }
            $this->languageRepository->delete($request->id);
            
            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => __('language_delete_success', [], $langCode),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'Failed to delete language.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function savelangword(Request $request)
    {
        try {
            $this->languageRepository->updateTranslation(
                $request->language_id,
                $request->lantext
            );

            return redirect()
                ->route('listkeywords', ['id' => $request->language_id])
                ->with('success', 'Translations updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function listkeywords(Request $request)
    {
        try {
            $languageMeta = Language::findOrFail($request->id);
            $listwords = $this->languageRepository->getTranslations($request->id);
            
            return view('admin.language-settings-list', [
                'listwords' => $listwords,
                'language_id' => $request->id
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function languagedefault($id): JsonResponse
    {
        try {
           $language = Language::findOrFail($id);

            if ($language->status != 1) {
                return response()->json([
                    'code' => '400',
                    'success' => false,
                    'message' => 'The language must be active to set as default.',
                ], 400);
            }

            Cache::flush();
            cookie()->queue(cookie('languageId', $id, 1440));
            session(['userlanguageId' => $id]);

            $authId = Auth::id();
            $user = User::find($authId);

            if ($user) {
                $user->update(['user_language_id' => $id]);
            }

            return response()->json([
                'code' => '200',
                'success' => true,
                'message' => 'Default language set successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function adminLanguagedefault($id): JsonResponse
    {
        try {
            $language = Language::findOrFail($id);

            if ($language->status != 1) {
                return response()->json([
                    'code' => '400',
                    'success' => false,
                    'message' => 'The language must be active to set as default.',
                    'id' => $id,
                ], 400);
            }

            cookie()->queue(cookie('languageId', $id, 1440));
            session(['userlanguageId' => $id]);
            $userId = Auth::id();

            $user = User::find($userId);
            if ($user) {
                $user->update(['user_language_id' => $id]);
            }

            return response()->json([
                'code' => '200',
                'success' => true,
                'message' => 'Default language set successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function translate(Request $request): JsonResponse
    {
        try {
            $langCode = App::getLocale();
            if (request()->has('language_code') && !empty($request->language_code)) {
                $langCode = $request->language_code;
            }
            $languageId = getLanguageId($langCode);

            if (request()->has('language_id') && !empty($request->language_id) && $request->language_id != $languageId) {
                $lang = Language::select('code')->where('id', $request->language_id)->first();
                $langCode = $lang->code;
            }

            $translatedValues = [];

            $path = resource_path("lang/{$langCode}.json");
            if (file_exists($path)) {
                $translatedValues = json_decode(file_get_contents($path), true);
            }

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => __('Translated Successfully.'),
                'translated_values' => $translatedValues,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => 'Translation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}