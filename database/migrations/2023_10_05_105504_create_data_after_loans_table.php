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
        Schema::create('data_after_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
            $table->unsignedBigInteger('payment_id');
            $table->foreign('payment')->references('id')->on('payments')->onDelete('cascade');
            $table->decimal('capital', 8, 2);
            $table->decimal('interest_rate', 8, 2);
            $table->decimal('legal_interest', 8, 2);
            $table->decimal('conservation_expense', 8, 2);
            $table->decimal('utility', 8, 2);
            $table->decimal('balance_pay', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_after_loans');
    }
};
