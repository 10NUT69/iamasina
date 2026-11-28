<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dealer_feed_runs', function (Blueprint $table) {
            $table->string('import_status', 20)->nullable();
            $table->text('import_error')->nullable();
            $table->unsignedInteger('import_unchanged')->default(0);
        });

        Schema::table('dealer_feed_listings', function (Blueprint $table) {
            $table->foreignId('import_run_id')->nullable()->constrained('dealer_feed_runs')->nullOnDelete();
            $table->string('import_status', 20)->nullable();
            $table->text('import_error')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('dealer_feed_listings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('import_run_id');
            $table->dropColumn(['import_status', 'import_error']);
        });

        Schema::table('dealer_feed_runs', function (Blueprint $table) {
            $table->dropColumn(['import_status', 'import_error', 'import_unchanged']);
        });
    }
};
