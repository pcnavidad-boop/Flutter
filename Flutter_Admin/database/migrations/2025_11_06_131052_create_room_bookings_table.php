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

            //guest data
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_contact')->nullable();

            //foregin keys
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            //booking attributes
            $table->date('check_in_date');
            $table->date('check_out_date');

            $table->date('booking_date');
            $table->enum('status', ['pending','confirmed','declined','checked_in','checked_out','cancelled']);
            $table->text('status_change_reason')->nullable();

            
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
