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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedBigInteger('base_unit_id')->nullable();
            $table->boolean('status')->default(true); // Added status column
            $table->softDeletes(); // Adds deleted_at column for soft deletes
            $table->timestamps();

            $table->foreign('base_unit_id')->references('id')->on('units')->onDelete('set null');
            $table->index(['code', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};