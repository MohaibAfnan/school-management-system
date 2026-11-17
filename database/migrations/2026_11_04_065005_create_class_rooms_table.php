<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('grade_level');
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('academic_year');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_rooms');
    }
};
