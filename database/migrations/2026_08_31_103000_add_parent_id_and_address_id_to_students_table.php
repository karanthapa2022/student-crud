<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {

            $table->foreignId('parent_id')
                ->nullable()
                ->after('id')
                ->constrained('parents')
                ->nullOnDelete();

            $table->foreignId('address_id')
                ->nullable()
                ->after('parent_id')
                ->constrained('addresses')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {

            $table->dropForeign([
                'parent_id'
            ]);

            $table->dropForeign([
                'address_id'
            ]);

            $table->dropColumn([
                'parent_id',
                'address_id'
            ]);
        });
    }
};