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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            //$table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            //status
            $table->string('state', 50)->default('borrador');
            $table->string('currency', 10);
            $table->decimal('capital', 8, 2);
            $table->decimal('interest_rate', 8, 2);
            $table->decimal('legal_interest', 8, 2);
            $table->decimal('conservation_expense', 8, 2);
            $table->decimal('utility', 8, 2);
            $table->decimal('balance_pay', 8, 2);
            $table->date('date_contract');
            $table->date('date_contract_expiration');
            $table->integer('renovation')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
