<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->nullable();
            $table->integer('parent_id')->nullable();
            $table->text('image')->nullable();
            $table->text('icon')->nullable();
            $table->unsignedTinyInteger('status')->default(0)->comment('0 => Active or 1 => In-active');
            $table->longText('description')->nullable();
            $table->unsignedTinyInteger('featured')->default(0)->comment('0 => Off or 1 => On');
            $table->string('slug', 100)->unique()->nullable();

            $table->unsignedInteger('language_id')->default(1)->index();
            $table->enum('type', ['product', 'service'])->default('product');

            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes()->default(\DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('deleted_by')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
