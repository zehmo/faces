<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Department;
use App\Models\State;
use App\Models\Lga;
use App\Models\Town;
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
            'town' => 'nullable|string|max:100',
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
        $student->load(['department', 'state', 'lga', 'fees.academicSession']);
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
            'town' => 'nullable|string|max:100',
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
        $query = Student::with(['department', 'state', 'lga', 'fees.academicSession']);

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
                    $s->town ?? '',
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

    public function importForm()
    {
        return view('admin.students.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'photos_zip' => 'nullable|file|mimes:zip|max:102400',
        ]);

        // Parse CSV
        $csvPath = $request->file('csv_file')->getPathname();
        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            return back()->with('error', 'Could not read CSV file.');
        }

        // Read header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'CSV file is empty.');
        }

        // Normalize header names (lowercase, trim, underscores)
        $header = array_map(function ($h) {
            return strtolower(trim(str_replace(' ', '_', $h)));
        }, $header);

        $required = ['reg_number', 'full_name', 'department', 'level'];
        $missing = array_diff($required, $header);
        if (!empty($missing)) {
            fclose($handle);
            return back()->with('error', 'CSV missing required columns: ' . implode(', ', $missing));
        }

        // Extract photos from ZIP if provided
        $photoMap = [];
        $tempZipDir = null;
        if ($request->hasFile('photos_zip')) {
            $zip = new \ZipArchive();
            $tempZipDir = storage_path('app/temp_import_' . time());
            mkdir($tempZipDir, 0755, true);

            if ($zip->open($request->file('photos_zip')->getPathname()) === true) {
                $zip->extractTo($tempZipDir);
                $zip->close();

                // Build map: reg_number (without extension) => file path
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tempZipDir));
                foreach ($iterator as $file) {
                    if ($file->isFile() && in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png'])) {
                        $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                        $photoMap[strtoupper(trim($name))] = $file->getPathname();
                    }
                }
            }
        }

        // Cache lookups
        $departments = Department::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);
        $states = State::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) < count($header)) {
                $row = array_pad($row, count($header), '');
            }
            $data = array_combine($header, array_slice($row, 0, count($header)));

            $regNumber = trim($data['reg_number'] ?? '');
            $fullName = trim($data['full_name'] ?? '');

            if (!$regNumber || !$fullName) {
                $skipped++;
                $errors[] = "Row {$rowNum}: Missing reg_number or full_name — skipped.";
                continue;
            }

            // Skip duplicates
            if (Student::where('reg_number', $regNumber)->exists()) {
                $skipped++;
                $errors[] = "Row {$rowNum}: Reg number '{$regNumber}' already exists — skipped.";
                continue;
            }

            // Resolve department
            $deptId = null;
            if (!empty($data['department'])) {
                $deptId = $departments[strtolower(trim($data['department']))] ?? null;
            }

            // Resolve state
            $stateId = null;
            if (!empty($data['state'])) {
                $stateId = $states[strtolower(trim($data['state']))] ?? null;
            }

            // Resolve LGA
            $lgaId = null;
            if (!empty($data['lga']) && $stateId) {
                $lgaId = Lga::where('state_id', $stateId)
                    ->whereRaw('LOWER(name) = ?', [strtolower(trim($data['lga']))])
                    ->value('id');
            }

            // Process photo if available
            $photoFilename = '';
            $regKey = strtoupper(trim($regNumber));
            if (isset($photoMap[$regKey])) {
                try {
                    $photoFilename = $this->processPhotoFromPath($photoMap[$regKey]);
                } catch (\Exception $e) {
                    // Photo failed, continue without it
                }
            }

            $level = trim($data['level'] ?? '100');
            if (!in_array($level, ['100', '200', '300', '400'])) {
                $level = '100';
            }

            $sex = ucfirst(strtolower(trim($data['sex'] ?? 'Male')));
            if (!in_array($sex, ['Male', 'Female'])) $sex = 'Male';

            $maritalStatus = ucfirst(strtolower(trim($data['marital_status'] ?? 'Single')));
            if (!in_array($maritalStatus, ['Single', 'Married'])) $maritalStatus = 'Single';

            Student::create([
                'reg_number' => $regNumber,
                'jamb_reg_number' => trim($data['jamb_reg_number'] ?? ''),
                'full_name' => $fullName,
                'date_of_birth' => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
                'sex' => $sex,
                'marital_status' => $maritalStatus,
                'state_id' => $stateId,
                'lga_id' => $lgaId,
                'town' => trim($data['town'] ?? ''),
                'phone_number' => trim($data['phone_number'] ?? $data['phone'] ?? ''),
                'email' => trim($data['email'] ?? ''),
                'department_id' => $deptId,
                'level' => $level,
                'photo_filename' => $photoFilename,
            ]);

            $imported++;
        }

        fclose($handle);

        // Cleanup temp zip directory
        if ($tempZipDir && is_dir($tempZipDir)) {
            $this->deleteDirectory($tempZipDir);
        }

        $message = "{$imported} students imported successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} rows skipped.";
        }

        return redirect()->route('admin.students.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_import_template.csv"',
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'reg_number', 'jamb_reg_number', 'full_name', 'date_of_birth',
                'sex', 'marital_status', 'state', 'lga', 'town',
                'phone_number', 'email', 'department', 'level',
            ]);
            // Sample row
            fputcsv($out, [
                'CSC/2025/001', 'JAMB/2025/12345678', 'John Doe', '2000-01-15',
                'Male', 'Single', 'Lagos', 'Ikeja', 'Oregun',
                '08012345678', 'john@example.com', 'Computer Science', '100',
            ]);
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function feeImportForm()
    {
        $sessions = AcademicSession::orderBy('name', 'desc')->get();
        return view('admin.students.fee_import', compact('sessions'));
    }

    public function feeImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'fee_type' => 'required|in:school_fees,departmental_dues,faculty_dues,all',
        ]);

        $sessionId = $request->academic_session_id;
        $feeType = $request->fee_type;

        $csvPath = $request->file('csv_file')->getPathname();
        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            return back()->with('error', 'Could not read CSV file.');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->with('error', 'CSV file is empty.');
        }

        $header = array_map(function ($h) {
            return strtolower(trim(str_replace(' ', '_', $h)));
        }, $header);

        if (!in_array('reg_number', $header)) {
            fclose($handle);
            return back()->with('error', 'CSV must have a "reg_number" column.');
        }

        $updated = 0;
        $notFound = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) < count($header)) {
                $row = array_pad($row, count($header), '');
            }
            $data = array_combine($header, array_slice($row, 0, count($header)));

            $regNumber = trim($data['reg_number'] ?? '');
            if (!$regNumber) continue;

            $student = Student::where('reg_number', $regNumber)->first();
            if (!$student) {
                $notFound++;
                $errors[] = "Row {$rowNum}: Reg number '{$regNumber}' not found — skipped.";
                continue;
            }

            // Determine what to mark as paid
            $feeData = [];
            if ($feeType === 'school_fees' || $feeType === 'all') {
                $paidValue = $data['school_fees_paid'] ?? $data['paid'] ?? 'Yes';
                $feeData['school_fees_paid'] = in_array(strtolower(trim($paidValue)), ['yes', '1', 'true', 'paid']);
                $feeData['school_fees_date_paid'] = !empty($data['date_paid']) ? $data['date_paid'] : null;
            }
            if ($feeType === 'departmental_dues' || $feeType === 'all') {
                $paidValue = $data['departmental_dues_paid'] ?? $data['paid'] ?? 'Yes';
                $feeData['departmental_dues_paid'] = in_array(strtolower(trim($paidValue)), ['yes', '1', 'true', 'paid']);
            }
            if ($feeType === 'faculty_dues' || $feeType === 'all') {
                $paidValue = $data['faculty_dues_paid'] ?? $data['paid'] ?? 'Yes';
                $feeData['faculty_dues_paid'] = in_array(strtolower(trim($paidValue)), ['yes', '1', 'true', 'paid']);
            }

            StudentFee::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_session_id' => $sessionId,
                ],
                $feeData
            );

            $updated++;
        }

        fclose($handle);

        $message = "{$updated} fee records updated successfully.";
        if ($notFound > 0) {
            $message .= " {$notFound} reg numbers not found.";
        }

        return redirect()->route('admin.students.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    public function feeTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fee_import_template.csv"',
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['reg_number', 'paid', 'date_paid']);
            fputcsv($out, ['CSC/2025/001', 'Yes', '2026-01-15']);
            fputcsv($out, ['CSC/2025/002', 'Yes', '']);
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function processPhotoFromPath(string $path): string
    {
        $filename = bin2hex(random_bytes(16)) . '.jpg';
        $photoDir = storage_path('app/public/photos');
        $thumbDir = storage_path('app/public/photos/thumbs');

        if (!is_dir($photoDir)) mkdir($photoDir, 0755, true);
        if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);

        $manager = new ImageManager(new Driver());

        $image = $manager->read($path);
        $image->scaleDown(800, 800);
        $image->toJpeg(70)->save($photoDir . '/' . $filename);

        $thumb = $manager->read($path);
        $thumb->cover(80, 80);
        $thumb->toJpeg(60)->save($thumbDir . '/' . $filename);

        return $filename;
    }

    private function deleteDirectory(string $dir): void
    {
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
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
