<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ChatbotQuestion;

class ChatbotAnswer extends Model
{
    protected $fillable = ['question_id', 'answer', 'next_question_id'];

    public function nextQuestion()
    {
        return $this->belongsTo(ChatbotQuestion::class, 'next_question_id');
    }
}
