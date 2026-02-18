@extends('admin.admin')

@section('content')
<div class="page-wrapper">
    <div class="content">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2>Add New Question</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('chatbot.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="question_text" class="form-label">Question Text</label>
                            <input type="text" class="form-control" id="question_text" name="question_text" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_first" name="is_first">
                            <label class="form-check-label" for="is_first">Is this the first question?</label>
                        </div>
                        
                        <h4>Answers</h4>
                        <div id="answers-container">
                            <div class="answer-group mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="answers[0][text]" placeholder="Answer text" required>
                                    </div>
                                    <div class="col-md-6">
                                        <select class="form-control" name="answers[0][next_question_id]">
                                            <option value="">No next question (end conversation)</option>
                                            @foreach ($questions as $question)
                                                <option value="{{ $question->id }}">Question #{{ $question->id }}: {{ Str::limit($question->question_text, 30) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-secondary" id="add-answer">Add Another Answer</button>
                        <button type="submit" class="btn btn-primary">Save Question</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@endsection

@include('chatbot::admin.questions.includes')