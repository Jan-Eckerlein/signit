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
        Schema::table('document_fields', function (Blueprint $table) {
            $table->foreignId('document_page_id')->constrained('document_pages')->onDelete('restrict');
            $table->dropForeign(['document_id']);
            $table->dropColumn('document_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_fields', function (Blueprint $table) {
            $table->dropForeign(['document_page_id']);
            $table->foreignId('document_id')->constrained('documents')->onDelete('restrict');
            $table->dropColumn('document_page_id');
        });
    }
};
