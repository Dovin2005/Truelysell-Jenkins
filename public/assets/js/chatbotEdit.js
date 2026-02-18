
     document.addEventListener('DOMContentLoaded', function() {
        let answerCount = {{ count($question->answers) }};
        
        document.getElementById('add-answer').addEventListener('click', function() {
            const container = document.getElementById('answers-container');
            const newAnswer = document.createElement('div');
            newAnswer.className = 'answer-group mb-3';
            newAnswer.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="answers[${answerCount}][text]" placeholder="Answer text" required>
                    </div>
                    <div class="col-md-6">
                        <select class="form-control" name="answers[${answerCount}][next_question_id]">
                            <option value="">No next question (end conversation)</option>
                            @foreach ($questions as $question)
                                <option value="{{ $question->id }}">Question #{{ $question->id }}: {{ Str::limit($question->question_text, 30) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            `;
            container.appendChild(newAnswer);
            answerCount++;
        });
    });