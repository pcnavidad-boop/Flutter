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
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            // Foreign Key 
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Service Identification
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();

            // Pricing
            $table->enum('price_type', ['per_hour', 'per_service', 'per_person'])->default('per_hour');
            $table->decimal('base_price', 10, 2)->default(0);

            // Capacity
            $table->integer('capacity')->default(1);

            // Availability
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['available','occupied','maintenance'])->default('available');
            
            // Archive Status
            $table->boolean('is_archived')->default(false);

            // URL Slug
            $table->string('slug')->unique();

            // Indexes
            $table->index('status');
            $table->index('price_type');
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
        Schema::dropIfExists('services');
    }
};
