<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $role = $user->role?->slug;

        $stats = [];
        $recentStudents = [];
        $classOverview = [];
        $recentTeachers = [];

        if ($role === 'admin') {
            $stats = [
                'totalStudents' => Student::count(),
                'totalTeachers' => Teacher::count(),
                'totalClasses' => ClassRoom::count(),
                'totalCourses' => Course::count(),
            ];
            $recentStudents = Student::with('user', 'classRoom')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->user->name,
                    'email' => $s->user->email,
                    'student_id' => $s->student_id,
                    'class' => $s->classRoom?->name,
                ]);
            $recentTeachers = Teacher::with('user')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->user->name,
                    'email' => $t->user->email,
                    'teacher_id' => $t->teacher_id,
                    'qualification' => $t->qualification,
                ]);
            $classOverview = ClassRoom::with('teacher.user', 'students')
                ->withCount('students')
                ->take(6)
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'grade' => $c->grade_level,
                    'teacher' => $c->teacher?->user->name,
                    'students_count' => $c->students_count,
                    'academic_year' => $c->academic_year,
                ]);
        } elseif ($role === 'teacher') {
            $teacher = $user->teacher;
            if ($teacher) {
                $courseIds = $teacher->courses()->pluck('id');
                $stats = [
                    'myCourses' => $teacher->courses()->count(),
                    'myClasses' => $teacher->classRooms()->count(),
                    'totalStudents' => Student::whereHas('courses', fn ($q) => $q->whereIn('courses.id', $courseIds))->distinct()->count(),
                    'totalClasses' => $teacher->classRooms()->count(),
                ];
                $classOverview = $teacher->classRooms()->withCount('students')->get()->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'grade' => $c->grade_level,
                    'students_count' => $c->students_count,
                    'academic_year' => $c->academic_year,
                ]);
            }
        } elseif ($role === 'student') {
            $student = $user->student;
            if ($student) {
                $stats = [
                    'enrolledCourses' => $student->courses()->count(),
                    'myClass' => $student->classRoom?->name ?? 'N/A',
                    'classmates' => $student->classRoom ? Student::where('class_room_id', $student->classRoom_id)->where('id', '!=', $student->id)->count() : 0,
                    'attendanceRate' => 92,
                ];
                $classOverview = $student->classRoom ? [[
                    'id' => $student->classRoom->id,
                    'name' => $student->classRoom->name,
                    'grade' => $student->classRoom->grade_level,
                    'teacher' => $student->classRoom->teacher?->user->name,
                    'academic_year' => $student->classRoom->academic_year,
                ]] : [];
            }
        }

        return Inertia::render('Dashboard', [
            'role' => $role,
            'stats' => $stats,
            'recentStudents' => $recentStudents,
            'recentTeachers' => $recentTeachers,
            'classOverview' => $classOverview,
        ]);
    }
}
