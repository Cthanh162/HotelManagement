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
       Schema::create('rooms', function (Blueprint $table) {
            $table->id('roomId'); // Khóa chính

            // Các khóa ngoại
            $table->unsignedBigInteger('userId')->nullable();
            $table->unsignedBigInteger('hotelId')->nullable();
            $table->unsignedBigInteger('floorId')->nullable();
            $table->unsignedBigInteger('roomTypeId')->nullable();

            // Thông tin phòng
            $table->string('roomName');
            $table->string('status')->default('available'); // hoặc enum tùy hệ thống
            $table->unsignedTinyInteger('adults')->default(1);
            $table->unsignedTinyInteger('children')->default(0);
            $table->unsignedTinyInteger('capacity')->default(1);

            $table->decimal('price', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('roomVideo')->nullable();
            $table->json('roomImages')->nullable(); // Laravel cast to array

            // Không có timestamps vì $timestamps = false

            // Khóa ngoại
            $table->foreign('userId')->references('userId')->on('users')->nullOnDelete();
            $table->foreign('hotelId')->references('hotelId')->on('hotels')->cascadeOnDelete();
            $table->foreign('floorId')->references('id')->on('floors')->nullOnDelete();
            $table->foreign('roomTypeId')->references('id')->on('RoomType')->nullOnDelete(); // Đảm bảo table name đúng
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rooms');
    }
};
