@extends('admin.admin')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">Questions</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">Chatbot</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Questions</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    <a href="chatbot/create" class="btn btn-primary">
                        <i class="ti ti-square-rounded-plus-filled me-2"></i>Add New Question
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0 py-3">
                <div class="table-responsive">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    <table class="table">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Question</th>
                                <th>Is First</th>
                                <th>Answers</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($questions as $question)
                                <tr>
                                    <td>{{ $question->id }}</td>
                                    <td>{{ $question->question_text }}</td>
                                    <td>
                                        <span class="badge bg-{{ $question->is_first ? 'success' : 'secondary' }}">
                                            {{ $question->is_first ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td>
                                        @foreach ($question->answers as $answer)
                                            <div class="mb-1">
                                                {{ $answer->answer_text }} 
                                                @if($answer->next_question_id)
                                                    <span class="badge bg-info">
                                                        → Q{{ $answer->next_question_id }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('chatbot.edit', $question->id) }}" class="btn btn-sm btn-warning me-2">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <form action="{{ route('chatbot.destroy', $question->id) }}" method="POST" class="delete-btn">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

