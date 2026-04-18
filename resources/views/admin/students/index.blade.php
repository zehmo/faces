@extends('layouts.admin')
@section('title', 'Students')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Students</h4>
    <a href="{{ route('admin.students.create') }}" class="btn btn-success btn-sm"><i class="bi bi-plus-lg"></i> Add Student</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search reg number or name..." value="{{ request('search') }}">
    </div>
    <div class="col-md-2">
        <select name="department" class="form-select form-select-sm">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <select name="level" class="form-select form-select-sm">
            <option value="">All Levels</option>
            @foreach(['100','200','300','400'] as $lvl)
                <option value="{{ $lvl }}" {{ request('level') == $lvl ? 'selected' : '' }}>{{ $lvl }}L</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-sm btn-outline-secondary w-100">Filter</button>
    </div>
    <div class="col-md-2">
        <a href="{{ route('admin.students.export', request()->only(['department', 'level'])) }}" class="btn btn-sm btn-outline-success w-100"><i class="bi bi-download"></i> Export CSV</a>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th></th>
                    <th>Reg Number</th>
                    <th>Full Name</th>
                    <th>Department</th>
                    <th>Level</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                <tr>
                    <td>
                        @if($student->photo_filename)
                            <img src="{{ asset('storage/photos/thumbs/' . $student->photo_filename) }}" class="photo-thumb" alt="">
                        @else
                            <span class="photo-thumb bg-secondary d-inline-flex align-items-center justify-content-center text-white"><i class="bi bi-person"></i></span>
                        @endif
                    </td>
                    <td class="fw-semibold">{{ $student->reg_number }}</td>
                    <td>{{ $student->full_name }}</td>
                    <td>{{ $student->department->name ?? '—' }}</td>
                    <td>{{ $student->level }}L</td>
                    <td>
                        <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-success" title="View"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this student?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No students found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $students->links() }}</div>
@endsection
