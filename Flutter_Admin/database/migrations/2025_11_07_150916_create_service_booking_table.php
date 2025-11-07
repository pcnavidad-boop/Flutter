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

            // Guest Information
            $table->string('service_guest_name');
            $table->string('service_guest_email');
            $table->string('service_guest_contact')->nullable();

            // Foreign Keys
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
        
            // Booking Details
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('quantity')->default(1);

            $table->date('service_booking_date');
            $table->enum('service_booking_status', ['Pending','Confirmed','Declined','Cancelled','Completed'])->default('Pending');
            $table->enum('service_booking_payment_status', ['Unpaid','Partially Paid','Paid'])->default('Unpaid');
            $table->decimal('total_service_price', 10, 2)->nullable();

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
