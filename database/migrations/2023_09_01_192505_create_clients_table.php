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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('code')->default('-');
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('document', 20);
            $table->string('issued', 128);
            $table->text('address', 1024);
            $table->text('address_references', 1024)->nullable();
            $table->string('mobile', 20);
            $table->string('phone', 20)->nullable();
            $table->string('email', 64)->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
