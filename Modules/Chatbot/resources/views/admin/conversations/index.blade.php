@extends('admin.admin')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">Conversations</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="javascript:void(0);">Chatbot</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Conversations</li>
                    </ol>
                </nav>
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
                                <th>User</th>
                                <th>Started At</th>
                                <th>Messages</th>
                                <th class="no-sort">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($conversations as $conversation)
                                <tr>
                                    <td>{{ $conversation->id }}</td>
                                    <td>
                                        @if($conversation->user_id)
                                            <strong>{{ $conversation->user_name }}</strong>
                                        @else
                                            <em>Guest User</em><br>
                                        @endif
                                    </td>

                                    <td>{{ $conversation->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $conversation->messages->count() }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('conversations.show', $conversation->id) }}" class="btn btn-sm btn-info me-2">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            <form action="{{ route('conversations.destroy', $conversation->id) }}" method="POST" class="delete-btn">
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