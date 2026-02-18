<?php

namespace Modules\MenuBuilder\app\Repositories\Eloquent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\GlobalSetting\app\Models\Language;
use Modules\MenuBuilder\app\Models\Menu;
use Modules\MenuBuilder\app\Repositories\Contracts\MenuBuilderRepositoryInterface;

class MenuBuilderRepository implements MenuBuilderRepositoryInterface
{
    public function index(Request $request): array
    {
        try {

            $langCode = App::getLocale();
            if (request()->has('language_code') && !empty($request->language_code)) {
                $langCode = $request->language_code;
            }
            $language = Language::where('code', $langCode)->first();
            $languageId = $language->id;

            if (request()->has('language_id') && !empty($request->language_id) && $request->language_id != $language->id) {
                $languageId = $request->language_id;
            }

            $data = Menu::select('id', 'menus', 'language_id')->where(['language_id' => $languageId])->first();

            if ($data && !empty($data->menus)) {
                $data->menus = json_decode($data->menus);
            }

            return [
                'code' => 200,
                'message' => __('Website menus retrieved successfully.'),
                'data' => $data ?? [],
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('An error occurred while retrieving website menus.'),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function store(Request $request): array
    {
        $menus = $request->input('menus');
        $id = $request->id;

        try {

            $langCode = App::getLocale();
            $language = Language::where('code', $langCode)->first();
            $languageId = $language->id;

            if (request()->has('language_id') && !empty($request->language_id)) {
                $languageId = $request->language_id;
                $languageData = Language::where('id', $languageId)->first();
                $langCode = $languageData->code;
            }

            if (empty($id)) {
                Menu::create([
                    'menus' => $menus,
                    'language_id' => $languageId
                ]);
            } else {
                Menu::where(['id' => $id, 'language_id' => $languageId])->update([
                    'menus' => $menus,
                    'language_id' => $languageId
                ]);
            }
            Cache::forget('menuList');

            return [
                'code' => 200,
                'message' => __('menu_save_success', [], $langCode),
                'data' => json_encode($menus),
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('menus_save_error', [], $langCode),
                'error' => $e->getMessage()
            ];
        }
    }

    public function getBuiltMenus(Request $request): array
    {
        $orderBy = $request->order_by ?? 'asc';

        try {

            $langCode = App::getLocale();
            if (request()->has('language_code') && !empty($request->language_code)) {
                $langCode = $request->language_code;
            }
            $language = Language::where('code', $langCode)->first();
            $languageId = $language->id;

            if (request()->has('language_id') && !empty($request->language_id) && $request->language_id != $language->id) {
                $languageId = $request->language_id;
            }

            $data = DB::table('pages')
                ->select('id', 'page_title', 'slug')
                ->where(['language_id' => $languageId])
                ->orderBy('id', $orderBy)
                ->get();

            return [
                'code' => 200,
                'message' => __('Built in menus retrieved successfully.'),
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('An error occurred while retrieving built in menus.'),
                'error' => $e->getMessage(),
            ];
        }
    }
}