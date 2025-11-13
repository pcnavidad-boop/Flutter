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

            // Descriptive Info
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('image')->nullable();

            // Pricing
            $table->enum('price_type', ['per_hour', 'per_service', 'per_person'])->default('per_hour');
            $table->decimal('base_price', 10, 2)->default(0);

            // Availability
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('status', ['Available','Occupied','Maintenance','Unavailable'])->default('Available');
            
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
        Schema::dropIfExists('services');
    }
};
