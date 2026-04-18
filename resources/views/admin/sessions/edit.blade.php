@extends('layouts.admin')
@section('title', 'Edit Session')

@section('content')
<h4 class="mb-3">Edit Academic Session</h4>

<div class="card" style="max-width:500px;">
    <div class="card-body">
        <form action="{{ route('admin.sessions.update', $session) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Session Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $session->name) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_current" value="1" class="form-check-input" id="isCurrent" {{ old('is_current', $session->is_current) ? 'checked' : '' }}>
                <label class="form-check-label" for="isCurrent">Current session</label>
            </div>
            <button class="btn btn-success">Update</button>
            <a href="{{ route('admin.sessions.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
