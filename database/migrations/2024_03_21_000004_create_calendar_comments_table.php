<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_comments', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->foreignId('calendar_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('attachment_type')->nullable(); // 'file' or 'link'
            $table->string('attachment_url')->nullable();
            $table->string('attachment_filename')->nullable();
            $table->integer('attachment_file_size')->nullable();
            $table->string('link_url')->nullable();
            $table->string('link_title')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_comments');
    }
}; 