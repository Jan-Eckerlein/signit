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
        Schema::table('document_signers', function (Blueprint $table) {
            $table->timestamp('signature_completed_at')->nullable();
            $table->boolean('electronic_signature_disclosure_accepted')->default(false);
            $table->timestamp('disclosure_accepted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_signers', function (Blueprint $table) {
            $table->dropColumn('signature_completed_at');
            $table->dropColumn('electronic_signature_disclosure_accepted');
            $table->dropColumn('disclosure_accepted_at');
        });
    }
};
