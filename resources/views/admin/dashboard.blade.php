@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<h4 class="mb-4">Dashboard</h4>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-stat">
            <div class="card-body">
                <h6 class="text-muted">Total Students</h6>
                <h2 class="fw-bold" style="color:#2e7d32;">{{ number_format($totalStudents) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat">
            <div class="card-body">
                <h6 class="text-muted">Active Students</h6>
                <h2 class="fw-bold" style="color:#2e7d32;">{{ number_format($activeStudents) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat">
            <div class="card-body">
                <h6 class="text-muted">Current Session</h6>
                <h2 class="fw-bold" style="color:#2e7d32;">{{ $currentSession->name ?? 'Not Set' }}</h2>
            </div>
        </div>
    </div>
</div>
@endsection
