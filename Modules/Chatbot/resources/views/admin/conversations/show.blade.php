@extends('admin.admin')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h2>Conversation Details</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h4>Conversation ID: {{ $conversation->id }}</h4>
                            <p>
                                <strong>User:</strong> 
                                @if($conversation->user_id)
                                    {{ $conversation->user_name }}
                                @else
                                    Guest ({{ $conversation->user_identifier }})
                                @endif
                            </p>
                            <p><strong>Started:</strong> {{ $conversation->created_at->format('Y-m-d H:i:s') }}</p>
                            <p><strong>Total Messages:</strong> {{ $conversation->messages->count() }}</p>
                        </div>
                        
                        @if($conversation->messages->isEmpty())
                            <div class="alert alert-info">No messages found in this conversation.</div>
                        @else
                            <h4>Messages</h4>
                            <div class="chat-history">
                                @foreach ($conversation->messages as $message)
                                    <div class="message mb-3 p-3 {{ $message->is_from_user ? 'bg-primary text-white' : 'bg-light' }}" style="border-radius: 10px;">
                                        <strong>{{ $message->is_from_user ? 'User' : 'Bot' }} : </strong> 
                                        {{ $message->message_text }}
                                        <div class="small mt-1 ms-5">
                                            <div><strong>Time:</strong> {{ $message->created_at->format('H:i:s') }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        
                        <div class="mt-4">
                            <a href="{{ route('conversations.index') }}" class="btn btn-secondary">Back to List</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection