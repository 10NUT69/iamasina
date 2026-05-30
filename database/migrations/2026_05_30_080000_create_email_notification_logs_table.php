<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('notification_type', 80);
            $table->date('period_date');
            $table->dateTime('period_start');
            $table->dateTime('period_end');
            $table->json('service_ids')->nullable();
            $table->unsignedInteger('service_count')->default(0);
            $table->string('status', 24)->default('reserved');
            $table->dateTime('reserved_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(
                ['user_id', 'notification_type', 'period_date'],
                'email_logs_user_type_period_unique'
            );
            $table->index(
                ['notification_type', 'period_date', 'status'],
                'email_logs_type_period_status_idx'
            );
        });

        Schema::table('services', function (Blueprint $table) {
            $table->index(
                ['status', 'published_at', 'user_id'],
                'services_status_published_user_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('services_status_published_user_idx');
        });

        Schema::dropIfExists('email_notification_logs');
    }
};
