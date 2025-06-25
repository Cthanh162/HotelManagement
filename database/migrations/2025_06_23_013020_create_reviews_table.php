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
       Schema::create('Reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('roomId');
            $table->unsignedTinyInteger('rating');
            $table->text('des')->nullable();
            $table->dateTime('createdAt')->nullable();

            // Khóa ngoại
            $table->foreign('userId')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('roomId')->references('roomId')->on('rooms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Reviews');
    }
};
