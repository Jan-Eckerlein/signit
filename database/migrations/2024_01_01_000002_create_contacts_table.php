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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('own_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('knows_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('knows_anonymous_users_id')->nullable()->constrained('anonymous_users')->onDelete('cascade');
            $table->string('email');
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
}; 