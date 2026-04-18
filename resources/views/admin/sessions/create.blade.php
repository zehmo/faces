@extends('layouts.admin')
@section('title', 'Add Session')

@section('content')
<h4 class="mb-3">Add Academic Session</h4>

<div class="card" style="max-width:500px;">
    <div class="card-body">
        <form action="{{ route('admin.sessions.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Session Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. 2025/2026" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_current" value="1" class="form-check-input" id="isCurrent" {{ old('is_current') ? 'checked' : '' }}>
                <label class="form-check-label" for="isCurrent">Set as current session</label>
            </div>
            <button class="btn btn-success">Create</button>
            <a href="{{ route('admin.sessions.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
