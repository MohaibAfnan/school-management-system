<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 32)->unique();
            $table->uuid('installation_id')->unique();
            $table->string('status')->default('active');
            $table->string('deployment_mode')->default('hosted');
            $table->timestamps();
        });

        $now = now();
        $schoolId = DB::table('schools')->insertGetId([
            'name' => 'Development School',
            'slug' => 'development-school',
            'code' => 'DEMO',
            'installation_id' => (string) Str::uuid(),
            'status' => 'active',
            'deployment_mode' => 'hosted',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $tenantTables = [
            'users', 'students', 'teachers', 'class_rooms', 'courses',
            'enrollments', 'attendances', 'grades', 'lessons', 'assignments',
        ];

        foreach ($tenantTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            });

            DB::table($tableName)->whereNull('school_id')->update(['school_id' => $schoolId]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->index(['school_id', 'email']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['student_id']);
            $table->unique(['school_id', 'student_id']);
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique(['teacher_id']);
            $table->unique(['school_id', 'teacher_id']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->unique(['school_id', 'code']);
        });

        Schema::table('class_rooms', function (Blueprint $table) {
            $table->unique(['school_id', 'name', 'academic_year']);
        });

        Schema::create('school_smtp_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('host');
            $table->unsignedSmallInteger('port')->default(587);
            $table->string('scheme', 10)->default('smtp');
            $table->text('username')->nullable();
            $table->text('password')->nullable();
            $table->string('from_email');
            $table->string('from_name');
            $table->timestamp('last_tested_at')->nullable();
            $table->string('last_test_status')->nullable();
            $table->timestamps();
        });

        Schema::create('activation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->uuid('request_id')->unique();
            $table->uuid('installation_id');
            $table->string('requested_by_email');
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'created_at']);
        });

        Schema::create('school_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->uuid('license_id')->unique();
            $table->uuid('installation_id');
            $table->string('plan');
            $table->json('features')->nullable();
            $table->string('token_digest', 64)->unique();
            $table->timestamp('issued_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_licenses');
        Schema::dropIfExists('activation_requests');
        Schema::dropIfExists('school_smtp_settings');

        Schema::table('class_rooms', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'name', 'academic_year']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'code']);
            $table->unique('code');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'teacher_id']);
            $table->unique('teacher_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'student_id']);
            $table->unique('student_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['school_id', 'email']);
        });

        foreach (['users', 'students', 'teachers', 'class_rooms', 'courses', 'enrollments', 'attendances', 'grades', 'lessons', 'assignments'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('school_id');
            });
        }

        Schema::dropIfExists('schools');
    }
};
