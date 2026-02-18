<?php

namespace Modules\GlobalSetting\app\Repositories\Eloquent;

use Modules\GlobalSetting\app\Repositories\Contracts\SocialLinkRepositoryInterface;
use Modules\GlobalSetting\app\Models\SocialLink;
use Illuminate\Http\Request;
use Exception;

class SocialLinkRepository implements SocialLinkRepositoryInterface
{
    public function store(Request $request)
    {
        try {
            if ($request->has('id') && $request->id !== null) {
                $socialLink = SocialLink::findOrFail($request->id);
                $message = __('Social Link Updated');
            } else {
                $socialLink = new SocialLink();
                $message = __('Social Link Saved');
            }

            $socialLink->status = $request->status == 1 ? 1 : 0;
            $socialLink->platform_name = $request->platform_name;
            $socialLink->link = $request->link;
            $socialLink->icon = $request->icon;
            $socialLink->save();

            return [
                'success' => true,
                'code' => 200,
                'message' => $message,
                'data' => $socialLink
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'code' => 422,
                'message' => __('Something Went Wrong'),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getAll(Request $request)
    {
        $pageLength = $request->input('length', 10);
        $offset = $request->input('start', 0);

        $query = SocialLink::query();

        if ($request->has('search') && $request->search != null) {
            $query->where(function ($q) use ($request) {
                $q->where('platform_name', 'like', '%' . $request->search . '%')
                  ->orWhere('link', 'like', '%' . $request->search . '%')
                  ->orWhere('icon', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('status') && $request->status != null) {
            $query->where('status', $request->status);
        }

        $totalRecords = SocialLink::count();
        $filteredRecords = $query->count();

        $socialLinks = $query->orderBy('platform_name', 'asc')
                            ->skip($offset)
                            ->take($pageLength)
                            ->get();

        return [
            'draw'            => intval($request->draw),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $socialLinks
        ];
    }

    public function find($id)
    {
        $result = SocialLink::find($id);

        return [
            'success' => true,
            'code' => 200,
            'data' => $result
        ];
    }

    public function delete(Request $request)
    {
        try {
            $result = SocialLink::findOrFail($request->delete_id);
            $result->delete();

            return [
                'success' => true,
                'code' => 200,
                'message' => __('Social Link Deleted'),
                'data' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'code' => 404,
                'message' => __('Something Went Wrong'),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function toggleStatus(Request $request)
    {
        try {
            $socialLink = SocialLink::findOrFail($request->id);
            $socialLink->status = $request->status;
            $socialLink->save();

            return [
                'success' => true,
                'code' => 200,
                'message' => __('Status Updated Successfully'),
                'data' => $socialLink
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'code' => 404,
                'message' => __('Something Went Wrong'),
                'error' => $e->getMessage(),
            ];
        }
    }
}