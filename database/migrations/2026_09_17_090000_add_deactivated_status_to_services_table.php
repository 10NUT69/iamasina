<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUSES = "'active','pending','expired','rejected','deactivated'";

    public function up(): void
    {
        if (! Schema::hasColumn('services', 'status') || DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `services` MODIFY `status` ENUM('.self::STATUSES.") NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (! Schema::hasColumn('services', 'status') || DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('services')->where('status', 'deactivated')->update(['status' => 'pending']);
        DB::statement("ALTER TABLE `services` MODIFY `status` ENUM('active','pending','expired','rejected') NOT NULL DEFAULT 'pending'");
    }
};
