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
        Schema::create('room_bookings', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');

            // Guest Information
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_contact')->nullable();

            // Booking Details
            $table->integer('number_of_guests')->default(1);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_price', 10, 2)->nullable();
            $table->text('remarks')->nullable();
            
            // Booking Life Cycle
            $table->string('reference')->unique();
            $table->enum('type', ['website','walk-in','phone','email'])->default('website');
            $table->date('booking_date');
            $table->enum('booking_status', ['confirmed','checked_in','checked_out','cancelled'])->default('confirmed');
            $table->enum('payment_status', ['downpayment','fully_paid','refunded'])->default('downpayment');
            $table->text('status_change_reason')->nullable();

            // Indexes
            $table->index('room_id');
            $table->index('user_id');

            $table->index('booking_status');
            $table->index('payment_status');

            $table->index('start_date');
            $table->index('end_date');
            $table->index('booking_date');
            $table->index(['room_id', 'booking_status']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_bookings');
    }
};
