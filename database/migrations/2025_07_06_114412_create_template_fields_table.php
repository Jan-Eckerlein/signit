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
        Schema::create('template_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('templates')->onDelete('cascade');
            $table->foreignId('template_signer_id')->nullable()->constrained('template_signers')->onDelete('set null');
            $table->integer('page');
            $table->float('x');
            $table->float('y');
            $table->float('width');
            $table->float('height');
            $table->enum('type', ['signature', 'initials', 'text', 'checkbox', 'date']);
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('required')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_fields');
    }
};
