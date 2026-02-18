<?php

namespace Modules\Chatbot\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = Conversation::with(['messages' => function($query) {
            $query->with(['question', 'answer'])->latest();
        }])->latest()->get();
        
        return view('chatbot::admin.conversations.index', compact('conversations'));
    }

    public function show($id)
    {
        $conversation = Conversation::with(['messages' => function($query) {
            $query->with(['question', 'answer'])->orderBy('created_at', 'asc');
        }])->findOrFail($id);
        
        return view('chatbot::admin.conversations.show', compact('conversation'));
    }

    public function destroy($id)
    {
        $conversation = Conversation::findOrFail($id);
        $conversation->delete();
        
        return redirect()
            ->route('admin.chatbot.conversations.index')
            ->with('success', 'Conversation deleted successfully');
    }
}