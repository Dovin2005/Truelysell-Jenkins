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
        Schema::create('provider_forms_input', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_form_inputs_id');
            $table->unsignedBigInteger('provider_id');
            $table->tinyInteger('status')->default(1); // Default to active
            $table->tinyInteger('user_status')->default(1); // Default to active
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_form_inputs_id')->references('id')->on('user_form_inputs')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_forms_input');
    }
};
