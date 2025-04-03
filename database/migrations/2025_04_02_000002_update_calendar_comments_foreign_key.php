<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_comments', function (Blueprint $table) {
            $table->dropForeign(['calendar_id']);
            $table->foreign('calendar_id')->references('id')->on('editorial_calendar')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_comments', function (Blueprint $table) {
            $table->dropForeign(['calendar_id']);
            $table->foreign('calendar_id')->references('id')->on('calendars')->onDelete('cascade');
        });
    }
}; 