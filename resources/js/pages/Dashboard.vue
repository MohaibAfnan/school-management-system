<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BookOpen,
    Users,
    GraduationCap,
    CalendarCheck,
    UserCheck,
    Building2,
    TrendingUp,
    Plus,
    Eye,
    School,
} from '@lucide/vue';
import { dashboard } from '@/routes';

interface StatCard {
    title: string;
    value: number | string;
    icon: any;
    change?: string;
    trend?: 'up' | 'down';
}

const props = defineProps<{
    role: string;
    stats: Record<string, number | string>;
    recentStudents?: Array<any>;
    recentTeachers?: Array<any>;
    classOverview?: Array<any>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const getAdminStats = (): StatCard[] => [
    {
        title: 'Total Students',
        value: props.stats.totalStudents || 0,
        icon: GraduationCap,
        change: '+12%',
        trend: 'up',
    },
    {
        title: 'Total Teachers',
        value: props.stats.totalTeachers || 0,
        icon: Users,
        change: '+3%',
        trend: 'up',
    },
    {
        title: 'Total Classes',
        value: props.stats.totalClasses || 0,
        icon: School,
        change: '+2%',
        trend: 'up',
    },
    {
        title: 'Total Subjects/Courses',
        value: props.stats.totalCourses || 0,
        icon: BookOpen,
        change: '+5%',
        trend: 'up',
    },
];

const getTeacherStats = (): StatCard[] => [
    {
        title: 'My Courses',
        value: props.stats.myCourses || 0,
        icon: BookOpen,
    },
    {
        title: 'My Classes',
        value: props.stats.myClasses || 0,
        icon: School,
    },
    {
        title: 'Total Students',
        value: props.stats.totalStudents || 0,
        icon: GraduationCap,
    },
    {
        title: 'Total Classes',
        value: props.stats.totalClasses || 0,
        icon: Building2,
    },
];

const getStudentStats = (): StatCard[] => [
    {
        title: 'Enrolled Courses',
        value: props.stats.enrolledCourses || 0,
        icon: BookOpen,
    },
    {
        title: 'My Class',
        value: props.stats.myClass || 'N/A',
        icon: School,
    },
    {
        title: 'Classmates',
        value: props.stats.classmates || 0,
        icon: Users,
    },
    {
        title: 'Attendance Rate',
        value: `${props.stats.attendanceRate || 0}%`,
        icon: CalendarCheck,
    },
];

const statCards = props.role === 'admin' 
    ? getAdminStats() 
    : props.role === 'teacher' 
        ? getTeacherStats() 
        : getStudentStats();
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                {{ role === 'admin' ? 'School Management Dashboard' : role === 'teacher' ? 'Teacher Dashboard' : 'Student Portal' }}
            </h1>
            <p class="text-muted-foreground">
                {{ role === 'admin' ? 'Manage students, teachers, classes and school operations' : role === 'teacher' ? 'Manage your classes and students' : 'View your class information and academic progress' }}
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="card in statCards"
                :key="card.title"
                class="group relative overflow-hidden rounded-xl border bg-card p-6 shadow-sm transition-all hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <p class="text-sm font-medium text-muted-foreground">{{ card.title }}</p>
                        <p class="text-2xl font-bold tracking-tight md:text-3xl">{{ card.value }}</p>
                        <p v-if="card.change" class="flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400">
                            <TrendingUp class="h-3 w-3" />
                            {{ card.change }} from last month
                        </p>
                    </div>
                    <div class="rounded-full bg-primary/10 p-3">
                        <component :is="card.icon" class="h-5 w-5 text-primary" />
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div v-if="role === 'admin' && recentStudents" class="rounded-xl border bg-card shadow-sm">
                <div class="flex items-center justify-between border-b p-6">
                    <div>
                        <h2 class="text-lg font-semibold">Recent Students</h2>
                        <p class="text-sm text-muted-foreground">Recently enrolled students</p>
                    </div>
                    <Link href="#" class="flex items-center gap-1 text-sm text-primary hover:underline">
                        <Eye class="h-4 w-4" />
                        View All
                    </Link>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div
                            v-for="student in recentStudents"
                            :key="student.id"
                            class="flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-muted/50"
                        >
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                                    <GraduationCap class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="font-medium">{{ student.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ student.email }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium">{{ student.student_id }}</p>
                                <p class="text-sm text-muted-foreground">{{ student.class || 'Unassigned' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="role === 'admin' && recentTeachers" class="rounded-xl border bg-card shadow-sm">
                <div class="flex items-center justify-between border-b p-6">
                    <div>
                        <h2 class="text-lg font-semibold">Faculty Members</h2>
                        <p class="text-sm text-muted-foreground">Teaching staff</p>
                    </div>
                    <Link href="#" class="flex items-center gap-1 text-sm text-primary hover:underline">
                        <Plus class="h-4 w-4" />
                        Add Teacher
                    </Link>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div
                            v-for="teacher in recentTeachers"
                            :key="teacher.id"
                            class="flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-muted/50"
                        >
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400">
                                    <Users class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="font-medium">{{ teacher.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ teacher.email }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium">{{ teacher.teacher_id }}</p>
                                <p class="text-sm text-muted-foreground">{{ teacher.qualification || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="classOverview && classOverview.length > 0" :class="role === 'admin' ? 'lg:col-span-2' : ''" class="rounded-xl border bg-card shadow-sm">
                <div class="flex items-center justify-between border-b p-6">
                    <div>
                        <h2 class="text-lg font-semibold">{{ role === 'student' ? 'My Class Details' : 'Class Overview' }}</h2>
                        <p class="text-sm text-muted-foreground">{{ role === 'student' ? 'Class information' : 'Classes and enrollment' }}</p>
                    </div>
                    <Link v-if="role === 'admin'" href="#" class="flex items-center gap-1 text-sm text-primary hover:underline">
                        <Plus class="h-4 w-4" />
                        Manage Classes
                    </Link>
                </div>
                <div class="p-6">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="cls in classOverview"
                            :key="cls.id"
                            class="flex flex-col gap-3 rounded-lg border p-4 transition-colors hover:bg-muted/50"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                        <School class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ cls.name }}</p>
                                        <p class="text-xs text-muted-foreground">Grade {{ cls.grade }}</p>
                                    </div>
                                </div>
                                <span class="rounded-full bg-primary/10 px-2 py-1 text-xs font-medium text-primary">
                                    {{ cls.academic_year }}
                                </span>
                            </div>
                            <div v-if="cls.teacher" class="flex items-center gap-2 text-sm">
                                <UserCheck class="h-4 w-4 text-muted-foreground" />
                                <span class="text-muted-foreground">Class Teacher:</span>
                                <span class="font-medium">{{ cls.teacher }}</span>
                            </div>
                            <div v-if="cls.students_count !== undefined" class="flex items-center justify-between rounded-lg bg-muted/50 p-2">
                                <span class="text-sm text-muted-foreground">Total Students</span>
                                <span class="text-lg font-semibold">{{ cls.students_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border bg-gradient-to-br from-primary/5 to-primary/10 p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Quick Actions</h2>
                    <p class="text-sm text-muted-foreground">Common school management tasks</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button v-if="role === 'admin'" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
                        Register Student
                    </button>
                    <button v-if="role === 'admin'" class="rounded-lg border bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-muted">
                        Add Teacher
                    </button>
                    <button v-if="role === 'admin'" class="rounded-lg border bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-muted">
                        Create Class
                    </button>
                    <button v-if="role === 'teacher'" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
                        Mark Attendance
                    </button>
                    <button v-if="role === 'teacher'" class="rounded-lg border bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-muted">
                        Manage Students
                    </button>
                    <button v-if="role === 'student'" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
                        View Timetable
                    </button>
                    <button v-if="role === 'student'" class="rounded-lg border bg-background px-4 py-2 text-sm font-medium transition-colors hover:bg-muted">
                        View Results
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
