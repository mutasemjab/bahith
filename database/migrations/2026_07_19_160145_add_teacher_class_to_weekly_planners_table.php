<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('weekly_planners', function (Blueprint $table) {
            $table->unsignedBigInteger('teacher_id')->nullable()->after('id');
            $table->unsignedBigInteger('class_id')->nullable()->after('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_planners', function (Blueprint $table) {
            $table->dropColumn(['teacher_id', 'class_id']);
        });
    }
};
