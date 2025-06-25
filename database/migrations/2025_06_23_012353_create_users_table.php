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
        Schema::create('users', function (Blueprint $table) {
            $table->id('userId'); // dùng làm khóa chính

            $table->string('fullName');
            $table->string('userName')->unique();
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->unique();
            $table->string('password');

            $table->boolean('isActive')->default(true);
            $table->boolean('isSuperAdmin')->default(false);

            $table->dateTime('createTime')->nullable();
            $table->string('createdBy')->nullable();
            $table->dateTime('lastUpdateTime')->nullable();
            $table->string('lastUpdatedBy')->nullable();

            // timestamps() bỏ qua vì bạn đã set $timestamps = false
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::dropIfExists('users');
    }
};
