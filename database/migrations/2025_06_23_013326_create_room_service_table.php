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
       Schema::create('RoomService', function (Blueprint $table) {
            $table->unsignedBigInteger('roomId');
            $table->unsignedBigInteger('serviceId');
            $table->decimal('price', 12, 2)->nullable();

            $table->primary(['roomId', 'serviceId']);

            $table->foreign('roomId')->references('roomId')->on('rooms')->onDelete('cascade');
            $table->foreign('serviceId')->references('id')->on('Service')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('RoomService');
    }
};
