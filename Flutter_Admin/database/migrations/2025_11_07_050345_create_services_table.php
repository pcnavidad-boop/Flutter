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

            // Relationship
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            // Service Identification
            $table->string('name')->unique();
            $table->string('location');
            $table->enum('service_type', ['restaurant','spa','gym','swimming_pool','bar']);
            $table->text('description'); 
            $table->string('image');     
            $table->string('slug')->unique();

            // Pricing
            $table->enum('price_type', ['per_hour', 'per_day', 'per_person']);
            $table->decimal('base_price', 10, 2)->default(0);

            // Capacity
            $table->unsignedSmallInteger('capacity')->default(1);

            // Operating Hours
            $table->time('start_time');
            $table->time('end_time');

            // Availability (occupancy now computed dynamically)
            $table->enum('status', ['available', 'maintenance'])->default('available');

            // Archive Status
            $table->boolean('is_archived')->default(false);

            // Indexes
            $table->index('created_by');
            $table->index('service_type');
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
        Schema::dropIfExists('services');
    }
};
