<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['question_text', 'is_first'];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}