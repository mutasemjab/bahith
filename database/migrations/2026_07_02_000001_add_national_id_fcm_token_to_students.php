<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('national_id', 50)->nullable()->unique()->after('phone');
            $table->string('fcm_token')->nullable()->after('national_id');
        });

        // Make email nullable (students can now login by national_id only)
        DB::statement('ALTER TABLE students MODIFY email VARCHAR(200) NULL');
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['national_id', 'fcm_token']);
        });

        DB::statement('ALTER TABLE students MODIFY email VARCHAR(200) NOT NULL');
    }
};
