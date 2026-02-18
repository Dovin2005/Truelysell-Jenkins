<?php

namespace Modules\Chatbot\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::with('answers')->get();
        return view('chatbot::admin.questions.index', compact('questions'));
    }

    public function create()
    {
        $questions = Question::all();
        return view('chatbot::admin.questions.create', compact('questions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'question_text' => 'required',
            'is_first' => 'sometimes|in:on,off',
            'answers' => 'required|array',
            'answers.*.text' => 'required',
            'answers.*.next_question_id' => 'nullable|exists:questions,id',
        ]);

        $question = Question::create([
            'question_text' => $request->question_text,
            'is_first' => $request->has('is_first'),
        ]);

        foreach ($request->answers as $answerData) {
            $question->answers()->create([
                'answer_text' => $answerData['text'],
                'next_question_id' => $answerData['next_question_id'],
            ]);
        }

        return redirect()->route('chatbot.index')->with('success', 'Question added successfully');
    }

    public function edit($id)
    {
        $question = Question::with('answers')->findOrFail($id);
        $questions = Question::where('id', '!=', $id)->get();
        
        return view('chatbot::admin.questions.edit', compact('question', 'questions'));
    }

    public function update(Request $request, $id)
    {
        $question = Question::findOrFail($id);
        
        $request->validate([
            'question_text' => 'required',
            'is_first' => 'sometimes|in:on,off',
            'answers' => 'required|array',
            'answers.*.text' => 'required',
            'answers.*.next_question_id' => 'nullable|exists:questions,id',
        ]);

        $question->update([
            'question_text' => $request->question_text,
            'is_first' => $request->has('is_first'),
        ]);

        // Delete old answers
        $question->answers()->delete();

        // Create new answers
        foreach ($request->answers as $answerData) {
            $question->answers()->create([
                'answer_text' => $answerData['text'],
                'next_question_id' => $answerData['next_question_id'],
            ]);
        }

        return redirect()->route('chatbot.index')->with('success', 'Question updated successfully');
    }

    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $question->answers()->delete();
        $question->delete();

        return redirect()->route('chatbot.index')->with('success', 'Question deleted successfully');
    }
}