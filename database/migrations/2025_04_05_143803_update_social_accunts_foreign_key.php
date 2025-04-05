<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migrasi ini sudah tidak dibutuhkan karena tabel social_accounts langsung dibuat dengan
     * referensi ke tabel platforms
     */
    public function up(): void
    {
        // Nonaktifkan karena perubahan sudah diterapkan langsung di migrasi create_social_accounts_table
        /*
        // Drop foreign key constraint
        Schema::table('social_accounts', function (Blueprint $table) {
            $table->dropForeign(['platform_id']);
        });

        // Update foreign key to reference platforms instead of social_platforms
        Schema::table('social_accounts', function (Blueprint $table) {
            $table->foreign('platform_id')->references('id')->on('platforms')->onDelete('cascade');
        });
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nonaktifkan karena perubahan sudah diterapkan langsung di migrasi create_social_accounts_table
        /*
        // Drop foreign key constraint
        Schema::table('social_accounts', function (Blueprint $table) {
            $table->dropForeign(['platform_id']);
        });

        // Restore original foreign key constraint
        Schema::table('social_accounts', function (Blueprint $table) {
            $table->foreign('platform_id')->references('id')->on('social_platforms')->onDelete('cascade');
        });
        */
    }
};
