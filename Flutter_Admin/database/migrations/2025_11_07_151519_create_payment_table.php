<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Foreign Key
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');

            // Polymorphic relationship (RoomBooking or ServiceBooking)
            $table->morphs('payable'); 

            // Payment Details
            $table->string('reference')->nullable()->unique();
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->enum('method', ['api', 'cash', 'card', 'bank_transfer', 'e_wallet'])->default('cash');
            $table->enum('channel', ['online','offline'])->default('offline');
            $table->enum('status', ['completed', 'refunded'])->default('completed');

            $table->index('user_id');
            $table->index('method');
            $table->index('status');
            $table->index('date');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
