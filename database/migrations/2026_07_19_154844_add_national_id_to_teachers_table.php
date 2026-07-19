<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('national_id', 50)->nullable()->unique()->after('name');
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique(['national_id']);
            $table->dropColumn('national_id');
            $table->string('email')->nullable(false)->change();
        });
    }
};
