<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove the existing foreign key first
        Schema::table('student_subject', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
        });

        // Make subject_id nullable
        Schema::table('student_subject', function (Blueprint $table) {
            $table->unsignedBigInteger('subject_id')->nullable()->change();
        });

        // Add the foreign key back
        Schema::table('student_subject', function (Blueprint $table) {
            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_subject', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
        });

        Schema::table('student_subject', function (Blueprint $table) {
            $table->unsignedBigInteger('subject_id')->nullable(false)->change();
        });

        Schema::table('student_subject', function (Blueprint $table) {
            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects')
                ->cascadeOnDelete();
        });
    }
};