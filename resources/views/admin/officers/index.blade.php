@extends('layouts.admin')
@section('title', 'Officers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Officers</h4>
    <a href="{{ route('admin.officers.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i> Add Officer</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Username</th><th>Full Name</th><th>Status</th><th style="width:120px;">Actions</th></tr></thead>
            <tbody>
                @forelse($officers as $officer)
                <tr>
                    <td>{{ $officer->username }}</td>
                    <td>{{ $officer->full_name }}</td>
                    <td><span class="badge {{ $officer->is_active ? 'badge-paid' : 'badge-unpaid' }}">{{ $officer->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <a href="{{ route('admin.officers.edit', $officer) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('admin.officers.destroy', $officer) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this officer?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-muted text-center py-3">No officers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
