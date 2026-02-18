<?php

namespace Modules\Chat\app\Repositories\Contracts;

use Illuminate\Http\Request;

interface ChatRepositoryInterface
{
    public function adminChat(Request $request);
    public function providerChat(Request $request);
    public function userChat(Request $request);
    public function sendChat(Request $request);
    public function fetchMessages(Request $request);
    public function getRelatedUsers($user_id): array;
}
