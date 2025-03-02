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
        Schema::create('professors_data', function (Blueprint $table) {
            $table->id();
            $table->string('position');
            $table->string('company');
            $table->enum('gender', ['male', 'female']);
            $table->date('birth_date');
            $table->integer('work_experience_years');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professors_data');
    }
};
