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
        Schema::rename('signer_document_fields', 'document_fields');
        Schema::rename('signer_document_field_values', 'document_field_values');

        Schema::table('document_field_values', function (Blueprint $table) {
            $table->dropForeign(['signer_document_field_id']);
            $table->renameColumn('signer_document_field_id', 'document_field_id');
            $table->foreign('document_field_id')->references('id')->on('document_fields')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('document_fields', 'signer_document_fields');
        Schema::rename('document_field_values', 'signer_document_field_values');

        Schema::table('signer_document_field_values', function (Blueprint $table) {
            $table->dropForeign(['document_field_id']);
            $table->renameColumn('document_field_id', 'signer_document_field_id');
            $table->foreign('signer_document_field_id')->references('id')->on('signer_document_fields')->onDelete('cascade');
        });
    }
};
