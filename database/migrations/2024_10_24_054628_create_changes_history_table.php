<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChangesHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('changes_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('type_id'); // the ID of the row in the user_pension table
            $table->string('type'); // e.g. 'user_pension'
            $table->unsignedBigInteger('changed_by'); // user ID who made the change
            $table->string('field_name'); // the field name, e.g. 'Provider'
            $table->text('from_value')->nullable(); // old value
            $table->text('to_value')->nullable(); // new value
            $table->timestamps();

            // Define relationships if necessary
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('changes_history');
    }
}
