<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index(); // User ID referencing the user
            $table->enum('type', ['Wallet Topup', 'Wallet Deduction']); // Type of transaction
            $table->decimal('amount', 10, 2); // Transaction amount
            $table->enum('payment_type', ['Paypal', 'Credit Card', 'Bank Transfer', 'Others']); // Payment type
            $table->enum('status', ['Completed', 'Pending', 'Failed', 'Refunded']); // Status of the transaction
            $table->timestamp('transaction_date')->nullable(); // Date of the transaction
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_history');
    }
}
