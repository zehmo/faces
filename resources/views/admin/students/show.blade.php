@extends('layouts.admin')
@section('title', 'Student Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Student Details</h4>
    <div>
        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-secondary">Back to List</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 text-center">
                @if($student->photo_filename)
                    <img src="{{ asset('storage/photos/' . $student->photo_filename) }}" class="rounded-circle mb-3" style="width:160px;height:160px;object-fit:cover;" alt="Photo">
                @else
                    <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-3" style="width:160px;height:160px;font-size:3rem;"><i class="bi bi-person"></i></div>
                @endif
            </div>
            <div class="col-md-9">
                <table class="table table-borderless">
                    <tr><th style="width:200px;">Reg Number</th><td class="fw-bold">{{ $student->reg_number }}</td></tr>
                    <tr><th>JAMB Reg Number</th><td>{{ $student->jamb_reg_number ?? '—' }}</td></tr>
                    <tr><th>Full Name</th><td>{{ $student->full_name }}</td></tr>
                    <tr><th>Date of Birth</th><td>{{ $student->date_of_birth?->format('d M Y') ?? '—' }}</td></tr>
                    <tr><th>Sex</th><td>{{ $student->sex }}</td></tr>
                    <tr><th>Marital Status</th><td>{{ $student->marital_status }}</td></tr>
                    <tr><th>State of Origin</th><td>{{ $student->state->name ?? '—' }}</td></tr>
                    <tr><th>L.G.A.</th><td>{{ $student->lga->name ?? '—' }}</td></tr>
                    <tr><th>Town</th><td>{{ $student->town ?? '—' }}</td></tr>
                    <tr><th>Phone</th><td>{{ $student->phone_number ?? '—' }}</td></tr>
                    <tr><th>Email</th><td>{{ $student->email ?? '—' }}</td></tr>
                    <tr><th>Department</th><td>{{ $student->department->name ?? '—' }}</td></tr>
                    <tr><th>Level</th><td>{{ $student->level }}L</td></tr>
                </table>
            </div>
        </div>

        <hr>
        <h6>Fee Records</h6>
        <table class="table table-sm">
            <thead><tr><th>Session</th><th>School Fees</th><th>Date Paid</th><th>Dept. Dues</th><th>Faculty Dues</th></tr></thead>
            <tbody>
                @forelse($student->fees->sortByDesc(fn($f) => $f->academicSession->name) as $fee)
                <tr>
                    <td>{{ $fee->academicSession->name }}</td>
                    <td><span class="badge {{ $fee->school_fees_paid ? 'badge-paid' : 'badge-unpaid' }}">{{ $fee->school_fees_paid ? 'PAID' : 'NOT PAID' }}</span></td>
                    <td>{{ $fee->school_fees_date_paid?->format('d M Y') ?? '—' }}</td>
                    <td><span class="badge {{ $fee->departmental_dues_paid ? 'badge-paid' : 'badge-unpaid' }}">{{ $fee->departmental_dues_paid ? 'PAID' : 'NOT PAID' }}</span></td>
                    <td><span class="badge {{ $fee->faculty_dues_paid ? 'badge-paid' : 'badge-unpaid' }}">{{ $fee->faculty_dues_paid ? 'PAID' : 'NOT PAID' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-muted">No fee records.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
