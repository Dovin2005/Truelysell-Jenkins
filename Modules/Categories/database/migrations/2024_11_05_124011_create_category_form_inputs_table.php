<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryFormInputsTable extends Migration
{
    public function up(): void
    {
        Schema::create('category_form_inputs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categories_id');
            $table->string('type');
            $table->string('label');
            $table->string('placeholder')->nullable();
            $table->string('name');
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();
            $table->integer('file_size')->nullable();
            $table->integer('order_no')->default(0);
            $table->timestamps();

            $table->foreign('categories_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_form_inputs');
    }
}
