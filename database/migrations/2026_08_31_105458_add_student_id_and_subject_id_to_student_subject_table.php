<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_subject', function (Blueprint $table) {

            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('student_subject', function (Blueprint $table) {

            $table->dropForeign(['student_id']);
            $table->dropForeign(['subject_id']);

            $table->dropColumn([
                'student_id',
                'subject_id',
            ]);

        });
    }
};