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
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_contact')->nullable();

            // Foreign Keys
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
        
            // Booking Details
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 10, 2)->nullable();
            $table->text('remarks')->nullable();

            // Booking Life Cycle
            $table->enum('type', ['Online','Walk-in','Phone','E-mail'])->default('Online');
            $table->date('booking_date');
            $table->enum('booking_status', ['Pending','Confirmed','Declined','Cancelled','Completed'])->default('Pending');
            $table->enum('payment_status', ['Unpaid','Partially_Paid','Paid','Refunded'])->default('Unpaid');
            $table->text('status_change_reason')->nullable();
            
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
