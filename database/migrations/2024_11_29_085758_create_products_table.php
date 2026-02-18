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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('source_name');
            $table->string('slug');
            $table->string('source_code');
            $table->string('source_type');
            $table->text('source_description');
            $table->string('source_category');
            $table->string('source_subcategory');
            $table->string('plan')->nullable();
            $table->string('price_type');
            $table->decimal('source_price', 10, 2);
            $table->integer('duration');
            $table->text('price_description')->nullable();
            $table->string('source_brand');
            $table->integer('source_stock')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('tags')->nullable();
            $table->text('seo_description');
            $table->boolean('featured')->nullable();
            $table->boolean('popular')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('pincode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
