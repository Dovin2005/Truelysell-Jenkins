<?php

namespace App\Repositories\Eloquent;

use App\Http\Resources\ChatUsersResource;
use App\Http\Resources\MessageResource;
use App\Models\Bookings;
use App\Models\User;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Chat\app\Models\Message;
use Modules\Leads\app\Models\ProviderFormsInput;
use Modules\Product\app\Models\Product;

class MessageRepository implements MessageRepositoryInterface
{
    public function chatList(Request $request): array
    {
        $authUser = Auth::user();
        $relatedUserIds = $this->getRelatedUsers($authUser->id);
        $relatedUsers = User::whereIn('id', $relatedUserIds)->get();
        if(!empty($relatedUserIds)){
            $lastUserIds = $this->getLastInteractedFromList($authUser->id, $relatedUserIds);
            $relatedUsers = User::whereIn('id', $lastUserIds)->get();
            $data = ChatUsersResource::collection($relatedUsers);
            return [
                'status' => true,
                'message' => 'Chat List Fetched Successfully',
                'data' => $data
            ];
        }else{
            return [
                'status' => false,
                'message' => 'No User Found'
            ];
        }
    }

    public function getRelatedUsers($user_id): array
    {
        $authUser = User::find($user_id);
        $adminUserIds = User::where('user_type', 1)->pluck('id')->toArray();
        $relatedUserIds = [];

        if ($authUser->user_type == 3) {
            $bookedServices = Bookings::where('user_id', $authUser->id)->pluck('product_id');
            $sellerIds = Product::whereIn('id', $bookedServices)->pluck('user_id')->toArray();
            $providerIds = ProviderFormsInput::whereHas('userFormInput', function ($q) use ($user_id) {
                $q->where('user_id', $user_id)->whereNull('deleted_at');
            })->pluck('provider_id')->unique()->toArray();

            $relatedUserIds = array_unique(array_merge($sellerIds, $providerIds, $adminUserIds));

        } elseif ($authUser->user_type == 2) {
            $myServices = Product::where('user_id', $authUser->id)->pluck('id');
            $buyerIds = Bookings::whereIn('product_id', $myServices)->pluck('user_id')->toArray();
            $relatedUserIds = array_unique(array_merge($buyerIds, $adminUserIds));
        }

        return $relatedUserIds;
    }

    public function sendMessage(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'to_user_id' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return [
                'status' => false,
                'message' => $validator->errors()->toArray()
            ];
        }
        DB::beginTransaction();
        try {
            $hasRelation = $this->hasRelation(Auth::user()->id, $request->to_user_id);
            if($hasRelation){
                $message = new Message();
                $message->content = $request->content;
                $message->from_user_id = Auth::user()->id;
                $message->to_user_id = $request->to_user_id;
                $message->save();
                return [
                    'status' => true,
                    'message' => 'Message Sent Successfully'
                ];

            }else{
                return [
                    'status' => false,
                    'message' => 'You are not related to this user'
                ];
            }
            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'Failed to send message',
                'error_message' => $th->getMessage()
            ];
        }

    }

    public function hasRelation($user_id, $to_user_id): bool
    {
        $authUser = User::where('id', $user_id)->first();
        $relatedUserIds = $this->getRelatedUsers($user_id);
        if(in_array($to_user_id, $relatedUserIds)){
            return true;
        }
        return false;
    }

    public function getLastInteractedFromList($authUserId, $allowedUserIds): array
    {
        return Message::selectRaw('
                CASE
                    WHEN from_user_id = ? THEN to_user_id
                    ELSE from_user_id
                END as user_id, MAX(created_at) as last_message_at', [$authUserId])
            ->where(function ($query) use ($authUserId) {
                $query->where('from_user_id', $authUserId)
                    ->orWhere('to_user_id', $authUserId);
            })
            ->where(function ($query) use ($allowedUserIds, $authUserId) {
                $query->whereIn('from_user_id', $allowedUserIds)
                    ->orWhereIn('to_user_id', $allowedUserIds);
            })
            ->groupBy('user_id')
            ->orderByDesc('last_message_at')
            ->limit(10)
            ->pluck('user_id')
            ->toArray();
    }

    public function getMessages(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'to_user_id' => 'required',
        ]);

        if($validator->fails()){
            return [
                'status' => false,
                'message' => $validator->errors()->toArray()
            ];
        }
        $authUser = Auth::user();
        $to_user_id = $request->to_user_id;

        //fetch messages
        $messages = Message::where(function ($query) use ($authUser, $to_user_id) {
            $query->where('from_user_id', $authUser->id)
                ->where('to_user_id', $to_user_id);
        })->orWhere(function ($query) use ($authUser, $to_user_id) {
            $query->where('from_user_id', $to_user_id)
                ->where('to_user_id', $authUser->id);
        })->orderBy('created_at', 'asc')->paginate(10);

        //prepare data for response
        if(!empty($messages)){
            $data = MessageResource::collection($messages);
        }else{
            $data = [];
        }

        //return response
        return [
            'status' => true,
            'message' => 'Messages Fetched Successfully',
            'data' => $data,
            'has_more' => $messages->hasMorePages(),
            'last_page' => $messages->lastPage(),
            'current_page' => $messages->currentPage(),
            'total_records' => $messages->total(),
            'per_page' => $messages->perPage()
        ];
    }

    public function searchUsers(Request $request): array
    {
        $search = $request->search;
        $authUser = Auth::user();
        $relatedUserIds = $this->getRelatedUsers($authUser->id);
        $relatedUsers = User::whereIn('id', $relatedUserIds);
        if(!empty($search)){
            $relatedUsers = $relatedUsers->where(function ($query) use ($search) {
                               $query->where('name', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%')
                                    ->orWhereHas('userDetails', function ($query) use ($search) {
                                        $query->where('first_name', 'like', '%' . $search . '%')
                                            ->orWhere('last_name', 'like', '%' . $search . '%');
                                    });
                            });
        }
        $relatedUsers = $relatedUsers->limit(10)->get();
        if(!empty($relatedUsers)){
            $data = ChatUsersResource::collection($relatedUsers);
        }else{
            $data = [];
        }
        return [
            'status' => true,
            'message' => 'Users Fetched Successfully',
            'data' => $data
        ];
    }
}
