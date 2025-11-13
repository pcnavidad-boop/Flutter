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

            // Room Identification
            $table->string('room_number')->unique();
            $table->enum('type', ['Single','Double','Quad','Family','Suite','Penthouse','Function'])->default('Single');

            // Pricing
            $table->enum('price_type', ['per_night','per_hour','per_event'])->default('per_night');
            $table->decimal('base_price', 10, 2)->default(0);
            $table->boolean('is_time_based')->default(false);

            // Capacity
            $table->integer('number_of_beds')->nullable();
            $table->integer('capacity')->default(1);

            // Availability
            $table->enum('status', ['Available','Occupied','Maintenance','Unavailable'])->default('Available');

            // Descriptive Info
            $table->text('description')->nullable();
            $table->string('image')->nullable();

            // Archive Status
            $table->boolean('is_archived')->default(false);

            // Foreign Key
            $table->foreignId('user_id')->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
