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
        Schema::create('Booking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roomId')->nullable();
            $table->unsignedBigInteger('userId')->nullable();

            $table->dateTime('checkinTime');
            $table->dateTime('checkoutTime');
            $table->string('status')->default('pending');        // waiting, confirmed, canceled
            $table->string('paymentStatus')->default('unpaid');  // unpaid, paid

            $table->dateTime('create_at')->nullable();
            $table->string('createdBy')->nullable();
            $table->decimal('totalPrice', 12, 2)->default(0);
            $table->string('paymentProof')->nullable(); // file ảnh chứng minh thanh toán

            $table->string('Name');
            $table->string('phone', 20);
            $table->string('cccd', 20); // số CMND/CCCD

            // FK
            $table->foreign('roomId')->references('roomId')->on('rooms')->nullOnDelete();
            $table->foreign('userId')->references('userId')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Booking');
    }
};
