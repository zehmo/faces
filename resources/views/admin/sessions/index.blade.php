@extends('layouts.admin')
@section('title', 'Academic Sessions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Academic Sessions</h4>
    <a href="{{ route('admin.sessions.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i> Add Session</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Name</th><th>Status</th><th style="width:260px;">Actions</th></tr></thead>
            <tbody>
                @forelse($sessions as $session)
                <tr>
                    <td>{{ $session->name }}</td>
                    <td>
                        @if($session->is_current)
                            <span class="badge badge-paid">Current</span>
                        @endif
                    </td>
                    <td>
                        @unless($session->is_current)
                        <form action="{{ route('admin.sessions.set-current', $session) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-success">Set Current</button>
                        </form>
                        @endunless
                        <a href="{{ route('admin.sessions.edit', $session) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('admin.sessions.destroy', $session) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this session?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-muted text-center py-3">No sessions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    <form action="{{ route('admin.sessions.rollover') }}" method="POST" id="rolloverForm">
        @csrf
        <button type="button" class="btn btn-warning" onclick="confirmRollover()"><i class="bi bi-arrow-up-circle"></i> Session Rollover (Promote Levels)</button>
    </form>
</div>

<!-- Rollover confirmation modal -->
<div class="modal fade" id="rolloverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirm Rollover</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="fw-bold text-danger">This action will promote ALL students to the next level:</p>
                <ul>
                    <li>100 Level &rarr; 200 Level</li>
                    <li>200 Level &rarr; 300 Level</li>
                    <li>300 Level &rarr; 400 Level</li>
                    <li>400 Level students remain at 400</li>
                </ul>
                <p class="mb-1">To confirm, type <strong>PROMOTE</strong> below:</p>
                <input type="text" id="rolloverConfirmInput" class="form-control" placeholder="Type PROMOTE to confirm" autocomplete="off">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="rolloverSubmitBtn" disabled onclick="document.getElementById('rolloverForm').submit()">Promote Students</button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmRollover() {
    var modal = new bootstrap.Modal(document.getElementById('rolloverModal'));
    document.getElementById('rolloverConfirmInput').value = '';
    document.getElementById('rolloverSubmitBtn').disabled = true;
    modal.show();
}
document.getElementById('rolloverConfirmInput').addEventListener('input', function() {
    document.getElementById('rolloverSubmitBtn').disabled = this.value.trim() !== 'PROMOTE';
});
</script>
@endsection
