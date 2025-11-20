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
            $table->decimal('total_price', 10, 2)->nullable();
            $table->text('remarks')->nullable();

            // Normal Rooms
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();

            // Function Rooms
            $table->date('event_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            
            // Booking Life Cycle
            $table->string('reference')->unique();
            $table->enum('type', ['website','walk_in','phone','e_mail'])->default('website');
            $table->date('booking_date');
            $table->enum('booking_status', ['confirmed','checked_in','checked_out','cancelled'])->default('confirmed');
            $table->enum('payment_status', ['downpayment','fully_paid','refunded'])->default('downpayment');
            $table->text('status_change_reason')->nullable();

            // Indexes
            $table->index('room_id');
            $table->index('user_id');

            $table->index('booking_status');
            $table->index('payment_status');

            $table->index('booking_date');
            $table->index('check_in_date');
            $table->index('check_out_date');
            $table->index('event_date');

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
