<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('paymentId');
            $table->string('userId');
            $table->string('productId');
            $table->decimal('price', 13,8);
            $table->enum('paymentMethod', ['paystack', 'flutterwave', 'monnify']);
            $table->enum('status', ['pending', 'success', 'failed']);
            $table->dateTime('statusDate');
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
        Schema::dropIfExists('payments');
    }
};
