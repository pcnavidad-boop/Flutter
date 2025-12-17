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

            // Relationships
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('cascade');

            // Polymorphic (RoomBooking or ServiceBooking)
            $table->morphs('payable');

            // Payment Details
            $table->string('reference')->unique();
            $table->decimal('amount', 10, 2);
            $table->dateTime('paid_at'); 
            $table->enum('method', ['api', 'cash', 'card', 'bank_transfer', 'e_wallet'])->default('cash');
            $table->enum('channel', ['online', 'offline'])->default('offline');
            $table->enum('status', ['completed', 'refunded'])->default('completed');

            // Indexes
            $table->index('processed_by');
            $table->index('method');
            $table->index(['status', 'paid_at']);

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
