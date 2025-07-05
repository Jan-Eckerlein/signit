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
            $table->dropForeign(['value_signature_sign_id']);
            $table->dropColumn([
                'value_signature_sign_id',
                'value_initials',
                'value_text',
                'value_checkbox',
                'value_date',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signer_document_fields', function (Blueprint $table) {
            $table->foreignId('value_signature_sign_id')->nullable()->constrained('signs')->onDelete('set null');
            $table->string('value_initials')->nullable();
            $table->text('value_text')->nullable();
            $table->boolean('value_checkbox')->nullable();
            $table->date('value_date')->nullable();
        });
    }
};
