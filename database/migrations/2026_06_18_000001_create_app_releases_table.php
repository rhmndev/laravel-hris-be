<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_releases', function (Blueprint $table) {
            $table->id();
            $table->string('version'); // e.g. "1.2.0"
            $table->text('release_notes')->nullable();
            $table->string('file_path'); // APK path on public disk
            $table->unsignedBigInteger('file_size')->default(0); // bytes
            $table->string('min_android_version')->default('8.0');
            $table->boolean('is_published')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_releases');
    }
};
