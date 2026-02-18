<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Modules\Chat\app\Models\Message;

class ChatUsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = $this->resource;
        $userDetails = $resource->userDetails;
        
        return [
            'user_id' => $resource->id,
            'username' => $resource->name,
            'fullname' => $this->getFullName($userDetails),
            'email' => $resource->email,
            'profile_image' => $this->getProfileImage($userDetails),
            'last_message' => $this->lastMessage(),

        ];
    }

    public function lastMessage(){
        $userId = $this->resource->id;
        $authUserId = Auth::user()->id;
        $response = [];
        $message = Message::where(function ($query) use ($userId, $authUserId) {
                  $query->where('from_user_id', $userId)
                        ->where('to_user_id', $authUserId)
                  ->orWhere('from_user_id', $authUserId)
                        ->where('to_user_id', $userId);
                })->orderBy('created_at', 'desc')->first();
        
        if(!empty($message)){
            $response = [
                'created_at' => $message->created_at,
                'created_at_humantime' => $message->created_at->diffForHumans(),
                'message' => $message->content
            ];    
        }
        
        return $response;
    }

    public function getFullName($userDetails)
    {
        $fullname = "";
        if(!empty($userDetails)){
            $fullname = $userDetails->first_name . ' ' . $userDetails->last_name;
        }
        return $fullname;
    }

    public function getProfileImage($userDetails)
    {  
        $profileImage = null;
        if(!empty($userDetails->profile_image)){
            //check if file exists
            $filepath = public_path('storage/profile/' . $userDetails->profile_image);
            if(file_exists($filepath)){
                $profileImage = url('storage/profile/' . $userDetails->profile_image);
            }else{
                $profileImage = asset('assets/img/profile-default.png');
            }
        }
        
        return $profileImage;   
    }
}
