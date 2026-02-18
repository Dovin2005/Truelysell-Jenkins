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
        Schema::create('subscription_packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_title');
            $table->decimal('price', 8, 2);
            $table->enum('package_term', ['daily', 'monthly', 'yearly', 'lifetime']);
            $table->integer('number_of_service');
            $table->integer('number_of_feature_service');
            $table->integer('number_of_product')->nullable();
            $table->integer('number_of_service_order')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_packages');
    }
};
