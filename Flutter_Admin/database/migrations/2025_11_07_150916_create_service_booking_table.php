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
        Schema::create('service_bookings', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');

            // Guest Information
            $table->string('guest_name');
            $table->string('guest_email'); 
            $table->string('guest_contact')->nullable();

            // Booking Details
            $table->unsignedSmallInteger('number_of_guests')->default(1);
            $table->date('appointment_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->text('remarks')->nullable();

            // Booking Life Cycle
            $table->string('reference')->unique();
            $table->enum('type', ['website','walk-in','phone','email'])->default('website');
            $table->date('booking_date');
            $table->enum('booking_status', ['pending', 'confirmed','completed','cancelled'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'downpayment','fully_paid','refunded'])->default('unpaid');

            // Indexes
            $table->index('service_id');
            $table->index('created_by');
            $table->index('payment_status');
            $table->index('appointment_date');
            $table->index(['booking_status', 'booking_date']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_bookings');
    }
};
