<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Student;
use Illuminate\Http\Request;

class AcademicSessionController extends Controller
{
    public function index()
    {
        $sessions = AcademicSession::orderBy('name', 'desc')->get();
        return view('admin.sessions.index', compact('sessions'));
    }

    public function create()
    {
        return view('admin.sessions.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:20|unique:academic_sessions,name']);
        AcademicSession::create($request->only('name'));
        return redirect()->route('admin.sessions.index')->with('success', 'Session created.');
    }

    public function edit(AcademicSession $session)
    {
        return view('admin.sessions.edit', compact('session'));
    }

    public function update(Request $request, AcademicSession $session)
    {
        $request->validate(['name' => 'required|string|max:20|unique:academic_sessions,name,' . $session->id]);
        $session->update($request->only('name'));
        return redirect()->route('admin.sessions.index')->with('success', 'Session updated.');
    }

    public function destroy(AcademicSession $session)
    {
        if ($session->is_current) {
            return redirect()->route('admin.sessions.index')->with('error', 'Cannot delete the current session.');
        }
        $session->delete();
        return redirect()->route('admin.sessions.index')->with('success', 'Session deleted.');
    }

    public function setCurrent(AcademicSession $session)
    {
        AcademicSession::where('is_current', true)->update(['is_current' => false]);
        $session->update(['is_current' => true]);
        return redirect()->route('admin.sessions.index')->with('success', "Session '{$session->name}' set as current.");
    }

    public function rollover(Request $request)
    {
        $levelMap = ['100' => '200', '200' => '300', '300' => '400'];

        $promoted = 0;
        Student::whereIn('level', ['100', '200', '300'])->chunkById(200, function ($students) use ($levelMap, &$promoted) {
            foreach ($students as $student) {
                $student->update(['level' => $levelMap[$student->level]]);
                $promoted++;
            }
        });

        return redirect()->route('admin.sessions.index')->with('success', "{$promoted} students promoted to next level.");
    }
}
