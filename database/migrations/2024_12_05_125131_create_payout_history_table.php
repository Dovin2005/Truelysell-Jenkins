<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CreatePayoutHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('payout_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id'); // Foreign key to providers table
            $table->string('provider_name');
            $table->string('provider_email');
            $table->integer('total_bookings');
            $table->decimal('total_earnings', 10, 2);
            $table->decimal('admin_earnings', 10, 2);
            $table->decimal('provider_pay_due', 10, 2);
            $table->decimal('entered_amount', 10, 2); // Amount entered by the admin
            $table->string('payment_proof_path'); // File path to the payment proof
            $table->timestamps();
            $table->softDeletes(); // Add the soft deletes column
            
            // Add foreign key constraint
            $table->foreign('provider_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_history');
    }
}
