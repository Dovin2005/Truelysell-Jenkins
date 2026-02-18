<?php

namespace Modules\Service\app\Repositories\Eloquent;

use Modules\Service\app\Repositories\Contracts\ServiceRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Branches;
use App\Models\PackageTrx;
use App\Models\ServiceBranch;
use App\Models\ServicePromotion;
use App\Models\ServiceStaff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Service\app\Models\Productmeta;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Service\app\Models\AdditionalService as ModelsAdditionalService;
use Modules\Service\app\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Product\app\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Categories\app\Models\Categories;
use Modules\Communication\app\Http\Controllers\EmailController;
use Modules\GlobalSetting\app\Models\Language;
use Modules\Communication\app\Http\Controllers\NotificationController;
use Modules\GlobalSetting\app\Models\Templates;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\Product\app\Models\Category;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function setDefault(Request $request): array
    {
        $category = Service::where('id', $request->input('id'))->first();
        if ($category->status == 0) {
            $product_views = Service::find($request->input('id'));
            $product_views->status = 1;
            $product_views->save();
        }
        if ($category->status == 1) {
            $product_views = Service::find($request->input('id'));
            $product_views->status = 0;
            $product_views->save();
        }
        return [
            'code' => '200',
            'success' => true,
            'message' => 'Service Staus updated Sucesfully'
        ];
    }

    public function delete(Request $request): array
    {
        try {
            $category = Service::where('id', $request->input('id'))->first();
            $category->delete();

            return [
                'code' => 200,
                'success' => true,
                'message' => 'Category deleted successfully.',
                'data' => $category
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'success' => false,
                'message' => 'Failed to deleted Category status.',
                'error' => $e->getMessage()
            ];
        }
    }

    public function index(Request $request): array
    {
        try {
            $userId = $request->input('user_id');
            $query = Service::query()->where('source_type', '=', 'service');
            $query = DB::table('products')
                ->select('products.id', 'products.verified_status', 'products.user_id', 'products.slug', 'products.status', 'categories.name', 'products.source_code', 'products.source_name')
                ->join('categories', 'products.source_category', '=', 'categories.id')
                ->where('products.source_type', '=', 'service')
                ->whereNull('products.deleted_at')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('users')
                        ->whereColumn('users.id', 'products.user_id')
                        ->whereNull('users.deleted_at');
                })
                ->groupBy('products.id', 'products.verified_status', 'products.user_id', 'products.slug', 'categories.name', 'products.source_code', 'products.source_name', 'products.status'); // Added products.source_code to group by

            return [
                'code' => '200',
                'message' => 'Products retrieved successfully.',
                'data' => $query->get(),
            ];
        } catch (\Exception $e) {
            return [
                'code' => '500',
                'message' => 'An error occurred while retrieving products.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function store(Request $request): JsonResponse | RedirectResponse
    {
        DB::beginTransaction();

        try {
            $validator = $request->validate([
                'source_name' => 'required|unique:products|max:255',
                'source_code' => 'required',
                'category_fied' => 'required',
                'Subcategory_fied' => 'required',
                'source_desc' => 'required',
            ]);

            $slug_text = $request->source_name;
            $divider = "-";
            $slug_text = preg_replace('~[^\pL\d]+~u', $divider, $slug_text);

            // transliterate
            $slug_text = iconv('utf-8', 'us-ascii//TRANSLIT', $slug_text);

            // remove unwanted characters
            $slug_text = preg_replace('~[^-\w]+~', '', $slug_text);

            // trim
            $slug_text = trim($slug_text, $divider);

            // remove duplicate divider
            $slug_text = preg_replace('~-+~', $divider, $slug_text);

            // lowercase
            $slug_text = strtolower($slug_text);

            $userId = Auth::id();

            $data = [
                'source_name' => $request->source_name,
                'source_type' => 'service',
                'slug' => $slug_text,
                'user_id' => $userId,
                'source_category' => $request->category_fied,
                'source_subcategory' => $request->Subcategory_fied,
                'price_type' => $request->price_type,
                'source_price' => $request->fixed_price,
                'source_brand' => $request->source_brand,
                'source_stock' => $request->source_stock,
                'seo_title' => $request->seo_title,
                'seo_description' => $request->content,
                'include' => $request->include,
                'tags' => $request->tags,
                'source_description' => $request->source_desc,
                'source_code' => $request->source_code,
                'created_by' => $userId,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
            ];

            $currency = Service::create($data);

            if ($request->has('service_name')) {
                foreach ($request->service_name as $index => $name) {
                    $imagePath = null;

                    if ($request->hasFile("service_image.{$index}")) {
                        $image = $request->file("service_image.{$index}");
                        $imagePath = $image->store('additional_service_images', 'public');
                    }

                    ModelsAdditionalService::create([
                        'provider_id' => 0,
                        'service_id'  => $currency->id,
                        'name'        => $name,
                        'price'       => $request->service_price[$index],
                        'duration'    => $request->service_desc[$index],
                        'image'       => $imagePath,
                    ]);
                }
            }
            $message = 'Product created successfully.';
            $statusCode = 200;
            if ($request->hasFile('logo')) {

                foreach ($request->file('logo') as $photo) {
                    $logoPath = $photo->store('service_images', 'public');
                    $data = [
                        'product_id' => $currency->id,
                        'source_key' => 'product_image',
                        'source_Values' => $logoPath
                    ];
                    $product_meta = Productmeta::create($data);
                }
            }

            if ($request->service_name != "") {
                $service_name = serialize($request->service_name);
                $service_price = serialize($request->service_price);
                $service_desc = serialize($request->service_desc);

                $data = [
                    'product_id' => $currency->id,
                    'source_key' => "service_name",
                    'source_Values' => $service_name
                ];
                $product_meta = Productmeta::create($data);

                $data = [
                    'product_id' => $currency->id,
                    'source_key' => "service_price",
                    'source_Values' => $service_price
                ];
                $product_meta = Productmeta::create($data);
                $data = [
                    'product_id' => $currency->id,
                    'source_key' => "service_desc",
                    'source_Values' => $service_desc
                ];
                $product_meta = Productmeta::create($data);
            }
            if ($request->price_type != "") {

                $data = [
                    'product_id' => $currency->id,
                    'source_key' => $request->price_type,
                    'source_Values' => $request->fixed_price
                ];

                $product_meta = Productmeta::create($data);
            }
            DB::commit();
            return redirect('admin/services');

            return response()->json([
                'code' => $statusCode,
                'success' => true,
                'message' => $message,
                'data' => $currency
            ], $statusCode);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect('admin/addservice')->withErrors($e->getMessage())->withInput();
        }
    }

    public function update(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        $data = [
            'source_name' => $request->source_name,
            'source_type' => 'service',
            'source_category' => $request->category,
            'source_subcategory' => $request->Subcategory_fied,
            'price_type' => $request->price_type,
            'source_price' => $request->fixed_price,
            'source_brand' => $request->source_brand,
            'source_stock' => $request->source_stock,
            'seo_title' => $request->seo_title,
            'seo_description' => $request->content,
            'include' => $request->include,
            'tags' => $request->tags,
            'source_description' => $request->source_desc,
            'source_code' => $request->source_code,
            'updated_by' => $userId,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
        ];
        Service::where('id', $request->source_id)->update($data);
        if ($request->hasFile('logo')) {
            foreach ($request->file('logo') as $photo) {
                $logoPath = $photo->store('service_images', 'public');
                $data = [
                    'product_id' => $request->source_id,
                    'source_key' => 'product_image',
                    'source_Values' => $logoPath
                ];
                $product_meta = Productmeta::create($data);
            }
        }

        if ($request->has('service_name')) {
            foreach ($request->service_name as $index => $name) {
                $imagePath = null;

                if ($request->hasFile('service_image') && isset($request->file('service_image')[$index])) {
                    $image = $request->file('service_image')[$index];
                    $imagePath = $image->store('additional_service_images', 'public');
                }

                $existingService = ModelsAdditionalService::where('service_id', $request->source_id)
                    ->first();

                if ($existingService) {
                    $existingService->update([
                        'name'    => $request->service_name[$index],
                        'price'    => $request->service_price[$index],
                        'duration' => $request->service_desc[$index],
                        'image'    => $imagePath ?? $existingService->image,
                    ]);
                } else {
                    ModelsAdditionalService::create([
                        'provider_id' => 0,
                        'service_id'  => $request->source_id,
                        'name'        => $name,
                        'price'       => $request->service_price[$index],
                        'duration'    => $request->service_desc[$index],
                        'image'       => $imagePath,
                    ]);
                }
            }
        }

        $removedImages = str_replace('storage/', '', explode(',', $request->removed_images));
        if (!empty($removedImages)) {
            foreach ($removedImages as $removedImage) {
                if (!empty($removedImage)) {
                    if (Storage::exists($removedImage)) {
                        Storage::delete($removedImage);
                    }
                    Productmeta::where(['product_id' => $request->source_id, 'source_Values' => $removedImage])->delete();
                }
            }
        }
        return redirect('admin/services');
    }

    public function providerServiceIndex(Request $request): array
    {
        $orderBy = $request->input('order_by', 'desc');
        $sortBy  = $request->input('sort_by', 'id');
        $authId  = $request->input('auth_id');

        $userId = User::select('user_language_id')->where('id', $authId)->first();

        if ($request->has('is_mobile') && $request->get('is_mobile') === "yes") {
            $services = Service::where('user_id', $request->provider_id)
                ->where('language_id', $request->language_id)
                ->orderBy($sortBy, $orderBy)
                ->get();
        } else {
            $services = Service::where('user_id', $authId)
                ->where('language_id', $userId->user_language_id)
                ->orderBy($sortBy, $orderBy)
                ->get();
        }

        $activePromotions = ServicePromotion::where('payment_status', 'paid')
            ->where('expires_at', '>', Carbon::now())
            ->whereIn('service_id', $services->pluck('id'))
            ->pluck('service_id')
            ->toArray();

        $countries = json_decode(file_get_contents(public_path('countries.json')), true);
        $states    = json_decode(file_get_contents(public_path('states.json')), true);
        $cities    = json_decode(file_get_contents(public_path('cities.json')), true);

        $countryMap = collect($countries['countries'])->pluck('name', 'id')->all();
        $stateMap   = collect($states['states'])->pluck('name', 'id')->all();
        $cityMap    = collect($cities['cities'])->pluck('name', 'id')->all();

        $data = $services->map(function ($service) use (
            $countryMap,
            $stateMap,
            $cityMap,
            $request,
            $activePromotions
        ) {

            $baseUrl = url('/storage');

            $productMeta = ProductMeta::where('product_id', $service->id)
                ->where('source_key', 'product_image')
                ->pluck('source_Values')
                ->map(fn ($image) => $baseUrl . '/' . $image);

            $additionalServices = ModelsAdditionalService::where('service_id', $service->id)
                ->get(['name', 'price', 'duration', 'image']);

            $additionalServices->transform(function ($additional) use ($baseUrl) {
                if ($additional->image) {
                    $additional->image = $baseUrl . '/' . $additional->image;
                }
                return $additional;
            });

            $cityNames = collect(explode(',', $service->city))
                ->map(fn ($cityId) => $cityMap[$cityId] ?? $cityId)
                ->unique()
                ->implode(', ');

            $stateName   = $stateMap[$service->state] ?? $service->state;
            $countryName = $countryMap[$service->country] ?? $service->country;

            $catgoryName    = Category::select('name')->where('id', $service->source_category)->first();
            $subCatgoryName = Category::select('name')->where('id', $service->source_subcategory)->first();

            return [
                'id'                   => $service->id,
                'user_id'              => $service->user_id,
                'source_name'          => $service->source_name,
                'slug'                 => $service->slug,
                'source_code'          => $service->source_code,
                'source_type'          => $service->source_type,
                'source_tag'           => $service->source_tag,
                'source_description'   => $service->source_description,
                'source_category'      => $request->is_mobile === "yes"
                                            ? $catgoryName->name ?? null
                                            : $service->source_category,
                'source_subcategory'   => $request->is_mobile === "yes"
                                            ? $subCatgoryName->name ?? null
                                            : $service->source_subcategory,
                'source_price'         => $service->source_price,
                'plan'                 => $service->plan,
                'price_description'    => $service->price_description,
                'source_brand'         => $service->source_brand,
                'source_stock'         => $service->source_stock,
                'seo_title'            => $service->seo_title,
                'tags'                 => $service->tags,
                'featured'             => $service->featured,
                'popular'              => $service->popular,
                'seo_description'      => $service->seo_description,
                'price_type'           => $service->price_type,
                'duration'             => $service->duration,
                'country'              => $countryName,
                'state'                => $stateName,
                'city'                 => $cityNames,
                'address'              => $service->address,
                'pincode'              => $service->pincode,
                'include'              => $service->include,
                'status'               => $service->status,
                'created_by'           => $service->created_by,
                'product_image'        => $productMeta,
                'additional_services'  => $additionalServices,
                'verified_status'      => $service->verified_status,
                'service_promoted'     => in_array($service->id, $activePromotions),
            ];
        });

        return [
            'code'    => '200',
            'message' => __('Service details retrieved successfully.'),
            'data'    => $data
        ];
    }

    public function getDetails(Request $request, string $slug): array
    {
        $product = Service::where('slug', $slug)->where('language_id', $request->language_id)->first();

        if (!$product) {
            $product = Service::where('language_id', $request->language_id)
                ->where('parent_id', $request->service_id)
                ->orWhere('id', $request->parent_id)->first();
        }

        if ($product) {
            $productMeta = ProductMeta::where('product_id', $product->id)->get();

            $baseUrl = url('/storage');

            $productMeta->transform(function ($meta) use ($baseUrl) {
                if ($meta->source_key === 'product_image') {
                    $meta->source_Values = $baseUrl . '/' . $meta->source_Values;
                }
                return $meta;
            });

            $productMeta->transform(function ($meta) use ($baseUrl) {
                if ($meta->source_key === 'product_video') {
                    $meta->source_Values = $baseUrl . '/' . $meta->source_Values;
                }
                return $meta;
            });

            $additionalServices = ModelsAdditionalService::where('service_id', $product->id)->get();

            $additionalServices->transform(function ($additional) use ($baseUrl) {
                if ($additional->image) {
                    $additional->image = $baseUrl . '/' . $additional->image;
                }
                unset($additional->source_Values);
                return $additional;
            });

            $serviceBranches = ServiceBranch::where('service_id', $product->id)->get();

            $branchIds = $serviceBranches->pluck('branch_id');

            $serviceBranchDetails = Branches::whereIn('id', $branchIds)
                ->select('id', 'branch_name', 'branch_email', 'branch_mobile', 'branch_image', 'branch_address', 'branch_country', 'branch_state', 'branch_city', 'branch_zip')
                ->get();

            $serviceBranchDetails = $serviceBranchDetails->map(function ($branch) use ($serviceBranches) {
                $serviceBranch = $serviceBranches->firstWhere('branch_id', $branch->id);

                if ($serviceBranch) {
                    $staffIds = ServiceStaff::where('service_branch_id', $serviceBranch->id)->pluck('staff_id');

                    $staffDetails = User::whereIn('id', $staffIds)
                        ->select('id', 'name', 'phone_number')
                        ->get();

                    $branch->staff_details = $staffDetails;
                } else {
                    $branch->staff_details = [];
                }

                return $branch;
            });

            return [
                'code' => 200,
                'data' => [
                    'product' => $product,
                    'meta' => $productMeta,
                    'additional_services' => $additionalServices,
                    'service_branch' => $serviceBranchDetails,
                ]
            ];
        } else {
            return [
                'code' => 422,
                'message' => 'Product not found',
                'data' => [
                    'status' => true,
                ],
            ];
        }
    }

    public function providerServiceStore(Request $request): JsonResponse
    {
        $rules = [
            'service_name'    => 'required|string|max:255',
            'product_code'    => 'required|string|max:100',
            'category'        => 'required|integer',
            'sub_category'    => 'required|integer',
            'description'     => 'required|string|min:10',
            'seo_title'       => 'required|string|max:255',
            'seo_description' => 'required|string|max:500|min:20',
            'address'         => 'nullable|string|max:255|min:5',
            'pincode'         => 'nullable',
            'state'           => 'nullable|string|max:100',
            'city' => 'nullable|array', // Validate as an array
            'city.*' => 'string|max:100', // Validate each city as a string with a max length
            'country'         => 'nullable|string|max:100',
        ];

        $messages = [];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $slug = Str::slug($request->service_name);

        $slugCount = Service::where('slug', $slug)->count();

        if ($slugCount > 0) {
            $slug = $slug . '-' . ($slugCount + 1);
        }

        $cities = $request->has('city') && !empty($request->city) ? implode(',', $request->city) : null;

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $include = !empty($request->include) ? implode(',', $request->include) : null;
        } else {
            $include = $request->include;
        }

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $seoTag = !empty($request->seo_tag) ? implode(',', $request->seo_tag) : null;
        } else {
            $seoTag = $request->seo_tag;
        }

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $userId = $request->user_id;
        } else {
            $userId = Auth::id();
        }

        if ($request->free_price === 'on') {
            $priceType = 'free';
            $servicePrice = 0;
        } else {
            $priceType = $request->price_type;
            $servicePrice = $request->service_price;
        }

        $serviceApprovalStatus = serviceApprovalStatus();
        $verifiedStatus = $serviceApprovalStatus == 1 ? 0 : 1;

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $languageId = $request->language_id;
            $verifiedStatus = 1;
        } else {
            $languageId = $request->userLangId;
        }

        $data = [
            'user_id'            => $userId,
            'source_name'        => $request->service_name,
            'slug'               => $slug,
            'source_code'        => $request->product_code,
            'source_type'        => 'service',
            'source_category'    => $request->category,
            'source_subcategory' => $request->sub_category,
            'source_description' => $request->description,
            'seo_title'          => $request->seo_title,
            'tags'               => $seoTag,
            'seo_description'    => $request->seo_description,
            'price_type'         => $priceType,
            'source_price'       => $servicePrice,
            'duration'           => $priceType == 'hourly' ? 1 : ($request->duration_hours ?? $request->duration_minute),
            'price_description'  => $request->price_description,
            'featured'           => 1,
            'popular'            => 1,
            'country'            => $request->country,
            'state'              => $request->state,
            'city'               => $cities,
            'address'            => $request->address,
            'pincode'            => $request->pincode,
            'language_id'        => $languageId,
            'include'            => $include,
            'parent_id'          => 0,
            'created_by'         => $userId,
            'verified_status'    => $verifiedStatus,
        ];

        $save = Service::create($data);

        if (!request()->has('is_mobile')) {
            if ($request->has('branch_staff_payload')) {
                $branchStaffPayload = json_decode($request->input('branch_staff_payload'), true);

                foreach ($branchStaffPayload as $branchData) {
                    $branchId = $branchData['branch_id'];
                    $staffIds = $branchData['staff_ids'];

                    $serviceBranch = ServiceBranch::create([
                        'service_id' => $save->id,
                        'branch_id' => $branchId,
                    ]);

                    foreach ($staffIds as $staffId) {
                        ServiceStaff::create([
                            'service_branch_id' => $serviceBranch->id,
                            'staff_id' => $staffId,
                        ]);
                    }
                }
            }
        }

        if ($request->has('is_mobile') && $request->get('is_mobile') === "yes") {
            $branchStaffPayload = $request->input('branch_staff_payload');

            if (!empty($branchStaffPayload) && is_array($branchStaffPayload)) {
                foreach ($branchStaffPayload as $branchData) {
                    $branchId = $branchData['branch_id'];
                    $staffIds = $branchData['staff_ids'];

                    $serviceBranch = ServiceBranch::create([
                        'service_id' => $save->id,
                        'branch_id' => $branchId,
                    ]);

                    foreach ($staffIds as $staffId) {
                        ServiceStaff::create([
                            'service_branch_id' => $serviceBranch->id,
                            'staff_id' => $staffId,
                        ]);
                    }
                }
            }
        }

        if (!request()->has('is_mobile')) {
            if ($request->has('day_checkbox') && $request->has('start_time') && $request->has('end_time')) {
                $dayCheckbox = json_decode($request->day_checkbox, true);
                $startTimeData = json_decode($request->start_time, true);
                $endTimeData = json_decode($request->end_time, true);

                foreach ($dayCheckbox as $day) {
                    $dayKey = strtolower($day);

                    if (isset($startTimeData[$dayKey]) && isset($endTimeData[$dayKey])) {
                        $startTimes = $startTimeData[$dayKey];
                        $endTimes = $endTimeData[$dayKey];

                        foreach ($startTimes as $index => $startTime) {
                            if (isset($endTimes[$index])) {
                                $slotCounter = $index + 1;
                                $endTime = $endTimes[$index];

                                $metaData = [
                                    'product_id'    => $save->id,
                                    'source_key'    => $dayKey . '_slot_' . $slotCounter,
                                    'source_Values' => $startTime . ' - ' . $endTime,
                                ];

                                Productmeta::create($metaData);
                            }
                        }
                    }
                }
            }
        }

        if ($request->has('is_mobile') && $request->get('is_mobile') === "yes") {
            if ($request->has('day_checkbox') && $request->has('start_time') && $request->has('end_time')) {
                $dayCheckbox = $request->input('day_checkbox'); // Already an array
                $startTimeData = $request->input('start_time'); // Already an array
                $endTimeData = $request->input('end_time'); // Already an array

                foreach ($dayCheckbox as $day) {
                    $dayKey = strtolower($day);

                    if (isset($startTimeData[$dayKey]) && isset($endTimeData[$dayKey])) {
                        $startTimes = $startTimeData[$dayKey];
                        $endTimes = $endTimeData[$dayKey];

                        foreach ($startTimes as $index => $startTime) {
                            if (isset($endTimes[$index])) {
                                $slotCounter = $index + 1;
                                $endTime = $endTimes[$index];

                                $metaData = [
                                    'product_id'    => $save->id,
                                    'source_key'    => $dayKey . '_slot_' . $slotCounter,
                                    'source_Values' => $startTime . ' - ' . $endTime,
                                ];

                                Productmeta::create($metaData);
                            }
                        }
                    }
                }
            }
        }

        if (strtolower($request->basic) !== 'basic') {
            $metaprice = [
                'source_key'   => ucfirst($priceType),
                'source_Values' => $servicePrice,
                'product_id'   => $save->id,
            ];

            $saveData = Productmeta::create($metaprice);
        }

        $metaMapping = [
            'add_name'     => 'service_name',
            'add_price'    => 'service_price',
            'add_duration' => 'service_desc',
        ];

        foreach ($metaMapping as $requestKey => $metaKey) {
            if ($request->has($requestKey)) {
                $serializedValue = serialize($request->get($requestKey));

                Productmeta::create([
                    'product_id'    => $save->id,
                    'source_key'    => $metaKey,
                    'source_Values' => $serializedValue,
                ]);
            }
        }

        $plans = ['basic', 'premium', 'pro'];
        foreach ($plans as $plan) {
            $priceField = "{$plan}_service_price";
            $descriptionField = "{$plan}_price_description";

            if ($request->has($priceField) && $request->filled($priceField)) {
                Productmeta::create([
                    'product_id'    => $save->id,
                    'source_key'    => "{$plan}_service_price",
                    'source_Values' => $request->$priceField,
                ]);
            }

            if ($request->has($descriptionField) && $request->filled($descriptionField)) {
                Productmeta::create([
                    'product_id'    => $save->id,
                    'source_key'    => "{$plan}_price_description",
                    'source_Values' => $request->$descriptionField,
                ]);
            }
        }

        if ($request->has('add_name') && !empty($request->add_name)) {
            foreach ($request->add_name as $index => $name) {
                $imagePath = null;

                if ($request->hasFile("add_image.{$index}")) {
                    $image = $request->file("add_image.{$index}");

                    if ($image instanceof \Illuminate\Http\UploadedFile) {
                        $imagePath = $image->store('additional_service_images', 'public');
                    } elseif (is_array($image)) {
                        foreach ($image as $img) {
                            if ($img instanceof \Illuminate\Http\UploadedFile) {
                                $imagePath = $img->store('additional_service_images', 'public');
                            }
                        }
                    }
                }

                ModelsAdditionalService::create([
                    'provider_id' => 0,
                    'service_id'  => $save->id,
                    'name'        => $name,
                    'price'       => $request->add_price[$index],
                    'duration'    => $request->add_duration[$index],
                    'image'       => $imagePath,
                ]);
            }
        }

        if ($request->hasFile('client_image')) {
            $file = $request->file('client_image');
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('testimonials', $filename, 'public');
                $data['client_image'] = $filename;
            }
        }

        $upload_method = GlobalSetting::where('key', 'aws_status')->first();

        if ($request->hasFile('service_images') && $request->file('service_images')) {
            $images = $request->file('service_images');
            if (!$images instanceof \Illuminate\Http\UploadedFile) {
                foreach ($images as $image) {
                    if ($image instanceof \Illuminate\Http\UploadedFile) {
                        $imagePath = $image->store('product_images', 'public');
                        $imageData = [
                            'product_id' => $save->id,
                            'source_key' => 'product_image',
                            'source_Values' => $imagePath
                        ];

                        Productmeta::create($imageData);
                    }
                }
            }
        }

        if ($request->service_video) {
            $videoData = [
                'product_id' => $save->id,
                'source_key' => 'video_link',
                'source_Values' => $request->service_video, // Corrected here
            ];

            Productmeta::create($videoData);
        }
        $sourcenotify = "New Service";
        $notificationType = 24;
        $template = Templates::select('subject', 'content')
            ->where('type', 1)
            ->where('notification_type', $notificationType)
            ->first();
        $admins = User::where('user_type', 1)->get();
        $todescriptionContent = __('New service is created');
        $fromdescriptionadmin = __('New service is created');

        try {
            foreach ($admins as $admin) {
                $data = [
                    'communication_type' => '3',
                    'source' => $sourcenotify,
                    'reference_id' => $save->id,
                    'user_id' => $userId,
                    'to_user_id' => $admin->id,
                    'from_description' => $fromdescriptionadmin ?? null,
                    'to_description' => $todescriptionContent  ?? null,
                ];

                $notificationRequest = new Request($data);
                $notification = new NotificationController();
                $notification->Storenotification($notificationRequest);
            }
        } catch (\Exception $e) {
            Log::error('Error storing notifications: ' . $e->getMessage());
        }

        if (!$save) {
            return response()->json(['message' => 'Something went wrong while saving the service!'], 500);
        }

        $redirectUrl = route('provider.service');

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            return response()->json([
                'code' => 200,
                'message' => __('service_create_success'),
                'verify_status' => $verifiedStatus,
            ], 200);
        }

        return response()->json([
            'code'    => 200,
            'message' => __('service_create_success'),
            'redirect_url' => $redirectUrl,
            'service_approval_status' => $serviceApprovalStatus,
            'data'    => [],
        ], 200);
    }

    public function verifyService(Request $request): array
    {
        $languageCode = $request->language_code ?? app()->getLocale();
        try {

            Service::where('id', $request->id)
                ->update(['verified_status' => 1]);

            $service = Service::where('id', $request->id)->first();

            $data = User::with('userDetails')
                ->where('id', $service->user_id)
                ->first();
            $serviceName = $service->source_name ?? '';

            $this->sendServiceVerificationEmail($data, 33, $serviceName);

            return [
                'code' => 200,
                'message' => __('service_verification_status_update_success', [], $languageCode)
            ];

        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('service_verification_status_update_error', [], $languageCode),
                'error' => $e->getMessage()
            ];
        }
    }

    private function sendServiceVerificationEmail($data, $notificationType, $serviceName): void
    {
        $template = Templates::select('subject', 'content')
            ->where('type', 1)
            ->where('notification_type', $notificationType)
            ->first();

        if ($template) {
            $settings = GlobalSetting::whereIn('key', ['company_name', 'website', 'phone_no', 'site_email'])->pluck('value', 'key');
            $companyName = $settings['company_name'] ?? '';
            $companyWebsite = $settings['website'] ?? '';
            $companyPhone = $settings['phone_no'] ?? '';
            $companyEmail = $settings['site_email'] ?? '';
            $contact = $companyEmail . ' | ' . $companyPhone;
            $customerName = $data['userDetails']['first_name'] . ' ' . $data['userDetails']['last_name'];

            // Prepare email data
            $subject = str_replace(
                ['{{service_name}}', '{{provider_name}}', '{{user_name}}', '{{first_name}}', '{{last_name}}', '{{customer_name}}', '{{phone_number}}', '{{email_id}}', '{{company_name}}', '{{website_link}}', '{{contact}}'],
                [ $serviceName, $customerName, $customerName, $data['userDetails']['first_name'], $data['userDetails']['last_name'], $customerName, $data['phone_number'], $data['email'], $companyName, $companyWebsite, $contact],
                $template->subject
            );

            $content = str_replace(
                ['{{service_name}}', '{{provider_name}}', '{{user_name}}', '{{first_name}}', '{{last_name}}', '{{customer_name}}', '{{phone_number}}', '{{email_id}}', '{{company_name}}', '{{website_link}}', '{{contact}}'],
                [$serviceName, $customerName, $customerName, $data['userDetails']['first_name'], $data['userDetails']['last_name'], $customerName, $data['phone_number'], $data['email'], $companyName, $companyWebsite, $contact],
                $template->content
            );

            $emailData = [
                'to_email' => $data['email'],
                'subject' => $subject,
                'content' => $content
            ];

            try {
                $emailRequest = new Request($emailData);
                $emailController = new EmailController();
                $emailController->sendEmail($emailRequest);
            } catch (\Exception $e) {
                Log::error('Failed to send registration email: ' . $e->getMessage());
            }
        }
    }

    public function providerServiceUpdate(Request $request): JsonResponse
    {
         $rules = [
            'service_name'    => 'required|string|max:255',
            'product_code'    => 'required|string|max:100',
            'category'        => 'required',
            'sub_category'    => 'nullable',
            'description'     => 'required|string',
            'seo_title'       => 'required|string|max:255',
            'seo_description' => 'required|string|max:500',
            'price_type'      => 'required',
        ];

        $messages = [];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = $request->id;

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $userId = $request->user_id;
        } else {
            $userId = Auth::id();
        }

        if ($request->has('is_mobile') && $request->get('is_mobile') === "yes") {
            $includeArray = json_decode($request->include, true);
            $include = implode(',', $includeArray);
        } else {
            $include = $request->include;
        }

        if ($request->has('is_mobile') && $request->get('is_mobile') === "yes") {
            $SeoArray = json_decode($request->seo_tag, true);
            $seoTag = implode(',', $SeoArray);
        } else {
            $seoTag = $request->seo_tag;
        }

        $slug = Str::slug($request->service_name);
        $slugCount = Service::where('slug', $slug)->count();

        $data = [
            'user_id'            => $userId,
            'source_name'        => $request->service_name,
            'slug'               => $slug,
            'source_code'        => $request->product_code,
            'source_type'        => 'service',
            'source_category'    => $request->category,
            'source_subcategory' => $request->sub_category,
            'source_description' => $request->description,
            'seo_title'          => $request->seo_title,
            'tags'               => $seoTag,
            'seo_description'    => $request->seo_description,
            'price_type'         => $request->price_type,
            'source_price'       => $request->service_price,
            'price_description'  => $request->price_description,
            'duration'           => $request->price_type == 'hourly' ? 1 : ($request->duration_hours ?? $request->duration_minute),
            'featured'           => 1,
            'popular'            => 1,
            'country'            => $request->country,
            'state'              => $request->state,
            'city'               => $request->city ? implode(',', $request->city) : null,
            'address'            => $request->address,
            'pincode'            => $request->pincode,
            'include'            => $include,
            'language_id'        => $request->language_id,
            'created_by'         => $userId,
        ];

        $existingService = Service::where('id', $product)
            ->where('language_id', $request->language_id)
            ->first();

        $existingLangService = Service::where('parent_id', $product)
            ->where('language_id', $request->language_id)
            ->first();

        if ($existingService) {
            if ($slugCount > 0 && $slug != $existingService->slug) {
                $data['slug'] = $slug . '-' . ($slugCount + 1);
            }
            $update = Service::where('id', $product)
                ->where('language_id', $request->language_id)
                ->update($data);

            $updatedService = Service::find($product);
        } else if ($existingLangService) {
            if ($slugCount > 0 && $slug != $existingLangService->slug) {
                $data['slug'] = $slug . '-' . ($slugCount + 1);
            }
            $update = Service::where('parent_id', $product)
                ->where('language_id', $request->language_id)
                ->update($data);

            $updatedService = Service::where('parent_id', $product)
                ->where('language_id', $request->language_id)
                ->first();
        } else {
            if ($slugCount > 0) {
                $data['slug'] = $slug . '-' . ($slugCount + 1);
            }
            $data['parent_id'] = $request->serviceId ?? 0;
            $updatedService = Service::create($data);
        }

        if ($updatedService) {
            $metaData = [
                'source_key'    => ucfirst($request->price_type),
                'source_Values' => $request->service_price,
                'product_id'    => $updatedService->id,
            ];

            Productmeta::updateOrCreate(
                ['product_id' => $updatedService->id],
                $metaData
            );
        }

        if ($request->has('branch_staff_payload')) {
            $branchStaffPayload = json_decode($request->input('branch_staff_payload'), true);

            $existingServiceBranches = ServiceBranch::where('service_id', $updatedService->id)->get();
            $existingBranchIds = $existingServiceBranches->pluck('branch_id')->toArray();

            foreach ($branchStaffPayload as $branchData) {
                $branchId = $branchData['branch_id'];
                $staffIds = $branchData['staff_ids'];

                $serviceBranch = $existingServiceBranches->firstWhere('branch_id', $branchId);

                if ($serviceBranch) {
                    $existingStaffIds = ServiceStaff::where('service_branch_id', $serviceBranch->id)->pluck('staff_id')->toArray();

                    foreach ($staffIds as $staffId) {
                        if (!in_array($staffId, $existingStaffIds)) {
                            ServiceStaff::create([
                                'service_branch_id' => $serviceBranch->id,
                                'staff_id' => $staffId,
                            ]);
                        }
                    }

                    ServiceStaff::where('service_branch_id', $serviceBranch->id)
                        ->whereNotIn('staff_id', $staffIds)
                        ->delete();
                } else {
                    $serviceBranch = ServiceBranch::create([
                        'service_id' => $updatedService->id,
                        'branch_id' => $branchId,
                    ]);

                    foreach ($staffIds as $staffId) {
                        ServiceStaff::create([
                            'service_branch_id' => $serviceBranch->id,
                            'staff_id' => $staffId,
                        ]);
                    }
                }
            }

            $newBranchIds = array_column($branchStaffPayload, 'branch_id');
            ServiceBranch::where('service_id', $updatedService->id)
                ->whereNotIn('branch_id', $newBranchIds)
                ->each(function ($serviceBranch) {
                    ServiceStaff::where('service_branch_id', $serviceBranch->id)->delete();
                    $serviceBranch->delete();
                });
        }

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            if ($request->has('day_checkbox') && $request->has('start_time') && $request->has('end_time')) {
                // Decode the input JSON fields
                $dayCheckbox = json_decode($request->day_checkbox, true);
                $startTimeData = json_decode($request->start_time, true);
                $endTimeData = json_decode($request->end_time, true);

                // Remove all existing meta data for the product
                Productmeta::where('product_id', $updatedService->id)->delete();

                // Insert updated meta data
                foreach ($dayCheckbox as $day) {
                    $dayKey = strtolower($day);

                    // Ensure start_time and end_time data exist for the day
                    if (isset($startTimeData[$dayKey]) && isset($endTimeData[$dayKey])) {
                        $startTimes = $startTimeData[$dayKey];
                        $endTimes = $endTimeData[$dayKey];

                        foreach ($startTimes as $index => $startTime) {
                            if (isset($endTimes[$index])) {
                                $slotCounter = $index + 1;
                                $endTime = $endTimes[$index];

                                $metaData = [
                                    'product_id'    => $updatedService->id,
                                    'source_key'    => $dayKey . '_slot_' . $slotCounter,
                                    'source_Values' => $startTime . ' - ' . $endTime,
                                ];

                                Productmeta::create($metaData);
                            }
                        }
                    }
                }
            }
        }

        $metaMapping = [
            'add_name'     => 'service_name',
            'add_price'    => 'service_price',
            'add_duration' => 'service_desc',
        ];


        foreach ($metaMapping as $requestKey => $metaKey) {
            if ($request->has($requestKey)) {
                $serializedValue = serialize($request->$requestKey);
                Productmeta::updateOrCreate(
                    [
                        'product_id' => $updatedService->id,
                        'source_key' => $metaKey,
                    ],
                    [
                        'source_Values' => $serializedValue,
                    ]
                );
            }
        }

        $plans = ['basic', 'premium', 'pro'];

        foreach ($plans as $plan) {
            $priceField = "{$plan}_service_price";
            $descriptionField = "{$plan}_price_description";

            if ($request->has($priceField) && $request->filled($priceField)) {
                Productmeta::updateOrCreate(
                    [
                        'product_id' => $updatedService->id,
                        'source_key' => "{$plan}_service_price",
                    ],
                    [
                        'source_Values' => $request->$priceField,
                    ]
                );
            }

            if ($request->has($descriptionField) && $request->filled($descriptionField)) {
                Productmeta::updateOrCreate(
                    [
                        'product_id' => $updatedService->id,
                        'source_key' => "{$plan}_price_description",
                    ],
                    [
                        'source_Values' => $request->$descriptionField,
                    ]
                );
            }
        }

        if ($request->has('services')) {
            // Initialize arrays for storing field values
            $serviceNames = [];
            $servicePrices = [];
            $serviceDescs = [];

            // Loop through the services to extract and populate values
            foreach ($request->input('services') as $service) {
                $serviceNames[] = $service['name'];
                $servicePrices[] = $service['price'];
                $serviceDescs[] = $service['duration'];
            }

            // Serialize the data for each key
            $serializedServiceNames = serialize($serviceNames);
            $serializedServicePrices = serialize($servicePrices);
            $serializedServiceDescs = serialize($serviceDescs);

            // Define fields and their serialized values
            $fields = [
                'service_name' => $serializedServiceNames,
                'service_price' => $serializedServicePrices,
                'service_desc' => $serializedServiceDescs,
            ];

            // Loop through fields and update or create Productmeta records
            foreach ($fields as $sourceKey => $serializedValue) {
                Productmeta::updateOrCreate(
                    [
                        'product_id' => $updatedService->id,
                        'source_key' => $sourceKey,
                    ],
                    [
                        'source_Values' => $serializedValue,
                    ]
                );
            }
        }

        if ($request->has('branch_select')) {
            $currentBranchIds = ServiceBranch::where('service_id', $updatedService->id)
                ->pluck('branch_id')
                ->toArray();

            $newBranchIds = $request->branch_select;

            ServiceBranch::where('service_id', $updatedService->id)
                ->whereNotIn('branch_id', $newBranchIds)
                ->delete();

            foreach ($newBranchIds as $branchId) {
                ServiceBranch::updateOrCreate([
                    'service_id' => $updatedService->id,
                    'branch_id' => $branchId,
                ]);
            }
        } else {
            ServiceBranch::where('service_id', $updatedService->id)->delete();
        }

        if ($request->has('services')) {
            foreach ($request->input('services') as $service) {
                $name = $service['name'];
                $price = $service['price'];
                $duration = $service['duration'];

                // Find the existing service based on `name` or any other unique identifier
                $existingService = ModelsAdditionalService::where('service_id', $updatedService->id)
                    ->where('name', $name) // Match by name or any unique field
                    ->first();

                if ($existingService) {
                    $existingService->update([
                        'name'    => $name,
                        'price'   => $price,
                        'duration' => $duration,
                    ]);
                } else {
                    ModelsAdditionalService::create([
                        'provider_id' => 0,
                        'service_id'  => $updatedService->id,
                        'name'        => $name,
                        'price'       => $price,
                        'duration'    => $duration,
                    ]);
                }
            }
        }


        if (!request()->has('is_mobile')) {
            if ($request->has('day_checkbox')) {
                $days = $request->day_checkbox;

                foreach ($days as $day) {
                    $dayKey = strtolower($day);
                    $startTimes = $request->input("start_time.$dayKey");
                    $endTimes = $request->input("end_time.$dayKey");

                    if ($startTimes && $endTimes) {
                        foreach ($startTimes as $index => $startTime) {
                            if (isset($endTimes[$index])) {
                                $sourceValues = $startTime . ' - ' . $endTimes[$index];
                                $sourceKey = $dayKey . '_slot_' . ($index + 1); // Append the index to create unique keys

                                $existingMeta = Productmeta::where('product_id', $updatedService->id)
                                    ->where('source_key', $sourceKey)
                                    ->first();

                                if ($existingMeta) {
                                    $existingMeta->update([
                                        'source_Values' => $sourceValues,
                                    ]);
                                } else {
                                    Productmeta::create([
                                        'product_id' => $updatedService->id,
                                        'source_key' => $sourceKey,
                                        'source_Values' => $sourceValues,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($request->has('add_name')) {
            foreach ($request->add_name as $index => $name) {
                $imagePath = null;

                // Check if the 'add_image' has a file for this index
                if ($request->hasFile('add_image') && isset($request->file('add_image')[$index])) {
                    $image = $request->file('add_image')[$index];

                    // If it's a single file, process it
                    if ($image instanceof \Illuminate\Http\UploadedFile) {
                        $imagePath = $image->store('additional_service_images', 'public');
                    }
                }

                // Check if the service already exists
                $existingService = ModelsAdditionalService::where('service_id', $updatedService->id)
                    ->where('name', $name)
                    ->first();

                // If the service exists, update it, otherwise create a new one
                if ($existingService) {
                    $existingService->update([
                        'price'    => $request->add_price[$index],
                        'duration' => $request->add_duration[$index],
                        'image'    => $imagePath ?? $existingService->image, // Preserve existing image if no new image is provided
                    ]);
                } else {
                    ModelsAdditionalService::create([
                        'provider_id' => 0,
                        'service_id'  => $updatedService->id,
                        'name'        => $name,
                        'price'       => $request->add_price[$index],
                        'duration'    => $request->add_duration[$index],
                        'image'       => $imagePath, // Set the image path if a new image is uploaded
                    ]);
                }
            }
        }


        if ($request->hasFile('service_images') && $request->file('service_images')) {
            Productmeta::where('product_id', $updatedService->id)->where('source_key', 'service_image')->delete();
            $images = $request->file('service_images');

            if (!$images instanceof \Illuminate\Http\UploadedFile) {
                foreach ($images as $image) {
                    if ($image instanceof \Illuminate\Http\UploadedFile) {
                        $imagePath = $image->store('product_images', 'public');

                        $imageData = [
                            'product_id'    => $updatedService->id,
                            'source_key'    => 'product_image',
                            'source_Values' => $imagePath,
                        ];

                        Productmeta::create($imageData);
                    }
                }
            } else {
                $imagePath = $images->store('product_images', 'public');

                $imageData = [
                    'product_id'    => $updatedService->id,
                    'source_key'    => 'product_image',
                    'source_Values' => $imagePath,
                ];

                Productmeta::create($imageData);
            }
        }

        if ($request->service_video) {
            $videoData = [
                'product_id' => $request->id,
                'source_key' => 'video_link',
            ];

            Productmeta::updateOrCreate(
                ['product_id' => $request->id, 'source_key' => 'video_link'],
                ['source_Values' => $request->service_video]
            );
        }

        $redirectUrl = route('provider.service');

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            return response()->json([
                'code'    => 200,
                'message' => __('service_update_success'),
                'data'    => [],
            ], 200);
        } else {
            return response()->json([
                'code'    => 200,
                'message' => __('service_update_success'),
                'redirect_url' => $redirectUrl,
                'data'    => [],
            ], 200);
        }
    }

    public function deleteServiceImage(string $id): array
    {
        try {
            $productMeta = Productmeta::where('id', $id)->first();

            if (!$productMeta) {
                return [
                    'code' => 200,
                    'message' => 'Image not found.',
                ];
            }

            $productMeta->delete();

            return [
                'code' => 200,
                'message' => 'Image deleted successfully.',
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => 'An error occurred while deleting the image.',
            ];
        }
    }

    public function deleteSlot(string $id): array
    {
        try {
            $slot = Productmeta::where('id', $id)->first();

            if (!$slot) {
                return [
                    'code' => 200,
                    'message' => 'Time slot not found.',
                ];
            }

            $slot->delete();

            return [
                'code' => 200,
                'message' => 'Time slot deleted successfully.',
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => 'An error occurred while deleting the time slot.',
            ];
        }
    }

    public function deleteAdditionalServices(string $id): array
    {
        try {
            $service = ModelsAdditionalService::where('id', $id)->first();

            if (!$service) {
                return [
                    'code' => 200,
                    'message' => 'Time slot not found.',
                ];
            }

            $service->delete();

            return [
                'code' => 200,
                'message' => 'Time slot deleted successfully.',
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => 'An error occurred while deleting the time slot.',
            ];
        }
    }

    public function deleteServices(Request $request): array
    {
        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            $id = $request->service_id;
        } else {
            $id = $request->input('id');
        }

        $delete = Service::where('id', $id)->first();

        $delete->deleted_at = Carbon::now();
        $delete->save();

        Productmeta::where('product_id', $id)->delete();

        ModelsAdditionalService::where('service_id', $id)->delete();

        return [
            'code' => '200',
            'success' => true,
            'message' => __('service_delete_success'),
        ];
    }

    public function status(Request $request): array
    {
        $request->validate([
            'id' => 'required',
        ]);

        $id = $request->input('id');

        $service = Service::select('status')->where('id', $id)->first();

        if ($service) {
            $newStatus = $service->status == 0 ? 1 : 0;

            DB::table('products')
                ->where('id', $id)
                ->update(['status' => $newStatus]);


            return [
                'code' => '200',
                'success' => true,
                'message' => __('Status updated successfully.'),
            ];
        }

        return [
            'code' => '200',
            'success' => false,
            'message' => 'Service not found.',
        ];
    }

    public function checkUnique(Request $request): bool
    {
        $serviceName = $request->input('service_name');
        $languageId = $request->input('language_id');

        $exists = DB::table('products')
            ->where('source_name', $serviceName)
            ->whereNull('deleted_at')
            ->when($languageId, function ($query, $languageId) {
                return $query->where('language_id', $languageId);
            })
            ->exists();

        return !$exists;
    }

    public function checkEditUnique(Request $request): bool
    {
        $serviceName = $request->input('edit_service_name');
        $id = $request->input('id');
        $languageId = $request->input('language_id');

        $exists = DB::table('products')
            ->where('source_name', $serviceName)
            ->whereNull('deleted_at')
            ->when($languageId, function ($query, $languageId) {
                return $query->where('language_id', $languageId);
            })
            ->when($id, function ($query, $id) {
                // Exclude the current record with the given ID
                return $query->where('id', '!=', $id);
            })
            ->exists();

        return !$exists;
    }

    public function providerSub(Request $request): array
    {
        $id = Auth::id();

        $packageTrx = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('package_transactions.provider_id', $id)
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('subscription_packages.subscription_type', 'regular')
            ->select(
                'package_transactions.id',
                'package_transactions.provider_id',
                'subscription_packages.number_of_service',
                'package_transactions.package_id',
                'package_transactions.trx_date',
                'package_transactions.end_date'
            )
            ->orderByDesc('package_transactions.id')
            ->first();

        $topup = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('package_transactions.provider_id', $id)
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('subscription_packages.subscription_type', 'topup')
            ->select(
                'package_transactions.id',
                'package_transactions.provider_id',
                'subscription_packages.number_of_service',
                'package_transactions.package_id',
                'package_transactions.trx_date',
                'package_transactions.end_date'
            )
            ->orderByDesc('package_transactions.id')
            ->first();

        if (!$packageTrx && !$topup) {
            return [
                'code' => 200,
                'success' => false,
                'no_package' => true,
                'message' => 'No Subscription and Topup Found.'
            ];
        }

        $serviceCount = Product::where('user_id', $id)->count();
        $currentDate = now();

        $packageEndDateCount = $packageTrx ? $currentDate->diffInDays(Carbon::parse($packageTrx->end_date), false) : -999;
        $topupEndDateCount   = $topup ? $currentDate->diffInDays(Carbon::parse($topup->end_date), false) : -999;

        $number_of_service = 0;

        if ($packageEndDateCount > -1) {
            $number_of_service += $packageTrx->number_of_service;
        }

        if ($topupEndDateCount > -1) {
            $number_of_service += $topup->number_of_service;
        }

        if ($packageEndDateCount <= -1 && $topupEndDateCount <= -1) {
            return [
                'code' => 200,
                'success' => true,
                'sub_end' => true,
                'message' => 'Subscription or Topup Ended.',
            ];
        }

        if ($serviceCount >= $number_of_service) {
            return [
                'code' => 200,
                'success' => true,
                'sub_count_end' => true,
                'message' => 'Subscription or Topup limit Ended.',
            ];
        }

        $redirectUrl = route('provider.add.service');

        $language = Language::select('id', 'code')->where('is_default', 1)->where('status', 1)->first();

        return [
            'code' => 200,
            'success' => true,
            'redirect_url' => $redirectUrl,
            'language' => $language,
            'message' => 'Successfully.',
        ];
    }

    public function providerSubApi(Request $request): array
    {
        $id = $request->provider_id;

        $packageTrx = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('package_transactions.provider_id', $id)
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('subscription_packages.subscription_type', 'regular')
            ->select(
                'package_transactions.id',
                'package_transactions.provider_id',
                'subscription_packages.number_of_service',
                'package_transactions.package_id',
                'package_transactions.trx_date',
                'package_transactions.end_date'
            )
            ->orderByDesc('package_transactions.id')
            ->first();

        $topup = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('package_transactions.provider_id', $id)
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('subscription_packages.subscription_type', 'topup')
            ->select(
                'package_transactions.id',
                'package_transactions.provider_id',
                'subscription_packages.number_of_service',
                'package_transactions.package_id',
                'package_transactions.trx_date',
                'package_transactions.end_date'
            )
            ->orderByDesc('package_transactions.id')
            ->first();

        if (!$packageTrx && !$topup) {
            return [
                'code' => 200,
                'success' => false,
                'no_package' => true,
                'message' => 'No Subscription and Topup Found.'
            ];
        }

        $serviceCount = Product::where('user_id', $id)->count();
        $currentDate = now();

        $packageEndDateCount = $packageTrx ? $currentDate->diffInDays(Carbon::parse($packageTrx->end_date), false) : -999;
        $topupEndDateCount   = $topup ? $currentDate->diffInDays(Carbon::parse($topup->end_date), false) : -999;

        $number_of_service = 0;

        if ($packageEndDateCount > -1) {
            $number_of_service += $packageTrx->number_of_service;
        }

        if ($topupEndDateCount > -1) {
            $number_of_service += $topup->number_of_service;
        }

        if ($packageEndDateCount <= -1 && $topupEndDateCount <= -1) {
            return [
                'code' => 422,
                'success' => true,
                'sub_end' => true,
                'message' => 'Subscription or Topup Ended.',
            ];
        }

        if ($serviceCount >= $number_of_service) {
            return [
                'code' => 422,
                'success' => true,
                'sub_count_end' => true,
                'message' => 'Subscription or Topup limit Ended.',
            ];
        }

        return [
            'code' => 200,
            'success' => true,
            'message' => 'Successfully.',
        ];
    }

    public function translate(Request $request): array
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

            return [
                'code' => 200,
                'success' => true,
                'message' => __('Translated Successfully.'),
                'translated_values' => $translatedValues,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'code' => 500,
                'message' => 'Translation failed: ' . $e->getMessage(),
            ];
        }
    }

    public function deleteImage(Request $request): array
    {
        $request->validate([
            'id' => 'required',
        ]);

        $deleteImage = Productmeta::find($request->id);

        if ($deleteImage) {

            $deleteImage->delete();

            return ['success' => true, 'message' => 'Image deleted successfully.'];
        }

        return ['success' => false, 'message' => 'Image not found.'];
    }

    public function checkCoupon(Request $request): array
    {
        $request->validate([
            'coupon_code'   => 'required|string',
            'service_id'    => 'required|integer',
            'category_id'   => 'required|integer',
            'subcategory_id' => 'required|integer',
        ]);

        $coupon = DB::table('coupons')
            ->whereRaw('BINARY `code` = ?', [$request->coupon_code])
            ->where('status', 1)
            ->first();

        $serviceId = $request->service_id;
        $categoryId = $request->category_id;
        $subcategoryId = $request->subcategory_id;

        $productType = $coupon->product_type ?? '';

        // Explode and convert to arrays
        $productIds = explode(',', $coupon->product_id ?? '');
        $categoryIds = explode(',', $coupon->category_id ?? '');
        $subcategoryIds = explode(',', $coupon->subcategory_id ?? '');

        // Check if any match
        $matched = in_array($serviceId, $productIds) ||
            in_array($categoryId, $categoryIds) ||
            in_array($subcategoryId, $subcategoryIds) ||
            $productType == 'all';

        if (!$matched) {
            return [
                'success' => false,
                'message' => 'This coupon is not valid.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'coupon' => [
                'coupon_id'   => $coupon->id,
                'coupon_code' => $coupon->code,
                'coupon_type' => $coupon->coupon_type,
                'coupon_value' => $coupon->coupon_value ?? 0,
            ]
        ];
    }
}