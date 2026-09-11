<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('subject_id')->constrained('teachers')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->after('teacher_id')->constrained('classes')->nullOnDelete();
        });

        // Backfill teacher_id for existing exams from their linked course's owner,
        // so ownership checks that already relied on course->teacher_id keep working.
        DB::statement('
            UPDATE exams
            JOIN courses ON courses.id = exams.course_id
            SET exams.teacher_id = courses.teacher_id
            WHERE exams.course_id IS NOT NULL AND courses.teacher_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['class_id']);
            $table->dropColumn(['teacher_id', 'class_id']);
        });
    }
};
