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
        // Create a temporary column with the new enum including 'template'
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status_new', ['draft', 'open', 'in_progress', 'completed', 'template'])->after('status');
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

        // First, delete any existing template documents
        DB::table('documents')
            ->where('status', 'template')
            ->delete();
        

        // Create a temporary column with the old enum including 'template'
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status_old', ['draft', 'open', 'in_progress', 'completed', 'template'])->after('status');
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