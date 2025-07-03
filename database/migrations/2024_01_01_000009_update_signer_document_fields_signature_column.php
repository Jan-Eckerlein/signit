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
            // Drop the old column
            $table->dropColumn('value_signature');
            
            // Add the new foreign key column
            $table->foreignId('value_signature_sign_id')->nullable()->constrained('signs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signer_document_fields', function (Blueprint $table) {
            // Drop the foreign key column
            $table->dropForeign(['value_signature_sign_id']);
            $table->dropColumn('value_signature_sign_id');
            
            // Add back the old column
            $table->text('value_signature')->nullable();
        });
    }
}; 