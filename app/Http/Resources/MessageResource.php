<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = $this->resource;
        $authUser = Auth::user();
        $position = $resource->from_user_id == $authUser->id ? 'right' : 'left';
        return [
           'message_id' => $resource->id,
           'from_user_id' => $resource->from_user_id,
           'to_user_id' => $resource->to_user_id,
           'content' => $resource->content,
           'position' => $position,
           'created_at' => $resource->created_at,
           'created_at_humantime' => Carbon::parse($resource->created_at)->diffForHumans(),
        ];
    }
    
}
