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
        Schema::create('pdf_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('restrict');
            $table->enum('status', [
                'pdf_missing',
                'pdf_regenerating',
                'pdf_regenerated',
                'pdf_regeneration_failed',
                'pdf_signing',
                'pdf_signed',
                'pdf_signing_failed',
                'pdf_signing_retrying',
                'pdf_signing_retry_failed',
            ])->default('pdf_missing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_processes');
    }
};
