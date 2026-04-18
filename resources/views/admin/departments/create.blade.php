@extends('layouts.admin')
@section('title', isset($department) ? 'Edit Department' : 'Add Department')

@section('content')
<h4 class="mb-3">{{ isset($department) ? 'Edit Department' : 'Add Department' }}</h4>

<div class="card" style="max-width:500px;">
    <div class="card-body">
        <form action="{{ isset($department) ? route('admin.departments.update', $department) : route('admin.departments.store') }}" method="POST">
            @csrf
            @if(isset($department)) @method('PUT') @endif
            <div class="mb-3">
                <label class="form-label">Department Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $department->name ?? '') }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <button class="btn btn-success">{{ isset($department) ? 'Update' : 'Create' }}</button>
            <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
