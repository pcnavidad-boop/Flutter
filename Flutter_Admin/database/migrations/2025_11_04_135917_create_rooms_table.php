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

            // Foreign Key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Room Identification
            $table->string('name')->unique();
            $table->string('room_number')->unique();
            $table->enum('room_type', ['single','double','quad','family','suite','penthouse','function'])->default('single');
            $table->text('description')->nullable();
            $table->string('image')->nullable();

            // Pricing
            $table->enum('price_type', ['per_night','per_hour'])->default('per_night');
            $table->decimal('base_price', 10, 2)->default(0);

            // Capacity
            $table->integer('number_of_beds')->nullable();
            $table->integer('capacity')->default(1);

            // Availability
            $table->enum('status', ['available','occupied','maintenance'])->default('available');

            // Archive Status
            $table->boolean('is_archived')->default(false);

            // URL Slug
            $table->string('slug')->unique();

            // Indexes
            $table->index('room_type');
            $table->index('status');
            $table->index('is_archived');
            $table->index('user_id');

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
