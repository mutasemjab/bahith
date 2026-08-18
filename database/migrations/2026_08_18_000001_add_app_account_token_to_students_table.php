<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->uuid('app_account_token')->nullable()->unique()->after('deviceId');
        });

        DB::table('students')
            ->select('id')
            ->whereNull('app_account_token')
            ->orderBy('id')
            ->chunkById(500, function ($students): void {
                foreach ($students as $student) {
                    DB::table('students')
                        ->where('id', $student->id)
                        ->whereNull('app_account_token')
                        ->update(['app_account_token' => (string) Str::uuid()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['app_account_token']);
            $table->dropColumn('app_account_token');
        });
    }
};
