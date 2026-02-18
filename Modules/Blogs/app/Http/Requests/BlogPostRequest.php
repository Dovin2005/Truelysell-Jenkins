<?php

namespace Modules\Blogs\app\Http\Requests;

use App\Library\CustomFailedValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Blogs\app\Models\BlogPost;

class BlogPostRequest extends CustomFailedValidation
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
                'slug' => strtolower(str_replace(' ', '-', trim($this->input('slug'))))
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

        $post = BlogPost::where(['id' => $id])->first();
        if ($post) {
            $parentId = $post->parent_id;
        }

        if ($this->input('method') === 'add') {
            return [
                'title' => 'required|string|max:255|unique:blog_posts,title',
                'slug' => 'required|string|max:255|unique:blog_posts,slug',
                'image' => 'required|mimes:jpeg,jpg,png,webp|max:2048',
                'category' => 'required',
                'description' => 'required',
                'seo_description' => [
                    function ($attribute, $value, $fail) {
                        if (str_word_count($value) > 300) {
                            $fail(__('SEO Description should contain no more than 300 words.'));
                        }
                    },
                ],
            ];
        } elseif ($this->input('method') === 'update') {
            return [
                'title' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('blog_posts', 'title')
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
                'slug' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('blog_posts', 'slug')
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
                'image' => 'mimes:jpeg,jpg,png,webp|max:2048',
                'category' => 'required',
                'description' => 'required',
                'seo_description' => [
                    function ($attribute, $value, $fail) {
                        if (str_word_count($value) > 300) {
                            $fail(__('SEO Description should contain no more than 300 words.'));
                        }
                    },
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
            'title.required' => __('Title is required.'),
            'title.unique' => __('Title already exists.'),
            'slug.required' => __('Slug is required.'),
            'slug.unique' => __('Slug already exists.'),
            'image.required' => __('Image is required.'),
            'image.mimes' => __('Only JPEG, JPG, PNG and WEBP formats are allowed.'),
            'image.max' => __('The image may not be greater than 2MB.'),
            'category.required' => __('Category is required.'),
            'description.required' => __('Description is required.'),

        ];
    }
}
