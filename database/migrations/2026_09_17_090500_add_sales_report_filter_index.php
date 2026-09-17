<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_deactivation_feedback', function (Blueprint $table) {
            $table->index(
                ['is_current', 'excluded_at', 'deactivated_at'],
                'service_feedback_sales_period_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('service_deactivation_feedback', function (Blueprint $table) {
            $table->dropIndex('service_feedback_sales_period_idx');
        });
    }
};
