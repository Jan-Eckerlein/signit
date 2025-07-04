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
        Schema::table('documents', function (Blueprint $table) {
            // Remove the completed_at column
            $table->dropColumn('completed_at');
            
            // Update the status enum to include 'in_progress'
            $table->enum('status', ['draft', 'open', 'in_progress', 'completed', 'template'])->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Add back the completed_at column
            $table->timestamp('completed_at')->nullable();
            
            // Revert the status enum to original values
            $table->enum('status', ['draft', 'open', 'completed', 'template'])->default('draft')->change();
        });
    }
};
