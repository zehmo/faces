@extends('layouts.admin')
@section('title', 'Import Fees')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Import Fees (CSV)</h4>
    <a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

@if(session('import_errors'))
<div class="alert alert-warning">
    <strong>Import Warnings:</strong>
    <ul class="mb-0 mt-1">
        @foreach(session('import_errors') as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <h6 class="card-title">Instructions</h6>
        <ol class="mb-2">
            <li>Download the <a href="{{ route('admin.students.fee.template') }}"><strong>fee CSV template</strong></a>.</li>
            <li>Fill in the <code>reg_number</code> column with each student's registration number.</li>
            <li>The <code>paid</code> column should be <strong>Yes</strong> or <strong>No</strong>.</li>
            <li>Select the <strong>academic session</strong> and <strong>fee type</strong> to update.</li>
            <li>If fee type is <strong>"All Fees"</strong>, you can use separate columns: <code>school_fees_paid</code>, <code>departmental_dues_paid</code>, <code>faculty_dues_paid</code> — or just <code>paid</code> to set all three.</li>
        </ol>
        <a href="{{ route('admin.students.fee.template') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> Download Fee CSV Template</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.students.fee.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold">Academic Session <span class="text-danger">*</span></label>
                <select name="academic_session_id" class="form-select @error('academic_session_id') is-invalid @enderror" required>
                    <option value="">Select session...</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ $session->is_current ? 'selected' : '' }}>
                            {{ $session->name }} {{ $session->is_current ? '(Current)' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('academic_session_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Fee Type <span class="text-danger">*</span></label>
                <select name="fee_type" class="form-select @error('fee_type') is-invalid @enderror" required>
                    <option value="school_fees">School Fees</option>
                    <option value="departmental_dues">Departmental Dues</option>
                    <option value="faculty_dues">Faculty Dues</option>
                    <option value="all">All Fees</option>
                </select>
                @error('fee_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Choose which fee to mark as paid for the listed students.</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">CSV File <span class="text-danger">*</span></label>
                <input type="file" name="csv_file" class="form-control @error('csv_file') is-invalid @enderror" accept=".csv,.txt" required>
                @error('csv_file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-success"><i class="bi bi-upload"></i> Import Fees</button>
        </form>
    </div>
</div>
@endsection
