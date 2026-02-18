<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

interface MessageRepositoryInterface
{
    public function chatList(Request $request): array;
    public function sendMessage(Request $request): array;
    public function getMessages(Request $request): array;
    public function searchUsers(Request $request): array;
}
