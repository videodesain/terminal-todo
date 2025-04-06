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
        // Langkah 1: Menambahkan kolom is_active
        Schema::table('news_feeds', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('is_featured');
        });
        
        // Langkah 2: Migrasi data yang di-softdelete
        DB::statement('UPDATE news_feeds SET is_active = 0 WHERE deleted_at IS NOT NULL');
        
        // Langkah 3: Hapus kolom deleted_at
        Schema::table('news_feeds', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Langkah 1: Tambahkan kembali kolom deleted_at
        Schema::table('news_feeds', function (Blueprint $table) {
            $table->softDeletes();
        });
        
        // Langkah 2: Migrasi data ke deleted_at
        DB::statement('UPDATE news_feeds SET deleted_at = NOW() WHERE is_active = 0');
        
        // Langkah 3: Hapus kolom is_active
        Schema::table('news_feeds', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
