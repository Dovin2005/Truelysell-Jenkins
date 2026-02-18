<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name',200);
            $table->tinyint('type')->default(1)->comment("1 = Phpemail and 2 = SMTP and 3 = Sendgrid");
            $table->boolean('is_default')->default(0)->comment("0 = Not Default , 1= Default");
            $table->string('from_email', 200);
            $table->string('email_host')->nullable();
            $table->string('password')->nullable();
            $table->string('port')->nullable();
            $table->integer('status')->default(1)->comment("1 = Active and 0 = Inactive");
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_settings');
    }
};
