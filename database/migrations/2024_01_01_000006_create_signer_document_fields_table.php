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
        Schema::create('signer_document_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_signer_id')->constrained('document_signers')->onDelete('cascade');
            $table->integer('page');
            $table->float('x');
            $table->float('y');
            $table->float('width');
            $table->float('height');
            $table->enum('type', ['signature', 'initials', 'text', 'checkbox', 'date']);
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('required')->default(false);
            $table->text('value_signature')->nullable();
            $table->string('value_initials')->nullable();
            $table->text('value_text')->nullable();
            $table->boolean('value_checkbox')->nullable();
            $table->date('value_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signer_document_fields');
    }
}; 