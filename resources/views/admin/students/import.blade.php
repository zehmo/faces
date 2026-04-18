@extends('layouts.admin')
@section('title', 'Import Students')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Import Students</h4>
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
            <li>Download the <a href="{{ route('admin.students.import.template') }}"><strong>CSV template</strong></a> and fill in student data.</li>
            <li><strong>Required columns:</strong> <code>reg_number</code>, <code>full_name</code>, <code>department</code>, <code>level</code></li>
            <li><strong>Optional columns:</strong> <code>jamb_reg_number</code>, <code>date_of_birth</code> (YYYY-MM-DD), <code>sex</code> (Male/Female), <code>marital_status</code> (Single/Married), <code>state</code>, <code>lga</code>, <code>town</code>, <code>phone_number</code>, <code>email</code></li>
            <li>For photos: create a ZIP file containing images named by reg number (e.g., <code>CSC2025001.jpg</code>). Use reg number <strong>without slashes</strong>.</li>
            <li>Supported photo formats: JPG, JPEG, PNG</li>
            <li>Duplicate reg numbers are automatically skipped.</li>
        </ol>
        <a href="{{ route('admin.students.import.template') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> Download CSV Template</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold">CSV File <span class="text-danger">*</span></label>
                <input type="file" name="csv_file" class="form-control @error('csv_file') is-invalid @enderror" accept=".csv,.txt" required>
                @error('csv_file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Photos ZIP <span class="text-muted fw-normal">(optional)</span></label>
                <input type="file" name="photos_zip" class="form-control @error('photos_zip') is-invalid @enderror" accept=".zip">
                @error('photos_zip')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">ZIP containing photos named by reg number (without slashes), e.g. CSC2025001.jpg</small>
            </div>

            <button type="submit" class="btn btn-success"><i class="bi bi-upload"></i> Import Students</button>
        </form>
    </div>
</div>
@endsection
