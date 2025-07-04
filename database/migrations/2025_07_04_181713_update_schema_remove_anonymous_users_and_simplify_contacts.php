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
        // Drop the anonymous_users table
        Schema::dropIfExists('anonymous_users');

        // Update contacts table
        Schema::table('contacts', function (Blueprint $table) {
            // Drop old columns
            $table->dropForeign(['own_user_id']);
            $table->dropForeign(['knows_user_id']);
            $table->dropForeign(['knows_anonymous_users_id']);
            $table->dropColumn(['own_user_id', 'knows_user_id', 'knows_anonymous_users_id']);
            
            // Add new columns
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        });

        // Update signs table
        Schema::table('signs', function (Blueprint $table) {
            // Drop anonymous_user_id column
            $table->dropForeign(['anonymous_user_id']);
            $table->dropColumn('anonymous_user_id');
        });

        // Update document_signers table
        Schema::table('document_signers', function (Blueprint $table) {
            // Drop contact_id and add user_id
            $table->dropForeign(['contact_id']);
            $table->dropColumn('contact_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        });

        // Update document_logs table
        Schema::table('document_logs', function (Blueprint $table) {
            // Drop contact_id and add document_signer_id
            $table->dropForeign(['contact_id']);
            $table->dropColumn('contact_id');
            $table->foreignId('document_signer_id')->nullable()->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate anonymous_users table
        Schema::create('anonymous_users', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('name');
            $table->timestamps();
        });

        // Revert contacts table
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            
            $table->foreignId('own_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('knows_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('knows_anonymous_users_id')->nullable()->constrained('anonymous_users')->onDelete('cascade');
        });

        // Revert signs table
        Schema::table('signs', function (Blueprint $table) {
            $table->foreignId('anonymous_user_id')->nullable()->constrained('anonymous_users')->onDelete('cascade');
        });

        // Revert document_signers table
        Schema::table('document_signers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
        });

        // Revert document_logs table
        Schema::table('document_logs', function (Blueprint $table) {
            $table->dropForeign(['document_signer_id']);
            $table->dropColumn('document_signer_id');
            $table->foreignId('contact_id')->nullable()->constrained()->onDelete('cascade');
        });
    }
};
