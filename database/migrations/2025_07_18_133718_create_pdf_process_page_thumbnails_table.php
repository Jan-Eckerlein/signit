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
        Schema::create('pdf_process_page_thumbnails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pdf_process_page_id')->constrained('pdf_process_pages')->restrictOnDelete();
            $table->string('path');
            $table->enum('size', ['20', '200', '400', '600', '800', '1000', '1200', '1400', '1600']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_process_page_thumbnails');
    }
};
