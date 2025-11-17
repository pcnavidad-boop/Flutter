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

            // Polymorphic relation
            $table->unsignedBigInteger('payable_id');
            $table->string('payable_type');

            // Admin Details
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');

            // Payment Details
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->enum('method', ['Cash', 'Card', 'Bank Transfer', 'E-Wallet'])->default('Cash');
            $table->enum('status', ['Pending', 'Completed', 'Failed', 'Refunded'])->default('Pending');

            $table->timestamps();

            $table->index(['payable_id', 'payable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
