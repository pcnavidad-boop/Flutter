<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique();
            $table->enum('room_type', ['Single','Double','Quad','Family','Suite','Penthouse'])->default('Single');
            $table->decimal('price_per_night',10,2)->default(0);
            $table->integer('number_of_beds')->nullable();
            $table->integer('room_capacity')->default(1);
            $table->boolean('room_availability_status')->default(false);
            $table->string('room_description')->nullable();
            $table->string('room_image')->nullable();

            $table->timestamps();

        });
    }



    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
