<?php

namespace Database\Seeders;

use App\Models\LeaderProfile;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@daruso.local'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
            ]
        );
        $admin->roles()->sync(Role::where('name', 'admin')->pluck('id'));

        $secretary = User::firstOrCreate(
            ['email' => 'secretary@daruso.local'],
            [
                'name' => 'Secretary General',
                'password' => Hash::make('password'),
            ]
        );
        $secretary->roles()->sync(Role::where('name', 'secretary_general')->pluck('id'));
        LeaderProfile::firstOrCreate(['user_id' => $secretary->id]);

        $ministryLeader = User::firstOrCreate(
            ['email' => 'ministry@daruso.local'],
            [
                'name' => 'Ministry Leader',
                'password' => Hash::make('password'),
            ]
        );
        $ministryLeader->roles()->sync(Role::where('name', 'ministry_leader')->pluck('id'));
        LeaderProfile::firstOrCreate(['user_id' => $ministryLeader->id]);

        $student = User::firstOrCreate(
            ['email' => 'student@daruso.local'],
            [
                'name' => 'John Student',
                'password' => Hash::make('password'),
            ]
        );
        $student->roles()->sync(Role::where('name', 'student')->pluck('id'));
        StudentProfile::firstOrCreate(
            ['user_id' => $student->id],
            [
                'registration_number' => 'REG-2025-001',
                'college' => 'College of Engineering',
                'school_faculty' => 'School of Engineering',
                'programme' => 'Computer Science',
                'year_of_study' => 2,
                'hostel' => 'Hostel A',
                'gender' => 'male',
                'status' => 'active',
            ]
        );

        $students = [
            ['name' => 'Alice Johnson', 'email' => 'alice@daruso.local', 'reg' => 'REG-2025-002', 'programme' => 'Computer Science', 'year' => 2, 'hostel' => 'Hostel A'],
            ['name' => 'Bob Smith', 'email' => 'bob@daruso.local', 'reg' => 'REG-2025-003', 'programme' => 'Electrical Engineering', 'year' => 3, 'hostel' => 'Hostel B'],
            ['name' => 'Carol White', 'email' => 'carol@daruso.local', 'reg' => 'REG-2025-004', 'programme' => 'Mechanical Engineering', 'year' => 1, 'hostel' => 'Hostel A'],
            ['name' => 'David Brown', 'email' => 'david@daruso.local', 'reg' => 'REG-2025-005', 'programme' => 'Civil Engineering', 'year' => 4, 'hostel' => 'Hostel C'],
        ];

        foreach ($students as $s) {
            $user = User::firstOrCreate(
                ['email' => $s['email']],
                [
                    'name' => $s['name'],
                    'password' => Hash::make('password'),
                ]
            );
            $user->roles()->sync(Role::where('name', 'student')->pluck('id'));
            StudentProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'registration_number' => $s['reg'],
                    'college' => 'College of Engineering',
                    'school_faculty' => 'School of Engineering',
                    'programme' => $s['programme'],
                    'year_of_study' => $s['year'],
                    'hostel' => $s['hostel'],
                    'status' => 'active',
                ]
            );
        }
    }
}
