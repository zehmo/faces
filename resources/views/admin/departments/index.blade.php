@extends('layouts.admin')
@section('title', 'Departments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Departments</h4>
    <a href="{{ route('admin.departments.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i> Add Department</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Name</th><th>Students</th><th style="width:120px;">Actions</th></tr></thead>
            <tbody>
                @forelse($departments as $dept)
                <tr>
                    <td>{{ $dept->name }}</td>
                    <td>{{ $dept->students_count }}</td>
                    <td>
                        <a href="{{ route('admin.departments.edit', $dept) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('admin.departments.destroy', $dept) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this department?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-muted text-center py-3">No departments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
