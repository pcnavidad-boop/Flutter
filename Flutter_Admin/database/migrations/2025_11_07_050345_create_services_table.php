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
            $table->string('service_name')->unique();
            $table->text('service_description')->nullable();
            $table->integer('service_capacity')->nullable();
            $table->string('service_image')->nullable();

            // Pricing
            $table->enum('price_type', ['per_hour', 'per_service', 'per_person'])->default('per_hour');
            $table->decimal('base_price', 10, 2)->default(0);

            // Availability
            $table->time('service_start_time')->nullable();
            $table->time('service_end_time')->nullable();
            $table->boolean('service_availability_status')->default(true);
            
            // Archive Status
            $table->boolean('is_archived')->default(false);

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
