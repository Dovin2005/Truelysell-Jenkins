<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_type', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('type', 255); // Type column
            $table->tinyInteger('status')->default(1); // Status column
            $table->unsignedBigInteger('created_by')->nullable(); // Created by column
            $table->timestamps(); // Includes created_at and updated_at
            $table->unsignedBigInteger('updated_by')->nullable(); // Updated by column
            $table->softDeletes(); // Includes deleted_at for soft deletion

            // Foreign keys or additional indexes can be added here if necessary
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_type');
    }
}
