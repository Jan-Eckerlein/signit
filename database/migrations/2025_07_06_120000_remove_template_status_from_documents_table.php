<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update any existing template documents to draft status
        DB::table('documents')
            ->where('status', 'template')
            ->update(['status' => 'draft']);
        
        // Create a temporary column with the new enum
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status_new', ['draft', 'open', 'in_progress', 'completed'])->default('draft')->after('status');
        });
        
        // Copy data from old column to new column
        DB::statement('UPDATE documents SET status_new = status');
        
        // Drop the old column and rename the new one
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('documents', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Create a temporary column with the old enum
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status_old', ['draft', 'open', 'in_progress', 'completed', 'template'])->default('draft')->after('status');
        });
        
        // Copy data from current column to old column
        DB::statement('UPDATE documents SET status_old = status');
        
        // Drop the current column and rename the old one
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('documents', function (Blueprint $table) {
            $table->renameColumn('status_old', 'status');
        });
    }
}; 