@extends('layouts.admin')
@section('title', 'Add Student')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Add Student</h4>
    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary btn-sm">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.students._form')

            <hr class="my-4">
            <h6>Fee Record (Optional)</h6>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Session</label>
                    <select name="academic_session_id" class="form-select form-select-sm">
                        <option value="">— None —</option>
                        @foreach($sessions as $sess)
                            <option value="{{ $sess->id }}" {{ $sess->is_current ? 'selected' : '' }}>{{ $sess->name }}{{ $sess->is_current ? ' (Current)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">School Fees</label>
                    <div class="form-check mt-1"><input type="checkbox" name="school_fees_paid" class="form-check-input" value="1" id="sfp"><label for="sfp" class="form-check-label">Paid</label></div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date Paid</label>
                    <input type="date" name="school_fees_date_paid" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dept. Dues</label>
                    <div class="form-check mt-1"><input type="checkbox" name="departmental_dues_paid" class="form-check-input" value="1" id="ddp"><label for="ddp" class="form-check-label">Paid</label></div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Faculty Dues</label>
                    <div class="form-check mt-1"><input type="checkbox" name="faculty_dues_paid" class="form-check-input" value="1" id="fdp"><label for="fdp" class="form-check-label">Paid</label></div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">Save Student</button>
            </div>
        </form>
    </div>
</div>
@endsection
