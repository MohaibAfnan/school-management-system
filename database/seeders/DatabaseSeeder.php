<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Role;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Tenancy\TenantContext;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $school = School::query()->firstOrCreate(
            ['code' => 'DEMO'],
            [
                'name' => 'Development School',
                'slug' => 'development-school',
                'installation_id' => (string) \Illuminate\Support\Str::uuid(),
                'status' => 'active',
                'deployment_mode' => 'hosted',
            ],
        );

        if (app()->environment('local') && $school->deployment_mode !== 'local') {
            $school->forceFill(['deployment_mode' => 'local'])->save();
        }

        app(TenantContext::class)->set($school);

        $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Administrator', 'permissions' => ['all']]);
        $teacherRole = Role::firstOrCreate(['slug' => 'teacher'], ['name' => 'Teacher', 'permissions' => ['manage_courses', 'manage_grades']]);
        $studentRole = Role::firstOrCreate(['slug' => 'student'], ['name' => 'Student', 'permissions' => ['view_courses']]);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@school.test',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
        ]);

        $teacherUser = User::factory()->create([
            'name' => 'John Smith',
            'email' => 'teacher@school.test',
            'password' => Hash::make('password'),
            'role_id' => $teacherRole->id,
            'email_verified_at' => now(),
        ]);

        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'teacher_id' => 'TCH-001',
            'phone' => '+1234567890',
            'qualification' => 'M.Ed.',
        ]);

        $classRoom = ClassRoom::create([
            'name' => 'Grade 10 - A',
            'grade_level' => '10',
            'teacher_id' => $teacher->id,
            'academic_year' => '2026-2027',
        ]);

        $course = Course::create([
            'code' => 'MATH101',
            'name' => 'Mathematics',
            'description' => 'Advanced Mathematics for Grade 10',
            'teacher_id' => $teacher->id,
            'class_room_id' => $classRoom->id,
            'credits' => 3,
        ]);

        $studentUser = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'student@school.test',
            'password' => Hash::make('password'),
            'role_id' => $studentRole->id,
            'email_verified_at' => now(),
        ]);

        Student::create([
            'user_id' => $studentUser->id,
            'student_id' => 'STU-001',
            'dob' => '2010-05-15',
            'gender' => 'female',
            'phone' => '+1234567891',
            'class_room_id' => $classRoom->id,
            'guardian_name' => 'Robert Doe',
            'guardian_phone' => '+1234567892',
        ]);
    }
}
