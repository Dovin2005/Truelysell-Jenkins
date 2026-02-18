<?php

namespace Modules\Chat\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $resource = $this->resource;
        $authUser = Auth::user();
        $position = $resource->from_user_id == $authUser->id ? 'right' : 'left';
        return [
            'message_id' => $resource->id,
            'message_type' => $resource->type,
            'file_path' => $this->getFilePath($resource->file),
            'from_user_id' => $resource->from_user_id,
            'to_user_id' => $resource->to_user_id,
            'content' => $resource->content,
            'position' => $position,
            'created_at' => $resource->created_at,
            'created_at_humantime' => Carbon::parse($resource->created_at)->diffForHumans(),
        ];
    }

    public function getFilePath($file)
    {
        $filepath = null;
        if(!empty($file)){
            if(file_exists(public_path('storage/' . $file))){
                $filepath = url('storage/' . $file);
            }else{
                $filepath = asset('assets/img/profile-default.png');
            }
        }
        
        return $filepath;   
    }

}
