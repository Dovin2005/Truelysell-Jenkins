<?php

namespace Modules\Faq\app\Repositories\Eloquent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Modules\Faq\app\Models\Faq;
use Modules\Faq\app\Repositories\Contracts\FaqRepositoryInterface;
use Modules\GlobalSetting\app\Models\Language;

class FaqRepository implements FaqRepositoryInterface
{
    public function getAll(Request $request)
    {
        $orderBy = $request->input('order_by', 'desc');
        $sortBy = $request->input('sort_by', 'id');

        $langCode = $request->language_code ?? App::getLocale();
        $language = Language::where('code', $langCode)->first();
        $languageId = $request->language_id ?? $language->id;

        $faqs = Faq::where('language_id', $languageId)
            ->orderBy($sortBy, $orderBy)
            ->get();

        $data = [];

        foreach ($faqs as $faq) {
            $data[] = [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'status' => $faq->status,
            ];
        }

        return $data;
    }

    public function store(Request $request)
    {
        $exists = Faq::where('question', $request->question)->first();
        if ($exists) {
            return ['exists' => true];
        }

        $last = Faq::latest('order_by')->first();
        $orderBy = $last ? $last->order_by + 1 : 1;

        return Faq::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'status' => $request->status,
            'order_by' => $orderBy,
            'language_id' => $request->language_id ?? 1,
        ]);
    }

    public function update(Request $request)
    {
        $faqId = $request->edit_id;
        $langId = $request->language_id;

        $data = [
            'question' => $request->edit_question,
            'answer' => $request->edit_answer,
            'status' => $request->status,
            'language_id' => $langId
        ];

        $faq = Faq::where('id', $faqId)->where('language_id', $langId)->first();

        if ($faq) {
            return $faq->update($data);
        }

        $faqLang = Faq::where('parent_id', $faqId)->where('language_id', $langId)->first();
        if ($faqLang) {
            return $faqLang->update($data);
        }

        $parent = Faq::find($faqId);
        if ($parent && $parent->parent_id) {
            $parentLang = Faq::where('id', $parent->parent_id)->where('language_id', $langId)->first();
            if ($parentLang) {
                return $parentLang->update($data);
            }
        }

        $last = Faq::latest('order_by')->first();
        $data['order_by'] = $last ? $last->order_by + 1 : 1;
        $data['created_by'] = Cache::get('auth_user_id');
        $data['parent_id'] = $faqId;

        return Faq::create($data);
    }

    public function delete(Request $request)
    {
        $faq = Faq::findOrFail($request->id);
        return $faq->delete();
    }

    public function getById(Request $request)
    {
        $id = $request->id;
        $languageId = $request->language_id;

        $faq = Faq::where('parent_id', $id)->where('language_id', $languageId)->first();

        if (!$faq) {
            $parent = Faq::select('parent_id')->where('id', $id)->first();
            if ($parent) {
                $faq = Faq::where('id', $parent->parent_id)->where('language_id', $languageId)->first();
            }
        }

        if (!$faq) {
            $faq = Faq::where('id', $id)->where('language_id', $languageId)->first();
        }

        return $faq;
    }
}
