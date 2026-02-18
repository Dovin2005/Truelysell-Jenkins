<?php

namespace Modules\Blogs\app\Repositories\Eloquent;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Modules\Blogs\app\Repositories\Contracts\BlogRepositoryInterface;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Blogs\app\Http\Requests\BlogCategoryRequest;
use Modules\Blogs\app\Http\Requests\BlogPostRequest;
use Modules\Blogs\app\Models\BlogCategory;
use Modules\Blogs\app\Models\BlogPost;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;
use Modules\Blogs\app\Models\BlogComment;
use Modules\GlobalSetting\app\Models\Language;
use Modules\GlobalSetting\Entities\GlobalSetting;
class BlogRepository implements BlogRepositoryInterface
{
     public function index(Request $request): JsonResponse
    {
        try {
            $orderBy = $request->order_by ?? 'desc';

            $langCode = App::getLocale();
            if (request()->has('language_code') && !empty($request->language_code)) {
                $langCode = $request->language_code;
            }
            $languageId = getLanguageId($langCode);

            if (request()->has('language_id') && !empty($request->language_id) && $request->language_id != $languageId) {
                $languageId = $request->language_id;
            }

            $data = BlogCategory::where(['language_id' => $languageId])->orderBy('id', $orderBy)->get();

            return response()->json([
                'code' => '200',
                'message' => __('Blog Category details retrieved successfully.'),
                'data' => $data,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'message' => __('An error occurred while retrieving Blog Categories.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

     public function store(Request $request): JsonResponse
    {
        if ($request->has('slug')) {
            $request->merge(['slug' => strtolower(str_replace(' ', '-', trim($request->slug)))]);
        }

        $language = Language::where('id', $request->language_id)->first();
        $langCode = $language->code ?? 'en';

        $data = [
            'name' => $request->category_name,
            'slug' => $request->slug,
        ];

        if ($request->method == 'add') {
            try {
                $data['language_id'] = $request->language_id;

                BlogCategory::create($data);
    
                return response()->json([
                    'code' => 200,
                    'message' => __('blog_category_create_success', [], $langCode),
                ], 200);
    
            } catch (\Exception $e) {
                return response()->json([
                    'code' => 500,
                    'message' => __('blog_category_save_error', [], $langCode),
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            try {
                $id = $request->id;

                $data['language_id'] = $request->language_id;

                $existingCategory = BlogCategory::where('id', $id)
                    ->where('language_id', $request->language_id)
                    ->first();

                $existingLangCategory = BlogCategory::where('parent_id', $id)
                    ->where('language_id', $request->language_id)
                    ->first();

                $existingParentCategory = '';
                $parentCategory = BlogCategory::where('id', $id)->first();
                if ($parentCategory) {
                    $existingParentCategory = BlogCategory::where(['id' => $parentCategory->parent_id, 'language_id' => $request->language_id])->first();
                }

                if ($existingCategory) {
                    BlogCategory::where('id', $id)
                        ->where('language_id', $request->language_id)
                        ->update($data);
                } elseif ($existingLangCategory) {
                    BlogCategory::where('parent_id', $id)
                        ->where('language_id', $request->language_id)
                        ->update($data); 
                } elseif ($existingParentCategory) {
                    BlogCategory::where('id', $parentCategory->parent_id)
                        ->where('language_id', $request->language_id)
                        ->update($data); 
                } else {
                    $data['created_by'] = Cache::get('auth_user_id');
                    $data['parent_id'] = $id;

                    BlogCategory::create($data);
                }
    
                return response()->json([
                    'code' => 200,
                    'message' => __('blog_category_update_success', [], $langCode),
                ], 200);
    
            } catch (\Exception $e) {
                return response()->json([
                    'code' => 500,
                    'message' => __('blog_category_save_error', [], $langCode)
                ], 500);
            }
        }
    }

     public function destroy(Request $request): JsonResponse
    {
        $id = $request->id;
        $langCode = $request->language_code ?? 'en';
        try {
            BlogCategory::where('id', $id)->delete();

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => __('blog_category_delete_success', [], $langCode)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'Error! while deleting blog category.'
            ], 500);
        }
    }

     public function categoryStatusChange(Request $request): JsonResponse
    {
        $id = $request->id;
        $status = $request->status;
        $langCode = $request->language_code ?? 'en';
        
        try {
            BlogCategory::where('id', $id)->update([
                'status' => $status
            ]);

            return response()->json([
                'code' => 200,
                'message' => __('blog_category_status_success', [], $langCode)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error! while changing blog category status'
            ], 500);
        }
    }

    public function getBlogCategory(Request $request): JsonResponse
    {
        try {

            $id = $request->id;            

            $category = '';

            if (request()->has('language_id') && !empty($request->language_id)) {

                $category = BlogCategory::where(['parent_id' => $id, 'language_id' => $request->language_id])->first();
                if (empty($category)) {
                    $categoryData = BlogCategory::select('parent_id')->where(['id' => $id])->first();
                    $category = BlogCategory::where(['id' => $categoryData->parent_id, 'language_id' => $request->language_id])->first();
                }
                if (empty($category)) {
                    $category = BlogCategory::where(['id' => $id, 'language_id' => $request->language_id])->first();
                }
                
            } 

            return response()->json([
                'code' => 200,
                'message' => __('Blog Category details retrieved successfully.'),
                'data' => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('An error occurred while retrieving Blog Categories.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCategory(Request $request): JsonResponse
    {
        try {
            $langCode = App::getLocale();
            if (request()->has('language_code') && !empty($request->language_code)) {
                $langCode = $request->language_code;
            }
            $languageId = getLanguageId($langCode);

            if (request()->has('language_id') && !empty($request->language_id) && $request->language_id != $languageId) {
                $languageId = $request->language_id;
            }

            $data = BlogCategory::where(['status' => 1, 'language_id' => $languageId])->orderBy('id', 'desc')->get();

            return response()->json([
                'code' => '200',
                'message' => __('Blog Category details retrieved successfully.'),
                'data' => $data,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'message' => __('An error occurred while retrieving Blog Categories.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function savePost(Request $request): JsonResponse
    {
        if ($request->has('slug')) {
            $request->merge(['slug' => strtolower(str_replace(' ', '-', trim($request->slug)))]);
        }
        $data = $request->except(['_token', 'method', 'id', 'image']);

        $language = Language::where('id', $request->language_id)->first();
        $langCode = $language->code ?? 'en';

        if ($request->method == 'add') {            
            try {

                $langCode = App::getLocale();
                $languageId = getLanguageId($langCode);

                $data = [
                    'title' => $request->title,
                    'slug' => $request->slug,
                    'category' => $request->category,
                    'description' => $request->description,
                    'tags' => $request->tags,
                    'seo_title' => $request->seo_title,
                    'seo_description' => $request->seo_description,
                    'status' => $request->status ?? 1,
                    'popular' => $request->popular,
                    'created_by' => $request->user_id ?? Cache::get('auth_user_id'),
                    'updated_by' => $request->user_id ?? Cache::get('auth_user_id'),
                    'language_id' => $languageId,
                ];

                $file = $request->file('image');
                if ($file instanceof \Illuminate\Http\UploadedFile ) {
                    $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('blogs', $filename, 'public');
                    $data['image'] = $filename;
                }
                BlogPost::create($data);
    
                return response()->json([
                    'code' => 200,
                    'message' => __('blog_post_create_success', [], $langCode),
                ], 200);
    
            } catch (\Exception $e) {
                return response()->json([
                    'code' => 500,
                    'message' => __('blog_post_save_error', [], $langCode),
                ], 500);
            }
        } else {
            try {
                $id = $request->id;

                $data = [
                    'title' => $request->title,
                    'slug' => $request->slug,
                    'category' => $request->category,
                    'description' => $request->description,
                    'tags' => $request->tags,
                    'seo_title' => $request->seo_title,
                    'seo_description' => $request->seo_description,
                    'status' => $request->status,
                    'popular' => $request->popular,
                    'updated_by' => $request->user_id ?? Cache::get('auth_user_id'),
                ];
                
                $data['language_id'] = $request->language_id;

                $existingPost = BlogPost::where('id', $id)
                    ->where('language_id', $request->language_id)
                    ->first();

                $existingLangPost = BlogPost::where('parent_id', $id)
                    ->where('language_id', $request->language_id)
                    ->first();
                
                $existingParentPost = '';
                $parentPost = BlogPost::where('id', $id)->first();
                if ($parentPost) {
                    $existingParentPost = BlogPost::where(['id' => $parentPost->parent_id, 'language_id' => $request->language_id])->first();
                }

                if ($existingPost) {
                    $oldImage = $existingPost->image;
                    if ($request->hasFile('image')) {
                        $file = $request->file('image');
                        if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                            if (Storage::disk('public')->exists('blogs/'.$oldImage)) {
                                Storage::disk('public')->delete('blogs/'.$oldImage);
                            }
                            $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('blogs', $filename, 'public');
                            $data['image'] = $filename;
                        }
                    }
                    BlogPost::where('id', $id)
                        ->where('language_id', $request->language_id)
                        ->update($data);
                } elseif ($existingLangPost) {
                    $oldImage = $existingLangPost->image;
                    if ($request->hasFile('image')) {
                        $file = $request->file('image');
                        if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                            if (Storage::disk('public')->exists('blogs/'.$oldImage)) {
                                Storage::disk('public')->delete('blogs/'.$oldImage);
                            }
                            $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('blogs', $filename, 'public');
                            $data['image'] = $filename;
                        }
                    }
                    BlogPost::where('parent_id', $id)
                        ->where('language_id', $request->language_id)
                        ->update($data); 
                } elseif ($existingParentPost) {
                    $oldImage = $existingParentPost->image;
                    if ($request->hasFile('image')) {
                        $file = $request->file('image');
                        if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                            if (Storage::disk('public')->exists('blogs/'.$oldImage)) {
                                Storage::disk('public')->delete('blogs/'.$oldImage);
                            }
                            $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('blogs', $filename, 'public');
                            $data['image'] = $filename;
                        }
                    }
                    BlogPost::where('id', $parentPost->parent_id)
                        ->where('language_id', $request->language_id)
                        ->update($data); 
                } else {
                    if ($request->hasFile('image')) {
                        $file = $request->file('image');
                        if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                            $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                            $file->storeAs('blogs', $filename, 'public');
                            $data['image'] = $filename;
                        }
                    }
                    $data['created_by'] = $request->user_id ?? Cache::get('auth_user_id');
                    $data['parent_id'] = $id;
                    
                    BlogPost::create($data);
                }

                return response()->json([
                    'code' => 200,
                    'message' => __('blog_post_update_success', [], $langCode),
                ], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'error_message' => $e->getMessage(),
                    'code' => 500,
                    'message' => __('blog_post_save_error', [], $langCode)
                ], 500);
            }
        }
    }

     public function listPost(Request $request): JsonResponse
    {
        try {
            $orderBy = $request->order_by ?? 'desc';
            $langCode = App::getLocale();
            if (request()->has('language_code') && !empty($request->language_code)) {
                $langCode = $request->language_code;
            }
            $languageId = getLanguageId($langCode);

            if (request()->has('language_id') && !empty($request->language_id) && $request->language_id != $languageId) {
                $languageId = $request->language_id;
            }

            $posts = BlogPost::with(['category' => function($query) {
                        $query->withTrashed();
            }])->where(['language_id' => $languageId])->orderBy('id', $orderBy)->get()->map(function ($post) {
                /** @var \Modules\Blogs\app\Models\BlogPost $post */
                $post->image = $post->image ? $post->file($post->image) : null;
                return $post;
            });

            return response()->json([
                'code' => '200',
                'message' => __('Blog Posts details retrieved successfully.'),
                'data' => $posts,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'message' => __('An error occurred while retrieving Blog Posts.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPost(Request $request): JsonResponse
    {
        try {
            $id = $request->id;
           
            /** @var \Modules\Blogs\app\Models\BlogPost $post */
            $post = '';
            
            if (request()->has('language_id') && !empty($request->language_id)) {
                $post = BlogPost::where(['parent_id' => $id, 'language_id' => $request->language_id])->first();
                if (empty($post)) {
                    $postData = BlogPost::select('parent_id')->where(['id' => $id])->first();
                    $post = BlogPost::where(['id' => $postData->parent_id, 'language_id' => $request->language_id])->first();
                }
                if (empty($post)) {
                    $post = BlogPost::where(['id' => $id, 'language_id' => $request->language_id])->first();
                }
            }

            if (!empty($post)) {
                $post->image = $post->image ? $post->file($post->image) : null;
            }

            return response()->json([
                'code' => '200',
                'message' => __('Blog Posts details retrieved successfully.'),
                'data' => $post,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'message' => __('An error occurred while retrieving Blog Posts.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deletePost(Request $request): JsonResponse
    {
        $id = $request->id;
        $langCode = $request->language_code ?? 'en';

        try {
            BlogPost::where('id', $id)->delete();

            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => __('blog_post_delete_success', [], $langCode)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'Error! while deleting blog category.'
            ], 500);
        }
    }

    public function postStatusChange(Request $request): JsonResponse
    {
        $id = $request->id;
        $status = $request->status;
        $langCode = $request->language_code ?? 'en';

        try {
            BlogPost::where('id', $id)->update([
                'status' => $status
            ]);

            return response()->json([
                'code' => 200,
                'message' => __('blog_post_status_success', [], $langCode)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error! while changing blog post status'
            ], 500);
        }
    }

    public function checkUniqueCategoryName(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $languageId = $request->input('language_id');
        $method = $request->input('method', 'add');
        $parentId = 0;

        $category = BlogCategory::where(['id' => $id])->first();
        if ($category) {
            $parentId = $category->parent_id;
        }

        $nameValidator = Validator::make($request->only('category_name'), [
            'category_name' => [
                Rule::unique('blog_categories', 'name')
                    ->where(function ($query) use ($languageId, $method, $id, $parentId) {
                        if ($method == 'update') {
                            if ($parentId == 0) {
                                $query->where('language_id', $languageId)
                                    ->where('parent_id', '!=', $id)
                                    ->where('id', '!=', $id);
                            } else {
                                $query->where('language_id', '!=', $languageId)
                                      ->where('parent_id', $id)
                                      ->where('id', '!=', $id);
                            }
                                
                        }
                    }),
            ],
        ]);
        if ($nameValidator->fails()) {
            return response()->json(false);
        }

        return response()->json(true);
    }

    public function checkUniqueCategorySlug(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $languageId = $request->input('language_id');
        $method = $request->input('method', 'add');
        $parentId = 0;

        $category = BlogCategory::where('id', $id)->first();
        if ($category) {
            $parentId = $category->parent_id;
        }

        if ($request->has('slug')) {
            $request->merge([
                'slug' => str_replace(' ', '-', trim($request->input('slug')))
            ]);
        }

        $slugValidator = Validator::make($request->only('slug'), [
            'slug' => [
                Rule::unique('blog_categories', 'slug')
                    ->where(function ($query) use ($languageId, $method, $id, $parentId) {
                        if ($method == 'update') {
                            if ($parentId == 0) {
                                $query->where('language_id', $languageId)
                                    ->where('parent_id', '!=', $id)
                                    ->where('id', '!=', $id);
                            } else {
                                $query->where('language_id', '!=', $languageId)
                                      ->where('parent_id', $id)
                                      ->where('id', '!=', $id);
                            }
                                
                        }
                    }),
            ],
        ]);
        if ($slugValidator->fails()) {
            return response()->json(false);
        }

        return response()->json(true);
    }

    public function checkUniquePostTitle(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $languageId = $request->input('language_id');
        $method = $request->input('method', 'add'); 
        $parentId = 0;

        $post = BlogPost::where(['id' => $id])->first();
        if ($post) {
            $parentId = $post->parent_id;
        }

        $titleValidator = Validator::make($request->only('title'), [
            'title' => [
                Rule::unique('blog_posts', 'title')
                    ->where(function ($query) use ($languageId, $method, $id, $parentId) {
                        if ($method == 'update') {
                            if ($parentId == 0) {
                                $query->where('language_id', $languageId)
                                    ->where('parent_id', '!=', $id)
                                    ->where('id', '!=', $id);
                            } else {
                                $query->where('language_id', '!=', $languageId)
                                      ->where('parent_id', $id)
                                      ->where('id', '!=', $id);
                            }
                                
                        }
                    }),
            ],
        ]);

        if ($titleValidator->fails()) {
            return response()->json(false);
        }
        return response()->json(true);
    }

    public function checkUniquePostSlug(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $languageId = $request->input('language_id');
        $method = $request->input('method', 'add');

        $parentId = 0;

        $post = BlogPost::where(['id' => $id])->first();
        if ($post) {
            $parentId = $post->parent_id;
        }

        if ($request->has('slug')) {
            $request->merge([
                'slug' => str_replace(' ', '-', trim($request->input('slug')))
            ]);
        }

        $slugValidator = Validator::make($request->only('slug'), [
            'slug' => [
                Rule::unique('blog_posts', 'slug')
                    ->where(function ($query) use ($languageId, $method, $id, $parentId) {
                        if ($method == 'update') {
                            if ($parentId == 0) {
                                $query->where('language_id', $languageId)
                                    ->where('parent_id', '!=', $id)
                                    ->where('id', '!=', $id);
                            } else {
                                $query->where('language_id', '!=', $languageId)
                                      ->where('parent_id', $id)
                                      ->where('id', '!=', $id);
                            }
                        }
                    })
            ],
        ]);

        if ($slugValidator->fails()) {
            return response()->json(false);
        }

        return response()->json(true);
    }

    public function blogList(Request $request): JsonResponse | View
    {
        $langCode = App::getLocale();
        $languageId = getLanguageId($langCode);

        if (request()->has('language_id') && !empty($request->language_id) && $request->language_id != $languageId) {
            $languageId = $request->language_id;
        }

        if (auth()->check()) {
            $languageId = auth()->user()->user_language_id;
        } elseif (Cookie::get('languageId')) {
            $languageId = Cookie::get('languageId');
        } else {
            $defaultLanguage = Language::select('id', 'code')->where('status', 1)->where('is_default', 1)->first();
            $languageId = $defaultLanguage ? $defaultLanguage->id : 1;
        }

        $perPage = $request->get('per_page', 6);
        $page = $request->get('page', 1);

        $blogs = BlogPost::select([
            'blog_posts.id',
            'blog_posts.title',
            'blog_posts.image',
            'blog_posts.slug',
            'blog_posts.category',
            'blog_posts.description',
            'blog_posts.popular',
            'blog_posts.status',
            'blog_posts.tags',
            'blog_posts.seo_title',
            'blog_posts.seo_description',
            'blog_posts.language_id',
            'blog_posts.created_at',
            'blog_posts.created_by',
            'user_details.first_name as author_name',
            'user_details.last_name as author_last_name',
            'user_details.profile_image as author_image',
            'blog_categories.name as category_name'
        ])
        ->leftJoin('user_details', 'blog_posts.created_by', '=', 'user_details.user_id')
        ->join('blog_categories', 'blog_posts.category', '=', 'blog_categories.id')
        ->where(['blog_posts.status' => 1, 'blog_posts.language_id' => $languageId])
        ->orderBy('blog_posts.id', 'DESC')
        ->paginate($perPage);

        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? 'Y-m-d';
    
        $blogsData = $blogs->getCollection()->map(function ($blog) use($dateFormat): array {
            return [
                'id' => $blog['id'],
                'title' => $blog['title'],
                'image' => url('storage/blogs/' . $blog['image']),
                'slug' => $blog['slug'],
                'category_name' => $blog['category_name'],
                'description' => $blog['description'],
                'popular' => $blog['popular'],
                'tags' => $blog['tags'],
                'seo_title' => $blog['seo_title'],
                'seo_description' => $blog['seo_description'],
                'language_id' => $blog['language_id'],
                'author_name' => $blog['author_name'] ? $blog['author_name'] . ' ' . $blog['author_last_name'] : 'Admin',
                'author_image' => $blog['author_image'] ? url('storage/profile/' . $blog['author_image']) : url('/assets/img/user-default.jpg'),
                'created_at' => Carbon::parse($blog['created_at'])->format($dateFormat),
            ];
        });

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {

            return response()->json([
                'code' => '200',
                'message' => __('Blog details retrieved successfully.'),
                'data' => $blogsData,
                'current_page' => $blogs->currentPage(),
                'per_page' => $blogs->perPage(),
                'total' => $blogs->total(),
                'last_page' => $blogs->lastPage(),
            ], 200);

        }
        session(['link' => url()->current()]);

        return view('blogs::blogs.blog_list', compact('blogs', 'blogsData'));

    }
    
    public function blogDetails(Request $request): JsonResponse | View
    {
        $slug = $request->slug;

        $blogDetail = BlogPost::select(
            'blog_posts.id',
            'blog_posts.title',
            'blog_posts.slug',
            'blog_posts.image',
            'blog_posts.description',
            'blog_posts.tags',
            'blog_posts.seo_title',
            'blog_posts.seo_description',
            'blog_posts.created_at',
            'user_details.first_name as author_name',
            'user_details.last_name as author_last_name',
            'user_details.profile_image as author_image',
            'blog_categories.name as category_name'
        )
        ->leftJoin('user_details', 'blog_posts.created_by', '=', 'user_details.user_id')
        ->join('blog_categories', 'blog_posts.category', '=', 'blog_categories.id')
        ->where(['blog_posts.slug' => $slug])
        ->first();

        $dateformatSetting = GlobalSetting::where('key', 'date_format_view')->first();
        $dateFormat = $dateformatSetting->value ?? 'Y-m-d';

        if (!empty($blogDetail)) {
            $blogDetail = $blogDetail->toArray();
            $blogDetail['image'] = url('storage/blogs/' . $blogDetail['image']);
            $blogDetail['author_name'] = $blogDetail['author_name'] ? $blogDetail['author_name'] . ' ' . $blogDetail['author_last_name'] : 'Admin';
            $blogDetail['author_image'] = $blogDetail['author_image'] ? url('storage/profile/' . $blogDetail['author_image']) : url('/assets/img/user-default.jpg');
            $blogDetail['created_at'] = Carbon::parse($blogDetail['created_at'])->format($dateFormat);
        }

        $comments = '';
        if (!empty($blogDetail['id'])) {
            $comments = BlogComment::where('post_id', $blogDetail['id'])->orderBy('id', 'DESC')->get()->map(function ($comment) {
                /** @var \Modules\Blogs\app\Models\BlogComment $comment */
                $comment->image = $comment->image ? url('storage/profile/' . $comment->image) : url('assets/img/profile-default.png');
                $comment->comment_date = Carbon::parse($comment->comment_date)->diffForHumans();
                
                return $comment;
            });
        }

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {

            return response()->json([
                'code' => '200',
                'message' => __('Blog details retrieved successfully.'),
                'data' => $blogDetail,
            ], 200);

        }
        session(['link' => url()->current()]);

        return view('blogs::blogs.blog_details', compact('blogDetail', 'comments'));

    }

    public function addComment(Request $request): JsonResponse
    {
        $userId = $request->user_id ?? '';
        
        if (request()->has('user_id') && !empty($request->user_id) ) {
            $validator = Validator::make($request->all(), [
                'comment' => 'required',
            ], [
                'comment.required' => __('Comment is required.'),
            ]);
        } 
        else {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email',
                'comment' => 'required',
            ], [
                'name.required' => __('Name is required.'),
                'email.required' => __('Email is required.'),
                'email.email' => __('Please enter a valid email.'),
                'comment.required' => __('Comment is required.'),
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'code' => 422,
                'errors' => $validator->messages()->toArray(),
            ], 422);
        }

        try {
            $data = [];

            if (request()->has('user_id') && !empty($request->user_id) ) {
                $user = User::select('email')->where('id', $userId)->first();
                $userDetail = UserDetail::select('first_name', 'profile_image')->where('user_id', $userId)->first();
                $data = [
                    'post_id' => $request->post_id,
                    'name' => $userDetail ? $userDetail->first_name : '',
                    'email' => $user ? $user->email : '',
                    'image' => $userDetail ? $userDetail->profile_image : '',
                    'comment' => $request->comment,
                    'comment_date' => Carbon::now(),
                ];
            } else {
                $data = [
                    'post_id' => $request->post_id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'comment' => $request->comment,
                    'comment_date' => Carbon::now(),
                ];
            }

            BlogComment::create($data);

            return response()->json([
                'code' => 200,
                'message' => __('Comment posted successfully.'),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('Error! while posting comment'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listComments(Request $request): JsonResponse
    {
        $postId = $request->post_id;

        try {

            $comments = BlogComment::where('post_id', $postId)->orderBy('id', 'DESC')->get()->map(function ($comment) {
                $comment->image = $comment->image ? url('storage/profile/' . $comment->image) : url('assets/img/profile-default.png');
                $comment->comment_date = Carbon::parse($comment->comment_date)->diffForHumans();
                
                return $comment;
            });

            return response()->json([
                'code' => 200,
                'message' => __('Comments retrieved successfully.'),
                'data' => $comments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('Error! while retrieving comments'),
                'error' => $e->getMessage()
            ], 500);
        }

    }
}