<?php

namespace Modules\Chatbot\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Conversation;
use App\Models\ChatMessage;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
     public function start(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $conversationId = $request->session()->get('conversation_id');
        
        if ($conversationId) {
            $conversation = Conversation::find($conversationId);
        } else {
            $conversation = Conversation::create([
                'user_id' => auth()->id(),
                'user_identifier' => 'user_'.auth()->id(),
                'user_name' => auth()->user()->name
            ]);
            $request->session()->put('conversation_id', $conversation->id);
        }

        $firstQuestion = Question::where('is_first', true)->first();

        if (!$firstQuestion) {
            return response()->json(['error' => 'No first question configured'], 404);
        }

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'question_id' => $firstQuestion->id,
            'message_text' => $firstQuestion->question_text,
            'is_from_user' => false,
        ]);

        return response()->json([
            'question' => $firstQuestion,
            'answers' => $firstQuestion->answers,
        ]);
    }

    public function answer(Request $request)
    {
        $request->validate([
            'answer_id' => 'required|exists:answers,id',
        ]);

        $conversationId = $request->session()->get('conversation_id');
        if (!$conversationId) {
            return response()->json(['error' => 'No active conversation'], 400);
        }

        $conversation = Conversation::find($conversationId);
        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $answer = Answer::with('question')->find($request->answer_id);
        
        // Save user's answer
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'answer_id' => $answer->id,
            'question_id' => $answer->question_id,
            'message_text' => $answer->answer_text,
            'is_from_user' => true,
        ]);

        // Get next question
        if ($answer->next_question_id) {
            $nextQuestion = Question::with('answers')->find($answer->next_question_id);
            
            // Save bot's next question
            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'question_id' => $nextQuestion->id,
                'message_text' => $nextQuestion->question_text,
                'is_from_user' => false,
            ]);

            return response()->json([
                'question' => $nextQuestion,
                'answers' => $nextQuestion->answers,
            ]);
        }

        // End of conversation
        return response()->json([
            'message' => 'Thank you for chatting with us!',
            'end_conversation' => true,
        ]);
    }

    public function history(Request $request)
    {
        $conversationId = $request->session()->get('conversation_id');
        if (!$conversationId) {
            return response()->json(['messages' => []]);
        }

        $messages = ChatMessage::with('question', 'answer')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['messages' => $messages]);
    }
}
