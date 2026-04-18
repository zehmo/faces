<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Department;
use App\Models\AcademicSession;

class SyncController extends Controller
{
    public function sync(Request $request)
    {
        $since = $request->query('since'); // ISO 8601 timestamp

        $query = Student::with(['department', 'state', 'lga', 'town', 'fees.academicSession']);

        if ($since) {
            $query->where('updated_at', '>', $since);
        }

        // Include soft-deleted records so the app knows to remove them
        $students = $query->withTrashed()->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'reg_number' => $s->reg_number,
                'jamb_reg_number' => $s->jamb_reg_number,
                'full_name' => $s->full_name,
                'date_of_birth' => $s->date_of_birth?->toDateString(),
                'sex' => $s->sex,
                'marital_status' => $s->marital_status,
                'state' => $s->state->name ?? null,
                'lga' => $s->lga->name ?? null,
                'town' => $s->town->name ?? null,
                'phone_number' => $s->phone_number,
                'email' => $s->email,
                'department' => $s->department->name ?? null,
                'level' => $s->level,
                'photo_url' => $s->photo_filename
                    ? url('storage/photos/' . $s->photo_filename)
                    : null,
                'thumb_url' => $s->photo_filename
                    ? url('storage/photos/thumbs/' . $s->photo_filename)
                    : null,
                'fees' => $s->fees->map(fn($f) => [
                    'session' => $f->academicSession->name,
                    'school_fees_paid' => $f->school_fees_paid,
                    'school_fees_date_paid' => $f->school_fees_date_paid?->toDateString(),
                    'departmental_dues_paid' => $f->departmental_dues_paid,
                    'faculty_dues_paid' => $f->faculty_dues_paid,
                ]),
                'deleted' => !is_null($s->deleted_at),
                'updated_at' => $s->updated_at->toIso8601String(),
            ];
        });

        $currentSession = AcademicSession::current();

        return response()->json([
            'students' => $students,
            'current_session' => $currentSession?->name,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function departments()
    {
        return response()->json(Department::orderBy('name')->pluck('name'));
    }

    public function photo(string $filename)
    {
        $filename = basename($filename);
        $path = storage_path('app/public/photos/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        $mime = mime_content_type($path);
        return response()->file($path, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
