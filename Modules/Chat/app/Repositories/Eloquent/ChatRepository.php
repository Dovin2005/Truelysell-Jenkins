<?php

namespace Modules\Chat\app\Repositories\Eloquent;

use Modules\Chat\app\Repositories\Contracts\ChatRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Chat\app\Models\Message;
use Modules\Leads\app\Models\ProviderFormsInput;
use Modules\Product\app\Models\Product;
use Modules\Chat\app\Transformers\ChatResource;
use App\Models\Bookings;
use App\Models\User;
use App\Models\UserDetail;
use App\Services\MqttService;

class ChatRepository implements ChatRepositoryInterface
{
    public function adminChat(Request $request)
    {
        $authId = Auth::id();
        $search = $request->search ?? '';
        $perPage = 10;

        $users = User::with('userDetail')
            ->select('users.id', 'users.email', 'users.name')
            ->where('users.id', '!=', $authId)
            ->where('status', 1)
            ->when($search, function ($q) use ($search) {
                $searchWords = array_filter(explode(' ', $search));

                $q->where(function ($query) use ($searchWords) {
                    foreach ($searchWords as $word) {
                        $word = trim($word);
                        if ($word === '') continue;

                        $query->where(function ($subquery) use ($word) {
                            $subquery->where('users.name', 'like', "%{$word}%")
                                ->orWhereHas('userDetail', function ($sub) use ($word) {
                                    $sub->where('first_name', 'like', "%{$word}%")
                                        ->orWhere('last_name', 'like', "%{$word}%");
                                });
                        });
                    }
                });
            })
            ->paginate($perPage);

            $users->getCollection()->transform(function ($user) {
                $name = $user->userDetail && $user->userDetail->first_name
                    ? $user->userDetail->first_name . ' ' . $user->userDetail->last_name
                    : $user->name;
                $user->name = ucwords($name);

                $profilePath = $user->userDetail->profile_image ?? '';
                $user->profile_image = (!empty($profilePath) && file_exists(public_path('storage/profile/' . $profilePath)))
                    ? url('storage/profile/' . $profilePath)
                    : asset('assets/img/profile-default.png');

                return $user;
            });

        return [
            'users' => $users->items(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'sender' => Auth::user()
        ];
    }

    public function providerChat(Request $request)
    {
        $authId = Auth::id();
        $relatedUserIds = $this->getRelatedUsers($authId);
        $search = $request->search ?? '';
        $perPage = 10;

        $users = User::with('userDetail')
            ->select('users.id', 'users.email', 'users.name', 'users.user_type')
            ->where('users.id', '!=', $authId)
            ->where('status', 1)
            ->where(function ($query) use ($authId, $relatedUserIds) {
                $query->whereIn('id', $relatedUserIds)
                    ->orWhereHas('userDetail', function ($q) use ($authId) {
                        $q->where('parent_id', $authId);
                    });
            })
            ->when($search, function ($q) use ($search) {
                $searchWords = array_filter(explode(' ', $search));

                $q->where(function ($query) use ($searchWords) {
                    foreach ($searchWords as $word) {
                        $word = trim($word);
                        if ($word === '') continue;

                        $query->where(function ($subquery) use ($word) {
                            $subquery->where('users.name', 'like', "%{$word}%")
                                ->orWhereHas('userDetail', function ($sub) use ($word) {
                                    $sub->where('first_name', 'like', "%{$word}%")
                                        ->orWhere('last_name', 'like', "%{$word}%");
                                });
                        });
                    }
                });
            })
            ->paginate($perPage);

        $usersCollection = $users->getCollection()->map(function ($user) use ($authId) {

            $name = $user->userDetail && $user->userDetail->first_name
                ? $user->userDetail->first_name . ' ' . $user->userDetail->last_name
                : $user->name;
            $user->name = ucwords($name);

            $profilePath = $user->userDetail->profile_image ?? '';
            $user->profile_image = (!empty($profilePath) && file_exists(public_path('storage/profile/' . $profilePath)))
                ? url('storage/profile/' . $profilePath)
                : asset('assets/img/profile-default.png');

            $lastMessage = Message::where(function ($query) use ($user, $authId) {
                    $query->where('from_user_id', $user->id)->where('to_user_id', $authId);
                })
                ->orWhere(function ($query) use ($user, $authId) {
                    $query->where('from_user_id', $authId)->where('to_user_id', $user->id);
                })
                ->orderBy('id', 'desc')
                ->first();

            $user->lastMessage = $lastMessage ? $lastMessage->content : null;
            $user->lastMessageTime = $lastMessage ? $lastMessage->created_at : null;

            $user->unread_count = Message::where('from_user_id', $user->id)
                ->where('to_user_id', $authId)
                ->where('is_read_message', 0)
                ->count();

            return $user;
        });

        // Sort by last message time
        $sortedUsers = $usersCollection->sortByDesc(function ($user) {
            return $user->lastMessageTime ? strtotime($user->lastMessageTime) : 0;
        })->values();

        // Push admin to last
        $admin = $sortedUsers->firstWhere('user_type', 1);
        if ($admin) {
            $sortedUsers = $sortedUsers->reject(fn($u) => $u->id === $admin->id);
            $sortedUsers->push($admin);
        }

        $users->setCollection($sortedUsers);

        return [
            'users' => $users->items(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'sender' => Auth::user(),
            'chatUserId' => $request->user_id ? customDecrypt($request->user_id, User::$userSecretKey) : ''
        ];
    }

    public function userChat(Request $request)
    {
        $authId = Auth::id();
        $relatedUserIds = $this->getRelatedUsers($authId);
        $search = $request->search ?? '';
        $perPage = 10;

        $users = User::with('userDetail')
            ->select('users.id', 'users.email', 'users.name')
            ->where('users.id', '!=', $authId)
            ->where('status', 1)
            ->whereIn('id', $relatedUserIds)
            ->when($search, function ($q) use ($search) {
                $searchWords = array_filter(explode(' ', $search));
                $q->where(function ($query) use ($searchWords) {
                    foreach ($searchWords as $word) {
                        $word = trim($word);
                        if ($word === '') continue;
                        $query->where(function ($subquery) use ($word) {
                            $subquery->where('users.name', 'like', "%{$word}%")
                                ->orWhereHas('userDetail', function ($sub) use ($word) {
                                    $sub->where('first_name', 'like', "%{$word}%")
                                        ->orWhere('last_name', 'like', "%{$word}%");
                                });
                        });
                    }
                });
            })
            ->paginate($perPage);

        $usersCollection = $users->getCollection()->map(function ($user) use ($authId) {

            // Format display name
            $name = $user->userDetail && $user->userDetail->first_name
                ? $user->userDetail->first_name . ' ' . $user->userDetail->last_name
                : $user->name;
            $user->name = ucwords($name);

            // Profile Path
            $profilePath = $user->userDetail->profile_image ?? '';
            $user->profile_image = (!empty($profilePath) && file_exists(public_path('storage/profile/' . $profilePath)))
                ? url('storage/profile/' . $profilePath)
                : asset('assets/img/profile-default.png');

            // Last Message
            $lastMessage = Message::where(function ($query) use ($user, $authId) {
                    $query->where('from_user_id', $user->id)->where('to_user_id', $authId);
                })
                ->orWhere(function ($query) use ($user, $authId) {
                    $query->where('from_user_id', $authId)->where('to_user_id', $user->id);
                })
                ->orderBy('id', 'desc')
                ->first();

            $user->lastMessage = $lastMessage ? $lastMessage->content : null;
            $user->lastMessageTime = $lastMessage ? $lastMessage->created_at : null;

            // Unread Count
            $user->unread_count = Message::where('from_user_id', $user->id)
                ->where('to_user_id', $authId)
                ->where('is_read_message', 0)
                ->count();

            return $user;
        });

        $sortedUsers = $usersCollection->sortByDesc(function ($user) {
            return $user->lastMessageTime ? strtotime($user->lastMessageTime) : 0;
        })->values();
        

        $users->setCollection($sortedUsers);

        return [
            'users' => $users->items(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'sender' => Auth::user(),
            'chatUserId' => $request->user_id ? customDecrypt($request->user_id, User::$userSecretKey) : ''
        ];
    }

    public function sendChat(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $payload = [
                'sender_id' => $request->sender_id,
                'receiver_id' => $request->receiver_id,
                'message' => '',
                'type' => $request->messageType ?? 'text',
            ];

            // Handle file messages
            if ($request->messageType === 'file' && $request->hasFile('file')) {
                $file = $request->file('file');
                $folder = 'chat';

                $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs($folder, $filename, 'public');

                $path = "$folder/$filename";
                $mimeType = $file->getClientMimeType();
                $size = $file->getSize();

                $message = new Message();
                $message->from_user_id = $request->sender_id;
                $message->to_user_id = $request->receiver_id;
                $message->type = 'file';
                $message->file = $path;
                $message->mime_type = $mimeType;
                $message->size = (string) $size;
                $message->content = $file->getClientOriginalName();
                $message->save();

                $payload['message'] = $path; // File path will be shown on frontend
            }

            // Handle text messages
            if (!empty($request->message) && $request->messageType === 'text') {
                $message = new Message();
                $message->from_user_id = $request->sender_id;
                $message->to_user_id = $request->receiver_id;
                $message->type = 'text';
                $message->content = $request->message;
                $message->save();

                $payload['message'] = $request->message;
            }

            // Encode and publish to MQTT
            $mqtt = new MqttService();
            $encodedPayload = json_encode($payload);

            if ($encodedPayload === false) {
                return response()->json([
                    'success' => false,
                    'message' => __('Failed to encode message payload'),
                ]);
            }

            $mqtt->publish($request->topic, $encodedPayload);

            return response()->json([
                'success' => true,
                'message' => __('Message sent successfully'),
                'data' => $payload,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to send message'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function fetchMessages(Request $request)
    {
        $authUserId = Auth::id();
        $messagePartnerId = $request->user_id;
        $last_offset = $request->last_offset ?? "";
        $perPage = min($last_offset ? intval($last_offset) : 10, 10);

        $totalMessages = Message::where(function ($q) use ($authUserId, $messagePartnerId) {
            $q->where('from_user_id', $authUserId)->where('to_user_id', $messagePartnerId);
        })->orWhere(function ($q) use ($authUserId, $messagePartnerId) {
            $q->where('from_user_id', $messagePartnerId)->where('to_user_id', $authUserId);
        })->count();

        $offset = !$request->has('offset') || $request->offset === ""
            ? max(0, ($totalMessages - $perPage) + 1)
            : max(0, (int) $request->offset);

        $messages = Message::where(function ($q) use ($authUserId, $messagePartnerId) {
            $q->where('from_user_id', $authUserId)->where('to_user_id', $messagePartnerId);
        })->orWhere(function ($q) use ($authUserId, $messagePartnerId) {
            $q->where('from_user_id', $messagePartnerId)->where('to_user_id', $authUserId);
        })->orderBy('id', 'asc')->offset($offset)->limit($perPage)->get();

        $lastMessage = Message::where(function ($q) use ($authUserId, $messagePartnerId) {
            $q->where('from_user_id', $authUserId)->where('to_user_id', $messagePartnerId);
        })->orWhere(function ($q) use ($authUserId, $messagePartnerId) {
            $q->where('from_user_id', $messagePartnerId)->where('to_user_id', $authUserId);
        })->orderBy('id', 'desc')->first();

        $lastMessageResp = $lastMessage ? [
            'id' => $lastMessage->id,
            'message' => strlen($lastMessage->content) > 20
                ? substr($lastMessage->content, 0, 20) . '...'
                : $lastMessage->content,
            'created_at' => optional($lastMessage->created_at)->diffForHumans()
        ] : null;

        return [
            'status' => true,
            'code' => 200,
            'messages' => ChatResource::collection($messages),
            'next_offset' => $offset === 0 ? null : max(0, $offset - $perPage),
            'last_offset' => $offset,
            'last_message' => $lastMessageResp
        ];
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
        } else if ($authUser->user_type == 4) {
            $providerIds = UserDetail::where('user_id', $authUser->id)
                ->pluck('parent_id')
                ->toArray();
            $myServices = Product::where('user_id', $authUser->id)->pluck('id');
            $buyerIds = Bookings::where(function($query) use ($myServices, $authUser) {
                    $query->whereIn('product_id', $myServices)
                        ->orWhere('staff_id', $authUser->id);
                })
                ->pluck('user_id')
                ->toArray();

            $relatedUserIds = array_unique(array_merge($providerIds, $adminUserIds, $buyerIds));
        }

        return $relatedUserIds;
    }
}
