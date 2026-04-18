<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AcademicSession;

class DashboardController extends Controller
{
    public function index()
    {
        $totalStudents = Student::count();
        $activeStudents = Student::whereNull('deleted_at')->count();
        $currentSession = AcademicSession::current();

        return view('admin.dashboard', compact('totalStudents', 'activeStudents', 'currentSession'));
    }
}
