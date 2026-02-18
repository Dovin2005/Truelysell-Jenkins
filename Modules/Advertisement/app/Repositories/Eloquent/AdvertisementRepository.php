<?php

namespace Modules\Advertisement\app\Repositories\Eloquent;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Modules\Advertisement\app\Models\SmartAd;
use Modules\Advertisement\app\Repositories\Contracts\AdvertisementRepositoryInterface;

class AdvertisementRepository implements AdvertisementRepositoryInterface
{
    public function index(): array
    {
        $advertisements = SmartAd::orderBy('id', 'desc')->get();
        $data = [];

        foreach ($advertisements as $advertisement) {
            $placements = !empty($advertisement->placements) ? json_decode($advertisement->placements, true) : [];
            $firstPlacement = $placements[0] ?? ['position' => '', 'selector' => '', 'style' => ''];

            $data[] = [
                'id' => $advertisement->id,
                'name' => $advertisement->name,
                'slug' => $advertisement->slug,
                'body_data' => $advertisement->body,
                'adType' => $advertisement->adType,
                'image' => $advertisement->image ? asset('storage/' . $advertisement->image) : null,
                'imageUrl' => $advertisement->imageUrl,
                'imageAlt' => $advertisement->imageAlt,
                'views' => $advertisement->views,
                'clicks' => $advertisement->clicks,
                'status' => $advertisement->enabled,
                'position' => $firstPlacement['position'],
                'selector' => $firstPlacement['selector'],
                'style' => $firstPlacement['style'],
            ];
        }

        return [
            'code' => 200,
            'message' => __('Advertisement details retrieved successfully.'),
            'data' => $data
        ];
    }

    public function createAd(Request $request): array
    {
        $adType = $request->ad_type;
        $adCustom = $request->ad_custom ?? '';
        $adSelector = $request->ad_selector ?? '';
        $adPosition = $request->ad_position ?? '';

        $imagePath = null;
        if ($adType === 'IMAGE' && $request->hasFile('ad_image')) {
            $imagePath = $request->file('ad_image')->store('image', 'public');
        }

        if ($adType === 'HTML') {
            $body = $request->body;
            $image = null;
            $imageUrl = null;
            $imageAlt = null;
        } else {
            $body = null;
            $image = $request->ad_image;
            $imageUrl = $request->ad_url;
            $imageAlt = $request->ad_alt;
        }

        $data = [
            'name' => $request->ad_name,
            'slug' => Str::slug($request->ad_name),
            'body' => $body,
            'adType' => $adType,
            'image' => $imagePath,
            'imageUrl' => $imageUrl,
            'imageAlt' => $imageAlt,
            'enabled' => $request->status,
            'placements' => json_encode([
                [
                    'position' => $adPosition,
                    'selector' => $adSelector,
                    'style' => $adCustom,
                ]
            ]),
        ];

        $smartAd = SmartAd::create($data);

        if ($smartAd) {
            return [
                'code' => 200,
                'message' => 'Advertisement created successfully',
                'data' => [],
            ];
        }

        return [
            'code' => 500,
            'message' => 'Failed to create advertisement'
        ];
    }

    public function editAd(Request $request): array
    {
        $id = $request->edit_id;
        $adType = $request->edit_ad_type;
        $adCustom = $request->edit_ad_custom ?? '';
        $adSelector = $request->edit_ad_selector ?? '';
        $adPosition = $request->edit_ad_position ?? '';

        $imagePath = null;
        if ($adType === 'IMAGE' && $request->hasFile('edit_ad_image')) {
            $imagePath = $request->file('edit_ad_image')->store('image', 'public');
        }

        if ($adType === 'HTML') {
            $body = $request->edit_body;
            $image = null;
            $imageUrl = null;
            $imageAlt = null;
        } else {
            $body = null;
            $image = $request->edit_ad_image;
            $imageUrl = $request->edit_ad_url;
            $imageAlt = $request->edit_ad_alt;
        }

        $data = [
            'name' => $request->edit_ad_name,
            'slug' => Str::slug($request->edit_ad_name),
            'body' => $body,
            'adType' => $adType,
            'imageUrl' => $imageUrl,
            'imageAlt' => $imageAlt,
            'enabled' => $request->edit_status,
            'placements' => json_encode([
                [
                    'position' => $adPosition,
                    'selector' => $adSelector,
                    'style' => $adCustom,
                ]
            ]),
        ];

        if (!empty($imagePath)) {
            $data['image'] = $imagePath;
        }

        $smartAd = SmartAd::where('id', $id)->update($data);

        if ($smartAd) {
            return [
                'code' => 200,
                'message' => 'Advertisement edited successfully',
                'data' => [],
            ];
        }

        return [
            'code' => 500,
            'message' => 'Failed to edit advertisement'
        ];
    }

    public function deleteAd(Request $request): array
    {
        $add = SmartAd::where('id', $request->input('id'))->firstOrFail();
        $add->delete();

        return [
            'code' => 200,
            'success' => true,
            'message' => 'Advertisement deleted successfully'
        ];
    }
}