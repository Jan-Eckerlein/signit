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
        Schema::table('signer_document_fields', function (Blueprint $table) {
            $table->foreignId('document_signer_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signer_document_fields', function (Blueprint $table) {
            $table->foreignId('document_signer_id')->nullable(false)->change();
        });
    }
}; 