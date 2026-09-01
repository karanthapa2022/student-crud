<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {

            $table->id();

            $table->string('province')->nullable();

            $table->string('district')->nullable();

            $table->string('municipality')->nullable();

            $table->string('ward')->nullable();

            $table->string('city')->nullable();

            $table->string('street')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};