@extends('layouts.admin')
@section('title', 'Academic Sessions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Academic Sessions</h4>
    <a href="{{ route('admin.sessions.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i> Add Session</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Name</th><th>Status</th><th style="width:260px;">Actions</th></tr></thead>
            <tbody>
                @forelse($sessions as $session)
                <tr>
                    <td>{{ $session->name }}</td>
                    <td>
                        @if($session->is_current)
                            <span class="badge badge-paid">Current</span>
                        @endif
                    </td>
                    <td>
                        @unless($session->is_current)
                        <form action="{{ route('admin.sessions.set-current', $session) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-success">Set Current</button>
                        </form>
                        @endunless
                        <a href="{{ route('admin.sessions.edit', $session) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('admin.sessions.destroy', $session) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this session?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-muted text-center py-3">No sessions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    <form action="{{ route('admin.sessions.rollover') }}" method="POST" onsubmit="return confirm('This will promote all students to the next level. Continue?')">
        @csrf
        <button class="btn btn-warning"><i class="bi bi-arrow-up-circle"></i> Session Rollover (Promote Levels)</button>
    </form>
</div>
@endsection
