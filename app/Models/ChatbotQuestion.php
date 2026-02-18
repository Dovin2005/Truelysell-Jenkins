<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ChatbotAnswer;


class ChatbotQuestion extends Model
{
    protected $fillable = ['question'];

    public function answers()
    {
        return $this->hasMany(ChatbotAnswer::class, 'question_id');
    }
}


