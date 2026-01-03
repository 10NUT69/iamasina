<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['individual', 'dealer'])
                ->default('individual')
                ->after('password');

            $table->string('company_name')->nullable()->after('user_type');
            $table->string('cui', 32)->nullable()->after('company_name');
            $table->string('phone', 32)->nullable()->after('cui');
            $table->string('county')->nullable()->after('phone');
            $table->string('city')->nullable()->after('county');
            $table->string('address')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'user_type',
                'company_name',
                'cui',
                'phone',
                'county',
                'city',
                'address',
            ]);
        });
    }
};
