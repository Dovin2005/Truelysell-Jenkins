<?php

namespace Modules\Page\app\Repositories\Eloquent;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Modules\GlobalSetting\app\Models\Language;
use Modules\Page\app\Models\Footer;
use Illuminate\Support\Facades\Cache;
use Modules\Page\app\Models\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Support\Facades\Cookie;
use Modules\Blogs\app\Http\Controllers\BlogsController;
use Modules\MenuBuilder\app\Models\Menu;
use Modules\Page\app\Repositories\Contracts\PageRepositoryInterface;
use Illuminate\View\View;
use Modules\GlobalSetting\app\Models\Currency;
use Modules\Page\app\Models\Section;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;
use App\Http\Controllers\UserController;
use App\Models\PackageTrx;
use App\Models\ServicePromotion;
use App\Models\User;
use Illuminate\Support\Str;

class PageRepository implements PageRepositoryInterface
{
    public function index(Request $request): JsonResponse
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

            $data = Footer::select('id', 'footer_content', 'status')->where(['language_id' => $languageId])->first();

            if ($data && !empty($data->footer_content)) {
                $data->footer_content = json_decode($data->footer_content);
            }

            return response()->json([
                'code' => 200,
                'message' => __('Footer retrieved successfully.'),
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('Error! while retrieving footers'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $sections = $request->input('sections');
        $id = $request->id ?? '';


        try {

            $langCode = App::getLocale();
            $language = Language::where('code', $langCode)->first();
            $languageId = $language->id;

            if (request()->has('language_id') && !empty($request->language_id)) {
                $languageId = $request->language_id;
                $languageData = Language::where('id', $languageId)->first();
                $langCode = $languageData->code;
            }

            $formattedSections = [];
            if (!empty($sections)) {
                foreach ($sections as $section) {
                    $formattedSections[] = [
                        'title' => $section['title'],
                        'footer_content' => $section['footer_content'],
                        'status' => isset($section['status']) ? 1 : 0,
                    ];
                }
            }

            $jsonSections = json_encode($formattedSections);
            Cache::forget('footerList');

            Footer::updateOrCreate(
                ['id' => $id, 'language_id' => $languageId],
                [
                'footer_content' => $jsonSections,
                'status' => isset($request->status) ? 1 : 0,
                ]
            );

            return response()->json([
                'code' => 200,
                'message' => __('footer_save_success', [], $langCode),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('footer_save_error', [], $langCode),
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function getFooterDetails(): JsonResponse
    {
        try {
            $data = Footer::select('footer_content')->where('status', 1)->latest('id')->first();

            $filteredContent = [];

            if ($data && !empty($data->footer_content)) {
                $footerContent = json_decode($data->footer_content, true);

                if (is_array($footerContent)) {
                    $filteredContent = collect($footerContent)->filter(function ($item) {
                        return ($item['status'] ?? 0) == 1;
                    })->values()->toArray();
                }
            }

        return response()->json([
            'code' => 200,
            'message' => __('Footer retrieved successfully.'),
            'data' => $filteredContent
        ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('Error! while getting footers'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pageBuilderApi(Request $request): JsonResponse | view
    {
        $slug = trim($request->path());

        $userSlugExists = User::where('user_slug', $slug)->exists();

        if ($userSlugExists) {
            return app(UserController::class)->showProviderDetails($slug);
        }

        if ($slug === 'blogs') {
            // Directly call blogList() from BlogsController
            return app(BlogsController::class)->blogList($request);
        }
        session(['link' => url()->current()]);

        $languages = Language::select('id', 'code')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->get();

        $language_id = null;

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $language_id = $request->language_id;
        } else {
            if (auth()->check()) {
                $language_id = auth()->user()->user_language_id;
            } elseif (Cookie::get('languageId')) {
                $language_id = Cookie::get('languageId');
            } else {
                $defaultLanguage = Language::select('id', 'code')
                    ->where('is_default', 1)
                    ->whereNull('deleted_at')
                    ->first();
                $language_id = $defaultLanguage->id;
            }

            if ($language_id === null) {
                $language_id = 1;
            }
        }

        if (empty($slug) || $slug === '/') {
            $slug = 'home-page';
        }

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $page = Page::where('slug', $request->page_name)->first();
        }

        if (!request()->has('is_mobile')) {
            $page = Page::where('slug', $slug)->where('language_id', $language_id)->first();

            if (!$page) {
                $getPage = Page::select('id')->where('slug', $slug)->first();
                if (!$getPage) {
                    return view('page_error');
                }
                $page = Page::where('parent_id', $getPage->id)->where('language_id', $language_id)->first();
            }

            if (!$page) {
                return view('page_error');
            }

            if (!$page) {
                return view('page_error');
            }
        }

        $pageContentSections = json_decode($page->page_content, true) ?? [];

        if (empty($pageContentSections) || !collect((array)$pageContentSections)->contains(fn($section) => $section['status'] == 1)) {
            $pageContentSections = [];
        } else {

            foreach ($pageContentSections as &$section) {

                // Banner
                if ($section['status'] == 1) {
                    if (isset($section['section_content']) && strpos($section['section_content'], '[banner') !== false) {
                        preg_match('/limit=(\d+)\s+viewall=(yes|no)\s+order=(asc|desc)/', $section['section_content'], $matches);
                        $limit = $matches[1] ?? 10;
                        $viewAll = $matches[2] ?? 'no';
                        $order = $matches[3] ?? 'asc';

                        $banners = DB::table('sections')
                            ->select('id', 'datas')
                            ->where('name', 'Banner One')
                            ->get();

                        foreach ($banners as &$banner) {
                            $decodedData = json_decode($banner->datas, true);

                            $banner->title = $decodedData['title'] ?? null;
                            $banner->label = $decodedData['label'] ?? null;
                            $banner->show_search = $decodedData['show_search'] ?? null;
                            $banner->show_location = $decodedData['show_location'] ?? null;
                            $banner->popular_search = $decodedData['popular_search'] ?? null;
                            $banner->provider_count = $decodedData['provider_count'] ?? null;
                            $banner->services_count = $decodedData['services_count'] ?? null;
                            $banner->review_count = $decodedData['review_count'] ?? null;

                            $backgroundImagePath = 'storage/uploads/background_image_banner/' . $decodedData['background_image'];
                            $thumbnailImagePath = 'storage/uploads/thumbnail_image_banner/' . $decodedData['thumbnail_image'];

                            $banner->background_image = (isset($decodedData['background_image']) && file_exists(public_path($backgroundImagePath)))
                                ? asset($backgroundImagePath)
                                : null;

                            $banner->thumbnail_image = (isset($decodedData['thumbnail_image']) && file_exists(public_path($thumbnailImagePath)))
                                ? asset($thumbnailImagePath)
                                : null;


                            unset($banner->datas);
                        }

                        $section['section_type'] = 'banner';
                        $section['type'] = 'banner';
                        $section['design'] = 'banner_one';
                        $section['section_content'] = $banners;
                    }
                }

                // Category
                if ($section['status'] == 1) {
                    if (isset($section['section_content']) && strpos($section['section_content'], '[category') !== false) {
                        preg_match('/type=([a-zA-Z]+)\s+limit=(\d+)\s+viewall=(yes|no)/', $section['section_content'], $matches);
                        $type = $matches[1] ?? 'all';
                        $limit = $matches[2] ?? 10;
                        $viewAll = $matches[3] ?? 'no';

                        $query = DB::table('categories')->select('id', 'parent_id', 'name', 'image', 'icon', 'description', 'featured', 'slug')
                            ->where('parent_id', 0)
                            ->where('language_id', $language_id)
                            ->where('source_type', 'service')
                            ->where('status', 1)
                            ->whereNull('deleted_at');

                        if ($type === 'featured') {
                            $query->where('featured', 1);
                            $section['section_type'] = 'featured_category';
                            $section['type'] = 'category';
                            $section['design'] = 'category_two';
                        } else {
                            $section['section_type'] = 'category';
                            $section['type'] = 'category';
                            $section['design'] = 'category_one';
                        }

                        $categories = $query->limit((int) $limit)->get();

                        foreach ($categories as &$category) {
                            $category->image = asset('storage/' . $category->image);
                            $category->icon = asset('storage/' . $category->icon);

                            $category->product_count = DB::table('products')
                                ->where('source_category', $category->id)
                                ->whereNull('deleted_at')
                                ->where('status', 1)
                                ->where('verified_status', 1)
                                ->whereExists(function ($query) {
                                    $query->select(DB::raw(1))
                                        ->from('users')
                                        ->whereColumn('users.id', 'products.user_id')
                                        ->whereNull('users.deleted_at');
                                })
                                ->count();
                        }

                        $sectionTitle = $section['section_title'] ?? '';
                        $words = explode(' ', $sectionTitle);
                        $lastWord = array_pop($words);
                        $remainingTitle = implode(' ', $words);

                        $section['section_title_main'] = $remainingTitle;
                        $section['section_title_last'] = $lastWord;

                        $section['section_content'] = $categories;
                    }
                }

                // Service
                if ($section['status'] == 1) {
                    if (isset($section['section_content']) && strpos($section['section_content'], '[service') !== false) {
                        preg_match('/type=([a-zA-Z]+)\s+limit=(\d+)\s+viewall=(yes|no)/', $section['section_content'], $matches);
                        $type = $matches[1] ?? 'all';
                        $limit = $matches[2] ?? 10;
                        $viewAll = $matches[3] ?? 'no';

                        $query = DB::table('products')
                            ->select(
                                'products.id',
                                'products.source_category',
                                'categories.name as category_name',
                                'products.source_name',
                                'products.source_description',
                                'products.source_price',
                                'products.source_brand',
                                'products.source_stock',
                                'products.featured',
                                'products.slug',
                                'products.language_id',
                                DB::raw("GROUP_CONCAT(products_meta.source_values SEPARATOR ',') as product_images"),
                                DB::raw("(SELECT AVG(ratings.rating) FROM ratings WHERE ratings.product_id = products.id AND ratings.parent_id = 0) as average_rating"),
                                DB::raw("(SELECT COUNT(ratings.id) FROM ratings WHERE ratings.product_id = products.id AND ratings.parent_id = 0) as review_count"),
                                DB::raw("(SELECT COUNT(bookings.id) FROM bookings WHERE bookings.product_id = products.id) as booking_count") // Add booking count
                            )
                            ->join('products_meta', function ($join) {
                                $join->on('products.id', '=', 'products_meta.product_id')
                                    ->where('products_meta.source_key', '=', 'product_image')
                                    ->whereNull('products_meta.deleted_at');
                            })
                            ->join('categories', 'products.source_category', '=', 'categories.id') // Join with categories to get the name
                            ->leftJoin('bookings', 'products.id', '=', 'bookings.product_id') // Join with bookings table to count bookings
                            ->where('products.source_type', 'service')
                            ->whereNull('products.deleted_at')
                            ->where('products.language_id', $language_id)
                            ->where('products.status', 1)
                            ->where('products.verified_status', 1)
                            ->whereExists(function ($query) {
                                $query->select(DB::raw(1))
                                    ->from('users')
                                    ->whereColumn('users.id', 'products.user_id')
                                    ->whereNull('users.deleted_at');
                            })
                            ->groupBy(
                                'products.id',
                                'products.source_category',
                                'categories.name', // Include in group by
                                'products.source_name',
                                'products.source_description',
                                'products.source_price',
                                'products.source_brand',
                                'products.source_stock',
                                'products.featured',
                                'products.slug',
                                'products.language_id'
                            );

                        if ($type === 'featured') {
                            $query->where('products.featured', 1);
                            $section['section_type'] = 'featured_service';
                            $section['type'] = 'service';
                            $section['design'] = 'service_three';
                            $sectionTitle = $section['section_title'] ?? '';
                            $words = explode(' ', $sectionTitle);
                            $lastWord = array_pop($words);
                            $remainingTitle = implode(' ', $words);

                            $section['section_title_main'] = $remainingTitle;
                            $section['section_title_last'] = $lastWord;
                        } elseif ($type === 'popular') {
                            $query->havingRaw('booking_count >= 1');
                            $section['section_type'] = 'popular_service';
                            $section['type'] = 'service';
                            $section['design'] = 'service_two';
                            $sectionTitle = $section['section_title'] ?? '';
                            $words = explode(' ', $sectionTitle);
                            $lastWord = array_pop($words);
                            $remainingTitle = implode(' ', $words);

                            $section['section_title_main'] = $remainingTitle;
                            $section['section_title_last'] = $lastWord;
                        } else {
                            $sectionTitle = $section['section_title'] ?? '';
                            $words = explode(' ', $sectionTitle);
                            $lastWord = array_pop($words);
                            $remainingTitle = implode(' ', $words);

                            $section['section_title_main'] = $remainingTitle;
                            $section['section_title_last'] = $lastWord;
                            $section['section_type'] = 'service';
                            $section['type'] = 'service';
                            $section['design'] = 'service_one';
                        }

                        $query->limit((int) $limit);

                        $service = $query->get();
                                                $service->transform(function ($product) {

                            $promotion = $this->getProviderPromotionFlags(
                                $product->user_id ?? null,
                                $product->id
                            );

                            $product->featured        = $promotion['featured'] ?? 0;
                            $product->badge           = $promotion['badge'] ?? 0;
                            $product->promotedJobber  = $promotion['promotedJobber'] ?? 0;
                            $product->promotedService = $promotion['promotedService'] ?? 0;

                            return $product;
                        });

                        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
                            foreach ($service as &$item) {
                                $images = explode(',', $item->product_images);
                                $item->product_images = asset('storage/' . $images[0]); // Use only the first image
                                $item->favourite = rand(0, 1);
                                $item->average_rating = $item->average_rating ? round($item->average_rating, 2) : 0;
                                $item->review_count = $item->review_count ?: 0; // Set review count to 0 if null
                                $item->booking_count = $item->booking_count ?: 0; // Set booking count to 0 if null
                            }
                        } else {
                            foreach ($service as &$item) {
                                $images = explode(',', $item->product_images);
                                $item->product_images = array_map(fn($image) => asset('storage/' . $image), $images);
                                $item->favourite = rand(0, 1);
                                $item->average_rating = $item->average_rating ? round($item->average_rating, 2) : 0;
                                $item->review_count = $item->review_count ?: 0; // Set review count to 0 if null
                                $item->booking_count = $item->booking_count ?: 0; // Set booking count to 0 if null
                            }
                        }

                        $section['section_content'] = $service;
                    }
                }

                // Raeted Servies
                if ($section['status'] == 1) {
                    if (isset($section['section_content']) && strpos($section['section_content'], '[rated_service') !== false) {
                        preg_match('/limit=(\d+)\s+viewall=(yes|no)\s+order=(asc|desc)/', $section['section_content'], $matches);
                        $limit = $matches[1] ?? 10;
                        $viewAll = $matches[2] ?? 'no';
                        $order = $matches[3] ?? 'asc';

                        $query = DB::table('products')
                            ->select(
                                'products.id',
                                'products.source_category',
                                'categories.name as category_name', // Fetch the category name
                                'products.source_name',
                                'products.user_id',
                                'products.source_description',
                                'products.source_price',
                                'products.source_brand',
                                'products.source_stock',
                                'products.featured',
                                'products.slug',
                                'products.language_id',
                                DB::raw("GROUP_CONCAT(products_meta.source_values SEPARATOR ',') as product_images"),
                                DB::raw("(SELECT AVG(ratings.rating) FROM ratings WHERE ratings.product_id = products.id AND ratings.parent_id = 0) as average_rating")
                            )
                            ->join('products_meta', function ($join) {
                                $join->on('products.id', '=', 'products_meta.product_id')
                                    ->where('products_meta.source_key', '=', 'product_image')
                                    ->whereNull('products_meta.deleted_at');
                            })
                            ->join('categories', 'products.source_category', '=', 'categories.id') // Join with categories to get the name
                            ->where('products.source_type', 'service')
                            ->where('products.language_id', $language_id)
                            ->whereNull('products.deleted_at')
                            ->where('products.status', 1)
                            ->where('products.verified_status', 1)
                            ->whereExists(function ($query) {
                                $query->select(DB::raw(1))
                                    ->from('users')
                                    ->whereColumn('users.id', 'products.user_id')
                                    ->whereNull('users.deleted_at');
                            })
                            ->groupBy(
                                'products.id',
                                'products.source_category',
                                'categories.name', // Include in group by
                                'products.source_name',
                                'products.user_id',
                                'products.source_description',
                                'products.source_price',
                                'products.source_brand',
                                'products.source_stock',
                                'products.featured',
                                'products.slug',
                                'products.language_id'
                            )
                            ->havingRaw('average_rating >= 4.0') // Filter products with average rating >= 4.0
                            ->limit((int) $limit);

                        $rated_service = $query->get();

                        foreach ($rated_service as &$item) {
                            // Process product images
                            $images = explode(',', $item->product_images);
                            $item->product_images = array_map(fn($image) => asset('storage/' . $image), $images);

                            // Assign other properties
                            $item->favourite = rand(0, 1);
                            $item->average_rating = $item->average_rating ? round($item->average_rating, 2) : 0;
                        }


                        $sectionTitle = $section['section_title'] ?? '';
                        $words = explode(' ', $sectionTitle);
                        $lastWord = array_pop($words);
                        $remainingTitle = implode(' ', $words);

                        $section['section_title_main'] = $remainingTitle;
                        $section['section_title_last'] = $lastWord;
                        $section['section_type'] = 'rated_service';
                        $section['design'] = 'rated_service_one';
                        $section['section_content'] = $rated_service;
                    }
                }

                // Popular Provider
                if ($section['status'] == 1) {
                    if (isset($section['section_content']) && strpos($section['section_content'], '[popular_provider') !== false) {
                        preg_match('/limit=(\d+)\s+viewall=(yes|no)\s+order=(asc|desc)/', $section['section_content'], $matches);
                        $limit = $matches[1] ?? 8;
                        $viewAll = $matches[2] ?? 'no';
                        $order = $matches[3] ?? 'asc';


                        $request = new \Illuminate\Http\Request([
                            'type' => '2',
                            'listtype' => 'popular',
                        ]);

                        $getUserListResponse = app()->call('App\Http\Controllers\UserController@getuserlist', ['request' => $request]);

                        $responseData = json_decode($getUserListResponse->getContent(), true);

                        if ($responseData['code'] === 200) {
                            $itemCount = count($responseData['data']);

                            if ($itemCount > 0) {
                                $itemCount = $itemCount > 8 ? 8 : 4;

                                $popular_provider = collect((array) $responseData['data'])->take($limit);

                                if ($order === 'desc') {
                                    $popular_provider = $popular_provider->sortByDesc('created_at');
                                } else {
                                    $popular_provider = $popular_provider->sortBy('created_at');
                                }
                            } else {
                                $popular_provider = collect();
                            }
                        }

                        $section['section_type'] = 'popular_provider';
                        $section['type'] = 'provider';
                        $section['design'] = 'provider_one';
                        $section['section_content'] = $popular_provider;
                    }
                }

                // How It Works
                if ($section['status'] == 1) {
                    if (isset($section['section_content']) && strpos($section['section_content'], '[how_it_work') !== false) {
                        preg_match('/limit=(\d+)\s+viewall=(yes|no)\s+order=(asc|desc)/', $section['section_content'], $matches);
                        $limit = $matches[1] ?? 10;
                        $viewAll = $matches[2] ?? 'no';
                        $order = $matches[3] ?? 'asc';

                        $how_it_works = DB::table('general_settings')->select('id', 'key', 'value', 'group_id')
                            ->where(['group_id' => 14, 'language_id' => $language_id])
                            ->orderBy('created_at', $order)
                            ->limit((int) $limit)
                            ->get();


                        $sectionTitle = $section['section_title'] ?? '';
                        $words = explode(' ', $sectionTitle);
                        $lastWord = array_pop($words);
                        $remainingTitle = implode(' ', $words);

                        $section['section_title_main'] = $remainingTitle;
                        $section['section_title_last'] = $lastWord;
                        $section['section_type'] = 'how_it_works';
                        $section['design'] = 'how_it_works_one';
                        $section['section_content'] = $how_it_works;
                    }
                }

                // Advertisement
                if ($section['status'] == 1) {
                    if (isset($section['section_content']) && strpos($section['section_content'], '[advertisement') !== false) {
                        preg_match('/limit=(\d+)\s+viewall=(yes|no)\s+order=(asc|desc)/', $section['section_content'], $matches);
                        $limit = $matches[1] ?? 10;
                        $viewAll = $matches[2] ?? 'no';
                        $order = $matches[3] ?? 'asc';

                        $how_it_works = DB::table('general_settings')->select('id', 'key', 'value', 'group_id')
                            ->where(['group_id' => 14, 'language_id' => $language_id])
                            ->orderBy('created_at', $order)
                            ->limit((int) $limit)
                            ->get();


                        $sectionTitle = $section['section_title'] ?? '';
                        $words = explode(' ', $sectionTitle);
                        $lastWord = array_pop($words);
                        $remainingTitle = implode(' ', $words);

                        $section['section_title_main'] = $remainingTitle;
                        $section['section_title_last'] = $lastWord;
                        $section['section_type'] = 'advertisement';
                        $section['design'] = 'advertisement_one';
                        $section['section_content'] = $how_it_works;
                    }
                }

                // FAQ
                if ($section['status'] == 1) {
                    if (isset($section['section_content']) && strpos($section['section_content'], '[faq') !== false) {
                        preg_match('/limit=(\d+)\s+viewall=(yes|no)\s+order=(asc|desc)/', $section['section_content'], $matches);
                        $limit = $matches[1] ?? 10;
                        $viewAll = $matches[2] ?? 'no';
                        $order = $matches[3] ?? 'asc';

                        $faqs = DB::table('faqs')->select('id', 'question', 'answer', 'status')->where('status', 1)
                            ->whereNull('deleted_at')
                            ->where('language_id', $language_id)
                            ->orderBy('created_at', $order)
                            ->limit((int) $limit)
                            ->get();

                        $section['section_type'] = 'faq';
                        $section['design'] = 'faq_one';
                        $section['section_content'] = $faqs;
                    }
                }

                //Blog
                if ($section['status'] == 1) {
                    if (isset($section['section_content']) && strpos($section['section_content'], '[blog') !== false) {
                        preg_match('/type=([a-zA-Z]+)\s+limit=(\d+)\s+viewall=(yes|no)/', $section['section_content'], $matches);
                        $type = $matches[1] ?? 'all';
                        $limit = $matches[2] ?? 10;
                        $viewAll = $matches[3] ?? 'no';

                        $blogs = DB::table('blog_posts')->select('id', 'title', 'image', 'slug', 'category', 'description', 'updated_at')
                            ->when($type === 'all', function ($query) {
                                return $query;
                            })
                            ->limit((int) $limit)
                            ->where('status', 1)
                            ->where('language_id', $language_id)
                            ->whereNull('deleted_at')
                            ->get();

                        foreach ($blogs as &$blog) {
                            $blog->image = asset('storage/blogs/' . $blog->image);
                        }

                        $sectionTitle = $section['section_title'] ?? '';
                        $words = explode(' ', $sectionTitle);
                        $lastWord = array_pop($words);
                        $remainingTitle = implode(' ', $words);

                        $section['section_title_main'] = $remainingTitle;
                        $section['section_title_last'] = $lastWord;
                        $section['section_type'] = 'blog';
                        $section['design'] = 'blog_one';
                        $section['section_content'] = $blogs;
                    }
                }

                //Testimonial
                if ($section['status'] == 1) {
                    if (isset($section['section_content']) && strpos($section['section_content'], '[testimonial') !== false) {
                        preg_match('/limit=(\d+)\s+viewall=(yes|no)/', $section['section_content'], $matches);
                        $limit = $matches[1] ?? 10;
                        $viewAll = $matches[2] ?? 'no';

                        $testimonials = DB::table('testimonials')->select('id', 'client_name', 'client_image', 'position', 'description', 'order_by', 'status', 'updated_at')
                            ->limit((int) $limit)
                            ->where('status', 1)
                            ->whereNull('deleted_at')
                            ->get();

                        foreach ($testimonials as &$testimonial) {
                            $testimonial->client_image = asset('storage/testimonials/' . $testimonial->client_image);
                        }

                        $sectionTitle = $section['section_title'] ?? '';
                        $words = explode(' ', $sectionTitle);
                        $lastWord = array_pop($words);
                        $remainingTitle = implode(' ', $words);

                        $section['section_title_main'] = $remainingTitle;
                        $section['section_title_last'] = $lastWord;
                        $section['section_type'] = 'testimonial';
                        $section['design'] = 'testimonial_one';
                        $section['section_content'] = $testimonials;
                    }
                }

                // Become Provider
                if ($section['status'] == 1) {
                    if (isset($section['section_content']) && is_string($section['section_content']) && strpos($section['section_content'], '[become_provider') !== false) {
                        preg_match('/link=([^\s\]]+)/', $section['section_content'], $matches);
                        $link = $matches[1] ?? 'https://example.com';

                        $section['section_type'] = 'become_provider';
                        $section['type'] = 'provider';
                        $section['design'] = 'provider_two';
                        $section['section_content'] = [
                            'link' => $link,
                        ];
                    }
                }

                // Business With Us
                if ($section['status'] == 1) {
                    if (isset($section['section_content']) && is_string($section['section_content']) && strpos($section['section_content'], '[business_with_us') !== false) {
                        preg_match('/link=([^\s\]]+)/', $section['section_content'], $matches);
                        $link = $matches[1] ?? 'https://example.com';

                        $section['section_type'] = 'business_with_us';
                        $section['design'] = 'business_with_us_one';
                        $section['section_content'] = [
                            'link' => $link,
                        ];
                    }
                }

                if (isset($section['section_content']) && is_string($section['section_content'])) {
                    if (preg_match('/\[[^\]]+\]/', $section['section_content']) === 0) {
                        $section['section_content'] = $section['section_content'];
                        $section['section_type'] = 'multiple_section';
                    }
                }
            }
        }


        if (!empty($page->about_us)) {
            $pageContentSections[] = ['section_type' => 'about_us', 'about_us' => $page->about_us, 'status' => 1]; //about us
        }

        if (!empty($page->terms_conditions)) {
            $pageContentSections[] = ['section_type' => 'terms_conditions', 'terms_conditions' => $page->terms_conditions, 'status' => 1]; //Terms and Conditions
        }

        if (!empty($page->privacy_policy)) {
            $pageContentSections[] = ['section_type' => 'privacy_policy', 'privacy_policy' => $page->privacy_policy,  'status' => 1]; //Privacy Policy
        }

        if (!empty($page->contact_us)) {
            $contact = GlobalSetting::whereIn('key', ['phone_no', 'site_address', 'site_email'])->pluck('value');
            $content = '';
            if (!empty($contact)) {
                if (!empty($contact[0]) && !empty($contact[1]) && !empty($contact[2])) {
                    $content = str_replace(
                        ['{{company_phonenumber}}', '{{company_address}}', '{{company_email}}'],
                        [$contact[0], $contact[1], $contact[2]],
                        $page->contact_us
                    );
                }
            }
            $pageContentSections[] = ['section_type' => 'contact_us', 'contact_us' => $content, 'status' => 1]; //Contact us
        }
        $email = "";
        if (Auth::check()) {
            $email = Auth::user()->email;
        }

        $currency = DB::table('currencies')
            ->where('status', 1)
            ->where('is_default', 1)
            ->whereNull('deleted_at')
            ->value('symbol');

        $data = [
            'page_name' => $page->page_title,
            'currency' => $currency,
            'language_id' => $page->language_id,
            'content_sections' => $pageContentSections,
            'seo_tag' => $page->seo_tag,
            'seo_title' => $page->seo_title,
            'seo_description' => $page->seo_description,
            'email' => $email,
            'status' => $page->status,
        ];

        $content_sections = collect((array) $data['content_sections']);

        $maintenance = GlobalSetting::where('key', 'maintenance')->first();
        $maintenanceContent = GlobalSetting::where('key', 'maintenance_content')->first();

        if ($maintenance && $maintenance->value == '1') {
            return view('user.partials.maintenance', compact('maintenanceContent'));
        }
        $currency_details = Cache::remember('currecy_details', 86400, function () {
            return Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();
        });

        $authID = Auth::id();

        $user_details = DB::table('user_details')->where('user_id', $authID)->first();

        $city_name = '';
        $address = '';

        if ($user_details) {
            if (!empty($user_details->city)) {
                $city = DB::table('cities')->where('id', $user_details->city)->first();
                if ($city) {
                    $city_name = $city->name;
                }
            }

            if (!empty($user_details->address)) {
                $address = $user_details->address;
            }
        }

        if ($city_name || $address) {
            $location_text = trim($city_name . ' - ' . $address, ' -');
        } else {
            $location_text = '';
        }

        if ($slug === 'delete-my-account') {
            return view('delete-my-account', compact('data', 'content_sections', 'currency_details', 'location_text'));
        }

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            return response()->json(['code' => "200", 'message' => __('Page details retrieved successfully.'), 'data' => $data], 200);
        } else {
            return view('homepage', compact('data', 'content_sections', 'currency_details', 'location_text'));
        }
    }
private function getProviderPromotionFlags($providerId, $serviceId = null)
{
    $currentDate = now();
    $packageTrx = PackageTrx::join(
            'subscription_packages',
            'subscription_packages.id',
            '=',
            'package_transactions.package_id'
        )
        ->where('package_transactions.provider_id', $providerId)
        ->where('package_transactions.status', 1)
        ->where('package_transactions.payment_status', 2)
        ->where('subscription_packages.subscription_type', 'regular')
        ->whereDate('package_transactions.end_date', '>=', $currentDate)
        ->select(
            'subscription_packages.featured',
            'subscription_packages.badge',
            'subscription_packages.promoted_jobber',
            'subscription_packages.promoted_service'
        )
        ->orderByDesc('package_transactions.id')
        ->first();

    $topup = PackageTrx::join(
            'subscription_packages',
            'subscription_packages.id',
            '=',
            'package_transactions.package_id'
        )
        ->where('package_transactions.provider_id', $providerId)
        ->where('package_transactions.status', 1)
        ->where('package_transactions.payment_status', 2)
        ->where('subscription_packages.subscription_type', 'topup')
        ->whereDate('package_transactions.end_date', '>=', $currentDate)
        ->select(
            'subscription_packages.featured',
            'subscription_packages.badge',
            'subscription_packages.promoted_jobber',
            'subscription_packages.promoted_service'
        )
        ->orderByDesc('package_transactions.id')
        ->first();

    $source = $packageTrx ?? $topup;
    $promotedService = 0;

    if ($providerId && $serviceId) {
        $promotedService = ServicePromotion::where('provider_id', $providerId)
            ->where('service_id', $serviceId)
            ->where('payment_status', 'paid')
            ->where('expires_at', '>', now())
            ->exists() ? 1 : 0;
    }
    return [
        'featured'        => $source->featured ?? 0,
        'badge'           => $source->badge ?? 0,
        'promotedJobber'  => $source->promoted_jobber ?? 0,
        'promotedService' => $promotedService ?? 0,
    ];
}

    public function deletePage(Request $request): JsonResponse
    {
        $page = Page::find($request->id);

        if (!$page) {
            return response()->json(['success' => false, 'message' => 'Page not found']);
        }

        // Find the Menu entry associated with the Page's language_id
        $menu = Menu::where("language_id", $page->language_id)->first();

        if ($menu) {
            // Decode the JSON from the menus column
            $menus = json_decode($menu->menus, true);

            // Filter out the menu item where page_id matches the deleted page
            $menus = array_filter($menus, function ($menuItem) use ($page) {
                return $menuItem['page_id'] != $page->id;
            });

            // Re-index array to avoid gaps in keys
            $menus = array_values($menus);

            // Update the menu table with the new JSON data
            $menu->menus = json_encode($menus);
            $menu->save();
        }
        Cache::forget('menuList');

        // Delete the page after updating menus
        $page->delete();

        return response()->json(['success' => true, 'message' => 'Page deleted successfully']);
    }

    public function indexSection(Request $request): JsonResponse
    {
        $orderBy = $request->input('order_by', 'asc');
        $sortBy = $request->input('sort_by', 'id');

        $sections = Section::orderBy($sortBy, $orderBy)->where("status", 1)->get();

        $data = [];
        $baseUrl = asset('storage/uploads');

        foreach ($sections as $section) {
            $decodedDatas = json_decode($section->datas, true);

            if (isset($decodedDatas['background_image'])) {
                $decodedDatas['background_image'] = $baseUrl . '/background_image_banner/' . $decodedDatas['background_image'];
            }

            if (isset($decodedDatas['thumbnail_image'])) {
                $decodedDatas['thumbnail_image'] = $baseUrl . '/thumbnail_image_banner/' . $decodedDatas['thumbnail_image'];
            }

            $data[] = array_merge([
                'id' => $section->id,
                'name' => $section->name,
                'status' => $section->status,
            ], $decodedDatas);
        }


        return response()->json(['code' => '200', 'message' => __('Section details retrieved successfully.'), 'data' => $data], 200);
    }

    public function getPageDetails(Request $request): JsonResponse
    {
        $orderBy = $request->input('order_by', 'asc');
        $sortBy = $request->input('sort_by', 'id');

        $sections = Section::orderBy($sortBy, $orderBy)->get();

        $data = [];
        $baseUrl = asset('storage/uploads');

        foreach ($sections as $section) {
            $decodedDatas = json_decode($section->datas, true);

            if (isset($decodedDatas['background_image'])) {
                $decodedDatas['background_image'] = $baseUrl . '/background_image_banner/' . $decodedDatas['background_image'];
            }

            if (isset($decodedDatas['thumbnail_image'])) {
                $decodedDatas['thumbnail_image'] = $baseUrl . '/thumbnail_image_banner/' . $decodedDatas['thumbnail_image'];
            }

            $data[] = array_merge([
                'id' => $section->id,
                'name' => $section->name,
                'status' => $section->status,
            ], $decodedDatas);
        }

        $pages = Page::all()->map(function ($page) {
            $page->encrypted_id = encrypt($page->id);
            return $page;
        });


        return response()->json(['code' => '200', 'message' => __('Section details retrieved successfully.'), 'data' => $data, 'meta' => $pages], 200);
    }

    public function getDetails(Request $request): JsonResponse
    {
        $id = $request->id;            

        $page = '';

        if (request()->has('language_id') && !empty($request->language_id)) {

            $page = Page::where(['parent_id' => $id, 'language_id' => $request->language_id])->first();
            if (empty($page)) {
                $pageData = Page::select('parent_id')->where(['id' => $id])->first();
                $page = Page::where(['id' => $pageData->parent_id, 'language_id' => $request->language_id])->first();
            }
            if (empty($page)) {
                $page = Page::where(['id' => $id, 'language_id' => $request->language_id])->first();
            }
            
        }

        if ($page) {
            return response()->json([
                'code' => 200,
                'data' => $page,
            ]);
        } else {
            return response()->json([
                'code' => 200,
                'message' => [],
            ], 200);
        }
    }

    public function indexBuilderList(Request $request): JsonResponse
    {
        $orderBy = $request->input('order_by', 'asc');
        $sortBy = $request->input('sort_by', 'id');

        $language_id = Language::select('id', 'code')->where('code', $request->language_code)->first();

        $pages = Page::orderBy($sortBy, $orderBy)->where('language_id', $language_id->id)->get();

        $data = [];

        foreach ($pages as $page) {
            $data[] = [
                'id' => $page->id,
                'page_title' => $page->page_title,
                'slug' => $page->slug,
                'page_content' => $page->page_content,
                'status' => $page->status,
                'encrypted_id' => customEncrypt($page->id, Page::$pageSecretKey),
            ];
        }

        return response()->json(['code' => '200', 'message' => __('Page builder details retrieved successfully.'), 'data' => $data], 200);
    }

    public function storeSection(Request $request): JsonResponse
    {
        if ($request->section_id == 1) {
            $rules['title'] = 'required';
            $rules['label'] = 'required';
        } elseif ($request->section_id == 2) {
            $rules['category'] = 'required';
        } elseif ($request->section_id == 3) {
            $rules['feature_category'] = 'required';
        } elseif ($request->section_id == 4) {
            $rules['popular_category'] = 'required';
        } elseif ($request->section_id == 5) {
            $rules['service'] = 'required';
        } elseif ($request->section_id == 6) {
            $rules['feature_service'] = 'required';
        } elseif ($request->section_id == 7) {
            $rules['popular_service'] = 'required';
        } elseif ($request->section_id == 8) {
            $rules['product'] = 'required';
        } elseif ($request->section_id == 9) {
            $rules['feature_product'] = 'required';
        } elseif ($request->section_id == 10) {
            $rules['popular_product'] = 'required';
        } elseif ($request->section_id == 11) {
            $rules['faq'] = 'required';
        } elseif ($request->section_id == 12) {
            $rules['service_package'] = 'required';
        } elseif ($request->section_id == 13) {
            $rules['about_as'] = 'required';
        } elseif ($request->section_id == 14) {
            $rules['testimonial'] = 'required';
        } elseif ($request->section_id == 15) {
            $rules['how_it_work'] = 'required';
        } elseif ($request->section_id == 16) {
            $rules['blog'] = 'required';
        } else {
            return response()->json(['message' => 'Invalid section ID'], 400);
        }

        $messages = [
            'how_it_work.required' => __('The how it work field is required.'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $backgroundImagePath = null;
        $thumbnailImagePath = null;

        if ($request->hasFile('background_image')) {
            $backgroundImagePath = Helpers::upload('background_image_banner/', $request->background_image->extension(), $request->file('background_image'));
        }
        if ($request->hasFile('thumbnail_image')) {
            $thumbnailImagePath = Helpers::upload('thumbnail_image_banner/', $request->thumbnail_image->extension(), $request->file('thumbnail_image'));
        }

        $data = [];

        if ($request->section_id == 1) {
            $bannerSectionData = Section::where('id', $request->section_id)->first();
            $oldBackgroundImage = null;
            $oldThumbnailImage  = null;

            if ($bannerSectionData) {
                $data = json_decode($bannerSectionData->datas, true);

                $oldBackgroundImage = $data['background_image'] ?? null;
                $oldThumbnailImage  = $data['thumbnail_image'] ?? null;
            }

            $data = [
                'title' => $request->title,
                'label' => $request->label,
                'show_search' => $request->show_search,
                'show_location' => $request->show_location,
                'popular_search' => $request->popular_search,
                'provider_count' => $request->provider_count,
                'services_count' => $request->services_count,
                'review_count' => $request->review_count,
                'background_image' => $backgroundImagePath ?? $oldBackgroundImage,
                'thumbnail_image' => $thumbnailImagePath ?? $oldThumbnailImage,
            ];
        } elseif ($request->section_id == 2) {
            $data = [
                'category' => $request->category,
            ];
        } elseif ($request->section_id == 3) {
            $data = [
                'feature_category' => $request->feature_category,
            ];
        } elseif ($request->section_id == 4) {
            $data = [
                'popular_category' => $request->popular_category,
            ];
        } elseif ($request->section_id == 5) {
            $data = [
                'service' => $request->service,
            ];
        } elseif ($request->section_id == 6) {
            $data = [
                'feature_service' => $request->feature_service,
            ];
        } elseif ($request->section_id == 7) {
            $data = [
                'popular_service' => $request->popular_service,
            ];
        } elseif ($request->section_id == 8) {
            $data = [
                'product' => $request->product,
            ];
        } elseif ($request->section_id == 9) {
            $data = [
                'feature_product' => $request->feature_product,
            ];
        } elseif ($request->section_id == 10) {
            $data = [
                'popular_product' => $request->popular_product,
            ];
        } elseif ($request->section_id == 11) {
            $data = [
                'faq' => $request->faq,
            ];
        } elseif ($request->section_id == 12) {
            $data = [
                'service_package' => $request->service_package,
            ];
        } elseif ($request->section_id == 13) {
            $data = [
                'about_as' => $request->about_as,
            ];
        } elseif ($request->section_id == 14) {
            $data = [
                'testimonial' => $request->testimonial,
            ];
        } elseif ($request->section_id == 15) {
            $data = [
                'how_it_work' => $request->how_it_work,
            ];
        } elseif ($request->section_id == 16) {
            $data = [
                'blog' => $request->blog,
            ];
        }

        $id = $request->section_id;

        $section = Section::updateOrCreate(
            ['id' => $id],
            ['datas' => json_encode($data)]
        );

        if (!$section) {
            return response()->json(['message' => 'Something went wrong while saving the section!'], 500);
        }

        return response()->json(['code' => 200, 'message' => 'Section saved successfully!'], 200);
    }

    public function pageBuilderStore(Request $request): JsonResponse
    {
        $sections = [];
        $titles = $request->input('section_title');
        $labels = $request->input('section_label');
        $contents = $request->input('page_content');
        $statuses = $request->input('page_status', []);

        for ($i = 0; $i < count($titles); $i++) {
            $sections[] = [
                'section_title' => $titles[$i],
                'section_label' => $labels[$i],
                'section_content' => $contents[$i],
                'status' => isset($statuses[$i]) ? 1 : 0,
            ];
        }

        $slug = Str::slug($request->slug);
        $language_id = Language::select('id', 'code')->where('code', $request->currentLang)->first();

        $data = [
            'page_title' => $request->page_title,
            'slug' => $slug,
            'page_content' => json_encode($sections),
            'seo_tag' => $request->tag,
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
            'language_id' => $language_id->id,
            'status' => $request->status
        ];

        $save = Page::create($data);

        if (!$save) {
            return response()->json(['message' => __('Something went wrong while saving!')], 500);
        }

        return response()->json(['code' => 200, 'message' => __('page_create_success'), 'data' => []], 200);
    }

    public function pageBuilderUpdate(Request $request): JsonResponse
    {
        try {
            $id = $request->id ?? '';
            $languageId = $request->input('language_id');

            // Fixed duplicate assignment
            $langCode = Language::find($languageId)->code ?? 'en';

            $titles = $request->input('section_title', []);
            $labels = $request->input('section_label', []);
            $contents = $request->input('page_content', []);
            $statuses = $request->input('page_status', []);

            $sections = [];

            for ($i = 0; $i < count($titles); $i++) {
                $sections[] = [
                    'section_title' => $titles[$i] ?? '',
                    'section_label' => $labels[$i] ?? '',
                    'section_content' => $contents[$i] ?? '',
                    'status' => isset($statuses[$i]) ? (int)$statuses[$i] : 0,
                ];
            }

            $slug = Str::slug($request->slug);

            $data = [
                'page_title' => $request->page_title,
                'slug' => $slug,
                'page_content' => json_encode($sections),
                'about_us' => $request->about_us,
                'terms_conditions' => $request->terms_conditions,
                'privacy_policy' => $request->privacy_policy,
                'contact_us' => $request->contact_us,
                'seo_tag' => $request->tag,
                'seo_title' => $request->seo_title,
                'seo_description' => $request->seo_description,
                'status' => $request->status
            ];

            $data['language_id'] = $request->language_id;

            $existingPage = Page::where('id', $id)
                ->where('language_id', $request->language_id)
                ->first();

            $existingLangPage = Page::where('parent_id', $id)
                ->where('language_id', $request->language_id)
                ->first();

            $existingParentPage = '';
            $parentPage = Page::where('id', $id)->first();
            if ($parentPage) {
                $existingParentPage = Page::where(['id' => $parentPage->parent_id, 'language_id' => $request->language_id])->first();
            }

            if ($existingPage) {
                $pageSlugExists = Page::where('id', '!=', $existingPage->id)->where('slug', $slug);
                if ($pageSlugExists->exists()) {
                    return response()->json(['code' => 422, 'message' => __('slug_exists', [], $langCode), 'data' => []], 422);
                }
                Page::where('id', $id)
                    ->where('language_id', $request->language_id)
                    ->update($data);
            } elseif ($existingLangPage) {
                $pageSlugExists = Page::where('id', '!=', $existingLangPage->id)->where('slug', $slug);
                if ($pageSlugExists->exists()) {
                    return response()->json(['code' => 422, 'message' => __('slug_exists', [], $langCode), 'data' => []], 422);
                }
                Page::where('parent_id', $id)
                    ->where('language_id', $request->language_id)
                    ->update($data); 
            } elseif ($existingParentPage) {
                $pageSlugExists = Page::where('id', '!=', $existingParentPage->id)->where('slug', $slug);
                if ($pageSlugExists->exists()) {
                    return response()->json(['code' => 422, 'message' => __('slug_exists', [], $langCode), 'data' => []], 422);
                }
                Page::where('id', $parentPage->parent_id)
                    ->where('language_id', $request->language_id)
                    ->update($data); 
            } else {
                $pageSlugExists = Page::where('slug', $slug);
                if ($pageSlugExists->exists()) {
                    return response()->json(['code' => 422, 'message' => __('slug_exists', [], $langCode), 'data' => []], 422);
                }
                $data['parent_id'] = $id;
                Page::create($data);
            }
            return response()->json(['code' => 200, 'message' => __('page_update_success'), 'data' => []], 200);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => __('error_occurred_update_data', [], $langCode)], 500);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:sections,id',
        ]);

        $id = $request->input('id');

        $faq = Section::where('id',$id)->first();

        $faq->delete();

        return response()->json(['code' => '200', 'success' => true, 'message' => 'Section deleted successfully.'], 200);
    }
}