@extends('layouts.admin')
@section('title', 'Edit Student')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Edit Student — {{ $student->reg_number }}</h4>
    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary btn-sm">Back to List</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.students._form')
            <div class="mt-4">
                <button type="submit" class="btn btn-success">Update Student</button>
            </div>
        </form>
    </div>
</div>

{{-- Fee records --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Fee Records</h6>
    </div>
    <div class="card-body">
        @foreach($student->fees->sortByDesc(fn($f) => $f->academicSession->name) as $fee)
        <form method="POST" action="{{ route('admin.students.fees.update', [$student, $fee]) }}" class="border rounded p-3 mb-3">
            @csrf @method('PUT')
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">{{ $fee->academicSession->name }}</label>
                </div>
                <div class="col-md-2">
                    <div class="form-check"><input type="checkbox" name="school_fees_paid" class="form-check-input" value="1" {{ $fee->school_fees_paid ? 'checked' : '' }}><label class="form-check-label">School Fees</label></div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="school_fees_date_paid" class="form-control form-control-sm" value="{{ $fee->school_fees_date_paid?->format('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <div class="form-check"><input type="checkbox" name="departmental_dues_paid" class="form-check-input" value="1" {{ $fee->departmental_dues_paid ? 'checked' : '' }}><label class="form-check-label">Dept. Dues</label></div>
                </div>
                <div class="col-md-2">
                    <div class="form-check"><input type="checkbox" name="faculty_dues_paid" class="form-check-input" value="1" {{ $fee->faculty_dues_paid ? 'checked' : '' }}><label class="form-check-label">Faculty Dues</label></div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-outline-success w-100">Save</button>
                </div>
            </div>
        </form>
        @endforeach

        <hr>
        <h6>Add Fee for Session</h6>
        <form method="POST" action="{{ route('admin.students.fees.store', $student) }}">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="academic_session_id" class="form-select form-select-sm" required>
                        <option value="">Session</option>
                        @foreach($sessions as $sess)
                            <option value="{{ $sess->id }}">{{ $sess->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-check"><input type="checkbox" name="school_fees_paid" class="form-check-input" value="1"><label class="form-check-label">School Fees</label></div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="school_fees_date_paid" class="form-control form-control-sm" placeholder="Date Paid">
                </div>
                <div class="col-md-2">
                    <div class="form-check"><input type="checkbox" name="departmental_dues_paid" class="form-check-input" value="1"><label class="form-check-label">Dept. Dues</label></div>
                </div>
                <div class="col-md-2">
                    <div class="form-check"><input type="checkbox" name="faculty_dues_paid" class="form-check-input" value="1"><label class="form-check-label">Faculty Dues</label></div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-success w-100">Add</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
