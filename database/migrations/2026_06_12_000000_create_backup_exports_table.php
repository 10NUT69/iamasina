<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_exports', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->index();
            $table->string('status')->index();
            $table->string('filename')->nullable();
            $table->string('relative_path')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('error_message', 500)->nullable();
            $table->string('active_key')->nullable()->unique();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['type', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_exports');
    }
};
