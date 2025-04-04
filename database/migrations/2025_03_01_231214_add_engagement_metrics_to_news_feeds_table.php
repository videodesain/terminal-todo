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
        Schema::table('news_feeds', function (Blueprint $table) {
            $table->integer('views')->default(0);
            $table->integer('shares')->default(0);
            $table->integer('likes')->default(0);
            $table->integer('comments')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news_feeds', function (Blueprint $table) {
            $table->dropColumn(['views', 'shares', 'likes', 'comments']);
        });
    }
};
