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
        Schema::create('contract_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->foreign('loan_id')->references('id')->on('loans');
            //$table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            //$table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('image_url')->nullable();
            $table->text('description')->nullable();
            $table->string('condition', 50);
            $table->decimal('estimated_value', 20, 2);
            $table->string('currency', 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_articles');
    }
};
