<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Department;
use App\Models\State;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with('department')
            ->when($request->search, function ($q, $search) {
                $q->where('reg_number', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });

        if ($request->department) {
            $query->where('department_id', $request->department);
        }
        if ($request->level) {
            $query->where('level', $request->level);
        }

        $students = $query->orderBy('reg_number')->paginate(50)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('admin.students.index', compact('students', 'departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $states = State::orderBy('name')->get();
        $sessions = AcademicSession::orderBy('name', 'desc')->get();

        return view('admin.students.create', compact('departments', 'states', 'sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reg_number' => 'required|string|max:50|unique:students,reg_number',
            'jamb_reg_number' => 'nullable|string|max:50',
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'sex' => 'required|in:Male,Female',
            'marital_status' => 'required|in:Single,Married',
            'state_id' => 'nullable|exists:states,id',
            'lga_id' => 'nullable|exists:lgas,id',
            'town_id' => 'nullable|exists:towns,id',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'level' => 'required|in:100,200,300,400',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
        ]);

        $photoFilename = '';
        if ($request->hasFile('photo')) {
            $photoFilename = $this->processPhoto($request->file('photo'));
        }

        $validated['photo_filename'] = $photoFilename;
        $student = Student::create($validated);

        // Create fee record if session selected
        if ($request->academic_session_id) {
            StudentFee::create([
                'student_id' => $student->id,
                'academic_session_id' => $request->academic_session_id,
                'school_fees_paid' => $request->boolean('school_fees_paid'),
                'school_fees_date_paid' => $request->school_fees_date_paid,
                'departmental_dues_paid' => $request->boolean('departmental_dues_paid'),
                'faculty_dues_paid' => $request->boolean('faculty_dues_paid'),
            ]);
        }

        return redirect()->route('admin.students.index')->with('success', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        $student->load(['department', 'state', 'lga', 'town', 'fees.academicSession']);
        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $student->load(['fees.academicSession']);
        $departments = Department::orderBy('name')->get();
        $states = State::orderBy('name')->get();
        $sessions = AcademicSession::orderBy('name', 'desc')->get();

        return view('admin.students.edit', compact('student', 'departments', 'states', 'sessions'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'reg_number' => 'required|string|max:50|unique:students,reg_number,' . $student->id,
            'jamb_reg_number' => 'nullable|string|max:50',
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'sex' => 'required|in:Male,Female',
            'marital_status' => 'required|in:Single,Married',
            'state_id' => 'nullable|exists:states,id',
            'lga_id' => 'nullable|exists:lgas,id',
            'town_id' => 'nullable|exists:towns,id',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'level' => 'required|in:100,200,300,400',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo
            $this->deletePhoto($student->photo_filename);
            $validated['photo_filename'] = $this->processPhoto($request->file('photo'));
        }

        $student->update($validated);

        return redirect()->route('admin.students.index')->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        $student->delete(); // soft-delete
        return redirect()->route('admin.students.index')->with('success', 'Student deleted successfully.');
    }

    public function restore($id)
    {
        $student = Student::withTrashed()->findOrFail($id);
        $student->restore();
        return redirect()->route('admin.students.index')->with('success', 'Student restored successfully.');
    }

    public function storeFee(Request $request, Student $student)
    {
        $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
        ]);

        StudentFee::updateOrCreate(
            [
                'student_id' => $student->id,
                'academic_session_id' => $request->academic_session_id,
            ],
            [
                'school_fees_paid' => $request->boolean('school_fees_paid'),
                'school_fees_date_paid' => $request->school_fees_date_paid,
                'departmental_dues_paid' => $request->boolean('departmental_dues_paid'),
                'faculty_dues_paid' => $request->boolean('faculty_dues_paid'),
            ]
        );

        return redirect()->route('admin.students.edit', $student)->with('success', 'Fee record saved.');
    }

    public function updateFee(Request $request, Student $student, StudentFee $fee)
    {
        $fee->update([
            'school_fees_paid' => $request->boolean('school_fees_paid'),
            'school_fees_date_paid' => $request->school_fees_date_paid,
            'departmental_dues_paid' => $request->boolean('departmental_dues_paid'),
            'faculty_dues_paid' => $request->boolean('faculty_dues_paid'),
        ]);

        return redirect()->route('admin.students.edit', $student)->with('success', 'Fee record updated.');
    }

    public function export(Request $request)
    {
        $query = Student::with(['department', 'state', 'lga', 'town', 'fees.academicSession']);

        if ($request->department) {
            $query->where('department_id', $request->department);
        }
        if ($request->level) {
            $query->where('level', $request->level);
        }

        $students = $query->orderBy('reg_number')->get();

        $filename = 'students_export_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($students) {
            $out = fopen('php://output', 'w');

            // CSV header row
            fputcsv($out, [
                'Reg Number', 'JAMB Reg', 'Full Name', 'Date of Birth', 'Sex',
                'Marital Status', 'State', 'LGA', 'Town', 'Phone', 'Email',
                'Department', 'Level',
                'School Fees Paid', 'Fees Date Paid',
                'Departmental Dues Paid', 'Faculty Dues Paid', 'Fee Session',
            ]);

            foreach ($students as $s) {
                $currentFee = $s->fees->first(); // most recent
                fputcsv($out, [
                    $s->reg_number,
                    $s->jamb_reg_number,
                    $s->full_name,
                    $s->date_of_birth?->format('Y-m-d'),
                    $s->sex,
                    $s->marital_status,
                    $s->state->name ?? '',
                    $s->lga->name ?? '',
                    $s->town->name ?? '',
                    $s->phone_number,
                    $s->email,
                    $s->department->name ?? '',
                    $s->level,
                    $currentFee?->school_fees_paid ? 'Yes' : 'No',
                    $currentFee?->school_fees_date_paid?->format('Y-m-d'),
                    $currentFee?->departmental_dues_paid ? 'Yes' : 'No',
                    $currentFee?->faculty_dues_paid ? 'Yes' : 'No',
                    $currentFee?->academicSession?->name ?? '',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function processPhoto($file): string
    {
        $filename = bin2hex(random_bytes(16)) . '.jpg';
        $photoDir = storage_path('app/public/photos');
        $thumbDir = storage_path('app/public/photos/thumbs');

        if (!is_dir($photoDir)) mkdir($photoDir, 0755, true);
        if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);

        $manager = new ImageManager(new Driver());

        // Compressed version: max 800x800, JPEG quality 70
        $image = $manager->read($file->getPathname());
        $image->scaleDown(800, 800);
        $image->toJpeg(70)->save($photoDir . '/' . $filename);

        // Thumbnail: 80x80, JPEG quality 60
        $thumb = $manager->read($file->getPathname());
        $thumb->cover(80, 80);
        $thumb->toJpeg(60)->save($thumbDir . '/' . $filename);

        return $filename;
    }

    private function deletePhoto(string $filename): void
    {
        if (!$filename) return;
        $photoPath = storage_path('app/public/photos/' . $filename);
        $thumbPath = storage_path('app/public/photos/thumbs/' . $filename);
        if (file_exists($photoPath)) unlink($photoPath);
        if (file_exists($thumbPath)) unlink($thumbPath);
    }
}
