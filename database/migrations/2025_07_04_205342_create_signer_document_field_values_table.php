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
        Schema::create('signer_document_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signer_document_field_id')->constrained('signer_document_fields')->onDelete('cascade');
            $table->foreignId('value_signature_sign_id')->nullable()->constrained('signs')->onDelete('restrict');
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
        Schema::dropIfExists('signer_document_field_values');
    }
};
