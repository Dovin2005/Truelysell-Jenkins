<?php

namespace Modules\GlobalSetting\app\Http\Controllers\admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\SocialMediaShare;
use Illuminate\Support\Facades\Validator;

class SocialMediaShareController extends Controller
{
    /**
     * Display the social media share list page
     */
    public function index()
    {
        return view('globalsetting::social_share.index');
    }

    /**
     * Store or update a social media share
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'platform_name' => 'required|string|max:255',
            'url' => 'required|url',
            'icon' => 'nullable|string|max:255',
            'status' => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'platform_name' => $request->platform_name,
            'url' => $request->url,
            'icon' => $request->icon,
            'status' => $request->boolean('status') ? 1 : 0, // ✅ Explicit conversion to 1 or 0
        ];

        if ($request->id) {
            $share = SocialMediaShare::find($request->id);
            if ($share) {
                $share->update($data);
                return response()->json(['message' => 'Social media share updated successfully.']);
            }
        }

        SocialMediaShare::create($data);
        return response()->json(['message' => 'Social media share created successfully.']);
    }


    /**
     * Fetch all social media shares for listing (e.g. DataTable)
     */
    public function getList(Request $request)
    {
        $shares = SocialMediaShare::latest()->get();
        return response()->json(['data' => $shares]);
    }

    /**
     * Return a specific social media share for editing
     */
    public function show($id)
    {
        $share = SocialMediaShare::find($id);

        if (!$share) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        return response()->json($share);
    }

    /**
     * Delete a social media share
     */
    public function destroy(Request $request)
    {
        $share = SocialMediaShare::find($request->id);

        if (!$share) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        $share->delete();

        return response()->json(['message' => 'Social media share deleted successfully.']);
    }
}