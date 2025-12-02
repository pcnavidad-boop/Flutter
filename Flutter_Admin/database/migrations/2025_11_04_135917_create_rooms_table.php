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

            // Relationship
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            // Room Identification
            $table->string('name')->unique();
            $table->string('room_number')->unique();
            $table->enum('room_type', ['single','double','quad','family','suite','penthouse','function']);            
            $table->text('description')->nullable();     
            $table->string('image')->nullable();         
            $table->string('slug')->unique();

            // Pricing
            $table->enum('price_type', ['per_night', 'per_event_per_day']);
            $table->decimal('base_price', 10, 2)->default(0);

            // Capacity
            $table->unsignedSmallInteger('number_of_beds')->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();

            // Availability 
            $table->enum('status', ['available', 'maintenance'])->default('available');

            // Archive Status
            $table->boolean('is_archived')->default(false);

            // Indexes
            $table->index('created_by');
            $table->index('room_type');
            $table->index('price_type');
            $table->index('status');
            $table->index('is_archived');

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
