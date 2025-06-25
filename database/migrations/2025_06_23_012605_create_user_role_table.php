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
       Schema::create('User_Role', function (Blueprint $table) {
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('roleId');
            $table->timestamps();

            // Composite key (không có primaryKey)
            $table->primary(['userId', 'roleId']);

            // Foreign keys
            $table->foreign('userId')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('roleId')->references('roleId')->on('Role')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('User_Role');
    }
};
