<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllTables extends Migration
{
    public function up()
    {
        // Tạo bảng User
        Schema::create('users', function (Blueprint $table) {
            $table->id('userId');
            $table->string('userName', 50);
            $table->string('address', 200);
            $table->string('phone', 12);
            $table->string('email', 50)->unique();
            $table->string('password', 200);
            $table->string('isActive', 1);
            $table->timestamp('createTime')->useCurrent();
            $table->string('createdBy', 50);
            $table->timestamp('lastUpdateTime')->nullable()->useCurrentOnUpdate();
            $table->string('lastUpdatedBy', 50);
            $table->string('isSuperAdmin', 1);
            $table->timestamps(0);
        });

        // Tạo bảng Role
        Schema::create('roles', function (Blueprint $table) {
            $table->id('roleId');
            $table->string('roleName', 20);
            $table->timestamps(0);
        });

        // Tạo bảng User_Role
        Schema::create('user_role', function (Blueprint $table) {
            $table->bigInteger('roleId')->unsigned();
            $table->bigInteger('userId')->unsigned();
            $table->timestamps();

            $table->primary(['roleId', 'userId']);

            $table->foreign('roleId')->references('roleId')->on('roles')->onDelete('cascade');
            $table->foreign('userId')->references('userId')->on('users')->onDelete('cascade');
        });
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('hotelName', 50);
            $table->string('address', 200);
            $table->string('phoneNumber', 12);
            $table->string('nearbyPlaces', 500);
            $table->timestamps();
        }); 
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotelId')->constrained('hotels')->onDelete('cascade');
            $table->string('floorName', 20);
            $table->timestamps();
        });
        Schema::create('rooms', function (Blueprint $table) {
            $table->id('roomId');
            $table->unsignedBigInteger('userId')->nullable(); // Khóa ngoại với bảng User
            $table->unsignedBigInteger('hotelId'); // Khóa ngoại với bảng Hotel
            $table->unsignedBigInteger('floorId'); // Khóa ngoại với bảng Floor
            $table->string('roomName', 20);
            $table->string('status', 1);
            $table->string('roomType', 1);
            $table->integer('capacity');
            $table->decimal('price', 18, 2);
            $table->string('description', 500);
            $table->string('roomVideo')->nullable();
            $table->json('roomImages')->nullable();
            $table->timestamps();
            $table->foreign('userId')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('hotelId')->references('id')->on('hotels')->onDelete('cascade');
            $table->foreign('floorId')->references('id')->on('floors')->onDelete('cascade');
        });
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId')->nullable(); 
            $table->unsignedBigInteger('roomId'); 
            $table->timestamp('checkinTime')->nullable();
            $table->timestamp('checkoutTime')->nullable();
            $table->string('status', 1);
            $table->string('paymentStatus', 1);
            $table->timestamp('createAt');
            $table->string('createdBy', 50);
            $table->decimal('totalPrice', 18, 2);
            $table->timestamps();
            $table->foreign('roomId')->references('roomId')->on('rooms')->onDelete('cascade');
            $table->foreign('userId')->references('userId')->on('users')->onDelete('cascade');
        });
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('status', 1);
            $table->decimal('price', 18, 2);
            $table->timestamps();
        });
        Schema::create('room_service', function (Blueprint $table) {
            $table->unsignedBigInteger('roomId')->nullable();
            $table->unsignedBigInteger('serviceId'); 
            $table->timestamps();
            
            $table->primary(['roomId', 'serviceId']);
            $table->foreign('roomId')->references('roomId')->on('rooms')->onDelete('cascade');
            $table->foreign('serviceId')->references('id')->on('services')->onDelete('cascade');
        });

        // Tạo bảng Reviews
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId')->nullable(); 
            $table->unsignedBigInteger('roomId');
            $table->integer('rating')->nullable();
            $table->string('des', 500)->nullable();
            $table->timestamps();
            $table->foreign('userId')->references('userId')->on('users')->onDelete('cascade');
            $table->foreign('roomId')->references('roomId')->on('rooms')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('room_service');
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
    }
}
