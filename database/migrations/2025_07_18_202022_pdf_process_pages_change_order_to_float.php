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
        Schema::table('pdf_process_pages', function (Blueprint $table) {
            $table->dropColumn('order');
            $table->float('tmp_order')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdf_process_pages', function (Blueprint $table) {
            $table->dropColumn('tmp_order');
            $table->integer('order');
        });
    }
};
