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
        Schema::create('pdf_process_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pdf_process_id')->constrained('pdf_processes')->restrictOnDelete();
            $table->foreignId('document_page_id')->nullable()->constrained('document_pages')->restrictOnDelete();
            $table->integer('order');
            $table->string('pdf_original_path');
            $table->string('pdf_processed_path')->nullable();
            $table->boolean('is_up_to_date')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_process_pages');
    }
};
