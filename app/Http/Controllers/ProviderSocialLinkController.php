<?php

namespace app\Http\Controllers;

use App\Models\ProviderSocialLink;
use App\Http\Controllers\Controller;
use Modules\GlobalSetting\app\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class ProviderSocialLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function providerSocialLinkIndex()
    {
        $providerId = Auth::user()->provider_id ?? Auth::id();

        $providerSocialLinks = ProviderSocialLink::with('socialLink')
            ->where('provider_id', $providerId)
            ->orderBy('created_at', 'desc')
            ->get();

        $socialLinks = SocialLink::active()->get();

        return view('provider.social_links.index', compact('providerSocialLinks', 'socialLinks'));
    }

    public function getSocialLinks()
    {
        $providerId = Auth::user()->provider_id ?? Auth::id();

        $socialLinks = ProviderSocialLink::with('socialLink')
            ->where('provider_id', $providerId)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $socialLinks
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $socialLinks = SocialLink::active()->get();
        return view('provider.social_links.create', compact('socialLinks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'social_link_id' => 'required|exists:social_links,id',
            'link' => 'required|url|max:255',
            'status' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $providerId = Auth::user()->provider_id ?? Auth::id();

        $existingLink = ProviderSocialLink::where('provider_id', $providerId)
            ->where('social_link_id', $request->social_link_id)
            ->first();

        if ($existingLink) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a link for this platform. Please update the existing one.'
            ], 422);
        }

        $socialLink = SocialLink::find($request->social_link_id);

        $providerSocialLink = ProviderSocialLink::create([
            'provider_id' => $providerId,
            'social_link_id' => $request->social_link_id,
            'platform_name' => $socialLink->platform_name,
            'link' => $request->link,
            'status' => $request->has('status') ? true : false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Social link created successfully!',
            'data' => $providerSocialLink->load('socialLink')
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProviderSocialLink $providerSocialLink)
    {
        $providerId = Auth::user()->provider_id ?? Auth::id();

        if ($providerSocialLink->provider_id !== $providerId) {
            abort(403, 'Unauthorized access');
        }

        return view('provider.social_links.show', compact('providerSocialLink'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProviderSocialLink $providerSocialLink)
    {
        $providerId = Auth::user()->provider_id ?? Auth::id();

        if ($providerSocialLink->provider_id !== $providerId) {
            abort(403, 'Unauthorized access');
        }

        $socialLinks = SocialLink::active()->get();
        return view('provider.social_links.edit', compact('providerSocialLink', 'socialLinks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProviderSocialLink $providerSocialLink)
    {
        $providerId = Auth::user()->provider_id ?? Auth::id();

        if ($providerSocialLink->provider_id !== $providerId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'social_link_id' => 'required|exists:social_links,id',
            'link' => 'required|url|max:255',
            'status' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $existingLink = ProviderSocialLink::where('provider_id', $providerId)
            ->where('social_link_id', $request->social_link_id)
            ->where('id', '!=', $providerSocialLink->id)
            ->first();

        if ($existingLink) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a link for this platform.'
            ], 422);
        }

        $socialLink = SocialLink::find($request->social_link_id);

        $providerSocialLink->update([
            'social_link_id' => $request->social_link_id,
            'platform_name' => $socialLink->platform_name,
            'link' => $request->link,
            'status' => $request->has('status') ? true : false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Social link updated successfully!',
            'data' => $providerSocialLink->load('socialLink')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProviderSocialLink $providerSocialLink)
    {
        $providerId = Auth::user()->provider_id ?? Auth::id();

        if ($providerSocialLink->provider_id !== $providerId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $providerSocialLink->delete();

        return response()->json([
            'success' => true,
            'message' => 'Social link deleted successfully!'
        ]);
    }

    /**
     * Toggle status of the specified resource.
     */
    public function toggleStatus(ProviderSocialLink $providerSocialLink)
    {
        $providerId = Auth::user()->provider_id ?? Auth::id();

        if ($providerSocialLink->provider_id !== $providerId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $providerSocialLink->update([
            'status' => !$providerSocialLink->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!',
            'status' => $providerSocialLink->status
        ]);
    }

    /**
     * Bulk update/create multiple social profiles.
     */
    public function bulkUpdate(Request $request)
    {
        $providerId = Auth::user()->provider_id ?? Auth::id();
        $updatedProfiles = [];

        // Check if profiles data exists
        if (empty($request->profiles) || !is_array($request->profiles)) {
            return $request->ajax()
                ? response()->json([
                    'success' => false,
                    'message' => 'No profile data provided'
                ], 400)
                : redirect()->back()->with('error', 'No profile data provided');
        }

        foreach ($request->profiles as $index => $profile) {
            try {
                // Skip if required fields are missing
                if (empty($profile['social_link_id'])) {
                    continue;
                }

                // Validate each profile
                $validator = Validator::make($profile, [
                    'social_link_id' => 'required|exists:social_links,id',
                    'link' => 'nullable|url|max:255',
                    'status' => 'nullable|string|in:on,off',
                ]);

                if ($validator->fails()) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'errors' => $validator->errors(),
                            'message' => "Validation failed for profile #{$index}"
                        ], 422);
                    }
                    return redirect()->back()->withErrors($validator)->withInput();
                }

                $socialLink = SocialLink::find($profile['social_link_id']);
                if (!$socialLink) {
                    continue;
                }

                // Convert status from 'on'/'off' to 1/0
                $status = ($profile['status'] ?? 'off') === 'on' ? 1 : 0;

                $data = [
                    'social_link_id' => $profile['social_link_id'],
                    'platform_name' => $socialLink->platform_name,
                    'link' => $profile['link'] ?? null,
                    'status' => $status,
                    'provider_id' => $providerId,
                ];

                // First check if this platform already exists for this provider
                $existingProfile = ProviderSocialLink::where('provider_id', $providerId)
                    ->where('social_link_id', $profile['social_link_id'])
                    ->first();

                if ($existingProfile) {
                    // Update existing record
                    $existingProfile->update($data);
                    $updatedProfiles[] = $existingProfile->fresh();
                } else {
                    // Create new record
                    $newProfile = ProviderSocialLink::create($data);
                    $updatedProfiles[] = $newProfile;
                }

            } catch (\Exception $e) {
                continue;
            }
        }

        $response = [
            'success' => true,
            'message' => 'Social profiles updated successfully',
            'data' => [
                'profiles' => $updatedProfiles
            ]
        ];

        return $request->ajax()
            ? response()->json($response)
            : redirect()->back()->with('success', $response['message']);
    }

    public function getProviderSocialLinksApi(Request $request): JsonResponse
    {
        // Get provider ID from request (or Auth if needed)
        $providerId = $request->provider_id;

        if (!$providerId) {
            return response()->json([
                'code' => 422,
                'message' => 'Provider ID is required.'
            ], 422);
        }

        // Get all active social links
        $socialLinks = SocialLink::where('status', 1)->get();

        // Get provider social links, indexed by social_link_id
        $providerLinks = ProviderSocialLink::where('provider_id', $providerId)
            ->whereIn('social_link_id', $socialLinks->pluck('id'))
            ->get()
            ->keyBy('social_link_id');

        // Merge data: add provider-specific link if available
        $mergedLinks = $socialLinks->map(function ($link) use ($providerLinks) {
            $providerData = $providerLinks[$link->id] ?? null;

            return [
                'id' => $link->id,
                'platform_name' => $link->platform_name,
                'icon' => $link->icon,
                'default_link' => $link->link,
                'provider_link' => $providerData?->link ?? null,
                'provider_platform_name' => $providerData?->platform_name ?? null,
                'provider_link_status' => $providerData?->status ?? null,
            ];
        });

        return response()->json([
            'code' => 200,
            'message' => 'Provider social links retrieved.',
            'data' => $mergedLinks
        ]);
    }

    public function saveProviderSocialLinksApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'links' => 'required|array|min:1',
            'links.*.provider_id' => 'required|integer|exists:users,id',
            'links.*.social_link_id' => 'required|integer|exists:social_links,id',
            'links.*.platform_name' => 'required|string|max:100',
            'links.*.status' => 'nullable|in:0,1',
            'links.*.link' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $insertedLinks = [];

        foreach ($validated['links'] as $linkData) {
            $socialLink = ProviderSocialLink::updateOrCreate(
                [
                    'provider_id' => $linkData['provider_id'],
                    'social_link_id' => $linkData['social_link_id'],
                ],
                [
                    'platform_name' => $linkData['platform_name'],
                    'link' => $linkData['link'] ?? null,
                    'status' => $linkData['status'] ?? 1,
                ]
            );

            $insertedLinks[] = $socialLink;
        }

        return response()->json([
            'code' => 200,
            'message' => 'Social media links saved successfully.',
            'data' => $insertedLinks,
        ]);
    }

}
