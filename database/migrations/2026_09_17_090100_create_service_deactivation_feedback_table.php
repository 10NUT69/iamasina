<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_deactivation_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('answer', 24)->nullable();
            $table->string('sold_on', 24)->nullable();
            $table->string('completion_status', 24)->default('skipped');
            $table->string('survey_version', 20)->default('v1');
            $table->dateTime('deactivated_at');

            // Snapshot for historical reporting, even if the listing is edited or deleted later.
            $table->string('title')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('model_name')->nullable();
            $table->unsignedInteger('an_fabricatie')->nullable();
            $table->unsignedInteger('km')->nullable();
            $table->decimal('price_value', 14, 4)->nullable();
            $table->string('currency', 8)->nullable();
            $table->decimal('price_eur', 14, 4)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('county_id')->nullable();
            $table->unsignedBigInteger('locality_id')->nullable();
            $table->string('county_name')->nullable();
            $table->string('locality_name')->nullable();
            $table->string('seller_type', 24)->nullable();
            $table->dateTime('service_created_at')->nullable();
            $table->dateTime('service_published_at')->nullable();

            $table->timestamps();

            $table->index(['deactivated_at', 'answer', 'sold_on'], 'service_feedback_reporting_idx');
            $table->index(['brand_id', 'model_id', 'deactivated_at'], 'service_feedback_vehicle_idx');
            $table->index(['user_id', 'deactivated_at'], 'service_feedback_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_deactivation_feedback');
    }
};
