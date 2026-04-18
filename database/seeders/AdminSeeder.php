<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AcademicSession;
use App\Models\Department;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'full_name' => 'System Administrator',
                'password' => Hash::make('admin123'),
            ]
        );

        AcademicSession::firstOrCreate(
            ['name' => '2025/2026'],
            ['is_current' => true]
        );

        $departments = [
            'Computer Science',
            'Cybersecurity',
            'Software Engineering',
            'Information Technology',
            'Information System',
            'Data Science',
        ];

        foreach ($departments as $name) {
            Department::firstOrCreate(['name' => $name]);
        }
    }
}
