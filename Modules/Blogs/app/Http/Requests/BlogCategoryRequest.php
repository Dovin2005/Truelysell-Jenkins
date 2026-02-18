<?php

namespace Modules\Blogs\app\Http\Requests;

use App\Library\CustomFailedValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Blogs\app\Models\BlogCategory;

class BlogCategoryRequest extends CustomFailedValidation
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('slug')) {
            $this->merge([
                'slug' => str_replace(' ', '-', trim($this->input('slug')))
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $languageId = $this->input('language_id');
        $id = $this->input('id');
        $parentId = 0;

        $category = BlogCategory::where(['id' => $id])->first();
        if ($category) {
            $parentId = $category->parent_id;
        }

        if ($this->input('method') === 'add') {
            return [
                'category_name' => 'required|string|max:255|unique:blog_categories,name',
                'slug' => 'required|string|max:255|unique:blog_categories,slug',
            ];
        } elseif ($this->input('method') === 'update') {
            return [
                'category_name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('blog_categories', 'name')
                        ->where(function ($query) use ($languageId, $id, $parentId) {
                            if ($parentId == 0) {
                                $query->where('language_id', $languageId)
                                    ->where('parent_id', '!=', $id)
                                    ->where('id', '!=', $id);
                            } else {
                                $query->where('language_id', '!=', $languageId)
                                        ->where('parent_id', '=', $id)
                                        ->where('id', '!=', $id);
                            }
                    }),
                ],
                'slug' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('blog_categories', 'slug')
                        ->where(function ($query) use ($languageId, $id, $parentId) {
                            if ($parentId == 0) {
                                $query->where('language_id', $languageId)
                                    ->where('parent_id', '!=', $id)
                                    ->where('id', '!=', $id);
                            } else {
                                $query->where('language_id', '!=', $languageId)
                                      ->where('parent_id', $id)
                                      ->where('id', '!=', $id);
                            }
                    }),
                ],
            ];
        }
        return [];

    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_name.required' => __('Category Name is required.'),
            'category_name.unique' => __('Category Name already exists.'),
            'slug.required' => __('Slug is required.'),
            'slug.unique' => __('Slug already exists.'),
        ];
    }
}
