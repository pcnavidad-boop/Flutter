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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('room_booking_id')->nullable()->constrained('room_bookings')->onDelete('cascade');
            $table->foreignId('service_booking_id')->nullable()->constrained('service_bookings')->onDelete('cascade');

            // Payment Details
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->enum('method', ['Cash', 'Card', 'Bank Transfer', 'E-Wallet'])->default('Cash');
            $table->enum('status', ['Pending', 'Completed', 'Failed', 'Refunded'])->default('Pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
