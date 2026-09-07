<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('marksheet_items', function (Blueprint $table) {

            // Make subject_id optional for manually entered subjects
            $table->foreignId('subject_id')
                ->nullable()
                ->change();

            // Store the subject directly on the marksheet
            $table->string('subject_name')->after('subject_id');

            // Allow different marking schemes for different subjects
            $table->decimal('full_marks', 8, 2)
                ->default(100)
                ->after('subject_name');

            $table->decimal('pass_marks', 8, 2)
                ->default(40)
                ->after('full_marks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marksheet_items', function (Blueprint $table) {

            $table->dropColumn([
                'subject_name',
                'full_marks',
                'pass_marks',
            ]);

            $table->foreignId('subject_id')
                ->nullable(false)
                ->change();
        });
    }
};